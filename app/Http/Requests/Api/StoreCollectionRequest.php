<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'           => ['nullable', 'integer', 'exists:customers,id'],
            'notes'                 => ['nullable', 'string'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.sale_order_id' => ['nullable', 'integer', 'exists:sale_orders,id'],
            'items.*.amount'        => ['required', 'numeric', 'min:0.01'],
            'items.*.notes'         => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'              => 'يجب إضافة صنف واحد على الأقل',
            'items.*.amount.required'     => 'المبلغ مطلوب لكل صنف',
            'items.*.amount.min'          => 'المبلغ يجب أن يكون أكبر من صفر',
            'items.*.sale_order_id.exists' => 'فاتورة البيع غير موجودة',
        ];
    }
}
