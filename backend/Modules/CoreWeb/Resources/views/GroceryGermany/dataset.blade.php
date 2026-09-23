@extends('coreweb::layouts.groceryGermany')
@section('title', "{{ __('dataset_page.title') }}")

@section('content')

                <style>
                    body {
                        background-color: #f8f9fa;
                    }

                    .table-container {
                        width: 100%;
                        overflow-x: auto;
                    }

                    table {
                        border-collapse: collapse;
                        width: 100%;
                    }

                    th,
                    td {
                        text-align: center;
                        vertical-align: middle;
                        border: 1px solid #dee2e6 !important;
                        padding: 12px;
                    }

                    th {
                        background: #343a40;
                        color: white;
                    }

                    tbody tr:hover {
                        background: #f1f3f5;
                        transition: 0.3s;
                    }

                    .editable {
                        cursor: pointer;
                        min-width: 80px;
                    }

                    .editable input {
                        width: 100%;
                        border: 1px solid #ccc;
                        outline: none;
                        text-align: center;
                        background: #fff;
                        padding: 4px;
                        border-radius: 4px;
                    }

                    .error {
                        border: 2px solid red !important;
                        background-color: #ffe6e6 !important;

                    }

                    .error-text {
                        color: red;
                        font-size: 12px;
                        display: block;
                        margin-top: 2px;
                    }

                    .btn-delete {
                        color: red;
                        cursor: pointer;
                        font-size: 18px;
                    }

                    .btn-sm {
                        padding: 8px 12px;
                        font-size: 14px;
                        font-weight: 500;
                        border-radius: 6px;
                    }

                    .checkbox-lg {
                        width: 18px;
                        height: 18px;
                    }


                    .table-container thead th {
                        position: sticky;
                        top: 0;
                        background: #343a40;
                        color: white;
                    }

                    .dataTables_filter {
                        margin-bottom: 15px;
                    }

                    .error-cell {
                        border: 2px solid red;
                        background-color: #f8d7da;
                    }

                    .error-row {
                        background-color: #f8d7da;
                    }

                    .error-span {
                        color: red;
                        font-size: 12px;
                        display: block;
                        margin-top: 5px;
                    }


                
                        #excelTable {
                            width: 100% !important;
                            table-layout: fixed;
                        }

                        #excelTable th:nth-child(2),
                        #excelTable td:nth-child(2) {
                            min-width: 200px;
                            max-width: 200px;
                            width: 200px;
                        }


                        #excelTable th:nth-child(7),
                        #excelTable td:nth-child(7) {
                            min-width: 100px;
                            max-width: 100px;
                            width: 100px;
                        }


                </style>

                <div class="pagetitle">
                    <h1 class="mb-2">{{ __('dataset_page.title') }}</h1>
                    <p class="text-muted">
                        {!!  __('dataset_page.info') !!}
                    </p>
                </div>

                <section class="section profile">
                    <div class="row">

                        <div class="col-xl-8">
                           @include('coreweb::components/error', [
                                'heading' => __('common.Subscription Alert'),
                                'modalIdAttribute' => 'subscripitonErrorId',
                            ])
                            </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-body pt-3">
                                    <div id="globalErrorBox" class="alert alert-danger d-none"></div> <!-- Global Error Box -->


                                    <div id="errorSection" class="alert alert-danger d-none">
                                        <ul id="errorList"></ul>
                                    </div>

                                    <form id="inventoryForm">
                                        <div class="table-container">
                                            <div class="d-flex justify-content-between mb-3">

                                                <div>
                                                    <button type="button" class="btn btn-danger btn-sm" id="deleteSelected" disabled><i
                                                            class="bi bi-trash"></i> {{ __('common.Delete Selected') }}</button>

                                                    <button type="button" class="btn btn-primary btn-sm text-white" id="resetDataset">
                                                                <i class="bi bi-arrow-counterclockwise"></i> {{ __('common.Reset Dataset') }}</button>
                                                </div>

                                                <div>
                                                    <button type="button" class="btn btn-success btn-sm" id="addRow"><i
                                                        class="bi bi-plus-circle"></i> {{ __('common.Add Row') }}</button>
                                                </div>

                                            </div>

                                            <div class="text-center">
                                                    <h5 id="itemCounts"></h5>
                                            </div>

                                            <!-- style="max-height: 400px; overflow-y: auto;" -->
                                            <div class="table-responsive">
                                                <table class="table table-hover table-bordered" id="excelTable">
                                                    <thead>
                                                        <tr>
                                                            <th><input type="checkbox" id="selectAll" class="checkbox-lg"></th>
                                                            <th>{{ __('common.Item Name') }}</th>
                                                            <th>{{ __('common.Quantity') }}</th>
                                                            <th>{{ __('common.Min Stock Alert') }}</th>
                                                            <th>{{ __('common.MRP') }}</th>
                                                            <th>{{ __('common.Sale Price') }}</th>
                                                            <th>{{ __('common.Unit') }}</th>
                                                            <th>{{ __('common.VAT (%)') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="text-start mt-3">
                                            <button type="button" class="btn btn-primary exportData">{{ __('common.Export Data') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                @include('coreweb::modals.datasetConfirmationModal', [
    'heading' => __('common.Confirmation'),
    'subHeading' => __('dataset_page.Do you want to “Append” or “Replace”'),
    'buttonText' => __('common.Click Confirm to proceed')
])

                @include('coreweb::modals.confirmation', [
    'heading' => __('common.Are you sure you want to do the action ?'),
    'subHeading' => __('dataset_page.Once reset, your changes will be lost, and the dataset will return to its original state.'),
    'buttonText' => __('common.Click Confirm to proceed')
])

@endsection

@section('scripts')
    @parent

    <script>
        $(document).ready(function () {

            // Assuming unitList is the array we created above
            let unitList = Object.entries(@json(config('german_units.units'))).map(([label, value]) => ({
                label: label,
                value: value
            }));

            console.log('unitList',unitList);

            $(document).on("click", ".editable", function (e) {
                var span = $(this).find(".value-span");

                // If the span already has an input, do nothing
                if (span.find("input").length) return;

                var currentText = span.text().trim();
                var input = $("<input type='text' class='form-control form-control-sm'>").val(currentText);

                span.html(input);
                input.focus();

                input.blur(function () {
                    var newValue = $(this).val().trim();
                    if (newValue === "") {
                        newValue = span.data("default") || ""; // Set default text if empty
                    }
                    span.html(newValue);
                });

                input.keypress(function (e) {
                    if (e.which == 13) {
                        $(this).blur();
                    }
                });

                e.stopPropagation(); // Prevents triggering multiple times if nested inside `.editable`
            });


            // Utility function to convert multiple zeros into a single '0'
            function convertToSingleZero(value) {
                // Check if the value consists only of zeros (e.g., "0000")
                if (/^0+$/.test(value)) {
                    return '0'; // Return '0' if the value is only zeros
                }
                return value; // Otherwise, return the original value
            }

            // // Event delegation for editable cells
            // $(document).on("click", ".editable", function () {
            //     var span = $(this).find('.value-span');
            //     var currentValue = span.text().trim();

            //     // If the span is empty, set a default value or empty string
            //     if (currentValue === "") {
            //         currentValue = span.data("default") || "";
            //     }

            //     // Only create input if it's not already an input field
            //     if (!span.find('input').length) {
            //         var inputField = $('<input>', {
            //             type: 'text',
            //             value: currentValue,
            //             class: 'value-input'
            //         });


            //         // Add event listener for when the input field loses focus or the user presses Enter
            //         inputField.on("blur keyup", function (e) {
            //             // Trigger the conversion only on 'Enter' keypress or when the input loses focus
            //             if (e.type === 'keyup' && e.key !== 'Enter') return; // Skip non-Enter key presses

            //             // var updatedValue = convertToSingleZero($(this).val()); // Convert the value
            //             var updatedValue = $(this).val(); // Convert the value

            //             // Update the span with the new value
            //             span.html(updatedValue);
            //             $(this).parent().html(span); // Revert back to the span with the updated value
            //         });

            //         // Set the input field inside the span
            //         span.html(inputField);
            //         inputField.focus(); // Focus on the input field
            //     }

            // });


            // ------------------------------------------------------- MULTIPLE DELETE ------------------------------------------------------


            // // Select/Deselect All
            // $("#selectAll").click(function () {
            //     $(".rowCheckbox").prop("checked", this.checked);
            //     toggleDeleteButton(); // Enable/Disable delete button
            // });

            $(document).on("change", "#selectAll", function () {
                $(".rowCheckbox").prop("checked", this.checked);
                toggleDeleteButton(); // Enable/Disable delete button
            });


            // Delete Selected Rows
            $("#deleteSelected").click(function () {
                // $(".rowCheckbox:checked").closest("tr").remove();


                let selectedIds = [];
                // Get all checked row checkboxes and extract their data IDs
                $('.rowCheckbox:checked').each(function () {
                    // selectedIds.push($(this).data('id'));
                    selectedIds.push($(this).closest("tr").data('id'));
                    $(".rowCheckbox:checked").closest("tr").remove();
                });

                if (selectedIds.length > 0) {
                    deleteSelectedItems(selectedIds);
                } else {
                    alert("Please select at least one item to delete.");
                }


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

            // Function to Delete Selected Items
            function deleteSelectedItems(itemIds) {

                console.log('deleteSelectedItems', itemIds);

                if (!confirm(@json(__('common.Are you sure you want to delete the selected items')))) return;

                $.ajax({
                    url: grocery_germany_api_url + 'dataset/multiple-delete',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Content-Type': 'application/json'
                    },
                    data: JSON.stringify({ ids: itemIds }), // Send IDs as JSON payload
                    success: function (response) {
                        // alert(response.message || "Items deleted successfully.");
                        // dataset(); // Refresh DataTable

                        $("#selectAll").prop("checked", false);

                        fetchUploadedExcelData();
                    },
                    error: function (error) {
                        alert("Failed to delete items. Please try again.");
                        console.error("Error:", error);
                    }
                });
            }

            // ------------------------------------------------------- MULTIPLE DELETE ------------------------------------------------------


            $("#addRow").click(function () {
                var newRow = `<tr data-id="0">
                            <td><input type="checkbox" class="rowCheckbox checkbox-lg"></td>
                            <td class="editable">
                                <span class="value-span"></span>
                                <span class="error-span"></span>
                            </td>
                            <td class="editable">
                                <span class="value-span"></span>
                                <span class="error-span"></span>g
                            </td>
                            <td class="editable">
                                <span class="value-span"></span>
                                <span class="error-span"></span>
                            </td>
                            <td class="editable">
                                <span class="value-span"></span>
                                <span class="error-span"></span>
                            </td>
                            <td class="editable">
                                <span class="value-span"></span>
                                <span class="error-span"></span>
                            </td>
                            <td class="">
                                <span class="value-span">
                                    <select class="form-select" name="unit">
                                        <option value="" selected>Select Unit</option> <!-- Default selected option -->
                                        ${unitList.map(unit => `<option value="${unit.value}">${unit.label}</option>`).join('')}
                                    </select>
                                </span>
                                <span class="error-span"></span>
                            </td>
                            <td class="editable">
                                <span class="value-span"></span>
                                <span class="error-span"></span>
                            </td>
                            <td class="editable">
                                <span class="value-span"></span>
                                <span class="error-span"></span>
                            </td>
                            <td class="editable">
                                <span class="value-span"></span>
                                <span class="error-span"></span>
                            </td>
                        </tr>`;

                $("#excelTable tbody").append(newRow);
            });


            // function highlightErrors(errors) {
            //     console.log('highlightErrors errors:', errors);

            //     // Iterate through all errors
            //     Object.keys(errors).forEach(function (key) {

            //         // Extract the coordinate from the error key (e.g., '00,00')
            //         let coordinates = errors[key];

            //         errors.forEach(function (coordinate) {
            //             // Extract row and column indices from the coordinate (e.g., '00,00')
            //             let [rowIndex, colIndex] = coordinate.split(',').map(val => parseInt(val));


            //             // Find the respective cell in the table
            //             let cell = $("#excelTable tbody tr").eq(rowIndex).find(`td:eq(${colIndex})`);

            //             // Get the error message for this field
            //             // let errorMessage = errors[key][0];

            //             // Highlight the cell and display the error message
            //             cell.addClass("error");

            //             // Get the error span or create one if it doesn't exist
            //             let errorSpan = cell.find(".error-span");
            //             if (errorSpan.length === 0) {
            //                 errorSpan = $("<span>").addClass("error-span");
            //                 cell.append(errorSpan); // Append error span if it's not already there
            //             }

            //             // Update the error span with the message
            //             // errorSpan.html(errorMessage).addClass("text-danger text-sm");
            //         });
            //     });
            // }


            // Separate function to highlight error cells
            function highlightErrors(gridCoordinates, messages) {

                console.log('highlightErrors', gridCoordinates);

                // Step 1: Clear previous highlights from all cells
                $("#excelTable tbody td").removeClass('error-cell').css({
                    'border': '', // Reset to default border
                    'background-color': '' // Reset to default background
                });

                if (!gridCoordinates || !Array.isArray(gridCoordinates)) {
                    console.log('No valid grid coordinates provided:', gridCoordinates);
                    return;
                }

                console.log('Highlighting errors for coordinates:', gridCoordinates);

                // Loop through each coordinate in the format "row,column" (e.g., "00,07")
                gridCoordinates.forEach(coordinate => {
                    const [rowIndex, colIndex] = coordinate.split(',').map(Number);
                    console.log('Processing coordinate - Row:', rowIndex, 'Column:', colIndex);

                    // Find the corresponding <tr> and <td> in the table
                    const row = $("#excelTable tbody tr").eq(rowIndex);
                    if (row.length) {
                        const cell = row.find('td').eq(colIndex);
                        if (cell.length) {
                            cell.addClass('error-cell');
                            cell.css({
                                'border': '2px solid red',
                                'background-color': '#ffcccc'
                            });
                            console.log('Highlighted cell at Row:', rowIndex, 'Column:', colIndex);
                        } else {
                            console.log('Cell not found at Row:', rowIndex, 'Column:', colIndex);
                        }
                    } else {
                        console.log('Row not found at index:', rowIndex);
                    }
                });


                let errorList = $("#errorList");
                errorList.empty();

                if (!gridCoordinates || !Array.isArray(gridCoordinates)) {
                    errorList.append(`<li>Validation Error!</li>`);
                    $("#errorSection").removeClass("d-none");
                }
                else {
                    messages.forEach(message => {
                        errorList.append(`<li>${message}</li>`);
                    });
                    $("#errorSection").removeClass("d-none");
                }

            }



            // Helper function to map column names to their corresponding index
            function columnIndex(columnName) {
                let columnMap = {
                    item_name: 1,
                    quantity: 2,
                    min_stock_alert: 3,
                    mrp: 4,
                    sale_price: 5,
                    unit: 6,
                    vat: 7,
                };
                return columnMap[columnName] || 0;
            }


            function fetchUploadedExcelData() {
                if ($.fn.DataTable.isDataTable('#excelTable')) {
                    $('#excelTable').DataTable().clear().destroy();
                }

                 $('.overlay').show();

                // Initialize DataTable with server-side processing
                let table = $('#excelTable').DataTable({
                    autoWidth: false,
                    processing: true,
                    serverSide: true,
                    searching: true,
                    ordering: true,
                    paging: true,
                    lengthChange: true,
                    pageLength: 100,
                    lengthMenu: [10, 25, 50, 100],
                    order: [
                        [1, 'asc']
                    ],

                    // Enable scrolling with fixed height
                    scrollY: '400px',
                    scrollX: true,
                    scrollCollapse: true,

                    ajax: {
                        url: grocery_germany_api_url + 'dataset',
                        type: 'POST',
                        headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') },
                        data: function (d) {
                            // Map DataTables parameters to what the backend expects
                            d.start = d.start;    // Starting record index
                            d.length = d.length;  // Page length
                            // d.search = d.search.value; // Search value
                        },
                        beforeSend: function () {
                            $('.overlay').show();
                        },
                        dataSrc: function (response) {

                            $("#inventoryForm #itemCounts").text("{{ __('common.Total Items') }}: " + response.recordsTotal);


                            $('.overlay').show();

                            // Handle errors and store them
                            $("#errorList").empty();
                            $("#errorSection").addClass("d-none");
                            if (response.errors && response.status == 0) {
                                highlightErrors(response.errors.grid_coordinates, response.errors.messages);
                            }

                            $('#deleteSelected').prop('disabled', true);

                            return response.data;
                        },
                        error: function (xhr, status, error) {
                            $('.overlay').hide();
                            console.error('Data fetch failed:', status, error);
                        }
                    },
                    columns: [
                        {
                            data: 'flag',
                            // title: 'Status',
                            render: () => '<span class="value-span"><input type="checkbox" class="rowCheckbox checkbox-lg"></span><span class="error-span"></span>',
                            // className: 'editable'
                        },
                        {
                            data: 'item_name',
                            // title: 'Item Name',
                            // title: '{{ __('common.Item Name') }}',
                            render: data => `<span class="value-span">${data}</span><span class="error-span"></span>`,
                            className: 'editable'
                        },
                        {
                            data: 'quantity',
                            // title: 'Quantity',
                            render: data => `<span class="value-span numericField">${parseFloat(data).toFixed(2)}</span><span class="error-span"></span>`,
                            className: 'editable'
                        },
                        {
                            data: 'min_stock_alert',
                            // title: 'Minimum Stock Alert',
                            render: data => `<span class="value-span numericField">${parseFloat(data).toFixed(2)}</span><span class="error-span"></span>`,
                            className: 'editable'
                        },
                        {
                            data: 'mrp',
                            // title: 'MRP',
                            render: data => `${currencySymbol()}<span class="value-span numericField"> ${$.isNumeric(data) ? updateRate(parseFloat(data).toFixed(2)) : parseFloat(data).toFixed(2)}</span><span class="error-span"></span>`,
                            className: 'editable'
                        },
                        {
                            data: 'sale_price',
                            // title: 'Sale Price',
                            // render: data => `${currencySymbol()}<span class="value-span"> ${updateRate(data ?? '0')}</span><span class="error-span"></span>`,
                            render: data => `${currencySymbol()}<span class="value-span numericField"> 
                                                ${$.isNumeric(data) ? updateRate(parseFloat(data).toFixed(2)) : parseFloat(data).toFixed(2)}
                                            </span><span class="error-span"></span>`,
                                className: 'editable'
                        },
                        {
                            data: 'unit',
                            // title: 'Unit',
                            render: data => `<select class="form-select" name="unit">
                                                    <option value="" selected>Select Unit</option>
                                                    ${unitList.map(unit => `<option value="${unit.value.toUpperCase()}" ${unit.value.toUpperCase() === data.toUpperCase() ? 'selected' : ''}>${unit.label}</option>`).join('')}
                                                </select>`
                        },
                        {
                            data: 'vat',
                            render: data => `<span class="value-span numericField">${data}</span><span class="error-span"></span>`,
                            className: 'editable'
                        },
                    ],

                    columnDefs: [
                        { targets: 0, orderable: false },
                        // // { targets: 0, width: '50px', className: 'dt-width-50' },   
                        // { targets: 1, width: '200px', className: 'dt-width-200'}, 
                        // // { targets: 2, width: '50px', className: 'dt-width-100' }, 
                        // // { targets: 3, width: '50px', className: 'dt-width-100' }, 
                        // { targets: 6, width: '150px', className: 'dt-width-100'} ,
                        // // { targets: 7, width: '150px', className: 'dt-width-100'} ,
                    ],

                    createdRow: function (row, data, dataIndex) {

                        console.log('Row Data:', data);

                        // Add data-id attribute to the <tr> element
                        $(row).attr('data-id', data.id);
                    },
                    drawCallback: function (settings) {

                        // Hide loader after rendering is complete
                        $(".overlay").hide();

                        // Ensure errors are highlighted after each draw
                        let api = this.api();
                        let response = api.ajax.json();
                        if (response && response.errors && response.status == 0) {
                            highlightErrors(response.errors.grid_coordinates, response.errors.messages);
                        }
                    }
                });

                // Custom info callback
                table.settings()[0].oLanguage.fnInfoCallback = function (settings, start, end, max, total, pre) {
                    return `Showing ${start} to ${end} of ${total} entries`;
                }; 
            }

            fetchUploadedExcelData();


            $(".toggle-sidebar-btn").click(function () {

                $(".overlay").show();

                console.log('expand');

                 // Wait for sidebar animation to complete
                setTimeout(function() {
                    let table = $('#excelTable').DataTable();
                    if (table) {
                        table.columns.adjust().draw();
                         $(".overlay").hide();
                    }
                }, 300); // Adjust timeout to match your sidebar animation duration
            });

            $(window).resize(function() {

                $(".overlay").show();

                clearTimeout(window.resizeTimer);
                window.resizeTimer = setTimeout(function() {
                    let table = $('#excelTable').DataTable();
                    if (table) {
                        table.columns.adjust();
                        $(".overlay").hide();
                    }
                }, 250);
            });         



            // ------------------------------------------------------- APPEND OR REPLACE CONFIRMATION ------------------------------------------------------
            $(".exportData").click(function () {

                $(".FirstConfimation").removeClass("d-none");
                $(".SecondConfimation").addClass("d-none");

                $("#datasetConfirmationModal h6").text("Do you want to “Append” or “Replace”");
                $('input[name="datasetAction"][value="1"]').prop('checked', true);

                $("#datasetConfirmationModal").modal("show");
            });


            $(".confirmationButton1").click(function () {

                if ($('input[name="datasetAction"]:checked').val() == 1) {
                    storeDataToInventory(1);
                    $("#datasetConfirmationModal").modal("hide");

                    $(".FirstConfimation").removeClass("d-none");
                    $(".SecondConfimation").addClass("d-none");
                }
                else {

                    $("#datasetConfirmationModal h6").text(@json(__('dataset_page.data_replace_warning')));

                    $(".FirstConfimation").addClass("d-none");
                    $(".SecondConfimation").removeClass("d-none");
                }
            });

            // ------------------------------------------------------- APPEND OR REPLACE CONFIRMATION ------------------------------------------------------


            // ------------------------------------------------------- REPLACE CONFIRMATION ------------------------------------------------------
            $(".confirmationButton2").click(function () {
                storeDataToInventory(2);
                $("#datasetConfirmationModal").modal("hide");
            });
            // ------------------------------------------------------- REPLACE CONFIRMATION ------------------------------------------------------


            // ------------------------------------------------------- STORE FUNCTION ------------------------------------------------------

            function storeDataToInventory(action) {
                event.preventDefault();
                var inventoryData = [];

                // Loop through all rows including the first row
                $("#excelTable tbody tr").each(function () {
                    var row = {
                        item_name: $(this).find(".value-span").eq(0).text().trim(),
                        quantity: $(this).find(".value-span").eq(1).text().trim(),
                        min_stock_alert: $(this).find(".value-span").eq(2).text().trim(),
                        mrp: $(this).find(".value-span").eq(3).text().trim(),
                        sale_price: $(this).find(".value-span").eq(4).text().trim(),

                        // unit: $(this).find(".value-span").eq(5).text().trim(),
                        unit: $(this).find("select").val(),
                        vat: $(this).find(".value-span").eq(6).text().trim(),

                    };
                    inventoryData.push(row);
                });

                // Clear previous errors
                $(".error").removeClass("error");
                $(".error-text").remove();
                $(".error-span").empty();
                $("#globalErrorBox").addClass("d-none").html("");

                $.ajax({
                    url: grocery_germany_api_url + "inventory-store-multiple",
                    type: "POST",
                    contentType: "application/json",
                    headers: { 
                        'Authorization' : 'Bearer ' + localStorage.getItem('token'),
                        "X-CSRF-TOKEN": "{{ csrf_token() }}" 
                    },
                    data: JSON.stringify({
                        items: inventoryData,
                        action: action,
                    }),
                    beforeSend: function () {
                        $('.overlay').show(); // Show loading overlay
                    },
                    success: function (response) {
                        $('.overlay').hide();
                        alert(response.message);
                        location.reload();

                    },
                    error: function (xhr) {

                        let response = xhr.responseJSON;

                        if (response.isErrorExsist == 1) {
                            fetchUploadedExcelData();
                        }

                        $('.overlay').hide();

                    }
                });
            }

            // ------------------------------------------------------- STORE FUNCTION ------------------------------------------------------



            // ------------------------------------------------------- ON UPDATE TABLE CELL ------------------------------------------------------
            function updateCellData(formData) {

                $.ajax({
                    url: grocery_germany_api_url + 'update-cell-data',
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
                            fetchUploadedExcelData();
                            console.log('item', response);
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                            $(".overlay").hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        $(".overlay").hide();
                        var errorResponse = JSON.parse(xhr.responseText);
                        console.error(errorResponse);

                        // Check if validation errors exist
                        if (errorResponse.data) {
                            let errorMessages = '';

                            $.each(errorResponse.data, function (field, messages) {
                                let cellIndex = columnIndex(field); // Get cell index dynamically
                                let errorMessage = messages[0];

                                errorMessages += `<p>${errorMessage}</p>`;

                            });

                            $("#errorSection").removeClass("d-none");
                            // Display all errors in a common section
                            $("#errorSection").html(errorMessages);
                        }

                        let rowIndex = errorResponse.row_index;
                        let colIndex = errorResponse.cell_index;

                        let cell = $("#excelTable tbody tr").eq(rowIndex).find(`td:eq(${colIndex})`);
                        cell.addClass("error");

                    }
                });
            }

            $('#excelTable').on('focusout', '.editable input, .editable select', function () {

                try {
                    var inputField = $(this);
                    var isSelect = inputField.is('select');
                    // var newValue = inputField.val().trim();
                    var newValue = isSelect ? inputField.find('option:selected').text().trim() : inputField.val().trim();

                    // // Validate input
                    // if (!newValue && !isSelect) {
                    //     console.warn('Empty value detected');
                    //     return;
                    // }

                    var cell = inputField.closest('td');
                    var cellIndex = cell.index();
                    var row = cell.closest('tr');

                    // Update display value (assuming .value-span is a child of the cell)
                    var span = cell.find('.value-span:first');
                    if (span.length) {
                        span.text(newValue);
                    } else {
                        console.warn('No value-span found in cell');
                    }

                    // Debounce database updates
                    clearTimeout(inputField.data('updateTimeout'));
                    inputField.data('updateTimeout', setTimeout(function () {
                        var rowData = {
                            item_id: row.data('id'),
                            item_name: getCellValue(row, 1),
                            quantity: parseFloat(getCellValue(row, 2)) || 0,
                            min_stock_alert: parseInt(getCellValue(row, 3)) || 0,
                            mrp: parseFloat(getCellValue(row, 4)) || 0,
                            sale_price: parseFloat(getCellValue(row, 5)) || 0,
                            unit: row.find("select").val() || getCellValue(row, 6),
                            gst: parseFloat(getCellValue(row, 6)) || 0,

                            row_index: row.index(),
                            cell_index: cellIndex,
                        };

                        console.log('rowData',rowData);


                        var formData = new FormData();
                        formData.append('id', rowData.item_id);
                        formData.append('item_name', rowData.item_name);
                        formData.append('quantity', rowData.quantity);
                        formData.append('min_stock_alert', rowData.min_stock_alert);
                        formData.append('mrp', rowData.mrp);
                        formData.append('sale_price', rowData.sale_price);
                        formData.append('short_unit', rowData.unit);
                        formData.append('vat', rowData.vat);

                        formData.append('row_index', rowData.row_index);
                        formData.append('cell_index', rowData.cell_index);

                        console.log("Updated Row Data:", rowData);
                        console.log("Cell Index:", cellIndex);

                        updateCellData(formData)

                    }, 300)); // 300ms debounce

                } catch (error) {
                    console.error('Error processing cell update:', error);
                }

            });


            $(document).on('change', '#excelTable select.form-select', function(e) {
                try {
                    var inputField = $(this);
                    var isSelect = inputField.is('select');
                    // var newValue = inputField.val().trim();
                    var newValue = isSelect ? inputField.find('option:selected').text().trim() : inputField.val().trim();

                    // Validate input
                    if (!newValue && !isSelect) {
                        console.warn('Empty value detected');
                        return;
                    }

                    var cell = inputField.closest('td');
                    var cellIndex = cell.index();
                    var row = cell.closest('tr');

                    // Update display value (assuming .value-span is a child of the cell)
                    var span = cell.find('.value-span:first');
                    if (span.length) {
                        span.val(newValue);
                    } else {
                        console.warn('No value-span found in cell');
                    }

                    // Debounce database updates
                    clearTimeout(inputField.data('updateTimeout'));
                    inputField.data('updateTimeout', setTimeout(function () {
                        var rowData = {
                            item_id: row.data('id'),
                            item_name: getCellValue(row, 1),
                            quantity: parseFloat(getCellValue(row, 2)) || 0,
                            min_stock_alert: parseInt(getCellValue(row, 3)) || 0,
                            mrp: parseFloat(getCellValue(row, 4)) || 0,
                            sale_price: parseFloat(getCellValue(row, 5)) || 0,
                            unit: row.find("select").val() || getCellValue(row, 6),
                            vat: parseFloat(getCellValue(row, 7)) || 0,

                            row_index: row.index(),
                            cell_index: cellIndex,
                        };


                        var formData = new FormData();
                        formData.append('id', rowData.item_id);
                        formData.append('item_name', rowData.item_name);
                        formData.append('quantity', rowData.quantity);
                        formData.append('min_stock_alert', rowData.min_stock_alert);
                        formData.append('mrp', rowData.mrp);
                        formData.append('sale_price', rowData.sale_price);
                        formData.append('short_unit', rowData.unit);
                        formData.append('vat', rowData.vat);

                        formData.append('row_index', rowData.row_index);
                        formData.append('cell_index', rowData.cell_index);

                        console.log("Updated Row Data:", rowData);
                        console.log("Cell Index:", cellIndex);

                        updateCellData(formData)

                    }, 300)); // 300ms debounce

                } catch (error) {
                    console.error('Error processing cell update:', error);
                }
            });


            // Helper function to get cell values
            function getCellValue(row, index) {
                var span = row.find(".value-span").eq(index);
                return span.length ? span.text().trim() : '';
            }


            // ------------------------------------------------------- ON UPDATE TABLE CELL ------------------------------------------------------


            // ------------------------------------------------------- RESET DATASET ------------------------------------------------------
            $("#resetDataset").click(function () {
                $("#confirmationModal").modal("show");
            });


            $('.confrimModalButton').click(function () {
                resetDataset();
            });

             function resetDataset() {

                $.ajax({
                    type: 'GET',
                    url: grocery_germany_api_url + 'reset-dataset',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    contentType: false,
                    processData: false,

                    beforeSend: function () {
                        $('.overlay').show();
                    },
                    success: function (response) {
                        if (response && response.status == 1) {
                            fetchUploadedExcelData();
                            $("#confirmationModal").modal("hide");
                        }

                        $('.overlay').hide();
                    },
                    error: function (error) {
                        console.log('Error', error);
                        $('.overlay').hide();
                        // Optionally display an error message to the user
                        console.log('Failed to load dataset');
                    }
                });
            }

            // ------------------------------------------------------- RESET DATASET ------------------------------------------------------

        });
    </script>

@endsection