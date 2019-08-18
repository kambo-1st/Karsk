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
 * An {@link FieldVisitor} that generates Java fields in bytecode form.
 *
 * @author Eric Bruneton
 * @author Bohuslav Simek <bohuslav@simek.si>
 */
class FieldWriter extends FieldVisitor
{
    /**
     * The class writer to which this field must be added.
     *
     * @var ClassWriter
     */
    protected $cw;

    protected $access;  // int
    protected $name;    // int
    protected $desc;    // int
    protected $signature;   // int
    protected $value;   // int
    protected $anns;    // AnnotationWriter
    protected $ianns;   // AnnotationWriter
    protected $tanns;   // AnnotationWriter
    protected $itanns;  // AnnotationWriter
    protected $attrs;   // Attribute

    /**
     * Constructs a new {@link FieldWriter}.
     *
     * @param ClassWriter $cw
     *            the class writer to which this field must be added.
     * @param int $access
     *            the field's access flags (see {@link Opcodes}).
     * @param string $name
     *            the field's name.
     * @param string $desc
     *            the field's descriptor (see {@link Type}).
     * @param string $signature
     *            the field's signature. May be <tt>null</tt>.
     * @param mixed $value
     *            the field's constant value. May be <tt>null</tt>.
     *
     * @throws Exception\IllegalArgumentException
     */
    public function __construct(ClassWriter $cw, int $access, string $name, string $desc, ?string $signature, $value)
    {
        parent::__construct(Opcodes::ASM4);

        if ($cw->firstField == null) {
            $cw->firstField = $this;
        } else {
            $cw->lastField->fv = $this;
        }
        
        $cw->lastField = $this;
        $this->cw = $cw;
        $this->access = $access;
        $this->name = $cw->newUTF8($name);
        $this->desc = $cw->newUTF8($desc);

        if (ClassReader::SIGNATURES && $signature != null) {
            $this->signature = $cw->newUTF8($signature);
        }

        if ($value != null) {
            $this->value = $cw->newConstItem($value)->index;
        }

        return $this;
    }

    // ------------------------------------------------------------------------
    // Implementation of the FieldVisitor abstract class
    // ------------------------------------------------------------------------

    /**
     * Visits an annotation of the field.
     *
     * @param string $desc
     *            the class descriptor of the annotation class.
     * @param bool $visible
     *            <tt>true</tt> if the annotation is visible at runtime.
     *
     * @return AnnotationVisitor a visitor to visit the annotation values, or <tt>null</tt> if
     *         this visitor is not interested in visiting this annotation.
     */
    public function visitAnnotation(string $desc, bool $visible)
    {
        if (!ClassReader::ANNOTATIONS) {
            return null;
        }

        $bv = new ByteVector();
        $bv->putShort($this->cw->newUTF8($desc))->putShort(0);
        $aw = new AnnotationWriter($this->cw, true, $bv, $bv, 2);
        if ($visible) {
            $aw->next = $this->anns;
            $this->anns = $aw;
        } else {
            $aw->next = $this->ianns;
            $this->ianns = $aw;
        }

        return $aw;
    }

    /**
     * Visits an annotation on the type of the field.
     *
     * @param int $typeRef
     *            a reference to the annotated type. The sort of this type
     *            reference must be {@link TypeReference#FIELD FIELD}. See
     *            {@link TypeReference}.
     * @param $typePath
     *            the path to the annotated type argument, wildcard bound, array
     *            element type, or static inner type within 'typeRef'. May be
     *            <tt>null</tt> if the annotation targets 'typeRef' as a whole.
     * @param string $desc
     *            the class descriptor of the annotation class.
     * @param bool $visible
     *            <tt>true</tt> if the annotation is visible at runtime.
     *
     * @return AnnotationWriter a visitor to visit the annotation values, or <tt>null</tt> if
     *         this visitor is not interested in visiting this annotation.
     */
    public function visitTypeAnnotation(int $typeRef, $typePath, string $desc, bool $visible)
    {
        if (!ClassReader::ANNOTATIONS) {
            return null;
        }

        $bv = new ByteVector();
        AnnotationWriter::putTarget($typeRef, $typePath, $bv);
        $bv->putShort($this->cw->newUTF8($desc))->putShort(0);
        $aw = new AnnotationWriter($this->cw, true, $bv, $bv, (count($bv) /*from: bv.length*/ - 2));
        if ($visible) {
            $aw->next = $this->tanns;
            $this->tanns = $aw;
        } else {
            $aw->next = $this->itanns;
            $this->itanns = $aw;
        }

        return $aw;
    }

    /**
     * Visits a non standard attribute of the field.
     *
     * @param $attr an attribute.
     *
     * @return void
     */
    public function visitAttribute($attr) // [final Attribute attr]
    {
        $attr->next = $this->attrs;
        $this->attrs = $attr;
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
    }

    // ------------------------------------------------------------------------
    // Utility methods
    // ------------------------------------------------------------------------

