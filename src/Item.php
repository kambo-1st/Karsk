<?php
/***
 * ASM: a very small and fast Java bytecode manipulation framework
 * Copyright (c) 2000-2011 INRIA, France Telecom
 * Copyright (c) 2018 PHP version, Bohuslav Šimek
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
namespace Kambo\Asm;

/**
 * A constant pool item. Constant pool items can be created with the 'newXXX'
 * methods in the {@link ClassWriter} class.
 *
 * @author  Eric Bruneton
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class Item
{
    /**
     * Index of this item in the constant pool.
     *
     * @var int
     */
    public $index;

    public $type;

    /**
     * Value of this item, for an integer item.
     *
     * @var int
     */
    public $intVal;

    /**
     * Value of this item, for an integer item.
     *
     * @var float ?
     */
    public $longVal;

    /**
     * First part of the value of this item, for items that do not hold a
     * primitive value.
     *
     * @var string
     */
    public $strVal1;

    /**
     * Second part of the value of this item, for items that do not hold a
     * primitive value.
     *
     * @var string
     */
    public $strVal2;

    /**
     * Third part of the value of this item, for items that do not hold a
     * primitive value.
     *
     * @var string
     */
    public $strVal3;

    /**
     * The hash code value of this constant pool item.
     *
     * @var int
     */
    public $hashCode;

    /**
     * Link to another constant pool item, used for collision lists in the
     * constant pool's hash table.
     *
     * @var Item
     */
    public $next;

    /**
     * Constructs an uninitialized {@link Item} for constant pool element at
     * given position or copy of given item.
     *
     * @param int  $index index of the item to be constructed.
     * @param Item $i the item that must be copied into the item to be constructed.
     */
    public function __construct($index = null, Item $i = null)
    {
        if ($index !== null) {
            $this->index = $index;
        }

        if ($index !== null && $i !== null) {
            $this->type     = $i->type;
            $this->intVal   = $i->intVal;
            $this->longVal  = $i->longVal;
            $this->strVal1  = $i->strVal1;
            $this->strVal2  = $i->strVal2;
            $this->strVal3  = $i->strVal3;
            $this->hashCode = $i->hashCode;
        }
    }

    /**
     * Sets this item to an integer item.
     *
     * @param int $intVal the value of this item.
     *
     * @return void
     */
    public function set_I($intVal)
    {
        $this->type     = ClassWriter::$INT;
        $this->intVal   = $intVal;
        $this->hashCode = (0x7FFFFFFF & (($this->type + $intVal)));
    }

    /**
     * Sets this item to a long item.
     *
     * @param int $longVal the value of this item.
     *
     * @return void
     */
    public function set_L($longVal)
    {
        $this->type     = ClassWriter::$LONG;
        $this->longVal  = $longVal;
        $this->hashCode = (0x7FFFFFFF & (($this->type + $longVal)));
    }

    /**
     * Sets this item to a float item.
     *
     * @param float $floatVal the value of this item.
     *
     * @return void
     */
    public function set_F($floatVal)
    {
        $this->type     = ClassWriter::$FLOAT;
        $this->intVal   = $Float->floatToRawIntBits($floatVal);
        $this->hashCode = (0x7FFFFFFF & (($this->type + $floatVal)));
    }
    /**
     * Sets this item to a double item.
     *
     * @param float $doubleVal the value of this item.
     *
     * @return void
     */
    public function set_D($doubleVal)
    {
        $this->type     = ClassWriter::$DOUBLE;
        $this->longVal  = $Double->doubleToRawLongBits($doubleVal);
        $this->hashCode = (0x7FFFFFFF & (($this->type + $doubleVal)));
    }

    /**
     * Sets this item to an item that do not hold a primitive value.
     *
     * @param int $type the type of this item.
     * @param string $strVal1 first part of the value of this item.
     * @param string $strVal2 second part of the value of this item.
     * @param string $strVal3 third part of the value of this item.
     *
     * @return void
     */
    public function set_I_String_String_String($type, $strVal1, $strVal2, $strVal3)
    {
        $this->type = $type;
        $this->strVal1 = $strVal1;
        $this->strVal2 = $strVal2;
        $this->strVal3 = $strVal3;

        switch ($type) {
            case ClassWriter::$CLASS:
                $this->intVal = 0; // intVal of a class must be zero, see visitInnerClass
            case ClassWriter::$UTF8:
            case ClassWriter::$STR:
            case ClassWriter::$MTYPE:
            case ClassWriter::$TYPE_NORMAL:
                $this->hashCode = (0x7FFFFFFF & (($type + $this->hashCode($strVal1))));
                return ;
            case ClassWriter::$NAME_TYPE:
                $this->hashCode = (0x7FFFFFFF & (($type + ($this->hashCode($strVal1) * $this->hashCode($strVal2)))));
                return ;
            default:
                $this->hashCode = (0x7FFFFFFF &
                    (($type + (($this->hashCode($strVal1) * $this->hashCode($strVal2)) * $this->hashCode($strVal3))))
                );
        }
    }

    /**
     * Sets the item to an InvokeDynamic item.
     *
     * @param string $name invokedynamic's name.
     * @param string $desc invokedynamic's desc.
     * @param int    $bsmIndex zero based index into the class attribute BootrapMethods.
     *
     * @return void
     */
    public function set_String_String_I($name, $desc, $bsmIndex)
    {
        $this->type     = ClassWriter::$INDY;
        $this->longVal  = $bsmIndex;
        $this->strVal1  = $name;
        $this->strVal2  = $desc;
        $this->hashCode = (0x7FFFFFFF &
            ((ClassWriter::$INDY + (($bsmIndex * $this->strVal1->hashCode()) * $this->strVal2->hashCode())))
        );
    }

    /**
     * Sets the item to a BootstrapMethod item.
     *
     * @param int $position position in byte in the class attribute BootrapMethods.
     * @param int $hashCode hashcode of the item. This hashcode is processed from the
     *                      hashcode of the bootstrap method and the hashcode of all
     *                      bootstrap arguments.
     *
     * @return void
     */
    public function set_I_I($position, $hashCode)
    {
        $this->type     = ClassWriter::$BSM;
        $this->intVal   = $position;
        $this->hashCode = $hashCode;
    }

    /**
     * Indicates if the given item is equal to this one. <i>This method assumes
     * that the two items have the same {@link #type}</i>.
     *
     * @param Item $i the item to be compared to this one. Both items must have the
     *                same {@link #type}.
     *
     * @return bool true if the given item if equal to this one, false otherwise.
     */
    public function isEqualTo($i)
    {
        switch ($this->type) {
            case ClassWriter::$UTF8:
            case ClassWriter::$STR:
            case ClassWriter::$CLASS:
            case ClassWriter::$MTYPE:
            case ClassWriter::$TYPE_NORMAL:
                return ($this->strVal1 == $i->strVal1);
            case ClassWriter::$TYPE_MERGED:
            case ClassWriter::$LONG:
            case ClassWriter::$DOUBLE:
                return ($i->longVal == $this->longVal);
            case ClassWriter::$INT:
            case ClassWriter::$FLOAT:
                return ($i->intVal == $this->intVal);
            case ClassWriter::$TYPE_UNINIT:
                return (($i->intVal == $this->intVal) && ($i->strVal1 == $this->strVal1));
            case ClassWriter::$NAME_TYPE:
                return (($this->strVal1 == $i->strVal1) && ($this->strVal2 == $i->strVal2));
            case ClassWriter::$INDY:
                return ($i->longVal == $this->longVal)
                        && ($this->strVal1 == $i->strVal1)
                        && ($this->strVal2 == $i->strVal2);
            default:
                return ($this->strVal1 == $i->strVal1)
                    && ($this->strVal2 == $i->strVal2)
                    && ($this->strVal3 == $i->strVal3);
        }
    }

    private function hashCode($s) : int
    {
        // Taken from https://stackoverflow.com/questions/8804875/php-internal-hashcode-function/40688976#40688976
        $hash = 0;
        $len = mb_strlen($s, 'UTF-8');
        if ($len == 0) {
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
