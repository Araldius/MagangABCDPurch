@extends('layouts.app')

@section('content')
@php $pageTitle = 'Item Detail'; @endphp

<div style="margin-bottom:24px;">
    <a href="{{ route('items.index') }}" class="btn-back" style="margin-bottom:12px; width:fit-content;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Back
    </a>
    <h1 style="font-size:24px;font-weight:700;color:#111827;margin:0;">{{ $item->item_name }}</h1>
    <p style="color:#6b7280;margin:4px 0 0;font-size:14px;">Code: {{ $item->item_code ?: '-' }} &nbsp;|&nbsp; Unit: {{ $item->unit }} &nbsp;|&nbsp; Status: {{ $item->is_archived ? 'Archived' : 'Active' }}</p>
</div>

<div style="display:flex;gap:24px;margin-bottom:24px">
    <div style="flex:1;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
        <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:8px">Specification</div>
        <div style="font-size:14px;color:#374151;">{{ $item->specification ?: 'No specification provided.' }}</div>
    </div>
    <div style="flex:1;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
        <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:8px">Total Purchase Value</div>
        <div style="font-size:20px;font-weight:700;color:#111827;">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
    </div>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;">
    <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
        <h2 style="font-size:14px;font-weight:700;color:#111827;margin:0">Purchase History</h2>
        <a href="{{ route('items.exportHistory', $item->id) }}" style="display:inline-block;padding:6px 12px;background:#fff;border:1px solid #d1d5db;border-radius:6px;font-size:12px;font-weight:600;color:#374151;cursor:pointer;text-decoration:none;">
            Download Excel
        </a>
    </div>
    
    <div style="overflow-x:auto">
        <table id="historyTable" style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">DATE</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">DOC NO.</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">VENDOR</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">REQUESTED BY</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">SPEC & NOTES</th>
                    <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">QTY</th>
                    <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">PRICE</th>
                    <th style="padding:9px 14px;text-align:right;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $h)
                <tr style="border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:10px 14px">{{ $h['date'] }}</td>
                    <td style="padding:10px 14px;font-family:monospace;font-weight:600;color:#111827">{{ $h['doc_no'] }}</td>
                    <td style="padding:10px 14px;font-weight:600;color:#374151">{{ $h['vendor_name'] }}</td>
                    <td style="padding:10px 14px;color:#6b7280">{{ $h['req_by'] }}</td>
                    <td style="padding:10px 14px;">
                        @if($h['spec'] !== '-')
                            <div style="font-size:11px;color:#374151;margin-bottom:2px"><strong>Spec:</strong> {{ $h['spec'] }}</div>
                        @endif
                        @if($h['notes'] !== '-')
                            <div style="font-size:11px;color:#6b7280;font-style:italic;"><strong>Notes:</strong> {{ $h['notes'] }}</div>
                        @endif
                        @if($h['spec'] === '-' && $h['notes'] === '-')
                            <span style="color:#9ca3af;font-size:11px">-</span>
                        @endif
                    </td>
                    <td style="padding:10px 14px;text-align:right;">{{ $h['qty'] }} {{ $h['unit'] }}</td>
                    <td style="padding:10px 14px;text-align:right;font-weight:600;">Rp {{ number_format($h['price'], 0, ',', '.') }}</td>
                    <td style="padding:10px 14px;text-align:right;font-weight:700;">Rp {{ number_format($h['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No purchase history found for this item.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


@endsection
