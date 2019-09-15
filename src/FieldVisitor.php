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

use Kambo\Karsk\Exception\IllegalArgumentException;
use Kambo\Karsk\Exception\RuntimeException;

/**
 * A visitor to visit a Java field. The methods of this class must be called in
 * the following order: ( <tt>visitAnnotation</tt> |
 * <tt>visitTypeAnnotation</tt> | <tt>visitAttribute</tt> )* <tt>visitEnd</tt>.
 *
 * @author Eric Bruneton
 * @author Bohuslav Simek <bohuslav@simek.si>
 */
abstract class FieldVisitor
{
    /**
     * The ASM API version implemented by this visitor. The value of this field
     * must be one of {@link Opcodes#ASM4} or {@link Opcodes#ASM5}.
     *
     * @var int
     */
    protected $api;

    /**
     * The field visitor to which this visitor must delegate method calls. May
     * be null.
     *
     * @var FieldVisitor
     */
    public $fv;

    /**
     * Constructs a new {@link FieldVisitor}.
     *
     * @param int          $api
     *            the ASM API version implemented by this visitor. Must be one
     *            of {@link Opcodes#ASM4} or {@link Opcodes#ASM5}.
     * @param FieldVisitor $fv
     *            the field visitor to which this visitor must delegate method
     *            calls. May be null.
     *
     * @throws IllegalArgumentException
     */
    public function __construct(int $api, FieldVisitor $fv = null)
    {
        if (($api != Opcodes::ASM4) && ($api != Opcodes::ASM5)) {
            throw new IllegalArgumentException();
        }

        $this->api = $api;
        $this->fv = $fv;
    }

    /**
     * Visits an annotation of the field.
     *
     * @param string $desc
     *            the class descriptor of the annotation class.
     * @param bool   $visible
     *            <tt>true</tt> if the annotation is visible at runtime.
     *
     * @return AnnotationVisitor a visitor to visit the annotation values, or <tt>null</tt> if
     *         this visitor is not interested in visiting this annotation.
     */
    public function visitAnnotation(string $desc, bool $visible)
    {
        if ($this->fv != null) {
            return $this->fv->visitAnnotation($desc, $visible);
        }

        return null;
    }

    /**
     * Visits an annotation on the type of the field.
     *
     * @param int      $typeRef
     *            a reference to the annotated type. The sort of this type
     *            reference must be {@link TypeReference#FIELD FIELD}. See
     *            {@link TypeReference}.
     * @param $typePath
     *            the path to the annotated type argument, wildcard bound, array
     *            element type, or static inner type within 'typeRef'. May be
     *            <tt>null</tt> if the annotation targets 'typeRef' as a whole.
     * @param string   $desc
     *            the class descriptor of the annotation class.
     * @param bool     $visible
     *            <tt>true</tt> if the annotation is visible at runtime.
     *
     * @return AnnotationVisitor a visitor to visit the annotation values, or <tt>null</tt> if
     *                           this visitor is not interested in visiting this annotation.
     */
    public function visitTypeAnnotation(int $typeRef, $typePath, string $desc, bool $visible)
    {
        if ($this->api < Opcodes::ASM5) {
            throw new RuntimeException();
        }

        if ($this->fv !== null) {
            return $this->fv->visitTypeAnnotation($typeRef, $typePath, $desc, $visible);
        }

        return null;
    }

    /**
     * Visits a non standard attribute of the field.
     *
     * @param $attr an attribute.
     *
     * @return void
     */
    public function visitAttribute($attr)
    {
        if ($this->fv != null) {
            $this->fv->visitAttribute($attr);
        }
    }

    /**
     * Visits the end of the field. This method, which is the last one to be
     * called, is used to inform the visitor that all the annotations and
     * attributes of the field have been visited.
     *
     * @return void
     */
    public function visitEnd() : void
    {
        if ($this->fv != null) {
            $this->fv->visitEnd();
        }
    }
}
