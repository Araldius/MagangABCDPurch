import re

with open('resources/views/quotations/edit.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace('Add Manual Quotation for', 'Edit Quotation for')
content = content.replace("route('quotations.store', $rfq->id)", "route('quotations.update', $quotation->id)")

# Replace Vendor Information section
vendor_section_old = """<div class="form-group flex-1" style="margin-bottom:16px;">
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
        </div>"""

vendor_section_new = """<div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Vendor Name</label>
            <div style="font-size:14px; color:#111827; font-weight:600;">{{ $quotation->vendor->vendor_name ?? $quotation->vendor->name }}</div>
            <div style="font-size:12px; color:#6b7280; margin-top:4px;">{{ $quotation->vendor->email ?? '-' }}</div>
        </div>"""
content = content.replace(vendor_section_old, vendor_section_new)
content = content.replace('<input type="hidden" name="vendor_id" id="hidden_vendor_id" value="">', '')

# Pre-populate Items
tbody_old = """                <tbody>
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
                                    
                                    <div style="margin-top:6px;">
                                        <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:#4b5563;cursor:pointer;">
                                            <input type="checkbox" class="note-toggle" onchange="document.getElementById('note-item-{{ $idx }}').style.display = this.checked ? 'block' : 'none'">
                                            <span>Tambahkan catatan/notes</span>
                                        </label>
                                        <div id="note-item-{{ $idx }}" style="display:none;margin-top:8px;">
                                            <input type="text" class="form-control" name="items[{{ $idx }}][notes]" placeholder="Catatan untuk item ini..." style="font-size:12px;padding:8px 10px;">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:6px">
                                        <input type="number" class="form-control qty-input" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" min="1" step="any" style="width:70px;padding:8px;font-size:12px" oninput="calcTotal()">
                                        <span style="font-size:12px;color:var(--text-muted)">{{ $item->unit }}</span>
                                    </div>
                                    <input type="hidden" name="items[{{ $idx }}][unit]" value="{{ $item->unit }}">
                                </td>
                                <td>
                                    <input type="number" class="form-control price-input" name="items[{{ $idx }}][price]" placeholder="0" min="0" style="font-size:13px;padding:8px 10px;font-family:monospace" required oninput="calcTotal()">
                                </td>
                                <td style="text-align:right; font-weight:700; font-family:monospace; color:#111827" class="subtotal-text">Rp 0</td>
                            </tr>
                        @endforeach
                    @endforeach
                @else
                    @foreach($rfq->purchaseRequest->items as $item)
                        <tr>
                            <td>{{ ++$idx }}</td>
                            <td>
                                <strong>{{ $item->item_name }}</strong>
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
                                
                                <div style="margin-top:6px;">
                                    <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:#4b5563;cursor:pointer;">
                                        <input type="checkbox" class="note-toggle" onchange="document.getElementById('note-item-{{ $idx }}').style.display = this.checked ? 'block' : 'none'">
                                        <span>Tambahkan catatan/notes</span>
                                    </label>
                                    <div id="note-item-{{ $idx }}" style="display:none;margin-top:8px;">
                                        <input type="text" class="form-control" name="items[{{ $idx }}][notes]" placeholder="Catatan untuk item ini..." style="font-size:12px;padding:8px 10px;">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px">
                                    <input type="number" class="form-control qty-input" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" min="1" step="any" style="width:70px;padding:8px;font-size:12px" oninput="calcTotal()">
                                    <span style="font-size:12px;color:var(--text-muted)">{{ $item->unit }}</span>
                                </div>
                                <input type="hidden" name="items[{{ $idx }}][unit]" value="{{ $item->unit }}">
                            </td>
                            <td>
                                <input type="number" class="form-control price-input" name="items[{{ $idx }}][price]" placeholder="0" min="0" style="font-size:13px;padding:8px 10px;font-family:monospace" required oninput="calcTotal()">
                            </td>
                            <td style="text-align:right; font-weight:700; font-family:monospace; color:#111827" class="subtotal-text">Rp 0</td>
                        </tr>
                    @endforeach
                @endif
                </tbody>"""

tbody_new = """                <tbody>
                @foreach($quotation->details as $idx => $detail)
                    @php 
                        $item = $isService ? $detail->serviceRequestItem : $detail->purchaseRequestItem; 
                        $specDiff = $detail->offered_specification ? true : false;
                        $noteDiff = $detail->item_notes ? true : false;
                    @endphp
                    @if($item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <strong>{{ $item->item_name ?? $item->name }}</strong>
                                <input type="hidden" name="items[{{ $idx }}][detail_id]" value="{{ $detail->id }}">
                                <div style="color:var(--text-muted); font-size:12px; margin-top:4px;">{{ $item->specification ?? '-' }}</div>
                                
                                <div style="margin-top:8px;">
                                    <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:#4b5563;cursor:pointer;">
                                        <input type="checkbox" class="diff-toggle" {{ $specDiff ? 'checked' : '' }} onchange="document.getElementById('spec-diff-{{ $idx }}').style.display = this.checked ? 'block' : 'none'">
                                        <span>Terdapat perbedaan Spesifikasi/Unit?</span>
                                    </label>
                                    <div id="spec-diff-{{ $idx }}" style="display:{{ $specDiff ? 'block' : 'none' }};margin-top:8px;">
                                        <input type="text" class="form-control" name="items[{{ $idx }}][specification]" value="{{ $detail->offered_specification }}" placeholder="Tuliskan spesifikasi yang ditawarkan..." style="font-size:12px;padding:8px 10px;">
                                    </div>
                                </div>
                                
                                <div style="margin-top:6px;">
                                    <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:#4b5563;cursor:pointer;">
                                        <input type="checkbox" class="note-toggle" {{ $noteDiff ? 'checked' : '' }} onchange="document.getElementById('note-item-{{ $idx }}').style.display = this.checked ? 'block' : 'none'">
                                        <span>Tambahkan catatan/notes</span>
                                    </label>
                                    <div id="note-item-{{ $idx }}" style="display:{{ $noteDiff ? 'block' : 'none' }};margin-top:8px;">
                                        <input type="text" class="form-control" name="items[{{ $idx }}][notes]" value="{{ $detail->item_notes }}" placeholder="Catatan untuk item ini..." style="font-size:12px;padding:8px 10px;">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px">
                                    <input type="number" class="form-control qty-input" name="items[{{ $idx }}][quantity]" value="{{ $detail->offered_quantity }}" min="1" step="any" style="width:70px;padding:8px;font-size:12px" oninput="calcTotal()">
                                    <span style="font-size:12px;color:var(--text-muted)">{{ $detail->offered_unit ?? $item->unit }}</span>
                                </div>
                                <input type="hidden" name="items[{{ $idx }}][unit]" value="{{ $detail->offered_unit ?? $item->unit }}">
                            </td>
                            <td>
                                <input type="number" class="form-control price-input" name="items[{{ $idx }}][price]" value="{{ floatval($detail->offered_price_per_item) }}" placeholder="0" min="0" style="font-size:13px;padding:8px 10px;font-family:monospace" required oninput="calcTotal()">
                            </td>
                            <td style="text-align:right; font-weight:700; font-family:monospace; color:#111827" class="subtotal-text">Rp {{ number_format($detail->offered_price_per_item * $detail->offered_quantity, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                @endforeach
                </tbody>"""

content = content.replace(tbody_old, tbody_new)

# Replace <textarea name="note"> with prepopulated value
content = content.replace('<textarea name="note" class="form-control"', f'<textarea name="note" class="form-control">{{{{ $quotation->note }}}}')
content = content.replace('>></textarea>', '></textarea>')
content = re.sub(r'<textarea name="note" class="form-control" placeholder="Optional general notes..."></textarea>', r'<textarea name="note" class="form-control" placeholder="Optional general notes...">{{ $quotation->note }}</textarea>', content)

# Remove Vendor Modal HTML & JS since we don't need to change vendor
modal_code = """<div class="modal-overlay" id="vendorModal">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Select Vendor</h3>
                <div class="modal-desc">Choose a vendor from the master catalog</div>
            </div>
            <button type="button" class="modal-close" onclick="closeVendorModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding:12px 20px">
            <input type="text" id="vendorSearch" class="form-control" placeholder="Search vendor..." style="margin-bottom:14px;padding:8px 12px;font-size:13px" oninput="filterVendors()">
            <div id="vendorList">
                <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px">Loading vendors...</div>
            </div>
        </div>
    </div>
</div>"""

content = content.replace(modal_code, '')

with open('resources/views/quotations/edit.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
