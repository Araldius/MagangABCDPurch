@extends('layouts.app')
@php
    $pageTitle='PR & SR List';
    $statusCfg=[
        'submitted'        => [Auth::user()->role === 'purchasing' ? 'Awaiting Approval' : 'Purchasing Approval', '#fef3c7', '#d97706', '#f59e0b'],
        'vendor_search'    => ['Vendor Search',     '#e0e7ff', '#4338ca', '#6366f1'],
        'vendor_selection' => ['Vendor Selection',  '#dbeafe', '#1d4ed8', '#3b82f6'],
        'quotation_reopen' => ['Quotation Reopen',  '#fce7f3', '#9d174d', '#ec4899'],
        'completed'        => ['Completed',         '#dcfce7', '#15803d', '#22c55e'],
        'rejected'         => ['Rejected',          '#fee2e2', '#b91c1c', '#ef4444'],
        'cancelled'        => ['Cancelled',         '#f3f4f6', '#4b5563', '#9ca3af'],
    ];
@endphp
@section('content')

<div style="margin-bottom:20px">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Procurement List</h1>
    <p style="font-size:12.5px;color:#6b7280;margin:0">
        {{ $isPurchasing ? 'All purchase & service requests from all departments.' : 'All your submitted requests.' }}
    </p>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;gap:10px">
        <span style="font-size:14px;font-weight:700;color:#111827">All Requests</span>
        <a href="{{ route('purchase_requests.create') }}" style="padding:6px 14px;background:#111827;color:#fff;border-radius:7px;font-size:12.5px;font-weight:600;text-decoration:none">+ New Request</a>
    </div>

    {{-- Toolbar Filter --}}
    <div style="display:flex;gap:8px;align-items:center;padding:12px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap">
        <input type="text" id="pr-search" placeholder="Search doc, title..." oninput="applyFilters()"
            style="height:32px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;font-size:12.5px;width:200px;outline:none">
        <select id="type-filter" onchange="applyFilters()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer">
            <option value="">All Types</option>
            <option value="goods">📦 Goods</option>
            <option value="service">🔧 Service</option>
        </select>
        @if($isPurchasing)
        <select id="dept-filter" onchange="applyFilters()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer">
            <option value="">All Dept.</option>
            @foreach($allRequests->pluck('department')->unique()->filter()->sort()->values() as $dept)
            <option value="{{ $dept }}">{{ $dept }}</option>
            @endforeach
        </select>
        @endif
        <select id="plant-filter" onchange="applyFilters()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer">
            <option value="">All Plant</option>
            <option value="Cikarang">Cikarang</option>
            <option value="Cibitung">Cibitung</option>
            <option value="Gresik">Gresik</option>
        </select>
        @if($isPurchasing)
        <select id="vendor-filter" onchange="applyFilters()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer">
            <option value="">All Vendors</option>
            @foreach($allRequests->pluck('vendor_name')->unique()->filter()->sort()->values() as $vend)
            <option value="{{ $vend }}">{{ $vend }}</option>
            @endforeach
        </select>
        @endif
        <select id="status-filter" onchange="applyFilters()" style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer">
            <option value="">All Status</option>
            <option value="submitted">{{ Auth::user()->role === 'purchasing' ? 'Awaiting Approval' : 'Purchasing Approval' }}</option>
            <option value="vendor_search">Vendor Search</option>
            <option value="vendor_selection">Vendor Selection</option>
            <option value="quotation_reopen">Quotation Reopen</option>
            <option value="completed">Completed</option>
            <option value="rejected">Rejected</option>
            <option value="cancelled">Cancelled</option>
        </select>
        
        <div style="display:flex; align-items:center; gap:8px; margin-left:5px; border-left:1px solid #e5e7eb; padding-left:15px;">
            <span style="font-size:11.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">DATE RANGE</span>
            <input type="date" id="start-date-filter" style="height:32px;padding:0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;outline:none;" onchange="applyFilters()">
            <span style="font-size:12.5px;color:#9ca3af;">to</span>
            <input type="date" id="end-date-filter" style="height:32px;padding:0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;background:#fff;cursor:pointer;outline:none;" onchange="applyFilters()">
        </div>
    </div>

    <div style="overflow-x:auto">
        <table id="pr-table" style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb">
                    <th onclick="prSortFn(0)" style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;white-space:nowrap">DOC NO. <span id="prs0">↕</span></th>
                    <th onclick="prSortFn(1)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer">DESCRIPTION <span id="prs1">↕</span></th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">CATEGORY</th>
                    @if($isPurchasing)
                    <th onclick="prSortFn(3)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer">REQUESTER <span id="prs3">↕</span></th>
                    @endif
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEMS</th>
                    <th onclick="prSortFn({{ $isPurchasing?5:4 }})" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer">STATUS <span id="prs{{ $isPurchasing?5:4 }}">↕</span></th>
                    <th onclick="prSortFn({{ $isPurchasing?6:5 }})" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;white-space:nowrap">SUBMITTED <span id="prs{{ $isPurchasing?6:5 }}">↕</span></th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;white-space:nowrap">LAST UPDATE</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">ACTION</th>
                </tr>
            </thead>
            <tbody id="pr-tbody">
                @forelse($allRequests as $pr)
                @php
                    $normStatus = str_replace(' ', '_', strtolower($pr->status));
                    [$sLabel,$sBg,$sText,$sDot] = $statusCfg[$normStatus] ?? [ucfirst(str_replace('_',' ',$pr->status)),'#f3f4f6','#374151','#9ca3af'];
                    $upd = $pr->updated_at;
                    $lu  = $upd->isToday()
                        ? 'Today, '.$upd->format('H:i')
                        : ($upd->isYesterday() ? 'Yesterday, '.$upd->format('H:i') : $upd->format('d M').', '.$upd->format('H:i'));

                    $prCategory = $pr->type
                        ?? ($pr->service_name ? 'service' : null)
                        ?? (str_contains(strtolower(class_basename($pr)), 'service') ? 'service' : 'goods');

                    $displayTitle = $pr->display_title ?? $pr->title ?? $pr->service_name ?? '—';
                    $displayDoc   = $pr->display_doc ?? $pr->document_number
                        ?? (($prCategory === 'service' ? 'SR-' : 'PR-') . str_pad($pr->id, 4, '0', STR_PAD_LEFT));
                    $plantVal     = $pr->plant ?? '—';

                    if ($prCategory === 'service') {
                        $itemCount = $pr->item_count ?? 0;
                        if (!$itemCount && method_exists($pr, 'jobs') && $pr->jobs) {
                            foreach ($pr->jobs as $job) { $itemCount += $job->items ? $job->items->count() : 0; }
                        }
                        $qtyLabel = $itemCount . ' item(s)';
                    } else {
                        $qtyLabel = ($pr->item_count ?? (method_exists($pr,'items') && $pr->items ? $pr->items->count() : 0)) . ' item(s)';
                    }
                @endphp
                <tr data-status="{{ $pr->status }}"
                    data-dept="{{ $pr->department ?? 'General' }}"
                    data-type="{{ $prCategory }}"
                    data-plant="{{ $plantVal }}"
                    data-vendor="{{ $pr->vendor_name ?? '' }}"
                    data-date="{{ \Carbon\Carbon::parse($pr->created_at)->format('Y-m-d') }}"
                    style="border-bottom:1px solid #f3f4f6"
                    onmouseover="this.style.background='#fafafa'"
                    onmouseout="this.style.background='transparent'">

                    <td style="padding:13px 20px">
                        <span style="font-family:monospace;font-size:12px;font-weight:600">{{ $displayDoc }}</span>
                    </td>
                    <td style="padding:13px 14px;max-width:200px">
                        <div style="font-weight:500">{{ $displayTitle }}</div>
                        <div style="font-size:11px;color:#9ca3af">{{ $plantVal }}</div>
                    </td>
                    <td style="padding:13px 14px">
                        @if($prCategory === 'service')
                        <span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#e0e7ff;color:#3730a3">🔧 Service</span>
                        @else
                        <span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569">📦 Goods</span>
                        @endif
                    </td>
                    @if($isPurchasing)
                    <td style="padding:13px 14px">
                        <div style="font-weight:500">{{ optional($pr->user)->name ?? '—' }}</div>
                        <div style="font-size:11px;color:#9ca3af">{{ $pr->department ?? '—' }}</div>
                    </td>
                    @endif
                    <td style="padding:13px 14px">{{ $qtyLabel }}</td>
                    <td style="padding:13px 14px">
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;background:{{ $sBg }};font-size:11.5px;font-weight:600;color:{{ $sText }};white-space:nowrap">
                            <span style="width:5px;height:5px;border-radius:50%;background:{{ $sDot }}"></span>{{ $sLabel }}
                        </span>
                    </td>
                    <td style="padding:13px 14px;color:#6b7280;white-space:nowrap">
                        {{ \Carbon\Carbon::parse($pr->submission_date ?? $pr->created_at)->format('d M Y') }}
                    </td>
                    <td style="padding:13px 14px;color:#6b7280;white-space:nowrap">{{ $lu }}</td>
                    <td style="padding:13px 14px">
                        <button onclick="openPRDetail({{ $pr->id }}, '{{ $prCategory }}')"
                            style="padding:4px 12px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;font-size:12px">
                            Detail
                        </button>
                    </td>
                </tr>
                @empty
                <tr id="pr-empty"><td colspan="{{ $isPurchasing?9:8 }}" style="text-align:center;padding:36px 20px;color:#9ca3af">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="pr-pager" style="padding:12px 20px;border-top:1px solid #f3f4f6"></div>
