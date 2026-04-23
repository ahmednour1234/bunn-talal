@extends('pdf.layout')

@section('content')
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>النوع</th>
            <th>الحساب</th>
            <th>الخزنة</th>
            <th>المبلغ</th>
            <th>الوصف</th>
            <th>التاريخ</th>
            <th>بواسطة</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $tx)
        <tr>
            <td>{{ $tx->id }}</td>
            <td>
                @if($tx->type === 'expense')
                    <span class="badge badge-red">مصروف</span>
                @else
                    <span class="badge badge-green">إيراد</span>
                @endif
            </td>
            <td>{{ $tx->account?->name ?? '—' }}</td>
            <td>{{ $tx->treasury?->name ?? '—' }}</td>
            <td class="amount {{ $tx->type === 'revenue' ? 'amount-positive' : 'amount-negative' }}">{{ number_format($tx->amount, 2) }}</td>
            <td>{{ $tx->description ?? '—' }}</td>
            <td>{{ $tx->date->format('Y-m-d') }}</td>
            <td>{{ $tx->admin?->name ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
