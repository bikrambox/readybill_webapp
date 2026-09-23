@extends('coreweb::layouts.groceryIndia')
@section('title', "{{ __('common.Settings') }}")
@section('content')
    <div class="pagetitle">

        <div class="alert alert-warning d-flex align-items-center alert-dismissible fade show showErrorMessageAlert-prefernces d-none"
            role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img"
                aria-label="Warning:">
                <path
                    d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
            </svg>
            <div>
                <!-- <div class="message">If you enable "maintain stock option" it will make 0 to all the items</div> -->
                <label class="col-form-label showErrorMessageAlertLabel-prefernces">
                    {{ __('preference_page.If you enable the “maintain stock” option, all Stock Quantity will be 0. Are you sure?') }}
                </label>
                <div class="form-check pt-2">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="confirm-checkbox" id="confirm-checkboxYes"
                            value="1">
                        <label class="form-check-label" for="confirm-checkboxYes">{{ __('common.Yes') }}</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="confirm-checkbox" id="confirm-checkboxNo"
                            value="0">
                        <label class="form-check-label" for="confirm-checkboxNo">{{ __('common.No') }}</label>
                    </div>
                </div>
                <span class="text-danger preference_mrp-error d-none"></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <h1>{{ __('common.Settings') }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                <li class="breadcrumb-item active">{{ __('common.Settings') }}</li>
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
                    <div class="card-body pt-3">

                        <!-- Profile Edit Form -->
                        <form id="preferences">

                            <div class="row mb-3">
                                <label for="fullName" class="col-md-4 col-lg-6 col-form-label">
                                    {{ __('preference_page.Do you maintain MRP?') }}
                                </label>
                                <div class="col-md-8 col-lg-6">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input border border-secondary" type="checkbox"
                                            id="preference_mrp" name="preference_mrp" value="1" />
                                    </div>
                                    <span class="text-danger preference_mrp-error d-none"></span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="fullName" class="col-md-4 col-lg-6 col-form-label">
                                    {{ __('preference_page.Do you want to show MRP in invoice?') }}
                                </label>
                                <div class="col-md-8 col-lg-6">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input border border-secondary" type="checkbox"
                                            id="preference_mrp_invoice" name="preference_mrp_invoice" value="1" />
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <label for="fullName" class="col-md-4 col-lg-6 col-form-label">
                                    {{ __('preference_page.Do you want to maintain stock?') }}
                                </label>
                                <div class="col-md-8 col-lg-6">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input border border-secondary" type="checkbox"
                                            id="preference_quantity" name="preference_quantity" value="1" />
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <label for="fullName" class="col-md-4 col-lg-6 col-form-label">
                                    {{ __('preference_page.Do you want HSN/ SAC code?') }}
                                </label>
                                <div class="col-md-8 col-lg-6">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input border border-secondary" type="checkbox"
                                            id="preference_hsn" name="preference_hsn" value="1" />
                                    </div>
                                    <span class="text-danger preference_hsn-error d-none"></span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="fullName" class="col-md-4 col-lg-6 col-form-label">
                                    {{ __('preference_page.Do you want to show HSN/ SAC code in invoice?') }}
                                </label>
                                <div class="col-md-8 col-lg-6">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input border border-secondary" type="checkbox"
                                            id="preference_hsn_invoice" name="preference_hsn_invoice" value="1" />
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="fullName" class="col-md-4 col-lg-6 col-form-label">
                                    {{ __('preference_page.Do you want to print the invoice on A4 size paper?') }}
                                </label>
                                <div class="col-md-8 col-lg-6">
                                    <div class="form-check pt-2">
                                        <select id="preference_invoice_format" name="preference_invoice_format" class="form-select">
                                            @foreach(config('general.invoice_format') as $key => $invoiceFormat)
                                                <option value="{{ $key }}" {{ $key == 0 ? 'selected' : '' }}>
                                                    {{ ucfirst(strtolower($invoiceFormat)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <div class="row mb-3">
                                <label for="preference_transaction_mark_as_paid" class="col-md-4 col-lg-6 col-form-label">
                                    {{ __('preference_page.When you save a transaction, mark it as paid') }}
                                </label>
                                <div class="col-md-8 col-lg-6">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input border border-secondary" type="checkbox"
                                            id="preference_transaction_mark_as_paid" name="preference_transaction_mark_as_paid" value="1" />
                                    </div>
                                    <span class="text-danger preference_transaction_mark_as_paid-error d-none"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="preference_transaction_mark_as_unpaid" class="col-md-4 col-lg-6 col-form-label">
                                    {{ __('preference_page.When you save a transaction, mark it as unpaid. Only admin/ owner can mark it as paid') }}
                                </label>
                                <div class="col-md-8 col-lg-6">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input border border-secondary" type="checkbox"
                                            id="preference_transaction_mark_as_unpaid" name="preference_transaction_mark_as_unpaid" value="1" />
                                    </div>
                                    <span class="text-danger preference_transaction_mark_as_unpaid-error d-none"></span>
                                </div>
                            </div>


                            <hr>

                            <div class="text-left">
                                <button type="submit" class="btn btn-primary">{{ __('common.Save Changes') }}</button>
                            </div>
                        </form><!-- End Profile Edit Form -->

                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @parent
    <!-- Include parent scripts -->
    <script>
        $(document).ready(function () {
            // ----------------------------------- PREFERENCES --------------------------------------

            $('#preferences').submit(function (e) {

                e.preventDefault();
                resetPreferenceForm();

                updatePreferncesData();

            });

            function updatePreferncesData() {

                var formData = new FormData();
                formData.append('preference_mrp', $('input[name="preference_mrp"]').is(':checked') ? $(
                    'input[name="preference_mrp"]').val() : 0);
                formData.append('preference_mrp_invoice', $('input[name="preference_mrp_invoice"]').is(
                    ':checked') ? $('input[name="preference_mrp_invoice"]').val() : 0);
                formData.append('preference_quantity', $('input[name="preference_quantity"]').is(
                    ':checked') ? $('input[name="preference_quantity"]').val() : 0);
                formData.append('preference_hsn', $('input[name="preference_hsn"]').is(':checked') ? $(
                    'input[name="preference_hsn"]').val() : 0);
                formData.append('preference_hsn_invoice', $('input[name="preference_hsn_invoice"]').is(
                    ':checked') ? $('input[name="preference_hsn_invoice"]').val() : 0);

                formData.append('preference_invoice_format', $('select[name="preference_invoice_format"]').val());

                formData.append('preference_transaction_mark_as_paid', $('input[name="preference_transaction_mark_as_paid"]').is(':checked') ? $(
                    'input[name="preference_transaction_mark_as_paid"]').val() : 0);


                formData.append('preference_transaction_mark_as_unpaid', $('input[name="preference_transaction_mark_as_unpaid"]').is(':checked') ? $(
                    'input[name="preference_transaction_mark_as_unpaid"]').val() : 0);

                // taxValues.forEach(function (value, index) {
                //     formData.append('taxes[]', value);
                // });

                console.log('formData', formData);

                $.ajax({
                    url: grocery_india_api_url + 'prefernce',
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
                            $(".overlay").hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);
                        resetPreferenceForm();
                        if (error.data.preference_hsn_invoice) {
                            $("#preference_hsn").addClass('border border-2 border-danger');
                            $(".preference_hsn-error").removeClass('d-none');
                            $(".preference_hsn-error").text(error.data
                                .preference_hsn_invoice[0]);
                        }

                        if (error.data.preference_mrp_invoice) {
                            $("#preference_mrp").addClass('border border-2 border-danger');
                            $(".preference_mrp-error").removeClass('d-none');
                            $(".preference_mrp-error").text(error.data
                                .preference_mrp_invoice[0]);

                        }


                        if (error.data.preference_transaction_mark_as_unpaid) {
                            $("#preference_transaction_mark_as_unpaid").addClass('border border-2 border-danger');
                            $(".preference_transaction_mark_as_unpaid-error").removeClass('d-none');
                            $(".preference_transaction_mark_as_unpaid-error").text(error.data
                                .preference_transaction_mark_as_unpaid[0]);
                        }


                        if (error.data.preference_transaction_mark_as_paid) {
                            $("#preference_transaction_mark_as_paid").addClass('border border-2 border-danger');
                            $(".preference_transaction_mark_as_paid-error").removeClass('d-none');
                            $(".preference_transaction_mark_as_paid-error").text(error.data
                                .preference_transaction_mark_as_paid[0]);
                        }

                    }
                });
            }


            function resetPreferenceForm() {
                $("#preference_hsn, #preference_mrp, #preference_transaction_mark_as_paid, #preference_transaction_mark_as_unpaid")
                    .removeClass('border border-2 border-danger');
                $(".preference_hsn-error, preference_mrp-error, .preference_transaction_mark_as_paid-error, .preference_transaction_mark_as_unpaid-error")
                    .addClass('d-none');
            }

            // COMMENTED

            // $('#preference_quantity').change(function () {
            //     // if (this.checked) {
            //     //     if (stockPrefernce == this.checked) {
            //     //         $(".showErrorMessageAlert-prefernces").addClass("d-none");
            //     //     }
            //     //     $('input[name="confirm-checkbox"][value="0"]').prop('checked', true);
            //     // } else {
            //     //     if (stockPrefernce == this.checked) {
            //     //         $(".showErrorMessageAlert-prefernces").addClass("d-none");
            //     //     }
            //     //     $('input[name="confirm-checkbox"][value="0"]').prop('checked', true);
            //     // } 


            //     if (this.checked) {

            //         var changeStockPrefernce = $('input[name="preference_quantity"]').is(':checked') ? $(
            //             'input[name="preference_quantity"]').val() : 0;

            //         if (changeStockPrefernce != stockPrefernce) {

            //             if (changeStockPrefernce == 1) {
            //                 $(".showErrorMessageAlert-prefernces").removeClass("d-none");
            //                 $(".showErrorMessageAlertLabel-prefernces").text(
            //                     'If you enable the “maintain stock” option, all Stock Quantity will be 0. Are you sure?'
            //                 );

            //             } else {
            //                 $(".showErrorMessageAlert-prefernces").removeClass("d-none");
            //                 $(".showErrorMessageAlertLabel-prefernces").text(
            //                     'If you enable the “maintain stock” option, all Stock Quantity will be Not Available (NA). Are you sure?'
            //                 );
            //             }

            //         } else {
            //             // updatePreferncesData();
            //             $(".showErrorMessageAlert-prefernces").addClass("d-none");
            //             $('input[name="confirm-checkbox"]').prop('checked', false);
            //         }
            //         $('input[name="confirm-checkbox"][value="0"]').prop('checked', true);

            //     } else {
            //         $(".showErrorMessageAlert-prefernces").removeClass("d-none");
            //         $(".showErrorMessageAlertLabel-prefernces").text(
            //             'If you enable the “maintain stock” option, all Stock Quantity will be Not Available (NA). Are you sure?'
            //         );

            //         $('input[name="confirm-checkbox"][value="1"]').prop('checked', true);
            //     }


            // });

            // COMMENTED
            // ----------------------------------- PREFERENCES --------------------------------------
        });

    </script>
@endsection