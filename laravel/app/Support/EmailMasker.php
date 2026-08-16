<?php

namespace App\Support;

class EmailMasker
{
    private const MASK_CHAR = '•';

    /**
     * Partially hide an email address for display.
     *
     *   jane.doe@example.com  ->  j••••••e@example.com
     *
     * The first and last characters of the local part are kept so the owner
     * can still recognise the address (and spot a typo), and the domain is
     * left intact so they can tell which provider to check. Anything with a
     * local part of two characters or fewer is masked completely, since
     * showing first + last there would reveal the whole thing.
     */
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
