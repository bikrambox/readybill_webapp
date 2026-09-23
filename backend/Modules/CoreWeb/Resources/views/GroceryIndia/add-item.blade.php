@extends('coreweb::layouts.groceryIndia')
@section('title', "{{ __('inventroy_page.Add Inventory') }}")
@section('content')


        <div class="pagetitle">
            <h1>{{ __('inventory_page.Add Inventory') }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('inventory_page.Inventory') }}</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">

                <div class="col-xl-8">
    
                    </div>
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body mt-4">

                            <form id="itemForm" class="row g-3">

                                <div class="col-12 text-start">
                                    <button type="button" class="btn btn-secondary exportData"
                                        style="margin-right: 10px;">{{ __('inventory_page.Download Data') }}</button>

                                    <a href="upload-data" class="btn btn-info" target="_blank">{{ __('inventory_page.Upload Data') }}</a>

                                    <a class="btn btn-link text-primary pt-4" href="{{  locale_route('data.upload.instruction') }}"
                                        role="button" target="_blank">{{ __('inventory_page.How to upload inventory data in CSV or XLS format?') }}</a>
                                </div>

                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <label for="item_name" class="form-label">
                                                {{ __('common.Item Name') }}<span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="item_name" name="item_name"
                                                placeholder="{{ __('common.Item Name') }}" autofocus />
                                            <span class="text-danger item_name-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row my-3">
                                        <div class="col-md-3 my-3 stockQuantuty-div">
                                            <label for="stock" class="form-label">{{ __('inventory_page.Stock Quantity') }}<span
                                                    class="text-danger stockQuantuty-required">*</span></label>
                                            <input type="text" class="form-control numericField" id="quantity" name="quantity"
                                                placeholder="{{ __('inventory_page.Stock Quantity') }}" aria-describedby="stockNote" />
                                            <span class="text-danger quantity-error d-none"></span>
                                        </div>

                                        <div class="col-md-3 my-3 minimumStockAlert-div d-none">
                                            <label for="stock" class="form-label">{{ __('inventory_page.Minimum Stock Alert') }}</label>
                                            <input type="text" class="form-control numericField" id="min_stock_alert"
                                                name="min_stock_alert" placeholder="{{ __('inventory_page.Minimum Stock Alert') }}"
                                                aria-describedby="stockNote" />
                                            <span class="text-danger min_stock_alert-error d-none"></span>
                                        </div>

                                        <div class="col-md-3 my-3">
                                            <label for="unit" class="form-label">{{ __('inventory_page.Unit') }}<span class="text-danger">*</span></label>
                                            <select id="unit" name="unit" class="form-select">
                                                <option disabled selected value="">{{ __('inventory_page.Select Unit') }}</option>
                                                @foreach(config('india_units.units') as $fullName => $shortName)
                                                    <option data-full="{{ $fullName }}" value="{{ $shortName }}">{{ $fullName }}
                                                        ({{ $shortName }})</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger unit-error d-none"></span>
                                        </div>

                                    </div>

                                    <div class="row my-3">
                                        <div class="col-md-3 my-3 hsn-div">
                                            <label for="hsn" class="form-label">{{ __('inventory_page.HSN/ SAC Code') }}<span
                                                    class="text-danger hsn-required">*</span></label>
                                            <input type="text" class="form-control" id="hsn" name="hsn" placeholder="{{ __('inventory_page.HSN/ SAC Code') }}"
                                                aria-describedby="stockNote" />
                                            <span class="text-danger hsn-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row my-3">
                                        <div class="col-md-3 my-3 mrp-div">
                                            <label for="sale_price" class="form-label">{{ __('inventory_page.MRP') }}<span
                                                    class="text-danger mrp-required">*</span><span
                                                    class="text-danger"></span></label>
                                            <div class="input-group">
                                                <span class="input-group-text currency">₹</span>
                                                <input type="text" class="form-control numericField" name="mrp" id="mrp"
                                                    placeholder="{{ __('inventory_page.Price') }}" />
                                                <span class="input-group-text unitSelected"></span>
                                            </div>
                                            <span class="text-danger mrp-error d-none"></span>
                                        </div>

                                        <div class="col-md-3 my-3">
                                            <label for="sale_price" class="form-label">{{ __('inventory_page.Rate') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text currency">₹</span>
                                                <input type="text" class="form-control numericField" name="sale_price"
                                                    id="sale_price" placeholder="{{ __('inventory_page.Rate') }}" />
                                                <span class="input-group-text unitSelected"></span>
                                            </div>
                                            <span class="text-danger sale_price-error d-none"></span>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-12">
                                    <div class="row taxSection my-2" id="row-1" data-id="1">
                                        <div class="col-4 col-md-2">
                                            <label for="tax" class="form-label">{{ __('inventory_page.Tax') }} <span
                                                    class="text-danger">*</span></label></label>
                                            <!-- <input type="text" class="form-control" name="tax1" id="tax1" placeholder="Tax" /> -->
                                            <select id="tax1" name="tax1" class="form-select">
                                                <option disabled selected value="">{{ __('inventory_page.Select Tax') }}</option>
                                                @foreach(config('india_tax.taxes') as $tax)
                                                    <option value="{{ $tax }}">{{ $tax }}</option>
                                                @endforeach
                                            </select>
                                            <!-- <span class="text-danger tax1-error d-none"></span> -->
                                        </div>
                                        <div class="col-5 col-md-2">
                                            <label for="rate" class="form-label">{{ __('inventory_page.Rate') }} <span
                                                    class="text-danger">*</span></label></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control numericField" name="rate1" id="rate1"
                                                    placeholder="{{ __('inventory_page.Rate') }}" value="0" />
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <!-- <span class="text-danger rate1-error d-none"></span> -->
                                        </div>
                                        <div class="col-1 col-md-2 d-flex align-items-end">
                                            <button class="btn btn-primary addMore" type="button">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                            <button class="btn btn-danger deleteRow d-none" type="button">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                        <div class="col-12">
                                            <span class="text-danger tax1-error d-none"></span>
                                            <span class="text-danger rate1-error d-none"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 text-start">
                                    <button type="submit" class="btn btn-primary">{{ __('common.Submit') }}</button>
                                    <a class="btn btn-danger" href="home" role="button">{{ __('common.Cancel') }}</a>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- IMPORT MODAL -->
        @include('coreweb::modals.importData')
        <!-- IMPORT MODAL -->

        <!-- PREVIEW MODAL -->
        <div class="modal fade" id="previewModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Preview Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <table class="table" id="preview-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Item Name</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col">MRP</th>
                                    <th scope="col">Sale Price</th>
                                    <th scope="col">Unit</th>
                                    <th scope="col">HSN</th>
                                    <th scope="col">GST</th>
                                    <th scope="col">CESS</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
        <!-- PREVIEW MODAL -->

@endsection

@section('scripts')
    @parent
    <!-- Include parent scripts -->
    <script>
        $(document).ready(function () {

            // ----------------------------------- ADD ITEM CODE --------------------------------------

            var count = 1;

            // Function to update count
            function updateCount() {
                count = $(".taxSection").length;
            }

            // Function to renumber rows
            function renumberRows() {
                $(".taxSection").each(function (index) {
                    var newIndex = index + 1;
                    console.log('Row ' + (index + 1) + ': newIndex = ' + newIndex);

                    $(this).attr("id", "row-" + newIndex);

                    // $(this).find("input[name^='tax']").attr("name", "tax" + newIndex);
                    // $(this).find("input[name^='rate']").attr("name", "rate" + newIndex);

                    $(this).find("#tax" + (newIndex - 1)).attr("id", "tax" + newIndex);
                    $(this).find('select').attr('name', "tax" + newIndex);
                    $(this).find("input[name='tax" + (newIndex - 1) + "']").attr("name", "tax" +
                        newIndex);

                    $(this).find("#rate" + (newIndex - 1)).attr("id", "rate" + newIndex);
                    $(this).find("input[name='rate" + (newIndex) + "']").attr("name", "rate" +
                        newIndex);

                    $(this).find("input[name='rate" + (newIndex - 1) + "']").val(0);

                    $(this).find('.addMore').attr("data-id", newIndex);

                    $(this).find('.tax1-error').attr("class", "tax" + newIndex + "-error text-danger");
                    $(this).find('.rate1-error').attr("class", "rate" + newIndex +
                        "-error text-danger");

                    // $(this).find('.tax1-error').attr("class", "text-danger");
                    // $(this).find('.rate1-error').attr("class", "text-danger");


                    $(this).find(".tax" + newIndex + "-error").addClass("d-none");
                    $(this).find(".rate" + newIndex + "-error").addClass("d-none");

                    resetForm();

                    // $(this).find('#tax'+newIndex).removeClass("border border-2 border-danger");
                    // $(this).find('#rate'+newIndex).removeClass("border border-2 border-danger");

                    // $(this).find('.tax'+newIndex+"-error").addClass("d-none");
                    // $(this).find('.rate'+newIndex+"-error").addClass("d-none");

                });
            }

            // Add more input fields when the "Add" button is clicked
            // $(".addMore").on("click", function () {
            $(document.body).on("click", '.addMore', function () {
                if (count < 2) {
                    var lastSection = $(".taxSection:last");
                    var newSection = lastSection.clone(true);
                    newSection.find("input").val("");
                    newSection.find('.deleteRow').removeClass('d-none');
                    newSection.find('.addMore').addClass('d-none');

                    newSection.insertAfter(lastSection);
                    renumberRows();
                    updateCount();
                    console.log('count', count);
                } else {
                    $.toast({
                        heading: 'Error',
                        text: "Only 2 tax rates fields are allowed",
                        icon: 'error',
                        loader: true,
                        position: 'top-right',
                        loaderBg: '#9EC600'
                    });
                }
            });

            // Delete the corresponding row when the "Delete" button is clicked
            $(document).on("click", ".deleteRow", function () {
                var section = $(this).closest(".taxSection");
                console.log('count', count);
                if (count > 1) {
                    count--;
                    section.remove();
                    renumberRows();
                    updateCount();
                } else {
                    $.toast({
                        heading: 'Error',
                        text: "At least one tax rate field is required",
                        icon: 'error',
                        loader: true,
                        position: 'top-right',
                        loaderBg: '#9EC600'
                    });
                }
            });

            $(document).on("change", "#unit", function () {
                $(".unitSelected").text("per " + $(this).val());
            });

            // ----------------------------------- ADD ITEM CODE --------------------------------------

            // ----------------------------------- STORE ITEM ON DATABASE --------------------------------------
            $('#itemForm').submit(function (e) {

                e.preventDefault();

                console.log('count', count);

                var formData = new FormData();
                formData.append('item_name', $('#item_name').val());
                formData.append('quantity', $('#quantity').val());
                formData.append('min_stock_alert', $('#min_stock_alert').val());
                formData.append('mrp', $('#mrp').val());
                formData.append('hsn', $('#hsn').val());

                if ($('#unit').val()) {
                    formData.append('short_unit', $('#unit').val());
                    formData.append('full_unit', $('#unit option:selected').data('full'));
                }

                formData.append('sale_price', $('#sale_price').val());

                if ($('#tax1').val()) {
                    formData.append('tax1', $('#tax1').val());
                }


                if ($('#rate1').val()) {
                    formData.append('rate1', $('#rate1').val());
                }

                if ($('#tax2').val()) {
                    formData.append('tax2', $('#tax2').val());
                }

                if ($('#rate2').val()) {
                    formData.append('rate2', $('#rate2').val());
                }

                console.log('formData', formData);

                $.ajax({
                    url: grocery_india_api_url + `add-item`,
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        
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

                            $("#item_name").val("");
                            $("#quantity").val("");

                            $("#min_stock_alert").val("");
                            $(".minimumStockAlert-div").addClass("d-none");

                            $("#mrp").val("");
                            $("#unit").val("");
                            $("#sale_price").val("");

                            $("#tax1").val("");
                            $("#rate1").val("");

                            $("#tax2").val("");
                            $("#rate2").val("");

                            $("#row-2").remove();

                            $("#hsn").val("");

                            $(".unitSelected").text("");

                            resetForm();
                            $(".overlay").hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);
                        handleErrors(error);
                    }
                });
            });

            function resetForm() {
                // Reset form fields
                $("#item_name, #quantity,#min_stock_alert,#unit, #mrp, #sale_price, #tax1, #rate1, #tax2, #rate2, #hsn")
                    .removeClass('border border-2 border-danger');
                $(".item_name-error, .quantity-error, .min_stock_alert-error, .unit-error, .mrp-error, .sale_price-error, .tax1-error, .rate1-error, .tax2-error, .rate2-error, .hsn-error")
                    .addClass('d-none');
            }

            function handleErrors(error) {
                // Use switch case for displaying error messages
                resetForm();

                console.log('handleErrors',error);

                $.each(error.data.errors, function (key, value) {
                    switch (key) {
                        case 'item_name':
                        case 'stock':
                        case 'quantity':
                        case 'min_stock_alert':
                        case 'mrp':
                        case 'sale_price':
                        case 'tax1':
                        case 'rate1':
                        case 'tax2':
                        case 'rate2':
                        case 'hsn':

                            if ((key === 'item_name') && (value[0] == 'The item name has already been taken.')) {
                                $("#errorModal").modal('show');
                                // $("#errorModal #errorMessage").text(value[0]);
                            }

                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;
                        case 'full_unit':
                            $("#unit").addClass('border border-2 border-danger');
                            $(".unit-error").removeClass('d-none');
                            $(".unit-error").text("Select Unit Option");
                            break;
                        default:
                            console.warn('Unhandled error key:', key);
                            break;
                    }
                });
            }


            function showErrorToast(heading, text) {
                $.toast({
                    heading: 'Error',
                    text: text,
                    icon: 'error',
                    loader: true,
                    position: 'top-right',
                    loaderBg: '#9EC600'
                });
            }

            $(document).on('keypress', '.numericField', function (event) {
                // Allow only numbers and decimal point
                var charCode = (event.which) ? event.which : event.keyCode;
                if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
                    // Prevent non-numeric input and display an alert
                    // alert("Please enter only numbers and decimals.");
                    $.toast({
                        heading: 'Error',
                        text: "Only numeric value are allow",
                        icon: 'error',
                        loader: true,
                        position: 'top-right',
                        loaderBg: '#9EC600'
                    });
                    return false;
                }

                // Allow decimal point only once
                if (charCode == 46 && $(this).val().indexOf('.') !== -1) {
                    return false;
                }
            });


            // ----------------------------------- STORE ITEM ON DATABASE --------------------------------------

            // ----------------------------------- EXPORT DATA ON CSV --------------------------------------
            $(".exportData").on('click', function (e) {
                $.ajax({
                    type: 'GET',
                    url: grocery_india_api_url + `export`,
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
            // ----------------------------------- EXPORT DATA ON CSV --------------------------------------

            $('#importModal').on('hidden.bs.modal', function () {
                resetErrorDiv("importModal");
                $("#file").val("");

                fileInput.val(""); // Reset input
                fileNameDisplay.text(""); // Clear file name
                $("#clearFile").addClass("d-none"); // Hide clear button

                $("#importModal").modal('hide');
            });


            $('#quantity').on('input', function () {

                if ($(this).val() == '') {
                    $(".minimumStockAlert-div").addClass("d-none");
                    $("#min_stock_alert").val("");
                }
                else if ( ($(this).val() != '') && ($(this).val() != 0) ) {
                    $(".minimumStockAlert-div").removeClass("d-none");
                }

            });

            $('#mrp').on('input', function () {

                $("#sale_price").val($("#mrp").val());

            });

        });

    </script>
@endsection