@php
    $baseTitle = 'Analyt Loan';
    
    if (Auth::check() && Auth::user()->organization) {
        $baseTitle = Auth::user()->organization->name;
    }

    // Try to get title from prop, then from section, then from breadcrumb if possible
    $pageName = $title ?? View::yieldContent('title');
    
    // If still empty, try to derive from route name as last resort
    if (!$pageName) {
        $routeName = Route::currentRouteName();
        if ($routeName) {
            $pageName = str_replace(['.', '_'], ' ', $routeName);
            $pageName = ucwords($pageName);
        }
    }

    if ($pageName) {
        $fullTitle = $baseTitle . ' - ' . $pageName;
    } else {
        $fullTitle = $baseTitle;
    }
@endphp

<title>{{ $fullTitle }}</title>
