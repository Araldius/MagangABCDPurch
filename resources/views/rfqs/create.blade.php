@extends('layouts.app')
@php $pageTitle = 'Buat RFQ'; @endphp

@section('content')
<div class="page-header">
    <div class="page-title">Buat Request for Quotation</div>
    <div class="page-desc">Pilih purchase request yang akan dibuatkan RFQ untuk proses pengadaan vendor.</div>
</div>

<div style="max-width:760px;">
    <form action="{{ route('rfqs.store') }}" method="post">
        @csrf

        {{-- PR Info Card --}}
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <div>
                    <div class="card-title">Informasi RFQ</div>
                    <div class="card-desc">Nomor RFQ akan digenerate otomatis oleh sistem.</div>
                </div>
                <span class="badge badge-rfq">Draft</span>
            </div>
            <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div class="form-group">
                    <label class="form-label">No. RFQ</label>
                    <input class="form-control" value="Auto-generate (RFQ-YYYY-MMDD-NNN)" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Buat</label>
                    <input class="form-control" value="{{ now()->format('d M Y') }}" disabled>
                </div>
            </div>
        </div>

        {{-- Select PR --}}
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <div>
                    <div class="card-title">Pilih Purchase Request</div>
                    <div class="card-desc">Hanya PR dengan status <strong>In Process</strong> yang belum memiliki RFQ aktif.</div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group" style="margin-bottom:20px;">
                    <label class="form-label">Purchase Request <span class="req">*</span></label>
                    <select name="purchase_request_id" class="form-control" required id="prSelect" onchange="updatePRInfo(this)">
                        <option value="">— Pilih Purchase Request —</option>
                        @foreach($requests as $req)
                        <option value="{{ $req->id }}"
                            data-doc="{{ $req->document_number }}"
                            data-title="{{ $req->title ?? '—' }}"
                            data-dept="{{ $req->department ?? '—' }}"
                            data-plant="{{ $req->plant ?? '—' }}"
                            data-need="{{ $req->need_date ? \Carbon\Carbon::parse($req->need_date)->format('d M Y') : '—' }}"
                            data-items="{{ $req->items->count() }}"
                            {{ old('purchase_request_id') == $req->id ? 'selected' : '' }}>
                            {{ $req->document_number }} — {{ $req->title ?? $req->department }} ({{ $req->department }})
                        </option>
                        @endforeach
                    </select>
                    @error('purchase_request_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- PR Preview (shown on select) --}}
                <div id="prPreview" style="display:none;background:#f9fafb;border-radius:10px;padding:16px;">
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                        <div>
                            <div class="stat-label">No. Dokumen</div>
                            <div id="pDoc" style="font-weight:700;font-family:monospace;margin-top:4px;color:#111;">—</div>
                        </div>
                        <div>
                            <div class="stat-label">Departemen</div>
                            <div id="pDept" style="font-weight:600;margin-top:4px;">—</div>
                        </div>
                        <div>
                            <div class="stat-label">Plant</div>
                            <div id="pPlant" style="font-weight:600;margin-top:4px;">—</div>
                        </div>
                        <div>
                            <div class="stat-label">Need Date</div>
                            <div id="pNeed" style="font-weight:600;margin-top:4px;">—</div>
                        </div>
                        <div>
                            <div class="stat-label">Jumlah Item</div>
                            <div id="pItems" style="font-weight:600;margin-top:4px;">—</div>
                        </div>
                    </div>
                </div>

                @if($requests->isEmpty())
                <div style="background:#fff7ed;border:1px solid #fde68a;border-radius:8px;padding:14px;font-size:13.5px;color:#92400e;">
                    ⚠️ Tidak ada Purchase Request yang tersedia. Semua PR sudah memiliki RFQ aktif atau belum ada PR dengan status Pending.
                </div>
                @endif
            </div>
        </div>

        {{-- Notes --}}
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <div class="card-title">Catatan RFQ</div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Catatan / Instruksi Khusus</label>
                    <textarea name="note" class="form-control" rows="3" placeholder="Tambahkan catatan untuk vendor atau tim purchasing...">{{ old('note') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex-between" style="justify-content:flex-end;gap:12px;">
            <a href="{{ route('dashboard') }}" class="btn-back">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Batal
            </a>
            <button type="submit" class="btn btn-primary" {{ $requests->isEmpty() ? 'disabled' : '' }}>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
                Buat RFQ & Pilih Vendor
            </button>
        </div>
    </form>
</div>

<script>
function updatePRInfo(select) {
    const opt     = select.options[select.selectedIndex];
    const preview = document.getElementById('prPreview');
    if (!select.value) { preview.style.display = 'none'; return; }
    document.getElementById('pDoc').textContent   = opt.dataset.doc   || '—';
    document.getElementById('pDept').textContent  = opt.dataset.dept  || '—';
    document.getElementById('pPlant').textContent = opt.dataset.plant || '—';
    document.getElementById('pNeed').textContent  = opt.dataset.need  || '—';
    document.getElementById('pItems').textContent = (opt.dataset.items || '0') + ' item';
    preview.style.display = 'block';
}
// Trigger on load if old value exists
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('prSelect');
    if (sel.value) updatePRInfo(sel);
});
</script>
@endsection