</div>

{{-- ── DETAIL MODAL ── --}}
<div id="pr-detail-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:1080px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 8px 40px rgba(0,0,0,.12)">
        {{-- Header --}}
        <div style="padding:18px 22px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:flex-start">
            <div>
                <div id="detail-title" style="font-size:15px;font-weight:700;color:#111827"></div>
                <div id="detail-sub"   style="font-size:12px;color:#3b5bdb;margin-top:2px"></div>
            </div>
            <button onclick="closePRDetail()" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af;line-height:1">&times;</button>
        </div>
        {{-- Meta bar --}}
        <div id="detail-meta" style="display:flex;gap:32px;padding:10px 22px;background:#f9fafb;border-bottom:1px solid #f3f4f6;font-size:12px;flex-wrap:wrap"></div>
        {{-- Body --}}
        <div id="detail-body" style="padding:18px 22px;overflow-y:auto;flex:1"></div>
        {{-- Footer --}}
        <div style="padding:14px 22px;border-top:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <button onclick="closePRDetail()" style="padding:7px 18px;border:1px solid #d1d5db;border-radius:7px;background:#fff;font-size:13px;cursor:pointer;color:#374151">Close</button>
                {{-- Attachment Upload (for User on any status, shown conditionally) --}}
                <form id="detail-attachment-form" method="POST" action="{{ route('pr.upload_attachment') }}" enctype="multipart/form-data" style="display:none;margin:0;">
                    @csrf
                    <input type="hidden" name="id" id="attach-pr-id">
                    <input type="hidden" name="type" id="attach-pr-type">
                    <label style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:7px;cursor:pointer;font-size:12.5px;font-weight:600;color:#15803d;">
                        📎 <span id="attach-label">Attach the file</span>
                        <input type="file" name="attachment" id="attach-file-input" accept=".pdf,.xlsx,.xls,.jpg,.jpeg,.png" style="display:none;" onchange="document.getElementById('attach-label').textContent = this.files[0]?.name || 'Lampirkan File'; document.getElementById('attach-submit-btn').style.display='inline-flex';">
                    </label>
                    <button type="submit" id="attach-submit-btn" style="display:none;padding:7px 14px;background:#15803d;color:#fff;border:none;border-radius:7px;font-size:12.5px;font-weight:600;cursor:pointer;">Upload</button>
                </form>
            </div>
            <div style="display:flex; gap:8px" id="detail-actions">
                <form id="detail-approve-form" method="POST" action="{{ route('requests.approve') }}" style="display:none; margin:0">
                    @csrf
                    <input type="hidden" name="id" id="approve-id">
                    <input type="hidden" name="type" id="approve-type">
                    <button type="submit" style="padding:7px 18px;background:#22c55e;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                        ✓ Approve to Vendor Search
                    </button>
                </form>
                <form id="detail-reject-form" method="POST" action="{{ route('requests.reject') }}" style="display:none; margin:0">
                    @csrf
                    <input type="hidden" name="id" id="reject-id">
                    <input type="hidden" name="type" id="reject-type">
                    <button type="submit" style="padding:7px 18px;background:#ef4444;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                        ✗ Reject
                    </button>
                </form>
                <form id="detail-cancel-form" method="POST" action="{{ route('requests.cancel') }}" style="display:none; margin:0">
                    @csrf
                    <input type="hidden" name="id" id="cancel-id">
                    <input type="hidden" name="type" id="cancel-type">
                    <button type="button" onclick="triggerCancel(this)" style="padding:7px 18px;background:#f59e0b;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                        Ø Cancel Request
                    </button>
                </form>
                <form id="detail-reopen-form" method="POST" action="{{ route('requests.reopen') }}" style="display:none; margin:0">
                    @csrf
                    <input type="hidden" name="id" id="reopen-id">
                    <input type="hidden" name="type" id="reopen-type">
                    <button type="button" onclick="triggerReopen(this)" style="padding:7px 18px;background:#3b82f6;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                        ↺ Re-open (Extend Time)
                    </button>
                </form>
                {{-- Edit Items for quotation_reopen status (User only) --}}
                <button id="detail-edit-items-btn" type="button" onclick="openEditItemsModal()"
                    style="display:none;padding:7px 18px;background:#fef3c7;color:#92400e;border:1px solid #fcd34d;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;align-items:center;gap:6px">
                    ✏️ Edit Items
                </button>
                <a id="detail-add-quotation-btn" href="#"
                    style="display:none;padding:7px 18px;background:#f8fafc;color:#475569;border:1px solid #cbd5e1;border-radius:7px;font-size:13px;font-weight:600;text-decoration:none;align-items:center;gap:6px">
                    + Add Quotation
                </a>
                <button id="detail-generate-link-btn" type="button" onclick="generateVendorLink()"
                    style="display:none;padding:7px 18px;background:#e0e7ff;color:#3730a3;border:1px solid #c7d2fe;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;align-items:center;gap:6px">
                    🔗 Generate Vendor Link
                </button>
                <a id="detail-select-vendor-btn" href="#"
                    style="display:none;padding:7px 18px;background:#1e3a5f;color:#fff;border-radius:7px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Select Vendor
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── VENDOR LINK MODAL ── --}}
<div id="vendor-link-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:210;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:480px;box-shadow:0 8px 40px rgba(0,0,0,.15);overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;background:#f8fafc">
            <div style="font-weight:700;color:#1e3a5f;display:flex;align-items:center;gap:8px">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Vendor Link Generated
            </div>
            <button onclick="document.getElementById('vendor-link-modal').style.display='none'" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af;line-height:1">&times;</button>
        </div>
        <div style="padding:24px 20px">
            <div style="font-size:13px;color:#4b5563;margin-bottom:12px">Tautan ini valid selama 7 hari. Bagikan ke vendor agar mereka dapat memasukkan penawaran secara mandiri.</div>
            <div style="display:flex;gap:8px">
                <input type="text" id="vendor-link-input" readonly style="flex:1;padding:10px 14px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#374151;background:#f9fafb;outline:none" value="">
                <button onclick="copyVendorLink()" id="copy-link-btn" style="padding:10px 16px;background:#1e3a5f;color:#fff;border:none;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:0.2s">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Copy
                </button>
            </div>
        </div>
    </div>
