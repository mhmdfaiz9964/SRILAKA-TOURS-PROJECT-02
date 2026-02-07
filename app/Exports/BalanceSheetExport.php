<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BalanceSheetExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $entries;

    public function __construct($entries)
    {
        $this->entries = $entries;
    }

    public function collection()
    {
        return $this->entries;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Category',
            'Name',
            'Amount (LKR)'
        ];
    }

    public function map($entry): array
    {
        return [
            $entry->date->format('d/m/Y'),
            ucwords($entry->category),
            $entry->name,
            number_format($entry->amount, 2)
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
