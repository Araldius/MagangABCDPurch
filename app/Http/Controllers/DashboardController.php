<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\ServiceRequest;
use App\Models\VendorSelection;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return $user->role === 'purchasing'
            ? $this->purchasingDashboard()
            : $this->userDashboard();
    }

    private function userDashboard()
    {
        $userId = Auth::id();

        $prs = PurchaseRequest::with([
            'items', 'user',
            'rfqs.vendorSelections.vendor',
            'rfqs.vendorSelections.selectionItems',
            'rfqs.histories.user',
        ])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('user', fn($u) => $u->where('role', 'purchasing'));
            })
            ->latest()
            ->get()
            ->map(function ($req) {
                $req->type          = 'goods';
                $req->display_doc   = $req->document_number;
                $req->display_title = $req->title;
                $req->item_count    = $req->items->count();
                return $req;
            });

        $srs = ServiceRequest::with(['jobs.items', 'user'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('user', fn($u) => $u->where('role', 'purchasing'));
            })
            ->latest()
            ->get()
            ->map(function ($req) {
                $req->type          = 'service';
                $req->display_doc   = $req->document_number ?? ('SR-' . now()->format('Y') . '-' . str_pad($req->id, 4, '0', STR_PAD_LEFT));
                $req->display_title = $req->service_name;
                $itemCount = 0;
                foreach ($req->jobs as $job) { $itemCount += $job->items->count(); }
                $req->item_count = $itemCount;
                return $req;
            });

        $requests = $prs->concat($srs)->sortByDesc('created_at')->values();

        $activePrs        = $requests->whereNotIn('status', ['completed', 'rejected', 'cancelled'])->count();
        $awaitingApproval = $requests->where('status', 'submitted')->count();
        $inProcess        = $requests->whereIn('status', ['vendor_search', 'vendor_selection'])->count();
        $completedMonth   = $requests->where('status', 'completed')
            ->filter(fn($r) => $r->updated_at->month === now()->month
                            && $r->updated_at->year  === now()->year)
            ->count();

        $recentHistory = VendorSelection::with(['vendor', 'rfq.purchaseRequest', 'selectionItems'])
            ->whereHas('rfq.purchaseRequest', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('status', 'completed');
            })
            ->latest('decided_at')
            ->limit(5)
            ->get();

        return view('dashboard.user', compact(
            'requests', 'activePrs', 'awaitingApproval', 'inProcess',
            'completedMonth', 'recentHistory'
        ));
    }

    private function purchasingDashboard()
    {
        return view('dashboard.admin');
    }

    private function getFilteredRequests(\Illuminate\Http\Request $request)
    {
        $timeMode = $request->query('time_mode', 'all'); // all, year, month, day
        $timeVal = $request->query('time_value');

        $prQuery = PurchaseRequest::with(['items', 'rfqs.vendorSelections.selectionItems', 'rfqs.vendorSelections.vendor']);
        $srQuery = ServiceRequest::with(['jobs.items', 'rfqs.vendorSelections.selectionItems', 'rfqs.vendorSelections.vendor']);

        if ($timeMode === 'year' && $timeVal) {
            $prQuery->whereYear('created_at', $timeVal);
            $srQuery->whereYear('created_at', $timeVal);
        } elseif ($timeMode === 'month' && $timeVal) {
            $parts = explode('-', $timeVal);
            if (count($parts) == 2) {
                $prQuery->whereYear('created_at', $parts[0])->whereMonth('created_at', $parts[1]);
                $srQuery->whereYear('created_at', $parts[0])->whereMonth('created_at', $parts[1]);
            }
        } elseif ($timeMode === 'day' && $timeVal) {
            $prQuery->whereDate('created_at', $timeVal);
            $srQuery->whereDate('created_at', $timeVal);
        }

        return ['prs' => $prQuery->get(), 'srs' => $srQuery->get()];
    }

    public function adminStats(\Illuminate\Http\Request $request)
    {
        $data = $this->getFilteredRequests($request);
        $allRequests = $data['prs']->concat($data['srs']);

        $statusTrend = [];
        $monthlySpend = [];
        $vendorSpend = [];
        $deptSpend = [];
        $orderRecords = [];
        $itemCatalog = [];
        $plantSpend = [];

        // Always show 12 months for trend if year or all is selected
        for ($i=1; $i<=12; $i++) {
            $label = date('M', mktime(0, 0, 0, $i, 1));
            $monthlySpend[$label] = 0;
            $statusTrend[$label] = ['Pending' => 0, 'Completed' => 0, 'Overdue' => 0];
        }

        foreach ($allRequests as $req) {
            $isCompleted = ($req->status === 'completed' || $req->status === 'approved');
            $dept = $req->department ?? 'Unknown';
            $p = $req->plant ?? 'Unknown';
            $monthLabel = \Carbon\Carbon::parse($req->created_at)->format('M');
            
            if (!isset($orderRecords[$p])) $orderRecords[$p] = 0;
            $orderRecords[$p]++;

            $needDate = $req->need_date ?? $req->requested_date;
            $isOverdue = (!$isCompleted && $needDate && \Carbon\Carbon::parse($needDate)->endOfDay()->isPast());
            
            if ($isCompleted) {
                $statusTrend[$monthLabel]['Completed']++;
            } elseif ($isOverdue) {
                $statusTrend[$monthLabel]['Overdue']++;
            } else {
                $statusTrend[$monthLabel]['Pending']++;
            }

            if ($isCompleted && $req->rfqs) {
                $reqSpend = 0;
                foreach ($req->rfqs as $rfq) {
                    foreach ($rfq->vendorSelections as $vs) {
                        $vendor = $vs->vendor;
                        $vName = $vendor ? ($vendor->vendor_name ?? $vendor->name) : 'Unknown';
                        
                        if (!isset($vendorSpend[$vName])) $vendorSpend[$vName] = 0;

                        foreach ($vs->selectionItems as $si) {
                            $val = ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0);
                            $reqSpend += $val;
                            $vendorSpend[$vName] += $val;

                            $itemId = $si->purchase_request_item_id ?? $si->service_request_item_id ?? 'Unknown';
                            $itemName = 'Item #' . $itemId;
                            if (isset($req->items)) {
                                $f = collect($req->items)->firstWhere('id', $itemId);
                                if ($f) $itemName = $f->item_name ?? $f->name ?? $itemName;
                            } elseif (isset($req->jobs)) {
                                foreach($req->jobs as $job) {
                                    $f = collect($job->items)->firstWhere('id', $itemId);
                                    if ($f) { $itemName = $f->item_name ?? $f->name ?? $itemName; break; }
                                }
                            }

                            if (!isset($itemCatalog[$itemName])) $itemCatalog[$itemName] = 0;
                            $itemCatalog[$itemName] += $val;
                        }
                    }
                }
                
                $monthlySpend[$monthLabel] += $reqSpend;
                if (!isset($deptSpend[$dept])) $deptSpend[$dept] = 0;
                $deptSpend[$dept] += $reqSpend;

                if (!isset($plantSpend[$p])) $plantSpend[$p] = 0;
                $plantSpend[$p] += $reqSpend;
            }
        }

        arsort($vendorSpend);
        $vendorSpend = array_slice($vendorSpend, 0, 10, true);

        arsort($deptSpend);
        $deptSpend = array_slice($deptSpend, 0, 10, true);

        arsort($itemCatalog);
        $itemCatalog = array_slice($itemCatalog, 0, 10, true);

        return response()->json([
            'charts' => [
                'status' => [
                    'labels' => array_keys($statusTrend),
                    'datasets' => [
                        ['label' => 'Completed', 'data' => array_column(array_values($statusTrend), 'Completed'), 'borderColor' => '#10b981'],
                        ['label' => 'Pending', 'data' => array_column(array_values($statusTrend), 'Pending'), 'borderColor' => '#3b82f6'],
                        ['label' => 'Overdue', 'data' => array_column(array_values($statusTrend), 'Overdue'), 'borderColor' => '#ef4444'],
                    ]
                ],
                'monthlySpend' => [
                    'labels' => array_keys($monthlySpend),
                    'data' => array_values($monthlySpend)
                ],
                'topVendors' => [
                    'labels' => array_keys($vendorSpend),
                    'data' => array_values($vendorSpend)
                ],
                'deptPerf' => [
                    'labels' => array_keys($deptSpend),
                    'data' => array_values($deptSpend)
                ],
                'orderRecords' => [
                    'labels' => array_keys($orderRecords),
                    'data' => array_values($orderRecords)
                ],
                'itemCatalog' => [
                    'labels' => array_keys($itemCatalog),
                    'data' => array_values($itemCatalog)
                ],
                'plantSpend' => [
                    'labels' => array_keys($plantSpend),
                    'data' => array_values($plantSpend)
                ]
            ],
            'entities' => [
                'topVendors' => array_keys($vendorSpend),
                'deptPerf' => array_keys($deptSpend)
            ]
        ]);
    }

    public function drillDown(\Illuminate\Http\Request $request)
    {
        $type = $request->query('type');
        $label = $request->query('label');
        
        $data = $this->getFilteredRequests($request);
        $allRequests = $data['prs']->concat($data['srs']);
        
        $rows = [];

        foreach ($allRequests as $req) {
            $isCompleted = ($req->status === 'completed' || $req->status === 'approved');
            $dept = $req->department ?? 'Unknown';
            $p = $req->plant ?? 'Unknown';
            $monthLabel = \Carbon\Carbon::parse($req->created_at)->format('M');
            
            $needDate = $req->need_date ?? $req->requested_date;
            $isOverdue = (!$isCompleted && $needDate && \Carbon\Carbon::parse($needDate)->endOfDay()->isPast());
            $currStatus = $isCompleted ? 'Completed' : ($isOverdue ? 'Overdue' : 'Pending');

            $docNo = $req->document_number ?? ('SR-'.$req->id);
            $reqDate = \Carbon\Carbon::parse($req->created_at)->format('Y-m-d');

            // Calculate request spend for modals
            $reqSpend = 0;
            if ($isCompleted && $req->rfqs) {
                foreach ($req->rfqs as $rfq) {
                    foreach ($rfq->vendorSelections as $vs) {
                        foreach ($vs->selectionItems as $si) {
                            $reqSpend += ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0);
                        }
                    }
                }
            }
            $fmtSpend = 'Rp ' . number_format($reqSpend, 0, ',', '.');

            if ($type === 'status') {
                $series = $request->query('series');
                if ($monthLabel === $label && $currStatus === $series) {
                    $rows[] = ['col1' => $docNo, 'col2' => $req->title ?? $req->service_name ?? '-', 'col3' => $dept, 'col4' => $currStatus, 'col5' => $reqDate];
                }
            }
            elseif ($type === 'monthlySpend') {
                if ($monthLabel === $label && $isCompleted) {
                    $rows[] = ['col1' => $docNo, 'col2' => $dept, 'col3' => $p, 'col4' => $fmtSpend, 'col5' => $reqDate];
                }
            }
            elseif ($type === 'topVendors') {
                if ($req->rfqs) {
                    foreach ($req->rfqs as $rfq) {
                        foreach ($rfq->vendorSelections as $vs) {
                            $vendor = $vs->vendor;
                            $vName = $vendor ? ($vendor->vendor_name ?? $vendor->name) : 'Unknown';
                            $vLoc = $vendor ? ($vendor->location ?? 'Unknown') : 'Unknown';
                            
                            if ($vName === $label) {
                                foreach ($vs->selectionItems as $si) {
                                    $val = ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0);
                                    $rows[] = ['col1' => $vName, 'col2' => $vLoc, 'col3' => $docNo, 'col4' => 'Rp ' . number_format($val, 0, ',', '.'), 'col5' => $reqDate];
                                }
                            }
                        }
                    }
                }
            }
            elseif ($type === 'deptPerf') {
                if ($dept === $label && $isCompleted) {
                    $rows[] = ['col1' => $docNo, 'col2' => $req->title ?? $req->service_name ?? '-', 'col3' => $currStatus, 'col4' => $fmtSpend, 'col5' => $reqDate];
                }
            }
            elseif ($type === 'orderRecords') {
                if ($p === $label) {
                    $rows[] = ['col1' => $docNo, 'col2' => $req->title ?? $req->service_name ?? '-', 'col3' => $dept, 'col4' => $currStatus, 'col5' => $reqDate];
                }
            }
            elseif ($type === 'plantSpend') {
                if ($p === $label && $isCompleted) {
                    $rows[] = ['col1' => $docNo, 'col2' => $req->title ?? $req->service_name ?? '-', 'col3' => $dept, 'col4' => $fmtSpend, 'col5' => $reqDate];
                }
            }
            elseif ($type === 'itemCatalog') {
                if ($isCompleted && $req->rfqs) {
                    foreach ($req->rfqs as $rfq) {
                        foreach ($rfq->vendorSelections as $vs) {
                            foreach ($vs->selectionItems as $si) {
                                $itemId = $si->purchase_request_item_id ?? $si->service_request_item_id ?? 'Unknown';
                                $itemName = 'Item #' . $itemId;
                                if (isset($req->items)) {
                                    $f = collect($req->items)->firstWhere('id', $itemId);
                                    if ($f) $itemName = $f->item_name ?? $f->name ?? $itemName;
                                } elseif (isset($req->jobs)) {
                                    foreach($req->jobs as $job) {
                                        $f = collect($job->items)->firstWhere('id', $itemId);
                                        if ($f) { $itemName = $f->item_name ?? $f->name ?? $itemName; break; }
                                    }
                                }
                                
                                if ($itemName === $label) {
                                    $val = ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0);
                                    $vName = $vs->vendor ? ($vs->vendor->vendor_name ?? $vs->vendor->name) : 'Unknown';
                                    $rows[] = ['col1' => $itemName, 'col2' => $vName, 'col3' => $si->final_quantity, 'col4' => 'Rp ' . number_format($val, 0, ',', '.'), 'col5' => $reqDate];
                                }
                            }
                        }
                    }
                }
            }
        }

        return response()->json(['rows' => $rows]);
    }

    public function compare(\Illuminate\Http\Request $request)
    {
        $topic = $request->query('topic');
        $entities = json_decode($request->query('entities'), true) ?? [];

        $data = $this->getFilteredRequests($request);
        $allRequests = $data['prs']->concat($data['srs']);

        $results = [];
        foreach($entities as $e) { $results[$e] = 0; }

        foreach ($allRequests as $req) {
            $isCompleted = ($req->status === 'completed' || $req->status === 'approved');
            $dept = $req->department ?? 'Unknown';

            if ($topic === 'deptPerf' && in_array($dept, $entities)) {
                if ($isCompleted && $req->rfqs) {
                    foreach ($req->rfqs as $rfq) {
                        foreach ($rfq->vendorSelections as $vs) {
                            foreach ($vs->selectionItems as $si) {
                                $results[$dept] += ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0);
                            }
                        }
                    }
                }
            }
            elseif ($topic === 'topVendors' && $isCompleted && $req->rfqs) {
                foreach ($req->rfqs as $rfq) {
                    foreach ($rfq->vendorSelections as $vs) {
                        $vendor = $vs->vendor;
                        $vName = $vendor ? ($vendor->vendor_name ?? $vendor->name) : 'Unknown';
                        if (in_array($vName, $entities)) {
                            foreach ($vs->selectionItems as $si) {
                                $results[$vName] += ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0);
                            }
                        }
                    }
                }
            }
        }

        return response()->json([
            'labels' => array_keys($results),
            'data' => array_values($results)
        ]);
    }
}