</div>

<script>
@php
    foreach($allRequests as $req) {
        $req->loadMissing('user');
        if (method_exists($req, 'jobs'))  { $req->loadMissing('jobs.items'); }
        if (method_exists($req, 'items')) { $req->loadMissing('items'); }
        if (method_exists($req, 'rfqs'))  {
            $req->loadMissing([
                'rfqs.vendorSelections.vendor',
                'rfqs.vendorSelections.selectionItems',
                'rfqs.quotations.vendor',
                'rfqs.histories.user',
            ]);
        }
    }
@endphp

// key = "type_id" to prevent PR id=1 and SR id=1 collision
const isAdmin = @json(auth()->check() && auth()->user()->role === 'admin');

const allPRs = @json(
    $allRequests->mapWithKeys(function($r) {
        $cat = $r instanceof \App\Models\ServiceRequest ? 'service' : 'goods';
        return [$cat . '_' . $r->id => $r];
    })->toArray()
);
const isPurchasing = {{ $isPurchasing ? 'true' : 'false' }};
const prEng = { page:1, pageSize:10, sortCol:null, sortDir:'asc', gotoFn:'prGoto', sizeFn:'prPageSz' };

function fmtRp(n) {
    if (!n && n !== 0) return '—';
    return 'Rp ' + Number(n).toLocaleString('id-ID');
}

