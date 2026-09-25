<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores uploaded files on the public disk and cleans up files that are no longer referenced.
 */
trait ManagesUploads
{
    /**
     * The path to save for an upload field: a newly stored file, null when removal was ticked, or the current path.
     */
    protected function uploadedPath(Request $request, string $field, ?string $currentPath, string $directory, ?string $removeField = null): ?string
    {
        if ($request->hasFile($field)) {
            return $request->file($field)->store($directory, 'public');
        }

        if ($removeField !== null && $request->boolean($removeField)) {
            return null;
        }

        return $currentPath;
    }

    /**
     * Delete the previous file once the record points at a different one.
     */
    protected function deleteReplacedUpload(?string $previousPath, ?string $currentPath): void
    {
        if ($previousPath !== $currentPath) {
            $this->deleteUpload($previousPath);
        }
    }

    protected function deleteUpload(?string $path): void
    {
        if (filled($path) && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
