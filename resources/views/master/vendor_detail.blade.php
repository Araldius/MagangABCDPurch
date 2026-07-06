@extends('layouts.app')

@section('content')
@php $pageTitle = 'Master Vendor Detail'; @endphp

<div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:flex-start;">
    <div>
        <a href="javascript:history.back()" class="btn-back" style="margin-bottom:12px; width:fit-content; text-decoration:none; color:#4b5563; font-size:13px; font-weight:600; display:flex; align-items:center; gap:6px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Back
        </a>
        <h1 style="font-size:24px;font-weight:700;color:#111827;margin:0;">{{ $vendor->vendor_name ?? $vendor->name }}</h1>
        <p style="color:#6b7280;margin:4px 0 0;font-size:14px;">Email: {{ $vendor->email ?? '-' }} &nbsp;|&nbsp; Location: {{ $vendor->location ?? '-' }} &nbsp;|&nbsp; Total Supplied Value: Rp {{ number_format($totalValue, 0, ',', '.') }}</p>
    </div>
    <button onclick="document.getElementById('edit-vendor-modal').style.display='flex'" style="padding:8px 16px; background:#111827; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
        Edit Vendor
    </button>
</div>

<!-- Edit Vendor Modal -->
<div id="edit-vendor-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:500px;box-shadow:0 8px 40px rgba(0,0,0,.15);overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;background:#f9fafb">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin:0">Edit Vendor Information</h2>
            <button onclick="document.getElementById('edit-vendor-modal').style.display='none'" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af;line-height:1">&times;</button>
        </div>
        <form method="POST" action="{{ route('history.master.vendors.update', $vendor->id) }}">
            @csrf
            <div style="padding:20px">
                <div style="margin-bottom:16px">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px">Company Name</label>
                    <input type="text" name="vendor_name" value="{{ $vendor->vendor_name ?? $vendor->name }}" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:13.5px;color:#111827;box-sizing:border-box">
                </div>
                <div style="margin-bottom:16px">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px">Email Address</label>
                    <input type="email" name="email" value="{{ $vendor->email }}" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:13.5px;color:#111827;box-sizing:border-box">
                </div>
            </div>
            <div style="padding:16px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:flex-end;gap:10px;background:#f9fafb">
                <button type="button" onclick="document.getElementById('edit-vendor-modal').style.display='none'" style="padding:8px 16px;background:#fff;border:1px solid #d1d5db;border-radius:6px;font-size:13px;font-weight:600;color:#374151;cursor:pointer">Cancel</button>
                <button type="submit" style="padding:8px 16px;background:#111827;border:none;border-radius:6px;font-size:13px;font-weight:600;color:#fff;cursor:pointer">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;">
    <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;">
        <h2 style="font-size:14px;font-weight:700;color:#111827;margin:0">Quotation History</h2>
    </div>
    
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">DOC NO.</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">REQ DATE</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">SUBMITTED AT</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEMS COUNT</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">TOTAL VALUE (RP)</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">STATUS</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ACTION</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $h)
                <tr style="border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:10px 14px;font-family:monospace;font-weight:600;color:#111827">{{ $h['doc_no'] ?: '-' }}</td>
                    <td style="padding:10px 14px">{{ $h['req_date'] }}</td>
                    <td style="padding:10px 14px">{{ $h['submitted_at'] }}</td>
                    <td style="padding:10px 14px">{{ $h['items_count'] }}</td>
                    <td style="padding:10px 14px;font-weight:700">{{ number_format($h['value'], 0, ',', '.') }}</td>
                    <td style="padding:10px 14px">
                        @if($h['is_selected'])
                            <span style="background:#d1fae5;color:#065f46;padding:4px 8px;border-radius:999px;font-size:11px;font-weight:600;">Selected</span>
                        @else
                            <span style="background:#f3f4f6;color:#6b7280;padding:4px 8px;border-radius:999px;font-size:11px;font-weight:600;">Not Selected</span>
                        @endif
                    </td>
                    <td style="padding:10px 14px">
                        <button onclick="openCombinedModal({{ $loop->index }})" style="padding:4px 10px;font-size:11.5px;font-weight:600;color:#374151;background:#fff;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer">View Details</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No quotation history found for this vendor.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Combined Detail Modal -->
