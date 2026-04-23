<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $category = Category::where('name', $row['اسم التصنيف'] ?? $row['اسم_التصنيف'] ?? null)->first();
        $unit = Unit::where('name', $row['اسم وحدة القياس'] ?? $row['اسم_وحدة_القياس'] ?? null)->first();

        if (!$category || !$unit) {
            return null;
        }

        return new Product([
            'name' => $row['اسم المنتج'] ?? $row['اسم_المنتج'],
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'cost_price' => $row['سعر التكلفة'] ?? $row['سعر_التكلفة'] ?? 0,
            'selling_price' => $row['سعر البيع'] ?? $row['سعر_البيع'] ?? 0,
            'discount' => $row['الخصم'] ?? 0,
            'is_active' => true,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.اسم المنتج' => 'required_without:*.اسم_المنتج|string|max:255',
        ];
    }
}
