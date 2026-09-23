
// // Automatically focus on the next OTP box
// $('.otp-box').on('input', function (e) {
//     const currentBox = $(this);
//     const nextBox = $(`#otp-${parseInt(currentBox.data('index')) + 1}`);
//     if (currentBox.val().length === 1 && nextBox.length) {
//         nextBox.focus();
//     }
// });

// // Restrict to numeric input only
// $('.otp-box').on('keypress', function (e) {
//     if (isNaN(String.fromCharCode(e.which))) {
//         e.preventDefault();
//     }
// });


// // Handle backspace to move to the previous OTP box
// $('.otp-box').on('keydown', function (e) {
//     if (e.key === 'Backspace') {

//         $(this).val('');
//         const prevBox = $(`#otp-${parseInt($(this).data('index')) - 1}`);
//         if (prevBox.length) {
//             prevBox.focus();
//         }
//     }
// });


// const firstOtpBox = document.getElementById('otp-1');
// if (firstOtpBox) {
//     firstOtpBox.focus(); // Auto-focus the first OTP input box
// }


// // X-API-KEY-SECRET
// var x_api_key_secret = "eyJpdiI6IklobHR5dk80RC96cktsOGN3elV4cHc9PSIsInZhbHVlIjoibi9PV2pGMmxlT083bUJmYVVXY3ZldjZSMnViUW1TV2tiSVpNN3U0SXR3YkFac2FweUlrZUNRNFp0QUs5bUJaWSIsIm1hYyI6IjE3OWVjNTYzNGI5NTQ3MDUyM2I5MWM5NWJjNGQ3M2RjOTUzMjg4MTFhNDcxYjYxZjg2MWRjZDBjYmYyNjYyNGQiLCJ0YWciOiIifQ==";
// // X-API-KEY-SECRET


