<?php

namespace App\Exports;

use App\Models\Cheque;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ChequesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $cheques;

    public function __construct($cheques)
    {
        $this->cheques = $cheques;
    }

    public function collection()
    {
        return $this->cheques;
    }

    public function headings(): array
    {
        return [
            'Cheq Date',
            'Status',
            'Client Name',
            'Cheque Number',
            'Bank',
            'Amount (LKR)',
            'Balance (LKR)',
            '3rd Party Status',
            '3rd Party Name',
            'CHQ RTN Note'
        ];
    }

    public function map($cheque): array
    {
        return [
            \Carbon\Carbon::parse($cheque->cheque_date)->format('d/m/Y'),
            ucwords($cheque->payment_status),
            $cheque->payer_name,
            $cheque->cheque_number,
            $cheque->bank->name ?? 'N/A',
            number_format($cheque->amount, 2),
            number_format($cheque->amount - ($cheque->payments_sum_amount ?? 0), 2),
            ucwords($cheque->third_party_payment_status ?? '-'),
            $cheque->payee_name ?? '-',
            $cheque->return_reason ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '6366F1']
                ]
            ],
        ];
    }
}
