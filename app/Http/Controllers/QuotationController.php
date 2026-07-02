<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\QuotationSummary;
use App\Models\Rfq;
use App\Models\SelectionItem;
use App\Models\PurchaseRequest;
use App\Models\VendorSelection;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function create(Rfq $rfq)
    {
        $rfq->load([
            'purchaseRequest.items',
            'serviceRequest.jobs.items',
            'vendor'
        ]);

        return view('quotations.create', compact('rfq'));
    }

    public function store(Request $request, Rfq $rfq)
    {
        $data = $request->validate([
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'new_vendor_name' => ['nullable', 'string', 'max:255'],
            'new_vendor_location' => ['nullable', 'string', 'max:255'],
            'new_vendor_email' => ['nullable', 'email', 'max:255'],
            'items' => ['required', 'array'],
            'items.*.item_id' => ['required'], // PR item or SR item ID
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
        ]);

        // Resolve vendor
        $vendorId = $data['vendor_id'];
        if (!$vendorId && !empty($data['new_vendor_name'])) {
            $vendor = \App\Models\Vendor::create([
                'vendor_name' => $data['new_vendor_name'],
                'location' => $data['new_vendor_location'],
                'email' => $data['new_vendor_email'],
                'status' => 'active',
            ]);
            $vendorId = $vendor->id;
        }

        if (!$vendorId) {
            return back()->withErrors(['vendor_id' => 'Please select or create a vendor.'])->withInput();
        }

        // Update RFQ vendor if not set
        if (!$rfq->vendor_id) {
            $rfq->vendor_id = $vendorId;
            $rfq->save();
        }

        // Create Quotation
        $quotation = Quotation::create([
            'rfq_id' => $rfq->id,
            'vendor_id' => $vendorId,
            'total_price' => collect($data['items'])->sum(fn($it) => $it['price'] * $it['quantity']),
            'note' => $data['note'] ?? null,
            'status' => 'submitted',
        ]);

        // Create details
        $isService = (bool) $rfq->service_request_id;
        foreach ($data['items'] as $it) {
            QuotationDetail::create([
                'quotation_id' => $quotation->id,
                'purchase_request_item_id' => $isService ? null : $it['item_id'],
                'service_request_item_id' => $isService ? $it['item_id'] : null,
                'offered_price_per_item' => $it['price'],
                'offered_quantity' => $it['quantity'],
                'offered_unit' => $it['unit'] ?? null,
            ]);
        }

        // Transition PR/SR status to vendor_selection if it is currently vendor_search
        $pr = $isService ? $rfq->serviceRequest : $rfq->purchaseRequest;
        if ($pr && $pr->status === 'vendor_search') {
            $pr->status = 'vendor_selection';
            $pr->save();
        }

        History::create([
            'user_id' => auth()->id(),
            'vendor_id' => $vendorId,
            'rfq_id' => $rfq->id,
            'action' => 'Manual Quotation Added',
            'transaction_status' => 'completed',
            'notes' => 'Quotation added manually by Admin',
            'action_date' => now(),
        ]);

                // Mulai: Kirim Notifikasi
        $vendorModel = \App\Models\Vendor::find($vendorId);
        if ($vendorModel) {
            
            // 1. Kirim ke User Pembuat PR/SR (Peminta Barang)
            if ($pr && $pr->user_id) {
                $userCreator = \App\Models\User::find($pr->user_id);
                if ($userCreator) {
                    $userCreator->notify(new \App\Notifications\VendorQuotationSubmitted($rfq, $vendorModel));
                }
            }
            
            // 2. Kirim juga ke Seluruh Tim Purchasing (termasuk Anda)
            $purchasingUsers = \App\Models\User::where('role', 'purchasing')->get();
            if ($purchasingUsers->count() > 0) {
                \Illuminate\Support\Facades\Notification::send($purchasingUsers, new \App\Notifications\VendorQuotationSubmitted($rfq, $vendorModel));
            }
        }
        // Selesai: Kirim Notifikasi

        return redirect()->route('dashboard')->with('success', 'Manual quotation saved successfully.');
    }

    public function status(Rfq $rfq)
    {
        $rfq->load(['purchaseRequest', 'vendor', 'vendorQuotations.vendor', 'quotationPeriods', 'quotation.details']);

        return view('quotations.status', compact('rfq'));
    }

    public function updateStatus(Request $request, Rfq $rfq)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,closed'],
            'closed_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $rfq->status = $data['status'];
        $rfq->closed_at = $data['status'] === 'closed' ? ($data['closed_at'] ?? now()) : null;
        $rfq->note = $data['note'] ?? $rfq->note;
        $rfq->save();

        if ($data['status'] === 'closed') {
            $period = $rfq->quotationPeriods()->latest('round')->first();
            if ($period) {
                $period->status = 'closed';
                $period->save();
            }
        }

        History::create([
            'user_id' => auth()->id(),
            'vendor_id' => $rfq->vendor_id,
            'rfq_id' => $rfq->id,
            'vendor_selection_id' => null,
            'action' => 'RFQ Status Updated',
            'transaction_status' => 'completed',
            'notes' => 'RFQ status set to ' . $rfq->status,
            'action_date' => now(),
        ]);

        return redirect()->route('quotations.final', $rfq)->with('success', 'Status RFQ berhasil diperbarui.');
    }

    public function final(Rfq $rfq)
    {
        $quotation = $rfq->quotation;
        $isService = (bool) $rfq->service_request_id;
        $items = $isService ? $rfq->serviceRequest->jobs->flatMap->items : $rfq->purchaseRequest->items;
        $details = $quotation ? $quotation->details->keyBy($isService ? 'service_request_item_id' : 'purchase_request_item_id') : collect();

        return view('quotations.final', compact('rfq', 'quotation', 'items', 'details'));
    }

    public function storeFinal(Request $request, Rfq $rfq)
    {
        $isService = (bool) $rfq->service_request_id;
        $itemTable = $isService ? 'service_request_items' : 'purchase_request_items';
        $itemColumn = $isService ? 'service_request_item_id' : 'purchase_request_item_id';

        $data = $request->validate([
            'total_price' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            "items.*.{$itemColumn}" => ['required', "exists:{$itemTable},id"],
            'items.*.offered_price_per_item' => ['required', 'numeric', 'min:0'],
            'items.*.offered_quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $quotation = Quotation::updateOrCreate([
            'rfq_id' => $rfq->id,
        ], [
            'vendor_id' => $rfq->vendor_id,
            'total_price' => $data['total_price'],
            'note' => $data['note'] ?? null,
            'status' => 'finalized',
        ]);

        QuotationDetail::where('quotation_id', $quotation->id)->delete();
        QuotationSummary::where('rfq_id', $rfq->id)->delete();

        foreach ($data['items'] as $itemData) {
            $detail = QuotationDetail::create([
                'quotation_id' => $quotation->id,
                'purchase_request_item_id' => $isService ? null : $itemData['purchase_request_item_id'],
                'service_request_item_id' => $isService ? $itemData['service_request_item_id'] : null,
                'offered_price_per_item' => $itemData['offered_price_per_item'],
                'offered_quantity' => $itemData['offered_quantity'],
            ]);

            QuotationSummary::create([
                'rfq_id' => $rfq->id,
                'quotation_detail_id' => $detail->id,
                'is_sent_to_user' => true,
                'sent_to_user_at' => now(),
            ]);
        }

        $selection = VendorSelection::updateOrCreate([
            'rfq_id' => $rfq->id,
        ], [
            'vendor_id' => $rfq->vendor_id,
            'quotation_id' => $quotation->id,
            'decision_notes' => 'Final quotation selected and approved.',
            'decided_at' => now(),
        ]);

        SelectionItem::where('vendor_selection_id', $selection->id)->delete();

        $summaryIds = QuotationSummary::where('rfq_id', $rfq->id)
            ->pluck('id', 'quotation_detail_id')
            ->toArray();

        foreach ($quotation->details as $detail) {
            SelectionItem::create([
                'vendor_selection_id' => $selection->id,
                'quotation_summary_id' => $summaryIds[$detail->id] ?? null,
                'final_price_per_item' => $detail->offered_price_per_item,
                'final_quantity' => $detail->offered_quantity,
                'notes' => 'Item included in final vendor selection.',
            ]);
        }

        $rfq->status = 'closed';
        $rfq->save();
        
        if ($isService) {
            $rfq->serviceRequest->update(['status' => 'completed']);
        } else {
            $rfq->purchaseRequest->update(['status' => 'completed']);
        }

        History::create([
            'user_id' => auth()->id(),
            'vendor_id' => $rfq->vendor_id,
            'rfq_id' => $rfq->id,
            'vendor_selection_id' => $selection->id,
            'action' => 'Quotation Finalized',
            'transaction_status' => 'completed',
            'notes' => 'Final quotation saved for RFQ ' . $rfq->id,
            'action_date' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Quotation final berhasil disimpan.');
    }

    public function generateVendorLink(Request $request, Rfq $rfq)
    {
        // 1 tautan spesifik untuk 1 RFQ (berlaku untuk beberapa vendor). Berlaku selama PR masih open.
        if (!$rfq->vendor_token) {
            $rfq->vendor_token = \Illuminate\Support\Str::random(32);
            $rfq->save();
        }

        $link = url('/vendors/quote/' . $rfq->vendor_token);
        return response()->json(['link' => $link]);
    }
}