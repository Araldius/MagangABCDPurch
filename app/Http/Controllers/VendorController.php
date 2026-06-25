<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\PurchaseRequest;
use App\Models\ServiceRequest;
use App\Models\Rfq;
use App\Models\SelectionItem;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Models\VendorSelection;
use App\Models\QuotationDetail;
use App\Models\QuotationSummary;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    // ─────────────────────────────────────────────
    // Shared data builder (used by index + select)
    // ─────────────────────────────────────────────
    private function buildViewData(): array
    {
        $user = auth()->user();
        $validStatuses = ['vendor_search', 'vendor_selection'];

        $prs = PurchaseRequest::with(['items', 'rfqs.quotations.details'])
            ->whereIn('status', $validStatuses)
            ->when($user->role !== 'purchasing', fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->get()
            ->map(function ($pr) {
                $pr->setAttribute('type', 'goods');
                $pr->setAttribute('display_doc', $pr->document_number);
                $pr->setAttribute('display_title', $pr->title);
                $pr->setAttribute('document_number', $pr->document_number);
                $pr->setAttribute('title', $pr->title);
                return $pr;
            });

        $srs = ServiceRequest::with(['jobs.items', 'rfqs.quotations.details'])
            ->whereIn('status', $validStatuses)
            ->when($user->role !== 'purchasing', fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->get()
            ->map(function ($sr) {
                $docNum = $sr->document_number
                    ?? ('SR-' . ($sr->created_at ?? now())->format('Y') . '-' . str_pad($sr->id, 4, '0', STR_PAD_LEFT));
                $sr->setAttribute('type', 'service');
                $sr->setAttribute('display_doc', $docNum);
                $sr->setAttribute('display_title', $sr->service_name);
                $sr->setAttribute('document_number', $docNum);
                $sr->setAttribute('title', $sr->service_name);
                return $sr;
            });

        return [
            'prs'     => $prs->concat($srs),
            'vendors' => Vendor::where('status', 'active')->get(),
        ];
    }

    // ─────────────────────────────────────────────
    // GET /vendor-selection[?key=type_id]
    // ─────────────────────────────────────────────
    public function index(Request $request)
    {
        // ?key=goods_1 or ?key=service_1 — passed from modal "Select Vendor" button
        $selectedKey = $request->query('key', '');

        return view('vendors.index', array_merge(
            $this->buildViewData(),
            ['selectedKey' => $selectedKey]
        ));
    }

    // ─────────────────────────────────────────────
    // GET /vendor/select/{rfq}  — construct key and redirect
    // ─────────────────────────────────────────────
    public function select(Rfq $rfq)
    {
        if ($rfq->purchase_request_id) {
            $key = 'goods_' . $rfq->purchase_request_id;
        } elseif ($rfq->service_request_id) {
            $key = 'service_' . $rfq->service_request_id;
        } else {
            $key = '';
        }

        return redirect()->route('vendors.list', $key ? ['key' => $key] : []);
    }

    // ─────────────────────────────────────────────
    // POST /vendor-selection/store
    // ─────────────────────────────────────────────
    public function storeSelection(Request $request)
    {
        $request->validate([
            'purchase_request_id'      => ['required'],
            'item_type'                => ['required', 'string'],
            'selection_notes'          => ['nullable', 'string'],
            'selections'               => ['required', 'array', 'min:1'],
            'selections.*.vendor_id'   => ['required', 'exists:vendors,id'],
            'selections.*.item_id'     => ['required'],
            'selections.*.unit_price'  => ['required', 'numeric', 'min:0'],
            'selections.*.quantity'    => ['required', 'numeric', 'min:0'],
            'selections.*.notes'       => ['nullable', 'string'],
        ]);

        $isService = ($request->item_type === 'service');

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $isService) {
            if ($isService) {
                $pr     = ServiceRequest::findOrFail($request->purchase_request_id);
                $docNum = $pr->document_number
                    ?? ('SR-' . ($pr->created_at ?? now())->format('Y') . '-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT));
            } else {
                $pr     = PurchaseRequest::findOrFail($request->purchase_request_id);
                $docNum = $pr->document_number;
            }

            $rfq = $pr->rfqs()->first();
            if (!$rfq) {
                $todayCount = Rfq::whereDate('created_at', today())->count() + 1;
                $rfq = Rfq::create([
                    'purchase_request_id' => $isService ? null : $pr->id,
                    'service_request_id'  => $isService ? $pr->id : null,
                    'rfq_number'          => 'RFQ-' . now()->format('Y-md') . '-' . str_pad($todayCount, 3, '0', STR_PAD_LEFT),
                    'status'              => 'closed',
                    'opened_at'           => now(),
                ]);
            }

            $byVendor = collect($request->selections)->groupBy('vendor_id');

            foreach ($byVendor as $vendorId => $items) {
                $vendor = Vendor::find($vendorId);

                $quotation = \App\Models\Quotation::where('rfq_id', $rfq->id)
                    ->where('vendor_id', $vendorId)
                    ->first();

                $sel = VendorSelection::updateOrCreate(
                    ['rfq_id' => $rfq->id, 'vendor_id' => $vendorId],
                    [
                        'quotation_id'   => $quotation ? $quotation->id : null,
                        'decision_notes' => $request->selection_notes ?? '',
                        'decided_at'     => now(),
                    ]
                );

                $sel->selectionItems()->delete();

                foreach ($items as $row) {
                    $qd = QuotationDetail::whereHas('quotation', function ($q) use ($rfq, $vendorId) {
                        $q->where('rfq_id', $rfq->id)->where('vendor_id', $vendorId);
                    })->where(
                        $isService ? 'service_request_item_id' : 'purchase_request_item_id',
                        $row['item_id']
                    )->first();

                    $qsId = null;
                    if ($qd) {
                        $qs   = QuotationSummary::where('quotation_detail_id', $qd->id)->first();
                        $qsId = $qs ? $qs->id : null;
                    }

                    SelectionItem::create([
                        'vendor_selection_id'       => $sel->id,
                        'quotation_summary_id'      => $qsId,
                        'final_price_per_item'      => $row['unit_price'],
                        'final_quantity'            => $row['quantity'],
                        'notes'                     => $row['notes'] ?? 'Selected',
                        'purchase_request_item_id'  => $isService ? null : $row['item_id'],
                        'service_request_item_id'   => $isService ? $row['item_id'] : null,
                    ]);
                }

                History::create([
                    'user_id'             => auth()->id(),
                    'vendor_id'           => $vendorId,
                    'rfq_id'              => $rfq->id,
                    'vendor_selection_id' => $sel->id,
                    'action'              => 'Vendor Selection Submitted',
                    'transaction_status'  => 'completed',
                    'notes'               => 'Vendor ' . ($vendor ? $vendor->vendor_name : 'Unknown') . ' dipilih untuk '
                                            . count($items) . ' item pada dokumen ' . $docNum,
                    'action_date'         => now(),
                ]);
            }

            $pr->update(['status' => 'completed']);

            return response()->json([
                'success'   => true,
                'message'   => 'Vendor selection submitted for ' . $docNum,
                'pr_number' => $docNum,
                'notes'     => $request->selection_notes ?? '—',
            ]);
        });
    }

    // ─────────────────────────────────────────────
    // GET /api/vendors
    // ─────────────────────────────────────────────
    public function apiList()
    {
        return response()->json(Vendor::all());
    }
}