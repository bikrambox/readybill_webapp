<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ __('transaction_report_page.Transaction Report') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            margin: 20px;
            font-size: 12pt;
        }

        .header {
            background-color: #2c3e50;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .header-content {
            overflow: hidden;
        }

        .header-left {
            float: left;
            width: 60%;
        }

        .header-right {
            float: right;
            width: 35%;
            text-align: right;
        }

        .business-name {
            font-size: 18pt;
            font-weight: bold;
            margin: 0 0 5px 0;
        }

        .address {
            font-size: 10pt;
            margin: 5px 0;
        }

        .report-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
        }

        .date-range,
        .current-date {
            font-size: 10pt;
            margin: 5px 0;
            font-style: italic;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            font-size: 10pt;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .month-header {
            background-color: #e8ecef;
            padding: 10px;
            font-weight: bold;
            font-size: 12pt;
        }

        .month-total {
            font-weight: bold;
            color: #2c3e50;
        }

        .no-transactions {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12pt;
        }

        .header-content:after {
            content: "";
            display: table;
            clear: both;
        }


        [class^="badge-"] {
            display: inline-block;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 999px;
            color: #fff;
        }

        .badge-primary {
            background-color: #0d6efd;
        }

        .badge-success {
            background-color: #198754;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
    </style>
</head>

<body>
    <div class="header"
        style="background-color: #2c3e50; color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 20px;">
        <div class="header-content">
            <div class="header-left" style="float: left; width: 60%;">
                <h1 class="business-name" style="font-size: 18pt; font-weight: bold; margin: 0 0 5px 0;">
                    {{ $business_name }}
                </h1>
                <p class="address" style="font-size: 10pt; margin: 5px 0;">{{ __('common.Address') }}: {{ $address }}
                </p>
                <p class="report-title" style="font-size: 14pt; font-weight: bold; margin: 5px 0;">
                    {{ __('transaction_report_page.Transaction Report') }}
                </p>
                @if ($dateFrom && $dateTo)
                    <p class="date-range" style="font-size: 10pt; margin: 5px 0; font-style: italic;">
                        {{ __('common.From') }}: {{ $dateFrom }}
                        To: {{ $dateTo }}</p>
                @else
                    <p class="date-range" style="font-size: 10pt; margin: 5px 0; font-style: italic;">
                        {{ __('transaction_report_page.All Transactions') }}</p>
                @endif
            </div>
            <div class="header-right" style="float: right; width: 35%; text-align: right;">
                <p class="current-date" style="font-size: 10pt; margin: 5px 0; font-style: italic;">
                    {{ __('transaction_report_page.Report Generated') }}:
                    {{ $current_date }}
                </p>
            </div>
        </div>
    </div>

    @php
$lastMonthYear = null;
$monthlyTotals = [];
foreach ($items as $item) {
    $date = \Carbon\Carbon::parse($item->created_at);
    $monthYear = $date->format('n-Y');
    if (!isset($monthlyTotals[$monthYear])) {
        $monthlyTotals[$monthYear] = 0;
    }
    $monthlyTotals[$monthYear] += $item->total_price;
}
    @endphp

    @foreach ($items as $item)
                            @php
        $date = \Carbon\Carbon::parse($item->created_at);
        $currentMonth = $date->month;
        $currentYear = $date->year;
        $currentMonthYear = $date->format('n-Y');
        $monthName = strtoupper($date->format('F'));
                            @endphp

                            @if ($lastMonthYear !== $currentMonthYear)
                                @if ($lastMonthYear !== null)
                                    </table>
                                @endif
                                <div class="month-header">
                                    {{ $monthName }}, {{ $currentYear }}
                                    <span class="month-total">
                                        @php
            $total = $monthlyTotals[$currentMonthYear];
            echo $total < 0 ? "-{$currency}" . number_format(-$total, 2, $decimal_separator, '') : "{$currency}" . number_format($total, 2, $decimal_separator, '');
                                        @endphp
                                    </span>
                                </div>
                                <table>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Products</th>
                                        <th>Total</th>
                                        <th>Payment Status</th>
                                        <th>User</th>
                                        <th>Date</th>
                                    </tr>
                                    @php
            $lastMonthYear = $currentMonthYear;
                                    @endphp
                            @endif

                                <tr>
                                    <td>{{ $item->invoice_number  }}</td>
                                    <td>
                                        @php
        $itemList = json_decode($item->item_list, true) ?? [];
        $products = array_map(function ($i) {
            return "{$i['itemName']} {$i['quantity']} {$i['selectedUnit']}";
        }, $itemList);
        echo implode(', ', $products);
                                        @endphp
                                    </td>
                                    <td>
                                        @php
        echo $item->total_price < 0 ? "-{$currency}" . number_format(-$item->total_price, 2, $decimal_separator, '') : "{$currency}" . number_format($item->total_price, 2, $decimal_separator, '');
                                        @endphp
                                    </td>
                                <td style="text-align: center;">
                                    @if($item->payment_status == 1)
                                        <span class="badge-success">Paid</span> 
                                    @elseif($item->payment_status == 0)
                                        <span class="badge-danger">Unpaid</span>
                                    @endif
                                </td>
                                    <td>{{ $item->user_name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y h:i A') }}</td>
                                </tr>

                                @if ($loop->last)
                                    </table>
                                @endif
    @endforeach

    @if (empty($items))
        <p class="no-transactions">{{ __('common.transaction_report_page.No transactions found for the selected period') }}.
        </p>
    @endif
</body>

</html>