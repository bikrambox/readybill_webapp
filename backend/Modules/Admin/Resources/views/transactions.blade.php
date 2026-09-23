@extends('admin::layouts.admin')
@section('content')

    <div class="pagetitle">
        <h1>Transaction List</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home">Home</a></li>
                <li class="breadcrumb-item active">Transaction List</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">


            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <div class="row mt-4">

                            <div class="container d-flex justify-content-center">
                                <div class="col-md-6">
                                    <div class="row text-center">
                                        <div class="col-md-6">
                                            <label for="date_from" class="form-label fw-bold">From Date</label>
                                            <input type="text" id="date_from" class="form-control" placeholder="From Date">
                                            <div id="date_from_error" class="text-danger mt-1" style="font-size: 0.875em;"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="date_to" class="form-label fw-bold">To Date</label>
                                            <input type="text" id="date_to" class="form-control" placeholder="To Date">
                                            <div id="date_to_error" class="text-danger mt-1" style="font-size: 0.875em;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 text-center my-2">
                                <button type="button" class="btn btn-danger btn-sm" id="deleteTransaction"><i
                                        class="bi bi-trash"></i> Delete Transactions</button>
                            </div>

                            <div class="col-md-4 col-sm-12 my-2 d-flex justify-content-md-start justify-content-center">
                                <label class="pt-1" for="">Show</label>
                                <select id="transaction_entity-select" class="form-select mx-2" style="width:30%">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <label class="pt-1 totalTransactionRecords" for="">Entries</label>
                            </div>

                            <div class="col-md-4">
                            </div>

                            <div class="col-md-4 col-12 my-2 d-flex justify-content-md-end justify-content-center">

                                <select id="transaction_filter-select" class="mx-2">
                                    <option value="invoice_number">Inoive Number</option>
                                    <option value="date">Date</option>
                                    <option value="user">User</option>
                                    <option value="total">Total</option>
                                </select>
                                <input type="text" id="transaction_dataTable_search" class="form-control"
                                    placeholder="Search..." />
                            </div>

                            <div class="col-12 table-responsive">
                                <!-- Table with stripped rows -->
                                <table class="table table-responsive-md table-responsive-lg table-responsive-xl"
                                    id="transactionTable">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Products</th>
                                            <th>Total</th>
                                            <th>User</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <!-- End Table with stripped rows -->
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    @include("coreweb::modals/transaction")


    @include('coreweb::modals.confirmation', [
        'heading' => 'Are you sure you want to do the action ?',
        'subHeading' => 'Once confirmed, all transactions between the selected dates will be deleted.',
        'buttonText' => 'Click Confirm to proceed'
    ])

@endsection


