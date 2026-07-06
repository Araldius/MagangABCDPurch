@extends('layouts.app')
@php $pageTitle = 'Master Item'; @endphp
@section('content')

<div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:20px">
    <div>
        <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 3px">Master Item</h1>
        <p style="font-size:12.5px;color:#6b7280;margin:0">Manage catalog items for goods and service requests.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a id="btn-export" href="{{ route('items.export') }}" style="display:inline-block;padding:8px 14px;background:#fff;border:1px solid #e5e7eb;border-radius:6px;font-size:12.5px;font-weight:600;color:#374151;text-decoration:none;cursor:pointer;">Download Excel</a>
        <button onclick="openAddModal()" style="padding:8px 14px;background:#111827;border:1px solid #111827;border-radius:6px;font-size:12.5px;font-weight:600;color:#fff;cursor:pointer;">+ Add New Item</button>
    </div>
</div>



<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px">
    {{-- Toolbar --}}
    <div style="display:flex;gap:8px;align-items:center;padding:12px 20px;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;">
        <input type="text" id="item-search" placeholder="Search by name, code or spec..."
            oninput="applyFilters()"
            style="height:32px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;font-size:12.5px;width:300px;outline:none;font-family:inherit;">
        
        <div style="position:relative;">
            <select id="status-filter" onchange="applyFilters()"
                style="height:32px;padding:0 28px 0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12.5px;color:#374151;background:#fff;appearance:none;cursor:pointer;font-family:inherit;">
                <option value="active">Active Only</option>
                <option value="archived">Archived Only</option>
                <option value="all">All Items</option>
            </select>
            <svg style="position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-linecap="round"/></svg>
        </div>
    </div>

    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb">
                    <th onclick="itemSort(0)" style="padding:9px 20px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">CODE <span id="is0" style="font-size:9px;">↕</span></th>
                    <th onclick="itemSort(1)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">ITEM NAME <span id="is1" style="font-size:9px;">↕</span></th>
                    <th onclick="itemSort(2)" style="padding:9px 14px;text-align:left;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">UNIT <span id="is2" style="font-size:9px;">+ </span></th>
                    <th onclick="itemSort(3)" style="padding:9px 14px;text-align:center;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">STATUS <span id="is3" style="font-size:9px;">+ </span></th>
                    <th style="padding:9px 20px;text-align:center;font-size:10.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">ACTIONS</th>
                </tr>
            </thead>
            <tbody id="item-tbody">
                @forelse($items as $item)
                <tr style="border-bottom:1px solid #f3f4f6;opacity:{{ $item->is_archived ? '0.55' : '1' }}"
                    data-status="{{ $item->is_archived ? 'archived' : 'active' }}"
                    onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:13px 20px;font-family:monospace;color:#374151;">{{ $item->item_code ?: '-' }}</td>
                    <td style="padding:13px 14px;font-weight:600;color:#111827;">{{ $item->item_name }}</td>
                    <td style="padding:13px 14px;color:#374151;">{{ $item->unit }}</td>
                    <td style="padding:13px 14px;text-align:center;">
                        @if($item->is_archived)
                            <span style="background:#fef2f2;color:#dc2626;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:600;">Archived</span>
                        @else
                            <span style="background:#dcfce7;color:#16a34a;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:600;">Active</span>
                        @endif
                    </td>
                    <td style="padding:13px 20px;text-align:center;white-space:nowrap;">
                        <a href="{{ route('items.show', $item->id) }}" style="background:#fff;border:1px solid #e5e7eb;color:#3b82f6;border-radius:6px;cursor:pointer;font-weight:600;font-size:11.5px;padding:4px 10px;margin-right:4px;text-decoration:none;display:inline-block;">View Detail</a>
                        <button onclick='openEditModal(@json($item))' style="background:#fff;border:1px solid #e5e7eb;color:#374151;border-radius:6px;cursor:pointer;font-weight:600;font-size:11.5px;padding:4px 10px;margin-right:4px;">Edit</button>
                        <form id="archive-form-{{ $item->id }}" action="{{ route('items.archive', $item->id) }}" method="POST" style="display:inline-block;margin:0;">
                            @csrf
                            <button type="button" 
                                    class="btn-archive-item"
                                    data-id="{{ $item->id }}"
                                    data-archived="{{ $item->is_archived ? 'true' : 'false' }}"
                                    style="background:#fff;border:1px solid #e5e7eb;color:{{ $item->is_archived ? '#16a34a' : '#f59e0b' }};border-radius:6px;cursor:pointer;font-weight:600;font-size:11.5px;padding:4px 10px;">
                                {{ $item->is_archived ? 'Restore' : 'Archive' }}
                            </button>
                        </form>
                        <form id="delete-form-{{ $item->id }}" action="{{ route('items.destroy', $item->id) }}" method="POST" style="display:inline-block;margin:0;">
                            @csrf
                            <button type="button" 
                                    class="btn-delete-item"
                                    data-id="{{ $item->id }}"
                                    style="background:#fff;border:1px solid #e5e7eb;color:#ef4444;border-radius:6px;cursor:pointer;font-weight:600;font-size:11.5px;padding:4px 10px;margin-left:4px;">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr id="item-empty" style="display:none;"><td colspan="6" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No items found.</td></tr>
                @endforelse
                <tr id="item-empty-js" style="display:none;"><td colspan="6" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No items found matching your search.</td></tr>
            </tbody>
        </table>
    </div>
    <div id="item-pager" style="padding:12px 20px;border-top:1px solid #f3f4f6;"></div>
