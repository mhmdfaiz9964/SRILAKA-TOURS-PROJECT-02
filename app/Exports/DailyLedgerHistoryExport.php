<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DailyLedgerHistoryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $records;

    public function __construct($records)
    {
        $records->transform(function ($item) {
            $item->date = \Carbon\Carbon::parse($item->date);
            return $item;
        });
        $this->records = $records;
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Total Income (LKR)',
            'Total Expense (LKR)',
            'A/c Sales (LKR)',
            'Bank Deposit (LKR)',
        ];
    }

    public function map($record): array
    {
        return [
            $record->date->format('Y-m-d'),
            number_format($record->total_income, 2),
            number_format($record->total_expense, 2),
            number_format($record->ac_sales, 2),
            number_format($record->bank_deposit, 2),
        ];
    }
}
