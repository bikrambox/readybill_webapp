@extends('admin::layouts.admin')
@section('content')

    <div class="pagetitle">
        <h1>Shop Details</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home">Home</a></li>
                <li class="breadcrumb-item active">Shop Details</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section profile">
        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body pt-3">

                        <div class="row">
                            <div class="col-12">
                                <form id="update-profile">

                                    <div class="text-danger mb-3 employeeNote d-none">Note: If any updatation needed, kindly
                                        contact
                                        you shop owner</div>

                                    <input name="module_type" type="hidden" class="form-control" id="module_type" value="{{ $module_type }}" readonly />

                                    <input name="user_id" type="hidden" class="form-control" id="user_id" value=""
                                        readonly />

                                    <input name="isActive" type="hidden" class="form-control" id="isActive" value=""
                                        readonly />

                                    <input name="shop_id" type="hidden" class="form-control" id="shop_id" value=""
                                        readonly />

                                    <input name="isAdmin" type="hidden" class="form-control" id="isAdmin" value=""
                                        readonly />

                                    <div class="row mb-3">
                                        <label for="entity_id" class="col-md-4 col-lg-3 col-form-label">Shop Entity
                                            ID</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="entity_id" type="text" class="form-control" id="entity_id" value=""
                                                readonly style="background-color:#EEEEEE" />
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Name</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="name" type="text" class="form-control" id="name" value="" readonly
                                                style="background-color:#EEEEEE" />
                                            <span class="text-danger name-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Actual Business
                                            Name</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="business_name" type="text" class="form-control" id="business_name"
                                                value="" readonly style="background-color:#EEEEEE" />
                                            <span class="text-danger business_name-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="company" class="col-md-4 col-lg-3 col-form-label">Email</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="email" type="email" class="form-control" id="email" value="" />
                                            <span class="text-danger email-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="company" class="col-md-4 col-lg-3 col-form-label">Mobile Number</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="mobile" type="text" class="form-control" id="mobile" value="" />
                                            <span class="text-danger mobile-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="about" class="col-md-4 col-lg-3 col-form-label">Address</label>
                                        <div class="col-md-8 col-lg-9">
                                            <textarea name="address" class="form-control" id="address"
                                                style="height: 100px;background-color:#EEEEEE" readonly></textarea>
                                            <span class="text-danger address-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="Job" class="col-md-4 col-lg-3 col-form-label">Shop Type</label>
                                        <div class="col-md-8 col-lg-9">
                                            <select id="shop_type" name="shop_type" class="form-select" disabled>
                                                <option disabled selected value="">Choose Type</option>
                                                <option value="grocery">Grocery</option>
                                                <option value="pharamcy">Pharmacy</option>
                                            </select>
                                            <span class="text-danger shop_type-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="Job" class="col-md-4 col-lg-3 col-form-label">GSTIN Number</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="gstin" type="text" class="form-control" id="gstin" value=""
                                                readonly style="background-color:#EEEEEE" />
                                            <span class="text-danger shop_type-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3 logoSection">
                                        <label for="Job" class="col-md-4 col-lg-3 col-form-label">Logo</label>
                                        <div class="col-md-8 col-lg-9">
                                            <div class="position-relative d-inline-block">

                                                <img src="" class="img-fluid mb-2 text-center" id="userLogo" alt="Logo">

                                                <button type="button" class="btn btn-link position-absolute d-none"
                                                    id="clear-button"
                                                    style="top: 0px; right: -60px; z-index: 1; font-size: 12px; color: red; background: rgba(255, 255, 255, 0.7); border-radius: 4px;">
                                                    Delete
                                                </button>
                                            </div>

                                            <span class="text-danger logo-error d-none userLogoMsg"
                                                style="padding-bottom:2px !important;">
                                                No logo has been uploaded
                                            </span>

                                            <input name="logo" type="file" class="form-control" id="logo" value="" readonly
                                                style="background-color:#EEEEEE" />
                                            <input type="hidden" name="isLogoDelete" id="isLogoDelete" value="0" readonly />
                                            <span class="text-danger logo-error d-none"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3 apiKeySection">
                                        <label for="Job" class="col-md-4 col-lg-3 col-form-label">API Key</label>
                                        <div class="col-md-8 col-lg-9">
                                            <textarea name="api_key" id="api_key" class="form-control" id="address"
                                                style="height: 150px;background-color:#EEEEEE" readonly></textarea>

                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4 text-left">
                                            <button type="submit" class="btn btn-primary profileUpdateButton">Update
                                                Changes</button>
                                        </div>

                                        <!-- <div class="col-md-6 text-center">
                                            <label for="">Ugrade Plan</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <select class="form-select" aria-label="Default select example">
                                                    <option selected>Open this select menu</option>
                                                    <option value="1">One</option>
                                                    <option value="2">Two</option>
                                                    <option value="3">Three</option>
                                                </select>
                                                <button type="button" class="btn btn-info">Upgrade</button>
                                            </div>
                                        </div> -->


                                        <div class="col-md-8 text-end">
                                            <!-- <button type="button"
                                                class="btn btn-success activateShopButton">Activate</button>
                                            <button type="button"
                                                class="btn btn-warning deactivateShopButton">Deactivate</button> -->
                                            <button type="button" class="btn activeOrDeactiveButton">Button</button>
                                        </div>

                                    </div>
                                </form>
                            </div>


                            <div class="col-12 my-3">
                                <h5>Subscribed Plan Details - </h5>
                                <table
                                    class="table table-responsive-md table-responsive-lg table-responsive-xl table-bordered border-secondary"
                                    id="subscriptionList">
                                    <thead>
                                        <tr>
                                            <th>Subscription Plan</th>
                                            <th>Payment Status</th>
                                            <th>Payment Mode</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>


                            <div class="col-12">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex flex-column">
                                        <label for="" class="fw-bold">Upgrade Plan</label>
                                        <select class="form-select" aria-label="" name="subscription_id"
                                            id="subscription_id">
                                            <option selected>Subscription Plan</option>
                                        </select>
                                    </div>

                                    <div class="d-flex flex-column">
                                        <label for="" class="fw-bold">Payment</label>
                                        <select class="form-select" aria-label="" name="payment_mode" id="payment_mode">
                                            <option selected>Select Payment</option>
                                            <option value="cash">Cash</option>
                                            <option value="online">Online</option>
                                        </select>
                                    </div>

                                    <div class="mt-4">
                                        <button type="button"
                                            class="btn btn-info upgradeSubscriptionButton">Upgrade</button>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection

