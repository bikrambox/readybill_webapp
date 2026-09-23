<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            background: url('/assets/img/404.jpg') no-repeat center center;
            background-size: cover;
            color: black;
            text-align: center;
            position: relative;
        }
        .error-title {
            font-size: 80px;
            font-weight: bold;
            position: absolute;
            top: 0%;
            left: 50%;
            transform: translateX(-50%);
        }
        .error-message {
            font-size: 40px;
            font-weight: bold;
            position: absolute;
            bottom: 2%;
            left: 50%;
            transform: translateX(-50%);
        }
        @media (max-width: 768px) {
            .error-title {
                font-size: 50px;
                top: 15%;
            }
            .error-message {
                font-size: 25px;
                bottom: 15%;
            }
        }
    </style>
</head>
<body>
    <div class="error-title">{{ __('common.404 NOT FOUND') }}</div>
    <div class="error-message">{{ __('common.Sorry! This page isnt available') }}</div>
</body>
</html>
