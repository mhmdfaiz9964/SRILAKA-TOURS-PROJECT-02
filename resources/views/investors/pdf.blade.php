<!DOCTYPE html>
<html>
<head>
    <title>Investors Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #6366f1; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #6366f1; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #6366f1; color: white; padding: 8px; text-align: left; text-transform: uppercase; font-size: 8px; }
        td { border-bottom: 1px solid #eee; padding: 8px; }
        .text-end { text-align: right; }
        .row-even { background-color: #f9fafb; }
    </style>
</head>
<body>
    <div class="header">
        <h2>INVESTOR MANAGEMENT SYSTEM</h2>
        <p>Generated on: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th class="text-end">Invest Amount</th>
                <th class="text-end">Expect Profit</th>
                <th class="text-end">Paid Profit</th>
                <th>Collect Date</th>
                <th>Refund Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($investors as $index => $investor)
            <tr class="{{ $index % 2 == 0 ? '' : 'row-even' }}">
                <td>{{ $investor->name }}</td>
                <td class="text-end">{{ number_format($investor->invest_amount, 2) }}</td>
                <td class="text-end">{{ number_format($investor->expect_profit, 2) }}</td>
                <td class="text-end">{{ number_format($investor->paid_profit, 2) }}</td>
                <td>{{ $investor->collect_date ? \Carbon\Carbon::parse($investor->collect_date)->format('d/m/Y') : '-' }}</td>
                <td>{{ $investor->refund_date ? \Carbon\Carbon::parse($investor->refund_date)->format('d/m/Y') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right; font-size: 12px;">
        <strong>Total Investment: </strong> LKR {{ number_format($investors->sum('invest_amount'), 2) }}
    </div>
</body>
</html>
