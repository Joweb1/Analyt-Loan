<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app(\App\Services\Localization::class)->getDirection() }}" class="light">
<head>
    <meta charset="utf-8"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    <x-page-title :title="$title ?? null" />
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Custom scrollbar for webkit */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        /* Sidebar Base Styles */
        #sidebar {
            width: 0;
            opacity: 0;
            overflow: hidden;
            transition: width 350ms cubic-bezier(0.4, 0, 0.2, 1), opacity 350ms ease, border-right-width 350ms;
            position: relative;
            z-index: 20;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            border-right: 0px solid transparent;
        }

        #sidebar[data-expanded="true"] {
            width: 256px;
            opacity: 1;
            border-right-width: 1px;
        }

        /* Sidebar content transition */
        .sidebar-nav-text, #sidebar-logo-text {
            white-space: nowrap;
            opacity: 0;
            transition: opacity 250ms ease-in-out;
            transition-delay: 50ms;
        }

        #sidebar[data-expanded="true"] .sidebar-nav-text,
        #sidebar[data-expanded="true"] #sidebar-logo-text {
            opacity: 1;
        }

        @media (max-width: 699px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                height: 100%;
                z-index: 100;
            }
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-primary h-screen flex overflow-hidden selection:bg-primary/10">
<!-- Immediate State Script -->
<script>
    (function() {
        try {
            const isPinned = localStorage.getItem('sidebarPinned') === 'true';
            const isWideScreen = window.innerWidth >= 700;
            if (isWideScreen && isPinned) {
                document.documentElement.setAttribute('data-sidebar-pinned', 'true');
            }
        } catch (e) {}
    })();
</script>
<style>
    /* Force state immediately if pinned to prevent flash or jump */
    [data-sidebar-pinned="true"] #sidebar {
        width: 256px !important;
        opacity: 1 !important;
        transition: none !important;
        border-right-width: 1px !important;
    }
    [data-sidebar-pinned="true"] #sidebar .sidebar-nav-text,
    [data-sidebar-pinned="true"] #sidebar #sidebar-logo-text {
        opacity: 1 !important;
        transition: none !important;
    }
</style>

