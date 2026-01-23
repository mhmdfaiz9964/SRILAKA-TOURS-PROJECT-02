<!DOCTYPE html>
<html>
<head>
    <title>Cheques Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #6366f1; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #6366f1; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #6366f1; color: white; padding: 8px; text-align: left; text-transform: uppercase; font-size: 8px; }
        td { border-bottom: 1px solid #eee; padding: 8px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 8px; }
        .bg-success { background-color: #ecfdf5; color: #10b981; }
        .bg-warning { background-color: #fffbeb; color: #f59e0b; }
        .bg-danger { background-color: #fef2f2; color: #ef4444; }
        .row-even { background-color: #f9fafb; }
    </style>
</head>
<body>
    <div class="header">
        <h2>CHEQUE RECOVERY SYSTEM</h2>
        <p>Generated on: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Status</th>
                <th>Client</th>
                <th>CHQ #</th>
                <th>Bank</th>
                <th class="text-end">Amount</th>
                <th class="text-end">Balance</th>
                <th>3rd Party</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cheques as $index => $cheque)
            <tr class="{{ $index % 2 == 0 ? '' : 'row-even' }}">
                <td>{{ \Carbon\Carbon::parse($cheque->cheque_date)->format('d/m/Y') }}</td>
                <td>{{ ucwords($cheque->payment_status) }}</td>
                <td>{{ $cheque->payer_name }}</td>
                <td>{{ $cheque->cheque_number }}</td>
                <td>{{ $cheque->bank->name ?? 'N/A' }}</td>
                <td class="text-end">{{ number_format($cheque->amount, 2) }}</td>
                <td class="text-end" style="color: #ef4444; font-weight: bold;">
                    {{ number_format($cheque->amount - ($cheque->payments_sum_amount ?? 0), 2) }}
                </td>
                <td>{{ $cheque->payee_name ?? '-' }} ({{ ucwords($cheque->third_party_payment_status ?? '-') }})</td>
                <td>{{ $cheque->return_reason ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right; font-size: 12px;">
        <strong>Total Amount: </strong> LKR {{ number_format($cheques->sum('amount'), 2) }}
    </div>
</body>
</html>
