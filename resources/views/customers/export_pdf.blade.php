<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customers List</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 20px;
            color: #000080;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            padding: 8px 5px;
            text-align: left;
            font-size: 10px;
        }
        td {
            padding: 6px 5px;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Customers List</h2>
        <p>Generated on {{ date('d M Y, h:i A') }}</p>
        <p>Total Customers: {{ count($customers) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 20%;">Full Name</th>
                <th style="width: 15%;">Company</th>
                <th style="width: 12%;">Mobile</th>
                <th class="text-right" style="width: 12%;">Credit Limit</th>
                <th class="text-right" style="width: 12%;">Outstanding</th>
                <th class="text-center" style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td class="text-center">#{{ $customer->id }}</td>
                <td>{{ $customer->full_name }}</td>
                <td>{{ $customer->company_name ?? '-' }}</td>
                <td>{{ $customer->mobile_number }}</td>
                <td class="text-right">{{ number_format($customer->credit_limit, 2) }}</td>
                <td class="text-right">{{ number_format($customer->outstanding, 2) }}</td>
                <td class="text-center">
                    @if($customer->status)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-danger">Inactive</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td colspan="4" class="text-right">Total Outstanding:</td>
                <td colspan="3" class="text-right">LKR {{ number_format($customers->sum('outstanding'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>{{ config('app.name') }} - Customer Management System</p>
    </div>
</body>
</html>
