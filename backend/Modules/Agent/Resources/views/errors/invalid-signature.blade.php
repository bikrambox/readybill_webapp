<!DOCTYPE html>
<html>

<head>
    <title>Link Status</title>
</head>

<body style="font-family: Arial; text-align:center; padding:50px;">

    <h2 style="color:red;">⚠️ Link Issue</h2>

    <p>{{ $message ?? 'This activation link is either expired or invalid.' }}</p>

    <a href="/" style="padding:10px 20px; background:#28a745; color:#fff; text-decoration:none;">
        Go to Home
    </a>

</body>

</html>