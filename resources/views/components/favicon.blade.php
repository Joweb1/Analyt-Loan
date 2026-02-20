@php
    $faviconUrl = asset('favicon.ico'); // Default fallback if anything fails
    $logoPath = null;

    if (Auth::check() && Auth::user()->organization) {
        $logoPath = Auth::user()->organization->logo_path;
    }

    if ($logoPath) {
        $faviconUrl = asset('storage/' . $logoPath);
    } else {
        // SVG data URI for the 'A' fallback
        // Background: #0f172a (Deep dark blue/Slate-900)
        // Text: A (White)
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" rx="8" fill="#0f172a"/><text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" fill="white" font-family="sans-serif" font-weight="900" font-size="20">A</text></svg>';
        $faviconUrl = 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
@endphp

<link rel="icon" type="{{ $logoPath ? 'image/*' : 'image/svg+xml' }}" href="{{ $faviconUrl }}">
