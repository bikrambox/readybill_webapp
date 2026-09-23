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
    <div class="position-fixed top-0 end-0 h-100 p-4" style="width: 650px;" id="registerSection-1">
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
                    <h3 class="fw-medium">{{ __('register_page.Get started with your phone number') }}</h3>
                </div>

                <!-- Error Section -->
                @include('coreweb::components/error', ['heading' => 'Error'])

                <!-- Registration Form -->
                <form id="registerOTpForm" class="flex-grow-1 d-flex flex-column">

                    <div class="mb-4 select2-dropdown-container">
                        <label for="mobile" class="form-label fw-semibold">{{  __('login_page.Mobile Number') }}</label>
                        <div class="input-group custom-input-group">

                            <!-- Country Code Dropdown -->
                            <div class="custom-select-wrapper">
                                <select class="form-select country-code-select countryCode" id="countryCode">
                                </select>
                            </div>

                            <!-- Mobile Number Input -->
                            <input type="text" class="form-control form-control-lg phone_number_check" id="mobile" name="mobile"
                                placeholder="Enter your mobile number" autofocus>
                        </div>
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
                        <a href="{{ locale_route('terms.and.conditions') }}" target="_blank" class="fw-bold text-decoration-none">
                            {{ __('login_page.Terms of Use') }}</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!--################################################ MOBILE NUMBER CARD ################################################   -->

    <!--################################################ OTP CARD ################################################   -->
    <div class="position-fixed top-0 end-0 h-100 p-4 d-none" style="width: 650px;" id="registerSection-2">
        <div class="card h-100 bg-light bg-opacity-90 shadow" style="border-radius: 25px;">
            <div class="card-body d-flex flex-column p-4 p-md-5">
                <!-- Main content centered vertically but left-aligned -->
                <div class="row flex-grow-1 align-items-center" id="">
                    <div class="col-12">
                        <img src="{{asset('assets/img/favicon/64.png')}}" alt="probill" class="mb-4" width="50">

                        <p class="fw-light mb-2">
                            {{  __('login_page.Welcome to') }}
                            <a href="/"
                                class="fw-normal text-primary text-decoration-none hover-underline">{{  __('common.Ready Bill') }}</a>
                        </p>

                        <h3 class="fw-medium mb-4">{{ __('register_page.Enter the OTP sent to your phone number') }}
                        </h3>


                        @include('coreweb::components/error', ['heading' => 'Error'])

                        <form id="verifyOTpForm">
                            <div class="mb-4">

                                <p class="d-flex align-items-center">
                                    <span class="" id="mobileText">+91 9876543210</span>
                                    <button type="button" class="btn btn-link"
                                        id="registertChangeMobileNumber">{{ __('register_page.Change') }}</button>
                                </p>

                            </div>

                            <div class="mb-4" id="otpSection">
                                <div class="d-flex gap-2">
                                    @for($i = 1; $i <= 6; $i++)
                                        <input type="text" class="form-control form-control-lg otp-box text-center border-2"
                                            maxlength="1" id="otp-{{ $i }}" data-index="{{ $i }}"
                                            style="width: 76px; height: 76px;">
                                    @endfor
                                </div>
                            </div>


                            <div class="mb-3 d-flex justify-content-end align-items-center resend-section">
                                <button type="button" class="btn btn-link text-secondary p-0 m-0 px-2 resend-btn" disabled
                                    id="resend-otp-btn">{{ __('register_page.Resend OTP') }}</button>
                                <span class="text-secondary fw-bold me-2 resendText">{{ __('register_page.In') }}</span>
                                <span class="text-secondary fw-bold resendText" id="otpTimer">01:00</span>
                            </div>

                            <div class="d-grid">
                                <button type="submit"
                                    class="btn btn-primary btn-lg">{{ __('register_page.Verify') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Footer fixed at bottom -->
                <div class="row mt-auto">
                    <div class="col-12">
                        <hr class="mb-3">
                        <p class="text-center text-muted small mb-0">
                            {{ __('login_page.By continuing, you agree to our') }} <strong><a href="{{ locale_route('privacy.policy') }}"
                                    target="_blank">{{ __('login_page.Privacy Policy') }}</a></strong>
                            {{ __('login_page.and') }} <a href="{{ locale_route('terms.and.conditions') }}"
                                target="_blank"><strong>{{ __('login_page.Terms of Use') }}</strong></a>
                        </p>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--################################################ OTP CARD ################################################   -->


    <!--################################################ PASSWORD INPUT CARD ################################################   -->
    <div class="position-fixed top-0 end-0 h-100 p-4 d-none" style="width: 650px;" id="registerSection-3">
        <div class="card h-100 bg-light bg-opacity-90 shadow" style="border-radius: 25px;">
            <div class="card-body d-flex flex-column p-4 p-md-5">
                <!-- Main content centered vertically but left-aligned -->
                <div class="row flex-grow-1 align-items-center" id="">
                    <div class="col-12">
                        <img src="{{asset('assets/img/favicon/64.png')}}" alt="probill" class="mb-4" width="50">

                        <p class="fw-light mb-2">
                            {{  __('login_page.Welcome to') }}
                            <a href="/"
                                class="fw-normal text-primary text-decoration-none hover-underline">{{  __('common.Ready Bill') }}</a>
                        </p>

                        <h3 class="fw-medium mb-4">{{ __('register_page.Create Password') }}</h3>


                        @include('coreweb::components/error', ['heading' => 'Error'])

                        <form id="createPasswordForm">
                            <div class="mb-3">
                                <!-- <input type="password" class="form-control form-control-lg" id="password"
                                    name="password" placeholder="Password" /> -->

                                <div class="input-group">
                                    <input type="password" class="form-control password form-control-lg" id="password"
                                        name="password" placeholder="Password" />
                                    <span class="input-group-text position-relative togglePasswordWrapper"
                                        data-target="password">
                                        <img class="togglePassword" src="{{ asset('assets/img/icons/eye.svg') }}"
                                            alt="Toggle Password" style="width: 18px; height: 18px;">
                                    </span>
                                </div>

                            </div>

                            <div class="mb-3">
                                <!-- <input type="password" class="form-control form-control-lg" id="password_confirmation"
                                    name="password_confirmation" placeholder="Confirm Password*" /> -->

                                <div class="input-group">
                                    <input type="password" class="form-control password form-control-lg"
                                        id="password_confirmation" name="password_confirmation"
                                        placeholder="Password" />
                                    <span class="input-group-text position-relative togglePasswordWrapper"
                                        data-target="password_confirmation">
                                        <img class="togglePassword" src="{{ asset('assets/img/icons/eye.svg') }}"
                                            alt="Toggle Password" style="width: 18px; height: 18px;">
                                    </span>
                                </div>

                            </div>

                            <div class="d-grid">
                                <button type="submit"
                                    class="btn btn-primary btn-lg">{{ __('register_page.Next') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Footer fixed at bottom -->
                <div class="row mt-auto">
                    <div class="col-12">
                        <hr class="mb-3">
                        <p class="text-center text-muted small mb-0">
                            {{ __('login_page.By continuing, you agree to our') }} <strong><a href="{{ locale_route('privacy.policy')  }}"
                                    target="_blank">{{ __('login_page.Privacy Policy') }}</a></strong>
                            {{ __('login_page.and') }} <a href="{{ locale_route('terms.and.conditions') }}"
                                target="_blank"><strong>{{ __('login_page.Terms of Use') }}</strong></a>
                        </p>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--################################################ PASSWORD INPUT CARD ################################################   -->


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


    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="{{asset('assets/toast/js/jquery.toast.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{asset('assets/js/common.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>

    <script src="{{asset('assets/js/changePassword.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>
    <script src="{{asset('assets/js/validation.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>

    <script>
        var base_url = "{{ env('BASE_URL') }}";
        var api_url = "{{ env('API_URL') }}";
        const duration = "{{ env('COUNTDOWN') }}";
        var x_api_key_secret = "{{ env('ENCRYPTED_API_SECRET_KEY') }}";

    </script>

    <!-- Country Dropdown -->
    <script src="{{asset('assets/js/countryDropdown.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>

    <script>

        $(document).ready(function () {

            var mobile_no = 0;
            const $display = $('#otpTimer');
            var detected_country_code = '';
            var detected_language = '';

            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Extract country_code and lang from URL
            let urlSegments = window.location.pathname.split('/');
            let countryCode = urlSegments[1] || 'us';
            let lang = urlSegments[2] || 'en';

            detected_country_code = urlSegments[1] || '';
            detected_language = lang;

            var base_url_with_country_lang = `${base_url}/${detected_country_code}/${detected_language}/`;


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


            // ------------------------------------------------------- OTP BOX -------------------------------------------------------
            // Automatically focus on the next OTP box
            $('.otp-box').on('input', function (e) {
                const currentBox = $(this);
                const nextBox = $(`#otp-${parseInt(currentBox.data('index')) + 1}`);
                if (currentBox.val().length === 1 && nextBox.length) {
                    nextBox.focus();
                }
            });

            // Restrict to numeric input only
            $('.otp-box').on('keypress', function (e) {
                if (isNaN(String.fromCharCode(e.which))) {
                    e.preventDefault();
                }
            });

            // const firstOtpBox = document.getElementById('otp-1');
            // if (firstOtpBox) {
            //     firstOtpBox.focus(); // Auto-focus the first OTP input box
            // }
            // ------------------------------------------------------- OTP BOX -------------------------------------------------------


            // ------------------------------------------------------- SEND OTP -------------------------------------------------------
            $('#registerOTpForm').submit(function (e) {

                e.preventDefault();

                var formData = new FormData();
                formData.append('mobile', $("#mobile").val());
                formData.append('country_code', $("#countryCode").val());

                mobile_no = $("#mobile").val();

                $.ajax({
                    url: base_url + '/register/send-otp',
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

                            console.log('registerOTpForm', response);

                            if (response.data && response.data.data.user_id != 0 && response.data.data.checkUser == 0) {
                                $("#registerSection-4").slideUp(300, function () {
                                    $(this).addClass("d-none"); // Hide after fade out
                                    $("#registerSection-4").removeClass("d-none").hide().fadeIn(300); // Show with fade in
                                    $(".overlay").hide();

                                    $("#registerSection-4 #user_id").val(response.data.data.user_id);
                                });
                            }
                            else {


                                $("#registerSection-1").slideUp(300, function () {
                                    $(this).addClass("d-none"); // Hide after fade out
                                    $("#registerSection-2").removeClass("d-none").hide().fadeIn(300); // Show with fade in
                                    $(".overlay").hide();

                                    // Using jQuery to focus on the first OTP box after a slight delay
                                    setTimeout(function () {
                                        var firstOtpBox = $('#otp-1');
                                        if (firstOtpBox.length) {
                                            firstOtpBox.focus(); // Auto-focus the first OTP input box
                                        }
                                    }, 100); // Adjust the delay as needed

                                    startTimer(duration, $display);

                                });

                                $("#mobileText").text($("#countryCode option:selected").data('dial_code') + ' ' + $("#mobile").val() + '.');

                            }

                            var functionLoc = "registerSection-1";
                            var errorHtml = $(`#${functionLoc} .error-div `)
                            errorHtml.addClass('d-none');

                        }
                        // $(".overlay").hide();
                    },
                    error: function (xhr) {

                        $('.overlay').hide();
                        var response = xhr.responseJSON;

                        var functionLoc = "registerSection-1";
                        var errorHtml = $(`#${functionLoc} .error-div `)
                        errorHtml.removeClass('d-none');

                        if (xhr.status === 400 && response && response.data) {
                            var errorMessages = []; // Collect error messages

                            $.each(response.data.errors, function (key, value) {

                                console.log('errors', key, value);
                                // Loop through validation errors and display them
                                $.each(response.data.errors, function (field, messages) {
                                    errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + messages + '</p>');
                                });
                            });

                        }

                        else if (xhr.status === 429 && response) {
                            let message = response.message +`. `+(response.data.errors.retry_after ? ` @json(__('login_page.Please try again in')) ${response.data.errors.retry_after}.` : '');
                            errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + message + '</p>');
                        }

                        else if (xhr.status === 410) {
                            errorHtml.find(".error-body").html(`<p class="my-1 text-danger"> @json(__('login_page.OTP has expired')) . @json(__('login_page.Please request a new OTP')). </p>`);
                        }
                        else {
                            errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + xhr.responseJSON.message || `@json(__('common.An unexpected error occurred')). @json(__('common.Please try again later')).` + '</p>');
                        }
                    }
                });

            });
            // ------------------------------------------------------- SEND OTP -------------------------------------------------------


            // ------------------------------------------------------- VERIFY OTP -------------------------------------------------------
            $('#verifyOTpForm').submit(function (e) {
                e.preventDefault();

                const otpInputs = $('.otp-box');
                let otp = '';
                otpInputs.each(function (index) {
                    const value = $(this).val();
                    otp += value;
                });

                var formData = new FormData();
                formData.append('mobile', $("#mobile").val());
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
                        if (response.status == 1) {


                            $("#registerSection-2").slideUp(300, function () {
                                $(this).addClass("d-none"); // Hide after fade out
                                $("#registerSection-3").removeClass("d-none").hide().fadeIn(300); // Show with fade in
                                
                                $(".overlay").hide();
                                $("#registerSection-3 #password").focus();
                            });

                            var functionLoc = "registerSection-2";
                            var errorHtml = $(`#${functionLoc} .error-div `)
                            errorHtml.addClass('d-none');

                        }
                        // $(".overlay").hide();
                    },
                    error: function (xhr) {

                        $('.overlay').hide();
                        var response = xhr.responseJSON;

                        var functionLoc = "registerSection-2";
                        var errorHtml = $(`#${functionLoc} .error-div `)
                        errorHtml.removeClass('d-none');

                        if (xhr.status === 400 && response && response.data) {
                            var errorMessages = []; // Collect error messages

                            $.each(response.data.errors, function (key, value) {

                                console.log('errors', key, value);
                                // Loop through validation errors and display them
                                $.each(response.data.errors, function (field, messages) {
                                    errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + messages + '</p>');
                                });

                            });
                        }
                        else if (xhr.status === 429 && response) {
                            let message = response.message + (response.data.retry_after ? ` @json(__('login_page.Please try again in')) ${response.data.retry_after}.` : '');
                            errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + message + '</p>');
                        }

                        else if (xhr.status === 410) {
                            errorHtml.find(".error-body").html(`<p class="my-1 text-danger"> @json(__('login_page.OTP has expired')) . @json(__('login_page.Please request a new OTP')). </p>`);
                        }
                        else {
                            errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + xhr.responseJSON.message || `@json(__('common.An unexpected error occurred')). @json(__('common.Please try again later')).` + '</p>');
                        }
                    }
                });

            });

            // ------------------------------------------------------- VERIFY OTP ------------------------------------------------------


            // ------------------------------------------------------- PASSWORD & CONFIRM NEW PASSWORD ------------------------------------------------------
            $('#createPasswordForm').submit(function (e) {

                e.preventDefault();

                var formData = new FormData();
                formData.append('mobile', $("#mobile").val());
                formData.append('password', $("#password").val());
                formData.append('password_confirmation', $("#password_confirmation").val());
                formData.append('shop_type', "grocery");
                formData.append('country_code', $("#countryCode").val());

                formData.append('detected_country_code', countryCode.toUpperCase());
                formData.append('detected_language', detected_language);

                $.ajax({
                    url: base_url + '/register/create-user',
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

                            console.log('createPasswordForm', response);


                            $("#shopDetailForm #user_id").val(response.data.user.user_id);

                            $("#registerSection-3").slideUp(300, function () {
                                $(this).addClass("d-none"); // Hide after fade out
                                $("#registerSection-4").removeClass("d-none").hide().fadeIn(300); // Show with fade in
                                $(".overlay").hide();

                                $("#registerSection-4 #name").focus();
                            });

                            var functionLoc = "registerSection-3";
                            var errorHtml = $(`#${functionLoc} .error-div `)
                            errorHtml.addClass('d-none');
                        }
                    },
                    error: function (xhr) {

                        $('.overlay').hide();
                        var response = xhr.responseJSON;

                        var functionLoc = "registerSection-3";
                        var errorHtml = $(`#${functionLoc} .error-div `)
                        errorHtml.removeClass('d-none');

                        if (xhr.status === 400 && response && response.data) {
                            var errorMessages = []; // Collect error messages

                            $.each(response.data.errors, function (key, value) {

                                console.log('errors', key, value);
                                // Loop through validation errors and display them
                                $.each(response.data.errors, function (field, messages) {
                                    errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + messages + '</p>');
                                });

                            });
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
                    }
                });
            });
            // ------------------------------------------------------- PASSWORD & CONFIRM NEW PASSWORD ------------------------------------------------------


            // ------------------------------------------------------- SHOP DETAILS FORM ------------------------------------------------------
            $('#shopDetailForm').submit(function (e) {

                e.preventDefault();

                var formData = new FormData(this);

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
                            // $("#successMessage").html(" __('register_page.successfull_registration_message').<br> __('Please login') <a href='{{ locale_route("login") }}' class='text-primary'>__('here')</a>.");
                            
                            $("#successMessage").html(
                                
                                 `@json(__('register_page.successfull_registration_message')) .<br> @json(__('login_page.Please login')) <a href='{{ locale_route('login') }}' class='text-primary'>@json(__('common.here'))</a>.`

                            );
                            
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
                            errorHtml.find(".error-body").html(`<p class="my-1 text-danger"> @json(__('login_page.OTP has expired')). @json(__('login_pagePlease request a new OTP')). </p>`);
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
            
            
            // ------------------------------------------------------- SUCCESS MODAL CLOSE BUTTON ------------------------------------------------------
            $(document).on('click', '#successMessageModal .btn-close', function () {
                // window.location.reload();
                window.location.href = base_url_with_country_lang + 'login'; // Redirect to the login page
            });
            // ------------------------------------------------------- SUCCESS MODAL CLOSE BUTTON ------------------------------------------------------


            // ------------------------------------------------------- CHANGE ------------------------------------------------------
            $(document).on('click', '#registertChangeMobileNumber', function () {
                // $("#registerSection-1").removeClass("d-none");
                // $("#registerSection-2").addClass("d-none");

                // EMPTY OTP BOXES
                const otpInputs = $('.otp-box');
                otpInputs.val('');
                // EMPTY OTP BOXES

                $("#registerSection-2").slideUp(300, function () {
                    $(this).addClass("d-none");
                    $("#registerSection-1").removeClass("d-none").hide().fadeIn(300);
                    $(".overlay").hide();
                });



            });
            // ------------------------------------------------------- CHANGE ------------------------------------------------------



            // ------------------------------------------------------- CLOSE BUTTON ------------------------------------------------------
            $(document).on('click', '.close-error-btn', function () {
                $(this).closest('.error-div').addClass('d-none');
            });
            // ------------------------------------------------------- CLOSE BUTTON ------------------------------------------------------


            // ------------------------------------------------------- TIMEER ------------------------------------------------------
            // Timer Logic with jQuery
            function startTimer(durationInSeconds, $display) {

                $(".resendText").removeClass("d-none");
                $("#resend-otp-btn").addClass("text-secondary");
                $("#resend-otp-btn").removeClass("text-success");

                let timer = durationInSeconds;
                const interval = setInterval(() => {
                    const hours = Math.floor(timer / 3600);
                    const minutes = Math.floor((timer % 3600) / 60);
                    const seconds = timer % 60;

                    $display.text(
                        // `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
                        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
                    );

                    if (--timer < 0) {
                        clearInterval(interval);
                        // $display.text("Time's up!");
                        $("#resend-otp-btn").removeClass('d-none');
                        $("#resend-otp-btn").removeClass("text-secondary");
                        $("#resend-otp-btn").addClass("text-success");
                        $("#resend-otp-btn").prop('disabled', false);
                        $(".resendText").addClass("d-none");
                        // console.log('timer 00:00');
                    }
                }, 1000);
            }
            // ------------------------------------------------------- TIMEER ------------------------------------------------------

             // ------------------------------------------------------- RESEND OTP ------------------------------------------------------
            $('#resend-otp-btn').on('click', function (e) {
                e.preventDefault();

                const phoneNumber = $('input[name="mobile"]').val();
                reSendOTP("sign_up", phoneNumber);
            });
            // ------------------------------------------------------- RESEND OTP ------------------------------------------------------


            // ------------------------------------------------------- LOGO UPLOAD ------------------------------------------------------
            // When a file is selected
            $('#logo').change(function () {

                // ✅ Call validation function first
                if (!validateImageFile(this, ".logo-error", "")) return;

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
            // ------------------------------------------------------- LOGO UPLOAD ------------------------------------------------------


            // ------------------------------------------------------- COUNTRY CODE ------------------------------------------------------
            getCountryCode().then(response => {

                let countryDropdown = $(".countryCode");

                console.log('getCountryCode', response);

                if (response && response.countryCode) { // Expecting countryShort (e.g., 'IN', 'US')
                    let dialCode = countryMap[response.countryCode]; // Find corresponding dial code

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


            // ------------------------------------------------------- UPLOAD OPTION ------------------------------------------------------
            // Update file name display when a file is selected
            document.getElementById('logo').addEventListener('change', function (event) {
                const fileName = event.target.files[0]?.name || 'No file selected';
                document.querySelector('.file-name').textContent = fileName;
            });
            // ------------------------------------------------------- UPLOAD OPTION ------------------------------------------------------
            
        });
    </script>
</body>

</html>