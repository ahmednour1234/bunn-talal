<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap');

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        direction: rtl;
        color: #2d2d2d;
        font-size: 11px;
        line-height: 1.5;
        background: #fff;
    }

    .header {
        background: #6D4C41;
        color: #fff;
        padding: 20px;
        margin-bottom: 20px;
    }
    .header-title { font-size: 22px; font-weight: 900; margin-bottom: 5px; }
    .header-sub { font-size: 12px; opacity: 0.8; }

    .info-grid {
        display: table;
        width: 100%;
        margin-bottom: 16px;
        border-collapse: collapse;
    }
    .info-cell {
        display: table-cell;
        width: 50%;
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        vertical-align: top;
    }
    .info-label { font-size: 9px; color: #888; font-weight: 600; margin-bottom: 2px; }
    .info-value { font-size: 12px; font-weight: 700; color: #1a1a1a; }

    .section-title {
        background: #5D4037;
        color: #fff;
        padding: 7px 12px;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 0;
        margin-top: 16px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }
    table thead th {
        background: #EFEBE9;
        padding: 7px 8px;
        text-align: right;
        font-weight: 700;
        border: 1px solid #d6cccc;
        color: #4E342E;
    }
    table tbody td {
        padding: 6px 8px;
        border: 1px solid #ece8e8;
        text-align: right;
    }
    table tbody tr:nth-child(even) { background: #faf9f9; }

    .totals-box {
        margin-top: 16px;
        background: #FBF7F5;
        border: 1px solid #D7CCC8;
        padding: 12px;
    }
    .totals-row {
        display: table;
        width: 100%;
        margin-bottom: 4px;
    }
    .totals-label { display: table-cell; width: 70%; font-size: 11px; color: #555; }
    .totals-value { display: table-cell; width: 30%; font-size: 11px; font-weight: 700; text-align: left; color: #1a1a1a; }
    .totals-total-label { display: table-cell; width: 70%; font-size: 13px; font-weight: 700; color: #4E342E; }
    .totals-total-value { display: table-cell; width: 30%; font-size: 13px; font-weight: 900; text-align: left; color: #4E342E; }

    .deficit-box {
        margin-top: 12px;
        background: #FFF5F5;
        border: 2px solid #FCA5A5;
        padding: 12px;
    }
    .deficit-title { font-size: 12px; font-weight: 700; color: #DC2626; margin-bottom: 6px; }

    .signatures {
        margin-top: 40px;
        display: table;
        width: 100%;
    }
    .sig-cell {
        display: table-cell;
        width: 33%;
        text-align: center;
        padding: 0 10px;
    }
    .sig-line {
        border-top: 1px solid #aaa;
        margin-top: 50px;
        padding-top: 6px;
        font-size: 10px;
        color: #666;
    }
    .sig-name { font-size: 11px; font-weight: 700; color: #1a1a1a; margin-top: 2px; }

    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
    }
    .badge-settled  { background: #D1FAE5; color: #065F46; }
    .badge-active   { background: #DBEAFE; color: #1E40AF; }
    .badge-draft    { background: #F3F4F6; color: #374151; }
    .badge-cancelled { background: #FEE2E2; color: #991B1B; }
    .badge-returning { background: #FEF3C7; color: #92400E; }
    .badge-in_transit { background: #EDE9FE; color: #5B21B6; }

    .footer {
        margin-top: 30px;
        padding-top: 10px;
        border-top: 1px solid #ddd;
        font-size: 9px;
        color: #aaa;
        text-align: center;
    }

    .no-items { text-align: center; color: #aaa; font-size: 10px; padding: 14px; }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div class="header-title">تقرير الرحلة — {{ $trip->trip_number }}</div>
    <div class="header-sub">
        المندوب: {{ $trip->delegate?->name ?? '—' }} &nbsp;|&nbsp;
        الفرع: {{ $trip->branch?->name ?? '—' }} &nbsp;|&nbsp;
        التاريخ: {{ now()->format('Y-m-d') }}
    </div>
</div>

{{-- Info Grid --}}
<div class="info-grid">
    <div class="info-cell">
        <div class="info-label">رقم الرحلة</div>
        <div class="info-value">{{ $trip->trip_number }}</div>
    </div>
    <div class="info-cell">
        <div class="info-label">الحالة</div>
        <div class="info-value">
            <span class="badge badge-{{ $trip->status }}">{{ $trip->statusLabel() }}</span>
        </div>
    </div>
    <div class="info-cell">
        <div class="info-label">تاريخ البدء</div>
        <div class="info-value">{{ $trip->start_date?->format('Y-m-d') ?? '—' }}</div>
    </div>
    <div class="info-cell">
        <div class="info-label">تاريخ العودة المتوقع</div>
        <div class="info-value">{{ $trip->expected_return_date?->format('Y-m-d') ?? '—' }}</div>
    </div>
    <div class="info-cell">
        <div class="info-label">تاريخ العودة الفعلي</div>
        <div class="info-value">{{ $trip->actual_return_date?->format('Y-m-d') ?? '—' }}</div>
    </div>
    <div class="info-cell">
        <div class="info-label">منشئ الرحلة</div>
        <div class="info-value">{{ $trip->admin?->name ?? '—' }}</div>
    </div>
    @if($trip->notes)
    <div class="info-cell" style="display:table-cell; width:100%; border-top:none;">
        <div class="info-label">ملاحظات</div>
        <div class="info-value" style="font-weight:400; font-size:11px;">{{ $trip->notes }}</div>
    </div>
    @endif
</div>

{{-- Totals Box --}}
<div class="totals-box">
    <div class="totals-row">
        <div class="totals-label">إجمالي قيمة الصرف</div>
        <div class="totals-value">{{ number_format($trip->total_dispatched_value, 2) }} ج.م</div>
    </div>
    <div class="totals-row">
        <div class="totals-label">إجمالي الفواتير</div>
        <div class="totals-value">{{ number_format($trip->total_invoiced, 2) }} ج.م</div>
    </div>
    <div class="totals-row">
        <div class="totals-label">إجمالي التحصيل</div>
        <div class="totals-value">{{ number_format($trip->total_collected, 2) }} ج.م</div>
    </div>
    <div class="totals-row">
        <div class="totals-label">إجمالي المرتجعات</div>
        <div class="totals-value">{{ number_format($trip->total_returned_value, 2) }} ج.م</div>
    </div>
    @if($trip->status === 'settled')
    <hr style="border:none; border-top:1px solid #D7CCC8; margin:6px 0;">
    <div class="totals-row">
        <div class="totals-total-label">الكاش المتوقع</div>
        <div class="totals-total-value">{{ number_format($trip->settlement_cash_expected, 2) }} ج.م</div>
    </div>
    <div class="totals-row">
        <div class="totals-total-label">الكاش الفعلي</div>
        <div class="totals-total-value">{{ number_format($trip->settlement_cash_actual, 2) }} ج.م</div>
    </div>
    @endif
</div>

{{-- Deficit Alert --}}
@if($trip->settlement_cash_deficit > 0 || $trip->settlement_product_deficit > 0)
<div class="deficit-box">
    <div class="deficit-title">⚠️ تنبيه: تم رصد عجز في هذه الرحلة</div>
    @if($trip->settlement_cash_deficit > 0)
    <div>عجز الكاش: <strong>{{ number_format($trip->settlement_cash_deficit, 2) }} ج.م</strong></div>
    @endif
    @if($trip->settlement_product_deficit > 0)
    <div>عجز البضاعة (القيمة): <strong>{{ number_format($trip->settlement_product_deficit, 2) }}</strong></div>
    @endif
    @if($trip->settlement_notes)
    <div style="margin-top:6px; font-size:10px; color:#666;">ملاحظات التسوية: {{ $trip->settlement_notes }}</div>
    @endif
</div>
@endif

{{-- Dispatch Orders --}}
<div class="section-title">أوامر الصرف</div>
@if($trip->dispatches->isNotEmpty())
<table>
    <thead>
        <tr>
            <th style="width:120px;">رقم الأمر</th>
            <th>الفرع</th>
            <th style="width:90px;">التاريخ</th>
            <th>الحالة</th>
            <th style="width:100px;">الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($trip->dispatches as $d)
        <tr>
            <td style="font-family:monospace;">{{ $d->dispatch_number }}</td>
            <td>{{ $d->branch?->name }}</td>
            <td>{{ $d->dispatch_date?->format('Y-m-d') }}</td>
            <td>{{ $d->status }}</td>
            <td>{{ number_format($d->total_value, 2) }} ج.م</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="no-items">لا توجد أوامر صرف مرتبطة</div>
@endif

{{-- Sale Orders --}}
<div class="section-title">فواتير البيع</div>
@if($trip->saleOrders->isNotEmpty())
<table>
    <thead>
        <tr>
            <th style="width:120px;">رقم الفاتورة</th>
            <th>العميل</th>
            <th style="width:90px;">التاريخ</th>
            <th>الحالة</th>
            <th style="width:100px;">الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($trip->saleOrders as $o)
        <tr>
            <td style="font-family:monospace;">{{ $o->order_number }}</td>
            <td>{{ $o->customer?->name }}</td>
            <td>{{ $o->order_date?->format('Y-m-d') }}</td>
            <td>{{ $o->status }}</td>
            <td>{{ number_format($o->final_amount, 2) }} ج.م</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="no-items">لا توجد فواتير بيع مرتبطة</div>
@endif

{{-- Collections --}}
<div class="section-title">التحصيلات</div>
@if($trip->collections->isNotEmpty())
<table>
    <thead>
        <tr>
            <th style="width:120px;">رقم التحصيل</th>
            <th>العميل</th>
            <th style="width:90px;">التاريخ</th>
            <th style="width:100px;">المبلغ</th>
        </tr>
    </thead>
    <tbody>
        @foreach($trip->collections as $c)
        <tr>
            <td style="font-family:monospace;">{{ $c->collection_number }}</td>
            <td>{{ $c->customer?->name }}</td>
            <td>{{ $c->collection_date?->format('Y-m-d') }}</td>
            <td style="color:#065F46; font-weight:700;">{{ number_format($c->amount, 2) }} ج.م</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="no-items">لا توجد تحصيلات مرتبطة</div>
@endif

{{-- Returns --}}
@if($trip->saleReturns->isNotEmpty())
<div class="section-title">المرتجعات</div>
<table>
    <thead>
        <tr>
            <th style="width:120px;">رقم المرتجع</th>
            <th>العميل</th>
            <th style="width:90px;">التاريخ</th>
            <th style="width:100px;">الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($trip->saleReturns as $r)
        <tr>
            <td style="font-family:monospace;">{{ $r->return_number }}</td>
            <td>{{ $r->customer?->name }}</td>
            <td>{{ $r->return_date?->format('Y-m-d') }}</td>
            <td style="color:#991B1B; font-weight:700;">{{ number_format($r->final_amount, 2) }} ج.م</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Signatures --}}
<div class="signatures">
    <div class="sig-cell">
        <div class="sig-line">
            توقيع المندوب
            <div class="sig-name">{{ $trip->delegate?->name ?? '——————' }}</div>
        </div>
    </div>
    <div class="sig-cell">
        <div class="sig-line">
            توقيع المسؤول
            <div class="sig-name">{{ $trip->admin?->name ?? '——————' }}</div>
        </div>
    </div>
    <div class="sig-cell">
        <div class="sig-line">
            الختم الرسمي
            <div class="sig-name">——————</div>
        </div>
    </div>
</div>

<div class="footer">
    تم إنشاء هذا التقرير بتاريخ {{ now()->format('Y-m-d H:i') }} &nbsp;|&nbsp;
    النظام: بن طلال &nbsp;|&nbsp;
    جميع الأرقام بالريال اليمني
</div>

</body>
</html>
