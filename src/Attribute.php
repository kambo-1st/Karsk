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
 * A non standard class, field, method or code attribute.
 *
 * @author Eric Bruneton
 * @author Eugene Kuleshov
 * @author Bohuslav Simek <bohuslav@simek.si>
 */
class Attribute
{
    /**
     * The type of this attribute.
     *
     * @var string
     */
    public $type;

    /**
     * The raw value of this attribute, used only for unknown attributes.
     *
     * @var int[]
     */
    protected $value;

    /**
     * The next attribute in this attribute list. May be <tt>null</tt>.
     *
     * @var Attribute
     */
    public $next;

    /**
     * Constructs a new empty attribute.
     *
     * @param string $type the type of the attribute.
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Returns <tt>true</tt> if this type of attribute is unknown. The default
     * implementation of this method always returns <tt>true</tt>.
     *
     * @return bool <tt>true</tt> if this type of attribute is unknown.
     */
    public function isUnknown() : bool
    {
        return true;
    }

    /**
     * Returns <tt>true</tt> if this type of attribute is a code attribute.
     *
     * @return bool <tt>true</tt> if this type of attribute is a code attribute.
     */
    public function isCodeAttribute() : bool
    {
        return false;
    }

    /**
     * Returns the labels corresponding to this attribute.
     *
     * @return array|null the labels corresponding to this attribute, or <tt>null</tt> if
     *         this attribute is not a code attribute that contains labels.
     */
    protected function getLabels() : ?array
    {
        // this is indeed in the original ASM 5.2 package
        return null;
    }

    /**
     * Reads a {@link #type type} attribute. This method must return a
     * <i>new</i> {@link Attribute} object, of type {@link #type type},
     * corresponding to the <tt>len</tt> bytes starting at the given offset, in
     * the given class reader.
     *
     * NOTE: unused params are also in the original ASM 5.2 package
     *
     * @param ClassReader $cr
     *                    the class that contains the attribute to be read.
     * @param int         $off
     *                    index of the first byte of the attribute's content in
     *                    {@link ClassReader#b cr.b}. The 6 attribute header bytes,
     *                    containing the type and the length of the attribute, are not
     *                    taken into account here.
     * @param int         $len
     *                    the length of the attribute's content.
     * @param array       $buf
     *                    buffer to be used to call {@link ClassReader#readUTF8
     *                    readUTF8}, {@link ClassReader#readClass(int,char[]) readClass}
     *                    or {@link ClassReader#readConst readConst}.
     * @param int         $codeOff
     *                    index of the first byte of code's attribute content in
     *                    {@link ClassReader#b cr.b}, or -1 if the attribute to be read
     *                    is not a code attribute. The 6 attribute header bytes,
     *                    containing the type and the length of the attribute, are not
     *                    taken into account here.
     * @param array       $labels
     *                    the labels of the method's code, or <tt>null</tt> if the
     *                    attribute to be read is not a code attribute.
     *
     * @return Attribute a <i>new</i> {@link Attribute} object corresponding to the given bytes.
     */
    protected function read(
        ClassReader $cr,
        int $off,
        int $len,
        array $buf,
        int $codeOff,
        array $labels
    ) : Attribute {
        $attr        = new self($this->type);
        $attr->value = [];

        /* from: System.arraycopy(cr.b, off, attr.value, 0, len) */
        foreach (range(0, ($len + 0)) as $_upto) {
            $attr->value[$_upto] = $cr->b[$_upto - (0) + $off];
        }

        return $attr;
    }

