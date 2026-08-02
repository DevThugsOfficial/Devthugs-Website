<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Contact Form Submission</title>
    <style>
        body { font-family: 'Inter', sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #00f0ff 0%, #8b5cf6 100%); color: white; padding: 30px; border-radius: 10px 10px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px; }
        .field { margin-bottom: 20px; }
        .label { font-weight: bold; color: #111827; }
        .value { color: #6b7280; margin-top: 5px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="margin:0; font-size: 24px;">Client Message</h1>
                    <p style="margin: 10px 0 0; opacity: 0.9;">From DevThugs Website</p>
                </div>
                <img src="{{ env('APP_URL') }}/images/partners_logo/DevThugs_Logo.png" alt="DevThugs Logo" style="height: 60px; width: auto; margin-left: auto;">
            </div>
        </div>
        <div class="content">
            <div class="field">
                <div class="label">Name</div>
                <div class="value">{{ $data['name'] }}</div>
            </div>
            <div class="field">
                <div class="label">Email</div>
                <div class="value">{{ $data['email'] }}</div>
            </div>
            <div class="field">
                <div class="label">Subject</div>
                <div class="value">{{ $data['subject'] }}</div>
            </div>
            <div class="field">
                <div class="label">Message</div>
                <div class="value" style="white-space: pre-wrap;">{{ $data['message'] }}</div>
            </div>
        </div>
        <div class="footer">
            <p>This message was sent through the DevThugs contact form.</p>
        </div>
    </div>
</body>
</html>
