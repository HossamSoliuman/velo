<x-layouts.admin-guest title="Choose a new password">
    <form method="POST" action="{{ route('admin.password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-admin.input name="email" type="email" label="Email address" :value="$email" autocomplete="username" required />
        <x-admin.input name="password" type="password" label="New password" autocomplete="new-password" required autofocus
            hint="At least 8 characters, with letters and numbers." />
        <x-admin.input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" required />

        <x-admin.button class="w-full">Reset password</x-admin.button>
    </form>
</x-layouts.admin-guest>
