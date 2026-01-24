<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? 'Analyt Loan' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-background-light dark:bg-background-dark font-display antialiased">
        <div class="flex flex-col items-center justify-center min-h-screen">
            <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-2xl shadow-soft dark:bg-[#1a1f2b]">
                <div class="flex justify-center">
                    <a href="/" wire:navigate>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center text-white">
                                <span class="material-symbols-outlined text-[24px]">account_balance</span>
                            </div>
                            <h1 class="text-primary dark:text-white text-2xl font-bold leading-none tracking-tight">Analyt</h1>
                        </div>
                    </a>
                </div>
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
