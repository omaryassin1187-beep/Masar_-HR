<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SignatureStorageService
{
    private const MAX_BYTES    = 2 * 1024 * 1024; // 2MB
    private const ALLOWED_MIME = ['image/png', 'image/jpeg'];

    public function store(string $base64DataUri, string $folder, string $filenamePrefix): string
    {
        $binary = $this->decodeAndValidate($base64DataUri);

        $path = sprintf(
            '%s/%s_%s_%s.png',
            $folder,
            $filenamePrefix,
            now()->format('Ymd_His'),
            Str::random(8)
        );

        Storage::disk('private')->put($path, $binary);

        return $path;
    }

    private function decodeAndValidate(string $base64DataUri): string
    {
        if (!preg_match('#^data:image/(png|jpeg);base64,#i', $base64DataUri)) {
            throw ValidationException::withMessages(['signature' => 'Invalid signature format']);
        }

        $binary = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64DataUri));

        if ($binary === false || strlen($binary) > self::MAX_BYTES) {
            throw ValidationException::withMessages(['signature' => 'Signature file size is not acceptable']);
        }

        // فحص حقيقي إنه الملف صورة فعلاً (ما بيحتاج GD، فقط قراءة الـ header)
        $info = @getimagesizefromstring($binary);
        if (!$info || !in_array($info['mime'], self::ALLOWED_MIME, true)) {
            throw ValidationException::withMessages(['signature' => 'Signature file is corrupted or unsupported']);
        }

        return $binary;
    }
}
