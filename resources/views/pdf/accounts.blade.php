@extends('pdf.layout')

@section('content')
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>اسم الحساب</th>
            <th>رقم الحساب</th>
            <th>يظهر للمندوب</th>
            <th>الحالة</th>
            <th>تاريخ الإنشاء</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $account)
        <tr>
            <td>{{ $account->id }}</td>
            <td>{{ $account->name }}</td>
            <td style="direction:ltr; text-align:left;">{{ $account->account_number }}</td>
            <td>
                @if($account->visible_to_delegate)
                    <span class="badge badge-blue">نعم</span>
                @else
                    <span class="badge badge-gray">لا</span>
                @endif
            </td>
            <td>
                @if($account->is_active)
                    <span class="badge badge-green">نشط</span>
                @else
                    <span class="badge badge-red">معطل</span>
                @endif
            </td>
            <td>{{ $account->created_at->format('Y-m-d') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
