<!DOCTYPE html>
<html lang="vi" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Xác nhận email - StudyHub</title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->
    <style>
        /* Reset */
        * { box-sizing: border-box; }
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; background-color: #0f172a; width: 100% !important; }

        /* Wrapper */
        .email-wrapper { width: 100%; background-color: #0f172a; padding: 24px 0; }
        .email-container { max-width: 560px; margin: 0 auto; background-color: #1e293b; border-radius: 16px; overflow: hidden; }

        /* Header */
        .header { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 32px 24px; text-align: center; }
        .header h1 { margin: 0 0 6px 0; color: #ffffff; font-size: 24px; font-weight: 700; font-family: Arial, sans-serif; }
        .header p  { margin: 0; color: rgba(255,255,255,0.75); font-size: 13px; font-family: Arial, sans-serif; }

        /* Body */
        .body { padding: 32px 24px; }
        .greeting { color: #cbd5e1; font-size: 15px; line-height: 1.7; margin: 0 0 24px 0; font-family: Arial, sans-serif; }
        .greeting strong { color: #f1f5f9; }

        /* OTP label */
        .otp-label {
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 0 0 10px 0;
            font-family: Arial, sans-serif;
        }

        /* OTP Box - dùng table để render đúng trên mọi client */
        .otp-box {
            background-color: #0f172a;
            border: 2px solid #4f46e5;
            border-radius: 12px;
            padding: 24px 16px;
            text-align: center;
            margin: 0 0 20px 0;
        }

        /* Hiển thị 6 chữ số trong bảng để không bị xuống dòng */
        .otp-table { margin: 0 auto; border-collapse: separate; border-spacing: 6px 0; }
        .otp-cell {
            width: 40px;
            height: 52px;
            background-color: #1e293b;
            border: 1px solid #4f46e5;
            border-radius: 8px;
            text-align: center;
            vertical-align: middle;
            color: #a78bfa;
            font-size: 26px;
            font-weight: 800;
            font-family: 'Courier New', Courier, monospace;
            line-height: 1;
        }

        .expire-note { color: #64748b; font-size: 13px; text-align: center; margin: 0 0 24px 0; line-height: 1.6; font-family: Arial, sans-serif; }
        .expire-note span { color: #f59e0b; font-weight: 700; }

        .divider { border: none; border-top: 1px solid rgba(255,255,255,0.07); margin: 0 0 20px 0; }

        .warning { background-color: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25); border-radius: 10px; padding: 14px 16px; }
        .warning p { margin: 0; color: #fbbf24; font-size: 13px; line-height: 1.6; font-family: Arial, sans-serif; }

        /* Footer */
        .footer { background-color: #0f172a; padding: 20px 24px; text-align: center; }
        .footer p { margin: 0; color: #475569; font-size: 12px; line-height: 1.9; font-family: Arial, sans-serif; }
        .footer a { color: #6366f1; text-decoration: none; }

        /* Responsive */
        @media only screen and (max-width: 480px) {
            .email-container { border-radius: 0 !important; }
            .email-wrapper { padding: 0 !important; }
            .body { padding: 24px 16px !important; }
            .header { padding: 28px 16px !important; }
            .otp-cell {
                width: 34px !important;
                height: 46px !important;
                font-size: 22px !important;
            }
            .otp-table { border-spacing: 4px 0 !important; }
        }

        @media only screen and (max-width: 360px) {
            .otp-cell {
                width: 28px !important;
                height: 40px !important;
                font-size: 18px !important;
                border-radius: 6px !important;
            }
            .otp-table { border-spacing: 3px 0 !important; }
        }
    </style>
</head>
<body>
<div class="email-wrapper">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center" style="padding: 24px 12px;">
                <!-- Container -->
                <table class="email-container" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:560px;">

                    <!-- HEADER -->
                    <tr>
                        <td class="header">
                            <h1>📚 StudyHub</h1>
                            <p>Nền tảng học trực tuyến</p>
                        </td>
                    </tr>

                    <!-- BODY -->
                    <tr>
                        <td class="body">

                            <p class="greeting">
                                Xin chào <strong>{{ $name }}</strong>,<br>
                                Cảm ơn bạn đã đăng ký tài khoản StudyHub! Vui lòng dùng mã xác nhận bên dưới để hoàn tất đăng ký.
                            </p>

                            <p class="otp-label">Mã xác nhận của bạn</p>

                            <!-- OTP Box -->
                            <div class="otp-box">
                                @php $digits = str_split($code); @endphp
                                <table class="otp-table" cellpadding="0" cellspacing="0" role="presentation">
                                    <tr>
                                        @foreach($digits as $digit)
                                        <td class="otp-cell">{{ $digit }}</td>
                                        @endforeach
                                    </tr>
                                </table>
                            </div>

                            <p class="expire-note">
                                Mã có hiệu lực trong <span>10 phút</span>.<br>
                                Vui lòng không chia sẻ mã này với bất kỳ ai.
                            </p>

                            <hr class="divider">

                            <div class="warning">
                                <p>⚠️ Nếu bạn không thực hiện yêu cầu này, hãy bỏ qua email này. Tài khoản sẽ không được tạo nếu không xác nhận.</p>
                            </div>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td class="footer">
                            <p>
                                Email này được gửi tự động từ hệ thống StudyHub.<br>
                                Vui lòng không trả lời email này.<br>
                                &copy; {{ date('Y') }} <a href="#">StudyHub</a> &mdash; Học tập không giới hạn.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>