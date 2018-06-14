<?php

namespace Kambo\Karsk\Utils;

/**
 * Simple hash function
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class HashCode
{
    /**
     * Generate hash for the given value.
     * Taken from https://stackoverflow.com/questions/8804875/php-internal-hashcode-function/40688976#40688976
     *
     * @param string $s String for which the hash will be generated.
     *
     * @return int
     */
    public function get(string $s) : int
    {
        $hash = 0;
        $len  = mb_strlen($s, 'UTF-8');
        if ($len === 0) {
            return $hash;
        }

        for ($i = 0; $i < $len; $i++) {
            $c = mb_substr($s, $i, 1, 'UTF-8');
            $cc = unpack('V', iconv('UTF-8', 'UCS-4LE', $c))[1];
            $hash = (($hash << 5) - $hash) + $cc;
            $hash &= $hash; // 16bit > 32bit
        }

        return $hash;
    }
}
