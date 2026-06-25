@extends('layouts.app')
@php $pageTitle = 'Master Vendor'; @endphp
@section('content')

<div style="margin-bottom:20px">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Master Vendor</h1>
    <p style="font-size:12.5px;color:#6b7280;margin:0">Complete list of all registered vendors in the system.</p>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-top:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;gap:10px;flex-wrap:wrap">
        <div style="font-size:14px;font-weight:700;color:#111827">All Vendors</div>
    </div>

    {{-- Toolbar --}}
    <div style="display:flex;gap:8px;align-items:center;padding:12px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;">
        <input type="text" id="hist-search" placeholder="Search vendor..."
            oninput="applyHFilters()"
            style="height:32px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;font-size:12.5px;width:200px;outline:none;font-family:inherit;">
        <div style="position:relative;">
            <select id="period-filter" onchange="applyHFilters()"
                style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit;">
                <option value="">All Locations</option>
                @foreach($locations as $loc)
                    <option value="{{ $loc }}">{{ $loc }}</option>
                @endforeach
            </select>
            <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
        <div style="display:flex;align-items:center;gap:6px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;height:32px;">
            <span style="font-size:10.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Date Range</span>
            <input type="date" id="hist-start-date" onchange="applyHFilters()" style="border:none;background:transparent;font-size:12.5px;outline:none;color:#111827;cursor:pointer;padding:0;font-family:inherit;">
            <span style="color:#9ca3af;font-size:11px;">to</span>
            <input type="date" id="hist-end-date" onchange="applyHFilters()" style="border:none;background:transparent;font-size:12.5px;outline:none;color:#111827;cursor:pointer;padding:0;font-family:inherit;">
        </div>
    </div>

    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb">
                    <th onclick="histSort(0)" style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">VENDOR NAME <span id="hs0" style="font-size:9px;">↕</span></th>
                    <th onclick="histSort(1)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">LAST SUBMITTED <span id="hs1" style="font-size:9px;">↕</span></th>
                    <th onclick="histSort(2)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">QUOTATIONS COUNT <span id="hs2" style="font-size:9px;">↕</span></th>
                    <th style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">ACTION</th>
                </tr>
            </thead>
            <tbody id="hist-tbody">
                @forelse($masterVendors as $idx => $vendor)
                <tr style="border-bottom:1px solid #f3f4f6"
                    data-location="{{ $vendor['vendor_city'] }}"
                    data-date="{{ $vendor['last_submitted'] }}"
                    data-value="0"
                    onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px">
                        <div style="font-size:12.5px;font-weight:600;color:#111827">{{ $vendor['vendor_name'] }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $vendor['vendor_city'] }}</div>
                    </td>
                    <td style="padding:13px 14px;font-size:12.5px;color:#374151">{{ $vendor['last_submitted'] }}</td>
                    <td style="padding:13px 14px;font-size:12.5px;font-weight:600;color:#111827">{{ $vendor['quotation_count'] }}</td>
                    <td style="padding:13px 20px"><button onclick="window.location.href='{{ route('history.vendor.detail', $vendor['vendor_id']) }}'" style="padding:4px 10px;font-size:11.5px;font-weight:600;color:#374151;background:#fff;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer">Detail</button></td>
                </tr>
                @empty
                <tr id="hist-empty"><td colspan="4" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No vendor records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="hist-pager" style="padding:12px 20px;border-top:1px solid #f3f4f6;"></div>
</div>

<script>
let histSortState = { col: null, dir: 'asc' };
let histPage = 1, histPageSize = 10;

function applyHFilters() {
    const q = (document.getElementById('hist-search')?.value || '').toLowerCase();
    const location = document.getElementById('period-filter')?.value || '';
    const dStart = document.getElementById('hist-start-date')?.value;
    const dEnd   = document.getElementById('hist-end-date')?.value;

    let rows = Array.from(document.querySelectorAll('#hist-tbody tr:not(#hist-empty)'));
    let filtered = rows.filter(r => {
        if (q && !r.textContent.toLowerCase().includes(q)) return false;
        if (location && (r.dataset.location || '') !== location) return false;
        if (dStart && r.dataset.date < dStart) return false;
        if (dEnd && r.dataset.date > dEnd) return false;
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

document.addEventListener('DOMContentLoaded', () => { applyHFilters(); });
</script>
@endsection
