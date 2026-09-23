let toastShown = false; // flag to control toast display

// ---------------------------------------------------------------------------------------- MOBILE NUMBER VALDATION ----------------------------------------------------------------------------------------

$('.phone_number_check').on('input', function () {
    let mobile = $(this).val();

    // Allow only numbers
    mobile = mobile.replace(/\D/g, '');

    // Restrict to maximum 11 digits
    if (mobile.length > 12) {
        mobile = mobile.slice(0, 12);
    }

    $(this).val(mobile);

});

// ---------------------------------------------------------------------------------------- MOBILE NUMBER VALDATION ----------------------------------------------------------------------------------------


// ---------------------------------------------------------------------------------------- PHOTO VALDATION ----------------------------------------------------------------------------------------
function validateImageFile(input, errorDiv = ".logo-error", actionButton="") {

    const $error = $(errorDiv); // jQuery object
    // const $submitBtn = $('.profileUpdateButton'); // submit button
    const $submitBtn = $(actionButton);

    $error.text('').addClass('d-none'); // clear error
    $submitBtn.prop('disabled', false); // enable by default

    const file = input.files && input.files[0];
    if (!file) return true;

    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/heic'];
    const maxSizeKB = 5120; // 5 MB

    // ❌ Invalid file type
    if (!allowedTypes.includes(file.type)) {
        $error.removeClass('d-none').text("Invalid file type! Please upload jpeg, png, gif, jpg or heic.");
        input.value = '';
        $submitBtn.prop('disabled', true); // disable submit
        return false;
    }

    // ❌ File too large
    if (file.size / 1024 > maxSizeKB) {
        $error.removeClass('d-none').text("File too large! Maximum size allowed is 5 MB.");
        input.value = '';
        $submitBtn.prop('disabled', true); // disable submit
        return false;
    }

    // ✅ Valid file
    $submitBtn.prop('disabled', false);
    return true;
}



// ---------------------------------------------------------------------------------------- PHOTO VALDATION ----------------------------------------------------------------------------------------



// ---------------------------------------------------------------------------------------- STOCK VALIDATION ----------------------------------------------------------------------------------------
// MAX STOCK INPUT => 100,000

function validateNumericInput(value) {
    value = value.trim();

    if (value === "") {
        return { status: false, message: "Value is required." };
    }

    // ✅ Allow integers or decimals
    const numericPattern = /^\d+(\.\d+)?$/;

    if (!numericPattern.test(value)) {
        return { status: false, message: "Please enter a valid numeric or decimal value." };
    }

    const numValue = parseFloat(value);

    // ✅ Max allowed value: 100,000
    if (numValue > 100000) {
        return { status: false, message: "Value cannot exceed 100,000." };
    }

    return { status: true, message: "" };
}



// ---------------------------------------------------------------------------------------- STOCK VALIDATION ----------------------------------------------------------------------------------------


// ---------------------------------------------------------------------------------------- NUMERIC FIELD ----------------------------------------------------------------------------------------
// Function to validate based on decimal separator
function validateInput(charCode, decimalSeparator, inputValue) {

    // Allow numbers, comma (44), and dot (46)
    if (charCode != 44 && charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
        showSingleToast(validateInputMessage);
        return false;
    }

    // For ',' as decimal separator
    if (decimalSeparator === ',') {
        // Comma is allowed as decimal separator, but only one comma
        if (charCode == 44) {
            if (inputValue.indexOf(',') !== -1) return false; // Prevent more than one comma
        }
        // Dot is allowed only as a thousand separator before the comma
        else if (charCode == 46) {
            // Dot should appear before the comma and only once
            if (inputValue.indexOf(',') !== -1 || inputValue.indexOf('.') !== -1) return false; // Prevent dot after comma or multiple dots
        }
    }
    // For '.' as decimal separator
    else if (decimalSeparator === '.') {
        // Dot is allowed, but only one dot
        if (charCode == 46) {
            if (inputValue.indexOf('.') !== -1) return false;
        }
        // Comma is allowed for thousands separator but not for decimals
        else if (charCode == 44) {
            // Comma can be inputted, but it must be before the decimal point
            if (inputValue.indexOf('.') !== -1 || inputValue.indexOf(',') !== -1) {
                return false;
            }
        }
    }

    return true;
}

// Helper function to show toast only once
function showSingleToast(message) {
    if (toastShown) return; // prevent showing again

    toastShown = true; // set flag
    $.toast({
        heading: 'Error',
        text: message,
        icon: 'error',
        loader: true,
        position: 'top-right',
        loaderBg: '#9EC600',
        hideAfter: 2000 // toast hides after 2 seconds
    });

    // reset flag after toast duration
    setTimeout(() => {
        toastShown = false;
    }, 2000);
}
// ---------------------------------------------------------------------------------------- NUMERIC FIELD ----------------------------------------------------------------------------------------
