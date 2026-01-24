<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>
<div class="max-w-[440px] w-full mx-auto">
    <div class="mb-10 text-center lg:text-left">
        <h1 class="text-[#131416] dark:text-white text-3xl font-bold tracking-tight mb-2">Create an Account</h1>
        <p class="text-[#6b7180] text-base">Join us and start managing your finances efficiently.</p>
    </div>

    <form wire:submit="register" class="space-y-5">
        <!-- Name -->
        <div class="flex flex-col gap-2">
            <label for="name" class="text-[#131416] dark:text-white text-sm font-semibold">Full Name</label>
            <input wire:model="name" id="name" type="text" class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-[#6b7180]" placeholder="John Doe" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="flex flex-col gap-2">
            <label for="email" class="text-[#131416] dark:text-white text-sm font-semibold">Email Address</label>
            <input wire:model="email" id="email" type="email" class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-[#6b7180]" placeholder="name@company.com" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="flex flex-col gap-2">
            <label for="password" class="text-[#131416] dark:text-white text-sm font-semibold">Password</label>
            <div class="relative flex items-center">
                <input wire:model.defer="password" id="password" type="password" class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 pr-12 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-[#6b7180]" placeholder="••••••••" required autocomplete="new-password" />
                <button onclick="togglePasswordVisibility('password', 'password_icon_register')" class="absolute right-4 text-[#6b7180] flex items-center justify-center" type="button">
                    <span id="password_icon_register" class="material-symbols-outlined text-[20px]">visibility</span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="flex flex-col gap-2">
            <label for="password_confirmation" class="text-[#131416] dark:text-white text-sm font-semibold">Confirm Password</label>
            <div class="relative flex items-center">
                <input wire:model.defer="password_confirmation" id="password_confirmation" type="password" class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 pr-12 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-[#6b7180]" placeholder="••••••••" required autocomplete="new-password" />
                <button onclick="togglePasswordVisibility('password_confirmation', 'password_confirmation_icon_register')" class="absolute right-4 text-[#6b7180] flex items-center justify-center" type="button">
                    <span id="password_confirmation_icon_register" class="material-symbols-outlined text-[20px]">visibility</span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-75" class="w-full bg-primary text-white rounded-lg h-14 font-bold text-base shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all active:scale-[0.98] mt-4 inline-flex items-center justify-center">
            <span wire:loading.remove>Create Account</span>
            <span wire:loading class="flex items-center justify-center">
                <span class="material-symbols-outlined animate-spin text-[20px] text-white">progress_activity</span>
            </span>
        </button>
    </form>

    <div class="mt-10 text-center">
        <p class="text-[#6b7180] text-sm">
            Already have an account?
            <a href="{{ route('login') }}" class="text-primary dark:text-white font-bold hover:underline ml-1" wire:navigate>
                Sign In
            </a>
        </p>
    </div>


</div>
