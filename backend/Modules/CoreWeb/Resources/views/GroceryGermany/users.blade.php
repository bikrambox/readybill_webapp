@extends('coreweb::layouts.groceryGermany')
@section('title', "{{ __('employee_page.Add Employee') }}")
@section('content')
            <div class="pagetitle">
                <h1>{{ __('employee_page.Add Employee') }}</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('employee_page.Add Employee') }}</li>
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
                                <form id="add-new-user">

                                    <div class="text-danger mb-3">
                                        {{ __('common.Fields marked * are mandatory') }}
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-12 mb-4 d-flex justify-content-start">
                                            <a class="btn btn-info" href="all-users" role="button">
                                                {{ __('employee_page.View All Employees') }}
                                            </a>
                                        </div>
                                    </div>

                                    <input name="id" type="hidden" class="form-control" id="id" value="" readonly />
                                    <div class="row mb-3">
                                        <label for="fullName" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Name') }}<span
                                                class="text-danger">*</span></label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="nu_name" type="text" class="form-control" id="nu_name" value=""
                                                autofocus placeholder="{{ __('common.Name') }}" />
                                            <span class="text-danger nu_name-error d-none"></span>
                                        </div>
                                    </div>


                                    <div class="row mb-3 d-none">
                                        <label for="company" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Email') }}</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="nu_email" type="email" class="form-control" id="nu_email" value="" placeholder="{{ __('common.Email') }}" />
                                            <span class="text-danger nu_email-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3 select2-dropdown-container">
                                        <label for="company" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Contact Number') }}<span
                                                class="text-danger">*</span></label>
                                        <div class="col-md-8 col-lg-9">
                                            <div class="mb-4">
                                                <div class="input-group custom-input-group">

                                                    <div class="custom-select-wrapper">
                                                        <select class="form-select country-code-select countryCode" id="">
                                                        </select>
                                                    </div>

                                                    <input type="text" class="form-control form-control-lg" id="nu_mobile"
                                                        name="nu_mobile" placeholder="{{ __('Enter your mobile number') }}" autofocus>
                                                </div>
                                            </div>
                                            <span class="text-danger nu_mobile-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="Job" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Password') }}<span
                                                class="text-danger">*</span></label>
                                        <div class="col-md-8 col-lg-9">

                                             <div class="input-group">
                                                <input type="password" class="form-control password" id="nu_password" name="nu_password" placeholder="Password" />
                                                <span class="input-group-text position-relative togglePasswordWrapper" data-target="nu_password">
                                                    <img class="togglePassword" src="{{ asset('assets/img/icons/eye.svg') }}" alt="Toggle Password" style="width: 18px; height: 18px;">
                                                </span>
                                            </div>   

                                            <span class="text-danger nu_password-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="Job" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Confirm Password') }}<span
                                                class="text-danger">*</span></label>
                                        <div class="col-md-8 col-lg-9">
                                            <!-- <input name="nu_password_confirmation" type="password" class="form-control"
                                                id="nu_password_confirmation" value=""> -->

                                                <div class="input-group">
                                                    <input type="password" class="form-control password" id="nu_password_confirmation" name="nu_password_confirmation" placeholder="{{ __('common.Confirm Password') }}" />
                                                    <span class="input-group-text position-relative togglePasswordWrapper" data-target="nu_password_confirmation">
                                                        <img class="togglePassword" src="{{ asset('assets/img/icons/eye.svg') }}" alt="Toggle Password" style="width: 18px; height: 18px;">
                                                    </span>
                                                </div>
                                            <span class="text-danger nu_password_confirmation-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="about" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Address') }}<span
                                                class="text-danger">*</span></label>
                                        <div class="col-md-8 col-lg-9">
                                            <textarea name="nu_address" class="form-control" id="nu_address"
                                                style="height: 100px" placeholder="{{ __('register_page.Address') }}"></textarea>
                                            <span class="text-danger nu_address-error d-none"></span>
                                        </div>
                                    </div>


                                    <div class="row mb-3">
                                        <label for="Job" class="col-md-4 col-lg-3 col-form-label">{{ __('common.Photo') }}</label>
                                        <div class="col-md-8 col-lg-9">
                                            <div class="position-relative d-inline-block">
                                                <!-- Image -->
                                                <img src="" class="img-fluid mb-2 text-center d-none" id="userPhoto" alt="Logo">

                                                <!-- Delete Button -->
                                                <button type="button" class="btn btn-link position-absolute d-none"
                                                    id="photo-clear-button"
                                                    style="top: 0px; right: -60px; z-index: 1; font-size: 12px; color: red; background: rgba(255, 255, 255, 0.7); border-radius: 4px;">
                                                    {{ __('common.Delete') }}
                                                </button>
                                            </div>

                                            <!-- Error Message -->
                                            <!-- <span class="text-danger logo-error d-none userPhotoMsg"
                                                        style="padding-bottom:2px !important;">
                                                        No logo has been uploaded
                                                    </span> -->

                                            <!-- File Input -->
                                            <input name="photo" type="file" class="form-control" id="photo" value="" accept="image/*"  />
                                            <input type="hidden" name="isPhotoDelete" id="isPhotoDelete" value="0" readonly />
                                            <span class="text-danger photo-error d-none"></span>
                                        </div>
                                    </div>

                                    <!-- <div class="text-left">
                                                <button type="submit" class="btn btn-primary">Add User</button>
                                            </div> -->
                                    <div class="row mb-3">
                                        <div class="col-6 text-start">
                                            <button type="submit" class="btn btn-primary nu_createNewUser">{{ __('common.Submit') }}</button>
                                            <a class="btn btn-danger" href="users" role="button">{{ __('common.Cancel') }}</a>
                                        </div>
                                        <!-- <div class="col-6 text-end">
                                                    <a class="btn btn-danger" href="users" role="button">Cancel</a>
                                                </div> -->
                                    </div>
                                </form><!-- End Profile Edit Form -->


                            </div>
                        </div>

                    </div>
                </div>
            </section>
