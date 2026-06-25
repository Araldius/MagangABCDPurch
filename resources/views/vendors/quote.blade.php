<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Quotation Portal</title>
    <style>
        :root {
            --primary: #1e3a5f;
            --primary-hover: #152b47;
            --secondary: #f1f5f9;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --danger: #ef4444;
            --success: #22c55e;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', system-ui, sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); font-size: 14px; line-height: 1.5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: var(--bg-card); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 24px; }
        .card-header { padding: 20px 24px; border-bottom: 1px solid var(--border); }
        .card-title { font-size: 18px; font-weight: 700; color: var(--primary); }
        .card-desc { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
        .card-body { padding: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--text-main); }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px; font-size: 14px; outline: none; transition: border-color 0.2s; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1); }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; border: none; transition: all 0.2s; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-block { width: 100%; }
        .table-responsive { overflow-x: auto; margin-top: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--border); }
        th { background: var(--secondary); font-size: 12px; text-transform: uppercase; font-weight: 600; color: var(--text-muted); white-space: nowrap; }
        td { font-size: 13px; }
        .alert { padding: 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .text-center { text-align: center; }
        .mt-4 { margin-top: 16px; }
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
        .item-option.selected { background:#f0f4ff;border-color:var(--primary); }
        .item-option-name { font-size:13.5px;font-weight:600;color:var(--text-main); }
        .item-option-desc { font-size:12px;color:var(--text-muted); margin-top: 4px;}
        .flex-between { display: flex; justify-content: space-between; align-items: center; width: 100%; }
    </style>
</head>
<body>
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
            <script>
                setTimeout(() => {
                    window.location.reload();
                }, 5000);
            </script>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul style="margin-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!session('success'))

        <form id="quote-form" method="POST" action="{{ route('vendors.quote.submit', $rfq->vendor_token) }}">
            @csrf
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Vendor Quotation Portal</div>
                    <div class="card-desc">Please provide your company details and quotation for the items below.</div>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label flex-between">
                                <span>Company Name *</span>
                                <a href="#" onclick="openVendorModal(); return false;" style="font-size:12px; color:var(--primary); text-decoration:none; font-weight:600;">Select from Catalog</a>
                            </label>
                            <input type="text" class="form-control" name="vendor_name" id="vendor_name_input" required placeholder="PT. ABC XYZ">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" id="vendor_contact" required placeholder="email@company.com">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Company Location / Address</label>
                        <input type="text" class="form-control" name="vendor_location" id="vendor_location" placeholder="Jakarta, Indonesia">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Quoted Items & Prices</div>
                </div>
                <div class="card-body" style="padding:0;">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Item / Service</th>
                                    <th>Unit</th>
                                    <th>Unit Price (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $isService = (bool)$rfq->service_request_id; 
                                    $idx = 0;
                                @endphp
                                
                                @if($isService)
                                    @foreach($rfq->serviceRequest->jobs as $job)
                                        <tr><td colspan="4" style="background:#f0f4f8; font-weight:700; color:#374151;">{{ $job->description ?? $job->job_description }}</td></tr>
                                        @foreach($job->items as $item)
                                            <tr>
                                                <td>{{ $idx + 1 }}</td>
                                                <td>
                                                    <strong>{{ $item->name ?? $item->item_name }}</strong>
                                                    <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                                    <div style="color:var(--text-muted); font-size:12px; margin-top:4px;">{{ $item->specification ?? '-' }}</div>
                                                </td>
                                                <td>
                                                    <div style="display:flex;align-items:center;gap:6px;">
                                                        <input type="number" step="0.01" class="form-control" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" required style="width:80px; text-align:center;" readonly>
                                                        <select class="form-control" name="items[{{ $idx }}][unit]" required style="width:85px; padding:8px;">
                                                            @foreach(['Pcs', 'Unit', 'Box', 'Kg', 'Liter', 'Meter', 'Roll', 'Set', 'Lot', 'Jasa', 'Pack'] as $u)
                                                                <option value="{{ $u }}" {{ strtolower($item->unit) == strtolower($u) ? 'selected' : '' }}>{{ $u }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text" inputmode="decimal" class="form-control price-input" name="items[{{ $idx }}][price]" required placeholder="Rp. 0" oninput="formatPriceInput(this)">
                                                </td>
                                            </tr>
                                            @php $idx++; @endphp
                                        @endforeach
                                    @endforeach
                                @else
                                    @foreach($items as $item)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->name ?? $item->item_name }}</strong>
                                                <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                                <div style="color:var(--text-muted); font-size:12px; margin-top:4px;">{{ $item->specification ?? '-' }}</div>
                                            </td>
                                            <td>
                                                <div style="display:flex;align-items:center;gap:6px;">
                                                    <input type="number" step="0.01" class="form-control" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" required style="width:80px; text-align:center;">
                                                    <select class="form-control" name="items[{{ $idx }}][unit]" required style="width:85px; padding:8px;">
                                                        @foreach(['Pcs', 'Unit', 'Box', 'Kg', 'Liter', 'Meter', 'Roll', 'Set', 'Lot', 'Jasa', 'Pack'] as $u)
                                                            <option value="{{ $u }}" {{ strtolower($item->unit) == strtolower($u) ? 'selected' : '' }}>{{ $u }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" inputmode="decimal" class="form-control price-input" name="items[{{ $idx }}][price]" required placeholder="Rp. 0" oninput="formatPriceInput(this)">
                                            </td>
                                        </tr>
                                        @php $idx++; @endphp
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-body" style="border-top:1px solid var(--border);">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Remarks / Notes</label>
                        <textarea class="form-control" name="note" rows="3" placeholder="Enter notes or conclusion for this quotation..."></textarea>
                    </div>
                </div>
                <div class="card-body" style="background:#f9fafb; border-top:1px solid var(--border); text-align:right;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 15px;">Submit Quotation</button>
                    <p style="font-size:11px; color:var(--text-muted); margin-top:10px;">By submitting, you agree to provide the items at the quoted prices.</p>
                </div>
            </div>
        </form>
        @endif
        <div class="modal-overlay" id="vendor-modal">
            <div class="modal">
                <div class="modal-header">
                    <div><div class="modal-title">Vendor Catalog</div><div class="modal-desc">Search and select registered vendors</div></div>
                    <button type="button" class="modal-close" onclick="closeVendorModal()">&times;</button>
                </div>
                <div style="padding: 16px 20px 12px; border-bottom: 1px solid var(--border); background: #fafafa;">
                    <input class="form-control" id="vendor-search" placeholder="Search vendor name..." oninput="filterVendors(this.value)">
                </div>
                <div class="modal-body" style="padding-top: 12px;">
                    <div id="vendor-list" style="display:flex;flex-direction:column;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" style="background:#fff;border:1px solid var(--border);color:var(--text-main);" onclick="closeVendorModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addSelectedVendor()">Select Vendor</button>
                </div>
            </div>
        </div>

        <script>
            const vendors = @json($vendors ?? []);
            let selectedVendorId = null;

            function filterVendors(q) { renderVendorList(q.toLowerCase()); }
            function renderVendorList(q='') {
                const filtered = vendors.filter(v => !q || v.vendor_name.toLowerCase().includes(q));
                document.getElementById('vendor-list').innerHTML = filtered.map(v => {
                    const isSelected = String(selectedVendorId) === String(v.id);
                    return `
                    <div class="item-option ${isSelected ? 'selected' : ''}" onclick="selectVendorModal('${v.id}')">
                        <div class="item-option-name">${v.vendor_name}</div>
                        <div class="item-option-desc">${v.location || '-'} | ${v.email || '-'}</div>
                    </div>`;
                }).join('');
            }

            function selectVendorModal(id) { 
                selectedVendorId = id; 
                renderVendorList(document.getElementById('vendor-search').value.toLowerCase()); 
            }
            
            function openVendorModal() { 
                selectedVendorId = null; 
                document.getElementById('vendor-search').value = ''; 
                renderVendorList(); 
                document.getElementById('vendor-modal').classList.add('open'); 
            }
            
            function closeVendorModal() { 
                document.getElementById('vendor-modal').classList.remove('open'); 
            }
            
            function addSelectedVendor() {
                if(!selectedVendorId){ alert('Please select a vendor.'); return; }
                const v = vendors.find(x => x.id == selectedVendorId);
                if(!v) return;
                
                document.getElementById('vendor_name_input').value = v.vendor_name;
                document.getElementById('vendor_location').value = v.location || '';
                document.getElementById('vendor_contact').value = v.email || '';
                
                closeVendorModal();
            }
            
            document.getElementById('vendor-modal')?.addEventListener('click', function(e) {
                if(e.target === this) this.classList.remove('open');
            });
            const vendorNameInput = document.getElementById('vendor_name_input');
            if(vendorNameInput) {
                vendorNameInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.blur();
                        document.getElementById('vendor_contact').focus();
                    }
                });

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

                    if (!newVal.trim()) return;

                    const match = vendors.find(v => v.vendor_name.toLowerCase() === newVal.trim().toLowerCase());
                    if (match) {
                        document.getElementById('vendor_location').value = match.location || '';
                        document.getElementById('vendor_contact').value = match.email || '';
                    }
                });
            }

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

            // Pastikan value yang dikirim ke server tetap angka polos, bukan "1.500,75"
            document.getElementById('quote-form').addEventListener('submit', function() {
                document.querySelectorAll('.price-input').forEach(input => {
                    input.value = parsePriceValue(input.value);
                });
            });
        </script>
    </div>
</body>
</html>