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
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Vendors Used</div>
        <div style="font-size:28px;font-weight:800;color:#111827;margin:8px 0 5px;line-height:1">{{ $vendorsUsed }}</div>
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

{{-- TABLE 1: Selected Vendors --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;gap:10px;flex-wrap:wrap">
        <div style="font-size:14px;font-weight:700;color:#111827">Selected Vendor Directory</div>
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
                @php
                    $locations = collect($vendors)->pluck('vendor_city')->filter()->unique()->sort()->values();
                @endphp
                @foreach($locations as $loc)
                    <option value="{{ $loc }}">{{ $loc }}</option>
                @endforeach
            </select>
            <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
        <div style="position:relative;">
            <select id="plant-filter" onchange="applyHFilters()"
                style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit;">
                <option value="">All Plants</option>
                @php
                    $allPlants = collect($vendors)->flatMap(fn($v) => collect($v['history'] ?? [])->pluck('plant'))->filter()->unique()->sort()->values();
                @endphp
                @foreach($allPlants as $p)
                    <option value="{{ $p }}">{{ $p }}</option>
                @endforeach
            </select>
            <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
        <div style="position:relative;">
            <select id="value-filter" onchange="applyHFilters()"
                style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit;">
                <option value="">All Values</option>
                <option value="low">< Rp 1 Jt</option>
                <option value="mid">Rp 1 Jt – 50 Jt</option>
                <option value="high">> Rp 50 Jt</option>
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
                    <th onclick="histSort(0)" style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">VENDOR NAME <span id="hs0" style="font-size:9px;">↕</span></th>
                    <th onclick="histSort(1)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">LAST PURCHASE <span id="hs1" style="font-size:9px;">↕</span></th>
                    <th onclick="histSort(2)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">TOTAL VALUE (RP) <span id="hs2" style="font-size:9px;">↕</span></th>
                    <th style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">ACTION</th>
                </tr>
            </thead>
            <tbody id="hist-tbody">
                @forelse($vendors as $idx => $vendor)
                <tr style="border-bottom:1px solid #f3f4f6"
                    data-location="{{ $vendor['vendor_city'] }}"
                    data-date="{{ $vendor['last_purchase'] }}"
                    data-value="{{ $vendor['total_value'] }}"
                    onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px">
                        <div style="font-size:12.5px;font-weight:600;color:#111827">{{ $vendor['vendor_name'] }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $vendor['vendor_city'] }}</div>
                    </td>
                    <td style="padding:13px 14px;font-size:12.5px;color:#374151">{{ $vendor['last_purchase'] }}</td>
                    <td style="padding:13px 14px;font-size:12.5px;font-weight:600;color:#111827">{{ number_format($vendor['total_value'],0,',','.') }}</td>
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
    const location = document.getElementById('period-filter')?.value || '';
    const valueRange = document.getElementById('value-filter')?.value || '';
    const dStart = document.getElementById('hist-start-date')?.value;
    const dEnd   = document.getElementById('hist-end-date')?.value;

    let rows = Array.from(document.querySelectorAll('#hist-tbody tr:not(#hist-empty)'));
    let filtered = rows.filter(r => {
        if (q && !r.textContent.toLowerCase().includes(q)) return false;
        if (location && (r.dataset.location || '') !== location) return false;
        if (dStart && r.dataset.date < dStart) return false;
        if (dEnd && r.dataset.date > dEnd) return false;
        if (valueRange) {
            const val = parseFloat(r.dataset.value || '0');
            if (valueRange === 'low' && val >= 1000000) return false;
            if (valueRange === 'mid' && (val < 1000000 || val > 50000000)) return false;
            if (valueRange === 'high' && val <= 50000000) return false;
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
    document.querySelectorAll('[id^="hs"]:not([id^="hs2-"])').forEach(el => el.textContent = '↕');
    const el = document.getElementById('hs'+col);
    if (el) el.textContent = histSortState.dir==='asc'?'↑':'↓';
    applyHFilters();
}
function histGoto(p) { histPage = p; applyHFilters(); }
function histSetPageSize(s) { histPageSize = parseInt(s); histPage = 1; applyHFilters(); }

document.addEventListener('DOMContentLoaded', () => { applyHFilters(); });
</script>
@endsection