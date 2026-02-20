<?php

namespace App\Traits;

trait SterilizesPhone
{
    /**
     * Sterilize phone number: remove leading 0 and prepend 234.
     * Ensures the phone is exactly 11 digits before sterilization.
     */
    public function sterilize($phone)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If it starts with 234 and is 13 digits, it's already sterilized
        if (strpos($phone, '234') === 0 && strlen($phone) === 13) {
            return $phone;
        }

        // If it starts with 0 and is 11 digits, remove 0 and prepend 234
        if (strpos($phone, '0') === 0 && strlen($phone) === 11) {
            return '234'.substr($phone, 1);
        }

        // If it is 10 digits (no leading 0), prepend 234
        if (strlen($phone) === 10) {
            return '234'.$phone;
        }

        return $phone;
    }
}
