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
        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white">
            <span class="material-symbols-outlined text-[20px]">account_balance</span>
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
