<!DOCTYPE html>
<!-- <html lang="en"> -->
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <meta name="google" content="notranslate">

    <title>{{  __('common.Ready Bill') }}</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('assets/img/favicon/16.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('assets/img/favicon/32.png')}}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{asset('assets/img/favicon/64.png')}}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{asset('assets/img/favicon/512.png')}}">

    <!-- Google Fonts -->
    <!-- <link href="https://fonts.gstatic.com" rel="preconnect"> -->
    <!-- <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet"> -->

    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
    <!-- <link href="{{asset('assets/vendor/quill/quill.snow.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/quill/quill.bubble.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/remixicon/remixicon.css')}}" rel="stylesheet"> -->
    <!-- <link href="{{asset('assets/vendor/simple-datatables/style.css')}}" rel="stylesheet"> -->

    <!-- Template Main CSS File -->
    <link href="{{asset('assets/css/style.css')}}" rel="stylesheet">

    <!-- CSS Loader -->
    <link href="{{asset('assets/css/loader.css')}}" rel="stylesheet">

    <!-- Country Dropdown -->
    <link href="{{asset('assets/css/countryDropdown.css')}}" rel="stylesheet">


    <!-- Toast -->
    <link rel="stylesheet" href="{{asset('assets/toast/css/jquery.toast.css')}}">

    <!-- Datatable -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.4.3/css/scroller.dataTables.min.css">
    <!-- Datatable -->

    <!-- Font Awesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Font Awesome 6.5.1 -->


    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">


    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            font-family: 'Roboto', sans-serif !important;
        }

        .activeNav a {
            background-color: #f6f9ff !important;
        }

        .disableLink {
            pointer-events: none;
            background-color: #adb5bd !important;
            /* color:white !important; */
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


        /* table#dataList.dataTable tbody tr:hover>td {
            background-color: #ffa;
            cursor: pointer;
        } */

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
            /* background-color: red !important; */
            /* color: white !important; */
            background-color: #faeae8 !important;
            color: black !important;
            /* font-weight: bold !important; */
        }

        #suggestionList {
            max-height: 170px;
            /* Set the maximum height for the suggestion list */
            overflow-y: auto;
            /* Enable vertical scrolling */


            position: absolute;
            z-index: 10;
            width: 70%
        }


        .suggestion-item {
            cursor: pointer;
        }

        /* .suggestion-item.active {
            background-color: #007bff;
            color: white;
        } */

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


        /* .probill-info p {
            font-size: 16px;
            line-height: 1.6;
        }

        .probill-info p:first-child {
            margin-bottom: 20px;
        } */


        .timer-display {
            font-size: 2rem;
            font-weight: bold;
        }


        .select2-selection__arrow {
            display: none;
        }


        /* --------------------------------------------- TRANSACTION --------------------------------------------- */
        /* .refund-row td {
            background-color: #dc3545 !important;
            color: #ffffff !important;
        } */
        /* --------------------------------------------- TRANSACTION --------------------------------------------- */
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">

        <div class="d-flex align-items-center justify-content-between">
            <div>
                <a href="{{ locale_route('home') }}" class="logo d-flex align-items-center">
                    <img src="{{  asset('assets/img/readybill.png')}}" alt="probill" />
                </a>
                <!-- <p class="logo-text">by Alegra Labs</p> -->
            </div>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->


        <div class="ms-auto mx-3  text-secondary">
            <!-- <span class="loggedInUserName"></span><br>
            <span class="lastLoggedInTime"></span> -->
        </div>

        <nav class="header-nav">

            {{--<ul class="d-flex align-items-center">
                <li class="nav-item dropdown pe-3">
                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">

                        <img src="#" alt="Profile" class="rounded-circle profileLogo">

                        <!-- <span class="d-none d-md-block dropdown-toggle ps-2"></span> -->
                    </a><!-- End Profile Iamge Icon -->

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center profile" href="profile">
                        <!-- <i class="fa-regular fa-address-card"></i> -->
                        <img src="" alt="" width="22" />
                        <span class="px-2">Account</span>
                    </a>
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center userLogout" href="#">
                        <!-- <i class="bi bi-box-arrow-right"></i> -->
                        <img src="" alt="" width="22" />
                        <span class="px-2">Sign Out</span>
                    </a>
                </li>

            </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->

            </ul> --}}

            <ul class="d-flex align-items-center">

                <li class="nav-item dropdown pe-3">
                    {{--<select class="form-select languageSelect" id="" aria-label="Select language">
                    </select>--}}

                    <div class="container py-5">

                        <!-- LIGHT -->
                        <div class="d-flex justify-content-center">
                            <!-- small width on desktop, fluid on mobile -->
                            <div class="lang-select lang-select-light w-100 w-sm-75 w-md-50" style="max-width:360px;">
                                <select class="form-select languageSelect" aria-label="Select language">
                                </select>
                            </div>
                        </div>

                        <!-- DARK -->
                        {{--<div class="d-flex justify-content-center">
                            <div class="lang-select lang-select-dark w-100 w-sm-75 w-md-50" style="max-width:360px;">
                                <select class="form-select languageSelect" aria-label="Select language">
                                    <option value="en" selected>English</option>
                                    <option value="hi">Hindi</option>
                                    <option value="bn">Bengali</option>
                                    <option value="ta">Tamil</option>
                                    <option value="te">Telugu</option>
                                </select>
                            </div>
                        </div>--}}

                    </div>
                </li>

                <li class="nav-item dropdown pe-3">
                    <span class="loggedInUserName"></span><br>
                    <span class="lastLoggedInTime"></span>
                </li>

                <li class="nav-item dropdown pe-3 nav-link nav-profile d-flex align-items-center pe-0">
                    <a href="{{ locale_route('profile') }}">
                        <img src="#" alt="Profile" class="rounded-circle profileLogo">
                    </a>
                </li>
            </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->

            </ul>
        </nav><!-- End Icons Navigation -->

    </header><!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">

        <ul class="sidebar-nav" id="sidebar-nav">

            <li class="nav-item add-sell-link">
                <a class="nav-link collapsed" href="sell">
                    <!-- <i class="fa-solid fa-shop"></i> -->
                    <img src="" alt="" width="22" />
                    <span class="px-2">{{ __('common.Quick Sell') }}</span>
                </a>
            </li>

            <li class="nav-item add-refund-link">
                <a class="nav-link collapsed" href="refund">
                    <!-- <i class="fa-solid fa-shop"></i> -->
                    <img src="" alt="" width="22" />
                    <span class="px-2">{{ __('common.Refund') }}</span>
                </a>
            </li>

            <li class="nav-item add-inventory-link d-none">
                <a class="nav-link collapsed" href="add-item">
                    <!-- <i class="fa-solid fa-plus"></i> -->
                    <img src="" alt="" width="22" />
                    <span class="px-2">{{ __('common.Add Inventory') }}</span>
                </a>
            </li>

            <li class="nav-item inventory-list-link">
                <a class="nav-link collapsed inventoryList" href="items">
                    <!-- <i class="fa-solid fa-table"></i> -->
                    <img src="" alt="" width="22" />
                    <span class="px-2">{{ __('common.Inventory') }}</span>
                </a>
            </li>

            <li class="nav-item transaction-link">
                <a class="nav-link collapsed" href="transactions">
                    <!-- <i class="fa-solid fa-list"></i> -->
                    <img src="" alt="" width="22" />
                    <span class="px-2">{{ __('common.Transaction') }}</span>
                </a>
            </li>

            <li class="nav-item add-user-link d-none">
                <a class="nav-link collapsed" href="users">
                    <!-- <i class="fa-regular fa-user"></i> -->
                    <img src="" alt="" width="22" />
                    <span class="px-2">{{ __('common.Employees') }}</span>
                </a>
            </li>

            <li class="nav-item preferences-link d-none">
                <a class="nav-link collapsed" href="settings">
                    <!-- <i class="fa-regular fa-user"></i> -->
                    <img src="" alt="" width="22" />
                    <span class="px-2">{{ __('common.Settings') }}</span>
                </a>
            </li>

            <li class="nav-item preferences-link">
                <hr>
            </li>

            <li class="nav-item account-link">
                <a class="nav-link collapsed" href="profile">
                    <img src="" alt="" width="22" />
                    <span class="px-2">{{ __('common.Account') }}</span>
                </a>
            </li>

            <li class="nav-item change-password-link">
                <a class="nav-link collapsed" href="#">
                    <img src="" alt="" width="22" />
                    <span class="px-2">{{ __('common.Change Password') }}</span>
                </a>
            </li>

            <!-- <li class="nav-item subscription-link">
                <a class="nav-link collapsed" href="subscription">
                    <img src="" alt="" width="22" />
                    <span class="px-2">Subscription</span>
                </a>
            </li> -->

            <li class="nav-item support-link">
                <a class="nav-link collapsed" href="support">
                    <img src="" alt="" width="22" />
                    <span class="px-2">{{ __('common.Support') }}</span>
                </a>
            </li>

            <li class="nav-item dataset-link">
                <a class="nav-link collapsed" href="dataset">
                    <img src="" alt="" width="22" />
                    <span class="px-2">{{ __('common.Dataset') }}</span>
                </a>
            </li>

            <!-- <li class="nav-item signout-link">
                <a class="nav-link collapsed userLogout" href="#">
                    <img src="" alt="" width="22" />
                    <span class="px-2">{{ __('common.Sign Out') }}</span>
                </a>
            </li> -->


            <li class="nav-item signout-link">
                <button type="button" class="nav-link collapsed userLogout flex items-center bg-transparent border-0">
                    <img src="" alt="" width="22" />
                    <span class="px-2">{{ __('common.Sign Out') }}</span>
                </button>
            </li>

        </ul>

    </aside><!-- End Sidebar-->


    <div id="">
        <main id="main" class="main">
            @yield('content')
        </main>
        <!-- End #main -->
    </div>



    <!-- ======= Footer ======= -->


    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer py-4 mt-auto"> <!-- mt-auto pushes it to bottom -->
        <div class="container-fluid px-md-4 px-3"> <!-- Responsive padding -->
            <div class="row align-items-center">
                <!-- Logo Section -->
                <div class="col-md-6 col-12 text-md-start text-center mb-3 mb-md-0">
                    <div class="d-flex justify-content-md-start justify-content-center align-items-center">
                        <img src="{{asset('assets/img/alegra-logo.png')}}" alt="Alegra Labs" height="40" class="me-2">
                        <div class="logo-divider"></div>
                        <img src="{{asset('assets/img/readybill.png')}}" alt="Ready Bill" height="35" class="ms-2">
                    </div>
                </div>

                <!-- Social Icons -->
                <div class="col-md-6 text-md-end text-center">
                    <div class="d-flex justify-content-md-end justify-content-center gap-3">
                        <a href="https://www.facebook.com/Alegralabs/" target="_blank">
                            <img src="{{asset('assets/img/scoial-icons/fb.png')}}" alt="Facebook" width="40">
                        </a>
                        <a href="https://x.com/i/flow/login?redirect_after_login=%2Falegralabs22" target="_blank">
                            <img src="{{asset('assets/img/scoial-icons/twittr.png')}}" alt="Twitter" width="40">
                        </a>
                        <a href="https://www.instagram.com/alegralabs7/" target="_blank">
                            <img src="{{asset('assets/img/scoial-icons/insta.png')}}" alt="Instagram" width="40">
                        </a>
                        <a href="https://www.linkedin.com/company/helix-enterprise/posts/?feedView=all" target="_blank">
                            <img src="{{asset('assets/img/scoial-icons/in.png')}}" alt="LinkedIn" width="40">
                        </a>
                    </div>
                </div>
            </div>

            <div class="my-3"></div>

            <div class="row text-center text-md-start">
                <div class="col-md-6 mb-2 mb-md-0">
                    <p class="mb-0">
                        {{  __('common.Copyright 2024 ©') }} <strong>{{  __('common.Ready Bill') }}</strong>.
                        {{  __('common.Designed and developed by') }}
                        <a href="https://www.alegralabs.com" target="_blank" class="fw-bold text-primary">Alegra
                            Labs</a>
                    </p>
                </div>

                <!-- Footer Links -->
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <a href="{{ locale_route('about') }}" target="_blank"
                            class="text-decoration-none">{{  __('common.About') }}</a> |
                        <a href="{{ locale_route('contact') }}" target="_blank" class="text-decoration-none">{{ __('common.Contact') }}</a>
                        |
                        <a href="{{ locale_route('agents') }}" target="_blank"
                            class="text-decoration-none">{{  __('common.Agents') }}</a>
                        |
                        <a href="{{ locale_route('privacy.policy') }}" target="_blank"
                            class="text-decoration-none">{{  __('common.Privacy Policy') }}</a> |
                        <a href="{{ locale_route('terms.and.conditions') }}" target="_blank"
                            class="text-decoration-none">{{  __('common.Terms of Use') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    <!-- End Footer -->

    <!-- <footer id="footer" class="footer py-4">

        <div class="container-fluid">
            <div class="row align-items-center">

                
                <div class="col-md-6 col-12 text-md-start text-center mb-3 mb-md-0">
                    <div class="d-flex justify-content-md-start justify-content-center align-items-center">
                        <img src="assets/img/alegra-logo.png" alt="Alegra Labs" height="40" class="me-2">
                        <div class="logo-divider"></div>
                        <img src="assets/img/readybill.png" alt="Ready Bill" height="35" class="ms-2">
                    </div>
                </div>

           
                <div class="col-md-6 text-md-end text-center">
                    <div class="d-flex justify-content-md-end justify-content-center gap-3">
                        <a href="https://www.facebook.com/Alegralabs/" target="_blank">
                            <img src="assets/img/scoial-icons/fb.png" alt="Facebook" width="40">
                        </a>
                        <a href="https://x.com/i/flow/login?redirect_after_login=%2Falegralabs22" target="_blank">
                            <img src="assets/img/scoial-icons/twittr.png" alt="Twitter" width="40">
                        </a>
                        <a href="https://www.instagram.com/alegralabs7/" target="_blank">
                            <img src="assets/img/scoial-icons/insta.png" alt="Instagram" width="40">
                        </a>
                        <a href="https://www.linkedin.com/company/helix-enterprise/posts/?feedView=all" target="_blank">
                            <img src="assets/img/scoial-icons/in.png" alt="LinkedIn" width="40">
                        </a>
                    </div>
                </div>

            </div>

            <div class="my-3"></div>

            <div class="row text-center text-md-start">
                <div class="col-md-6 mb-2 mb-md-0">
                    <p class="mb-0">
                        Copyright 2024 © <strong>Ready Bill</strong>. Designed and developed by
                        <a href="https://www.alegralabs.com" target="_blank" class="fw-bold text-primary">Alegra
                            Labs</a>
                    </p>
                </div>

   
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <a href="{{ locale_route('about') }}" target="_blank" class="text-decoration-none">About</a> |
                        <a href="{{ locale_route('contact') }}" target="_blank" class="text-decoration-none">Contact</a> |
                        <a href="{{ locale_route('agents') }}" target="_blank" class="text-decoration-none">Agents</a> |
                        <a href="{{ locale_route('privacy.policy') }}" target="_blank" class="text-decoration-none">Privacy
                            Policy</a> |
                        <a href="{{ locale_route('terms.and.conditions') }}" target="_blank" class="text-decoration-none">Terms
                            of Use</a>
                    </p>
                </div>
            </div>

        </div>

    </footer> -->


    <!-- End Footer -->


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
    <!-- <div class="modal fade" id="showMessageModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bold" id="exampleModalLabel">Alert</h3>
                </div>
                <div class="modal-body">
                    <h5 class="text-danger text-center errorMessage">API Key Not Found</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary logout">Closed</button>
                </div>
            </div>
        </div>
    </div> -->
    <!-- modal -->


    <!-- Modal -->
    <!-- <div class="modal fade" id="changePasswordModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bold" id="exampleModalLabel">Change Password</h3>
                </div>
                <div class="modal-body text-center">
                    <input type="hidden" name="phone_number" id="phone_number" readonly />
                    <a class="btn btn-success generateOtpForm" href="#" role="button">Send</a>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div> -->
    <!-- modal -->

    @include('coreweb::modals.showMessageModal')
    @include('coreweb::modals.changePassword', ['loc' => 'change'])
    @include('coreweb::modals.errorMessage')

    @include('coreweb::modals.signout', [
    'heading' => __('common.Confirm Sign Out'),
    'subHeading' => __('common.Are you sure you want to sign out from your account?'),
    'buttonText' => __('common.Sign Out')
])


    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    @section('scripts')

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="{{asset('assets/toast/js/jquery.toast.js')}}"></script>

    <!-- jQuery Cookie CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        var base_url = "{{ env('BASE_URL') }}";
        var api_url = "{{ env('API_URL') }}";

        var grocery_germany_api_url = "{{ env('API_URL') }}" + "{{ env('GROCERY_GERMANY_PREFIX') }}";

        var media_url = "{{ env('MEDIA_URL') }}";
        var logo_url = "{{ env('LOGO_URL') }}";
        var public_url = "{{ env('PUBLIC_URL') }}";
        const duration = "{{ env('COUNTDOWN') }}";
        var api_key = "{{ session('api_key') }}";
        var x_api_key_secret = "{{ env('ENCRYPTED_API_SECRET_KEY') }}";


        validateInputMessage = @json(__('common.Only numeric values, dots, and commas are allowed'));
        unexpectedErrorMessage = @json(__('common.An unexpected error occurred. Please try again later'));
        tryAgainMessage = @json(__('common.Please try again in'));
        otpExpiredMessage = @json(__('otp.otp_expired'));


        (function () {
            // Ensure there's state so popstate fires.
            history.replaceState({ p: 'auth' }, document.title, location.href);

            // 1) If restored from bfcache (some browsers set persisted), reload.
            window.addEventListener('pageshow', function (e) {
                if (e.persisted) location.reload();
            });

            // 2) Detect any back/forward navigation via Navigation Timing L2.
            function isBackForwardNav() {
                var nav = performance.getEntriesByType && performance.getEntriesByType('navigation');
                return nav && nav[0] && nav[0].type === 'back_forward';
            }

            // 3) On popstate, reload to re-run server auth checks.
            window.addEventListener('popstate', function () {
                location.reload();
            });

            // 4) Fallback for Incognito: when the page becomes visible after a history nav, reload.
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible' && isBackForwardNav()) {
                    location.reload();
                }
            });

            // 5) Final safety: if this render itself is from back/forward, reload immediately.
            if (isBackForwardNav()) {
                location.reload();
            }
        })();

    </script>

    <!-- Country Dropdown -->
    <script src="{{asset('assets/js/countryDropdown.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>

    <!-- ########################################################## LOGIN CCHECK ###################################################### -->
    <script>
        var access_token = "{{ session('access_token') }}";
        var language = "{{ Auth::user()->lang ?? session('lang') }}".toLowerCase();
        var country_code = "{{ Auth::user()->country_code ?? session('country_code') }}".toLowerCase();
        var shop_type = "{{ Auth::user()->shop_type }}";


        localStorage.setItem('token', access_token);
        localStorage.setItem('language', language);
        localStorage.setItem('api_key', api_key);


        var base_url_with_country_lang = `${base_url}/${country_code}/${language}/${shop_type}/`;

        // console.log('token', access_token, api_key);

        $(".overlay").show();

        $.ajaxSetup({
            headers: {
                'auth-key': api_key,
                'Accept-Language': language,
                'X-API-Secret': x_api_key_secret,
            }
        });

        let user_selected_country = "{{ Auth::user()->country_code }}".toLowerCase();
        localStorage.setItem('country_code', user_selected_country);
        // let country_details = "{{ Auth::user()->country_details }}";
        let country_details = {!! json_encode(Auth::user()->country_details) !!};
        let user_selected_region = JSON.parse(country_details).region;


        var isAdmin = 0;
        var stockPrefernce = 0;

        $(".add-sell-link").find('img').attr('src', public_url + 'home.svg');
        $(".add-inventory-link").find('img').attr('src', public_url + 'add-inventory.svg');
        $(".inventory-list-link").find('img').attr('src', public_url + 'list.svg');
        $(".transaction-link").find('img').attr('src', public_url + 'transactions.svg');
        $(".add-user-link").find('img').attr('src', public_url + 'users.svg');
        $(".preferences-link").find('img').attr('src', public_url + 'preferences.svg');
        $(".add-refund-link").find('img').attr('src', public_url + 'refund.svg');

        $(".account-link").find('img').attr('src', public_url + 'profile.svg');
        $(".support-link").find('img').attr('src', public_url + 'support.svg');
        $(".dataset-link").find('img').attr('src', public_url + 'dataset.svg');
        $(".signout-link").find('img').attr('src', public_url + 'logout.svg');

        $(".change-password-link").find('img').attr('src', public_url + 'change_password.svg');
        $(".subscription-link").find('img').attr('src', public_url + 'subscribe.svg');

        function setActiveNavbar() {

            var currentUrl = window.location.pathname;
            var urlParts = currentUrl.split('/');
            var pathname = urlParts[4];

            console.log('setActiveNavbar', pathname);

            if (pathname == 'sell') {
                $(".add-sell-link").addClass("activeNav");
            }

            else if (pathname == 'refund') {
                $(".add-refund-link").addClass("activeNav");
            }

            else if (pathname == 'add-item') {
                $(".add-inventory-link").addClass("activeNav");
            } else if (pathname == 'items') {
                $(".inventory-list-link").addClass("activeNav");
            } else if ((pathname == 'transactions') || (pathname == 'generate-report')) {
                $(".transaction-link").addClass("activeNav");
            } else if (pathname == 'users') {
                $(".add-user-link").addClass("activeNav");
            } else if (pathname == 'all-users') {
                $(".add-user-link").addClass("activeNav");
            } else if (pathname == 'settings') {
                $(".preferences-link").addClass("activeNav");
            }

            else if (pathname == 'profile') {
                $(".account-link").addClass("activeNav");
            }

            else if (pathname == 'subscription') {
                $(".subscription-link").addClass("activeNav");
            }

            else if (pathname == 'support') {
                $(".support-link").addClass("activeNav");
            }

            else if (pathname == 'dataset') {
                $(".dataset-link").addClass("activeNav");
            }
        }

        setActiveNavbar();

        function checkUserLogin() {

            $.ajax({
                type: 'GET',
                url: api_url + 'user-detail',
                headers: {
                    // 'Authorization': 'Bearer ' + localStorage.getItem('token')
                    'Authorization': 'Bearer ' + access_token
                    // 'Authorization': 'Bearer ' + localStorage.getItem('token') ? localStorage.getItem('token') : access_token
                },
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $(".overlay").show();
                },
                success: function (response) {
                    if (response) {
                        isAdmin = response.data.isAdmin;
                        console.log('user-details', response);
                        console.log('user-details logo', response.logo);


                        if (response.data.isAdmin == 1) {
                            $(".loggedInUserName").text(@json(__('common.Hello'))+", " + response.data.shop.name);
                        }
                        else if (response.data.isAdmin == 0) {
                            $(".loggedInUserName").text(@json(__('common.Hello'))+", " + response.data.staff.name);
                        }


                        $(".lastLoggedInTime").text("Last Logged In " + convertDateFormats(response.data
                            .last_logged_in));

                        $("#user_id").val(response.data.user_id);
                        $("#isAdmin").val(response.data.isAdmin);

                        if (response.data.isAdmin == 1) {
                            $("#name").val(response.data.shop.name);
                        }
                        else if (response.data.isAdmin == 0) {
                            $("#name").val(response.data.staff.name);

                            $(".deleteAccountSection").addClass("d-none")
                        }

                        $("#business_name").val(response.data.details.business_name);
                        $("#email").val(response.data.details.email);
                        $("#mobile").val(response.data.mobile);


                        var countryCode = response.data.country_details.code;
                        user_selected_country = response.data.country_details.code.toLowerCase();
                        country_details = response.data.country_details;

                        setCurrency();

                        console.log('checkUserLogin', response.data.country_details);


                        $(".updateProfile .countryCode").val(countryCode).trigger('change');
                        $("#changeMobileNumberModal .countryCode").val(countryCode).trigger('change');
                        $("#add-new-user .countryCode").val(countryCode).trigger('change');
                        $("#changePasswordModal .countryCode").val(countryCode).trigger('change');
                        $("#shareInvoiceModal .countryCode").val(countryCode).trigger('change');


                        $(".updateProfile .countryCode").prop("disabled", true).css("background-color", "#EEEEEE");
                        $("#changeMobileNumberModal .countryCode").prop("disabled", true).css("background-color", "#EEEEEE");
                        $("#add-new-user .countryCode").prop("disabled", true).css("background-color", "#EEEEEE");
                        $("#sub-user-profile-update .countryCode").prop("disabled", true).css("background-color", "#EEEEEE");
                        // $("#shareInvoiceModal .countryCode").prop("disabled", true).css("background-color", "#EEEEEE");


                        // console.log('all_languages', response.data.lang);
                        // language code
                        response.all_languages.forEach(lang => {
                            // let value = typeof lang === 'object' ? lang.value : lang;
                            // let text = typeof lang === 'object' ? lang.name : lang;


                            // console.log('all_languages', lang.flag)

                            $(".languageSelect").append(`<option value="${lang.short_code}">${lang.language}</option>`);

                        });
                        $(".languageSelect").val(response.data.lang);

                        // language code



                        // getCountryCode().then(response => {

                        //     let countryDropdown = $(".updateProfile .countryCode");
                        //     let countryDropdown1 = $("#changePasswordModal .countryCode");
                        //     let countryDropdown2 = $("#changeMobileNumberModal .countryCode");
                        //     let countryDropdown3 = $("#add-new-user .countryCode");
                        //     let countryDropdown4 = $("#sub-user-profile-update .countryCode");


                        //     if (response && response.countryCode) { // Expecting countryShort (e.g., 'IN', 'US')
                        //         let dialCode = countryMap[response.countryCode]; // Find corresponding dial code

                        //         console.log('dialCode', dialCode);

                        //         // if (countryCode) {
                        //         //     countryDropdown.val(countryCode).trigger('change');
                        //         //     countryDropdown1.val(countryCode).trigger('change');
                        //         //     countryDropdown2.val(countryCode).trigger('change');
                        //         //     countryDropdown3.val(countryCode).trigger('change');
                        //         //     countryDropdown4.val(countryCode).trigger('change');
                        //         // }
                        //         // else if (dialCode) {
                        //         //     countryDropdown.val(dialCode).trigger('change');
                        //         //     countryDropdown1.val(countryCode).trigger('change');
                        //         //     countryDropdown2.val(countryCode).trigger('change');
                        //         //     countryDropdown3.val(countryCode).trigger('change');
                        //         //     countryDropdown4.val(countryCode).trigger('change');
                        //         // } else {
                        //         //     console.warn("No matching country found for:", response.countryCode);
                        //         // }


                        //         if (countryCode) {
                        //             console.log('countryCode', countryCode);
                        //         }


                        //         countryDropdown.prop("disabled", true).css("background-color", "#EEEEEE")
                        //         countryDropdown1.prop("disabled", true).css("background-color", "#EEEEEE")
                        //         countryDropdown2.prop("disabled", true).css("background-color", "#EEEEEE")
                        //         countryDropdown3.prop("disabled", true).css("background-color", "#EEEEEE")
                        //         countryDropdown4.prop("disabled", true).css("background-color", "#EEEEEE")

                        //     } else {
                        //         console.warn("Country short code not found in response");
                        //     }
                        // }).catch(error => {
                        //     console.error("Failed to fetch country code:", error);
                        // });


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


                        // RESTRICT MENU BASED ON USER
                        if (response.data.isAdmin == 1) {
                            console.log('is Admin');
                            $(".add-inventory-link").removeClass("d-none");
                            $(".add-user-link").removeClass("d-none");
                            // $(".view-users-link").removeClass("d-none");
                            $(".preferences-link").removeClass("d-none");
                            $(".apiKeySection").removeClass("d-none");

                            $(".employeePhotoSection").addClass("d-none");
                            $(".logoSection").removeClass("d-none");

                            $(".profileUpdateButton").removeClass('d-none');
                            $(".profileUpdateButton").prop('disabled', false);


                            $(".change-password-link").removeClass('d-none');

                            $(".isAdminSection").removeClass("d-none");

                        } else {
                            $(".inventoryList span").text("View Inventory");
                            $(".inventoryHeading").text("View Inventory");
                            $(".inventoryLi").text("View Inventory");

                            $('#gstin').attr('disabled', true);
                            $('#logo').attr('disabled', true);

                            $("#clear-button").addClass("d-none");
                            $(".apiKeySection").addClass("d-none");

                            $(".employeePhotoSection").removeClass("d-none");
                            $(".logoSection").addClass("d-none");

                            $(".profileUpdateButton").addClass('d-none');
                            $(".profileUpdateButton").prop('disabled', true);

                            $(".employeeNote").removeClass('d-none');


                            // $(".change-password-link").addClass('d-none');
                            $(".subscription-link").addClass('d-none');
                            $(".dataset-link").addClass('d-none');

                            // $(".add-user-link").removeClass("d-none");
                            // $(".add-user-link a").attr('href', 'all-users');


                            // show staff photo
                            $(".staffPhotoSection").removeClass('d-none');
                            $("#staffPhoto").attr("src", response.staffPhoto);
                            $('#staffPhoto').on('load', function () {
                                $(this).css({
                                    'max-width': '200px',
                                    'max-height': '100px'
                                });
                            });
                            // show staff photo

                            $(".isAdminSection").addClass("d-none");

                        }

                        $("#userPhoto").attr("src", response.photo);
                        // $("#photo").addClass('d-none');

                        // if (response.isPhoto == 1) {
                        //     $("#photo-clear-button").addClass("d-none");
                        // }
                        // else {
                        //     $("#photo-clear-button").addClass("d-none");
                        // }

                        $('#userPhoto').on('load', function () {
                            $(this).css({
                                'max-width': '200px',
                                'max-height': '100px'
                            });
                        });

                        getUserPrefernces();

                        // SUBSCRIPTION EXPIRY DATE
                        $("#expiryDate").text(response.subscription_expiry_date);
                        $("#currentPlan").text(response.subscription_current_plan);
                        // SUBSCRIPTION EXPIRY DATE


                        // RESTRICT MENU BASED ON USER
                        $(".overlay").hide();

                        // CHECK SUBSCRIPTION EXPIRED OR NOT



                        // if (response.data.isAdmin == 1) {
                        //     if (!window.location.pathname.includes('/subscription')) {
                        //         if (response.isSubscriptionExpired == 1) {
                        //         }
                        //     }
                        // }



                        if (response.data.isAdmin == 1) {
                            let ignoredRoutes = ['/profile', '/subscription', '/support', '/dataset', '/change-password']; // Add routes to ignore

                            let currentPath = window.location.pathname;

                            if (!ignoredRoutes.some(route => currentPath.includes(route))) {
                                if (response.isSubscriptionExpired == 1) {
                                    window.location.href = base_url_with_country_lang + 'subscription';
                                }
                            }
                        }
                        else if (response.data.isAdmin == 0) {
                            let ignoredRoutes = ['/profile', '/subscription', '/support', '/dataset', '/change-password']; // Add routes to ignore

                            let currentPath = window.location.pathname;

                            if (!ignoredRoutes.some(route => currentPath.includes(route))) {
                                if (response.isSubscriptionExpired == 1) {
                                    window.location.href = base_url_with_country_lang + 'profile';
                                }
                            }
                        }

                        if (response.isSubscriptionExpired == 1) {

                            console.log('isSubscriptionExpired', response.isSubscriptionExpired);

                            $(document).find("#subscripitonErrorId").removeClass('d-none');
                            $(document).find("#subscripitonErrorId .error-body").html('<p class="my-1 text-danger">' + response.subscriptionExpiredMessage + '</p>');

                            // $(".error-div .error-body").html(
                            //     $('<div class="d-flex justify-content-between align-items-center">')
                            //         .append($('<p class="my-1 text-danger">' + response.subscriptionExpiredMessage + '</p>'))
                            //         .append($('<a href="/subscription" class="btn btn-success">Renew Subscription</a>'))
                            // );

                            $(".add-sell-link a").addClass("disabled disableLink");
                            $(".add-inventory-link a").addClass("disabled disableLink");
                            $(".inventory-list-link a").addClass("disabled disableLink");
                            $(".transaction-link a").addClass("disabled disableLink");
                            $(".add-user-link a").addClass("disabled disableLink");
                            $(".preferences-link a").addClass("disabled disableLink");
                            $(".add-refund-link a").addClass("disabled disableLink");


                            $(".add-sell-link a").addClass("d-none");
                            $(".add-refund-link a").addClass("d-none");
                            $(".add-inventory-link a").addClass("d-none");
                            $(".inventory-list-link a").addClass("d-none");
                            $(".transaction-link a").addClass("d-none");
                            $(".add-user-link a").addClass("d-none");
                            $(".preferences-link a").addClass("d-none");

                            // $(".account-link a").addClass("disabled disableLink");
                            // $(".support-link a").addClass("disabled disableLink");
                            // $(".dataset-link a").addClass("disabled disableLink");
                            // $(".change-password-link a").addClass("disabled disableLink");


                            if (response.data.isAdmin == 0) {
                                // userLogout();
                            }
                        }
                        else {
                            $(".add-sell-link a").removeClass("disabled disableLink");
                            $(".add-inventory-link a").removeClass("disabled disableLink");
                            $(".inventory-list-link a").removeClass("disabled disableLink");
                            $(".transaction-link a").removeClass("disabled disableLink");
                            $(".add-user-link a").removeClass("disabled disableLink");
                            $(".preferences-link a").removeClass("disabled disableLink");
                            $(".add-refund-link a").removeClass("disabled disableLink");
                            $(".account-link a").removeClass("disabled disableLink");
                            $(".support-link a").removeClass("disabled disableLink");
                            $(".dataset-link a").removeClass("disabled disableLink");
                            $(".change-password-link a").removeClass("disabled disableLink");
                        }
                        // CHECK SUBSCRIPTION EXPIRED OR NOT


                        if (window.location.pathname.includes('/profile')) {
                            addorRemoveRestrictionOnFields();
                        }

                        setActiveNavbar();
                    }
                },
                error: function (error) {
                    $(".overlay").hide();
                    console.log('Error', error);
                    console.log('access_token', access_token);

                }
            });
        }

        function userLogout() {

            // var formData = new FormData();
            // formData.append('token', = );

            $.ajax({
                url: base_url + '/logout',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
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
                        window.location.href = base_url;
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

        checkUserLogin();

        $('.userLogout').click(function (e) {
            // userLogout();
            $("#signOutModal").modal('show');

        });
        $('.logout').click(function (e) {
            // userLogout();
              $("#signOutModal").modal('show');
         });


        $(".signOutConfrimModalButton").click(function (e){
            userLogout();
        });


        function checkImageExists(url, callback) {
            var img = new Image();
            img.onload = function () {
                callback(true);
            };
            img.onerror = function () {
                callback(false);
            };
            img.src = url;
        }


        function checkAPIKey(key) {

            var formData = new FormData();
            formData.append('key', key);

            $.ajax({
                type: 'POST',
                url: api_url + 'check-api-key',
                headers: {
                    'Authorization': 'Bearer ' + access_token
                },
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $(".overlay").show();
                },
                success: function (response) {
                    if (response) {

                        console.log('checkAPIKey', response);

                        if (response.status == 'failed') {
                            $("#showMessageModal").modal('show');
                        }

                        $(".overlay").hide();
                    }
                },
                error: function (error) {
                    $(".overlay").hide();
                    userLogout();
                }
            });
        }



        // ----------------------------------- SHOP OR STAFF --------------------------------------
        function addorRemoveRestrictionOnFields() {
            var isAdmin = $("#isAdmin").val();

            console.log('addorRemoveRestrictionOnFields', isAdmin);

            if (isAdmin == 1) {

                $("#api_key").attr("readonly", "readonly").css("background-color", "#EEEEEE");
                $("#business_name").attr("readonly", "readonly").css("background-color", "#EEEEEE");
                $("#mobile").attr("readonly", "readonly").css("background-color", "#EEEEEE");

                $("#update-profile .countryCode").prop("disabled", true).css("background-color", "#EEEEEE");

                // $("#changeMobileNumberModal .countryCode").prop("disabled", true).css("background-color", "#EEEEEE");

                // $("#add-new-user .countryCode").prop("disabled", true).css("background-color", "#EEEEEE");
                // $("#sub-user-profile-update .countryCode").prop("disabled", true).css("background-color", "#EEEEEE");

                $("#shop_type").prop("disabled", true).css("background-color", "#EEEEEE");


                $("#name").removeAttr("readonly").css("background-color", "#FFFFFF");
                $("#email").removeAttr("readonly").css("background-color", "#FFFFFF");
                $("#address").removeAttr("readonly").css("background-color", "#FFFFFF");
                $("#gstin").removeAttr("readonly").css("background-color", "#FFFFFF");

            }
            else if (isAdmin == 0) {
                $("#name").attr("readonly", "readonly").css("background-color", "#EEEEEE");
                $("#business_name").attr("readonly", "readonly").css("background-color", "#EEEEEE");
                $("#email").attr("readonly", "readonly").css("background-color", "#EEEEEE");
                $("#mobile").attr("readonly", "readonly").css("background-color", "#EEEEEE");

                // $("#update-profile .countryCode").prop("disabled", true).css("background-color", "#EEEEEE");

                // $("#changeMobileNumberModal .countryCode").prop("disabled", true).css("background-color", "#EEEEEE");

                $("#changeMobileNumberButton").prop("disabled", true);
                $("#changeMobileNumberButton").addClass("d-none");

                $("#address").attr("readonly", "readonly").css("background-color", "#EEEEEE");
                $("#shop_type").attr("readonly", "readonly").css("background-color", "#EEEEEE");
                $("#gstin").attr("readonly", "readonly").css("background-color", "#EEEEEE");
            }

        }
        // ----------------------------------- SHOP OR STAFF --------------------------------------



        // ------------------------------------------------------- COMMON FUNCTIONS ------------------------------------------------------


        // Add Item and Update Item Prefernce Check
        function getUserPrefernces() {
            $.ajax({
                type: 'GET',
                url: grocery_germany_api_url + 'user-preferences',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $(".overlay").show();
                },
                success: function (response) {
                    if (response) {
                        console.log('user-preferences', response);

                        if (response.data != null) {


                            if (response.data.preference_mrp == 1) {
                                $('input[type="checkbox"][name="preference_mrp"]').prop(
                                    'checked', true);
                                // $(".mrp-div").removeClass("d-none");

                                $(".mrp-required").removeClass('d-none');

                            } else {
                                // $(".mrp-div").addClass("d-none");
                                $(".mrp-required").addClass('d-none');
                            }

                            if (response.data.preference_mrp_invoice == 1) {
                                $('input[type="checkbox"][name="preference_mrp_invoice"]').prop(
                                    'checked', true);
                            }

                            if (response.data.preference_quantity == 1) {
                                stockPrefernce = 1;
                                $('input[type="checkbox"][name="preference_quantity"]').prop(
                                    'checked', true);
                                // $(".stockQuantuty-div").removeClass("d-none");
                                $(".stockQuantuty-required").removeClass('d-none');
                            } else {
                                // $(".stockQuantuty-div").addClass("d-none");
                                $(".stockQuantuty-required").addClass('d-none');
                            }

                            if (response.data.preference_hsn == 1) {
                                $('input[type="checkbox"][name="preference_hsn"]').prop(
                                    'checked', true);
                                // $(".hsn-div").removeClass("d-none");
                                $(".hsn-required").removeClass('d-none');
                            } else {
                                // $(".hsn-div").addClass("d-none");
                                $(".hsn-required").addClass('d-none');
                            }

                            if (response.data.preference_hsn_invoice == 1) {
                                $('input[type="checkbox"][name="preference_hsn_invoice"]').prop(
                                    'checked', true);
                            }

                            $('select[name="preference_invoice_format"]').val(response.data.preference_invoice_format);
                            $("#invoice_format").val(response.data.preference_invoice_format);


                            if (response.data.preference_transaction_mark_as_paid == 1) {
                                $('input[type="checkbox"][name="preference_transaction_mark_as_paid"]').prop(
                                    'checked', true);
                            }

                            if (response.data.preference_transaction_mark_as_unpaid == 1) {
                                $('input[type="checkbox"][name="preference_transaction_mark_as_unpaid"]').prop(
                                    'checked', true);
                            }



                        }

                        // $(".overlay").hide();
                    }
                    // updateTaxList();
                },
                error: function (error) {
                    $(".overlay").hide();
                    console.log('Error', error);
                }
            });
        }

        // Add Item and Update Item Prefernce Check

        // Function to update count
        function updateCount() {
            count = $(".taxSection").length;
        }


        // ------------------------------------- SEARCH ITEM --------------------------------------

        // // on change item get related units list for quantity
        let selectedSuggestionIndex = -1;
        var item_unit;
        var searh_itemID;


        // $('#searchInput').keyup(function () {
        //     $('#suggestionList').empty();
        //     const searchTerm = $(this).val().trim().toLowerCase();

        //     if (searchTerm.length > 0) {
        //         var formData = new FormData();
        //         formData.append('item_name', searchTerm);

        //         $.ajax({
        //             url: api_url + 'suggesstion-list',
        //             headers: {
        //                 'Authorization': 'Bearer ' + localStorage.getItem('token')
        //             },
        //             method: 'post',
        //             data: formData,
        //             contentType: false,
        //             processData: false,
        //             success: function (response) {
        //                 if (response.status === "success") {
        //                     $('#suggestionList').empty().show();
        //                     response.data.forEach(function (suggestion) {
        //                         $('#suggestionList').append(
        //                             `<li class="list-group-item suggestion-item" data-id="${suggestion.id}">${suggestion.item_name}</li>`
        //                         );
        //                     });
        //                 } else {
        //                     $('#suggestionList').hide();
        //                 }
        //             },
        //             error: function () {
        //                 $('#suggestionList').hide();
        //             }
        //         });
        //     } else {
        //         $('#suggestionList').hide();
        //     }
        // });

        // // Hide suggestions when clicking outside
        // $(document).on('click', function (event) {
        //     if (!$(event.target).closest('#searchInput, #suggestionList').length) {
        //         $('#suggestionList').hide();
        //     }
        // });

        // // Select suggestion on click
        // $(document).on('click', '.suggestion-item', function () {
        //     $('#searchInput').val($(this).text());
        //     $('#suggestionList').hide();
        // });

        // // Handle suggestion click
        // $(document).on('click', '.suggestion-item', function () {
        //     const selectedSuggestion = $(this).text();

        //     $('#searchInput').val(selectedSuggestion);
        //     getRelatedUnit($(this).data('id'));
        //     // $('#searh_itemID').val($(this).data('id'));
        //     searh_itemID = $(this).data('id');

        //     $('#suggestionList').empty();

        // });

        // // Handle keyboard navigation
        // $('#searchInput').keydown(function (event) {

        //     const suggestionItems = $('.suggestion-item');

        //     if (event.keyCode === 40) { // Down arrow key
        //         selectedSuggestionIndex = Math.min(selectedSuggestionIndex + 1,
        //             suggestionItems
        //                 .length - 1);
        //         updateActiveSuggestion(suggestionItems);
        //     } else if (event.keyCode === 38) { // Up arrow key
        //         selectedSuggestionIndex = Math.max(selectedSuggestionIndex - 1, -1);
        //         updateActiveSuggestion(suggestionItems);
        //     } else if (event.keyCode === 13) { // Enter key
        //         if (selectedSuggestionIndex !== -1) {
        //             const selectedSuggestion = suggestionItems.eq(
        //                 selectedSuggestionIndex).text();
        //             $('#searchInput').val(selectedSuggestion);
        //             $('#suggestionList').empty();
        //         }
        //     }
        // });

        // function updateActiveSuggestion(suggestionItems) {
        //     suggestionItems.removeClass('active');
        //     if (selectedSuggestionIndex !== -1) {
        //         suggestionItems.eq(selectedSuggestionIndex).addClass('active');
        //     }
        // }

        // ------------------------------------- UPDATED CODE --------------------------------------

        $('#searchInput').keyup(function (event) {

            if (event.keyCode === 13) return;

            if (event.keyCode === 40) {
                return;
            }

            $('#suggestionList').empty();
            const searchTerm = $(this).val().trim().toLowerCase();

            if (searchTerm.length > 0) {
                var formData = new FormData();
                formData.append('item_name', searchTerm);

                $.ajax({
                    url: grocery_germany_api_url + 'suggesstion-list',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    method: 'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        if (response.status === "success") {
                            $('#suggestionList').empty().show();
                            response.data.forEach(function (suggestion, index) {
                                $('#suggestionList').append(
                                    `<li class="list-group-item suggestion-item" data-id="${suggestion.id}" data-index="${index}">${suggestion.item_name}</li>`
                                );
                            });
                            selectedSuggestionIndex = -1; // Reset index on new search
                        } else {
                            $('#suggestionList').hide();
                        }
                    },
                    error: function () {
                        $('#suggestionList').hide();
                    }
                });
            } else {
                $('#suggestionList').hide();
            }

        });

        // Hide suggestions when clicking outside
        $(document).on('click', function (event) {
            if (!$(event.target).closest('#searchInput, #suggestionList').length) {
                $('#suggestionList').hide();
            }
        });

        // Select suggestion on click
        $(document).on('click', '.suggestion-item', function () {
            const selectedSuggestion = $(this).text();
            $('#searchInput').val(selectedSuggestion);
            getRelatedUnit($(this).data('id'));
            searh_itemID = $(this).data('id');
            $('#suggestionList').empty();
        });



        // $(document).on('keydown', '#searchInput', function (event) {
        //     const suggestionItems = $('.suggestion-item');

        //     if (suggestionItems.length === 0) return;

        //     if (event.keyCode === 40) { // Down arrow key
        //         selectedSuggestionIndex = (selectedSuggestionIndex + 1) % suggestionItems.length;
        //         updateActiveSuggestion(suggestionItems);
        //         scrollIntoView(suggestionItems.eq(selectedSuggestionIndex));
        //     } else if (event.keyCode === 38) { // Up arrow key
        //         selectedSuggestionIndex = (selectedSuggestionIndex - 1 + suggestionItems.length) % suggestionItems.length;
        //         updateActiveSuggestion(suggestionItems);
        //         scrollIntoView(suggestionItems.eq(selectedSuggestionIndex));
        //     } else if (event.keyCode === 13) { // Enter key
        //         event.preventDefault();
        //         if (selectedSuggestionIndex !== -1) {
        //             suggestionItems.eq(selectedSuggestionIndex).trigger('click');
        //         }
        //     }
        // });


        $(document).on('keydown', '#searchInput', function (event) {
            const suggestionItems = $('.suggestion-item');

            if (suggestionItems.length === 0) return;

            if (event.keyCode === 40) { // Down arrow key
                selectedSuggestionIndex = (selectedSuggestionIndex + 1) % suggestionItems.length;
                updateActiveSuggestion(suggestionItems);
                scrollIntoView(suggestionItems.eq(selectedSuggestionIndex));
            } else if (event.keyCode === 38) { // Up arrow key
                selectedSuggestionIndex = (selectedSuggestionIndex - 1 + suggestionItems.length) % suggestionItems.length;
                updateActiveSuggestion(suggestionItems);
                scrollIntoView(suggestionItems.eq(selectedSuggestionIndex));
            } else if (event.keyCode === 13) { // Enter key
                event.preventDefault(); // Prevent form submission or other default behavior
                if (selectedSuggestionIndex !== -1) {
                    const selectedItem = suggestionItems.eq(selectedSuggestionIndex);
                    const selectedSuggestion = selectedItem.text();
                    const itemId = selectedItem.data('id');

                    // Mimic the click event behavior
                    $('#searchInput').val(selectedSuggestion);
                    getRelatedUnit(itemId); // Call the function to get related unit
                    searh_itemID = itemId;  // Update the global variable
                    $('#suggestionList').empty().hide(); // Clear and hide the suggestion list
                    selectedSuggestionIndex = -1; // Reset the index
                }
            }
        });


        // Highlight and select suggestion on mouse hover
        $(document).on('mouseenter', '.suggestion-item', function () {
            $('.suggestion-item').removeClass('active');
            $(this).addClass('active');
        });


        // Select suggestion on click
        $(document).on('click', '.suggestion-item', function () {
            const selectedSuggestion = $(this).text();
            $('#searchInput').val(selectedSuggestion);
            selectedSuggestionIndex = -1; // Reset index after selection
            $('#suggestionList').hide();
        });

        // Function to highlight the active suggestion and update input box
        function updateActiveSuggestion(suggestionItems) {
            suggestionItems.removeClass('active');

            if (selectedSuggestionIndex >= 0 && selectedSuggestionIndex < suggestionItems.length) {
                let selectedItem = suggestionItems.eq(selectedSuggestionIndex);
                selectedItem.addClass('active');

                // Show the selected item in the input box
                $('#searchInput').val(selectedItem.text());
            }
        }

        // Function to scroll into view when navigating
        function scrollIntoView(selectedItem) {
            let suggestionList = $('#suggestionList');

            let itemTop = selectedItem.position().top;
            let itemBottom = itemTop + selectedItem.outerHeight();
            let containerScrollTop = suggestionList.scrollTop();
            let containerHeight = suggestionList.outerHeight();

            if (itemBottom > containerHeight) {
                // Scroll down
                suggestionList.scrollTop(containerScrollTop + (itemBottom - containerHeight));
            } else if (itemTop < 0) {
                // Scroll up
                suggestionList.scrollTop(containerScrollTop + itemTop);
            }
        }

        // CSS for highlighting selected item
        $('<style>')
            .prop('type', 'text/css')
            .html(`
        .suggestion-item.active {
            background-color: #007bff;
            color: white;
        }
    `)
            .appendTo('head');


        // ------------------------------------- UPDATED CODE --------------------------------------



        function getRelatedUnit(item_id) {
            var formData = new FormData();
            formData.append('item_id', item_id);

            $.ajax({
                url: grocery_germany_api_url + 'related-units',
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
                        console.log('units', response);

                        $('.relatedUnit').html('');

                        if (response.units.length >= 2) {
                            $('.relatedUnit').html(
                                '<option selected disabled>Unit</option>');
                        }

                        for (var i = 0; i < response.units.length; i++) {

                            if (response.units.length <= 1) {
                                $('.relatedUnit').append(
                                    '<option selected value="' +
                                    response.units[
                                    i] + '">' + response.units[i] +
                                    '</option>');
                            } else {
                                $('.relatedUnit').append('<option value="' +
                                    response
                                        .units[
                                    i] + '">' + response.units[i] +
                                    '</option>');
                            }
                        }
                        item_unit = response.item_unit;
                        $(".overlay").hide();
                    }
                },
                error: function (xhr, status, error) {
                    // Handle the error response
                    $(".overlay").hide();
                    var error = JSON.parse(xhr.responseText);
                    console.error(error);
                    handleErrors(error);
                }
            });
            // on change item get related units list for quantity
        }

        // ------------------------------------- SEARCH ITEM --------------------------------------

        // ------------------------------------- CONVERT TIME FORMAT --------------------------------------

        function convertDateFormats(dateString) {

            var originalDateStr = dateString;
            var originalDate = new Date(originalDateStr);

            // var convertedDateStr = ('0' + originalDate.getDate()).slice(-2) + '/' + ('0' + (originalDate
            //     .getMonth() + 1)).slice(-2) + '/' + originalDate.getFullYear() + ' - ' + ('0' + (
            //         originalDate.getHours() % 12 || 12)).slice(-2) + ':' + ('0' + originalDate.getMinutes())
            //             .slice(-2) + ':' + ('0' + originalDate.getSeconds()).slice(-2) + ' ' + (originalDate
            //                 .getHours() >= 12 ? 'PM' : 'AM');

            var convertedDateStr = ('0' + originalDate.getDate()).slice(-2) + '/' + ('0' + (originalDate
                .getMonth() + 1)).slice(-2) + '/' + originalDate.getFullYear() + ' - ' + ('0' + (
                    originalDate.getHours() % 12 || 12)).slice(-2) + ':' + ('0' + originalDate.getMinutes())
                        .slice(-2) + ' ' + (originalDate
                            .getHours() >= 12 ? 'PM' : 'AM');
            return convertedDateStr;
        }
        // ------------------------------------- CONVERT TIME FORMAT --------------------------------------

        // $(document).on('keypress', '.numericField', function (event) {
        //     // Allow only numbers and decimal point
        //     var charCode = (event.which) ? event.which : event.keyCode;
        //     if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
        //         // Prevent non-numeric input and display an alert
        //         // alert("Please enter only numbers and decimals.");
        //         $.toast({
        //             heading: 'Error',
        //             text: "Only numeric value are allow",
        //             icon: 'error',
        //             loader: true,
        //             position: 'top-right',
        //             loaderBg: '#9EC600'
        //         });
        //         return false;
        //     }

        //     // Allow decimal point only once
        //     if (charCode == 46 && $(this).val().indexOf('.') !== -1) {
        //         return false;
        //     }
        // });

        $(document).on('keypress', '.numericField', function (event) {
            var decimalSeparator = country_details.decimal_separator;
            var inputValue = $(this).val();

            // Call validate function
            if (!validateInput(event.which, decimalSeparator, inputValue)) {
                return false;
            }
        });


        // Function to validate based on decimal separator
        function validateInput(charCode, decimalSeparator, inputValue) {
            // Allow numbers, comma (44), and dot (46)
            if (charCode != 44 && charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
                $.toast({
                    heading: 'Error',
                    text: "Only numeric values, dots, and commas are allowed",
                    icon: 'error',
                    loader: true,
                    position: 'top-right',
                    loaderBg: '#9EC600'
                });
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



        // ------------------------------------------------------- COMMON FUNCTIONS ------------------------------------------------------


        // ------------------------------------------------------- CHANGE PASSWORD ------------------------------------------------------
        $(document).on('click', '.change-password-link', function (event) {
            $("#changePasswordModal").modal('show');
        });

        // ------------------------------------------------------- CHANGE PASSWORD ------------------------------------------------------

    </script>

    <script src="{{asset('assets/js/changePassword.js')}}"></script>
    <!-- ########################################################## LOGIN CCHECK ###################################################### -->


    <script>

        // -------------------------------------------------------GENERATE OTP  ------------------------------------------------------
        $('.generateOtpForm').click(function (e) {
            e.preventDefault(); // Prevent default form submission
            generateOrVerifyOTP("send-otp", "change", "change_password", "changePasswordModal");
        });
        // -------------------------------------------------------GENERATE OTP  ------------------------------------------------------


        // ------------------------------------------------------- VERIFY OTP -------------------------------------------------------
        // $('#otp-verify-form').submit(function (e) {
        //     e.preventDefault(); // Prevent default form submission
        //     generateOrVerifyOTP("verify-otp", "change");
        // });

        if (!window.location.pathname.includes('/profile')) {

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
                    generateOrVerifyOTP("verify-otp", "change", "", "changePasswordSection");
                }
            });

        }
        // ------------------------------------------------------- VERIFY OTP ------------------------------------------------------

        // ------------------------------------------------------- RESEND OTP ------------------------------------------------------
        $('#resend-otp-btn').on('click', function (e) {
            e.preventDefault();

            const phoneNumber = $('input[name="phone_number"]').val();
            reSendOTP("change_password", phoneNumber);
        });
        // ------------------------------------------------------- RESEND OTP ------------------------------------------------------


        // ------------------------------------------------------- PASSWORD VALIDATOIN ------------------------------------------------------
        function passwordValidation(password, loc) {

            const minLength = 8;

            console.log(password.length);

            if (password.length < minLength) {

                console.log(password, loc);

                var errorMessage = 'Password must be minimum 8 character';
                // $(".attachment-error").text(message);
                // $(".attachment-error").removeClass("d-none");
                // $(".askYourQuestionsSubmitButton").prop('disabled', true);

                $("#" + loc + "_password").addClass('border border-2 border-danger');
                $("." + loc + "_password-error").removeClass('d-none');
                $("." + loc + "_password-error").text(errorMessage);

                if (loc == 'nu') {
                    $("." + loc + "_createNewUser").prop('disabled', true);
                }
                else if (loc = "sub_user") {
                    $("." + loc + "_updateBtn").prop('disabled', true);
                }
                // return;
            }
            else {
                // $("."+loc+"_createNewUser").prop('disabled', false);

                if (loc == 'nu') {
                    $("." + loc + "_createNewUser").prop('disabled', false);
                }
                else if (loc = "sub_user") {
                    $("." + loc + "_updateBtn").prop('disabled', false);
                }

                $("#" + loc + "_password").removeClass('border border-2 border-danger');
                $("." + loc + "_password-error").addClass('d-none');
            }
        }
        // ------------------------------------------------------- PASSWORD VALIDATOIN ------------------------------------------------------


        // ------------------------------------------------------- NOTIFICATIONS ------------------------------------------------------
        function notifications() {

            $.ajax({
                url: api_url + 'notifications',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                type: 'GET',
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $(".overlay").show();
                },
                success: function (response) {
                    if (response) {

                        console.log('notifications', response);

                        if ((response.notifications.length > 0) && (response.isSubscriptionExpired == 0)) {
                            // $(".main .error-div").not(".modal-body .error-div").removeClass('d-none');
                            $(document).find("#subscripitonErrorId").removeClass('d-none');
                            response.notifications.forEach(notification => {

                                // $(".main .error-div .error-body").html(
                                //     $('<div class="d-flex justify-content-between align-items-center">')
                                //         .append($('<p class="my-1 text-danger">' + notification.message + '</p>'))
                                //         .append($('<a href="/subscription" class="btn btn-success">Renew Subscription</a>'))
                                // );


                                $(document).find("#subscripitonErrorId .error-body").html(
                                    $('<div class="d-flex justify-content-between align-items-center">')
                                        .append($('<p class="my-1 text-danger">' + notification.message + '</p>'))
                                        .append($('<a href="/subscription" class="btn btn-success">' + @json(__('common.Renew Subscription')) + '</a>'))
                                );

                            });

                            checkCookiesToShowSubscripitionAlert();

                        }

                        // else {
                        //     $(".main .error-div").not(".modal-body .error-div").addClass('d-none');
                        // }


                        $(".overlay").hide();


                    }
                },
                error: function (xhr, status, error) {
                    // Handle the error response
                    $(".overlay").hide();
                    var error = JSON.parse(xhr.responseText);
                    console.error(error);
                }
            });
            // on change item get related units list for quantity
        }

        notifications();
        // ------------------------------------------------------- NOTIFICATIONS ------------------------------------------------------


        // ------------------------------------------------------- ERROR CLOSE BUTTON ------------------------------------------------------
        $(document).on('click', '.close-error-btn', function () {
            $(this).closest('.error-div').addClass('d-none');

            $.cookie('subscriptionAlert', 1, {
                expires: 1
                // expires: 1 / 1440, // Expiry in 1 minute (1 minute = 1/1440 day)
            });

        });

        // IF SUBSCRIPTION ALERT CLOSE BUTTON IS CLICKED THEN MESSAGE WILL NOT BE DISPLAY FOR 1 DAY
        function checkCookiesToShowSubscripitionAlert() {

            console.log('subscriptionAlert', $.cookie('subscriptionAlert'));

            if ($.cookie('subscriptionAlert') && $.cookie('subscriptionAlert') == 1) {
                // $('.main .error-div').addClass('d-none');
                $(".main .error-div").not(".modal-body .error-div").addClass('d-none');
            }
        }
        // checkCookiesToShowSubscripitionAlert();
        // IF SUBSCRIPTION ALERT CLOSE BUTTON IS CLICKED THEN MESSAGE WILL NOT BE DISPLAY FOR 1 DAY

        // ------------------------------------------------------- ERROR CLOSE BUTTON ------------------------------------------------------


        // ------------------------------------------------------- CURRECNY FUNCTION ------------------------------------------------------
        function currency(price) {

            // console.log('country_details', country_details);

            let decimalSeparator = country_details.decimal_separator;
            let currencySymbol = country_details.currency_symbol;


            // Default currency symbol to '$' if not present
            if (!currencySymbol) {
                currencySymbol = '$';
            }

            let formattedPrice = '0.00';
            if (!isNaN(price)) {
                // Convert price to a fixed 2 decimal places and replace '.' with the decimal separator
                formattedPrice = Number(price).toFixed(2).replace('.', decimalSeparator);
            }

            return `${currencySymbol}${formattedPrice}`;
        }


        function currencySymbol() {

            let currencySymbol = country_details.currency_symbol;

            // console.log('currencySymbol',country_details.currency_symbol);

            // Default currency symbol to '$' if not present
            if (!currencySymbol) {
                currencySymbol = '$';
            }

            return currencySymbol;
        }


        function updateRate(price) {

            // console.log('updateRate', price);

            let decimalSeparator = country_details.decimal_separator;

            let formattedPrice = '0.00';
            if (!isNaN(price)) {
                // Convert price to a fixed 2 decimal places and replace '.' with the decimal separator
                formattedPrice = Number(price).toFixed(2).replace('.', decimalSeparator);
            }

            return formattedPrice;
        }



        function convertPriceToNumber(price) {

            let decimalSeparator = country_details.decimal_separator;

            if (decimalSeparator === ',') {
                // Convert "45,550" to "45550" (thousands separator)
                price = price.replace(/\./g, ''); // Remove thousands separator (if any)
                price = price.replace(',', '.'); // Convert decimal separator
            } else if (decimalSeparator === '.') {
                // Convert "45.550" to 45.550
                price = price.replace(/,/g, ''); // Remove thousands separator
            }

            let numericPrice = parseFloat(price);
            return isNaN(numericPrice) ? 0 : numericPrice.toFixed(2);
        }


        function setCurrency() {

            let currency = currencySymbol();

            $(".currency").text(currency);
        }

        // function convertPriceToNumber(price) {
        //     if (price == null) price = "0"; // Handle null or undefined
        //     price = price.toString(); // Convert to string to prevent errors

        //     let decimalSeparator = country_details.decimal_separator;

        //     if (decimalSeparator === ',') {
        //         price = price.replace(/\./g, ''); // Remove thousands separator
        //         price = price.replace(',', '.');  // Convert decimal separator
        //     } else if (decimalSeparator === '.') {
        //         price = price.replace(/,/g, ''); // Remove thousands separator
        //     }

        //     let numericPrice = parseFloat(price);
        //     return isNaN(numericPrice) ? 0 : numericPrice.toFixed(2);
        // }

        // ------------------------------------------------------- CURRECNY FUNCTION ------------------------------------------------------


        // ------------------------------------------------------- SELL & REFUND CANCEL BUTTON FUNCTION ------------------------------------------------------
        $(document).on('click', '.deleteAllItems', function () {


            let location = $(this).data('location');

            $.ajax({
                url: grocery_germany_api_url + 'delete-all-items-from-cart/' + location,
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                type: 'GET',
                contentType: false,
                processData: false,
                success: function (response) {

                    console.log('deleteItemFromCart', response);

                    if (response && response.status == "success") {
                        window.location.href = base_url_with_country_lang + location;
                    }
                },
                error: function (xhr, status, error) {
                    // Handle the error response
                    $(".overlay").hide();
                    var error = JSON.parse(xhr.responseText);
                    console.error(error);
                }
            });
        });
        // ------------------------------------------------------- SELL & REFUND CANCEL BUTTON FUNCTION ------------------------------------------------------

        // ------------------------------------------------------- ON CHANGE LANGUAGE CODE ------------------------------------------------------
        $(document).on('change', '.languageSelect', function () {
            var selectedLanguage = $(this).val(); // Get the selected language code
            var formData = new FormData();
            formData.append('lang', selectedLanguage);

            $.ajax({
                url: api_url + 'switch-language',
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

                        // Get the current URL and split it
                        var currentUrl = window.location.pathname;
                        var urlParts = currentUrl.split('/');

                        localStorage.setItem('language', selectedLanguage);

                        // Replace the language segment (assuming it's the second segment, e.g., 'en')
                        if (urlParts[2]) {
                            urlParts[2] = selectedLanguage; // Update language code
                            var newUrl = base_url + urlParts.join('/');

                            window.location.href = newUrl; // Redirect to updated URL

                        } else {
                            // Fallback if URL structure is unexpected
                            window.location.href = base_url_with_country_lang + 'profile';
                        }

                        console.log('units', response);
                        $(".overlay").hide();
                    }
                },
                error: function (xhr, status, error) {
                    $(".overlay").hide();
                    var errorResponse = JSON.parse(xhr.responseText);
                    console.error(errorResponse);
                }
            });
        });
        // ------------------------------------------------------- ON CHANGE LANGUAGE CODE ------------------------------------------------------


        window.firebaseConfig = {
            apiKey: '{{ env('FIREBASE_API_KEY') }}',
            authDomain: '{{ env('FIREBASE_AUTH_DOMAIN') }}',
            databaseURL: '{{ env('FIREBASE_DATABASE_URL') }}',
            projectId: '{{ env('FIREBASE_PROJECT_ID') }}',
            storageBucket: '{{ env('FIREBASE_STORAGE_BUCKET') }}',
            messagingSenderId: '{{ env('FIREBASE_MESSAGING_SENDER_ID') }}',
            appId: '{{ env('FIREBASE_APP_ID') }}'
        };



    </script>

    @show

    <!-- Vendor JS Files -->
    <!-- <script src="{{asset('assets/vendor/apexcharts/apexcharts.min.js')}}"></script>
    <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/vendor/chart.js/chart.umd.js')}}"></script>
    <script src="assets/vendor/echarts/echarts.min.js"></script>
    <script src="{{asset('assets/vendor/quill/quill.min.js')}}"></script>

    <script src="{{asset('assets/vendor/php-email-form/validate.js')}}"></script> -->

    <!-- Template Main JS File -->
    <script src="{{asset('assets/js/main.js')}}"></script>

    <!-- Datatable -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/scroller/2.4.3/js/dataTables.scroller.min.js"></script>
    <!-- Datatable -->

    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <script src="{{asset('assets/js/common.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>
   <script src="{{asset('assets/js/validation.js')}}?v={{ env('APP_VERSION', '1.0.0') }}"></script>
</body>

</html>