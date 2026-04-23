@extends('pdf.layout')

@section('content')
<table>
    <thead>
        <tr>
            <th>رقم الفاتورة</th>
            <th>المورد</th>
            <th>الفرع</th>
            <th>التاريخ</th>
            <th>الإجمالي</th>
            <th>المدفوع</th>
            <th>المتبقي</th>
            <th>الحالة</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $invoice)
            <tr>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->supplier?->name ?? '—' }}</td>
                <td>{{ $invoice->branch?->name ?? '—' }}</td>
                <td>{{ $invoice->date?->format('Y-m-d') }}</td>
                <td class="amount">{{ number_format($invoice->total, 2) }}</td>
                <td class="amount amount-positive">{{ number_format($invoice->paid_amount, 2) }}</td>
                <td class="amount amount-negative">{{ number_format($invoice->remaining_amount, 2) }}</td>
                <td>{{ $invoice->status_label }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align:center; color:#888;">لا توجد بيانات</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
