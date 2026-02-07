<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $expenses;

    public function __construct($expenses)
    {
        $this->expenses = $expenses;
    }

    public function collection()
    {
        return $this->expenses;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Reason',
            'Category',
            'Amount (LKR)',
            'Paid By',
            'Payment Method',
            'Notes'
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->expense_date->format('d/m/Y'),
            $expense->reason,
            $expense->category->name ?? 'N/A',
            number_format($expense->amount, 2),
            $expense->paid_by,
            ucwords(str_replace('_', ' ', $expense->payment_method)),
            $expense->notes ?? '-'
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
