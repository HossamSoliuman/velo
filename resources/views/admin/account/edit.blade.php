<x-layouts.admin title="My account">
    <form method="POST" action="{{ route('admin.account.update') }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        <x-admin.card title="Profile" class="space-y-6">
            <x-admin.input name="name" label="Name" :value="$user->name" required autocomplete="name" />
            <x-admin.input name="email" type="email" label="Email address" :value="$user->email" required autocomplete="username"
                hint="Used to log in and to receive password reset links." />
        </x-admin.card>

        <x-admin.card title="Change password" description="Leave blank to keep your current password." class="space-y-6">
            <x-admin.input name="current_password" type="password" label="Current password" autocomplete="current-password" />
            <div class="grid gap-6 sm:grid-cols-2">
                <x-admin.input name="password" type="password" label="New password" autocomplete="new-password" hint="At least 8 characters, with letters and numbers." />
                <x-admin.input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" />
            </div>
        </x-admin.card>

        <x-admin.button>Save account</x-admin.button>
    </form>
</x-layouts.admin>
