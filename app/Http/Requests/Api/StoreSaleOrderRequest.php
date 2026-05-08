<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'           => ['required', 'integer', 'exists:customers,id'],
            'payment_method'        => ['required', 'string', 'in:cash,credit,partial'],
            'discount_amount'       => ['nullable', 'numeric', 'min:0'],
            'discount_type'         => ['nullable', 'string', 'in:fixed,percentage'],
            'due_date'              => ['nullable', 'date'],
            'notes'                 => ['nullable', 'string'],
            'paid_amount'           => ['nullable', 'numeric', 'min:0'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'integer', 'exists:products,id'],
            'items.*.unit_id'       => ['nullable', 'integer', 'exists:units,id'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0'],
            'items.*.discount'      => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'items.*.tax_amount'    => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required'        => 'العميل مطلوب',
            'customer_id.exists'          => 'العميل غير موجود',
            'payment_method.required'     => 'طريقة الدفع مطلوبة',
            'payment_method.in'           => 'طريقة الدفع يجب أن تكون: cash أو credit أو partial',
            'items.required'              => 'يجب إضافة منتج واحد على الأقل',
            'items.*.product_id.required' => 'كل صنف يجب أن يحتوي على منتج',
            'items.*.product_id.exists'   => 'أحد المنتجات غير موجود',
            'items.*.quantity.required'   => 'الكمية مطلوبة لكل صنف',
            'items.*.quantity.min'        => 'الكمية يجب أن تكون أكبر من صفر',
            'items.*.unit_price.required' => 'السعر مطلوب لكل صنف',
        ];
    }
}
