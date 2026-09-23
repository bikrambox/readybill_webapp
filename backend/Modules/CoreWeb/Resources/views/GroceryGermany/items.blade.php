@extends('coreweb::layouts.groceryGermany')
@section('title', "{{ __('inventory_page.View & Update Inventory') }}")
@section('content')


    <div class="pagetitle">
        <h1 class="inventoryHeading">{{ __('inventory_page.View & Update Inventory') }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                <li class="breadcrumb-item active inventoryLi">{{ __('inventory_page.View & Update Inventory') }}</li>
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
                        <!-- <h5 class="card-title">Items</h5> -->

                        <div class="row mt-4">

                            <div class="col-md-4 col-sm-12 my-2 d-flex justify-content-md-start justify-content-center">
                                <label class="pt-1" for="">{{ __('common.Show') }}</label>
                                <select id="entity-select" class="form-select mx-2" style="width:30%">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <label class="pt-1 totalItemsRecords" for="">{{ __('common.Entries') }}</label>
                            </div>

                            <div class="col-md-4">
                            </div>

                            <div class="col-md-4 col-12 my-2 d-flex justify-content-md-end justify-content-center">

                                <select id="filter-select" class="mx-2">
                                    <option value="item_name">{{ __('common.Item Name') }}</option>
                                    <option value="quantity">{{ __('inventory_page.Stock Quantity') }}</option>
                                    <option value="mrp">{{ __('inventory_page.MRP') }}</option>
                                    <option value="sale_price">{{ __('inventory_page.Rate') }}</option>
                                </select>

                                <input type="text" id="dataTable_search" class="form-control" placeholder="Search..." />

                            </div>

                            <div>
                                <button id="deleteSelected" class="btn btn-danger fw-bold text-uppercase" disabled>
                                    {{ __('common.Delete Selected') }}
                                </button>
                            </div>

                            <div class="col-12 table-responsive my-3">

                                <!-- Table with stripped rows -->
                                <table class="table table-responsive-md table-responsive-lg table-responsive-xl"
                                    id="dataList">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAll" class="checkbox-lg"></th>
                                            <th>{{ __('common.Item Name') }}</th>
                                            <th>{{ __('inventory_page.Stock') }}</th>
                                            <th>{{ __('inventory_page.MRP') }}</th>
                                            <th>{{ __('inventory_page.Rate') }}</th>
                                            <th>{{ __('inventory_page.Unit') }}</th>
                                            <th>{{ __('common.Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Table body content will be populated dynamically -->
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

@endsection

@include('coreweb::modals.confirmation', [
    'heading' => __('inventory_page.Are you sure you want to do the action ?'),
    'subHeading' => __('inventory_page.Once deleted, this item will be permanently deleted.'),
    'buttonText' => __('inventory_page.Click Confirm to proceed.')
]);

@include('coreweb::GroceryGermany/modals/updateItem');


@section('scripts')
    @parent
    <!-- Include parent scripts -->
    <script>
        $(document).ready(function () {
            // ----------------------------------- VIEW ITEMS ON TABLE --------------------------------------
            var dataTable;
            var table;
            var originalData;
            var stock_preference = 0;

            // function dataList() {

            //     // Check if the DataTable has already been initialized
            //     if ($.fn.DataTable.isDataTable('#dataList')) {
            //         // If it's already initialized, just destroy it before reinitializing
            //         $('#dataList').DataTable().destroy();
            //     }

            //     dataTable = $('#dataList').DataTable({
            //         processing: true,
            //         serverSide: true,
            //         // responsive: true,
            //         ajax: {
            //             url: grocery_germany_api_url + 'items',
            //             type: 'POST',
            //             headers: {
            //                 'Authorization': 'Bearer ' + localStorage.getItem('token')
            //             },
            //             data: function (d) {
            //                 // Include the selected option value in the request
            //                 d.filter_option = $('#filter-select').val();
            //             },
            //             dataSrc: function (response) {
            //                 console.log('response.data', response);
            //                 $(".totalItemsRecords").html("rows of total <strong>" + response
            //                     .recordsFiltered +
            //                     " records</strong>");
            //                 originalData = response.data;

            //                 stock_preference = response.preferences.preference_quantity;


            //                 return response.data;
            //             },
            //             error: function (error) {
            //                 // Handle error
            //             }
            //         },
            //         columns: [

            //             {
            //                 data: null,
            //                 orderable: false,
            //                 render: function (data, type, row) {
            //                     let td = `<td><input type="checkbox" data-id="${data.id}" class="rowCheckbox checkbox-lg"></td>`;
            //                     return td;
            //                 },
            //             },

            //             {
            //                 data: 'item_name'
            //             },
            //             {
            //                 data: 'quantity'
            //             },
            //             {
            //                 data: null,
            //                 render: function (data, type, row) {

            //                     if (data.mrp == '-' || data.mrp == '-') {
            //                         return '-';
            //                     }
            //                     return '₹ ' + data.mrp;
            //                 }
            //             },
            //             {
            //                 data: null,
            //                 render: function (data, type, row) {
            //                     if (data.sale_price == '-' || data.sale_price == '-') {
            //                         return '-';
            //                     }
            //                     return '₹ ' + data.sale_price;
            //                 }
            //             },
            //             {
            //                 data: null,
            //                 render: function (data, type, row) {
            //                     return data.short_unit;
            //                 }
            //             },

            //             {
            //                 data: null,
            //                 render: function (data, type, row) {
            //                     let buttons = '<div class="flex items-center justify-center">';

            //                     // buttons += `<button id="deleteSubUserModal" type="button" class="btn btn-danger fw-bold text-uppercase" data-staff_id="${data.id}" >Delete</button>`;
            //                     buttons += `<button data-id="${data.id}" type="button" class="btn btn-link viewDetails">View Details</button>`;
            //                     buttons += '</div>';

            //                     return buttons;
            //                 }
            //             }
            //         ],

            //         rowCallback: function (row, data) {
            //             // // $(row).on('click', function () {
            //             // //     // Show modal or perform any action you want here
            //             // //     // For example, to show a modal with item details:
            //             // //     if (isAdmin == 1) {
            //             // //         showItemDataByID(data.id);
            //             // //     }
            //             // // });
            //             // // Add CSS class if isAdmin is 1
            //             // if (isAdmin == 1) {
            //             //     $(row).addClass('admin-highlight');
            //             // }
            //             // if (parseFloat(data.min_stock_alert) >= parseFloat(data.quantity)) {
            //             //     $(row).addClass('inventoryMinStockAlert');
            //             // }

            //             // if (parseFloat(data.quantity) == 0) {
            //             //     $(row).addClass('inventoryMinStockAlert');
            //             // }

            //             // // Check if the current row is the last row
            //             // var totalRows = $('#dataList').DataTable().rows().count();

            //             // // Select all <td> elements except the last one
            //             // var allTdsExceptLast = $(row).find('td').not(':last');

            //             // // Disable pointer-events for the last <td> (the whole last cell, which includes the button)
            //             // $(row).find('td').last().css('pointer-events', 'none');

            //             // // Enable pointer-events for all other <td> elements
            //             // allTdsExceptLast.css('pointer-events', 'auto');

            //             // // // Make the button inside the last <td> clickable
            //             // // $(row).find('td').last().find('button').css('pointer-events', 'auto').on('click', function (event) {
            //             // //     event.stopPropagation(); // Prevent click event from bubbling up to the td
            //             // //     // Perform the button-specific action here
            //             // //     // For example, handle the button click action
            //             // //     console.log("Button clicked for staff ID:", data.staff_id);
            //             // //     // Your custom logic for button click

            //             // //     $("#confirmationModal").modal('show');

            //             // //     $(".confrimModalButton").data("id", data.id);

            //             // // });


            //             console.log('isAdmin',isAdmin);

            //             if (isAdmin === 0) {

            //                 $("#deleteSelected").addClass("d-none");

            //                 // Hide the last column for the current row
            //                 // $(row).find('td:last-child').remove();
            //                 // $(row).find('td:first-child').remove();

            //                 // Hide the last column header
            //                 // $('#dataList thead th:last-child').remove();
            //                 // $('#dataList tfoot th:last-child').remove();

            //                 // $('#dataList thead th:first-child').remove();
            //                 // $('#dataList tfoot th:first-child').remove();

            //                 // $("#dataList thead th:first-child").remove();
            //                 // $("#dataList thead th:last-child").remove();


            //                 // $("#dataList thead tr:first").find("th").eq(6).remove(); 
            //                 // $(row).find("td").eq(6).remove();


            //                 // $("#dataList thead tr:first").find("th").eq(0).remove(); 
            //                 $(row).find("td").eq(0).remove();

            //                 // $("thead tr:first").find("th").eq(6).remove(); 
            //                 // $(row).find("td").eq(6).remove();

            //             }


            //             if (stock_preference == 1) {
            //                 if (parseFloat(data.min_stock_alert) >= parseFloat(data.quantity)) {
            //                     $(row).addClass('inventoryMinStockAlert');
            //                 }

            //                 if (parseFloat(data.quantity) == 0) {
            //                     $(row).addClass('inventoryMinStockAlert');
            //                 }

            //             }


            //             console.log('stock_preference', stock_preference);

            //         },
            //         pagingType: 'full_numbers',
            //         order: [
            //             [0, 'asc']
            //         ],
            //         // lengthMenu: [10, 25, 50, 100], // Define available page lengths
            //         pageLength: 100, // Set the default page length to 100
            //         // searching: false,
            //         language: {
            //             "emptyTable": "Currently there are no items"
            //         },
            //     });

            //     $("#entity-select").val(100);
            //     // Hide the default search bar
            //     $('.dataTables_filter').hide();
            //     $('.dataTables_length').hide();

            //     // Initialize Select2 on the filter select dropdown

            //     // // Add event listeners for filtering
            //     $('#filter-select').on('change', function () {
            //         $("#dataTable_search").val("");
            //         var searchValue = $("#dataTable_search").val();
            //         table.search(searchValue).draw();

            //     });

            //     $("#dataTable_search").on('keyup', function () {
            //         var searchValue = $(this).val();
            //         table.search(searchValue).draw();
            //     });

            //     // Add event listener for entity filter
            //     $('#entity-select').on('change', function () {
            //         table.page.len($(this).val()).draw();
            //     });

            //     table = dataTable;
            // }

            function dataList() {

                console.log('isAdmin before DataTable initialization:', isAdmin); // Debug log

                // Check if the DataTable has already been initialized
                if ($.fn.DataTable.isDataTable('#dataList')) {
                    $('#dataList').DataTable().destroy();
                }

                dataTable = $('#dataList').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: grocery_germany_api_url + `items`,
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('token')
                        },
                        data: function (d) {
                            d.filter_option = $('#filter-select').val();
                        },
                        dataSrc: function (response) {
                            console.log('response.data', response);
                            $(".totalItemsRecords").html("rows of total <strong>" + response.recordsFiltered + " records</strong>");
                            originalData = response.data;
                            stock_preference = response.preferences.preference_quantity;


                            $('#deleteSelected').prop('disabled', true);


                            return response.data;
                        },
                        error: function (xhr, error, thrown) {
                            console.error('AJAX error:', xhr, error, thrown);
                            alert('Failed to load data. Please try again.');
                        }
                    },
                    columns: [
                        {
                            data: null,
                            orderable: false,
                            render: function (data, type, row) {
                                return `<td><input type="checkbox" data-id="${data.id}" class="rowCheckbox checkbox-lg"></td>`;
                            },
                            visible: true // Initially visible
                        },
                        {
                            data: 'item_name'
                        },
                        {
                            // data: 'quantity'
                            data:null,
                            render: function(data, type, row){
                                if(data.quantity == '' || data.quantity =='–'){
                                    return '&ndash;';
                                }
                                return parseFloat(data.quantity).toFixed(2);
                            }
                        },
                        {
                            data: null,
                            render: function (data, type, row) {
                                if (data.mrp == '' || data.mrp == '–') {
                                    return '&ndash;';
                                    // &mdash; - long dash
                                    // &ndash; - short dash
                                }
                                return currencySymbol() + `${$.isNumeric(data.mrp) ? updateRate(parseFloat(data.mrp).toFixed(2)) : parseFloat(data.mrp).toFixed(2)}`;
                            }
                        },
                        {
                            data: null,
                            render: function (data, type, row) {
                                // return currencySymbol() + data.sale_price;
                                return currencySymbol() + `${$.isNumeric(data.sale_price) ? updateRate(parseFloat(data.sale_price).toFixed(2)) : parseFloat(data.sale_price).toFixed(2)}`;
                            }
                        },
                        {
                            data: null,
                            render: function (data, type, row) {
                                return data.short_unit;
                            }
                        },
                        {
                            data: null,
                            render: function (data, type, row) {
                                let buttons = '<div class="flex items-center justify-center">';
                                buttons += `<button data-id="${data.id}" type="button" class="btn btn-link viewDetails">`+@json(__('inventory_page.View Details'))+`</button>`;
                                buttons += '</div>';
                                return buttons;
                            },
                            visible: true // Initially visible
                        }
                    ],
                    rowCallback: function (row, data) {

                        if (isAdmin === 0) {
                            $("#deleteSelected").addClass("d-none");
                        } else {
                            $("#deleteSelected").removeClass("d-none"); // Ensure visible for admin
                        }

                        if (stock_preference == 1) {
                            if (parseFloat(data.min_stock_alert) >= parseFloat(data.quantity)) {
                                $(row).addClass('inventoryMinStockAlert');
                            }
                            if (parseFloat(data.quantity) == 0) {
                                $(row).addClass('inventoryMinStockAlert');
                            }
                        }
                        
                    },

                    pagingType: 'full_numbers',
                    order: [[0, 'asc']],
                    pageLength: 100,
                    language: {
                        "emptyTable": "Currently there are no items"
                    },
                    initComplete: function () {
                        // Adjust column visibility based on isAdmin after initialization
                        console.log('Adjusting column visibility after initialization for isAdmin:', isAdmin);
                        dataTable.column(0).visible(isAdmin === 1); // First column (checkbox)
                        dataTable.column(6).visible(isAdmin === 1); // Last column (action buttons)

                        // Debug log to confirm column visibility
                        console.log('First column visible:', dataTable.column(0).visible());
                        console.log('Last column visible:', dataTable.column(6).visible());
                    }
                });

                $("#entity-select").val(100);
                $('.dataTables_filter').hide();
                $('.dataTables_length').hide();

                // Event listeners for filtering and searching
                $('#filter-select').on('change', function () {
                    $("#dataTable_search").val("");
                    var searchValue = $("#dataTable_search").val();
                    dataTable.search(searchValue).draw();
                });

                $("#dataTable_search").on('keyup', function () {
                    var searchValue = $(this).val();
                    dataTable.search(searchValue).draw();
                });

                $('#entity-select').on('change', function () {
                    dataTable.page.len($(this).val()).draw();
                });

                table = dataTable;
            }

            // // Function to reinitialize DataTable when isAdmin changes
            // function updateDataTableForAdminStatus(newIsAdmin) {
            //     console.log('Updating DataTable for isAdmin:', newIsAdmin);
            //     isAdmin = newIsAdmin;
            //     dataList(); // Reinitialize DataTable
            // }

            dataList();

            // Handle DataTables processing event
            dataTable.on('preXhr.dt', function () {
                $(".overlay").show();
            });

            // Handle DataTables processed event
            dataTable.on('xhr.dt', function () {
                $(".overlay").hide();
            });

            // ----------------------------------- VIEW ITEMS ON TABLE --------------------------------------

            // ----------------------------------- EXPORT DATA ON CSV ---------------------------------------
            $(".exportData").on('click', function (e) {
                $.ajax({
                    type: 'GET',
                    url: grocery_germany_api_url + `export`,
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    contentType: false,
                    processData: false,

                    beforeSend: function () {
                        $('.overlay').show();
                    },
                    success: function (response) {

                        console.log('response', response);

                        if (response.status == 1) {

                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                            $('.overlay').hide();

                            location.href = media_url + response.file;
                        } else if (response.status == 0) {
                            $.toast({
                                heading: 'Error',
                                text: response.message,
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                            $('.overlay').hide();
                        }
                    },
                    error: function (error) {
                        console.log('Error', error);

                    }
                });
            });
            // ----------------------------------- EXPORT DATA ON CSV ----------------------------------------

            // ----------------------------------- IMPORT DATA FROM CSV --------------------------------------
            $('#uploadBulkData').submit(function (e) {

                e.preventDefault();

                var formData = new FormData();
                formData.append('file', $('#file')[0].files[0]);

                $.ajax({
                    url: grocery_germany_api_url + `preview/excel`,
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".overlay").show();
                    },
                    success: function (response) {

                        if (response) {
                            console.log('response', response);

                            $("#file").val("");
                            $("#importModal").modal('hide');
                            $(".overlay").hide();

                            // SHOW PREVIEW RESULT ON MODAL
                            // $("#previewModal").modal('show');

                        }

                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);

                        $.toast({
                            heading: 'Error',
                            text: error.message,
                            icon: 'error',
                            loader: true,
                            position: 'top-right',
                            loaderBg: '#9EC600'
                        });

                    }
                });
            });
            // ----------------------------------- IMPORT DATA FROM CSV --------------------------------------


            // ----------------------------------- FETCH ITEM BY ID ------------------------------------------

            function showItemDataByID(id) {
                $.ajax({
                    type: 'GET',
                    url: grocery_germany_api_url + `item/` + id,
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    contentType: false,
                    processData: false,

                    beforeSend: function () {
                        $('.overlay').show();
                    },
                    success: function (response) {

                        console.log('showItemDataByID',response);

                        if (response) {
                            $("#editModal").modal('show');
                            console.log('response', response);

                            var responseData = response.data[0]

                            $("#item_id").val(responseData.id);
                            $("#u_item_name").val(responseData.item_name);
                            if (responseData.quantity != '–') {
                                $("#u_quantity").val(parseFloat(responseData.quantity).toFixed(2));
                            } else {
                                $("#u_quantity").val('');
                            }

                            if (responseData.min_stock_alert != '–') {
                                $("#u_min_stock_alert").val(parseFloat(responseData.min_stock_alert).toFixed(2));
                            } else {
                                $("#u_min_stock_alert").val('');
                            }

                            $("#u_unit").val(responseData.short_unit);


                            if (responseData.mrp != '–') {
                                $("#u_mrp").val(parseFloat(responseData.mrp).toFixed(2));
                            } else {
                                $("#u_mrp").val('');
                            }

                            $("#u_sale_price").val(parseFloat(responseData.sale_price).toFixed(2));

                            $(".unitSelected").text("per " + responseData.short_unit);


                            $("#u_tax").val(responseData.tax1);


                            updateCount();
                            $("#editModal").modal('show');
                        }
                        $('.overlay').hide();
                    },
                    error: function (error) {
                        console.log('Error', error);
                        $('.overlay').hide();
                    }
                });
            }


            $(document).on('click', '.viewDetails', function () {


                console.log('viewDetails', $(this).data('id'));

                showItemDataByID($(this).data('id'))
            });

            // ----------------------------------- FETCH ITEM BY ID --------------------------------------

            // ------------------------------------ UPDATE ITEM ------------------------------------------
            $('#updateItemForm').submit(function (e) {

                e.preventDefault();
                resetItemUpdateForm();

                var formData = new FormData();
                formData.append('id', $('#item_id').val());
                formData.append('item_name', $('#u_item_name').val());

                formData.append('quantity', $('#u_quantity').val());

                formData.append('min_stock_alert', $('#u_min_stock_alert').val());
                formData.append('mrp', $('#u_mrp').val());

                if ($('#u_unit').val()) {
                    formData.append('short_unit', $('#u_unit').val());
                    formData.append('full_unit', $('#u_unit option:selected').data('full'));
                }

                formData.append('sale_price', $('#u_sale_price').val());


                let taxOption = $('#u_tax option:selected');

                if (taxOption.val()) {
                    formData.append('tax1', taxOption.val());         
                    formData.append('rate1', taxOption.data('value'));
                }

                console.log('formData', formData);

                $.ajax({
                    url: grocery_germany_api_url + `update-item`,
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
                            console.log('item', response);
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });

                            resetItemUpdateForm();
                            dataTable.destroy();
                            dataList();
                            $("#editModal").modal('hide');
                            $(".overlay").hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);
                        handleItemUpdateErrors(error);
                    }
                });
            });

            function resetItemUpdateForm() {
                // Reset form fields
                $("#u_item_name, #u_quantity,#u_min_stock_alert, #u_unit, #u_mrp, #u_sale_price, #u_tax, #u_rate")
                    .removeClass('border border-2 border-danger');
                $(".u_item_name-error, .u_quantity-error, .u_min_stock_alert-error, .u_unit-error, .u_mrp-error, .u_sale_price-error, .u_tax-error, .u_rate-error")
                    .addClass('d-none');
            }

            function handleItemUpdateErrors(error) {

                console.log('handleItemUpdateErrors',error);

                // Use switch case for displaying error messages
                resetItemUpdateForm();

                $.each(error.data[0], function (key, value) {
                    switch (key) {
                        case 'item_name':
                        case 'quantity':
                        case 'min_stock_alert':
                        case 'mrp':
                        case 'sale_price':
                        case 'tax1':
                        case 'rate1':
                            $("#u_" + key).addClass('border border-2 border-danger');
                            $(".u_" + key + "-error").removeClass('d-none');
                            $(".u_" + key + "-error").text(value[0]);
                            break;
                        case 'full_unit':
                            $("#u_unit").addClass('border border-2 border-danger');
                            $(".u_unit-error").removeClass('d-none');
                            $(".u_unit-error").text("Select Unit Option");
                            break;
                        default:
                            console.warn('Unhandled error key:', key);
                            break;
                    }
                });
            }

            // // Click event to make cell editable
            // $('#dataList tbody').on('click', 'td', function () {

            //     if (isAdmin == 1) {
            //         var cell = $(this);
            //         var lastCellIndex = cell.closest('tr').find('td').length - 1;
            //         // Check if the cell is not already in edit mode
            //         if (!cell.hasClass('edit-mode') && cell.index() == 1) {

            //             var currentValue = cell.text();
            //             var inputField = $('<input type="text">').val(currentValue);

            //             // Replace the cell content with an input field
            //             cell.empty().append(inputField);
            //             inputField.focus().select();

            //             // Add a class to identify the cell is in edit mode
            //             cell.addClass('edit-mode');

            //             // Blur event on the input field
            //             inputField.blur(updateCell);

            //             // Enter key press event on the input field
            //             inputField.keydown(function (event) {
            //                 if (event.which === 13) {
            //                     // 13 is the Enter key code
            //                     updateCell.call(inputField);
            //                 }
            //             });
            //         }
            //     }


            // });

            // function updateCell() {
            //     var cell = $(this);
            //     var newValue = cell.val();
            //     var columnIndex = cell.parent().index();
            //     var rowIndex = cell.closest('tr').index();

            //     // Check if originalData is defined
            //     if (originalData && originalData[rowIndex] !== undefined) {
            //         // Update the table locally
            //         originalData[rowIndex][table.column(columnIndex).dataSrc()] = newValue;
            //         table.row(rowIndex).data(originalData[rowIndex]).draw();

            //         // Remove edit mode and restore the original content
            //         cell.closest('td').removeClass('edit-mode');
            //         cell.closest('td').empty().text(newValue);

            //         console.log('id', originalData[rowIndex].id);

            //         // Send the updated data to the server (you need to implement this part)
            //         $.ajax({
            //             url: '/api/update-cell', // Replace with your server-side update script
            //             headers: {
            //                 'Authorization': 'Bearer ' + localStorage.getItem('token')
            //             },
            //             type: 'POST',
            //             data: {
            //                 id: originalData[rowIndex].id,
            //                 columnIndex: columnIndex,
            //                 newValue: newValue
            //             },
            //             success: function (response) {
            //                 if (response) {
            //                     dataTable.destroy();
            //                     dataList();
            //                 }
            //             },
            //             error: function (error) {
            //                 console.log('error', error);
            //                 console.log('error', error.responseJSON.data);

            //                 var len = error.responseJSON.data.newValue.length;
            //                 var msg = '';
            //                 for (var i = 0; i < len; i++) {
            //                     msg += error.responseJSON.data.newValue[i];
            //                 }
            //                 alert(msg);

            //             }
            //         });
            //     } else {
            //         console.error('originalData is undefined for rowIndex:', rowIndex);
            //     }
            // }

            // ------------------------------------- UPDATE ITEM --------------------------------------

            
            // ----------------------------------- DELETE ITEM --------------------------------------
            $('.confrimModalButton').click(function () {
                deleteSubUser($(this).data('id'));
            });

            function deleteSubUser(id) {

                $.ajax({
                    type: 'GET',
                    url: grocery_germany_api_url + `delete-item/` + id,
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

                            $(".overlay").hide();
                            $("#confirmationModal").modal('hide');

                            dataTable.destroy();
                            dataList();
                        }
                    },
                    error: function (error) {
                        console.log('Error', error);

                    }
                });

            }
            // ----------------------------------- DELETE ITEM --------------------------------------


            // ------------------------------------------------------- MULTIPLE DELETE ------------------------------------------------------

            // Handle header checkbox click event
            $('#selectAll').on('click', function () {
                $('.rowCheckbox').prop('checked', this.checked);
                toggleDeleteButton(); // Enable/Disable delete button
            });

            // Handle row checkboxes click event to update header checkbox state
            $(document).on('click', '.rowCheckbox', function () {
                if ($('.rowCheckbox:checked').length === $('.rowCheckbox').length) {
                    $('#selectAll').prop('checked', true);
                } else {
                    $('#selectAll').prop('checked', false);
                }
                toggleDeleteButton(); // Enable/Disable delete button
            });

            // Function to toggle delete button state
            function toggleDeleteButton() {
                if ($('.rowCheckbox:checked').length > 0) {
                    $('#deleteSelected').prop('disabled', false);
                } else {
                    $('#deleteSelected').prop('disabled', true);
                }
            }

            // Handle Delete Button Click Event
            $('#deleteSelected').on('click', function () {
                let selectedIds = [];

                // Get all checked row checkboxes and extract their data IDs
                $('.rowCheckbox:checked').each(function () {
                    selectedIds.push($(this).data('id'));
                });

                if (selectedIds.length > 0) {
                    deleteSelectedItems(selectedIds);
                } else {
                    alert("Please select at least one item to delete.");
                }

            });


            // Function to Delete Selected Items
            function deleteSelectedItems(itemIds) {

                console.log('deleteSelectedItems', itemIds);

                if (!confirm("Are you sure you want to delete the selected items?")) return;

                $.ajax({
                    url: grocery_germany_api_url + `multiple-delete`,
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Content-Type': 'application/json'
                    },
                    data: JSON.stringify({ ids: itemIds }), // Send IDs as JSON payload
                    success: function (response) {
                        // alert(response.message || "Items deleted successfully.");
                        dataList(); // Refresh DataTable

                        $("#selectAll").prop("checked", false);
                    },
                    error: function (error) {
                        alert("Failed to delete items. Please try again.");
                        console.error("Error:", error);
                    }
                });
            }
            // ------------------------------------------------------- MULTIPLE DELETE ------------------------------------------------------

            $('#quantity').on('input', function () {

                if ($(this).val() == '') {
                    $(".minimumStockAlert-div").addClass("d-none");
                    $("#min_stock_alert").val("");
                }
                else if (($(this).val() != '') && ($(this).val() != 0)) {
                    $(".minimumStockAlert-div").removeClass("d-none");
                }

            });


        });

    </script>
@endsection