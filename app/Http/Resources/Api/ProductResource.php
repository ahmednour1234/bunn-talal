<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        // Stock in the product's base unit — set by the controller as a virtual attribute
        $stockInBaseUnit = (float) ($this->resource->available_stock ?? 0);

        $unit           = $this->resource->unit;
        $availableUnits = $this->buildAvailableUnits($unit, $stockInBaseUnit);

        $discountAmount = $this->net_price !== null
            ? round((float) $this->selling_price - (float) $this->net_price, 2)
            : 0;

        $taxAmount = $this->tax && $this->net_price
            ? ($this->tax->type === 'percentage'
                ? round((float) $this->net_price * $this->tax->rate / 100, 2)
                : round((float) $this->tax->rate, 2))
            : 0;

        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'image'              => $this->image ? asset('storage/' . $this->image) : null,
            'category'           => $this->whenLoaded('category', fn () =>
                $this->category ? ['id' => $this->category->id, 'name' => $this->category->name] : null
            ),
            'selling_price'      => $this->selling_price,
            'discount'           => $this->discount,
            'discount_type'      => $this->discount_type,
            'discount_amount'    => $discountAmount,
            'net_price'          => $this->net_price,
            'tax_amount'         => $taxAmount,
            'final_price'        => $this->final_price,
            'unit'               => $unit
                ? ['id' => $unit->id, 'name' => $unit->name, 'symbol' => $unit->symbol]
                : null,
            'tax'                => $this->tax ? [
                'id'   => $this->tax->id,
                'name' => $this->tax->name,
                'rate' => $this->tax->rate,
                'type' => $this->tax->type,
            ] : null,
            'available_quantity' => $stockInBaseUnit,
            'available_units'    => $availableUnits,
        ];
    }

    protected function buildAvailableUnits($unit, float $stockInBaseUnit): array
    {
        if (!$unit) {
            return [];
        }

        if ($unit->isBaseUnit()) {
            $familyUnits = collect([$unit])->merge($unit->derivedUnits);
        } else {
            $base        = $unit->baseUnit;
            $allUnits    = $base
                ? collect([$base])->merge($base->derivedUnits)
                : collect([$unit]);
            $familyUnits = collect([$unit])->merge(
                $allUnits->filter(fn ($u) => $u->id !== $unit->id)->values()
            );
        }

        $productFactor = (float) $unit->conversion_factor;
        $baseUnit      = $unit->isBaseUnit() ? $unit : ($unit->baseUnit ?? $unit);
        $product       = $this->resource;

        return $familyUnits->map(function ($u) use ($product, $productFactor, $stockInBaseUnit, $baseUnit) {
            $factor            = (float) $u->conversion_factor / $productFactor;
            $sellPrice         = round((float) $product->selling_price * $factor, 2);
            $stockInFamilyBase = $stockInBaseUnit * $productFactor;

            $stockInThisUnit = $u->conversion_factor > 0
                ? $stockInFamilyBase / $u->conversion_factor
                : 0;

            $remainderInBase = $u->conversion_factor > 0
                ? fmod($stockInFamilyBase, $u->conversion_factor)
                : 0;

            $discountAmount = 0;
            if ($product->discount > 0) {
                if ($product->discount_type === 'percentage') {
                    $discountAmount = round($sellPrice * $product->discount / 100, 2);
                } else {
                    $discountAmount = round((float) $product->discount * $factor, 2);
                }
            }
            $netPrice = round(max(0, $sellPrice - $discountAmount), 2);

            $taxAmount = 0;
            if ($product->tax) {
                $taxAmount = $product->tax->type === 'percentage'
                    ? round($netPrice * $product->tax->rate / 100, 2)
                    : round((float) $product->tax->rate * $factor, 2);
            }

            $discountRate = $product->discount_type === 'percentage'
                ? (float) $product->discount
                : ($sellPrice > 0 ? round($discountAmount / $sellPrice * 100, 2) : 0);

            return [
                'id'                   => $u->id,
                'name'                 => $u->name,
                'symbol'               => $u->symbol,
                'price'                => $sellPrice,
                'discount_type'        => $product->discount_type,
                'discount_rate'        => $discountRate,
                'discount_amount'      => $discountAmount,
                'price_after_discount' => $netPrice,
                'tax_name'             => $product->tax?->name,
                'tax_rate'             => $product->tax?->rate,
                'tax_type'             => $product->tax?->type,
                'tax_amount'           => $taxAmount,
                'price_with_tax'       => round($netPrice + $taxAmount, 2),
                'available_quantity'   => (float) $stockInThisUnit,
                'remainder_quantity'   => (float) $remainderInBase,
                'remainder_unit'       => $u->id !== $baseUnit->id
                    ? ['id' => $baseUnit->id, 'name' => $baseUnit->name, 'symbol' => $baseUnit->symbol]
                    : null,
            ];
        })->values()->toArray();
    }
}
