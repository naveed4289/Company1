<x-mail::message>
# Hello {{ $user->first_name }},

You requested to reset your password.  
Click the button below to set a new password.

<x-mail::button :url="$resetUrl">
Reset Password
</x-mail::button>

This link will expire in **60 minutes**.

If you did not request this, please ignore this email.

Thanks,  
{{ config('app.name') }}
</x-mail::message>
