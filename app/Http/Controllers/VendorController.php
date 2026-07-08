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

        $prs = PurchaseRequest::with(['items', 'rfqs.quotations.details', 'rfqs.vendorSelections.selectionItems'])
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

        $srs = ServiceRequest::with(['jobs.items', 'rfqs.quotations.details', 'rfqs.vendorSelections.selectionItems'])
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
            'selections.*.unit'        => ['nullable', 'string'],
            'selections.*.specification'=> ['nullable', 'string'],
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
                        'final_unit'                => $row['unit'] ?? null,
                        'final_brand'               => $row['brand'] ?? null,
                        'final_specification'       => $row['specification'] ?? null,
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
            $userId = $pr->user_id ?? null;
            if ($userId) {
                $userCreator = \App\Models\User::find($userId);
                if ($userCreator) {
                    $userCreator->notify(new \App\Notifications\VendorSelectedNotification($pr, $isService ? 'service' : 'goods'));
                }
            }
            
            // Notify all purchasing users as well
            $purchasingUsers = \App\Models\User::where('role', 'purchasing')->get();
            if ($purchasingUsers->count() > 0) {
                \Illuminate\Support\Facades\Notification::send($purchasingUsers, new \App\Notifications\VendorSelectedNotification($pr, $isService ? 'service' : 'goods'));
            }

            return response()->json([
                'success'   => true,
                'message'   => 'Vendor selection submitted for ' . $docNum,
                'pr_number' => $docNum,
                'notes'     => $request->selection_notes ?? '—',
            ]);
        });
    }

    public function updateMaster(Request $request, $id)
    {
        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        $vendor = Vendor::findOrFail($id);
        $vendor->update([
            'vendor_name' => $request->vendor_name,
            'email' => $request->email,
            'department' => $request->department,
        ]);

        return back()->with('success', 'Vendor information updated successfully.');
    }

    public function exportQuotations(Request $request)
    {
        $id = $request->query('id');
        $type = $request->query('type', 'goods');

        $doc = $type === 'service' ? ServiceRequest::findOrFail($id) : PurchaseRequest::findOrFail($id);
        
        // 1. Get all items in the request
        if ($type === 'service') {
            $items = collect();
            foreach ($doc->jobs as $job) {
                $items = $items->merge($job->items);
            }
        } else {
            $items = $doc->items;
        }

        // 2. Get all participating vendors and their quotations
        $rfqs = Rfq::with('quotations.vendor', 'quotations.details')->where($type === 'service' ? 'service_request_id' : 'purchase_request_id', $id)->get();
        $participatingVendors = [];
        $vendorQuotations = []; 
        
        foreach ($rfqs as $rfq) {
            foreach ($rfq->quotations as $quotation) {
                $v = $quotation->vendor;
                $vName = $v->vendor_name ?? $v->name ?? 'Unknown Vendor';
                $participatingVendors[$quotation->id] = $vName;
                $vendorQuotations[$quotation->id] = $quotation;
            }
        }

        // 3. Build headers
        // 3. Build headers
        $headerTop = ['<b>NO.</b>', '<b>ITEM ID</b>', '<b>NAMA BARANG</b>', '<b>SPEC/BRAND</b>', '<b>QTY</b>', '<b>UNIT</b>', '<b>NOTES</b>'];
        $headerBottom = ['', '', '', '', '', '', ''];
        
        foreach ($participatingVendors as $vName) {
            $headerTop[] = '<b>' . $vName . '</b>';
            $headerTop[] = '';
            $headerTop[] = '';
            $headerTop[] = '';
            $headerTop[] = '';
            
            $headerBottom[] = '<b>QTY</b>';
            $headerBottom[] = '<b>UNIT</b>';
            $headerBottom[] = '<b>NOTES</b>';
            $headerBottom[] = '<b>PRICE/ITEM</b>';
            $headerBottom[] = '<b>TOTAL PRICE</b>';
        }
        $data = [$headerTop, $headerBottom];

        // 4. Build rows per item
        $no = 1;
        $vendorTotals = array_fill_keys(array_keys($participatingVendors), 0);

        foreach ($items as $item) {
            $row = [
                $no++,
                $item->item_id ?? '-',
                $item->item_name ?? $item->name ?? '-',
                $type === 'service' ? ($item->specification ?? '-') : ($item->brand ?? '-'),
                $item->quantity ?? '-',
                $item->unit ?? '-',
                $item->item_notes ?? $item->admin_notes ?? '-'
            ];

            foreach ($vendorQuotations as $qId => $quotation) {
                $detail = $quotation->details->first(function($d) use ($item, $type) {
                    if ($type === 'service') {
                        return $d->service_request_item_id == $item->id;
                    }
                    return $d->purchase_request_item_id == $item->id;
                });

                if ($detail) {
                    $qQty = $detail->offered_quantity ?? $item->quantity;
                    $qPrice = $detail->offered_price_per_item ?? 0;
                    $qTotal = $qQty * $qPrice;
                    
                    $row[] = $qQty;
                    $row[] = $detail->offered_unit ?? $item->unit;
                    $row[] = $detail->item_notes ?? '-';
                    $row[] = $qPrice;
                    $row[] = $qTotal;
                    
                    $vendorTotals[$qId] += $qTotal;
                } else {
                    $row[] = '-';
                    $row[] = '-';
                    $row[] = '-';
                    $row[] = '-';
                    $row[] = '-';
                }
            }
            $data[] = $row;
        }
        
        // 5. Build Grand Total row
        $totalRow = ['', '', '', '', '', '', '<b>GRAND TOTAL</b>'];
        foreach ($participatingVendors as $qId => $vName) {
            $totalRow[] = '';
            $totalRow[] = '';
            $totalRow[] = '';
            $totalRow[] = '';
            $totalRow[] = '<b>' . $vendorTotals[$qId] . '</b>';
        }
        $data[] = $totalRow;

        $xlsFileName = "quotations_{$doc->document_number}_" . date('Ymd_His') . '.xlsx';
        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);

        return response((string) $xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $xlsFileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // ─────────────────────────────────────────────
    // GET /api/vendors
    // ─────────────────────────────────────────────
    public function apiList()
    {
        return response()->json(Vendor::all());
    }
}