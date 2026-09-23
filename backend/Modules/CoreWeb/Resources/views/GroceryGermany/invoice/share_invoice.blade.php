<!DOCTYPE html>
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

        /* A4 dimensions for large screens and print */
        @media screen and (min-width: 992px), print {
            body {
                width: 210mm;
                min-height: 297mm;
                margin: 0 auto;
                padding: 10mm;
                box-sizing: border-box;
                font-family: 'Courier New', 'Arial', monospace;
                font-size: 12pt;
                background: #fff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            .container {
                max-width: 190mm;
                margin: 0 auto;
            }
        }

        /* Screen styles for smaller screens */
        @media screen and (max-width: 991.98px) {
            body {
                font-family: 'Courier New', 'Arial', monospace;
                font-size: 14px;
                padding: 1rem;
                min-height: 100vh;
                margin: 0;
            }
            .container {
                max-width: 100%;
                margin: 0 auto;
            }
        }

        /* Logo size */
        .logo-img {
            width: 180px;
            height: 80px;
            /* object-fit: contain; */
        }

        /* General styles */
        .dotted-border {
            border-top: 2px dotted black;
            margin: 0.5rem 0;
        }

        .table {
            font-size: 0.85rem;
        }

        .table th, .table td {
            padding: 0.5rem;
            vertical-align: middle;
        }

        .text-center h3, .text-center h5 {
            margin: 0.25rem 0;
        }

        /* Responsive font sizes for smaller screens */
        @media (max-width: 576px) {
            body {
                font-size: 12px;
            }
            .table {
                font-size: 0.75rem;
            }
            .table th, .table td {
                padding: 0.25rem;
            }
        }

        /* Ensure table responsiveness */
        .table-responsive {
            overflow-x: auto;
        }

        /* Align totals to the right on larger screens */
        .totals-table td:last-child {
            text-align: right;
        }

        @media (max-width: 768px) {
            .totals-table td:last-child {
                text-align: left;
            }
        }



        .pdf-download {
            position: relative;
        }
        .pdf-btn {
            cursor: pointer;
            width: 28px;
            height: 28px;
        }

        @media print {
            .pdf-download { display: none !important; }
        }
    </style>