<!-- Sidebar -->
<aside id="sidebar" wire:ignore.self data-expanded="false" class="bg-white dark:bg-[#1a1f2b] h-full flex flex-col border-slate-100 dark:border-slate-800 shadow-soft overflow-hidden">
    @php
        $org = \App\Models\Organization::current();
    @endphp
    <div class="h-20 flex items-center justify-start px-8 border-b border-transparent shrink-0">
        <div class="flex items-center gap-3">
            @if($org && $org->logo_path)
                <img src="{{ $org->logo_url }}" class="size-9 object-contain rounded-lg" alt="{{ $org->name }}">
            @else
                <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-[20px]">account_balance</span>
                </div>
            @endif
            <div id="sidebar-logo-text" class="flex flex-col">
                <h1 class="text-primary dark:text-white text-base font-bold leading-none tracking-tight">
                    {{ $org->name ?? 'Analyt' }}
                </h1>
                <span class="text-slate-400 text-[10px] font-medium mt-1 uppercase tracking-wider">
                    {{ $org ? 'Self-Driving Money' : 'Self-Driving Bank' }}
                </span>
            </div>
        </div>
    </div>
    <nav id="sidebar-nav" class="flex-1 flex flex-col gap-2 p-4 overflow-y-auto overflow-x-hidden">
        <!-- Dashboard Item -->
        @if(Auth::user()->isAppOwner())
            <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('admin.dashboard') }}">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('admin.dashboard') ? 'icon-fill' : '' }}">dashboard</span>
                <span class="sidebar-nav-text text-sm font-medium">Platform Dashboard</span>
            </a>

            <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('admin.organizations') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('admin.organizations') }}">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('admin.organizations') ? 'icon-fill' : '' }}">hub</span>
                <span class="sidebar-nav-text text-sm font-medium">Organizations</span>
            </a>

            <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('admin.distribution') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('admin.distribution') }}">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('admin.distribution') ? 'icon-fill' : '' }}">account_balance_wallet</span>
                <span class="sidebar-nav-text text-sm font-medium">Distribution</span>
            </a>

            <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('admin.reports') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('admin.reports') }}">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('admin.reports') ? 'icon-fill' : '' }}">analytics</span>
                <span class="sidebar-nav-text text-sm font-medium">Platform Reports</span>
            </a>

            <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('admin.settings') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('admin.settings') }}">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('admin.settings') ? 'icon-fill' : '' }}">settings_suggest</span>
                <span class="sidebar-nav-text text-sm font-medium">Platform Settings</span>
            </a>
        @else
            @can('view_dashboard')
                <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('dashboard') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('dashboard') }}">
                    <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('dashboard') ? 'icon-fill' : '' }}">dashboard</span>
                    <span class="sidebar-nav-text text-sm font-medium">Dashboard</span>
                </a>
            @endcan

            @can('manage_loans')
                @unless(Auth::user()->hasRole('Collection Officer'))
                    <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('loan') || request()->routeIs('loan.*') || request()->routeIs('status-board') || request()->routeIs('loan-application') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('loan') }}">
                        <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('loan') || request()->routeIs('loan.*') || request()->routeIs('status-board') || request()->routeIs('loan-application') ? 'icon-fill' : '' }}">monetization_on</span>
                        <span class="sidebar-nav-text text-sm font-medium">Loans</span>
                    </a>
                @endunless
            @endcan

            @can('manage_collections')
                <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('collections') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('collections') }}">
                    <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('collections') ? 'icon-fill' : '' }}">trending_up</span>
                    <span class="sidebar-nav-text text-sm font-medium">Collections</span>
                </a>
            @endcan

            @can('view_reports')
                @unless(Auth::user()->hasRole('Collection Officer'))
                    <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('reports') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('reports') }}">
                        <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('reports') ? 'icon-fill' : '' }}">bar_chart</span>
                        <span class="sidebar-nav-text text-sm font-medium">Reports</span>
                    </a>
                @endunless
            @endcan

            @can('manage_borrowers')
                @unless(Auth::user()->hasRole('Collection Officer'))
                    <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('customer') || request()->routeIs('customer.create') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('customer') }}">
                        <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('customer') || request()->routeIs('customer.create') ? 'icon-fill' : '' }}">group</span>
                        <span class="sidebar-nav-text text-sm font-medium">Customers</span>
                    </a>
                @endunless
            @endcan

            @can('manage_vault')
                @unless(Auth::user()->hasRole('Collection Officer'))
                    <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('vault') || request()->routeIs('collateral.create') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('vault') }}">
                        <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('vault') || request()->routeIs('collateral.create') ? 'icon-fill' : '' }}">shield</span>
                        <span class="sidebar-nav-text text-sm font-medium">Vault</span>
                    </a>
                @endunless
            @endcan

            @can('manage_settings')
                @unless(Auth::user()->hasRole('Collection Officer'))
                    <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('settings') || request()->routeIs('settings.*') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('settings') }}">
                        <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('settings') || request()->routeIs('settings.*') ? 'icon-fill' : '' }}">settings</span>
                        <span class="sidebar-nav-text text-sm font-medium">Settings</span>
                    </a>
                @endunless
            @endcan
        @endif
        <button id="hideSidebarBtn" class="flex items-center gap-3 px-3 py-3 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400 transition-all group hover:text-primary mt-auto shrink-0">
            <span class="material-symbols-outlined group-hover:scale-110 transition-transform">arrow_back_ios</span>
            <span class="sidebar-nav-text text-sm font-medium">Collapse</span>
        </button>
    </nav>
    
    <div class="px-4 pb-6 shrink-0">
        @if($org && $org->use_manual_date)
            <div class="mb-4 px-4 py-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-900/30 rounded-2xl sidebar-nav-text">
                <div class="flex items-center gap-2 text-orange-700 dark:text-orange-400 mb-1">
                    <span class="material-symbols-outlined text-sm font-black">simulation</span>
                    <span class="text-[10px] font-black uppercase tracking-widest">Simulated Time</span>
                </div>
                <p class="text-xs font-bold text-orange-800 dark:text-orange-300">
                    {{ \App\Models\Organization::systemNow()->format('M d, Y') }}
                </p>
            </div>
        @endif
        <livewire:components.sidebar-profile />
    </div>
</aside>
<script>
    // Set initial attribute on aside element immediately after it's defined
    (function() {
        const isPinned = localStorage.getItem('sidebarPinned') === 'true';
        const isWideScreen = window.innerWidth >= 700;
        if (isWideScreen && isPinned) {
            document.getElementById('sidebar').setAttribute('data-expanded', 'true');
        }
    })();
</script>

<!-- Main Content -->
<main id="main-content" class="flex-1 flex flex-col h-full overflow-hidden relative">
    <!-- Top Header & Omnibar -->
    <header class="h-24 min-h-[96px] w-full flex items-center justify-between px-6 lg:px-12 z-[60]">
        <div class="flex-1 flex justify-center max-w-3xl mx-auto w-full">
            <!-- Omnibar -->
            <livewire:components.omnibar-search />
        </div>
        <div class="flex items-center gap-4 ml-6 shrink-0">
            <a href="{{ route('notifications') }}" class="relative p-2 text-slate-500 hover:text-primary hover:bg-white rounded-full transition-all">
                <span class="material-symbols-outlined">notifications</span>
                <span class="absolute top-2 right-2 w-2 h-2 bg-brand-red rounded-full"></span>
            </a>
        </div>
    </header>
    <!-- Scrollable Dashboard Content -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden p-6 lg:p-12 pt-2 scroll-smooth">
        <div class="max-w-[1200px] mx-auto flex flex-col gap-8 pb-10">
            {{ $slot }}
        </div>
    </div>
</main>
<button id="showSidebarFab" class="fixed bottom-8 right-8 w-14 h-14 rounded-full bg-primary text-white flex items-center justify-center shadow-lg hover:scale-110 active:scale-95 transition-all z-50">
    <span class="material-symbols-outlined text-2xl">menu</span>
</button>
<x-custom-alert />
@stack('scripts')
<script>
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[placeholder*="Universal search"]');
            if (searchInput) searchInput.focus();
        }
    });
</script>
</body>
</html>