function smartCompare(a, b, dir) {
    const da = new Date(a), db = new Date(b);
    let cmp = 0;
    if (!isNaN(da.getTime()) && !isNaN(db.getTime()) && !a.match(/^(PR|SR|PO|RFQ)-/i)) {
        cmp = da - db;
    } else {
        cmp = a.localeCompare(b, undefined, {numeric: true, sensitivity: 'base'});
    }
    return dir === 'asc' ? cmp : -cmp;
}

function renderPager(id, eng, total, start, end, pages) {
    const pager = document.getElementById(id);
    if (!pager) return;
    let btns = '';
    for (let i = 1; i <= pages; i++)
        btns += `<button onclick="${eng.gotoFn}(${i})"
            style="margin:0 2px;padding:3px 8px;background:${i===eng.page?'#111827':'#fff'};color:${i===eng.page?'#fff':'#000'};border:1px solid #d1d5db;border-radius:4px;cursor:pointer">${i}</button>`;
    pager.innerHTML = `<div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#6b7280">
        <span>Showing ${total===0?0:start+1}–${end} of ${total} entries</span>
        <div style="display:flex;align-items:center;gap:10px">
            <div>${btns}</div>
            <select onchange="${eng.sizeFn}(this.value)" style="height:28px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;padding:0 6px;background:#fff;cursor:pointer">
                ${[5,10,20,50].map(n=>`<option value="${n}" ${n===eng.pageSize?'selected':''}>${n} / page</option>`).join('')}
            </select>
        </div>
    </div>`;
}

