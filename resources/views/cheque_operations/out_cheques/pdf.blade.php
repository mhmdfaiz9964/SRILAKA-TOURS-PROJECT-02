<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Out Cheques Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #ef4444; padding-bottom: 10px; }
        .header h1 { color: #ef4444; margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .header p { color: #6b7280; margin: 5px 0; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #fef2f2; color: #991b1b; padding: 10px; text-align: left; font-size: 10px; text-transform: uppercase; font-weight: bold; border-bottom: 2px solid #fecaca; }
        td { padding: 10px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tr:nth-child(even) { background: #fff1f2; }
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; display: inline-block; text-transform: uppercase; }
        .badge-sent { background: #e0e7ff; color: #3730a3; }
        .badge-realized { background: #dcfce7; color: #166534; }
        .badge-bounced { background: #fee2e2; color: #991b1b; }
        .amount { font-weight: bold; color: #991b1b; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #9ca3af; padding: 10px 0; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Out Cheques Report</h1>
        <p>Generated on {{ now()->format('d M, Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Cheque No.</th>
                <th>Bank</th>
                <th>Payee</th>
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
                <td>{{ $cheque->payee_name }}</td>
                <td>
                    <span class="badge badge-{{ $cheque->status }}">
                        {{ ucfirst($cheque->status) }}
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
