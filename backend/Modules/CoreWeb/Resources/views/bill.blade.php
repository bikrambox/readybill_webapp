<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title></title>
    <style>
        .dotted-border {
            border-top: 2px dotted black;
            /* Adjust color and thickness as needed */
            padding: 10px;
            /* Optional: Add padding to the div */
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
            /* Ensure table takes full width */
            table-layout: fixed;
            /* Fix table layout */
        }

        .topTable1 {
            /* width: 87.5%; */
            /* width: 91.5%; */
            width: 100%;
            /* Ensure table takes full width */
            table-layout: fixed;
            /* Fix table layout */
        }

        .left {
            text-align: left;
            /* Align content of left cell to the left */
        }

        .right {
            text-align: right;
            /* Align content of right cell to the right */
        }

        .vertical-spacing{
            padding-top:-10px !important;
            padding-bottom:30px !important;

        }

    </style>
</head>

<body>

    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                @if(!in_array($logo, ['NA', 'na', ''], true))

                <img class="img-fluid" style="width:200px;height:100px" src="{{$logo_url}}{{$logo}}"
                    alt="" />
                @else
                <h3 class="fw-bold">{{$business_name}}</h3>
                @endif
                <h5 class="pt-2">{{ __('common.Address') }}: {{$address}} </h5>
                @if(!in_array($gstin, ['NA', 'na', ''], true)) <h5>GST No.: {{ $gstin }}</h5> @endif
            </div>

            <div class="col-12">
                <table class="topTable">
                    <tr>
                        <td class="left"><span>{{ __('common.Invoice No') }}.: {{$invoice_number}}</span></td>
                        <td class="right"><span>{{ __('common.Date') }}: {{ $invoice_date}}</span></td>
                    </tr>
                    <tr>
                        <td><span>{{ __('common.User') }}: {{$user_name}}</span></td>
                    </tr>
                </table>
            </div>

            <div class="mt-2 col-12 dotted-border">
            </div>
            <div class="col-12">
                <table class="table table-borderless" id="sale_itemList">
                    <thead>
                        @if( ($preferences->preference_mrp_invoice == 1) && ($preferences->preference_hsn_invoice == 1)
                        )
                        <tr>
                            <th scope="col" width="10%" class="vertical-spacing">HSN</th>
                            <th scope="col" width="30%" class="vertical-spacing">Item</th>
                            <th scope="col" width="8%" class="vertical-spacing">Qty</th>
                            <th scope="col" width="9%" class="vertical-spacing">MRP</th>
                            <th scope="col" width="9%" class="vertical-spacing">Rate</th>
                            <th scope="col" width="12%" class="vertical-spacing">Amount</th>
                        </tr>
                        @elseif($preferences->preference_hsn_invoice == 1)
                        <tr>
                            <th scope="col" width="10%" class="vertical-spacing">HSN</th>
                            <th scope="col" width="30%" class="vertical-spacing">Item</th>
                            <th scope="col" width="10%" class="vertical-spacing">Qty</th>
                            <th scope="col" width="10%" class="vertical-spacing">Rate</th>
                            <th scope="col" width="12%" class="vertical-spacing">Amount</th>
                        </tr>
                        @elseif($preferences->preference_mrp_invoice == 1)
                        <tr>
                            <th scope="col" width="30%" class="vertical-spacing">Item</th>
                            <th scope="col" width="10%" class="vertical-spacing">Qty</th>
                            <th scope="col" width="10%" class="vertical-spacing">MRP</th>
                            <th scope="col" width="10%" class="vertical-spacing">Rate</th>
                            <th scope="col" width="12%" class="vertical-spacing">Amount</th>
                        </tr>
                        @else
                        <tr>
                            <th scope="col" width="40%" class="vertical-spacing">Item</th>
                            <th scope="col" width="10%" class="vertical-spacing">Qty</th>
                            <th scope="col" width="10%" class="vertical-spacing">Rate</th>
                            <th scope="col" width="12%" class="vertical-spacing">Amount</th>
                        </tr>
                        @endif

                    </thead>
                    <tbody class="dotted-border">

                        @php
                        $mrp_total = 0;
                        $sub_total = 0;
                        $diff_mrp_n_rate = 0;
                        $isRefund = 0;
                        @endphp
                        @php $total_tax = 0.00; @endphp
                        @foreach($item_list as $item)

                        <!-- total tax calculation -->
                        @if(isset($item->tax2))
                        @php $total_tax = $total_tax + floatval(number_format($item->tax1->amount, 2)) +
                            floatval(number_format($item->tax2->amount, 2)); @endphp
                        @else
                            @if(isset($item->tax1))
                                @php $total_tax = $total_tax + floatval(number_format($item->tax1->amount, 2)); @endphp
                            @endif
                        @endif
                        <!-- total tax calculation -->

                        @if($item->isRefund == 1)
                            @php
                                $sub_total += ((float)$item->quantity * (float)$item->rate)*(-1);
                                $isRefund=1;
                            @endphp
                        @elseif($item->isRefund == 0)
                            @php
                                $sub_total += ((float)$item->quantity * (float)$item->rate);
                            @endphp
                        @endif


                        @if( ($preferences->preference_mrp_invoice == 1) && ($preferences->preference_hsn_invoice == 1))

                        @if($item->mrp != 'NA')
                            @php $mrp_total = $mrp_total + (float)$item->quantity * (float)$item->mrp; @endphp
                        @else
                            @php $mrp_total = $mrp_total + (float)$item->quantity * (float)$item->rate; @endphp
                        @endif

                        @php $diff_mrp_n_rate = $mrp_total - $grand_total; @endphp

                        
                        <tr>
                            <td>{{$item->hsn}}</td>
                            <td>{{$item->itemName}}</td>
                            <td>{{$item->quantity}}</td>
                            <td>{{$currency}} {{$item->mrp}}</td>
                            <td>{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($item->rate,$decimal_separator)}}</td>
                            @if($item->isRefund == 1)
                                <td>&ndash; {{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber((-1)*$item->amount,$decimal_separator)}}</td>
                            @else
                            <td>{{$currency}} {{ \App\Helpers\BillingHelpher::convertPriceToNumber($item->amount,$decimal_separator)}}</td>
                            @endif
                        </tr>

                        @elseif($preferences->preference_mrp_invoice == 1)


                        @if($item->mrp != 'NA')
                            @php $mrp_total = $mrp_total + (float)$item->quantity * (float)$item->mrp; @endphp
                        @else
                            @php $mrp_total = $mrp_total + (float)$item->quantity * (float)$item->rate; @endphp
                        @endif

                        @php $diff_mrp_n_rate = $mrp_total - $grand_total; @endphp

                        <tr>
                            <td>{{$item->itemName}}</td>
                            <td>{{$item->quantity}}</td>
                            <td>{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($item->mrp,$decimal_separator)}}</td>
                            <td>{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($item->rate,$decimal_separator)}}</td>
                            @if($item->isRefund == 1)
                                <td>&ndash; {{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber((-1)*$item->amount,$decimal_separator)}}</td>
                            @else
                                <td>{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($item->amount,$decimal_separator)}}</td>
                            @endif
                        </tr>
                        @elseif($preferences->preference_hsn_invoice == 1)
                        <tr>
                            <td>{{$item->hsn}}</td>
                            <td>{{$item->itemName}}</td>
                            <td>{{$item->quantity}}</td>
                            <td>{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($item->rate,$decimal_separator)}}</td>
                            @if($item->isRefund == 1)
                                <td>&ndash; {{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber((-1)*$item->amount,$decimal_separator)}}</td>
                            @else
                                <td>{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($item->amount,$decimal_separator)}}</td>
                            @endif
                        </tr>
                        @else
                        <tr>
                            <td>{{$item->itemName}}</td>
                            <td>{{$item->quantity}}</td>
                            <td>{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($item->rate,$decimal_separator)}}</td>
                            @if($item->isRefund == 1)
                                <td>&ndash; {{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber((-1)*$item->amount,$decimal_separator)}}</td>
                            @else
                                <td>{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($item->amount,$decimal_separator)}}</td>
                            @endif
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-2 col-12 dotted-border">
                </div>
                @if( ($preferences->preference_mrp_invoice == 1) && ($preferences->preference_hsn_invoice == 1) )
                <table class="topTable">
                    <tbody>
                        @if($preferences->preference_mrp_invoice == 1)
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-start" width="18%">Total MRP:</td>
                            <td width="14%">{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($mrp_total,$decimal_separator)}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-start" width="18%">Sub Total:</td>
                            @if($sub_total<0)
                            <td width="14%">&ndash; {{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber((-1)*$sub_total,$decimal_separator)}}</td>
                            @else
                            <td width="14%">{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($sub_total,$decimal_separator)}}</td>
                            @endif
                        </tr>

                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-start" width="18%">Grand Total:</td>
                            @if($grand_total<0)
                            <td width="14%">&ndash; {{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber((-1)*$grand_total,$decimal_separator)}}</td>
                            @else
                            <td width="14%">{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($grand_total,$decimal_separator)}}</td>
                            @endif
                        </tr>
                        @if( ($preferences->preference_mrp_invoice == 1) && ($diff_mrp_n_rate!=0) && ($isRefund==0)  )
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td width="18%" class="text-start">You have saved</td>
                            <td width="14%" >{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($diff_mrp_n_rate,$decimal_separator)}} </td>
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
                            <td class="text-start" width="18%">Total MRP:</td>
                            <td width="16%">{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($mrp_total,$decimal_separator)}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-start" width="18%">Sub Total:</td>
                            @if($sub_total<0)
                            <td width="16%" style="white-space: nowrap;">&ndash; {{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber((-1)*$sub_total,$decimal_separator)}}</td>
                            @else
                            <td width="16%" style="white-space: nowrap;">{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($sub_total,$decimal_separator)}}</td>
                            @endif
                        </tr>

                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-start" width="18%">Grand Total:</td>
                            @if($grand_total<0)
                            <td width="16%" style="white-space: nowrap;">&ndash; {{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber((-1)*$grand_total,$decimal_separator)}}</td>
                            @else
                            <td width="16%" style="white-space: nowrap;">{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($grand_total,$decimal_separator)}}</td>
                            @endif
                        </tr>
                        @if( ($preferences->preference_mrp_invoice == 1) && ($diff_mrp_n_rate!=0) && ($isRefund==0)  )
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-start" width="18%">You have saved</td>
                            <td  width="16%">{{$currency}} {{\App\Helpers\BillingHelpher::convertPriceToNumber($diff_mrp_n_rate,$decimal_separator)}} </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                @endif
            </div>
            <div class="col-12"><span>Tax Details</span></div>
            <div class="col-12 dotted-border">
            </div>
            <div class="col-12">
                <table class="topTable1">
                    <tr>
                        <td class="left">Total Taxable Value</td>
                        <td  width="12%" class="left" >{{$currency}} {{$totalTaxAmount}}</td>
                    </tr>

                    @foreach($taxGroups as $taxType => $taxDetails)
                        @foreach($taxDetails as $percent => $tax)
                            <tr>
                                <td class="left">{{ $tax['taxName'] }} ({{ $percent }}%)</td>
                                <!-- <td width="14%" class="left">{{ number_format($tax['totalAmount'], 2) }}</td> -->
                                <td width="12%" class="left">{{$currency}} {{ number_format($tax['totalTax'], 2) }}</td>
                            </tr>
                        @endforeach
                    @endforeach


                </table>
            </div>
            <div class="col-12 dotted-border">
            </div>
            <div class="col-12 text-center">
                <span>Contact Number: {{ $mobile_number }}</span><br>
                <span>It is a computer generated invoice</span>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>

</body>

</html>
