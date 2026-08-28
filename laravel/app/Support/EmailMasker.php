<?php

namespace App\Support;

class EmailMasker
{
    private const MASK_CHAR = '•';

    public static function mask(?string $email): string
    {
        $email = trim((string) $email);

        if ($email === '') {
            return '';
        }

        $atPosition = mb_strrpos($email, '@');

        // Not a shape we recognise — mask the lot rather than risk leaking it.
        if ($atPosition === false || $atPosition === 0) {
            return str_repeat(self::MASK_CHAR, max(mb_strlen($email), 3));
        }

        $local = mb_substr($email, 0, $atPosition);
        $domain = mb_substr($email, $atPosition + 1);
        $length = mb_strlen($local);

        if ($length <= 2) {
            return str_repeat(self::MASK_CHAR, 3) . '@' . $domain;
        }

        return mb_substr($local, 0, 1)
            . str_repeat(self::MASK_CHAR, $length - 2)
            . mb_substr($local, -1)
            . '@' . $domain;
    }
}
