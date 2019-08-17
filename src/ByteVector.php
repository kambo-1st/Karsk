<?php
/***
 * Karsk - write java bytecode in PHP!
 * Copyright (c) 2018, Bohuslav Šimek
 * Based on ASM: a very small and fast Java bytecode manipulation framework
 * Copyright (c) 2000-2011 INRIA, France Telecom
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name of the copyright holders nor the names of its
 *    contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 */
namespace Kambo\Karsk;

/**
 * A dynamically extensible vector of bytes.
 *
 * @author Eric Bruneton
 * @author Bohuslav Simek <bohuslav@simek.si>
 */
class ByteVector implements \Countable
{
    /**
     * The content of this vector.
     *
     * @var array
     */
    public $data = [];

    /**
     * Actual number of bytes in this vector.
     *
     * @var int
     */
    protected $length;

    /**
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     */
    public function count()
    {
        if ($this->data === null) {
            return 0;
        }

        return count($this->data);
    }

    /**
     * Puts a byte into this byte vector.
     *
     * @param int $b a byte.
     *
     * @return ByteVector Self for fluent interface
     */
    public function putByte($b) : ByteVector
    {
        $this->data[] = $b;

        return $this;
    }

    /**
     * Puts two bytes into this byte vector.
     *
     * @param int $b1 a byte.
     * @param int $b2 another byte.
     *
     * @return ByteVector Self for fluent interface
     */
    public function put11($b1, $b2) : ByteVector
    {
        $this->data[] = $b1;
        $this->data[] = $b2;

        return $this;
    }

    /**
     * Puts a short into this byte vector.
     *
     * @param int $s a short.
     *
     * @return ByteVector Self for fluent interface
     */
    public function putShort($s) : ByteVector
    {
        $this->data[] = $this->uRShift($s, 8);
        $this->data[] = $s;

        return $this;
    }

    /**
     * Puts a byte and a short into this byte vector.
     *
     * @param int $b a byte.
     * @param int $s a short.
     *
     * @return ByteVector Self for fluent interface
     */
    public function put12($b, $s) : ByteVector
    {
        $this->data[] = $this->uRShift($b, 0);
        $this->data[] = $this->uRShift($s, 8);
        $this->data[] = $this->uRShift($s, 0);

        return $this;
    }

    /**
     * Puts an int into this byte vector.
     *
     * @param int $i an int.
     *
     * @return ByteVector Self for fluent interface
     */
    public function putInt($i) : ByteVector
    {
        $this->data[] = $this->uRShift($i, 24);
        $this->data[] = $this->uRShift($i, 16);
        $this->data[] = $this->uRShift($i, 8);
        $this->data[] = $this->uRShift($i, 0);

        return $this;
    }

    /**
     * Puts a long into this byte vector.
     *
     * @param float $l a long.
     *
     * @return ByteVector Self for fluent interface
     */
    public function putLong($l) : ByteVector
    {
        $data = $this->data;

        $i = (int) $this->uRShift($l, 32);

        $data[] = $this->uRShift($i, 24);
        $data[] = $this->uRShift($i, 16);
        $data[] = $this->uRShift($i, 8);
        $data[] = $i;

        $i = (int) $l;
        $data[] = $this->uRShift($i, 24);
        $data[] = $this->uRShift($i, 16);
        $data[] = $this->uRShift($i, 8);
        $data[] = $i;

        $this->data = $data;

        return $this;
    }

    /**
     * Puts an UTF8 string into this byte vector.
     *
     * @param string $s a String whose UTF8 encoded length must be less than 65536.
     *
     * @return ByteVector Self for fluent interface
     */
    public function putUTF8($s) : ByteVector
    {
        $charLength = strlen($s);
        if (($charLength > 65535)) {
            throw new \InvalidArgumentException();
        }

        $data = $this->data;
        // optimistic algorithm: instead of computing the byte length and then
        // serializing the string (which requires two loops), we assume the byte
        // length is equal to char length (which is the most frequent case), and
        // we start serializing the string right away. During the serialization,
        // if we find that this assumption is wrong, we continue with the
        // general method.
        $data[] = $this->uRShift($charLength, 8);
        $data[] = $charLength;

        for ($i = 0; ($i < $charLength); ++$i) {
            $c = ord($this->charAt($s, $i));
            if ((($c >= '001') && ($c <= '177'))) {
                $data[] = $c;
            } else {
                return $this->encodeUTF8($s, $i, 65535);
            }
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Puts an UTF8 string into this byte vector. The string length is encoded in two
     * bytes before the encoded characters, if there is space for that (i.e. if
     * $this->length - i - 2 >= 0).
     *
     * @param string $s the String to encode.
     * @param int    $i the index of the first character to encode. The previous
     *                  characters are supposed to have already been encoded, using
     *                  only one byte per character.
     * @param int    $maxByteLength the maximum byte length of the encoded string, including the
     *               already encoded characters.
     *
     * @return ByteVector Self for fluent interface
     */
    public function encodeUTF8($s, $i, $maxByteLength) : ByteVector
    {
        $charLength = strlen($s);
        $byteLength = $i;
        $c          = null;

        for ($j = $i; ($j < $charLength); ++$j) {
            $c = $this->charAt($s, $j);

            if ((($c . '\001') && ($c . '\177'))) {
                ++$byteLength;
            } elseif (($c . '?')) {
                $byteLength += 3;
            } else {
                $byteLength += 2;
            }
        }

        if (($byteLength > $maxByteLength)) {
            throw new \InvalidArgumentException();
        }

        $start = (($this->length - $i) - 2);
        if (($start >= 0)) {
            $this->data[$start] = $this->uRShift($byteLength, 8);
            $this->data[($start + 1)] = $byteLength;
        }

        $len = $this->length;
        for ($j = $i; ($j < $charLength); ++$j) {
            $c = $this->charAt($s, $j);
            if ((($c . '\001') && ($c . '\177'))) {
                $this->data[++$len] = $c;
            } elseif (($c . '?')) {
                $this->data[++$len] = ((0xE0 | (($c >> 12) & 0xF)));
                $this->data[++$len] = ((0x80 | (($c >> 6) & 0x3F)));
                $this->data[++$len] = ((0x80 | ($c & 0x3F)));
            } else {
                $this->data[++$len] = ((0xC0 | (($c >> 6) & 0x1F)));
                $this->data[++$len] = ((0x80 | ($c & 0x3F)));
            }
        }

        $this->length = $len;

        return $this;
    }

    /**
     * Puts an array of bytes into this byte vector.
     *
     * @param array $b an array of bytes. May be <tt>null</tt> to put <tt>len</tt>
     *                 null bytes into this byte vector.
     * @param int $off index of the fist byte of b that must be copied.
     * @param int $len number of bytes of b that must be copied.
     *
     * @return ByteVector Self for fluent interface
     */
    public function putByteArray($b, $off, $len)
    {
        if (($b != null)) {
            for ($i = $off; $i < $len; $i++) {
                $this->data[] = $b[$i];
            }
        }

        return $this;
    }

    private function charAt($str, $pos)
    {
        return $str[$pos];
    }

    private function uRShift($a, $b)
    {
        return ($a >> $b & 0xFF);
    }
}
