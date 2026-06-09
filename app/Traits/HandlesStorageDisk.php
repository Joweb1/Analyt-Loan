<?php

namespace App\Traits;

use App\Contracts\StorageProvider;

trait HandlesStorageDisk
{
    /**
     * Get the active storage disk name based on configuration and environment.
     */
    protected function getStorageDisk(): string
    {
        return app(StorageProvider::class)->getDisk();
    }
}
