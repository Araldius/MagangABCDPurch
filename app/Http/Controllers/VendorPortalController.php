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

        $vendorId = session('last_vendor_id') ?: $rfq->vendor_id;
        $quotation = Quotation::with('details')->where('rfq_id', $rfq->id)->where('vendor_id', $vendorId)->first();
        $currentVendor = $vendorId ? Vendor::find($vendorId) : null;

        $existingItems = [];
        if ($quotation) {
            foreach ($quotation->details as $det) {
                $k = $det->purchase_request_item_id ?: $det->service_request_item_id;
                $existingItems[$k] = $det;
            }
        }

        return view('vendors.quote', compact('rfq', 'items', 'neededDate', 'closedDate', 'vendors', 'closedReason', 'existingItems', 'currentVendor'));
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
            'attachment' => 'nullable|file|mimes:pdf,xlsx,xls,jpg,jpeg,png|max:10240',
            'items' => 'required|array',
            'items.*.item_id' => 'required',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'nullable|string',
            'items.*.specification' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        if (!$request->filled('vendor_id') && str_contains($data['email'], '*')) {
            return back()->withInput()->withErrors(['email' => 'Silakan pilih vendor dari daftar dengan benar, atau masukkan alamat email lengkap secara manual jika membuat vendor baru (tanpa tanda bintang).']);
        }

        // Find or create vendor
        $vendor = null;
        if ($request->filled('vendor_id')) {
            $vendor = Vendor::find($request->vendor_id);
        }

        if (!$vendor) {
            $vendor = Vendor::firstOrCreate(
                ['vendor_name' => $data['vendor_name'], 'email' => $data['email']],
                ['location' => $data['vendor_location'] ?? '-', 'status' => 'active']
            );
        }

        // Find existing quotation for this vendor + RFQ
        $quotation = Quotation::where('rfq_id', $rfq->id)
            ->where('vendor_id', $vendor->id)
            ->first();

        if ($quotation && !$request->has('confirm_overwrite')) {
            return back()->withInput()->with('overwrite_warning', 'You have previously submitted a quotation for this request. Do you want to overwrite your previous submission?');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('quotations', 'public');
        }

        if ($quotation) {
            // Update
            $updateData = [
                'total_price' => collect($data['items'])->sum(fn($it) => $it['price'] * $it['quantity']),
                'note' => $data['note'] ?? null,
                'status' => 'submitted',
            ];
            if ($attachmentPath) {
                $updateData['attachment_path'] = $attachmentPath;
            }
            if (!$quotation->vendor_token) {
                $updateData['vendor_token'] = Str::random(40);
            }
            $quotation->update($updateData);
            QuotationDetail::where('quotation_id', $quotation->id)->delete();
        } else {
            // Create
            $quotation = Quotation::create([
                'rfq_id' => $rfq->id,
                'vendor_id' => $vendor->id,
                'total_price' => collect($data['items'])->sum(fn($it) => $it['price'] * $it['quantity']),
                'note' => $data['note'] ?? null,
                'status' => 'submitted',
                'vendor_token' => Str::random(40),
                'attachment_path' => $attachmentPath,
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

        // Send Quotation Details + Attachment to Company Email (using system email)
        $companyEmail = env('MAIL_FROM_ADDRESS', 'purchasing@duniakimiajaya.com');
        Notification::route('mail', $companyEmail)->notify(new \App\Notifications\CompanyQuotationSubmitted($rfq, $vendor, $quotation));

        // Send Edit Link to Vendor
        if ($quotation->vendor_token) {
            $vendor->notify(new \App\Notifications\VendorQuotationEditLink($rfq, $vendor, $quotation->vendor_token));
        }

        return back()->with('success', 'Quotation submitted successfully. An edit link has been sent to your email address.');
    }

    public function showEdit($token)
    {
        $quotation = Quotation::where('vendor_token', $token)->firstOrFail();
        $rfq = $quotation->rfq;
        
        // Temporarily set session to remember who is editing
        session(['last_vendor_id' => $quotation->vendor_id]);
        
        return $this->show($rfq->vendor_token);
    }

    public function submitEdit(Request $request, $token)
    {
        $quotation = Quotation::where('vendor_token', $token)->firstOrFail();
        $rfq = $quotation->rfq;
        
        // Pass confirm_overwrite to skip the warning
        $request->merge(['confirm_overwrite' => true]);
        
        return $this->submit($request, $rfq->vendor_token);
    }

    /**
     * Autocomplete endpoint for vendor name with word matching and server-side masking.
     */
    public function autocomplete(Request $request)
    {
        $q = $request->query('q', '');
        if (empty($q)) {
            return response()->json([]);
        }

        $words = array_filter(explode(' ', $q));
        $queryBuilder = Vendor::query();

        foreach ($words as $word) {
            $queryBuilder->where('vendor_name', 'like', '%' . $word . '%');
        }

        $vendors = $queryBuilder->limit(8)->get(['id', 'vendor_name', 'location', 'email']);

        $maskedVendors = $vendors->map(function ($v) {
            return [
                'id' => $v->id,
                'vendor_name' => $v->vendor_name,
                'location' => $this->maskLocation($v->location),
                'email' => $this->maskEmail($v->email),
            ];
        });

        return response()->json($maskedVendors);
    }

    private function maskEmail($email)
    {
        if (!$email) return '';
        $parts = explode('@', $email);
        if (count($parts) < 2) return str_repeat('*', strlen($email));
        $name = $parts[0];
        $domain = $parts[1];
        $maskedName = strlen($name) <= 2 ? $name . '*' : substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
        return $maskedName . '@' . $domain;
    }

    private function maskLocation($location)
    {
        if (!$location || $location === '-') return '-';
        return strlen($location) <= 3 ? $location . '*' : substr($location, 0, 3) . str_repeat('*', strlen($location) - 3);
    }
}