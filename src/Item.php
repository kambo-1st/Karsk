<?php
/***
 * ASM: a very small and fast Java bytecode manipulation framework
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
namespace Kambo\Asm;

/**
 * A constant pool item. Constant pool items can be created with the 'newXXX'
 * methods in the {@link ClassWriter} class.
 *
 * @package Kambo\Asm
 * @author  Eric Bruneton
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class Item
{
	public $index;	// int
	public $type;	// int
	public $intVal;	// int
	public $longVal;	// long
	public $strVal1;	// String
	public $strVal2;	// String
	public $strVal3;	// String
	public $hashCode;	// int
	public $next;	// Item

        public function __construct($index = null, $i = null) {
            if ($index !== null) {
                $this->index = $index;
            }

            if ($index !== null && $i !== null) {
		$this->type = $i->type;
		$this->intVal = $i->intVal;
		$this->longVal = $i->longVal;
		$this->strVal1 = $i->strVal1;
		$this->strVal2 = $i->strVal2;
		$this->strVal3 = $i->strVal3;
		$this->hashCode = $i->hashCode;
            }
        }

	public function set_I ($intVal) // [final int intVal]
	{
		$this->type = ClassWriter::$INT;
		$this->intVal = $intVal;
		$this->hashCode = (0x7FFFFFFF & (($this->type + $intVal)));
	}

	public function set_L ($longVal) // [final long longVal]
	{
		$this->type = ClassWriter::$LONG;
		$this->longVal = $longVal;
		$this->hashCode = (0x7FFFFFFF & (($this->type + $longVal)));
	}

	public function set_F ($floatVal) // [final float floatVal]
	{
		$this->type = ClassWriter::$FLOAT;
		$this->intVal = $Float->floatToRawIntBits($floatVal);
		$this->hashCode = (0x7FFFFFFF & (($this->type + $floatVal)));
	}

	public function set_D ($doubleVal) // [final double doubleVal]
	{
		$this->type = ClassWriter::$DOUBLE;
		$this->longVal = $Double->doubleToRawLongBits($doubleVal);
		$this->hashCode = (0x7FFFFFFF & (($this->type + $doubleVal)));
	}

        private function hashCode($s) : int {
            $hash = 0;
            $len = mb_strlen($s, 'UTF-8');
            if($len == 0 )
                return $hash;
            for ($i = 0; $i < $len; $i++) {
                $c = mb_substr($s, $i, 1, 'UTF-8');
                $cc = unpack('V', iconv('UTF-8', 'UCS-4LE', $c))[1];
                $hash = (($hash << 5) - $hash) + $cc;
                $hash &= $hash; // 16bit > 32bit
            }
            return $hash;
        }

    /**
     * Sets this item to an item that do not hold a primitive value.
     *
     * @param type
     *            the type of this item.
     * @param strVal1
     *            first part of the value of this item.
     * @param strVal2
     *            second part of the value of this item.
     * @param strVal3
     *            third part of the value of this item.
     */
    public function set_I_String_String_String ($type, $strVal1, $strVal2, $strVal3) // [final int type, final String strVal1, final String strVal2, final String strVal3]
    {
        $this->type = $type;
        $this->strVal1 = $strVal1;
        $this->strVal2 = $strVal2;
        $this->strVal3 = $strVal3;
        switch ($type) {
                case ClassWriter::$CLASS:
                        $this->intVal = 0;
                case ClassWriter::$UTF8:
                case ClassWriter::$STR:
                case ClassWriter::$MTYPE:
                case ClassWriter::$TYPE_NORMAL:
                        $this->hashCode = (0x7FFFFFFF & (($type + $this->hashCode($strVal1))));
                        return ;
                case ClassWriter::$NAME_TYPE:
                {
                        $this->hashCode = (0x7FFFFFFF & (($type + ($this->hashCode($strVal1) * $this->hashCode($strVal2)))));
                        return ;
                }
                default:
                        $this->hashCode = (0x7FFFFFFF & (($type + (($this->hashCode($strVal1) * $this->hashCode($strVal2)) * $this->hashCode($strVal3)))));
        }
    }

	public function set_String_String_I ($name, $desc, $bsmIndex) // [String name, String desc, int bsmIndex]
	{
		$this->type = ClassWriter::$INDY;
		$this->longVal = $bsmIndex;
		$this->strVal1 = $name;
		$this->strVal2 = $desc;
		$this->hashCode = (0x7FFFFFFF & ((ClassWriter::$INDY + (($bsmIndex * $this->strVal1->hashCode()) * $this->strVal2->hashCode()))));
	}
	public function set_I_I ($position, $hashCode) // [int position, int hashCode]
	{
		$this->type = ClassWriter::$BSM;
		$this->intVal = $position;
		$this->hashCode = $hashCode;
	}
	public function isEqualTo ($i) // [final Item i]
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
				return (($i->intVal == $this->intVal) && $i->strVal1->equals($this->strVal1));
			case ClassWriter::$NAME_TYPE:
                                return (($this->strVal1 == $i->strVal1) && ($this->strVal2 == $i->strVal2));
				return ($i->strVal1->equals($this->strVal1) && $i->strVal2->equals($this->strVal2));
			case ClassWriter::$INDY:
			{
				return ((($i->longVal == $this->longVal) && $i->strVal1->equals($this->strVal1)) && $i->strVal2->equals($this->strVal2));
			}
			default:
                            return (($this->strVal1 == $i->strVal1) && ($this->strVal2 == $i->strVal2)) && ($this->strVal3 == $i->strVal3);
				return (($i->strVal1->equals($this->strVal1) && $i->strVal2->equals($this->strVal2)) && $i->strVal3->equals($this->strVal3));
		}
	}
}
