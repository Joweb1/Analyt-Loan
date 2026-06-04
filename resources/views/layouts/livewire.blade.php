<!DOCTYPE html>
<html lang="{{ fetch_data(str_replace('_', '-', app()?->getLocale()) ?? null) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <x-page-title :title="$title ?? null" />
        <x-favicon />
    </head>
    <body>
        {{ $slot }}
    </body>
</html>
