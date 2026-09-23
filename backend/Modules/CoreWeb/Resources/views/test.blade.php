<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('assets/img/favicon/16.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('assets/img/favicon/32.png')}}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{asset('assets/img/favicon/64.png')}}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{asset('assets/img/favicon/512.png')}}">

    <!-- Toast -->
    <link rel="stylesheet" href="{{asset('assets/toast/css/jquery.toast.css')}}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i" rel="stylesheet">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- CSS Loader -->
    <link href="{{asset('assets/css/loader.css')}}" rel="stylesheet">

    <title>Ready Bill</title>

    <style>
        * {
            font-family: 'Roboto', sans-serif !important;
        }

        /* Style for the top-left logo */
        #topLogo {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 10;
        }

        /* Style for the bottom-left text */
        #bottomText {
            position: fixed;
            bottom: 20px;
            left: 20px;
            color: white;
            z-index: 10;
        }

        #bottomText h1{
            font-size: 60px !important;
        }

        #bottomText h2{
            font-size: 40px !important;
        }


        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            background-image: url('{{ asset("assets/img/main-photo.jpg") }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
        }

    </style>
</head>

<body>

    <!-- Top-left logo -->
    <div id="topLogo">
        <img src="{{ asset('assets/img/ready-bill-white-logo.png') }}" alt="Logo" width="220">
    </div>

    <!--################################################ MOBILE NUMBER CARD ################################################-->
    <div class="position-fixed top-0 end-0 h-100 p-4" style="width: 100%; max-width: 650px;" id="registerSection-1">
        <div class="card h-100 bg-light bg-opacity-90 shadow" style="border-radius: 25px;">
            <div class="card-body d-flex flex-column p-4 p-md-5">
                <!-- Main content centered vertically but left-aligned -->
                <div class="row flex-grow-1 align-items-center" id="sendOTPSection">
                    <div class="col-12">
                        <img src="{{asset('assets/img/favicon/64.png')}}" alt="probill" class="mb-4"
                            width="50">

                        <p class="fw-light mb-2">
                            Welcome to <span class="fw-normal text-primary">Ready Bill</span>
                        </p>

                        <h3 class="fw-medium mb-4">Get started with your phone number</h3>

                        @include('components/error', ['heading' => 'Error'])

                        <form id="registerOTpForm">
                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text">+91</span>
                                    <input type="text" class="form-control form-control-lg" id="mobile" name="mobile"
                                        placeholder="Mobile Number*" autofocus>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Continue</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Footer fixed at bottom -->
                <div class="row mt-auto">
                    <div class="col-12">
                        <hr class="mb-3">
                        <p class="text-center text-muted small mb-0">
                            By continuing you agree to our <strong><a href="/privacy-policy" target="_blank">Privacy
                                    Policy</a></strong> and <a href="/terms-and-conditions"
                                target="_blank"><strong>Terms of
                                    Use</strong></a></p>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--################################################ MOBILE NUMBER CARD ################################################-->

    <!-- Bottom-left text -->
    <div id="bottomText">
        <h1 class="mb-1 fs-3 fs-md-4 fs-lg-5">The <span style="color:#00D47C">fastest</span> billing app</h1>
        <h2 class="mb-0 fs-5 fs-md-6 fs-lg-7">...all from your mobile.</h2>
    </div>

</body>

</html>
