<?php

namespace App\Exports;

use App\Models\ThirdPartyCheque;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ThirdPartyChequesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
            'Transfer Date',
            'Cheque Date',
            'Third Party Name',
            'Bank',
            'Cheque Number',
            'Amount (LKR)',
            'Status',
            'Notes'
        ];
    }

    public function map($cheque): array
    {
        // $cheque is ThirdPartyCheque instance
        // It belongs to InCheque
        return [
            $cheque->created_at->format('d/m/Y'),
            \Carbon\Carbon::parse($cheque->inCheque->cheque_date)->format('d/m/Y'),
            $cheque->third_party_name ?? 'N/A',
            $cheque->inCheque->bank->name ?? 'N/A',
            $cheque->inCheque->cheque_number,
            number_format($cheque->inCheque->amount, 2),
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