function applyFilters() {
    const q      = (document.getElementById('pr-search')?.value    || '').toLowerCase();
    const type   =  document.getElementById('type-filter')?.value   || '';
    const status =  document.getElementById('status-filter')?.value || '';
    const plant  =  document.getElementById('plant-filter')?.value  || '';
    const dept   = isPurchasing ? (document.getElementById('dept-filter')?.value || '') : '';
    const vendor = isPurchasing ? (document.getElementById('vendor-filter')?.value || '') : '';
    const startDateVal = document.getElementById('start-date-filter')?.value || '';
    const endDateVal   = document.getElementById('end-date-filter')?.value || '';

    const tbody   = document.getElementById('pr-tbody');
    const allRows = Array.from(tbody.querySelectorAll('tr[data-status]'));
    const emptyRow= document.getElementById('pr-empty');

    let filtered = allRows.filter(r => {
        if (status && r.dataset.status !== status) return false;
        if (type   && r.dataset.type   !== type)   return false;
        if (dept   && r.dataset.dept   !== dept)   return false;
        if (plant  && r.dataset.plant  !== plant)  return false;
        if (vendor && (r.dataset.vendor || '') !== vendor) return false;
        if (q      && !r.textContent.toLowerCase().includes(q)) return false;
        
        if (startDateVal || endDateVal) {
            const rDate = r.dataset.date;
            if (startDateVal && rDate < startDateVal) return false;
            if (endDateVal   && rDate > endDateVal)   return false;
        }
        
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
    renderPager('pr-pager', prEng, filtered.length, start, end, pages);
}

function prSortFn(col) {
    if (prEng.sortCol === col) {
        prEng.sortDir = prEng.sortDir === 'asc' ? 'desc' : 'asc';
    } else {
        prEng.sortCol = col; prEng.sortDir = 'asc';
    }
    document.querySelectorAll('[id^="prs"]').forEach(el => el.textContent = '↕');
    const el = document.getElementById('prs' + col);
    if (el) el.textContent = prEng.sortDir === 'asc' ? '↑' : '↓';
    applyFilters();
}
function prGoto(p)   { prEng.page = p;               applyFilters(); }
function prPageSz(s) { prEng.pageSize = parseInt(s); prEng.page = 1; applyFilters(); }

// ── Open Detail Modal ─────────────────────────────────────────────────────────
function openPRDetail(id, category) {
    const pr = allPRs[category + '_' + id];
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
    if (pr.plant)      subParts.push(pr.plant);
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
        <div><span style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Plant</span>
             <div style="font-weight:600;font-size:12.5px;margin-top:2px">${pr.plant || '—'}</div></div>
        <div><span style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Status</span>
             <div style="font-weight:600;font-size:12.5px;margin-top:2px">${statusLabel}</div></div>
        ${isService ? '' : `<div><span style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em">Department</span>
             <div style="font-weight:600;font-size:12.5px;margin-top:2px">${pr.department || '—'}</div></div>`}`;

    document.getElementById('detail-select-vendor-btn').style.display = 'none';
    document.getElementById('detail-add-quotation-btn').style.display = 'none';
    document.getElementById('detail-generate-link-btn').style.display = 'none';
    const approveForm = document.getElementById('detail-approve-form');
    const rejectForm = document.getElementById('detail-reject-form');
    const cancelForm = document.getElementById('detail-cancel-form');
    const reopenForm = document.getElementById('detail-reopen-form');
    if (reopenForm) reopenForm.style.display = 'none';
    if (approveForm) approveForm.style.display = 'none';
    if (rejectForm) rejectForm.style.display = 'none';
    if (cancelForm) cancelForm.style.display = 'none';

    if (pr.status === 'vendor_selection' || pr.status === 'vendor_search') {
        document.getElementById('detail-select-vendor-btn').style.display = 'inline-flex';
        document.getElementById('detail-select-vendor-btn').href = `/vendor-selection?key=${category}_${id}`;
    }

    // ── Attachment form + Edit Items button visibility ──
    const attachForm = document.getElementById('detail-attachment-form');
    const editItemsBtn = document.getElementById('detail-edit-items-btn');
    if (attachForm) {
        attachForm.style.display = 'none';
        document.getElementById('attach-pr-id').value = pr.id;
        document.getElementById('attach-pr-type').value = category;
    }
    if (editItemsBtn) editItemsBtn.style.display = 'none';

    // Non-purchasing (User) can always attach files
    if (!isPurchasing && attachForm) {
        attachForm.style.display = 'inline-flex';
        attachForm.style.alignItems = 'center';
        attachForm.style.gap = '8px';
    }
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
                document.getElementById('detail-add-quotation-btn').style.display = 'inline-flex';
                document.getElementById('detail-add-quotation-btn').href = `/rfq/${rfqId}/quotations/create`;
                document.getElementById('detail-generate-link-btn').style.display = 'inline-flex';
                document.getElementById('detail-generate-link-btn').dataset.rfq = rfqId;
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
                        ${vs && vs.spec && vs.spec.toLowerCase() !== (it.specification||'').toLowerCase() ? `<div style="background:#fef3c7;color:#b45309;padding:1px 4px;border-radius:3px;font-size:8.5px;font-weight:800;display:inline-block;margin-top:2px" title="Original PR Spec: ${it.specification || '-'}">DIFFERS</div>` : ''}
                    </td>
                    <td style="${tdS};text-align:right;font-weight:600;color:#111827">${it.quantity || 0}</td>
                    <td style="${tdS};color:#6b7280">
                        ${vs && vs.unit ? vs.unit : (it.unit || '-')}
                        ${vs && vs.unit && vs.unit.toLowerCase() !== (it.unit||'').toLowerCase() ? `<div style="background:#fef3c7;color:#b45309;padding:1px 4px;border-radius:3px;font-size:8.5px;font-weight:800;display:inline-block;margin-top:2px" title="Original PR Unit: ${it.unit || '-'}">DIFFERS</div>` : ''}
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
            rows += `<tr>
                <td style="${tdS}">${i+1}</td>
                <td style="${tdS};font-family:monospace;color:#3b5bdb;font-weight:600">${it.item_id || '—'}</td>
                <td style="${tdS};font-weight:500;color:#111827">${it.item_name || it.name || '—'}</td>
                <td style="${tdS};color:#6b7280;font-size:11.5px">
                    ${it.item_notes || '—'}
                    ${vs && vs.notes && vs.notes !== 'Selected' ? `<div style="background:#fef3c7;color:#b45309;padding:1px 4px;border-radius:3px;font-size:8.5px;font-weight:800;display:inline-block;margin-top:2px">VENDOR NOTE</div>` : ''}
                </td>
                <td style="${tdS};color:#6b7280;font-size:11.5px">
                    ${vs && vs.brand ? vs.brand : (it.brand || '—')}
                    ${vs && vs.brand && vs.brand.toLowerCase() !== (it.brand||'').toLowerCase() ? `<div style="background:#fef3c7;color:#b45309;padding:1px 4px;border-radius:3px;font-size:8.5px;font-weight:800;display:inline-block;margin-top:2px" title="Original PR Brand: ${it.brand || '-'}">DIFFERS</div>` : ''}
                </td>
                <td style="${tdS};text-align:right;font-weight:600">${it.quantity || 0}</td>
                <td style="${tdS};color:#6b7280">
                    ${vs && vs.unit ? vs.unit : (it.unit || '—')}
                    ${vs && vs.unit && vs.unit.toLowerCase() !== (it.unit||'').toLowerCase() ? `<div style="background:#fef3c7;color:#b45309;padding:1px 4px;border-radius:3px;font-size:8.5px;font-weight:800;display:inline-block;margin-top:2px" title="Original PR Unit: ${it.unit || '-'}">DIFFERS</div>` : ''}
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
                <td colspan="${isAdmin ? 8 : 7}" style="padding:9px 10px;text-align:right;font-size:12px;font-weight:700;color:#374151">Total Request Value</td>
                <td colspan="3" style="padding:9px 10px;text-align:right;font-family:monospace;font-size:13px;font-weight:800;color:#111827">${fmtRp(grandTotal)}</td>
               </tr>` : '';

        tableHtml = `<div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:4px">
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:${hasPriceCol?'800px':'460px'}">
                    <thead><tr>
                        <th style="${thS}">NO</th>
                        <th style="${thS}">ITEM ID</th>
                        <th style="${thS}">ITEM NAME</th>
                        <th style="${thS}">NOTES</th>
                        <th style="${thS}">BRAND</th>
                        <th style="${thS};text-align:right">QTY</th>
                        <th style="${thS}">UNIT</th>
                        ${gTh}
                        ${isAdmin ? `<th style="${thS};width:150px">ADMIN NOTES</th>` : ''}
                        ${purchNotesTh}
                    </tr></thead>
                    <tbody>${rows || '<tr><td colspan="${isAdmin ? 8 : 7}" style="text-align:center;padding:16px;color:#9ca3af">No items</td></tr>'}</tbody>
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
                    <div><div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600;margin-bottom:3px">Plant</div>
                         <div style="font-weight:500;font-size:12.5px">${pr.plant || '—'}</div></div>
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
    document.getElementById('pr-detail-modal').style.display = 'flex';
}

function closePRDetail() { document.body.style.overflow = ''; document.getElementById('pr-detail-modal').style.display = 'none'; }

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
                if (allPRs[allPRKey]) allPRs[allPRKey].purchasing_notes = value;
            }
        });
    }, 800);
}

