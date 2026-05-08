<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHrLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'       => ['required', Rule::in(['annual', 'sick', 'emergency', 'unpaid'])],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'gte:start_date'],
            'reason'     => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'       => 'نوع الإجازة مطلوب',
            'type.in'             => 'نوع الإجازة يجب أن يكون: annual أو sick أو emergency أو unpaid',
            'start_date.required' => 'تاريخ البداية مطلوب',
            'start_date.date'     => 'تاريخ البداية غير صحيح',
            'end_date.required'   => 'تاريخ النهاية مطلوب',
            'end_date.date'       => 'تاريخ النهاية غير صحيح',
            'end_date.gte'        => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية',
        ];
    }
}
