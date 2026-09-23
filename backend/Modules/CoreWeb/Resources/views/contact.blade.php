@extends('coreweb::layouts.guest')
@section('title', "{{ __('common.Contact Us') }}")
@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8 mt-4">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('common.Contact Us') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-12 col-md-10 col-lg-8">
                <h1>{{ __('common.Contact Us') }}</h1>
            </div>

            <div class="col-12 col-md-10 col-lg-8 d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0">{{ __('common.Get in touch with us') }}</p>
                <p class="mb-0"><i class="bi bi-telephone-fill me-2"></i>+91 9531227130, +91 9864081806 , +91 7002871610</p>
            </div>


            <div class="col-12 col-md-10 col-lg-8 py-4 py-md-0 py-lg-0 py-xl-0">
                <div class="row">
                    <div class="col-12 mb-3">
                        <!-- Add Map -->
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3581.189836259248!2d91.68229927519711!3d26.157946277105086!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x375a5ba41ba15c79%3A0xc7110f7bf45c6a2f!2sAlegra%20Labs!5e0!3m2!1sen!2sin!4v1737445562197!5m2!1sen!2sin"
                            width="100%" height="400" style="border:0; border-radius: 10px;" allowfullscreen=""
                            loading="lazy">
                        </iframe>
                    </div>

                    <div class="col-md-12">

                        <form id="contactForm">
                            <div class="form-group mb-3">
                                <label for="full_name">{{ __('common.Full Name') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="full_name" name="full_name" class="form-control">
                                <span class="error-message text-danger" id="error_full_name"></span>
                            </div>
                            <div class="form-group mb-3">
                                <label for="contact_no">{{ __('common.Contact Number') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="contact_no" name="contact_no" class="form-control">
                                <span class="error-message text-danger" id="error_contact_no"></span>
                            </div>
                            <div class="form-group mb-3">
                                <label for="email">{{ __('common.Email') }} <span class="text-danger">*</span></label>
                                <input type="email" id="contact_person_email" name="contact_person_email"
                                    class="form-control" />
                                <span class="error-message text-danger" id="error_email"></span>
                            </div>
                            <div class="form-group mb-3">
                                <label for="message">{{ __('common.Message') }} <span class="text-danger">*</span></label>
                                <textarea id="message" name="message" class="form-control"></textarea>
                                <span class="error-message text-danger" id="error_message"></span>
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('common.Submit') }}</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @include('coreweb::modals.successMessage')

@endsection


@section('scripts')
    @parent

    <script>
        $(document).ready(function () {
            $('#contactForm').submit(function (e) {

                e.preventDefault();
                var formData = new FormData();

                var formData = new FormData();

                formData.append('full_name', $('#full_name').val());
                formData.append('contact_no', $('#contact_no').val());
                formData.append('email', $('#contact_person_email').val());
                formData.append('message', $('#message').val());

                $.ajax({
                    url: api_url + 'contact-submit',
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
                            $('#contactForm').trigger('reset');

                            $("#successMessage").text(response.message);
                            $("#successMessageModal").modal('show');

                            $(".overlay").hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        $(".overlay").hide();

                        // Clear previous errors
                        $(".error-message").text("");
                        $(".form-control").removeClass("input-error");

                        if (xhr.responseText) {
                            try {
                                let response = JSON.parse(xhr.responseText);
                                if (response.errors) {
                                    for (let field in response.errors) {
                                        // Show error message below the field

                                        console.log(field);

                                        if (field == 'email') {
                                            $(`#contact_person_email`).addClass("input-error");
                                        }

                                        $(`#error_${field}`).text(response.errors[field].join(", "));

                                        // Add red border to the input field
                                        $(`#${field}`).addClass("input-error");
                                    }
                                }
                            } catch (e) {
                                console.error("Error parsing JSON response:", e);
                            }
                        }

                        // Optionally show a generic error toast
                        $.toast({
                            heading: 'Error',
                            text: "Validation failed. Please correct the errors.",
                            icon: 'error',
                            loader: true,
                            position: 'top-right',
                            loaderBg: '#f27474'
                        });
                    }
                });

            });
        });

    </script>
@endsection