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

use Kambo\Karsk\Type as KarskType;

/**
 * A Java field or method type. This class can be used to make it easier to
 * manipulate type and method descriptors.
 *
 * @author Eric Bruneton
 * @author Chris Nokleberg
 * @author Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class Type
{
	public const VOID = 0;	// int
	public const BOOLEAN = 1;	// int
	public const CHAR = 2;	// int
	public const BYTE = 3;	// int
	public const SHORT = 4;	// int
	public const INT = 5;	// int
	public const FLOAT = 6;	// int
	public const LONG = 7;	// int
	public const DOUBLE = 8;	// int
	public const ARRAY = 9;	// int
	public const OBJECT = 10;	// int
	public const METHOD = 11;	// int

    /**
     * The sort of this Java type.
     *
     * @var int
     */
	protected $sort;

    /**
     * A buffer containing the internal name of this Java type. This field is
     * only used for reference types.
     *
     * @var array
     */
	protected $buf;

    /**
     * The offset of the internal name of this Java type in {@link #buf buf} or,
     * for primitive types, the size, descriptor and getOpcode offsets for this
     * type (byte 0 contains the size, byte 1 the descriptor, byte 2 the offset
     * for IALOAD or IASTORE, byte 3 the offset for all other instructions).
     *
     * @var int
     */
	protected $off;

    /**
     * The length of the internal name of this Java type.
     *
     * @var int
     */
	protected $len;

    /**
     * Constructs a reference type.
     *
     * @param int   $sort the sort of the reference type to be constructed.
     * @param array $buf  a buffer containing the descriptor of the previous type.
     * @param int   $off  the offset of this descriptor in the previous buffer.
     * @param int   $len  the length of this descriptor.
     */
    public function __construct($sort, $buf, $off, $len)
    {
        $this->sort = $sort;
        $this->buf = $buf;
        $this->off = $off;
        $this->len = $len;
    }

    /**
     * Returns the Java type corresponding to the given internal name.
     *
     * @param string $internalName an internal name.
     *
     * @return self the Java type corresponding to the given internal name.
     */
	public static function getObjectType (string $internalName) : self
	{
        $buf = str_split($internalName);
		return new self (
		    ($buf[0] == '[' ? self::ARRAY : self::OBJECT),
            $buf,
            0,
            count($buf)
        );
	}

	public static function getMethodType_String ($methodDescriptor) // [final String methodDescriptor]
	{
		return self::getType_aC_I($methodDescriptor->toCharArray(), 0);
	}

	public static function getMethodType_Type_Type($returnType, ...$argumentTypes) // [final Type returnType, final Type... argumentTypes]
	{
		return self::getType_String(self::getMethodDescriptor_Type_Type($returnType, ...$argumentTypes));
	}

    /**
     * Returns the Java type corresponding to the given type descriptor. For
     * method descriptors, buf is supposed to contain nothing more than the
     * descriptor itself.
     *
     * @param array $buf a buffer containing a type descriptor.
     * @param int   $off the offset of this descriptor in the previous buffer.
     *
     * @return Type the Java type corresponding to the given type descriptor.
     */
    protected static function getType_aC_I(array $buf, int $off) : Type
    {
        $len = null;
        switch ($buf[$off]) {
            case 'V':
                return new self(self::VOID, NULL, (((((ord('V') << 24)) | ((5 << 16))) | ((0 << 8))) | 0), 1);
            case 'Z':
                return new self(self::BOOLEAN, NULL, (((((ord ('Z') << 24)) | ((0 << 16))) | ((5 << 8))) | 1), 1);
            case 'C':
                return new self(self::CHAR, NULL, (((((ord ('C') << 24)) | ((0 << 16))) | ((6 << 8))) | 1), 1);
            case 'B':
                return new self(self::BYTE, NULL, (((((ord ('B') << 24)) | ((0 << 16))) | ((5 << 8))) | 1), 1);
            case 'S':
                return  new self(self::SHORT, NULL, (((((ord ('S') << 24)) | ((0 << 16))) | ((7 << 8))) | 1), 1);
            case 'I':
                return new self(self::INT, NULL, (((((ord ('I') << 24)) | ((0 << 16))) | ((0 << 8))) | 1), 1);
            case 'F':
                return new self(self::FLOAT, NULL, (((((ord ('F') << 24)) | ((2 << 16))) | ((2 << 8))) | 1), 1);
            case 'J':
                return new self(self::LONG, NULL, (((((ord ('J') << 24)) | ((1 << 16))) | ((1 << 8))) | 2), 1);
            case 'D':
                return new self(self::DOUBLE, NULL, (((((ord ('D') << 24)) | ((3 << 16))) | ((3 << 8))) | 2), 1);
            case '[':
                $len = 1;
                while (($buf[($off + $len)] == '[')) {
                    ++$len;
                }

                if (($buf[($off + $len)] == 'L')) {
                    ++$len;
                    while (($buf[($off + $len)] != ';')) {
                        ++$len;
                    }
                }

                return new self(self::ARRAY, $buf, $off, ($len + 1));
            case 'L':
                $len = 1;
                while (($buf[($off + $len)] != ';')) {
                    ++$len;
                }

                return new self(self::OBJECT, $buf, ($off + 1), ($len - 1));
            default:
                return new self(self::METHOD, $buf, $off, (count($buf) - $off));
        }
    }

    public static function getType_String ($typeDescriptor) // [final String typeDescriptor]
    {
        return self::getType_aC_I($typeDescriptor->toCharArray(), 0);
    }

	public static function getType_Class($c) // [final Class? c]
	{
        switch (gettype($c)) {
            case 'boolean':
                return new self(self::BOOLEAN, NULL, (((((ord ('Z') << 24)) | ((0 << 16))) | ((5 << 8))) | 1), 1);
            case 'integer':
                return new self(self::INT, NULL, (((((ord ('I') << 24)) | ((0 << 16))) | ((0 << 8))) | 1), 1);
            case 'double': // for historical reasons "double" is returned in case of a float, and not simply "float"
                return new self(self::FLOAT, NULL, (((((ord ('F') << 24)) | ((2 << 16))) | ((2 << 8))) | 1), 1);
            case 'string':
                return self::getType_String(self::getDescriptor_Class($c));
            case 'object':
                return self::getType_String(self::getDescriptor_Class($c));
            default:
                throw new IllegalArgumentException("value " . var_export($c, true));
        }
	}

	public static function getType_Constructor ($c) // [final Constructor? c]
	{
		return self::getType_String(self::getConstructorDescriptor($c));
	}

	public static function getType_Method ($m) // [final Method m]
	{
		return self::getType_String(self::getMethodDescriptor_Method($m));
	}

	public static function getArgumentTypes_String ($methodDescriptor) // [final String methodDescriptor]
	{
		$buf = $methodDescriptor->toCharArray();
		$off = 1;
		$size = 0;
		while (true) {
			$car = $buf[$off++];
			if (($car == ')')) {
				break;
			} else if (($car == 'L')) {
			    while (($buf[$off++] != ';')) {
                    ++$size;
                }
			} else if (($car != '[')) {
			    ++$size;
			}
		}

		$args = [];
		$off  = 1;
		$size = 0;
		while ($buf[$off] != ')') {
			$args[$size] = self::getType_aC_I($buf, $off);
			$off += ($args[$size]->len + (( (($args[$size]->sort == self::OBJECT)) ? 2 : 0 )));
			$size += 1;
		}

		return $args;
	}

	public static function getArgumentTypes_Method ($method) // [final Method method]
	{
		$classes = $method->getParameterTypes();
		$types   = [];
		for ($i = (count($classes) - 1); ($i >= 0); --$i) {
			$types[$i] = self::getType_Constructor($classes[$i]);
		}

		return $types;
	}

    /**
     * Returns the Java type corresponding to the return type of the given
     * method descriptor.
     *
     * @param string $methodDescriptor a method descriptor.
     *
     * @return Type the Java type corresponding to the return type of the given method descriptor.
     */
	public static function getReturnType_String($methodDescriptor) : Type
	{
        $buf = str_split($methodDescriptor);
		$off = 1;
		while ( true ) {
			$car = $buf[++$off];
			if (($car == ')')) {
				return self::getType_aC_I($buf, $off);
			} else if (($car == 'L')) {
			    while (($buf[++$off] . ';')) {

                }
			}
		}
	}

	public static function getReturnType_Method ($method) // [final Method method]
	{
		return self::getType_Constructor($method->getReturnType());
	}

    /**
     * Returns the size of the arguments and of the return value of methods of
     * this type. This method should only be used for method types.
     *
     * @return int the size of the arguments (plus one for the implicit this
     *             argument), argSize, and the size of the return value, retSize,
     *             packed into a single
     *             int i = <tt>(argSize &lt;&lt; 2) | retSize</tt>
     *             (argSize is therefore equal to <tt>i &gt;&gt; 2</tt>,
     *             and retSize to <tt>i &amp; 0x03</tt>).
     */
    public function getArgumentsAndReturnSizes() : int
    {
        return self::getArgumentsAndReturnSizesFromDescription($this->getDescriptor());
    }

    /**
     * Computes the size of the arguments and of the return value of a method.
     *
     * @param int desc the descriptor of a method.
     *
     * @return int the size of the arguments of the method (plus one for the
     *             implicit this argument), argSize, and the size of its return
     *             value, retSize, packed into a single int i =
     *             <tt>(argSize &lt;&lt; 2) | retSize</tt> (argSize is therefore equal to
     *             <tt>i &gt;&gt; 2</tt>, and retSize to <tt>i &amp; 0x03</tt>).
     */
	public static function getArgumentsAndReturnSizesFromDescription(string $desc) : int
	{
		$n = 1;
		$c = 1;
		while (true) {
			$car = self::charAt($desc, $c++);
			if (($car == ')')) {
				$car = self::charAt($desc, $c);
				return (($n << 2)
                    | (( (($car == 'V')) ? 0 : (( ((($car == 'D') || ($car == 'J'))) ? 2 : 1 )) )));
			} else  if (($car == 'L')) {
					while ((self::charAt($desc,$c++) != ';'))
					$n += 1;
			} else if (($car == '[')) {
			    while ((($car = self::charAt($desc, $c)) == '[')) {
				    ++$c;
				}

				if ((($car == 'D') || ($car == 'J'))) {
				    $n -= 1;
				}
			} else if ((($car == 'D') || ($car == 'J'))) {
			    $n += 2;
			} else {
			    $n += 1;
			}
		}
	}

    /**
     * Returns the sort of this Java type.
     *
     * @return int {@link #VOID VOID}, {@link #BOOLEAN BOOLEAN}, {@link #CHAR CHAR},
     *             {@link #BYTE BYTE}, {@link #SHORT SHORT}, {@link #INT INT},
     *             {@link #FLOAT FLOAT}, {@link #LONG LONG}, {@link #DOUBLE DOUBLE},
     *             {@link #ARRAY ARRAY}, {@link #OBJECT OBJECT} or {@link #METHOD METHOD}.
     */
	public function getSort () : int
	{
		return $this->sort;
	}

    /**
     * Returns the number of dimensions of this array type. This method should
     * only be used for an array type.
     *
     * @return int the number of dimensions of this array type.
     */
	public function getDimensions () : int
	{
		$i = 1;
		while ($this->buf[($this->off + $i)] == '[') {
			++$i;
		}

		return $i;
	}

    /**
     * Returns the type of the elements of this array type. This method should
     * only be used for an array type.
     *
     * @return Type Returns the type of the elements of this array type.
     */
	public function getElementType () 
	{
			/* match: aC_I */
		return $this->getType_aC_I($this->buf, ($this->off + $this->getDimensions()));
	}

    /**
     * Returns the binary name of the class corresponding to this type. This
     * method must not be used on method types.
     *
     * @return string the binary name of the class corresponding to this type.
     */
	public function getClassName () : ?string
	{
		switch ($this->sort) {
			case self::VOID:
				return "void";
			case self::BOOLEAN:
				return "boolean";
			case self::CHAR:
				return "char";
			case self::BYTE:
				return "byte";
			case self::SHORT:
				return "short";
			case self::INT:
				return "int";
			case self::FLOAT:
				return "float";
			case self::LONG:
				return "long";
			case self::DOUBLE:
				return "double";
			case self::ARRAY:
				$arrayDefinition = $this->getElementType()->getClassName();
				for ($i = $this->getDimensions(); ($i > 0); --$i) {
                    $arrayDefinition .= "[]";
				}

				return $arrayDefinition;
			case self::OBJECT:
                $object = substr($this->buf, $this->off, $this->len);
                return str_replace('/', '.', $object);
			default:
				return null;
		}
	}

    /**
     * Returns the internal name of the class corresponding to this object or
     * array type. The internal name of a class is its fully qualified name (as
     * returned by Class.getName(), where '.' are replaced by '/'. This method
     * should only be used for an object or array type.
     *
     * @return string the internal name of the class corresponding to this object type.
     */
	public function getInternalName () : string
	{
		return substr($this->buf, $this->off, $this->len);
	}

    /**
     * Returns the argument types of methods of this type. This method should
     * only be used for method types.
     *
     * @return array the argument types of methods of this type.
     */
	public function getArgumentTypes() : array
	{
		return $this->getArgumentTypes_String($this->getDescriptor());
	}

    /**
     * Returns the return type of methods of this type. This method should only
     * be used for method types.
     *
     * @return Type the return type of methods of this type.
     */
	public function getReturnType() : Type
	{
		return $this->getReturnType_String($this->getDescriptor());
	}

    // ------------------------------------------------------------------------
    // Conversion to type descriptors
    // ------------------------------------------------------------------------

    /**
     * Returns the descriptor corresponding to this Java type.
     *
     * @return string the descriptor corresponding to this Java type.
     */
	public function getDescriptor() : string
	{
		$buf = [];
		$this->getDescriptor_StringBuilder($buf);

        return implode('', $buf);
	}

	public static function getMethodDescriptor_Type_Type (Type $returnType, Type ...$argumentTypes) : string
	{
		$buf = new StringBuilder();
		$buf->append('(');
		for ($i = 0; ($i < count($argumentTypes)); ++$i) {
			$argumentTypes[$i]->getDescriptor($buf);
		}

		$buf->append(')');
        $returnType->getDescriptor_StringBuilder($buf);

        return implode('', $buf);
	}

	protected function getDescriptor_StringBuilder(&$buf) // [final StringBuilder buf]
	{
		if ($this->buf == null) {
            $buf = str_split((string)$this->uRShift(($this->off & 0xFF000000), 24));
		} else if ($this->sort == self::OBJECT) {
            $buf->append('L');
            $buf->append($this->buf, $this->off, $this->len);
            $buf->append(';');
		} else {
            $buf->append($this->buf, $this->off, $this->len);
		}
	}

    private function uRShift($a, $b)
    {
        return ($a >> $b & 0xFF);
    }

	public static function getInternalName_Class ($c) // [final Class? c]
	{
		return $c->getName()->replace('.', '/');
	}

	public static function getDescriptor_Class ($c) // [final Class? c]
	{
		$buf = new StringBuilder();
			/* match: StringBuilder_Class */
		self::getDescriptor_StringBuilder_Class($buf, $c);
		return $buf->toString();
	}

	public static function getConstructorDescriptor ($c) // [final Constructor? c]
	{
		$parameters = $c->getParameterTypes();
		$buf = new StringBuilder();
		$buf->append('(');
		for ($i = 0; ($i < count($parameters) /*from: parameters.length*/); ++$i) 
		{
			/* match: StringBuilder_Class */
			self::getDescriptor_StringBuilder_Class($buf, $parameters[$i]);
		}

		return $buf->append(")V")->toString();
	}

	public static function getMethodDescriptor_Method ($m) // [final Method m]
	{
		$parameters = $m->getParameterTypes();
		$buf = new StringBuilder();
		$buf->append('(');
		for ($i = 0; ($i < count($parameters) /*from: parameters.length*/); ++$i) 
		{
			/* match: StringBuilder_Class */
			self::getDescriptor_StringBuilder_Class($buf, $parameters[$i]);
		}
		$buf->append(')');
			/* match: StringBuilder_Class */
		self::getDescriptor_StringBuilder_Class($buf, $m->getReturnType());
		return $buf->toString();
	}

	protected static function getDescriptor_StringBuilder_Class(&$buf, $c) // [final StringBuilder buf, final Class? c]
	{
        while (true) {
            switch (true) {
                case $c instanceof KarskType\Integer:
                    $buf .= 'I';
                    return;
                case $c instanceof KarskType\Void_:
                    $buf .= 'V';
                    return;
                case $c instanceof KarskType\Boolean:
                    $buf .= 'Z';
                    return;
                case $c instanceof KarskType\Byte:
                    $buf .= 'B';
                    return;
                case $c instanceof KarskType\Character:
                    $buf .= 'C';
                    return;
                case $c instanceof KarskType\Short:
                    $buf .= 'S';
                    return;
                case $c instanceof KarskType\Double:
                    $buf .= 'D';
                    return;
                case $c instanceof KarskType\Float_:
                    $buf .= 'F';
                    return;
                case $c instanceof KarskType\Long:
                    $buf .= 'J';
                    return;
                case $c instanceof KarskType\Array_:
                    $buf .= '[';
                    $c    = $c->getType();
                case $c instanceof KarskType\Object_:
                    $buf .= 'L';
                    str_replace(".","/", $c->getType());
                    $buf .= ';';
                    return;
                default:
                    // scream
            }
        }
	}

    /**
     * Returns the size of values of this type. This method must not be used for
     * method types.
     *
     * @return int the size of values of this type, i.e., 2 for <tt>long</tt> and
     *             <tt>double</tt>, 0 for <tt>void</tt> and 1 otherwise.
     */
	public function getSize() : int
	{
		return $this->buf == null ? ($this->off & 0xFF) : 1;
	}

    /**
     * Returns a JVM instruction opcode adapted to this Java type. This method
     * must not be used for method types.
     *
     * @param int $opcode a JVM instruction opcode. This opcode must be one of ILOAD,
     *                    ISTORE, IALOAD, IASTORE, IADD, ISUB, IMUL, IDIV, IREM, INEG,
     *                    ISHL, ISHR, IUSHR, IAND, IOR, IXOR and IRETURN.
     *
     * @return int an opcode that is similar to the given opcode, but adapted to
     *             this Java type. For example, if this type is <tt>float</tt> and
     *             <tt>opcode</tt> is IRETURN, this method returns FRETURN.
     */
	public function getOpcode(int $opcode) : int
	{
		if ((($opcode == Opcodes::IALOAD) || ($opcode == Opcodes::IASTORE))) {
            // the offset for IALOAD or IASTORE is in byte 1 of 'off' for
            // primitive types (buf == null)
			return ($opcode + (( (($this->buf == NULL)) ? ((($this->off & 0xFF00)) >> 8) : 4 )));
		}else {
            // the offset for other instructions is in byte 2 of 'off' for
            // primitive types (buf == null)
			return ($opcode + (( (($this->buf == NULL)) ? ((($this->off & 0xFF0000)) >> 16) : 4 )));
		}
	}

    /**
     * Tests if the given object is equal to this type.
     *
     * @param object o the object to be compared to this type.
     *
     * @return bool <tt>true</tt> if the given object is equal to this type.
     */
	public function equals($o) : bool
	{
		if ($this == $o) {
			return true;
		}

		if (!($o instanceof Type)) {
			return false;
		}

		$t = $o;
		if ($this->sort != $t->sort) {
			return false;
		}

		if ($this->sort >= self::ARRAY) {
			if (($this->len != $t->len)) {
				return false;
			}

			for ($i = $this->off, $j = $t->off, $end = ($i + $this->len); ($i < $end); ++$i, ++$j) {
				if (($this->buf[$i] != $t->buf[$j]))
				{
					return false;
				}
			}
		}

		return true;
	}

    /**
     * Returns a hash code value for this type.
     *
     * @return int a hash code value for this type.
     */
	public function hashCode() : int
	{
		$hc = (13 * $this->sort);
		if (($this->sort >= self::ARRAY)) {
			for ($i = $this->off, $end = ($i + $this->len); ($i < $end); ++$i) {
				$hc = (17 * (($hc + $this->buf[$i])));
			}
		}

		return $hc;
	}

    /**
     * Returns a string representation of this type.
     *
     * @return string the descriptor of this type.
     */
	public function toString() : string
	{
		return $this->getDescriptor();
	}

    /**
     * Returns a string representation of this type.
     *
     * @return string the descriptor of this type.
     */
    public function __toString() : string
    {
        return $this->toString();
    }

    private static function charAt($str, $pos)
    {
        return $str{$pos};
    }
}
