<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Exceptions\ValidationException;
use finfo;

/**
 * Safe image uploads:
 *  - size limit, checked server-side
 *  - real MIME type sniffed from the file bytes (the browser's claim and the
 *    original filename are ignored), plus a getimagesize() sanity check
 *  - stored under a random name with an extension we choose
 *  - the uploads folder has PHP execution disabled via .htaccess
 */
final class ImageUploader
{
    /**
     * @return string path relative to /public, e.g. "uploads/events/3f9c….jpg"
     * @throws ValidationException
     */
    public function store(array $file, string $field = 'image'): string
    {
        $maxBytes = (int) Config::get('app.uploads.max_bytes');
        $mimes = Config::get('app.uploads.mimes');

        $fail = static fn (string $msg) => new ValidationException([$field => $msg]);

        match ($file['error'] ?? UPLOAD_ERR_NO_FILE) {
            UPLOAD_ERR_OK => null,
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => throw $fail('The image is too large (max ' . ($maxBytes / 1048576) . ' MB).'),
            default => throw $fail('The image could not be uploaded. Please try again.'),
        };

        if (!is_uploaded_file($file['tmp_name'])) {
            throw $fail('Invalid upload.');
        }
        if ($file['size'] > $maxBytes) {
            throw $fail('The image is too large (max ' . ($maxBytes / 1048576) . ' MB).');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset($mimes[$mime]) || @getimagesize($file['tmp_name']) === false) {
            throw $fail('Please upload a JPG, PNG or WebP image.');
        }

        $relativeDir = trim(Config::get('app.uploads.dir'), '/');
        $absoluteDir = BASE_PATH . '/public/' . $relativeDir;
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            throw new \RuntimeException("Upload directory [$absoluteDir] is not writable.");
        }

        $name = bin2hex(random_bytes(16)) . '.' . $mimes[$mime];
        if (!move_uploaded_file($file['tmp_name'], "$absoluteDir/$name")) {
            throw new \RuntimeException('Failed to move uploaded file.');
        }

        return "$relativeDir/$name";
    }

    /** Delete a previously uploaded image. Refuses anything outside the uploads folder. */
    public function delete(?string $path): void
    {
        $relativeDir = trim(Config::get('app.uploads.dir'), '/');
        if (!$path || !str_starts_with($path, $relativeDir . '/') || str_contains($path, '..')) {
            return; // seed images in /assets, or nothing to delete
        }

        $file = BASE_PATH . '/public/' . $path;
        if (is_file($file)) {
            @unlink($file);
        }
    }
}
