<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class Localization
{
    /**
     * Set the application locale and timezone based on the organization.
     */
    public function setContext(Organization $organization): void
    {
        // Set Timezone
        if ($organization->timezone) {
            Config::set('app.timezone', $organization->timezone);
            date_default_timezone_set($organization->timezone);
        }

        // Set Locale (defaulting to organization's language if we had one, but for now we'll use app default or NGN/USD logic)
        // In a real world, organization would have a 'language' field.
        // For now, we'll assume 'en' but allow for expansion.
        $locale = $organization->locale ?? Config::get('app.locale');
        App::setLocale($locale);
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
