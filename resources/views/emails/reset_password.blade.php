@component('mail::message')
# Introduction

The body of your message.

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
