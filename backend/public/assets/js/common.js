
// --------------------------------------------------------------------------- REMOVE ERROR DIV ---------------------------------------------------------------------------
function resetErrorDiv(modalLocation) {
    var errorHtml = $(`#${modalLocation} .error-div `)

    errorHtml.addClass('d-none');
    errorHtml.find(".error-body").empty();

}
// --------------------------------------------------------------------------- REMOVE ERROR DIV ---------------------------------------------------------------------------


// --------------------------------------------------------------------------- SHOW ERROR DIV ---------------------------------------------------------------------------
function showErrorDiv(modalLocation, message) {
    var errorHtml = $(`#${modalLocation} .error-div `)

    errorHtml.find(".error-body").empty();
    errorHtml.removeClass('d-none');
    errorHtml.find(".error-body").html('<p class="my-1 text-danger">' + message + '</p>');
}
// --------------------------------------------------------------------------- SHOW ERROR DIV ---------------------------------------------------------------------------


// --------------------------------------------------------------------------- PASSWORD TOGGLE ---------------------------------------------------------------------------
$('.togglePasswordWrapper').on('click', function () {
    const targetId = $(this).data('target');
    const password = $('#' + targetId);
    const wrapper = $(this);
    const type = password.attr('type') === 'password' ? 'text' : 'password';
    password.attr('type', type);
    // Toggle diagonal line using inline style on the specific wrapper
    if (type === 'text') {
        wrapper.attr('style', 'position: relative; background: #f8f9fa;');
        wrapper.append('<span class="line" style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #6c757d; transform: rotate(-45deg); transform-origin: center;"></span>');
    } else {
        wrapper.removeAttr('style');
        wrapper.find('.line').remove();
    }
}).on('mouseover', function () {
    $(this).css('cursor', 'pointer');
}).on('mouseout', function () {
    $(this).css('cursor', 'default');
});
// --------------------------------------------------------------------------- PASSWORD TOGGLE ---------------------------------------------------------------------------

