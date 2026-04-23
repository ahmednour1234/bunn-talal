<?php

namespace App\Exports;

use App\Models\TreasuryTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TreasuryTransactionsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected string $search = '',
        protected string $treasuryFilter = '',
        protected string $typeFilter = '',
    ) {}

    public function query()
    {
        $query = TreasuryTransaction::query()->with(['treasury', 'admin']);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        if ($this->treasuryFilter) {
            $query->where('treasury_id', $this->treasuryFilter);
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return ['#', 'الخزنة', 'النوع', 'المبلغ', 'الوصف', 'التاريخ', 'رقم المرجع', 'بواسطة'];
    }

    public function map($tx): array
    {
        return [
            $tx->id,
            $tx->treasury?->name ?? '—',
            $tx->type === 'deposit' ? 'إيداع' : 'سحب',
            number_format($tx->amount, 2),
            $tx->description ?? '—',
            $tx->date->format('Y-m-d'),
            $tx->reference_number ?? '—',
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
