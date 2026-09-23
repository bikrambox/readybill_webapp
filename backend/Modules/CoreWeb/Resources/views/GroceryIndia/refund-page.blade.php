@extends('coreweb::layouts.groceryIndia')
@section('title', "{{ __('common.Refund') }}")
@section('content')
                                        <div class="pagetitle">
                                            <h1>{{ __('common.Refund') }}</h1>
                                            <nav>
                                                <ol class="breadcrumb">
                                                    <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                                                    <li class="breadcrumb-item active">{{ __('common.Refund') }}</li>
                                                </ol>
                                            </nav>
                                        </div><!-- End Page Title -->

                                        <section class="section profile">
                                            <div class="row">

                                                <div class="col-xl-8">
                                                   @include('coreweb::components/error', [
                                                    'heading' => __('common.Subscription Alert'),
                                                    'modalIdAttribute' => 'subscripitonErrorId',
                                                ])
                                                </div>

                                                <div class="col-xl-8">

                                                    <div class="card">
                                                        <div class="card-body pt-3 sellSection">

                                                            <!-- Profile Edit Form -->
                                                            <form id="refund-form">

                                                            @include('coreweb::components/error', ['heading' => 'Error'])


                                                                <div class="row mb-3">
                                                                    <label for="fullName" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Item Name') }}</label>
                                                                    <div class="col-md-8 col-lg-9 searchInputSection">
                                                                        <input type="text" class="form-control searchItem" id="searchInput"
                                                                            placeholder="{{ __('common.Search Item') }}..." />
                                                                        <ul class="list-group" id="suggestionList"></ul>
                                                                        <span class="text-danger qs_searchItem-error d-none"></span>
                                                                    </div>
                                                                </div>

                                                                <div class="row mb-3">
                                                                    <label for="fullName" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Quantity') }}</label>
                                                                    <div class="col-md-8 col-lg-9">
                                                                        <div class="input-group mb-3">
                                                                            <input type="text" class="form-control"
                                                                                aria-label="Text input with segmented dropdown button" name="quantity"
                                                                                id="quantity" style="width: 80%;">
                                                                            <select class="form-select relatedUnit" name="relatedUnit" id="relatedUnit"
                                                                                aria-label="Default select example" style="width: 20%;">
                                                                            </select>
                                                                        </div>
                                                                        <span class="text-danger qs_quantity-error d-none"></span>
                                                                        <span class="text-danger qs_relatedUnit-error d-none"></span>

                                                                    </div>
                                                                </div>

                                                                <div class="text-start">
                                                                    <button type="button" class="btn btn-danger refundItemOnTable">{{ __('common.Refund') }}</button>
                                                                    <button type="button" class="btn btn-primary refund_addItemOnTable">{{ __('common.Add') }}</button>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <hr>
                                                                    </div>
                                                                </div>
                                                                <div class="row mb-3">
                                                                    <div class="col-12">
                                                                         <h5>{{ __('common.Item List') }}</h5>
                                                                        <table class="table" id="sale_itemList">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th scope="col">#</th>
                                                                                    <th scope="col">{{ __('common.Item Name') }}</th>
                                                                                    <th scope="col">{{ __('common.Quantity') }}</th>
                                                                                    <th scope="col">{{ __('common.Rate') }}</th>
                                                                                    <th scope="col">{{ __('common.Amount') }}</th>
                                                                                    <th scope="col">{{ __('common.Action') }}</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                    <div class="erroDiv d-none">
                                                                        <span class="text-danger itemList-error d-none"></span>
                                                                    </div>
                                                                    <div class="col-6 text-start">
                                                                        <button type="button" class="btn btn-primary printBill">{{ __('common.Print') }}</button>
                                                                        <button type="submit" class="btn btn-success">{{ __('common.Save') }}</button>
                                                                        <button type="button" class="btn btn-info shareInvoice disabled">{{ __('common.Send SMS') }}</button>
                                                                    </div>
                                                                    <div class="col-6 text-end">
                                                                        <a class="btn btn-danger deleteAllItems disabled" href="#" data-location="refund" role="button">{{ __('common.Cancel') }}</a>
                                                                    </div>
                                                                </div>
                                                            </form><!-- End Profile Edit Form -->

                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </section>
@endsection


@include('coreweb::modals.shareInvoice')

