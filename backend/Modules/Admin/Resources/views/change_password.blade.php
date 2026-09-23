@extends('admin::layouts.admin')

@section('content')
<div class="pagetitle">
    <h1>Change Password</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="home">Home</a></li>
            <li class="breadcrumb-item active">Change Password</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-body pt-3">
                    <form id="change-password">
                        <input name="admin_id" type="hidden" class="form-control" id="admin_id"
                            value="{{ Auth::user()->id }}" />

                        <!-- New Password Field -->
                        <div class="row mb-3">
                            <label for="new_password" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="new_password" type="password" class="form-control" id="new_password"
                                    value="" required />
                                <span class="text-danger new_password-error d-none"></span>
                            </div>
                        </div>

                        <!-- Confirm New Password Field -->
                        <div class="row mb-3">
                            <label for="confirm_new_password" class="col-md-4 col-lg-3 col-form-label">Confirm New
                                Password</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="confirm_new_password" type="text" class="form-control"
                                    id="confirm_new_password" value="" required />
                                <span class="text-danger confirm_new_password-error d-none"></span>
                            </div>
                        </div>

                        <!-- OTP Field (Initially hidden) -->
                        <div class="row mb-3 d-none" id="otp-field">
                            <label for="otp" class="col-md-4 col-lg-3 col-form-label">Enter OTP</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="otp" type="text" class="form-control" id="otp" value="" />
                                <span class="text-danger otp-error d-none"></span>
                            </div>
                        </div>

                        <!-- Button to trigger OTP -->
                        <!-- <div class="text-left" id="otp-button" style="display:none;">
                            <button type="button" id="send-otp" class="btn btn-info">Send OTP</button>
                        </div> -->

                        <!-- Update Password Button -->
                        <div class="text-left">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function () {


            // Set up the CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // // Show OTP Field when New Password is entered
            // $('#new_password').on('input', function () {
            //     var newPassword = $(this).val();
            //     if (newPassword.length > 0) {
            //         $('#otp-field').show();  // Show OTP field
            //         $('#otp-button').show(); // Show Send OTP button
            //     } else {
            //         $('#otp-field').hide();  // Hide OTP field if New Password is empty
            //         $('#otp-button').hide(); // Hide Send OTP button
            //     }
            // });

            // OTP generation and sending
            function sendOtp() {

                var formData = new FormData();
                formData.append('admin_id', $('#admin_id').val());
                formData.append('password', $('#new_password').val());
                formData.append('password_confirmation', $('#confirm_new_password').val());
                formData.append('otp', $('#otp').val());
                formData.append('email', '{{ Auth::user()->email }}');

                $.ajax({
                    url: "{{ route('admin.send.otp') }}", // Create this route
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".overlay").show();
                    },
                    success: function (response) {
                        if (response.success) {
                            alert('OTP sent to your email!');

                            $("#otp-field").removeClass("d-none");
                            $('#otp').focus();
                        } else {
                            alert('Error sending OTP!');
                            $("#otp-field").addClass("d-none");
                        }
                        $(".overlay").hide();
                    },
                    error: function (xhr) {
                        $(".overlay").hide();
                        let errors = xhr.responseJSON.errors;
                        for (let field in errors) {
                            $(`#${field}`).addClass('is-invalid');
                            $(`.${field}-error`).removeClass('d-none').text(errors[field][0]);
                        }
                    }
                });
            }

            // Handle form submission for updating the password
            $('#change-password').submit(function (e) {
                e.preventDefault();

                handleValidation();

                // Check if the OTP field is visible and not empty
                var otp = $('#otp').val();
                if ($('#otp-field').is(':visible') && (!otp || otp.length === 0)) {
                    alert("Please enter the OTP.");
                    $('#otp').focus();  // Focus OTP field if it's visible
                    return;
                    updatePassword();
                }

                if ($('#otp-field').is(':visible')) {
                    updatePassword();
                }
                else {
                    sendOtp();
                }

            });


            function updatePassword() {

                var formData = new FormData();
                formData.append('admin_id', $('#admin_id').val());
                formData.append('password', $('#new_password').val());
                formData.append('password_confirmation', $('#confirm_new_password').val());
                formData.append('otp', $('#otp').val());

                $.ajax({
                    url: "{{ route('admin.update.password') }}", // Create this route
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".overlay").show();
                    },
                    success: function (response) {
                        if (response.success) {
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                            location.reload();
                            $(".overlay").hide();
                        } else {
                            $(".overlay").hide();
                            alert(response.message || 'An error occurred!');
                        }
                    },
                    error: function (xhr) {
                        $(".overlay").hide();
                        let errors = xhr.responseJSON.data;

                        console.log('errors',xhr);

                        for (let field in errors) {
                            console.log('field',field);

                            $(`#${field}`).addClass('is-invalid');
                            $(`.${field}-error`).removeClass('d-none').text(errors[field][0]);
                        }
                    }
                });
            }

            function handleValidation() {

                $('.form-control').removeClass('is-invalid');
                $('.text-danger').addClass('d-none').text('');

                let newPassword = $('#new_password').val();
                let confirmPassword = $('#confirm_new_password').val();
                let otp = $('#otp').val();
                let formValid = true;

                // Validate New Password
                if (!newPassword || newPassword.length < 6) {
                    $('#new_password').addClass('is-invalid');
                    $('.new_password-error').removeClass('d-none').text('Password must be at least 6 characters.');
                    formValid = false;
                }

                // Validate Confirm Password
                if (confirmPassword !== newPassword) {
                    $('#confirm_new_password').addClass('is-invalid');
                    $('.confirm_new_password-error').removeClass('d-none').text('Passwords do not match.');
                    formValid = false;
                }

                // Validate OTP if visible
                if ($('#otp-field').is(':visible') && (!otp || otp.length === 0)) {
                    $('#otp').addClass('is-invalid');
                    $('.otp-error').removeClass('d-none').text('Please enter the OTP.');
                    formValid = false;
                }

                if (!formValid) return;

            }

        });
    </script>
@endsection