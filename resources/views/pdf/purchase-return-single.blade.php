@extends('pdf.layout')

@section('content')
{{-- Header Info --}}
<div style="margin-bottom:18px; border:1px solid #e5e5e5; border-radius:6px; padding:12px;">
    <table style="width:100%; border-collapse:collapse; margin-top:0;">
        <tr>
            <td style="width:25%; font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">رقم المرتجع</td>
            <td style="border:none; padding:4px 6px; background:none; font-weight:bold;">{{ $return->return_number }}</td>
            <td style="width:25%; font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">الفاتورة المرتبطة</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $return->invoice?->invoice_number ?? '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">المورد</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $return->supplier?->name ?? '—' }}</td>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">الفرع</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $return->branch?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">التاريخ</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $return->date?->format('Y-m-d') }}</td>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">الحالة</td>
            <td style="border:none; padding:4px 6px; background:none;">
                <span class="badge {{ match($return->status) { 'confirmed','refunded' => 'badge-green', 'cancelled' => 'badge-red', default => 'badge-gray' } }}">
                    {{ $return->status_label }}
                </span>
            </td>
        </tr>
        @if($return->treasury)
        <tr>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">الخزينة</td>
            <td colspan="3" style="border:none; padding:4px 6px; background:none;">{{ $return->treasury->name }}</td>
        </tr>
        @endif
        @if($return->notes)
        <tr>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">ملاحظات</td>
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
            <th>الكمية</th>
            <th>الوحدة</th>
            <th>سعر الوحدة</th>
            <th>الإجمالي</th>
            <th>مبلغ الخسارة</th>
            <th>المسترد</th>
            <th>السبب</th>
        </tr>
    </thead>
    <tbody>
        @foreach($return->items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->product?->name ?? '—' }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->unit?->symbol ?? '—' }}</td>
            <td class="amount">{{ number_format((float)$item->unit_price, 2) }}</td>
            <td class="amount">{{ number_format($item->quantity * (float)$item->unit_price, 2) }}</td>
            <td class="amount amount-negative">{{ number_format((float)$item->loss_amount, 2) }}</td>
            <td class="amount amount-positive">{{ number_format($item->quantity * (float)$item->unit_price - (float)$item->loss_amount, 2) }}</td>
            <td>{{ $item->reason ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Totals --}}
<div style="margin-top:18px; width:280px; float:left; border:1px solid #e5e5e5; border-radius:6px; padding:10px;">
    <table style="width:100%; border-collapse:collapse; margin-top:0;">
        <tr>
            <td style="border:none; padding:4px 6px; background:none; color:#555;">إجمالي القيمة</td>
            <td style="border:none; padding:4px 6px; background:none; font-weight:bold; direction:ltr; text-align:left;">{{ number_format((float)$return->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="border:none; padding:4px 6px; background:none; color:#991b1b;">إجمالي الخسائر</td>
            <td style="border:none; padding:4px 6px; background:none; font-weight:bold; color:#991b1b; direction:ltr; text-align:left;">{{ number_format((float)$return->loss_amount, 2) }}</td>
        </tr>
        <tr style="background:#f0fdf4;">
            <td style="border:none; padding:6px; color:#166534; font-weight:bold; font-size:12px;">إجمالي المسترد</td>
            <td style="border:none; padding:6px; font-weight:bold; color:#166534; font-size:13px; direction:ltr; text-align:left;">{{ number_format((float)$return->refund_amount, 2) }}</td>
        </tr>
    </table>
</div>
<div style="clear:both;"></div>
@endsection
