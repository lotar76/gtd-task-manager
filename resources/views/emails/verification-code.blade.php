<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Код подтверждения</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:40px 0;">
        <tr>
            <td align="center">
                <table width="460" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.07);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding:32px 40px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:24px; font-weight:700; letter-spacing:-0.5px;">
                                Крепкая Башня
                            </h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:40px;">
                            <p style="margin:0 0 8px; color:#374151; font-size:18px; font-weight:600;">
                                Здравствуйте, {{ $userName }}!
                            </p>
                            <p style="margin:0 0 32px; color:#6b7280; font-size:15px; line-height:1.6;">
                                Для подтверждения email введите код в приложении:
                            </p>
                            <!-- Code -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <div style="display:inline-block; background-color:#f3f4f6; border:2px dashed #d1d5db; border-radius:12px; padding:20px 48px;">
                                            <span style="font-size:36px; font-weight:700; letter-spacing:8px; color:#1f2937; font-family:'Courier New',monospace;">
                                                {{ $code }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:32px 0 0; color:#9ca3af; font-size:13px; line-height:1.5; text-align:center;">
                                Код действителен 15 минут.<br>
                                Если вы не регистрировались -- просто проигнорируйте это письмо.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 40px; background-color:#f9fafb; border-top:1px solid #e5e7eb; text-align:center;">
                            <p style="margin:0; color:#9ca3af; font-size:12px;">
                                skylit.ru
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
