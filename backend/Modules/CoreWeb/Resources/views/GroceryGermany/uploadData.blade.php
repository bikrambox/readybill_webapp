@extends('coreweb::layouts.groceryGermany')
@section('title', "{{ __('upload_data_page.title') }}")

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

                .error-row {
                    background-color: #fff3f3;
                }

                .error-cell {
                    /* position: relative; */
                    transition: all 0.3s ease;
                }

                .border-red {
                    border: 2px solid red !important;
                }


                 #excelTable {
                            width: 100% !important;
                            table-layout: fixed;
                        }

                        /* #excelTable th:nth-child(1),
                        #excelTable td:nth-child(1) {
                            min-width: 250px;
                            max-width: 250px;
                            width: 250px;
                        } */

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
                <h1 class="mb-2">{{ __('upload_data_page.title') }}</h1>
                <p class="text-muted">{{ __('upload_data_page.info') }}</p>
                <p class="text-danger">{{ __('upload_data_page.note') }}</p>
            </div>

            <section class="section" id="uploadDataSection">
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
                                <div class="mb-3">
                                    @include('coreweb::components/error', [
                                        'heading' => 'Error',
                                        'modalIdAttribute' => 'uploadDataId',
                                    ])

                                    <form id="uploadBulkData" class="row g-3">
                                        <div class="col-12">
                                            <div id="drop-area" class="border border-primary rounded p-3 text-center">
                                                <p class="mb-2">
                                                    {{ __('upload_data_page.drag_n_drop_note') }}
                                                    <label for="file" class="text-primary" style="cursor: pointer">{{
                                                        __('common.browse') }}</label>
                                                </p>
                                                <input type="file" id="file" name="file" class="form-control d-none" accept=".xls, .xlsx" />
                                                <p id="file-name" class="text-muted"></p>
                                                <button type="button" id="clearFile" class="btn btn-sm btn-danger mt-2 d-none">
                                                    {{ __('common.Clear') }}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary">
                                                {{ __('common.Upload') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div id="progressSection" class="d-none">
                                    <div class="progress">
                                        <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%"
                                            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p id="progressMessage" class="progress-message">
                                        {{ __('upload_data_page.process_message') }}
                                    </p>
                                </div>

                                <div id="globalErrorBox" class="alert alert-danger d-none"></div>
                                <!-- Global Error Box -->

                                <div id="errorSection" class="alert alert-danger d-none">
                                    <ul id="errorList"></ul>
                                </div>

                                <form class="d-none" id="inventoryForm">
                                    <div class="table-container">
                                        <div class="d-flex justify-content-between mb-3">
                                            <button type="button" class="btn btn-danger btn-sm" id="deleteSelected" disabled>
                                                <i class="bi bi-trash"></i> {{ __('common.Delete Selected') }}
                                            </button>
                                            <button type="button" class="btn btn-success btn-sm" id="addRow">
                                                <i class="bi bi-plus-circle"></i> {{ __('common.Add Row') }}
                                            </button>
                                        </div>

                                        <div class="text-center">
                                            <h5 id="itemCounts"></h5>
                                        </div>

                                        <!-- style="max-height: 400px; overflow-y: auto;" -->
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered" id="excelTable">
                                                <thead>
                                                    <tr>
                                                        <th>
                                                            <input type="checkbox" id="selectAll" class="checkbox-lg" />
                                                        </th>
                                                        <th>{{ __('common.Item Name') }}</th>
                                                        <th>{{ __('common.Quantity') }}</th>
                                                        <th>{{ __('common.Min Stock Alert') }}</th>
                                                        <th>{{ __('common.MRP') }}</th>
                                                        <th>{{ __('common.Sale Price') }}</th>
                                                        <th style="width: 80px">{{ __('common.Unit') }}</th>
                                                        <th>{{ __('common.VAT (%)') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="text-start mt-3">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('common.Export Data') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


@endsection

@section('scripts')
        @parent

        <script>
            $(document).ready(function () {
                let unitList = Object.entries(@json(config('german_units.units'))).map(([label, value]) => ({
                    label: label,
                    value: value
                }));

                let dropArea = $("#drop-area");
                let fileInput = $("#file"); // jQuery object
                let fileNameDisplay = $("#file-name");
                let clearButton = $("#clearFile");

                $(document).on("click", ".editable", function () {
                    var span = $(this).find('.value-span');
                    var currentText = span.text().trim();

                    console.log('editable vs', currentText);

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
                });

                // Select/Deselect All
                $("#selectAll").click(function () {
                    $(".rowCheckbox").prop("checked", this.checked);
                    toggleDeleteButton(); // Enable/Disable delete button
                });

                // Delete a Single Row
                $(document).on("click", ".btn-delete", function () {
                    $(this).closest("tr").remove();
                });

                $("#addRow").click(function () {
                    var newRow = `<tr data-id="0">
                        <td><input type="checkbox" class="rowCheckbox checkbox-lg"></td>
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
                                    <option value="" selected>Select Unit</option>
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

                $("#inventoryForm").submit(function (event) {
                    event.preventDefault();

                    // Clear previous errors
                    $(".error").removeClass("error");
                    $(".error-text").remove();
                    $("#globalErrorBox").addClass("d-none").html("");

                    $.ajax({
                        url: grocery_germany_api_url +"export-to-inventory",
                        type: "POST",
                        contentType: "application/json",
                        // headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        headers: {
                            'Authorization' : 'Bearer ' + localStorage.getItem('token'),
                        },
                        data: JSON.stringify({
                            action: 0, // Adjust based on user selection (0: append, 1: update, 2: replace)
                        }),
                        beforeSend: function () {
                            $('.overlay').show();
                            $('#progressSection').removeClass('d-none');
                            $('#progressBar').css('width', '0%').attr('aria-valuenow', 0);
                            $('#progressMessage').text('Initiating export...');
                        },
                        success: function (response) {
                            if (response.status === 1 && response.job_id) {
                                // Start polling for progress
                                pollExportProgress(response.job_id);
                            } else {
                                $('#progressSection').addClass('d-none');
                                var errorHtml = $('#globalErrorBox');
                                errorHtml.removeClass('d-none');
                                errorHtml.html('<p class="my-1 text-danger">' + response.message + '</p>');
                            }
                        },
                        error: function (xhr) {
                            $('.overlay').hide();
                            $('#progressSection').addClass('d-none');
                            var response = xhr.responseJSON;
                            var errorHtml = $('#globalErrorBox');
                            errorHtml.removeClass('d-none');
                            errorHtml.html('<p class="my-1 text-danger">' + (response?.message || 'An unexpected error occurred.') + '</p>');
                        }
                    });
                });

                function pollExportProgress(jobId) {
                    var interval = setInterval(function () {
                        $.ajax({
                            type: 'POST',
                            url: grocery_germany_api_url + "export-to-inventory",
                            // headers: {
                            //     "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            // },

                            headers:{
                                'Authorization' : 'Bearer ' + localStorage.getItem('token'),
                            },

                            contentType: "application/json",
                            data: JSON.stringify({ job_id: jobId }),
                            success: function (response) {
                                $('#progressBar').css('width', response.progress.percentage + '%').attr('aria-valuenow', response.progress.percentage);
                                $('#progressMessage').text(response.progress.message);

                                if (response.progress.percentage === 100) {
                                    $('.overlay').hide();
                                    clearInterval(interval);
                                    $('#progressSection').addClass('d-none');

                                    if (response.status === 1) {
                                        alert(response.progress.message);
                                        location.reload();
                                    } else {
                                        var errorHtml = $('#globalErrorBox');
                                        errorHtml.removeClass('d-none');
                                        errorHtml.html('<p class="my-1 text-danger">' + response.progress.message + '</p>');
                                        if (response.result && response.result.isErrorExsist) {
                                            fetchUploadedExcelData();
                                        }
                                    }
                                }
                            },
                            error: function (xhr) {
                                clearInterval(interval);
                                $('#progressSection').addClass('d-none');
                                var response = xhr.responseJSON;
                                var errorHtml = $('#globalErrorBox');
                                errorHtml.removeClass('d-none');
                                errorHtml.html('<p class="my-1 text-danger">' + (response?.message || 'An unexpected error occurred.') + '</p>');
                            }
                        });
                    }, 1000);
                }

                function previewUploadData() {
                    var file = fileInput[0].files[0]; // Access native DOM element for file input
                    if (!file) {
                        alert("No file selected!");
                        return;
                    }

                    var formData = new FormData();
                    formData.append('file', file);

                    $.ajax({
                        type: 'POST',
                        url: grocery_germany_api_url + 'preview/excel',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('token')
                        },
                        data: formData,
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            $('.overlay').show();
                            $('#progressSection').removeClass('d-none');
                            $('#progressBar').css('width', '0%').attr('aria-valuenow', 0);
                            $('#progressMessage').text('Initiating upload...');
                        },
                        success: function (response) {
                            $('.overlay').hide();

                            console.log('previewUploadData',response);

                            // if (response.status === 1 && response.job_id) {
                            if (response.status === 1) {
                                fileInput.data('jobId', response.job_id); // Store jobId on jQuery object
                                pollProgress(response.job_id);
                            } else {
                                $('#progressSection').addClass('d-none');
                                var errorHtml = $('#uploadDataId');
                                errorHtml.removeClass('d-none');
                                errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + response.message + '</p>');
                            }
                        },
                        error: function (xhr) {
                            $('.overlay').hide();
                            $('#progressSection').addClass('d-none');
                            var errorHtml = $('#uploadDataId');
                            errorHtml.removeClass('d-none');
                            errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + (xhr.responseJSON?.message || 'An unexpected error occurred.') + '</p>');
                        }
                    });
                }

                function pollProgress(jobId) {
                    var interval = setInterval(function () {
                        $.ajax({
                            type: 'POST',
                            url: grocery_germany_api_url + 'preview/excel',
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('token')
                            },
                            data: { job_id: jobId },
                            success: function (response) {
                                $('#progressBar').css('width', response.progress.percentage + '%').attr('aria-valuenow', response.progress.percentage);
                                $('#progressMessage').text(response.progress.message);

                                if (response.progress.percentage === 100) {
                                    clearInterval(interval);
                                    $('#progressSection').addClass('d-none');

                                    // if (response.status === 1 && response.result) {
                                    if (response.status === 2) {
                                        $("#inventoryForm").removeClass("d-none");
                                        $("#file").val("");
                                        $("#clearFile").addClass("d-none");
                                        pollFetchProgress(jobId);
                                    } else {
                                        var errorHtml = $('#uploadDataId');
                                        errorHtml.removeClass('d-none');
                                        errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + response.progress.message + '</p>');
                                    }
                                }
                            },
                            error: function (xhr) {
                                clearInterval(interval);
                                $('#progressSection').addClass('d-none');
                                var errorHtml = $('#uploadDataId');
                                errorHtml.removeClass('d-none');
                                errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + (xhr.responseJSON.progress.message || 'An unexpected error occurred.') + '</p>');
                            }
                        });
                    }, 1000);
                }


                function pollFetchProgress(jobId) {
                    // var interval = setInterval(function () {
                    //     $.ajax({
                    //         type: 'POST',
                    //         url: grocery_germany_api_url + 'preview/fetch/excel/data',
                    //         headers: {
                    //             'Authorization': 'Bearer ' + localStorage.getItem('token')
                    //         },
                    //         data: { job_id: jobId },
                    //         success: function (response) {
                    //             $('#progressBar').css('width', response.progress.percentage + '%').attr('aria-valuenow', response.progress.percentage);
                    //             $('#progressMessage').text(response.progress.message);

                    //             if (response.progress.percentage === 100) {
                    //                 clearInterval(interval);
                    //                 $('#progressSection').addClass('d-none');

                    //                 if (response.status === 1 && response.result) {
                    //                     $("#inventoryForm").removeClass("d-none");
                    //                     $("#file").val("");
                    //                     $("#clearFile").addClass("d-none");
                    //                     fetchUploadedExcelData(jobId);
                    //                 } else {
                    //                     var errorHtml = $('#uploadDataId');
                    //                     errorHtml.removeClass('d-none');
                    //                     errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + response.progress.message + '</p>');
                    //                 }
                    //             }
                    //         },
                    //         error: function (xhr) {
                    //             clearInterval(interval);
                    //             $('#progressSection').addClass('d-none');
                    //             var errorHtml = $('#uploadDataId');
                    //             errorHtml.removeClass('d-none');
                    //             errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + (xhr.responseJSON?.message || 'An unexpected error occurred.') + '</p>');
                    //         }
                    //     });
                    // }, 1000);

                    var interval = setInterval(function () {
                        $.ajax({
                            type: 'POST',
                            url: grocery_germany_api_url + 'preview/fetch/excel/data',
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('token')
                            },
                            data: { job_id: jobId },
                            success: function (response) {
                                $('#progressBar').css('width', response.progress.percentage + '%').attr('aria-valuenow', response.progress.percentage);
                                $('#progressMessage').text(response.progress.message);

                                if (response.progress.percentage === 100) {
                                    clearInterval(interval);
                                    $('#progressSection').addClass('d-none');

                                    if (response.status === 1) {
                                        $("#inventoryForm").removeClass("d-none");
                                        $("#file").val("");
                                        $("#clearFile").addClass("d-none");
                                        fetchUploadedExcelData(jobId);
                                    } else {
                                        var errorHtml = $('#uploadDataId');
                                        errorHtml.removeClass('d-none');
                                        errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + response.progress.message + '</p>');
                                    }
                                }
                            },
                            error: function (xhr) {
                                clearInterval(interval);
                                $('#progressSection').addClass('d-none');
                                var errorHtml = $('#uploadDataId');
                                errorHtml.removeClass('d-none');
                                errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + (xhr.responseJSON?.message || 'An unexpected error occurred.') + '</p>');
                            }
                        });
                    }, 1000);
                }



                // function fetchUploadedExcelData(jobId) {
                //     if (!jobId) {
                //         jobId = fileInput.data('jobId');
                //     }

                //     if (!jobId) {
                //         console.error('No jobId available for fetching data');
                //         $('#uploadDataId').removeClass('d-none')
                //             .find(".error-body")
                //             .html('<p class="my-1 text-danger">No job ID available. Please upload a file again.</p>');
                //         return;
                //     }

                //     // Destroy existing DataTable
                //     if ($.fn.DataTable.isDataTable('#excelTable')) {
                //         $('#excelTable').DataTable().clear().destroy();
                //     }

                //     // Initialize DataTable
                //     $('#excelTable').DataTable({
                //         processing: true,
                //         serverSide: true,
                //         pageLength: 10, // Match default page size (or set to 100 to match your example)
                //         ajax: {
                //             url: api_url + 'preview/fetch/excel/data',
                //             type: 'POST',
                //             headers: {
                //                 'Authorization': 'Bearer ' + localStorage.getItem('token')
                //             },
                //             data: function (d) {
                //                 d.job_id = jobId;
                //                 return JSON.stringify(d);
                //             },
                //             contentType: 'application/json',
                //             beforeSend: function () {
                //                 $('.overlay').show();
                //             },
                //             dataSrc: function (response) {

                //                 $("#inventoryForm #itemCounts").text("Total Items: " + response.recordsTotal);


                //                 $('.overlay').show();

                //                 // Handle errors and store them
                //                 $("#errorList").empty();
                //                 $("#errorSection").addClass("d-none");
                //                 if (response.errors && response.status == 1) {
                //                     highlightErrors(response.errors.grid_coordinates, response.errors.messages);
                //                 }

                //                 $('#deleteSelected').prop('disabled', true);

                //                 return response.data;
                //             },
                //             complete: function () {
                //                 $('.overlay').hide();
                //             },
                //             error: function (xhr) {
                //                 $('.overlay').hide();
                //                 $('#progressSection').addClass('d-none');
                //                 $('#uploadDataId').removeClass('d-none')
                //                     .find(".error-body")
                //                     .html('<p class="my-1 text-danger">' + (xhr.responseJSON?.message || 'An unexpected error occurred.') + '</p>');
                //             }
                //         },
                //         autoWidth: false,
                //         columns: [
                //             {
                //                 data: 'flag',
                //                 render: () => '<span class="value-span"><input type="checkbox" class="rowCheckbox checkbox-lg"></span><span class="error-span"></span>',
                //                 orderable: false
                //             },
                //             {
                //                 data: 'item_name',
                //                 title: 'Item Name',
                //                 render: data => `<span class="value-span">${data || ''}</span><span class="error-span"></span>`,
                //                 className: 'editable'
                //             },
                //             {
                //                 data: 'quantity',
                //                 title: 'Quantity',
                //                 render: data => `<span class="value-span">${data || ''}</span><span class="error-span"></span>`,
                //                 className: 'editable'
                //             },
                //             {
                //                 data: 'min_stock_alert',
                //                 title: 'Minimum Stock Alert',
                //                 render: data => `<span class="value-span">${data || ''}</span><span class="error-span"></span>`,
                //                 className: 'editable'
                //             },
                //             {
                //                 data: 'mrp',
                //                 title: 'MRP',
                //                 render: data => `${currencySymbol()}<span class="value-span"> ${$.isNumeric(data) ? updateRate(data) : (data || '')}</span><span class="error-span"></span>`,
                //                 className: 'editable'
                //             },
                //             {
                //                 data: 'sale_price',
                //                 title: 'Sale Price',
                //                 render: data => `${currencySymbol()}<span class="value-span"> ${$.isNumeric(data) ? updateRate(data) : (data || '')}</span><span class="error-span"></span>`,
                //                 className: 'editable'
                //             },
                //             {
                //                 data: 'unit',
                //                 title: 'Unit',
                //                 render: data => `<select class="form-select" name="unit">
                //                     <option value="" selected>Select Unit</option>
                //                     ${unitList.map(unit => `<option value="${unit.value}" ${unit.value === (data ? data.toUpperCase() : '') ? 'selected' : ''}>${unit.label}</option>`).join('')}
                //                 </select>`
                //             },
                //             {
                //                 data: 'gst',
                //                 title: 'GST',
                //                 render: data => `<span class="value-span">${data || ''}</span><span class="error-span"></span>`,
                //                 className: 'editable'
                //             },
                //             {
                //                 data: 'cess',
                //                 title: 'CESS',
                //                 render: data => `<span class="value-span">${data || ''}</span><span class="error-span"></span>`,
                //                 className: 'editable'
                //             }
                //         ],
                //         columnDefs: [
                //             { targets: 0, orderable: false, width: '50px' },
                //             { targets: 1, width: '200px' },
                //             { targets: 2, width: '100px' },
                //             { targets: 3, width: '100px' },
                //             { targets: 6, width: '150px' },
                //             { targets: 7, width: '150px' }
                //         ],
                //         createdRow: function (row, data) {
                //             $(row).attr('data-id', data.id || '');
                //         },
                //         drawCallback: function (settings) {
                //             var response = settings.json;
                //             $("#errorList").empty();
                //             $("#errorSection").addClass("d-none");
                //             $("#inventoryForm #itemCounts").text("Total Items: " + (response.recordsTotal || 0));
                //             $('#deleteSelected').prop('disabled', true);

                //             if (response.status === 0 || response.errors?.messages?.length > 0) {
                //                 highlightErrors(response.errors.grid_coordinates, response.errors.messages);
                //             }
                //         }
                //     });
                // }


                function fetchUploadedExcelData(jobId) {
                    if (!jobId) {
                        jobId = fileInput.data('jobId'); // Retrieve jobId from jQuery object
                    }

                    if (!jobId) {
                        console.error('No jobId available for fetching data');
                        var errorHtml = $('#uploadDataId');
                        errorHtml.removeClass('d-none');
                        errorHtml.find(".error-body").html('<p class="my-1 text-danger">No job ID available. Please upload a file again.</p>');
                        return;
                    }

                    // Clear previous DataTable if it exists
                    if ($.fn.DataTable.isDataTable('#excelTable')) {
                        $('#excelTable').DataTable().clear().destroy();
                    }

                    // Initialize DataTable with server-side processing
                    $('#excelTable').DataTable({
                        autoWidth: false,
                        processing: true,
                        serverSide: true, // Enable server-side processing

                        order: [
                            [1, 'asc']
                        ],


                        // Enable scrolling with fixed height
                        scrollY: '400px',
                        scrollX: true,
                        scrollCollapse: true,


                        ajax: {
                            url: grocery_germany_api_url + 'preview/fetch/excel/data',
                            type: 'POST',
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('token')
                            },
                            data: function (d) {
                                d.job_id = jobId; // Send job_id with each request
                                return JSON.stringify(d); // Stringify for contentType: application/json
                            },
                            contentType: 'application/json',
                            dataSrc: function (response) {

                                console.log('response',response);
                                console.log('response',response.data);

                                $("#inventoryForm #itemCounts").text("Total Items: " + response.recordsTotal);


                                $('.overlay').show();

                                // Handle errors and store them
                                $("#errorList").empty();
                                $("#errorSection").addClass("d-none");
                                if (response.errors && response.status == 1) {
                                    highlightErrors(response.errors.grid_coordinates, response.errors.messages);
                                }

                                $('#deleteSelected').prop('disabled', true);

                                return response.data;
                            },
                            beforeSend: function () {
                                $('.overlay').show();
                            },
                            complete: function () {
                                $('.overlay').hide();
                            },
                            error: function (xhr) {
                                $('.overlay').hide();
                                $('#progressSection').addClass('d-none');
                                var errorHtml = $('#uploadDataId');
                                errorHtml.removeClass('d-none');
                                errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + (xhr.responseJSON?.message || 'An unexpected error occurred.') + '</p>');
                            }
                        },
                        columns: [
                            {
                                data: 'flag',
                                render: () => '<span class="value-span"><input type="checkbox" class="rowCheckbox checkbox-lg"></span><span class="error-span"></span>',
                                orderable: false
                            },
                            {
                                data: 'item_name',
                                // title: 'Item Name',
                                render: data => `<span class="value-span">${data || ''}</span><span class="error-span"></span>`,
                                className: 'editable'
                            },
                            {
                                data: 'quantity',
                                // title: 'Quantity',
                                render: data => `<span class="value-span numericField">${parseFloat(data).toFixed(2) || ''}</span><span class="error-span"></span>`,
                                className: 'editable'
                            },
                            {
                                data: 'min_stock_alert',
                                // title: 'Minimum Stock Alert',
                                render: data => `<span class="value-span numericField">${parseFloat(data).toFixed(2) || ''}</span><span class="error-span"></span>`,
                                className: 'editable'
                            },
                            {
                                data: 'mrp',
                                // title: 'MRP',
                                render: data => `${currencySymbol()}<span class="value-span numericField"> ${$.isNumeric(data) ? updateRate(parseFloat(data).toFixed(2)) : (parseFloat(data).toFixed(2) || '')}</span><span class="error-span"></span>`,
                                className: 'editable'
                            },
                            {
                                data: 'sale_price',
                                // title: 'Sale Price',
                                render: data => `${currencySymbol()}<span class="value-span numericField"> ${$.isNumeric(data) ? updateRate(parseFloat(data).toFixed(2)) : (parseFloat(data).toFixed(2) || '')}</span><span class="error-span"></span>`,
                                className: 'editable'
                            },
                            {
                                data: 'unit',
                                // title: 'Unit',
                                render: data => `<select class="form-select" name="unit">
                                    <option value="" selected>Select Unit</option>
                                    ${unitList.map(unit => `<option value="${unit.value.toUpperCase()}" ${unit.value.toUpperCase() === (data ? data.toUpperCase() : '') ? 'selected' : ''}>${unit.label}</option>`).join('')}
                                </select>`
                            },
                            {
                                data: 'vat',
                                render: data => `<span class="value-span numericField">${data || ''}</span><span class="error-span"></span>`,
                                className: 'editable'
                            },
                        ],
                        columnDefs: [
                            { targets: 0, orderable: false },
                            // { targets: 0, width: '50px', className: 'dt-width-50' },
                            // { targets: 1, width: '200px', className: 'dt-width-200' },
                            // { targets: 2, width: '50px', className: 'dt-width-100' },
                            // { targets: 3, width: '50px', className: 'dt-width-100' },
                            // { targets: 6, width: '150px', className: 'dt-width-100' },
                            // { targets: 7, width: '150px', className: 'dt-width-100' }
                        ],
                        createdRow: function (row, data, dataIndex) {
                            $(row).attr('data-id', data.id || '');
                        },
                        drawCallback: function (settings) {
                            // Retrieve the response from the API for error handling
                            var response = settings.json;
                            $("#errorList").empty();
                            $("#errorSection").addClass("d-none");
                            $("#inventoryForm #itemCounts").text("Total Items: " + (response.recordsTotal || 0));
                            $('#deleteSelected').prop('disabled', true);

                            if (response && response.errors && response.status == 1) {
                                console.log('drawCallback: Triggering highlightErrors');
                                highlightErrors(response.errors.grid_coordinates, response.errors.messages);
                            }
                        }
                    });
                }


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


    //             function fetchUploadedExcelData(jobId) {
    //     if (!jobId) {
    //         jobId = fileInput.data('jobId'); // Retrieve jobId from jQuery object
    //     }

    //     if (!jobId) {
    //         console.error('No jobId available for fetching data');
    //         var errorHtml = $('#uploadDataId');
    //         errorHtml.removeClass('d-none');
    //         errorHtml.find(".error-body").html('<p class="my-1 text-danger">No job ID available. Please upload a file again.</p>');
    //         return;
    //     }

    //     // Clear previous DataTable if it exists
    //     if ($.fn.DataTable.isDataTable('#excelTable')) {
    //         $('#excelTable').DataTable().clear().destroy();
    //     }

    //     // Initialize DataTable with server-side processing
    //     $('#excelTable').DataTable({
    //         processing: true,
    //         serverSide: true, // Enable server-side processing
    //         ajax: {
    //             url: api_url + 'preview/fetch/excel/data',
    //             type: 'POST',
    //             headers: {
    //                 'Authorization': 'Bearer ' + localStorage.getItem('token')
    //             },
    //             data: function (d) {
    //                 d.job_id = jobId; // Send job_id with each request
    //                 return JSON.stringify(d); // Stringify for contentType: application/json
    //             },
    //             contentType: 'application/json',
    //             dataSrc: function (response) {
    //                 console.log('fetchUploadedExcelData response:', response);
    //                 console.log('response.data:', response.data);
    //                 console.log('recordsTotal:', response.recordsTotal);
    //                 console.log('status:', response.status);
    //                 console.log('errors:', response.errors);

    //                 if (!response.data || !Array.isArray(response.data) || response.data.length === 0) {
    //                     console.warn('No data returned or invalid data format');
    //                     $("#inventoryForm #itemCounts").text("Total Items: 0");
    //                     $("#errorList").empty();
    //                     $("#errorSection").removeClass("d-none").html('<li>No data available to display.</li>');
    //                     return [];
    //                 }

    //                 $("#inventoryForm #itemCounts").text("Total Items: " + (response.recordsTotal || 0));

    //                 $('.overlay').show();

    //                 // Handle errors and store them
    //                 $("#errorList").empty();
    //                 $("#errorSection").addClass("d-none");
    //                 if (response.errors && response.errors.coordinates.length > 0) {
    //                     console.log('dataSrc: Triggering highlightErrors');
    //                     highlightErrors(response.errors.grid_coordinates, response.errors.messages);
    //                 }

    //                 $('#deleteSelected').prop('disabled', true);

    //                 return response.data;
    //             },
    //             beforeSend: function () {
    //                 $('.overlay').show();
    //             },
    //             complete: function () {
    //                 $('.overlay').hide();
    //             },
    //             error: function (xhr) {
    //                 $('.overlay').hide();
    //                 $('#progressSection').addClass('d-none');
    //                 var errorHtml = $('#uploadDataId');
    //                 errorHtml.removeClass('d-none');
    //                 errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + (xhr.responseJSON?.message || 'An unexpected error occurred.') + '</p>');
    //             }
    //         },
    //         autoWidth: false,
    //         columns: [
    //             {
    //                 data: 'flag',
    //                 render: () => '<span class="value-span"><input type="checkbox" class="rowCheckbox checkbox-lg"></span><span class="error-span"></span>',
    //                 orderable: false
    //             },
    //             {
    //                 data: 'item_name',
    //                 title: 'Item Name',
    //                 render: data => `<span class="value-span">${data || ''}</span><span class="error-span"></span>`,
    //                 className: 'editable'
    //             },
    //             {
    //                 data: 'quantity',
    //                 title: 'Quantity',
    //                 render: data => `<span class="value-span">${data || ''}</span><span class="error-span"></span>`,
    //                 className: 'editable'
    //             },
    //             {
    //                 data: 'min_stock_alert',
    //                 title: 'Minimum Stock Alert',
    //                 render: data => `<span class="value-span">${data || ''}</span><span class="error-span"></span>`,
    //                 className: 'editable'
    //             },
    //             {
    //                 data: 'mrp',
    //                 title: 'MRP',
    //                 render: data => `${currencySymbol()}<span class="value-span"> ${$.isNumeric(data) ? updateRate(data) : (data || '')}</span><span class="error-span"></span>`,
    //                 className: 'editable'
    //             },
    //             {
    //                 data: 'sale_price',
    //                 title: 'Sale Price',
    //                 render: data => `${currencySymbol()}<span class="value-span"> ${$.isNumeric(data) ? updateRate(data) : (data || '')}</span><span class="error-span"></span>`,
    //                 className: 'editable'
    //             },
    //             {
    //                 data: 'unit',
    //                 title: 'Unit',
    //                 render: data => `<select class="form-select" name="unit">
    //                     <option value="" selected>Select Unit</option>
    //                     ${unitList.map(unit => `<option value="${unit.value}" ${unit.value === (data ? data.toUpperCase() : '') ? 'selected' : ''}>${unit.label}</option>`).join('')}
    //                 </select>`
    //             },
    //             {
    //                 data: 'gst',
    //                 title: 'GST',
    //                 render: data => `<span class="value-span">${data || ''}</span><span class="error-span"></span>`,
    //                 className: 'editable'
    //             },
    //             {
    //                 data: 'cess',
    //                 title: 'CESS',
    //                 render: data => `<span class="value-span">${data || ''}</span><span class="error-span"></span>`,
    //                 className: 'editable'
    //             }
    //         ],
    //         columnDefs: [
    //             { targets: 0, orderable: false },
    //             { targets: 0, width: '50px', className: 'dt-width-50' },
    //             { targets: 1, width: '200px', className: 'dt-width-200' },
    //             { targets: 2, width: '50px', className: 'dt-width-100' },
    //             { targets: 3, width: '50px', className: 'dt-width-100' },
    //             { targets: 6, width: '150px', className: 'dt-width-100' },
    //             { targets: 7, width: '150px', className: 'dt-width-100' }
    //         ],
    //         createdRow: function (row, data, dataIndex) {
    //             $(row).attr('data-id', data.id || '');
    //         },
    //         drawCallback: function (settings) {
    //             var response = settings.json;
    //             console.log('drawCallback response:', response);
    //             $("#errorList").empty();
    //             $("#errorSection").addClass("d-none");
    //             $("#inventoryForm #itemCounts").text("Total Items: " + (response.recordsTotal || 0));
    //             $('#deleteSelected').prop('disabled', true);

    //             if (response && response.errors && response.errors.coordinates.length > 0) {
    //                 console.log('drawCallback: Triggering highlightErrors');
    //                 highlightErrors(response.errors.grid_coordinates, response.errors.messages);
    //             }
    //         }
    //     });
    // }

                function highlightErrors(gridCoordinates, messages) {
                    console.log('highlightErrors called with:', { gridCoordinates, messages });

                    // Clear previous highlights
                    $("#excelTable tbody td").removeClass('error-cell').css({
                        'border': '',
                        'background-color': ''
                    });

                    // Validate inputs
                    if (!gridCoordinates || !Array.isArray(gridCoordinates) || gridCoordinates.length === 0) {
                        console.warn('No valid grid coordinates provided or empty array');
                        let errorList = $("#errorList");
                        errorList.empty();
                        if (messages && Array.isArray(messages) && messages.length > 0) {
                            messages.forEach(message => {
                                errorList.append(`<li>${message}</li>`);
                            });
                            $("#errorSection").removeClass("d-none");
                        } else {
                            errorList.append(`<li>Validation Error: No specific errors provided</li>`);
                            $("#errorSection").removeClass("d-none");
                        }
                        return;
                    }

                    // Highlight cells
                    gridCoordinates.forEach(coordinate => {
                        if (!coordinate || typeof coordinate !== 'string') {
                            console.warn('Invalid coordinate format:', coordinate);
                            return;
                        }

                        const [rowIndex, colIndex] = coordinate.split(',').map(num => parseInt(num, 10));
                        console.log('Processing coordinate - Row:', rowIndex, 'Column:', colIndex);

                        // Ensure row and cell exist
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
                                console.warn('Cell not found at Row:', rowIndex, 'Column:', colIndex);
                            }
                        } else {
                            console.warn('Row not found at index:', rowIndex);
                        }
                    });

                    // Update error list
                    let errorList = $("#errorList");
                    errorList.empty();
                    if (messages && Array.isArray(messages) && messages.length > 0) {
                        messages.forEach(message => {
                            errorList.append(`<li>${message}</li>`);
                        });
                        $("#errorSection").removeClass("d-none");
                    } else {
                        errorList.append(`<li>Validation errors detected in the uploaded data</li>`);
                        $("#errorSection").removeClass("d-none");
                    }
                }

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

                $('#uploadBulkData').submit(function (e) {
                    e.preventDefault();
                    previewUploadData();
                });

                // Drag and Drop
                dropArea.on("dragover dragenter", function (e) {
                    e.preventDefault();
                    $(this).addClass("bg-light");
                });

                dropArea.on("dragleave dragend drop", function () {
                    $(this).removeClass("bg-light");
                });

                dropArea.on("drop", function (e) {
                    e.preventDefault();
                    $(this).removeClass("bg-light");

                    let files = e.originalEvent.dataTransfer.files;
                    if (files.length > 0) {
                        fileInput.prop("files", files);
                        fileNameDisplay.text("Selected File: " + files[0].name);
                        clearButton.removeClass("d-none");
                    }
                });

                fileInput.on("change", function () {
                    if (this.files.length > 0) {
                        fileNameDisplay.text("Selected File: " + this.files[0].name);
                        clearButton.removeClass("d-none");
                    }
                });

                clearButton.on("click", function () {
                    fileInput.val("");
                    fileNameDisplay.text("");
                    $(this).addClass("d-none");
                    fileInput.data('jobId', null); // Clear jobId on file clear
                });

                // Multiple Delete
                $('#selectAll').on('click', function () {
                    $('.rowCheckbox').prop('checked', this.checked);
                    toggleDeleteButton();
                });

                $(document).on('click', '.rowCheckbox', function () {
                    if ($('.rowCheckbox:checked').length === $('.rowCheckbox').length) {
                        $('#selectAll').prop('checked', true);
                    } else {
                        $('#selectAll').prop('checked', false);
                    }
                    toggleDeleteButton();
                });

                function toggleDeleteButton() {
                    if ($('.rowCheckbox:checked').length > 0) {
                        $('#deleteSelected').prop('disabled', false);
                    } else {
                        $('#deleteSelected').prop('disabled', true);
                    }
                }

                $("#deleteSelected").click(function () {
                    let selectedIds = [];
                    $('.rowCheckbox:checked').each(function () {
                        selectedIds.push($(this).closest("tr").data('id'));
                        $(".rowCheckbox:checked").closest("tr").remove();
                    });

                    if (selectedIds.length > 0) {
                        deleteSelectedItems(selectedIds);
                    } else {
                        alert("Please select at least one item to delete.");
                    }
                });

                function deleteSelectedItems(itemIds) {
                    console.log('deleteSelectedItems', itemIds);

                    if (!confirm("Are you sure you want to delete the selected items?")) return;

                    $.ajax({
                        url: grocery_germany_api_url + 'upload-data/multiple-delete',
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('token'),
                            'Content-Type': 'application/json'
                        },
                        data: JSON.stringify({ ids: itemIds }),
                        success: function (response) {
                            $("#selectAll").prop("checked", false);
                            fetchUploadedExcelData(fileInput.data('jobId')); // Use stored jobId
                        },
                        error: function (error) {
                            alert("Failed to delete items. Please try again.");
                            console.error("Error:", error);
                        }
                    });
                }

                function updateCellData(formData) {
                    $.ajax({
                        url: grocery_germany_api_url + 'upload-data/update-cell-data',
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
                                fetchUploadedExcelData(fileInput.data('jobId')); // Use stored jobId
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

                            if (errorResponse.data) {
                                let errorMessages = '';
                                $.each(errorResponse.data, function (field, messages) {
                                    let cellIndex = columnIndex(field);
                                    let errorMessage = messages[0];
                                    errorMessages += `<p>${errorMessage}</p>`;
                                });

                                $("#errorSection").removeClass("d-none");
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
                        var newValue = isSelect ? inputField.find('option:selected').text().trim() : inputField.val().trim();

                        var cell = inputField.closest('td');
                        var cellIndex = cell.index();
                        var row = cell.closest('tr');

                        var span = cell.find('.value-span:first');
                        if (span.length) {
                            span.text(newValue);
                        } else {
                            console.warn('No value-span found in cell');
                        }

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

                            updateCellData(formData);
                        }, 300));
                    } catch (error) {
                        console.error('Error processing cell update:', error);
                    }
                });

                $(document).on('change', '#excelTable select.form-select', function (e) {
                    try {
                        var inputField = $(this);
                        var isSelect = inputField.is('select');
                        var newValue = isSelect ? inputField.find('option:selected').text().trim() : inputField.val().trim();

                        var cell = inputField.closest('td');
                        var cellIndex = cell.index();
                        var row = cell.closest('tr');

                        var span = cell.find('.value-span:first');
                        if (span.length) {
                            span.val(newValue);
                        } else {
                            console.warn('No value-span found in cell');
                        }

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

                            updateCellData(formData);
                        }, 300));
                    } catch (error) {
                        console.error('Error processing cell update:', error);
                    }
                });

                function getCellValue(row, index) {
                    var span = row.find(".value-span").eq(index);
                    return span.length ? span.text().trim() : '';
                }


                // Check for active jobs when the page loads
                checkActiveJob();

                function checkActiveJob() {
                    $.ajax({
                        type: 'POST',
                        url: grocery_germany_api_url + 'get-active-job',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('token'),
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        contentType: 'application/json',
                        data: JSON.stringify({}),
                        beforeSend: function () {
                            $('.overlay').show();
                        },
                        success: function (response) {
                            $('.overlay').hide();
                            if (response.status === 1 && response.job_id) {
                                $('#progressSection').removeClass('d-none');
                                $('#progressBar').css('width', response.progress.percentage + '%').attr('aria-valuenow', response.progress.percentage);
                                $('#progressMessage').text(response.progress.message);

                                // Store jobId for subsequent operations
                                $("#file").data('jobId', response.job_id);

                                // Start polling based on job type
                                if (response.job_type === 'upload') {
                                    pollProgress(response.job_id);
                                } else if (response.job_type === 'export') {
                                    pollExportProgress(response.job_id);
                                }
                            }
                        },
                        error: function (xhr) {
                            $('.overlay').hide();
                            console.error('Error checking active job:', xhr.responseJSON?.message || 'An unexpected error occurred.');
                        }
                    });
                }


            });
        </script>
@endsection