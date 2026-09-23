@extends('coreweb::layouts.groceryIndia')
@section('title', "{{ __('common.Profile') }}")
@section('content')


                    <div class="pagetitle">
                        <h1>{{ __('common.Profile') }}</h1>
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                                <li class="breadcrumb-item active">{{ __('common.Profile') }}</li>
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
                                        <form class="updateProfile" id="update-profile">

                                            <div class="text-danger mb-3 employeeNote d-none">
                                                <!-- Note: If any updatation needed, kindly contact you shop owner -->
                                                {{ __('profile_page.Note: If any updation is required, kindly contact your shop owner') }}
                                            </div>

                                            <input name="user_id" type="hidden" class="form-control" id="user_id" value="" readonly />

                                            <input name="isAdmin" type="hidden" class="form-control" id="isAdmin" value="" readonly />

                                            <div class="row mb-3">
                                                <label for="entity_id" class="col-md-4 col-lg-3 col-form-label">{{ __('profile_page.Entity ID') }}</label>
                                                <div class="col-md-8 col-lg-9">
                                                    <input name="entity_id" type="text" class="form-control" id="entity_id" value=""
                                                        readonly style="background-color:#EEEEEE" />
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label for="fullName" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Name') }}</label>
                                                <div class="col-md-8 col-lg-9">
                                                    <input name="name" type="text" class="form-control" id="name" value="" autofocus />
                                                    <span class="text-danger name-error d-none"></span>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label for="fullName" class="col-md-4 col-lg-3 col-form-label">{{ __('profile_page.Business Name') }}</label>
                                                <div class="col-md-8 col-lg-9">
                                                    <input name="business_name" type="text" class="form-control" id="business_name"
                                                        value="" />
                                                    <span class="text-danger business_name-error d-none"></span>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label for="company" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Email') }}</label>
                                                <div class="col-md-8 col-lg-9">
                                                    <input name="email" type="email" class="form-control" id="email" value="" />
                                                    <span class="text-danger email-error d-none"></span>
                                                </div>
                                            </div>

                                            <div class="row mb-3 select2-dropdown-container">
                                                <label for="company" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Mobile Number') }}</label>
                                                <div class="col-md-8 col-lg-9">

                                                    <div class="">
                                                        <div class="input-group custom-input-group">

                                                            <div class="custom-select-wrapper">
                                                                <select class="form-select country-code-select countryCode" id="">
                                                                </select>
                                                            </div>

                                                            <input type="text" class="form-control form-control-lg phone_number_check" id="mobile"
                                                                name="mobile" placeholder="{{ __('profile_page.Enter your mobile number') }}" autofocus>
                                                        </div>
                                                    </div>

                                                    <span class="text-danger mobile-error d-none"></span>
                                                </div>
                                                <button type="button" class="btn btn-link text-end" id="changeMobileNumberButton">
                                                    {{ __('profile_page.Change Mobile Number') }}</button>
                                            </div>

                                            <div class="row mb-3">
                                                <label for="about" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Address') }}</label>
                                                <div class="col-md-8 col-lg-9">
                                                    <textarea name="address" class="form-control" id="address"
                                                        style="height: 100px"></textarea>
                                                    <span class="text-danger address-error d-none"></span>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label for="shop_type" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Shop Type') }}</label>
                                                <div class="col-md-8 col-lg-9">
                                                    <select id="shop_type" name="shop_type" class="form-select" disabled>
                                                        <option disabled selected value="">{{ __('common.Choose Type') }}</option>
                                                        @foreach(config('general.shop_type') as $shopType)
                                                            <option value="{{ $shopType }}">
                                                                {{ ucfirst(strtolower($shopType)) }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                    <span class="text-danger shop_type-error d-none"></span>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label for="Job" class="col-md-4 col-lg-3 col-form-label">{{ __('profile_page.GSTIN Number') }}</label>
                                                <div class="col-md-8 col-lg-9">
                                                    <input name="gstin" type="text" class="form-control" id="gstin" value="">
                                                    <span class="text-danger shop_type-error d-none"></span>
                                                </div>
                                            </div>

                                            <div class="row mb-3 logoSection">
                                                <label for="Job" class="col-md-4 col-lg-3 col-form-label">{{ __('profile_page.Logo') }}</label>
                                                <div class="col-md-8 col-lg-9">
                                                    <div class="position-relative d-inline-block">
                                                        <!-- Image -->
                                                        <img src="" class="img-fluid mb-2 text-center" id="userLogo" alt="Logo">

                                                        <!-- Delete Button -->
                                                        <button type="button" class="btn btn-link position-absolute d-none"
                                                            id="clear-button"
                                                            style="top: 0px; right: -60px; z-index: 1; font-size: 12px; color: red; background: rgba(255, 255, 255, 0.7); border-radius: 4px;">
                                                            {{ __('common.Delete') }}
                                                        </button>
                                                    </div>

                                                    <!-- Error Message -->
                                                    <!-- <span class="text-danger logo-error d-none userLogoMsg"
                                                        style="padding-bottom:2px !important;">
                                                        No logo has been uploaded
                                                    </span> -->

                                                    <!-- File Input -->
                                                    <input name="logo" type="file" class="form-control" id="logo" value="" accept="image/*" />
                                                    <input type="hidden" name="isLogoDelete" id="isLogoDelete" value="0" readonly />
                                                    <span class="text-danger logo-error d-none"></span>
                                                </div>
                                            </div>

                                            <div class="row mb-3 staffPhotoSection d-none">
                                                <label for="staffPhoto" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Photo') }}</label>
                                                <div class="col-md-8 col-lg-9">
                                                    <div class="position-relative d-inline-block">
                                                        <!-- Image -->
                                                        <img src="" class="img-fluid mb-2 text-center" name="staffPhoto" id="staffPhoto" alt="Logo">
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="row mb-3 apiKeySection">
                                                <label for="Job" class="col-md-4 col-lg-3 col-form-label">{{ __('profile_page.API Key') }}</label>
                                                <div class="col-md-8 col-lg-9">

                                                    <textarea name="api_key" id="api_key" class="form-control"></textarea>

                                                </div>
                                            </div>

                                            <div class="text-left">
                                                <button type="submit" class="btn btn-primary profileUpdateButton">{{ __('common.Update Changes') }}</button>
                                            </div>
                                        </form>
                                        <!-- End Profile Edit Form -->
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-8 deleteAccountSection">
                                <div class="card">
                                    <div class="card-body pt-3">

                                            <div class="row">
                                                <label for="fullName" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Delete Account') }}</label>
                                                <div class="col-md-8 col-lg-9">
                                                    <button type="button" class="btn text-danger btn-link text-end" id="deleteAccountConfirmationBtn">
                                                        {{ __('common.Delete My Account') }}</button>
                                                </div>
                                            </div>

                                    </div>
                                </div>
                            </div>


                        </div>
                    </section>


                    @include('coreweb::modals.confirmation', [
        'heading' => __('common.Are you sure you want to do the action ?'),
        'subHeading' => __('profile_page.Once proceed, your inventory, transactions, dataset and account will be permanently deleted.'),
        'buttonText' => __('common.Click Confirm to proceed')
    ])


@endsection


@include('coreweb::modals.otp', ['loc' => 'profile'])
@include('coreweb::modals.changeMobileNumberModal')
@include('coreweb::modals.deleteConfirmationModal')


@include('coreweb::modals.confirmation', [
    'heading' => __('common.Are you sure you want to do the action ?'),
    'subHeading' => __('profile_page.Once reset, your changes will be lost, and the dataset will return to its original state.'),
    'buttonText' => __('common.Click Confirm to proceed')
])


@section('scripts')
    @parent
    <!-- Include parent scripts -->
    <script>
        $(document).ready(function () {


            var loggedInUserMobile = "{{Auth::user()->mobile}}";

            // ----------------------------------- UPDATE USER PROFILE --------------------------------------
            $('#update-profile').submit(function (e) {

                e.preventDefault();

                var formData = new FormData();

                formData.append('user_id', $('#user_id').val());
                formData.append('name', $('#name').val());
                formData.append('email', $('#email').val());

                formData.append('old_mobile', loggedInUserMobile);
                formData.append('mobile', $('#mobile').val());

                formData.append('address', $('#address').val());
                formData.append('shop_type', $('#shop_type').val());
                formData.append('gstin', $('#gstin').val());
                formData.append('isLogoDelete', $('#isLogoDelete').val());
                if ($('#logo')[0].files.length > 0) {
                    formData.append('logo', $('#logo')[0].files[0]);
                }
                formData.append('password', $('#password').val());
                formData.append('isAdmin', $('#isAdmin').val());


                if ($("#isAdmin").val() == 0) {

                    formData.append('isPhotoDelete', $('#isPhotoDelete').val());
                    if ($('#photo')[0].files.length > 0) {
                        formData.append('photo', $('#photo')[0].files[0]);
                    }
                }

                console.log('formData', formData);

                $.ajax({
                    url: api_url + 'update-profile',
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


                            // $.toast({
                            //     heading: 'Success',
                            //     text: response.message,
                            //     icon: 'success',
                            //     loader: true,
                            //     position: 'top-right',
                            //     loaderBg: '#9EC600'
                            // });
                            // window.location.href = base_url_with_country_lang + 'profile';
                            $(".overlay").hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);
                        handleUserProfileDataErrors(error);
                    }
                });

            });

            function resetUserPofileForm() {
                // Reset form fields
                $("#name, #username, #email, #mobile, #password, #password_confirmation, #address, #shop_type, #gstin, #logo")
                    .removeClass('border border-2 border-danger');
                $(".name-error, .email-error, .mobile-error, .password-error, .password_confirmation-error, .address-error, .shop_type-error, gstin-error, .logo-error")
                    .addClass('d-none');
            }

            function handleUserProfileDataErrors(error) {
                // Use switch case for displaying error messages
                resetUserPofileForm();

                $.each(error.data, function (key, value) {
                    switch (key) {
                        case 'name':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;
                        case 'username':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;
                        case 'email':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;
                        case 'mobile':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;
                        case 'password':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("#password_confirmation").addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;
                        case 'address':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;
                        case 'shop_type':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;

                        case 'gstin':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;

                        case 'logo':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            // $("." + key + "-error").text(value[0]);
                            $("." + key + "-error").text(Array.isArray(value) ? value.join(' ') : value);
                            break;

                        default:
                            console.warn('Unhandled error key:', key);
                            break;
                    }
                });
            }


            // When a file is selected
            // $('#logo').change(function () {

            //     var file = this.files[0];
            //     if (file) {
            //         var reader = new FileReader();
            //         reader.onload = function (e) {
            //             // Set the source of the preview image to the data URL
            //             $('#userLogo').attr('src', e.target.result);

            //             // Once the image is loaded, set its size
            //             $('#userLogo').on('load', function () {
            //                 $(this).css({
            //                     'max-width': '200px',
            //                     'max-height': '100px'
            //                 });
            //             });

            //         };
            //         // Read the file as a data URL
            //         reader.readAsDataURL(file);

            //         // $(".clear-button-div").removeClass("d-none");
            //         $("#clear-button").removeClass("d-none");
            //         $(".userLogo-div").removeClass("d-none");

            //         $("#isLogoDelete").val(0);
            //     }
            // });


            $('#logo').change(function () {
                // ✅ Call validation function first
                if (!validateImageFile(this, ".logo-error","")) return;

                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                                reader.onload = function (e) {
                                    $('#userLogo').attr('src', e.target.result);
                                    $('#userLogo').on('load', function () {
                                        $(this).css({
                                            'max-width': '200px',
                                'max-height': '100px'
                            });
                        });
                    };
                    reader.readAsDataURL(file);

                    $("#clear-button").removeClass("d-none");
                    $(".userLogo-div").removeClass("d-none");
                    $("#isLogoDelete").val(0);
                }
            });


            // Function to clear the preview image and reset file input
            function clearPreview() {
                $('#userLogo').attr('src', ''); // Clear the image source
                $('#logo').val(''); // Reset the file input

                // $(".clear-button-div").addClass("d-none");
                $("#clear-button").addClass("d-none");
                $(".userLogo-div").addClass("d-none");


                $('#userLogo').attr('src', base_url + '/assets/img/user.jpg');

                $("#isLogoDelete").val(1);

            }

            // Clear button click event handler
            $('#clear-button').click(function () {
                clearPreview();
            });

            // ----------------------------------- UPDATE USER PROFILE --------------------------------------


            // // ----------------------------------- EMPLOYEE PHOTO --------------------------------------
            // // When a file is selected
            // $('#photo').change(function () {

            //     var file = this.files[0];
            //     if (file) {
            //         var reader = new FileReader();
            //         reader.onload = function (e) {
            //             // Set the source of the preview image to the data URL
            //             $('#userPhoto').attr('src', e.target.result);

            //             // Once the image is loaded, set its size
            //             $('#userPhoto').on('load', function () {
            //                 $(this).css({
            //                     'max-width': '200px',
            //                     'max-height': '100px'
            //                 });
            //             });

            //         };
            //         // Read the file as a data URL
            //         reader.readAsDataURL(file);

            //         // $(".clear-button-div").removeClass("d-none");
            //         $("#clear-button").removeClass("d-none");
            //         $(".userPhoto-div").removeClass("d-none");

            //         $("#isLogoDelete").val(0);
            //     }
            // });


            // // Function to clear the preview image and reset file input
            // function clearPreview() {
            //     $('#userPhoto').attr('src', ''); // Clear the image source
            //     $('#logo').val(''); // Reset the file input

            //     // $(".clear-button-div").addClass("d-none");
            //     $("#clear-button").addClass("d-none");
            //     $(".userPhoto-div").addClass("d-none");


            //     $('#userPhoto').attr('src', base_url + '/assets/img/user.jpg');

            //     $("#isPhotoDelete").val(1);

            // }

            // // Clear button click event handler
            // $('#photo-clear-button').click(function () {
            //     clearPreview();
            // });

            // // ----------------------------------- EMPLOYEE PHOTO --------------------------------------


            // ------------------------------------------------------ CHANGE MOBILE NUMBER ------------------------------------------------------
            $(document).on('click', '#changeMobileNumberButton', function () {
                $("#changeMobileNumberModal").modal('show');
            });

            var myModal = new bootstrap.Modal(document.getElementById('changeMobileNumberModal'));
            myModal._element.addEventListener('shown.bs.modal', function () {
                document.getElementById('newMobileNumber').focus();
            });

            $(document).on('click', '.changeMobileNumberSendOTP', function () {
                generateOrVerifyOTP("send-otp", "profile", "change_mobile_number", "changePasswordModal");
            });


            $('.otp-box').on('input', function (e) {

                e.preventDefault();
                const otpInputs = $('.otp-box');
                let otp = '';
                otpInputs.each(function (index) {
                    const value = $(this).val();
                    otp += value;
                });

                var otpLength = otp.length;
                if (otpLength === 6) {
                    // generateOrVerifyOTP("verify-otp", "profile", "", "changePasswordSection");

                    //  CALL UPDATE MOBILE NUMBER API
                    var formData = new FormData();
                    formData.append('mobile', $("#newMobileNumber").val());
                    formData.append('user_id', $("#user_id").val());
                    formData.append('country_code', $("#changeMobileNumberModal .countryCode").val());
                    formData.append('otp', otp);
                    updateMobileNumber(formData);
                    //  CALL UPDATE MOBILE NUMBER API
                }

            });

            // ------------------------------------------------------ CHANGE MOBILE NUMBER ------------------------------------------------------


            // // ----------------------------------- VERIFY MOBILE NUMBER --------------------------------------
            // $('.updateMobileNumberOTPVerificaion').click(function (e) {
            //     e.preventDefault();

            //     generateOrVerifyOTP("send-otp", "profile", "change_mobile_number", "changePasswordModal");

            // });


            // // ----------------------------------- VERIFY MOBILE NUMBER --------------------------------------



            // // ------------------------------------------------------- RESEND OTP ------------------------------------------------------
            $('#profile-resend-otp-btn').on('click', function (e) {
                e.preventDefault();
                reSendOTP("change_mobile_number", $('#newMobileNumber').val());
            });
            // // ------------------------------------------------------- RESEND OTP ------------------------------------------------------

            // // ------------------------------------------------------- DELETE ACCOUNT ------------------------------------------------------
            $("#deleteAccountConfirmationBtn").click(function () {
                $("#confirmationModal").modal("show");
            });



            $(".confrimModalButton").click(function () {
                $("#confirmationModal").modal("hide");
                generateOrVerifyOTP("send-otp", "account-delete", "delete_account", "deleteConfirmationModal");
                $("#deleteConfirmationModal").modal("show");
            });



            // Automatically focus on the next OTP box
            $('.deleteotpbox').on('input', function () {
                const currentBox = $(this);
                const nextBox = $(`#deleteotp-${parseInt(currentBox.data('index')) + 1}`);
                if (currentBox.val().length === 1 && nextBox.length) {
                    nextBox.focus();
                }
            });

            // Restrict to numeric input only
            $('.deleteotpbox').on('keypress', function (e) {
                if (isNaN(String.fromCharCode(e.which))) {
                    e.preventDefault();
                }
            });

            // Handle backspace: delete current value and move focus to previous box
            $('.deleteotpbox').on('keydown', function (e) {
                if (e.key === 'Backspace') {
                    e.preventDefault(); // Prevent default backspace behavior

                    const currentBox = $(this);
                    const prevBox = $(`#deleteotp-${parseInt(currentBox.data('index')) - 1}`);

                    // Clear current box
                    currentBox.val('');

                    // Move focus to the previous box if it exists
                    if (prevBox.length) {
                        prevBox.focus();
                    }
                }
            });


            // Auto-focus the first OTP input box
            const firstOtpBox = document.getElementById('deleteotp-1');
            if (firstOtpBox) {
                firstOtpBox.focus();
            }



            $('.deleteotpbox').on('input', function (e) {

                e.preventDefault();
                const otpInputs = $('.deleteotpbox');
                let otp = '';
                otpInputs.each(function (index) {
                    const value = $(this).val();
                    otp += value;
                });

                var otpLength = otp.length;
                if (otpLength === 6) {
                    var formData = new FormData();
                    formData.append('user_id', $("#user_id").val());
                    formData.append('otp', otp);
                    deleteAccount(formData);
                }

            });


            function deleteAccount(formData) {

                $.ajax({
                    url: api_url + 'delete-account',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    data: formData,
                    contentType: false, // Important for FormData
                    processData: false, // Important for FormData
                    beforeSend: function () {
                        $('.overlay').show();
                    },
                    success: function (response) {


                        if (response && response.status == 1) {

                            $('#deleteConfirmationModal').modal('hide');


                            // Handle OTP verification success
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });

                            window.location.href = base_url;


                        } else {
                            // Handle error responses
                            $.toast({
                                heading: 'Error',
                                text: 'An unexpected error occurred. Please try again later.',
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                        }

                        $('.overlay').hide();
                    },
                    error: function (xhr) {

                        $('.overlay').hide();
                        var response = xhr.responseJSON;

                        var functionLoc = "deleteConfirmationModal";
                        var errorHtml = $(`#${functionLoc} .error-div `)
                        errorHtml.removeClass('d-none');


                        console.log('deleteAccount outside', response, xhr.status,functionLoc);

                        if (xhr.status === 400 && response && response.data) {
                            // Loop through validation errors and display them
                            $.each(response.data.errors, function (field, messages) {

                                console.log('changeMobileNumberModal 400', messages);

                                errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + messages + '</p>');
                            });
                        }
                        else if (xhr.status === 429 && response) {

                            console.log('changeMobileNumberModal 429', response);

                            let message = response.message + (response.data.retry_after ? ` Please try again in ${response.data.retry_after}.` : '');
                            errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + message + '</p>');
                        }

                        else if (xhr.status === 410) {
                            errorHtml.find(".error-body").html('<p class="my-1 text-danger"> OTP has expired. Please request a new OTP. </p>');
                        }
                        else {
                            errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + xhr.responseJSON.message || 'An unexpected error occurred. Please try again later.' + '</p>');
                        }

                    }
                });
            }

            // // ------------------------------------------------------- DELETE ACCOUNT ------------------------------------------------------


        });

    </script>
@endsection