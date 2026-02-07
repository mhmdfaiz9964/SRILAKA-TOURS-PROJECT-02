<!DOCTYPE html>
<html>
<head>
    <title>Daily Ledger History</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-end { text-align: right; }
        .success { color: green; }
        .danger { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Daily Ledger History</h2>
        <p>Generated on: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Total Income</th>
                <th>Total Expense</th>
                <th>A/c Sales</th>
                <th>Bank Deposit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td>{{ \Carbon\Carbon::parse($record->date)->format('Y-m-d') }}</td>
                <td class="text-end success">{{ number_format($record->total_income, 2) }}</td>
                <td class="text-end danger">{{ number_format($record->total_expense, 2) }}</td>
                <td class="text-end">{{ number_format($record->ac_sales, 2) }}</td>
                <td class="text-end">{{ number_format($record->bank_deposit, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
