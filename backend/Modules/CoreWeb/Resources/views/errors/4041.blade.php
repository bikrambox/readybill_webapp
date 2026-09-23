<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .error-container {
            text-align: center;
        }
        .error-code {
            font-size: 100px;
            font-weight: bold;
            color: #dc3545;
        }
        .error-message {
            font-size: 24px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center">
        <div class="error-container">
            <div class="error-code">404</div>
            <div class="error-message">{{ __('common.Oops! Page Not Found') }}</div>
            <p class="text-muted">{{ __('common.The page you are looking for might have been removed or is temporarily unavailable') }}.</p>
            <a href="{{ url('/') }}" class="btn btn-primary">{{ __('common.Go to Homepage') }}</a>
        </div>
    </div>
</body>
</html>