</head>
<body>
    <!-- Download as PDF button -->
    <div class="container">
    
        <div class="d-flex justify-content-end pdf-download mb-2">
            <div class="d-flex justify-content-end pdf-download mb-2" data-html2canvas-ignore="true">
                <img src="{{ asset('assets/img/icons/pdf.png') }}" alt="Download PDF" class="pdf-btn" id="downloadPdfBtn"
                    title="Download PDF" />
            </div>
        </div>


        <div class="row justify-content-center text-center mb-3">
            <div class="col-12 col-md-8">
                
                @if(!in_array($logo, ['NA', 'na', ''], true))
                    <img class="logo-img mb-2" src="{{$logo}}" alt="Business Logo" />
                @else
                    <h3 class="fw-bold">{{$business_name}}</h3>
                @endif
                <h5 class="mb-1">{{ __('invoice_page.Address') }}: {{$address}}</h5>
                @if(!in_array($gstin, ['NA', 'na', ''], true))
                    <h5 class="mb-1">{{ __('invoice_page.GST No.') }}: {{$gstin}}</h5>
                @endif
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-12">
                <div class="d-flex justify-content-between flex-wrap">
                    <span>{{ __('invoice_page.Invoice No.') }}: {{$invoice_number}}</span>
                    <span>{{ __('invoice_page.Date') }}: {{$invoice_date}}</span>
                </div>
                <div class="mt-1">
                    <span>{{ __('invoice_page.User') }}: {{$user_name}}</span>
                </div>
            </div>
        </div>

        <div class="dotted-border"></div>

        <div class="row mb-2">
            <div class="col-12 table-responsive">
                <table class="table table-borderless" id="sale_itemList">
                    <thead>
                        @if(($preferences->preference_mrp_invoice == 1) && ($preferences->preference_hsn_invoice == 1))
                            <tr>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.HSN') }}</th>
                                <th scope="col">{{ __('invoice_page.Item') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.Qty') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.MRP') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.Rate') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.Amount') }}</th>
                            </tr>
                        @elseif($preferences->preference_hsn_invoice == 1)
                            <tr>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.HSN') }}</th>
                                <th scope="col">{{ __('invoice_page.Item') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.Qty') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.Rate') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.Amount') }}</th>
                            </tr>
                        @elseif($preferences->preference_mrp_invoice == 1)
                            <tr>
                                <th scope="col">{{ __('invoice_page.Item') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.Qty') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.MRP') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.Rate') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.Amount') }}</th>
                            </tr>
                        @else
                            <tr>
                                <th scope="col">{{ __('invoice_page.Item') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.Qty') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.Rate') }}</th>
                                <th scope="col" class="text-nowrap">{{ __('invoice_page.Amount') }}</th>
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
                                    <td class="text-nowrap">{{$item->hsn}}</td>
                                    <td>{{$item->itemName}}</td>
                                    <td class="text-nowrap">{{$item->quantity}} {{$item->selectedUnit}}</td>
                                    <td class="text-nowrap">{{$currency}} {{$item->mrp}}</td>
                                    <td class="text-nowrap">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->rate, $decimal_separator)}}</td>
                                    <td class="text-nowrap">
                                        @if($item->isRefund == 1)
                                            – {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $item->amount, $decimal_separator)}}
                                        @else
                                            {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->amount, $decimal_separator)}}
                                        @endif
                                    </td>
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
                                    <td class="text-nowrap">{{$item->quantity}} {{$item->selectedUnit}}</td>
                                    <td class="text-nowrap">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->mrp, $decimal_separator)}}</td>
                                    <td class="text-nowrap">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->rate, $decimal_separator)}}</td>
                                    <td class="text-nowrap">
                                        @if($item->isRefund == 1)
                                            – {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $item->amount, $decimal_separator)}}
                                        @else
                                            {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->amount, $decimal_separator)}}
                                        @endif
                                    </td>
                                </tr>
                            @elseif($preferences->preference_hsn_invoice == 1)
                                <tr>
                                    <td class="text-nowrap">{{$item->hsn}}</td>
                                    <td>{{$item->itemName}}</td>
                                    <td class="text-nowrap">{{$item->quantity}} {{$item->selectedUnit}}</td>
                                    <td class="text-nowrap">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->rate, $decimal_separator)}}</td>
                                    <td class="text-nowrap">
                                        @if($item->isRefund == 1)
                                            – {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $item->amount, $decimal_separator)}}
                                        @else
                                            {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->amount, $decimal_separator)}}
                                        @endif
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td>{{$item->itemName}}</td>
                                    <td class="text-nowrap">{{$item->quantity}} {{$item->selectedUnit}}</td>
                                    <td class="text-nowrap">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->rate, $decimal_separator)}}</td>
                                    <td class="text-nowrap">
                                        @if($item->isRefund == 1)
                                            – {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $item->amount, $decimal_separator)}}
                                        @else
                                            {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($item->amount, $decimal_separator)}}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dotted-border"></div>

        <div class="row mb-2">
            <div class="col-12">
                <table class="table table-borderless totals-table">
                    <tbody>
                        @if($preferences->preference_mrp_invoice == 1)
                            <tr>
                                <td class="text-start">{{ __('invoice_page.Total MRP') }}:</td>
                                <td class="text-nowrap">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($mrp_total, $decimal_separator)}}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-start">{{ __('invoice_page.Sub Total') }}:</td>
                            <td class="text-nowrap">
                                @if($sub_total < 0)
                                    – {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $sub_total, $decimal_separator)}}
                                @else
                                    {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($sub_total, $decimal_separator)}}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-start">{{ __('invoice_page.Grand Total') }}:</td>
                            <td class="text-nowrap">
                                @if($grand_total < 0)
                                    – {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber((-1) * $grand_total, $decimal_separator)}}
                                @else
                                    {{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($grand_total, $decimal_separator)}}
                                @endif
                            </td>
                        </tr>
                        @if(($preferences->preference_mrp_invoice == 1) && ($diff_mrp_n_rate != 0) && ($isRefund == 0))
                            <tr>
                                <td class="text-start">{{ __('invoice_page.You have saved') }}:</td>
                                <td class="text-nowrap">{{$currency}} {{\Modules\GroceryGermany\Helpers\BillingHelpher::convertPriceToNumber($diff_mrp_n_rate, $decimal_separator)}}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-12">
                <span class="fw-bold">{{ __('invoice_page.Tax Details') }}</span>
            </div>
        </div>
        <div class="dotted-border"></div>
        <div class="row mb-2">
            <div class="col-12">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td class="text-start">{{ __('invoice_page.Total Taxable Value') }}</td>
                            <td class="text-nowrap">{{$currency}} {{$totalTaxAmount}}</td>
                        </tr>
                        @foreach($taxGroups as $taxType => $taxDetails)
                            @foreach($taxDetails as $percent => $tax)
                                <tr>
                                    <td class="text-start">{{ $tax['taxName'] }} ({{ $percent }}%)</td>
                                    <td class="text-nowrap">{{$currency}} {{ number_format($tax['totalTax'], 2) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="dotted-border"></div>
        <div class="row text-center">
            <div class="col-12">
                <span>{{ __('invoice_page.Contact Number') }}: {{$mobile_number}}</span><br>
                <span>{{ __('invoice_page.It is a computer generated invoice') }}</span>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>

    <!-- html2pdf.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>

        (function () {
            const btn = document.getElementById('downloadPdfBtn');
            if (!btn) return;

            btn.addEventListener('click', function () {
            const invoiceEl = document.querySelector('.container');
            if (!invoiceEl) return;

            const rawNo = "{{ $invoice_number ?? 'invoice' }}";
            const rawDate = "{{ $invoice_date ?? '' }}";

            // 1) Replace slashes with underscores: RB2025/101 -> RB2025_101
            // 2) Remove other illegal filename chars: \ / : " * ? < > |
            // 3) Collapse spaces to underscores
            const sanitize = (s) => String(s || '')
                .replace(/\//g, '_')                 // slash -> underscore
                .replace(/[\\/:\"*?<>|]+/g, '')      // illegal characters removed
                .replace(/\s+/g, '_');               // spaces -> underscore

            const safeNo = sanitize(rawNo);
            const safeDate = sanitize(rawDate);

            // If invoice number has a pattern like RB2025_101, also add separators between letters and digits for readability
            // e.g., RB2025_101 -> RB_2025_101
            const readableNo = safeNo
                .replace(/^([A-Za-z]+)(\d+)/, '$1_$2');  // RB2025 -> RB_2025
            // Final filename preference: just invoice number (readable variant)
            const filename = (readableNo || 'invoice') + '.pdf';

            const opt = {
                margin:       [10/25.4, 10/25.4, 10/25.4, 10/25.4],
                filename:     filename,               // e.g., RB_2025_101.pdf
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            const prevBoxShadow = document.body.style.boxShadow;
            document.body.style.boxShadow = 'none';

            html2pdf().set(opt).from(invoiceEl).save().finally(() => {
                document.body.style.boxShadow = prevBoxShadow;
            });
            });
        })();

    </script>

</body>
</html>