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
 * The path to a type argument, wildcard bound, array element type, or static
 * inner type within an enclosing type.
 *
 * @author  Eric Bruneton
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class TypePath
{
    /**
     * A type path step that steps into the element type of an array type. See
     * {@link #getStep getStep}.
     *
     * @var int
     */
    public const ARRAY_ELEMENT = 0;

    /**
     * A type path step that steps into the nested type of a class type. See
     * {@link #getStep getStep}.
     *
     * @var int
     */
    public const INNER_TYPE = 1;

    /**
     * A type path step that steps into the bound of a wildcard type. See
     * {@link #getStep getStep}.
     *
     * @var int
     */
    public const WILDCARD_BOUND = 2;

    /**
     * A type path step that steps into a type argument of a generic type. See
     * {@link #getStep getStep}.
     *
     * @var int
     */
    public const TYPE_ARGUMENT = 3;

    /**
     * The byte array where the path is stored, in Java class file format.
     *
     * @var array
     */
    public $b;

    /**
     * The offset of the first byte of the type path in 'b'.
     *
     * @var int
     */
    public $offset;

    /**
     * Creates a new type path.
     *
     * @param array $b
     *              the byte array containing the type path in Java class file
     *              format.
     * @param int   $offset
     *              the offset of the first byte of the type path in 'b'.
     */
    public function __construct(array $b, int $offset)
    {
        $this->b = $b;
        $this->offset = $offset;
    }

    /**
     * Returns the length of this path.
     *
     * @return int the length of this path.
     */
    public function getLength() : int
    {
        return $this->b[$this->offset];
    }

    /**
     * Returns the value of the given step of this path.
     *
     * @param int $index an index between 0 and {@link #getLength()}, exclusive.
     *
     * @return int {@link #ARRAY_ELEMENT ARRAY_ELEMENT}, {@link #INNER_TYPE
     *         INNER_TYPE}, {@link #WILDCARD_BOUND WILDCARD_BOUND}, or
     *         {@link #TYPE_ARGUMENT TYPE_ARGUMENT}.
     */
    public function getStep(int $index) : int
    {
        return $this->b[(($this->offset + (2 * $index)) + 1)];
    }

    /**
     * Returns the index of the type argument that the given step is stepping
     * into. This method should only be used for steps whose value is
     * {@link #TYPE_ARGUMENT TYPE_ARGUMENT}.
     *
     * @param int $index an index between 0 and {@link #getLength()}, exclusive.
     *
     * @return int  the index of the type argument that the given step is stepping
     *              into.
     */
    public function getStepArgument(int $index) : int
    {
        return $this->b[(($this->offset + (2 * $index)) + 2)];
    }

    /**
     * Converts a type path in string form, in the format used by
     * {@link #toString()}, into a TypePath object.
     *
     * @param string $typePath a type path in string form, in the format used by
     *                         {@link #toString()}. May be null or empty.
     *
     * @return TypePath the corresponding TypePath object, or null if the path is empty.
     */
    public static function fromString(string $typePath) : TypePath
    {
        if (($typePath === null) || (strlen($typePath) === 0)) {
            return null;
        }

        $n   = strlen($typePath);
        $out = new ByteVector();
        $out->putByte(0);

        for ($i = 0; ($i < $n);) {
            $c = self::charAt($typePath, $i++);
            if (($c == '[')) {
                $out->put11(self::ARRAY_ELEMENT, 0);
            } elseif (($c == '.')) {
                $out->put11(self::INNER_TYPE, 0);
            } elseif (($c == '*')) {
                $out->put11(self::WILDCARD_BOUND, 0);
            } elseif (($c >= '0') && ($c <= '9')) {
                $typeArg = $c - '0';
                while (($i < $n) && (($c = self::charAt($typePath, $i)) >= '0') && ($c <= '9')) {
                    $typeArg = ((($typeArg * 10) + $c) - '0');
                    $i += 1;
                }

                if ((($i < $n) && (self::charAt($typePath, $i) == ';'))) {
                    $i += 1;
                }

                $out->put11(self::TYPE_ARGUMENT, $typeArg);
            }
        }

        $out->data[0] = ((count($out) / 2));

        return new self($out->data, 0);
    }

    /**
     * Returns a string representation of this type path. {@link #ARRAY_ELEMENT
     * ARRAY_ELEMENT} steps are represented with '[', {@link #INNER_TYPE
     * INNER_TYPE} steps with '.', {@link #WILDCARD_BOUND WILDCARD_BOUND} steps
     * with '*' and {@link #TYPE_ARGUMENT TYPE_ARGUMENT} steps with their type
     * argument index in decimal form followed by ';'.
     *
     * @return string
     */
    public function toString() : string
    {
        $length = $this->getLength();
        $result = '';
        for ($i = 0; ($i < $length); ++$i) {
            switch ($this->getStep($i)) {
                case self::ARRAY_ELEMENT:
                    $result .= '[';
                    break;
                case self::INNER_TYPE:
                    $result .='.';
                    break;
                case self::WILDCARD_BOUND:
                    $result .='*';
                    break;
                case self::TYPE_ARGUMENT:
                    $result .= $this->getStepArgument($i).';';
                    break;
                default:
                    $result .='_';
            }
        }

        return $result;
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    private static function charAt($str, $pos)
    {
        return $str{$pos};
    }
}
