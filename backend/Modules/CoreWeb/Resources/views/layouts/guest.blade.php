<!DOCTYPE html>
<!-- <html lang="en"> -->
<html lang="{{ app()->getLocale() }}">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="google" content="notranslate">

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('assets/img/favicon/16.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('assets/img/favicon/32.png')}}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{asset('assets/img/favicon/64.png')}}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{asset('assets/img/favicon/512.png')}}">

    <!-- Toast -->
    <link rel="stylesheet" href="{{asset('assets/toast/css/jquery.toast.css')}}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">


    <!-- CSS Loader -->
    <link href="{{asset('assets/css/loader.css')}}" rel="stylesheet">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">


    <!-- Country Dropdown -->
    <link href="{{asset('assets/css/countryDropdown.css')}}" rel="stylesheet">


    <title>{{  __('common.Ready Bill') }}</title>

    <style>
        * {
            font-family: 'Roboto', sans-serif !important;
        }

        body {
            background-color: #EDF0F3 !important;
            /* background-color: red; */
        }

        .btn-link:hover {
            text-decoration: none;
        }

        .btn-linkBtn {
            /* background: #6E6D70; */
            background: black;
            color: #DFDEDF !important;
            text-decoration: none;
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

        .input-error {
            border: 1px solid red;
            border-radius: 4px;
            outline: none;
        }

        /* --------------------------------------------- FOOTER --------------------------------------------- */
        .logo-divider {
            width: 3px;
            /* Thickness of the divider */
            height: 35px;
            /* Adjust height */
            background-color: #6c757d;
            /* Adjust color */
            margin: 0 10px;
            /* Spacing between logos */
        }

        /* --------------------------------------------- FOOTER --------------------------------------------- */


        /* --------------------------------------------- LANGUAGE SWITCHING --------------------------------------------- */
        /* Wrapper to draw icons via pseudo-element and center easily */
        .lang-select {
            position: relative;
            display: inline-block;
        }

        /* Base pill styling for the native Bootstrap 5 .form-select */
        .lang-select .form-select {
            border-radius: 999px;
            height: 42px;
            /* line-height: 42px; */
            padding-left: 3rem;
            /* space for globe */
            padding-right: 3rem;
            /* space for chevron */

            /* min-width: 220px; */
            /* a little small on desktop */
            border-width: 2px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-repeat: no-repeat;
            background-position: right 1rem center;
            /* chevron position */
            background-size: 16px 12px;
        }

        /* Light chevron icon */
        .lang-select-light .form-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='12' viewBox='0 0 16 12'%3E%3Cpath d='M2 2l6 6 6-6' fill='none' stroke='%23343a40' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        }

        /* Dark chevron icon */
        .lang-select-dark .form-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='12' viewBox='0 0 16 12'%3E%3Cpath d='M2 2l6 6 6-6' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        }

        /* Left globe icon (stroke color inherited per variant below) */
        .lang-select::before {
            content: "";
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background-position: center;
            background-repeat: no-repeat;
            background-size: 26px 26px;
        }

        /* Light theme */
        .lang-select-light .form-select {
            background-color: #fff;
            color: #212529;
            border-color: #212529;
        }

        .lang-select-light::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='26' height='26' viewBox='0 0 24 24'%3E%3Cg fill='none' stroke='%23343a40' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='10'/%3E%3Cpath d='M2 12h20'/%3E%3Cpath d='M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .lang-select-light .form-select:focus {
            border-color: #212529;
            box-shadow: 0 0 0 .2rem rgba(33, 37, 41, .15);
        }

        /* Dark theme */
        .lang-select-dark .form-select {
            background-color: #212529;
            color: #f8f9fa;
            border-color: #212529;
        }

        .lang-select-dark::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='26' height='26' viewBox='0 0 24 24'%3E%3Cg fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='10'/%3E%3Cpath d='M2 12h20'/%3E%3Cpath d='M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .lang-select-dark .form-select:focus {
            border-color: #212529;
            box-shadow: 0 0 0 .2rem rgba(33, 37, 41, .35);
        }

        /* Mobile responsiveness */
        @media (max-width:576px) {
            .lang-select {
                width: 100%;
            }

            /* allow full width within row */
            .lang-select .form-select {
                min-width: 0;
                height: 38px;
                line-height: 38px;
                font-size: .95rem;
                padding-left: 2.6rem;
                padding-right: 2.6rem;
            }

            .lang-select::before {
                left: 8px;
                width: 22px;
                height: 22px;
                background-size: 22px 22px;
            }
        }

        /* --------------------------------------------- LANGUAGE SWITCHING --------------------------------------------- */
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Header -->
    <header>
        <div class="container">
            <div class="row mt-4 d-flex justify-content-between">
                <!-- Logo and Company Info -->
                <div class="col-6 text-left">
                    <a href="/" class="">
                        <img src="{{asset('assets/img/readybill.png')}}" alt="probill" width="220"
                            class="d-inline-block align-text-top" />
                        <!-- <p class="px-3">by Alegra Labs</p> -->
                    </a>
                </div>

                <!-- Authentication Links -->
                <div class="col-6 text-end">
                    @if (route::has('login'))
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            @auth
                                <!-- <a href="{{ url('/sell') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Sell</a> -->
                            @else

                                {{--<select class="form-select languageSelect" id="" aria-label="Select language"
                                    style="width: auto;">
                                </select>--}}




                                <!-- LIGHT -->
                                <div class="d-flex justify-content-center">
                                    <!-- small width on desktop, fluid on mobile -->
                                    <div class="lang-select lang-select-light w-100 w-sm-75 w-md-50" style="max-width:360px;">
                                        <select class="form-select languageSelect" aria-label="Select language">
                                        </select>
                                    </div>
                                </div>





                                <a href="{{locale_route('register')}}" class="btn btn-linkBtn fw-bold text-uppercase"
                                    style="color:#456582">{{  __('common.Register') }}</a>
                                <button type="button" class="btn btn-linkBtn fw-bold text-uppercase" data-bs-toggle="modal"
                                    data-bs-target="#signin" style="color:#456582">{{  __('common.Sign In') }}</button>
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
            <div class="row my-3">
                <div class="col-12">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                    @endif
                </div>
            </div>
        </div>
    </header>
    <!-- Header -->

    <!-- Start #main -->
    <main id="main" class="main">
        @yield('content')
    </main>
    <!-- End #main -->

    <!-- Footer -->

    <footer id="footer" class="footer py-4 mt-auto">

        <div class="container px-md-4 px-3">
            <div class="row align-items-center">

                <!-- Logo Section -->
                <div class="col-md-6 col-12 text-md-start text-center mb-3 mb-md-0">
                    <div class="d-flex justify-content-md-start justify-content-center align-items-center">
                        <img src="{{asset('assets/img/alegra-logo.png')}}" alt="Alegra Labs" height="40" class="me-2">
                        <div class="logo-divider"></div>
                        <img src="{{asset('assets/img/readybill.png')}}" alt="Ready Bill" height="35" class="ms-2">
                    </div>
                </div>

                <!-- Social Icons -->
                <div class="col-md-6 text-md-end text-center">
                    <div class="d-flex justify-content-md-end justify-content-center gap-3">
                        <a href="https://www.facebook.com/Alegralabs/" target="_blank">
                            <img src="{{asset('assets/img/scoial-icons/fb.png')}}" alt="Facebook" width="40">
                        </a>
                        <a href="https://x.com/i/flow/login?redirect_after_login=%2Falegralabs22" target="_blank">
                            <img src="{{asset('assets/img/scoial-icons/twittr.png')}}" alt="Twitter" width="40">
                        </a>
                        <a href="https://www.instagram.com/alegralabs7/" target="_blank">
                            <img src="{{asset('assets/img/scoial-icons/insta.png')}}" alt="Instagram" width="40">
                        </a>
                        <a href="https://www.linkedin.com/company/helix-enterprise/posts/?feedView=all" target="_blank">
                            <img src="{{asset('assets/img/scoial-icons/in.png')}}" alt="LinkedIn" width="40">
                        </a>
                    </div>
                </div>

            </div>

            <div class="my-3"></div>

            <!-- <hr class="my-3"> -->

            <div class="row text-center text-md-start">
                <div class="col-md-6 mb-2 mb-md-0">
                    <p class="mb-0">
                        {{  __('common.Copyright 2024 ©') }} <strong>{{  __('common.Ready Bill') }}</strong>.
                        {{  __('common.Designed and developed by') }}
                        <a href="https://www.alegralabs.com" target="_blank" class="fw-bold text-primary">Alegra
                            Labs</a>
                    </p>
                </div>

                <!-- Footer Links -->
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <a href="{{ locale_route('about') }}" target="_blank"
                            class="text-decoration-none">{{  __('common.About') }}</a> |
                        <a href="{{ locale_route('contact') }}" target="_blank"
                            class="text-decoration-none">{{  __('common.Contact') }}</a> |
                        <a href="{{ locale_route('agents') }}" target="_blank"
                            class="text-decoration-none">{{  __('common.Agents') }}</a>
                        |
                        <a href="{{ locale_route('privacy.policy') }}" target="_blank"
                            class="text-decoration-none">{{  __('common.Privacy Policy') }}</a> |
                        <a href="{{ locale_route('terms.and.conditions') }}" target="_blank"
                            class="text-decoration-none">{{  __('common.Terms of Use') }}</a>
                    </p>
                </div>
            </div>

        </div>

    </footer>

    <!-- Footer -->


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



    <!-- MODAL CALL -->
    @include('coreweb::modals.signin')<!-- LOCALISATION DONE -->
    @include('coreweb::modals.signup')
    @include('coreweb::modals.changePassword', ['loc' => 'forgot']) <!-- LOCALISATION DONE -->
    @include('coreweb::modals.otp') <!-- LOCALISATION DONE -->
    @include('coreweb::modals.resetPassword') <!-- LOCALISATION DONE -->
    <!-- MODAL CALL -->

    <!-- SUBSCRIPTION FAILED MODAL -->
    <div class="modal fade" id="subscriptionFaildedModal" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bold" id="exampleModalLabel">{{ __('common.Subscription') }}</h3>
                </div>
                <div class="modal-body text-center">
                    <h5 class="text-danger text-center errorMessage"></h5>
                    <a class="btn btn-success" href="#" role="button">{{ __('common.Renew') }}</a>
                    <button type="button" class="btn btn-danger"
                        data-bs-dismiss="modal">{{ __('common.Close') }}</button>
                </div>
                <div class="modal-footer">
                    <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> -->
                </div>
            </div>
        </div>
    </div>
    <!-- SUBSCRIPTION FAILED MODAL -->

    @section('scripts')

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="{{asset('assets/toast/js/jquery.toast.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>

    <!-- jQuery Cookie CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

    <!-- jQuery Cookie CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>

        var base_url = "{{ env('BASE_URL') }}";
        var api_url = "{{ env('API_URL') }}";
        const duration = "{{ env('COUNTDOWN') }}";
        var base_url = "{{ env('BASE_URL') }}";
        var x_api_key_secret = "{{ env('ENCRYPTED_API_SECRET_KEY') }}";



        validateInputMessage = @json(__('common.Only numeric values, dots, and commas are allowed'));
        unexpectedErrorMessage = @json(__('common.An unexpected error occurred. Please try again later'));
        tryAgainMessage = @json(__('common.Please try again in'));
        otpExpiredMessage = @json(__('otp.otp_expired'));


    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
        </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>


    @show

    <script src="{{asset('assets/js/changePassword.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>
    <script src="{{asset('assets/js/common.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>
    <script src="{{asset('assets/js/login.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>
    <script src="{{asset('assets/js/validation.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>


    <!-- Country Dropdown -->
    <script src="{{asset('assets/js/countryDropdown.js')}}?v={{ env('APP_VERSION') }}"></script>
    
    <script>
        $(document).ready(function () {

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

            var myModal = new bootstrap.Modal(document.getElementById('signin'));
            myModal._element.addEventListener('shown.bs.modal', function () {
                document.getElementById('signin_mobile').focus();
            });

            myModal._element.addEventListener('hidden.bs.modal', function () {
                resetLoginForm();
            });

            var myModal = new bootstrap.Modal(document.getElementById('signup'));
            myModal._element.addEventListener('shown.bs.modal', function () {
                document.getElementById('name').focus();
            });

            myModal._element.addEventListener('hidden.bs.modal', function () {
                resetForm();
            });

            var myModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
            myModal._element.addEventListener('shown.bs.modal', function () {
                document.getElementById('phone_number').focus();
            });


            var myModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
            myModal._element.addEventListener('shown.bs.modal', function () {
                document.getElementById('newPassword').focus();
            });


            // ------------------------------------------------------- SIGN UP ------------------------------------------------------
            var registerUserMobileNumber = '';

            function registerNewUser(otp) {

                var formData = new FormData();
                formData.append('mobile', registerUserMobileNumber);
                formData.append('otp', otp);

                $.ajax({
                    url: base_url + '/register/verify-otp',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function (xhr) {
                        $(".overlay").show();
                    },
                    success: function (response) {
                        // Handle the success response

                        if (response) {
                            resetForm();
                            $(".overlay").hide();
                            localStorage.setItem('token', response.data.token);
                            window.location.href = base_url_with_country_lang + 'sell';
                            console.log(response);
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var errors = JSON.parse(xhr.responseText);
                        console.error(error);

                        $('.overlay').hide();
                        var response = xhr.responseJSON;

                        if (xhr.status === 429 && response) {
                            $.toast({
                                heading: 'Error',
                                text: response.message + (response.data.retry_after ? ` Please try again in ${response.data.retry_after}.` : ''),
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                        } else if (xhr.status === 410) {
                            $.toast({
                                heading: 'Error',
                                text: 'OTP has expired. Please request a new OTP.',
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                            $('.resend-otp').removeClass('hidden');
                            // $("#type").val('send-otp');
                        }
                        else if (xhr.status === 400) {
                            $.toast({
                                heading: 'Error',
                                text: response.message,
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                        }

                        else {
                            $.toast({
                                heading: 'Error',
                                text: xhr.responseJSON.message || 'An unexpected error occurred. Please try again later.',
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                        }

                    }
                });
            }

            // console.log('base_url',base_url);
            $('#signupForm').submit(function (e) {

                e.preventDefault();

                var formData = new FormData(this);
                formData.append('shop_type', 'grocery');

                $.ajax({
                    // url: base_url + '/register', // Replace with your actual endpoint
                    // url: api_url + 'register-validation',
                    url: base_url + '/register/send-otp',
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
                        // Handle the success response

                        if (response.status == 1) {
                            // this.submit();
                            // $('#signupForm').get(0).submit();

                            registerUserMobileNumber = $('#mobile').val();

                            $("#otpModalType").val("register");

                            $("#signup").modal('hide');
                            $("#otpModal").modal('show');

                        }

                        $(".overlay").hide();

                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        // console.error('register-error',error);

                        // Handle errors and show error messages
                        handleErrors(error);

                    }
                });

            });

            function resetForm() {
                // Reset form fields
                $("#name, #business_name, #email, #mobile, #password, #password_confirmation, #address, #shop_type, #gstin, #logo, #terms_n_conditions")
                    .removeClass('border border-2 border-danger');
                $(".name-error, .business_name-error, .email-error, .mobile-error, .password-error, .password_confirmation-error, .address-error, .shop_type-error, gstin-error, .logo-error, .terms_n_conditions-error")
                    .addClass('d-none');
            }

            function handleErrors(error) {

                console.error('register-error', error.data.errors);

                // Use switch case for displaying error messages
                resetForm();
                $.each(error.data.errors, function (key, value) {
                    switch (key) {
                        case 'name':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;
                        case 'business_name':
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
                            $("." + key + "-error").text(value[0]);
                            break;

                        case 'terms_n_conditions':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;

                        default:
                            console.warn('Unhandled error key:', key);
                            break;
                    }
                });
            }

            // When a file is selected
            $('#logo').change(function () {

                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        // Set the source of the preview image to the data URL
                        $('#preview-image').attr('src', e.target.result);

                        // Once the image is loaded, set its size
                        $('#preview-image').on('load', function () {
                            $(this).css({
                                'max-width': '200px',
                                'max-height': '100px'
                            });
                        });

                    };
                    // Read the file as a data URL
                    reader.readAsDataURL(file);

                    $(".clear-button-div").removeClass("d-none");
                    $(".preview-image-div").removeClass("d-none");
                }
            });

            // Function to clear the preview image and reset file input
            function clearPreview() {
                $('#preview-image').attr('src', ''); // Clear the image source
                $('#logo').val(''); // Reset the file input

                $(".clear-button-div").addClass("d-none");
                $(".preview-image-div").addClass("d-none");
            }

            // Clear button click event handler
            $('#clear-button').click(function () {
                clearPreview();
            });

            // ------------------------------------------------------- SIGN UP ------------------------------------------------------


            // ------------------------------------------------------- SIGN IN ------------------------------------------------------
            $('#signinForm').submit(function (e) {

                e.preventDefault();

                var formData = new FormData();
                formData.append('loginFrom', 2);
                formData.append('mobile', $('#signin_mobile').val());
                formData.append('password', $('#signin_password').val());
                formData.append('country_code', $('#signinForm .countryCode').val());

                var signin_mobile = $('#signin_mobile').val();
                var signin_password = $('#signin_password').val();
                // var signin_country_code = $('#signinForm .countryCode').val();

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

                                resetLoginForm();

                                $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                                $(".signin_mobile-error").removeClass('d-none');
                                $(".signin_mobile-error").text(response.message);
                            }
                            else if (response.status == "success") {

                                if (rememberMe) {
                                    $.cookie('signin_mobile', signin_mobile, {
                                        expires: 365
                                    });
                                    $.cookie('signin_password', signin_password, {
                                        expires: 365
                                    });
                                    // $.cookie('signin_country_code', signin_country_code, {
                                    //     expires: 365
                                    // });

                                } else {
                                    $.removeCookie('signin_mobile');
                                    $.removeCookie('signin_password');
                                    // $.removeCookie('signin_country_code');
                                }

                                $('#signinForm').get(0).submit();
                            }
                        }

                        $(".overlay").hide();

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

                console.log('login error', error.code);

                if (error.errors && (error.code == 400) && (error.status == 'failed')) {
                    $.each(error.errors, function (key, value) {
                        switch (key) {
                            case 'mobile':

                                // $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                                // $("#signin_mobile").addClass('border border-2 border-danger');
                                // $(".signin_mobile-error").removeClass('d-none');
                                // $(".signin_mobile-error").text(value[0]);

                                $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                                $("#signin_password").addClass('border border-2 border-danger');
                                $(".signin_password-error").removeClass('d-none');
                                $(".signin_password-error").text(value[0]);


                                break;
                            case 'password':

                                // $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                                // $("#signin_password").addClass('border border-2 border-danger');
                                // $(".signin_password-error").removeClass('d-none');
                                // $(".signin_password-error").text(value[0]);


                                $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                                $("#signin_password").addClass('border border-2 border-danger');
                                $(".signin_password-error").removeClass('d-none');
                                $(".signin_password-error").text(value[0]);

                                break;
                            case 'country_code':

                                // $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                                // $("#signin_password").addClass('border border-2 border-danger');
                                // $(".signin_password-error").removeClass('d-none');
                                // $(".signin_password-error").text(value[0]);

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
                } else if (error.status == 'failed') {
                    // $("#signin_password").addClass('border border-2 border-danger');
                    // $(".signin_password-error").removeClass('d-none');
                    // $(".signin_password-error").text(error.message);

                    $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                    $("#signin_password").addClass('border border-2 border-danger');
                    $(".signin_password-error").removeClass('d-none');
                    $(".signin_password-error").text(error.message);

                }
                else if (error.status == 'country-code') {
                    // $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                    // $(".signin_mobile-error").removeClass('d-none');
                    // $(".signin_mobile-error").text(error.message);

                    $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                    $(".signin_mobile-error").removeClass('d-none');
                    $(".signin_mobile-error").text(error.message);

                }
                else if (error.status == 'subscription-failed') {

                    // $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                    // $("#signin_password").addClass('border border-2 border-danger');
                    // $(".signin_password-error").removeClass('d-none');
                    // $(".signin_password-error").text(error.message);

                    $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                    $("#signin_password").addClass('border border-2 border-danger');
                    $(".signin_password-error").removeClass('d-none');
                    $(".signin_password-error").text(error.message);

                }
                else if (error.status == 'user-deactivate') {
                    // $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                    // $("#signin_password").addClass('border border-2 border-danger');
                    // $(".signin_password-error").removeClass('d-none');
                    // $(".signin_password-error").text(error.message);

                    $("#signinForm .custom-input-group").addClass('border border-2 border-danger');
                    $("#signin_password").addClass('border border-2 border-danger');
                    $(".signin_password-error").removeClass('d-none');
                    $(".signin_password-error").text(error.message);
                }
            }
            // ------------------------------------------------------- SIGN IN ------------------------------------------------------


            // // ------------------------------------------------------- FORGOT PASSWORD ------------------------------------------------------
            // $(document).on('click', '#forgotPasswordButton', function (event) {
            //     $("#signin").modal('hide');
            //     $("#changePasswordModal").modal('show');
            // });
            // // ------------------------------------------------------- FORGOT PASSWORD ------------------------------------------------------

            // // -------------------------------------------------------GENERATE OTP  ------------------------------------------------------
            // $('.generateOtpForm').click(function (e) {
            //     e.preventDefault(); // Prevent default form submission

            //     $("#otpModalType").val("forgot_password");

            //     generateOrVerifyOTP("send-otp", "forgot", "forgot_password", "changePasswordModal");
            // });
            // // -------------------------------------------------------GENERATE OTP  ------------------------------------------------------


            // // ------------------------------------------------------- VERIFY OTP -------------------------------------------------------
            // $('.otp-box').on('input', function (e) {
            //     e.preventDefault();
            //     const otpInputs = $('.otp-box');
            //     let otp = '';
            //     otpInputs.each(function (index) {
            //         const value = $(this).val();
            //         otp += value;
            //     });

            //     var otpLength = otp.length;
            //     if (otpLength === 6) {

            //         if ($("#otpModalType").val() == "forgot_password") {
            //             generateOrVerifyOTP("verify-otp", "forgot", "", "otpModal");
            //         }
            //         else if ($("#otpModalType").val() == "register") {
            //             registerNewUser(otp);
            //         }
            //     }
            // });
            // // ------------------------------------------------------- VERIFY OTP ------------------------------------------------------


            // // ------------------------------------------------------- RESEND OTP ------------------------------------------------------
            // $('#resend-otp-btn').on('click', function (e) {
            //     e.preventDefault();

            //     const phoneNumber = $('input[name="phone_number"]').val();
            //     reSendOTP("forgot_password", phoneNumber);
            // });
            // // ------------------------------------------------------- RESEND OTP ------------------------------------------------------


            // // ------------------------------------------------------- ERROR CLOSE BUTTON ------------------------------------------------------
            // $(document).on('click', '.close-error-btn', function () {
            //     $(this).closest('.error-div').addClass('d-none');
            // });
            // // ------------------------------------------------------- ERROR CLOSE BUTTON ------------------------------------------------------


            // ------------------------------------------------------- COUNTRY CODE ------------------------------------------------------
            getCountryCode().then(response => {


                console.log('getCountryCode', response);

                let countryDropdown = $(".countryCode");


                if (response && response.countryCode) { // Expecting countryShort (e.g., 'IN', 'US')
                    let dialCode = countryMap[response.countryCode]; // Find corresponding dial code

                    console.log('dialCode', dialCode);

                    if (dialCode) {
                        countryDropdown.val(response.countryCode).trigger('change');
                        // countryDropdown.val($.cookie('signin_country_code')).trigger('change');

                        let selectedDialCode = $(".countryCode option:selected").attr("data-dial_code");
                        console.log("Selected Dial Code:", selectedDialCode);


                    } else {
                        console.warn("No matching country found for:", response.countryCode);
                    }
                } else {
                    console.warn("Country short code not found in response");
                }


                response.all_languages.forEach(lang => {
                    $(".languageSelect").append(`<option value="${lang.short_code}">${lang.language}</option>`);
                });

                let setLanguage = localStorage.getItem('language');

                if (setLanguage) {
                    $(".languageSelect").val(setLanguage);
                }
                else {
                    $(".languageSelect").val(response.national_language);
                }




            }).catch(error => {
                console.error("Failed to fetch country code:", error);
            });
            // ------------------------------------------------------- COUNTRY CODE ------------------------------------------------------


            // ------------------------------------------------------- ON CHANGE LANGUAGE CODE ------------------------------------------------------
            $(document).on('change', '.languageSelect', function () {
                // var selectedLanguage = $(this).val();

                // // Get the current URL and split it
                // var currentUrl = window.location.pathname;
                // var urlParts = currentUrl.split('/');

                // localStorage.setItem('language', selectedLanguage);

                // // Replace the language segment (assuming it's the second segment, e.g., 'en')
                // if (urlParts[2]) {
                //     urlParts[2] = selectedLanguage; // Update language code
                //     var newUrl = base_url + urlParts.join('/');
                //     window.location.href = newUrl; // Redirect to updated URL
                // }


                var selectedLanguage = $(this).val(); // Get the selected language code
                var formData = new FormData();
                formData.append('lang', selectedLanguage);

                $.ajax({
                    url: api_url + 'switch-language',
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

                            // Get the current URL and split it
                            var currentUrl = window.location.pathname;
                            var urlParts = currentUrl.split('/');

                            localStorage.setItem('language', selectedLanguage);

                            // Replace the language segment (assuming it's the second segment, e.g., 'en')
                            if (urlParts[2]) {
                                urlParts[2] = selectedLanguage; // Update language code
                                var newUrl = base_url + urlParts.join('/');
                                window.location.href = newUrl; // Redirect to updated URL
                            } else {
                                // Fallback if URL structure is unexpected
                                window.location.href = base_url + 'profile';
                            }

                            console.log('units', response);
                            $(".overlay").hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        $(".overlay").hide();
                        var errorResponse = JSON.parse(xhr.responseText);
                        console.error(errorResponse);
                    }
                });


            });
            // ------------------------------------------------------- ON CHANGE LANGUAGE CODE ------------------------------------------------------



        });
    </script>

</body>

</html>