// Automatically focus on the next OTP box
$('.otp-box').on('input', function () {
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

// Handle backspace: delete current value and move focus to previous box
$('.otp-box').on('keydown', function (e) {
    if (e.key === 'Backspace') {
        e.preventDefault(); // Prevent default backspace behavior

        const currentBox = $(this);
        const prevBox = $(`#otp-${parseInt(currentBox.data('index')) - 1}`);

        // Clear current box
        currentBox.val('');

        // Move focus to the previous box if it exists
        if (prevBox.length) {
            prevBox.focus();
        }
    }
});

// Auto-focus the first OTP input box
const firstOtpBox = document.getElementById('otp-1');
if (firstOtpBox) {
    firstOtpBox.focus();
}


// ------------------------------------------------------- UPDATE PASSWORD ------------------------------------------------------
$('#change-password-form').submit(function (e) {
    e.preventDefault(); // Prevent default form submission

    var formData = new FormData();

    // Get the phone number
    const phoneNumber = $('input[name="phone_number"]').val(); // Adjust if you have a specific ID for the phone input

    formData.append('mobile', phoneNumber);
    formData.append('password', $("#newPassword").val());
    formData.append('password_confirmation', $("#confirmNewPassword").val());

    // Make the AJAX request for both OTP generation and verification
    $.ajax({
        url: api_url + 'update-password', // Single API for both actions
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            "X-API-Secret": '"' + x_api_key_secret + '"',
        },
        data: formData,
        contentType: false, // Important for FormData
        processData: false, // Important for FormData
        beforeSend: function () {
            $('.overlay').show();
        },
        success: function (response) {

            if (response && response.status == 1) {

                // // Handle OTP verification success
                // $.toast({
                //     heading: 'Success',
                //     text: response.message,
                //     icon: 'success',
                //     loader: true,
                //     position: 'top-right',
                //     loaderBg: '#9EC600'
                // });

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

            } else {
                // Handle error responses
                $.toast({
                    heading: 'Error',
                    // text: 'An unexpected error occurred. Please try again later.',
                    text: unexpectedErrorMessage,
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

            if (xhr.status === 400 && response && response.data) {
                var errorMessages = []; // Collect error messages

                var functionLoc = $("#functionLoc").val();
                var errorHtml = $(`#${functionLoc} .error-div `)
                errorHtml.removeClass('d-none');


                $.each(response.data.errors, function (key, value) {

                    console.log('errors', key, value);

                    // switch (key) {
                    //     case 'mobile':
                    //         $.toast({
                    //             heading: 'Error',
                    //             text: value[0], // Combine all error messages with line breaks
                    //             icon: 'error',
                    //             loader: true,
                    //             position: 'top-right',
                    //             loaderBg: '#9EC600'
                    //         });

                    //         break;
                    //     case 'password':
                    //         $.toast({
                    //             heading: 'Error',
                    //             text: value[0], // Combine all error messages with line breaks
                    //             icon: 'error',
                    //             loader: true,
                    //             position: 'top-right',
                    //             loaderBg: '#9EC600'
                    //         });
                    //         break;
                    //     default:
                    //         console.warn('Unhandled error key:', key);
                    //         return; // Skip to the next iteration
                    // }

                    // // Collect the error message for the toast
                    // errorMessages.push(value[0]); // Push the first error message (you can join multiple messages if needed)

                    // Loop through validation errors and display them
                    $.each(response.data.errors, function (field, messages) {
                        errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + messages + '</p>');
                    });



                });

                // // Show all error messages in a toast
                // $.toast({
                //     heading: 'Error',
                //     text: errorMessages.join('<br>'), // Combine all error messages with line breaks
                //     icon: 'error',
                //     loader: true,
                //     position: 'top-right',
                //     loaderBg: '#9EC600'
                // });
            }

            else if (xhr.status === 429 && response) {
                // $.toast({
                //     heading: 'Error',
                //     text: response.message + (response.data.retry_after ? ` Please try again in ${response.data.retry_after}.` : ''),
                //     icon: 'error',
                //     loader: true,
                //     position: 'top-right',
                //     loaderBg: '#9EC600'
                // });

                let message = response.message + (response.data.retry_after ? ` ${tryAgainMessage} ${response.data.retry_after}.` : '');
                errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + message + '</p>');

            }

            else if (xhr.status === 410) {
                // $.toast({
                //     heading: 'Error',
                //     text: 'OTP has expired. Please request a new OTP.',
                //     icon: 'error',
                //     loader: true,
                //     position: 'top-right',
                //     loaderBg: '#9EC600'
                // });

                errorHtml.find(".error-body").html(`<p class="my-1 text-danger"> ${otpExpiredMessage} </p>`);

                $('.resend-otp').removeClass('hidden');
                // $("#type").val('send-otp');

            }
            // else if (xhr.status === 400) {
            //     $.toast({
            //         heading: 'Error',
            //         text: response.message,
            //         icon: 'error',
            //         loader: true,
            //         position: 'top-right',
            //         loaderBg: '#9EC600'
            //     });
            // }

            else {
                // $.toast({
                //     heading: 'Error',
                //     text: xhr.responseJSON.message || 'An unexpected error occurred. Please try again later.',
                //     icon: 'error',
                //     loader: true,
                //     position: 'top-right',
                //     loaderBg: '#9EC600'
                // });

                errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + xhr.responseJSON.message || unexpectedErrorMessage + '</p>');

            }

        }
    });
});
// ------------------------------------------------------- UPDATE PASSWORD ------------------------------------------------------


// ------------------------------------------------------- TIMEER ------------------------------------------------------
// Timer Logic with jQuery
function startTimer(durationInSeconds, $display) {

    $(".resendText").removeClass("d-none");
    $(".resend-btn").addClass("text-secondary");
    $(".resend-btn").removeClass("text-success");

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
            $(".resend-btn").removeClass('d-none');
            $(".resend-btn").removeClass("text-secondary");
            $(".resend-btn").addClass("text-success");
            $(".resend-btn").prop('disabled', false);
            $(".resendText").addClass("d-none");
            // console.log('timer 00:00');
        }
    }, 1000);
}

// const duration = 600; // 10 minutes in seconds

// console.log('duration', duration);

const $display = $('#otpTimer');
// startTimer(duration, $display);
// ------------------------------------------------------- TIMEER ------------------------------------------------------


