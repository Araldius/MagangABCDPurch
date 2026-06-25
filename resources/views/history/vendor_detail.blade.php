@extends('layouts.app')

@section('content')
@php $pageTitle = 'Master Vendor Detail'; @endphp

<div style="margin-bottom:24px;">
    <a href="{{ route('history.vendors') }}" style="color:#6b7280;text-decoration:none;font-size:13px;display:flex;align-items:center;gap:6px;margin-bottom:12px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg> Back to Vendor Directory
    </a>
    <h1 style="font-size:24px;font-weight:700;color:#111827;margin:0;">{{ $vendor->vendor_name ?? $vendor->name }}</h1>
    <p style="color:#6b7280;margin:4px 0 0;font-size:14px;">Location: {{ $vendor->location ?? '-' }} &nbsp;|&nbsp; Total Supplied Value: Rp {{ number_format($totalValue, 0, ',', '.') }}</p>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;">
    <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;">
        <h2 style="font-size:14px;font-weight:700;color:#111827;margin:0">History of Supplied Items / Services</h2>
    </div>
    
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM ID</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ITEM NAME</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">VALUE (RP)</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">QTY</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">UNIT</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">SPEC</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">REQUESTED BY</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">LEAD TIME</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">REQ DATE</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">DOC NO.</th>
                    <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:600;color:#6b7280;text-transform:uppercase">ACTION</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $h)
                <tr style="border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:10px 14px;font-family:monospace;font-weight:600;font-size:11px;color:#3b82f6">{{ $h['item_id'] }}</td>
                    <td style="padding:10px 14px;font-weight:600">{{ $h['item_name'] }}</td>
                    <td style="padding:10px 14px;font-weight:700">{{ number_format($h['value'], 0, ',', '.') }}</td>
                    <td style="padding:10px 14px">{{ $h['qty'] }}</td>
                    <td style="padding:10px 14px">{{ $h['unit'] }}</td>
                    <td style="padding:10px 14px;font-size:11.5px;color:#6b7280;max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $h['specification'] }}">{{ $h['specification'] }}</td>
                    <td style="padding:10px 14px">{{ $h['requested_by'] }}</td>
                    <td style="padding:10px 14px">{{ $h['lead_time'] }}</td>
                    <td style="padding:10px 14px">{{ $h['req_date'] }}</td>
                    <td style="padding:10px 14px;font-family:monospace;font-weight:600;color:#111827">{{ $h['doc_no'] ?: '-' }}</td>
                    <td style="padding:10px 14px"><button onclick="window.location.href='/procurement-history/orders?search={{ $h['doc_no'] }}'" style="padding:4px 10px;font-size:11.5px;font-weight:600;color:#374151;background:#fff;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer">View Order</button></td>
                </tr>
                @empty
                <tr><td colspan="11" style="text-align:center;padding:36px 20px;color:#9ca3af;font-size:12.5px">No supplied items found for this vendor.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection