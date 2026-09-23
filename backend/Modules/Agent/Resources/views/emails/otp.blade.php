<!DOCTYPE html>
<html>

<head>
    <title>Readybill Agents - OTP Verification</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f9; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#28a745; padding: 32px 40px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:24px; font-weight:700; letter-spacing:0.5px;">
                                Readybill Agents
                            </h1>
                            <p style="margin:8px 0 0; color:#d4edda; font-size:14px;">OTP Verification</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 40px 32px;">
                            <h2 style="margin:0 0 12px; color:#1a1a2e; font-size:20px;">Password Change Request 🔐</h2>
                            <p style="margin:0 0 16px; color:#555555; font-size:15px; line-height:1.7;">
                                We received a request to change your <strong>Readybill Agent</strong> account password.
                                Use the OTP below to confirm this request.
                            </p>
                            <p style="margin:0 0 16px; color:#555555; font-size:15px; line-height:1.7;">
                                This OTP is valid for a limited time. Do not share it with anyone.
                            </p>

                            <!-- OTP Box -->
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding: 8px 0 32px;">
                                        <div style="display:inline-block; background-color:#f0faf3; border: 2px dashed #28a745;
                                                    border-radius:8px; padding: 20px 48px; text-align:center;">
                                            <p
                                                style="margin:0 0 6px; color:#555555; font-size:13px; letter-spacing:0.5px; text-transform:uppercase;">
                                                Your One-Time Password
                                            </p>
                                            <p
                                                style="margin:0; color:#28a745; font-size:36px; font-weight:700; letter-spacing:10px;">
                                                {{ $otp }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0; color:#999999; font-size:13px; line-height:1.6; text-align:center;">
                                If you did not request a password change, please ignore this email or contact us
                                immediately.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="background-color:#f8f9fa; padding: 24px 40px; text-align:center; border-top:1px solid #e9ecef;">
                            <p style="margin:0; color:#aaaaaa; font-size:12px; line-height:1.6;">
                                &copy; {{ date('Y') }} Readybill. All rights reserved.<br>
                                If you have any questions, contact us at
                                <a href="mailto:support@readybill.in"
                                    style="color:#28a745; text-decoration:none;">support@readybill.in</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>