<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expenses Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #6366f1; margin: 0; font-size: 24px; }
        .header p { color: #6b7280; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #6366f1; color: white; padding: 10px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #9ca3af; padding: 10px 0; border-top: 1px solid #e5e7eb; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; background: #e5e7eb; color: #374151; }
        .total-box { margin-top: 20px; text-align: right; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Expenses Report</h1>
        <p>Generated on {{ now()->format('d M, Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reason</th>
                <th>Category</th>
                <th>Paid By</th>
                <th>Method</th>
                <th style="text-align: right;">Amount (LKR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                <td>{{ $expense->reason }}</td>
                <td>
                    @if($expense->category)
                        <span class="badge">{{ $expense->category->name }}</span>
                    @else
                        -
                    @endif
                </td>
                <td>{{ $expense->paid_by }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $expense->payment_method)) }}</td>
                <td style="text-align: right; font-weight: bold; color: #991b1b;">
                    {{ number_format($expense->amount, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        Total Expenses: <span style="color: #991b1b;">LKR {{ number_format($totalAmount, 2) }}</span>
    </div>

    <div class="footer">
        Generated on {{ now()->format('d M, Y h:i A') }} | Sri Lanka Tours
    </div>
</body>
</html>
