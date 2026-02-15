<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>{{ $title ?? 'Analyt Loan Dashboard' }}</title>
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

        /* Specific styles for sidebar animation */
        .sidebar-expanded {
            width: 256px; /* lg:w-64 */
            transform: translateX(0);
            visibility: visible;
            transition-property: width, transform, visibility;
            transition-duration: 300ms; /* Match Tailwind's default duration */
        }

        .sidebar-collapsed {
            width: 0px; /* w-20 */
            transform: translateX(0); /* Default for desktop collapsed */
            visibility: hidden; /* Added visibility: hidden; back */
            transition-property: width, transform, visibility;
            transition-duration: 300ms; /* Match Tailwind's default duration */
        }

        @media (max-width: 1023px) { /* Before lg breakpoint */
            .sidebar-collapsed {
                transform: translateX(-100%); /* Full hide on mobile */
            }
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-primary h-screen flex overflow-hidden selection:bg-primary/10">
<!-- Sidebar -->
<aside id="sidebar" wire:ignore.self class="bg-white dark:bg-[#1a1f2b] h-full flex flex-col border-r border-slate-100 dark:border-slate-800 transition-all duration-300 z-20 shadow-soft relative sidebar-collapsed overflow-hidden">
    @php
        $org = Auth::user()->organization;
    @endphp
    <div class="h-20 flex items-center justify-center lg:justify-start lg:px-8 border-b border-transparent">
        <div class="flex items-center gap-3">
            @if($org && $org->logo_path)
                <img src="{{ Storage::url($org->logo_path) }}" class="size-9 object-contain rounded-lg" alt="{{ $org->name }}">
            @else
                <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-[20px]">account_balance</span>
                </div>
            @endif
            <div id="sidebar-logo-text" class="lg:flex flex-col hidden">
                <h1 class="text-primary dark:text-white text-base font-bold leading-none tracking-tight">
                    {{ $org->name ?? 'Analyt' }}
                </h1>
                <span class="text-slate-400 text-[10px] font-medium mt-1 uppercase tracking-wider">
                    {{ $org ? 'Self-Driving Money' : 'Self-Driving Bank' }}
                </span>
            </div>
        </div>
    </div>
    <nav id="sidebar-nav" class="flex flex-col gap-2 p-4 overflow-hidden">
        <!-- Active Item -->
        @can('view_dashboard')
            <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('dashboard') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('dashboard') }}">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('dashboard') ? 'icon-fill' : '' }}">dashboard</span>
                <span class="sidebar-nav-text text-sm font-medium hidden">Dashboard</span>
            </a>
        @endcan

        @if(Auth::user()->isAppOwner())
            <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('admin.distribution') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('admin.distribution') }}">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('admin.distribution') ? 'icon-fill' : '' }}">hub</span>
                <span class="sidebar-nav-text text-sm font-medium hidden">Owner Distribution</span>
            </a>
        @endif

        @can('manage_loans')
            <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('loan') || request()->routeIs('loan.*') || request()->routeIs('status-board') || request()->routeIs('loan-application') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('loan') }}">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('loan') || request()->routeIs('loan.*') || request()->routeIs('status-board') || request()->routeIs('loan-application') ? 'icon-fill' : '' }}">monetization_on</span>
                <span class="sidebar-nav-text text-sm font-medium hidden">Loans</span>
            </a>
        @endcan

        @can('manage_collections')
            <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('collections') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('collections') }}">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('collections') ? 'icon-fill' : '' }}">trending_up</span>
                <span class="sidebar-nav-text text-sm font-medium hidden">Collections</span>
            </a>
        @endcan

        @can('view_reports')
            <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('reports') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('reports') }}">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('reports') ? 'icon-fill' : '' }}">bar_chart</span>
                <span class="sidebar-nav-text text-sm font-medium hidden">Reports</span>
            </a>
        @endcan

        @can('manage_borrowers')
            <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('customer') || request()->routeIs('customer.create') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('customer') }}">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('customer') || request()->routeIs('customer.create') ? 'icon-fill' : '' }}">group</span>
                <span class="sidebar-nav-text text-sm font-medium hidden">Customers</span>
            </a>
        @endcan

        @can('manage_vault')
            <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('vault') || request()->routeIs('collateral.create') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('vault') }}">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('vault') || request()->routeIs('collateral.create') ? 'icon-fill' : '' }}">shield</span>
                <span class="sidebar-nav-text text-sm font-medium hidden">Vault</span>
            </a>
        @endcan

        @can('manage_settings')
            <a class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all group {{ request()->routeIs('settings') || request()->routeIs('settings.*') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400' }}" href="{{ route('settings') }}">
                <span class="material-symbols-outlined group-hover:scale-110 transition-transform {{ request()->routeIs('settings') || request()->routeIs('settings.*') ? 'icon-fill' : '' }}">settings</span>
                <span class="sidebar-nav-text text-sm font-medium hidden">Settings</span>
            </a>
        @endcan
        <a id="hideSidebarBtn" class="flex items-center gap-3 px-3 py-3 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400 transition-all group hover:text-primary">
            <span class="material-symbols-outlined group-hover:scale-110 transition-transform">arrow_back_ios</span>
            <span class="sidebar-nav-text text-sm font-medium hidden">Collapse</span>
        </a>
    </nav>
    
    <div class="px-4 pb-6">
        <livewire:components.sidebar-profile />
    </div>
</aside>
<!-- Main Content -->
<main id="main-content" class="flex-1 flex flex-col h-full overflow-hidden relative transition-all duration-300 lg:ml-0">
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
<button id="showSidebarFab" class="fixed bottom-8 right-8 w-14 h-14 rounded-full bg-primary text-white flex items-center justify-center shadow-lg transform transition-all duration-300 z-50" style="display: block !important;">
    <span class="material-symbols-outlined text-2xl">menu</span>
</button>
<x-custom-alert />
@stack('scripts')
<script>
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[placeholder*="Universal search"]');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
</script>
</body>
</html>