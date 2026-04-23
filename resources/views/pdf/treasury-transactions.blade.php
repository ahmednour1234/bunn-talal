@extends('pdf.layout')

@section('content')
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>الخزنة</th>
            <th>النوع</th>
            <th>المبلغ</th>
            <th>الوصف</th>
            <th>التاريخ</th>
            <th>رقم المرجع</th>
            <th>بواسطة</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $tx)
        <tr>
            <td>{{ $tx->id }}</td>
            <td>{{ $tx->treasury?->name ?? '—' }}</td>
            <td>
                @if($tx->type === 'deposit')
                    <span class="badge badge-green">إيداع</span>
                @else
                    <span class="badge badge-red">سحب</span>
                @endif
            </td>
            <td class="amount {{ $tx->type === 'deposit' ? 'amount-positive' : 'amount-negative' }}">{{ number_format($tx->amount, 2) }}</td>
            <td>{{ $tx->description ?? '—' }}</td>
            <td>{{ $tx->date->format('Y-m-d') }}</td>
            <td style="direction:ltr; text-align:left;">{{ $tx->reference_number ?? '—' }}</td>
            <td>{{ $tx->admin?->name ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
