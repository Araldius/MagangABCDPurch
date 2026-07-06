<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $showArchived = $request->has('archived');
        
        $query = Item::query();
        
        // Let frontend JS handle archive filtering and search
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('specification', 'like', "%{$search}%");
            });
        }
        
        $items = $query->orderBy('item_name')->get();
        
        $lastItem = Item::where('item_code', 'like', 'ITM-%')->orderBy('item_code', 'desc')->first();
        $nextId = 'ITM-0001';
        if ($lastItem) {
            $lastNum = intval(substr($lastItem->item_code, 4));
            $nextId = 'ITM-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        }
        
        return view('master.items', compact('items', 'search', 'showArchived', 'nextId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_code' => 'nullable|string|max:255',
            'item_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'specification' => 'nullable|string',
            'item_notes' => 'nullable|string',
        ]);

        $data = $request->all();
        if (empty($data['item_code'])) {
            $lastItem = Item::where('item_code', 'like', 'ITM-%')->orderBy('item_code', 'desc')->first();
            $nextId = 'ITM-0001';
            if ($lastItem) {
                $lastNum = intval(substr($lastItem->item_code, 4));
                $nextId = 'ITM-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            }
            $data['item_code'] = $nextId;
        }

        Item::create($data);

        return back()->with('success', 'Item berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        
        $request->validate([
            'item_code' => 'nullable|string|max:255',
            'item_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'specification' => 'nullable|string',
            'item_notes' => 'nullable|string',
        ]);

        $item->update($request->all());

        return back()->with('success', 'Item berhasil diubah.');
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        
        $isUsedInPR = \App\Models\PurchaseRequestItem::where('item_id', $item->item_code)->exists();
        if ($isUsedInPR) {
            return back()->with('error', 'Item tidak bisa dihapus karena sedang digunakan dalam Purchase Request.');
        }

        $item->delete();
        return back()->with('success', 'Item berhasil dihapus.');
    }

    public function archive($id)
    {
        $item = Item::findOrFail($id);
        $item->is_archived = !$item->is_archived;
        $item->save();

        $action = $item->is_archived ? 'diarsipkan' : 'diaktifkan kembali';
        return back()->with('success', "Item berhasil {$action}.");
    }

    public function export(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status', 'active');
        
        $query = Item::query();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('specification', 'like', "%{$search}%");
            });
        }
        
        if ($status === 'active') {
            $query->where('is_archived', false);
        } elseif ($status === 'archived') {
            $query->where('is_archived', true);
        }
        
        $sortColIndex = $request->get('sort_col');
        $sortDir = $request->get('sort_dir', 'asc');
        
        if ($sortColIndex !== null) {
            $cols = [
                0 => 'item_code',
                1 => 'item_name',
                2 => 'unit',
                3 => 'is_archived',
            ];
            if (isset($cols[$sortColIndex])) {
                $query->orderBy($cols[$sortColIndex], $sortDir);
            } else {
                $query->orderBy('item_name', 'asc');
            }
        } else {
            $query->orderBy('item_name', 'asc');
        }
        
        $items = $query->get();
        $xlsFileName = 'master_items_' . date('Ymd_His') . '.xlsx';
        
        $data = [
            ['Item Code', 'Item Name', 'Unit', 'Specification', 'Notes', 'Status']
        ];
        
        foreach ($items as $item) {
            $data[] = [
                $item->item_code,
                $item->item_name,
                $item->unit,
                $item->specification,
                $item->item_notes,
                $item->is_archived ? 'Archived' : 'Active'
            ];
        }
        
        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);
        
        return response((string) $xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $xlsFileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function show($id)
    {
        $item = Item::findOrFail($id);
        
        // Fetch purchase history for this item
        $prQuery = \App\Models\PurchaseRequest::with([
            'items',
            'rfqs.vendorSelections.vendor',
            'rfqs.vendorSelections.selectionItems'
        ])->where('status', 'completed')->get();
        
        $srQuery = \App\Models\ServiceRequest::with([
            'jobs.items',
            'rfqs.vendorSelections.vendor',
            'rfqs.vendorSelections.selectionItems'
        ])->where('status', 'completed')->get();

        $history = [];
        $totalValue = 0;
        
        foreach ([$prQuery, $srQuery] as $prs) {
            foreach ($prs as $pr) {
                foreach ($pr->rfqs as $rfq) {
                    foreach ($rfq->vendorSelections as $sel) {
                        $vendor = $sel->vendor;
                        foreach ($sel->selectionItems as $si) {
                            $pri = null;
                            if (isset($pr->jobs)) { // Service
                                foreach ($pr->jobs as $job) {
                                    $found = collect($job->items)->firstWhere('id', $si->service_request_item_id);
                                    if ($found) { $pri = $found; break; }
                                }
                            } else { // Goods
                                $pri = collect($pr->items)->firstWhere('id', $si->purchase_request_item_id);
                            }
                            
                            if (!$pri) continue;
                            
                            $itemId = $pri->item_code ?? $pri->item_id ?? null;
                            if ($itemId === $item->item_code || ($pri->item_name ?? $pri->name) === $item->item_name) {
                                $value = $si->final_price_per_item * $si->final_quantity;
                                $totalValue += $value;
                                
                                // Fetch quotation details to get offered unit, spec, and notes
                                $qd = \App\Models\QuotationDetail::whereHas('quotation', function($q) use ($rfq, $vendor) {
                                    $q->where('rfq_id', $rfq->id)->where('vendor_id', $vendor->id);
                                })->where(function($q) use ($si) {
                                    if ($si->purchase_request_item_id) {
                                        $q->where('purchase_request_item_id', $si->purchase_request_item_id);
                                    } elseif ($si->service_request_item_id) {
                                        $q->where('service_request_item_id', $si->service_request_item_id);
                                    }
                                })->first();
                                
                                $history[] = [
                                    'doc_no' => $pr->document_number,
                                    'date' => $sel->decided_at ? \Carbon\Carbon::parse($sel->decided_at)->format('Y-m-d') : '-',
                                    'vendor_name' => optional($vendor)->name ?? optional($vendor)->vendor_name ?? '-',
                                    'qty' => $si->final_quantity,
                                    'unit' => $qd->offered_unit ?? $pri->unit ?? '-',
                                    'spec' => $qd->offered_specification ?? $pri->specification ?? '-',
                                    'notes' => $qd->item_notes ?? $si->notes ?? '-',
                                    'price' => $si->final_price_per_item,
                                    'subtotal' => $value,
                                    'req_by' => $pr->user->name ?? '-'
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        usort($history, fn($a, $b) => strcmp($b['date'], $a['date']));

        return view('master.item_detail', compact('item', 'history', 'totalValue'));
    }

    public function exportHistory($id)
    {
        $item = Item::findOrFail($id);
        
        $prQuery = \App\Models\PurchaseRequest::with([
            'items',
            'rfqs.vendorSelections.vendor',
            'rfqs.vendorSelections.selectionItems'
        ])->where('status', 'completed')->get();
        
        $srQuery = \App\Models\ServiceRequest::with([
            'jobs.items',
            'rfqs.vendorSelections.vendor',
            'rfqs.vendorSelections.selectionItems'
        ])->where('status', 'completed')->get();

        $history = [];
        
        foreach ([$prQuery, $srQuery] as $prs) {
            foreach ($prs as $pr) {
                foreach ($pr->rfqs as $rfq) {
                    foreach ($rfq->vendorSelections as $sel) {
                        $vendor = $sel->vendor;
                        foreach ($sel->selectionItems as $si) {
                            $pri = null;
                            if (isset($pr->jobs)) {
                                foreach ($pr->jobs as $job) {
                                    $found = collect($job->items)->firstWhere('id', $si->service_request_item_id);
                                    if ($found) { $pri = $found; break; }
                                }
                            } else {
                                $pri = collect($pr->items)->firstWhere('id', $si->purchase_request_item_id);
                            }
                            
                            if (!$pri) continue;
                            
                            $itemId = $pri->item_code ?? $pri->item_id ?? null;
                            if ($itemId === $item->item_code || ($pri->item_name ?? $pri->name) === $item->item_name) {
                                $qd = \App\Models\QuotationDetail::whereHas('quotation', function($q) use ($rfq, $vendor) {
                                    $q->where('rfq_id', $rfq->id)->where('vendor_id', $vendor->id);
                                })->where(function($q) use ($si) {
                                    if ($si->purchase_request_item_id) {
                                        $q->where('purchase_request_item_id', $si->purchase_request_item_id);
                                    } elseif ($si->service_request_item_id) {
                                        $q->where('service_request_item_id', $si->service_request_item_id);
                                    }
                                })->first();
                                
                                $history[] = [
                                    'doc_no' => $pr->document_number,
                                    'date' => $sel->decided_at ? \Carbon\Carbon::parse($sel->decided_at)->format('Y-m-d') : '-',
                                    'vendor_name' => optional($vendor)->name ?? optional($vendor)->vendor_name ?? '-',
                                    'qty' => $si->final_quantity,
                                    'unit' => $qd->offered_unit ?? $pri->unit ?? '-',
                                    'spec' => $qd->offered_specification ?? $pri->specification ?? '-',
                                    'notes' => $qd->item_notes ?? $si->notes ?? '-',
                                    'price' => $si->final_price_per_item,
                                    'subtotal' => $si->final_price_per_item * $si->final_quantity,
                                    'req_by' => $pr->user->name ?? '-'
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        usort($history, fn($a, $b) => strcmp($b['date'], $a['date']));

        $xlsFileName = 'History_Purchase_' . $item->item_code . '_' . date('Ymd_His') . '.xlsx';
        
        $data = [
            ['DATE', 'DOC NO.', 'VENDOR', 'REQUESTED BY', 'SPEC', 'NOTES', 'QTY', 'UNIT', 'PRICE', 'SUBTOTAL']
        ];
        
        foreach ($history as $h) {
            $data[] = [
                $h['date'],
                $h['doc_no'],
                $h['vendor_name'],
                $h['req_by'],
                $h['spec'],
                $h['notes'],
                $h['qty'],
                $h['unit'],
                $h['price'],
                $h['subtotal']
            ];
        }
        
        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);
        
        // Add some basic styling
        $xlsx->setDefaultFont('Calibri', 11);
        
        return response((string) $xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $xlsFileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
