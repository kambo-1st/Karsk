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
 * A reference to a type appearing in a class, field or method declaration, or
 * on an instruction. Such a reference designates the part of the class where
 * the referenced type is appearing (e.g. an 'extends', 'implements' or 'throws'
 * clause, a 'new' instruction, a 'catch' clause, a type cast, a local variable
 * declaration, etc).
 *
 * @author  Eric Bruneton
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class TypeReference
{
    public const CLASS_TYPE_PARAMETER = 0x00;   // int
    public const METHOD_TYPE_PARAMETER = 0x01;  // int
    public const CLASS_EXTENDS = 0x10;  // int
    public const CLASS_TYPE_PARAMETER_BOUND = 0x11; // int
    public const METHOD_TYPE_PARAMETER_BOUND = 0x12;    // int
    public const FIELD = 0x13;  // int
    public const METHOD_RETURN = 0x14;  // int
    public const METHOD_RECEIVER = 0x15;    // int
    public const METHOD_FORMAL_PARAMETER = 0x16;    // int
    public const THROWS = 0x17; // int
    public const LOCAL_VARIABLE = 0x40; // int
    public const RESOURCE_VARIABLE = 0x41;  // int
    public const EXCEPTION_PARAMETER = 0x42;    // int
    public const INSTANCEOF = 0x43; // int
    public const NEW = 0x44;// int
    public const CONSTRUCTOR_REFERENCE = 0x45;  // int
    public const METHOD_REFERENCE = 0x46;   // int
    public const CAST = 0x47;   // int
    public const CONSTRUCTOR_INVOCATION_TYPE_ARGUMENT = 0x48;   // int
    public const METHOD_INVOCATION_TYPE_ARGUMENT = 0x49;    // int
    public const CONSTRUCTOR_REFERENCE_TYPE_ARGUMENT = 0x4A;    // int
    public const METHOD_REFERENCE_TYPE_ARGUMENT = 0x4B; // int

    /**
     * The type reference value in Java class file format.
     *
     * @var int
     */
    protected $value;   // int

    /**
     * Creates a new TypeReference.
     *
     * @param int $typeRef
     *            the int encoded value of the type reference, as received in a
     *            visit method related to type annotations, like
     *            visitTypeAnnotation.
     */
    public function __construct(int $typeRef)
    {
        $this->value = $typeRef;
    }

    /**
     * Returns a type reference of the given sort.
     *
     * @param $sort
     *            {@link #FIELD FIELD}, {@link #METHOD_RETURN METHOD_RETURN},
     *            {@link #METHOD_RECEIVER METHOD_RECEIVER},
     *            {@link #LOCAL_VARIABLE LOCAL_VARIABLE},
     *            {@link #RESOURCE_VARIABLE RESOURCE_VARIABLE},
     *            {@link #INSTANCEOF INSTANCEOF}, {@link #NEW NEW},
     *            {@link #CONSTRUCTOR_REFERENCE CONSTRUCTOR_REFERENCE}, or
     *            {@link #METHOD_REFERENCE METHOD_REFERENCE}.
     *
     * @return TypeReference a type reference of the given sort.
     */
    public static function newTypeReference(int $sort) : TypeReference
    {
        return new self($sort << 24);
    }

    public static function newTypeParameterReference(int $sort, int $paramIndex) : TypeReference
    {
        return new self(($sort << 24) | ($paramIndex << 16));
    }

    public static function newTypeParameterBoundReference(int $sort, int $paramIndex, int $boundIndex) : TypeReference
    {
        return new self(($sort << 24) | ($paramIndex << 16) | ($boundIndex << 8));
    }

    public static function newSuperTypeReference(int $itfIndex) : TypeReference
    {
        $itfIndex &= 0xFFFF;

        return new self((self::CLASS_EXTENDS << 24) | ($itfIndex << 8));
    }

    public static function newFormalParameterReference(int $paramIndex) : TypeReference
    {
        return new self((self::METHOD_FORMAL_PARAMETER << 24) | ($paramIndex << 16));
    }

    public static function newExceptionReference(int $exceptionIndex) : TypeReference
    {
        return new self((self::THROWS << 24) | ($exceptionIndex << 8));
    }

    public static function newTryCatchReference(int $tryCatchBlockIndex) : TypeReference
    {
        return new self((self::EXCEPTION_PARAMETER << 24) | ($tryCatchBlockIndex << 8));
    }

    public static function newTypeArgumentReference(int $sort, int $argIndex) : TypeReference
    {
        return new self(($sort << 24) | $argIndex);
    }

    public function getSort()
    {
        return $this->uRShift($this->value, 24);
    }

    public function getTypeParameterIndex()
    {
        return (($this->value & 0x00FF0000) >> 16);
    }

    public function getTypeParameterBoundIndex()
    {
        return (($this->value & 0x0000FF00) >> 8);
    }

    public function getSuperTypeIndex()
    {
        return (($this->value & 0x00FFFF00) >> 8);
    }

    public function getFormalParameterIndex()
    {
        return ((($this->value & 0x00FF0000)) >> 16);
    }

    public function getExceptionIndex()
    {
        return ((($this->value & 0x00FFFF00)) >> 8);
    }

    public function getTryCatchBlockIndex()
    {
        return (($this->value & 0x00FFFF00) >> 8);
    }

    public function getTypeArgumentIndex()
    {
        return ($this->value & 0xFF);
    }

    public function getValue()
    {
        return $this->value;
    }

    private function uRShift($a, $b)
    {
        return ($a >> $b & 0xFF);
    }
}
