<x-admin-layout title="Edit User">
    <div class="max-w-lg bg-white rounded shadow p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')

            <div class="mb-4">
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $user->name) }}" required />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>
            <div class="mb-4">
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email', $user->email) }}" required />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>
            <div class="mb-4">
                <x-input-label for="role" value="Role" />
                <select id="role" name="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="client" {{ old('role', $user->role) === 'client' ? 'selected' : '' }}>Client</option>
                    <option value="admin"  {{ old('role', $user->role) === 'admin'  ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div class="mb-4">
                <x-input-label for="ui_locale" value="Dashboard Language" />
                <select id="ui_locale" name="ui_locale" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="" {{ old('ui_locale', $user->ui_locale) === null ? 'selected' : '' }}>— Browser default</option>
                    <option value="en" {{ old('ui_locale', $user->ui_locale) === 'en' ? 'selected' : '' }}>English</option>
                    <option value="nl" {{ old('ui_locale', $user->ui_locale) === 'nl' ? 'selected' : '' }}>Nederlands (Dutch)</option>
                    <option value="id" {{ old('ui_locale', $user->ui_locale) === 'id' ? 'selected' : '' }}>Bahasa Indonesia</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">Language used for the dashboard interface. Leave on "Browser default" to follow the user's browser language.</p>
            </div>
            <div class="mb-4">
                <x-input-label for="password" value="New Password (leave blank to keep current)" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>
            <div class="mb-6">
                <x-input-label for="password_confirmation" value="Confirm Password" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
            </div>

            <div class="flex gap-3">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-700 self-center">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
