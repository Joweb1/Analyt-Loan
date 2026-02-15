<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? 'Loan Document' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            @media print {
                .no-print { display: none !important; }
                body { background: white !important; padding: 0 !important; margin: 0 !important; }
                .print-container { box-shadow: none !important; border: none !important; max-width: 100% !important; padding: 0 !important; }
            }
            body { background-color: #f3f4f6; }
        </style>
    </head>
    <body class="font-sans text-slate-900 antialiased p-4 sm:p-12 flex justify-center">
        <div class="print-container bg-white w-full max-w-4xl p-8 sm:p-16 shadow-lg border border-slate-100 rounded-sm overflow-hidden">
            {{ $slot }}
        </div>
    </body>
</html>
