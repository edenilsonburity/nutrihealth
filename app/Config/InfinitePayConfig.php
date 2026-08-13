<?php
namespace App\Config;

class InfinitePayConfig
{
    public static function getHandle(): ?string
    {
        $handle = getenv('INFINITEPAY_HANDLE');
        return $handle ?: null;
    }

    public static function isConfigured(): bool
    {
        return self::getHandle() !== null;
    }
}