function generateOrVerifyOTP(type, location, sms_type = "", functionLoc = "") {

    console.log('generateOrVerifyOTP', type, location, sms_type, functionLoc);

    // Determine if the form is for generating or verifying the OTP
    var formType = type; // 'send-otp' or 'verify-otp'
    var formData = new FormData();

    var country_code = $(`#${functionLoc} .countryCode`).val();
    // Get the phone number
    var phoneNumber = '';
    if (location == 'profile') {
        phoneNumber = $('input[name="newMobileNumber"]').val()
        formData.append('user_id', $("#user_id").val());
        functionLoc = "changeMobileNumberModal";
    }

    else if (location == 'account-delete') {
        phoneNumber = $('#update-profile input[name="mobile"]').val()
        country_code = $(`#update-profile .countryCode`).val();
    }
    else {
        phoneNumber = $('input[name="phone_number"]').val(); // Adjust if you have a specific ID for the phone input
    }


    formData.append('mobile', phoneNumber);
    formData.append('type', formType);
    formData.append('sms_type', sms_type);

    if(type == 'send-otp'){
        formData.append('country_code', country_code);
    }

    console.log('country_code', formData);

    // console.log('formData before', formData);

    if (formType === 'verify-otp') {
        // Collect OTP values from input fields if verifying
        const otpInputs = $('.otp-box');
        let otp = '';
        otpInputs.each(function (index) {
            const value = $(this).val(); // Get value of the current input
            otp += value; // Concatenate the values to form the complete OTP
        });

        // console.log('Complete OTP:', otp); // Log the complete OTP

        // Validate inputs (optional)
        if (!phoneNumber || otp.length < 6) {
            $.toast({
                heading: 'Error',
                text: 'Please enter a valid OTP.',
                icon: 'error',
                loader: true,
                position: 'top-right',
                loaderBg: '#9EC600'
            });
            return;
        }

        // Append OTP to the form data for verification
        formData.append('otp', otp);
        // formData.append('phone_number', phoneNumber);
    }

    // console.log('generateOrVerifyOTP formData', formData);

    // Make the AJAX request for both OTP generation and verification
    $.ajax({
        url: api_url + 'generate-verify-otp', // Single API for both actions
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            "X-API-Secret": '"' + x_api_key_secret + '"',
        },
        data: formData,
        contentType: false, // Important for FormData
        processData: false, // Important for FormData
        beforeSend: function () {
            $('.overlay').show();
        },
        success: function (response) {


            if (response && response.status == 1) {

                if (formType === 'send-otp') {

                    $('.opt-section').removeClass('hidden');
                    $('.term_div').addClass('hidden');
                    // $('.term_div').removeClass('d-flex');

                    $("#type").val('verify-otp');

                    $("#span_mobile_number").text($('input[name="phone_number"]').val());

                    $('input[name="phone_number"]').prop('disabled', true);

                    // change button color & text
                    $('#getVerificationCode').css('background-color', '#DCDCD9');
                    $('#getVerificationCode .text').text('Continue');
                    // change button color & text

                    $.toast({
                        heading: 'Success',
                        text: response.message,
                        icon: 'success',
                        loader: true,
                        position: 'top-right',
                        loaderBg: '#9EC600'
                    });

                    if (location == "forgot") {
                        $('#changePasswordModal').modal('hide');
                        $('#otpModal').modal('show');
                    }
                    else if (location === "change") {
                        // window.location.href = '/' + user_selected_country + '/' + language + '/change-password/';
                        window.location.href = base_url_with_country_lang + 'change-password';
                    }
                    else if (location == "profile") {
                        $('#changeMobileNumberModal').modal('hide');
                        $('#otpModal').modal('show');
                    }

                    else if (location == 'delete-account') {
                        const $display = $('#deleteConfirmationModal #otpTimer1');
                        startTimer(duration, $display);
                    }

                    startTimer(duration, $display);

                } else if (formType === 'verify-otp') {

                    if (location == "forgot") {
                        $('#otpModal').modal('hide');
                        $("#resetPasswordModal").modal("show");
                    }
                    else if (location === "change") {

                        console.log('generateOrVerifyOTP chagne');

                        $("#changePasswordSection .error-div").addClass('d-none');
                        $("#changePasswordSection .error-div .error-body").empty();

                        $("#change-password-form").removeClass('d-none');
                        $("#otp-verify-form").addClass('d-none');
                        $(".resend-section").addClass('d-none');

                    }

                    else if (location == "profile") {
                        $('#otpModal').modal('hide');
                    }

                    // Handle OTP verification success
                    $.toast({
                        heading: 'Success',
                        text: response.message,
                        icon: 'success',
                        loader: true,
                        position: 'top-right',
                        loaderBg: '#9EC600'
                    });
                }

            } else {
                // Handle error responses
                $.toast({
                    heading: 'Error',
                    text: unexpectedErrorMessage,
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

            var errorHtml = $(`#${functionLoc} .error-div `)
            errorHtml.removeClass('d-none');


            // console.log('error outside', response);
            // console.log('error outside', response,xhr.status);


            if (xhr.status === 400 && response && response.data) {

                // var errorMessages = []; // Collect error messages

                // $.each(response.errors, function (key, value) {
                //     switch (key) {
                //         case 'phone_number':
                //             inputField = $('[name="phone_number"]');
                //             var parentDiv = inputField.closest('.d-flex.col-12');
                //             parentDiv.addClass('has-error'); // Add error class to the div with d-flex and col-12
                //             break;
                //         case 'terms':
                //             inputField = $('#terms');
                //             var parentDiv = inputField.closest('.d-flex.col-12');
                //             break;
                //         default:
                //             console.warn('Unhandled error key:', key);
                //             return; // Skip to the next iteration
                //     }

                //     // Add the red border to the input field
                //     inputField.addClass('is-invalid');

                //     // Collect the error message for the toast
                //     errorMessages.push(value[0]); // Push the first error message (you can join multiple messages if needed)
                // });

                // // // Show all error messages in a toast
                // // $.toast({
                // //     heading: 'Error',
                // //     text: errorMessages.join('<br>'), // Combine all error messages with line breaks
                // //     icon: 'error',
                // //     loader: true,
                // //     position: 'top-right',
                // //     loaderBg: '#9EC600'
                // // });


                // console.log("400 inside",response.data.errors);

                // Loop through validation errors and display them
                $.each(response.data.errors, function (field, messages) {
                    // var fieldName = $('#' + field); // Get the input field by ID
                    // fieldName.addClass('border border-2 border-danger'); // Add red border
                    // // Append error message below the field
                    // fieldName.after('<div class="error-message text-danger text-sm">' + messages.join(', ') + '</div>');

                    errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + messages + '</p>');

                });

            }

            else if (xhr.status === 429 && response) {

                console.log('error 429', response);

                // $.toast({
                //     heading: 'Error',
                //     text: response.message + (response.data.retry_after ? ` Please try again in ${response.data.retry_after}.` : ''),
                //     icon: 'error',
                //     loader: true,
                //     position: 'top-right',
                //     loaderBg: '#9EC600'
                // });
                let message = response.message + (response.data.retry_after ? ` ${otpExpiredMessage} ${response.data.retry_after}.` : '');
                errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + message + '</p>');
            }

            else if (xhr.status === 410) {
                // $.toast({
                //     heading: 'Error',
                //     text: 'OTP has expired. Please request a new OTP.',
                //     icon: 'error',
                //     loader: true,
                //     position: 'top-right',
                //     loaderBg: '#9EC600'
                // });
                errorHtml.find(".error-body").html(`<p class="my-1 text-danger"> ${tryAgainMessage} </p>`);

                $('.resend-otp').removeClass('hidden');
            }

            // else if (xhr.status === 400) {


            //     console.log('error inside 400', response,xhr.status);

            //     // $.toast({
            //     //     heading: 'Error',
            //     //     text: response.message,
            //     //     icon: 'error',
            //     //     loader: true,
            //     //     position: 'top-right',
            //     //     loaderBg: '#9EC600'
            //     // });

            //     errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + response.message + '</p>');
            // }

            else {
                // $.toast({
                //     heading: 'Error',
                //     text: xhr.responseJSON.message || 'An unexpected error occurred. Please try again later.',
                //     icon: 'error',
                //     loader: true,
                //     position: 'top-right',
                //     loaderBg: '#9EC600'
                // });
                errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + xhr.responseJSON.message || unexpectedErrorMessage + '</p>');
            }

        }
    });
}

