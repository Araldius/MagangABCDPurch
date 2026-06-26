@extends('layouts.app')
@php 
$pageTitle = 'Procurement History'; 
$sd = request('start_date');
$ed = request('end_date');
if ($sd && $ed) $rangeText = \Carbon\Carbon::parse($sd)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($ed)->format('d M Y');
elseif ($sd) $rangeText = 'Since ' . \Carbon\Carbon::parse($sd)->format('d M Y');
elseif ($ed) $rangeText = 'Until ' . \Carbon\Carbon::parse($ed)->format('d M Y');
else $rangeText = 'All Time';
@endphp
@section('content')

<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:20px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Procurement History</h1>
        <p style="font-size:12.5px;color:#6b7280;margin:0">All selected vendors and completed procurement records.</p>
    </div>
</div>
 
{{-- STAT CARDS --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Total Items</div>
        <div style="font-size:28px;font-weight:800;color:#111827;margin:8px 0 5px;line-height:1">{{ count($items) }}</div>
        <div style="font-size:11.5px;color:#9ca3af">{{ $rangeText }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Total Value</div>
        <div style="font-size:22px;font-weight:800;color:#111827;margin:8px 0 5px;line-height:28px">Rp {{ number_format($totalValue/1000000,0) }} Jt</div>
        <div style="font-size:11.5px;color:#9ca3af">{{ $rangeText }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Request Completed</div>
        <div style="font-size:28px;font-weight:800;color:#16a34a;margin:8px 0 5px;line-height:1">{{ $prsCompleted }}</div>
        <div style="font-size:11.5px;color:#9ca3af">{{ $rangeText }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Avg. Lead Time</div>
        <div style="font-size:28px;font-weight:800;color:#d97706;margin:8px 0 5px;line-height:1">{{ $avgLeadDays }} Days</div>
        <div style="font-size:11.5px;color:#9ca3af">Request to goods received</div>
    </div>
</div>

{{-- TABLE --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;gap:10px;flex-wrap:wrap">
        <div style="font-size:14px;font-weight:700;color:#111827">Item Catalog</div>
    </div>

    {{-- Toolbar --}}
    <div style="display:flex;gap:8px;align-items:center;padding:12px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;">
        <input type="text" id="hist-search" placeholder="Search Item..."
            oninput="applyHFilters()"
            style="height:32px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;font-size:12.5px;width:200px;outline:none;font-family:inherit;">
        <div style="position:relative;">
            <select id="period-filter" onchange="applyHFilters()"
                style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit;">
                <option value="">All Periods</option>
                @php
                    $periods = collect($items)->pluck('last_purchase')->filter()->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m'))->unique()->sortDesc()->values();
                @endphp
                @foreach($periods as $p)
                    <option value="{{ $p }}">{{ \Carbon\Carbon::parse($p.'-01')->format('M Y') }}</option>
                @endforeach
            </select>
            <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
        <div style="position:relative;">
            <select id="value-filter" onchange="applyHFilters()"
                style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit;">
                <option value="">All Values</option>
                <option value="low">< Rp 1 Jt</option>
                <option value="mid">Rp 1 Jt – 10 Jt</option>
                <option value="high">> Rp 10 Jt</option>
            </select>
            <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
        <div style="display:flex;align-items:center;gap:6px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;height:32px;">
            <span style="font-size:10.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Date Range</span>
            <input type="date" id="hist-start-date" value="{{ request('start_date') }}" onchange="updateHistoryRange()" style="border:none;background:transparent;font-size:12.5px;outline:none;color:#111827;cursor:pointer;padding:0;font-family:inherit;">
            <span style="color:#9ca3af;font-size:11px;">to</span>
            <input type="date" id="hist-end-date" value="{{ request('end_date') }}" onchange="updateHistoryRange()" style="border:none;background:transparent;font-size:12.5px;outline:none;color:#111827;cursor:pointer;padding:0;font-family:inherit;">
        </div>
    </div>

    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb">
                    <th onclick="histSort(0)" style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">ITEM ID <span id="hs0" style="font-size:9px;">↕</span></th>
                    <th onclick="histSort(1)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">ITEM NAME <span id="hs1" style="font-size:9px;">↕</span></th>
                    <th onclick="histSort(2)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">LAST PURCHASE <span id="hs2" style="font-size:9px;">↕</span></th>
                    <th onclick="histSort(3)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">LAST VALUE (RP) <span id="hs3" style="font-size:9px;">↕</span></th>
                    <th style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">ACTION</th>
                </tr>
            </thead>
            <tbody id="hist-tbody">
                @forelse($items as $idx => $item)
                <tr style="border-bottom:1px solid #f3f4f6"
                    data-period="{{ $item['last_purchase'] ? \Carbon\Carbon::parse($item['last_purchase'])->format('Y-m') : '' }}"
                    data-date="{{ $item['last_purchase'] }}"
                    data-value="{{ $item['last_value'] }}"
                    onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px"><span style="font-family:'Courier New',monospace;font-size:12px;font-weight:600;color:#111827">{{ $item['item_id'] }}</span></td>
                    <td style="padding:13px 14px;font-size:12.5px;font-weight:600;color:#111827">{{ $item['item_name'] }}</td>
                    <td style="padding:13px 14px;font-size:12.5px;color:#374151">{{ $item['last_purchase'] }}</td>
                    <td style="padding:13px 14px;font-size:12.5px;font-weight:600;color:#111827">{{ number_format($item['last_value'],0,',','.') }}</td>
                    <td style="padding:13px 20px"><button onclick="openItemDetail({{ $idx }})" style="padding:4px 10px;font-size:11.5px;font-weight:600;color:#374151;background:#fff;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer">Detail</button></td>
                </tr>
                @empty
                <tr id="hist-empty"><td colspan="5" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No item records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="hist-pager" style="padding:12px 20px;border-top:1px solid #f3f4f6;"></div>
</div>

{{-- MODAL ITEM HISTORY --}}
<div id="item-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(17,24,39,0.4);z-index:999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px)">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:1200px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1)">
        <div style="padding:16px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center">
            <h2 style="font-size:14px;font-weight:700;color:#111827;margin:0" id="item-modal-title">Item History —</h2>
            <button onclick="closeItemDetail()" style="background:none;border:none;color:#9ca3af;cursor:pointer;font-size:20px;line-height:1">×</button>
        </div>
        <div style="padding:0;overflow-y:auto;flex:1">
            <table style="width:100%;border-collapse:collapse;font-size:12.5px">
                <thead>
                    <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">DOC NO.</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM NAME</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">VENDOR</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">VALUE (RP)</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">QTY</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">UNIT</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">SPEC</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">REQUESTED BY</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">LEAD TIME</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">REQ DATE</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ACTION</th>
                    </tr>
                </thead>
                <tbody id="item-modal-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
let itemsData = @json($items);

function openItemDetail(idx) {
    const item = itemsData[idx];
    if (!item) return;
    document.getElementById('item-modal-title').innerText = `Item History — ${item.item_id}`;
    let tbody = '';
    item.history.forEach(h => {
        tbody += `
        <tr style="border-bottom:1px solid #f3f4f6">
        <td style="padding:10px 14px;font-family:monospace;font-weight:600;font-size:11px">${h.doc_no || '-'}</td>
            <td style="padding:10px 14px;font-weight:600">${h.item_name}</td>
            <td style="padding:10px 14px">
                <div style="font-weight:600">${h.vendor}</div>
                <div style="font-size:10.5px;color:#9ca3af">${h.vendor_city}</div>
            </td>
            <td style="padding:10px 14px;font-weight:700">${new Intl.NumberFormat('id-ID').format(h.value)}</td>
            <td style="padding:10px 14px">${h.qty}</td>
            <td style="padding:10px 14px">${h.unit}</td>
            <td style="padding:10px 14px;font-size:11.5px;color:#6b7280;max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="${h.spec}">${h.spec}</td>
            <td style="padding:10px 14px">${h.requested_by}</td>
            <td style="padding:10px 14px">${h.lead_time}</td>
            <td style="padding:10px 14px">${h.req_date}</td>
            <td style="padding:10px 14px"><button onclick="window.location.href='/procurement-history/orders?search=${h.doc_no || ''}'" style="padding:4px 10px;font-size:11.5px;font-weight:600;color:#374151;background:#fff;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer">Detail</button></td>
        </tr>
        `;
    });
    document.getElementById('item-modal-tbody').innerHTML = tbody;
    document.getElementById('item-modal').style.display = 'flex';
}
function closeItemDetail() {
    document.getElementById('item-modal').style.display = 'none';
}

let histSortState = { col: null, dir: 'asc' };
let histPage = 1, histPageSize = 10;

function updateHistoryRange() {
    const s = document.getElementById('hist-start-date').value;
    const e = document.getElementById('hist-end-date').value;
    let url = new URL(window.location.href);
    if (s) url.searchParams.set('start_date', s); else url.searchParams.delete('start_date');
    if (e) url.searchParams.set('end_date', e); else url.searchParams.delete('end_date');
    window.location.href = url.toString();
}

function applyHFilters() {
    const q = (document.getElementById('hist-search')?.value || '').toLowerCase();
    const period = document.getElementById('period-filter')?.value || '';
    const valueRange = document.getElementById('value-filter')?.value || '';
    const dStart = document.getElementById('hist-start-date')?.value;
    const dEnd   = document.getElementById('hist-end-date')?.value;

    let rows = Array.from(document.querySelectorAll('#hist-tbody tr:not(#hist-empty)'));
    let filtered = rows.filter(r => {
        if (q && !r.textContent.toLowerCase().includes(q)) return false;
        if (period && (r.dataset.period || '') !== period) return false;
        if (dStart && r.dataset.date < dStart) return false;
        if (dEnd && r.dataset.date > dEnd) return false;
        if (valueRange) {
            const val = parseFloat(r.dataset.value || '0');
            if (valueRange === 'low' && val >= 1000000) return false;
            if (valueRange === 'mid' && (val < 1000000 || val > 10000000)) return false;
            if (valueRange === 'high' && val <= 10000000) return false;
        }
        return true;
    });

    if (histSortState.col !== null) {
        filtered.sort((a, b) => {
            const at = (a.querySelectorAll('td')[histSortState.col]?.textContent || '').trim();
            const bt = (b.querySelectorAll('td')[histSortState.col]?.textContent || '').trim();
            const an = parseFloat(at.replace(/[^0-9.]/g,'')), bn = parseFloat(bt.replace(/[^0-9.]/g,''));
            const cmp = (!isNaN(an)&&!isNaN(bn)) ? an-bn : at.localeCompare(bt,'id');
            return histSortState.dir === 'asc' ? cmp : -cmp;
        });
    }

    rows.forEach(r => r.style.display = 'none');
    const empty = document.getElementById('hist-empty');
    const pages = Math.max(1, Math.ceil(filtered.length / histPageSize));
    if (histPage > pages) histPage = 1;
    const start = (histPage - 1) * histPageSize;
    const end   = Math.min(histPage * histPageSize, filtered.length);
    const tbody = document.getElementById('hist-tbody');
    filtered.slice(start, end).forEach(r => { r.style.display = ''; tbody.appendChild(r); });
    if (empty) empty.style.display = filtered.length === 0 ? '' : 'none';

    let btns = '';
    for (let i = 1; i <= pages; i++) {
        btns += `<button onclick="histGoto(${i})" style="min-width:28px;height:28px;border-radius:6px;border:1px solid ${i===histPage?'#111827':'#e5e7eb'};background:${i===histPage?'#111827':'#fff'};color:${i===histPage?'#fff':'#374151'};font-size:12px;font-weight:600;cursor:pointer;padding:0 6px;">${i}</button>`;
    }
    const pager = document.getElementById('hist-pager');
    if (pager) pager.innerHTML = `<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <span style="font-size:12px;color:#6b7280;">${filtered.length===0?'No results':`Showing ${start+1}–${end} of ${filtered.length}`}</span>
        <div style="display:flex;gap:4px;">
            <button onclick="histGoto(${histPage-1})" ${histPage<=1?'disabled':''} style="min-width:28px;height:28px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-size:13px;opacity:${histPage<=1?.35:1};">‹</button>
            ${btns}
            <button onclick="histGoto(${histPage+1})" ${histPage>=pages?'disabled':''} style="min-width:28px;height:28px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-size:13px;opacity:${histPage>=pages?.35:1};">›</button>
        </div>
        <select onchange="histSetPageSize(this.value)" style="height:28px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;padding:0 6px;background:#fff;">
            ${[5,10,20].map(n=>`<option value="${n}" ${n===histPageSize?'selected':''}>${n} / page</option>`).join('')}
        </select>
    </div>`;
}

function histSort(col) {
    if (histSortState.col === col) histSortState.dir = histSortState.dir==='asc'?'desc':'asc';
    else { histSortState.col = col; histSortState.dir = 'asc'; }
    document.querySelectorAll('[id^="hs"]').forEach(el => el.textContent = '↕');
    const el = document.getElementById('hs'+col);
    if (el) el.textContent = histSortState.dir==='asc'?'↑':'↓';
    applyHFilters();
}
function histGoto(p) { histPage = p; applyHFilters(); }
function histSetPageSize(s) { histPageSize = parseInt(s); histPage = 1; applyHFilters(); }

document.addEventListener('DOMContentLoaded', applyHFilters);
</script>
@endsection