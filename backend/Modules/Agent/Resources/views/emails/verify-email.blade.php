<!DOCTYPE html>
<html>

<head>
    <title>Readybill Agents - Email Verification</title>
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
                            <p style="margin:8px 0 0; color:#d4edda; font-size:14px;">Email Verification</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 40px 32px;">
                            <h2 style="margin:0 0 12px; color:#1a1a2e; font-size:20px;">Welcome to Readybill! 👋</h2>
                            <p style="margin:0 0 16px; color:#555555; font-size:15px; line-height:1.7;">
                                Thank you for registering as a <strong>Readybill Agent</strong>. Your account has been
                                created successfully and is just one step away from being active.
                            </p>
                            <p style="margin:0 0 16px; color:#555555; font-size:15px; line-height:1.7;">
                                Please verify your email address by clicking the button below. This helps us confirm
                                your identity and secure your account.
                            </p>
                            <p style="margin:0 0 32px; color:#555555; font-size:15px; line-height:1.7;">
                                This verification link will expire in <strong>24 hours</strong>. If you did not create
                                an account with Readybill, you can safely ignore this email.
                            </p>

                            <!-- CTA Button -->
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $activationUrl }}" style="display:inline-block; padding:14px 36px; background-color:#28a745; color:#ffffff;
                                                   text-decoration:none; border-radius:6px; font-weight:600; font-size:15px;
                                                   letter-spacing:0.3px;">
                                            ✅ Activate My Account
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Fallback Link -->
                    <tr>
                        <td style="padding: 0 40px 32px;">
                            <p style="margin:24px 0 0; color:#999999; font-size:13px; line-height:1.6;">
                                If the button doesn't work, copy and paste the link below into your browser:
                            </p>
                            <p style="margin:6px 0 0; font-size:13px; word-break:break-all;">
                                <a href="{{ $activationUrl }}"
                                    style="color:#28a745;">
                                    {{ $activationUrl }}
                                </a>
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