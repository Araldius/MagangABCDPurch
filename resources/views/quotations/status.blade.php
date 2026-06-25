@extends('layouts.app')
@php $pageTitle = 'Status RFQ'; @endphp

@section('content')
<div class="page-header flex-between">
    <div>
        <div class="page-title">Status RFQ</div>
        <div class="page-desc">{{ $rfq->rfq_number ?? 'RFQ-#'.$rfq->id }} — Monitor penawaran dan kelola status.</div>
    </div>
    @php
        $statusBadge = $rfq->status === 'open' ? 'badge-rfq' : 'badge-completed';
    @endphp
    <span class="badge {{ $statusBadge }}" style="font-size:13px;padding:6px 14px;">{{ ucfirst($rfq->status) }}</span>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-label">No. RFQ</div>
        <div style="font-family:monospace;font-weight:700;font-size:14px;margin-top:6px;color:#2563eb;">{{ $rfq->rfq_number ?? '#'.$rfq->id }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Purchase Request</div>
        <div style="font-weight:700;font-size:14px;margin-top:6px;">{{ $rfq->purchaseRequest->document_number }}</div>
        <div class="stat-sub">{{ $rfq->purchaseRequest->title ?? '—' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Vendor Terpilih</div>
        <div style="font-weight:700;font-size:14px;margin-top:6px;">{{ $rfq->vendor->vendor_name ?? '— Belum dipilih' }}</div>
        <div class="stat-sub">{{ $rfq->vendor->location ?? '' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Periode Aktif</div>
        @if($rfq->quotationPeriods->count())
        @php $period = $rfq->quotationPeriods->last(); @endphp
        <div style="font-weight:600;font-size:13px;margin-top:6px;">
            {{ \Carbon\Carbon::parse($period->start_date)->format('d M') }} — {{ \Carbon\Carbon::parse($period->end_date)->format('d M Y') }}
        </div>
        <div class="stat-sub">Round {{ $period->round }} — {{ ucfirst($period->status) }}</div>
        @else
        <div style="color:var(--text-muted);margin-top:6px;font-size:13px;">Belum ditentukan</div>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:flex-start;">

    {{-- Left: Vendor Quotations --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Penawaran Vendor</div>
                    <div class="card-desc">{{ $rfq->vendorQuotations->count() }} vendor telah diundang.</div>
                </div>
                @if($rfq->status === 'open')
                <a href="{{ route('vendors.list', $rfq) }}" class="btn btn-outline btn-sm">+ Tambah Vendor</a>
                @endif
            </div>
            @if($rfq->vendorQuotations->count())
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Vendor</th>
                            <th>Lokasi</th>
                            <th>Catatan</th>
                            <th>Status</th>
                            <th>Submit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rfq->vendorQuotations as $vq)
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $vq->vendor->vendor_name }}</div>
                            </td>
                            <td class="text-muted text-sm">{{ $vq->vendor->location ?? '—' }}</td>
                            <td class="text-muted text-sm" style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $vq->notes ?? '—' }}</td>
                            <td>
                                @if($vq->status === 'submitted')
                                    <span class="badge badge-approved">Submitted</span>
                                @elseif($vq->status === 'draft')
                                    <span class="badge badge-pending">Draft</span>
                                @else
                                    <span class="badge badge-inprocess">{{ ucfirst($vq->status) }}</span>
                                @endif
                            </td>
                            <td class="text-muted text-sm">{{ $vq->submitted_at ? \Carbon\Carbon::parse($vq->submitted_at)->format('d M Y') : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="padding:32px;text-align:center;color:var(--text-muted);">Belum ada vendor yang diundang.</div>
            @endif
        </div>

        {{-- Current Quotation Summary --}}
        @if($rfq->quotation)
        <div class="card">
            <div class="card-header">
                <div class="card-title">Quotation Final Saat Ini</div>
                <span class="badge badge-approved">Finalized</span>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div style="background:#f0fdf4;border-radius:8px;padding:14px;">
                        <div class="stat-label">Total Harga</div>
                        <div style="font-size:20px;font-weight:800;color:#15803d;margin-top:4px;">
                            Rp {{ number_format($rfq->quotation->total_price, 0, ',', '.') }}
                        </div>
                    </div>
                    <div style="background:#f9fafb;border-radius:8px;padding:14px;">
                        <div class="stat-label">Vendor</div>
                        <div style="font-weight:700;margin-top:4px;">{{ $rfq->quotation->vendor->vendor_name ?? '—' }}</div>
                    </div>
                </div>
                @if($rfq->quotation->note)
                <div style="background:#f9fafb;border-radius:8px;padding:12px;font-size:13px;color:var(--text-muted);">
                    {{ $rfq->quotation->note }}
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Right: Update Status Form --}}
    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Update Status RFQ</div>
            </div>
            <div class="card-body">
                <form action="{{ route('quotations.updateStatus', $rfq) }}" method="post">
                    @csrf
                    <div class="form-group" style="margin-bottom:14px;">
                        <label class="form-label">Status RFQ</label>
                        <select name="status" class="form-control" required>
                            <option value="open"   {{ $rfq->status === 'open'   ? 'selected' : '' }}>Open</option>
                            <option value="closed" {{ $rfq->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:14px;">
                        <label class="form-label">Tanggal Penutupan</label>
                        <input type="date" name="closed_at" class="form-control" value="{{ old('closed_at', optional($rfq->closed_at)->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group" style="margin-bottom:20px;">
                        <label class="form-label">Catatan</label>
                        <textarea name="note" class="form-control" rows="3">{{ old('note', $rfq->note) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        Update & Lanjut ke Finalisasi
                    </button>
                </form>
            </div>
        </div>

        @if($rfq->status === 'closed')
        <div class="card">
            <div class="card-header">
                <div class="card-title">Finalisasi Quotation</div>
            </div>
            <div class="card-body">
                <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">RFQ sudah ditutup. Input harga final dari vendor terpilih untuk menyelesaikan proses pengadaan.</p>
                <a href="{{ route('quotations.final', $rfq) }}" class="btn btn-primary" style="width:100%;justify-content:center;">
                    Input Quotation Final →
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection