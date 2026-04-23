@extends('pdf.layout')

@section('content')
<table>
    <thead>
        <tr>
            <th>رقم المرتجع</th>
            <th>رقم الفاتورة</th>
            <th>المورد</th>
            <th>الفرع</th>
            <th>التاريخ</th>
            <th>الإجمالي</th>
            <th>الخسائر</th>
            <th>المسترد</th>
            <th>الحالة</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $return)
            <tr>
                <td>{{ $return->return_number }}</td>
                <td>{{ $return->invoice?->invoice_number ?? '—' }}</td>
                <td>{{ $return->supplier?->name ?? '—' }}</td>
                <td>{{ $return->branch?->name ?? '—' }}</td>
                <td>{{ $return->date?->format('Y-m-d') }}</td>
                <td class="amount">{{ number_format($return->subtotal, 2) }}</td>
                <td class="amount amount-negative">{{ number_format($return->loss_amount, 2) }}</td>
                <td class="amount amount-positive">{{ number_format($return->refund_amount, 2) }}</td>
                <td>{{ $return->status_label }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center; color:#888;">لا توجد بيانات</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
