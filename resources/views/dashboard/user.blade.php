@extends('layouts.app')
@php
    $pageTitle = 'Dashboard';
    $user      = auth()->user();
    $firstName = explode(' ', $user->name)[0];
    $statusCfg = [
        'submitted'        => ['Awaiting Approval', '#fef3c7', '#d97706', '#f59e0b'],
        'vendor_search'    => ['Vendor Search',     '#e0e7ff', '#4338ca', '#6366f1'],
        'vendor_selection' => ['Vendor Selection',  '#dbeafe', '#1d4ed8', '#3b82f6'],
        'completed'        => ['Completed',         '#dcfce7', '#15803d', '#22c55e'],
        'rejected'         => ['Rejected',          '#fee2e2', '#b91c1c', '#ef4444'],
        'cancelled'        => ['Cancelled',         '#f3f4f6', '#4b5563', '#9ca3af'],
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
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Active PR</div>
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
        <span style="font-size:14px;font-weight:700;color:#111827">Purchase Requests (PR)</span>
        <a href="{{ route('purchase_requests.create') }}" style="padding:6px 14px;background:#111827;color:#fff;border-radius:8px;font-size:12.5px;font-weight:600;text-decoration:none">+ New Request</a>
    </div>

    <div style="display:flex;gap:8px;align-items:center;padding:12px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;">
        <input type="text" id="dash-pr-search" placeholder="Search doc, title..." oninput="applyDashPR()" style="height:32px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;font-size:12.5px;width:180px;outline:none;">
        <select id="dash-pr-category" onchange="applyDashPR()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;">
            <option value="">All Category</option>
            <option value="goods"> Goods</option>
            <option value="service"> Service</option>
        </select>
        <select id="dash-pr-status" onchange="applyDashPR()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;">
            <option value="">All Status</option>
            <option value="submitted">Awaiting Approval</option>
            <option value="vendor_search">Vendor Search</option>
            <option value="vendor_selection">Vendor Selection</option>
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
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">ITEMS</th>
                    <th onclick="dashPRSortFn(5)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;">STATUS <span id="dps5">↕</span></th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;white-space:nowrap;">SUBMITTED</th>
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
                        $qtyLabel = $itemCount . ' Item(s)';
                    } else {
                        $qtyLabel = ($pr->item_count ?? (method_exists($pr, 'items') && $pr->items ? $pr->items->count() : 0)) . ' Item(s)';
                    }

                    $submittedDate = \Carbon\Carbon::parse($pr->submission_date ?? $pr->created_at)->format('d M Y');
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
                        <span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#e0e7ff;color:#3730a3;"> Service</span>
                        @else
                        <span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569;"> Goods</span>
                        @endif
                    </td>
                    <td style="padding:13px 14px;color:#374151;">{{ $qtyLabel }}</td>
                    <td style="padding:13px 14px">
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:999px;background:{{ $sBg }};font-size:11.5px;font-weight:600;color:{{ $sText }}">
                            <span style="width:5px;height:5px;border-radius:50%;background:{{ $sDot }}"></span>{{ $sLabel }}
                        </span>
                    </td>
                    <td style="padding:13px 14px;color:#6b7280;white-space:nowrap">{{ $submittedDate }}</td>
                    <td style="padding:13px 14px;color:#6b7280;">{{ $lastUpdate }}</td>
                    <td style="padding:13px 14px">
                        <button onclick="openDetailModal({{ $pr->id }}, '{{ $prCategory }}')" style="padding:4px 12px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;font-size:12px;">View</button>
                    </td>
                </tr>
                @empty
                <tr id="dash-pr-empty"><td colspan="9" style="text-align:center;padding:36px 20px;color:#9ca3af;">No purchase requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="dash-pr-pager" style="padding:12px 20px;border-top:1px solid #f3f4f6;"></div>
</div>

