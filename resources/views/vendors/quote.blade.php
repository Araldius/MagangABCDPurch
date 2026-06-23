<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Quotation Portal - Magang ABCD Purch</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #111827;
            padding: 20px;
            color: #fff;
            text-align: center;
        }
        .header-title {
            font-size: 24px;
            font-weight: 700;
        }
        .header-desc {
            font-size: 14px;
            color: #9ca3af;
            margin-top: 4px;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px 0 rgba(0,0,0,0.06);
            overflow: hidden;
            margin-bottom: 24px;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            background-color: #fafafa;
        }
        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
        }
        .card-desc {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }
        .card-body {
            padding: 20px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box;
            outline: none;
            color: #111827;
            background: #fff;
        }
        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .table th {
            text-align: left;
            padding: 12px;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            background: #fafafa;
        }
        .table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            background-color: #111827;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-submit:hover {
            background-color: #1f2937;
        }
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        .alert-success { background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-title">PT Dunia Kimia Jaya</div>
        <div class="header-desc">Vendor Quotation Submission Portal</div>
    </div>

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

        @if(!session('success'))
            @if(isset($neededDate) && isset($closedDate))
                <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 16px; margin-bottom: 20px; border-radius: 4px;">
                    <strong style="color: #991b1b; font-size: 14px;">Batas Waktu Penawaran (Quotation Deadline)</strong>
                    <p style="margin: 4px 0 0 0; color: #b91c1c; font-size: 13px;">
                        Barang/Jasa ini dibutuhkan pada: <strong>{{ $neededDate->format('d M Y') }}</strong>.<br>
                        Mohon kirimkan penawaran harga Anda paling lambat tanggal <strong>{{ $closedDate->format('d M Y') }}</strong> (H-1 sebelum tanggal dibutuhkan).
                    </p>
                </div>
            @endif

        <form method="POST" action="{{ route('vendors.quote.submit', $rfq->vendor_token) }}">
            @csrf
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Vendor Quotation Portal</div>
                    <div class="card-desc">Please provide your company details and quotation for the items below.</div>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Company Name *</label>
                            <input type="text" class="form-control" name="vendor_name" id="vendor_name_input" required placeholder="PT. ABC XYZ">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email / Contact Number *</label>
                            <input type="text" class="form-control" name="vendor_contact" required placeholder="email@company.com">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Company Location / Address</label>
                        <input type="text" class="form-control" name="vendor_location" placeholder="Jakarta, Indonesia">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Quoted Items & Prices</div>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item / Service</th>
                                <th style="width: 100px;">Req. Qty</th>
                                <th style="width: 120px;">Unit Price (Rp) <span style="color:#ef4444">*</span></th>
                                <th style="width: 100px;">Offered Qty <span style="color:#ef4444">*</span></th>
                                <th style="width: 100px;">Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: #111827;">{{ $item->item_name }}</div>
                                    @if($item->specification)
                                        <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">{{ $item->specification }}</div>
                                    @endif
                                </td>
                                <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                <td>
                                    <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->id }}">
                                    <input type="number" name="items[{{ $index }}][price]" class="form-control" required min="0" placeholder="0">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control" required min="1" value="{{ $item->quantity }}">
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][unit]" class="form-control" value="{{ $item->unit }}" placeholder="Pcs/Lot">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-body" style="border-top: 1px solid #e5e7eb;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Additional Notes (Optional)</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Term of payment, lead time, warranty, etc."></textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:8px;"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Submit Quotation
            </button>
        </form>
        @endif

        <div style="text-align: center; color: #9ca3af; font-size: 12px; margin-top: 30px; margin-bottom: 30px;">
            &copy; {{ date('Y') }} Magang ABCD Purch. All rights reserved.<br>
            If you have questions regarding this RFQ, please contact our purchasing department.
        </div>
    </div>

</body>
</html>