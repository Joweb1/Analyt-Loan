<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <h1 class="text-primary dark:text-white text-3xl font-extrabold tracking-tight">Security Settings</h1>
        <p class="text-gray-500 mt-1">Manage your account security and password.</p>
    </div>

    <div class="grid grid-cols-12 gap-8">
        <nav class="col-span-12 lg:col-span-3 flex flex-col gap-1">
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings') }}">
                <span class="font-semibold text-sm">General</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border-l-4 border-primary rounded-r-lg shadow-sm" href="{{ route('settings.security') }}">
                <span class="text-primary dark:text-white font-bold text-sm">Security</span>
                <span class="material-symbols-outlined text-primary dark:text-white text-[18px]">chevron_right</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings.team-members') }}">
                <span class="font-semibold text-sm">Team Members</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings.roles') }}">
                <span class="font-semibold text-sm">Roles & Permissions</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings.form-builder') }}">
                <span class="font-semibold text-sm">Form Customization</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings.notifications') }}">
                <span class="font-semibold text-sm">Notifications</span>
            </a>
        </nav>

        <div class="col-span-12 lg:col-span-9 space-y-6">
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-zinc-800">
                <h2 class="text-lg font-bold text-primary dark:text-white mb-6">Update Password</h2>
                
                <form wire:submit.prevent="updatePassword" class="space-y-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Current Password</label>
                        <input wire:model="current_password" type="password" class="rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary focus:border-primary text-sm">
                        @error('current_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700 dark:text-gray-300">New Password</label>
                        <input wire:model="password" type="password" class="rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary focus:border-primary text-sm">
                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Confirm New Password</label>
                        <input wire:model="password_confirmation" type="password" class="rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary focus:border-primary text-sm">
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="px-8 py-3 bg-primary text-white font-bold rounded-xl text-sm shadow-lg shadow-primary/20 hover:scale-[1.02] transition-transform">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
