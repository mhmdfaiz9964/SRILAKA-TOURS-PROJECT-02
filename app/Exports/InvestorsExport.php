<?php

namespace App\Exports;

use App\Models\Investor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvestorsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $investors;

    public function __construct($investors)
    {
        $this->investors = $investors;
    }

    public function collection()
    {
        return $this->investors;
    }

    public function headings(): array
    {
        return [
            'Name',
            'Invest Amount (LKR)',
            'Expect Profit (LKR)',
            'Paid Profit (LKR)',
            'Collect Date',
            'Refund Date',
            'Created At'
        ];
    }

    public function map($investor): array
    {
        return [
            $investor->name,
            number_format($investor->invest_amount, 2),
            number_format($investor->expect_profit, 2),
            number_format($investor->paid_profit, 2),
            $investor->collect_date ? \Carbon\Carbon::parse($investor->collect_date)->format('d/m/Y') : '-',
            $investor->refund_date ? \Carbon\Carbon::parse($investor->refund_date)->format('d/m/Y') : '-',
            $investor->created_at->format('d/m/Y')
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