function reSendOTP(sms_type, mobile) {

    const formData = new FormData();
    formData.append('mobile', mobile);
    formData.append('sms_type', sms_type);

    // location 
    var location = "";
    if (sms_type == "change_password") {
        location = "changePasswordSection";
    }
    else if (sms_type == "forgot_password") {
        location = "otpModal";
    }
    else if (sms_type == "change_mobile_number") {
        location = "otpModal";
    }
    else if(sms_type == 'sign_up'){
        location = "registerSection-2";
    }
    // location 

    $.ajax({
        url: api_url + 'resend-otp',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            "X-API-Secret": '"' + x_api_key_secret + '"',
        },
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function (xhr) {
            $('.overlay').show();
        },
        success: function (response) {
            console.log('response', response);
            // Check if the response indicates a successful status
            if (response && response.status == 1) {

                const otpInputs = $('.otp-box');
                otpInputs.each(function (index) {
                    $(this).val('');
                });

                // $('.resend-otp').addClass('hidden');

                $.toast({
                    heading: 'Success',
                    text: response.message,
                    icon: 'success',
                    loader: true,
                    position: 'top-right',
                    loaderBg: '#9EC600'
                });

                startTimer(duration, $display);

                // $("#resend-otp-btn").addClass('d-none');
                // $("#resend-otp-btn").prop('disabled', true);
                $(".resend-btn").prop('disabled', true);


                var errorHtml = $(`#${location} .error-div `)
                errorHtml.addClass('d-none');
                $('.overlay').hide();

            } else {
                showErrorDiv(location, error.message);
                $('.overlay').hide();
            }


        },

        error: function (xhr, status, error) {
            $('.overlay').hide();
            console.error('error', xhr.responseText);

            var response = xhr.responseJSON; // Parse the response as JSON

            // If validation errors exist, iterate through the errors
            if (response && response.errors) {
                $.each(response.errors, function (key, value) {
                    var inputField;

                    // Switch case to handle different error keys
                    switch (key) {
                        case 'phone_number':
                            // $.toast({
                            //     heading: 'Error',
                            //     text: value[0],
                            //     icon: 'error',
                            //     loader: true,
                            //     position: 'top-right',
                            //     loaderBg: '#9EC600'
                            // });
                            showErrorDiv(location, value[0]);
                            break;

                        default:
                            // console.warn('Unhandled error key:', key);
                            // $.toast({
                            //     heading: 'Error',
                            //     text: 'Unhandled error key: ' + key,
                            //     icon: 'error',
                            //     loader: true,
                            //     position: 'top-right',
                            //     loaderBg: '#9EC600'
                            // });
                            let message = 'Unhandled error key: ' + key;
                            showErrorDiv(location, message);

                            return; // Skip to the next iteration
                    }

                });
            }

            else if (xhr.status === 429 && response) {
                // $.toast({
                //     heading: 'Error',
                //     text: response.message + (response.retry_after ? ` Please try again in ${response.retry_after}.` : ''),
                //     icon: 'error',
                //     loader: true,
                //     position: 'top-right',
                //     loaderBg: '#9EC600'
                // });

                let message = response.message + (response.data.retry_after ? ` ${tryAgainMessage} ${response.data.retry_after}.` : '');
                showErrorDiv(location, message);
            }
            else if (xhr.status === 410) {
                // $.toast({
                //     heading: 'Error',
                //     text: 'OTP has expired. Please request a new OTP.',
                //     icon: 'error',
                //     loader: true,
                //     position: 'top-right',
                //     loaderBg: '#9EC600'
                // });

                showErrorDiv(location, tryAgainMessage);

                $('.resend-otp').removeClass('hidden');
                // $("#type").val('send-otp');
            }

            else {
                // If no specific validation errors, display a general error message
                // $.toast({
                //     heading: 'Error',
                //     text: 'An unexpected error occurred. Please try again later.',
                //     icon: 'error',
                //     loader: true,
                //     position: 'top-right',
                //     loaderBg: '#9EC600'
                // });
                showErrorDiv(location, unexpectedErrorMessage);
            }
        }
    });

}


