<!-- resources/views/invoice/50mm_bill_converted.blade.php -->
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <title>{{ __('common.Ready Bill') }} | {{ __('common.Invoice') }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('assets/img/favicon/16.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('assets/img/favicon/32.png')}}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{asset('assets/img/favicon/64.png')}}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{asset('assets/img/favicon/512.png')}}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            width: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', 'Arial', monospace;
            font-size: 7pt;
            font-weight: 600;
            color: #000000;
            line-height: 1.1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .bill-container {
            width: 50mm;
            padding: 0;
            margin: 0 auto;
        }

        .logo {
            width: 60px !important;
            height: 30px !important;
            display: block;
            margin: 0 auto 0.5mm auto;
        }

        .header-info p {
            margin-bottom: 0.5mm;
        }

        .table-bordered,
        .totals-table,
        .tax-table {
            border: 1px solid #000 !important;
            width: 50mm !important;
            margin: 0 0 1mm 0;
            table-layout: fixed;
        }

        .table-bordered th,
        .table-bordered td,
        .tax-table th,
        .tax-table td {
            border: 1px solid #000 !important;
            padding: 0.1mm 0.2mm;
            vertical-align: top;
            overflow-wrap: break-word;
        }

        .totals-table td {
            border: none !important;
            padding: 0.1mm 0.2mm;
        }

        .item-name {
            width: 25%;
            white-space: normal;
            font-size: 6.5pt;
        }

        .item-qty {
            width: 15%;
            white-space: nowrap;
        }

        .item-rate {
            width: 17%;
            white-space: normal;
            font-size: 6pt;
        }

        .item-amount {
            width: 20%;
            white-space: normal;
            font-size: 6pt;
        }

        .item-mrp {
            width: 17%;
            white-space: normal;
            font-size: 6pt;
        }

        .item-hsn {
            width: 16%;
            white-space: normal;
            font-size: 5.5pt;
        }

        .text-end {
            text-align: right !important;
        }

        .text-start {
            text-align: left !important;
        }

        .tax-details p {
            font-weight: bold;
            font-size: 8pt;
            margin-bottom: 0.5mm;
        }

        .footer p {
            margin-bottom: 0.5mm;
        }

        .saved-amount {
            font-weight: bold;
        }

        @media print {
            @page {
                margin: 0;
                size: 50mm auto;
            }

            body {
                width: 50mm;
                min-height: 0;
                display: block;
                margin: 0;
                padding: 0;
            }

            .bill-container {
                width: 50mm;
                margin: 0;
            }

            .table-bordered,
            .totals-table,
            .tax-table {
                width: 50mm !important;
            }
        }
    </style>
</head>

<body>
    <div class="bill-container">
        <div class="header-info text-center">
            @if(!in_array($logo, ['NA', 'na', ''], true))
                <img src="{{$logo}}" alt="Logo" class="logo" />
            @else
                <p class="fw-bold mb-0">{{ $business_name }}</p>
            @endif
            <p>{{ __('invoice_page.Address') }}: {{ $address }}</p>
            @if(!in_array($gstin, ['NA', 'na', ''], true))
                <p>GST No.: {{ $gstin }}</p>
            @endif
            <p>{{ __('invoice_page.Invoice No.') }}: {{ $invoice_number }}</p>
            <p>{{ __('invoice_page.Date') }}: {{$invoice_date}}</p>
            <p>{{ __('invoice_page.User') }}: {{ $user_name }}</p>
        </div>

        <table class="table table-bordered">
            <thead>
                @if(($preferences->preference_mrp_invoice == 1) && ($preferences->preference_hsn_invoice == 1))
                    <tr>
                        <th scope="col" width="16%" class="vertical-spacing item-hsn">{{ __('invoice_page.HSN') }}</th>
                        <th scope="col" width="25%" class="vertical-spacing item-name">{{ __('invoice_page.Item') }}</th>
                        <th scope="col" width="15%" class="vertical-spacing item-qty">{{ __('invoice_page.Qty') }}</th>
                        <th scope="col" width="17%" class="vertical-spacing item-mrp">{{ __('invoice_page.MRP') }}</th>
                        <th scope="col" width="17%" class="vertical-spacing item-rate">{{ __('invoice_page.Rate') }}</th>
                        <th scope="col" width="20%" class="vertical-spacing item-amount">{{ __('invoice_page.Amount') }}</th>
                    </tr>
                @elseif($preferences->preference_hsn_invoice == 1)
                    <tr>
                        <th scope="col" width="16%" class="vertical-spacing item-hsn">{{ __('invoice_page.HSN') }}</th>
                        <th scope="col" width="32%" class="vertical-spacing item-name">{{ __('invoice_page.Item') }}</th>
                        <th scope="col" width="15%" class="vertical-spacing item-qty">{{ __('invoice_page.Qty') }}</th>
                        <th scope="col" width="17%" class="vertical-spacing item-rate">{{ __('invoice_page.Rate') }}</th>
                        <th scope="col" width="20%" class="vertical-spacing item-amount">{{ __('invoice_page.Amount') }}</th>
                    </tr>
                @elseif($preferences->preference_mrp_invoice == 1)
                    <tr>
                        <th scope="col" width="32%" class="vertical-spacing item-name">{{ __('invoice_page.Item') }}</th>
                        <th scope="col" width="15%" class="vertical-spacing item-qty">{{ __('invoice_page.Qty') }}</th>
                        <th scope="col" width="17%" class="vertical-spacing item-mrp">{{ __('invoice_page.MRP') }}</th>
                        <th scope="col" width="17%" class="vertical-spacing item-rate">{{ __('invoice_page.Rate') }}</th>
                        <th scope="col" width="20%" class="vertical-spacing item-amount">{{ __('invoice_page.Amount') }}</th>
                    </tr>
                @else
                    <tr>
                        <th scope="col" width="40%" class="vertical-spacing item-name">{{ __('invoice_page.Item') }}</th>
                        <th scope="col" width="15%" class="vertical-spacing item-qty">{{ __('invoice_page.Qty') }}</th>
                        <th scope="col" width="17%" class="vertical-spacing item-rate">{{ __('invoice_page.Rate') }}</th>
                        <th scope="col" width="20%" class="vertical-spacing item-amount">{{ __('invoice_page.Amount') }}</th>
                    </tr>
                @endif
            </thead>
            <tbody>
                @php
$mrp_total = 0;
$sub_total = 0;
$diff_mrp_n_rate = 0;
$isRefund = 0;
$total_tax = 0.00;
                @endphp
                @foreach($item_list as $item)
                    @if(isset($item->tax2))
                        @php $total_tax = $total_tax + floatval(number_format((float) $item->tax1->amount, 2)) + floatval(number_format((float) $item->tax2->amount, 2)); @endphp
                    @else
                        @if(isset($item->tax1))
                            @php $total_tax = $total_tax + floatval(number_format($item->tax1->amount, 2)); @endphp
                        @endif
                    @endif
                    @if($item->isRefund == 1)
                        @php
        $sub_total += ((float) $item->quantity * (float) $item->rate) * (-1);
        $isRefund = 1;
                        @endphp
                    @else
                        @php
        $sub_total += ((float) $item->quantity * (float) $item->rate);
                        @endphp
                    @endif
                    @if($preferences->preference_mrp_invoice == 1)
                        @if($item->mrp != 'NA')
                            @php $mrp_total = $mrp_total + (float) $item->quantity * (float) $item->mrp; @endphp
                        @else
                            @php $mrp_total = $mrp_total + (float) $item->quantity * (float) $item->rate; @endphp
                        @endif
                        @php $diff_mrp_n_rate = $mrp_total - $grand_total; @endphp
                    @endif
                    <tr>
                        @if($preferences->preference_hsn_invoice == 1)
                            <td class="item-hsn">{{ $item->hsn != 'NA' ? $item->hsn : '' }}</td>
                        @endif
                        <td class="item-name">{{ $item->itemName }}</td>
                        <td class="item-qty text-end">{{ number_format($item->quantity, 0) }} {{ $item->selectedUnit }}</td>
                        @if($preferences->preference_mrp_invoice == 1)
                            <td class="item-mrp text-end">
                                @if($item->mrp == 0 || $item->mrp == 'NA')
                                    N/A
                                @else
                                    {{ $currency }}
                                    {{ \Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->mrp, $decimal_separator) }}
                                @endif
                            </td>
                        @endif
                        <td class="item-rate text-end">
                            {{ $currency }}
                            {{ \Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->rate, $decimal_separator) }}
                        </td>
                        <td class="item-amount text-end">
                            @if($item->isRefund == 1)
                                – {{ $currency }}
                                {{ \Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber(abs($item->amount), $decimal_separator) }}
                            @else
                                {{ $currency }}
                                {{ \Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->amount, $decimal_separator) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-table">
            <tbody>
                @if($preferences->preference_mrp_invoice == 1)
                    <tr>
                        <td class="text-start" width="70%">{{ __('invoice_page.Total MRP') }}:</td>
                        <td class="text-end" width="30%">{{ $currency }}
                            {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($mrp_total, $decimal_separator) }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td class="text-start" width="70%">{{ __('invoice_page.Sub Total') }}:</td>
                    <td class="text-end" width="30%">
                        @if($sub_total < 0)
                            – {{ $currency }}
                            {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber(abs($sub_total), $decimal_separator) }}
                        @else
                            {{ $currency }}
                            {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($sub_total, $decimal_separator) }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="text-start" width="70%">{{ __('invoice_page.Grand Total') }}:</td>
                    <td class="text-end" width="30%">
                        @if($grand_total < 0)
                            – {{ $currency }}
                            {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber(abs($grand_total), $decimal_separator) }}
                        @else
                            {{ $currency }}
                            {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($grand_total, $decimal_separator) }}
                        @endif
                    </td>
                </tr>
                @if($preferences->preference_mrp_invoice == 1 && $diff_mrp_n_rate != 0 && $isRefund == 0)
                    <tr>
                        <td class="text-start" width="70%">{{ __('invoice_page.You have saved') }}:</td>
                        <td class="text-end saved-amount" width="30%">{{ $currency }}
                            {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($diff_mrp_n_rate, $decimal_separator) }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="tax-details">
            <p>{{ __('invoice_page.Tax Details') }}</p>
            <table class="tax-table">
                <tbody>
                    <tr>
                        <th class="text-start" width="70%">{{ __('invoice_page.Total Taxable Value') }}</th>
                        <td class="text-end" width="30%">{{ $currency }} {{ number_format($totalTaxAmount, 2) }}</td>
                    </tr>
                    @foreach($taxGroups as $taxType => $taxDetails)
                        @foreach($taxDetails as $percent => $tax)
                            <tr>
                                <th class="text-start">{{ $tax['taxName'] }} ({{ $percent }}%)</th>
                                <td class="text-end">{{ $currency }} {{ number_format($tax['totalTax'], 2) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer text-center">
            @php
                $formatted_number = preg_replace('/(\d{5})(\d{5})/', '$1 $2', str_replace('+91-', '+91 ', $mobile_number));
            @endphp
            <p>{{ __('invoice_page.Contact Number') }}: {{ $formatted_number }}</p>
            <p>{{ __('invoice_page.It is a computer generated invoice') }}</p>
        </div>
    </div>
</body>

<script>
    window.onload = function () {
        window.print();
    };
</script>

</html>