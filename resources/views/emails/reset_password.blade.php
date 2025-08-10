<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
</head>
<body>
    <h2>Password Reset Request</h2>
    <p>Hello {{ $user->first_name }},</p>
    <p>You requested to reset your password. Click the link below to reset it:</p>
    <a href="{{ $resetUrl }}">Reset Password</a>
    <p>This link will expire in 60 minutes.</p>
    <p>If you didn't request this, please ignore this email.</p>
</body>
</html>