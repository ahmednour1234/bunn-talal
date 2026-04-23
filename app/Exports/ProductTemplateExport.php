<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['بن يمني فاخر', 'مشروبات', 'كيلو', '500.00', '750.00', '50.00'],
            ['شاي أخضر', 'مشروبات', 'علبة', '200.00', '350.00', '0.00'],
        ];
    }

    public function headings(): array
    {
        return [
            'اسم المنتج',
            'اسم التصنيف',
            'اسم وحدة القياس',
            'سعر التكلفة',
            'سعر البيع',
            'الخصم',
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
