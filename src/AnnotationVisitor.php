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

/**
 * A visitor to visit a Java annotation. The methods of this class must be
 * called in the following order: ( <tt>visit</tt> | <tt>visitEnum</tt> |
 * <tt>visitAnnotation</tt> | <tt>visitArray</tt> )* <tt>visitEnd</tt>.
 *
 * @author Eric Bruneton
 * @author Eugene Kuleshov
 * @author Bohuslav Simek <bohuslav@simek.si>
 */
class AnnotationVisitor
{
    /**
     * The ASM API version implemented by this visitor. The value of this field
     * must be one of {@link Opcodes#ASM4} or {@link Opcodes#ASM5}.
     *
     * @var int
     */
    protected $api;

    /**
     * The annotation visitor to which this visitor must delegate method calls.
     * May be null.
     *
     * @var AnnotationVisitor
     */
    protected $av;

    /**
     * Constructs a new {@link AnnotationVisitor}.
     *
     * @param int               $api
     *                          the ASM API version implemented by this visitor. Must be one
     *                          of {@link Opcodes#ASM4} or {@link Opcodes#ASM5}.
     * @param AnnotationVisitor $av
     *                          the annotation visitor to which this visitor must delegate
     *                          method calls. May be null.
     */
    public function __construct(int $api, AnnotationVisitor $av = null)
    {
        if ((($api != Opcodes::ASM4) && ($api != Opcodes::ASM5))) {
            throw new IllegalArgumentException();
        }

        $this->api = $api;
        $this->av = $av;
    }

    /**
     * Visits a primitive value of the annotation.
     *
     * @param string $name the value name.
     * @param mixed  $value
     *               the actual value, whose type must be {@link Byte},
     *               {@link Boolean}, {@link Character}, {@link Short},
     *               {@link Integer} , {@link Long}, {@link Float}, {@link Double},
     *               {@link String} or {@link Type} of OBJECT or ARRAY sort. This
     *               value can also be an array of byte, boolean, short, char, int,
     *               long, float or double values (this is equivalent to using
     *               {@link #visitArray visitArray} and visiting each array element
     *               in turn, but is more convenient).
     *
     * @return void
     */
    public function visit(string $name, $value) : void
    {
        if (($this->av != null)) {
            $this->av->visit($name, $value);
        }
    }

    /**
     * Visits an enumeration value of the annotation.
     *
     * @param string $name  the value name.
     * @param string $desc  the class descriptor of the enumeration class.
     * @param string $value the actual enumeration value.
     *
     * @return void
     */
    public function visitEnum(string $name, string $desc, string $value) : void
    {
        if (($this->av != null)) {
            $this->av->visitEnum($name, $desc, $value);
        }
    }

    /**
     * Visits a nested annotation value of the annotation.
     *
     * @param string $name the value name.
     * @param string $desc the class descriptor of the nested annotation class.
     *
     * @return AnnotationVisitor a visitor to visit the actual nested annotation value, or
     *         <tt>null</tt> if this visitor is not interested in visiting this
     *         nested annotation. <i>The nested annotation value must be fully
     *         visited before calling other methods on this annotation
     *         visitor</i>.
     */
    public function visitAnnotation(string $name, string $desc) : AnnotationVisitor
    {
        if (($this->av != null)) {
            return $this->av->visitAnnotation($name, $desc);
        }

        return null;
    }

    /**
     * Visits an array value of the annotation. Note that arrays of primitive
     * types (such as byte, boolean, short, char, int, long, float or double)
     * can be passed as value to {@link #visit visit}. This is what
     * {@link ClassReader} does.
     *
     * @param string $name the value name.
     *
     * @return AnnotationVisitor a visitor to visit the actual array value elements, or
     *                           <tt>null</tt> if this visitor is not interested in visiting these
     *                           values. The 'name' parameters passed to the methods of this
     *                           visitor are ignored. <i>All the array values must be visited
     *                           before calling other methods on this annotation visitor</i>.
     */
    public function visitArray(string $name) : AnnotationVisitor
    {
        if (($this->av != null)) {
            return $this->av->visitArray($name);
        }
        return null;
    }

    /**
     * Visits the end of the annotation.
     *
     * @return void
     */
    public function visitEnd() : void
    {
        if (($this->av != null)) {
            $this->av->visitEnd();
        }
    }
}
