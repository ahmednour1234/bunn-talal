<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithMapping
{
    public function collection()
    {
        return Product::with(['category', 'unit', 'branches'])->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'اسم المنتج',
            'التصنيف',
            'الوحدة',
            'سعر التكلفة',
            'سعر البيع',
            'الخصم',
            'الكمية الكلية',
            'الحالة',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->category->name ?? '-',
            $product->unit->name ?? '-',
            $product->cost_price,
            $product->selling_price,
            $product->discount,
            $product->total_quantity,
            $product->is_active ? 'نشط' : 'معطل',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->setRightToLeft(true);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '6B4F3A'],
                ],
            ],
        ];
    }
}
