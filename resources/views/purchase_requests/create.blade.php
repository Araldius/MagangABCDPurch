@extends('layouts.app')
@php $pageTitle = 'New Request'; @endphp
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
.page-header   { margin-bottom: 24px; }
.page-title    { font-size: 20px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
.page-desc     { font-size: 13.5px; color: var(--text-muted); }
.card          { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
.card-header   { padding: 16px 20px; border-bottom: 1px solid var(--border); background: #fafafa; }
.card-body     { padding: 20px; }
.card-title    { font-size: 14px; font-weight: 700; color: var(--text); line-height: 1.3; }
.card-desc     { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.form-section-icon { width: 34px; height: 34px; border-radius: 8px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0; }
.flex-center   { display: flex; align-items: center; }
.flex-between  { display: flex; align-items: center; justify-content: space-between; }
.form-row      { display: grid; gap: 16px; }
.form-row-2    { grid-template-columns: 1fr 1fr; }
.form-group    { display: flex; flex-direction: column; margin-bottom: 16px; }
.form-label    { font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
.req           { color: var(--req-color); margin-left: 2px; }
.form-control  { width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 13.5px; border: 1px solid var(--border-strong); border-radius: 8px; background: #fff; color: var(--text); outline: none; }
.form-control:focus  { border-color: #6366f1; box-shadow: 0 0 0 3px rgb(99 102 241/.12); }
.mt-2 { margin-top: 8px; }
.mt-4 { margin-top: 24px; }
.mb-2 { margin-bottom: 8px; }

/* Buttons */
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 600; cursor: pointer; border: 1px solid transparent; text-decoration: none; }
.btn-primary  { background: #111827; color: #fff; }
.btn-primary:hover { background: #1f2937; }
.btn-outline  { background: #fff; color: var(--text); border-color: var(--border-strong); }
.btn-outline:hover { background: #f9fafb; }
.btn-sm       { padding: 6px 14px; font-size: 12.5px; border-radius: 7px; }

/* Tables */
.item-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.item-table th { text-align: left; padding: 8px; color: var(--text-muted); border-bottom: 1px solid var(--border); font-weight: 600; font-size: 11px; text-transform: uppercase; }
.item-table td { padding: 8px 6px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }

/* Modals */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1000; align-items: center; justify-content: center; padding: 16px; }
.modal-overlay.open { display: flex; }
.modal { background: #fff; border-radius: 14px; width: 100%; max-width: 560px; display: flex; flex-direction: column; overflow: hidden; }
.modal-xl { max-width: 850px; }
.modal-header { padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.modal-title  { font-size: 15px; font-weight: 700; }
.modal-desc   { font-size: 12.5px; color: var(--text-muted); }
.modal-body   { padding: 20px; overflow-y: auto; max-height: 65vh; }
.modal-footer { padding: 14px 20px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; }
.modal-close  { background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 18px; }

/* Tabs & UI Blocks */
.tab-btn { padding:7px 18px;border-radius:8px;border:1px solid var(--border);font-size:13.5px;font-weight:600;cursor:pointer;background:white;color:var(--text-muted); }
.tab-btn.tab-active { background:#111827;color:white;border-color:#111827; }
.item-option { padding:12px 14px;border-radius:8px;cursor:pointer;border:1px solid var(--border);transition:background .1s; margin-bottom: 6px; }
.item-option:hover { background:#f9fafb; }
.item-option.selected { background:var(--primary-light);border-color:var(--primary); }
.item-option-name { font-size:13.5px;font-weight:600;color:var(--text); }
.item-option-desc { font-size:12px;color:var(--text-muted); margin-top: 4px;}

/* Service Hierarchy Specific UI */
.modal-job-block { border: 1px solid var(--border); border-radius: 10px; background: #fafafa; padding: 16px; margin-bottom: 16px; }
.modal-job-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 12px; gap: 16px; }
.main-svc-container { margin-bottom: 12px; padding: 10px; border: 1px solid #f3f4f6; border-radius: 8px; background: #fff; }
.main-job-title { font-weight: 600; color: #374151; font-size: 13px; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
.nested-items-list { list-style: none; padding-left: 18px; margin: 0; font-size: 12.5px; color: var(--text-muted); }
.nested-items-list li { padding: 4px 0; display: flex; justify-content: space-between; border-bottom: 1px dashed #f3f4f6; }
.nested-items-list li:last-child { border-bottom: none; }
</style>
<div class="page-header">
    <div class="page-title">New Request</div>
    <div class="page-desc">Fill in the form below to submit a new procurement/service request.</div>
</div>

<form action="{{ route('purchase_requests.store') }}" method="post" id="pr-form" enctype="multipart/form-data">
@csrf
<input type="hidden" name="item_type" id="item_type_field" value="goods">

<div class="card">
    <div class="card-header">
        <div class="flex-center">
            <div class="form-section-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="2" width="6" height="4" rx="1"/><path d="M7 2H5a2 2 0 00-2 2v16a2 2 0 002 2h14a2 2 0 002-2V4a2 2 0 00-2-2h-2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            <div>
                <div class="card-title">Request Schedule & Location</div>
                <div class="card-desc">Determine where and when the request is needed</div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row" style="grid-template-columns: 1fr 1fr 1fr; margin-bottom:16px;">
            <div class="form-group" style="margin-bottom:0;"><label class="form-label">Requested Date <span class="req">*</span></label><input class="form-control" type="date" name="requested_date" value="{{ date('Y-m-d') }}" readonly required style="background:#f9fafb;color:#6b7280;cursor:not-allowed;" title="Auto-filled"></div>
            <div class="form-group" style="margin-bottom:0;"><label class="form-label">Need Date <span class="req">*</span></label><input class="form-control" type="date" name="need_date" min="{{ date('Y-m-d') }}" required></div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Plant <span class="req">*</span></label>
                <select class="form-control" name="plant" required>
                    <option value="">Select Plant</option>
                    <option value="Cikarang">Cikarang</option>
                    <option value="Cibitung">Cibitung</option>
                    <option value="Gresik">Gresik</option>
                </select>
            </div>
        </div>

    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <div class="flex-center">
            <div class="form-section-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" stroke-linecap="round"/></svg></div>
            <div>
                <div class="card-title" id="section-title">Purchase Details</div>
                <div class="card-desc">Select request type and input item/service scopes</div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div style="display:flex;gap:8px;margin-bottom:20px;">
            <button type="button" id="tab-goods" class="tab-btn tab-active" onclick="setTab('goods')">🛒 Goods</button>
            <button type="button" id="tab-service" class="tab-btn" onclick="setTab('service')">🔧 Service</button>
        </div>

        <div id="goods-panel">
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Document Number</label>
                    <input class="form-control" id="goods-doc" value="{{ $nextPrDocNum ?? 'PR-'.now()->format('Y').'-XXXX' }}" readonly
                        style="background:#f9fafb;color:#6b7280;cursor:not-allowed;"
                        title="Auto-generated">
                    <span style="font-size:11px;color:#9ca3af;margin-top:4px">Auto-generated saat submit</span>
                </div>
                <div class="form-group"><label class="form-label">Title / Project Name <span class="req">*</span></label><input class="form-control" name="title" id="goods-title" placeholder="e.g. Pengadaan ATK Bulanan" required></div>
            </div>
            <div class="form-group"><label class="form-label">Department</label><input class="form-control" name="department" value="{{ Auth::user()->department ?? '' }}"></div>
            
            <div class="flex-between mb-2 mt-4">
                <label class="form-label mb-0" id="goods-count-label">0 item(s) added</label>
                <button type="button" class="btn btn-primary btn-sm" onclick="openItemModal()">+ Add Item</button>
            </div>
            <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;">
                <table class="item-table" style="margin-bottom:0;">
                    <thead style="background:#f9fafb;">
                        <tr>
                            <th style="width:40px;">NO</th>
                            <th style="width:110px;">ITEM ID</th>
                            <th>ITEM NAME</th>
                            <th style="width:70px;">QTY</th>
                            <th style="width:100px;">UNIT</th>
                            <th>NOTES</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="goods-tbody">
                        <tr><td colspan="8" style="text-align:center;padding:28px 16px;color:#9ca3af;">No items added yet. Click "+ Add Item" to begin.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="service-panel" style="display:none;">
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Document Number</label>
                    <input class="form-control" id="service-doc" value="{{ $nextSrDocNum ?? 'SR-'.now()->format('Y').'-XXXX' }}" readonly
                        style="background:#f9fafb;color:#6b7280;cursor:not-allowed;"
                        title="Auto-generated">
                    <span style="font-size:11px;color:#9ca3af;margin-top:4px">Auto-generated saat submit</span>
                </div>
                <div class="form-group"><label class="form-label">Title / Project Name <span class="req">*</span></label><input class="form-control" name="service_title" id="service-title" placeholder="e.g. Perawatan Fasilitas Tahunan" required></div>
            </div>
            <div class="form-group"><label class="form-label">Department</label><input class="form-control" name="service_department" value="{{ Auth::user()->department ?? '' }}"></div>

            <div class="flex-between mb-2 mt-4">
                <label class="form-label mb-0" id="service-count-label">0 service(s) added</label>
                <button type="button" class="btn btn-primary btn-sm" onclick="openSvcListModal()">+ Add Service</button>
            </div>
            <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;">
                <table class="item-table" style="margin-bottom:0;">
                    <thead style="background:#f9fafb;">
                        <tr>
                            <th style="width:40px;">NO</th>
                            <th style="width:220px;">SERVICE NAME</th>
                            <th>JOB DESCRIPTION & REQUIRED ITEMS</th>
                            <th style="width:50px; text-align:center;">ACT</th>
                        </tr>
                    </thead>
                    <tbody id="service-tbody">
                        <tr><td colspan="4" style="text-align:center;padding:28px 16px;color:#9ca3af;">No services added yet. Click "+ Add Service" to begin.</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="hidden-service-inputs"></div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body" style="padding-top:16px; padding-bottom:16px;">
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Attachment (Optional)</label>
            <input type="file" class="form-control" name="attachment" accept=".pdf,.xlsx,.xls,.jpg,.jpeg,.png">
            <span style="font-size:11px;color:#9ca3af;margin-top:4px">Max 10MB (PDF, Excel, Images)</span>
        </div>
    </div>
</div>

<div class="actions" style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
    <a href="{{ route('dashboard') }}" class="btn-back">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Cancel
    </a>
    <button type="submit" class="btn btn-primary">Submit Request</button>
</div>
</form>

<div class="modal-overlay" id="item-modal">
    <div class="modal">
        <div class="modal-header">
            <div><div class="modal-title">Select Item</div><div class="modal-desc">Search catalog items</div></div>
            <button class="modal-close" onclick="closeItemModal()">&times;</button>
        </div>
        <div style="padding: 16px 20px 12px; border-bottom: 1px solid var(--border); background: #fafafa; display:flex; flex-direction:column; gap:8px;">
            <div style="display:flex; gap:8px;">
                <input class="form-control" id="item-search" placeholder="Search..." oninput="filterItems()" style="flex:1;">
                <select class="form-control" id="item-filter-unit" onchange="filterItems()" style="width:120px;">
                    <option value="">All Units</option>
                    @php $allUnits = collect($existingItems ?? [])->pluck('unit')->unique()->sort()->values(); @endphp
                    @foreach($allUnits as $u) <option value="{{$u}}">{{$u}}</option> @endforeach
                </select>
                <select class="form-control" id="item-sort" onchange="filterItems()" style="width:140px;">
                    <option value="name_asc">Name (A-Z)</option>
                    <option value="name_desc">Name (Z-A)</option>
                    <option value="id_asc">ID (A-Z)</option>
                </select>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="openNewItemModal()" style="width:100%; justify-content:center;">+ Create New Item ID</button>
        </div>
        <div class="modal-body" style="padding-top: 12px;">
            <div id="item-list" style="display:flex;flex-direction:column;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-back" onclick="closeItemModal()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Cancel
            </button>
            <button type="button" class="btn btn-primary" onclick="addSelectedItem()">Add Selected</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="new-item-modal">
    <div class="modal">
        <div class="modal-header">
            <div><div class="modal-title">Create New Item</div></div>
            <button class="modal-close" onclick="closeNewItemModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-row" style="grid-template-columns: 2fr 1fr 1.5fr;">
                <div class="form-group"><label class="form-label">Item Name <span class="req">*</span></label><input class="form-control" id="new-item-name"></div>
                <div class="form-group"><label class="form-label">Qty <span class="req">*</span></label><input type="number" class="form-control" id="new-item-qty" value="1" min="1"></div>
                <div class="form-group"><label class="form-label">Unit <span class="req">*</span></label>
                    <select class="form-control" id="new-item-unit">
                        <option>Pcs</option><option>Roll</option><option>Unit</option><option>Meter</option><option>Kg</option><option>Set</option><option>Box</option><option>Lot</option><option>Jasa</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:0;"><label class="form-label">Notes</label><textarea class="form-control" id="new-item-notes"></textarea></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-back" onclick="closeNewItemModal()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Cancel
            </button>
            <button type="button" class="btn btn-primary" onclick="saveNewItem()">Save & Add</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="svc-list-modal">
    <div class="modal">
        <div class="modal-header">
            <div><div class="modal-title">Select Service</div><div class="modal-desc">Search from previous service templates</div></div>
            <button class="modal-close" onclick="closeSvcListModal()">&times;</button>
        </div>
        <div style="padding: 16px 20px 12px; border-bottom: 1px solid var(--border); background: #fafafa; display:flex; flex-direction:column; gap:8px;">
            <div style="display:flex; gap:8px;">
                <input class="form-control" id="svc-search" placeholder="Search services..." oninput="filterSvcList()" style="flex:1;">
                <select class="form-control" id="svc-sort" onchange="filterSvcList()" style="width:160px;">
                    <option value="name_asc">Name (A-Z)</option>
                    <option value="name_desc">Name (Z-A)</option>
                    <option value="jobs_desc">Most Jobs</option>
                </select>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="openNewSvcModal()" style="width:100%; justify-content:center;">+ Create New Service Form</button>
        </div>
        <div class="modal-body" style="padding-top: 12px;">
            <div id="svc-list-container" style="display:flex;flex-direction:column;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-back" onclick="closeSvcListModal()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Cancel
            </button>
            <button type="button" class="btn btn-primary" onclick="addSelectedSvcTemplate()">Add Selected</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="new-svc-modal">
    <div class="modal modal-xl">
        <div class="modal-header">
            <div><div class="modal-title">Create New Service</div><div class="modal-desc">Define service name, scopes of work, and specific items</div></div>
            <button class="modal-close" onclick="closeNewSvcModal()">&times;</button>
        </div>
        <div class="modal-body" style="background:#e5e7eb; padding:16px;">
            <div class="form-group mb-4" style="background:#fff; padding:16px; border:1px solid var(--border); border-radius:8px;">
                <label class="form-label" style="font-size:14px;">Service Name / Category Title <span class="req">*</span></label>
                <input class="form-control" id="modal-service-name" placeholder="e.g. Perbaikan Sistem Kelistrikan">
            </div>
            <div id="modal-jobs-container"></div>
            <button type="button" class="btn btn-outline btn-sm" onclick="addJobBlockToModal()" style="border: 1px dashed var(--primary); color: var(--primary); width: 100%; justify-content: center; padding: 12px; background:#fff;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg> Add Job Description (Scope of Work)
            </button>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-back" onclick="closeNewSvcModal()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Cancel
            </button>
            <button type="button" class="btn btn-primary" onclick="commitModalServiceToMainTable()">Save Service & Add</button>
        </div>
    </div>
</div>
<script>
const unitOptionsHtml = `
    <option value="Pcs">Pcs</option>
    <option value="Unit">Unit</option>
    <option value="Box">Box</option>
    <option value="Kg">Kg</option>
    <option value="Liter">Liter</option>
    <option value="Meter">Meter</option>
    <option value="Roll">Roll</option>
    <option value="Set">Set</option>
    <option value="Lot">Lot</option>
    <option value="Jasa">Jasa</option>
    <option value="Pack">Pack</option>
`;

function setTab(t) {
    document.getElementById('item_type_field').value = t;
    document.getElementById('tab-goods').classList.toggle('tab-active', t==='goods');
    document.getElementById('tab-service').classList.toggle('tab-active', t==='service');
    document.getElementById('goods-panel').style.display   = t==='goods'   ? '' : 'none';
    document.getElementById('service-panel').style.display = t==='service' ? '' : 'none';
    
    // Toggle 'required' attributes so hidden fields don't block form submission
    document.getElementById('goods-title').required = (t === 'goods');
    document.getElementById('service-title').required = (t === 'service');
}

/* GOODS LOGIC */
const catalog = @json($existingItems ?? []);
let selectedItemId = null, addedItems = [], itemCounter = 0;

function filterItems() {
    const q = document.getElementById('item-search').value.toLowerCase();
    const u = document.getElementById('item-filter-unit').value;
    const s = document.getElementById('item-sort').value;
    renderItemList(q, u, s);
}
function renderItemList(q='', u='', s='name_asc') {
    let filtered = catalog.filter(i => {
        const matchQ = !q || i.name.toLowerCase().includes(q) || (i.brand && i.brand.toLowerCase().includes(q));
        const matchU = !u || i.unit === u;
        return matchQ && matchU;
    });
    if (s === 'name_asc') filtered.sort((a,b) => a.name.localeCompare(b.name));
    else if (s === 'name_desc') filtered.sort((a,b) => b.name.localeCompare(a.name));
    else if (s === 'id_asc') filtered.sort((a,b) => a.id.localeCompare(b.id));

    document.getElementById('item-list').innerHTML = filtered.map(i=>`
        <div class="item-option ${selectedItemId===i.id?'selected':''}" onclick="selectItem('${i.id}')">
            <div class="item-option-name">[${i.id}] ${i.name}</div>
            <div class="item-option-desc">Unit: ${i.unit}</div>
        </div>`).join('');
}
function selectItem(id){ selectedItemId=id; filterItems(); }
function openItemModal(){ selectedItemId=null; document.getElementById('item-search').value=''; document.getElementById('item-filter-unit').value=''; document.getElementById('item-sort').value='name_asc'; filterItems(); document.getElementById('item-modal').classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeItemModal(){ document.getElementById('item-modal').classList.remove('open'); document.body.style.overflow = ''; }
function addSelectedItem(){
    if(!selectedItemId){alert('Please select an item.');return;}
    const i=catalog.find(x=>x.id===selectedItemId); if(!i)return;
    addedItems.push({idx:itemCounter++, id:i.id, name:i.name, unit:i.unit, notes:i.notes, qty:1});
    renderGoodsTable(); closeItemModal();
}
function removeGoods(idx){ addedItems=addedItems.filter(i=>i.idx!==idx); renderGoodsTable(); }
function openNewItemModal(){ closeItemModal(); document.getElementById('new-item-modal').classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeNewItemModal(){ document.getElementById('new-item-modal').classList.remove('open'); document.body.style.overflow = ''; }
function saveNewItem(){
    saveCurrentGoodsInputValuesToState();
    const name=document.getElementById('new-item-name').value.trim();
    if(!name){alert('Item name is required.');return;}
    const unit=document.getElementById('new-item-unit').value, notes=document.getElementById('new-item-notes').value.trim();
    const qty=parseFloat(document.getElementById('new-item-qty').value) || 1;
    const newId='ITM-'+Math.floor(Math.random()*9000+1000);
    catalog.push({id:newId, name, unit, notes});
    addedItems.push({idx:itemCounter++, id:newId, name, unit, notes, qty:qty});
    renderGoodsTable(); closeNewItemModal();
    ['new-item-name','new-item-notes'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('new-item-qty').value = '1';
}
function saveCurrentGoodsInputValuesToState() {
    addedItems.forEach(it => {
        const qtyInput = document.querySelector(`input[name="items[${it.idx}][quantity]"]`);
        if (qtyInput) {
            it.qty = parseFloat(qtyInput.value) || 1;
            const nameInput = document.querySelector(`input[name="items[${it.idx}][item_name]"]`);
            if (nameInput) it.name = nameInput.value;
            const brandInput = document.querySelector(`input[name="items[${it.idx}][brand]"]`);
            if (brandInput) it.brand = brandInput.value;
            const notesInput = document.querySelector(`input[name="items[${it.idx}][item_notes]"]`);
            if (notesInput) it.notes = notesInput.value;
            const unitSelect = document.querySelector(`select[name="items[${it.idx}][unit]"]`);
            if (unitSelect) it.unit = unitSelect.value;
        }
    });
}

function renderGoodsTable(){
    saveCurrentGoodsInputValuesToState();
    const t=document.getElementById('goods-tbody');
    document.getElementById('goods-count-label').textContent=`${addedItems.length} item(s) added`;
    if(!addedItems.length){t.innerHTML='<tr><td colspan="8" style="text-align:center;padding:28px 16px;color:#9ca3af;">No items added yet.</td></tr>';return;}
    t.innerHTML=addedItems.map((it,i)=>`<tr>
        <td>${i+1}</td>
        <td style="font-family: monospace; font-weight: 600; color: var(--primary);">${it.id}<input type="hidden" name="items[${it.idx}][item_id]" value="${it.id}"></td>
        <td><input class="form-control" name="items[${it.idx}][item_name]" value="${it.name}" required></td>
        <td><input type="number" class="form-control" name="items[${it.idx}][quantity]" value="${it.qty}" min="1" required></td>
        <td><select class="form-control" name="items[${it.idx}][unit]" required>${unitOptionsHtml}</select></td>
        <td><input class="form-control" name="items[${it.idx}][item_notes]" value="${it.notes||''}"></td>
        <td style="text-align:center;"><button type="button" onclick="removeGoods(${it.idx})" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;">&times;</button></td>
    </tr>`).join('');
    addedItems.forEach((it) => { document.querySelector(`select[name="items[${it.idx}][unit]"]`).value = it.unit; });
}

/* SERVICE LOGIC */
const existingServices = @json($existingServiceTemplates ?? []);

let mainServicesData = []; 
let selectedSvcTemplateId = null;
let modalJobsList = [];
let modalJobIdCounter = 0;
let modalItemIdCounter = 0;

function filterSvcList() {
    const q = document.getElementById('svc-search').value.toLowerCase();
    const s = document.getElementById('svc-sort').value;
    renderSvcSelectionList(q, s);
}
function renderSvcSelectionList(q='', s='name_asc') {
    let filtered = existingServices.filter(sv => !q || sv.service_name.toLowerCase().includes(q));
    if (s === 'name_asc') filtered.sort((a,b) => a.service_name.localeCompare(b.service_name));
    else if (s === 'name_desc') filtered.sort((a,b) => b.service_name.localeCompare(a.service_name));
    else if (s === 'jobs_desc') filtered.sort((a,b) => (b.jobs?.length || 0) - (a.jobs?.length || 0));

    document.getElementById('svc-list-container').innerHTML = filtered.map(sv => `
        <div class="item-option ${selectedSvcTemplateId===sv.id?'selected':''}" onclick="selectSvcTemplate('${sv.id}')">
            <div class="item-option-name">${sv.service_name}</div>
            <div class="item-option-desc">${sv.doc_number ? sv.doc_number+' — ' : ''}${(sv.jobs||[]).length} job scope(s)</div>
        </div>`).join('');
}
function selectSvcTemplate(id) { selectedSvcTemplateId=id; filterSvcList(); }
function openSvcListModal() { selectedSvcTemplateId=null; document.getElementById('svc-search').value=''; document.getElementById('svc-sort').value='name_asc'; filterSvcList(); document.getElementById('svc-list-modal').classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeSvcListModal() { document.getElementById('svc-list-modal').classList.remove('open'); document.body.style.overflow = ''; }
function addSelectedSvcTemplate() {
    if(!selectedSvcTemplateId) { alert('Please select a template.'); return; }
    const template = existingServices.find(x => x.id === selectedSvcTemplateId);
    if(template) { mainServicesData.push(JSON.parse(JSON.stringify(template))); renderMainServicesTable(); closeSvcListModal(); }
}

function openNewSvcModal() {
    closeSvcListModal();
    document.getElementById('modal-service-name').value = '';
    modalJobsList = [];
    addJobBlockToModal();
    document.getElementById('new-svc-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeNewSvcModal() { document.getElementById('new-svc-modal').classList.remove('open'); document.body.style.overflow = ''; }

function addJobBlockToModal() {
    const jId = modalJobIdCounter++;
    modalJobsList.push({ id: jId, description: '', items: [] });
    renderModalJobsDOM();
    addDraftItemToJob(jId);
}
function removeJobBlockFromModal(jId) { modalJobsList = modalJobsList.filter(j => j.id !== jId); renderModalJobsDOM(); }
function addDraftItemToJob(jId) {
    const job = modalJobsList.find(j => j.id === jId);
    if(job) { job.items.push({ id: modalItemIdCounter++, name: '', qty: 1, unit: 'Pcs', spec: '' }); renderModalJobsDOM(); }
}
function removeDraftItemFromJob(jId, iId) {
    const job = modalJobsList.find(j => j.id === jId);
    if(job) { job.items = job.items.filter(i => i.id !== iId); renderModalJobsDOM(); }
}

function saveCurrentModalInputValuesToState() {
    modalJobsList.forEach(job => {
        const jDescInput = document.getElementById(`modal-j-desc-${job.id}`);
        if(jDescInput) job.description = jDescInput.value;
        job.items.forEach(it => {
            const iName = document.getElementById(`modal-i-name-${it.id}`);
            const iQty  = document.getElementById(`modal-i-qty-${it.id}`);
            const iUnit = document.getElementById(`modal-i-unit-${it.id}`);
            const iSpec = document.getElementById(`modal-i-spec-${it.id}`);
            if(iName) it.name = iName.value;
            if(iQty)  it.qty  = parseFloat(iQty.value) || 1;
            if(iUnit) it.unit = iUnit.value;
            if(iSpec) it.spec = iSpec.value;
        });
    });
}

function renderModalJobsDOM() {
    saveCurrentModalInputValuesToState();
    const container = document.getElementById('modal-jobs-container');
    if(!modalJobsList.length) { container.innerHTML = '<div style="text-align:center; padding:20px; color:#9ca3af;">No job scopes added.</div>'; return; }

    container.innerHTML = modalJobsList.map((job) => `
        <div class="modal-job-block">
            <div class="modal-job-header">
                <div class="form-group" style="flex:1; margin-bottom:0;">
                    <label class="form-label">Job Description / Scope of Work <span class="req">*</span></label>
                    <input class="form-control" id="modal-j-desc-${job.id}" value="${job.description}" placeholder="e.g. Pembongkaran jaringan instalasi">
                </div>
                <button type="button" class="btn btn-outline btn-sm" style="color:#ef4444; border-color:#fca5a5; background:#fff;" onclick="removeJobBlockFromModal(${job.id})">Remove</button>
            </div>
            <div style="background:#fff; border:1px solid var(--border); border-radius:8px; padding:12px;">
                <div class="flex-between mb-2">
                    <label class="form-label mb-0" style="color:var(--text-muted);">Required Items & Services</label>
                    <button type="button" class="btn btn-primary btn-sm" style="padding:4px 10px; font-size:11.5px;" onclick="addDraftItemToJob(${job.id})">+ Add Item Line</button>
                </div>
                <table class="item-table">
                    <thead style="background:#f9fafb;"><tr><th>Description <span class="req">*</span></th><th style="width:75px;">Qty</th><th style="width:100px;">Unit</th><th>Spec</th><th style="width:30px;"></th></tr></thead>
                    <tbody>
                        ${job.items.length === 0 ? `<tr><td colspan="5" style="text-align:center; padding:12px; color:#9ca3af;">No rows added.</td></tr>` : 
                        job.items.map(it => `<tr>
                                <td><input class="form-control" id="modal-i-name-${it.id}" value="${it.name}"></td>
                                <td><input type="number" step="0.01" class="form-control" id="modal-i-qty-${it.id}" value="${it.qty}"></td>
                                <td><select class="form-control" id="modal-i-unit-${it.id}">${unitOptionsHtml}</select></td>
                                <td><input class="form-control" id="modal-i-spec-${it.id}" value="${it.spec}"></td>
                                <td style="text-align:center;"><button type="button" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;" onclick="removeDraftItemFromJob(${job.id}, ${it.id})">&times;</button></td>
                            </tr>`).join('')}
                    </tbody>
                </table>
            </div>
        </div>`).join('');

    modalJobsList.forEach(job => {
        job.items.forEach(it => {
            const selectEl = document.getElementById(`modal-i-unit-${it.id}`);
            if(selectEl) selectEl.value = it.unit;
        });
    });
}

function commitModalServiceToMainTable() {
    const svcName = document.getElementById('modal-service-name').value.trim();
    if(!svcName) { alert('Service Name is required!'); return; }
    saveCurrentModalInputValuesToState();
    if(!modalJobsList.length) { alert('Please create at least one job scope.'); return; }
    
    for(let j of modalJobsList) {
        if(!j.description.trim()) { alert('Job description cannot be empty!'); return; }
        if(!j.items.length) { alert('Each Job Scope needs at least one item.'); return; }
        for(let it of j.items) { if(!it.name.trim()) { alert('Item description is required!'); return; } }
    }

    mainServicesData.push({
        service_name: svcName,
        jobs: modalJobsList.map(j => ({
            description: j.description,
            items: j.items.map(it => ({ name: it.name, qty: it.qty, unit: it.unit, spec: it.spec }))
        }))
    });
    renderMainServicesTable();
    closeNewSvcModal();
}

function removeServiceRecord(sIdx) { mainServicesData.splice(sIdx, 1); renderMainServicesTable(); }

function renderMainServicesTable() {
    const tbody = document.getElementById('service-tbody');
    const hiddenContainer = document.getElementById('hidden-service-inputs');
    document.getElementById('service-count-label').textContent = `${mainServicesData.length} service(s) added`;
    
    if(!mainServicesData.length) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:28px 16px;color:#9ca3af;">No services added yet. Click "+ Add Service" to begin.</td></tr>';
        hiddenContainer.innerHTML = '';
        return;
    }

    let tableHtml = ''; let hiddenHtml = '';
    mainServicesData.forEach((svc, sIdx) => {
        let detailsBlockHtml = '';
        svc.jobs.forEach((job) => {
            detailsBlockHtml += `<div class="main-svc-container"><div class="main-job-title"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg> ${job.description}</div><ul class="nested-items-list">`;
            job.items.forEach(it => { detailsBlockHtml += `<li><span>• <b>${it.name}</b> <small style="color:#9ca3af;">(${it.spec || '-'})</small></span> <span>${it.qty} ${it.unit}</span></li>`; });
            detailsBlockHtml += `</ul></div>`;
        });

        tableHtml += `<tr>
            <td>${sIdx + 1}</td>
            <td><div style="font-weight:700; color:var(--primary); font-size:13.5px;">${svc.service_name}</div></td>
            <td>${detailsBlockHtml}</td>
            <td style="text-align:center;"><button type="button" onclick="removeServiceRecord(${sIdx})" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:18px;">&times;</button></td>
        </tr>`;

        hiddenHtml += `<input type="hidden" name="services[${sIdx}][service_name]" value="${svc.service_name}">`;
        svc.jobs.forEach((job, jIdx) => {
            hiddenHtml += `<input type="hidden" name="services[${sIdx}][jobs][${jIdx}][description]" value="${job.description}">`;
            job.items.forEach((it, iIdx) => {
                hiddenHtml += `
                    <input type="hidden" name="services[${sIdx}][jobs][${jIdx}][items][${iIdx}][item_name]" value="${it.name}">
                    <input type="hidden" name="services[${sIdx}][jobs][${jIdx}][items][${iIdx}][quantity]" value="${it.qty}">
                    <input type="hidden" name="services[${sIdx}][jobs][${jIdx}][items][${iIdx}][unit]" value="${it.unit}">
                    <input type="hidden" name="services[${sIdx}][jobs][${jIdx}][items][${iIdx}][specification]" value="${it.spec}">
                `;
            });
        });
    });

    tbody.innerHTML = tableHtml; hiddenContainer.innerHTML = hiddenHtml;
}

const modalIds = ['item-modal','new-item-modal','svc-list-modal','new-svc-modal'];
modalIds.forEach(id => {
    const el = document.getElementById(id);
    if(el) { el.addEventListener('click', function(e) { if(e.target===this) this.classList.remove('open'); }); }
});

document.getElementById('pr-form').addEventListener('submit', function(e) {
    const type = document.getElementById('item_type_field').value;
    if (type === 'goods' && addedItems.length === 0) { e.preventDefault(); alert('Please add at least one item.'); return; }
    if (type === 'service' && mainServicesData.length === 0) { e.preventDefault(); alert('Please register at least one complete service module.'); return; }
});

// Initialize form validation state on page load
document.addEventListener('DOMContentLoaded', function() {
    setTab('goods');
});
</script>
@endsection