function updateMobileNumber(formData) {

    $.ajax({
        url: api_url + 'update-mobile-number', // Single API for both actions
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            "X-API-Secret": '"' + x_api_key_secret + '"',
        },
        data: formData,
        contentType: false, // Important for FormData
        processData: false, // Important for FormData
        beforeSend: function () {
            $('.overlay').show();
        },
        success: function (response) {


            if (response && response.status == 1) {

                $('#otpModal').modal('hide');


                // Handle OTP verification success
                $.toast({
                    heading: 'Success',
                    text: response.message,
                    icon: 'success',
                    loader: true,
                    position: 'top-right',
                    loaderBg: '#9EC600'
                });

                window.location.href = base_url_with_country_lang + 'profile';


            } else {
                // Handle error responses
                $.toast({
                    heading: 'Error',
                    text: unexpectedErrorMessage,
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

            var functionLoc = "otpModal";
            var errorHtml = $(`#${functionLoc} .error-div `)
            errorHtml.removeClass('d-none');


            console.log('changeMobileNumberModal outside', response, xhr.status);

            if (xhr.status === 400 && response && response.data) {
                // Loop through validation errors and display them
                $.each(response.data.errors, function (field, messages) {

                    console.log('changeMobileNumberModal 400', messages);

                    errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + messages + '</p>');
                });
            }
            else if (xhr.status === 429 && response) {

                console.log('changeMobileNumberModal 429', response);

                let message = response.message + (response.data.retry_after ? ` ${tryAgainMessage} ${response.data.retry_after}.` : '');
                errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + message + '</p>');
            }

            else if (xhr.status === 410) {
                errorHtml.find(".error-body").html(`<p class="my-1 text-danger"> ${tryAgainMessage} </p>`);
            }
            else {
                errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + xhr.responseJSON.message || unexpectedErrorMessage + '</p>');
            }

        }
    });
}




$('#changePasswordModal').on('hidden.bs.modal', function () {
    $("#changePasswordModal .error-div").addClass('d-none');
    $("#changePasswordModal .error-div .error-body").empty();
    $("#changePasswordModal #phone_number").prop('disabled', false);
    // $("#changePasswordModal #phone_number").val('');
});
