<!DOCTYPE html>
<!-- <html lang="en"> -->
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('assets/img/favicon/16.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('assets/img/favicon/32.png')}}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{asset('assets/img/favicon/64.png')}}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{asset('assets/img/favicon/512.png')}}">

    <!-- Toast -->
    <link rel="stylesheet" href="{{asset('assets/toast/css/jquery.toast.css')}}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i" rel="stylesheet">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- CSS Loader -->
    <link href="{{asset('assets/css/loader.css')}}" rel="stylesheet">


    <!-- Country Dropdown -->
    <link href="{{asset('assets/css/countryDropdown.css')}}" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">


    <title>{{  __('common.Ready Bill') }}</title>

    <style>
        * {
            font-family: 'Roboto', sans-serif !important;
        }

        /* Style for the top-left logo */
        #topLogo {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 10;
        }

        /* Style for the bottom-left text */
        #bottomText {
            position: fixed;
            bottom: 20px;
            left: 20px;
            color: white;
            z-index: 10;
        }

        #bottomText h1 {
            font-size: 60px !important;
        }

        #bottomText h2 {
            font-size: 40px !important;
        }


        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background-image: url('{{ asset("assets/img/main-photo.jpg") }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hover-underline:hover {
            text-decoration: underline !important;
        }


        /* --------------------------------------------- MODAL CSS --------------------------------------------- */
        .modal .modal-content {
            border-radius: 20px !important;
        }

        .modal .modal-header {
            border-bottom: none !important;
            /* Remove the bottom border line */
        }

        .btn-close {
            font-size: 20px !important;
        }

        .custom-modal-width {
            max-width: 35%;
        }

        /* --------------------------------------------- MODAL CSS --------------------------------------------- */

        /* --------------------------------------------- UPLOAD CSS --------------------------------------------- */
        .file-upload-container {
            display: inline-flex;
            align-items: center;
            border: 1px solid #ccc;
            /* Single border around both elements */
            border-radius: 5px;
            font-family: Arial, sans-serif;
            background-color: #fff;
            /* White background for the container */
        }

        .custom-file-upload {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #f3f4f6;
            /* Light gray button */
            border: none;
            border-right: 1px solid #ccc;
            /* Divider line */
            border-radius: 5px 0 0 5px;
            /* Rounded left corners only */
            font-size: 16px;
            color: #333;
            text-align: center;
            transition: background-color 0.3s;
        }

        .custom-file-upload:hover {
            background-color: #e5e7eb;
            /* Slightly darker gray on hover */
        }

        .file-name {
            padding: 10px 20px;
            font-size: 14px;
            color: #555;
            min-width: 150px;
            /* Ensure space for file name */
            cursor: pointer;
            /* Hand cursor for clickability */
        }

        #logo {
            display: none;
            /* Hide default file input */
        }

        /* --------------------------------------------- UPLOAD CSS --------------------------------------------- */
    </style>
</head>

