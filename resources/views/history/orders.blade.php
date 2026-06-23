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
        <div style="font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em">Total Orders</div>
        <div style="font-size:28px;font-weight:800;color:#111827;margin:8px 0 5px;line-height:1">{{ $records->count() }}</div>
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

{{-- ORDER RECORDS TAB --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;gap:10px;flex-wrap:wrap">
        <div style="font-size:14px;font-weight:700;color:#111827">Order Records</div>
    </div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6">
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <input type="text" id="hist-search" placeholder="Search doc, vendor..." oninput="applyHFilters()" style="height:34px;border:1px solid #d1d5db;border-radius:6px;padding:0 12px;font-size:12.5px;width:220px;outline:none">
            <select id="unit-filter" onchange="applyHFilters()" style="height:34px;border:1px solid #d1d5db;border-radius:6px;padding:0 12px;font-size:12.5px;outline:none;background:#fff">
                <option value="">All Departments</option>
                @foreach($records->pluck('department')->filter()->unique() as $d)
                    <option value="{{ $d }}">{{ $d }}</option>
                @endforeach
            </select>
            <div style="display:flex;align-items:center;gap:6px;background:#f9fafb;border:1px solid #d1d5db;border-radius:6px;padding:0 10px;height:34px;">
                <span style="font-size:10.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Date Range</span>
                <input type="date" id="hist-start-date" onchange="applyHFilters()" style="border:none;background:transparent;font-size:12.5px;outline:none;color:#111827;cursor:pointer;padding:0">
                <span style="color:#9ca3af;font-size:11px;">to</span>
                <input type="date" id="hist-end-date" onchange="applyHFilters()" style="border:none;background:transparent;font-size:12.5px;outline:none;color:#111827;cursor:pointer;padding:0">
            </div>
        </div>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                    <th onclick="histSort(0)" style="padding:12px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer;white-space:nowrap">DOC NO. <span id="hs0">↕</span></th>
                    <th onclick="histSort(1)" style="padding:12px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer">VENDOR <span id="hs1">↕</span></th>
                    <th onclick="histSort(2)" style="padding:12px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer">DEPARTMENT <span id="hs2">↕</span></th>
                    <th onclick="histSort(3)" style="padding:12px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;cursor:pointer">TOTAL VALUE <span id="hs3">↕</span></th>
                    <th style="padding:12px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">LEAD TIME</th>
                    <th style="padding:12px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">COMPLETED DATE</th>
                    <th style="padding:12px 14px;text-align:right;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">ACTION</th>
                </tr>
            </thead>
            <tbody id="hist-tbody">
                @forelse($records as $idx => $r)
                <tr style="border-bottom:1px solid #f3f4f6" data-dept="{{ $r->department }}" data-date="{{ $r->completed_date_raw ?? '' }}">
                    <td style="padding:12px 20px;font-weight:600;color:#111827;font-family:monospace">{{ $r->doc_number }}</td>
                    <td style="padding:12px 14px">
                        <div style="font-weight:600;color:#111827">{{ $r->vendor_name }}</div>
                    </td>
                    <td style="padding:12px 14px;color:#6b7280">{{ $r->department }}</td>
                    <td style="padding:12px 14px;font-family:monospace;font-weight:600;color:#111827">Rp{{ number_format($r->total_value,0,',','.') }}</td>
                    <td style="padding:12px 14px;color:#6b7280">{{ $r->lead_days !== null ? $r->lead_days.' days' : '-' }}</td>
                    <td style="padding:12px 14px;color:#6b7280">{{ $r->completed_date }}</td>
                    <td style="padding:12px 14px;text-align:right">
                        <button onclick="openDetail({{ $idx }})" style="padding:4px 10px;font-size:11.5px;font-weight:600;color:#374151;background:#fff;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer">Detail</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="padding:20px;text-align:center;color:#6b7280;font-size:13px">No completed records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:12px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;background:#f9fafb" id="hist-pager"></div>
</div>


<script>
let histSortState = { col: null, dir: 'asc' };
let histPage = 1, histPageSize = 10;

function applyHFilters() {
    const q      = (document.getElementById('hist-search')?.value || '').toLowerCase();
    const dept   = document.getElementById('unit-filter')?.value || '';
    const status = document.getElementById('hist-status-filter')?.value || '';
    const dStart = document.getElementById('hist-start-date')?.value;
    const dEnd   = document.getElementById('hist-end-date')?.value;

    let rows = Array.from(document.querySelectorAll('#hist-tbody tr[data-dept]'));

    let filtered = rows.filter(r => {
        if (dept   && r.dataset.dept   !== dept)   return false;
        if (status && r.dataset.status !== status) return false;
        if (q && !r.textContent.toLowerCase().includes(q)) return false;
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
        <span style="font-size:12px;color:#6b7280;">${filtered.length===0?'No results':`Showing ${start+1}-${end} of ${filtered.length}`}</span>
        <div style="display:flex;gap:4px;">
            <button onclick="histGoto(${histPage-1})" ${histPage<=1?'disabled':''} style="min-width:28px;height:28px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-size:13px;opacity:${histPage<=1?.35:1};"><</button>
            ${btns}
            <button onclick="histGoto(${histPage+1})" ${histPage>=pages?'disabled':''} style="min-width:28px;height:28px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-size:13px;opacity:${histPage>=pages?.35:1};">></button>
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

document.addEventListener('DOMContentLoaded', () => {
    // Membaca parameter "search" dari URL
    const params = new URLSearchParams(window.location.search);
    if (params.has('search')) {
        const searchInput = document.getElementById('hist-search');
        if (searchInput) {
            // Memasukkan nilai pencarian ke dalam input box
            searchInput.value = params.get('search');
        }
    }
    // Langsung filter tabelnya sesuai nilai yang ada
    applyHFilters();
});

let prData = @json($records->values());

function openDetail(idx) {
    const pr = prData[idx];
    if (!pr) return;

    // Fill Header
    document.getElementById('modal-title-text').innerHTML = `${pr.items[0] ? pr.items[0].name : 'Procurement'} <br><span style="font-size:12px;font-weight:400;color:#6b7280;margin-top:2px;display:block">${pr.doc_number} | ${pr.department}</span>`;

    // Fill Request Info
    document.getElementById('modal-info-submission').textContent = pr.decided_at ? pr.decided_at.split('T')[0] : '-'; // Just as an example, normally submission_date
    document.getElementById('modal-info-department').textContent = pr.department;
    document.getElementById('modal-info-requestedby').textContent = '-'; // If we have user we'd put it here
    
    document.getElementById('modal-info-vendor-label').textContent = 'SELECTED VENDOR';
    document.getElementById('modal-info-vendor-val').textContent = pr.vendor_name;

    document.getElementById('modal-info-val-label').textContent = 'FINAL VALUE';
    document.getElementById('modal-info-val-val').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(pr.total_value);

    document.getElementById('modal-info-date-label').textContent = 'RECEIVED DATE';
    document.getElementById('modal-info-date-val').textContent = pr.decided_at ? pr.decided_at.split('T')[0] : '-'; // using decided_at for received_date mock

    // Items
    let itemHtml = '';
    (pr.items || []).forEach((it, idx) => {
        let uPrice = it.final_price_per_item || 0;
        let tPrice = uPrice * (it.quantity || 0);
        itemHtml += `
            <tr style="border-bottom:1px solid #e5e7eb">
                <td style="padding:10px 14px;font-weight:600;color:#111827">${idx+1}</td>
                <td style="padding:10px 14px;font-weight:700">${it.item_id||it.item_code||'-'}</td>
                <td style="padding:10px 14px;font-weight:600">${it.name||it.item_name||'-'}</td>
                <td style="padding:10px 14px;font-size:11.5px;color:#6b7280">${it.description||'-'}</td>
                <td style="padding:10px 14px;font-size:11.5px;color:#6b7280;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${it.specification||'-'}</td>
                <td style="padding:10px 14px;font-weight:600">${it.quantity}</td>
                <td style="padding:10px 14px">${it.unit}</td>
                <td style="padding:10px 14px;font-weight:600">Rp ${new Intl.NumberFormat('id-ID').format(uPrice)}</td>
                <td style="padding:10px 14px;font-weight:700">Rp ${new Intl.NumberFormat('id-ID').format(tPrice)}</td>
                <td style="padding:10px 14px">${it.vendor_name || pr.vendor_name || '-'}</td>
            </tr>
        `;
    });
    document.getElementById('modal-items-tbody').innerHTML = itemHtml;
    document.getElementById('modal-tot-req-val').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(pr.total_value);

    // Show modal
    document.getElementById('pr-modal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('pr-modal').style.display = 'none';
}
</script>

{{-- Modal HTML --}}
<div id="pr-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(17,24,39,0.4);z-index:999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px)">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:1000px;max-height:95vh;display:flex;flex-direction:column;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1)">
        <div style="padding:16px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:flex-start">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin:0;line-height:1.4" id="modal-title-text"></h2>
            <button onclick="closeModal()" style="background:none;border:none;color:#9ca3af;cursor:pointer;font-size:20px;line-height:1">x</button>
        </div>
        <div style="padding:24px;overflow-y:auto;flex:1">
            <!-- Progress Status -->
            <div style="margin-bottom:30px">
                <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px">Progress Status</div>
                <div style="display:flex;align-items:flex-start;justify-content:space-between;position:relative;padding:0 20px">
                    <div style="position:absolute;top:12px;left:60px;right:60px;height:2px;background:#10b981;z-index:1"></div>
                    <div style="position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;gap:8px;width:80px">
                        <div style="width:26px;height:26px;border-radius:50%;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                        <div style="font-size:11px;font-weight:600;color:#10b981;text-align:center">PR<br>Submitted</div>
                    </div>
                    <div style="position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;gap:8px;width:80px">
                        <div style="width:26px;height:26px;border-radius:50%;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                        <div style="font-size:11px;font-weight:600;color:#10b981;text-align:center">Vendor<br>Search<br><span style="font-weight:500">(Purchasing)</span></div>
                    </div>
                    <div style="position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;gap:8px;width:80px">
                        <div style="width:26px;height:26px;border-radius:50%;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                        <div style="font-size:11px;font-weight:600;color:#10b981;text-align:center">Vendor<br>Selection</div>
                    </div>
                    <div style="position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;gap:8px;width:80px">
                        <div style="width:26px;height:26px;border-radius:50%;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                        <div style="font-size:11px;font-weight:600;color:#10b981;text-align:center">Completed</div>
                    </div>
                </div>
            </div>

            <!-- Request Information -->
            <div style="margin-bottom:24px">
                <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px">Request Information</div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
                    <div>
                        <div style="font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:2px">Submission Date</div>
                        <div style="font-size:12.5px;font-weight:600;color:#111827" id="modal-info-submission"></div>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:2px">Department</div>
                        <div style="font-size:12.5px;font-weight:600;color:#111827" id="modal-info-department"></div>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:2px">Requested By</div>
                        <div style="font-size:12.5px;font-weight:600;color:#111827" id="modal-info-requestedby"></div>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:2px" id="modal-info-vendor-label">Selected Vendor</div>
                        <div style="font-size:12.5px;font-weight:600;color:#111827" id="modal-info-vendor-val"></div>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:2px" id="modal-info-val-label">Final Value</div>
                        <div style="font-size:12.5px;font-weight:600;color:#111827" id="modal-info-val-val"></div>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:2px" id="modal-info-date-label">Received Date</div>
                        <div style="font-size:12.5px;font-weight:600;color:#111827" id="modal-info-date-val"></div>
                    </div>
                </div>
            </div>

            <!-- Item List -->
            <div style="margin-bottom:24px">
                <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px">Item List</div>
                <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">
                    <table style="width:100%;border-collapse:collapse;font-size:12px">
                        <thead>
                            <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                                <th style="padding:10px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">NO</th>
                                <th style="padding:10px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM ID</th>
                                <th style="padding:10px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM NAME</th>
                                <th style="padding:10px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">DESCRIPTION</th>
                                <th style="padding:10px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">SPEC</th>
                                <th style="padding:10px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">QTY</th>
                                <th style="padding:10px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">UNIT</th>
                                <th style="padding:10px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">UNIT PRICE (RP)</th>
                                <th style="padding:10px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">TOTAL (RP)</th>
                                <th style="padding:10px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase">VENDOR</th>
                            </tr>
                        </thead>
                        <tbody id="modal-items-tbody"></tbody>
                    </table>
                    <div style="background:#f9fafb;padding:12px 14px;display:flex;justify-content:flex-end;align-items:center;gap:24px">
                        <div style="font-size:12px;font-weight:700;color:#111827">Total Request Value</div>
                        <div style="font-size:14px;font-weight:800;color:#111827" id="modal-tot-req-val"></div>
                    </div>
                </div>
            </div>

            <!-- Activity Log -->
            <div>
                <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px">Activity Log</div>
                <div style="font-size:12.5px;color:#374151;display:flex;flex-direction:column;gap:8px" id="modal-activity-log">
                    <div style="display:flex;gap:12px">
                        <div style="width:4px;height:4px;border-radius:50%;background:#111827;margin-top:6px"></div>
                        <div>
                            <div style="font-weight:600;color:#111827">Goods received - PR marked as completed</div>
                            <div style="font-size:11px;color:#9ca3af">System - Warehouse</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:12px;background:#f9fafb;border-bottom-left-radius:12px;border-bottom-right-radius:12px">
            <button onclick="closeModal()" style="padding:8px 16px;background:#fff;border:1px solid #d1d5db;border-radius:8px;font-size:12.5px;font-weight:600;color:#374151;cursor:pointer">Close</button>
        </div>
    </div>
</div>
@endsection