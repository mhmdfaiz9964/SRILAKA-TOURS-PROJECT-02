<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>In Cheques Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #6366f1; padding-bottom: 10px; }
        .header h1 { color: #6366f1; margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .header p { color: #6b7280; margin: 5px 0; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f3f4f6; color: #374151; padding: 10px; text-align: left; font-size: 10px; text-transform: uppercase; font-weight: bold; border-bottom: 2px solid #e5e7eb; }
        td { padding: 10px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tr:nth-child(even) { background: #f9fafb; }
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; display: inline-block; text-transform: uppercase; }
        .badge-received { background: #dbeafe; color: #1e40af; }
        .badge-deposited { background: #fef9c3; color: #854d0e; }
        .badge-realized { background: #dcfce7; color: #166534; }
        .badge-returned { background: #fee2e2; color: #991b1b; }
        .badge-transferred { background: #f3e8ff; color: #6b21a8; }
        .amount { font-weight: bold; color: #1f2937; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #9ca3af; padding: 10px 0; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>In Cheques Report</h1>
        <p>Generated on {{ now()->format('d M, Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Cheque No.</th>
                <th>Bank</th>
                <th>Payer</th>
                <th>Status</th>
                <th style="text-align: right;">Amount (LKR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cheques as $cheque)
            <tr>
                <td>{{ $cheque->cheque_date->format('d/m/Y') }}</td>
                <td><span style="font-family: monospace;">{{ $cheque->cheque_number }}</span></td>
                <td>{{ $cheque->bank->name ?? '-' }}</td>
                <td>{{ $cheque->payer_name }}</td>
                <td>
                    @php
                        $statusClass = 'badge-' . ($cheque->status == 'transferred_to_third_party' ? 'transferred' : $cheque->status);
                    @endphp
                    <span class="badge {{ $statusClass }}">
                        {{ ucwords(str_replace('_', ' ', $cheque->status)) }}
                    </span>
                </td>
                <td style="text-align: right;" class="amount">{{ number_format($cheque->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sri Lanka Tours | Confidential Report
    </div>
</body>
</html>