    /**
     * Returns the size of this field.
     *
     * @return int the size of this field.
     */
    public function getSize() : int
    {
        $size = 8;
        if (($this->value != 0)) {
            $this->cw->newUTF8("ConstantValue");
            $size += 8;
        }

        if (((($this->access & Opcodes::ACC_SYNTHETIC)) != 0)) {
            if ((($this->cw->version & 0xFFFF) < Opcodes::V1_5)
                || ((($this->access & ClassWriter::$ACC_SYNTHETIC_ATTRIBUTE)) != 0)) {
                $this->cw->newUTF8("Synthetic");
                $size += 6;
            }
        }

        if (((($this->access & Opcodes::ACC_DEPRECATED)) != 0)) {
            $this->cw->newUTF8("Deprecated");
            $size += 6;
        }

        if ((ClassReader::SIGNATURES && ($this->signature != 0))) {
            $this->cw->newUTF8("Signature");
            $size += 8;
        }

        if ((ClassReader::ANNOTATIONS && ($this->anns != null))) {
            $this->cw->newUTF8("RuntimeVisibleAnnotations");
            $size += (8 + $this->anns->getSize());
        }

        if ((ClassReader::ANNOTATIONS && ($this->ianns != null))) {
            $this->cw->newUTF8("RuntimeInvisibleAnnotations");
            $size += (8 + $this->ianns->getSize());
        }

        if ((ClassReader::ANNOTATIONS && ($this->tanns != null))) {
            $this->cw->newUTF8("RuntimeVisibleTypeAnnotations");
            $size += (8 + $this->tanns->getSize());
        }

        if ((ClassReader::ANNOTATIONS && ($this->itanns != null))) {
            $this->cw->newUTF8("RuntimeInvisibleTypeAnnotations");
            $size += (8 + $this->itanns->getSize());
        }

        if (($this->attrs != null)) {
            $size += $this->attrs->getSize($this->cw, null, 0, -1, -1);
        }

        return $size;
    }

    /**
     * Puts the content of this field into the given byte vector.
     *
     * @param ByteVector $out where the content of this field must be put.
     *
     * @return void
     */
    public function put(ByteVector $out) : void
    {
        $FACTOR = ClassWriter::$TO_ACC_SYNTHETIC;
        $mask = ((Opcodes::ACC_DEPRECATED | ClassWriter::$ACC_SYNTHETIC_ATTRIBUTE)
            | (((($this->access & ClassWriter::$ACC_SYNTHETIC_ATTRIBUTE)) / $FACTOR)));

        $out->putShort(($this->access & ~$mask))->putShort($this->name)->putShort($this->desc);
        $attributeCount = 0;
        if (($this->value != 0)) {
            ++$attributeCount;
        }

        if (((($this->access & Opcodes::ACC_SYNTHETIC)) != 0)) {
            if ((((($this->cw->version & 0xFFFF)) < Opcodes::V1_5)
                || ((($this->access & ClassWriter::$ACC_SYNTHETIC_ATTRIBUTE)) != 0))) {
                ++$attributeCount;
            }
        }

        if (((($this->access & Opcodes::ACC_DEPRECATED)) != 0)) {
            ++$attributeCount;
        }

        if ((ClassReader::SIGNATURES && ($this->signature != 0))) {
            ++$attributeCount;
        }

        if ((ClassReader::ANNOTATIONS && ($this->anns != null))) {
            ++$attributeCount;
        }

        if ((ClassReader::ANNOTATIONS && ($this->ianns != null))) {
            ++$attributeCount;
        }

        if ((ClassReader::ANNOTATIONS && ($this->tanns != null))) {
            ++$attributeCount;
        }

        if ((ClassReader::ANNOTATIONS && ($this->itanns != null))) {
            ++$attributeCount;
        }

        if (($this->attrs != null)) {
            $attributeCount += $this->attrs->getCount();
        }

        $out->putShort($attributeCount);
        if (($this->value != 0)) {
            $out->putShort($this->cw->newUTF8("ConstantValue"));
            $out->putInt(2)->putShort($this->value);
        }

        if (((($this->access & Opcodes::ACC_SYNTHETIC)) != 0)) {
            if ((((($this->cw->version & 0xFFFF)) < Opcodes::V1_5)
                || ((($this->access & ClassWriter::$ACC_SYNTHETIC_ATTRIBUTE)) != 0))) {
                $out->putShort($this->cw->newUTF8("Synthetic"))->putInt(0);
            }
        }

        if (((($this->access & Opcodes::ACC_DEPRECATED)) != 0)) {
            $out->putShort($this->cw->newUTF8("Deprecated"))->putInt(0);
        }

        if ((ClassReader::SIGNATURES && ($this->signature != 0))) {
            $out->putShort($this->cw->newUTF8("Signature"));
            $out->putInt(2)->putShort($this->signature);
        }

        if ((ClassReader::ANNOTATIONS && ($this->anns != null))) {
            $out->putShort($this->cw->newUTF8("RuntimeVisibleAnnotations"));
            $this->anns->put($out);
        }

        if ((ClassReader::ANNOTATIONS && ($this->ianns != null))) {
            $out->putShort($this->cw->newUTF8("RuntimeInvisibleAnnotations"));
            $this->ianns->put($out);
        }

        if ((ClassReader::ANNOTATIONS && ($this->tanns != null))) {
            $out->putShort($this->cw->newUTF8("RuntimeVisibleTypeAnnotations"));
            $this->tanns->put($out);
        }

        if ((ClassReader::ANNOTATIONS && ($this->itanns != null))) {
            $out->putShort($this->cw->newUTF8("RuntimeInvisibleTypeAnnotations"));
            $this->itanns->put($out);
        }

        if (($this->attrs != null)) {
            $this->attrs->put($this->cw, null, 0, -1, -1, $out);
        }
    }
}
