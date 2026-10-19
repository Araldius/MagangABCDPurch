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
    public function show(Request $request, $token)
    {
        if ($request->query('new')) {
            session()->forget('last_vendor_id');
        }

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

        $vendors = collect();

        $quotation = $request->attributes->get('editing_quotation');
        $currentVendor = $quotation ? $quotation->vendor : null;

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
            $vendors = collect();
            return response()->view('vendors.quote', compact('rfq', 'items', 'neededDate', 'closedDate', 'vendors', 'closedReason'), 403);
        }

        if ($closedDate && now()->gt($closedDate)) {
            // We no longer block submission here. Vendors can submit overdue quotations.
        }

        $data = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'email' => 'nullable|string|max:255',
            'vendor_location' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,xlsx,xls,jpg,jpeg,png|max:10240',
            'items' => 'required|array',
            'items.*.item_id' => 'required',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.unit' => 'nullable|string',
            'items.*.specification' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        // If registering a brand-new vendor and email was entered manually (not blurred/masked), check email format
        if (!$request->filled('vendor_id') && !empty($data['email'])) {
            if (str_contains($data['email'], '•') || str_contains($data['email'], '*')) {
                $data['email'] = null;
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return back()->withInput()->withErrors(['email' => 'Format email tidak valid.']);
            }
        }

        // Filter items that have a quoted price > 0
        $quotedItems = collect($data['items'])->filter(function ($it) {
            return isset($it['price']) && $it['price'] !== '' && $it['price'] !== null && (float) $it['price'] > 0;
        });

        if ($quotedItems->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'Mohon isi harga penawaran minimal pada 1 item.']);
        }

        // Find or create vendor by exact company name (so similar names like Trial & Trial2 or Citra Pratama & Citra Sejahtera remain distinct)
        $vendor = null;
        if ($request->filled('vendor_id')) {
            $vendor = Vendor::find($request->vendor_id);
            if ($vendor && strtolower(trim($vendor->vendor_name)) !== strtolower(trim($data['vendor_name']))) {
                $vendor = null;
            }
        }

        if (!$vendor) {
            $vendor = Vendor::whereRaw('LOWER(TRIM(vendor_name)) = ?', [strtolower(trim($data['vendor_name']))])->first();
        }

        if (!$vendor) {
            $vendor = Vendor::create([
                'vendor_name' => trim($data['vendor_name']),
                'email' => (!empty($data['email']) && !str_contains($data['email'], '*') && !str_contains($data['email'], '•')) ? $data['email'] : null,
                'location' => (!empty($data['vendor_location']) && !str_contains($data['vendor_location'], '*') && !str_contains($data['vendor_location'], '•')) ? $data['vendor_location'] : '-',
                'status' => 'active',
            ]);
        } else {
            $vendorUpdates = [];
            if (!empty($data['email']) && !str_contains($data['email'], '*') && !str_contains($data['email'], '•')) {
                $vendorUpdates['email'] = $data['email'];
            }
            if (!empty($data['vendor_location']) && !str_contains($data['vendor_location'], '*') && !str_contains($data['vendor_location'], '•')) {
                $vendorUpdates['location'] = $data['vendor_location'];
            }
            if (!empty($vendorUpdates)) {
                $vendor->update($vendorUpdates);
            }
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

        $totalPrice = $quotedItems->sum(fn($it) => (float) $it['price'] * (float) ($it['quantity'] ?? 1));

        if ($quotation) {
            // Update
            $updateData = [
                'total_price' => $totalPrice,
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
                'total_price' => $totalPrice,
                'note' => $data['note'] ?? null,
                'status' => 'submitted',
                'vendor_token' => Str::random(40),
                'attachment_path' => $attachmentPath,
            ]);
        }

        $isService = (bool) $rfq->service_request_id;
        foreach ($quotedItems as $it) {
            QuotationDetail::create([
                'quotation_id' => $quotation->id,
                'purchase_request_item_id' => $isService ? null : $it['item_id'],
                'service_request_item_id' => $isService ? $it['item_id'] : null,
                'offered_price_per_item' => $it['price'],
                'offered_quantity' => $it['quantity'] ?? 1,
                'offered_unit' => $it['unit'] ?? null,
                'offered_brand' => $it['brand'] ?? null,
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

        // Send Notifications in the background (after HTTP response is sent) to prevent slow loading
        $purchasingUsers = User::where('role', 'purchasing')->get();
        $pr = $rfq->purchaseRequest ?? $rfq->serviceRequest;
        $userCreator = ($pr && $pr->user_id) ? User::find($pr->user_id) : null;
        
        dispatch(function () use ($purchasingUsers, $userCreator, $rfq, $vendor, $quotation) {
            try {
                // 1. In-app database notification to all Purchasing users
                if ($purchasingUsers->count() > 0) {
                    Notification::send($purchasingUsers, new VendorQuotationSubmitted($rfq, $vendor));
                }

                // 2. In-app database notification to PR creator
                if ($userCreator) {
                    $userCreator->notify(new VendorQuotationSubmitted($rfq, $vendor));
                }

                // 3. Email Quotation Details + Attachment to all Purchasing users
                if ($purchasingUsers->count() > 0) {
                    Notification::send($purchasingUsers, new \App\Notifications\CompanyQuotationSubmitted($rfq, $vendor, $quotation));
                }

                // 4. Send Quotation Details to Company Email if configured
                $companyEmail = config('mail.from.address') ?: env('MAIL_FROM_ADDRESS');
                if ($companyEmail && !in_array($companyEmail, $purchasingUsers->pluck('email')->toArray())) {
                    Notification::route('mail', $companyEmail)->notify(new \App\Notifications\CompanyQuotationSubmitted($rfq, $vendor, $quotation));
                }

                // 5. Send Edit Link to Vendor
                if ($quotation->vendor_token && !empty($vendor->email)) {
                    $vendor->notify(new \App\Notifications\VendorQuotationEditLink($rfq, $vendor, $quotation->vendor_token));
                }
            } catch (\Throwable $e) {
                \Log::error('Error sending quotation notifications: ' . $e->getMessage());
            }
        })->afterResponse();

        return back()->with('success', 'Quotation submitted successfully. An edit link has been sent to your email address.');
    }

    public function showEdit(Request $request, $token)
    {
        $quotation = Quotation::with('details', 'vendor')->where('vendor_token', $token)->firstOrFail();
        $rfq = $quotation->rfq;
        
        $request->attributes->set('editing_quotation', $quotation);
        
        return $this->show($request, $rfq->vendor_token);
    }

    public function submitEdit(Request $request, $token)
    {
        $quotation = Quotation::where('vendor_token', $token)->firstOrFail();
        $rfq = $quotation->rfq;
        
        // Pass confirm_overwrite and vendor_id to skip the warning
        $request->merge([
            'confirm_overwrite' => true,
            'vendor_id' => $quotation->vendor_id,
        ]);
        
        return $this->submit($request, $rfq->vendor_token);
    }

    /**
     * Autocomplete endpoint for vendor name with location masking and full email blurring/masking.
     */
    public function autocomplete(Request $request)
    {
        $q = trim($request->query('q', ''));
        $queryBuilder = Vendor::query();

        if (!empty($q)) {
            $words = array_filter(explode(' ', $q));
            foreach ($words as $word) {
                $queryBuilder->where('vendor_name', 'like', '%' . $word . '%');
            }
        }

        $vendors = $queryBuilder->orderBy('vendor_name')->limit(100)->get(['id', 'vendor_name', 'location', 'email']);

        $maskedVendors = $vendors->map(function ($v) {
            return [
                'id' => $v->id,
                'vendor_name' => $v->vendor_name,
                'location' => $this->maskLocation($v->location),
                // Email is fully masked as bullets so real email is never transmitted to frontend
                'email' => !empty($v->email) ? '••••••••••••••••' : '-',
            ];
        });

        return response()->json($maskedVendors);
    }

    private function maskLocation($location)
    {
        if (!$location || $location === '-') return '-';
        $len = strlen($location);
        if ($len <= 2) {
            return substr($location, 0, 1) . '*';
        }
        if ($len <= 4) {
            return substr($location, 0, 2) . str_repeat('*', $len - 2);
        }
        $maskedCount = max(3, $len - 3);
        return substr($location, 0, 3) . str_repeat('*', $maskedCount);
    }
}