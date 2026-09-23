<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <title>{{ __('common.Ready Bill') }} | {{ __('common.Invoice') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('assets/img/favicon/16.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('assets/img/favicon/32.png')}}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{asset('assets/img/favicon/64.png')}}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{asset('assets/img/favicon/512.png')}}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        /* A4 page size for printing */
        @page {
            size: A4;
            margin: 10mm;
        }

        /* Set body to A4 dimensions for print and screen preview */
        body {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 10mm;
            box-sizing: border-box;
            /* font-family: 'Arial', 'Helvetica', sans-serif; */
            font-family: 'Courier New', 'Arial', monospace;
            font-size: 12pt;
        }

        /* Ensure container fits within A4 */
        .container {
            width: 100%;
            max-width: 190mm; /* Account for margins */
            height: auto;
            margin: 0 auto;
        }

        .dotted-border {
            border-top: 2px dotted black;
            padding: 8px 0;
        }

        .tableTr {
            margin-top: -8px !important;
            margin-bottom: -8px !important;
            display: flex;
        }

        .flex-container {
            display: flex;
        }

        .topTable {
            width: 100%;
            table-layout: fixed;
        }

        .topTable1 {
            width: 100%;
            table-layout: fixed;
        }

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .vertical-spacing {
            padding-top: -10px !important;
            padding-bottom: 30px !important;
        }

        /* Adjust table font size and padding for better fit */
        .table {
            font-size: 10pt;
        }

        .table th, .table td {
            padding: 4px;
            vertical-align: middle;
        }

        /* Ensure image scales appropriately */
        .img-fluid {
            max-width: 180px;
            max-height: 80px;
        }

        /* Center text for business name and address */
        .text-center h3, .text-center h5 {
            margin: 4px 0;
        }

        /* Tighten spacing for totals */
        .topTable tbody tr td {
            padding: 2px 4px;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                @if(!in_array($logo, ['NA', 'na', ''], true))
                    <img class="img-fluid" style="width:200px;height:100px" src="{{$logo}}" alt="" />
                @else
                    <h3 class="fw-bold">{{$business_name}}</h3>
                @endif
                <h5 class="pt-2">{{ __('invoice_page.Address') }}: {{$address}}</h5>
                @if(!in_array($gstin, ['NA', 'na', ''], true))
                    <h5>GST No.: {{$gstin}}</h5>
                @endif
            </div>

            <div class="col-12">
                <table class="topTable">
                    <tr>
                        <td class="left"><span>{{ __('invoice_page.Invoice No.') }}: {{$invoice_number}}</span></td>
                        <td class="right"><span>{{ __('invoice_page.Date') }}: {{$invoice_date}}</span></td>
                    </tr>
                    <tr>
                        <td><span>{{ __('invoice_page.User') }}: {{$user_name}}</span></td>
                    </tr>
                </table>
            </div>

            <div class="col-12 dotted-border"></div>

            <div class="col-12">
                <table class="table table-borderless" id="sale_itemList">
                    <thead>
                        @if(($preferences->preference_mrp_invoice == 1))
                            <tr>
                                <th scope="col" width="30%" class="vertical-spacing">{{ __('invoice_page.Item') }}</th>
                                <th scope="col" width="8%" class="vertical-spacing">{{ __('invoice_page.QTY') }}</th>
                                <th scope="col" width="9%" class="vertical-spacing">{{ __('invoice_page.MRP') }}</th>
                                <th scope="col" width="9%" class="vertical-spacing">{{ __('invoice_page.Rate') }}</th>
                                <th scope="col" width="12%" class="vertical-spacing">{{ __('invoice_page.Amount') }}</th>
                            </tr>
                        @else
                            <tr>
                                <th scope="col" width="40%" class="vertical-spacing">{{ __('invoice_page.Item') }}</th>
                                <th scope="col" width="10%" class="vertical-spacing">{{ __('invoice_page.QTY') }}</th>
                                <th scope="col" width="10%" class="vertical-spacing">{{ __('invoice_page.Rate') }}</th>
                                <th scope="col" width="12%" class="vertical-spacing">{{ __('invoice_page.Amount') }}</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody class="dotted-border">
                        @php
$mrp_total = 0;
$sub_total = 0;
$diff_mrp_n_rate = 0;
$isRefund = 0;
$total_tax = 0.00;
                        @endphp
                        @foreach($item_list as $item)
                            <!-- Total tax calculation -->
                            @if(isset($item->tax2))
                                @php $total_tax = $total_tax + floatval(number_format((float) $item->tax1->amount, 2)) +
            floatval(number_format($item->tax2->amount, 2)); @endphp
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
                            @elseif($item->isRefund == 0)
                                @php
        $sub_total += ((float) $item->quantity * (float) $item->rate);
                                @endphp
                            @endif

                            @if(($preferences->preference_mrp_invoice == 1) && ($preferences->preference_hsn_invoice == 1))
                                @if($item->mrp != 'NA')
                                    @php $mrp_total = $mrp_total + (float) $item->quantity * (float) $item->mrp; @endphp
                                @else
                                    @php $mrp_total = $mrp_total + (float) $item->quantity * (float) $item->rate; @endphp
                                @endif
                                @php $diff_mrp_n_rate = $mrp_total - $grand_total; @endphp
                                <tr>
                                    <td>{{$item->hsn}}</td>
                                    <td>{{$item->itemName}}</td>
                                    <td>{{$item->quantity}} {{ $item->selectedUnit }}</td>
                                    <td>{{$currency}} {{$item->mrp}}</td>
                                    <td>{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->rate, $decimal_separator)}}</td>
                                    @if($item->isRefund == 1)
                                        <td>– {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $item->amount, $decimal_separator)}}</td>
                                    @else
                                        <td>{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->amount, $decimal_separator)}}</td>
                                    @endif
                                </tr>
                            @elseif($preferences->preference_mrp_invoice == 1)
                                @if($item->mrp != 'NA')
                                    @php $mrp_total = $mrp_total + (float) $item->quantity * (float) $item->mrp; @endphp
                                @else
                                    @php $mrp_total = $mrp_total + (float) $item->quantity * (float) $item->rate; @endphp
                                @endif
                                @php $diff_mrp_n_rate = $mrp_total - $grand_total; @endphp
                                <tr>
                                    <td>{{$item->itemName}}</td>
                                    <td>{{$item->quantity}} {{ $item->selectedUnit }}</td>
                                    <td>{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->mrp, $decimal_separator)}}</td>
                                    <td>{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->rate, $decimal_separator)}}</td>
                                    @if($item->isRefund == 1)
                                        <td>– {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $item->amount, $decimal_separator)}}</td>
                                    @else
                                        <td>{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->amount, $decimal_separator)}}</td>
                                    @endif
                                </tr>
                            @elseif($preferences->preference_hsn_invoice == 1)
                                <tr>
                                    <td>{{$item->hsn}}</td>
                                    <td>{{$item->itemName}}</td>
                                    <td>{{$item->quantity}} {{ $item->selectedUnit }}</td>
                                    <td>{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->rate, $decimal_separator)}}</td>
                                    @if($item->isRefund == 1)
                                        <td>– {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $item->amount, $decimal_separator)}}</td>
                                    @else
                                        <td>{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->amount, $decimal_separator)}}</td>
                                    @endif
                                </tr>
                            @else
                                <tr>
                                    <td>{{$item->itemName}}</td>
                                    <td>{{$item->quantity}} {{ $item->selectedUnit }}</td>
                                    <td>{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->rate, $decimal_separator)}}</td>
                                    @if($item->isRefund == 1)
                                        <td>– {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $item->amount, $decimal_separator)}}</td>
                                    @else
                                        <td>{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->amount, $decimal_separator)}}</td>
                                    @endif
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                <div class="col-12 dotted-border"></div>

                @if(($preferences->preference_mrp_invoice == 1) && ($preferences->preference_hsn_invoice == 1))
                    <table class="topTable">
                        <tbody>
                            @if($preferences->preference_mrp_invoice == 1)
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-start" width="18%">{{ __('invoice_page.Total MRP') }}:</td>
                                    <td width="14%">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($mrp_total, $decimal_separator)}}</td>
                                </tr>
                            @endif
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-start" width="18%">{{ __('invoice_page.Sub Total') }}:</td>
                                @if($sub_total < 0)
                                    <td width="14%">– {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $sub_total, $decimal_separator)}}</td>
                                @else
                                    <td width="14%">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($sub_total, $decimal_separator)}}</td>
                                @endif
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-start" width="18%">{{ __('invoice_page.Grand Total') }}:</td>
                                @if($grand_total < 0)
                                    <td width="14%">– {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $grand_total, $decimal_separator)}}</td>
                                @else
                                    <td width="14%">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($grand_total, $decimal_separator)}}</td>
                                @endif
                            </tr>
                            @if(($preferences->preference_mrp_invoice == 1) && ($diff_mrp_n_rate != 0) && ($isRefund == 0))
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td width="18%" class="text-start">{{ __('invoice_page.You have saved') }}</td>
                                    <td width="14%">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($diff_mrp_n_rate, $decimal_separator)}}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @else
                    <table class="topTable">
                        <tbody>
                            @if($preferences->preference_mrp_invoice == 1)
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-start" width="18%">{{ __('invoice_page.Total MRP') }}:</td>
                                    <td width="16%">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($mrp_total, $decimal_separator)}}</td>
                                </tr>
                            @endif
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-start" width="18%">{{ __('invoice_page.Sub Total') }}:</td>
                                @if($sub_total < 0)
                                    <td width="16%" style="white-space: nowrap;">– {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $sub_total, $decimal_separator)}}</td>
                                @else
                                    <td width="16%" style="white-space: nowrap;">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($sub_total, $decimal_separator)}}</td>
                                @endif
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-start" width="18%">{{ __('invoice_page.Grand Total') }}:</td>
                                @if($grand_total < 0)
                                    <td width="16%" style="white-space: nowrap;">– {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $grand_total, $decimal_separator)}}</td>
                                @else
                                    <td width="16%" style="white-space: nowrap;">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($grand_total, $decimal_separator)}}</td>
                                @endif
                            </tr>
                            @if(($preferences->preference_mrp_invoice == 1) && ($diff_mrp_n_rate != 0) && ($isRefund == 0))
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-start" width="18%">{{ __('invoice_page.You have saved') }}</td>
                                    <td width="16%">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($diff_mrp_n_rate, $decimal_separator)}}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="col-12"><span>{{ __('invoice_page.Tax Details') }}</span></div>
            <div class="col-12 dotted-border"></div>
            <div class="col-12">
                <table class="topTable1">
                    <tr>
                        <td class="left">{{ __('invoice_page.Total Taxable Value') }}</td>
                        <td width="12%" class="left">{{$currency}} {{$totalTaxAmount}}</td>
                    </tr>
                    @foreach($taxGroups as $taxType => $taxDetails)
                        @foreach($taxDetails as $percent => $tax)
                            <tr>
                                <td class="left">{{ $tax['taxName'] }} ({{ $percent }}%)</td>
                                <td width="12%" class="left">{{$currency}} {{ number_format($tax['totalTax'], 2) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </table>
            </div>
            <div class="col-12 dotted-border"></div>
            <div class="col-12 text-center">
                <span>{{ __('invoice_page.Contact Number') }}: {{$mobile_number}}</span><br>
                <span>{{ __('invoice_page.It is a computer generated invoice') }}</span>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
</body>

</html>