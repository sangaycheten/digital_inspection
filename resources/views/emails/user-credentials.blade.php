<x-mail::message>
# Your Login Credentials

Dear {{ $user->name }},

Your account on **{{ config('app.name') }}** has been set up. Below are your login credentials:

<x-mail::table>
| | |
|---|---|
| **Email** | {{ $user->email }} |
| **Password** | `{!! $password !!}` |
</x-mail::table>

Please log in and change your password immediately after signing in.

<x-mail::button :url="url('/login')">
Login Now
</x-mail::button>

If you did not expect this email, please contact your administrator.

Thanks,
**{{ config('app.name') }}**
</x-mail::message>
