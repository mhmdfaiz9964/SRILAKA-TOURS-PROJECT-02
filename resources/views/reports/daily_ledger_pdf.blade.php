<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Ledger Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #6366f1; margin: 0; font-size: 24px; }
        .header p { color: #6b7280; margin: 5px 0; }
        .summary-cards { display: table; width: 100%; margin-bottom: 20px; }
        .card { display: table-cell; width: 32%; padding: 10px; background: #f9fafb; border-radius: 8px; margin-right: 1%; text-align: center; }
        .card.income { background: #f0fdf4; color: #166534; }
        .card.expense { background: #fef2f2; color: #991b1b; }
        .card.balance { background: #eff6ff; color: #1e40af; }
        .card-title { font-size: 10px; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; opacity: 0.8; }
        .card-amount { font-size: 16px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #6366f1; color: white; padding: 10px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; display: inline-block; }
        .badge-income { background: #dcfce7; color: #166534; }
        .badge-expense { background: #fee2e2; color: #991b1b; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #9ca3af; padding: 10px 0; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Ledger Report</h1>
        <p>Date: {{ $date->format('d M, Y') }}</p>
    </div>

    <div class="summary-cards">
        <div class="card income">
            <div class="card-title">Total Income</div>
            <div class="card-amount">+ LKR {{ number_format($totalIncome, 2) }}</div>
        </div>
        <div class="card expense">
            <div class="card-title">Total Expenses</div>
            <div class="card-amount">- LKR {{ number_format($totalExpenses, 2) }}</div>
        </div>
        <div class="card balance">
            <div class="card-title">Closing Balance</div>
            <div class="card-amount">LKR {{ number_format($closingBalance, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Type</th>
                <th style="text-align: right;">Amount (LKR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $entry)
            <tr>
                <td>{{ $entry->date->format('d/m/Y') }}</td>
                <td>{{ $entry->description }}</td>
                <td>
                    <span class="badge {{ $entry->type == 'income' ? 'badge-income' : 'badge-expense' }}">
                        {{ ucfirst($entry->type) }}
                    </span>
                </td>
                <td style="text-align: right; font-weight: bold; color: {{ $entry->type == 'income' ? '#166534' : '#991b1b' }}">
                    {{ $entry->type == 'income' ? '+' : '-' }} {{ number_format($entry->amount, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d M, Y h:i A') }} | Sri Lanka Tours
    </div>
</body>
</html>
