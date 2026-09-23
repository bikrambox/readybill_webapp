<!-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/js/app.js'])
</head>

<body>
    <div id="app">
        <test-web-socket></test-web-socket>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html> -->



<!DOCTYPE html>
<html>

<head>
    <style>
        .transition-new-row {
            animation: slideIn 0.5s ease-in-out forwards;
            -webkit-animation: slideIn 0.5s ease-in-out forwards;
            -moz-animation: slideIn 0.5s ease-in-out forwards;
        }

        @keyframes slideIn {
            0% {
                opacity: 0;
                transform: translateX(-20px);
            }

            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>
    <table>
        <tr class="transition-new-row">
            <td>Test Row</td>
        </tr>
    </table>
</body>

</html>