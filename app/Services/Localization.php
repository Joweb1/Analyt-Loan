<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class Localization
{
    /**
     * Set the application locale based on the organization.
     */
    public function setContext(Organization $organization): void
    {
        // Set Locale (defaulting to organization's language if we had one)
        $locale = $organization->locale ?? Config::get('app.locale');
        App::setLocale($locale);

        // We specifically DO NOT call date_default_timezone_set here
        // to avoid shifting the cookie expiration headers in the same request.
    }

    /**
     * Get the text direction for the current locale.
     */
    public function getDirection(): string
    {
        $rtlLocales = ['ar', 'he', 'fa', 'ur'];

        return in_array(App::getLocale(), $rtlLocales) ? 'rtl' : 'ltr';
    }
}