<div id="pr-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(17,24,39,0.4);z-index:999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px)">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:1400px;height:95vh;display:flex;flex-direction:column;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1)">
        <div style="padding:16px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:flex-start">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin:0;line-height:1.4" id="modal-title-text"></h2>
            <button onclick="closePRModal()" style="background:none;border:none;color:#9ca3af;cursor:pointer;font-size:20px;line-height:1">x</button>
        </div>
        <div style="padding:24px;overflow-y:auto;flex:1;display:flex;gap:24px;">
            
            <!-- Left Side: Order Details -->
            <div style="flex:3;display:flex;flex-direction:column;">
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
                        <div id="modal-progress-completed-dot" style="width:26px;height:26px;border-radius:50%;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                        <div style="font-size:11px;font-weight:600;color:#10b981;text-align:center">Completed</div>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div style="display:flex;gap:24px;margin-bottom:30px">
                <div style="flex:2;background:#f9fafb;border-radius:8px;padding:16px;border:1px solid #f3f4f6">
                    <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px">Request Information</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div>
                            <div style="font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:2px">Submitted Date</div>
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
            </div>

            <!-- Items -->
            <div style="margin-bottom:30px">
                <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px">Approved Items</div>
                <table style="width:100%;border-collapse:collapse;font-size:12.5px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">
                    <thead>
                        <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">NO</th>
                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">CODE</th>
                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">NAME</th>
                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">DESC</th>
                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">SPEC</th>
                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">QTY</th>
                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">UNIT</th>
                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">UNIT PRICE</th>
                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">SUBTOTAL</th>
                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">VENDOR</th>
                        </tr>
                    </thead>
                    <tbody id="modal-items-tbody"></tbody>
                </table>
                <div style="padding:12px 14px;background:#f9fafb;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 8px 8px;display:flex;justify-content:space-between;align-items:center">
                    <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase">Total Value</div>
                    <div style="font-size:14px;font-weight:800;color:#111827" id="modal-tot-req-val"></div>
                </div>
            </div>
            
            </div> <!-- End Left Side -->

            <!-- Right Side: Quoted Items -->
            <div style="flex:2;background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;display:flex;flex-direction:column;overflow:hidden">
                <div style="padding:16px 20px;border-bottom:1px solid #e5e7eb;background:#fff">
                    <h2 style="font-size:14px;font-weight:700;color:#111827;margin:0">Vendor Quoted Items</h2>
                    <p style="font-size:12px;color:#6b7280;margin:2px 0 0">Items offered by this vendor for this request</p>
                </div>
                <div style="overflow-y:auto;flex:1;padding:0">
                    <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
                        <thead>
                            <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                                <th style="padding:12px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">Item Name</th>
                                <th style="padding:12px 16px;text-align:right;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">Qty</th>
                                <th style="padding:12px 16px;text-align:right;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">Unit Price</th>
                                <th style="padding:12px 16px;text-align:right;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="items-modal-tbody">
                        </tbody>
                    </table>
                </div>
            </div> <!-- End Right Side -->

        </div>
    </div>
</div>

<script>
let histData = @json($history);

function openCombinedModal(idx) {
    const h = histData[idx];
    const quotedItems = h.items || [];
    const pr = h.pr_data;
    if (!pr) return;

    // Adjust PR status color
    if (pr.status !== 'completed') {
        const dot = document.getElementById('modal-progress-completed-dot');
        dot.style.background = '#e5e7eb';
        dot.style.color = '#9ca3af';
    } else {
        const dot = document.getElementById('modal-progress-completed-dot');
        dot.style.background = '#10b981';
        dot.style.color = '#fff';
    }

    document.getElementById('modal-title-text').innerHTML = `${pr.items[0] ? pr.items[0].name : 'Procurement'} <br><span style="font-size:12px;font-weight:400;color:#6b7280;margin-top:2px;display:block">${pr.doc_number} | ${pr.department}</span>`;
    document.getElementById('modal-info-submission').textContent = h.req_date || '-';
    document.getElementById('modal-info-department').textContent = pr.department;
    document.getElementById('modal-info-requestedby').textContent = '-'; 
    document.getElementById('modal-info-vendor-val').textContent = pr.vendor_name;
    document.getElementById('modal-info-val-val').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(pr.total_value);
    document.getElementById('modal-info-date-val').textContent = pr.decided_at ? pr.decided_at.split('T')[0] : '-'; 

    let itemHtml = '';
    (pr.items || []).forEach((it, i) => {
        let uPrice = it.final_price_per_item || 0;
        let tPrice = uPrice * (it.quantity || 0);
        itemHtml += `
            <tr style="border-bottom:1px solid #e5e7eb">
                <td style="padding:10px 14px;font-weight:600;color:#111827">${i+1}</td>
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

    // Populate Right Side Quoted Items
    const tbody = document.getElementById('items-modal-tbody');
    tbody.innerHTML = quotedItems.map(it => `
        <tr style="border-bottom:1px solid #f3f4f6">
            <td style="padding:12px 16px;font-weight:600;">${it.name}</td>
            <td style="padding:12px 16px;text-align:right;">${it.qty} ${it.unit}</td>
            <td style="padding:12px 16px;text-align:right;">${new Intl.NumberFormat('id-ID').format(it.price)}</td>
            <td style="padding:12px 16px;text-align:right;font-weight:600;">${new Intl.NumberFormat('id-ID').format(it.subtotal)}</td>
        </tr>
    `).join('');

    document.getElementById('pr-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closePRModal() {
    document.getElementById('pr-modal').style.display = 'none';
    document.body.style.overflow = '';
}

document.getElementById('pr-modal').addEventListener('click', function(e) {
    if (e.target === this) closePRModal();
});
</script>
@endsection