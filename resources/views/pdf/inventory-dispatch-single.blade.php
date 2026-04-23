@extends('pdf.layout')

@section('content')
{{-- Header Info --}}
<div style="margin-bottom:18px; border:1px solid #e5e5e5; border-radius:6px; padding:12px;">
    <table style="width:100%; border-collapse:collapse; margin-top:0;">
        <tr>
            <td style="width:22%; font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">رقم الأمر</td>
            <td style="border:none; padding:4px 6px; background:none; font-weight:bold;">#{{ $dispatch->id }}</td>
            <td style="width:22%; font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">التاريخ</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $dispatch->date?->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">الفرع</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $dispatch->branch?->name ?? '—' }}</td>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">المندوب</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $dispatch->delegate?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">المسؤول</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $dispatch->admin?->name ?? '—' }}</td>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">الحالة</td>
            <td style="border:none; padding:4px 6px; background:none;">
                <span class="badge {{ match($dispatch->status) { 'dispatched','settled' => 'badge-green', 'pending' => 'badge-gray', 'partial_return','returned' => 'badge-orange', default => 'badge-gray' } }}">
                    {{ $dispatch->status_label }}
                </span>
            </td>
        </tr>
        @if($dispatch->notes)
        <tr>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">ملاحظات</td>
            <td colspan="3" style="border:none; padding:4px 6px; background:none;">{{ $dispatch->notes }}</td>
        </tr>
        @endif
    </table>
</div>

{{-- Items Table --}}
<div class="section-title">تفاصيل المنتجات المصروفة</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>المنتج</th>
            <th>الوحدة</th>
            <th>الكمية المصروفة</th>
            <th>المرتجع</th>
            <th>معه الآن</th>
            <th>سعر التكلفة</th>
            <th>سعر البيع</th>
            <th>إجمالي التكلفة</th>
            <th>المبيعات المتوقعة</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dispatch->items as $i => $item)
        @php
            $remaining = $item->quantity - ($item->returned_quantity ?? 0);
            $unitSymbol = $item->product?->unit?->symbol ?? '';
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->product?->name ?? '—' }}</td>
            <td style="text-align:center;">{{ $unitSymbol }}</td>
            <td style="text-align:center;">{{ $item->quantity }} {{ $unitSymbol }}</td>
            <td style="text-align:center; color:#b45309;">{{ $item->returned_quantity ?? 0 }} {{ $unitSymbol }}</td>
            <td style="text-align:center; color:{{ $remaining > 0 ? '#b45309' : '#166534' }}; font-weight:bold;">{{ $remaining }} {{ $unitSymbol }}</td>
            <td class="amount">{{ number_format((float)$item->cost_price, 2) }}</td>
            <td class="amount">{{ number_format((float)$item->selling_price, 2) }}</td>
            <td class="amount">{{ number_format((float)$item->cost_price * $item->quantity, 2) }}</td>
            <td class="amount">{{ number_format((float)$item->selling_price * $item->quantity, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Totals --}}
<div style="margin-top:18px; width:300px; float:left; border:1px solid #e5e5e5; border-radius:6px; padding:10px;">
    <table style="width:100%; border-collapse:collapse; margin-top:0;">
        <tr>
            <td style="border:none; padding:4px 6px; background:none; color:#555;">إجمالي الكمية المصروفة</td>
            <td style="border:none; padding:4px 6px; background:none; font-weight:bold; direction:ltr; text-align:left;">{{ $dispatch->items->sum('quantity') }}</td>
        </tr>
        <tr>
            <td style="border:none; padding:4px 6px; background:none; color:#b45309;">إجمالي المرتجع</td>
            <td style="border:none; padding:4px 6px; background:none; font-weight:bold; color:#b45309; direction:ltr; text-align:left;">{{ $dispatch->items->sum('returned_quantity') }}</td>
        </tr>
        <tr>
            <td style="border:none; padding:4px 6px; background:none; color:#555;">إجمالي التكلفة</td>
            <td style="border:none; padding:4px 6px; background:none; font-weight:bold; direction:ltr; text-align:left;">{{ number_format((float)$dispatch->total_cost, 2) }}</td>
        </tr>
        <tr style="background:#f0fdf4;">
            <td style="border:none; padding:6px; color:#166534; font-weight:bold; font-size:12px;">المبيعات المتوقعة</td>
            <td style="border:none; padding:6px; font-weight:bold; color:#166534; font-size:13px; direction:ltr; text-align:left;">{{ number_format((float)$dispatch->expected_sales, 2) }}</td>
        </tr>
    </table>
</div>
<div style="clear:both;"></div>
@endsection
