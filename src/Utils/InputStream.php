<?php

namespace Kambo\Karsk\Utils;

/**
 * Class InputStream
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
interface InputStream
{
    public function close();
    public function read(array &$b = null, ?int $off = null, ?int $len = null);
    public function available(): int;
}