@endsection


@section('scripts')
    @parent
    <!-- Include parent scripts -->
    <script>
        $(document).ready(function () {
            // ----------------------------------- ADD NEW USER --------------------------------------
            $('#add-new-user').submit(function (e) {

                e.preventDefault();
                resetAddUserForm();
                var formData = new FormData();

                formData.append('name', $('#nu_name').val());
                formData.append('email', $('#nu_email').val());
                formData.append('mobile', $('#nu_mobile').val());
                formData.append('country_code', $('.countryCode').val());
                formData.append('address', $('#nu_address').val());
                formData.append('password', $('#nu_password').val());
                formData.append('password_confirmation', $('#nu_password_confirmation').val());
                formData.append('isPhotoDelete', $('#isPhotoDelete').val());
                if ($('#photo')[0].files.length > 0) {
                    formData.append('photo', $('#photo')[0].files[0]);
                }

                console.log('formData', formData);

                $.ajax({
                    url: grocery_germany_api_url + 'add-new-user',
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
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                            resetAddUserForm();
                            $(".overlay").hide();
                            window.location.href =  base_url_with_country_lang + 'all-users';
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);
                        resetAddUserForm();
                        // Handle errors and show error messages
                        handleAddUserErrors(error);
                    }
                });
            });


            function handleAddUserErrors(error) {
                // Use switch case for displaying error messages
                resetAddUserForm();
                $.each(error.data, function (key, value) {
                    switch (key) {
                        case 'name':
                            $("#nu_" + key).addClass('border border-2 border-danger');
                            $(".nu_" + key + "-error").removeClass('d-none');
                            $(".nu_" + key + "-error").text(value[0]);
                            break;

                        case 'email':
                            $("#nu_" + key).addClass('border border-2 border-danger');
                            $(".nu_" + key + "-error").removeClass('d-none');
                            $(".nu_" + key + "-error").text(value[0]);
                            break;
                        case 'mobile':
                            $("#nu_" + key).addClass('border border-2 border-danger');
                            $(".nu_" + key + "-error").removeClass('d-none');
                            $(".nu_" + key + "-error").text(value[0]);
                            break;
                        case 'password':
                            $("#nu_" + key).addClass('border border-2 border-danger');
                            $("#nu_password_confirmation").addClass('border border-2 border-danger');
                            $(".nu_" + key + "-error").removeClass('d-none');
                            $(".nu_" + key + "-error").text(value[0]);
                            break;

                        case 'photo':
                            $("#photo").addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(Array.isArray(value) ? value.join(' ') : value);
                            break;

                        case 'address':
                            $("#nu_" + key).addClass('border border-2 border-danger');
                            $(".nu_" + key + "-error").removeClass('d-none');
                            $(".nu_" + key + "-error").text(value[0]);
                            break;

                        default:
                            console.warn('Unhandled error key:', key);
                            break;
                    }
                });
            }

            function resetAddUserForm() {
                // Reset form fields
                $("#nu_name, #nu_email, #nu_mobile, #nu_password, #nu_password_confirmation, #nu_address, #photo")
                    .removeClass('border border-2 border-danger');
                $(".nu_name-error, .nu_email-error, .nu_mobile-error, .nu_password-error, .nu_password_confirmation-error ,.nu_address-error, .photo-error")
                    .addClass('d-none');
            }

            // ----------------------------------- ADD NEW USER --------------------------------------

            // ----------------------------------- EMPLOYEE PHOTO --------------------------------------
            // When a file is selected
            $('#photo').change(function () {

                // ✅ Call validation function first
                if (!validateImageFile(this, ".photo-error")) return;


                $("#userPhoto").removeClass("d-none");
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        // Set the source of the preview image to the data URL
                        $('#userPhoto').attr('src', e.target.result);

                        // Once the image is loaded, set its size
                        $('#userPhoto').on('load', function () {
                            $(this).css({
                                'max-width': '200px',
                                'max-height': '100px'
                            });
                        });

                    };
                    // Read the file as a data URL
                    reader.readAsDataURL(file);

                    // $(".clear-button-div").removeClass("d-none");
                    $("#photo-clear-button").removeClass("d-none");
                    $(".userPhoto-div").removeClass("d-none");


                    $("#isLogoDelete").val(0);


                }
            });


            // Function to clear the preview image and reset file input
            function clearPreview() {
                $(".nu_createNewUser").prop('disabled', false)
                $('#userPhoto').attr('src', ''); // Clear the image source
                $('#logo').val(''); // Reset the file input

                // $(".clear-button-div").addClass("d-none");
                $("#clear-button").addClass("d-none");
                $(".userPhoto-div").addClass("d-none");


                $('#userPhoto').attr('src', base_url + '/assets/img/user.jpg');

                $("#isPhotoDelete").val(1);
            }

            // Clear button click event handler
            $('#photo-clear-button').click(function () {
                clearPreview();
            });

            // ----------------------------------- EMPLOYEE PHOTO --------------------------------------

            $('#nu_password').on('input', function () {
                passwordValidation($(this).val(), "nu");
            });



            // // ------------------------------------------------------- COUNTRY CODE ------------------------------------------------------
            // getCountryCode().then(response => {

            //     let countryDropdown = $("#add-new-user .countryCode");

            //     if (response && response.countryCode) { // Expecting countryShort (e.g., 'IN', 'US')
            //         let dialCode = countryMap[response.countryCode]; // Find corresponding dial code

            //         console.log('dialCode', dialCode);

            //         if (dialCode) {
            //             countryDropdown.val(dialCode).trigger('change');
            //         } else {
            //             console.warn("No matching country found for:", response.countryCode);
            //         }
            //     } else {
            //         console.warn("Country short code not found in response");
            //     }
            // }).catch(error => {
            //     console.error("Failed to fetch country code:", error);
            // });
            // // ------------------------------------------------------- COUNTRY CODE ------------------------------------------------------




        });




    </script>
@endsection