<x-layouts.admin-guest title="Forgot password">
    <p class="text-sm text-slate-600">Enter the email address you use to log in. If it belongs to an admin account, we'll email you a link to choose a new password. The link expires after {{ config('auth.passwords.users.expire') }} minutes.</p>

    <form method="POST" action="{{ route('admin.password.email') }}" class="mt-6 space-y-5">
        @csrf

        <x-admin.input name="email" type="email" label="Email address" autocomplete="username" required autofocus />

        <x-admin.button class="w-full">Email reset link</x-admin.button>
    </form>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('admin.login') }}" class="font-semibold text-brand-600 hover:text-brand-800">Back to login</a>
    </p>
</x-layouts.admin-guest>
