<!DOCTYPE html>
<html>

<head>
    <title>New Contact Form Submission</title>
    <style>
        /* Inline styles for email compatibility */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #343a40;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            text-align: center;
            background-color: #0d6efd;
            color: #ffffff;
            padding: 10px 0;
            border-radius: 8px 8px 0 0;
        }

        .email-header h1 {
            margin: 0;
            font-size: 20px;
        }

        .email-body {
            padding: 20px;
        }

        .email-body p {
            margin: 10px 0;
            font-size: 16px;
            line-height: 1.5;
        }

        .email-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>Contact Information</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <p><strong>Full Name:</strong> {{ $formData['full_name'] }}</p>
            <p><strong>Contact No:</strong> {{ $formData['contact_no'] }}</p>
            <p><strong>Email:</strong> {{ $formData['email'] }}</p>
            <p><strong>Message:</strong></p>
            <p style="border-left: 4px solid #0d6efd; padding-left: 10px; background-color: #f1f3f5;">
                {{ $formData['message'] }}
            </p>
        </div>

        <!-- Footer -->
        <!-- <div class="email-footer">
            <p>This email was generated automatically. Please do not reply to this email.</p>
        </div> -->
    </div>
</body>

</html>