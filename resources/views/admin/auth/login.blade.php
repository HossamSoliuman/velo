<x-layouts.admin-guest title="Admin login">
    <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5">
        @csrf

        <x-admin.input name="email" type="email" label="Email address" autocomplete="username" required autofocus />
        <x-admin.input name="password" type="password" label="Password" autocomplete="current-password" required />

        <div class="flex items-center justify-between gap-4">
            <x-admin.toggle name="remember" label="Keep me logged in" />
            <a href="{{ route('admin.password.request') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-800">Forgot password?</a>
        </div>

        <x-admin.button class="w-full">Log in</x-admin.button>
    </form>
</x-layouts.admin-guest>
