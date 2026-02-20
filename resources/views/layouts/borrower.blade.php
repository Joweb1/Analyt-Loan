<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport"/>
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    <title>{{ $title ?? 'Borrower App' }}</title>
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $org = Auth::user()->organization;
        $brandColor = $org->brand_color ?? '#2563eb'; // Default to blue-600 if not set
    @endphp
    <style>
        :root {
            --brand-primary: {{ $brandColor }};
            --brand-primary-soft: {{ $brandColor }}15; /* 15% opacity */
        }
        .bg-brand { background-color: var(--brand-primary) !important; }
        .text-brand { color: var(--brand-primary) !important; }
        .border-brand { border-color: var(--brand-primary) !important; }
        .ring-brand { --tw-ring-color: var(--brand-primary) !important; }
        .accent-brand { accent-color: var(--brand-primary) !important; }
        .bg-brand-soft { background-color: var(--brand-primary-soft) !important; }

        /* Mobile-first overrides */
        body {
            overscroll-behavior-y: none; /* Prevent pull-to-refresh on some browsers */
        }
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sans h-screen flex flex-col overflow-hidden selection:bg-blue-500/20">

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto overflow-x-hidden relative {{ !str_contains(request()->route()->getName(), 'onboarding') ? 'pb-24' : '' }} hide-scrollbar">
        {{ $slot }}
    </main>

    <!-- Bottom Navigation -->
    @if(!str_contains(request()->route()->getName(), 'onboarding'))
    <nav class="fixed bottom-0 w-full bg-white border-t border-slate-100 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-50 pb-safe">
        <div class="flex justify-around items-center h-16">
            <a href="{{ route('borrower.home') }}" wire:navigate class="flex flex-col items-center gap-1 w-full h-full justify-center transition-colors {{ request()->routeIs('borrower.home') ? 'text-brand' : 'text-slate-400 hover:text-slate-600' }}">
                <span class="material-symbols-outlined text-2xl {{ request()->routeIs('borrower.home') ? 'icon-fill' : '' }}">home</span>
                <span class="text-[10px] font-medium">Home</span>
            </a>
            
            <a href="{{ route('borrower.borrow') }}" wire:navigate class="flex flex-col items-center gap-1 w-full h-full justify-center transition-colors {{ request()->routeIs('borrower.borrow') ? 'text-brand' : 'text-slate-400 hover:text-slate-600' }}">
                <span class="material-symbols-outlined text-2xl {{ request()->routeIs('borrower.borrow') ? 'icon-fill' : '' }}">payments</span>
                <span class="text-[10px] font-medium">Borrow</span>
            </a>

            <a href="{{ route('borrower.repayment') }}" wire:navigate class="flex flex-col items-center gap-1 w-full h-full justify-center transition-colors {{ request()->routeIs('borrower.repayment') ? 'text-brand' : 'text-slate-400 hover:text-slate-600' }}">
                <span class="material-symbols-outlined text-2xl {{ request()->routeIs('borrower.repayment') ? 'icon-fill' : '' }}">account_balance_wallet</span>
                <span class="text-[10px] font-medium">Repay</span>
            </a>

            <a href="{{ route('borrower.activity') }}" wire:navigate class="flex flex-col items-center gap-1 w-full h-full justify-center transition-colors {{ request()->routeIs('borrower.activity') ? 'text-brand' : 'text-slate-400 hover:text-slate-600' }}">
                <span class="material-symbols-outlined text-2xl {{ request()->routeIs('borrower.activity') ? 'icon-fill' : '' }}">history</span>
                <span class="text-[10px] font-medium">Activity</span>
            </a>

            <a href="{{ route('borrower.account') }}" wire:navigate class="flex flex-col items-center gap-1 w-full h-full justify-center transition-colors {{ request()->routeIs('borrower.account') ? 'text-brand' : 'text-slate-400 hover:text-slate-600' }}">
                <span class="material-symbols-outlined text-2xl {{ request()->routeIs('borrower.account') ? 'icon-fill' : '' }}">person</span>
                <span class="text-[10px] font-medium">Account</span>
            </a>
        </div>
    </nav>
    @endif

    <!-- Global Loading Overlay -->
    <div id="global-loader" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-white/60 backdrop-blur-[2px]">
        <div class="relative">
            <!-- Pulsing Rings -->
            <div class="absolute inset-0 rounded-full bg-brand opacity-20 animate-ping"></div>
            <div class="absolute -inset-4 rounded-full bg-brand opacity-10 animate-pulse"></div>
            
            <!-- Center Logo/Icon -->
            <div class="relative size-20 bg-white rounded-3xl flex items-center justify-center shadow-xl shadow-brand/20 animate-bounce overflow-hidden border-2 border-brand/10">
                @if($org && $org->logo_path)
                    <img src="{{ Storage::url($org->logo_path) }}" class="size-14 object-contain" alt="Logo">
                @else
                    <div class="size-full bg-brand flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-4xl">account_balance</span>
                    </div>
                @endif
            </div>
            
            <!-- Loading Text -->
            <div class="absolute -bottom-10 left-1/2 -translate-x-1/2 whitespace-nowrap">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand animate-pulse">Processing...</p>
            </div>
        </div>
    </div>

    <!-- Global Modals/Toasts -->
    <x-custom-alert />
    
    @stack('scripts')
    <script>
        (function() {
            if (window.borrowerAppInitialized) return;

            const showLoader = () => {
                const loader = document.getElementById('global-loader');
                if (loader) loader.classList.remove('hidden');
            };

            const hideLoader = () => {
                const loader = document.getElementById('global-loader');
                if (loader) loader.classList.add('hidden');
            };

            // Navigation listeners
            document.addEventListener('livewire:navigate', showLoader);
            document.addEventListener('livewire:navigated', hideLoader);

            // Hook into standard Livewire AJAX requests
            document.addEventListener('livewire:init', () => {
                Livewire.hook('request', ({ respond, fail }) => {
                    const timeout = setTimeout(showLoader, 200);
                    respond(() => { clearTimeout(timeout); hideLoader(); });
                    fail(() => { clearTimeout(timeout); hideLoader(); });
                });
            });

            window.borrowerAppInitialized = true;
        })();
    </script>
</body>
</html>
