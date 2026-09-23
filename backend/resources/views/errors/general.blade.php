<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Something went wrong</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(13, 110, 253, 0.18), transparent 30%),
                radial-gradient(circle at bottom right, rgba(111, 66, 193, 0.16), transparent 28%),
                linear-gradient(135deg, #f8f9fa 0%, #eef2f7 100%);
            font-family: "Inter", "Segoe UI", Arial, sans-serif;
        }

        .error-section {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .error-card {
            max-width: 620px;
            border: 0;
            border-radius: 24px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(8px);
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.12);
        }

        .error-header {
            background: linear-gradient(135deg, #0d6efd, #6f42c1);
            padding: 2.5rem 2rem 4.5rem;
            position: relative;
            color: #fff;
            text-align: center;
        }

        .error-header::after {
            content: "";
            position: absolute;
            left: -10%;
            right: -10%;
            bottom: -45px;
            height: 90px;
            background: #fff;
            border-radius: 50%;
        }

        .icon-badge {
            width: 88px;
            height: 88px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
            font-size: 2rem;
        }

        .status-pill {
            display: inline-block;
            padding: .45rem .9rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: .9rem;
            font-weight: 600;
            letter-spacing: .3px;
        }

        .error-body {
            position: relative;
            z-index: 2;
            padding: 1.5rem 2rem 2.25rem;
            margin-top: -1.25rem;
            text-align: center;
        }

        .error-title {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
        }

        .error-text {
            color: #6c757d;
            font-size: 1rem;
            line-height: 1.7;
            max-width: 460px;
            margin: 0 auto;
        }

        .info-box {
            border: 1px solid rgba(13, 110, 253, 0.08);
            background: linear-gradient(180deg, #f8fbff 0%, #f3f7ff 100%);
            border-radius: 18px;
        }

        .btn-home {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border: 0;
            box-shadow: 0 10px 20px rgba(13, 110, 253, 0.22);
        }

        .btn-home:hover {
            background: linear-gradient(135deg, #0b5ed7, #0a58ca);
        }

        .btn-back {
            border-radius: 12px;
        }

        .mini-points i {
            color: #0d6efd;
        }

        @media (max-width: 576px) {
            .error-header {
                padding: 2rem 1.25rem 4rem;
            }

            .error-body {
                padding: 1.25rem 1.25rem 2rem;
            }

            .error-title {
                font-size: 1.6rem;
            }

            .icon-badge {
                width: 74px;
                height: 74px;
                font-size: 1.6rem;
            }
        }
    </style>
</head>

<body>
    <section class="container d-flex align-items-center justify-content-center error-section py-5">
        <div class="card error-card w-100">
            <div class="error-header">
                <div class="icon-badge">
                    <i class="bi bi-shield-exclamation"></i>
                </div>

                <div class="status-pill mb-3">
                    Error {{ $status ?? 500 }}
                </div>

                <h1 class="h2 fw-bold mb-2">We hit a small problem</h1>
                <p class="mb-0 text-white-50">
                    Don’t worry — your request could not be completed right now.
                </p>
            </div>

            <div class="error-body">
                <h2 class="error-title mb-3">Something went wrong</h2>

                <p class="error-text mb-4">
                    Please try again in a moment. If the issue continues, return to the dashboard or homepage and
                    continue from there.
                </p>

                <div class="info-box p-3 p-md-4 mb-4">
                    <div class="row g-3 text-start mini-points">
                        <div class="col-md-4">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-arrow-repeat fs-5"></i>
                                <div>
                                    <div class="fw-semibold">Try again</div>
                                    <small class="text-muted">Refresh and retry the action.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-house-door fs-5"></i>
                                <div>
                                    <div class="fw-semibold">Go home</div>
                                    <small class="text-muted">Return to a safe starting point.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-clock-history fs-5"></i>
                                <div>
                                    <div class="fw-semibold">Try later</div>
                                    <small class="text-muted">The issue may be temporary.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <a href="{{ url('/') }}" class="btn btn-primary btn-home px-4 py-2 rounded-3">
                        <i class="bi bi-house-door me-2"></i>Go to Home
                    </a>

                    <button type="button" onclick="history.back()" class="btn btn-outline-secondary btn-back px-4 py-2">
                        <i class="bi bi-arrow-left me-2"></i>Go Back
                    </button>
                </div>
            </div>
        </div>
    </section>
</body>

</html>