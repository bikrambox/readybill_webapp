$(document).ready(function () {

    // $("#otpModal").modal('show');

    // var base_url = "https://probill.app";  
    // var base_url = "https://dev.probill.app";  
    // var base_url = "http://127.0.0.1:8000";


    // $("#signin_mobile").addClass("d-none");

    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Include CSRF token in AJAX request headers
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var myModal = new bootstrap.Modal(document.getElementById('signin'));
    myModal._element.addEventListener('shown.bs.modal', function () {
        document.getElementById('signin_mobile').focus();
    });


    var myModal = new bootstrap.Modal(document.getElementById('signup'));
    myModal._element.addEventListener('shown.bs.modal', function () {
        document.getElementById('name').focus();
    });

    if ($.cookie('signin_mobile') && $.cookie('signin_password')) {
        $('#signin_mobile').val($.cookie('signin_mobile'));
        $('#signin_password').val($.cookie('signin_password'));
        $('#remember_me').prop('checked', true);
    }

    var registerUserMobileNumber = '';

    // console.log('base_url',base_url);
    $('#signupForm').submit(function (e) {

        e.preventDefault();

        var formData = new FormData(this);

        // var formData = new FormData();
        // formData.append('name', $('#name').val());
        // formData.append('business_name', $('#business_name').val());
        // formData.append('email', $('#email').val());
        // formData.append('mobile', $('#mobile').val());
        // formData.append('password', $('#password').val());
        // formData.append('password_confirmation', $('#password_confirmation').val());
        // formData.append('address', $('#address').val());
        // // formData.append('shop_type', $('#shop_type').val());
        // formData.append('gstin', $('#gstin').val());
        // if ($('#logo')[0].files.length > 0) {
        //     formData.append('logo', $('#logo')[0].files[0]);
        // }


        console.log('formData', formData);

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

                // if (response) {
                //     resetForm();
                //     $(".overlay").hide();
                //     localStorage.setItem('token', response.data.token);
                //     console.log(response);
                // }
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


    $('#signinForm').submit(function (e) {


        e.preventDefault();

        // var formData = new FormData(this);

        var formData = new FormData();
        formData.append('mobile', $('#signin_mobile').val());
        formData.append('password', $('#signin_password').val());
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

                if (response.status == "success") {

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

                    // this.submit();
                    $('#signinForm').get(0).submit();
                }

                // if (response) {
                //     console.log(response);
                //     resetLoginForm();
                //     $(".overlay").hide();
                //     localStorage.setItem('token', response.data.token);
                //     localStorage.setItem('token', response.token);
                //     // $.cookie('token', response.data.token, {expires: 365});

                //     if (rememberMe) {
                //         $.cookie('signin_mobile', signin_mobile, {
                //             expires: 365
                //         });
                //         $.cookie('signin_password', signin_password, {
                //             expires: 365
                //         });
                //     } else {
                //         $.removeCookie('signin_mobile');
                //         $.removeCookie('signin_password');
                //     }
                //     // if(response.token){
                //     // }
                // }
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


    $('.registration_otp-box').on('input', function (e) {
        e.preventDefault();
        const otpInputs = $('.registration_otp-box');
        let otp = '';
        otpInputs.each(function (index) {
            const value = $(this).val();
            otp += value;
        });

        var otpLength = otp.length;
        if (otpLength === 6) {
            // console.log('Registration Successfull');
            registerNewUser(otp);
        }
    });

    $('.registerVerifyOtp').click(function (e) {
        e.preventDefault();

        const otpInputs = $('.otp-box');
        let otp = '';
        otpInputs.each(function (index) {
            const value = $(this).val();
            otp += value;
        });

        registerNewUser(otp);

    });


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

    $('.registration_otp-box').on('input', function (e) {
        const currentBox = $(this);
        const nextBox = $(`#otp-${parseInt(currentBox.data('index')) + 1}`);
        if (currentBox.val().length === 1 && nextBox.length) {
            nextBox.focus();
        }
    });

    // Restrict to numeric input only
    $('.registration_otp-box').on('keypress', function (e) {
        if (isNaN(String.fromCharCode(e.which))) {
            e.preventDefault();
        }
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

    function resetLoginForm() {
        $("#signin_mobile, #signin_password")
            .removeClass('border border-2 border-danger');
        $(".signin_mobile-error, .signin_password-error")
            .addClass('d-none');
    }

    function handleLoginErrors(error) {
        resetLoginForm();

        console.log('login error', error.status);

        if (error.data) {
            $.each(error.data, function (key, value) {
                switch (key) {
                    case 'mobile':
                        $("#signin_" + key).addClass('border border-2 border-danger');
                        $(".signin_" + key + "-error").removeClass('d-none');
                        $(".signin_" + key + "-error").text(value[0]);
                        break;
                    case 'password':
                        $("#signin_" + key).addClass('border border-2 border-danger');
                        $(".signin_" + key + "-error").removeClass('d-none');
                        $(".signin_" + key + "-error").text(value[0]);
                        break;
                    default:
                        console.warn('Unhandled error key:', key);
                        break;
                }
            });
        } else if (error.status == 'failed') {
            $("#signin_password").addClass('border border-2 border-danger');
            $(".signin_password-error").removeClass('d-none');
            $(".signin_password-error").text(error.message);
        }
        else if (error.status == 'subscription-failed') {
            // $(".signin_password-error").removeClass('d-none');
            // $(".signin_password-error").text(error.message);

            $("#signin").modal('hide');
            $("#subscriptionFaildedModal").modal('show');
            $("#subscriptionFaildedModal .errorMessage").text(error.message);

        }
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


    // ------------------------------------------------------- CHANGE PASSWORD ------------------------------------------------------
    $(document).on('click', '#forgotPasswordButton', function (event) {
        $("#signin").modal('hide');
        $("#changePasswordModal").modal('show');
    });

    // ------------------------------------------------------- CHANGE PASSWORD ------------------------------------------------------

    // -------------------------------------------------------GENERATE OTP  ------------------------------------------------------
    $('.generateOtpForm').click(function (e) {
        e.preventDefault(); // Prevent default form submission
        generateOrVerifyOTP("send-otp", "forgot", "forgot_password");
    });
    // -------------------------------------------------------GENERATE OTP  ------------------------------------------------------


    // ------------------------------------------------------- VERIFY OTP -------------------------------------------------------
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
            generateOrVerifyOTP("verify-otp", "forgot");
        }
    });
    // ------------------------------------------------------- VERIFY OTP ------------------------------------------------------


    // ------------------------------------------------------- RESEND OTP ------------------------------------------------------
    $('#resend-otp-btn').on('click', function (e) {
        e.preventDefault();

        const phoneNumber = $('input[name="phone_number"]').val();
        reSendOTP("forgot_password", phoneNumber);
    });
    // ------------------------------------------------------- RESEND OTP ------------------------------------------------------


});