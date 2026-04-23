@extends('pdf.layout')

@section('content')
{{-- Header Info --}}
<div style="margin-bottom:18px; border:1px solid #e5e5e5; border-radius:6px; padding:12px;">
    <table style="width:100%; border-collapse:collapse; margin-top:0;">
        <tr>
            <td style="width:25%; font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">رقم العرض</td>
            <td style="border:none; padding:4px 6px; background:none; font-weight:bold;">{{ $quotation->quotation_number }}</td>
            <td style="width:25%; font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">العميل</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $quotation->customer?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">الفرع</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $quotation->branch?->name ?? '—' }}</td>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">المندوب</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $quotation->delegate?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">تاريخ العرض</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $quotation->date?->format('Y-m-d') }}</td>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">صالح حتى</td>
            <td style="border:none; padding:4px 6px; background:none; font-weight:bold; color:#dc2626;">{{ $quotation->expiry_date?->format('Y-m-d') ?? '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">الحالة</td>
            <td colspan="3" style="border:none; padding:4px 6px; background:none;">{{ $quotation->status_label }}</td>
        </tr>
        @if($quotation->notes)
        <tr>
            <td style="font-weight:bold; color:#1a4480; border:none; padding:4px 6px; background:none;">ملاحظات</td>
            <td colspan="3" style="border:none; padding:4px 6px; background:none;">{{ $quotation->notes }}</td>
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
        @foreach($quotation->items as $i => $item)
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
            <td style="border:none; padding:4px 6px; font-weight:bold; color:#555;">المجموع الفرعي</td>
            <td style="border:none; padding:4px 6px; text-align:left;">{{ number_format((float)$quotation->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="border:none; padding:4px 6px; font-weight:bold; color:#555;">الخصم</td>
            <td style="border:none; padding:4px 6px; text-align:left; color:#b45309;">{{ number_format((float)$quotation->discount_amount, 2) }}</td>
        </tr>
        <tr style="background:#eff6ff;">
            <td style="border:none; padding:6px 6px; font-weight:bold; color:#1a4480; font-size:14px;">الإجمالي النهائي</td>
            <td style="border:none; padding:6px 6px; text-align:left; font-weight:bold; color:#1a4480; font-size:14px;">{{ number_format((float)$quotation->total, 2) }}</td>
        </tr>
    </table>
</div>
@endsection
