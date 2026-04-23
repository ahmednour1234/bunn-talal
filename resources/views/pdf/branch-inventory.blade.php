@extends('pdf.layout')

@section('content')
<div style="margin-bottom: 12px; font-size: 11px; color: #555;">
    <strong>الفرع:</strong> {{ $branchName }}
    <span style="margin-right: 20px;"><strong>الفترة:</strong> {{ $rangeText }}</span>
</div>

<table>
    <thead>
        <tr>
            <th>الفرع</th>
            <th>المنتج</th>
            <th>التصنيف</th>
            <th>الوحدة</th>
            <th>الكمية</th>
            <th>سعر التكلفة</th>
            <th>سعر البيع</th>
            <th>قيمة التكلفة</th>
            <th>قيمة البيع</th>
        </tr>
    </thead>
    <tbody>
        @forelse($inventory as $item)
            <tr>
                <td>{{ $item->branch_name }}</td>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->category_name ?? '—' }}</td>
                <td>{{ $item->unit_name ?? '—' }}</td>
                <td>{{ number_format($item->quantity) }}</td>
                <td class="amount">{{ number_format($item->cost_price, 2) }}</td>
                <td class="amount">{{ number_format($item->selling_price, 2) }}</td>
                <td class="amount">{{ number_format($item->cost_value, 2) }}</td>
                <td class="amount amount-positive">{{ number_format($item->selling_value, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center; color:#888;">لا توجد بيانات</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if(count($summary) > 0)
    <h3 class="section-title">ملخص الفروع</h3>
    <table>
        <thead>
            <tr>
                <th>الفرع</th>
                <th>عدد المنتجات</th>
                <th>إجمالي الكمية</th>
                <th>إجمالي قيمة التكلفة</th>
                <th>إجمالي قيمة البيع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary as $row)
                <tr>
                    <td>{{ $row->branch_name }}</td>
                    <td>{{ $row->total_products }}</td>
                    <td>{{ number_format($row->total_quantity) }}</td>
                    <td class="amount">{{ number_format($row->total_cost_value, 2) }}</td>
                    <td class="amount amount-positive">{{ number_format($row->total_selling_value, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
