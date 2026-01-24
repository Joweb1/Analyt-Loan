<x-guest-layout>
    <div class="max-w-[440px] w-full mx-auto">
    <div class="mb-10 text-center lg:text-left">
        <h1 class="text-[#131416] dark:text-white text-3xl font-bold tracking-tight mb-2">Confirm Password</h1>
        <p class="text-[#6b7180] text-base">
            This is a secure area of the application. Please confirm your password before continuing.
        </p>
    </div>

    <form wire:submit="confirmPassword" class="space-y-5">
        <!-- Password Field -->
        <div class="flex flex-col gap-2">
            <label for="password" class="text-[#131416] dark:text-white text-sm font-semibold">Password</label>
            <div class="relative flex items-center">
                <input wire:model.defer="password" id="password" type="password" class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 pr-12 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-[#6b7180]" required autocomplete="current-password" />
                <button onclick="togglePasswordVisibility('password', 'password_icon_confirm')" class="absolute right-4 text-[#6b7180] flex items-center justify-center" type="button">
                    <span id="password_icon_confirm" class="material-symbols-outlined text-[20px]">visibility</span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-75" class="w-full bg-primary text-white rounded-lg h-14 font-bold text-base shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all active:scale-[0.98] mt-4 inline-flex items-center justify-center">
            <span wire:loading.remove>Confirm</span>
            <span wire:loading class="flex items-center justify-center">
                <span class="material-symbols-outlined animate-spin text-[20px] text-white">progress_activity</span>
            </span>
        </button>
    </form>
</div>
</x-guest-layout>