@section('scripts')
    @parent
    <!-- Include parent scripts -->
    <script>
        $(document).ready(function () {
            // ----------------------------------- REFUND --------------------------------------

            var itemList = [];
            var grand_total = 0.00;
            var slno = 1;

            var errorHtml = $(`.sellSection .error-div`);

            $('.refund_addItemOnTable').click(function () {

                console.log('val', searh_itemID);

                var formData = new FormData();
                var selectedUnit = $("#relatedUnit").val() ?? "";

                if (item_unit !== undefined && item_unit !== null) {
                    var quantityInput = convertQuantityBasedOnUnit(
                        item_unit.toUpperCase(),
                        selectedUnit.toUpperCase(),
                        $("#quantity").val()
                    );
                } else {
                    console.error("item_unit is undefined or null");
                    // Handle the undefined case here, e.g., set a default value or display an error message
                }

                formData.append('item_id', searh_itemID);
                formData.append('quantity', quantityInput);
                formData.append('relatedUnit', selectedUnit);
                formData.append('isRefund', 0);

                console.log('formData', formData);

                $.ajax({
                    url: grocery_india_api_url + 'stock-quantity',
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
                        console.log('stock', response);

                        if (response && response.stockStatus == 1) {
                            // Append a new row to the table with the item details
                            resetQuickSaleForm();
                            // var slno = $('#sale_itemList tr').length;
                            var itemName = response.data.item_name;
                            var rate = response.data.sale_price;
                            var itemUnit = response.data.short_unit;

                            var amount = parseFloat(response.amount);

                            addItemOnCart(itemName, rate, itemUnit, amount, quantityInput, 0);

                            // $(".searchItem").val("");
                            // $("#quantity").val("");

                        } else if (response && response.stockStatus == 2) {
                            alert(
                                @json(__("common.You have enabled stock maintainance in preferences but no stock is available for this item."))
                            );
                        } else if (response && response.stockStatus == 0) {
                            alert(@json(__('common.Input Stock is not available. Available Stock are')) +': ' +
                                response.availableStock + ' ' + response.item_unit);
                        }
                        // $("#searchInput").val("");
                        // $("#quantity").val("");
                        // $("#relatedUnit").empty();
                        $(".overlay").hide();
                    },
                    error: function (xhr, status, error) {
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);
                        handleSearchItemErrors(error);
                    }
                });

            });

            $('.refundItemOnTable').click(function () {

                console.log('val', searh_itemID);

                var formData = new FormData();
                var selectedUnit = $("#relatedUnit").val() ?? "";


                if (item_unit !== undefined && item_unit !== null) {
                    var quantityInput = convertQuantityBasedOnUnit(
                        item_unit.toUpperCase(),
                        selectedUnit.toUpperCase(),
                        $("#quantity").val()
                    );
                } else {
                    console.error("item_unit is undefined or null");
                    // Handle the undefined case here, e.g., set a default value or display an error message
                }

                formData.append('item_id', searh_itemID);
                formData.append('quantity', quantityInput);
                formData.append('relatedUnit', selectedUnit);
                formData.append('isRefund', 1);

                console.log('formData', formData);

                $.ajax({
                    url: grocery_india_api_url + 'stock-quantity',
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
                        console.log('stock', response);

                        if (response && response.stockStatus == 1) {
                            // Append a new row to the table with the item details
                            resetQuickSaleForm();

                            var itemName = response.data.item_name;
                            var rate = response.data.sale_price;
                            var itemUnit = response.data.short_unit;

                            var amount = parseFloat(response.amount);

                            addItemOnCart(itemName, rate, itemUnit, amount, quantityInput, 1);


                            // $(".searchItem").val("");
                            // $("#quantity").val("");
                        } else if (response && response.stockStatus == 2) {
                            alert(
                                "You have enabled stock maintainance in preferences but no stock is available for this item."
                            );
                        } else if (response && response.stockStatus == 0) {
                            alert("Input Stock is not available. Available Stock are: " +
                                response.availableStock);
                        }
                        // $("#searchInput").val("");
                        // $("#quantity").val("");
                        // $("#relatedUnit").empty();
                        $(".overlay").hide();

                        console.log('refund', itemList);

                    },
                    error: function (xhr, status, error) {
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);
                        handleSearchItemErrors(error);
                    }
                });

            });



            $('#refund-form').submit(function (e) {
                e.preventDefault();
                generateRefundBill(false,false);
            });

            $('.printBill').click(function (e) {
                e.preventDefault();
                console.log('Print button clicked');
                generateRefundBill(true,false);
            });


            $('.shareInvoice').click(function (e) {
                e.preventDefault();

                resetQuickSaleForm();

                $("#shareInvoiceModal #phone_number").val('');

                $('#shareInvoiceModal').on('shown.bs.modal', function () {
                    $('#shareInvoiceModal #phone_number').focus();
                });

                $("#shareInvoiceModal").modal('show');

            });


            $('#sendSMS').click(function (e) {
                e.preventDefault();
                generateRefundBill(false,true);
            });


            function generateRefundBill(print,isSendSms) {
                console.log('item_list', itemList)

                var formData = new FormData();
                // Append each item of the itemList array with unique keys
                itemList.forEach(function (item, index) {
                    formData.append('itemList[' + index + '][itemId]', item.itemId);
                    formData.append('itemList[' + index + '][itemName]', item.itemName);
                    formData.append('itemList[' + index + '][quantity]', item.quantity);
                    formData.append('itemList[' + index + '][rate]', item.rate);
                    formData.append('itemList[' + index + '][selectedUnit]', item.selectedUnit
                        .toUpperCase());
                    formData.append('itemList[' + index + '][amount]', item.amount);
                    formData.append('itemList[' + index + '][isDelete]', 0);
                    formData.append('itemList[' + index + '][isRefund]', item.isRefund);
                    // Add more fields of the item as needed
                });
                // formData.append('itemList', itemList);
                formData.append('grand_total', grand_total);
                formData.append('print', print);
                formData.append('isSendSms', isSendSms);


                if(isSendSms == true){
                    formData.append('mobile', $("#shareInvoiceModal #phone_number").val());
                    formData.append('country_code', $("#shareInvoiceModal .countryCode").val());
                }

                console.log('formData', formData);

                $.ajax({
                    url: grocery_india_api_url + 'billing-n-refund',
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
                            if (response.stockError && response.stockError == 0) {
                                alert(response.message);
                            } else {

                                slno=1;

                                resetQuickSaleForm();
                                if ((print == 1) || (print == true)) {

                                    refundFormReset();
                                    // var timestamp = new Date().getTime();
                                    // window.open(base_url + '/storage/bill/' + response.filename +
                                    //     '?version=' + timestamp, '_blank');

                                        // Construct the URL
                                        var url = `/print/invoice/${response.bill_id}/`;

                                        // Redirect to the URL
                                        window.open(url, '_blank');

                                } else {

                                    // console.log('item', response);
                                    // $.toast({
                                    //     heading: 'Success',
                                    //     text: response.message,
                                    //     icon: 'success',
                                    //     loader: true,
                                    //     position: 'top-right',
                                    //     loaderBg: '#9EC600'
                                    // });

                                    // window.location.href = base_url_with_country_lang + 'refund';

                                     Swal.fire({
                                            title: 'Success!',
                                            text: response.message,
                                            icon: 'success',
                                            confirmButtonText: 'OK',
                                            allowOutsideClick: false // Dialog will not close on outside click
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                // window.location.href = base_url_with_country_lang + 'profile'; // Redirect to the homepage
                                                window.location.reload();
                                            }
                                        });

                                }
                                $("#shareInvoiceModal").modal('hide');
                                $(".overlay").hide();

                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);

                        handleBillingErrors(error,xhr);
                    }
                });
            }


            function handleBillingErrors(error,xhr) {
                // Use switch case for displaying error messages
                resetQuickSaleForm();
                  let html = '';

                if (xhr.status === 403 && error && error.data) {
                    $.each(error.data, function (key, value) {

                        // console.log(key,':',value);

                        if (key == 'itemList') {
                            // $("#searchInput").addClass('border border-2 border-danger');
                            // $(".searchInputSection .select2-selection__rendered").addClass('border border-2 border-danger');
                            // $(".qs_searchItem-error").removeClass('d-none');
                            // $(".qs_searchItem-error").text("Search Item is required");

                            // $("#quantity").addClass('border border-2 border-danger');
                            // $(".qs_quantity-error").removeClass('d-none');
                            // $(".qs_quantity-error").text("Invalid Quantity. ");

                            // $("#relatedUnit").addClass('border border-2 border-danger');
                            // $(".qs_relatedUnit-error").removeClass('d-none');
                            // $(".qs_relatedUnit-error").text("Select Unit");

                            // $.each(value, function (field, message) {
                            //     $(".erroDiv").removeClass("d-none");
                            //     $(".itemList-error").removeClass("d-none");
                            //     $(".itemList-error").text(message);
                            // });

                            $.each(value, function (field, messages) {
                                html += '<p class="my-1 text-danger">' + messages + '</p>';
                            });
                            errorHtml.find(".error-body").html(html);

                        }

                        if(key == 'mobile'){

                            $("#shareInvoiceModal .error-div").removeClass('d-none');

                                $("#shareInvoiceModal .error-div .error-body").html(
                                $('<div class="d-flex justify-content-between align-items-center">')
                                .append($('<p class="my-1 text-danger">' + value + '</p><br>'))
                            );
                            $("#shareInvoiceModal .custom-input-group").addClass('border border-2 border-danger');
                            $("#shareInvoiceModal  #phone_number").addClass('border border-2 border-danger');
                        }

                        if(key == 'country_code'){
                            $("#shareInvoiceModal .custom-input-group").addClass('border border-2 border-danger');
                            $("#shareInvoiceModal #phone_number").addClass('border border-2 border-danger');
                        }

                    });
                }

                 else {
                    let errMsg = error?.message ?? @json(__('common.Error'));
                    errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + errMsg + '</p>');
                }



            }


            function convertQuantityBasedOnUnit(itemUnit, selectedUnit, quantityInput) {

                if (itemUnit == 'KG') {

                    if (selectedUnit == 'KG') {
                        return quantityInput;
                    } else if (selectedUnit == 'GM') {
                        return quantityInput / 1000;
                    }
                } else if (itemUnit == 'GM') {
                    if (selectedUnit == 'KG') {
                        return quantityInput * 1000;
                    } else if (selectedUnit == 'GM') {
                        return quantityInput;
                    }
                } else if (itemUnit == 'LTR') {
                    if ((selectedUnit == 'LTR') || (selectedUnit == 'KG')) {
                        return quantityInput;
                    } else if ((selectedUnit == 'ML') || (selectedUnit == 'GM')) {
                        return quantityInput / 1000;
                    }
                } else if (itemUnit == 'ML') {
                    if ((selectedUnit == 'LTR') || (selectedUnit == 'KG')) {
                        return quantityInput * 1000;
                    } else if ((selectedUnit == 'ML') || (selectedUnit == 'GM')) {
                        return quantityInput;
                    }
                } else {
                    return quantityInput;
                }
            }


            function resetQuickSaleForm() {
                // Reset form fields
                $(".searchItem, #quantity, #relatedUnit")
                    .removeClass('border border-2 border-danger');

                $("#shareInvoiceModal .error-div")
                    .removeClass('border border-2 border-danger');
                $("#shareInvoiceModal .error-div").addClass('d-none');

                $("#shareInvoiceModal .custom-input-group").removeClass('border border-2 border-danger');
                $("#shareInvoiceModal #phone_number").removeClass('border border-2 border-danger');

                $(".qs_searchItem-error, .qs_quantity-error, .qs_relatedUnit-error,.itemList-error,.erroDiv")
                    .addClass('d-none');

                errorHtml.addClass('d-none');
            }

            function handleSearchItemErrors(error) {
                // Use switch case for displaying error messages
                resetQuickSaleForm();

                $.each(error.data, function (key, value) {
                    switch (key) {

                        case 'item_id':
                            $(".searchInputSection .select2-selection__rendered").addClass('border border-2 border-danger');
                            $(".qs_searchItem-error").removeClass('d-none');
                            $(".qs_searchItem-error").text("Search Item is required");
                            break;

                        case 'quantity':
                            $("#quantity").addClass('border border-2 border-danger');
                            $(".qs_quantity-error").removeClass('d-none');
                            $(".qs_quantity-error").text(value[0]);
                            break;

                        case 'relatedUnit':
                            $("#relatedUnit").addClass('border border-2 border-danger');
                            $(".qs_relatedUnit-error").removeClass('d-none');
                            $(".qs_relatedUnit-error").text(value[0]);
                            break;

                        case 'itemList':
                            $(".searchInputSection .select2-selection__rendered").addClass('border border-2 border-danger');
                            $(".qs_searchItem-error").removeClass('d-none');
                            $(".qs_searchItem-error").text("Search Item is required");

                            $("#quantity").addClass('border border-2 border-danger');
                            $(".qs_quantity-error").removeClass('d-none');
                            $(".qs_quantity-error").text("Invalid Quantity. ");

                            $("#relatedUnit").addClass('border border-2 border-danger');
                            $(".qs_relatedUnit-error").removeClass('d-none');
                            $(".qs_relatedUnit-error").text("Select Unit");
                            break;

                        default:
                            console.warn('Unhandled error key:', key);
                            break;
                    }
                });
            }


            function handleAddItemOnCartErrors(xhr, error) {
                // Use switch case for displaying error messages
                resetQuickSaleForm();


                if (xhr.status === 403 && error && error.data) {

                    errorHtml.removeClass('d-none');

                    let html = '';

                    console.log('error.data',error.data);

                    $.each(error.data, function (field, messages) {
                        $.each(messages, function (i, msg) {
                            html += '<p class="my-1 text-danger">' + msg + '</p>';
                        });
                    });
                    errorHtml.find(".error-body").html(html);
                }
            }

            function refundFormReset() {
                resetQuickSaleForm();
                $("#searchInput").val("");
                $("#relatedUnit").empty();
                $("#sale_itemList tbody tr").remove();

                itemList.splice(0, itemList.length);
                grand_total = 0;
            }

            // ----------------------------------- REFUND --------------------------------------


            // ----------------------------------- ADD ITEM ON CART --------------------------------------
            function addItemOnCart(itemName, rate, itemUnit, amount, quantityInput, isRefund) {
                // Input validation
                if (!itemName || !quantityInput || isNaN(rate)) {
                    console.error('Invalid input parameters');
                    return;
                }

                grand_total = grand_total || 0;
                grand_total += Number(amount);

                let itemExistsWithSameRefundStatus = false;
                // const searh_itemID = window.search_itemID; // Assuming this is defined globally
                const isRefundBool = isRefund === 1 || isRefund === true; // Normalize isRefund to boolean

                // Check existing items
                $('#sale_itemList tr').each(function () {
                    const itemId = $(this).data('id');
                    const isRefundRow = $(this).data('isrefund') == 1;

                    // Match item ID and refund status
                    if (itemId === searh_itemID && isRefundRow === isRefundBool) {
                        const quantitySpan = $(this).find('.iitemQuantity');
                        let currentQuantity = parseInt(quantitySpan.val()) || 0; // Changed to .val() for input
                        const newQuantity = currentQuantity + parseInt(quantityInput);
                        quantitySpan.val(newQuantity); // Update input value

                        const itemRateInput = $(this).find('.itemRate');
                        const rateValue = parseFloat(itemRateInput.val()) || parseFloat(rate);
                        const multiplier = isRefundBool ? -1 : 1;
                        const newAmount = rateValue * newQuantity * multiplier;
                        $(this).find('.itemAmount').text(newAmount.toFixed(2));

                        itemExistsWithSameRefundStatus = true;
                        return false; // Exit loop once found
                    }
                });

                const cartFormData = new FormData();
                cartFormData.append('item_id', searh_itemID);
                cartFormData.append('item_name', itemName);
                cartFormData.append('sale_price', convertPriceToNumber(rate));
                cartFormData.append('quantity', quantityInput);
                cartFormData.append('item_unit', itemUnit);
                cartFormData.append('location', 'refund');
                cartFormData.append('isRefund', isRefundBool ? 1 : 0);

                $.ajax({
                    url: grocery_india_api_url + 'add-item-on-cart',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    type: 'POST',
                    data: cartFormData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".overlay").show();
                    },
                    success: function (response) {
                        $(".overlay").hide();

                        if (response && response.status === "success") {
                            console.log('addItemOnCart', { response });

                            // Append new row only if item doesn't exist with same refund status
                            if (!itemExistsWithSameRefundStatus) {
                                appendTableRow(
                                    slno++,
                                    response.data.id, // Assuming this is the cart_id
                                    searh_itemID,
                                    itemName,
                                    quantityInput,
                                    itemUnit,
                                    rate,
                                    updateRate(amount),
                                    isRefundBool
                                );
                            }

                            grandTotalSection(grand_total);
                            setItemList(
                                response.data.id,
                                searh_itemID,
                                itemName,
                                quantityInput,
                                convertPriceToNumber(rate),
                                itemUnit,
                                amount,
                                isRefundBool ? 1 : 0
                            );
                        }
                    },
                    error: function (xhr, status, error) {
                        $(".overlay").hide();
                        // try {
                        //     const errorData = JSON.parse(xhr.responseText);
                        //     console.error('Error adding item:', errorData.message || errorData);
                        // } catch (e) {
                        //     console.error('Unexpected error:', status, error);
                        // }

                        handleAddItemOnCartErrors(xhr, error);
                    }
                });
            }
            // ----------------------------------- ADD ITEM ON CART --------------------------------------


            // ----------------------------------- UPDATE ITEM ON CART --------------------------------------
            $(document).on('input', '.itemRate', function () {


                let val = $(this).val().trim();

                // ✅ Allow only numbers + one decimal
                val = val.replace(/[^0-9.]/g, '');
                val = val.replace(/(\..*)\./g, '$1'); // prevent multiple dots

                // ✅ Max 6 digits before decimal
                let parts = val.split('.');
                if (parts[0].length > 6) {
                    parts[0] = parts[0].slice(0, 6);
                }
                val = parts.join('.');
                $(this).val(val);

                if (val > 100000) {
                    return;  
                } 


                // Get the current rate entered in the input field
                var rate = 0;

                if ($(this).val() != '') {
                    rate = parseFloat(convertPriceToNumber($(this).val()));
                }

                // Get the parent row of the clicked trash can icon
                var row = $(this).closest('tr');
                // Get the index of the row
                var index = row.index();

                // Find the corresponding quantity and calculate the new amount
                var quantity = parseFloat($(this).closest('tr').find('.iitemQuantity').val());
                var amount = rate * quantity;

                if ($(this).closest('tr').data('isrefund') == 1) {

                    // Update the amount displayed in the table
                    $(this).closest('tr').find('.itemAmount').text(updateRate(amount));
                }
                else {
                    // Update the amount displayed in the table
                    $(this).closest('tr').find('.itemAmount').text(updateRate(amount));
                }

                // Recalculate the total
                var total = 0;
                $('.itemAmount').each(function () {

                    if ($(this).closest('tr').data('isrefund') == 1) {
                        total -= parseFloat(convertPriceToNumber($(this).text()));
                    }
                    else {
                        total += parseFloat(convertPriceToNumber($(this).text()));
                    }
                });


                itemList[index].rate = rate.toFixed(2);
                itemList[index].amount = amount.toFixed(2);

                grand_total = total;

                updateItemOnCart(index, $(this).closest('tr').data('isrefund'));

                // Update grand total row
                grandTotalSection(grand_total);

            });


            $(document).on('input', '.iitemQuantity', function () {

                let val = $(this).val().trim();

                // ✅ Allow only numbers + one decimal
                val = val.replace(/[^0-9.]/g, '');
                val = val.replace(/(\..*)\./g, '$1'); // prevent multiple dots

                // ✅ Max 6 digits before decimal
                let parts = val.split('.');
                if (parts[0].length > 6) {
                    parts[0] = parts[0].slice(0, 6);
                }
                val = parts.join('.');
                $(this).val(val);

                if (val > 100000) {
                    return;  
                }


                // Get the current rate entered in the input field
                var quantity = 0;

                if ($(this).val() != '') {
                    quantity = parseFloat(convertPriceToNumber($(this).val()));
                }

                // console.log('itemRate',rate);

                // Get the parent row of the clicked trash can icon
                var row = $(this).closest('tr');
                // Get the index of the row
                var index = row.index();

                console.log('index', index);

                // Find the corresponding quantity and calculate the new amount
                var rate = parseFloat($(this).closest('tr').find('.itemRate').val());
                var amount = rate * quantity;


                // console.log('iitemQuantity', quantity);


                // Update the amount displayed in the table
                $(this).closest('tr').find('.itemAmount').text(updateRate(amount));


                // Recalculate the total
                var total = 0;
                $('.itemAmount').each(function () {
                    if ($(this).closest('tr').data('isrefund') == 1) {
                        total -= parseFloat(convertPriceToNumber($(this).text()));
                    }
                    else {
                        total += parseFloat(convertPriceToNumber($(this).text()));
                    }
                });


                console.log('iitemQuantity',grand_total,total);


                itemList[index].rate = rate.toFixed(2);
                itemList[index].quantity = quantity.toFixed(2);
                itemList[index].amount = amount.toFixed(2);

                grand_total = total;

                updateItemOnCart(index, $(this).closest('tr').data('isrefund'));

                // Update grand total row
                grandTotalSection(grand_total);

            });

            function updateItemOnCart(index, isRefund) {

                var cartFormData = new FormData()
                cartFormData.append('cart_id', itemList[index].cart_id);
                cartFormData.append('item_id', itemList[index].itemId);
                cartFormData.append('item_name', itemList[index].itemName);
                cartFormData.append('sale_price', convertPriceToNumber(itemList[index].rate));
                cartFormData.append('quantity', itemList[index].quantity);
                cartFormData.append('item_unit', itemList[index].selectedUnit);
                cartFormData.append('location', 'refund');
                cartFormData.append('isRefund', isRefund);

                $.ajax({
                    url: grocery_india_api_url + 'update-item-on-cart',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    type: 'POST',
                    data: cartFormData,
                    contentType: false,
                    processData: false,
                    success: function (response) {

                        console.log('addItemOnCart', response);

                        if (response && response.status == "success") {

                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);

                        errorHtml.removeClass('d- none');
                        let html = '';

                        console.log('error.data',error.data);

                        if (xhr.status === 403 && error && error.data) {

                            $.each(error.data, function (field, messages) {
                                $.each(messages, function (i, msg) {

                                    let inputSelector = `[name="itemList[${field}][${index}]"]`;

                                    // Add red border if exists
                                    let input = $(inputSelector);
                                    if (input.length > 0) {
                                        input.addClass("border border-danger");
                                    }

                                    html += '<p class="my-1 text-danger">' + msg + '</p>';
                                });
                            });
                            errorHtml.find(".error-body").html(html);
                        }

                        else {
                            let errMsg = error?.message ?? @json(__('common.Renew Subscription'));
                            errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + errMsg + '</p>');
                        }
                    }
                });
            }


            // ----------------------------------- UPDATE ITEM ON CART --------------------------------------


            // ----------------------------------- DELETE ITEM FROM CART --------------------------------------

            // Event listener for delete button clicks
            $(document).on('click', '.deleteItem', function () {

                // Get the parent row of the clicked trash can icon
                var row = $(this).closest('tr');
                // Get the index of the row
                var index = row.index();

                deleteItemFromCart(row, index);

            });

            function deleteItemFromCart(row, index) {

                console.log('deleteItemFromCart', row, index, itemList);


                var cart_id = itemList[index].cart_id;

                $.ajax({
                    url: grocery_india_api_url + 'delete-item-from-cart/' + cart_id,
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    type: 'GET',
                    contentType: false,
                    processData: false,
                    success: function (response) {

                        // console.log('deleteItemFromCart', response);

                        if (response && response.status == "success") {
                            // Get the amount of the deleted item
                            var amount = itemList[index].amount;
                            var isRefund = itemList[index].isRefund;


                            // Recalculate grand total
                            if(isRefund == 0){
                                grand_total -= parseFloat(amount);
                            }
                            else if(isRefund == 1){
                                grand_total += parseFloat(amount);
                            }


                            // Remove the row from the table
                            row.remove();
                            // Remove the corresponding item from the itemList array
                            itemList.splice(index, 1);


                            slno = itemList.length+1; // Update slno to reflect the new number of items

                            // Update slno for all remaining items in itemList
                            itemList.forEach(function (item, i) {
                                item.slno = i + 1; // Reassign slno starting from 1
                            });


                            // Update the table display to reflect new slno values
                            updateTableSerialNumbers();

                            if(itemList.length == 0){
                                $('.shareInvoice').addClass('disabled').prop('disabled', true);
                                $('.deleteAllItems').addClass('disabled').prop('disabled', true);
                                grand_total=0;
                            }

                            // Update grand total row
                            grandTotalSection(grand_total);

                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);
                    }
                });
            }


            // Function to update serial numbers in the table
            function updateTableSerialNumbers() {
                // Assuming the table rows have a class or identifier for serial number
                $('#sale_itemList tbody tr').each(function (index) {
                    $(this).find('.serial-number').text(index + 1); // Update the serial number column
                });
            }

            // ----------------------------------- DELETE ITEM FROM CART --------------------------------------


            // ----------------------------------- SHOW CART ITEMS --------------------------------------
            function showCartItems() {
                $.ajax({
                    url: grocery_india_api_url + 'items-on-cart/refund',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    type: 'GET',
                    contentType: false,
                    processData: false,
                    success: function (response) {

                        if (response && response.status == "success") {
                            tableItemList(response.data);
                        }

                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);
                    }
                });
            }

            showCartItems();

            // ----------------------------------- SHOW CART ITEMS --------------------------------------


            // ----------------------------------- ITEMS LIST --------------------------------------
            function tableItemList(items) {

                items.forEach(item => {
                    // $('#sale_itemList').append(
                    //     '<tr data-id=' + item.item_id + ' data-cart_id=' + item.id + '><td>' + (slno++) +
                    //     '</td>' +
                    //     '<td>' + item.item_name + '</td>' +
                    //     // '<td><span class="iitemQuantity">' + item.quantity +
                    //     // '</span>' + item.item_unit + '</td>' +

                    //     '<td width="18%"><div class="input-group">' +
                    //     '<input type="text" class="form-control iitemQuantity" value="' + item.quantity + '">' +
                    //     '<span class="input-group-text">' + item.item_unit + '</span>' +
                    //     '</div></td>' +

                    //     '<td width="18%"> <div class="input-group">' +
                    //     '<span class="input-group-text">' + currencySymbol() + '</span>' +
                    //     '<input type="text" class="form-control itemRate numericField" name="itemRate" value="' +
                    //     item.sale_price + '" />' +
                    //     '</div></td>' +
                    //     '<td> ' + currencySymbol() + '<span class="itemAmount">' + updateRate(parseFloat(item.sale_price) * parseFloat(item.quantity)) +
                    //     '</span></td>' +
                    //     '<td><img src="assets/img/delete.png" alt="Delete Icon" class="img-fluid deleteItem" style="width: 20px; height: 20px; cursor:pointer"></td>' +
                    //     '</tr>'
                    // );

                    appendTableRow(
                        slno++,
                        item.id,
                        item.item_id,
                        item.item_name,
                        item.quantity,
                        item.item_unit,
                        item.sale_price,
                        updateRate(parseFloat(item.sale_price) * parseFloat(item.quantity)),
                        item.isRefund
                    );

                    setItemList(
                        item.id,
                        item.item_id,
                        item.item_name,
                        item.quantity,
                        convertPriceToNumber(item.sale_price),
                        item.item_unit,
                        parseFloat(item.sale_price) * parseFloat(item.quantity),
                        item.isRefund
                    );


                    // CALCULATE GRAND TOTAL

                    if (item.isRefund == 1) {
                        grand_total = grand_total - (parseFloat(item.sale_price) * parseFloat(item.quantity));
                    }
                    else if (item.isRefund == 0) {

                        grand_total = grand_total + (parseFloat(item.sale_price) * parseFloat(item.quantity));
                    }
                    // CALCULATE GRAND TOTAL

                });

                // Update grand total row
                grandTotalSection(grand_total);
            }
            // ----------------------------------- ITEMS LIST --------------------------------------



            // ----------------------------------- COMMON FUNCTIONS --------------------------------------
            function grandTotalSection(grand_total) {

                $('#grand_total_row')
                    .remove(); // Remove existing grand total row

                if (grand_total < 0) {

                    var total = (-1) * grand_total;

                    $('#sale_itemList').append(
                        '<tr id="grand_total_row"><td></td><td>'+@json(__("common.Grand Total"))+'</td><td></td><td></td><td width="10%" class="text-start text-nowrap"> <span class="d-inline-block">− ' +
                        currency(total) + '</span></td><td></td></tr>');
                }
                else {
                    $('#sale_itemList').append(
                        '<tr id="grand_total_row"><td></td><td>'+@json(__("common.Grand Total"))+'</td><td></td><td></td><td class="text-start text-nowrap"><span class="d-inline-block">' +
                        currency(grand_total) + '</span></td><td></td></tr>');
                }
            }


            // function appendTableRow(slno, cart_id, item_id, item_name, quantity, item_unit, sale_price, amount, isRefund) {


            //     var amountHtml = '<td>' + currencySymbol() + '<span class="itemAmount">' + updateRate(amount);

            //     if (isRefund == 1) {
            //         amountHtml = '<td> − ' + currencySymbol() + '<span class="itemAmount">' + (-1) * updateRate(amount);
            //     }

            //     $('#sale_itemList').append(
            //         '<tr data-id=' + item_id + ' data-cart_id=' + cart_id + ' data-isrefund=' + isRefund + '><td>' + (slno) +
            //         '</td>' +

            //         '<td>' + item_name + '</td>' +

            //         '<td width="18%"><div class="input-group">' +
            //         '<input type="text" class="form-control iitemQuantity" value="' + quantity + '">' +
            //         '<span class="input-group-text">' + item_unit + '</span>' +
            //         '</div></td>' +

            //         '<td width="18%"> <div class="input-group">' +
            //         '<span class="input-group-text">' + currencySymbol() + '</span>' +
            //         '<input type="text" class="form-control itemRate numericField" name="itemRate" value="' +
            //         sale_price + '" />' +
            //         '</div></td>' +

            //         amountHtml +

            //         '<td><img src="assets/img/delete.png" alt="Delete Icon" class="img-fluid deleteItem" style="width: 20px; height: 20px; cursor:pointer"></td>' +
            //         '</tr>'
            //     );
            // }


            // Updated appendTableRow function
            function appendTableRow(slno, cart_id, item_id, item_name, quantity, item_unit, sale_price, amount, isRefund) {

                // console.log('appendTableRow',amount);


                $("#searchInput").val("");
                $("#quantity").val("");
                $("#relatedUnit").empty();


                // var amountHtml = '<td width="10%">' + currencySymbol() + '<span class="itemAmount">' + updateRate(amount) + '</span></td>';

                // if (isRefund == 1) {

                //     if(amount < 0){
                //         var amount = (-1)*updateRate(amount);
                //     }

                //     amountHtml = '<td width="10%"> − ' + currencySymbol() + '<span class="itemAmount">' + (amount) + '</span></td>';
                // }

                var amountHtml = '<td width="10%" class="text-start text-nowrap"><span class="d-inline">' + currencySymbol() +
                 '<span class="itemAmount">' + updateRate(amount) + '</span></span></td>';

                if (isRefund == 1) {
                    // Ensure amount is positive for display (even though it’s a refund)
                    var positiveAmount = Math.abs(updateRate(amount));

                    amountHtml = '<td width="10%" class="text-start text-nowrap">' +
                                '<span class="d-inline-block">− ' + currencySymbol() +
                                '<span class="itemAmount">' + positiveAmount.toFixed(2) + '</span></span>' +
                                '</td>';
                }


                $('#sale_itemList').append(
                    '<tr data-id="' + item_id + '" data-cart_id="' + cart_id + '" data-isrefund="' + (isRefund ? 1 : 0) + '">' +
                    '<td class="serial-number">' + slno + '</td>' +
                    '<td>' + item_name + '</td>' +
                    '<td width="18%"><div class="input-group">' +
                    '<input name="itemList['+(slno-1)+'][quantity]" type="text" class="form-control iitemQuantity" value="' + parseFloat(quantity).toFixed(2) + '">' +
                    '<span class="input-group-text">' + item_unit + '</span>' +
                    '</div></td>' +
                    '<td width="18%"><div class="input-group">' +
                    '<span class="input-group-text">' + currencySymbol() + '</span>' +
                    '<input type="text" class="form-control itemRate numericField" name="itemList['+(slno-1)+'][rate]" value="' + parseFloat(sale_price).toFixed(2) + '" />' +
                    '</div></td>' +
                    amountHtml +
                    '<td><img src="/assets/img/delete.png" alt="Delete Icon" class="img-fluid deleteItem" style="width: 20px; height: 20px; cursor:pointer"></td>' +
                    '</tr>'
                );

                $('.shareInvoice').removeClass('disabled').prop('disabled', false);
                $('.deleteAllItems').removeClass('disabled').prop('disabled', false);
            }


            function setItemList(cart_id, itemId, itemName, quantity, rate, selectedUnit, amount, isRefund) {
                // Push the item details to the itemList array
                itemList.push({
                    cart_id: cart_id,
                    itemId: itemId,
                    itemName: itemName,
                    quantity: quantity,
                    rate: convertPriceToNumber(rate),
                    selectedUnit: selectedUnit,
                    amount: amount,
                    isRefund: isRefund,
                });
            }

            // ----------------------------------- COMMON FUNCTIONS --------------------------------------



             // ----------------------------------- STOCK QUANTITY --------------------------------------
            $(document).on('input', '#quantity', function () {

                let val = $(this).val();

                // ✅ Allow only numbers and one decimal point
                val = val.replace(/[^0-9.]/g, '');

                // ✅ Prevent multiple dots
                val = val.replace(/(\..*)\./g, '$1');

                // ✅ Restrict digits before decimal to 6 max (for 100000 limit)
                let parts = val.split('.');
                if (parts[0].length > 6) {
                    parts[0] = parts[0].slice(0, 6);
                }

                val = parts.join('.');

                $(this).val(val);

                // ✅ Run validation
                const result = validateNumericInput($(this).val());

                if (!result.status) {
                    errorHtml.removeClass("d-none");
                    errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + result.message + '</p>');

                    $('.addItemOnTable').removeClass('disabled').prop('disabled', true);
                } else {
                    errorHtml.addClass("d-none");
                        $('.ad dI temOnTable').removeClass('disabled').prop('disabled', false);
                }

            });


            // ----------------------------------- STOCK QUANTITY --------------------------------------


        });

    </script>
@endsection