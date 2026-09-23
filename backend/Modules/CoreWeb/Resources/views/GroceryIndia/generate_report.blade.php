@extends('coreweb::layouts.groceryIndia')
@section('title', "{{ __('generate_report_page.Generate Report') }}")
@section('content')
        <div class="pagetitle">
            <h1>{{ __('generate_report_page.Generate Report') }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('generate_report_page.Generate Report') }}</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-xl-8">
                    @include('coreweb::components/error', [
                        'heading' => __('common.Subscription Alert'),
                        'modalIdAttribute' => 'subscripitonErrorId',
                    ])
                </div>
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="row mt-4">

                                <div class="container d-flex justify-content-center isAdminSection">
                                    <div class="col-md-6">
                                        <div class="row text-center">
                                            <div class="col-md-6">
                                                <label for="date_from" class="form-label fw-bold">{{ __('generate_report_page.From Date') }}</label>
                                                <input type="text" id="date_from" class="form-control" placeholder="From Date">
                                                <div id="date_from_error" class="text-danger mt-1" style="font-size: 0.875em;"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="date_to" class="form-label fw-bold">{{ __('generate_report_page.To Date') }}</label>
                                                <input type="text" id="date_to" class="form-control" placeholder="To Date">
                                                <div id="date_to_error" class="text-danger mt-1" style="font-size: 0.875em;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 text-center my-2 isAdminSection">
                                    <button type="button" class="btn btn-success btn-sm my-2 report-btn" data-report-type="pdf">
                                        {{ __('generate_report_page.Request Report (PDF)') }}
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm report-btn" data-report-type="excel">
                                        {{ __('generate_report_page.Request Report (Excel)') }}
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm my-2 report-btn" data-report-type="csv">
                                        {{ __('generate_report_page.Request Report (CSV)') }}
                                    </button>
                                </div>


                                <!-- Downloaded Report Section -->
                                 <div class="col-12 table-responsive">
                                    <table class="table" id="reportsTable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('common.SL.No') }}</th>
                                                <th>{{ __('common.Report Type') }}</th>
                                                <th>{{ __('common.Date Range') }}</th>
                                                <th>{{ __('common.Request Date') }}</th>
                                                <th>{{ __('common.Status') }}</th>
                                                <th>{{ __('common.Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Downloaded Report Section -->


                                <div class="col-12">
                                    <hr>
                                </div>


                                <div class="col-md-4 col-sm-12 my-2 d-flex justify-content-md-start justify-content-center">
                                    <label class="pt-1" for="">{{ __('common.Show') }}</label>
                                    <select id="transaction_entity-select" class="form-select mx-2" style="width:30%">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <label class="pt-1 totalTransactionRecords" for="">{{ __('common.Entries') }}</label>
                                </div>

                                <div class="col-md-4">
                                </div>

                                <div class="col-md-4 col-12 my-2 d-flex justify-content-md-end justify-content-center">
                                    <!-- <select id="transaction_filter-select" class="mx-2">
                                        <option value="invoice_number">Invoice Number</option>
                                        <option value="date">Date</option>
                                        <option value="user">User</option>
                                        <option value="total">Total</option>
                                    </select> -->
                                    <input type="text" id="transaction_dataTable_search" class="form-control" placeholder="{{ __('common.Search') }}..." />
                                </div>

                                <div class="col-12 table-responsive">
                                    <table class="table table-responsive-md table-responsive-lg table-responsive-xl" id="transactionTable" style="table-layout: fixed;">
                                        <thead>
                                            <tr>
                                                <th style="width:10%">{{ __('common.Invoice') }} #</th>
                                                <th>{{ __('common.Products') }}</th>
                                                <th style="width:10%">{{ __('common.Total') }}</th>
                                                <th style="width:10%">{{ __('common.User') }}</th>
                                                <th style="width:20%">{{ __('common.Date') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @include("coreweb::modals/transaction")

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function () {
            // Attach click handlers to report buttons
            $('.report-btn').on('click', function () {
                const reportType = $(this).data('report-type');
                requestReport(reportType);
            });

            var transactionTable;

            function transactiondataList() {
                function getMonthName(monthNumber) {
                    return new Date(2024, monthNumber - 1, 1).toLocaleString('default', {
                        month: 'long'
                    });
                }

                transactionTable = $('#transactionTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: `${grocery_india_api_url}transaction-report`,
                        type: 'POST',
                        headers: {
                            'Authorization': `Bearer ${localStorage.getItem('token')}`
                        },
                        data: function (d) {
                            d.filter_option = $('#transaction_filter-select').val();
                            d.date_from = $('#date_from').val();
                            d.date_to = $('#date_to').val();
                        },
                        dataSrc: function (response) {
                            originalData = response.data;
                            $(".totalTransactionRecords").html(
                                `rows of total <strong>${response.recordsFiltered} records</strong>`
                            );
                            return response.data;
                        },
                        error: function (xhr) {
                            console.error('Error fetching data:', xhr);
                            let errorMessage = 'An error occurred while fetching transactions.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            $.toast({
                                heading: 'Error',
                                text: errorMessage,
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#ff0000'
                            });
                        }
                    },
                    columns: [
                        { data: 'invoice_number' },
                        {
                            data: null,
                            render: function (data) {
                                const itemList = JSON.parse(data.item_list);
                                return itemList.map(item =>
                                    `${item.itemName} ${item.quantity} ${item.selectedUnit}`)
                                    .join(', ');
                            }
                        },
                        {
                            data: null,
                            render: function (data) {
                                if (data.total_price < 0) {
                                    return '− ' + currency((-1) * data.total_price);
                                }
                                return currency(data.total_price);
                            }
                        },
                        { data: 'user_name' },
                        {
                            data: null,
                            render: function (data) {
                                return convertDateFormats(data.created_at);
                            }
                        }
                    ],
                    pagingType: 'full_numbers',
                    order: [[4, 'desc']],
                    pageLength: 100,
                    language: {
                        emptyTable: @json(__('common.Currently there are no transactions'))
                    },
                    drawCallback: function (settings) {
                        var api = this.api();
                        var rows = api.rows({ page: 'current' }).nodes();
                        var lastMonthYear = null;
                        var monthlyTotals = {};

                        api.rows({ page: 'current' }).data().each(function (data) {
                            var currentMonth = new Date(data.created_at).getMonth() + 1;
                            var currentYear = new Date(data.created_at).getFullYear();
                            var currentMonthYear = `${currentMonth}-${currentYear}`;

                            if (!monthlyTotals[currentMonthYear]) {
                                monthlyTotals[currentMonthYear] = 0;
                            }
                            monthlyTotals[currentMonthYear] += parseFloat(data.total_price);
                        });

                        api.rows({ page: 'current' }).data().each(function (data, i) {
                            var currentMonth = new Date(data.created_at).getMonth() + 1;
                            var currentYear = new Date(data.created_at).getFullYear();
                            var currentMonthYear = `${currentMonth}-${currentYear}`;

                            if (lastMonthYear !== currentMonthYear) {
                                var monthName = getMonthName(currentMonth);
                                var total = monthlyTotals[currentMonthYear].toFixed(2);
                                var headerText = `${monthName}, ${currentYear}`;
                                total = total < 0 ? '−' + currency((-1) * total) : currency(total);
                                $(rows).eq(i).before(
                                    `<tr class="month-header fw-bold text-start table-active"><td colspan="1">${headerText}</td>
                                    <td colspan="3"></td>
                                    <td class="text-center" colspan="1">${total}</td></tr>`
                                );
                                lastMonthYear = currentMonthYear;
                            }
                        });
                    },
                    rowCallback: function (row, data) {
                        $(row).on('click', function () {
                            showTransactionalDataByID(data.id);
                        });
                    },
                });

                $("#transaction_entity-select").val(100);
                $('.dataTables_filter').hide();
                $('.dataTables_length').hide();

                $('#transaction_filter-select').on('change', function () {
                    const filterOption = $(this).val();
                    const searchPlaceholder = filterOption === 'date' ? 'DD/MM/YYYY' : 'Search...';
                    $('#transaction_dataTable_search').attr('placeholder', searchPlaceholder).val('');
                    transactionTable.search('').draw();
                });

                $('#transaction_dataTable_search').on('keyup', function () {
                    const searchValue = $(this).val();
                    transactionTable.search(searchValue).draw();
                });

                $('#transaction_entity-select').on('change', function () {
                    const pageLength = $(this).val();
                    transactionTable.page.len(pageLength).draw();
                });
            }

            transactiondataList();

            transactionTable.on('preXhr.dt', function () {
                $(".overlay").show();
            });

            transactionTable.on('xhr.dt', function () {
                $(".overlay").hide();
            });

            function showTransactionalDataByID(id) {
                $.ajax({
                    type: 'GET',
                    url: grocery_india_api_url + 'transaction/' + id,
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $('.overlay').show();
                    },
                    success: function (response) {
                        if (response) {
                            $("#billing_id").val(response.data.id);
                            $(".generateInvoice").attr('data-id', response.data.id);
                            $("#u_invoic_number").val(response.data.invoice_number);
                            itemList = JSON.parse(response.data.item_list);
                            $('#e_sale_itemList tbody tr').remove();
                            grand_total = 0;
                            var status = 'new';
                            var amount = 0;
                            var amountHtml = '';

                            for (var i = 0; i < itemList.length; i++) {
                                let rowClass = '';
                                if (itemList[i].isRefund == 1) {
                                    status = "Refund";
                                    amountHtml = '</td><td> − ' + currency((-1) * itemList[i].amount);
                                    amount = itemList[i].amount;
                                    $('#e_sale_itemList').append('<tr class="refund-row"><td>' + (i + 1) +
                                        '</td><td>' + itemList[i].itemName + '</td><td>' +
                                        itemList[i].quantity + " " + itemList[i].selectedUnit +
                                        '</td><td>' +
                                        currency(itemList[i].rate) +
                                        amountHtml +
                                        '</td><td><span class="badge bg-danger text-white">' + status +
                                        '</span>' +
                                        '</td></tr>'
                                    );
                                } else {
                                    status = 'Sold';
                                    amountHtml = '</td><td>' + currency(itemList[i].amount);
                                    amount = itemList[i].amount;
                                    $('#e_sale_itemList').append('<tr><td>' + (i + 1) +
                                        '</td><td>' + itemList[i].itemName + '</td><td>' +
                                        itemList[i].quantity + " " + itemList[i].selectedUnit +
                                        '</td><td> ₹' +
                                        currency(itemList[i].rate) +
                                        amountHtml +
                                        '</td><td><span class="badge bg-info text-dark">' + status +
                                        '</span>' +
                                        '</td></tr>'
                                    );
                                }
                                grand_total = grand_total + parseFloat(amount);
                            }

                            if (grand_total < 0) {
                                var total = (-1) * grand_total;
                                $('#e_sale_itemList').append(
                                    '<tr id="e_grand_total_row"><td></td><td>Grand Total</td><td></td><td></td><td> − ' +
                                    currency(total) + '</td><td></td></tr>');
                            } else {
                                $('#e_sale_itemList').append(
                                    '<tr id="e_grand_total_row"><td></td><td>Grand Total</td><td></td><td></td><td>' +
                                    currency(grand_total) + '</td><td></td></tr>');
                            }

                            $('.overlay').hide();
                            $("#transactionalDetails").modal('show');
                        }
                    },
                    error: function (error) {
                        console.log('Error', error);
                    }
                });
            }

            $(document).on('click', '.u_deleteItem', function () {
                var row = $(this).closest('tr');
                var index = row.index();
                var amount = itemList[index].amount;
                row.remove();
                itemList[index]['isDelete'] = 1;
                grand_total -= amount;
                $('#e_grand_total_row').remove();
                $('#e_sale_itemList').append(
                    '<tr id="e_grand_total_row"><td></td><td>Grand Total</td><td></td><td></td><td>' +
                    grand_total + '</td><td></td></tr>');
                console.log('itemList', itemList);
            });

            $(".generateInvoice").click(function (e) {
                e.preventDefault();
                var bill_id = $("#billing_id").val();
                var url = `/invoice/${bill_id}/`;
                window.open(url, '_blank');
            });

            $('#update-transaction').submit(function (e) {
                e.preventDefault();
                var formData = new FormData();
                formData.append('billing_id', $("#billing_id").val());
                itemList.forEach(function (item, index) {
                    formData.append('itemList[' + index + '][itemId]', item.itemId);
                    formData.append('itemList[' + index + '][itemName]', item.itemName);
                    formData.append('itemList[' + index + '][quantity]', item.quantity);
                    formData.append('itemList[' + index + '][rate]', item.rate);
                    formData.append('itemList[' + index + '][selectedUnit]', item.selectedUnit);
                    formData.append('itemList[' + index + '][amount]', item.amount);
                    formData.append('itemList[' + index + '][isDelete]', item.isDelete);
                });
                formData.append('grand_total', grand_total);

                $.ajax({
                    url: grocery_india_api_url + 'update-transaction',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".overlay").show();
                    },
                    success: function (response) {
                        if (response) {
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                            transactionTable.destroy();
                            transactiondataList();
                            $(".overlay").hide();
                            $("#transactionalDetails").modal('hide');
                        }
                    },
                    error: function (xhr, status, error) {
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);
                        handleErrors(error);
                    }
                });
            });

            $("#date_from, #date_to").datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                changeYear: true,
                maxDate: 0,
                onSelect: function (selectedDate) {
                    const $this = $(this);
                    const otherDateField = $this.attr('id') === 'date_from' ? $('#date_to') : $('#date_from');
                    const currentDate = $this.datepicker('getDate');

                    $("#date_from_error").text("");
                    $("#date_to_error").text("");

                    if (currentDate) {
                        const minDate = new Date(currentDate);
                        const maxDate = new Date(currentDate);
                        if ($this.attr('id') === 'date_from') {
                            maxDate.setMonth(maxDate.getMonth() + 6);
                            otherDateField.datepicker('option', 'minDate', currentDate);
                            otherDateField.datepicker('option', 'maxDate', maxDate > new Date() ? 0 : maxDate);
                        } else {
                            minDate.setMonth(minDate.getMonth() - 6);
                            otherDateField.datepicker('option', 'maxDate', currentDate);
                            otherDateField.datepicker('option', 'minDate', minDate);
                        }
                    }

                    const dateFrom = $("#date_from").datepicker('getDate');
                    const dateTo = $("#date_to").datepicker('getDate');

                    if (dateFrom && dateTo) {
                        const diffMonths = (dateTo.getFullYear() - dateFrom.getFullYear()) * 12 + (dateTo.getMonth() - dateFrom.getMonth());
                        if (diffMonths > 6 || (diffMonths === 6 && dateTo.getDate() > dateFrom.getDate())) {
                            $("#date_to_error").text(__('common.Date range cannot exceed 6 months'));
                            $this.val('');
                            return;
                        }
                        transactionTable.draw();
                    }
                }
            });

            $('#date_from, #date_to').on('change', function () {
                const dateFrom = $("#date_from").val();
                const dateTo = $("#date_to").val();

                $("#date_from_error").text("");
                $("#date_to_error").text("");

                if (!dateFrom || !dateTo) {
                    if (!dateFrom) {
                        $("#date_from_error").text("Please select From Date");
                    }
                    if (!dateTo) {
                        $("#date_to_error").text("Please select To Date");
                    }
                    return;
                }

                const fromDateObj = $.datepicker.parseDate('dd/mm/yy', dateFrom);
                const toDateObj = $.datepicker.parseDate('dd/mm/yy', dateTo);

                if (fromDateObj && toDateObj) {
                    const diffMonths = (toDateObj.getFullYear() - fromDateObj.getFullYear()) * 12 + (toDateObj.getMonth() - fromDateObj.getMonth());
                    if (diffMonths > 6 || (diffMonths === 6 && toDateObj.getDate() > fromDateObj.getDate())) {
                        $("#date_to_error").text(@json(__('common.Date range cannot exceed 6 months')));
                        $("#date_to").val('');
                        return;
                    }
                    if (toDateObj > new Date()) {
                        $("#date_to_error").text(@json(__('common.Future dates are not allowed')));
                        $("#date_to").val('');
                        return;
                    }
                    transactionTable.draw();
                }
            });


            // ----------------------------------------- REPORT SECTION -----------------------------------------

            function requestReport(reportType) {
                const dateFrom = $("#date_from").val();
                const dateTo = $("#date_to").val();
                const filterOption = $('#transaction_filter-select').val();
                const searchValue = $('#transaction_dataTable_search').val();

                if (!dateFrom || !dateTo) {
                    $.toast({
                        heading: 'Error',
                        text: !dateFrom ? @json(__('common.Please select From Date')) : @json(__('common.Please select To Date')),
                        icon: 'error',
                        loader: true,
                        position: 'top-right',
                        loaderBg: '#ff0000'
                    });
                    return;
                }

                $.ajax({
                    url: `${grocery_india_api_url}request-report`,
                    type: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    },
                    data: {
                        date_from: dateFrom,
                        date_to: dateTo,
                        filter_option: filterOption,
                        search_value: searchValue,
                        report_type: reportType
                    },
                    beforeSend: function () {
                        $(".overlay").show();
                    },
                    success: function (response) {

                        if(response.status =='success'){
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                        }
                        else if(response.status == 'queue'){
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                        }
                        reportsTable.ajax.reload();
                        $(".overlay").hide();
                    },
                    error: function (xhr,code) {
                        let errorMessage = 'An error occurred while requesting the report.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }



                        if(xhr.code == 400){
                            $.toast({
                                heading: 'Error',
                                text: errorMessage,
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#ff0000'
                            });
                        }
                        else{
                            $.toast({
                                heading: 'Error',
                                text: errorMessage,
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#ff0000'
                            });
                        }

                        $(".overlay").hide();
                    }
                });
            }


            function reInitateRequestReport(report_id) {

                $.ajax({
                    url: `${grocery_india_api_url}re-initiate/request-report/${report_id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    },
                    data: {
                    },
                    beforeSend: function () {
                        $(".overlay").show();
                    },
                    success: function (response) {

                        if(response.status =='success'){
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                        }
                        else if(response.status == 'queue'){
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                        }
                        reportsTable.ajax.reload();
                        $(".overlay").hide();
                    },
                    error: function (xhr) {
                        let errorMessage = 'An error occurred while requesting the report.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        $.toast({
                            heading: 'Error',
                            text: errorMessage,
                            icon: 'error',
                            loader: true,
                            position: 'top-right',
                            loaderBg: '#ff0000'
                        });
                        $(".overlay").hide();
                    }
                });
            }

                var reportsTable = $('#reportsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    lengthChange: false,
                    ajax: {
                        url: `${grocery_india_api_url}reports`,
                        type: 'POST',
                        headers: {
                            'Authorization': `Bearer ${localStorage.getItem('token')}`
                        },
                        dataSrc: function (response) {

                            console.log('response',response);


                            if(response.data.length>0){
                                $("#date_from").val(response.data[0].parameters.date_from);
                                $("#date_to").val(response.data[0].parameters.date_to);
                                  transactionTable.draw();
                            }

                            return response.data;

                        },
                        error: function (xhr) {
                            let errorMessage = 'An error occurred while fetching reports.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            $.toast({
                                heading: 'Error',
                                text: errorMessage,
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#ff0000'
                            });
                        }
                    },
                    columns: [
                        { data: 'slno' },
                        { data: 'report_type' },
                        { data: 'date_range' },
                        { data: 'created_at' },
                        // { data: 'statusMessage'},
                        {
                            data:null,

                            render: function(data){
                                if(data.status == 0){
                                    return `<span class="badge text-bg-primary">${data.statusMessage}</span>`;
                                }
                                else if(data.status == 1){
                                    return `<span class="badge text-bg-info">${data.statusMessage}</span>`;
                                }
                                else if(data.status == 2){
                                    return `<span class="badge text-bg-success">${data.statusMessage}</span>`;
                                }
                                else if(data.status == 3){
                                    return `<span class="badge text-bg-danger">${data.statusMessage}</span>`;
                                }
                            }
                        },
                        {
                            data: null,
                            render: function (data) {

                                if(data.status == 2){
                                    return `<button class="btn btn-primary btn-sm download-report" data-report-id="${data.id}" data-report-type="${data.report_type}">Download</button>`;
                                }
                                else if(data.status == 3){
                                    return `<button class="btn btn-danger btn-sm reinitiate-report" data-report-id="${data.id}">Retry</button>`;
                                }
                                else {
                                    return ``;
                                }

                            }
                        }
                    ],
                    order: [[3, 'desc']],
                    pageLength: 10,
                    language: {
                        emptyTable: @json(__('common.No reports available for download'))
                    }
                });

                $('#reportsTable').on('click', '.reinitiate-report', function () {
                    reInitateRequestReport($(this).data('report-id'));
                });


                $('#reportsTable').on('click', '.download-report', function () {

                    var reportId = $(this).data('report-id');
                    var url = `${grocery_india_api_url}reports/download/${reportId}`;
                    var token = localStorage.getItem('token');

                    $.ajax({
                        url: url,
                        type: 'GET',
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        success: function (response) {
                            var temporaryUrl = response.url;

                            var fileName = response.filename || 'transaction_report_' + reportId;
                            var contentType = response.contentType || 'application/octet-stream';
                            var api_secret_key = response.api_secret_key;

                            var extension = contentType.includes('pdf') ? '.pdf' :
                                            contentType.includes('excel') ? '.xlsx' :
                                            contentType.includes('csv') ? '.csv' : '.bin';

                            if (!fileName.includes('.')) {
                                fileName += extension;
                            }


                           // console.log('temporaryUrl',temporaryUrl);

                                // var link = document.createElement('a');
                                // link.href = temporaryUrl;
                                // link.download = fileName;
                                // document.body.appendChild(link);
                                // link.click();
                                // document.body.removeChild(link);

                                var xhr = new XMLHttpRequest();
                                xhr.open('GET', temporaryUrl, true);
                                xhr.responseType = 'blob';
                                xhr.setRequestHeader('X-API-Secret', api_secret_key);

                                xhr.onload = function() {
                                    if (xhr.status === 200) {
                                        var blob = xhr.response;
                                        var blobUrl = URL.createObjectURL(blob);
                                        var link = document.createElement('a');
                                        link.href = blobUrl;
                                        link.download = fileName;
                                        document.body.appendChild(link);
                                        link.click();
                                        document.body.removeChild(link);
                                        setTimeout(() => URL.revokeObjectURL(blobUrl), 100);
                                    }
                                };

                                xhr.onerror = function() {
                                    $('#error-message').text('Download failed.');
                                };

                                xhr.send();



                        },
                        error: function (xhr) {
                            var errorMessage = 'An error occurred while downloading the file.';
                            if (xhr.status === 404) {
                                errorMessage = 'File not found. Please try again later.';
                            } else if (xhr.status === 401) {
                                errorMessage = 'Unauthorized. Please log in again.';
                            }
                            $('#error-message').text(errorMessage);
                        }
                    });
                });

                //     $('#reportsTable').on('click', '.download-report', function () {
                //     var reportId = $(this).data('report-id');
                //     var url = `${grocery_india_api_url}reports/download/${reportId}`;
                //     var token = localStorage.getItem('token');

                //     console.log('1. Starting download for report:', reportId);

                //     $.ajax({
                //         url: url,
                //         type: 'GET',
                //         headers: {
                //             'Authorization': `Bearer ${token}`
                //         },
                //         success: function (response) {
                //             console.log('2. Initial response:', response);

                //             var temporaryUrl = response.url;
                //             var fileName = response.filename || 'transaction_report_' + reportId;
                //             var apiKey = response.api_secret_key;

                //             console.log('3. API Key:', apiKey);
                //             console.log('4. Temporary URL:', temporaryUrl);

                //             // Use fetch to download with custom headers
                //             fetch(temporaryUrl, {
                //                 method: 'GET',
                //                 headers: {
                //                     'X-API-Secret': apiKey
                //                 }
                //             })
                //             .then(response => {
                //                 console.log('5. Fetch response status:', response.status);
                //                 console.log('6. Fetch response ok:', response.ok);

                //                 if (!response.ok) {
                //                     return response.text().then(text => {
                //                         console.error('Error response:', text);
                //                         throw new Error('Download failed: ' + response.status);
                //                     });
                //                 }
                //                 return response.blob();
                //             })
                //             .then(blob => {
                //                 console.log('7. Blob received:', blob.size, 'bytes');

                //                 var blobUrl = URL.createObjectURL(blob);
                //                 var link = document.createElement('a');
                //                 link.href = blobUrl;
                //                 link.download = fileName;
                //                 document.body.appendChild(link);
                //                 link.click();
                //                 document.body.removeChild(link);

                //                 console.log('8. Download triggered');
                //                 setTimeout(() => URL.revokeObjectURL(blobUrl), 100);
                //             })
                //             .catch(error => {
                //                 console.error('9. Error caught:', error);
                //                 $('#error-message').text('Download failed: ' + error.message);
                //             });
                //         },
                //         error: function (xhr) {
                //             console.error('10. Initial AJAX error:', xhr.status, xhr.responseText);
                //             var errorMessage = 'An error occurred while downloading the file.';
                //             if (xhr.status === 404) {
                //                 errorMessage = 'File not found. Please try again later.';
                //             } else if (xhr.status === 401) {
                //                 errorMessage = 'Unauthorized. Please log in again.';
                //             }
                //             $('#error-message').text(errorMessage);
                //         }
                //     });
                // });

                reportsTable.on('preXhr.dt', function () {
                    $(".overlay").show();
                });

                reportsTable.on('xhr.dt', function () {
                    $(".overlay").hide();
                });


            // ----------------------------------------- REPORT SECTION -----------------------------------------

        });
    </script>
@endsection