@extends('pdf.layout')

@section('content')
{{-- Header Info --}}
<div style="margin-bottom:18px; border:1px solid #e5e5e5; border-radius:6px; padding:12px;">
    <table style="width:100%; border-collapse:collapse; margin-top:0;">
        <tr>
            <td style="width:22%; font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">رقم الطلب</td>
            <td style="border:none; padding:4px 6px; background:none; font-weight:bold;">{{ $depreciation->depreciation_number }}</td>
            <td style="width:22%; font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">التاريخ</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $depreciation->date?->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">الفرع</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $depreciation->branch?->name ?? '—' }}</td>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">أنشأ بواسطة</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $depreciation->admin?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">الحالة</td>
            <td style="border:none; padding:4px 6px; background:none;">
                @php
                    $badgeClass = match($depreciation->status) {
                        'approved' => 'badge-green',
                        'rejected' => 'badge-red',
                        default    => 'badge-gray',
                    };
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $depreciation->status_label }}</span>
            </td>
            @if($depreciation->approvedByAdmin)
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">{{ $depreciation->status === 'approved' ? 'وافق بواسطة' : 'رفض بواسطة' }}</td>
            <td style="border:none; padding:4px 6px; background:none;">{{ $depreciation->approvedByAdmin->name }}</td>
            @else
            <td colspan="2" style="border:none; padding:4px 6px; background:none;"></td>
            @endif
        </tr>
        <tr>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">سبب الإهلاك</td>
            <td colspan="3" style="border:none; padding:4px 6px; background:none;">{{ $depreciation->reason }}</td>
        </tr>
        @if($depreciation->notes)
        <tr>
            <td style="font-weight:bold; color:#6B4F3A; border:none; padding:4px 6px; background:none;">ملاحظات</td>
            <td colspan="3" style="border:none; padding:4px 6px; background:none;">{{ $depreciation->notes }}</td>
        </tr>
        @endif
    </table>
</div>

{{-- Items Table --}}
<div class="section-title">تفاصيل المنتجات المهلكة</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>المنتج</th>
            <th>الوحدة</th>
            <th>الكمية</th>
            <th>تكلفة الوحدة</th>
            <th>الخسارة</th>
        </tr>
    </thead>
    <tbody>
        @foreach($depreciation->items as $i => $item)
        @php
            $unitName = $item->unit?->name ?? $item->product?->unit?->name ?? '—';
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->product?->name ?? '—' }}</td>
            <td style="text-align:center;">{{ $unitName }}</td>
            <td style="text-align:center;">{{ $item->quantity }}</td>
            <td class="amount">{{ number_format((float)$item->cost_price, 2) }}</td>
            <td class="amount" style="color:#991b1b; font-weight:bold;">{{ number_format((float)$item->total_loss, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Total Loss --}}
<div style="margin-top:18px; width:260px; float:left; border:1px solid #fee2e2; border-radius:6px; padding:10px; background:#fff5f5;">
    <table style="width:100%; border-collapse:collapse; margin-top:0;">
        <tr>
            <td style="border:none; padding:6px; color:#555;">عدد المنتجات</td>
            <td style="border:none; padding:6px; font-weight:bold; direction:ltr; text-align:left;">{{ $depreciation->items->count() }}</td>
        </tr>
        <tr style="background:#fee2e2;">
            <td style="border:none; padding:6px; color:#991b1b; font-weight:bold; font-size:12px;">إجمالي الخسارة</td>
            <td style="border:none; padding:6px; font-weight:bold; color:#991b1b; font-size:13px; direction:ltr; text-align:left;">{{ number_format((float)$depreciation->total_loss, 2) }}</td>
        </tr>
    </table>
</div>
<div style="clear:both;"></div>
@endsection
