<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F8FAFC; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #0F172A;">
    
    <!-- Wrapper Table -->
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #F8FAFC; padding: 32px 16px;">
        <tr>
            <td align="center">
                
                <!-- Main Card Container -->
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 520px; background-color: #FFFFFF; border-radius: 16px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td style="background-color: #ffffff; padding: 24px 32px; text-align: left;">
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <img src="{{ $message->embed(public_path('images/LODISv2.png')) }}" 
                                             alt="LODISv2 - Lendell Online Digital Interactive System" 
                                             style="max-height: 48px; width: auto; display: block; border: 0; outline: none; text-decoration: none;">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px;">
                            <h2 style="margin: 0 0 12px 0; font-size: 18px; font-weight: 700; color: #0F172A;">
                                Password Reset Request
                            </h2>
                            
                            <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #334155;">
                                Hello <strong style="color: #0F172A;">{{ $name }}</strong>,
                            </p>
                            
                            <p style="margin: 0 0 24px 0; font-size: 14px; line-height: 1.6; color: #334155;">
                                You are receiving this email because we received a password reset request for your LODISv2 account. Click the button below to establish a new password:
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $url }}" target="_blank" style="display: inline-block; background-color: #00687A; color: #FFFFFF; font-size: 14px; font-weight: 600; text-decoration: none; padding: 14px 28px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0, 104, 122, 0.2);">
                                            Reset Account Password
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Expiry Warning -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 24px; background-color: #FFFBEB; border-left: 4px solid #F59E0B; border-radius: 0 8px 8px 0;">
                                <tr>
                                    <td style="padding: 12px 16px;">
                                        <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #92400E;">
                                            <strong>Time Sensitive:</strong> This password reset link will expire in <strong>{{ $expire }} minutes</strong>.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 20px 0; font-size: 13px; line-height: 1.5; color: #64748B;">
                                If you did not request a password reset, no further action is required. Your account security remains intact.
                            </p>

                            <!-- Raw Link Backup -->
                            <p style="margin: 0; font-size: 11px; line-height: 1.5; color: #94A3B8; word-break: break-all;">
                                Having trouble with the button? Copy and paste this URL into your browser:<br>
                                <a href="{{ $url }}" style="color: #00687A; text-decoration: underline;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding: 0 32px;">
                            <hr style="border: none; border-top: 1px solid #F1F5F9; margin: 0;">
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 32px; background-color: #FAFAFA; text-align: center;">
                            <p style="margin: 0 0 6px 0; font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">
                                Confidential Internal System
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #94A3B8;">
                                &copy; {{ date('Y') }} Lendell. All rights reserved. Managed by System Administration.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>