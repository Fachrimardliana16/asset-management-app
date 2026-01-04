<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class DataEncryptionService
{
    /**
     * Encrypt sensitive data
     */
    public static function encrypt($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::encryptString($value);
        } catch (\Exception $e) {
            Log::channel('security')->error('Encryption failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Decrypt sensitive data
     */
    public static function decrypt($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            Log::channel('security')->error('Decryption failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Hash sensitive data (one-way)
     */
    public static function hash($value): string
    {
        return hash('sha256', $value);
    }

    /**
     * Mask sensitive data for display
     */
    public static function mask($value, $visibleChars = 4): string
    {
        if (empty($value) || strlen($value) <= $visibleChars) {
            return $value;
        }

        $masked = str_repeat('*', strlen($value) - $visibleChars);
        return $masked . substr($value, -$visibleChars);
    }
}
