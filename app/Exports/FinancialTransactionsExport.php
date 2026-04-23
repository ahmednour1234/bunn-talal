<?php

namespace App\Exports;

use App\Models\FinancialTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialTransactionsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected string $search = '',
        protected string $typeFilter = '',
        protected string $accountFilter = '',
    ) {}

    public function query()
    {
        $query = FinancialTransaction::query()->with(['account', 'treasury', 'admin']);

        if ($this->search) {
            $query->where('description', 'like', "%{$this->search}%");
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->accountFilter) {
            $query->where('account_id', $this->accountFilter);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return ['#', 'النوع', 'الحساب', 'الخزنة', 'المبلغ', 'الوصف', 'التاريخ', 'بواسطة'];
    }

    public function map($tx): array
    {
        return [
            $tx->id,
            $tx->type === 'expense' ? 'مصروف' : 'إيراد',
            $tx->account?->name ?? '—',
            $tx->treasury?->name ?? '—',
            number_format($tx->amount, 2),
            $tx->description ?? '—',
            $tx->date->format('Y-m-d'),
            $tx->admin?->name ?? '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->setRightToLeft(true);

        return [
            1 => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '6B4F3A'],
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }
}