<body>

    <!-- Top-left logo -->
    <div id="topLogo">
        <a href="/" class="">
            <img src="{{ asset('assets/img/ready-bill-white-logo.png') }}" alt="Logo" width="220">
        </a>
    </div>


    <!--################################################ MOBILE NUMBER CARD ################################################   -->
    <div class="position-fixed top-0 end-0 h-100 p-4" style="width: 650px;" id="loginSection">
        <div class="card h-100 bg-light bg-opacity-90 shadow" style="border-radius: 25px;">
            <div class="card-body d-flex flex-column p-4 p-md-5">
                <!-- Logo & Welcome Text with additional margin-top on larger screens -->
                <div class="mb-3 pt-4 pt-lg-6 mt-4 mt-lg-8">
                    <img src="{{ asset('assets/img/favicon/64.png') }}" alt="probill" width="50" class="mb-3">
                    <p class="fw-light mb-2">
                        {{  __('login_page.Welcome to') }}
                        <a href="/"
                            class="fw-normal text-primary text-decoration-none hover-underline">{{  __('common.Ready Bill') }}</a>
                    </p>
                    <h3 class="fw-medium">{{  __('login_page.Enter your mobile number & password') }}</h3>
                </div>

                <!-- Error Section -->
                @include('coreweb::components/error', ['heading' => 'Error'])


                @if ($errors->has('empty_detected_country_code'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $errors->first('empty_detected_country_code') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif



                <!-- Registration Form -->
                <form id="signinForm" method="POST" action="{{ locale_route('login') }}"
                    class="flex-grow-1 d-flex flex-column">
                    @csrf

                    <!-- <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">+91</span>
                            <input type="text" class="form-control form-control-lg" id="signin_mobile" name="mobile"
                                placeholder="Mobile Number" autofocus value="">
                        </div>
                        <span class="text-danger signin_mobile-error d-none"></span>
                    </div> -->

                    {{--<div class="mb-4 select2-dropdown-container">
                        <label for="mobile" class="form-label fw-semibold">{{ __('login_page.Mobile Number') }}</label>
                        <div class="input-group custom-input-group">

                            <!-- Country Code Dropdown -->
                            <div class="custom-select-wrapper">
                                <select class="form-select country-code-select countryCode" id="" name="country_code">
                                </select>
                            </div>

                            <!-- Mobile Number Input -->
                            <input type="text" class="form-control form-control-lg phone_number_check"
                                id="signin_mobile" name="mobile"
                                placeholder="{{ __('common.Enter your mobile number') }}" autofocus>

                        </div>
                        <span class="text-danger signin_mobile-error d-none"></span>
                    </div>--}}

                    <input name="loginFrom" type="hidden" value="2" readonly/>

                    <div class="mb-4 select2-dropdown-container">
                        <label for="signin_mobile" class="form-label fw-semibold">Mobile Number</label>
                        <div class="input-group custom-input-group">
                            <div class="custom-select-wrapper">
                                <select class="form-select country-code-select countryCode" name="country_code">
                                    <!-- blank option inserted by JS if missing -->
                                </select>
                            </div>
                            <input type="text" class="form-control form-control-lg phone_number_check"
                                id="signin_mobile" name="mobile" placeholder="{{ __('common.Enter your mobile number') }}" autofocus>
                        </div>
                        <span class="text-danger signin_mobile-error d-none"></span>
                    </div>


                    <div class="mb-3">
                        {{-- <input type="password" class="form-control form-control-lg" id="signin_password"
                            name="password" placeholder="{{ __('common.Password') }}*" /> --}}
                            
                        <label for="password" class="form-label fw-semibold">{{ __('common.Password') }}</label>
                        <div class="input-group">
                            <input type="password" class="form-control password form-control-lg" id="signin_password"
                                name="password" placeholder="{{ __('common.Password') }}" />
                            <span class="input-group-text position-relative togglePasswordWrapper"
                                data-target="signin_password">
                                <img class="togglePassword" src="{{ asset('assets/img/icons/eye.svg') }}"
                                    alt="Toggle Password" style="width: 18px; height: 18px;">
                            </span>
                        </div>
                        <span class="text-danger signin_password-error d-none"></span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div><input type="checkbox" class="form-check-input" id="remember_me"
                                name="remember_me" /><label for="remember_me" class="form-check-label px-1">
                                {{ __('common.Remember me') }}
                            </label></div>
                        <!-- <a href="forgot-password" target="_blank">Forgot Password</a> -->
                        <button type="button" class="btn btn-link" id="forgotPasswordButton">
                            {{ __('common.Forgot Password') }}</button>
                    </div>

                    <!-- Add extra margin for more space between the form and button -->
                    <div class="flex-grow-1"></div> <!-- This takes the available space between the form and button -->

                    <!-- Continue Button -->
                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-primary btn-lg">{{ __('login_page.Continue') }}</button>
                    </div>
                </form>

                <!-- Footer -->
                <div class="text-center mt-4 pt-4">
                    <hr class="mb-3">
                    <p class="text-muted small mb-0">
                        {{ __('login_page.By continuing, you agree to our') }}
                        <a href="{{ locale_route('privacy.policy') }}" target="_blank"
                            class="fw-bold text-decoration-none">{{ __('login_page.Privacy Policy') }}</a>
                        {{ __('login_page.and') }}
                        <a href="{{ locale_route('terms.and.conditions') }}" target="_blank"
                            class="fw-bold text-decoration-none">
                            {{ __('login_page.Terms of Use') }}
                        </a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!--################################################ MOBILE NUMBER CARD ################################################   -->


    <!--################################################ SHOP DETAILS CARD ################################################   -->
    @include('coreweb::components/register_shop_details')
    <!--################################################ SHOP DETAILS CARD ################################################   -->


    <!-- Bottom-left text -->
    <div id="bottomText">
        <h1 class="mb-1 fs-3 fs-md-4 fs-lg-5">{{ __('login_page.The') }} <span
                style="color:#00D47C">{{ __('login_page.fastest') }}</span> {{ __('login_page.billing app') }}</h1>
        <h2 class="mb-0 fs-5 fs-md-6 fs-lg-7">...{{ __('login_page.all from your mobile') }}.</h2>
    </div>

    <!-- loader -->
    <div class="overlay">
        <div class="overlay__inner">
            <div class="overlay__content">
                <div class="lds-spinner loader">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
    <!-- loader -->

    @include('coreweb::modals.successMessage')

    @include('coreweb::modals.changePassword', ['loc' => 'forgot'])
    @include('coreweb::modals.otp')
    @include('coreweb::modals.resetPassword')

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="{{asset('assets/toast/js/jquery.toast.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>


    <!-- jQuery Cookie CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        var base_url = "{{ env('BASE_URL') }}";
        var api_url = "{{ env('API_URL') }}";
        const duration = "{{ env('COUNTDOWN') }}";
        var x_api_key_secret = "{{ env('ENCRYPTED_API_SECRET_KEY') }}";
    </script>

    <!-- Country Dropdown -->
    <script src="{{asset('assets/js/countryDropdown.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>

    <script src="{{asset('assets/js/changePassword.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>
    <script src="{{asset('assets/js/login.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>

    <script src="{{asset('assets/js/common.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>
    <script src="{{asset('assets/js/validation.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>


    <script>

        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');


        // Extract country_code and lang from URL
        let urlSegments = window.location.pathname.split('/');
        let countryCode = urlSegments[1] || 'us';
        let lang = urlSegments[2] || 'en';

        localStorage.setItem('language', lang);
        localStorage.setItem('countryCode', countryCode);


        // Include CSRF token in AJAX request headers
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept-Language': lang,
                'Accept-country_code': countryCode,
                'X-API-Secret': x_api_key_secret,
            }
        });


        $(document).ready(function () {

            $('#signinForm').submit(function (e) {


                e.preventDefault();

                // console.log('HI');

                var formData = new FormData();
                formData.append('loginFrom', 2);
                formData.append('mobile', $('#signin_mobile').val());
                formData.append('password', $('#signin_password').val());

                formData.append('country_code', $('#signinForm .countryCode').val());

                var signin_mobile = $('#signin_mobile').val();
                var signin_password = $('#signin_password').val();
                var rememberMe = $('#remember_me').prop('checked');
                $.ajax({
                    url: api_url + 'login-validation',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function (xhr) {
                        // xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]')
                        //     .attr('content'));
                        $(".overlay").show();
                    },
                    success: function (response) {


                        console.log('signinForm', response);

                        if (response) {

                            if (response.user && response.user.user_id != 0 && response.user.checkUser == 0) {

                                $("#registerSection-4").slideUp(300, function () {
                                    $("#loginSection").addClass("d-none"); // Hide after fade out
                                    $("#registerSection-4").removeClass("d-none").hide().fadeIn(300);
                                    $(".overlay").hide();

                                    $("#registerSection-4 #user_id").val(response.user.user_id);
                                });
                            }
                            else if (response.status == "success") {

                                if (rememberMe) {
                                    $.cookie('signin_mobile', signin_mobile, {
                                        expires: 365
                                    });
                                    $.cookie('signin_password', signin_password, {
                                        expires: 365
                                    });
                                } else {
                                    $.removeCookie('signin_mobile');
                                    $.removeCookie('signin_password');
                                }

                                $('#signinForm').get(0).submit();
                            }

                        }

                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        // console.error(error);
                        // console.log('handleLoginErrors',xhr.status);

                        handleLoginErrors(error);
                    }
                });

            });

            function resetLoginForm() {
                $("#signin_mobile, #signin_password, #signinForm .custom-input-group")
                    .removeClass('border border-2 border-danger');
                $(".signin_mobile-error, .signin_password-error")
                    .addClass('d-none');
            }

            function handleLoginErrors(error) {
                resetLoginForm();

                console.log('login error', error, error.data);

                if (error.errors && (error.code == 400) && (error.status == 'failed')) {

                    console.log('IF');

                    $.each(error.errors, function (key, value) {
                        switch (key) {
                            case 'mobile':
                                // $("#signin_" + key).addClass('border border-2 border-danger');
                                // $(".signin_" + key + "-error").removeClass('d-none');
                                // $(".signin_" + key + "-error").text(value[0]);

                                $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                                $("#signin_password").addClass('border border-2 border-danger');
                                $(".signin_password-error").removeClass('d-none');
                                $(".signin_password-error").text(value[0]);

                                break;
                            case 'password':
                                // $("#signin_" + key).addClass('border border-2 border-danger');
                                // $(".signin_" + key + "-error").removeClass('d-none');
                                // $(".signin_" + key + "-error").text(value[0]);

                                $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                                $("#signin_password").addClass('border border-2 border-danger');
                                $(".signin_password-error").removeClass('d-none');
                                $(".signin_password-error").text(value[0]);

                                break;
                            case 'country_code':
                                // $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                                // $(".signin_mobile-error").removeClass('d-none');
                                // $(".signin_mobile-error").text(value[0]);

                                $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                                $("#signin_password").addClass('border border-2 border-danger');
                                $(".signin_password-error").removeClass('d-none');
                                $(".signin_password-error").text(value[0]);

                                break;
                            default:
                                console.warn('Unhandled error key:', key);
                                break;
                        }
                    });
                }
                else if (error.status == 'failed') {

                    console.log('ELSE');

                    $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                    $("#signin_password").addClass('border border-2 border-danger');
                    $(".signin_password-error").removeClass('d-none');
                    $(".signin_password-error").text(error.message);
                }
                else if (error.status == 'country-code') {
                    $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                    $(".signin_mobile-error").removeClass('d-none');
                    $(".signin_mobile-error").text(error.message);
                }
                else if (error.status == 'subscription-failed') {

                    $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                    // $("#signin_mobile").addClass('border border-2 border-danger');
                    $("#signin_password").addClass('border border-2 border-danger');
                    $(".signin_password-error").removeClass('d-none');
                    $(".signin_password-error").text(error.message);

                }
                else if (error.status == 'user-deactivate') {
                    $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                    // $("#signin_mobile").addClass('border border-2 border-danger');
                    $("#signin_password").addClass('border border-2 border-danger');
                    $(".signin_password-error").removeClass('d-none');
                    $(".signin_password-error").text(error.message);
                }
            }


            // ------------------------------------------------------- SHOP DETAILS FORM ------------------------------------------------------
            $('#shopDetailForm').submit(function (e) {

                e.preventDefault();

                var formData = new FormData(this);
                // formData.append('user_id', $("#user_id").val());
                // formData.append('name', $("#name").val());
                // formData.append('business_name', $("#business_name").val());
                // formData.append('email', $("#email").val());
                // formData.append('address', $("#address").val());
                // formData.append('gstin', $("#gstin").val());

                // if ($('#logo')[0].files.length > 0) {
                //     formData.append('logo', $('#logo')[0].files[0]);
                // }

                $.ajax({
                    url: base_url + '/register/create-shop',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function (xhr) {
                        $(".overlay").show();
                    },
                    success: function (response) {
                        // Handle the success response
                        if (response.status == 1) {
                            $(".overlay").hide();
                            var functionLoc = "registerSection-4";
                            var errorHtml = $(`#${functionLoc} .error-div `)
                            errorHtml.addClass('d-none');

                            // SHOW SUCCESS MESSAGE.
                            $("#successMessage").html(@json(__('login_page.Your registration with ReadyBill is successful'))+".<br>"+@json(__('login_page.Please login'))+" <a href='/login' class='text-primary'>"+ @json(__('common.here')) +"</a>.");
                            $("#successMessageModal").modal('show');

                        }
                    },
                    error: function (xhr) {

                        $('.overlay').hide();
                        var response = xhr.responseJSON;

                        var functionLoc = "registerSection-4";
                        var errorHtml = $(`#${functionLoc} .error-div `)
                        errorHtml.removeClass('d-none');
                        // errorHtml.html("");
                        errorHtml.find(".error-body").empty();

                        if (xhr.status === 400 && response && response.data) {
                            var errorMessages = []; // Collect error messages

                            // Loop through validation errors and display them
                            $.each(response.data.errors, function (field, messages) {
                                console.log('errors', field, messages);
                                errorHtml.find(".error-body").append('<p class="my-1 text-danger">' + messages + '</p>');
                            });


                            // // Scroll to the entire section identified by functionLoc
                            // $('html, body').animate({
                            //     scrollTop: $(`#${functionLoc}`).offset().top - 85 // Adjust for spacing
                            // }, 100);

                        }
                        else if (xhr.status === 429 && response) {
                            let message = response.message + (response.data.retry_after ? ` @json(__('login_page.Please try again in')) ${response.data.retry_after}.` : '');
                            errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + message + '</p>');
                        }

                        else if (xhr.status === 410) {
                            errorHtml.find(".error-body").html(`<p class="my-1 text-danger"> @json(__('login_page.OTP has expired')). @json(__('login_page.Please request a new OTP')). </p>`);
                        }
                        else {
                            errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + xhr.responseJSON.message || `@json(__('common.An unexpected error occurred')). @json(__('common.Please try again later')).` + '</p>');
                        }

                        // Scroll to the entire section identified by functionLoc
                        $('html, body').animate({
                            scrollTop: $(`#${functionLoc}`).offset().top - 85 // Adjust for spacing
                        }, 100);

                    }
                });
            });
            // ------------------------------------------------------- SHOP DETAILS FORM ------------------------------------------------------

            // ------------------------------------------------------- COUNTRY CODE ------------------------------------------------------


            getCountryCode().then(response => {

                let countryDropdown = $(".countryCode");

                if (response && response.countryCode) { // Expecting countryShort (e.g., 'IN', 'US')
                    let dialCode = countryMap[response.countryCode]; // Find corresponding dial code

                    console.log('dialCode', dialCode);

                    if (dialCode) {
                        countryDropdown.val(response.countryCode).trigger('change');
                    } else {
                        console.warn("No matching country found for:", response.countryCode);
                    }
                } else {
                    console.warn("Country short code not found in response");
                }
            }).catch(error => {
                console.error("Failed to fetch country code:", error);
            });
            // ------------------------------------------------------- COUNTRY CODE ------------------------------------------------------



        });

    </script>

</body>

</html>