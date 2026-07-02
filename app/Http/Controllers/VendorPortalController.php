<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\History;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VendorQuotationSubmitted;

class VendorPortalController extends Controller
{
    public function show($token)
    {
        $rfq = Rfq::where('vendor_token', $token)->firstOrFail();
        $pr = $rfq->purchaseRequest ?? $rfq->serviceRequest;

        $neededDateRaw = $rfq->purchaseRequest ? $rfq->purchaseRequest->need_date : ($rfq->serviceRequest ? $rfq->serviceRequest->requested_date : null);
        $neededDate = $neededDateRaw ? \Carbon\Carbon::parse($neededDateRaw)->startOfDay() : null;
        $closedDate = $neededDate ? $neededDate->copy()->subDay()->endOfDay() : null;

        $closedReason = null;
        if ($pr && !in_array($pr->status, ['vendor_search', 'vendor_selection', 'submitted'])) {
            $closedReason = 'completed';
        }

        $items = $rfq->purchaseRequest ? $rfq->purchaseRequest->items : collect();
        if ($rfq->serviceRequest) {
            foreach ($rfq->serviceRequest->jobs as $job) {
                $items = $items->merge($job->items);
            }
        }

        $vendors = Vendor::select('id', 'vendor_name', 'email', 'location')->get();

        $quotation = Quotation::with('details')->where('rfq_id', $rfq->id)->where('vendor_id', $rfq->vendor_id)->first();
        $existingItems = [];
        if ($quotation) {
            foreach ($quotation->details as $det) {
                $k = $det->purchase_request_item_id ?: $det->service_request_item_id;
                $existingItems[$k] = $det;
            }
        }

        return view('vendors.quote', compact('rfq', 'items', 'neededDate', 'closedDate', 'vendors', 'closedReason', 'existingItems'));
    }

    public function submit(Request $request, $token)
    {
        $rfq = Rfq::where('vendor_token', $token)->firstOrFail();
        $pr = $rfq->purchaseRequest ?? $rfq->serviceRequest;

        $neededDateRaw = $rfq->purchaseRequest ? $rfq->purchaseRequest->need_date : ($rfq->serviceRequest ? $rfq->serviceRequest->requested_date : null);
        $neededDate = $neededDateRaw ? \Carbon\Carbon::parse($neededDateRaw)->startOfDay() : null;
        $closedDate = $neededDate ? $neededDate->copy()->subDay()->endOfDay() : null;

        $closedReason = null;
        if ($pr && !in_array($pr->status, ['vendor_search', 'vendor_selection', 'submitted'])) {
            $closedReason = 'completed';
        }

        if ($closedReason) {
            $items = $rfq->purchaseRequest ? $rfq->purchaseRequest->items : collect();
            if ($rfq->serviceRequest) {
                foreach ($rfq->serviceRequest->jobs as $job) {
                    $items = $items->merge($job->items);
                }
            }
            $vendors = Vendor::select('id', 'vendor_name', 'email', 'location')->get();
            return response()->view('vendors.quote', compact('rfq', 'items', 'neededDate', 'closedDate', 'vendors', 'closedReason'), 403);
        }

        $neededDateRaw = $rfq->purchaseRequest ? $rfq->purchaseRequest->need_date : ($rfq->serviceRequest ? $rfq->serviceRequest->requested_date : null);
        $neededDate = $neededDateRaw ? \Carbon\Carbon::parse($neededDateRaw)->startOfDay() : null;
        $closedDate = $neededDate ? $neededDate->copy()->subDay()->endOfDay() : null;

        if ($closedDate && now()->gt($closedDate)) {
            // We no longer block submission here. Vendors can submit overdue quotations.
        }

        $data = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'vendor_location' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*.item_id' => 'required',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'nullable|string',
            'items.*.specification' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        // Find or create vendor based on exact name and contact
        $vendor = Vendor::firstOrCreate(
            ['vendor_name' => $data['vendor_name'], 'email' => $data['email']],
            ['location' => $data['vendor_location'] ?? '-', 'status' => 'active']
        );

        // Find existing quotation for this vendor + RFQ
        $quotation = Quotation::where('rfq_id', $rfq->id)
            ->where('vendor_id', $vendor->id)
            ->first();

        if ($quotation && !$request->has('confirm_overwrite')) {
            return back()->withInput()->with('overwrite_warning', 'You have previously submitted a quotation for this request. Do you want to overwrite your previous submission?');
        }

        if ($quotation) {
            // Update
            $quotation->update([
                'total_price' => collect($data['items'])->sum(fn($it) => $it['price'] * $it['quantity']),
                'note' => $data['note'] ?? null,
                'status' => 'submitted',
            ]);
            QuotationDetail::where('quotation_id', $quotation->id)->delete();
        } else {
            // Create
            $quotation = Quotation::create([
                'rfq_id' => $rfq->id,
                'vendor_id' => $vendor->id,
                'total_price' => collect($data['items'])->sum(fn($it) => $it['price'] * $it['quantity']),
                'note' => $data['note'] ?? null,
                'status' => 'submitted',
            ]);
        }

        $isService = (bool) $rfq->service_request_id;
        foreach ($data['items'] as $it) {
            QuotationDetail::create([
                'quotation_id' => $quotation->id,
                'purchase_request_item_id' => $isService ? null : $it['item_id'],
                'service_request_item_id' => $isService ? $it['item_id'] : null,
                'offered_price_per_item' => $it['price'],
                'offered_quantity' => $it['quantity'],
                'offered_unit' => $it['unit'] ?? null,
                'offered_specification' => $it['specification'] ?? null,
                'item_notes' => $it['notes'] ?? null,
            ]);
        }

        if ($pr && $pr->status === 'vendor_search') {
            $pr->status = 'vendor_selection';
            $pr->save();
        }

        History::create([
            'user_id' => null,
            'vendor_id' => $vendor->id,
            'rfq_id' => $rfq->id,
            'action' => 'Vendor Submitted Quotation via Link',
            'transaction_status' => 'completed',
            'notes' => 'Quotation submitted by ' . $vendor->vendor_name,
            'action_date' => now(),
        ]);

        // Send Notification to all purchasing users
        $purchasingUsers = User::where('role', 'purchasing')->get();
        if ($purchasingUsers->count() > 0) {
            Notification::send($purchasingUsers, new VendorQuotationSubmitted($rfq, $vendor));
        }

        return back()->with('success', 'Quotation berhasil dikirim! Terima kasih atas penawaran Anda.');
    }
}
