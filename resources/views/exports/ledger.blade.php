<!DOCTYPE html>
<html>
<head>
    <title>{{ $type }} Ledger - {{ $entity->full_name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; color: #1a73e8; margin-bottom: 5px; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; }
        .info td { vertical-align: top; }
        .ledger-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .ledger-table th { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; text-align: left; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .ledger-table td { border: 1px solid #dee2e6; padding: 8px; font-size: 11px; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .footer { margin-top: 50px; font-size: 10px; text-align: center; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ config('app.name') }}</div>
        <div>{{ $type }} Statement of Account (Ledger)</div>
    </div>

    <div class="info">
        <table>
            <tr>
                <td style="width: 60%;">
                    <strong>{{ $type }}:</strong> {{ $entity->full_name }}<br>
                    <strong>Company:</strong> {{ $entity->company_name ?? 'N/A' }}<br>
                    <strong>{{ $type == 'Customer' ? 'Mobile' : 'Contact' }}:</strong> {{ $type == 'Customer' ? $entity->mobile_number : $entity->contact_number }}
                </td>
                <td style="text-align: right;">
                    <strong>Date:</strong> {{ date('d M Y') }}<br>
                    <strong>Statement Period:</strong> Up to {{ date('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 40%;">Description</th>
                <th style="width: 15%;" class="text-end">Debit</th>
                <th style="width: 15%;" class="text-end">Credit</th>
                <th style="width: 15%;" class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $balance = 0; @endphp
            @foreach($ledger as $item)
                @php $balance += ($item['debit'] - $item['credit']); @endphp
                <tr>
                    <td>{{ $item['date'] }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td class="text-end">{{ number_format($item['debit'], 2) }}</td>
                    <td class="text-end">{{ number_format($item['credit'], 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($balance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
             <tr style="background: #f1f3f4;">
                <td colspan="2" class="text-end fw-bold">TOTALS</td>
                <td class="text-end fw-bold">{{ number_format($ledger->sum('debit'), 2) }}</td>
                <td class="text-end fw-bold">{{ number_format($ledger->sum('credit'), 2) }}</td>
                <td class="text-end fw-bold">{{ number_format($balance, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ date('Y-m-d H:i:s') }} | Statement for {{ $entity->full_name }}
    </div>
</body>
</html>