@section('scripts')
    @parent
    <!-- Include parent scripts -->
    <script>
        $(document).ready(function () {
            // Set up the CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            $("#date_from, #date_to").datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                changeYear: true,
                onSelect: function () {
                    transactionTable.draw();
                }
            });


            let user_id = "{{$user_id}}";

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
                        url: '{{ route("admin.shop.transactions.data") }}',
                        type: 'POST',
                        data: function (d) {
                            d.filter_option = $('#transaction_filter-select').val();
                            d.user_id = user_id;

                            // Add date range parameters
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
                        error: function (error) {
                            console.error('Error fetching data:', error);
                        }
                    },
                    columns: [
                        {
                            data: 'invoice_number'
                        },
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
                                    // return '− ₹' + (-1) * data.total_price
                                    return '− ' + currency((-1) * data.total_price)
                                }
                                else {
                                    // return `₹ ${data.total_price}`;
                                    return currency(data.total_price);
                                }
                            }
                        },
                        {
                            data: 'user_name'
                        },
                        {
                            data: null,
                            data: null,
                            render: function (data, type, row) {
                                return convertDateFormats(data.created_at)
                            }
                        }
                    ],
                    pagingType: 'full_numbers',
                    order: [
                        [4, 'desc']
                    ],
                    pageLength: 100,
                    language: {
                        emptyTable: 'Currently there are no transactions'
                    },

                    drawCallback: function (settings) {

                
                        var api = this.api();
                        var rows = api.rows({ page: 'current' }).nodes();
                        var lastMonthYear = null;
                        var monthlyTotals = {};
                        var totalRecords = api.data().count();


                        // Show/hide delete button based on record count
                        if (totalRecords > 0) {
                            $("#deleteTransaction").show();
                        } else {
                            $("#deleteTransaction").hide();
                        }

                        // Calculate monthly totals
                        api.rows({ page: 'current' }).data().each(function (data) {
                            var currentMonth = new Date(data.created_at).getMonth() + 1;
                            var currentYear = new Date(data.created_at).getFullYear();
                            var currentMonthYear = `${currentMonth}-${currentYear}`;

                            if (!monthlyTotals[currentMonthYear]) {
                                monthlyTotals[currentMonthYear] = 0;
                            }
                            monthlyTotals[currentMonthYear] += parseFloat(data.total_price);
                        });

                        // Add month headers with date at start and total at end
                        api.rows({ page: 'current' }).data().each(function (data, i) {
                            var currentMonth = new Date(data.created_at).getMonth() + 1;
                            var currentYear = new Date(data.created_at).getFullYear();
                            var currentMonthYear = `${currentMonth}-${currentYear}`;

                            if (lastMonthYear !== currentMonthYear) {
                                var monthName = getMonthName(currentMonth);
                                var total = monthlyTotals[currentMonthYear].toFixed(2);
                                var headerText = `${monthName}, ${currentYear}`;

                                if (total < 0) {
                                    total = '−' + (((-1) * total));
                                }
                                else {
                                    total = (total);
                                }

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
                            // Show modal or perform any action you want here
                            // For example, to show a modal with item details:
                            showTransactionalDataByID(user_id, data.id);
                        });
                    },
                });

                // Hide the default search bar and length menu
                $("#transaction_entity-select").val(100);
                $('.dataTables_filter').hide();
                $('.dataTables_length').hide();

                // Filter and search event listeners
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


            // Add event listeners for date filters
            $('#date_from, #date_to').on('change', function () {

                const dateFrom = $("#date_from").val();
                const dateTo = $("#date_to").val();

                // Clear previous error messages
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

                transactionTable.draw();
            });

            // Modify the existing filter select change handler
            $('#transaction_filter-select').on('change', function () {
                const filterOption = $(this).val();
                let searchPlaceholder = 'Search...';
                if (filterOption === 'date') {
                    searchPlaceholder = 'DD/MM/YYYY';
                }
                $('#transaction_dataTable_search').attr('placeholder', searchPlaceholder).val('');
                // Clear date filters when changing filter type if not date
                if (filterOption !== 'date') {
                    $('#date_from, #date_to').val('');
                }
                transactionTable.search('').draw();
            });



            function showTransactionalDataByID(user_id, transaction_id) {

                $.ajax({
                    type: 'GET',
                    url: base_url + '/admin/shop/transaction/' + user_id + '/' + transaction_id,
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
                            console.log('response', response);

                            $("#billing_id").val(response.data.id);

                            $(".generateInvoice").attr('data-id', response.data.id);

                            $("#u_invoic_number").val(response.data.invoice_number);
                            // var item_list = JSON.parse(response.data.item_list);
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
                                    amountHtml = '</td><td>' + currency(itemList[i].amount);
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
                                    '<tr id="e_grand_total_row"><td></td><td>Grand Total</td><td></td><td></td><td> −' +
                                    currency(total) + '</td><td></td></tr>');
                            }
                            else {
                                $('#e_sale_itemList').append(
                                    '<tr id="e_grand_total_row"><td></td><td>Grand Total</td><td></td><td></td><td>' +
                                    currency(grand_total) + '</td><td></td></tr>');
                            }


                            // $('#e_sale_itemList').append(
                            //     '<tr id="e_grand_total_row"><td></td><td>Grand Total</td><td></td><td></td><td> ₹' +
                            //     grand_total + '</td><td></td></tr>');


                            $('.overlay').hide();
                            $("#transactionalDetails").modal('show');
                        }
                    },
                    error: function (error) {
                        console.log('Error', error);

                    }
                });

            }

            function currency(price) {
                return price;
            }


            $("#deleteTransaction").click(function () {
                const dateFrom = $("#date_from").val();
                const dateTo = $("#date_to").val();

                // Clear previous error messages
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

                $("#confirmationModal").modal("show");

                // Handle confirmation button click
                $(".confrimModalButton").off('click').on('click', function () {
                    deleteTransactionsByDateRange(dateFrom, dateTo);
                });
            });


            function deleteTransactionsByDateRange(dateFrom, dateTo) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route("admin.shop.delete.transactions") }}', // You'll need to create this route
                    data: {
                        date_from: dateFrom,
                        date_to: dateTo,
                        user_id: "{{$user_id}}"
                    },
                    beforeSend: function () {
                        $('.overlay').show();
                        $("#confirmationModal").modal("hide");
                    },
                    success: function (response) {
                        $('.overlay').hide();
                        if (response.success) {
                            // Refresh the table after successful deletion
                            transactionTable.ajax.reload();
                            alert(response.message || "Transactions deleted successfully!");
                        } else {
                            alert(response.message || "Failed to delete transactions.");
                        }
                    },
                    error: function (error) {
                        $('.overlay').hide();
                        console.error('Error deleting transactions:', error);
                        alert("An error occurred while deleting transactions.");
                    }
                });
            }

        });
    </script>

@endsection