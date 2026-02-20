<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Features\SupportRedirects\Redirector;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): Redirector
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect()->route('login');
    }
}
