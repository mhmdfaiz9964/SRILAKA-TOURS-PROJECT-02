<?php

namespace App\Exports;

use App\Models\InCheque;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InChequesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
            'Received Date',
            'Cheque Date',
            'Payer Name',
            'Bank',
            'Cheque Number',
            'Amount (LKR)',
            'Status',
            'Notes'
        ];
    }

    public function map($cheque): array
    {
        return [
            $cheque->created_at->format('d/m/Y'),
            \Carbon\Carbon::parse($cheque->cheque_date)->format('d/m/Y'),
            $cheque->payer_name,
            $cheque->bank->name ?? 'N/A',
            $cheque->cheque_number,
            number_format($cheque->amount, 2),
            ucwords(str_replace('_', ' ', $cheque->status)),
            $cheque->notes ?? '-'
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
