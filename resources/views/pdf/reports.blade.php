@extends('pdf.layout')

@section('content')
{{-- Summary Cards --}}
<div style="margin-bottom: 20px;">
    <table style="width: 100%; margin-bottom: 0;">
        <tr>
            <td style="text-align: center; padding: 10px; background: #faf8f5; border: 1px solid #e5e5e5; width: 20%;">
                <div style="font-size: 9px; color: #888; margin-bottom: 3px;">إجمالي أرصدة الخزن</div>
                <div class="amount" style="font-size: 14px; font-weight: bold; color: #6B4F3A;">{{ number_format($summary['totalTreasuryBalance'], 2) }}</div>
            </td>
            <td style="text-align: center; padding: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; width: 20%;">
                <div style="font-size: 9px; color: #888; margin-bottom: 3px;">إجمالي الإيرادات</div>
                <div class="amount amount-positive" style="font-size: 14px; font-weight: bold;">{{ number_format($summary['totalRevenues'], 2) }}</div>
            </td>
            <td style="text-align: center; padding: 10px; background: #fef2f2; border: 1px solid #fecaca; width: 20%;">
                <div style="font-size: 9px; color: #888; margin-bottom: 3px;">إجمالي المصروفات</div>
                <div class="amount amount-negative" style="font-size: 14px; font-weight: bold;">{{ number_format($summary['totalExpenses'], 2) }}</div>
            </td>
            <td style="text-align: center; padding: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; width: 20%;">
                <div style="font-size: 9px; color: #888; margin-bottom: 3px;">إجمالي الإيداعات</div>
                <div class="amount" style="font-size: 14px; font-weight: bold; color: #166534;">{{ number_format($summary['totalDeposits'], 2) }}</div>
            </td>
            <td style="text-align: center; padding: 10px; background: #fef2f2; border: 1px solid #fecaca; width: 20%;">
                <div style="font-size: 9px; color: #888; margin-bottom: 3px;">إجمالي السحوبات</div>
                <div class="amount" style="font-size: 14px; font-weight: bold; color: #991b1b;">{{ number_format($summary['totalWithdrawals'], 2) }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Treasury Balances --}}
<h3 style="color: #6B4F3A; font-size: 13px; margin: 15px 0 5px;">أرصدة الخزن</h3>
<table>
    <thead>
        <tr>
            <th>اسم الخزنة</th>
            <th>الرصيد</th>
        </tr>
    </thead>
    <tbody>
        @foreach($summary['treasuryBalances'] as $treasury)
        <tr>
            <td>{{ $treasury->name }}</td>
            <td class="amount {{ $treasury->balance >= 0 ? 'amount-positive' : 'amount-negative' }}">{{ number_format($treasury->balance, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Expenses by Account --}}
<h3 style="color: #6B4F3A; font-size: 13px; margin: 15px 0 5px;">المصروفات حسب الحساب</h3>
<table>
    <thead>
        <tr>
            <th>الحساب</th>
            <th>الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($summary['expensesByAccount'] as $item)
        <tr>
            <td>{{ $item->account?->name ?? '—' }}</td>
            <td class="amount amount-negative">{{ number_format($item->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Revenues by Account --}}
<h3 style="color: #6B4F3A; font-size: 13px; margin: 15px 0 5px;">الإيرادات حسب الحساب</h3>
<table>
    <thead>
        <tr>
            <th>الحساب</th>
            <th>الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($summary['revenuesByAccount'] as $item)
        <tr>
            <td>{{ $item->account?->name ?? '—' }}</td>
            <td class="amount amount-positive">{{ number_format($item->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Recent Transactions --}}
<h3 style="color: #6B4F3A; font-size: 13px; margin: 15px 0 5px;">آخر المعاملات</h3>
<table>
    <thead>
        <tr>
            <th>النوع</th>
            <th>الحساب</th>
            <th>المبلغ</th>
            <th>التاريخ</th>
            <th>الوصف</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $tx)
        <tr>
            <td>
                @if($tx->type === 'expense')
                    <span class="badge badge-red">مصروف</span>
                @else
                    <span class="badge badge-green">إيراد</span>
                @endif
            </td>
            <td>{{ $tx->account?->name ?? '—' }}</td>
            <td class="amount {{ $tx->type === 'revenue' ? 'amount-positive' : 'amount-negative' }}">
                {{ $tx->type === 'revenue' ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
            </td>
            <td>{{ $tx->date->format('Y-m-d') }}</td>
            <td>{{ \Str::limit($tx->description, 40) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
