<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $purchase->invoice_number ?? $purchase->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #000;
        }

        .invoice-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .border-bottom-dark {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .mb-3 {
            margin-bottom: 15px;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 5px;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .company-name {
            color: #000080;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .invoice-title {
            color: #000080;
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="border-bottom-dark">
            <table style="border: none;">
                <tr style="border: none;">
                    <td style="width: 30%; border: none;">
                        <div
                            style="width: 80px; height: 80px; border: 2px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                            LOGO
                        </div>
                    </td>
                    <td style="width: 70%; border: none; text-align: right;">
                        <div class="company-name">{{ $globalSettings['company_name'] ?? config('app.name') }}</div>
                        <div style="font-size: 10px;">{{ $globalSettings['company_address'] ?? '' }}</div>
                        <div style="font-size: 10px;">Tel: {{ $globalSettings['company_phone'] ?? '' }}</div>
                    </td>
                </tr>
            </table>
            <table style="border: none; margin-top: 10px;">
                <tr style="border: none;">
                    <td style="border: none; width: 50%;"><strong>Ref No:
                            {{ $purchase->invoice_number ?? $purchase->id }}</strong></td>
                    <td style="border: none; width: 50%; text-align: right;"><strong>Date:
                            {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Title -->
        <div class="text-center mb-3">
            <h3 class="invoice-title">Purchase Order / GRN</h3>
        </div>

        <!-- Supplier Details -->
        <div class="mb-3">
            <div style="border-bottom: 1px dotted #999; margin-bottom: 5px;">
                <strong>Supplier:</strong> {{ $purchase->supplier->full_name }}
            </div>
            <div style="border-bottom: 1px dotted #999; margin-bottom: 5px;">
                <strong>Company:</strong> {{ $purchase->supplier->company_name ?? '-' }}
                <span style="margin-left: 20px;"><strong>Tel:</strong> {{ $purchase->supplier->contact_number }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <table class="mb-2">
            <thead>
                <tr class="text-center">
                    <th style="width: 15%;">Item Code</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 45%;">Description</th>
                    <th style="width: 15%;">Cost</th>
                    <th style="width: 15%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                    <tr>
                        <td class="text-center">{{ $item->product->code }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td>
                            {{ $item->product->name }}
                            @if($item->description)<br><small>({{ $item->description }})</small>@endif
                        </td>
                        <td class="text-end">{{ number_format($item->cost_price, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @php
                    $additionalCosts = [
                        ['label' => 'Sub Total Item', 'val' => $purchase->items->sum('total_price')],
                        ['label' => 'Transport', 'val' => $purchase->transport_cost],
                        ['label' => 'Broker', 'val' => $purchase->broker_cost],
                        ['label' => 'Loading', 'val' => $purchase->loading_cost],
                        ['label' => 'Unloading', 'val' => $purchase->unloading_cost],
                        ['label' => 'Labour Charges', 'val' => $purchase->labour_cost],
                        ['label' => 'Air Ticket', 'val' => $purchase->air_ticket_cost],
                        ['label' => 'Other Expenses', 'val' => $purchase->other_expenses],
                    ];
                @endphp
                @foreach($additionalCosts as $cost)
                    @if($cost['val'] > 0 || $cost['label'] == 'Sub Total Item')
                        <tr>
                            <td colspan="3" style="border: none;"></td>
                            <td class="text-end fw-bold">{{ $cost['label'] }}:</td>
                            <td class="text-end fw-bold">{{ number_format($cost['val'], 2) }}</td>
                        </tr>
                    @endif
                @endforeach

                <tr>
                    <td colspan="3" style="border: none;"></td>
                    <td class="text-end fw-bold" style="background-color: #f0f0f0;">Grand Total:</td>
                    <td class="text-end fw-bold" style="background-color: #f0f0f0; font-size: 14px;">
                        {{ number_format($purchase->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: none;"></td>
                    <td class="text-end fw-bold">Paid:</td>
                    <td class="text-end fw-bold">{{ number_format($purchase->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: none;"></td>
                    <td class="text-end fw-bold">Balance:</td>
                    <td class="text-end fw-bold" style="font-size: 14px;">
                        {{ number_format($purchase->total_amount - $purchase->paid_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Footer -->
        <div style="margin-top: 40px; text-align: center;">
            <table style="border: none;">
                <tr style="border: none;">
                    <td style="width: 50%; border: none; text-align: center;">
                        <div style="border-top: 1px dotted #000; padding-top: 5px; display: inline-block; width: 80%;">
                            <strong>Authorized By</strong>
                        </div>
                    </td>
                    <td style="width: 50%; border: none; text-align: center;">
                        <div style="border-top: 1px dotted #000; padding-top: 5px; display: inline-block; width: 80%;">
                            <strong>Received By</strong>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-top: 30px; text-align: right; font-size: 9px; color: #666;">
            Generated on {{ date('Y-m-d H:i:s') }}
        </div>
    </div>
</body>

</html>