// ── Edit Items Modal ──
let _editPR = null;
function openEditItemsModal() {
    if (!_editPR) return;
    const pr = _editPR;
    const items = pr.items || [];
    let rows = items.map((it, i) => `
        <tr>
            <td style="padding:8px 10px;font-size:12px;color:#6b7280">${i+1}</td>
            <td style="padding:8px 10px;font-size:12px;font-weight:600;color:#111827">${it.item_name}</td>
            <td style="padding:8px 10px">
                <input type="number" min="1" value="${it.quantity}" id="edit-qty-${it.id}" style="width:80px;padding:4px 8px;border:1px solid #d1d5db;border-radius:4px;font-size:12px;">
            </td>
            <td style="padding:8px 10px">
                <select id="edit-unit-${it.id}" style="padding:4px 8px;border:1px solid #d1d5db;border-radius:4px;font-size:12px;">
                    ${['Pcs','Unit','Box','Kg','Liter','Meter','Roll','Set','Lot','Jasa','Pack'].map(u => `<option value="${u}" ${it.unit === u ? 'selected' : ''}>${u}</option>`).join('')}
                </select>
            </td>
        </tr>
    `).join('');

    document.getElementById('edit-items-modal-body').innerHTML = `
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead><tr style="background:#f9fafb">
                <th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:#9ca3af">NO</th>
                <th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:#9ca3af">ITEM NAME</th>
                <th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:#9ca3af">QTY</th>
                <th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:#9ca3af">UNIT</th>
            </tr></thead>
            <tbody>${rows}</tbody>
        </table>
    `;
    document.getElementById('edit-items-pr-id').value = pr.id;
    document.getElementById('edit-items-pr-type').value = pr.type || 'goods';
    document.getElementById('edit-items-modal').style.display = 'flex';
}

