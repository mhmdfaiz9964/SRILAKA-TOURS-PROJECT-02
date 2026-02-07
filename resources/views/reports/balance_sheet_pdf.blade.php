<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Sheet Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #6366f1; margin: 0; font-size: 24px; }
        .header p { color: #6b7280; margin: 5px 0; }
        .section-title { font-size: 14px; font-weight: bold; color: #1f2937; margin-top: 20px; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 2px solid #e5e7eb; }
        .grid-container { display: table; width: 100%; table-layout: fixed; border-spacing: 10px; }
        .grid-item { display: table-cell; vertical-align: top; width: 50%; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #f3f4f6; color: #374151; padding: 8px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .total-row { font-weight: bold; background: #f9fafb; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #9ca3af; padding: 10px 0; border-top: 1px solid #e5e7eb; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .bg-indigo { background: #e0e7ff; color: #3730a3; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Balance Sheet</h1>
        <p>As of {{ $date->format('d M, Y') }}</p>
    </div>

    <div class="section-title">Assets</div>
    <table>
        <thead>
            <tr>
                <th>Account / Asset</th>
                <th style="text-align: right;">Amount (LKR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $asset)
            <tr>
                <td>{{ $asset->name }}</td>
                <td style="text-align: right;">{{ number_format($asset->amount, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>Total Assets</td>
                <td style="text-align: right; color: #166534;">{{ number_format($totalAssets, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="grid-container">
        <div class="grid-item">
            <div class="section-title">Liabilities</div>
            <table>
                <thead>
                    <tr>
                        <th>Account / Liability</th>
                        <th style="text-align: right;">Amount (LKR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($liabilities as $liability)
                    <tr>
                        <td>{{ $liability->name }}</td>
                        <td style="text-align: right;">{{ number_format($liability->amount, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>Total Liabilities</td>
                        <td style="text-align: right; color: #991b1b;">{{ number_format($totalLiabilities, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="grid-item">
            <div class="section-title">Equity</div>
            <table>
                <thead>
                    <tr>
                        <th>Account / Equity</th>
                        <th style="text-align: right;">Amount (LKR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equity as $eq)
                    <tr>
                        <td>{{ $eq->name }}</td>
                        <td style="text-align: right;">{{ number_format($eq->amount, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>Total Equity</td>
                        <td style="text-align: right; color: #1e40af;">{{ number_format($totalEquity, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center; padding: 20px; background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;">
        <h3 style="margin: 0; color: #166534; font-size: 14px;">Total Liabilities & Equity</h3>
        <p style="margin: 5px 0 0 0; font-size: 20px; font-weight: bold; color: #14532d;">LKR {{ number_format($totalLiabilitiesAndEquity, 2) }}</p>
    </div>

    <div class="footer">
        Generated on {{ now()->format('d M, Y h:i A') }} | Sri Lanka Tours
    </div>
</body>
</html>
