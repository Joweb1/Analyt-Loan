<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirect(
            session('url.intended', route('dashboard')),
            navigate: true
        );
    }
}; ?>

<div class="max-w-[440px] w-full mx-auto">
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Mobile Logo (Visible only on small screens) -->
    <div class="lg:hidden flex items-center justify-center gap-2 mb-10">
        <div class="size-8 text-primary dark:text-white">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M44 11.2727C44 14.0109 39.8386 16.3957 33.69 17.6364C39.8386 18.877 44 21.2618 44 24C44 26.7382 39.8386 29.123 33.69 30.3636C39.8386 31.6043 44 33.9891 44 36.7273C44 40.7439 35.0457 44 24 44C12.9543 44 4 40.7439 4 36.7273C4 33.9891 8.16144 31.6043 14.31 30.3636C8.16144 29.123 4 26.7382 4 24C4 21.2618 8.16144 18.877 14.31 17.6364C8.16144 16.3957 4 14.0109 4 11.2727C4 7.25611 12.9543 4 24 4C35.0457 4 44 7.25611 44 11.2727Z" fill="currentColor"></path>
            </svg>
        </div>
        <span class="text-primary dark:text-white text-xl font-bold tracking-tight">Analyt Loan</span>
    </div>

    <div class="mb-10 text-center lg:text-left">
        <h1 class="text-[#131416] dark:text-white text-3xl font-bold tracking-tight mb-2">Welcome Back</h1>
        <p class="text-[#6b7180] text-base">Manage your finances with our self-driving banking tools.</p>
    </div>

    <form wire:submit="login" class="space-y-5">
        <!-- Email Field -->
        <div class="flex flex-col gap-2">
            <label for="email" class="text-[#131416] dark:text-white text-sm font-semibold">Email Address</label>
            <input wire:model="form.email" id="email" type="email" class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-[#6b7180]" placeholder="name@company.com" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password Field -->
        <div class="flex flex-col gap-2">
            <div class="flex justify-between items-center">
                <label for="password" class="text-[#131416] dark:text-white text-sm font-semibold">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-primary dark:text-white/80 text-xs font-bold hover:underline" href="{{ route('password.request') }}" wire:navigate>
                        Forgot Password?
                    </a>
                @endif
            </div>
            <div class="relative flex items-center">
                <input wire:model.defer="form.password" id="password" type="password" class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 pr-12 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-[#6b7180]" placeholder="••••••••" required autocomplete="current-password" />
                <button onclick="togglePasswordVisibility('password', 'password_icon')" class="absolute right-4 text-[#6b7180] flex items-center justify-center" type="button">
                    <span id="password_icon" class="material-symbols-outlined text-[20px]">visibility</span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center gap-2 pt-2">
            <input wire:model="form.remember" id="remember" type="checkbox" class="size-4 rounded border-[#dedfe3] text-primary focus:ring-primary" />
            <label for="remember" class="text-sm text-[#6b7180]">Keep me signed in</label>
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-75" class="w-full bg-primary text-white rounded-lg h-14 font-bold text-base shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all active:scale-[0.98] mt-4 inline-flex items-center justify-center">
            <span wire:loading.remove>Sign In</span>
            <span wire:loading class="flex items-center justify-center">
                <span class="material-symbols-outlined custom-spin text-[20px] text-white">progress_activity</span>
            </span>
        </button>
    </form>

    <div class="mt-10 text-center">
        <p class="text-[#6b7180] text-sm">
            New to Analyt Loan?
            <a href="{{ route('register') }}" class="text-primary dark:text-white font-bold hover:underline ml-1" wire:navigate>
                Create an Account
            </a>
        </p>
    </div>


</div>