</div>

<!-- Modal Overlay styling for standard looks -->
<style>
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; padding:16px; }
.modal-overlay.open { display:flex; }
.modal-box { background:#fff; border-radius:12px; width:100%; max-width:500px; max-height:90vh; overflow-y:auto; box-shadow:0 10px 25px rgba(0,0,0,0.1); }
.modal-header { padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; }
.modal-title { font-size:16px; font-weight:700; color:#111827; }
.modal-close { background:none; border:none; font-size:24px; color:#9ca3af; cursor:pointer; line-height:1; }
.modal-body { padding:20px; display:flex; flex-direction:column; gap:16px; }
.modal-footer { padding:16px 20px; border-top:1px solid #f3f4f6; display:flex; justify-content:flex-end; gap:12px; background:#fafafa; border-bottom-left-radius:12px; border-bottom-right-radius:12px; }
.form-label { display:block; font-size:12px; font-weight:600; color:#4b5563; margin-bottom:6px; }
.form-control { width:100%; height:36px; border:1px solid #d1d5db; border-radius:6px; padding:0 10px; font-size:13px; font-family:inherit; outline:none; transition:border-color 0.2s; box-sizing:border-box; }
.form-control:focus { border-color:#3b82f6; }
textarea.form-control { height:auto; padding:8px 10px; resize:vertical; }
</style>

<div class="modal-overlay" id="item-modal">
    <div class="modal-box">
        <form id="item-form" method="POST">
            @csrf
            <div class="modal-header">
                <div class="modal-title" id="modal-title">Add New Item</div>
                <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div>
                    <label class="form-label">Item Code (Auto-generated)</label>
                    <input type="text" name="item_code" id="input_item_code" class="form-control" placeholder="e.g. ITM-001" readonly style="background:#f3f4f6; color:#6b7280; cursor:not-allowed;">
                </div>
                <div>
                    <label class="form-label">Item Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="item_name" id="input_item_name" class="form-control" required>
                </div>
                <div>
                    <label class="form-label">Unit <span style="color:#ef4444">*</span></label>
                    <select name="unit" id="input_unit" class="form-control" required>
                        <option value="">Select Unit</option>
                        @foreach(['Pcs', 'Unit', 'Box', 'Kg', 'Liter', 'Meter', 'Roll', 'Set', 'Lot', 'Jasa', 'Pack', 'Ls'] as $u)
                            <option value="{{ $u }}">{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Specification</label>
                    <textarea name="specification" id="input_specification" class="form-control" rows="3"></textarea>
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="item_notes" id="input_item_notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal()" style="padding:8px 16px;background:#fff;border:1px solid #e5e7eb;border-radius:6px;font-size:12.5px;font-weight:600;color:#374151;cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:8px 16px;background:#111827;border:1px solid #111827;border-radius:6px;font-size:12.5px;font-weight:600;color:#fff;cursor:pointer;">Save Item</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('modal-title').textContent = 'Add New Item';
        const form = document.getElementById('item-form');
        form.action = '{{ route("items.store") }}';
        form.reset();
        document.getElementById('input_item_code').value = '{{ $nextId }}';
        document.getElementById('item-modal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function openEditModal(item) {
        document.getElementById('modal-title').textContent = 'Edit Item';
        const form = document.getElementById('item-form');
        form.action = '{{ url("master-items/update") }}/' + item.id;
        
        document.getElementById('input_item_code').value = item.item_code || '';
        document.getElementById('input_item_name').value = item.item_name || '';
        document.getElementById('input_unit').value = item.unit || '';
        document.getElementById('input_specification').value = item.specification || '';
        document.getElementById('input_item_notes').value = item.item_notes || '';

        document.getElementById('item-modal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('item-modal').classList.remove('open');
        document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            const archiveBtn = e.target.closest('.btn-archive-item');
            const deleteBtn = e.target.closest('.btn-delete-item');
            if (archiveBtn) {
                e.preventDefault();
                const id = archiveBtn.getAttribute('data-id');
                const isArchived = archiveBtn.getAttribute('data-archived') === 'true';
                
                const text = isArchived ? 'mengaktifkan kembali' : 'mengarsipkan';
                const btnText = isArchived ? 'Restore' : 'Archive';
                const color = isArchived ? '#16a34a' : '#f59e0b';
                
                showConfirmModal('Konfirmasi', 'Apakah Anda yakin ingin <b>' + text + '</b> item ini?', btnText, color, function() {
                    const form = document.getElementById('archive-form-' + id);
                    if (form) form.submit();
                });
            }
            if (deleteBtn) {
                e.preventDefault();
                const id = deleteBtn.getAttribute('data-id');
                
                showConfirmModal('Konfirmasi', 'Apakah Anda yakin ingin menghapus item ini secara permanen?', 'Delete', '#ef4444', function() {
                    const form = document.getElementById('delete-form-' + id);
                    if (form) form.submit();
                });
            }
        });
    });

    /* JS TABLE LOGIC */
    let itemSortState = { col: null, dir: 'asc' };
    let itemPage = 1, itemPageSize = 10;

    function applyFilters() {
        const searchInput = document.getElementById('item-search');
        const statusSelect = document.getElementById('status-filter');
        
        const q = (searchInput?.value || '').toLowerCase();
        const status = statusSelect?.value || 'active';
        
        // Update URL to preserve state across reloads (like when archiving)
        const newUrl = new URL(window.location.href);
        if (status !== 'active') newUrl.searchParams.set('status', status);
        else newUrl.searchParams.delete('status');
        if (q) newUrl.searchParams.set('search', searchInput.value);
        else newUrl.searchParams.delete('search');
        window.history.replaceState({}, '', newUrl);
        const exportBtn = document.getElementById('btn-export');
        if (exportBtn) {
            const exportUrl = new URL('{{ route("items.export") }}');
            if (status !== 'active') exportUrl.searchParams.set('status', status);
            if (q) exportUrl.searchParams.set('search', q);
            if (itemSortState.col !== null) {
                exportUrl.searchParams.set('sort_col', itemSortState.col);
                exportUrl.searchParams.set('sort_dir', itemSortState.dir);
            }
            exportBtn.href = exportUrl.toString();
        }
        
        let rows = Array.from(document.querySelectorAll('#item-tbody tr:not(#item-empty):not(#item-empty-js)'));
        let filtered = rows.filter(r => {
            if (q && !r.textContent.toLowerCase().includes(q)) return false;
            if (status !== 'all' && r.dataset.status !== status) return false;
            return true;
        });

        if (itemSortState.col !== null) {
            filtered.sort((a, b) => {
                const at = (a.querySelectorAll('td')[itemSortState.col]?.textContent || '').trim();
                const bt = (b.querySelectorAll('td')[itemSortState.col]?.textContent || '').trim();
                const cmp = at.localeCompare(bt, 'id', { numeric: true });
                return itemSortState.dir === 'asc' ? cmp : -cmp;
            });
        }

        rows.forEach(r => r.style.display = 'none');
        const emptyJs = document.getElementById('item-empty-js');
        const pages = Math.max(1, Math.ceil(filtered.length / itemPageSize));
        if (itemPage > pages) itemPage = 1;
        const start = (itemPage - 1) * itemPageSize;
        const end   = Math.min(itemPage * itemPageSize, filtered.length);
        const tbody = document.getElementById('item-tbody');
        
        filtered.slice(start, end).forEach(r => { r.style.display = ''; tbody.appendChild(r); });
        if (emptyJs) emptyJs.style.display = filtered.length === 0 ? '' : 'none';

        let btns = '';
        for (let i = 1; i <= pages; i++) {
            btns += `<button onclick="itemGoto(${i})" style="min-width:28px;height:28px;border-radius:6px;border:1px solid ${i===itemPage?'#111827':'#e5e7eb'};background:${i===itemPage?'#111827':'#fff'};color:${i===itemPage?'#fff':'#374151'};font-size:12px;font-weight:600;cursor:pointer;padding:0 6px;">${i}</button>`;
        }
        const pager = document.getElementById('item-pager');
        if (pager) pager.innerHTML = `<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-size:12px;color:#6b7280;">${filtered.length===0?'No results':`Showing ${start+1}–${end} of ${filtered.length}`}</span>
            <div style="display:flex;gap:4px;">
                <button onclick="itemGoto(${itemPage-1})" ${itemPage<=1?'disabled':''} style="min-width:28px;height:28px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-size:13px;opacity:${itemPage<=1?.35:1};">‹</button>
                ${btns}
                <button onclick="itemGoto(${itemPage+1})" ${itemPage>=pages?'disabled':''} style="min-width:28px;height:28px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:pointer;font-size:13px;opacity:${itemPage>=pages?.35:1};">›</button>
            </div>
            <select onchange="itemSetPageSize(this.value)" style="height:28px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;padding:0 6px;background:#fff;outline:none;">
                ${[5,10,20,50].map(n=>`<option value="${n}" ${n===itemPageSize?'selected':''}>${n} / page</option>`).join('')}
            </select>
        </div>`;
    }

    function itemSort(col) {
        if (itemSortState.col === col) itemSortState.dir = itemSortState.dir==='asc'?'desc':'asc';
        else { itemSortState.col = col; itemSortState.dir = 'asc'; }
        document.querySelectorAll('[id^="is"]').forEach(el => el.textContent = '↕');
        const el = document.getElementById('is'+col);
        if (el) el.textContent = itemSortState.dir==='asc'?'↑':'↓';
        applyFilters();
    }
    function itemGoto(p) { itemPage = p; applyFilters(); }
    function itemSetPageSize(s) { itemPageSize = parseInt(s); itemPage = 1; applyFilters(); }

    document.addEventListener('DOMContentLoaded', () => { 
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('status')) {
            const statusSelect = document.getElementById('status-filter');
            if (statusSelect) statusSelect.value = urlParams.get('status');
        }
        if (urlParams.has('search')) {
            const searchInput = document.getElementById('item-search');
            if (searchInput) searchInput.value = urlParams.get('search');
        }
        applyFilters(); 
    });
</script>
@endsection