{{-- RECENT PROCUREMENT ACTIVITY --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6">
        <span style="font-size:14px;font-weight:700;color:#111827">Recent Procurement Activity</span>
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
            <option value="completed">Completed</option>
            <option value="vendor_search">Vendor Search</option>
            <option value="vendor_selection">Vendor Selection</option>
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
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">LEAD TIME</th>
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
                    <td style="padding:13px 14px;color:#6b7280;">—</td>
                    <td style="padding:13px 14px"><span style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:999px;background:#eff6ff;font-size:11.5px;font-weight:600;color:#1d4ed8"><span style="width:5px;height:5px;border-radius:50%;background:#3b82f6;"></span>Completed</span></td>
                </tr>
                @empty
                <tr id="dash-h-empty"><td colspan="6" style="text-align:center;padding:28px 20px;color:#9ca3af;">No vendor history yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="dash-h-pager" style="padding:12px 20px;border-top:1px solid #f3f4f6;"></div>
</div>

{{-- DETAIL MODAL --}}
<div id="detail-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:820px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 8px 40px rgba(0,0,0,.12)">
        {{-- Header --}}
        <div style="padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div id="modal-pr-title" style="font-size:15px;font-weight:700;color:#111827"></div>
                <div id="modal-pr-sub" style="font-size:12px;color:#3b5bdb;margin-top:2px"></div>
            </div>
            <button onclick="closeDetailModal()" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af;line-height:1;">&times;</button>
        </div>
        {{-- Meta --}}
        <div id="modal-pr-meta" style="display:flex;gap:32px;padding:10px 22px;background:#f9fafb;border-bottom:1px solid #f3f4f6;font-size:12px;"></div>
        {{-- Body --}}
        <div id="modal-pr-body" style="padding:18px 22px;overflow-y:auto;flex:1;"></div>
        {{-- Footer --}}
        <div style="padding:14px 22px;border-top:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
            <button onclick="closeDetailModal()" style="padding:7px 18px;border:1px solid #d1d5db;border-radius:7px;background:#fff;font-size:13px;cursor:pointer;color:#374151;">Close</button>
            <a id="modal-select-vendor-btn" href="#" style="padding:7px 18px;background:#1e3a5f;color:#fff;border-radius:7px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Select Vendor
            </a>
        </div>
    </div>
</div>

<script>
@php
    foreach($requests as $req) {
        $req->loadMissing('user');
        if (method_exists($req, 'jobs')) { $req->loadMissing('jobs.items'); }
        if (method_exists($req, 'items')) { $req->loadMissing('items'); }
        if (method_exists($req, 'rfqs')) { $req->loadMissing(['rfqs.vendorSelections.vendor','rfqs.vendorSelections.selectionItems','rfqs.histories.user']); }
    }
@endphp
const prData = @json(
    $requests->mapWithKeys(function($r) {
        return [($r->type ?? 'goods') . '_' . $r->id => $r];
    })->toArray()
);

const prEng = { page:1, pageSize:5, sortCol:null, sortDir:'asc', gotoFn:'dashPRGoto', sizeFn:'dashPRPageSz' };
const hEng  = { page:1, pageSize:5, sortCol:null, sortDir:'asc', gotoFn:'dashHGoto',  sizeFn:'dashHPageSz'  };

function fmtRp(n) {
    if (!n && n !== 0) return '—';
    return 'Rp ' + Number(n).toLocaleString('id-ID');
}

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
function getStep(s){ return s==='completed'?4:s==='vendor_selection'?3:s==='vendor_search'?2:1; }

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

    let filtered = allRows.filter(r => {
        if (cat    && r.dataset.type   !== cat)    return false;
        if (status && r.dataset.status !== status) return false;
        if (q      && !r.textContent.toLowerCase().includes(q)) return false;
        return true;
    });

    if (prEng.sortCol !== null) {
        filtered.sort((a, b) => {
            const at = (a.querySelectorAll('td')[prEng.sortCol]?.textContent || '').trim();
            const bt = (b.querySelectorAll('td')[prEng.sortCol]?.textContent || '').trim();
            return smartCompare(at, bt, prEng.sortDir);
        });
    }

    const pages = Math.max(1, Math.ceil(filtered.length / prEng.pageSize));
    if (prEng.page > pages) prEng.page = 1;
    const start = (prEng.page - 1) * prEng.pageSize;
    const end   = Math.min(prEng.page * prEng.pageSize, filtered.length);

    allRows.forEach(r => r.style.display = 'none');

    if (filtered.length === 0) {
        if (emptyRow) emptyRow.style.display = '';
    } else {
        if (emptyRow) emptyRow.style.display = 'none';
        filtered.forEach(r => tbody.appendChild(r));
        filtered.slice(start, end).forEach(r => r.style.display = '');
    }

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

    let filtered = allRows.filter(r => {
        if (dept   && r.dataset.dept   !== dept)   return false;
        if (status && r.dataset.status !== status) return false;
        if (q      && !r.textContent.toLowerCase().includes(q)) return false;
        return true;
    });

    if (hEng.sortCol !== null) {
        filtered.sort((a, b) => {
            const at = (a.querySelectorAll('td')[hEng.sortCol]?.textContent || '').trim();
            const bt = (b.querySelectorAll('td')[hEng.sortCol]?.textContent || '').trim();
            return smartCompare(at, bt, hEng.sortDir);
        });
    }

    const pages = Math.max(1, Math.ceil(filtered.length / hEng.pageSize));
    if (hEng.page > pages) hEng.page = 1;
    const start = (hEng.page - 1) * hEng.pageSize;
    const end   = Math.min(hEng.page * hEng.pageSize, filtered.length);

    allRows.forEach(r => r.style.display = 'none');

    if (filtered.length === 0) {
        if (emptyRow) emptyRow.style.display = '';
    } else {
        if (emptyRow) emptyRow.style.display = 'none';
        filtered.forEach(r => tbody.appendChild(r));
        filtered.slice(start, end).forEach(r => r.style.display = '');
    }

    if (emptyRow) tbody.appendChild(emptyRow);
    renderPager('dash-h-pager', hEng, filtered.length, start, end, pages);
}

function dashPRSortFn(col) {
    if (prEng.sortCol === col) {
        prEng.sortDir = prEng.sortDir === 'asc' ? 'desc' : 'asc';
    } else {
        prEng.sortCol = col;
        prEng.sortDir = 'asc';
    }
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
    document.querySelectorAll('[id^="dhs"]').forEach(el => el.textContent = '↕');
    document.getElementById('dhs' + col).textContent = hEng.sortDir === 'asc' ? '↑' : '↓';
    applyDashH();
}

function dashPRGoto(p)  { prEng.page = p;            applyDashPR(); }
function dashPRPageSz(s){ prEng.pageSize = parseInt(s); prEng.page = 1; applyDashPR(); }
function dashHGoto(p)   { hEng.page  = p;            applyDashH(); }
function dashHPageSz(s) { hEng.pageSize  = parseInt(s); hEng.page  = 1; applyDashH(); }

function openDetailModal(id, category) {
    const pr = prData[category + '_' + id];
    if (!pr) return;

    // === Gather vendor selection data ===
    const rfq = (pr.rfqs || [])[0];
    const rfqId = rfq ? rfq.id : null;
    const vendorSelections = rfq ? (rfq.vendor_selections || []) : [];
    const hasVS = vendorSelections.length > 0;

    const itemVS = {};
    vendorSelections.forEach(vs => {
        const vName = (vs.vendor && (vs.vendor.vendor_name || vs.vendor.name)) || '—';
        (vs.selection_items || []).forEach(si => {
            const key = si.purchase_request_item_id || si.service_request_item_id;
            if (key) {
                itemVS[key] = {
                    vendor:     vName,
                    vendor_id:  vs.vendor_id,
                    unit_price: parseFloat(si.final_price_per_item) || 0,
                    qty:        parseInt(si.final_quantity) || 0,
                    total:      (parseFloat(si.final_price_per_item)||0) * (parseInt(si.final_quantity)||0),
                };
            }
        });
    });

    const vendorTotals = {};
    vendorSelections.forEach(vs => {
        const vName = (vs.vendor && (vs.vendor.vendor_name || vs.vendor.name)) || '—';
        const vid = vs.vendor_id;
        if (!vendorTotals[vid]) vendorTotals[vid] = { name: vName, items: [], total: 0 };
        (vs.selection_items || []).forEach(si => {
            const subtotal = (parseFloat(si.final_price_per_item)||0) * (parseInt(si.final_quantity)||0);
            vendorTotals[vid].total += subtotal;
            const key = si.purchase_request_item_id || si.service_request_item_id;
            const pool = (category === 'service' || pr.type === 'service')
                ? (pr.jobs||[]).flatMap(j => j.items || [])
                : (pr.items || []);
            const prItem = pool.find(it => it.id == key);
            vendorTotals[vid].items.push({
                item_id:    prItem?.item_id || '—',
                item_name:  prItem?.item_name || prItem?.name || '—',
                qty:        si.final_quantity,
                unit_price: si.final_price_per_item,
                subtotal,
            });
        });
    });

    // Header
    document.getElementById('modal-pr-title').textContent = pr.display_title || pr.title || pr.service_name || 'Request Detail';
    document.getElementById('modal-pr-sub').textContent   =
        (pr.display_doc || pr.document_number || (category==='service'?'SR-':'PR-')+String(pr.id).padStart(4,'0'))
        + ' | ' + (pr.department || '') + (pr.plant ? ' | Kebutuhan ' + pr.plant : '');

    // Meta bar
    document.getElementById('modal-pr-meta').innerHTML =
        `<div><span style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Priority</span><div style="font-weight:600;font-size:12.5px;margin-top:2px">${pr.priority || 'Normal'}</div></div>
         <div><span style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Plant</span><div style="font-weight:600;font-size:12.5px;margin-top:2px">${pr.plant || '—'}</div></div>
         <div><span style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Status</span><div style="font-weight:600;font-size:12.5px;margin-top:2px">${pr.status ? pr.status.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()) : '—'}</div></div>`;

    // Select Vendor btn
    document.getElementById('modal-select-vendor-btn').href =
        `/vendor-selection?key=${category}_${id}`;

    // === Build item rows ===
    const thStyle = 'padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:#9ca3af;white-space:nowrap;';
    const tdStyle = 'padding:8px 10px;border-bottom:1px solid #f9fafb;font-size:12px;';

    let itemRows = '';
    let grandTotal = 0;

    if (category === 'service' || pr.type === 'service' || pr.jobs) {
        (pr.jobs||[]).forEach(job => {
            itemRows += `<tr><td colspan="${hasVS?10:7}" style="background:#f3f4f6;padding:6px 10px;font-weight:700;font-size:11.5px;">JOB: ${job.job_description}</td></tr>`;
            (job.items||[]).forEach((it, i) => {
                const vs = itemVS[it.id];
                if (vs) grandTotal += vs.total;
                itemRows += `<tr>
                    <td style="${tdStyle}">${i+1}</td>
                    <td style="${tdStyle}font-family:monospace;color:#3b5bdb;font-weight:600;">${it.item_id||'—'}</td>
                    <td style="${tdStyle}font-weight:500;">${it.item_name||it.name||'—'}</td>
                    <td style="${tdStyle}color:#6b7280;font-size:11.5px;">${it.item_notes||it.description||'—'}</td>
                    <td style="${tdStyle}color:#6b7280;font-size:11.5px;">${it.specification||'—'}</td>
                    <td style="${tdStyle}text-align:right;font-weight:600;">${it.quantity}</td>
                    <td style="${tdStyle}color:#6b7280;">${it.unit}</td>
                    ${hasVS ? `
                    <td style="${tdStyle}font-family:monospace;font-weight:600;color:#111827;">${vs ? fmtRp(vs.unit_price) : '—'}</td>
                    <td style="${tdStyle}font-family:monospace;font-weight:700;color:#111827;">${vs ? fmtRp(vs.total) : '—'}</td>
                    <td style="${tdStyle}">
                        ${vs ? `<span style="padding:2px 8px;background:#e0f2fe;border-radius:4px;font-size:11px;font-weight:600;color:#0369a1;white-space:nowrap;">${vs.vendor}</span>` : '—'}
                    </td>` : ''}
                </tr>`;
            });
        });
    } else {
        (pr.items||[]).forEach((it, i) => {
            const vs = itemVS[it.id];
            if (vs) grandTotal += vs.total;
            itemRows += `<tr>
                <td style="${tdStyle}">${i+1}</td>
                <td style="${tdStyle}font-family:monospace;color:#3b5bdb;font-weight:600;">${it.item_id||it.item_code||'—'}</td>
                <td style="${tdStyle}font-weight:500;">${it.item_name||it.name||'—'}</td>
                <td style="${tdStyle}color:#6b7280;font-size:11.5px;">${it.item_notes||it.description||'—'}</td>
                <td style="${tdStyle}color:#6b7280;font-size:11.5px;">${it.specification||'—'}</td>
                <td style="${tdStyle}text-align:right;font-weight:600;">${it.quantity}</td>
                <td style="${tdStyle}color:#6b7280;">${it.unit}</td>
                ${hasVS ? `
                <td style="${tdStyle}font-family:monospace;font-weight:600;color:#111827;">${vs ? fmtRp(vs.unit_price) : '—'}</td>
                <td style="${tdStyle}font-family:monospace;font-weight:700;color:#111827;">${vs ? fmtRp(vs.total) : '—'}</td>
                <td style="${tdStyle}">
                    ${vs ? `<span style="padding:2px 8px;background:#e0f2fe;border-radius:4px;font-size:11px;font-weight:600;color:#0369a1;white-space:nowrap;">${vs.vendor}</span>` : '—'}
                </td>` : ''}
            </tr>`;
        });
    }

    const extraTh = hasVS ? `
        <th style="${thStyle}text-align:right;">UNIT PRICE (RP)</th>
        <th style="${thStyle}text-align:right;">TOTAL (RP)</th>
        <th style="${thStyle}">VENDOR</th>` : '';

    const totalRow = hasVS && grandTotal > 0 ? `
        <tr style="background:#f9fafb;">
            <td colspan="7" style="padding:9px 10px;text-align:right;font-size:12px;font-weight:700;color:#374151;">Total Request Value</td>
            <td colspan="3" style="padding:9px 10px;text-align:right;font-family:monospace;font-size:13px;font-weight:800;color:#111827;">${fmtRp(grandTotal)}</td>
        </tr>` : '';

    // === Vendor Purchase Summary ===
    let vendorSummaryHtml = '';
    if (hasVS && Object.keys(vendorTotals).length > 0) {
        const vendorCols = Object.values(vendorTotals).map(v => `
            <div style="flex:1;min-width:180px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                <div style="padding:10px 12px;background:#f8fafc;border-bottom:1px solid #e5e7eb;">
                    <div style="font-size:13px;font-weight:700;color:#1e3a5f;">${v.name}</div>
                    <div style="font-size:13px;font-weight:800;color:#111827;margin-top:3px;font-family:monospace;">${fmtRp(v.total)}</div>
                </div>
                <div style="padding:8px 12px;display:flex;flex-direction:column;gap:5px;">
                    ${v.items.map(si=>`
                    <div style="display:flex;justify-content:space-between;font-size:11.5px;color:#374151;">
                        <span style="color:#6b7280;">${si.item_name} — ${si.qty} × ${fmtRp(si.unit_price)}</span>
                        <span style="font-family:monospace;font-weight:600;">${fmtRp(si.subtotal)}</span>
                    </div>`).join('')}
                </div>
            </div>`).join('');

        vendorSummaryHtml = `
            <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-top:18px;margin-bottom:8px;padding-bottom:5px;border-bottom:2px solid #e5e7eb;">
                Vendor Purchase Summary
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                ${vendorCols}
            </div>`;
    }

    // === Activity Log ===
    let activityHtml = '';
    const histories = rfq ? (rfq.histories || []) : [];
    const subDate = new Date(pr.submission_date||pr.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
    const rawReqDate = new Date(pr.requested_date||pr.need_date||pr.created_at);
    const reqDate = rawReqDate.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
    const isOverdue = pr.status !== 'completed' && pr.status !== 'cancelled' && pr.status !== 'rejected' && rawReqDate < new Date(new Date().setHours(0,0,0,0));

    let logItems = '';
    if (histories.length > 0) {
        logItems = histories.slice(-6).reverse().map(h => {
            const actor = h.user?.name || 'System';
            const time  = h.action_date ? new Date(h.action_date).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '';
            return `<div style="display:flex;gap:8px;font-size:12px;margin-bottom:8px;">
                <span style="width:6px;height:6px;border-radius:50%;background:#3b5bdb;margin-top:5px;flex-shrink:0;"></span>
                <div>
                    <span style="font-weight:600;color:#111827;">${h.action||'Action'}</span>
                    ${h.notes?`<span style="color:#6b7280;"> — Notes: ${h.notes}</span>`:''}
                    <div style="font-size:11px;color:#9ca3af;margin-top:1px;">${time} — ${actor}</div>
                </div>
            </div>`;
        }).join('');
    } else {
        logItems = `<div style="display:flex;gap:8px;font-size:12px;">
            <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;margin-top:5px;flex-shrink:0;"></span>
            <div><span style="font-weight:600;color:#111827;">PR created and submitted to supervisor</span>
            <div style="font-size:11px;color:#9ca3af;margin-top:1px;">${subDate} — ${pr.user?.name||'You'}</div></div>
        </div>`;
    }

    // === Assemble body ===
    document.getElementById('modal-pr-body').innerHTML = `
        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:10px">Progress Status</div>
        ${buildProgressBar(pr.status)}

        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:10px">Request Information</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px;background:#f9fafb;border-radius:8px;padding:12px 14px">
            <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Submission Date</div><div style="font-weight:500;font-size:12.5px">${subDate}</div></div>
            <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Department</div><div style="font-weight:500;font-size:12.5px">${pr.department||'—'}</div></div>
            <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Need Date</div><div style="font-weight:500;font-size:12.5px">${reqDate} ${isOverdue ? '<span style="background:#fee2e2;color:#991b1b;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;margin-left:4px">OVERDUE</span>' : ''}</div></div>
            <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Plant</div><div style="font-weight:500;font-size:12.5px">${pr.plant||'—'}</div></div>
        </div>

        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:8px;padding-bottom:5px;border-bottom:2px solid #e5e7eb;">Item List</div>
        <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:4px">
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:${hasVS?'780px':'460px'}">
                    <thead><tr style="background:#f9fafb">
                        <th style="${thStyle}">NO</th>
                        <th style="${thStyle}">ITEM ID</th>
                        <th style="${thStyle}">ITEM NAME</th>
                        <th style="${thStyle}">DESCRIPTION</th>
                        <th style="${thStyle}">SPEC</th>
                        <th style="${thStyle}text-align:right;">QTY</th>
                        <th style="${thStyle}">UNIT</th>
                        ${extraTh}
                    </tr></thead>
                    <tbody>${itemRows||'<tr><td colspan="7" style="text-align:center;padding:16px;">No items</td></tr>'}</tbody>
                    ${totalRow ? `<tfoot>${totalRow}</tfoot>` : ''}
                </table>
            </div>
        </div>
        ${vendorSummaryHtml}

        <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-top:18px;margin-bottom:8px;padding-bottom:5px;border-bottom:2px solid #e5e7eb;">Activity Log</div>
        ${logItems}`;

    const selectBtn = document.getElementById('modal-select-vendor-btn');
    if (pr.status === 'vendor_selection') {
        selectBtn.style.display = 'inline-flex';
    } else {
        selectBtn.style.display = 'none';
    }

    document.body.style.overflow = 'hidden';
    document.getElementById('detail-modal').style.display = 'flex';
}

function closeDetailModal() { document.body.style.overflow = ''; document.getElementById('detail-modal').style.display = 'none'; }

document.addEventListener('DOMContentLoaded', () => { applyDashPR(); applyDashH(); });
</script>
@endsection