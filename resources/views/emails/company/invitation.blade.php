@component('mail::message')
# Invitation to Join {{ $companyName }}

You have been invited to join **{{ $companyName }}** as an employee.

@if($hasGeneratedPassword)
## 🎉 Welcome! Your Account Has Been Created

We've automatically created an account for you with the following login details:

@component('mail::panel')
**Login Email:** {{ $email }}  
**Temporary Password:** `{{ $password }}`
@endcomponent

⚠️ **Security Notice:** Please change this temporary password after your first login.

## Next Steps

1. **Accept the invitation** by clicking the button below
2. **Login to the system** using the credentials above  
3. **Change your password** in your profile settings for security

@else
## Next Steps

You already have an account with us. Simply click the button below to accept the invitation and join the company:

@endif

@component('mail::button', ['url' => $acceptUrl])
Accept Invitation & Join {{ $companyName }}
@endcomponent

@if($hasGeneratedPassword)
---

**Quick Login Summary:**
- Email: {{ $email }}
- Password: {{ $password }}
- After accepting, login at: {{ url('/login') }}

@else
After accepting the invitation, you can login using your existing account credentials.
@endif

If you have any questions, please contact your company administrator.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent
