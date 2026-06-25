<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Closed - Magang ABCD Purch</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .icon {
            color: #ef4444;
            margin-bottom: 20px;
        }
        .title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
        }
        .message {
            font-size: 15px;
            color: #4b5563;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .details {
            background-color: #f9fafb;
            padding: 16px;
            border-radius: 8px;
            text-align: left;
            font-size: 14px;
            color: #374151;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
        }
        .details div {
            margin-bottom: 8px;
        }
        .details div:last-child {
            margin-bottom: 0;
        }
        .strong {
            font-weight: 600;
            color: #111827;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
    </div>
    
    <div class="title">Penawaran Telah Ditutup</div>
    
    <div class="message">
        Mohon maaf, masa pengisian harga penawaran (Quotation) untuk permintaan ini telah berakhir. Tautan ini tidak dapat digunakan lagi.
    </div>

    <div class="details">
        <div><span class="strong">No. Referensi:</span> {{ $pr->document_number ?? '-' }}</div>
        @if(isset($neededDate))
        <div><span class="strong">Tanggal Dibutuhkan:</span> {{ $neededDate->format('d M Y') }}</div>
        @endif
        @if(isset($closedDate))
        <div><span class="strong">Batas Akhir Penawaran:</span> {{ $closedDate->format('d M Y') }}</div>
        @endif
    </div>

    <div style="font-size: 13px; color: #9ca3af;">
        Jika Anda memiliki pertanyaan, silakan hubungi tim Purchasing kami.
    </div>
</div>

</body>
</html>