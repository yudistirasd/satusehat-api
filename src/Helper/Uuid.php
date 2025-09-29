<?php

namespace Satusehat\Integration\Helper;

class Uuid
{
    /**
     * Generate UUID v4 (random-based).
     *
     * @throws \Exception
     */
    public static function generateV4(): string
    {
        $data = random_bytes(16);

        // Set versi ke 4 (0100xxxx)
        $data[6] = chr((ord($data[6]) & 0x0F) | 0x40);

        // Set variant ke RFC 4122 (10xxxxxx)
        $data[8] = chr((ord($data[8]) & 0x3F) | 0x80);

        $hex = bin2hex($data);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}
