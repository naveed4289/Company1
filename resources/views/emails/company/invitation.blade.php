@component('mail::message')
# Invitation to Join {{ $companyName }}

You have been invited to join **{{ $companyName }}** as an employee.

@if($hasGeneratedPassword)
## Your Account Details

We've created an account for you with the following login details:

**Email:** {{ $email }}  
**Password:** {{ $password }}

⚠️ **Important:** Please change your password after your first login for security.

## Next Steps

1. Click the button below to accept the invitation
2. Use the login details above to access your account
3. Change your password in your profile settings

@else
## Next Steps

Click the button below to accept the invitation and join the company:

@endif

@component('mail::button', ['url' => $acceptUrl])
Accept Invitation
@endcomponent

@if($hasGeneratedPassword)
After accepting the invitation, you can login to the system using the credentials provided above.
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
