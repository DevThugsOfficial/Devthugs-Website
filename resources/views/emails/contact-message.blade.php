<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Message</title>
</head>
<body style="font-family: Arial, sans-serif; background: #0b0f17; color: #f8fafc; margin: 0; padding: 24px;">
    <div style="max-width: 680px; margin: 0 auto; background: #111827; border: 1px solid #2dd4bf; border-radius: 20px; padding: 28px;">
        <!-- Header with logo on right -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 style="margin: 0; font-size: 28px; color: #2dd4bf;">Client Message</h1>
            <img src="{{ env('APP_URL') }}/images/partners_logo/DevThugs_Logo.png" alt="DevThugs Logo" style="height: 60px; width: auto; margin-left: auto;">
        </div>
        <p style="margin: 0 0 22px; color: #cbd5e1;">A visitor submitted the contact form on the DevThugz website.</p>

        <table cellpadding="0" cellspacing="0" style="width: 100%; color: #e2e8f0;">
            <tr>
                <td style="padding: 10px 0; font-weight: bold; width: 160px;">Full Name</td>
                <td style="padding: 10px 0;">{{ $name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Email Address</td>
                <td style="padding: 10px 0;"><a href="mailto:{{ $email }}" style="color: #2dd4bf;">{{ $email }}</a></td>
            </tr>
            <tr>
                <td style="padding: 10px 0; font-weight: bold;">Subject</td>
                <td style="padding: 10px 0;">{{ $subject }}</td>
            </tr>
        </table>

        <div style="margin-top: 24px; padding: 18px; background: #0f172a; border-radius: 16px; border: 1px solid rgba(45,212,191,0.15);">
            <h2 style="margin: 0 0 12px; font-size: 18px; color: #e2e8f0;">Message</h2>
            <div style="color: #cbd5e1; white-space: pre-line; line-height: 1.8;">{!! nl2br(e($message)) !!}</div>
        </div>

        <p style="margin: 30px 0 0; color: #94a3b8; font-size: 13px;">This message was sent from the DevThugz contact page.</p>
    </div>
</body>
</html>
