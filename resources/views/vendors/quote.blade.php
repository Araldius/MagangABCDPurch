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
        .container { max-width: 1000px; margin: 0 auto; }
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
    @if(isset($closedReason))
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.65); backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;">
        <script>document.body.style.overflow = 'hidden';</script>
        <div style="background: #fff; border-radius: 12px; width: 100%; max-width: 450px; text-align: center; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="padding: 32px 24px;">
                <div style="width: 64px; height: 64px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 32px; height: 32px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h1 style="font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 12px;">Quotation Access Closed</h1>
                <p style="font-size: 14px; color: #6b7280; line-height: 1.6; margin-bottom: 24px;">
                    @if($closedReason === 'completed')
                        This request has been completely processed and is no longer accepting quotations. Thank you for your participation.
                    @else
                        This link has expired. The link is only valid during the quotation submission period.
                    @endif
                    <br><br>
                    If you have any questions, please contact <a href="mailto:purchasing@duniakimiajaya.com" style="color: #3b5bdb; text-decoration: none;">purchasing@duniakimiajaya.com</a>.
                </p>
                <button onclick="window.close(); history.back();" class="btn-back" style="width: 100%; justify-content: center;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Close / Go Back
                </button>
            </div>
        </div>
    </div>
    @endif

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

        @if(session('overwrite_warning'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const overlay = document.createElement('div');
                    overlay.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.65); backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;';
                    
                    const box = document.createElement('div');
                    box.style.cssText = 'background: #fff; border-radius: 12px; width: 100%; max-width: 450px; text-align: center; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2); padding: 32px 24px;';
                    
                    box.innerHTML = `
                        <div style="width: 64px; height: 64px; background: #fef3c7; color: #d97706; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 32px; height: 32px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h1 style="font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 12px;">Quotation Already Exists</h1>
                        <p style="font-size: 14px; color: #6b7280; line-height: 1.6; margin-bottom: 24px;">
                            {{ session('overwrite_warning') }}
                        </p>
                        <div style="display: flex; gap: 12px;">
                            <button onclick="this.closest('div').parentElement.remove(); document.body.style.overflow = 'auto';" style="flex:1; background: #f3f4f6; color: #374151; border: none; padding: 10px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer;">Cancel</button>
                            <button onclick="confirmOverwrite()" style="flex:1; background: #d97706; color: #fff; border: none; padding: 10px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer;">Yes, Overwrite</button>
                        </div>
                    `;
                    
                    overlay.appendChild(box);
                    document.body.appendChild(overlay);
                    document.body.style.overflow = 'hidden';
                });

                function confirmOverwrite() {
                    const form = document.getElementById('quote-form');
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'confirm_overwrite';
                    input.value = '1';
                    form.appendChild(input);
                    form.submit();
                }
            </script>
        @endif

        @if(!session('success'))

        <form id="quote-form" method="POST" action="{{ route('vendors.quote.submit', $rfq->vendor_token) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="vendor_id" id="vendor_id_input">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Vendor Quotation Portal</div>
                    <div class="card-desc">Please provide your company details and quotation for the items below.</div>
                </div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:16px; position:relative;">
                        <label class="form-label flex-between">
                            <span>Company Name *</span>
                            <span id="vendor-status-badge" style="display:none; font-size:11px; background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:4px; font-weight:700;">✓ REGISTERED VENDOR</span>
                        </label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" class="form-control" name="vendor_name" id="vendor_name_input" value="{{ old('vendor_name') }}" required placeholder="Type your company name..." autocomplete="off">
                            <button type="button" id="clear-vendor-btn" onclick="resetVendorSelection()" style="display:none; position:absolute; right:10px; background:none; border:none; color:#ef4444; font-weight:bold; cursor:pointer; font-size:16px;">&times;</button>
                        </div>
                        <div id="autocomplete-dropdown" style="display:none; position:absolute; left:0; right:0; top:100%; background:#fff; border:1px solid #cbd5e1; border-radius:8px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); z-index:100; max-height:220px; overflow-y:auto; margin-top:4px;"></div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Company Location / Address</label>
                            <input type="text" class="form-control" name="vendor_location" id="vendor_location" value="{{ old('vendor_location') }}" placeholder="Jakarta, Indonesia">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Email <span style="color:#9ca3af;font-weight:400">(Optional)</span></label>
                            <input type="email" class="form-control" name="email" id="vendor_contact" value="{{ old('email') }}" placeholder="email@company.com" oninvalid="this.setCustomValidity('Email must contain \'@\'')" oninput="this.setCustomValidity('')">
                        </div>
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
                                    <th>Spesifikasi & Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $isService = (bool)$rfq->service_request_id; 
                                    $idx = 0;
                                @endphp
                                
                                @if($isService)
                                    @foreach($rfq->serviceRequest->jobs as $job)
                                        <tr><td colspan="5" style="background:#f0f4f8; font-weight:700; color:#374151;">{{ $job->description ?? $job->job_description }}</td></tr>
                                        @foreach($job->items as $item)
                                            @php
                                                $ex = $existingItems[$item->id] ?? null;
                                                $defQty = $ex ? $ex->offered_quantity : $item->quantity;
                                                $defUnit = $ex ? $ex->offered_unit : $item->unit;
                                                $defPrice = $ex ? $ex->offered_price_per_item : '';
                                                $defSpec = $ex ? $ex->offered_specification : '';
                                                $defNotes = $ex ? $ex->item_notes : '';
                                            @endphp
                                            <tr>
                                                <td>{{ $idx + 1 }}</td>
                                                <td>
                                                    <strong>{{ $item->name ?? $item->item_name }}</strong>
                                                    <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                                    <div style="color:var(--text-muted); font-size:12px; margin-top:4px;">{{ $item->specification ?? '-' }}</div>
                                                    @if(isset($item->item_notes) && $item->item_notes)
                                                        <div style="background:#e0f2fe;border:1px solid #38bdf8;color:#0369a1;padding:6px 10px;border-radius:4px;font-size:11.5px;margin-top:8px;">
                                                            <strong>User Note:</strong> {{ $item->item_notes }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div style="font-size:11px;color:#6b7280;margin-bottom:4px;font-weight:600;">Target: {{ $item->quantity }} {{ $item->unit }}</div>
                                                    <div style="display:flex;align-items:center;gap:6px;">
                                                        <input type="number" step="0.01" class="form-control" name="items[{{ $idx }}][quantity]" value="{{ old('items.'.$idx.'.quantity', $defQty) }}" required style="width:80px; text-align:center;" readonly>
                                                        <select class="form-control" name="items[{{ $idx }}][unit]" required style="width:85px; padding:8px;">
                                                            @php
                                                                $baseUnits = ['Pcs', 'Unit', 'Box', 'Kg', 'Liter', 'Meter', 'Roll', 'Set', 'Lot', 'Jasa', 'Pack'];
                                                                $itemUnit = ucfirst(strtolower(trim($defUnit)));
                                                                if (!in_array($itemUnit, $baseUnits)) array_unshift($baseUnits, $itemUnit);
                                                            @endphp
                                                            @foreach($baseUnits as $u)
                                                                <option value="{{ $u }}" {{ strtolower(trim(old('items.'.$idx.'.unit') ?? $defUnit)) == strtolower(trim($u)) ? 'selected' : '' }}>{{ $u }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td style="min-width:130px;">
                                                    <input type="text" inputmode="decimal" class="form-control price-input" name="items[{{ $idx }}][price]" value="{{ old('items.'.$idx.'.price', $defPrice) }}" required placeholder="Rp. 0" oninput="formatPriceInput(this)">
                                                </td>
                                                <td style="min-width:260px;">
                                                    <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:#4b5563;cursor:pointer;">
                                                        <input type="checkbox" class="diff-toggle" onchange="document.getElementById('spec-diff-{{ $idx }}').style.display = this.checked ? 'block' : 'none'" {{ $defSpec ? 'checked' : '' }}>
                                                        <span>Terdapat perbedaan Spesifikasi?</span>
                                                    </label>
                                                    <div id="spec-diff-{{ $idx }}" style="display:{{ $defSpec ? 'block' : 'none' }};margin-top:6px;">
                                                        <input type="text" class="form-control" name="items[{{ $idx }}][specification]" value="{{ old('items.'.$idx.'.specification', $defSpec) }}" placeholder="Tuliskan spesifikasi yang Anda tawarkan..." style="font-size:12px;padding:8px 10px;">
                                                    </div>
                                                    <div style="margin-top:6px;">
                                                        <textarea class="form-control" name="items[{{ $idx }}][notes]" rows="2" placeholder="Catatan untuk item ini (opsional)..." style="font-size:12px;padding:8px 10px;resize:vertical;">{{ old('items.'.$idx.'.notes', $defNotes) }}</textarea>
                                                    </div>
                                                </td>
                                            </tr>
                                            @php $idx++; @endphp
                                        @endforeach
                                    @endforeach
                                @else
                                    @foreach($items as $item)
                                        @php
                                            $ex = $existingItems[$item->id] ?? null;
                                            $defQty = $ex ? $ex->offered_quantity : $item->quantity;
                                            $defUnit = $ex ? $ex->offered_unit : $item->unit;
                                            $defPrice = $ex ? $ex->offered_price_per_item : '';
                                            $defSpec = $ex ? $ex->offered_specification : '';
                                            $defNotes = $ex ? $ex->item_notes : '';
                                        @endphp
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->name ?? $item->item_name }}</strong>
                                                <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                                <div style="color:var(--text-muted); font-size:12px; margin-top:4px;">{{ $item->specification ?? '-' }}</div>
                                                @if(isset($item->item_notes) && $item->item_notes)
                                                    <div style="background:#e0f2fe;border:1px solid #38bdf8;color:#0369a1;padding:6px 10px;border-radius:4px;font-size:11.5px;margin-top:8px;">
                                                        <strong>User Note:</strong> {{ $item->item_notes }}
                                                    </div>
                                                @endif

                                                    <div style="margin-top:8px;">
                                                        <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:#4b5563;cursor:pointer;">
                                                            <input type="checkbox" class="diff-toggle" onchange="document.getElementById('spec-diff-{{ $idx }}').style.display = this.checked ? 'block' : 'none'" {{ $defSpec ? 'checked' : '' }}>
                                                            <span>Terdapat perbedaan Spesifikasi?</span>
                                                        </label>
                                                        <div id="spec-diff-{{ $idx }}" style="display:{{ $defSpec ? 'block' : 'none' }};margin-top:8px;">
                                                            <input type="text" class="form-control" name="items[{{ $idx }}][specification]" value="{{ old('items.'.$idx.'.specification', $defSpec) }}" placeholder="Tuliskan spesifikasi yang Anda tawarkan..." style="font-size:12px;padding:8px 10px;">
                                                        </div>
                                                    </div>
                                                    <div style="margin-top:8px;">
                                                        <textarea class="form-control" name="items[{{ $idx }}][notes]" rows="2" placeholder="Catatan untuk item ini (opsional)..." style="font-size:12px;padding:8px 10px;resize:vertical;">{{ old('items.'.$idx.'.notes', $defNotes) }}</textarea>
                                                    </div>
                                            </td>
                                            <td>
                                                <div style="font-size:11px;color:#6b7280;margin-bottom:4px;font-weight:600;">Target: {{ $item->quantity }} {{ $item->unit }}</div>
                                                <div style="display:flex;align-items:center;gap:6px;">
                                                    <input type="number" step="0.01" class="form-control" name="items[{{ $idx }}][quantity]" value="{{ old('items.'.$idx.'.quantity', $defQty) }}" required style="width:80px; text-align:center;">
                                                    <select class="form-control" name="items[{{ $idx }}][unit]" required style="width:85px; padding:8px;">
                                                        @php
                                                            $baseUnits = ['Pcs', 'Unit', 'Box', 'Kg', 'Liter', 'Meter', 'Roll', 'Set', 'Lot', 'Jasa', 'Pack'];
                                                            $itemUnit = ucfirst(strtolower(trim($defUnit)));
                                                            if (!in_array($itemUnit, $baseUnits)) array_unshift($baseUnits, $itemUnit);
                                                        @endphp
                                                        @foreach($baseUnits as $u)
                                                            <option value="{{ $u }}" {{ strtolower(trim(old('items.'.$idx.'.unit') ?? $defUnit)) == strtolower(trim($u)) ? 'selected' : '' }}>{{ $u }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" inputmode="decimal" class="form-control price-input" name="items[{{ $idx }}][price]" value="{{ old('items.'.$idx.'.price', $defPrice) }}" required placeholder="Rp. 0" oninput="formatPriceInput(this)">
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
                        <label class="form-label" style="font-weight:700;">Upload Quotation Document (Optional)</label>
                        <p style="font-size:11px; color:var(--text-muted); margin-bottom:8px;">Supported formats: PDF, Excel (xlsx, xls), JPG, PNG. Max size: 10MB.</p>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.xlsx,.xls,.jpg,.jpeg,.png" style="padding:10px;">
                        @error('attachment')
                            <div style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</div>
                        @enderror
                        @if($rfq->quotations && $rfq->quotations->where('vendor_id', session('last_vendor_id'))->first() && $rfq->quotations->where('vendor_id', session('last_vendor_id'))->first()->attachment_path)
                            <div style="margin-top:8px; font-size:12px; color:#10b981;">
                                ✓ You have previously uploaded a document. Uploading a new one will replace it.
                            </div>
                        @endif
                    </div>
                </div>


                <div class="card-body" style="background:#f9fafb; border-top:1px solid var(--border); text-align:right;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 15px;">Submit Quotation</button>
                    <p style="font-size:11px; color:var(--text-muted); margin-top:10px;">By submitting, you agree to provide the items at the quoted prices.</p>
                </div>
            </div>
        </form>
        @endif
        
        <script>
            let selectedVendor = null;
            const autocompleteDropdown = document.getElementById('autocomplete-dropdown');
            const vendorNameInput = document.getElementById('vendor_name_input');
            const vendorIdInput = document.getElementById('vendor_id_input');
            const vendorLocationInput = document.getElementById('vendor_location');
            const vendorContactInput = document.getElementById('vendor_contact');
            const statusBadge = document.getElementById('vendor-status-badge');
            const clearBtn = document.getElementById('clear-vendor-btn');

            let debounceTimeout = null;

            if (vendorNameInput) {
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

                    if (!selectedVendor) {
                        vendorIdInput.value = '';
                        vendorLocationInput.value = '';
                        vendorContactInput.value = '';
                    }

                    clearTimeout(debounceTimeout);
                    const query = newVal.trim();
                    if (query.length < 2 || selectedVendor) {
                        autocompleteDropdown.style.display = 'none';
                        return;
                    }

                    debounceTimeout = setTimeout(() => {
                        fetch(`/vendor-portal/autocomplete?q=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.length === 0) {
                                    autocompleteDropdown.style.display = 'none';
                                    return;
                                }
                                
                                const exactMatch = data.find(v => v.vendor_name.toLowerCase() === query.toLowerCase());
                                if (exactMatch) {
                                    selectVendor(exactMatch.id, exactMatch.vendor_name, exactMatch.location, exactMatch.email);
                                    return;
                                }

                                autocompleteDropdown.innerHTML = data.map(v => `
                                    <div class="item-option" style="padding:10px 14px; cursor:pointer; border-bottom:1px solid var(--border);" onclick="selectVendor(${v.id}, '${v.vendor_name.replace(/'/g, "\\'")}', '${v.location}', '${v.email}')">
                                        <div style="font-weight:600; color:var(--primary); font-size:13.5px;">${v.vendor_name}</div>
                                        <div style="font-size:11.5px; color:var(--text-muted); margin-top:2px;">Location: ${v.location} | Email: ${v.email || '-'}</div>
                                    </div>
                                `).join('');
                                autocompleteDropdown.style.display = 'block';
                            });
                    }, 300);
                });

                // Prevent submitting on Enter inside name input
                vendorNameInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.blur();
                    }
                });
            }

            function selectVendor(id, name, location, email) {
                selectedVendor = { id, name, location, email };
                
                vendorIdInput.value = id;
                vendorNameInput.value = name;
                vendorNameInput.readOnly = true;
                
                vendorLocationInput.value = location;
                vendorLocationInput.readOnly = true;
                vendorLocationInput.style.background = '#f1f5f9';
                
                vendorContactInput.value = email;
                vendorContactInput.readOnly = true;
                vendorContactInput.style.background = '#f1f5f9';
                
                statusBadge.style.display = 'inline-flex';
                clearBtn.style.display = 'block';
                autocompleteDropdown.style.display = 'none';
            }

            function resetVendorSelection() {
                selectedVendor = null;
                vendorIdInput.value = '';
                vendorNameInput.value = '';
                vendorNameInput.readOnly = false;
                
                vendorLocationInput.value = '';
                vendorLocationInput.readOnly = false;
                vendorLocationInput.style.background = '#fff';
                
                vendorContactInput.value = '';
                vendorContactInput.readOnly = false;
                vendorContactInput.style.background = '#fff';
                
                statusBadge.style.display = 'none';
                clearBtn.style.display = 'none';
                vendorNameInput.focus();
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (autocompleteDropdown && !autocompleteDropdown.contains(e.target) && e.target !== vendorNameInput) {
                    autocompleteDropdown.style.display = 'none';
                }
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

            // Pastikan value yang dikirim ke server tetap angka polos, bukan "1.500,75"
            const quoteForm = document.getElementById('quote-form');
            if (quoteForm) {
                quoteForm.addEventListener('submit', function() {
                    // Temporarily remove readonly/disabled so values are submitted
                    vendorLocationInput.readOnly = false;
                    vendorContactInput.readOnly = false;
                    
                    document.querySelectorAll('.price-input').forEach(input => {
                        input.value = parsePriceValue(input.value);
                    });
                });
            }
        </script>
    </div>
</body>
</html>