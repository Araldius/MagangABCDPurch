@extends('layouts.app')
@php $pageTitle = 'Procurement History'; @endphp
@section('content')

<div style="margin-bottom:20px">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Procurement History</h1>
    <p style="font-size:12.5px;color:#6b7280;margin:0">All selected vendors and completed procurement records.</p>
</div>
 
{{-- STAT CARDS --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Vendors Used</div>
        <div style="font-size:28px;font-weight:800;color:#111827;margin:8px 0 5px;line-height:1">{{ $vendorsUsed }}</div>
        <div style="font-size:11.5px;color:#9ca3af">Throughout {{ now()->year }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Total Value</div>
        <div style="font-size:22px;font-weight:800;color:#111827;margin:8px 0 5px;line-height:1">Rp {{ number_format($totalValue/1000000,0) }} Jt</div>
        <div style="font-size:11.5px;color:#9ca3af">Jan–{{ now()->format('M Y') }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">PR Completed</div>
        <div style="font-size:28px;font-weight:800;color:#16a34a;margin:8px 0 5px;line-height:1">{{ $prsCompleted }}</div>
        <div style="font-size:11.5px;color:#9ca3af">Year {{ now()->year }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px">
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Avg. Lead Time</div>
        <div style="font-size:28px;font-weight:800;color:#d97706;margin:8px 0 5px;line-height:1">{{ $avgLeadDays }} Days</div>
        <div style="font-size:11.5px;color:#9ca3af">PR to goods received</div>
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
                    $allVendors = collect($vendors)->merge($unselectedVendors);
                    $locations = collect($allVendors)->pluck('vendor_city')->filter()->unique()->sort()->values();
                @endphp
                @foreach($locations as $loc)
                    <option value="{{ $loc }}">{{ $loc }}</option>
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
                    data-value="{{ $vendor['total_value'] }}"
                    onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px">
                        <div style="font-size:12.5px;font-weight:600;color:#111827">{{ $vendor['vendor_name'] }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $vendor['vendor_city'] }}</div>
                    </td>
                    <td style="padding:13px 14px;font-size:12.5px;color:#374151">{{ $vendor['last_purchase'] }}</td>
                    <td style="padding:13px 14px;font-size:12.5px;font-weight:600;color:#111827">{{ number_format($vendor['total_value'],0,',','.') }}</td>
                    <td style="padding:13px 20px"><button onclick="openVendorDetail({{ $idx }})" style="padding:4px 10px;font-size:11.5px;font-weight:600;color:#374151;background:#fff;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer">Detail</button></td>
                </tr>
                @empty
                <tr id="hist-empty"><td colspan="4" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No vendor records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="hist-pager" style="padding:12px 20px;border-top:1px solid #f3f4f6;"></div>
</div>

{{-- TABLE 2: Participated / Unselected Vendors --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-top:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;gap:10px;flex-wrap:wrap">
        <div style="font-size:14px;font-weight:700;color:#111827">Participated / Unselected Vendors</div>
    </div>

    {{-- Toolbar 2 Khusus untuk tabel Unselected --}}
    <div style="display:flex;gap:8px;align-items:center;padding:12px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;">
        <input type="text" id="hist-search2" placeholder="Search vendor..."
            oninput="applyHFilters2()"
            style="height:32px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;font-size:12.5px;width:200px;outline:none;font-family:inherit;">
        <div style="position:relative;">
            <select id="period-filter2" onchange="applyHFilters2()"
                style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit;">
                <option value="">All Locations</option>
                @foreach($locations as $loc)
                    <option value="{{ $loc }}">{{ $loc }}</option>
                @endforeach
            </select>
            <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
    </div>

    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb">
                    <th onclick="histSort2(0)" style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">VENDOR NAME <span id="hs2-0" style="font-size:9px;">↕</span></th>
                    <th onclick="histSort2(1)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">LAST SUBMITTED <span id="hs2-1" style="font-size:9px;">↕</span></th>
                    <th onclick="histSort2(2)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">QUOTATIONS COUNT <span id="hs2-2" style="font-size:9px;">↕</span></th>
                    <th style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">ACTION</th>
                </tr>
            </thead>
            <tbody id="hist-tbody2">
                @forelse($unselectedVendors as $idx => $vendor)
                <tr style="border-bottom:1px solid #f3f4f6"
                    data-location="{{ $vendor['vendor_city'] }}"
                    data-value="0"
                    onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px">
                        <div style="font-size:12.5px;font-weight:600;color:#111827">{{ $vendor['vendor_name'] }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $vendor['vendor_city'] }}</div>
                    </td>
                    <td style="padding:13px 14px;font-size:12.5px;color:#374151">{{ $vendor['last_submitted'] }}</td>
                    <td style="padding:13px 14px;font-size:12.5px;font-weight:600;color:#111827">{{ $vendor['quotation_count'] }}</td>
                    <td style="padding:13px 20px"><button onclick="openVendorDetail2({{ $idx }})" style="padding:4px 10px;font-size:11.5px;font-weight:600;color:#374151;background:#fff;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer">Detail</button></td>
                </tr>
                @empty
                <tr id="hist-empty2"><td colspan="4" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No unselected vendor records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="hist-pager2" style="padding:12px 20px;border-top:1px solid #f3f4f6;"></div>
</div>

{{-- MODAL VENDOR HISTORY --}}
<div id="vendor-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(17,24,39,0.4);z-index:999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px)">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:1200px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1)">
        <div style="padding:16px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center">
            <h2 style="font-size:14px;font-weight:700;color:#111827;margin:0" id="vendor-modal-title">Vendor Detail —</h2>
            <button onclick="closeVendorDetail()" style="background:none;border:none;color:#9ca3af;cursor:pointer;font-size:20px;line-height:1">×</button>
        </div>
        <div style="padding:0;overflow-y:auto;flex:1">
            <table style="width:100%;border-collapse:collapse;font-size:12.5px">
                <thead>
                    <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM ID</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM NAME</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">VALUE (RP)</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">QTY</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">UNIT</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">SPEC</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">REQUESTED BY</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">LEAD TIME</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">REQ DATE</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">DOC NO.</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ACTION</th>
                    </tr>
                </thead>
                <tbody id="vendor-modal-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
let vendorsData = @json($vendors);
let unselectedVendorsData = @json($unselectedVendors);

function openVendorDetail(idx) {
    const vendor = vendorsData[idx];
    if (!vendor) return;
    document.getElementById('vendor-modal-title').innerText = `Vendor Detail (Selected) — ${vendor.vendor_name}`;
    let tbody = '';
    vendor.history.forEach(h => {
        tbody += `
        <tr style="border-bottom:1px solid #f3f4f6">
            <td style="padding:10px 14px;font-family:monospace;font-weight:600;font-size:11px;color:#3b82f6">${h.item_id}</td>
            <td style="padding:10px 14px;font-weight:600">${h.item_name}</td>
            <td style="padding:10px 14px;font-weight:700">${new Intl.NumberFormat('id-ID').format(h.value)}</td>
            <td style="padding:10px 14px">${h.qty}</td>
            <td style="padding:10px 14px">${h.unit}</td>
            <td style="padding:10px 14px;font-size:11.5px;color:#6b7280;max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="${h.spec}">${h.spec}</td>
            <td style="padding:10px 14px">${h.requested_by}</td>
            <td style="padding:10px 14px">${h.lead_time}</td>
            <td style="padding:10px 14px">${h.req_date}</td>
            <td style="padding:10px 14px;font-family:monospace;font-weight:600;color:#111827">${h.doc_no || '-'}</td>
            <td style="padding:10px 14px"><button onclick="window.location.href='/procurement-history/orders?search=${h.doc_no || ''}'" style="padding:4px 10px;font-size:11.5px;font-weight:600;color:#374151;background:#fff;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer">Detail</button></td>
        </tr>
        `;
    });
    document.getElementById('vendor-modal-tbody').innerHTML = tbody;
    document.getElementById('vendor-modal').style.display = 'flex';
}

function openVendorDetail2(idx) {
    const vendor = unselectedVendorsData[idx];
    if (!vendor) return;
    document.getElementById('vendor-modal-title').innerText = `Vendor Detail (Unselected) — ${vendor.vendor_name}`;
    let tbody = '';
    vendor.history.forEach(h => {
        tbody += `
        <tr style="border-bottom:1px solid #f3f4f6;background:#fffbeb">
            <td style="padding:10px 14px;font-family:monospace;font-weight:600;font-size:11px;color:#3b82f6">${h.item_id}</td>
            <td style="padding:10px 14px;font-weight:600">${h.item_name}</td>
            <td style="padding:10px 14px;font-weight:700">${new Intl.NumberFormat('id-ID').format(h.value)}</td>
            <td style="padding:10px 14px">${h.qty}</td>
            <td style="padding:10px 14px">${h.unit}</td>
            <td style="padding:10px 14px;font-size:11.5px;color:#6b7280;max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="${h.spec}">${h.spec}</td>
            <td style="padding:10px 14px">${h.requested_by}</td>
            <td style="padding:10px 14px">-</td>
            <td style="padding:10px 14px">${h.req_date}</td>
            <td style="padding:10px 14px;font-family:monospace;font-weight:600;color:#111827">${h.doc_no || '-'}</td>
            <td style="padding:10px 14px"><button onclick="window.location.href='/procurement-history/orders?search=${h.doc_no || ''}'" style="padding:4px 10px;font-size:11.5px;font-weight:600;color:#374151;background:#fff;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer">Detail</button></td>
        </tr>
        `;
    });
    document.getElementById('vendor-modal-tbody').innerHTML = tbody;
    document.getElementById('vendor-modal').style.display = 'flex';
}

function closeVendorDetail() {
    document.getElementById('vendor-modal').style.display = 'none';
}

let histSortState = { col: null, dir: 'asc' };
let histPage = 1, histPageSize = 10;
let histSortState2 = { col: null, dir: 'asc' };
let histPage2 = 1, histPageSize2 = 10;

function applyHFilters() {
    const q = (document.getElementById('hist-search')?.value || '').toLowerCase();
    const location = document.getElementById('period-filter')?.value || '';
    const valueRange = document.getElementById('value-filter')?.value || '';

    let rows = Array.from(document.querySelectorAll('#hist-tbody tr:not(#hist-empty)'));
    let filtered = rows.filter(r => {
        if (q && !r.textContent.toLowerCase().includes(q)) return false;
        if (location && (r.dataset.location || '') !== location) return false;
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

function applyHFilters2() {
    const q = (document.getElementById('hist-search2')?.value || '').toLowerCase();
    const location = document.getElementById('period-filter2')?.value || '';

    let rows2 = Array.from(document.querySelectorAll('#hist-tbody2 tr:not(#hist-empty2)'));
    let filtered2 = rows2.filter(r => {
        if (q && !r.textContent.toLowerCase().includes(q)) return false;
        if (location && (r.dataset.location || '') !== location) return false;
        return true;
    });

    if (histSortState2.col !== null) {
        filtered2.sort((a, b) => {
            const at = (a.querySelectorAll('td')[histSortState2.col]?.textContent || '').trim();
            const bt = (b.querySelectorAll('td')[histSortState2.col]?.textContent || '').trim();
            const an = parseFloat(at.replace(/[^0-9.]/g,'')), bn = parseFloat(bt.replace(/[^0-9.]/g,''));
            const cmp = (!isNaN(an)&&!isNaN(bn)) ? an-bn : at.localeCompare(bt,'id');
            return histSortState2.dir === 'asc' ? cmp : -cmp;
        });
    }

    rows2.forEach(r => r.style.display = 'none');
    const empty2 = document.getElementById('hist-empty2');
    const pages2 = Math.max(1, Math.ceil(filtered2.length / histPageSize2));
    if (histPage2 > pages2) histPage2 = 1;
    const start2 = (histPage2 - 1) * histPageSize2;
    const end2   = Math.min(histPage2 * histPageSize2, filtered2.length);
    const tbody2 = document.getElementById('hist-tbody2');
    filtered2.slice(start2, end2).forEach(r => { r.style.display = ''; tbody2.appendChild(r); });
    if (empty2) empty2.style.display = filtered2.length === 0 ? '' : 'none';

    let btns2 = '';
    for (let i = 1; i <= pages2; i++) {
        btns2 += `<button onclick="histGoto2(${i})" style="min-width:28px;height:28px;border-radius:6px;border:1px solid ${i===histPage2?'#111827':'#e5e7eb'};background:${i===histPage2?'#111827':'#fff'};color:${i===histPage2?'#fff':'#374151'};font-size:12px;font-weight:600;cursor:pointer;padding:0 6px;">${i}</button>`;
    }
    const pager2 = document.getElementById('hist-pager2');
    if (pager2) pager2.innerHTML = `<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <span style="font-size:12px;color:#6b7280;">${filtered2.length===0?'No results':`Showing ${start2+1}–${end2} of ${filtered2.length}`}</span>
        <div style="display:flex;gap:4px;">
            <button onclick="histGoto2(${histPage2-1})" ${histPage2<=1?'disabled':''} style="min-width:28px;height:28px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-size:13px;opacity:${histPage2<=1?.35:1};">‹</button>
            ${btns2}
            <button onclick="histGoto2(${histPage2+1})" ${histPage2>=pages2?'disabled':''} style="min-width:28px;height:28px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-size:13px;opacity:${histPage2>=pages2?.35:1};">›</button>
        </div>
        <select onchange="histSetPageSize2(this.value)" style="height:28px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;padding:0 6px;background:#fff;">
            ${[5,10,20].map(n=>`<option value="${n}" ${n===histPageSize2?'selected':''}>${n} / page</option>`).join('')}
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

function histSort2(col) {
    if (histSortState2.col === col) histSortState2.dir = histSortState2.dir==='asc'?'desc':'asc';
    else { histSortState2.col = col; histSortState2.dir = 'asc'; }
    document.querySelectorAll('[id^="hs2-"]').forEach(el => el.textContent = '↕');
    const el = document.getElementById('hs2-'+col);
    if (el) el.textContent = histSortState2.dir==='asc'?'↑':'↓';
    applyHFilters2();
}
function histGoto2(p) { histPage2 = p; applyHFilters2(); }
function histSetPageSize2(s) { histPageSize2 = parseInt(s); histPage2 = 1; applyHFilters2(); }

document.addEventListener('DOMContentLoaded', () => {
    applyHFilters();
    applyHFilters2();
});
</script>
@endsection