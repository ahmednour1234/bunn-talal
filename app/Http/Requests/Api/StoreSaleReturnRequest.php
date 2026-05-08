<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_order_id'              => ['required', 'integer', 'exists:sale_orders,id'],
            'notes'                      => ['nullable', 'string'],
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.product_id'         => ['required', 'integer', 'exists:products,id'],
            'items.*.unit_id'            => ['nullable', 'integer', 'exists:units,id'],
            'items.*.quantity'           => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price'         => ['required', 'numeric', 'min:0'],
            'items.*.reason'             => ['nullable', 'string'],
            'items.*.sale_order_item_id' => ['nullable', 'integer', 'exists:sale_order_items,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'sale_order_id.required'      => 'رقم فاتورة البيع مطلوب',
            'sale_order_id.exists'        => 'فاتورة البيع غير موجودة',
            'items.required'              => 'يجب إضافة منتج واحد على الأقل',
            'items.*.product_id.required' => 'كل صنف يجب أن يحتوي على منتج',
            'items.*.product_id.exists'   => 'أحد المنتجات غير موجود',
            'items.*.quantity.required'   => 'الكمية مطلوبة لكل صنف',
            'items.*.quantity.min'        => 'الكمية يجب أن تكون أكبر من صفر',
            'items.*.unit_price.required' => 'السعر مطلوب لكل صنف',
        ];
    }
}
