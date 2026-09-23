// ------------------------------------------------------- REMEMBER ME ------------------------------------------------------
if ($.cookie('signin_mobile') && $.cookie('signin_password')) {
    $('#signin_mobile').val($.cookie('signin_mobile'));
    $('#signin_password').val($.cookie('signin_password'));

    //$('#signinForm .countryCode').val($.cookie('signin_country_code')).trigger('change');
    $('#remember_me').prop('checked', true);
}
// ------------------------------------------------------- REMEMBER ME ------------------------------------------------------


// ------------------------------------------------------- FORGOT PASSWORD ------------------------------------------------------
$(document).on('click', '#forgotPasswordButton', function (event) {
    $("#signin").modal('hide');
    $("#changePasswordModal").modal('show');
});
// ------------------------------------------------------- FORGOT PASSWORD ------------------------------------------------------

// -------------------------------------------------------GENERATE OTP  ------------------------------------------------------
$('.generateOtpForm').click(function (e) {
    e.preventDefault(); // Prevent default form submission

    $("#otpModalType").val("forgot_password");

    generateOrVerifyOTP("send-otp", "forgot", "forgot_password", "changePasswordModal");
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

        if ($("#otpModalType").val() == "forgot_password") {
            generateOrVerifyOTP("verify-otp", "forgot", "", "otpModal");
        }
        else if ($("#otpModalType").val() == "register") {
            registerNewUser(otp);
        }
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


// ------------------------------------------------------- ERROR CLOSE BUTTON ------------------------------------------------------
$(document).on('click', '.close-error-btn', function () {
    $(this).closest('.error-div').addClass('d-none');
});
// ------------------------------------------------------- ERROR CLOSE BUTTON ------------------------------------------------------
