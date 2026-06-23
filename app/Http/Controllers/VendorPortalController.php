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

        if ($rfq->token_expires_at < now()) {
            return abort(403, 'Tautan ini sudah kedaluwarsa.');
        }

        $pr = $rfq->purchaseRequest ?? $rfq->serviceRequest;
        if ($pr && !in_array($pr->status, ['vendor_search', 'vendor_selection', 'submitted'])) {
            return abort(403, 'Permintaan ini sudah diproses dan tidak menerima penawaran lagi.');
        }

        $neededDateRaw = $rfq->purchaseRequest ? $rfq->purchaseRequest->need_date : ($rfq->serviceRequest ? $rfq->serviceRequest->requested_date : null);
        $neededDate = $neededDateRaw ? \Carbon\Carbon::parse($neededDateRaw)->startOfDay() : null;
        $closedDate = $neededDate ? $neededDate->copy()->subDay()->endOfDay() : null;

        if ($closedDate && now()->gt($closedDate)) {
            return view('vendors.closed', compact('rfq', 'pr', 'neededDate', 'closedDate'));
        }

        $items = $rfq->purchaseRequest ? $rfq->purchaseRequest->items : collect();
        if ($rfq->serviceRequest) {
            foreach ($rfq->serviceRequest->jobs as $job) {
                $items = $items->merge($job->items);
            }
        }

        return view('vendors.quote', compact('rfq', 'items', 'neededDate', 'closedDate'));
    }

    public function submit(Request $request, $token)
    {
        $rfq = Rfq::where('vendor_token', $token)->firstOrFail();

        if ($rfq->token_expires_at < now()) {
            return back()->withErrors(['error' => 'Tautan ini sudah kedaluwarsa.']);
        }

        $pr = $rfq->purchaseRequest ?? $rfq->serviceRequest;
        if ($pr && !in_array($pr->status, ['vendor_search', 'vendor_selection', 'submitted'])) {
            return back()->withErrors(['error' => 'Permintaan ini sudah diproses dan tidak menerima penawaran lagi.']);
        }

        $neededDateRaw = $rfq->purchaseRequest ? $rfq->purchaseRequest->need_date : ($rfq->serviceRequest ? $rfq->serviceRequest->requested_date : null);
        $neededDate = $neededDateRaw ? \Carbon\Carbon::parse($neededDateRaw)->startOfDay() : null;
        $closedDate = $neededDate ? $neededDate->copy()->subDay()->endOfDay() : null;

        if ($closedDate && now()->gt($closedDate)) {
            return back()->withErrors(['error' => 'Masa penawaran (Quotation) telah ditutup karena sudah melewati batas waktu H-1 dari tanggal dibutuhkan.']);
        }

        $data = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'vendor_contact' => 'required|string|max:255',
            'vendor_location' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*.item_id' => 'required',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        // Find or create vendor based on exact name and contact
        $vendor = Vendor::firstOrCreate(
            ['vendor_name' => $data['vendor_name'], 'contact' => $data['vendor_contact']],
            ['location' => $data['vendor_location'] ?? '-', 'status' => 'active']
        );

        // Find existing quotation for this vendor + RFQ
        $quotation = Quotation::where('rfq_id', $rfq->id)
            ->where('vendor_id', $vendor->id)
            ->first();

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