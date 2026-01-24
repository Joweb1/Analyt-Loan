<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Handle an incoming password reset link request.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('status', __($status));
        } else {
            $this->addError('email', __($status));
        }
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
        <h1 class="text-[#131416] dark:text-white text-3xl font-bold tracking-tight mb-2">Forgot Password?</h1>
        <p class="text-[#6b7180] text-base">No problem. Enter your email address below and we'll send you a link to reset it.</p>
    </div>

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <!-- Email Field -->
        <div class="flex flex-col gap-2">
            <label for="email" class="text-[#131416] dark:text-white text-sm font-semibold">Email Address</label>
            <input wire:model="email" id="email" type="email" class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-[#6b7180]" placeholder="name@company.com" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-75" class="w-full bg-primary text-white rounded-lg h-14 font-bold text-base shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all active:scale-[0.98] mt-4 inline-flex items-center justify-center">
            <span wire:loading.remove>Email Password Reset Link</span>
            <span wire:loading class="flex items-center justify-center">
                <span class="material-symbols-outlined animate-spin text-[20px] text-white">progress_activity</span>
            </span>
        </button>
    </form>

    <div class="mt-10 text-center">
        <p class="text-[#6b7180] text-sm">
            Remembered your password?
            <a href="{{ route('login') }}" class="text-primary dark:text-white font-bold hover:underline ml-1" wire:navigate>
                Sign In
            </a>
        </p>
    </div>
</div>