document.addEventListener('DOMContentLoaded', () => {
    applyFilters();
    
    // Auto-open PR if requested via query string (e.g. from Notifications)
    const urlParams = new URLSearchParams(window.location.search);
    const openPrId = urlParams.get('open_pr');
    const category = urlParams.get('category');
    if (openPrId && category) {
        openPRDetail(openPrId, category);
    }
});
</script>

{{-- ── EDIT ITEMS MODAL ── --}}
<div id="edit-items-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:300;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:560px;box-shadow:0 8px 40px rgba(0,0,0,.15);overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:14px;font-weight:700;color:#111827">Edit Items (Quotation Reopen)</div>
            <button onclick="document.getElementById('edit-items-modal').style.display='none'" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af">&times;</button>
        </div>
        <div style="padding:16px 20px;">
            <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:6px;padding:8px 12px;font-size:12px;color:#92400e;margin-bottom:14px;">⚠️ Perubahan akan memperbarui Qty dan Unit item. Quotation sebelumnya akan tetap, namun vendor akan mendapat notifikasi.</div>
            <form id="edit-items-form" method="POST" action="{{ route('pr.update_items') }}">
                @csrf
                <input type="hidden" name="id" id="edit-items-pr-id">
                <input type="hidden" name="type" id="edit-items-pr-type">
                <div id="edit-items-modal-body"></div>
                <div style="margin-top:14px;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" onclick="document.getElementById('edit-items-modal').style.display='none'" style="padding:7px 18px;border:1px solid #d1d5db;border-radius:7px;background:#fff;font-size:13px;cursor:pointer;color:#374151">Cancel</button>
                    <button type="submit" style="padding:7px 18px;background:#3b5bdb;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection