<?php

namespace App\Exports;

use App\Models\Treasury;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TreasuriesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Treasury::orderBy('id')->get();
    }

    public function headings(): array
    {
        return ['#', 'اسم الخزنة', 'الرصيد', 'الحالة', 'تاريخ الإنشاء'];
    }

    public function map($treasury): array
    {
        return [
            $treasury->id,
            $treasury->name,
            number_format($treasury->balance, 2),
            $treasury->is_active ? 'نشط' : 'معطل',
            $treasury->created_at->format('Y-m-d'),
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
