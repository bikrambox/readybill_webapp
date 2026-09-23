<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Readybill - Account Activated</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --green: #22c55e;
            --green-dark: #16a34a;
            --green-deeper: #15803d;
            --green-bg: #f0fdf4;
            --green-ring: #bbf7d0;
            --text-dark: #0f172a;
            --text-mid: #475569;
            --text-soft: #94a3b8;
            --border: #e2e8f0;
            --surface: #ffffff;
            --page-bg: #f1f5f9;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--page-bg);
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        .card {
            background: var(--surface);
            border-radius: 20px;
            width: 100%;
            max-width: 520px;
            overflow: hidden;
            box-shadow:
                0 1px 3px rgba(0, 0, 0, 0.06),
                0 8px 32px rgba(0, 0, 0, 0.08);
        }

        .stripe {
            height: 6px;
            background: linear-gradient(90deg, #16a34a 0%, #22c55e 50%, #4ade80 100%);
        }

        .content {
            padding: 48px 48px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .icon-ring {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: var(--green-bg);
            border: 6px solid var(--green-ring);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 28px;
            animation: scalePop 0.55s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
        }

        @keyframes scalePop {
            0% {
                transform: scale(0.4);
                opacity: 0;
            }

            70% {
                transform: scale(1.08);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .icon-ring svg {
            width: 38px;
            height: 38px;
            stroke: var(--green-dark);
            stroke-width: 2.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
            stroke-dasharray: 50;
            stroke-dashoffset: 50;
            animation: drawCheck 0.45s 0.4s ease forwards;
        }

        @keyframes drawCheck {
            to {
                stroke-dashoffset: 0;
            }
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            background: var(--green-bg);
            border: 1px solid var(--green-ring);
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--green-deeper);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 16px;
            animation: fadeUp 0.4s 0.6s ease both;
        }

        .badge-dot {
            width: 6px;
            height: 6px;
            background: var(--green);
            border-radius: 50%;
        }

        h1 {
            font-size: 26px;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.25;
            margin-bottom: 12px;
            animation: fadeUp 0.4s 0.7s ease both;
        }

        .subtitle {
            font-size: 15px;
            color: var(--text-mid);
            line-height: 1.7;
            max-width: 380px;
            margin-bottom: 36px;
            animation: fadeUp 0.4s 0.8s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .steps {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 36px;
            animation: fadeUp 0.4s 0.9s ease both;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .step-circle.done {
            background: var(--green-dark);
            color: #fff;
        }

        .step-circle.done svg {
            width: 14px;
            height: 14px;
            stroke: #fff;
            stroke-width: 2.5;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .step-circle.next {
            background: var(--green-bg);
            border: 2px dashed var(--green);
            color: var(--green-dark);
        }

        .step-label {
            font-size: 10.5px;
            font-weight: 500;
            color: var(--text-soft);
            white-space: nowrap;
        }

        .step-label.active {
            color: var(--green-dark);
            font-weight: 600;
        }

        .step-line {
            width: 48px;
            height: 2px;
            background: var(--green-ring);
            margin-bottom: 18px;
            flex-shrink: 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 36px;
            background: var(--green-dark);
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.01em;
            box-shadow: 0 1px 3px rgba(22, 163, 74, 0.2), 0 4px 16px rgba(22, 163, 74, 0.25);
            transition: background 160ms ease, transform 120ms ease, box-shadow 160ms ease;
            animation: fadeUp 0.4s 1.0s ease both;
            width: 100%;
            max-width: 320px;
        }

        .btn:hover {
            background: var(--green-deeper);
            transform: translateY(-2px);
            box-shadow: 0 2px 6px rgba(22, 163, 74, 0.22), 0 8px 24px rgba(22, 163, 74, 0.30);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn svg {
            width: 17px;
            height: 17px;
            stroke: #fff;
            stroke-width: 2.2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }

        .help {
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-soft);
            animation: fadeUp 0.4s 1.1s ease both;
        }

        .help a {
            color: var(--green-dark);
            font-weight: 500;
            text-decoration: none;
        }

        .help a:hover {
            text-decoration: underline;
        }

        .footer {
            border-top: 1px solid var(--border);
            background: #fafafa;
            padding: 18px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo-mark {
            width: 28px;
            height: 28px;
            background: var(--green-dark);
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-mark svg {
            width: 16px;
            height: 16px;
            stroke: #fff;
            stroke-width: 2.5;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .logo-text {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .footer-copy {
            font-size: 12px;
            color: var(--text-soft);
        }

        .footer-copy a {
            color: var(--green-dark);
            text-decoration: none;
        }

        @media (max-width: 540px) {
            .content {
                padding: 36px 28px 32px;
            }

            .footer {
                padding: 16px 28px;
                justify-content: center;
                text-align: center;
            }

            h1 {
                font-size: 22px;
            }

            .step-line {
                width: 28px;
            }
        }
    </style>
</head>

<body>

    <div class="card">
        <div class="stripe"></div>

        <div class="content">

            <div class="icon-ring">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </div>

            <div class="badge">
                <span class="badge-dot"></span>
                Account Verified
            </div>

            <h1>You're all set, welcome aboard!</h1>
            <p class="subtitle">
                Your <strong>Readybill Agent</strong> account is now active.
                Log in to access your dashboard and get started.
            </p>

            <div class="steps">
                <div class="step-item">
                    <div class="step-circle done">
                        <svg viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </div>
                    <span class="step-label">Register</span>
                </div>
                <div class="step-line"></div>
                <div class="step-item">
                    <div class="step-circle done">
                        <svg viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </div>
                    <span class="step-label">Verify Email</span>
                </div>
                <div class="step-line"></div>
                <div class="step-item">
                    <div class="step-circle done">
                        <svg viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </div>
                    <span class="step-label">Activated</span>
                </div>
                <div class="step-line"></div>
                <div class="step-item">
                    <div class="step-circle next">&#8594;</div>
                    <span class="step-label active">Login</span>
                </div>
            </div>

            <a href="{{ env('FRONTEND_URL') . '/in/en/authorized-agents/login' }}" class="btn">
                <svg viewBox="0 0 24 24">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                    <polyline points="10 17 15 12 10 7" />
                    <line x1="15" y1="12" x2="3" y2="12" />
                </svg>
                Go to Login
            </a>

            <p class="help">
                Need help? <a href="mailto:support@readybill.in">Contact support</a>
            </p>

        </div>

        <div class="footer">
            <div class="footer-brand">
                <div class="logo-mark">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="9" y1="13" x2="15" y2="13" />
                        <line x1="9" y1="17" x2="13" y2="17" />
                    </svg>
                </div>
                <span class="logo-text">Readybill Agents</span>
            </div>
            <span class="footer-copy">
                &copy; {{ date('Y') }} Readybill &nbsp;&bull;&nbsp;
                <a href="mailto:support@readybill.in">support@readybill.in</a>
            </span>
        </div>
    </div>

</body>

</html>