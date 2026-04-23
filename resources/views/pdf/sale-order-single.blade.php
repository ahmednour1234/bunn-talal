@extends('pdf.layout')

@section('content')
{{-- Header Info --}}
<div style="margin-bottom:18px; border:1px solid #e5e5e5; border-radius:6px; padding:12px;">
    <table style="width:100%; border-collapse:collapse; margin-top:0;">
        <tr>
            <td style="width:25%; font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">رقم الطلب</td>
            <td style="border:none; padding:4px 6px; background:none; font-weight:bold;">{{ $order->order_number }}</td>
            <td style="width:25%; font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">العميل</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $order->customer?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">الفرع</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $order->branch?->name ?? '—' }}</td>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">المندوب</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $order->delegate?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">التاريخ</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $order->date?->format('Y-m-d') }}</td>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">تاريخ الاستحقاق</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $order->due_date?->format('Y-m-d') ?? '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">طريقة الدفع</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $order->payment_method_label }}</td>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">الحالة</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $order->status_label }}</td>
        </tr>
        @if($order->treasury)
        <tr>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">الخزينة</td>
            <td colspan="3" style="border:none; padding:4px 6px; background:none;">{{ $order->treasury->name }}</td>
        </tr>
        @endif
        @if($order->notes)
        <tr>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">ملاحظات</td>
            <td colspan="3" style="border:none; padding:4px 6px; background:none;">{{ $order->notes }}</td>
        </tr>
        @endif
    </table>
</div>

{{-- Items Table --}}
<div class="section-title">تفاصيل المنتجات</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>المنتج</th>
            <th>الوحدة</th>
            <th>الكمية</th>
            <th>سعر الوحدة</th>
            <th>الخصم</th>
            <th>الضريبة</th>
            <th>الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->product?->name ?? '—' }}</td>
            <td>{{ $item->unit?->symbol ?? $item->product?->unit?->symbol ?? '—' }}</td>
            <td>{{ number_format($item->quantity, 2) }}</td>
            <td class="amount">{{ number_format((float)$item->unit_price, 2) }}</td>
            <td class="amount">{{ number_format((float)$item->discount, 2) }}</td>
            <td class="amount">{{ number_format((float)$item->tax_amount, 2) }}</td>
            <td class="amount">{{ number_format((float)$item->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Totals --}}
<div style="margin-top:18px; width:280px; float:left; border:1px solid #e5e5e5; border-radius:6px; padding:10px;">
    <table style="width:100%; border-collapse:collapse; margin-top:0;">
        <tr>
            <td style="border:none; padding:4px 6px; font-weight:bold; color:#555;">الإجمالي الفرعي</td>
            <td style="border:none; padding:4px 6px; text-align:left;">{{ number_format((float)$order->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="border:none; padding:4px 6px; font-weight:bold; color:#555;">الخصم</td>
            <td style="border:none; padding:4px 6px; text-align:left; color:#b45309;">{{ number_format((float)$order->discount_amount, 2) }}</td>
        </tr>
        <tr>
            <td style="border:none; padding:4px 6px; font-weight:bold; color:#555;">الضريبة</td>
            <td style="border:none; padding:4px 6px; text-align:left;">{{ number_format((float)$order->tax_amount, 2) }}</td>
        </tr>
        <tr style="background:#eff6ff;">
            <td style="border:none; padding:6px 6px; font-weight:bold; color:#1a4480; font-size:14px;">الإجمالي النهائي</td>
            <td style="border:none; padding:6px 6px; text-align:left; font-weight:bold; color:#1a4480; font-size:14px;">{{ number_format((float)$order->total, 2) }}</td>
        </tr>
        <tr>
            <td style="border:none; padding:4px 6px; font-weight:bold; color:#15803d;">المدفوع</td>
            <td style="border:none; padding:4px 6px; text-align:left; color:#15803d;">{{ number_format((float)$order->paid_amount, 2) }}</td>
        </tr>
        <tr>
            <td style="border:none; padding:4px 6px; font-weight:bold; color:#dc2626;">المتبقي</td>
            <td style="border:none; padding:4px 6px; text-align:left; color:#dc2626; font-weight:bold;">{{ number_format((float)$order->remaining_amount, 2) }}</td>
        </tr>
    </table>
</div>

@if($order->payments->count())
<div style="clear:both; margin-top:30px;">
    <div class="section-title">سجل المدفوعات</div>
    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>المبلغ</th>
                <th>الخزينة</th>
                <th>المسؤول</th>
                <th>ملاحظات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->payments as $payment)
            <tr>
                <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                <td class="amount amount-positive">{{ number_format((float)$payment->amount, 2) }}</td>
                <td>{{ $payment->treasury?->name ?? '—' }}</td>
                <td>{{ $payment->admin?->name ?? '—' }}</td>
                <td>{{ $payment->notes ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
