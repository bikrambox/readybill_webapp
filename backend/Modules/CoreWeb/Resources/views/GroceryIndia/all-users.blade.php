@extends('coreweb::layouts.groceryIndia')
@section('title', "{{ __('employee_page.All Employees') }}")
@section('content')
    <div class="pagetitle">
        <h1>{{ __('employee_page.All Employees') }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home">{{ __('common.Home') }}</a></li>
                <li class="breadcrumb-item active">{{ __('employee_page.All Employees') }}</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">

            <div class="col-xl-8">
               @include('coreweb::components/error', [
                    'heading' => __('common.Subscription Alert'),
                    'modalIdAttribute' => 'subscripitonErrorId',
                ])
            </div>
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <div class="row mt-4">
                            <!-- <div class="col-12 totalUsersRecords mb-3 text-center fs-2 text-info"></div> -->
                            @if(Auth::user()->isAdmin == 1)
                                <div class="col-12 mb-4 d-flex justify-content-end">
                                    <a class="btn btn-info" href="users" role="button">{{ __('employee_page.Add Employee') }}</a>
                                </div>
                            @endif

                            <div class="col-md-4 col-sm-12 my-2 d-flex justify-content-md-start justify-content-center">
                                <label class="pt-1" for="">Show</label>
                                <select id="users_entity-select" class="form-select mx-2" style="width:30%">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <label class="pt-1 totalUsersRecords" for="">{{ __('common.Entries') }}</label>
                            </div>

                            <div class="col-md-4">
                            </div>

                            <div class="col-md-4 col-12 my-2 d-flex users_justify-content-md-end justify-content-center">

                                <select id="users_filter-select" class="mx-2">
                                    <option value="name">{{ __('common.Name') }}</option>
                                    <option value="email">{{ __('common.Email') }}</option>
                                    <option value="mobile">{{ __('common.Contact Number') }}</option>
                                </select>
                                <input type="text" id="users_dataTable_search" class="form-control"
                                    placeholder="Search..." />
                            </div>


                            <div class="col-12 table-responsive">
                                <!-- Table with stripped rows -->
                                <table class="table table-responsive-md table-responsive-lg table-responsive-xl"
                                    id="userDataList">
                                    <thead>
                                        <tr>
                                            <!-- <th>ID</th> -->
                                            <th>{{ __('employee_page.User Name') }}</th>
                                            <th>{{ __('common.Email') }}</th>
                                            <th>{{ __('common.Contact Number') }}</th>
                                            <!-- <th>Address</th> -->
                                            <th>{{ __('common.Photo') }}</th>
                                            <th>{{ __('common.Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Table body content will be populated dynamically -->
                                    </tbody>
                                </table>
                                <!-- End Table with stripped rows -->
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    @include("coreweb::modals/employee")
    <!-- EDIT ITEM -->
@endsection

@include('coreweb::modals.confirmation', [
    'heading' => __('employee_page.Are you sure you want to do the action ?'),
    'subHeading' => __('employee_page.Once deleted, this employee will be permanently deleted.'),
    'buttonText' => __('employee_page.Click Confirm to proceed')
]);

@section('scripts')
    @parent
    <!-- Include parent scripts -->
    <script>
        $(document).ready(function () {
            // ----------------------------------- ALL SUB USER --------------------------------------
            var userDataTable;

            function userDataList() {

                userDataTable = $('#userDataList').DataTable({
                    processing: true,
                    serverSide: true,
                    // responsive: true,
                    ajax: {
                        url: grocery_india_api_url + 'all-sub-users',
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('token')
                        },
                        data: function (d) {
                            // Include the selected option value in the request
                            d.filter_option = $('#users_filter-select').val();
                        },
                        dataSrc: function (response) {
                            console.log('response.data', response.data)
                            // $(".totalUsersRecords").text("Total " + response.recordsFiltered +
                            //     " records found");
                            $(".totalUsersRecords").html("rows of total <strong>" + response.recordsFiltered +
                                " records</strong>");
                            // originalData = response.data;
                            return response.data;
                        },
                        error: function (error) {
                            // Handle error
                        }
                    },
                    columns: [
                        {
                            data: 'name'
                        },
                        {
                            data: 'email'
                        },
                        {
                            data: 'mobile'
                        },
                        // {
                        //     data: 'address'
                        // },
                        {
                            data: 'photo',
                            title: 'Photo',
                            render: function (data, type, row) {
                                if (data && data != 'NA') {
                                    return `<img src="${media_url}/photo/${data}" alt="Photo" class="img-thumbnail" style="max-width: 50px;">`;
                                } else {
                                    // return 'No Photo';
                                    return `<img src="/assets/img/user.jpg" alt="Photo" class="img-thumbnail" style="max-width: 50px;">`;
                                }
                            }
                        },
                        {
                            data: null,
                            render: function (data, type, row) {
                                let buttons = '<div class="flex items-center justify-center">';

                                // buttons += `<a class="btn btn-danger mb-2" href="#" role="button">Delete</a>`;
                                buttons += `<button id="deleteSubUserModal" type="button" class="btn btn-danger fw-bold text-uppercase" data-id="${data.staff_id}" >Delete</button>`;
                                buttons += '</div>';

                                return buttons;
                            }
                        }
                    ],
                    pagingType: 'full_numbers',
                    order: [
                        [0, 'asc']
                    ],
                    // lengthMenu: [10, 25, 50, 100], // Define available page lengths
                    pageLength: 100, // Set the default page length to 100
                    // searching: false,

                    language: {
                        "emptyTable": "Currently there are no Users"
                    },

                    // rowCallback: function (row, data) {
                    //     $(row).on('click', function () {
                    //         // Show modal or perform any action you want here
                    //         // For example, to show a modal with item details:
                    //         if (isAdmin == 1) {
                    //             showUserByID(data.staff_id);
                    //         }
                    //     });
                    // },
                    rowCallback: function (row, data, index) {
                        // Check if the current row is the last row
                        var totalRows = $('#userDataList').DataTable().rows().count();

                        // Select all <td> elements except the last one
                        var allTdsExceptLast = $(row).find('td').not(':last');

                        // Disable pointer-events for the last <td> (the whole last cell, which includes the button)
                        $(row).find('td').last().css('pointer-events', 'none');

                        // Enable pointer-events for all other <td> elements
                        allTdsExceptLast.css('pointer-events', 'auto');

                        // Bind click event for all cells except the last one
                        allTdsExceptLast.on('click', function () {
                            // Perform action here for the clickable td cells
                            if (isAdmin == 1) {
                                showUserByID(data.staff_id);
                            }
                        });

                        // Make the button inside the last <td> clickable
                        $(row).find('td').last().find('button').css('pointer-events', 'auto').on('click', function (event) {
                            event.stopPropagation(); // Prevent click event from bubbling up to the td
                            // Perform the button-specific action here
                            // For example, handle the button click action
                            console.log("Button clicked for staff ID:", data.staff_id);
                            // Your custom logic for button click

                            $("#confirmationModal").modal('show');

                            $(".confrimModalButton").data("id", data.staff_id);


                        });

                        if (isAdmin === 0) {
                            // Hide the last column for the current row
                            $(row).find('td:last-child').hide();

                            // Hide the last column header
                            $('#userDataList thead th:last-child').hide();
                            $('#userDataList tfoot th:last-child').hide(); // If there's a footer, hide it too

                            allTdsExceptLast.css('pointer-events', 'none');
                        }
                    }

                });


                $("#users_entity-select").val(100);
                // Hide the default search bar
                $('.dataTables_filter').hide();
                $('.dataTables_length').hide();

                // // Add event listeners for filtering
                $('#users_filter-select').on('change', function () {
                    $("#users_dataTable_search").val("");
                    var searchValue = $("#users_dataTable_search").val();
                    userDataTable.search(searchValue).draw();
                });

                $("#users_dataTable_search").on('keyup', function () {
                    var searchValue = $(this).val();
                    userDataTable.search(searchValue).draw();
                    // var column = $('#filter-select').val();
                    // var value = $(this).val();
                    // table.column(column).search(value).draw();
                });

                // Add event listener for entity filter
                $('#users_entity-select').on('change', function () {
                    userDataTable.page.len($(this).val()).draw();
                });

            }

            userDataList();

            // Handle DataTables processing event
            userDataTable.on('preXhr.dt', function () {
                $(".overlay").show();
            });

            // Handle DataTables processed event
            userDataTable.on('xhr.dt', function () {
                $(".overlay").hide();
            });


            function showUserByID(staff_id) {
                
                $.ajax({
                    type: 'GET',
                    url: grocery_india_api_url + 'sub-users/' + staff_id,
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    contentType: false,
                    processData: false,

                    beforeSend: function () {
                        $('.overlay').show();
                    },
                    success: function (response) {

                        console.log('showUserByID', response);

                        if (response) {
                            $("#staff_id").val(response.staff.staff_id);
                            $("#sub_user_name").val(response.staff.name);
                            $("#sub_user_email").val(response.staff.email);
                            $("#sub_user_mobile").val(response.staff.user.mobile);


                            let countryDropdown = $("#sub-user-profile-update .countryCode");
                            if(response.staff.user.country_details){
                                countryDropdown.val(response.staff.user.country_details.code).trigger('change');
                            }
                            else{
                                countryDropdown.val(user_selected_country).trigger('change');
                            }

                            $("#sub_user_address").val(response.staff.address);
                            $('.overlay').hide();
                            $("#editUserModal").modal('show');

                            if (response.staff.photo != '' && response.staff.photo != 'NA') {

                                console.log('showUserByID');
                                $("#userPhoto").removeClass('d-none');
                                $("#editUserModal #userPhoto").attr('src',  response.staff.photo_url);
                            }
                            else {
                                $("#userPhoto").removeClass('d-none');
                                $("#editUserModal #userPhoto").attr('src',  `/${response.staff.photo_url}`);
                            }


                            $(".passwordChangeSection").addClass("d-none");
                            $(".changePasswordButton").removeClass("d-none");

                        }
                    },
                    error: function (error) {
                        console.log('Error', error);
                    }
                });
            }

            $('#sub-user-profile-update').submit(function (e) {

                e.preventDefault();
                resetAddSubUserForm();

                var formData = new FormData();
                formData.append('staff_id', $('#staff_id').val());
                formData.append('name', $('#sub_user_name').val());
                formData.append('email', $('#sub_user_email').val());
                formData.append('mobile', $('#sub_user_mobile').val());
                formData.append('country_code', $("#editUserModal .countryCode").val());
                formData.append('address', $('#sub_user_address').val());
                formData.append('password', $('#sub_user_password').val());
                formData.append('password_confirmation', $('#sub_user_password_confirmation').val());

                formData.append('isPhotoDelete', $('#isPhotoDelete').val());
                if ($('#photo')[0].files.length > 0) {
                    formData.append('photo', $('#photo')[0].files[0]);
                }

                $.ajax({
                    url: grocery_india_api_url + 'update-sub-users',
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
                            // console.log('item', response);
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                loader: true,
                                position: 'top-right',
                                loaderBg: '#9EC600'
                            });
                            resetAddSubUserForm();
                            $(".overlay").hide();
                            $("#editUserModal").modal('hide');

                            userDataTable.destroy();
                            userDataList();
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle the error response
                        $(".overlay").hide();
                        var error = JSON.parse(xhr.responseText);
                        console.error(error);
                        resetAddSubUserForm();
                        // Handle errors and show error messages
                        handleAddSubUserErrors(error);
                    }
                });

            });

            function resetAddSubUserForm() {
                // Reset form fields
                $("#sub_user_name, #sub_user_email, #sub_user_mobile, #sub_user_address, #sub_user_password, #userPhoto")
                    .removeClass('border border-2 border-danger');
                $(".sub_user_name-error, .sub_user_email-error, .sub_user_mobile-error, .sub_user_address-error, .sub_user_password-error, .sub_user_photo-error")
                    .addClass('d-none');
            }

            function handleAddSubUserErrors(error) {
                $.each(error.data, function (key, value) {
                    switch (key) {
                        case 'name':
                            $("#sub_user_" + key).addClass('border border-2 border-danger');
                            $(".sub_user_" + key + "-error").removeClass('d-none');
                            $(".sub_user_" + key + "-error").text(value[0]);
                            break;

                        case 'email':
                            $("#sub_user_" + key).addClass('border border-2 border-danger');
                            $(".sub_user_" + key + "-error").removeClass('d-none');
                            $(".sub_user_" + key + "-error").text(value[0]);
                            break;

                        case 'mobile':
                            $("#sub_user_" + key).addClass('border border-2 border-danger');
                            $(".sub_user_" + key + "-error").removeClass('d-none');
                            $(".sub_user_" + key + "-error").text(value[0]);
                            break;

                        case 'address':
                            $("#sub_user_" + key).addClass('border border-2 border-danger');
                            $(".sub_user_" + key + "-error").removeClass('d-none');
                            $(".sub_user_" + key + "-error").text(value[0]);
                            break;

                        case 'photo':
                            $("#photo").addClass('border border-2 border-danger');
                            $(".sub_user_" + key + "-error").removeClass('d-none');
                            // $(".sub_user_" + key + "-error").text(value[0]);
                            $(".sub_user_" + key + "-error").text(Array.isArray(value) ? value.join(' ') : value);
                            break;

                        case 'password':
                            $("#sub_user_" + key).addClass('border border-2 border-danger');
                            $(".sub_user_" + key + "-error").removeClass('d-none');
                            $(".sub_user_" + key + "-error").text(value[0]);
                            break;

                        case 'password_confirmation':
                            $("#sub_user_" + key).addClass('border border-2 border-danger');
                            $(".sub_user_" + key + "-error").removeClass('d-none');
                            $(".sub_user_" + key + "-error").text(value[0]);
                            break;

                    }
                });
            }

            // ----------------------------------- ALL SUB USER --------------------------------------


            // ----------------------------------- EMPLOYEE PHOTO --------------------------------------
            // When a file is selected
            $('#photo').change(function () {
                // ✅ Call validation function first
                if (!validateImageFile(this, ".sub_user_photo-error")) return;


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


            // ----------------------------------- DELETE SUB USER --------------------------------------

            // $(document).on('click', '#deleteSubUserModal', function () {

            //     // deletSubUser($(this).data('staff_id'));
            //     $("#confirmationModal").modal('show');

            //     $(".deleteSubUser").data($(this).data('staff_id'));

            // });

            $('.confrimModalButton').click(function () {
                deleteSubUser($(this).data('id'));
            });

            function deleteSubUser(staff_id) {

                $.ajax({
                    type: 'GET',
                    url: grocery_india_api_url + 'delete-sub-user/' + staff_id,
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    contentType: false,
                    processData: false,

                    beforeSend: function () {
                        $('.overlay').show();
                    },
                    success: function (response) {
                        if (response) {

                            $(".overlay").hide();
                            $("#confirmationModal").modal('hide');

                            userDataTable.destroy();
                            userDataList();
                        }
                    },
                    error: function (error) {
                        console.log('Error', error);

                    }
                });

            }
            // ----------------------------------- DELETE SUB USER --------------------------------------

            $('#sub_user_password').on('input', function () {
                passwordValidation($(this).val(), "sub_user");
            });


            // ----------------------------------- SHOW PASSWORD SECTION --------------------------------------
            $('.upadateEmpPasswordChangeButton').click(function () {
                $(".passwordChangeSection").removeClass("d-none");
                $(".changePasswordButton").addClass("d-none");
            });
            // ----------------------------------- SHOW PASSWORD SECTION --------------------------------------



        });

    </script>
@endsection