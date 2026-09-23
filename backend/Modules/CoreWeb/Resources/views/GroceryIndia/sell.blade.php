@extends('coreweb::layouts.groceryIndia')
@section('title', "{{ __('common.Quick Sell') }}")
@section('content')
                <div class="pagetitle">
                    <h1>{{ __('common.Quick Sell') }}</h1>
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('common.Quick Sell') }}</li>
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

                                    @include('coreweb::components/error', ['heading' => 'Error'])

                                    <!-- Profile Edit Form -->
                                    <form id="billing-form">

                                        <div class="row mb-3">
                                            <label for="fullName" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Item Name') }}</label>
                                            <div class="col-md-8 col-lg-9 searchInputSection">
                                                <input type="text" class="form-control searchItem" id="searchInput"
                                                    placeholder="{{ __('common.Search Item') }}..." autocomplete="off" />
                                                <ul class="list-group" id="suggestionList"></ul>
                                                <span class="text-danger qs_searchItem-error d-none"></span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="fullName" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Quantity') }}</label>
                                            <div class="col-md-8 col-lg-9">
                                                <!-- <input name="quantity" type="text" class="form-control" id="quantity" value="" /> -->
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control numericField"
                                                        aria-label="Text input with segmented dropdown button" name="quantity"
                                                        id="quantity" style="width: 80%;">
                                                    <select class="form-select relatedUnit" name="relatedUnit" id="relatedUnit"
                                                        aria-label="Default select example" style="width: 20%;">
                                                        <!-- <option selected disabled>Unit</option> -->
                                                    </select>
                                                </div>
                                                <span class="text-danger qs_quantity-error d-none"></span>
                                                <span class="text-danger qs_relatedUnit-error d-none"></span>

                                            </div>
                                        </div>

                                        <div class="text-start">
                                            <button type="button" class="btn btn-primary addItemOnTable">{{ __('common.Add') }}</button>
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
                                                <button type="submit" class="btn btn-success billFormSubmitButton">{{ __('common.Save') }}</button>
                                                <button type="button" class="btn btn-info shareInvoice disabled">{{ __('common.Send SMS') }}</button>
                                            </div>
                                            <div class="col-6 text-end">
                                                <a class="btn btn-danger deleteAllItems disabled" data-location="sell" href="#" role="button">{{ __('common.Cancel') }}</a>
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

            // ----------------------------------- QUICK SALE --------------------------------------
            var itemList = [];
            var grand_total = 0.00;
            var slno = 1;
            var errorHtml = $(`.sellSection .error-div`);


            $('.addItemOnTable').click(function () {

                console.log('val', searh_itemID);

                var formData = new FormData();
                var selectedUnit = $("#relatedUnit").val() ?? "";

                // var quantityInput = convertQuantityBasedOnUnit(item_unit
                //     .toUpperCase(), selectedUnit.toUpperCase(), $(
                //         "#quantity").val());

                console.log('selectedUnit', selectedUnit);

                // Check if selectedUnit is null
                // if (selectedUnit === null) {
                //     console.error("selectedUnit is null. Please select a unit.");
                //     alert("Please select a unit before proceeding.");
                //     return; // Stop execution if selectedUnit is null
                // }

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


                            var itemName = response.data.item_name;
                            var rate = response.data.sale_price;
                            var itemUnit = response.data.short_unit;

                            var amount = parseFloat(response.amount);

                            addItemOnCart(itemName, rate, itemUnit, amount, quantityInput);

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
                        handleSearchItemErrors(error);
                    }
                });
            });

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
                            $(".qs_searchItem-error").text(value[0]);
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



            $('#billing-form').submit(function (e) {
                e.preventDefault();
                generateBill(false,false);
            });

            $('.printBill').click(function (e) {
                e.preventDefault();
                console.log('Print button clicked');
                generateBill(true,false);
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
                generateBill(false,true);
            });


            function generateBill(print, isSendSms) {

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
                    formData.append('itemList[' + index + '][isRefund]', 0);
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
                    url: grocery_india_api_url + 'billing',
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

                        console.log('generateBill', response);

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

                                } 
                                // else if( (isSendSms == 1) || (isSendSms == true) ){
                                //     $("#shareInvoiceModal").modal('hide');
                                // }
                                else {

                                    // console.log('item', response);

                                    // $.toast({
                                    //     heading: 'Success',
                                    //     text: response.message,
                                    //     icon: 'success',
                                    //     loader: true,
                                    //     position: 'top-right',
                                    //     loaderBg: '#9EC600'
                                    // });

                                    // window.location.href =  base_url_with_country_lang + 'sell';

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


                        // $.each(error, function(key, value) {
                        //     alert(value);
                        // });

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

                        // errorHtml.removeClass('d-none');


                        $.each(value, function (i, msg) {
                                html += '<p class="my-1 text-danger">' + msg + '</p>';

                                // ✅ Highlight field inside table

                                // Convert itemList.0.amount → itemList[0][amount]
                                let parts = key.split('.');
                                let row = parts[1];          // 0
                                let col = parts[2];          // amount

                                // Build selector
                                let inputSelector = `[name="itemList[${row}][${col}]"]`;

                                // Add red border if exists
                                let input = $(inputSelector);
                                if (input.length > 0) {
                                    input.addClass("border border-danger");
                                }
                        });

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
                            $("#shareInvoiceModal #phone_number").addClass('border border-2 border-danger');
                        }

                        if(key == 'country_code'){
                            $("#shareInvoiceModal .custom-input-group").addClass('border border-2 border-danger');
                            $("#shareInvoiceModal #phone_number").addClass('border border-2 border-danger');
                        }

                    });

                    $(".error-body").html(html); 
                }

                else {
                    let errMsg = error?.message ?? @json(__('common.Error'));
                    errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + errMsg + '</p>');
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

            // ----------------------------------- QUICK SALE --------------------------------------


            // ----------------------------------- ADD ITEM ON CART --------------------------------------
            function addItemOnCart(itemName, rate, itemUnit, amount, quantityInput) {

                // grand_total = grand_total + amount;

                grand_total = grand_total || 0;
                grand_total += Number(amount);


                // Flag to check if item is already in the list
                var itemExists = false;

                // Iterate over each row in the table to check if item already exists
                $('#sale_itemList tr').each(function () {
                    // Get the ID of the item in the current row
                    var itemId = $(this).data('id');

                    // Check if the current item ID matches the searched item ID
                    if (itemId === searh_itemID) {
                        // Item already exists in the list, update quantity and amount
                        var quantitySpan = $(this).find('.iitemQuantity');
                        var currentQuantity = parseFloat(quantitySpan.val());
                        var newQuantity = parseFloat(currentQuantity) + parseFloat(quantityInput);
                        quantitySpan.val(newQuantity);

                        // Recalculate the amount
                        var itemRateInput = $(this).find('.itemRate');
                        var rate = parseFloat(itemRateInput.val());
                        var newAmount = rate * newQuantity;
                        $(this).find('.itemAmount').text(newAmount.toFixed(2));

                        // Set flag to indicate that item exists
                        itemExists = true;

                        // Exit the loop since item is found
                        return false;
                    }
                });

                // console.log('addItemOnCart',itemName, rate, itemUnit, amount, quantityInput);

                var cartFormData = new FormData()
                cartFormData.append('item_id', searh_itemID);
                cartFormData.append('item_name', itemName);
                cartFormData.append('sale_price', convertPriceToNumber(rate));
                cartFormData.append('quantity', quantityInput);
                cartFormData.append('item_unit', itemUnit);
                cartFormData.append('location', 'sell');
                cartFormData.append('isRefund', 0);

                $.ajax({
                    url: grocery_india_api_url + 'add-item-on-cart',
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


                            // If item doesn't exist, append a new row
                            if (!itemExists) {
                                // $('#sale_itemList').append(
                                //     '<tr data-id=' + searh_itemID + ' data-cart_id=' + response.data.id + '><td>' + (slno++) +
                                //     '</td>' +
                                //     '<td>' + itemName + '</td>' +
                                //     // '<td><span class="iitemQuantity">' + quantityInput +
                                //     // '</span>' + itemUnit + '</td>' +


                                //     '<td width="18%"><div class="input-group">' +
                                //     '<input type="text" class="form-control iitemQuantity" value="' + quantityInput + '">' +
                                //     '<span class="input-group-text">' + itemUnit + '</span>' +
                                //     '</div></td>' +


                                //     '<td width="18%"> <div class="input-group">' +
                                //     '<span class="input-group-text">' + currencySymbol() + '</span>' +
                                //     '<input type="text" class="form-control itemRate numericField" name="itemRate" value="' +
                                //     rate + '" />' +
                                //     '</div></td>' +

                                //     '<td> ' + currencySymbol() + '<span class="itemAmount">' + updateRate(amount) +
                                //     '</span></td>' +
                                //     // '<td><i class="fa-solid fa-trash-can deleteItem"></i></td>' +
                                //     '<td><img src="assets/img/delete.png" alt="Delete Icon" class="img-fluid deleteItem" style="width: 20px; height: 20px; cursor:pointer"></td>' +
                                //     '</tr>'
                                // );

                                appendTableRow(
                                    slno++,
                                    response.data.id,
                                    searh_itemID,
                                    itemName,
                                    quantityInput,
                                    itemUnit,
                                    rate,
                                    updateRate(amount)
                                );

                            }


                            // Update grand total row
                            grandTotalSection(grand_total);

                            // // Push the item details to the itemList array
                            // itemList.push({
                            //     cart_id: response.data.id,
                            //     itemId: searh_itemID,
                            //     itemName: itemName,
                            //     quantity: quantityInput,
                            //     rate: convertPriceToNumber(rate),
                            //     selectedUnit: itemUnit,
                            //     amount: amount,
                            //     isRefund: 0,
                            // });

                            setItemList(
                                response.data.id,
                                searh_itemID,
                                itemName,
                                quantityInput,
                                convertPriceToNumber(rate),
                                itemUnit,
                                amount,
                                0
                            );

                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        // console.error(error);

                        // console.log('handleSearchItemErrors',error);

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

                // console.log('itemRate',rate);

                // Get the parent row of the clicked trash can icon
                var row = $(this).closest('tr');
                // Get the index of the row
                var index = row.index();

                console.log('index', index);

                // Find the corresponding quantity and calculate the new amount
                var quantity = parseFloat($(this).closest('tr').find('.iitemQuantity').val());
                var amount = rate * quantity;


                // Update the amount displayed in the table
                $(this).closest('tr').find('.itemAmount').text(updateRate(amount));


                // Recalculate the total
                var total = 0;
                $('.itemAmount').each(function () {
                    total += parseFloat(convertPriceToNumber($(this).text()));
                });


                itemList[index].rate = rate.toFixed(2);
                itemList[index].amount = amount.toFixed(2);

                grand_total = total;

                updateItemOnCart(index,"rate");

                // Update grand total row
                grandTotalSection(grand_total);

            });


            // $(document).on('input', '.iitemQuantity', function () {


            //     // const result = validateNumericInput($(this).val());

            //     // if (!result.status) {
            //     //     errorHtml.removeClass("d-none");
            //     //     errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + result.message + '</p>');

            //     // } else {
            //     //      errorHtml.addClass("d-none");
            //     // }


            //     let val = $(this).val();

            //     // ✅ Allow only numbers and one decimal point
            //     val = val.replace(/[^0-9.]/g, '');

            //     // ✅ Prevent multiple dots
            //     val = val.replace(/(\..*)\./g, '$1');

            //     // ✅ Restrict digits before decimal to 6 max (for 100000 limit)
            //     let parts = val.split('.');
            //     if (parts[0].length > 6) {
            //         parts[0] = parts[0].slice(0, 6);
            //     }

            //     val = parts.join('.');

            //     $(this).val(val);


            //     // Get the current rate entered in the input field
            //     var quantity = 0;

            //     if ($(this).val() != '') {
            //         quantity = parseFloat(convertPriceToNumber($(this).val()));
            //     }

            //     // console.log('itemRate',rate);

            //     // Get the parent row of the clicked trash can icon
            //     var row = $(this).closest('tr');
            //     // Get the index of the row
            //     var index = row.index();

            //     console.log('index', index);

            //     // Find the corresponding quantity and calculate the new amount
            //     var rate = parseFloat($(this).closest('tr').find('.itemRate').val());
            //     var amount = rate * quantity;

            //     console.log('iitemQuantity', quantity);

            //     // Update the amount displayed in the table
            //     $(this).closest('tr').find('.itemAmount').text(updateRate(amount));


            //     // Recalculate the total
            //     var total = 0;
            //     $('.itemAmount').each(function () {
            //         total += parseFloat(convertPriceToNumber($(this).text()));
            //     });


            //     itemList[index].rate = rate.toFixed(2);
            //     itemList[index].quantity = quantity.toFixed(2);
            //     itemList[index].amount = amount.toFixed(2);

            //     grand_total = total;

            //     updateItemOnCart(index,"quantity");

            //     // Update grand total row
            //     grandTotalSection(grand_total);

            // });


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

                // ✅ Convert quantity safely
                let quantity = 0;
                if (val !== '') {
                    quantity = parseFloat(convertPriceToNumber(val)) || 0;
                }

                // ✅ Find row and its index
                const row = $(this).closest('tr');
                const index = row.index();

                // ✅ Safely get rate
                let rate = parseFloat(convertPriceToNumber(row.find('.itemRate').val())) || 0;

                // ✅ Calculate amount
                let amount = rate * quantity;

                // ✅ Update amount in UI
                row.find('.itemAmount').text(updateRate(amount));

                // ✅ Recalculate total
                let total = 0;
                $('.itemAmount').each(function () {
                    total += parseFloat(convertPriceToNumber($(this).text())) || 0;
                });

                grand_total = total;

                // ✅ Update local array safely
                if (itemList[index]) {
                    itemList[index].rate = rate.toFixed(2);
                    itemList[index].quantity = quantity.toFixed(2);
                    itemList[index].amount = amount.toFixed(2);
                }

                // ✅ Update cart API
                updateItemOnCart(index, "quantity");

                // ✅ Update final total UI
                grandTotalSection(grand_total);
            });


            function updateItemOnCart(index, field) {

                var cartFormData = new FormData()
                cartFormData.append('cart_id', itemList[index].cart_id);
                cartFormData.append('item_id', itemList[index].itemId);
                cartFormData.append('item_name', itemList[index].itemName);
                cartFormData.append('sale_price', convertPriceToNumber(itemList[index].rate));
                cartFormData.append('quantity', itemList[index].quantity);
                cartFormData.append('item_unit', itemList[index].selectedUnit);
                cartFormData.append('location', 'sell');
                cartFormData.append('isRefund', 0);

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

                        resetQuickSaleForm();

                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);

                        errorHtml.removeClass('d-none');
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

                        console.log('deleteItemFromCart', response);

                        if (response && response.status == "success") {
                            // Get the amount of the deleted item
                            var amount = itemList[index].amount;

                            // Remove the row from the table
                            row.remove();
                            // Remove the corresponding item from the itemList array
                            itemList.splice(index, 1);

                            // Recalculate grand total
                            grand_total -= amount;
                            slno = itemList.length+1; // Update slno to reflect the new number of items

                            // Update slno for all remaining items in itemList
                            itemList.forEach(function (item, i) {
                                item.slno = i + 1; // Reassign slno starting from 1
                            });

                            // Update the table display to reflect new slno values
                            updateTableSerialNumbers();

                            // Update grand total row
                            grandTotalSection(grand_total);

                            // console.log('grandTotalSection',itemList.length);

                            if(itemList.length == 0){
                                $('.shareInvoice').addClass('disabled').prop('disabled', true);
                                $('.deleteAllItems').addClass('disabled').prop('disabled', true);
                                grand_total=0;
                            }


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
                    url: grocery_india_api_url + 'items-on-cart/sell',
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
                        updateRate(parseFloat(item.sale_price) * parseFloat(item.quantity))
                    );

                    // itemList.push({
                    //     cart_id: item.id,
                    //     itemId: item.item_id,
                    //     itemName: item.item_name,
                    //     quantity: item.quantity,
                    //     rate: convertPriceToNumber(item.sale_price),
                    //     selectedUnit: item.item_unit,
                    //     amount: parseFloat(item.sale_price) * parseFloat(item.quantity),
                    //     isRefund: 0,
                    // });

                    setItemList(
                        item.id,
                        item.item_id,
                        item.item_name,
                        item.quantity,
                        convertPriceToNumber(item.sale_price),
                        item.item_unit,
                        parseFloat(item.sale_price) * parseFloat(item.quantity),
                        0
                    );


                    // CALCULATE GRAND TOTAL
                    grand_total = grand_total + (parseFloat(item.sale_price) * parseFloat(item.quantity));
                    // CALCULATE GRAND TOTAL

                });

                // Update grand total row
                grandTotalSection(grand_total);
            }
            // ----------------------------------- ITEMS LIST --------------------------------------


            // ----------------------------------- COMMON FUNCTIONS --------------------------------------
            function grandTotalSection(grand_total) {

                // Update grand total row
                $('#grand_total_row')
                    .remove(); // Remove existing grand total row
                $('#sale_itemList').append(
                    '<tr id="grand_total_row"><td></td><td>'+@json(__("common.Grand Total"))+'</td><td></td><td></td><td>' +
                    currency(grand_total) + '</td><td></td></tr>');
            }

            function appendTableRow(slno, cart_id, item_id, item_name, quantity, item_unit, sale_price, amount) {

                $("#searchInput").val("");
                $("#quantity").val("");
                $("#relatedUnit").empty();

                $('#sale_itemList').append(
                    '<tr data-id=' + item_id + ' data-cart_id=' + cart_id + '><td class="serial-number">' + (slno) +
                    '</td>' +

                    '<td>' + item_name + '</td>' +

                    '<td width="18%"><div class="input-group">' +
                    '<input name="itemList['+(slno-1)+'][quantity]" type="text" class="form-control iitemQuantity" value="' + parseFloat(quantity).toFixed(2) + '">' +
                    '<span class="input-group-text">' + item_unit + '</span>' +
                    '</div></td>' +

                    '<td width="18%"> <div class="input-group">' +
                    '<span class="input-group-text">' + currencySymbol() + '</span>' +
                    '<input type="text" class="form-control itemRate numericField" name="itemList['+(slno-1)+'][rate]" value="' +
                    parseFloat(sale_price).toFixed(2) + '" />' +
                    '</div></td>' +

                    '<td> ' + currencySymbol() + '<span class="itemAmount">' + parseFloat(amount).toFixed(2) +
                    '</span></td>' +

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

            //    if(response==false) {
            //         $('.addItemOnTable').removeClass('disabled').prop('disabled', true);
            //         // $('.shareInvoice').removeClass('disabled').prop('disabled', true);
            //         // $('.billFormSubmitButton').removeClass('disabled').prop('disabled', true);
            //    }
            //    else{
            //         $('.addItemOnTable').removeClass('disabled').prop('disabled', false);
            //         // $('.shareInvoice').removeClass('disabled').prop('disabled', false);
            //         // $('.billFormSubmitButton').removeClass('disabled').prop('disabled', false);
            //    }

            });


            // ----------------------------------- STOCK QUANTITY --------------------------------------

        });
        
    </script>
@endsection