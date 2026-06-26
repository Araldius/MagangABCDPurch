@extends('layouts.app')

@section('content')
@php $pageTitle = 'Master Vendor Detail'; @endphp

<div style="margin-bottom:24px;">
    <a href="{{ route('history.master.vendors') }}" style="color:#6b7280;text-decoration:none;font-size:13px;display:flex;align-items:center;gap:6px;margin-bottom:12px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Back to Master Vendor
    </a>
    <h1 style="font-size:24px;font-weight:700;color:#111827;margin:0;">{{ $vendor->vendor_name ?? $vendor->name }}</h1>
    <p style="color:#6b7280;margin:4px 0 0;font-size:14px;">Location: {{ $vendor->location ?? '-' }} &nbsp;|&nbsp; Total Supplied Value: Rp {{ number_format($totalValue, 0, ',', '.') }}</p>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;">
    <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;">
        <h2 style="font-size:14px;font-weight:700;color:#111827;margin:0">Quotation History</h2>
    </div>
    
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">DOC NO.</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">REQ DATE</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">SUBMITTED AT</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEMS COUNT</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">TOTAL VALUE (RP)</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">STATUS</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ACTION</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $h)
                <tr style="border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:10px 14px;font-family:monospace;font-weight:600;color:#111827">{{ $h['doc_no'] ?: '-' }}</td>
                    <td style="padding:10px 14px">{{ $h['req_date'] }}</td>
                    <td style="padding:10px 14px">{{ $h['submitted_at'] }}</td>
                    <td style="padding:10px 14px">{{ $h['items_count'] }}</td>
                    <td style="padding:10px 14px;font-weight:700">{{ number_format($h['value'], 0, ',', '.') }}</td>
                    <td style="padding:10px 14px">
                        @if($h['is_selected'])
                            <span style="background:#d1fae5;color:#065f46;padding:4px 8px;border-radius:999px;font-size:11px;font-weight:600;">Selected</span>
                        @else
                            <span style="background:#f3f4f6;color:#6b7280;padding:4px 8px;border-radius:999px;font-size:11px;font-weight:600;">Not Selected</span>
                        @endif
                    </td>
                    <td style="padding:10px 14px"><button onclick="window.location.href='/procurement-history/orders?search={{ $h['doc_no'] }}'" style="padding:4px 10px;font-size:11.5px;font-weight:600;color:#374151;background:#fff;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer">View Order</button></td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No quotation history found for this vendor.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
