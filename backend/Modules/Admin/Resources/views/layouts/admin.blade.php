<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Ready Bill</title>
    <meta content="" name="description">
    <meta content="" name="keywords">


    <!-- <link rel="icon" type="image/png" sizes="32x32" href="{{asset('assets/img/favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('assets/img/favicon-16x16.png')}}"> -->


    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('assets/img/favicon/16.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('assets/img/favicon/32.png')}}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{asset('assets/img/favicon/64.png')}}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{asset('assets/img/favicon/512.png')}}">


    <!-- Google Fonts -->
    <!-- <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet"> -->

    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i" rel="stylesheet">


    <!-- Vendor CSS Files -->
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/quill/quill.snow.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/quill/quill.bubble.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/remixicon/remixicon.css')}}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{asset('assets/css/style.css')}}" rel="stylesheet">

    <!-- CSS Loader -->
    <link href="{{asset('assets/css/loader.css')}}" rel="stylesheet">

    <!-- Toast -->
    <link rel="stylesheet" href="{{asset('assets/toast/css/jquery.toast.css')}}">

    <!-- Datatable -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.css">
    <!-- Datatable -->

    <!-- Font Awesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Font Awesome 6.5.1 -->


    <!-- select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- select2 -->


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        * {
            font-family: 'Roboto', sans-serif !important;
        }

        .activeNav a {
            background-color: #f6f9ff !important;
        }

        .fa-trash-can {
            cursor: pointer;
            color: red;
        }

        .taxBadge {
            font-size: 14px;
            margin-right: 10px;
        }

        #sale_itemList .taxBadge i {
            cursor: pointer;
            padding-left: 10px;
            color: red;
        }

        .deleteRow i {
            color: white;
        }

        .searchItem {
            width: 100%;
        }

        table#transactionTable.dataTable tbody tr:hover>td {
            background-color: #ffa;
            cursor: pointer;
        }

        table#userDataList.dataTable tbody tr:hover>td {
            background-color: #ffa;
            cursor: pointer;
        }

        .admin-highlight:hover>td {
            background-color: #ffa;
            cursor: pointer;
        }

        .inventoryMinStockAlert>td {
            background-color: red !important;
            color: white !important;
        }

        #suggestionList {
            max-height: 170px;
            /* Set the maximum height for the suggestion list */
            overflow-y: auto;
            /* Enable vertical scrolling */
        }


        .dataTables_processing {
            display: none !important;
        }


        /* Adjust styles as needed */
        .alert-warning label {
            margin-right: 10px;
            /* Adjust spacing between icon and text */
        }

        /* To enable text wrapping */
        .col-form-label {
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .footer {
            padding-left: 15px;
            padding-right: 15px;
        }
    </style>

</head>

<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">

        <div class="d-flex align-items-center justify-content-between">
            <div>
                <a href="{{route('admin.dashboard')}}" class="logo d-flex align-items-center">
                    <img src="{{asset('assets/img/readybill.png')}}" alt="probill" />
                </a>
                <!-- <p class="logo-text">by Alegra Labs</p> -->
            </div>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->
        <div class="ms-auto mx-3  text-secondary">
            <span class="loggedInUserName"></span><br>
            <span class="lastLoggedInTime"></span>
        </div>
        <nav class="header-nav">

            <ul class="d-flex align-items-center">
                <li class="nav-item dropdown pe-3 nav-link nav-profile d-flex align-items-center pe-0">
                    <img src="{{asset('assets/img/user.jpg')}}" alt="Profile" class="rounded-circle profileLogo">
                </li>

            </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->

            </ul>
        </nav><!-- End Icons Navigation -->

    </header><!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">

        <ul class="sidebar-nav" id="sidebar-nav">


            <li class="nav-item dashboard-link {{ request()->routeIs('admin.dashboard') ? 'activeNav' : '' }}">
                <a class="nav-link collapsed" href="{{route('admin.dashboard')}}">
                    <img src="" alt="" width="22" />
                    <span class="px-2">Dashboard</span>
                </a>
            </li>

            <li class="nav-item shops-link {{ request()->routeIs('admin.shop.list') ? 'activeNav' : '' }}">
                <a class="nav-link collapsed" href="{{route('admin.shop.list')}}">
                    <img src="" alt="" width="22" />
                    <span class="px-2">Shops</span>
                </a>
            </li>

            <li class="nav-item agents-link {{ request()->routeIs('admin.agent.index') ? 'activeNav' : '' }}">
                <a class="nav-link collapsed" href="{{route('admin.agent.index')}}">
                    <img src="" alt="" width="22" />
                    <span class="px-2">Agents</span>
                </a>
            </li>

            <li class="nav-item agent-transactions-link {{ request()->routeIs('admin.agent.transactions') ? 'activeNav' : '' }}">
                <a class="nav-link collapsed" href="{{route('admin.agent.transactions')}}">
                    <img src="" alt="" width="22" />
                    <span class="px-2">Agent Transactions</span>
                </a>
            </li>

            {{--<li
                class="nav-item subscription-plans-link {{ request()->routeIs('admin.subscription.plan') ? 'activeNav' : '' }}">
                <a class="nav-link collapsed" href="{{route('admin.subscription.plan')}}">
                    <img src="" alt="" width="22" />
                    <span class="px-2">Subscription Plan</span>
                </a>
            </li>--}}

            <li
                class="nav-item change-password-link {{ request()->routeIs('admin.change.password') ? 'activeNav' : '' }}">
                <a class="nav-link collapsed" href="{{route('admin.change.password')}}">
                    <img src="" alt="" width="22" />
                    <span class="px-2">Change Password</span>
                </a>
            </li>

            <li class="nav-item log-data-link {{ request()->routeIs('admin.log.data') ? 'activeNav' : '' }}">
                <a class="nav-link collapsed" href="{{route('admin.log.data')}}">
                    <img src="" alt="" width="22" />
                    <span class="px-2">Log Data</span>
                </a>
            </li>

            <li class="nav-item preferences-link">
                <hr>
            </li>

            <li class="nav-item signout-link">
                <a class="nav-link collapsed adminLogout" href="#">
                    <img src="" alt="" width="22" />
                    <span class="px-2">Sign Out</span>
                </a>
            </li>

        </ul>

    </aside><!-- End Sidebar-->

    <main id="main" class="main">
        @yield('content')
    </main>
    <!-- End #main -->

    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">

        <div class="row">
            <div class="col-md-6 col-12 text-md-start text-center">
                <a href="{{route('admin.dashboard')}}" class="logo">
                    <img src="{{asset('assets/img/alegra-logo.png')}}" alt="probill" />
                    <!-- <span class="d-none d-lg-block">POS</span> -->
                </a>
                <!-- <p class="logo-text">by Alegra Labs</p> -->
            </div>
            <div class="col-md-6 text-md-end text-center">
                <div class="row justify-content-md-end justify-content-center my-3">
                    <div class="col-auto">
                        <a href="https://www.facebook.com/Alegralabs/" target="_BLANK"><img
                                src="{{ asset('assets/img/scoial-icons/fb.png') }}" alt="Facebook" width="40" /></a>
                    </div>
                    <div class="col-auto">
                        <a href="https://x.com/i/flow/login?redirect_after_login=%2Falegralabs22" target="_BLANK"><img
                                src="{{ asset('assets/img/scoial-icons/twittr.png') }}" alt="Twitter" width="40" /></a>
                    </div>
                    <div class="col-auto">
                        <a href="https://www.instagram.com/alegralabs7/" target="_BLANK"><img
                                src="{{ asset('assets/img/scoial-icons/insta.png') }}" alt="Instagram" width="40" /></a>
                    </div>
                    <div class="col-auto">
                        <a href="https://www.linkedin.com/company/helix-enterprise/posts/?feedView=all"
                            target="_BLANK"><img src="{{ asset('assets/img/scoial-icons/in.png') }}" alt="LinkedIn"
                                width="40" /></a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-md-start text-center">
                <p>Copyright 2024 © <strong>Ready Bill</strong>. Designed and developed by
                    <a href="https://www.alegralabs.com" target="_BLANK">Alegra Labs</a>
                </p>
            </div>

            <div class="col-md-6 text-md-end text-center">
                <p>
                   {{-- <a href="{{locale_route('about')}}" target="_BLANK">About</a> |
                    <a href="{{locale_route('privacy.policy')}}" target="_BLANK">Privacy Policy</a> |
                    <a href="{{locale_route('terms.and.conditions')}}" target="_BLANK">Terms of Use</a> --}}
                </p>
            </div>
        </div>

    </footer><!-- End Footer -->


    <!-- loader -->
    <div class="overlay">
        <div class="overlay__inner">
            <div class="overlay__content">
                <div class="lds-spinner loader">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
    <!-- loader -->

    <!-- Modal -->
    <div class="modal fade" id="showMessageModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bold" id="exampleModalLabel">Alert</h3>
                    <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
                </div>
                <div class="modal-body">
                    <h5 class="text-danger text-center">API Key Not Found</h5>
                </div>
                <div class="modal-footer">
                    <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> -->
                    <button type="button" class="btn btn-primary logout">Closed</button>
                </div>
            </div>
        </div>
    </div>
    <!-- modal -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    @section('scripts')

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="{{asset('assets/toast/js/jquery.toast.js')}}"></script>
    <!-- select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- select2 -->

    <!-- jQuery Cookie CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>


    <script>

        var media_url = "{{ env('MEDIA_URL') }}";
        var base_url = "{{ env('BASE_URL') }}";
        var admin_prefix = "{{ env('ADMIN_PREFIX') }}";
        var admin_logout_url = "{{ env('ADMIN_LOGOUT_URL') }}";
        var admin_url = "{{ env('ADMIN_URL') }}";
        var public_url = "{{ env('PUBLIC_URL') }}";

        
        $(".dashboard-link").find('img').attr('src', public_url + 'home.svg');
        $(".shops-link").find('img').attr('src', public_url + 'add-inventory.svg');
        $(".agents-link").find('img').attr('src', public_url + 'agents-list.svg');
        $(".agent-transactions-link").find('img').attr('src', public_url + 'agent-transactions.svg');
        $(".subscription-plans-link").find('img').attr('src', public_url + 'subscribe.svg');
        $(".change-password-link").find('img').attr('src', public_url + 'change_password.svg');
        $(".log-data-link").find('img').attr('src', public_url + 'log.svg');
        $(".signout-link").find('img').attr('src', public_url + 'logout.svg');

        function adminLogout() {

            // var formData = new FormData();
            // formData.append('token', = );

            $.ajax({
                url: '{{route("admin.logout")}}',
                type: 'POST',
                // data: formData,
                contentType: false,
                processData: false,
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]')
                        .attr('content'));
                    $(".overlay").show();
                },
                success: function (response) {

                    if (response) {
                        $(".overlay").hide();
                        localStorage.removeItem('token');
                        console.log('logout', response);
                        $.toast({
                            heading: 'Success',
                            text: response.message,
                            icon: 'success',
                            loader: true,
                            position: 'top-right',
                            loaderBg: '#9EC600'
                        });
                        window.location.href = admin_logout_url;
                    }
                },
                error: function (xhr, status, error) {
                    // Handle the error response
                    // var error = JSON.parse(xhr.responseText);
                    // console.error(error);
                    $(".overlay").hide();
                }
            });
        }

        $('.adminLogout').click(function (e) {
            adminLogout();
        });



        // ------------------------------------- CONVERT TIME FORMAT --------------------------------------

        function convertDateFormats(dateString) {

            var originalDateStr = dateString;
            var originalDate = new Date(originalDateStr);

            var convertedDateStr = ('0' + originalDate.getDate()).slice(-2) + '/' + ('0' + (originalDate
                .getMonth() + 1)).slice(-2) + '/' + originalDate.getFullYear() + ' - ' + ('0' + (
                    originalDate.getHours() % 12 || 12)).slice(-2) + ':' + ('0' + originalDate.getMinutes())
                        .slice(-2) + ' ' + (originalDate
                            .getHours() >= 12 ? 'PM' : 'AM');
            return convertedDateStr;
        }
        // ------------------------------------- CONVERT TIME FORMAT --------------------------------------


    </script>

    @show

    <!-- Vendor JS Files -->
    <script src="{{asset('assets/vendor/apexcharts/apexcharts.min.js')}}"></script>
    <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/vendor/chart.js/chart.umd.js')}}"></script>
    <!-- <script src="{{asset('assets/vendor/echarts/echarts.min.js')}}"></script> -->
    <script src="{{asset('assets/vendor/quill/quill.min.js')}}"></script>
    <script src="{{asset('assets/vendor/tinymce/tinymce.min.js')}}"></script>
    <script src="{{asset('assets/vendor/php-email-form/validate.js')}}"></script>

    <!-- Template Main JS File -->
    <script src="{{asset('assets/js/main.js')}}"></script>

    <!-- Datatable -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.js"></script>
    <!-- Datatable -->

    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

</body>

</html>