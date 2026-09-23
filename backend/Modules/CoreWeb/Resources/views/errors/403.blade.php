<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <style>
        /* You can add custom CSS styles here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 36px;
            color: #333;
        }
        p {
            font-size: 18px;
            color: #666;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>403 - {{ __('common.Access Denied') }}</h1>
        <p>{{ __('common.Sorry, you dont have permission to access this page') }}.</p>
        <p>{{ __('common.If you believe this is a mistake, please contact the site administrator') }}.</p>
    </div>
</body>
</html>
