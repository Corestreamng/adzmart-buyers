@component('mail::message')
# Introduction

You are recieving this mail because yo have requested a password reset on your Adzmart account.

@component('mail::message')
    # Reset Your Password

    Click the button below to reset your password:

    @component('mail::button', ['url' => $resetUrl])
        Reset Password
    @endcomponent

    If you didn't request a password reset, no further action is required.

    Thanks,
    {{ config('app.name') }}
@endcomponent


Thanks,<br>
{{ config('app.name') }}
@endcomponent
