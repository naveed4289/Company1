<x-mail::message>
# Hello {{ $user->first_name }},

Please click the button below to verify your email address.

<x-mail::button :url="$url">
Verify Email
</x-mail::button>

If you did not create an account, no further action is required.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
