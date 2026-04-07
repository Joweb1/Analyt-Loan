<?php

namespace App\Contracts;

interface StorageProvider
{
    /**
     * Get the public URL for a given path.
     */
    public function url(string $path): ?string;

    /**
     * Store a file from a stream.
     */
    public function put(string $path, $resource): bool;

    /**
     * Delete a file.
     */
    public function delete(string $path): bool;

    /**
     * Check if a file exists.
     */
    public function exists(string $path): bool;
}
