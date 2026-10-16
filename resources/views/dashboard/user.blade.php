@extends('layouts.app')
@php
    $pageTitle = 'Dashboard';
    $user      = auth()->user();
    $firstName = explode(' ', $user->name)[0];
    $statusCfg = [
        'awaiting_approval' => ['Awaiting Approval', '#fff7ed','#c2410c','#f97316'],
        'in_process'        => ['In Process',        '#f0f9ff','#0369a1','#0ea5e9'],
        'approved'          => ['Approved',           '#f0fdf4','#15803d','#22c55e'],
        'completed'         => ['Completed',          '#eff6ff','#1d4ed8','#3b82f6'],
        'rejected'          => ['Rejected',           '#fef2f2','#b91c1c','#ef4444'],
        'cancelled'         => ['Cancelled',          '#f3f4f6','#374151','#9ca3af'],
    ];
@endphp
@section('content')
<div style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Welcome back, {{ $firstName }}</h1>
        <p style="font-size:12.5px;color:#6b7280;margin:0">Here's a summary of your procurement requests.</p>
    </div>
</div>
{{-- STAT CARDS --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Active Request</div>
        <div style="font-size:28px;font-weight:800;color:#2563eb;margin:8px 0 5px;line-height:1">{{ $activePrs }}</div>
        <div style="font-size:11.5px;color:#9ca3af">Period: 
            @if($startDate && $endDate) {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            @elseif($startDate) From {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
            @elseif($endDate) Until {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            @else All Time
            @endif
        </div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Awaiting Approval</div>
        <div style="font-size:28px;font-weight:800;color:#ea580c;margin:8px 0 5px;line-height:1">{{ $awaitingApproval }}</div>
        <div style="font-size:11.5px;color:#9ca3af">Needs action</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">In Process</div>
        <div style="font-size:28px;font-weight:800;color:#0284c7;margin:8px 0 5px;line-height:1">{{ $inProcess }}</div>
        <div style="font-size:11.5px;color:#9ca3af">Purchasing verification</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Completed</div>
        <div style="font-size:28px;font-weight:800;color:#16a34a;margin:8px 0 5px;line-height:1">{{ $completedMonth }}</div>
        <div style="font-size:11.5px;color:#9ca3af">PR & SR fulfilled</div>
    </div>
</div>
{{-- PR TABLE --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:20px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6">
        <span style="font-size:14px;font-weight:700;color:#111827">Your Recent Requests</span>
        <a href="{{ route('purchase_requests.create') }}" style="padding:6px 14px;background:#111827;color:#fff;border-radius:8px;font-size:12.5px;font-weight:600;text-decoration:none">+ New Request</a>
    </div>
    <div style="display:flex;gap:8px;align-items:center;padding:12px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;">
        <input type="text" id="dash-pr-search" placeholder="Search doc, title..." oninput="applyDashPR()" style="height:32px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;font-size:12.5px;width:180px;outline:none;">
        <select id="dash-pr-category" onchange="applyDashPR()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;">
            <option value="">All Category</option>
            <option value="goods">📦 Goods</option>
            <option value="service">🔧 Service</option>
        </select>
        <select id="dash-pr-status" onchange="applyDashPR()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;">
            <option value="">All Status</option>
            <option value="awaiting_approval">Awaiting Approval</option>
            <option value="in_process">In Process</option>
            <option value="approved">Approved</option>
            <option value="completed">Completed</option>
            <option value="rejected">Rejected</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <form id="dash-global-filter-form" action="{{ route('dashboard') }}" method="GET" style="display:flex; align-items:center; gap:8px; margin-left:5px; border-left:1px solid #e5e7eb; padding-left:15px; margin-bottom:0;">
            <span style="font-size:11.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">DATE RANGE</span>
            <input type="date" name="start_date" id="dash-pr-start-date" value="{{ $startDate }}" style="height:32px;padding:0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;outline:none;" onchange="document.getElementById('dash-global-filter-form').submit()">
            <span style="font-size:12.5px;color:#9ca3af;">to</span>
            <input type="date" name="end_date" id="dash-pr-end-date" value="{{ $endDate }}" style="height:32px;padding:0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;outline:none;" onchange="document.getElementById('dash-global-filter-form').submit()">
        </form>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb">
                    <th onclick="dashPRSortFn(0)" style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;white-space:nowrap;">DOC NO. <span id="dps0">↕</span></th>
                    <th onclick="dashPRSortFn(1)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;">REQUESTED DATE <span id="dps1">↕</span></th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">DESCRIPTION</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">CATEGORY</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">QTY</th>
                    <th onclick="dashPRSortFn(5)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;">STATUS <span id="dps5">↕</span></th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;white-space:nowrap;">LAST UPDATE</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">ACTION</th>
                </tr>
            </thead>
            <tbody id="dash-pr-tbody">
                @forelse($requests as $pr)
                @php
                    [$sLabel,$sBg,$sText,$sDot] = $statusCfg[$pr->status] ?? [ucfirst(str_replace('_',' ',$pr->status)),'#f3f4f6','#374151','#9ca3af'];
                    $upd = $pr->updated_at;
                    $reqDate = \Carbon\Carbon::parse($pr->need_date ?? $pr->requested_date ?? $pr->created_at);
                    $lastUpdate = $upd->isToday() ? 'Today, '.$upd->format('H:i') : ($upd->isYesterday() ? 'Yesterday, '.$upd->format('H:i') : $upd->format('d M').', '.$upd->format('H:i'));
                    $prCategory = $pr->type
                        ?? ($pr->service_name ? 'service' : null)
                        ?? (str_contains(strtolower(class_basename($pr)), 'service') ? 'service' : 'goods');
                    $displayTitle = $pr->display_title ?? $pr->title ?? $pr->service_name ?? '—';
                    $displayDoc = $pr->display_doc
                        ?? $pr->document_number
                        ?? (($prCategory === 'service' ? 'SR-' : 'PR-') . str_pad($pr->id, 4, '0', STR_PAD_LEFT));
                    if($prCategory === 'service') {
                        $itemCount = $pr->item_count ?? 0;
                        if(!$itemCount && method_exists($pr, 'jobs')) {
                            foreach($pr->jobs as $job) { $itemCount += $job->items ? $job->items->count() : 0; }
                        }
                        $qtyLabel = $itemCount . ' Items';
                    } else {
                        $units = method_exists($pr, 'items') && $pr->items ? $pr->items->pluck('unit')->unique() : collect();
                        $qtyLabel = $units->count() === 1
                            ? ($pr->items ? $pr->items->sum('quantity') : 0) . ' ' . $units->first()
                            : (method_exists($pr, 'items') && $pr->items ? $pr->items->count() : 0) . ' Items';
                    }
                @endphp
                <tr data-status="{{ $pr->status }}" data-type="{{ $prCategory }}" data-date="{{ \Carbon\Carbon::parse($pr->created_at)->format('Y-m-d') }}" style="border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px"><span style="font-family:monospace;font-size:12px;font-weight:600;">{{ $displayDoc }}</span></td>
                    <td style="padding:13px 14px;color:#6b7280;white-space:nowrap">{{ $reqDate->format('d M Y') }}</td>
                    <td style="padding:13px 14px;">
                        <div style="font-weight:500;">{{ $displayTitle }}</div>
                        <div style="font-size:11px;color:#9ca3af;">{{ $pr->business_area }}</div>
                    </td>
                    <td style="padding:13px 14px;">
                        @if($prCategory === 'service')
                        <span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#e0e7ff;color:#3730a3;">🔧 Service</span>
                        @else
                        <span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569;">📦 Goods</span>
                        @endif
                    </td>
                    <td style="padding:13px 14px;color:#374151;">{{ $qtyLabel }}</td>
                    <td style="padding:13px 14px">
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:999px;background:{{ $sBg }};font-size:11.5px;font-weight:600;color:{{ $sText }}">
                            <span style="width:5px;height:5px;border-radius:50%;background:{{ $sDot }}"></span>{{ $sLabel }}
                        </span>
                    </td>
                    <td style="padding:13px 14px;color:#6b7280;">{{ $lastUpdate }}</td>
                    <td style="padding:13px 14px"><button onclick="openDetailModal({{ $pr->id }}, '{{ $prCategory }}')" style="padding:4px 12px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;">View</button></td>
                </tr>
                @empty
                <tr id="dash-pr-empty"><td colspan="8" style="text-align:center;padding:36px 20px;color:#9ca3af;">No purchase requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="dash-pr-pager" style="padding:12px 20px;border-top:1px solid #f3f4f6;"></div>
</div>
@if(auth()->user()->role === 'purchasing')
{{-- CHART SECTION --}}
<div style="margin-bottom:20px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:10px">
        <span style="font-size:14px;font-weight:700;color:#111827">Vendor Performance</span>
        <select id="chart-filter" onchange="fetchChartData()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;">
            <option value="week">This Week</option>
            <option value="month" selected>This Month</option>
            <option value="year">This Year</option>
            <option value="all">Anytime</option>
        </select>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px 20px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
                <span style="font-size:13.5px;font-weight:700;color:#111827">The Most Popular Vendor</span>
                <span style="font-size:11px;color:#9ca3af">👆 Click a slice</span>
            </div>
            <div style="position:relative;height:260px;width:100%">
                <canvas id="vendorFreqChart"></canvas>
            </div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px 20px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
                <span style="font-size:13.5px;font-weight:700;color:#111827">Total Value Of Each Vendor</span>
                <span style="font-size:11px;color:#9ca3af">👆 Click a bar</span>
            </div>
            <div style="position:relative;height:260px;width:100%">
                <canvas id="vendorValueChart"></canvas>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endif
{{-- RECENT VENDOR HISTORY --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6">
        <span style="font-size:14px;font-weight:700;color:#111827">Recent Vendor History</span>
        <a href="{{ route('history.orders') }}" style="font-size:12px;font-weight:500;color:#6b7280;text-decoration:none">View All →</a>
    </div>
    <div style="display:flex;gap:8px;align-items:center;padding:12px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;">
        <input type="text" id="dash-h-search" placeholder="Search vendor..." oninput="applyDashH()" style="height:32px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;font-size:12.5px;width:180px;outline:none;">
        <select id="dash-h-dept" onchange="applyDashH()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;">
            <option value="">All Units</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Produksi">Produksi</option>
            <option value="IT">IT</option>
            <option value="Finance">Finance</option>
            <option value="Operations">Operations</option>
            <option value="Engineering">Engineering</option>
        </select>
        <select id="dash-h-status" onchange="applyDashH()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;">
            <option value="">All Status</option>
            <option value="awaiting_approval">Awaiting Approval</option>
            <option value="in_process">In Process</option>
            <option value="approved">Approved</option>
            <option value="completed">Completed</option>
            <option value="rejected">Rejected</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb">
                    <th onclick="dashHSortFn(0)" style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;">DOC NO. <span id="dhs0">↕</span></th>
                    <th onclick="dashHSortFn(1)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;">VENDOR <span id="dhs1">↕</span></th>
                    <th onclick="dashHSortFn(2)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;">DEPARTMENT <span id="dhs2">↕</span></th>
                    <th onclick="dashHSortFn(3)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;">TOTAL VALUE <span id="dhs3">↕</span></th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">STATUS</th>
                </tr>
            </thead>
            <tbody id="dash-h-tbody">
                @forelse($recentHistory ?? [] as $h)
                @php
                    $vName = optional($h->vendor)->name ?? optional($h->vendor)->vendor_name ?? '—';
                    $docNo = optional($h->rfq)->purchaseRequest->document_number ?? '—';
                    $dept  = optional($h->rfq)->purchaseRequest->department ?? '—';
                    $totalVal = $h->selectionItems ? $h->selectionItems->sum(fn($si)=>($si->final_price_per_item??0)*($si->final_quantity??0)) : 0;
                    $deptColors=['Maintenance'=>['#e0f2fe','#0369a1'],'Produksi'=>['#dcfce7','#15803d'],'Operations'=>['#dcfce7','#15803d'],'Engineering'=>['#ede9fe','#7c3aed'],'IT'=>['#ede9fe','#7c3aed'],'Finance'=>['#fef9c3','#92400e']];
                    [$dBg,$dText]=$deptColors[$dept]??['#f1f5f9','#475569'];
                    
                    $prCreatedAt = optional(optional($h->rfq)->purchaseRequest)->created_at;
                    $decidedAt = $h->decided_at;
                    $leadDays = ($prCreatedAt && $decidedAt) ? (int) abs(\Carbon\Carbon::parse($prCreatedAt)->diffInDays($decidedAt)) : null;
                @endphp
                <tr data-dept="{{ $dept }}" data-status="completed" data-date="{{ \Carbon\Carbon::parse($prCreatedAt ?? $h->created_at)->format('Y-m-d') }}" style="border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px"><span style="font-family:monospace;font-size:12px;font-weight:600;">{{ $docNo }}</span></td>
                    <td style="padding:13px 14px"><div style="font-weight:500;">{{ $vName }}</div></td>
                    <td style="padding:13px 14px"><span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:{{ $dBg }};color:{{ $dText }}">{{ $dept }}</span></td>
                    <td style="padding:13px 14px;font-family:monospace;font-weight:600;">Rp {{ number_format($totalVal,0,',','.') }}</td>
                    <td style="padding:13px 14px;color:#6b7280;">{{ $leadDays !== null ? $leadDays . ' days' : '—' }}</td>
                    <td style="padding:13px 14px"><span style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:999px;background:#eff6ff;font-size:11.5px;font-weight:600;color:#1d4ed8"><span style="width:5px;height:5px;border-radius:50%;background:#3b82f6;"></span>Completed</span></td>
                </tr>
                @empty
                <tr id="dash-h-empty"><td colspan="5" style="text-align:center;padding:28px 20px;color:#9ca3af;">No vendor history yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="dash-h-pager" style="padding:12px 20px;border-top:1px solid #f3f4f6;"></div>
</div>
{{-- MODAL --}}
<div id="detail-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:1080px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 8px 40px rgba(0,0,0,.12)">
        {{-- Header --}}
        <div style="padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:flex-start">
            <div>
                <div id="detail-title" style="font-size:15px;font-weight:700;color:#111827"></div>
                <div id="detail-sub"   style="font-size:12px;color:#3b5bdb;margin-top:2px"></div>
            </div>
            <button onclick="closeDetailModal()" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af;line-height:1">&times;</button>
        </div>
        {{-- Meta bar --}}
        <div id="detail-meta" style="display:flex;gap:32px;padding:10px 22px;background:#f9fafb;border-bottom:1px solid #f3f4f6;font-size:12px;flex-wrap:wrap"></div>
        {{-- Body --}}
        <div id="detail-body" style="padding:18px 22px;overflow-y:auto;flex:1"></div>
        {{-- Footer --}}
        <div style="padding:14px 22px;border-top:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <button onclick="closeDetailModal()" style="padding:7px 18px;border:1px solid #d1d5db;border-radius:7px;background:#fff;font-size:13px;cursor:pointer;color:#374151">Close</button>
                {{-- Attachment Upload removed --}}
            </div>
            <div style="display:flex; gap:8px" id="detail-actions"></div>
        </div>
    </div>
</div>

<script>
@php
    foreach($requests as $req) {
        $req->loadMissing('user');
        if (method_exists($req, 'jobs')) { $req->loadMissing('jobs.items'); }
        if (method_exists($req, 'items')) { $req->loadMissing('items'); }
    }
@endphp
const prData = @json($requests->keyBy(fn($r) => ($r->type ?? 'goods') . '_' . $r->id)->toArray());
const isAdmin = false;
const isPurchasing = {{ $isPurchasing ? 'true' : 'false' }};
const prEng = { page:1, pageSize:5, sortCol:null, sortDir:'asc', gotoFn:'dashPRGoto', sizeFn:'dashPRPageSz' };
const hEng  = { page:1, pageSize:5, sortCol:null, sortDir:'asc', gotoFn:'dashHGoto',  sizeFn:'dashHPageSz'  };
// Auto-detects number → date → string and applies direction
function smartCompare(a, b, dir) {
    const aStripped = a.replace(/[^0-9.-]/g, '');
    const bStripped = b.replace(/[^0-9.-]/g, '');
    const an = parseFloat(aStripped);
    const bn = parseFloat(bStripped);
    let cmp;
    if (aStripped !== '' && bStripped !== '' && !isNaN(an) && !isNaN(bn)) {
        cmp = an - bn;
    } else {
        const da = new Date(a), db = new Date(b);
        cmp = (!isNaN(da.getTime()) && !isNaN(db.getTime())) ? da - db : a.localeCompare(b, 'id');
    }
    return dir === 'asc' ? cmp : -cmp;
}
const steps = [{label:'PR\nSubmitted'},{label:'Vendor\nSearch\n(Purchasing)'},{label:'Vendor\nSelection'},{label:'Completed'}];
function getStep(s){ return s==='completed'?4:s==='approved'?3:s==='in_process'?2:1; }
function buildProgressBar(status){
    const cur = getStep(status);
    const isFail = (status==='rejected'||status==='cancelled');
    return `<div style="display:flex;align-items:flex-start;gap:0;margin-bottom:20px">
        ${steps.map((s,i)=>{
            const n=i+1; let done=n<cur; let active=n===cur;
            if(status==='completed'&&n===4){done=true;active=false;}
            let cb=done?'#22c55e':active?'#3b5bdb':'#e5e7eb';
            let cc=done||active?'#fff':'#9ca3af';
            let lc=active?'#3b5bdb':done?'#22c55e':'#9ca3af';
            let ct=done?'✓':n;
            if(isFail&&active){cb=status==='rejected'?'#ef4444':'#9ca3af';lc=cb;ct='✕';}
            const lineColor=done?'#22c55e':'#e5e7eb';
            return `<div style="display:flex;flex-direction:column;align-items:center;flex:1;position:relative">
                ${i>0?`<div style="position:absolute;top:14px;right:50%;width:100%;height:2px;background:${lineColor};z-index:0"></div>`:''}
                <div style="width:28px;height:28px;border-radius:50%;background:${cb};color:${cc};font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;">${ct}</div>
                <div style="font-size:10.5px;font-weight:600;color:${lc};text-align:center;margin-top:5px;white-space:pre-line">${s.label}</div>
            </div>`;
        }).join('')}
    </div>`;
}
function renderPager(id, eng, total, start, end, pages) {
    const pager = document.getElementById(id);
    if (!pager) return;
    let btns = '';
    for (let i = 1; i <= pages; i++)
        btns += `<button onclick="${eng.gotoFn}(${i})" style="margin:0 2px;padding:3px 8px;background:${i===eng.page?'#111827':'#fff'};color:${i===eng.page?'#fff':'#000'};border:1px solid #d1d5db;border-radius:4px;cursor:pointer;">${i}</button>`;
    pager.innerHTML = `<div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#6b7280;">
        <span>Showing ${total===0?0:start+1}-${end} of ${total} entries</span>
        <div style="display:flex;align-items:center;gap:10px;">
            <div>${btns}</div>
            <select onchange="${eng.sizeFn}(this.value)" style="height:28px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;padding:0 6px;background:#fff;cursor:pointer;">
                ${[5,10,20,50].map(n=>`<option value="${n}" ${n===eng.pageSize?'selected':''}>${n} / page</option>`).join('')}
            </select>
        </div>
    </div>`;
}
function applyDashPR() {
    const q      = (document.getElementById('dash-pr-search')?.value   || '').toLowerCase();
    const cat    =  document.getElementById('dash-pr-category')?.value  || '';
    const status =  document.getElementById('dash-pr-status')?.value    || '';
    const tbody    = document.getElementById('dash-pr-tbody');
    const allRows  = Array.from(tbody.querySelectorAll('tr[data-status]'));
    const emptyRow = document.getElementById('dash-pr-empty');
    // 1. Filter
    let filtered = allRows.filter(r => {
        if (cat    && r.dataset.type   !== cat)    return false;
        if (status && r.dataset.status !== status) return false;
        if (q      && !r.textContent.toLowerCase().includes(q)) return false;
        return true;
    });
    // 2. Sort — re-orders the JS array
    if (prEng.sortCol !== null) {
        filtered.sort((a, b) => {
            const at = (a.querySelectorAll('td')[prEng.sortCol]?.textContent || '').trim();
            const bt = (b.querySelectorAll('td')[prEng.sortCol]?.textContent || '').trim();
            return smartCompare(at, bt, prEng.sortDir);
        });
    }
    // 3. Pagination math
    const pages = Math.max(1, Math.ceil(filtered.length / prEng.pageSize));
    if (prEng.page > pages) prEng.page = 1;
    const start = (prEng.page - 1) * prEng.pageSize;
    const end   = Math.min(prEng.page * prEng.pageSize, filtered.length);
    // 4. Hide every data row
    allRows.forEach(r => r.style.display = 'none');
    if (filtered.length === 0) {
        if (emptyRow) emptyRow.style.display = '';
    } else {
        if (emptyRow) emptyRow.style.display = 'none';
        // 5. Re-append sorted rows into the tbody — this is what actually reorders the DOM
        filtered.forEach(r => tbody.appendChild(r));
        // 6. Show only the current page slice
        filtered.slice(start, end).forEach(r => r.style.display = '');
    }
    // Keep the empty row pinned at the bottom
    if (emptyRow) tbody.appendChild(emptyRow);
    renderPager('dash-pr-pager', prEng, filtered.length, start, end, pages);
}
function applyDashH() {
    const q      = (document.getElementById('dash-h-search')?.value  || '').toLowerCase();
    const dept   =  document.getElementById('dash-h-dept')?.value    || '';
    const status =  document.getElementById('dash-h-status')?.value  || '';
    const tbody    = document.getElementById('dash-h-tbody');
    const allRows  = Array.from(tbody.querySelectorAll('tr[data-dept]'));
    const emptyRow = document.getElementById('dash-h-empty');
    // 1. Filter
    let filtered = allRows.filter(r => {
        if (dept   && r.dataset.dept   !== dept)   return false;
        if (status && r.dataset.status !== status) return false;
        if (q      && !r.textContent.toLowerCase().includes(q)) return false;
        return true;
    });
    // 2. Sort
    if (hEng.sortCol !== null) {
        filtered.sort((a, b) => {
            const at = (a.querySelectorAll('td')[hEng.sortCol]?.textContent || '').trim();
            const bt = (b.querySelectorAll('td')[hEng.sortCol]?.textContent || '').trim();
            return smartCompare(at, bt, hEng.sortDir);
        });
    }
    // 3. Pagination math
    const pages = Math.max(1, Math.ceil(filtered.length / hEng.pageSize));
    if (hEng.page > pages) hEng.page = 1;
    const start = (hEng.page - 1) * hEng.pageSize;
    const end   = Math.min(hEng.page * hEng.pageSize, filtered.length);
    // 4. Hide all
    allRows.forEach(r => r.style.display = 'none');
    if (filtered.length === 0) {
        if (emptyRow) emptyRow.style.display = '';
    } else {
        if (emptyRow) emptyRow.style.display = 'none';
        // 5. Re-append in sorted order
        filtered.forEach(r => tbody.appendChild(r));
        // 6. Show current page
        filtered.slice(start, end).forEach(r => r.style.display = '');
    }
    if (emptyRow) tbody.appendChild(emptyRow);
    renderPager('dash-h-pager', hEng, filtered.length, start, end, pages);
}
// Sort click handlers — toggle direction on same col, reset to asc on new col
function dashPRSortFn(col) {
    if (prEng.sortCol === col) {
        prEng.sortDir = prEng.sortDir === 'asc' ? 'desc' : 'asc';
    } else {
        prEng.sortCol = col;
        prEng.sortDir = 'asc';
    }
    // Reset all PR sort indicators, then set the active one
    document.querySelectorAll('[id^="dps"]').forEach(el => el.textContent = '↕');
    document.getElementById('dps' + col).textContent = prEng.sortDir === 'asc' ? '↑' : '↓';
    applyDashPR();
}
function dashHSortFn(col) {
    if (hEng.sortCol === col) {
        hEng.sortDir = hEng.sortDir === 'asc' ? 'desc' : 'asc';
    } else {
        hEng.sortCol = col;
        hEng.sortDir = 'asc';
    }
    // Reset all H sort indicators, then set the active one
    document.querySelectorAll('[id^="dhs"]').forEach(el => el.textContent = '↕');
    document.getElementById('dhs' + col).textContent = hEng.sortDir === 'asc' ? '↑' : '↓';
    applyDashH();
}
function dashPRGoto(p)  { prEng.page = p;            applyDashPR(); }
function dashPRPageSz(s){ prEng.pageSize = parseInt(s); prEng.page = 1; applyDashPR(); }
function dashHGoto(p)   { hEng.page  = p;            applyDashH(); }
function dashHPageSz(s) { hEng.pageSize  = parseInt(s); hEng.page  = 1; applyDashH(); }
function openDetailModal(id, category) {
    try { const pr = prData[category + '_' + id];
    if (!pr) return;

    const isService = (category === 'service' || pr.type === 'service');
    const rfq  = (pr.rfqs || [])[0];
    const rfqId= rfq ? rfq.id : null;

    // ── Vendor selection data ──
    const vendorSelections = rfq ? (rfq.vendor_selections || []) : [];
    const hasVS = vendorSelections.length > 0;

    // Build itemVS map: item_id → { vendor, unit_price, qty, total }
    const itemVS = {};
    vendorSelections.forEach(vs => {
        const vName = (vs.vendor && (vs.vendor.vendor_name || vs.vendor.name)) || '—';
        (vs.selection_items || []).forEach(si => {
            const key = si.purchase_request_item_id || si.service_request_item_id;
            if (key) itemVS[key] = {
                vendor:     vName,
                unit_price: parseFloat(si.final_price_per_item) || 0,
                qty:        parseInt(si.final_quantity) || 0,
                total:      (parseFloat(si.final_price_per_item)||0) * (parseInt(si.final_quantity)||0),
                unit:       si.final_unit || null,
                brand:      si.final_brand || null,
                spec:       si.final_specification || null,
                notes:      si.notes || null,
            };
        });
    });

    // Build vendorTotals map for summary cards
    const vendorTotals = {};
    const quotations = rfq ? (rfq.quotations || []) : [];
    vendorSelections.forEach(vs => {
        const vName = (vs.vendor && (vs.vendor.vendor_name || vs.vendor.name)) || '—';
        const vid   = vs.vendor_id;
        const q = quotations.find(qq => qq.vendor_id === vid);
        const qNote = q && q.note ? q.note : null;
        if (!vendorTotals[vid]) vendorTotals[vid] = { name: vName, items: [], total: 0, note: qNote };
        (vs.selection_items || []).forEach(si => {
            const sub = (parseFloat(si.final_price_per_item)||0) * (parseInt(si.final_quantity)||0);
            vendorTotals[vid].total += sub;
            const key  = si.purchase_request_item_id || si.service_request_item_id;
            const pool = isService
                ? (pr.jobs||[]).flatMap(j => j.items || [])
                : (pr.items || []);
            const found = pool.find(it => it.id == key);
            vendorTotals[vid].items.push({
                item_name: found ? (found.item_name || found.name || '—') : '(Item #' + key + ')',
                qty:       si.final_quantity,
                unit_price:si.final_price_per_item,
                subtotal:  sub,
            });
        });
    });

    // ── Store reference for Edit Items modal ──
    _editPR = pr;

    // ── Header ──
    document.getElementById('detail-title').textContent =
        pr.display_title || pr.title || pr.service_name || 'Request Detail';

    // Build sub-header without dangling separators
    const subParts = [
        pr.display_doc || pr.document_number ||
            (isService ? 'SR-' : 'PR-') + String(pr.id).padStart(4, '0')
    ];
    if (pr.department) subParts.push(pr.department);
    if (pr.business_area)      subParts.push(pr.business_area);
    document.getElementById('detail-sub').textContent = subParts.join(' | ');

    // ── Meta bar ──
    const priorityLabel = pr.priority
        ? pr.priority.charAt(0).toUpperCase() + pr.priority.slice(1)
        : 'Normal';
    const statusLabel = pr.status
        ? pr.status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
        : '—';

    document.getElementById('detail-meta').innerHTML = `
        <div><span style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Priority</span>
             <div style="font-weight:600;font-size:12.5px;margin-top:2px">${priorityLabel}</div></div>
        <div><span style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Business Area</span>
             <div style="font-weight:600;font-size:12.5px;margin-top:2px">${pr.business_area || '—'}</div></div>
        <div><span style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Status</span>
             <div style="font-weight:600;font-size:12.5px;margin-top:2px">${statusLabel}</div></div>
        ${isService ? '' : `<div><span style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Department</span>
             <div style="font-weight:600;font-size:12.5px;margin-top:2px">${pr.department || '—'}</div></div>`}`;

    if(document.getElementById('detail-select-vendor-btn')) document.getElementById('detail-select-vendor-btn').style.display = 'none';
    if(document.getElementById('detail-add-quotation-btn')) document.getElementById('detail-add-quotation-btn').style.display = 'none';
    if(document.getElementById('detail-generate-link-btn')) document.getElementById('detail-generate-link-btn').style.display = 'none';
    const approveForm = document.getElementById('detail-approve-form');
    const rejectForm = document.getElementById('detail-reject-form');
    const cancelForm = document.getElementById('detail-cancel-form');
    const reopenForm = document.getElementById('detail-reopen-form');
    if (reopenForm) reopenForm.style.display = 'none';
    if (approveForm) approveForm.style.display = 'none';
    if (rejectForm) rejectForm.style.display = 'none';
    if (cancelForm) cancelForm.style.display = 'none';

    if (pr.status === 'vendor_selection' || pr.status === 'vendor_search') {
        if(document.getElementById('detail-select-vendor-btn')) document.getElementById('detail-select-vendor-btn').style.display = 'inline-flex';
        if(document.getElementById('detail-select-vendor-btn')) document.getElementById('detail-select-vendor-btn').href = `/vendor-selection?key=${category}_${id}`;
    }

    const editItemsBtn = document.getElementById('detail-edit-items-btn');
    if (editItemsBtn) editItemsBtn.style.display = 'none';
    // User can edit items only when status = quotation_reopen. Admin can always edit active PRs.
    if (editItemsBtn) {
        if (isAdmin && pr.status !== 'completed' && pr.status !== 'rejected') {
            editItemsBtn.style.display = 'inline-flex';
        } else if (!isPurchasing && pr.status === 'quotation_reopen') {
            editItemsBtn.style.display = 'inline-flex';
        }
    }

    if (isPurchasing) {
        if (pr.status === 'submitted') {
            if (approveForm) {
                approveForm.style.display = 'block';
                document.getElementById('approve-id').value = pr.id;
                document.getElementById('approve-type').value = category;
            }
            if (rejectForm) {
                rejectForm.style.display = 'block';
                document.getElementById('reject-id').value = pr.id;
                document.getElementById('reject-type').value = category;
            }
        } else if (pr.status === 'vendor_selection' || pr.status === 'vendor_search') {
            if (rfqId) {
                if(document.getElementById('detail-add-quotation-btn')) document.getElementById('detail-add-quotation-btn').style.display = 'inline-flex';
                if(document.getElementById('detail-add-quotation-btn')) document.getElementById('detail-add-quotation-btn').href = `/rfq/${rfqId}/quotations/create`;
                if(document.getElementById('detail-generate-link-btn')) document.getElementById('detail-generate-link-btn').style.display = 'inline-flex';
                if(document.getElementById('detail-generate-link-btn')) document.getElementById('detail-generate-link-btn').dataset.rfq = rfqId;
            }
            if (cancelForm) {
                cancelForm.style.display = 'block';
                document.getElementById('cancel-id').value = pr.id;
                document.getElementById('cancel-type').value = category;
            }
        } else if (pr.status === 'completed') {
            if (reopenForm) {
                reopenForm.style.display = 'block';
                document.getElementById('reopen-id').value = pr.id;
                document.getElementById('reopen-type').value = category;
            }
        }
    }

    // ── Progress bar ──
    const _steps = [{label:'PR\nSubmitted'},{label:'Vendor\nSearch'},{label:'Vendor\nSelection'},{label:'Completed'}];
    function _step(s) { return s==='completed'?4:s==='vendor_selection'?3:s==='vendor_search'?2:1; }
    function buildProgressBar(status) {
        const cur = _step(status), isFail = (status==='rejected'||status==='cancelled');
        return '<div style="display:flex;align-items:flex-start;margin-bottom:20px">'
            + _steps.map((s, i) => {
                const n = i+1; let done = n<cur, active = n===cur;
                if (status==='completed' && n===4) { done=true; active=false; }
                let cb = done?'#22c55e':active?'#3b5bdb':'#e5e7eb';
                let cc = done||active?'#fff':'#9ca3af';
                let lc = active?'#3b5bdb':done?'#22c55e':'#9ca3af';
                let ct = done?'✓':n;
                if (isFail&&active) { cb=status==='rejected'?'#ef4444':'#9ca3af'; lc=cb; ct='✕'; }
                const line = n<=cur&&!isFail?'#22c55e':'#e5e7eb';
                return `<div style="display:flex;flex-direction:column;align-items:center;flex:1;position:relative">
                    ${i>0?`<div style="position:absolute;top:14px;right:50%;width:100%;height:2px;background:${line};z-index:0"></div>`:''}
                    <div style="width:28px;height:28px;border-radius:50%;background:${cb};color:${cc};font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;position:relative;z-index:1">${ct}</div>
                    <div style="font-size:10.5px;font-weight:600;color:${lc};text-align:center;margin-top:5px;white-space:pre-line">${s.label}</div>
                </div>`;
            }).join('') + '</div>';
    }

    // ── Request info grid ──
    const subDate = new Date(pr.submission_date||pr.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
    const rawReqDate = new Date(pr.requested_date||pr.need_date||pr.created_at);
    const reqDate = rawReqDate.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
    const isOverdue = pr.status !== 'completed' && pr.status !== 'cancelled' && pr.status !== 'rejected' && rawReqDate < new Date(new Date().setHours(0,0,0,0));

    // ── Item table ──
    const thS = 'padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:#9ca3af;white-space:nowrap;background:#f9fafb;border-bottom:1px solid #e5e7eb';
    const tdS = 'padding:8px 10px;border-bottom:1px solid #f3f4f6;font-size:12px;vertical-align:middle';
    let rows = '', grandTotal = 0, tableHtml = '';

    if (isService) {
        // ── SR Table: NO, ITEM NAME, SPEC, QTY, UNIT (no per-item price — shown in vendor summary below) ──
        (pr.jobs||[]).forEach(job => {
            const jCodeBadge = job.job_code
                ? `<span style="font-family:monospace;font-size:10px;background:#e0e7ff;color:#3730a3;padding:1px 6px;border-radius:4px;margin-right:7px;font-weight:700">${job.job_code}</span>`
                : '';
            rows += `<tr>
                <td colspan="${hasVS ? (isAdmin ? 11 : 10) : (isAdmin ? 8 : 7)}" style="background:#f0f4f8;padding:8px 12px;font-weight:700;font-size:11.5px;color:#374151;border-bottom:1px solid #e5e7eb">
                    💼 ${jCodeBadge}${job.job_description || '-'}
                </td>
            </tr>`;
            (job.items||[]).forEach((it, i) => {
                const vs = itemVS[it.id];
                if (vs) grandTotal += vs.total;
                rows += `<tr>
                    <td style="${tdS};color:#9ca3af">${i+1}</td>
                    <td style="${tdS};font-weight:600;color:#111827;font-family:monospace">${it.item_id || '—'}</td>
                    <td style="${tdS};font-weight:600;color:#111827">${it.item_name || it.name || '-'}</td>
                    <td style="${tdS};color:#6b7280;font-size:11.5px">
                        ${it.item_notes || it.description || '-'}
                        ${vs && vs.notes && vs.notes !== 'Selected' ? `<div style="background:#fef3c7;color:#b45309;padding:1px 4px;border-radius:3px;font-size:8.5px;font-weight:800;display:inline-block;margin-top:2px">VENDOR NOTE</div>` : ''}
                    </td>
                    <td style="${tdS};color:#6b7280;font-size:11.5px">
                        ${vs && vs.spec ? vs.spec : (it.specification || '-')}
                        ${vs && vs.spec && String(vs.spec).toLowerCase() !== String(it.specification||'').toLowerCase() ? `<div style="background:#fef3c7;color:#b45309;padding:1px 4px;border-radius:3px;font-size:8.5px;font-weight:800;display:inline-block;margin-top:2px" title="Original PR Spec: ${it.specification || '-'}">DIFFERS</div>` : ''}
                    </td>
                    <td style="${tdS};text-align:right;font-weight:600;color:#111827;font-family:monospace">${it.quantity || 0}</td>
                    <td style="${tdS};color:#6b7280">
                        ${vs && vs.unit ? vs.unit : (it.unit || '-')}
                        ${vs && vs.unit && String(vs.unit).toLowerCase() !== String(it.unit||'').toLowerCase() ? `<div style="background:#fef3c7;color:#b45309;padding:1px 4px;border-radius:3px;font-size:8.5px;font-weight:800;display:inline-block;margin-top:2px" title="Original PR Unit: ${it.unit || '-'}">DIFFERS</div>` : ''}
                    </td>
                    ${hasVS ? `
                    <td style="${tdS};font-family:monospace;font-weight:600;color:#111827;text-align:right;">${vs ? fmtRp(vs.unit_price) : '-'}</td>
                    <td style="${tdS};font-family:monospace;font-weight:700;color:#111827;text-align:right;">${vs ? fmtRp(vs.total) : '-'}</td>
                    <td style="${tdS}">
                        ${vs ? `<span style="padding:2px 8px;background:#e0f2fe;border-radius:4px;font-size:11px;font-weight:600;color:#0369a1;white-space:nowrap;">${vs.vendor}</span>` : '-'}
                    </td>` : ''}
                    ${isAdmin ? `
                    <td style="${tdS}">
                        <input type="text" class="form-control" style="font-size:11px;padding:4px;border:1px solid #e5e7eb;border-radius:4px;width:100%" placeholder="Add note..." value="${it.admin_notes || ''}" onchange="saveAdminNote(${it.id}, 'service', this.value)">
                    </td>
                    ` : ''}
                </tr>`;
            });
        });

        const extraTh = hasVS ? `
                        <th style="${thS};text-align:right;">UNIT PRICE (RP)</th>
                        <th style="${thS};text-align:right;">TOTAL (RP)</th>
                        <th style="${thS}">VENDOR</th>` : '';

        const totalRow = hasVS && grandTotal > 0 ? `
            <tr style="background:#f9fafb;">
                <td colspan="${isAdmin ? 8 : 7}" style="padding:9px 10px;text-align:right;font-size:12px;font-weight:700;color:#374151;">Total Request Value</td>
                <td colspan="3" style="padding:9px 10px;text-align:right;font-family:monospace;font-size:13px;font-weight:800;color:#111827;">${fmtRp(grandTotal)}</td>
            </tr>` : '';

        tableHtml = `<div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:4px">
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:${hasVS?'780px':'460px'}">
                    <thead><tr>
                        <th style="${thS};width:36px">NO</th>
                        <th style="${thS}">ITEM ID</th>
                        <th style="${thS}">ITEM NAME</th>
                        <th style="${thS}">NOTES</th>
                        <th style="${thS}">SPEC</th>
                        <th style="${thS};text-align:right;width:60px">QTY</th>
                        <th style="${thS};width:55px">UNIT</th>
                        ${extraTh}
                        ${isAdmin ? `<th style="${thS};width:150px">ADMIN NOTES</th>` : ''}
                    </tr></thead>
                    <tbody>${rows || `<tr><td colspan="${hasVS ? (isAdmin ? 11 : 10) : (isAdmin ? 8 : 7)}" style="text-align:center;padding:16px;color:#9ca3af">No items</td></tr>`}</tbody>
                    ${totalRow ? `<tfoot>${totalRow}</tfoot>` : ''}
                </table>
            </div>
        </div>`;

    } else {
        // ── Goods Table: NO, ITEM ID, ITEM NAME, NOTES, SPEC, QTY, UNIT [+ UNIT PRICE, TOTAL, VENDOR if VS] ──
        const hasPriceCol = hasVS;
        (pr.items||[]).forEach((it, i) => {
            const vs = itemVS[it.id];
            if (vs) grandTotal += vs.total;

            let origName = [it.item_name || it.name, it.specification, it.brand].filter(x => x).join('_') || '—';
            let dispName = origName;
            let differsBadge = '';
            if (vs) {
                let vsName = [it.item_name || it.name, vs.spec || it.specification, vs.brand || it.brand].filter(x => x).join('_');
                if (vsName.toLowerCase() !== origName.toLowerCase()) {
                    dispName = vsName;
                    differsBadge = `<div style="background:#fef3c7;color:#b45309;padding:1px 4px;border-radius:3px;font-size:8.5px;font-weight:800;display:inline-block;margin-top:2px" title="Original PR Item: ${origName}">DIFFERS</div>`;
                }
            }

            rows += `<tr>
                <td style="${tdS}">${i+1}</td>
                <td style="${tdS};font-family:monospace;color:#3b5bdb;font-weight:600">${it.item_id || '—'}</td>
                <td style="${tdS};font-weight:500;color:#111827">
                    ${dispName}
                    ${differsBadge}
                </td>
                <td style="${tdS};color:#6b7280;font-size:11.5px">
                    ${it.item_notes || '—'}
                    ${vs && vs.notes && vs.notes !== 'Selected' ? `<div style="background:#fef3c7;color:#b45309;padding:1px 4px;border-radius:3px;font-size:8.5px;font-weight:800;display:inline-block;margin-top:2px">VENDOR NOTE</div>` : ''}
                </td>
                <td style="${tdS};text-align:right;font-weight:600;color:#111827;font-family:monospace">${it.quantity || 0}</td>
                <td style="${tdS};color:#6b7280">
                    ${vs && vs.unit ? vs.unit : (it.unit || '—')}
                    ${vs && vs.unit && String(vs.unit).toLowerCase() !== String(it.unit||'').toLowerCase() ? `<div style="background:#fef3c7;color:#b45309;padding:1px 4px;border-radius:3px;font-size:8.5px;font-weight:800;display:inline-block;margin-top:2px" title="Original PR Unit: ${it.unit || '-'}">DIFFERS</div>` : ''}
                </td>
                ${hasPriceCol ? `
                <td style="${tdS};font-family:monospace;font-weight:600">${vs ? fmtRp(vs.unit_price) : '—'}</td>
                <td style="${tdS};font-family:monospace;font-weight:700;color:#111827">${vs ? fmtRp(vs.total) : '—'}</td>
                <td style="${tdS}">${vs ? `<span style="padding:2px 8px;background:#e0f2fe;border-radius:4px;font-size:11px;font-weight:600;color:#0369a1;white-space:nowrap">${vs.vendor}</span>` : '—'}</td>` : ''}
                ${isAdmin ? `
                <td style="${tdS}">
                    <input type="text" class="form-control" style="font-size:11px;padding:4px;border:1px solid #e5e7eb;border-radius:4px;width:100%" placeholder="Add note..." value="${it.admin_notes || ''}" onchange="saveAdminNote(${it.id}, 'goods', this.value)">
                </td>
                ` : ''}
                ${isPurchasing ? `
                <td style="${tdS}">
                    <textarea rows="2" style="font-size:11px;padding:4px;border:1px solid #e5e7eb;border-radius:4px;width:100%;resize:vertical;min-width:140px" placeholder="Catatan purchasing..." onchange="savePurchasingNote(${pr.id}, '${category}', this.value)">${it.purchasing_notes || ''}</textarea>
                </td>` : ''}
            </tr>`;
        });

        const gTh    = hasPriceCol ? `<th style="${thS};text-align:right">UNIT PRICE (RP)</th><th style="${thS};text-align:right">TOTAL (RP)</th><th style="${thS}">VENDOR</th>` : '';
        const purchNotesTh = isPurchasing ? `<th style="${thS};min-width:160px">PURCHASING NOTES</th>` : '';
        const gTotal = hasPriceCol && grandTotal > 0
            ? `<tr style="background:#f9fafb">
                <td colspan="6" style="padding:9px 10px;text-align:right;font-size:12px;font-weight:700;color:#374151">Total Request Value</td>
                <td colspan="3" style="padding:9px 10px;text-align:right;font-family:monospace;font-size:13px;font-weight:800;color:#111827">${fmtRp(grandTotal)}</td>
               </tr>` : '';

        tableHtml = `<div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:4px">
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:${hasPriceCol?'800px':'460px'}">
                    <thead><tr>
                        <th style="${thS}">NO</th>
                        <th style="${thS}">ITEM ID</th>
                        <th style="${thS};width:100%">ITEM NAME</th>
                        <th style="${thS}">NOTES</th>
                        <th style="${thS};text-align:right">QTY</th>
                        <th style="${thS}">UNIT</th>
                        ${gTh}
                        ${isAdmin ? `<th style="${thS};width:150px">ADMIN NOTES</th>` : ''}
                        ${purchNotesTh}
                    </tr></thead>
                    <tbody>${rows || '<tr><td colspan="${isAdmin ? 6 : 5}" style="text-align:center;padding:16px;color:#9ca3af">No items</td></tr>'}</tbody>
                    ${gTotal ? `<tfoot>${gTotal}</tfoot>` : ''}
                </table>
            </div>
        </div>`;
    }

    // ── Vendor Purchase Summary (for both PR and SR with VS) ──
    let vSumHtml = '';
    if (hasVS && Object.keys(vendorTotals).length > 0) {
        const isServiceSummary = isService;
        vSumHtml = `<div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-top:18px;margin-bottom:8px;padding-bottom:5px;border-bottom:2px solid #e5e7eb">
            ${isServiceSummary ? '🔧 Selected Service Vendor' : '📦 Vendor Purchase Summary'}
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">` +
            Object.values(vendorTotals).map(v => `
            <div style="flex:1;min-width:200px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">
                <div style="padding:10px 12px;background:#f8fafc;border-bottom:1px solid #e5e7eb">
                    <div style="font-size:13px;font-weight:700;color:#1e3a5f">${v.name}</div>
                    <div style="font-size:13px;font-weight:800;color:#111827;margin-top:3px;font-family:monospace">${fmtRp(v.total)}</div>
                    ${v.note ? `<div style="font-size:11px;color:#6b7280;margin-top:4px;font-style:italic">"${v.note}"</div>` : ''}
                </div>
                <div style="padding:8px 12px;display:flex;flex-direction:column;gap:5px">
                    ${v.items.map(si => `<div style="display:flex;justify-content:space-between;font-size:11.5px">
                        <span style="color:#6b7280">${si.item_name} — ${si.qty} × ${fmtRp(si.unit_price)}</span>
                        <span style="font-family:monospace;font-weight:600">${fmtRp(si.subtotal)}</span>
                    </div>`).join('')}
                </div>
            </div>`).join('')
        + '</div>';
    }

    // ── Activity Log ──
    const histories = rfq ? (rfq.histories || []) : [];
    let activityHtml = '';
    if (histories.length > 0) {
        activityHtml = histories.slice().reverse().map(h => {
            const actor = h.user?.name || 'System';
            const time  = h.action_date
                ? new Date(h.action_date).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'})
                : '';
            return `<div style="display:flex;gap:10px;padding:10px;background:#f9fafb;border-radius:8px;margin-bottom:8px;">
                <span style="width:7px;height:7px;border-radius:50%;background:#3b5bdb;margin-top:6px;flex-shrink:0"></span>
                <div>
                    <div style="font-size:12.5px;font-weight:600;color:#111827;line-height:1.4;">${h.action || 'Action'}</div>
                    ${h.notes ? `<div style="font-size:12px;color:#6b7280;margin-top:1px;line-height:1.4;">${h.notes}</div>` : ''}
                    <div style="font-size:11.5px;color:#9ca3af;margin-top:3px;">${time} — ${actor}</div>
                </div>
            </div>`;
        }).join('');
    } else {
        const subDate2 = new Date(pr.submission_date||pr.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
        activityHtml = `<div style="display:flex;gap:10px;padding:10px;background:#f9fafb;border-radius:8px;">
            <span style="width:7px;height:7px;border-radius:50%;background:#22c55e;margin-top:6px;flex-shrink:0"></span>
            <div>
                <div style="font-size:12.5px;font-weight:600;color:#111827;line-height:1.4;">${isService?'SR':'PR'} created and submitted</div>
                <div style="font-size:11.5px;color:#9ca3af;margin-top:3px;">${subDate2} — ${pr.user?.name || 'User'}</div>
            </div>
        </div>`;
    }

    // ── Assemble body ──
    document.getElementById('detail-body').innerHTML = `
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:10px">Progress Status</div>
        ${buildProgressBar(pr.status)}

        <div style="margin-top:16px;">
            <div style="font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;padding-bottom:5px;border-bottom:2px solid #e5e7eb">
                📋 Request Information
            </div>
            <div style="background:#f9fafb;border-radius:8px;padding:12px 14px;">
                <div style="display:grid;grid-template-columns:repeat(${isService ? 4 : 5},1fr);gap:10px;">
                    <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Submission Date</div>
                         <div style="font-weight:500;font-size:12.5px">${subDate}</div></div>
                    <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">
                             ${isService ? 'Service Name' : 'Department'}
                         </div>
                         <div style="font-weight:500;font-size:12.5px">${isService ? (pr.service_name || pr.display_title || '—') : (pr.department || '—')}</div></div>
                    <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Need Date</div>
                         <div style="font-weight:500;font-size:12.5px">${reqDate} ${isOverdue ? '<span style="background:#fee2e2;color:#991b1b;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;margin-left:4px">OVERDUE</span>' : ''}</div></div>
                    <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Business Area</div>
                         <div style="font-weight:500;font-size:12.5px">${pr.business_area || '—'}</div></div>
                    ${!isService ? `<div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Priority</div>
                         <div style="font-weight:500;font-size:12.5px">${pr.priority ? pr.priority.charAt(0).toUpperCase()+pr.priority.slice(1) : 'Normal'}</div></div>` : ''}
                </div>
                    ${pr.attachment_path ? `
                <div style="display:flex;align-items:center;gap:10px;margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;">
                    <div style="width:28px;height:28px;background:#dcfce7;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;">📎</div>
                    <div style="min-width:0;flex:1">
                        <div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;">Attached File</div>
                        <a href="/storage/${pr.attachment_path}" target="_blank" style="font-size:12.5px;font-weight:600;color:#16a34a;text-decoration:underline;">View the Document</a>
                    </div>
                </div>` : ''}
            </div>
        </div>

        </div>

        ${isPurchasing ? `
        <div style="margin-top:16px;padding:14px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;">
            <div style="font-size:10.5px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">✏️ Purchasing Notes</div>
            <textarea id="general-purchasing-notes" rows="3" style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px;font-size:12px;font-family:inherit;outline:none;resize:vertical;" placeholder="Add notes for this PR..." onchange="savePurchasingNote(${pr.id}, '${category}', this.value)">${pr.purchasing_notes || ''}</textarea>
        </div>` : (pr.purchasing_notes ? `
        <div style="margin-top:16px;padding:14px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;">
            <div style="font-size:10.5px;font-weight:700;color:#1d4ed8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">✏️ Purchasing Notes</div>
            <div style="font-size:12.5px;color:#374151;white-space:pre-wrap;">${pr.purchasing_notes}</div>
        </div>` : '')}

        <div style="display:flex;gap:20px;margin-top:16px;align-items:flex-start;">
            <!-- Left Column (Items Table) -->
            <div style="flex:2;min-width:0;display:flex;flex-direction:column;gap:16px;">
                <div>
                    <div style="font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;padding-bottom:5px;border-bottom:2px solid #e5e7eb">
                        ${isService ? '🛠️ Scope of Work & Items' : '📦 Item List'}
                    </div>
                    ${tableHtml}
                </div>
                ${vSumHtml}
            </div>

            <!-- Right Column (Activity Log) -->
            <div style="flex:1;min-width:260px;display:flex;flex-direction:column;gap:16px;">
                <div>
                    <div style="font-size:10.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;padding-bottom:5px;border-bottom:2px solid #e5e7eb">🕒 Activity Log</div>
                    ${activityHtml}
                </div>
            </div>
        </div>`;

    document.body.style.overflow = 'hidden';
    document.getElementById('detail-modal').style.display = 'flex'; } catch (err) { console.error(err); alert('Gagal: ' + err.message); }
}

function closeDetailModal() { document.body.style.overflow = ''; document.getElementById('detail-modal').style.display = 'none'; }

async function generateVendorLink() {
    const btn = document.getElementById('detail-generate-link-btn');
    const rfqId = btn.dataset.rfq;
    if(!rfqId) return;
    
    btn.innerText = 'Generating...';
    try {
        const res = await fetch(`/api/rfq/${rfqId}/generate-link`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        const data = await res.json();
        if(data.link) {
            document.getElementById('vendor-link-input').value = data.link;
            document.getElementById('vendor-link-modal').style.display = 'flex';
            document.getElementById('copy-link-btn').innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/></svg> Copy`;
        } else {
            alert('Error generating link');
        }
    } catch(e) {
        alert('Error generating link');
    }
    btn.innerHTML = '🔗 Generate Vendor Link';
}

function copyVendorLink() {
    const input = document.getElementById('vendor-link-input');
    input.select();
    document.execCommand('copy');
    
    const btn = document.getElementById('copy-link-btn');
    btn.innerHTML = `✓ Copied!`;
    setTimeout(() => {
        btn.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/></svg> Copy`;
    }, 2000);
}

function triggerCancel(btn) {
    const form = btn.closest('form');
    showConfirmModal('Batalkan Request', 'Apakah Anda yakin ingin membatalkan Request ini?', 'Batalkan', '#f59e0b', function() {
        form.submit();
    });
}

function triggerReopen(btn) {
    const form = btn.closest('form');
    showConfirmModal('Kembalikan Status', 'Kembalikan status ke Vendor Selection untuk memperpanjang waktu pengadaan (Link Vendor akan otomatis aktif kembali)?', 'Lanjutkan', '#3b82f6', function() {
        form.submit();
    });
}

// ── Purchasing Notes Save (inline) ──
let _purchasingNoteTimeout = null;
function savePurchasingNote(prId, prType, value) {
    clearTimeout(_purchasingNoteTimeout);
    _purchasingNoteTimeout = setTimeout(() => {
        fetch('{{ route("pr.save_purchasing_notes") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ id: prId, type: prType, notes: value })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const allPRKey = prType + '_' + prId;
                if (prData[allPRKey]) prData[allPRKey].purchasing_notes = value;
            }
        });
    }, 800);
}

// ── Edit Items Modal ──
let _editPR = null;

function fmtRp(num) { return new Intl.NumberFormat('id-ID', {style: 'currency', currency: 'IDR', minimumFractionDigits:0}).format(num); }
function formatRupiahShort(num) {
    if (num >= 1e9) return 'Rp ' + (num / 1e9).toFixed(1) + 'B';
    if (num >= 1e6) return 'Rp ' + (num / 1e6).toFixed(1) + 'Jt';
    if (num >= 1e3) return 'Rp ' + (num / 1e3).toFixed(0) + 'rb';
    return 'Rp ' + num;
}

async function fetchChartData() {
    const filter = document.getElementById('chart-filter')?.value;
    if (!filter) return;
    const response = await fetch(`/dashboard/chart-data?filter=${filter}`);

    if (response.status === 403) {
        console.error("Unauthorized to view chart data");
        return;
    }
    const data = await response.json();
    chartDataCache = data;
    const labels      = data.map(item => item.vendor_name);
    const frequencies = data.map(item => item.frequency);
    const values       = data.map(item => item.total_value);
    renderFreqChart(labels, frequencies);
    renderValueChart(labels, values);
}

function renderFreqChart(labels, dataPoints) {
    const canvas = document.getElementById('vendorFreqChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (freqChart) freqChart.destroy();
    const colors = [
        'rgba(59, 130, 246, 0.85)', 'rgba(16, 185, 129, 0.85)',
        'rgba(245, 158, 11, 0.85)', 'rgba(239, 68, 68, 0.85)',
        'rgba(139, 92, 246, 0.85)', 'rgba(236, 72, 153, 0.85)'
    ];
    freqChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{ data: dataPoints, backgroundColor: colors, borderColor: '#fff', borderWidth: 1.5 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            onClick: (evt, elements) => {
                if (elements.length > 0) showVendorDetail(elements[0].index);
            },
            onHover: (evt, elements) => {
                evt.native.target.style.cursor = elements.length ? 'pointer' : 'default';
            },
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } }
        }
    });
}

function renderValueChart(labels, dataPoints) {
    const canvas = document.getElementById('vendorValueChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (valueChart) valueChart.destroy();
    valueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Value',
                data: dataPoints,
                backgroundColor: 'rgba(59, 130, 246, 0.85)',
                borderColor: '#2563eb',
                borderWidth: 1.5,
                borderRadius: 6,
                maxBarThickness: 32,
                categoryPercentage: 0.5,
                barPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            onClick: (evt, elements) => {
                if (elements.length > 0) showVendorDetail(elements[0].index);
            },
            onHover: (evt, elements) => {
                evt.native.target.style.cursor = elements.length ? 'pointer' : 'default';
            },
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => formatRupiahShort(v) } } }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    applyDashPR();
    applyDashH();
    if (isPurchasing) {
        fetchChartData();
    }
});
</script>
@endsection

