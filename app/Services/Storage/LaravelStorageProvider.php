<?php

namespace App\Services\Storage;

use App\Contracts\StorageProvider;
use Illuminate\Support\Facades\Storage;

class LaravelStorageProvider implements StorageProvider
{
    protected string $disk;

    public function __construct(?string $disk = null)
    {
        $this->disk = $disk ?: (config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default'));
    }

    public function url(string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk($this->disk)->url($path);
    }

    public function put(string $path, $resource): bool
    {
        return Storage::disk($this->disk)->put($path, $resource);
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }
}
