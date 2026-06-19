<?php

namespace App\Exports;

use App\Models\Customer;
use App\Support\CustomerDebt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected array $debts = [];

    public function __construct(
        protected string $search = '',
        protected string $classificationFilter = '',
        protected string $areaFilter = '',
        protected string $branchFilter = '',
    ) {
    }

    public function collection()
    {
        $query = Customer::query()->with(['area', 'branch']);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($this->classificationFilter !== '') {
            $query->where('classification', $this->classificationFilter);
        }

        if ($this->areaFilter !== '') {
            $query->where('area_id', $this->areaFilter);
        }

        if ($this->branchFilter !== '') {
            $query->where('branch_id', $this->branchFilter);
        }

        $customers = $query->latest()->get();

        // احسب المديونية لكل العملاء دفعة واحدة
        $this->debts = CustomerDebt::forCustomers($customers);

        return $customers;
    }

    public function headings(): array
    {
        return [
            '#',
            'العميل',
            'الهاتف',
            'البريد الإلكتروني',
            'المنطقة',
            'الفرع',
            'التصنيف',
            'مديونية العميل',
            'الحالة',
        ];
    }

    public function map($customer): array
    {
        $labels = Customer::classificationLabels();
        $debt = $this->debts[$customer->id] ?? 0;

        return [
            $customer->id,
            $customer->name,
            $customer->phone ?? '—',
            $customer->email ?? '—',
            $customer->area?->name ?? '—',
            $customer->branch?->name ?? '—',
            $labels[$customer->classification] ?? $customer->classification,
            number_format((float) $debt, 2),
            $customer->is_active ? 'نشط' : 'معطل',
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
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            ],
        ];
    }
}
