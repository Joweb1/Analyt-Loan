<?php

use App\ValueObjects\Money;
use Illuminate\Support\Carbon;

if (! function_exists('fetch_data')) {
    /**
     * Safely fetch and display data, showing a fallback if the data is missing.
     *
     * @param  mixed  $value
     * @param  string  $fallback
     * @return mixed
     */
    function fetch_data($value, $fallback = 'Error fetching data')
    {
        if ($value instanceof Money) {
            return $value->format();
        }

        if ($value instanceof Carbon) {
            return $value->format('M d, Y');
        }

        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return $fallback;
        }

        return $value;
    }
}
