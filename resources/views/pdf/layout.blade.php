<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'XB Riyaz', 'Al Qalam Quran', sans-serif;
            font-size: 11px;
            direction: rtl;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 3px solid #6B4F3A;
        }
        .header h1 {
            font-size: 22px;
            color: #6B4F3A;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            color: #888;
        }
        .meta {
            margin-bottom: 15px;
            font-size: 10px;
            color: #666;
        }
        .meta-right { float: right; }
        .meta-left { float: left; }
        .clearfix::after { content: ""; display: table; clear: both; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #6B4F3A;
            color: #fff;
            padding: 8px 10px;
            text-align: right;
            font-size: 11px;
            font-weight: bold;
        }
        td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e5e5;
            font-size: 10px;
            text-align: right;
        }
        tr:nth-child(even) td { background-color: #faf8f5; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #f3f4f6; color: #6b7280; }
        .amount { direction: ltr; text-align: left; }
        .amount-positive { color: #166534; }
        .amount-negative { color: #991b1b; }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e5e5;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #6B4F3A;
            margin: 15px 0 5px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>بن طلال</h1>
        <p>{{ $title }}</p>
    </div>

    <div class="meta clearfix">
        <div class="meta-right">تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}</div>
        <div class="meta-left">عدد السجلات: {{ isset($data) ? count($data) : 1 }}</div>
    </div>

    @yield('content')

    <div class="footer">
        &copy; {{ date('Y') }} بن طلال — جميع الحقوق محفوظة
    </div>
</body>
</html>
