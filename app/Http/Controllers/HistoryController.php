<?php
 
namespace App\Http\Controllers;
 
use App\Models\PurchaseRequest;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    private function getBaseCompletedPRs()
    {
        $user = Auth::user();
        $startDate = request('start_date');
        $endDate = request('end_date');
        
        $prQuery = PurchaseRequest::with([
            'items',
            'rfqs.vendorSelections.vendor',
            'rfqs.vendorSelections.selectionItems'
        ])->where('status', 'completed');

        $srQuery = ServiceRequest::with([
            'jobs.items',
            'rfqs.vendorSelections.vendor',
            'rfqs.vendorSelections.selectionItems'
        ])->where('status', 'completed');

        if ($startDate) {
            $prQuery->whereDate('updated_at', '>=', $startDate);
            $srQuery->whereDate('updated_at', '>=', $startDate);
        }
        if ($endDate) {
            $prQuery->whereDate('updated_at', '<=', $endDate);
            $srQuery->whereDate('updated_at', '<=', $endDate);
        }

        $prQuery = $prQuery->latest();
        $srQuery = $srQuery->latest();

        if ($user->role !== 'purchasing') {
            $prQuery->where('user_id', $user->id);
            $srQuery->where('user_id', $user->id);
        }

        $prs = $prQuery->get();
        $srs = $srQuery->get();

        $srs->each(function ($sr) {
            $sr->type = 'service';
        });

        return $prs->concat($srs)->sortByDesc('created_at')->values();
    }

    public function orders()
    {
        $prs = $this->getBaseCompletedPRs();
        $recordMap = [];

        foreach ($prs as $pr) {
            $isPR = $pr instanceof PurchaseRequest;
            $docNo = $pr->document_number;

            if (!isset($recordMap[$docNo])) {
                $recordMap[$docNo] = (object) [
                    'doc_number'     => $docNo,
                    'vendor_names'   => collect(),
                    'department'     => $pr->department ?? optional($pr->user)->department ?? '—',
                    'plant'          => $pr->plant ?? '—',
                    'items'          => collect(),
                    'total_value'    => 0,
                    'lead_days'      => null,
                    'status'         => $pr->status,
                    'decided_at'     => null,
                    'completed_date' => $pr->updated_at ? $pr->updated_at->format('d M Y') : '-',
                    'completed_date_raw' => $pr->updated_at ? $pr->updated_at->format('Y-m-d') : '',
                ];
            }
            $rec = $recordMap[$docNo];

            foreach ($pr->rfqs as $rfq) {
                foreach ($rfq->vendorSelections as $sel) {
                    $vendor = $sel->vendor;
                    $vName = optional($vendor)->name ?? optional($vendor)->vendor_name ?? '—';
                    if ($vName !== '—') $rec->vendor_names->push($vName);

                    $selTotal = $sel->selectionItems->sum(fn($si) => ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0));
                    $rec->total_value += $selTotal;

                    $leadDays = $sel->decided_at ? (int) abs(\Carbon\Carbon::parse($pr->created_at)->diffInDays($sel->decided_at)) : null;
                    if ($leadDays !== null && ($rec->lead_days === null || $leadDays > $rec->lead_days)) {
                        $rec->lead_days = $leadDays;
                    }
                    if ($sel->decided_at && (!$rec->decided_at || $sel->decided_at > $rec->decided_at)) {
                        $rec->decided_at = $sel->decided_at;
                    }

                    $selectionItems = $sel->selectionItems->keyBy(function($si) use ($isPR) {
                        return $isPR ? $si->purchase_request_item_id : $si->service_request_item_id;
                    });

                    if ($isPR) {
                        foreach(optional($pr)->items ?? [] as $item) {
                            $si = $selectionItems->get($item->id);
                            if (!$si) continue;
                            $qd = \App\Models\QuotationDetail::whereHas('quotation', function($q) use ($rfq, $vendor) {
                                $q->where('rfq_id', $rfq->id)->where('vendor_id', $vendor->id);
                            })->where('purchase_request_item_id', $item->id)->first();
                            
                            $rec->items->push((object)[
                                'item_id' => $item->item_id ?? $item->item_code,
                                'name' => $item->full_name ?? $item->item_name,
                                'description' => $item->description,
                                'specification' => $qd->offered_specification ?? $item->specification,
                                'quantity' => $si->final_quantity,
                                'unit' => $qd->offered_unit ?? $item->unit,
                                'notes' => $qd->item_notes ?? $si->notes,
                                'final_price_per_item' => $si->final_price_per_item,
                                'vendor_name' => $vName,
                            ]);
                        }
                    } else {
                        foreach(optional($pr)->jobs ?? [] as $job) {
                            foreach(optional($job)->items ?? [] as $item) {
                                $si = $selectionItems->get($item->id);
                                if (!$si) continue;
                                $qd = \App\Models\QuotationDetail::whereHas('quotation', function($q) use ($rfq, $vendor) {
                                    $q->where('rfq_id', $rfq->id)->where('vendor_id', $vendor->id);
                                })->where('service_request_item_id', $item->id)->first();

                                $rec->items->push((object)[
                                    'item_id' => $item->item_id ?? $item->item_code ?? '-',
                                    'name' => $item->full_name ?? $item->item_name,
                                    'description' => $job->job_description,
                                    'specification' => $qd->offered_specification ?? $item->specification,
                                    'quantity' => $si->final_quantity,
                                    'unit' => $qd->offered_unit ?? $item->unit,
                                    'notes' => $qd->item_notes ?? $si->notes,
                                    'final_price_per_item' => $si->final_price_per_item,
                                    'vendor_name' => $vName,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Finalize vendor_name as comma-separated string
        $records = collect(array_values($recordMap));
        $records->each(function($r) {
            $r->vendor_name = $r->vendor_names->unique()->implode(', ') ?: '—';
            unset($r->vendor_names);
        });

        $vendorsUsed  = $records->pluck('vendor_name')->reject(fn($v) => $v === '—')->flatMap(fn($v) => explode(', ', $v))->unique()->count();
        $totalValue   = $records->sum('total_value');
        $prsCompleted = $prs->count();
        $avgLeadDays  = round($records->filter(fn($r) => $r->lead_days !== null)->avg('lead_days') ?? 0);
        $departments  = $records->pluck('department')->unique()->filter()->values();

        return view('history.orders', compact(
            'records', 'vendorsUsed', 'totalValue',
            'prsCompleted', 'avgLeadDays', 'departments'
        ));
    }

    public function items()
    {
        $prs = $this->getBaseCompletedPRs();
        $itemMap = [];

        foreach ($prs as $pr) {
            foreach ($pr->rfqs as $rfq) {
                foreach ($rfq->vendorSelections as $sel) {
                    $vendor = $sel->vendor;
                    $vName = optional($vendor)->name ?? optional($vendor)->vendor_name ?? '-';
                    $leadDays = $sel->decided_at ? (int) abs(\Carbon\Carbon::parse($pr->created_at)->diffInDays($sel->decided_at)) : null;
                    
                    foreach ($sel->selectionItems as $si) {
                        $pri = null;
                        if ($pr->type === 'service' || method_exists($pr, 'jobs')) {
                            foreach ($pr->jobs ?? [] as $job) {
                                $found = collect($job->items)->firstWhere('id', $si->service_request_item_id);
                                if ($found) { $pri = $found; break; }
                            }
                        } else {
                            $pri = collect($pr->items)->firstWhere('id', $si->purchase_request_item_id);
                        }

                        if (!$pri) continue;
                        $itemId = $pri->item_id ?? $pri->item_code ?? '-';

                        if (!isset($itemMap[$itemId])) {
                            $itemMap[$itemId] = [
                                'item_id' => $itemId,
                                'item_name' => $pri->full_name ?? $pri->item_name ?? $pri->name ?? '-',
                                'last_purchase' => null,
                                'last_value' => 0,
                                'pr_count' => 0,
                                'sr_count' => 0,
                                'history' => []
                            ];
                        }

                        if ($pr->type === 'service' || method_exists($pr, 'jobs')) {
                            $itemMap[$itemId]['sr_count']++;
                        } else {
                            $itemMap[$itemId]['pr_count']++;
                        }

                        $dateStr = $sel->decided_at ? \Carbon\Carbon::parse($sel->decided_at)->format('Y-m-d') : '';
                        if (!$itemMap[$itemId]['last_purchase'] || $dateStr > $itemMap[$itemId]['last_purchase']) {
                            $itemMap[$itemId]['last_purchase'] = $dateStr;
                            $itemMap[$itemId]['last_value'] = $si->final_price_per_item * $si->final_quantity;
                        }

                        $qd = \App\Models\QuotationDetail::whereHas('quotation', function($q) use ($rfq, $vendor) {
                            $q->where('rfq_id', $rfq->id)->where('vendor_id', $vendor->id);
                        })->where(function($q) use ($si) {
                            if ($si->purchase_request_item_id) {
                                $q->where('purchase_request_item_id', $si->purchase_request_item_id);
                            } elseif ($si->service_request_item_id) {
                                $q->where('service_request_item_id', $si->service_request_item_id);
                            }
                        })->first();

                        $itemMap[$itemId]['history'][] = [
                            'item_name' => $pri->full_name ?? $pri->item_name ?? $pri->name ?? '-',
                            'vendor' => $vName,
                            'vendor_city' => optional($vendor)->location ?? '',
                            'plant' => $pr->plant ?? '-',
                            'value' => $si->final_price_per_item * $si->final_quantity,
                            'qty' => $si->final_quantity,
                            'unit' => $qd->offered_unit ?? $pri->unit ?? '-',
                            'spec' => $qd->offered_specification ?? $pri->specification ?? '-',
                            'notes' => $qd->item_notes ?? $si->notes ?? '-',
                            'requested_by' => $pr->user->name ?? '-',
                            'lead_time' => $leadDays ? $leadDays . ' days' : '-',
                            'req_date' => $pr->requested_date ? \Carbon\Carbon::parse($pr->requested_date)->format('Y-m-d') : '-',
                            'doc_no' => $pr->document_number,
                            'pr_id' => $pr->id,
                            'type' => $pr->type ?? 'goods'
                        ];
                    }
                }
            }
        }

        $items = collect(array_values($itemMap))->map(function($item) {
            $item['category'] = $item['pr_count'] >= $item['sr_count'] ? 'Goods' : 'Service';
            return $item;
        })->sortByDesc('last_purchase')->values();

        $vendorsUsed = 0; $totalValue = 0;
        foreach ($prs as $p) {
            foreach ($p->rfqs as $r) {
                foreach ($r->vendorSelections as $s) {
                    $totalValue += $s->selectionItems->sum(fn($si) => ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0));
                }
            }
        }
        $vendorsUsed = collect($prs)->flatMap->rfqs->flatMap->vendorSelections->pluck('vendor_id')->unique()->count();
        $prsCompleted = $prs->count();
        $avgLeadDays = 0;
        $lDays = collect();
        foreach ($prs as $p) {
            foreach ($p->rfqs as $r) {
                foreach ($r->vendorSelections as $s) {
                    if ($s->decided_at) {
                        $lDays->push(abs(\Carbon\Carbon::parse($p->created_at)->diffInDays($s->decided_at)));
                    }
                }
            }
        }
        if ($lDays->count() > 0) $avgLeadDays = round($lDays->avg());

        return view('history.items', compact('items', 'vendorsUsed', 'totalValue', 'prsCompleted', 'avgLeadDays'));
    }

    public function vendors()
    {
        $prs = $this->getBaseCompletedPRs();
        $vendorMap = [];
        $unselectedVendorMap = [];

        foreach ($prs as $pr) {
            foreach ($pr->rfqs as $rfq) {
                // Collect selected vendors
                $selectedVendorIds = [];
                foreach ($rfq->vendorSelections as $sel) {
                    $vendor = $sel->vendor;
                    if (!$vendor) continue;
                    $vid = $vendor->id;
                    $vName = $vendor->name ?? $vendor->vendor_name ?? '-';
                    if ($vName === '-' || $vName === '-') continue;

                    $selectedVendorIds[] = $vid;

                    if (!isset($vendorMap[$vid])) {
                        $vendorMap[$vid] = [
                            'vendor_id' => $vid,
                            'vendor_name' => $vName,
                            'vendor_city' => $vendor->location ?? '',
                            'last_purchase' => null,
                            'total_value' => 0,
                            'history' => []
                        ];
                    }

                    $dateStr = $sel->decided_at ? \Carbon\Carbon::parse($sel->decided_at)->format('Y-m-d') : '';
                    if (!$vendorMap[$vid]['last_purchase'] || $dateStr > $vendorMap[$vid]['last_purchase']) {
                        $vendorMap[$vid]['last_purchase'] = $dateStr;
                    }

                    $subtotal = 0;
                    foreach ($sel->selectionItems as $si) {
                        $val = $si->final_price_per_item * $si->final_quantity;
                        $subtotal += $val;
                        $pri = null;
                        if ($pr->type === 'service' || method_exists($pr, 'jobs')) {
                            foreach ($pr->jobs ?? [] as $job) {
                                $found = collect($job->items)->firstWhere('id', $si->service_request_item_id);
                                if ($found) { $pri = $found; break; }
                            }
                        } else {
                            $pri = collect($pr->items)->firstWhere('id', $si->purchase_request_item_id);
                        }

                        $leadDays = $sel->decided_at ? (int) abs(\Carbon\Carbon::parse($pr->created_at)->diffInDays($sel->decided_at)) : null;

                        $vendorMap[$vid]['history'][] = [
                            'item_id' => $pri->item_id ?? $pri->item_code ?? '-',
                            'item_name' => $pri->full_name ?? $pri->item_name ?? $pri->name ?? '-',
                            'plant' => $pr->plant ?? '-',
                            'value' => $val,
                            'qty' => $si->final_quantity,
                            'unit' => $pri->unit ?? '-',
                            'specification' => $pri->specification ?? '-',
                            'requested_by' => $pr->user->name ?? '-',
                            'lead_time' => $leadDays ? $leadDays . ' days' : '-',
                            'req_date' => $pr->requested_date ? \Carbon\Carbon::parse($pr->requested_date)->format('Y-m-d') : '-',
                            'doc_no' => $pr->document_number,
                            'pr_id' => $pr->id,
                            'type' => $pr->type ?? 'goods'
                        ];
                    }
                    $vendorMap[$vid]['total_value'] += $subtotal;
                }
            }
        }

        $vendors = collect(array_values($vendorMap))->sortByDesc('last_purchase')->values();

        $vendorsUsed = count($vendorMap);
        $totalValue = $vendors->sum('total_value');
        $prsCompleted = $prs->count();
        $lDays = collect();
        foreach ($prs as $p) {
            foreach ($p->rfqs as $r) {
                foreach ($r->vendorSelections as $s) {
                    if ($s->decided_at) {
                        $lDays->push(abs(\Carbon\Carbon::parse($p->created_at)->diffInDays($s->decided_at)));
                    }
                }
            }
        }
        $avgLeadDays = $lDays->count() > 0 ? round($lDays->avg()) : 0;

        return view('history.vendors', compact('vendors', 'vendorsUsed', 'totalValue', 'prsCompleted', 'avgLeadDays'));
    }

    public function masterVendors()
    {
        $vendors = \App\Models\Vendor::withCount('quotations')->get();
        $vendorList = [];

        foreach ($vendors as $vendor) {
            $quotations = \App\Models\Quotation::where('vendor_id', $vendor->id)->get();
            $lastSubmitted = $quotations->max('created_at');
            $lastSubmitted = $lastSubmitted ? \Carbon\Carbon::parse($lastSubmitted) : null;
            
            $completedCount = 0;
            foreach ($quotations as $q) {
                if (\App\Models\VendorSelection::where('rfq_id', $q->rfq_id)->where('vendor_id', $vendor->id)->exists()) {
                    $completedCount++;
                }
            }

            $vendorList[] = [
                'vendor_id' => $vendor->id,
                'vendor_name' => $vendor->vendor_name ?? '-',
                'vendor_city' => $vendor->location ?? '-',
                'email' => $vendor->email ?? '',
                'department' => $vendor->department ?? '',
                'last_submitted' => $lastSubmitted ? $lastSubmitted->format('Y-m-d') : '-',
                'quotation_count' => $quotations->count(),
                'completed_count' => $completedCount,
            ];
        }

        $masterVendors = collect($vendorList)->sortBy('vendor_name')->values();
        $locations = $masterVendors->pluck('vendor_city')->filter(fn($c) => $c !== '-')->unique()->sort()->values();

        return view('master.vendors', compact('masterVendors', 'locations'));
    }

    public function vendorDetail($id)
    {
        $vendor = \App\Models\Vendor::findOrFail($id);
        $quotations = \App\Models\Quotation::with([
            'rfq.purchaseRequest.items',
            'rfq.purchaseRequest.rfqs.vendorSelections.vendor',
            'rfq.purchaseRequest.rfqs.vendorSelections.selectionItems',
            'rfq.serviceRequest.jobs.items',
            'rfq.serviceRequest.rfqs.vendorSelections.vendor',
            'rfq.serviceRequest.rfqs.vendorSelections.selectionItems',
            'details.purchaseRequestItem', 
            'details.serviceRequestItem'
        ])
            ->where('vendor_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $history = [];
        $totalValue = 0;
        
        foreach ($quotations as $q) {
            $pr = $q->rfq->purchaseRequest ?? $q->rfq->serviceRequest;
            if (!$pr) continue;
            
            $isSelected = \App\Models\VendorSelection::where('rfq_id', $q->rfq_id)
                ->where('vendor_id', $id)
                ->exists();
                
            $subtotal = $q->total_price ?? 0;
            if ($isSelected) {
                $totalValue += $subtotal;
            }
            
            $itemsList = $q->details->map(function($d) {
                $originalItem = $d->purchaseRequestItem ?? $d->serviceRequestItem;
                return [
                    'name' => $originalItem ? ($originalItem->full_name ?? $originalItem->item_name) : '-',
                    'qty' => $d->offered_quantity,
                    'unit' => $d->offered_unit ?? ($originalItem ? $originalItem->unit : '-'),
                    'price' => $d->offered_price_per_item,
                    'subtotal' => ($d->offered_quantity ?? 0) * ($d->offered_price_per_item ?? 0),
                ];
            })->toArray();

            // Compute PR full data for modal
            $isPR = $pr instanceof \App\Models\PurchaseRequest;
            $winnerNames = [];
            $winnerTotal = 0;
            $winnerDecidedAt = null;
            $prItemsData = [];
            
            foreach ($pr->rfqs ?? [] as $prRfq) {
                foreach ($prRfq->vendorSelections ?? [] as $sel) {
                    $vName = optional($sel->vendor)->name ?? optional($sel->vendor)->vendor_name ?? '—';
                    if ($vName !== '—') $winnerNames[] = $vName;

                    $selTotal = $sel->selectionItems->sum(fn($si) => ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0));
                    $winnerTotal += $selTotal;

                    if ($sel->decided_at && (!$winnerDecidedAt || $sel->decided_at > $winnerDecidedAt)) {
                        $winnerDecidedAt = $sel->decided_at;
                    }

                    $selectionItems = $sel->selectionItems->keyBy(function($si) use ($isPR) {
                        return $isPR ? $si->purchase_request_item_id : $si->service_request_item_id;
                    });

                    if ($isPR) {
                        foreach(optional($pr)->items ?? [] as $item) {
                            $si = $selectionItems->get($item->id);
                            if (!$si) continue;
                            $prItemsData[] = [
                                'item_id' => $item->item_id ?? $item->item_code,
                                'name' => $item->full_name ?? $item->item_name,
                                'description' => $item->description,
                                'specification' => $item->specification,
                                'quantity' => $si->final_quantity,
                                'unit' => $item->unit,
                                'final_price_per_item' => $si->final_price_per_item,
                                'vendor_name' => $vName,
                            ];
                        }
                    } else {
                        foreach(optional($pr)->jobs ?? [] as $job) {
                            foreach(optional($job)->items ?? [] as $item) {
                                $si = $selectionItems->get($item->id);
                                if (!$si) continue;
                                $prItemsData[] = [
                                    'item_id' => $item->item_id ?? $item->item_code ?? '-',
                                    'name' => $item->full_name ?? $item->item_name,
                                    'description' => $job->job_description,
                                    'specification' => $item->specification,
                                    'quantity' => $si->final_quantity,
                                    'unit' => $item->unit,
                                    'final_price_per_item' => $si->final_price_per_item,
                                    'vendor_name' => $vName,
                                ];
                            }
                        }
                    }
                }
            }

            $history[] = [
                'doc_no' => $pr->document_number ?? '-',
                'req_date' => $pr->requested_date ? \Carbon\Carbon::parse($pr->requested_date)->format('Y-m-d') : '-',
                'submitted_at' => $q->created_at ? $q->created_at->format('Y-m-d H:i') : '-',
                'items_count' => $q->details->count(),
                'value' => $subtotal,
                'is_selected' => $isSelected,
                'status' => $isSelected ? 'Selected' : 'Not Selected',
                'pr_id' => $pr->id,
                'type' => $pr->type ?? 'goods',
                'items' => $itemsList,
                'pr_data' => [
                    'doc_number' => $pr->document_number ?? '-',
                    'department' => $pr->department ?? optional($pr->user)->department ?? '—',
                    'vendor_name' => collect($winnerNames)->unique()->implode(', ') ?: '—',
                    'total_value' => $winnerTotal,
                    'decided_at' => $winnerDecidedAt,
                    'items' => $prItemsData,
                    'status' => $pr->status,
                ]
            ];
        }

        return view('master.vendor_detail', compact('vendor', 'history', 'totalValue'));
    }
}