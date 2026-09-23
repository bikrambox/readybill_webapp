<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS (5.0.2) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon/16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/32.png') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('assets/img/favicon/64.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('assets/img/favicon/512.png') }}">

    <!-- Optional: modern font for headings -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700&display=swap" rel="stylesheet">

    <title>Ready Bill Admin</title>

    <style>
        :root {
            --rb-primary: #0d6efd;
            --rb-bg1: #f7f9fc;
            --rb-bg2: #eef3ff;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(1200px 800px at 80% -10%, var(--rb-bg2), transparent 60%),
                radial-gradient(900px 600px at -10% 120%, var(--rb-bg1), transparent 60%),
                #f8f9fa;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            padding: 1.75rem;
            background-color: #fff;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(13, 110, 253, 0.08), 0 6px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(13, 110, 253, .06);
        }

        /* Header/logo and titles */
        .logo {
            max-height: 56px;
            height: auto;
            object-fit: contain;
        }

        .login-title {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif;
            letter-spacing: 0.3px;
            font-weight: 700;
            line-height: 1.1;
        }

        .text-gradient {
            color: #0d6efd;
            /* fallback solid color */
            background-image: linear-gradient(90deg, #0d6efd, #5aa0ff 60%, #6f42c1);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            letter-spacing: 0.2px;
        }

        /* Form cosmetics */
        .form-label {
            font-weight: 600;
            color: #495057;
        }

        .input-group-text {
            background: #f8f9fa;
        }

        .togglePasswordBtn {
            border: 0;
            background: transparent;
            padding: 0;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
        }

        /* Button and loading state */
        .btn-primary {
            box-shadow: 0 8px 20px rgba(13, 110, 253, .25);
        }

        .btn-primary:disabled {
            opacity: .85;
        }

        /* Footer small print */
        .form-footnote {
            font-size: .9rem;
        }

        /* Override browser autofill yellow background */
        .form-control:-webkit-autofill,
        .form-control:-webkit-autofill:hover,
        .form-control:-webkit-autofill:focus,
        .form-control:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0px 1000px #ffffff inset !important;
            box-shadow: 0 0 0px 1000px #ffffff inset !important;
            -webkit-text-fill-color: #1a1a1a !important;
            transition: background-color 5000s ease-in-out 0s;
        }

    </style>
</head>

<body>

    <div class="login-card">
        <!-- Logo -->
        <div class="text-center mb-3">
            <img src="{{ asset('assets/img/readybill.png') }}" alt="Ready Bill" class="logo img-fluid mx-auto d-block"
                width="140" height="40" loading="eager">
        </div>

        <!-- Attractive title and subtitle -->
        <h1 class="text-center login-title display-6 text-gradient mb-1">Login</h1>
        <p class="text-center text-muted lead subtitle mt-0 mb-4">Access your Ready Bill dashboard</p>

        <form action="{{ route('admin.login') }}" id="loginForm" method="POST" novalidate>
            @csrf

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email ID</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="name@company.com"
                    aria-describedby="emailHelp" required autofocus autocomplete="off">
                <!-- <div id="emailHelp" class="form-text text-muted">Use your work email for administrator access.</div> -->
                <div class="invalid-feedback" id="email-error"></div>
            </div>

            <!-- Password with toggle -->
            <div class="mb-2">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Enter your password" aria-describedby="passwordHelp" required autocomplete="off" />
                    <span class="input-group-text">
                        <button type="button" class="togglePasswordBtn" aria-label="Toggle password visibility"
                            data-target="#password">
                            <img class="togglePasswordIcon" src="{{ asset('assets/img/icons/eye.svg') }}" alt=""
                                width="18" height="18">
                        </button>
                    </span>
                    <div class="invalid-feedback" id="password-error"></div>
                </div>
                <!-- <div id="passwordHelp" class="form-text text-muted">At least 8 characters, including a number and a
                    letter.</div> -->
            </div>

            <!-- Remember + Forgot -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <!-- <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                    <label class="form-check-label text-secondary" for="remember">Remember me</label>
                </div>
                <a href="" class="text-decoration-none">Forgot password?</a> -->
            </div>

            <!-- Submit -->
            <button type="submit" id="btnSubmit" class="btn btn-primary w-100">
                <span class="btn-text">Login</span>
                <span class="btn-spinner ms-2 d-none align-middle">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </span>
            </button>

            <!-- Small print -->
            <!-- <p class="text-center text-muted form-footnote mt-3 mb-0">
                By continuing, you agree to our
                <a href="" class="text-decoration-none">Terms</a> and
                <a href="" class="text-decoration-none">Privacy Policy</a>.
            </p> -->

        </form>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>

    <!-- jQuery + SweetAlert2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('assets/js/common.js') }}"></script>

    <script>
        $(function () {
            const $form = $('#loginForm');
            const $btn = $('#btnSubmit');
            const $btnText = $btn.find('.btn-text');
            const $btnSpinner = $btn.find('.btn-spinner');

            // Toggle password visibility
            $(document).on('click', '.togglePasswordBtn', function () {
                const target = $(this).data('target');
                const $input = $(target);
                const isPwd = $input.attr('type') === 'password';
                $input.attr('type', isPwd ? 'text' : 'password');
            });

            // Clear validation on input
            $form.on('input change', 'input', function () {
                $(this).removeClass('is-invalid');
                const id = $(this).attr('id');
                $('#' + id + '-error').text('');
            });

            $form.on('submit', function (e) {
                e.preventDefault();

                const url = $(this).attr('action');
                const method = $(this).attr('method') || 'POST';
                const formData = new FormData(this);

                // Reset messages
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.invalid-feedback').text('');

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $btn.prop('disabled', true);
                        $btnText.text('Please wait…');
                        $btnSpinner.removeClass('d-none');
                    },
                    success: function (data) {

                        console.log('data', data);

                        if (data.status && data.status == 'success') {
                            window.location.replace("{{ route('admin.dashboard') }}");
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Login failed.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                        $btnText.text('Login');
                        $btnSpinner.addClass('d-none');
                    },
                    error: function (xhr) {

                        console.log(xhr);

                        if (xhr.status === 401) {
                            Swal.fire({
                                title: 'Invalid Credentials!',
                                text: 'The email or password you entered is incorrect.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        } else if (xhr.status === 422) {
                            const errors = xhr.responseJSON?.errors || {};
                            Object.keys(errors).forEach(function (key) {
                                const msg = errors[key][0];
                                const $field = $('#' + key);
                                $field.addClass('is-invalid');

                                console.log( $('#' + key + '-error').length );

                                $('#' + key + '-error').text(msg);
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'An unexpected error occurred. Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    
                });
            });
        });
    </script>
</body>

</html>