    /**
     * Returns the byte array form of this attribute.
     *
     * NOTE: unused params are also in the original ASM 5.2 package
     *
     * @param ClassWriter $cw
     *                    the class to which this attribute must be added. This
     *                    parameter can be used to add to the constant pool of this
     *                    class the items that corresponds to this attribute.
     * @param array       $code
     *                    the bytecode of the method corresponding to this code
     *                    attribute, or <tt>null</tt> if this attribute is not a code
     *                    attributes.
     * @param int         $len
     *                    the length of the bytecode of the method corresponding to this
     *                    code attribute, or <tt>null</tt> if this attribute is not a
     *                    code attribute.
     * @param int         $maxStack
     *                    the maximum stack size of the method corresponding to this
     *                    code attribute, or -1 if this attribute is not a code
     *                    attribute.
     * @param int         $maxLocals
     *                    the maximum number of local variables of the method
     *                    corresponding to this code attribute, or -1 if this attribute
     *                    is not a code attribute.
     *
     * @return ByteVector the byte array form of this attribute.
     */
    protected function write(
        ClassWriter $cw,
        array $code,
        int $len,
        int $maxStack,
        int $maxLocals
    ) : ByteVector {
        $v = new ByteVector();
        $v->data = $this->value;

        return $v;
    }

    /**
     * Returns the length of the attribute list that begins with this attribute.
     *
     * @return int the length of the attribute list that begins with this attribute.
     */
    protected function getCount() : int
    {
        $count = 0;
        $attr  = $this;
        while ($attr !== null) {
            $count += 1;
            $attr = $attr->next;
        }

        return $count;
    }

    /**
     * Returns the size of all the attributes in this attribute list.
     *
     * @param ClassWriter $cw
     *                    the class writer to be used to convert the attributes into
     *                    byte arrays, with the {@link #write write} method.
     * @param array       $code
     *                    the bytecode of the method corresponding to these code
     *                    attributes, or <tt>null</tt> if these attributes are not code
     *                    attributes.
     * @param int         $len
     *                    the length of the bytecode of the method corresponding to
     *                    these code attributes, or <tt>null</tt> if these attributes
     *                    are not code attributes.
     * @param int         $maxStack
     *                    the maximum stack size of the method corresponding to these
     *                    code attributes, or -1 if these attributes are not code
     *                    attributes.
     * @param int         $maxLocals
     *                    the maximum number of local variables of the method
     *                    corresponding to these code attributes, or -1 if these
     *                    attributes are not code attributes.
     *
     * @return int the size of all the attributes in this attribute list. This size includes
     *             the size of the attribute headers.
     */
    protected function getSize(ClassWriter $cw, array $code, int $len, int $maxStack, int $maxLocals) : int
    {
        $attr = $this;
        $size = 0;
        while ($attr !== null) {
            $cw->newUTF8($attr->type);
            $size += (count($attr->write($cw, $code, $len, $maxStack, $maxLocals)) + 6);
            $attr = $attr->next;
        }

        return $size;
    }

    /**
     * Writes all the attributes of this attribute list in the given byte
     * vector.
     *
     * @param ClassWriter $cw
     *                    the class writer to be used to convert the attributes into
     *                    byte arrays, with the {@link #write write} method.
     * @param array       $code
     *                    the bytecode of the method corresponding to these code
     *                    attributes, or <tt>null</tt> if these attributes are not code
     *                    attributes.
     * @param int         $len
     *                    the length of the bytecode of the method corresponding to
     *                    these code attributes, or <tt>null</tt> if these attributes
     *                    are not code attributes.
     * @param int         $maxStack
     *                    the maximum stack size of the method corresponding to these
     *                    code attributes, or -1 if these attributes are not code
     *                    attributes.
     * @param int         $maxLocals
     *                    the maximum number of local variables of the method
     *                    corresponding to these code attributes, or -1 if these
     *                    attributes are not code attributes.
     * @param ByteVector  $out
     *                    where the attributes must be written.
     *
     * @return void
     */
    protected function put(
        ClassWriter $cw,
        array $code,
        int $len,
        int $maxStack,
        int $maxLocals,
        ByteVector $out
    ) : void {
        $attr = $this;
        while ($attr !== null) {
            $b = $attr->write($cw, $code, $len, $maxStack, $maxLocals);
            $out->putShort($cw->newUTF8($attr->type))->putInt(count($b) /*from: b.length*/);
            $out->putByteArray($b->data, 0, count($b) /*from: b.length*/);
            $attr = $attr->next;
        }
    }
}
