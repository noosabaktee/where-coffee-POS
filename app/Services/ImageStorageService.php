<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImageStorageService
{
    public function storeDataUri(?string $dataUri, string $directory, ?string $oldPath = null): ?string
    {
        if (! $dataUri || ! str_starts_with($dataUri, 'data:image/')) {
            return $oldPath;
        }

        if (! preg_match('/^data:image\/(png|jpe?g|webp);base64,(.+)$/', $dataUri, $matches)) {
            throw new InvalidArgumentException('Format gambar tidak valid.');
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false || strlen($binary) > 3 * 1024 * 1024) {
            throw new InvalidArgumentException('Ukuran gambar maksimal 3 MB.');
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, $binary);

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