@include('coreweb::modals.confirmation', [
    'heading' => 'Are you sure you want to do the action ?',
    'subHeading' => '',
    'buttonText' => 'Confirm'
]);

@section('scripts')
    @parent
    <!-- Include parent scripts -->
    <script>
        $(document).ready(function () {

            // Set up the CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            let module_type = $("#module_type").val();

            getShopByShopId({{$user_id}});

            function getShopByShopId(user_id) {
                $.ajax({
                    type: 'GET',
                    url: `/${admin_url}/shop-data/${module_type}/${user_id}`,
                    contentType: false,
                    processData: false,

                    beforeSend: function () {
                        $('.overlay').show();
                    },
                    success: function (response) {

                        // console.log('user-details', response)
                        // console.log('user-details', response.data.shop_type)

                        $("#user_id").val(response.data.user_id);
                        $("#isActive").val(response.data.active);
                        $("#shop_id").val(response.data.details.shop_id);

                        $("#name").val(response.data.details.name);
                        $("#business_name").val(response.data.details.business_name);
                        $("#email").val(response.data.details.email);
                        $("#mobile").val(response.data.mobile);
                        $("#phone_number").val(response.data.mobile);
                        $("#address").val(response.data.details.address);
                        $("#shop_type").val(response.data.shop_type);
                        $("#gstin").val(response.data.details.gstin);
                        $("#entity_id").val(response.entity_id);

                        $("#api_key").val(response.data.api_key);

                        $(".profileLogo").attr("src", response.logo);
                        $("#userLogo").attr("src", response.logo);


                        if (response.isLogo == 1) {
                            $("#clear-button").removeClass("d-none");
                        }
                        else {
                            $("#clear-button").addClass("d-none");
                        }


                        $('#userLogo').on('load', function () {
                            $(this).css({
                                'max-width': '200px',
                                'max-height': '100px'
                            });
                        });

                        // console.log('user-details', response);

                        if (response.data.active == 1) {
                            $(".activeOrDeactiveButton").text("Deactivate");

                            $(".activeOrDeactiveButton").removeClass("btn-success");
                            $(".activeOrDeactiveButton").addClass("btn-danger");
                        }
                        else if (response.data.active == 0) {
                            $(".activeOrDeactiveButton").text("Activate");

                            $(".activeOrDeactiveButton").addClass("btn-success");
                            $(".activeOrDeactiveButton").removeClass("btn-danger");
                        }

                        subscriptionPlans(response.shop_subscription.subscription_id);

                        if(response.shop_subscription.payment_mode){
                            $('#payment_mode').val(response.shop_subscription.payment_mode);
                        }

                        // ALL SUBSCRIPTION DATA
                        console.log('all_shop_subscription', response.all_shop_subscription);

                        let allShopSubscription = response.all_shop_subscription;

                        if (allShopSubscription.length > 0) {

                            for (var i = 0; i < allShopSubscription.length; i++) {

                                $('#subscriptionList').append(
                                    '<tr>' +

                                    '<td>' + allShopSubscription[i].plan_name + '</td>' +

                                    '<td>' + (allShopSubscription[i].payment_status == null ? 'NA' : allShopSubscription[i].payment_status) + '</td>' +
                                    '<td>' + (allShopSubscription[i].payment_mode == null ? 'NA' : allShopSubscription[i].payment_mode) + '</td>' +

                                    '<td>' + ('0' + new Date(allShopSubscription[i].start_date).getDate()).slice(-2) + '-' + ('0' + (new Date(allShopSubscription[i].start_date).getMonth() + 1)).slice(-2) + '-' + new Date(allShopSubscription[i].start_date).getFullYear() + '</td>' +
                                    '<td>' + ('0' + new Date(allShopSubscription[i].end_date).getDate()).slice(-2) + '-' + ('0' + (new Date(allShopSubscription[i].end_date).getMonth() + 1)).slice(-2) + '-' + new Date(allShopSubscription[i].end_date).getFullYear() + '</td>' +

                                    '</tr>'
                                );

                            }
                        }

                        // ALL SUBSCRIPTION DATA

                        $('.overlay').hide();
                    },
                    error: function (error) {
                        console.log('Error', error);
                    }
                });
            }


            // ----------------------------------- UPDATE USER PROFILE --------------------------------------
            $('#update-profile').submit(function (e) {

                e.preventDefault();

                var formData = new FormData();

                formData.append('user_id', $('#user_id').val());
                formData.append('shop_id', $('#shop_id').val());
                formData.append('email', $('#email').val());
                formData.append('mobile', $('#mobile').val());

                // formData.append('name', $('#name').val());
                // formData.append('address', $('#address').val());
                // formData.append('shop_type', $('#shop_type').val());
                // formData.append('gstin', $('#gstin').val());
                // formData.append('isLogoDelete', $('#isLogoDelete').val());
                // if ($('#logo')[0].files.length > 0) {
                //     formData.append('logo', $('#logo')[0].files[0]);
                // }
                // formData.append('password', $('#password').val());
                // formData.append('isAdmin', $('#isAdmin').val());


                // if ($("#isAdmin").val() == 0) {

                //     formData.append('isPhotoDelete', $('#isPhotoDelete').val());
                //     if ($('#photo')[0].files.length > 0) {
                //         formData.append('photo', $('#photo')[0].files[0]);
                //     }
                // }

                // console.log('formData', formData);

                $.ajax({
                    url: "{{route('admin.shop.data.update')}}",
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
                            $(".overlay").hide();
                            // window.location.href = base_url + '/profile';
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);
                        handleUserProfileDataErrors(error);
                    }
                });
            });


            function resetUserPofileForm() {
                // Reset form fields
                $("#name, #username, #email, #mobile, #password, #password_confirmation, #address, #shop_type, #gstin, #logo")
                    .removeClass('border border-2 border-danger');
                $(".name-error, .email-error, .mobile-error, .password-error, .password_confirmation-error, .address-error, .shop_type-error, gstin-error, .logo-error")
                    .addClass('d-none');
            }

            function handleUserProfileDataErrors(error) {
                // Use switch case for displaying error messages
                resetUserPofileForm();

                $.each(error.data, function (key, value) {
                    switch (key) {
                        case 'name':
                            $("#" + key).addClass('border border-2 border-danger');
                            $("." + key + "-error").removeClass('d-none');
                            $("." + key + "-error").text(value[0]);
                            break;
                        case 'username':
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
                        $('#userLogo').attr('src', e.target.result);

                        // Once the image is loaded, set its size
                        $('#userLogo').on('load', function () {
                            $(this).css({
                                'max-width': '200px',
                                'max-height': '100px'
                            });
                        });

                    };
                    // Read the file as a data URL
                    reader.readAsDataURL(file);

                    // $(".clear-button-div").removeClass("d-none");
                    $("#clear-button").removeClass("d-none");
                    $(".userLogo-div").removeClass("d-none");

                    $("#isLogoDelete").val(0);
                }


            });

            // Function to clear the preview image and reset file input
            function clearPreview() {
                $('#userLogo').attr('src', ''); // Clear the image source
                $('#logo').val(''); // Reset the file input

                // $(".clear-button-div").addClass("d-none");
                $("#clear-button").addClass("d-none");
                $(".userLogo-div").addClass("d-none");


                $('#userLogo').attr('src', base_url + '/assets/img/user.jpg');

                $("#isLogoDelete").val(1);

            }

            // Clear button click event handler
            $('#clear-button').click(function () {
                clearPreview();
            });
            // ----------------------------------- UPDATE USER PROFILE --------------------------------------


            // ----------------------------------- ACTIVATE SHOP ACCOUNT --------------------------------------


            // When the confirm modal button is clicked
            $('.confrimModalButton').click(function (e) {
                // Retrieve the data-type value
                // const type = $(this).data('type');

                // console.log(type); // Log the value

                const type = $("#isActive").val();

                console.log('confrimModalButton', type);

                // Perform actions based on the data-type value
                if (type == 1) {
                    deactivateShopAccount();
                } else if (type == 0) {
                    activateShopAccount();
                }
            });


            // // When the activate shop button is clicked
            // $('.activateShopButton').click(function (e) {
            //     // Set the data-type attribute using data() instead of attr()
            //     $('.confrimModalButton').data('type', 1);

            //     // Show the modal
            //     $("#confirmationModal").modal('show');
            // });


            function activateShopAccount() {

                var formData = new FormData();

                formData.append('user_id', $('#user_id').val());

                $.ajax({
                    url: "{{route('admin.activate.shop')}}",
                    // headers: {
                    //     'Authorization': 'Bearer ' + localStorage.getItem('token')
                    // },
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".overlay").show();
                    },
                    success: function (response) {

                        if (response) {
                            // console.log('item', response);
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                            $(".overlay").hide();
                            $("#confirmationModal").modal('hide');
                            window.location.href = `/${admin_url}/shop-list`;
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                    }
                });
            }
            // ----------------------------------- ACTIVATE SHOP ACCOUNT --------------------------------------

            // ----------------------------------- DEACTIVATE SHOP ACCOUNT --------------------------------------

            // $('.deactivateShopButton').click(function (e) {
            //     // $("#confrimModalButton").data('type', 0);
            //     $('.confrimModalButton').data('type', 0);
            //     $("#confirmationModal").modal('show');

            // });

            function deactivateShopAccount() {

                var formData = new FormData();

                formData.append('user_id', $('#user_id').val());

                $.ajax({
                    url: "{{route('admin.deactivate.shop')}}",
                    // headers: {
                    //     'Authorization': 'Bearer ' + localStorage.getItem('token')
                    // },
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".overlay").show();
                    },
                    success: function (response) {

                        if (response) {
                            // console.log('item', response);
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                            $(".overlay").hide();
                            $("#confirmationModal").modal('hide');
                            window.location.href = `/${admin_url}/shop-list`;
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();


                    }
                });
            }
            // ----------------------------------- DEACTIVATE SHOP ACCOUNT --------------------------------------

            $('.activeOrDeactiveButton').click(function (e) {
                // $('.confrimModalButton').data('type', 1);
                $("#confirmationModal").modal('show');
            });


            // ----------------------------------- UPGRADE SUBSCRIPTION --------------------------------------
            function subscriptionPlans(subscription_id) {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('admin.all.subscription.plan') }}",
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".overlay").show();
                    },
                    success: function (response) {
                        if (response) {
                            console.log('subscriptionPlans', response);
                            // Clear existing plans
                            $("#subscription_id").empty();

                            // Add a default option
                            $("#subscription_id").append('<option value="" selected disabled>Choose a subscription plan</option>');

                            // Track if the subscription ID is found
                            let planFound = false;

                            // Loop through the response and append each plan
                            response.data.forEach(function (plan) {
                                $("#subscription_id").append('<option value="' + plan.subscription_id + '">' + plan.plan_name + '</option>');
                                if (plan.subscription_id == subscription_id) {
                                    planFound = true;
                                }
                            });

                            // Set the select value to subscription_id if found, else keep the default
                            if (planFound) {
                                $("#subscription_id").val(subscription_id);
                            } else {
                                $("#subscription_id").val(""); // Keeps the default option selected
                            }

                            $(".overlay").hide();
                        }
                    },
                    error: function (xhr) {
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error('Error:', error);
                    }
                });
            }


            // function upgradeSubscription() {
            //     var formData = new FormData();
            //     formData.append('shop_id', $('#shop_id').val());
            //     formData.append('subscription_id', $('#subscription_id').val());
            //     formData.append('payment_mode', $('#payment_mode').val());

            //     $.ajax({
            //         url: "{{route('admin.upgrade.subscription')}}",
            //         type: 'POST',
            //         data: formData,
            //         contentType: false,
            //         processData: false,
            //         beforeSend: function () {
            //             $(".overlay").show();
            //         },
            //         success: function (response) {

            //             if (response) {
            //                 // console.log('item', response);
            //                 $.toast({
            //                     heading: 'Success',
            //                     text: response.message,
            //                     icon: 'success',
            //                     loader: true,
            //                     position: 'top-right',
            //                     loaderBg: '#9EC600'
            //                 });
            //                 $(".overlay").hide();
            //                 $("#confirmationModal").modal('hide');
            //                 // window.location.href = '/admin/shop-list';
            //             }
            //         },
            //         error: function (xhr, status, error) {
            //             // Handle the error response
            //             $(".overlay").hide();
            //         }
            //     });
            // }

            function upgradeSubscription() {
                var formData = new FormData();
                formData.append('user_id', $('#user_id').val());
                formData.append('subscription_id', $('#subscription_id').val());
                formData.append('payment_mode', $('#payment_mode').val());
                // formData.append('module_type', $('#module_type').val());

                $.ajax({
                    url: "{{route('admin.upgrade.subscription')}}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(".overlay").show();
                        // Remove any previous error styles/messages
                        $('input, select').removeClass('border-red-500');
                        $('.error-message').remove();
                    },
                    success: function (response) {

                        console.log('allShopSubscription', response);


                        if (response.status == "success") {


                            let allShopSubscription = response.data;

                            console.log('allShopSubscription', allShopSubscription, response);

                            $('#subscriptionList tbody').empty();

                            if (allShopSubscription.length > 0) {

                                for (var i = 0; i < allShopSubscription.length; i++) {

                                    $('#subscriptionList').append(
                                        '<tr>' +

                                        '<td>' + allShopSubscription[i].plan_name + '</td>' +

                                        '<td>' + (allShopSubscription[i].payment_status == null ? 'NA' : allShopSubscription[i].payment_status) + '</td>' +
                                        '<td>' + (allShopSubscription[i].payment_mode == null ? 'NA' : allShopSubscription[i].payment_mode) + '</td>' +

                                        '<td>' + ('0' + new Date(allShopSubscription[i].start_date).getDate()).slice(-2) + '-' + ('0' + (new Date(allShopSubscription[i].start_date).getMonth() + 1)).slice(-2) + '-' + new Date(allShopSubscription[i].start_date).getFullYear() + '</td>' +
                                        '<td>' + ('0' + new Date(allShopSubscription[i].end_date).getDate()).slice(-2) + '-' + ('0' + (new Date(allShopSubscription[i].end_date).getMonth() + 1)).slice(-2) + '-' + new Date(allShopSubscription[i].end_date).getFullYear() + '</td>' +

                                        '</tr>'
                                    );

                                }
                            }


                            // $.toast({
                            //     heading: 'Success',
                            //     text: response.message,
                            //     icon: 'success',
                            //     loader: true,
                            //     position: 'top-right',
                            //     loaderBg: '#9EC600'
                            // });

                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });


                            $(".overlay").hide();
                            $("#confirmationModal").modal('hide');
                            // window.location.href = '/admin/shop-list';
                        } else {
                            // Handle the case where the response indicates a failure
                            $(".overlay").hide();
                            // Optionally show a toast for failure
                            $.toast({
                                heading: 'Error',
                                text: response.message || 'Something went wrong.',
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#FF0000'
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        $(".overlay").hide();
                        var response = xhr.responseJSON;

                        console.log('error', response.data);

                        // Clear any previous error styles and messages
                        $('input, select').removeClass('border border-2 border-danger'); // Remove red borders
                        $('.error-message').remove(); // Remove previous error messages

                        if (response && response.data) {
                            // Loop through validation errors and display them
                            $.each(response.data, function (field, messages) {
                                var fieldName = $('#' + field); // Get the input field by ID
                                fieldName.addClass('border border-2 border-danger'); // Add red border
                                // Append error message below the field
                                fieldName.after('<div class="error-message text-danger text-sm">' + messages.join(', ') + '</div>');
                            });
                        }

                        else if (xhr.status === 429 && response) {
                            $.toast({
                                heading: 'Error',
                                text: response.message,
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                        }

                        else if (xhr.status === 404 && response) {
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
                            // Generic error handling
                            $.toast({
                                heading: 'Error',
                                text: 'An unexpected error occurred.',
                                icon: 'error',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#FF0000'
                            });
                        }
                    }
                });
            }


            $('.upgradeSubscriptionButton').click(function (e) {
                upgradeSubscription();
            });

            // ----------------------------------- UPGRADE SUBSCRIPTION --------------------------------------




        });
    </script>

@endsection