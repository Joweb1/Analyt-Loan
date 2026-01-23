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
<aside id="sidebar" class="bg-white dark:bg-[#1a1f2b] h-full flex flex-col border-r border-slate-100 dark:border-slate-800 transition-all duration-300 z-20 shadow-soft relative sidebar-collapsed overflow-hidden">
    <div class="h-20 flex items-center justify-center lg:justify-start lg:px-8 border-b border-transparent">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white">
                <span class="material-symbols-outlined text-[20px]">account_balance</span>
            </div>
            <div id="sidebar-logo-text" class="lg:flex flex-col hidden">
                <h1 class="text-primary dark:text-white text-base font-bold leading-none tracking-tight">Analyt</h1>
                <span class="text-slate-400 text-xs font-medium mt-1">Self-Driving Bank</span>
            </div>
        </div>
    </div>
    <nav id="sidebar-nav" class="flex-1 flex flex-col gap-2 p-4 overflow-hidden">
        <!-- Active Item -->
        <a class="flex items-center gap-3 px-3 py-3 rounded-xl bg-primary text-white shadow-lg shadow-primary/30 group transition-all" href="{{ route('dashboard') }}">
            <span class="material-symbols-outlined icon-fill">dashboard</span>
            <span class="sidebar-nav-text text-sm font-medium hidden">Dashboard</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-3 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400 transition-all group hover:text-primary" href="{{ route('loan') }}">
            <span class="material-symbols-outlined group-hover:scale-110 transition-transform">monetization_on</span>
            <span class="sidebar-nav-text text-sm font-medium hidden">Loans</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-3 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400 transition-all group hover:text-primary" href="#">
            <span class="material-symbols-outlined group-hover:scale-110 transition-transform">trending_up</span>
            <span class="sidebar-nav-text text-sm font-medium hidden">Collections</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-3 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400 transition-all group hover:text-primary" href="#">
            <span class="material-symbols-outlined group-hover:scale-110 transition-transform">group</span>
            <span class="sidebar-nav-text text-sm font-medium hidden">Customers</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-3 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400 transition-all group hover:text-primary" href="#">
            <span class="material-symbols-outlined group-hover:scale-110 transition-transform">settings</span>
            <span class="sidebar-nav-text text-sm font-medium hidden">Settings</span>
        </a>
        <a id="hideSidebarBtn" class="flex items-center gap-3 px-3 py-3 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-400 transition-all group hover:text-primary">
            <span class="material-symbols-outlined group-hover:scale-110 transition-transform">arrow_back_ios</span>
            <span class="sidebar-nav-text text-sm font-medium hidden">Collapse</span>
        </a>
    </nav>
    <div class="p-4 border-t border-slate-100 dark:border-slate-800">
        <a class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all" href="#">
            <div class="relative">
                <div class="w-8 h-8 rounded-full bg-slate-200 overflow-hidden">
                    <img alt="User Profile" class="w-full h-full object-cover" data-alt="User profile avatar" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDTPSkG8LxAvEKU3y-o8jXr480n0CjG2ops26TXr5tqXVUcpyMybsmA67kAWpWteOWTOhMo4woSPl3-3K457eWu8Kf92YucBtkcDIrpUF327xMrlW_-osLHVImeOrk_UId7-3LmiwKP6QH9RFcSYLIX7ATzAA0faRc5a6IUyvOiOp0SArK9Ishh1-pmzFOl2_ZRBAlRImI"/>
                </div>
                <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></div>
            </div>
            <div id="sidebar-user-profile-text" class="lg:block hidden">
                <p class="text-sm font-bold text-primary dark:text-white">Admin User</p>
                <p class="text-xs text-slate-400">View Profile</p>
            </div>
        </a>
    </div>
</aside>
<!-- Main Content -->
<main id="main-content" class="flex-1 flex flex-col h-full overflow-hidden relative transition-all duration-300 lg:ml-0">
    <!-- Top Header & Omnibar -->
    <header class="h-24 min-h-[96px] w-full flex items-center justify-between px-6 lg:px-12 z-10">
        <div class="flex-1 flex justify-center max-w-3xl mx-auto w-full">
            <!-- Omnibar -->
            <div class="relative w-full group transform transition-all hover:-translate-y-0.5">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-primary/50">smart_toy</span>
                </div>
                <input class="block w-full pl-12 pr-4 py-3.5 bg-white dark:bg-[#1a1f2b] dark:text-white border-none rounded-2xl text-sm shadow-soft focus:ring-2 focus:ring-primary/20 placeholder-slate-400 transition-all" placeholder="Ask Analyt to process a loan or search by User ID..." type="text"/>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <kbd class="hidden sm:inline-flex items-center h-6 px-2 text-[10px] font-medium text-slate-400 bg-slate-50 rounded border border-slate-200">⌘ K</kbd>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4 ml-6 shrink-0">
            <button class="relative p-2 text-slate-500 hover:text-primary hover:bg-white rounded-full transition-all">
                <span class="material-symbols-outlined">notifications</span>
                <span class="absolute top-2 right-2 w-2 h-2 bg-brand-red rounded-full"></span>
            </button>
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
</body>
</html>