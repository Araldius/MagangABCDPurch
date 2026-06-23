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
<div style="margin-bottom:20px">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Welcome back, {{ $firstName }}</h1>
    <p style="font-size:12.5px;color:#6b7280;margin:0">Here's a summary of your procurement requests.</p>
</div>
{{-- STAT CARDS --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Active Request</div>
        <div style="font-size:28px;font-weight:800;color:#2563eb;margin:8px 0 5px;line-height:1">{{ $activePrs }}</div>
        <div style="font-size:11.5px;color:#9ca3af">Period: {{ now()->format('M Y') }}</div>
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
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Completed This Month</div>
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
                <tr data-status="{{ $pr->status }}" data-type="{{ $prCategory }}" style="border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px"><span style="font-family:monospace;font-size:12px;font-weight:600;">{{ $displayDoc }}</span></td>
                    <td style="padding:13px 14px;color:#6b7280;white-space:nowrap">{{ $reqDate->format('d M Y') }}</td>
                    <td style="padding:13px 14px;">
                        <div style="font-weight:500;">{{ $displayTitle }}</div>
                        <div style="font-size:11px;color:#9ca3af;">{{ $pr->plant }}</div>
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
                @endphp
                <tr data-dept="{{ $dept }}" data-status="completed" style="border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px"><span style="font-family:monospace;font-size:12px;font-weight:600;">{{ $docNo }}</span></td>
                    <td style="padding:13px 14px"><div style="font-weight:500;">{{ $vName }}</div></td>
                    <td style="padding:13px 14px"><span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:{{ $dBg }};color:{{ $dText }}">{{ $dept }}</span></td>
                    <td style="padding:13px 14px;font-family:monospace;font-weight:600;">Rp {{ number_format($totalVal,0,',','.') }}</td>
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
    <div style="background:#fff;border-radius:14px;width:100%;max-width:700px;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 8px 40px rgba(0,0,0,.12)">
        <div style="padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center">
            <div>
                <div id="modal-pr-title" style="font-size:15px;font-weight:700;"></div>
                <div id="modal-pr-sub" style="font-size:12px;color:#3b5bdb;margin-top:2px"></div>
            </div>
            <button onclick="closeDetailModal()" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af;">&times;</button>
        </div>
        <div id="modal-pr-body" style="padding:18px 22px;overflow-y:auto;flex:1;"></div>
    </div>
</div>
{{-- VENDOR CHART DETAIL MODAL --}}
<div id="vendor-detail-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:210;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:640px;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 8px 40px rgba(0,0,0,.12)">
        <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center">
            <div id="vendor-detail-name" style="font-size:14.5px;font-weight:700;color:#111827"></div>
            <button onclick="closeVendorDetailModal()" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af;">&times;</button>
        </div>
        <div id="vendor-detail-body" style="padding:16px 20px;overflow-y:auto;flex:1;"></div>
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
const prData = @json($requests->keyBy('id')->toArray());
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
    const pr = prData[id];
    if (!pr) return;
    document.getElementById('modal-pr-title').textContent = pr.title || pr.service_name || 'Request Detail';
    document.getElementById('modal-pr-sub').textContent   = (pr.document_number || (category==='service'?'SR-':'PR-')+String(pr.id).padStart(4,'0')) + ' | ' + (pr.plant||'');
    let itemRows = '';
    if (category === 'service' || pr.type === 'service' || pr.jobs) {
        (pr.jobs||[]).forEach(job => {
            itemRows += `<tr><td colspan="6" style="background:#f3f4f6;padding:6px 12px;font-weight:700;font-size:11.5px;">💼 JOB: ${job.job_description}</td></tr>`;
            (job.items||[]).forEach((it,i) => {
                itemRows += `<tr>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${i+1}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;color:#9ca3af;">—</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-weight:500;">${it.item_name||it.name||'—'}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${it.specification||'—'}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${it.quantity}</td>
                    <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${it.unit}</td>
                </tr>`;
            });
        });
    } else {
        itemRows = (pr.items||[]).map((it,i) => `<tr>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${i+1}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-family:monospace;color:#3b5bdb;font-weight:600">${it.item_id||it.item_code||'—'}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;font-weight:500;">${it.item_name||it.name||'—'}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${it.specification||'—'}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${it.quantity}</td>
            <td style="padding:9px 12px;border-bottom:1px solid #f9fafb;">${it.unit}</td>
        </tr>`).join('');
    }
    const subDate = new Date(pr.submission_date||pr.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
    const reqDate = new Date(pr.requested_date||pr.need_date||pr.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
    document.getElementById('modal-pr-body').innerHTML = `
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:10px">Progress Status</div>
        ${buildProgressBar(pr.status)}
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:10px">Request Information</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px;background:#f9fafb;border-radius:8px;padding:12px 14px">
            <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Submission Date</div><div style="font-weight:500;font-size:12.5px">${subDate}</div></div>
            <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Department</div><div style="font-weight:500;font-size:12.5px">${pr.department||'—'}</div></div>
            <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Requested Date</div><div style="font-weight:500;font-size:12.5px">${reqDate}</div></div>
            <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Plant</div><div style="font-weight:500;font-size:12.5px">${pr.plant||'—'}</div></div>
        </div>
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:8px">Scope Details</div>
        <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:16px">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead><tr style="background:#f9fafb">
                    <th style="padding:8px 12px;text-align:left;">NO</th>
                    <th style="padding:8px 12px;text-align:left;">ITEM ID</th>
                    <th style="padding:8px 12px;text-align:left;">ITEM NAME</th>
                    <th style="padding:8px 12px;text-align:left;">SPEC</th>
                    <th style="padding:8px 12px;text-align:left;">QTY</th>
                    <th style="padding:8px 12px;text-align:left;">UNIT</th>
                </tr></thead>
                <tbody>${itemRows||'<tr><td colspan="6" style="text-align:center;padding:16px;">No items</td></tr>'}</tbody>
            </table>
        </div>
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:8px">Activity Log</div>
        <div style="font-size:12px;color:#111827;">Request submitted on ${subDate} by ${pr.user?.name||'You'}</div>`;
    document.getElementById('detail-modal').style.display = 'flex';
}
function closeDetailModal() { document.getElementById('detail-modal').style.display = 'none'; }
function showVendorDetail(idx) {
    const item = chartDataCache[idx];
    if (!item) return;
    document.getElementById('vendor-detail-name').textContent = item.vendor_name;
 
    const rows = (item.items || []).map(it => `<tr>
        <td style="padding:8px 10px;border-bottom:1px solid #f9fafb;font-family:monospace;color:#3b5bdb;font-weight:600">${it.item_id ?? '-'}</td>
        <td style="padding:8px 10px;border-bottom:1px solid #f9fafb;font-weight:500;">${it.item_name ?? '-'}</td>
        <td style="padding:8px 10px;border-bottom:1px solid #f9fafb;">${it.qty ?? 0}</td>
        <td style="padding:8px 10px;border-bottom:1px solid #f9fafb;">${it.unit ?? '-'}</td>
        <td style="padding:8px 10px;border-bottom:1px solid #f9fafb;">Rp ${Number(it.unit_price ?? 0).toLocaleString('id-ID')}</td>
        <td style="padding:8px 10px;border-bottom:1px solid #f9fafb;font-weight:600;">Rp ${Number(it.total_price ?? 0).toLocaleString('id-ID')}</td>
    </tr>`).join('');
 
    document.getElementById('vendor-detail-body').innerHTML = `
        <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f9fafb">
            <span style="font-size:12.5px;color:#6b7280">Selected Frequency</span>
            <span style="font-size:13px;font-weight:700;color:#111827">${item.frequency}x</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:9px 0;margin-bottom:14px">
            <span style="font-size:12.5px;color:#6b7280">Total Transaction Value</span>
            <span style="font-size:13px;font-weight:700;color:#111827">Rp ${Number(item.total_value).toLocaleString('id-ID')}</span>
        </div>
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:8px">Item Breakdown</div>
        <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead><tr style="background:#f9fafb">
                    <th style="padding:8px 10px;text-align:left;">ITEM ID</th>
                    <th style="padding:8px 10px;text-align:left;">ITEM NAME</th>
                    <th style="padding:8px 10px;text-align:left;">QTY</th>
                    <th style="padding:8px 10px;text-align:left;">UNIT</th>
                    <th style="padding:8px 10px;text-align:left;">UNIT PRICE</th>
                    <th style="padding:8px 10px;text-align:left;">TOTAL PRICE</th>
                </tr></thead>
                <tbody>${rows || '<tr><td colspan="6" style="text-align:center;padding:16px;color:#9ca3af;">No items</td></tr>'}</tbody>
            </table>
        </div>`;
    document.getElementById('vendor-detail-modal').style.display = 'flex';
}
function closeVendorDetailModal() { document.getElementById('vendor-detail-modal').style.display = 'none'; }
const isPurchasing = {{ auth()->user()->role === 'purchasing' ? 'true' : 'false' }};
let freqChart  = null;
let valueChart = null;
let chartDataCache = [];

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