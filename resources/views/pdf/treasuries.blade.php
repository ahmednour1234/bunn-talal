@extends('pdf.layout')

@section('content')
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>اسم الخزنة</th>
            <th>الرصيد</th>
            <th>الحالة</th>
            <th>تاريخ الإنشاء</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $treasury)
        <tr>
            <td>{{ $treasury->id }}</td>
            <td>{{ $treasury->name }}</td>
            <td class="amount {{ $treasury->balance >= 0 ? 'amount-positive' : 'amount-negative' }}">{{ number_format($treasury->balance, 2) }}</td>
            <td>
                @if($treasury->is_active)
                    <span class="badge badge-green">نشط</span>
                @else
                    <span class="badge badge-red">معطل</span>
                @endif
            </td>
            <td>{{ $treasury->created_at->format('Y-m-d') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
