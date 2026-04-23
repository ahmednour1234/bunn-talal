@extends('pdf.layout')

@section('content')
{{-- Header Info --}}
<div style="margin-bottom:18px; border:1px solid #e5e5e5; border-radius:6px; padding:12px;">
    <table style="width:100%; border-collapse:collapse; margin-top:0;">
        <tr>
            <td style="width:25%; font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">رقم المرتجع</td>
            <td style="border:none; padding:4px 6px; background:none; font-weight:bold;">{{ $return->return_number }}</td>
            <td style="width:25%; font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">طلب المبيعات</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $return->order?->order_number ?? '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">العميل</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $return->customer?->name ?? '—' }}</td>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">الفرع</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $return->branch?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">التاريخ</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $return->date?->format('Y-m-d') }}</td>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">الحالة</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $return->status_label }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">المسؤول</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $return->admin?->name ?? '—' }}</td>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">الخزينة</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $return->treasury?->name ?? '—' }}</td>
        </tr>
        @if($return->notes)
        <tr>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">ملاحظات</td>
            <td colspan="3" style="border:none; padding:4px 6px; background:none;">{{ $return->notes }}</td>
        </tr>
        @endif
    </table>
</div>

{{-- Items Table --}}
<div class="section-title">تفاصيل المنتجات المرتجعة</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>المنتج</th>
            <th>الوحدة</th>
            <th>الكمية</th>
            <th>سعر الوحدة</th>
            <th>المبلغ المسترد</th>
            <th>السبب</th>
        </tr>
    </thead>
    <tbody>
        @foreach($return->items as $i => $item)
        <tr>
            <td style="text-align:center;">{{ $i + 1 }}</td>
            <td>{{ $item->product->name }}</td>
            <td style="text-align:center;">{{ $item->unit?->name ?? $item->product->unit?->name ?? '—' }}</td>
            <td style="text-align:center;">{{ number_format($item->quantity, 2) }}</td>
            <td style="text-align:center;">{{ number_format($item->unit_price, 2) }}</td>
            <td style="text-align:center; font-weight:bold; color:#166534;">{{ number_format($item->refund_amount, 2) }}</td>
            <td style="font-size:10px; color:#666;">{{ $item->reason ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Totals --}}
<div style="margin-top:20px; display:flex; justify-content:flex-end;">
    <table style="width:280px; border-collapse:collapse;">
        <tr>
            <td style="padding:6px 10px; font-weight:bold; color:#555; background:#f9fafb; border:1px solid #e5e7eb;">إجمالي المرتجع</td>
            <td style="padding:6px 10px; text-align:left; background:#f9fafb; border:1px solid #e5e7eb;">{{ number_format($return->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="padding:6px 10px; font-weight:bold; color:#166534; background:#f0fdf4; border:1px solid #bbf7d0;">المبلغ المسترد</td>
            <td style="padding:6px 10px; text-align:left; font-weight:bold; color:#166534; background:#f0fdf4; border:1px solid #bbf7d0; font-size:13px;">{{ number_format($return->refund_amount, 2) }}</td>
        </tr>
    </table>
</div>
@endsection
