<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Analyt Loan | The Self-Driving Bank for Modern Lending</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
        .hero-gradient {
            background: radial-gradient(circle at 80% 20%, rgba(23, 15, 41, 0.05) 0%, transparent 50%);
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-inter-font text-primary dark:text-white transition-colors duration-300">
<div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
<!-- Top Navigation -->
<header class="sticky top-0 z-50 w-full border-b border-gray-100 dark:border-gray-800 bg-white/80 dark:bg-background-dark/80 backdrop-blur-md">
<div class="mx-auto flex max-w-[1200px] items-center justify-between px-6 py-4">
<div class="flex items-center gap-3">
<div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-white">
<span class="material-symbols-outlined text-xl">account_balance</span>
</div>
<h2 class="text-xl font-bold tracking-tight">Analyt Loan</h2>
</div>
<nav class="hidden md:flex items-center gap-10">
<a class="text-sm font-medium opacity-70 hover:opacity-100 transition-opacity" href="#">Features</a>
<a class="text-sm font-medium opacity-70 hover:opacity-100 transition-opacity" href="#">Solutions</a>
<a class="text-sm font-medium opacity-70 hover:opacity-100 transition-opacity" href="#">Pricing</a>
<a class="text-sm font-medium opacity-70 hover:opacity-100 transition-opacity" href="{{ route('login') }}" wire:navigate>Login</a>
<a href="{{ route('register') }}" wire:navigate>
    <button class="flex min-w-[110px] items-center justify-center rounded-lg bg-primary dark:bg-white px-5 py-2.5 text-sm font-bold text-white dark:text-primary transition-all hover:shadow-lg active:scale-95">
                            Get Started
                        </button>
</a>
</nav>
<div class="md:hidden">
<button id="menu-toggle" class="flex items-center justify-center">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
</div>
</header>
<main class="flex-1">
{{ $slot }}
</main>
<!-- Footer -->
<footer class="border-t border-gray-100 dark:border-gray-800 py-12">
<div class="mx-auto max-w-[1200px] px-6">
<div class="flex flex-col md:flex-row justify-between items-center gap-8">
<div class="flex items-center gap-3">
<div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-white">
<span class="material-symbols-outlined text-xl">account_balance</span>
</div>
<h2 class="text-xl font-bold tracking-tight">Analyt Loan</h2>
</div>
<div class="flex flex-wrap justify-center gap-8 text-sm opacity-60">
<a class="hover:opacity-100" href="#">Terms of Service</a>
<a class="hover:opacity-100" href="#">Privacy Policy</a>
<a class="hover:opacity-100" href="#">Security</a>
<a class="hover:opacity-100" href="#">Contact Us</a>
</div>
<div class="flex items-center gap-4 text-primary dark:text-white">
<span class="material-symbols-outlined cursor-pointer hover:opacity-70">language</span>
<span class="text-sm font-medium">EN-NG</span>
</div>
</div>
<div class="mt-12 text-center text-xs opacity-40">
                    © 2024 Analyt Loan Technologies Limited. All rights reserved.
                </div>
</div>
</footer>
</div>
<!-- Mobile Navigation Modal -->
<div id="mobile-menu" class="hidden fixed inset-0 z-50 bg-background-light dark:bg-background-dark p-6">
    <div class="flex justify-between items-center mb-10">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-white">
                <span class="material-symbols-outlined text-xl">account_balance</span>
            </div>
            <h2 class="text-xl font-bold tracking-tight">Analyt Loan</h2>
        </div>
        <button id="close-menu">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <nav class="flex flex-col items-center gap-10">
        <a class="text-lg font-medium opacity-70 hover:opacity-100 transition-opacity" href="#">Features</a>
        <a class="text-lg font-medium opacity-70 hover:opacity-100 transition-opacity" href="#">Solutions</a>
        <a class="text-lg font-medium opacity-70 hover:opacity-100 transition-opacity" href="#">Pricing</a>
        <a class="text-lg font-medium opacity-70 hover:opacity-100 transition-opacity" href="{{ route('login') }}" wire:navigate>Login</a>
<a href="{{ route('register') }}" class="w-full" wire:navigate>
    <button class="flex w-full items-center justify-center rounded-lg bg-primary dark:bg-white px-5 py-3 text-base font-bold text-white dark:text-primary transition-all hover:shadow-lg active:scale-95">
                Get Started
            </button>
</a>
    </nav>
</div>
<script>
    const menuToggle = document.getElementById('menu-toggle');
    const closeMenu = document.getElementById('close-menu');
    const mobileMenu = document.getElementById('mobile-menu');

    menuToggle.addEventListener('click', () => {
        mobileMenu.classList.remove('hidden');
    });

    closeMenu.addEventListener('click', () => {
        mobileMenu.classList.add('hidden');
    });
</script>
</body></html>
