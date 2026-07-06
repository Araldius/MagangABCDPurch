@extends('layouts.app')
@php 
    $pageTitle = 'Manual Quotation Entry';
    $isService = $rfq->service_request_id ? true : false;
    $requestTitle = $isService ? $rfq->serviceRequest->service_name : $rfq->purchaseRequest->title;
    $docNumber = $isService ? ($rfq->serviceRequest->document_number ?? 'SR-'.$rfq->serviceRequest->id) : $rfq->purchaseRequest->document_number;
@endphp
@section('content')

<style>
:root {
    --primary:        #111827;
    --primary-light:  #f0f4ff;
    --border:         #e5e7eb;
    --border-strong:  #d1d5db;
    --text:           #111827;
    --text-muted:     #6b7280;
    --radius:         12px;
    --shadow-sm:      0 1px 3px 0 rgb(0 0 0/.08), 0 1px 2px -1px rgb(0 0 0/.06);
    --req-color:      #ef4444;
}
.page-header   { margin-bottom: 28px; }
.page-title    { font-size: 22px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
.page-desc     { font-size: 14px; color: var(--text-muted); }
.card          { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
.card-header   { padding: 18px 24px; border-bottom: 1px solid var(--border); background: #fafafa; }
.card-body     { padding: 24px; }
.card-title    { font-size: 15px; font-weight: 700; color: var(--text); line-height: 1.3; }
.card-desc     { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
.form-section-icon { width: 38px; height: 38px; border-radius: 8px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; margin-right: 14px; flex-shrink: 0; }
.flex-center   { display: flex; align-items: center; }
.form-group    { display: flex; flex-direction: column; margin-bottom: 20px; }
.form-row      { display: grid; gap: 20px; }
.form-row-2    { grid-template-columns: 1fr 1fr; }
.form-label    { font-size: 13.5px; font-weight: 600; color: var(--text); margin-bottom: 8px; }
.req           { color: var(--req-color); margin-left: 2px; }
.form-control  { width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border: 1px solid var(--border-strong); border-radius: 8px; background: #fff; color: var(--text); outline: none; }
.form-control:focus  { border-color: #6366f1; box-shadow: 0 0 0 3px rgb(99 102 241/.12); }
.mt-4 { margin-top: 28px; }
.flex-between  { display: flex; align-items: center; justify-content: space-between; }

/* Buttons */
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: 1px solid transparent; text-decoration: none; }
.btn-primary  { background: #111827; color: #fff; }
.btn-primary:hover { background: #1f2937; }
.btn-outline  { background: #fff; color: var(--text); border-color: var(--border-strong); }
.btn-outline:hover { background: #f9fafb; }

/* Tables */
.item-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.item-table th { text-align: left; padding: 12px 14px; color: var(--text-muted); border-bottom: 1px solid var(--border); font-weight: 600; font-size: 11.5px; text-transform: uppercase; }
.item-table td { padding: 14px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }

/* Modals & Catalog */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1000; align-items: center; justify-content: center; padding: 16px; }
.modal-overlay.open { display: flex; }
.modal { background: #fff; border-radius: 14px; width: 100%; max-width: 600px; display: flex; flex-direction: column; overflow: hidden; }
.modal-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.modal-title  { font-size: 16px; font-weight: 700; }
.modal-desc   { font-size: 13px; color: var(--text-muted); }
.modal-body   { padding: 20px; overflow-y: auto; max-height: 65vh; }
.modal-footer { padding: 14px 20px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; }
.modal-close  { background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 18px; }
.item-option { padding:12px 14px;border-radius:8px;cursor:pointer;border:1px solid var(--border);transition:background .1s; margin-bottom: 6px; }
.item-option:hover { background:#f9fafb; }
.item-option.selected { background:var(--primary-light);border-color:var(--primary); }
.item-option-name { font-size:13.5px;font-weight:600;color:var(--text); }
.item-option-desc { font-size:12px;color:var(--text-muted); margin-top: 4px;}
</style>

<div style="margin-bottom:20px">
    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Edit Quotation for {{ $docNumber }}</h1>
    <p style="font-size:12.5px;color:#6b7280;margin:0">
        Enter the vendor details and quoted prices for {{ $requestTitle }}
    </p>
</div>

<form action="{{ route('quotations.update', $quotation->id) }}" method="post" id="quote-form">
@csrf

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">
    <div style="display:flex;align-items:center;padding:16px 20px;border-bottom:1px solid #f3f4f6;">
        <span style="font-size:14px;font-weight:700;color:#111827">Vendor Information</span>
    </div>
    
    <div style="padding:20px;border-bottom:1px solid #e5e7eb">
        
        
        <div class="form-group flex-1" style="margin-bottom:16px;">
            <label class="form-label flex-between" style="width:100%">
                <span>Vendor Name <span class="req">*</span></span>
                <a href="#" onclick="openVendorModal(); return false;" style="font-size:12px; color:#3b5bdb; text-decoration:underline;">Select from Catalog</a>
            </label>
            <input class="form-control" name="new_vendor_name" id="new_vendor_name" placeholder="Enter vendor name manually or select from catalog" required>
        </div>

        <div class="form-row form-row-2">
            <div class="form-group flex-1" style="margin-bottom:0;">
                <label class="form-label">Location / Address</label>
                <input class="form-control" name="new_vendor_location" id="vendor_location" placeholder="Vendor location">
            </div>
            <div class="form-group flex-1" style="margin-bottom:0;">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="new_vendor_email" id="vendor_contact" placeholder="vendor@email.com" oninvalid="this.setCustomValidity('Email must contain \'@\'')" oninput="this.setCustomValidity('')">
            </div>
        </div>
    </div>

    <div style="display:flex;align-items:center;padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#fafafa">
        <span style="font-size:14px;font-weight:700;color:#111827">Quoted Items & Prices</span>
    </div>

    <div style="overflow-x:auto;">
        <table class="item-table" style="margin:0;">
            <thead style="background:#f9fafb;">
                <tr>
                    <th style="width:40px;">#</th>
                    <th>NAMA ITEM</th>
                    <th style="width:100px;">REQUESTED QTY</th>
                    <th style="width:150px;">UNIT PRICE (Rp) <span class="req">*</span></th>
                    <th style="width:150px;text-align:right;">SUBTOTAL (Rp)</th>
                </tr>
            </thead>
                        <tbody>
                @php $idx = 0; @endphp
                @if($isService)
                    @foreach($rfq->serviceRequest->jobs as $job)
                        <tr><td colspan="5" style="background:#f0f4f8; font-weight:700; color:#374151;">{{ $job->description ?? $job->job_description }}</td></tr>
                        @foreach($job->items as $item)
                            <tr>
                                <td>{{ ++$idx }}</td>
                                <td>
                                    <strong>{{ $item->name ?? $item->item_name }}</strong>
                                    <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                    <div style="color:var(--text-muted); font-size:12px; margin-top:4px;">{{ $item->specification ?? '-' }}</div>
                                    <div style="margin-top:8px;">
                                        <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:#4b5563;cursor:pointer;">
                                            <input type="checkbox" class="diff-toggle" onchange="document.getElementById('spec-diff-{{ $idx }}').style.display = this.checked ? 'block' : 'none'">
                                            <span>Terdapat perbedaan Spesifikasi/Unit?</span>
                                        </label>
                                        <div id="spec-diff-{{ $idx }}" style="display:none;margin-top:8px;">
                                            <input type="text" class="form-control" name="items[{{ $idx }}][specification]" placeholder="Tuliskan spesifikasi yang ditawarkan..." style="font-size:12px;padding:8px 10px;">
                                        </div>
                                    </div>
                                    <div style="margin-top:8px;">
                                        <textarea class="form-control" name="items[{{ $idx }}][notes]" rows="2" placeholder="Catatan untuk item ini (opsional)..." style="font-size:12px;padding:8px 10px;resize:vertical;"></textarea>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:11px;color:#6b7280;margin-bottom:4px;font-weight:600;">Target: {{ $item->quantity }} {{ $item->unit }}</div>
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <input type="number" step="0.01" class="form-control qty-input" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" required style="width:80px; text-align:center;" readonly>
                                        <select class="form-control" name="items[{{ $idx }}][unit]" required style="width:85px; padding:8px;" onchange="checkUnitChange(this, {{ $idx }}, '{{ strtolower($item->unit) }}')">
                                            @php
                                                $baseUnits = ['Pcs', 'Unit', 'Box', 'Kg', 'Liter', 'Meter', 'Roll', 'Set', 'Lot', 'Jasa', 'Pack'];
                                                $itemUnit = ucfirst(strtolower($item->unit));
                                                if (!in_array($itemUnit, $baseUnits)) array_unshift($baseUnits, $itemUnit);
                                            @endphp
                                            @foreach($baseUnits as $u)
                                                <option value="{{ $u }}" {{ strtolower($item->unit) == strtolower($u) ? 'selected' : '' }}>{{ $u }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" inputmode="decimal" class="form-control price-input" name="items[{{ $idx }}][price]" required placeholder="Rp. 0" oninput="formatPriceInput(this)">
                                </td>
                                <td class="subtotal-cell" style="font-weight:700; font-family:monospace; font-size:14px; text-align:right;">Rp. 0</td>
                            </tr>
                        @endforeach
                    @endforeach
                @else
                    @foreach($rfq->purchaseRequest->items as $item)
                        <tr>
                            <td>{{ ++$idx }}</td>
                            <td>
                                <strong>{{ $item->name ?? $item->item_name }}</strong>
                                <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                <div style="color:var(--text-muted); font-size:12px; margin-top:4px;">{{ $item->specification ?? '-' }}</div>
                                <div style="margin-top:8px;">
                                    <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:#4b5563;cursor:pointer;">
                                        <input type="checkbox" class="diff-toggle" onchange="document.getElementById('spec-diff-{{ $idx }}').style.display = this.checked ? 'block' : 'none'">
                                        <span>Terdapat perbedaan Spesifikasi?</span>
                                    </label>
                                    <div id="spec-diff-{{ $idx }}" style="display:none;margin-top:8px;">
                                        <input type="text" class="form-control" name="items[{{ $idx }}][specification]" placeholder="Tuliskan spesifikasi yang ditawarkan..." style="font-size:12px;padding:8px 10px;">
                                    </div>
                                </div>
                                <div style="margin-top:8px;">
                                    <textarea class="form-control" name="items[{{ $idx }}][notes]" rows="2" placeholder="Catatan untuk item ini (opsional)..." style="font-size:12px;padding:8px 10px;resize:vertical;"></textarea>
                                </div>
                            </td>
                            <td>
                                <div style="font-size:11px;color:#6b7280;margin-bottom:4px;font-weight:600;">Target: {{ $item->quantity }} {{ $item->unit }}</div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <!-- TIDAK ADA READONLY UNTUK GOODS -->
                                    <input type="number" step="0.01" class="form-control qty-input" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" required style="width:80px; text-align:center;">
                                    <select class="form-control" name="items[{{ $idx }}][unit]" required style="width:85px; padding:8px;" onchange="checkUnitChange(this, {{ $idx }}, '{{ strtolower($item->unit) }}')">
                                        @php
                                            $baseUnits = ['Pcs', 'Unit', 'Box', 'Kg', 'Liter', 'Meter', 'Roll', 'Set', 'Lot', 'Jasa', 'Pack'];
                                            $itemUnit = ucfirst(strtolower($item->unit));
                                            if (!in_array($itemUnit, $baseUnits)) array_unshift($baseUnits, $itemUnit);
                                        @endphp
                                        @foreach($baseUnits as $u)
                                            <option value="{{ $u }}" {{ strtolower($item->unit) == strtolower($u) ? 'selected' : '' }}>{{ $u }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td>
                                <input type="text" inputmode="decimal" class="form-control price-input" name="items[{{ $idx }}][price]" required placeholder="Rp. 0" oninput="formatPriceInput(this)">
                            </td>
                            <td class="subtotal-cell" style="font-weight:700; font-family:monospace; font-size:14px; text-align:right;">Rp. 0</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr style="background:#f9fafb;">
                    <td colspan="4" style="text-align:right; font-weight:700; color:var(--text-muted);">Grand Total</td>
                    <td id="grand-total" style="font-weight:800; font-size:16px; color:#111827; font-family:monospace; text-align:right;">Rp. 0</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div style="padding:16px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:flex-end;gap:12px;background:#fafafa">
        <a href="{{ route('dashboard') }}" class="btn-back">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Cancel
        </a>
        <button type="submit" class="btn btn-primary" style="border-radius:7px;font-size:12.5px;padding:8px 16px;box-shadow:0 1px 2px rgba(0,0,0,0.05)">Save Quotation</button>
    </div>
</div>
</form>
<div class="modal-overlay" id="vendor-modal">
    <div class="modal">
        <div class="modal-header">
            <div><div class="modal-title">Vendor Catalog</div><div class="modal-desc">Search and select registered vendors</div></div>
            <button type="button" class="modal-close" onclick="closeVendorModal()">&times;</button>
        </div>
        <div style="padding: 16px 20px 12px; border-bottom: 1px solid var(--border); background: #fafafa; display:flex; flex-direction:column; gap:8px;">
            <div style="display:flex; gap:8px;">
                <input class="form-control" id="vendor-search" placeholder="Search vendor name..." oninput="filterVendors()" style="flex:1;">
                <select class="form-control" id="vendor-sort" onchange="filterVendors()" style="width:160px;">
                    <option value="name_asc">Name (A-Z)</option>
                    <option value="name_desc">Name (Z-A)</option>
                </select>
            </div>
        </div>
        <div class="modal-body" style="padding-top: 12px;">
            <div id="vendor-list" style="display:flex;flex-direction:column;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-back" onclick="closeVendorModal()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Cancel
            </button>
            <button type="button" class="btn btn-primary" onclick="addSelectedVendor()">Select Vendor</button>
        </div>
    </div>
</div>

<script>
    let vendors = [];
    let selectedVendorId = null;

    // Fetch vendors
    fetch('/api/vendors')
        .then(res => res.json())
        .then(data => {
            vendors = data;
        });

    function resetVendorId() {
        document.getElementById('hidden_vendor_id').value = '';
    }

    // Auto-caps and Autofill logic
    const vendorNameInput = document.getElementById('new_vendor_name');

    // Handle Enter key to prevent form submit and move to next field
    vendorNameInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.blur();
            document.getElementById('vendor_location').focus();
        }
    });

    // Format immediately as they type, autofill if match
    vendorNameInput.addEventListener('input', function() {
        let start = this.selectionStart;
        let end = this.selectionEnd;
        let val = this.value;
        
        let words = val.split(' ');
        for (let i = 0; i < words.length; i++) {
            let w = words[i];
            if (!w) continue;
            let wl = w.toLowerCase();
            if (['pt', 'pt.', 'cv', 'cv.', 'ud', 'ud.', 'tbk', 'tbk.'].includes(wl)) {
                words[i] = w.toUpperCase();
            } else {
                words[i] = w.charAt(0).toUpperCase() + w.slice(1).toLowerCase();
            }
        }
        let newVal = words.join(' ');
        
        if (this.value !== newVal) {
            this.value = newVal;
            this.setSelectionRange(start, end);
        }

        // Autofill logic
        if (!newVal.trim()) {
            resetVendorId();
            return;
        }

        const match = vendors.find(v => v.vendor_name.toLowerCase() === newVal.trim().toLowerCase());
        if (match) {
            document.getElementById('hidden_vendor_id').value = match.id;
            document.getElementById('vendor_location').value = match.location || '';
            document.getElementById('vendor_contact').value = match.email || '';
        } else {
            resetVendorId();
        }
    });

    function filterVendors() {
        const q = document.getElementById('vendor-search').value.toLowerCase();
        const s = document.getElementById('vendor-sort').value;
        renderVendorList(q, s);
    }
    function renderVendorList(q='', s='name_asc') {
        let filtered = vendors.filter(v => !q || v.vendor_name.toLowerCase().includes(q));
        if (s === 'name_asc') filtered.sort((a,b) => a.vendor_name.localeCompare(b.vendor_name));
        else if (s === 'name_desc') filtered.sort((a,b) => b.vendor_name.localeCompare(a.vendor_name));

        document.getElementById('vendor-list').innerHTML = filtered.map(v => {
            const isSelected = String(selectedVendorId) === String(v.id);
            return `
            <div class="item-option ${isSelected ? 'selected' : ''}" onclick="selectVendorModal('${v.id}')">
                <div class="item-option-name">${v.vendor_name}</div>
                <div class="item-option-desc">${v.location || '-'} | ${v.email || '-'}</div>
            </div>`;
        }).join('');
    }

    function selectVendorModal(id) { selectedVendorId = id; filterVendors(); }
    function openVendorModal() { selectedVendorId = null; document.getElementById('vendor-search').value = ''; document.getElementById('vendor-sort').value = 'name_asc'; filterVendors(); document.getElementById('vendor-modal').classList.add('open'); document.body.style.overflow = 'hidden'; }
    
    function closeVendorModal() { 
        document.getElementById('vendor-modal').classList.remove('open'); 
        document.body.style.overflow = '';
    }
    
    function addSelectedVendor() {
        if(!selectedVendorId){ alert('Please select a vendor.'); return; }
        const v = vendors.find(x => x.id == selectedVendorId);
        if(!v) return;
        
        document.getElementById('hidden_vendor_id').value = v.id;
        document.getElementById('new_vendor_name').value = v.vendor_name;
        document.getElementById('vendor_location').value = v.location || '';
        document.getElementById('vendor_contact').value = v.email || '';
        
        closeVendorModal();
    }
    
    // Close modal when clicking outside
    document.getElementById('vendor-modal').addEventListener('click', function(e) {
        if(e.target === this) closeVendorModal();
    });

    // Helper: ubah "1.500,75" -> 1500.75 (number biasa)
    function parsePriceValue(str) {
        if (!str) return 0;
        let cleanStr = String(str).replace(/[^0-9,]/g, '').replace(',', '.');
        return parseFloat(cleanStr) || 0;
    }

    // Format live saat input: titik = pemisah ribuan, koma = desimal
    function formatPriceInput(input) {
        let val = input.value.replace(/[^0-9,]/g, '');
        const commaIndex = val.indexOf(',');
        let intPart = commaIndex === -1 ? val : val.slice(0, commaIndex);
        let decPart = commaIndex === -1 ? null : val.slice(commaIndex + 1).replace(/,/g, '').slice(0, 2);

        intPart = intPart.replace(/^0+(?=\d)/, '');
        const formattedInt = intPart ? Number(intPart).toLocaleString('id-ID') : '';

        let finalValue = decPart !== null
            ? formattedInt + ',' + decPart
            : (commaIndex !== -1 ? formattedInt + ',' : formattedInt);
        input.value = finalValue ? 'Rp. ' + finalValue : '';
    }

    // Calculate totals
    const rows = document.querySelectorAll('tbody tr:not([style*="background:#f0f4f8"])');
    rows.forEach(row => {
        const qty = row.querySelector('.qty-input');
        const price = row.querySelector('.price-input');
        const sub = row.querySelector('.subtotal-cell');

        const update = () => {
            const q = parseFloat(qty.value) || 0;
            const p = parsePriceValue(price.value);
            sub.textContent = 'Rp. ' + (q * p).toLocaleString('id-ID');
            updateGrandTotal();
        };

        if(qty) qty.addEventListener('input', update);
        if(price) price.addEventListener('input', update);
    });

    function updateGrandTotal() {
        let total = 0;
        rows.forEach(row => {
            const qty = row.querySelector('.qty-input');
            const price = row.querySelector('.price-input');
            if(qty && price) {
                total += (parseFloat(qty.value)||0) * parsePriceValue(price.value);
            }
        });
        document.getElementById('grand-total').textContent = 'Rp. ' + total.toLocaleString('id-ID');
    }

    // Pastikan value yang dikirim ke server tetap angka polos, bukan "1.500,75"
    document.getElementById('quote-form').addEventListener('submit', function() {
        document.querySelectorAll('.price-input').forEach(input => {
            input.value = parsePriceValue(input.value);
        });
    });
    function toggleDiff(checkbox, idx) {
        const alertBox = document.getElementById('diff-alert-' + idx);
        alertBox.style.display = checkbox.checked ? 'block' : 'none';
        
        // Jika ada satu saja item yang beda, kotak "Notes" global di bawah akan menjadi REQUIRED
        const anyChecked = document.querySelectorAll('.diff-toggle:checked').length > 0;
        const globalNote = document.querySelector('textarea[name="note"]');
        
        if (globalNote) {
            globalNote.required = anyChecked;
            if (anyChecked) {
                globalNote.placeholder = "WAJIB DIISI: Jelaskan perbedaan spesifikasi pada item yang Anda ubah...";
                globalNote.style.border = "1px solid #ef4444";
            } else {
                globalNote.placeholder = "Enter notes or conclusion for this quotation...";
                globalNote.style.border = "1px solid #d1d5db";
            }
        }
    }

    // Ter-trigger otomatis jika Vendor mengganti unit di dropdown
    function checkUnitChange(selectObj, idx, originalUnit) {
        const toggle = document.querySelector(`.diff-toggle[onchange*="${idx}"]`);
        if (toggle) {
            if (selectObj.value.toLowerCase() !== originalUnit.toLowerCase()) {
                toggle.checked = true;
            }
            toggleDiff(toggle, idx);
        }
    }
</script>
@endsection