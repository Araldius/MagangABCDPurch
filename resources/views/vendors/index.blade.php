@extends('layouts.app')
@php $pageTitle = 'Vendor Selection'; @endphp
@section('content')
 
<style>
/* Base Styles */
h1 { font-size:20px;font-weight:700;color:#111827;margin:0 0 3px }
.desc { font-size:12.5px;color:#6b7280;margin:0 }
.card-box { background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:20px }
.btn-primary { display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:#3b5bdb;color:#fff;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;white-space:nowrap;transition:background .2s }
.btn-primary:hover { background:#3451c7 }
.btn-outline { padding:6px 14px;border:1px solid #d1d5db;border-radius:7px;background:#fff;font-size:12.5px;font-weight:600;color:#374151;cursor:pointer }
.btn-back { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border:1px solid #e5e7eb;border-radius:7px;background:#fff;font-size:12.5px;font-weight:600;color:#374151;cursor:pointer }

/* Page level width protection */
#step1-card, #selection-workspace, #result-workspace {
    width: 100%;
    max-width: 100%;
    overflow: hidden;
    box-sizing: border-box;
}

/* ============================================================
   SIDE-BY-SIDE LAYOUT: Left (sticky-feel) + Right (scrollable)
   ============================================================ */
#vs-split-wrapper {
    display: flex;
    align-items: flex-start;
    gap: 0;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    margin-bottom: 14px;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

/* LEFT PANEL: Item Requirements */
#vs-left-panel {
    flex: 0 0 420px;
    min-width: 420px;
    background: #fff;
    border-right: 2px solid #e5e7eb;
    box-shadow: 4px 0 12px rgba(0,0,0,0.06);
    border-radius: 12px 0 0 12px;
    overflow: hidden;
}

#vs-left-panel .panel-header {
    padding: 14px 18px;
    border-bottom: 1px solid #f3f4f6;
    background: #f9fafb;
}

/* RIGHT PANEL: Vendor Cards - scrollable */
#vs-right-panel {
    display: flex;
    flex: 1;
    min-width: 0;
    max-width: calc(100% - 420px);
    overflow-x: auto;
    align-items: flex-start;
}

/* Each Vendor Card */
.vendor-card {
    flex: 0 0 380px;
    min-width: 380px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #fafafa;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 100%;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}
.vendor-card-header {
    padding: 14px;
    border-bottom: 1px solid #e5e7eb;
    background: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 60px;
    box-sizing: border-box;
    flex-shrink: 0;
}
.vendor-card-body {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.vendor-card-footer {
    padding: 12px 14px;
    border-top: 1px solid #e5e7eb;
    background: #fff;
    font-size: 12.5px;
    font-weight: 700;
    color: #111827;
}

/* ============================================================
   ROW ALIGNMENT: Each row in left panel & right cards
   must have the same height.
   Item row height = 52px header + 245px per item.
   ============================================================ */

.req-item-card {
min-height: 180px;   /* atur sesuai kebutuhan, makin besar makin panjang ke bawah */
 padding: 16px;       /* biar isi kontennya juga lebih lega, opsional */
}

.req-job-header {
    padding: 12px 14px;
    min-height: 90px;
    font-size: 12.5px;
}

/* Left panel header row */
.req-row-header {
    height: 52px;
    box-sizing: border-box;
    display: flex;
    align-items: center;
    padding: 0 18px;
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
    font-size: 10.5px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Right panel: vendor card rows */
.vc-row-header {
    padding: 8px 14px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: 10.5px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    flex-shrink: 0;
}
.vc-row-service {
    padding: 10px 12px;
    background: #e2e8f0;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.vc-row-job {
    padding: 6px 12px;
    background: #f1f5f9;
    border-radius: 4px;
    font-size: 11.5px;
    font-weight: 600;
    color: #475569;
    flex-shrink: 0;
}
.vc-row-item {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    box-sizing: border-box;
    transition: all 0.2s;
    flex-shrink: 0;
    height: 180px;
}
.vc-row-item:hover {
    border-color: #cbd5e1;
}

/* Table fallback (result view) */
.req-table { width:100%;border-collapse:collapse;font-size:12.5px }
.req-table th { padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;background:#f9fafb }
.req-table td { padding:11px 16px;border-bottom:1px solid #f3f4f6 }
.tr-service td { background:#f3f4f6; font-weight:700; color:#111827; border-bottom:2px solid #e5e7eb; padding:8px 16px; font-size:13px; }
.tr-job td { background:#f9fafb; font-weight:600; color:#374151; padding:8px 16px 8px 30px; font-size:12px; border-bottom:1px dashed #e5e7eb; }
.tr-item td { padding:10px 16px 10px 45px; }
</style>

<div style="margin-bottom:20px">
    <h1>Vendor Selection</h1>
    <p class="desc">Select vendor. Divide the quantity to several vendors if stock is insufficient.</p>
</div>
 
{{-- STEP 1: SELECT PR/SR --}}
<div class="card-box" id="step1-card">
    <div style="font-size:13.5px;font-weight:700;color:#111827;margin-bottom:14px">Select Request Number</div>
    <div style="display:flex;gap:10px;align-items:center">
        <div style="flex:1;position:relative">
            <select id="pr-select"
                style="width:100%;padding:9px 32px 9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit">
                <option value="">Select PR/SR number to view vendor offers</option>
                @foreach($prs as $pr)
                <option value="{{ $pr->type }}_{{ $pr->id }}"
                    {{ isset($selectedKey) && $selectedKey === $pr->type.'_'.$pr->id ? 'selected' : '' }}>
                    {{ $pr->document_number }} | {{ $pr->title }}
                </option>
                @endforeach
            </select>
            <svg style="position:absolute;right:10px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
        <button class="btn-primary" onclick="loadPR(document.getElementById('pr-select').value)">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35" stroke-linecap="round"/></svg>
            Show Vendors
        </button>
    </div>
</div>
 
{{-- STEP 2+: Selection workspace --}}
<div id="selection-workspace" style="display:none">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
        <div>
            <h2 id="ws-title" style="font-size:16px;font-weight:700;color:#111827;margin:0"></h2>
            <p id="ws-sub" style="font-size:12px;color:#6b7280;margin:2px 0 0"></p>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
            <span id="ws-status-badge" style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;background:#fff7ed;font-size:12px;font-weight:600;color:#c2410c">
                <span style="width:6px;height:6px;border-radius:50%;background:#f97316"></span>Awaiting Selection
            </span>
            <button class="btn-outline" onclick="exportQuotations()" style="display:inline-flex;align-items:center;gap:6px;padding:7px 12px;color:#059669;border-color:#059669;background:#ecfdf5;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16" stroke-linecap="round" stroke-linejoin="round"/></svg> Export Excel
            </button>
            <button class="btn-back" onclick="backToStep1()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Back
            </button>
        </div>
    </div>
 
    {{-- Requirements table + Vendor cards, side by side --}}
    <div style="display:flex;align-items:stretch;gap:16px;margin-bottom:14px;width:100%;min-width:0;overflow:hidden;">
        {{-- LEFT: Requirements table (tetap di tempat, tidak ikut geser) --}}
        <div id="vs-left-col" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;width:400px;flex-shrink:0;display:flex;flex-direction:column;min-height:400px;">
        <div class="vendor-card-header" style="background:#f9fafb;">
            <div>
                <div style="font-size:13.5px;font-weight:700;color:#111827">Item / Service Requirements</div>
                <div id="ws-item-count" style="font-size:11.5px;color:#6b7280;margin-top:1px"></div>
            </div>
            <div style="font-size:12px;color:#6b7280">Items Fulfilled: <span id="sel-count" style="font-weight:700;color:#111827">0</span> of <span id="sel-total" style="font-weight:700;color:#111827">0</span></div>
        </div>
                <div id="items-requirement-tbody" class="vendor-card-body"></div>
        </div>

        {{-- RIGHT: Vendor cards grid (carousel, tetap bisa digeser kiri-kanan) --}}
        <div id="vendor-cards-grid" style="display:flex;overflow-x:auto;gap:16px;padding-bottom:12px;scroll-snap-type:x mandatory;flex:1;min-width:0;min-height:400px;"></div>
    </div>
 
    {{-- Footer bar --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px 18px;display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-size:12.5px;font-weight:600;color:#111827">Target Items fulfilled: <span id="footer-sel">0</span> / <span id="footer-total">0</span></div>
            <div style="font-size:11.5px;color:#9ca3af;margin-top:1px">Sistem akan memperingatkan jika Anda submit sebelum semua item/quantity terpenuhi</div>
        </div>
        <button id="show-result-btn" onclick="showSelectionResult()"
            style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:#111827;color:#fff;border-radius:8px;font-size:12.5px;font-weight:600;border:none;cursor:pointer;opacity:.4;pointer-events:none"
            onmouseover="this.style.opacity='1'">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
            Review & Submit
        </button>
    </div>
</div>

{{-- STEP 3: Selection Result / Summary --}}
<div id="result-workspace" style="display:none">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
        <div>
            <div id="res-pr-label" style="font-size:14px;font-weight:700;color:#111827;margin-top:3px"></div>
            <div style="font-size:12px;color:#6b7280;margin-top:2px">Review final split PO Anda sebelum diproses oleh Purchasing</div>
        </div>
        <button onclick="document.getElementById('selection-workspace').style.display='block'; document.getElementById('result-workspace').style.display='none';" class="btn-back">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Edit Selection
        </button>
    </div>
 
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:14px;overflow:hidden;">
        <div style="padding:14px 18px;border-bottom:1px solid #f3f4f6;font-size:13.5px;font-weight:700;color:#111827">Selected Items</div>
        <div style="overflow-x:auto">
            <table class="req-table">
                <thead><tr>
                    <th>NO</th><th>ITEM NAME</th><th>VENDOR</th><th style="text-align:right">BUY QTY</th><th>UNIT</th><th style="text-align:right">PRICE (RP)</th><th style="text-align:right">SUBTOTAL (RP)</th>
                </tr></thead>
                <tbody id="selected-items-tbody"></tbody>
                <tfoot>
                    <tr style="background:#f9fafb">
                        <td colspan="6" style="padding:10px 14px;text-align:right;font-weight:700;font-size:12.5px;color:#374151;border-top:1px solid #e5e7eb">Grand Total:</td>
                        <td id="grand-total-cell" style="padding:10px 14px;font-weight:800;font-size:13px;color:#111827;border-top:1px solid #e5e7eb;text-align:right">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
 
    <div style="margin-bottom:14px">
        <div style="font-size:11.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px">Total per Vendor</div>
        <div id="vendor-summary-cards" style="display:flex;gap:12px;overflow-x:auto;padding-bottom:8px;"></div>
    </div>
 
    <div style="display:flex;justify-content:flex-end;margin-top:20px">
        <button onclick="openSubmitModal()" style="display:inline-flex;align-items:center;gap:6px;padding:12px 24px;background:#16a34a;color:#fff;border-radius:8px;font-size:14px;font-weight:700;border:none;cursor:pointer;box-shadow:0 4px 6px -1px rgba(22,163,74,.2)">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Confirm &amp; Submit
        </button>
    </div>
</div>

{{-- MODALS --}}
<div id="warning-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:400;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px)"><div style="background:#fff;border-radius:12px;width:100%;max-width:440px;box-shadow:0 10px 40px rgba(0,0,0,.2);overflow:hidden"><div style="background:#fef2f2;padding:20px;border-bottom:1px solid #fee2e2;display:flex;align-items:center;gap:14px"><div style="width:44px;height:44px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#ef4444;flex-shrink:0"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div><div><div style="font-size:16px;font-weight:700;color:#991b1b;line-height:1.2">Peringatan Kuantitas</div><div style="font-size:12.5px;color:#b91c1c;margin-top:2px">Target Qty belum sepenuhnya terpenuhi</div></div></div><div style="padding:22px;font-size:13.5px;color:#374151;line-height:1.6">Masih ada item yang kuantitasnya <strong>BELUM TERPENUHI</strong>.<br>Apakah Anda yakin ingin mengabaikannya dan melanjutkan?</div><div style="padding:16px 22px;border-top:1px solid #f3f4f6;background:#f9fafb;display:flex;justify-content:flex-end;gap:10px"><button onclick="closeWarningModal()" class="btn-back"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Batalkan</button><button onclick="forceShowSelectionResult()" style="padding:9px 18px;background:#ef4444;color:#fff;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;">Ya, Lanjutkan</button></div></div></div>
<div id="submit-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:20px"><div style="background:#fff;border-radius:12px;width:100%;max-width:440px;"><div style="padding:18px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:flex-start;justify-content:space-between"><div><div style="font-size:14px;font-weight:700;color:#111827">Submission Notes</div></div><button onclick="closeSubmitModal()" style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px">✕</button></div><div style="padding:18px 20px"><textarea id="submit-notes" rows="4" placeholder="Catatan untuk tim Purchasing..." style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:inherit;resize:vertical;outline:none"></textarea></div><div style="padding:14px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:flex-end;gap:10px"><button onclick="closeSubmitModal()" class="btn-back"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Cancel</button><button onclick="submitToServer()" style="padding:7px 18px;background:#16a34a;color:#fff;border-radius:7px;font-size:12.5px;font-weight:600;border:none;cursor:pointer;">Final Submit</button></div></div></div>
<div id="success-popup" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:300;align-items:center;justify-content:center;padding:20px"><div style="background:#fff;border-radius:12px;padding:32px;width:100%;max-width:400px;text-align:center;"><div style="font-size:22px;font-weight:700;color:#16a34a;margin-bottom:12px">Success!</div><div style="font-size:13px;color:#374151;margin-bottom:4px">PR/SR: <span id="popup-pr" style="font-weight:700"></span></div><button onclick="closeSuccess()" style="margin-top:20px;padding:8px 24px;border:1px solid #d1d5db;border-radius:8px;background:#fff;font-size:13px;font-weight:600;cursor:pointer">Close</button></div></div>

<script>
// Data dikirim secara flat dari Controller untuk mencegah ID Conflict PR dan SR
const serverRequests = @json($prs);
const serverVendors = @json($vendors);

let currentPR = null;
let selections = {}; 
let vendorOffers = {};
let bestPriceMap = {};
let bestServiceVendor = null;

function fmt(n){return 'Rp '+Number(n).toLocaleString('id-ID');}

function buildVendorOffers(pr, vendors) {
    const offers = {};
    vendors.forEach(v => { offers[v.id] = { items: {} }; });
    
    let rfqs = pr.rfqs || pr.rfq || [];
    if (!Array.isArray(rfqs)) rfqs = [rfqs];
    
    rfqs.forEach(rfq => {
        if (!rfq) return;
        let quots = rfq.quotations || rfq.vendorQuotations || rfq.vendor_quotations || [];
        if (!Array.isArray(quots)) quots = [quots];
        
        quots.forEach(quot => {
            if (!quot) return;
            const vId = quot.vendor_id;
            let details = quot.details || quot.quotation_details || quot.quotationDetails || [];
            if (!Array.isArray(details)) details = [details];
            
            if (offers[vId]) {
                offers[vId].quotation_id = quot.id;
                offers[vId].attachment = quot.attachment_path || quot.attachment;
                details.forEach(det => {
                    const itemId = det.purchase_request_item_id || det.service_request_item_id;
                    if (itemId) {
                        offers[vId].items[itemId] = {
                            qty_offered: det.offered_quantity || det.quantity || 0,
                            unit_price: det.offered_price_per_item || det.price || 0,
                            unit_offered: det.offered_unit || '',
                            notes: det.notes || det.item_notes || '',
                            brand_offered: det.offered_brand || '',
                            specification_offered: det.offered_specification || ''
                        };
                    }
                });
            }
        });
    });
    return offers;
}

function computeBestPrices() {
    bestPriceMap = {};
    bestServiceVendor = null;
    
    currentPR.items.forEach(item => {
        let minPrice = Infinity;
        serverVendors.forEach(v => {
            const o = vendorOffers[v.id]?.items[item.id];
            if (o && parseFloat(o.unit_price) > 0 && o.unit_price < minPrice) {
                minPrice = parseFloat(o.unit_price);
            }
        });
        if (minPrice < Infinity) bestPriceMap[item.id] = minPrice;
    });

    if (currentPR.type === 'service') {
        let minTotal = Infinity;
        serverVendors.forEach(v => {
            let total = 0;
            let hasOffer = false;
            currentPR.items.forEach(item => {
                const o = vendorOffers[v.id]?.items[item.id];
                if (o) {
                    total += parseFloat(o.unit_price) * parseFloat(item.quantity);
                    hasOffer = true;
                }
            });
            if (hasOffer && total < minTotal && total > 0) {
                minTotal = total;
                bestServiceVendor = v.id;
            }
        });
    }
}

function syncWorkspaceHeight() {
    const grid = document.getElementById('vendor-cards-grid');
    const leftCol = document.getElementById('vs-left-col');
    if (!grid || grid.offsetParent === null) return;

    const topOffset = grid.getBoundingClientRect().top;
    const footerBar = document.querySelector('#selection-workspace > div:last-child');
    const footerHeight = footerBar ? footerBar.offsetHeight + 24 : 90;

    const availableHeight = Math.max(400, window.innerHeight - topOffset - footerHeight);

    grid.style.height = availableHeight + 'px';
    if (leftCol) leftCol.style.height = availableHeight + 'px';
}

window.addEventListener('resize', syncWorkspaceHeight);

function loadPR(uniqueKey) {
    if (!uniqueKey) return;
    const [type, id] = uniqueKey.split('_');
    currentPR = serverRequests.find(r => r.type === type && r.id == id);
    if (!currentPR) return;
    selections = {};
    
    // PRELOAD existing selections if any (for re-open flow)
    if (currentPR.rfqs && currentPR.rfqs.length > 0) {
        currentPR.rfqs.forEach(rfq => {
            if (rfq.vendor_selections) {
                rfq.vendor_selections.forEach(sel => {
                    if (sel.selection_items) {
                        sel.selection_items.forEach(si => {
                            const pItem = (currentPR.type === 'service' ? (currentPR.jobs ? currentPR.jobs.flatMap(j=>j.items||[]) : []) : (currentPR.items || [])).find(i => i.id == (si.purchase_request_item_id || si.service_request_item_id));
                            if (pItem) {
                                selections[`${sel.vendor_id}_${pItem.id}`] = {
                                    vendor_id: sel.vendor_id,
                                    item_id: pItem.id,
                                    item_name: pItem.item_name || pItem.name,
                                    unit_price: parseFloat(si.final_price_per_item || si.unit_price || 0),
                                    quantity: parseFloat(si.final_quantity || si.quantity || 0),
                                    unit: si.offered_unit || pItem.unit,
                                    subtotal: parseFloat(si.quantity) * parseFloat(si.unit_price),
                                    notes: si.notes || '',
                                    brand: si.final_brand || '',
                                    specification: si.offered_specification || ''
                                };
                            }
                        });
                    }
                });
            }
        });
    }

    let flatItems = [];
    if (currentPR.type === 'service') {
        flatItems = currentPR.jobs ? currentPR.jobs.flatMap(j => j.items || []) : [];
        currentPR.items = flatItems; 
    } else {
        flatItems = currentPR.items || [];
    }

    vendorOffers = buildVendorOffers(currentPR, serverVendors);
    computeBestPrices();
 
    document.getElementById('selection-workspace').style.display='block';
    document.getElementById('result-workspace').style.display='none';
    
    document.getElementById('ws-title').textContent='Vendor Selection: '+ currentPR.display_doc;
    document.getElementById('ws-sub').textContent= currentPR.display_doc +' | '+ currentPR.display_title;
    document.getElementById('ws-item-count').textContent=flatItems.length+' items/services required';
    document.getElementById('sel-total').textContent=flatItems.length;
    document.getElementById('footer-total').textContent=flatItems.length;
    
    renderRequirementsTable();
    renderVendorCards();
    updateCounts();
    syncWorkspaceHeight();
}

function exportQuotations() {
    if (!currentPR) return;
    window.location.href = `{{ url('vendor-selection/export') }}?id=${currentPR.id}&type=${currentPR.type}`;
}

function backToStep1(){
    document.getElementById('selection-workspace').style.display='none';
    document.getElementById('result-workspace').style.display='none';
    document.getElementById('step1-card').style.display='block';
    currentPR=null; selections={};
}

function getItemStatus(itemId) {
    const item = currentPR.items.find(i => i.id == itemId);
    if (!item) return ['Pending','#fff7ed','#c2410c','#f97316'];
    // FIX: parseFloat prevents string-concat bug ("0"+"1.00"="01.00") when quantity comes as string from DB
    let totalSel = 0;
    for (let key in selections) {
        if (selections[key].item_id == itemId) totalSel += parseFloat(selections[key].quantity) || 0;
    }
    const target = parseFloat(item.quantity) || 0;

    if (totalSel === 0) return ['Pending','#fff7ed','#c2410c','#f97316'];
    if (totalSel < target) return [`Partial (${totalSel}/${target})`,'#fef9c3','#854d0e','#eab308'];
    if (totalSel > target) return [`Over (${totalSel}/${target})`,'#dbeafe','#1d4ed8','#3b82f6'];
    return ['Full Match','#f0fdf4','#15803d','#22c55e'];
}

function getRowBg(label) {
    if (label === 'Full Match') return '#f0fdf4';
    if (label.startsWith('Partial')) return '#fefce8';
    if (label.startsWith('Over')) return '#eff6ff';
    if (label !== 'Pending') return '#fff5f5';
    return '#fff';
}

function requirementCardHtml(item, label, bg, tc, dot) {
    const isSelected = label !== 'Pending';
    const cardBg = isSelected ? bg : '#fff';
    const cardBorder = isSelected ? dot : '#e2e8f0';

    return `<div class="vc-row-item req-item-card" style="border:1px solid ${cardBorder};border-left:4px solid ${cardBorder};background:${cardBg};">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:8px">
            <div>
                <div style="font-family:monospace;font-size:13px;font-weight:700;color:#3b5bdb;margin-bottom:3px;">${item.item_id || '—'}</div>
                <div style="font-size:14.5px;font-weight:700;color:#111827;">${item.item_name}</div>
            </div>
            <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;background:${isSelected ? '#fff' : bg};font-size:10.5px;font-weight:700;color:${tc};white-space:nowrap;flex-shrink:0;">
                <span style="width:5px;height:5px;border-radius:50%;background:${dot}"></span>${label}
            </span>
        </div>
        <div style="display:grid;grid-template-columns:60px 1fr;gap:6px 8px;align-items:center;font-size:12.5px;">
            <div style="color:#9ca3af">Qty</div>
            <div style="font-weight:700;color:#111827">${item.quantity} ${item.unit}</div>
            <div style="color:#9ca3af">Brand</div>
            <div style="color:#111827;font-size:14px;font-weight:800;">${item.brand || '-'}</div>
            <div style="color:#9ca3af">Spec</div>
            <div style="color:#111827;font-size:12px;">${item.specification || '-'}</div>
            <div style="color:#9ca3af">Notes</div>
            <div style="color:#6b7280;font-style:italic;font-size:12px;">${item.item_notes || item.notes || '-'}</div>
        </div>
    </div>`;
}

function renderRequirementsTable(){
    const container = document.getElementById('items-requirement-tbody');
    let html = '';

    if (currentPR.type === 'service') {
        currentPR.jobs.forEach(job => {
            html += `<div class="vc-row-job req-job-header">💼 ${job.job_description}</div>`;
            job.items.forEach(item => {
                const [label,bg,tc,dot] = getItemStatus(item.id);
                html += requirementCardHtml(item, label, bg, tc, dot);
            });
        });
    } else {
        currentPR.items.forEach(item => {
            const [label,bg,tc,dot] = getItemStatus(item.id);
            html += requirementCardHtml(item, label, bg, tc, dot);
        });
    }

    container.innerHTML = html;
    syncWorkspaceHeight();
}

function toggleVendorService(vId, isChecked) {
    if (isChecked) {
        selections = {}; // Paksa Radio Button (hanya 1 vendor untuk service)
        currentPR.items.forEach(item => {
            const offer = vendorOffers[vId].items[item.id];
            if (offer) {
                selections[`${vId}_${item.id}`] = {
                    vendor_id: vId,
                    item_id: item.id,
                    item_name: item.item_name,
                    unit_price: offer.unit_price,
                    quantity: item.quantity,
                    unit: item.unit,
                    subtotal: item.quantity * offer.unit_price
                };
            }
        });
    } else {
        for (let key in selections) {
            if (selections[key].vendor_id == vId) {
                delete selections[key];
            }
        }
    }

    renderRequirementsTable();
    renderVendorCards();
    updateCounts();
}

function renderVendorCards(){
    const grid = document.getElementById('vendor-cards-grid');

    // Filter vendors that have offered at least 1 item for this PR
    const activeVendors = serverVendors.filter(v => {
        const off = vendorOffers[v.id];
        if (!off) return false;
        const items = currentPR.items || [];
        return items.some(item => off.items[item.id] != null);
    });

    if (activeVendors.length === 0) {
        grid.innerHTML = `<div style="padding:40px;text-align:center;color:#9ca3af;font-size:13px;min-width:300px;">Tidak ada vendor yang menawarkan item untuk request ini.</div>`;
        return;
    }

    grid.innerHTML = activeVendors.map(v => {
        const vName = v.vendor_name || v.name || 'Vendor';
        const off = vendorOffers[v.id];

        let wasPreviouslySelected = false;
        if (currentPR.rfqs) {
            currentPR.rfqs.forEach(rfq => {
                if (rfq.vendor_selections && rfq.vendor_selections.some(sel => sel.vendor_id == v.id)) {
                    wasPreviouslySelected = true;
                }
            });
        }
        const prevBadge = wasPreviouslySelected ? `<span style="background:#fef08a;color:#854d0e;padding:2px 6px;border-radius:4px;font-size:9.5px;font-weight:700;margin-left:8px;">PREV. SELECTED</span>` : '';

        let isVendorChecked = Object.values(selections).some(s => s.vendor_id == v.id);
        let bodyHtml = '';

        if (currentPR.type === 'service') {
            const isBestService = (bestServiceVendor == v.id);
            const allSelected = currentPR.items.every(i => selections[`${v.id}_${i.id}`]);
            
            // Service header row
            bodyHtml += `<div class="vc-row-service" style="margin-bottom:8px">
                <label style="cursor:pointer;display:flex;align-items:center;gap:8px;flex:1">
                    <input type="checkbox" ${allSelected ? 'checked' : ''} onchange="toggleVendorService(${v.id}, this.checked)" style="width:16px;height:16px;accent-color:#3b5bdb;">
                    <span style="font-size:12px">${currentPR.display_title}</span>
                    ${isBestService ? '<span style="background:#e0f2fe;color:#0284c7;padding:2px 6px;border-radius:4px;font-size:9.5px;font-weight:700;">BEST PRICE</span>' : ''}
                </label>
            </div>`;

            if (currentPR.jobs) {
                currentPR.jobs.forEach((job, jIdx) => {
                    bodyHtml += `<div class="vc-row-job" style="margin-bottom:8px">${job.job_description}</div>`;
                    if (job.items) {
                        job.items.forEach(item => { bodyHtml += renderItemCard(v, item, off); });
                    }
                });
            }
        } else {
            // PR header col label row
            bodyHtml += currentPR.items.map(item => renderItemCard(v, item, off)).join('');
        }

        return `<div class="vendor-card" style="border-left:3px solid ${isVendorChecked ? '#3b5bdb' : 'transparent'};">
            <div class="vendor-card-header" style="background:${isVendorChecked ? '#eff6ff' : '#fff'}">
                <div style="font-size:13.5px;font-weight:700;color:${isVendorChecked ? '#1d4ed8' : '#111827'};display:flex;flex-direction:column;gap:4px;">
                    <div>${vName}${prevBadge}</div>
                    ${off && off.attachment ? `<a href="/storage/${off.attachment}" target="_blank" style="font-size:11px;color:#3b5bdb;text-decoration:underline;display:inline-flex;align-items:center;gap:4px;font-weight:600;"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg> View Attachment</a>` : ''}
                </div>
            </div>
            <div class="vendor-card-body">
                ${bodyHtml}
            </div>
            <div class="vendor-card-footer">Total Quote <span id="vendor-total-${v.id}" style="float:right">${fmt(0)}</span></div>
        </div>`;
    }).join('');
    updateVendorTotals();
}

function renderItemCard(v, item, off) {
    const o = off ? off.items[item.id] : null;
    if (!o) return `<div class="vc-row-item" style="background:#fafafa; border-style: dashed; opacity: 0.6;">
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:12px 0;">
            <div style="font-size:12px;font-weight:600;color:#9ca3af;margin-bottom:4px">${item.item_name}</div>
            <div style="color:#ef4444;font-size:10.5px;font-weight:700;">❌ NOT OFFERED</div>
        </div>
    </div>`;

    const isService = currentPR.type === 'service';
    const selKey = `${v.id}_${item.id}`;
    const isSelected = !!selections[selKey];

    // FIX: parseFloat prevents string-concat bug
    let totalSel = 0;
    for (let key in selections) { if (selections[key].item_id == item.id) totalSel += parseFloat(selections[key].quantity) || 0; }
    const targetQty = parseFloat(item.quantity) || 0;

    const isFullMatch = totalSel >= targetQty;
    const disableSel = isFullMatch && !isSelected;
    const isBestItemPrice = (bestPriceMap[item.id] == o.unit_price);

    let qtyBadge = '';
    if (!isService) {
        if (o.qty_offered == targetQty) qtyBadge = `<span style="background:#dcfce7;color:#16a34a;padding:2px 5px;border-radius:4px;font-size:9px;font-weight:700;">MATCH</span>`;
        else if (o.qty_offered < targetQty) qtyBadge = `<span style="background:#ffedd5;color:#ea580c;padding:2px 5px;border-radius:4px;font-size:9px;font-weight:700;">INSUF.</span>`;
        else qtyBadge = `<span style="background:#e0f2fe;color:#0284c7;padding:2px 5px;border-radius:4px;font-size:9px;font-weight:700;">SURPLUS</span>`;
    }

    const itemNotes = item.item_notes || item.notes || '';
    const offerNotes = o.notes || '';
    const combinedNotes = [itemNotes, offerNotes].filter(n => n && n.trim()).join(' - ') || '-';
    const specDiffers = o.specification_offered && (!item.specification || o.specification_offered.toLowerCase() !== item.specification.toLowerCase());
    const unitDiffers = o.unit_offered && item.unit && o.unit_offered.toLowerCase() !== item.unit.toLowerCase();
    const hasDiffers = specDiffers || unitDiffers;

    const borderColor = isSelected ? '#3b5bdb' : (hasDiffers ? '#fcd34d' : '#e2e8f0');
    const bgColor = hasDiffers ? '#fef3c7' : '#fff';

    return `<div class="vc-row-item" style="background:${bgColor};border-left:3px solid ${borderColor};${!isService ? `cursor:${disableSel?'not-allowed':'pointer'};` : ''}opacity:${disableSel?'0.5':'1'};transition:border-color .15s; margin-bottom: 2px;"
        ${!isService && !disableSel ? `onclick="toggleSelect(${v.id}, '${item.id}')"` : ''}>

        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px">
            <div style="font-size:12.5px;font-weight:700;color:#111827;line-height:1.3;flex:1;padding-right:8px;">
                ${item.item_name}
                ${specDiffers ? `<span style="background:#f59e0b;color:#fff;padding:1px 5px;border-radius:3px;font-size:8.5px;font-weight:700;margin-left:4px;">SPEC ≠</span>` : ''}
                ${unitDiffers ? `<span style="background:#f59e0b;color:#fff;padding:1px 5px;border-radius:3px;font-size:8.5px;font-weight:700;margin-left:4px;">UNIT ≠</span>` : ''}
            </div>
            ${!isService ? `<input type="checkbox" ${isSelected?'checked':''} ${disableSel?'disabled':''} onclick="event.stopPropagation(); toggleSelect(${v.id}, '${item.id}')" style="width:15px;height:15px;accent-color:#3b5bdb;cursor:pointer;flex-shrink:0;">` : ''}
        </div>

        <div style="display:grid;grid-template-columns:60px 1fr;gap:5px 8px;align-items:center;font-size:11px;">
            <div style="color:#9ca3af">Qty</div>
            <div style="font-weight:700;color:#111827;display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                ${o.qty_offered} <span style="${unitDiffers ? 'background:#fef08a;color:#854d0e;padding:1px 4px;border-radius:3px;' : ''}">${o.unit_offered||''}</span> / ${targetQty} ${item.unit||''}
                ${qtyBadge}
            </div>

            ${isSelected && !isService ? `
            <div style="color:#3b5bdb;font-weight:700">Buy Qty</div>
            <div><input type="number" min="1" max="${o.qty_offered}" value="${selections[`${v.id}_${item.id}`].quantity}"
                onclick="event.stopPropagation()"
                onchange="updateQty(${v.id}, '${item.id}', this.value)"
                style="width:75px;height:24px;border:1px solid #3b5bdb;border-radius:4px;padding:0 6px;font-size:12px;font-weight:600;outline:none;color:#3b5bdb;"></div>` : ''}

            <div style="color:#9ca3af">Price</div>
            <div style="font-weight:600;color:#111827;display:flex;align-items:center;gap:4px;">
                ${fmt(o.unit_price)}
                ${(isBestItemPrice && !isService) ? `<span style="background:#e0f2fe;color:#0284c7;padding:1px 5px;border-radius:3px;font-size:9px;font-weight:700;">BEST</span>` : ''}
            </div>

            <div style="color:#9ca3af">Spec</div>
            <div style="color:#111827;font-size:10.5px;">${o.specification_offered || (item.specification || '-')}</div>

            <div style="color:#9ca3af">Notes</div>
            <div style="color:#6b7280;font-style:italic;font-size:10.5px;">${combinedNotes}</div>

            <div style="color:#9ca3af">Subtotal</div>
            <div style="font-weight:700;color:#111827;">${isSelected && !isService ? fmt(selections[`${v.id}_${item.id}`].quantity * o.unit_price) : fmt(o.qty_offered * o.unit_price)}</div>
        </div>
    </div>`;
}

function toggleVendorJob(vId, jIdx, isChecked) {
    const job = currentPR.jobs[jIdx];
    if (!job || !job.items) return;
    
    if (!isChecked) {
        // Unselect all
        job.items.forEach(item => {
            const selKey = `${vId}_${item.id}`;
            delete selections[selKey];
        });
    } else {
        // Select all
        job.items.forEach(item => {
            const offer = vendorOffers[vId].items[item.id];
            if(offer) {
                const selKey = `${vId}_${item.id}`;
                if (!selections[selKey]) {
                    // Select this item
                    let qtyAlreadySelected = 0;
                    for(let key in selections) { if (selections[key].item_id == item.id) qtyAlreadySelected += parseFloat(selections[key].quantity) || 0; }
                    if (qtyAlreadySelected >= parseFloat(item.quantity)) return;

                    let remainingNeed = parseFloat(item.quantity) - qtyAlreadySelected;
                    let defaultBuyQty = Math.min(Math.max(1, remainingNeed), offer.qty_offered);

                    selections[selKey] = { vendor_id: vId, item_id: item.id, item_name: item.item_name, unit_price: offer.unit_price, quantity: defaultBuyQty, unit: offer.unit_offered || item.unit, brand: offer.brand_offered || item.brand || '', specification: offer.specification_offered || '', notes: offer.notes || '' };
                    selections[selKey].subtotal = defaultBuyQty * offer.unit_price;
                }
            }
        });
    }
    
    renderRequirementsTable();
    renderVendorCards();
    updateCounts();
}

function toggleSelect(vId, itemId, forceRenderOnlyAtEnd = false) {
    if (currentPR.type === 'service') return; 

    const selKey = `${vId}_${itemId}`;
    if(selections[selKey]){
        delete selections[selKey];
    } else {
        const item = currentPR.items.find(i => i.id == itemId);
        const offer = vendorOffers[vId].items[itemId];
        if(item && offer) {
            let qtyAlreadySelected = 0;
            for(let key in selections) { if (selections[key].item_id == itemId) qtyAlreadySelected += parseFloat(selections[key].quantity) || 0; }
            if (qtyAlreadySelected >= parseFloat(item.quantity)) return;

            let remainingNeed = parseFloat(item.quantity) - qtyAlreadySelected;
            let defaultBuyQty = Math.min(Math.max(1, remainingNeed), offer.qty_offered);

            selections[selKey] = { vendor_id: vId, item_id: itemId, item_name: item.item_name, unit_price: offer.unit_price, quantity: defaultBuyQty, unit: offer.unit_offered || item.unit, brand: offer.brand_offered || item.brand || '', specification: offer.specification_offered || '', notes: offer.notes || '' };
            selections[selKey].subtotal = defaultBuyQty * offer.unit_price;
        }
    }
    
    if(!forceRenderOnlyAtEnd) {
        renderRequirementsTable(); renderVendorCards(); updateCounts();
    }
}

function updateQty(vendorId, itemId, val) {
    const selKey = `${vendorId}_${itemId}`;
    if (selections[selKey]) {
        let q = parseInt(val) || 1;
        const max = vendorOffers[vendorId].items[itemId].qty_offered;
        if (q > max) q = max; if (q < 1) q = 1;
        selections[selKey].quantity = q;
        selections[selKey].subtotal = q * selections[selKey].unit_price;
        renderRequirementsTable(); renderVendorCards(); updateCounts();
    }
}

function updateCounts(){
    let itemsMet = 0;
    if (currentPR && currentPR.items) {
        currentPR.items.forEach(item => {
            let t = 0;
            // FIX: parseFloat prevents string-concat bug
            for(let key in selections) { if(selections[key].item_id == item.id) t += parseFloat(selections[key].quantity) || 0; }
            if (t >= parseFloat(item.quantity)) itemsMet++;
        });
    }
    document.getElementById('sel-count').textContent = itemsMet; document.getElementById('footer-sel').textContent = itemsMet;
    const btn = document.getElementById('show-result-btn');
    if(Object.keys(selections).length > 0) {
        btn.style.opacity='1'; btn.style.pointerEvents='auto'; btn.style.background='#16a34a';
    } else {
        btn.style.opacity='.4'; btn.style.pointerEvents='none'; btn.style.background='#111827';
    }
}

function updateVendorTotals(){
    serverVendors.forEach(v=>{
        let t=0;
        if(currentPR && currentPR.items) {
            currentPR.items.forEach(item=>{
                const o = vendorOffers[v.id]?.items[item.id];
                if(o) t += (parseFloat(item.quantity) * o.unit_price);
            });
        }
        const el=document.getElementById('vendor-total-'+v.id); if(el) el.textContent=fmt(t);
    });
}

function showSelectionResult() {
    let itemsMet = 0;
    currentPR.items.forEach(item => {
        let t = 0;
        for(let key in selections) { if(selections[key].item_id == item.id) t += parseFloat(selections[key].quantity) || 0; }
        if (t >= parseFloat(item.quantity)) itemsMet++;
    });
    if (itemsMet < currentPR.items.length) { document.getElementById('warning-modal').style.display = 'flex'; }
    else { renderResultWorkspace(); }
}

function closeWarningModal() { document.getElementById('warning-modal').style.display = 'none'; }
function forceShowSelectionResult() { closeWarningModal(); renderResultWorkspace(); }

function renderResultWorkspace() {
    document.getElementById('selection-workspace').style.display='none';
    document.getElementById('result-workspace').style.display='block';
    
    document.getElementById('res-pr-label').textContent='Summary PO untuk '+ currentPR.display_doc;
 
    let grandTotal=0; 
    let rowNum=1;
    let itemsArrHtml = '';

    if (currentPR.type === 'service') {
        currentPR.jobs.forEach(job => {
            const jobItemsSelected = job.items.filter(i => Object.values(selections).some(s => s.item_id == i.id));
            if(jobItemsSelected.length > 0) {
                itemsArrHtml += `<tr style="background:#f3f4f6; border-bottom:1px dashed #d1d5db;">
                    <td colspan="7" style="font-weight:700; color:#374151; padding:10px 14px;">Scope: ${job.job_description}</td>
                </tr>`;
                
                job.items.forEach(item => {
                    const selKeys = Object.keys(selections).filter(k => selections[k].item_id == item.id);
                    selKeys.forEach(k => {
                        const s = selections[k];
                        const v = serverVendors.find(x => x.id == s.vendor_id) || {};
                        const vName = v.vendor_name || v.name || s.vendor_id;
                        grandTotal += s.subtotal;
                        
                        itemsArrHtml += `<tr style="border-bottom:1px solid #f3f4f6; background:#fff">
                            <td style="padding:10px 14px;color:#6b7280;padding-left:35px;">${rowNum++}</td>
                            <td style="padding:10px 14px;font-weight:600;color:#111827">${s.item_name}</td>
                            <td style="padding:10px 14px"><span style="padding:3px 8px;border-radius:6px;background:#dbeafe;color:#1d4ed8;font-size:11px;font-weight:700">${vName}</span></td>
                            <td style="padding:10px 14px;text-align:right;font-weight:700;font-size:13px">${s.quantity}</td>
                            <td style="padding:10px 14px">${s.unit}</td>
                            <td style="padding:10px 14px;text-align:right">${Number(s.unit_price).toLocaleString('id-ID')}</td>
                            <td style="padding:10px 14px;text-align:right;font-weight:700;color:#111827">${Number(s.subtotal).toLocaleString('id-ID')}</td>
                        </tr>`;
                    });
                });
            }
        });
    } else {
        itemsArrHtml = Object.values(selections).map((s) => {
            const v = serverVendors.find(x => x.id == s.vendor_id) || {};
            const vName = v.vendor_name || v.name || s.vendor_id;
            grandTotal += s.subtotal;
            
            return `<tr style="border-bottom:1px solid #f3f4f6">
                <td style="padding:10px 14px;color:#6b7280">${rowNum++}</td>
                <td style="padding:10px 14px;font-weight:600;color:#111827">${s.item_name}</td>
                <td style="padding:10px 14px"><span style="padding:3px 8px;border-radius:6px;background:#dbeafe;color:#1d4ed8;font-size:11px;font-weight:700">${vName}</span></td>
                <td style="padding:10px 14px;text-align:right;font-weight:700;font-size:13px">${s.quantity}</td>
                <td style="padding:10px 14px">${s.unit}</td>
                <td style="padding:10px 14px;text-align:right">${Number(s.unit_price).toLocaleString('id-ID')}</td>
                <td style="padding:10px 14px;text-align:right;font-weight:700;color:#111827">${Number(s.subtotal).toLocaleString('id-ID')}</td>
            </tr>`;
        }).join('');
    }
    
    document.getElementById('selected-items-tbody').innerHTML = itemsArrHtml;
    document.getElementById('grand-total-cell').textContent = fmt(grandTotal);
 
    const vSummaries={};
    Object.values(selections).forEach(s => {
        const v = serverVendors.find(x => x.id == s.vendor_id) || {};
        const vName = v.vendor_name || v.name || s.vendor_id;
        if(!vSummaries[s.vendor_id]) { vSummaries[s.vendor_id] = { name:vName, total:0, jobs:{}, items:[] }; }

        if (currentPR.type === 'service') {
            // FIX: find which job scope this item belongs to
            let jobDesc = null;
            (currentPR.jobs || []).forEach(job => {
                if ((job.items || []).some(it => it.id == s.item_id)) jobDesc = job.job_description;
            });
            jobDesc = jobDesc || '—';
            if (!vSummaries[s.vendor_id].jobs[jobDesc]) vSummaries[s.vendor_id].jobs[jobDesc] = [];
            vSummaries[s.vendor_id].jobs[jobDesc].push({ name:s.item_name, qty:s.quantity, unit:s.unit, price:s.unit_price, sub:s.subtotal });
        } else {
            vSummaries[s.vendor_id].items.push({ name:s.item_name, qty:s.quantity, unit:s.unit, price:s.unit_price, sub:s.subtotal });
        }
        vSummaries[s.vendor_id].total += s.subtotal;
    });

    document.getElementById('vendor-summary-cards').innerHTML = Object.values(vSummaries).map(vs=>`
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px;min-width:250px;flex:0 0 250px">
            <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f3f4f6;padding-bottom:10px;margin-bottom:10px">
                <div style="font-size:13.5px;font-weight:700;color:#1d4ed8">${vs.name}</div><div style="font-size:13.5px;font-weight:800;color:#111827">${fmt(vs.total)}</div>
            </div>
            ${currentPR.type === 'service'
                ? Object.entries(vs.jobs).map(([jobDesc, items]) => `
                    <div style="margin-bottom:10px">
                        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;padding-bottom:4px;border-bottom:1px dashed #e5e7eb;"> ${jobDesc}</div>
                        ${items.map(it=>`<div style="margin-bottom:6px">
                            <div style="font-size:12.5px;font-weight:600;color:#374151">${it.name}</div>
                            <div style="font-size:11.5px;color:#9ca3af">${it.qty} ${it.unit} × Rp ${Number(it.price).toLocaleString('id-ID')} <span style="float:right;font-weight:700;color:#4b5563">${fmt(it.sub)}</span></div>
                        </div>`).join('')}
                    </div>`).join('')
                : vs.items.map(it=>`<div style="margin-bottom:8px">
                    <div style="font-size:12.5px;font-weight:600;color:#374151">${it.name}</div>
                    <div style="font-size:11.5px;color:#9ca3af">${it.qty} ${it.unit} × Rp ${Number(it.price).toLocaleString('id-ID')} <span style="float:right;font-weight:700;color:#4b5563">${fmt(it.sub)}</span></div>
                </div>`).join('')
            }
        </div>
    `).join('');
}

function openSubmitModal() {
    let hasDiffers = false;
    for (let key in selections) {
        const s = selections[key];
        const vId = s.vendor_id;
        const itemId = s.item_id;
        
        let originalItem = null;
        if (currentPR.type === 'service') {
            currentPR.jobs.forEach(j => {
                const found = j.items.find(i => i.id == itemId);
                if(found) originalItem = found;
            });
        } else {
            originalItem = currentPR.items.find(i => i.id == itemId);
        }

        if (originalItem) {
            const specDiff = s.specification && (!originalItem.specification || s.specification.toLowerCase() !== originalItem.specification.toLowerCase());
            const unitDiff = s.unit && originalItem.unit && s.unit.toLowerCase() !== originalItem.unit.toLowerCase();
            
            if (specDiff || unitDiff) {
                hasDiffers = true;
                break;
            }
        }
    }

    if (hasDiffers) {
        Swal.fire({
            title: 'Terdapat Perbedaan?',
            html: 'Beberapa item yang Anda pilih memiliki <strong style="color:#b45309">spesifikasi</strong> atau <strong style="color:#b45309">unit</strong> yang berbeda dari permintaan asli.<br><br>Apakah Anda yakin ingin melanjutkan pemilihan vendor ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3b5bdb',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('submit-modal').style.display = 'flex';
            }
        });
    } else {
        document.getElementById('submit-modal').style.display = 'flex';
    }
}
function closeSubmitModal(){document.getElementById('submit-modal').style.display='none';}
function closeSuccess(){document.getElementById('success-popup').style.display='none'; backToStep1();}

function submitToServer(){
    const notes = document.getElementById('submit-notes').value.trim();
    const payload = {
        purchase_request_id: currentPR.id,
        item_type: currentPR.type,
        selection_notes: notes,
        selections: Object.values(selections).map(s => ({ vendor_id: s.vendor_id, item_id: s.item_id, unit_price: s.unit_price, quantity: s.quantity, notes: s.notes, unit: s.unit, specification: s.specification })),
        _token: document.querySelector('meta[name=csrf-token]')?.content||'',
    };
    
    fetch('{{ route("vendors.store.selection") }}', { 
        method:'POST', 
        headers:{
            'Content-Type':'application/json',
            'Accept':'application/json',
            'X-CSRF-TOKEN':payload._token
        }, 
        body:JSON.stringify(payload) 
    })
    .then(r => {
        if (!r.ok) {
            return r.json().then(err => { 
                throw new Error(err.message || JSON.stringify(err)); 
            });
        }
        return r.json();
    })
    .then(data => {
        closeSubmitModal(); 
        document.getElementById('popup-pr').textContent=data.pr_number || currentPR.display_doc; 
        document.getElementById('success-popup').style.display='flex';
    }).catch(err => {
        closeSubmitModal(); 
        alert('Submission failed. Please try again.\nError: ' + (err.message || ''));
    });
}

// Auto-load PR/SR when arriving from "Select Vendor" button in modal (?key=type_id)
document.addEventListener('DOMContentLoaded', function () {
    const preKey = @json($selectedKey ?? '');
    if (preKey) {
        const sel = document.getElementById('pr-select');
        if (sel) sel.value = preKey;
        loadPR(preKey);
        // Hide step1 dropdown so user lands directly on vendor cards
        const step1 = document.getElementById('step1-card');
        if (step1) step1.style.display = 'none';
    }
});
</script>
@endsection
