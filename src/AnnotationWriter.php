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

use Kambo\Karsk\Type;

/**
 * An {@link AnnotationVisitor} that generates annotations in bytecode form.
 *
 * @author Eric Bruneton
 * @author Eugene Kuleshov
 * @author Bohuslav Simek <bohuslav@simek.si>
 */
final class AnnotationWriter extends AnnotationVisitor
{
    public $cw;    // ClassWriter
    public $size;    // int
    public $named;    // boolean
    public $bv;    // ByteVector
    public $parent;    // ByteVector
    public $offset;    // int
    public $next;    // AnnotationWriter
    public $prev;    // AnnotationWriter

    /**
     * Constructs a new {@link AnnotationWriter}.
     *
     * @param ClassWriter $cw the class writer to which this annotation must be added.
     * @param bool        $named  <tt>true<tt> if values are named, <tt>false</tt> otherwise.
     * @param ByteVector  $bv     where the annotation values must be stored.
     * @param ByteVector  $parent where the number of annotation values must be stored.
     * @param int         $offset where in <tt>parent</tt> the number of annotation values must be stored.
     */
    public function __construct(
        ClassWriter $cw,
        bool $named,
        ByteVector $bv,
        ByteVector $parent,
        int $offset
    ) {
        parent::__construct(Opcodes::ASM5);
        $this->cw     = $cw;
        $this->named  = $named;
        $this->bv     = $bv;
        $this->parent = $parent;
        $this->offset = $offset;
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
        $this->size++;
        if ($this->named) {
            $this->bv->putShort($this->cw->newUTF8($name));
        }

        if (is_array($value)) {
            $this->visitArrayValue($value);
        } else {
            switch (true) {
                case $value instanceof Type\String_:
                    $this->bv->put12('s', $this->cw->newUTF8($value->getValue()));
                    break;
                case $value instanceof Type\Byte:
                    $this->bv->put12('B', $this->cw->newInteger($value->getValue()->byteValue())->index);
                    break;
                case $value instanceof Type\Boolean:
                    $v = ( (($value->getValue())->booleanValue()) ? 1 : 0 );
                    $this->bv->put12('Z', $this->cw->newInteger($v)->index);
                    break;
                case $value instanceof Type\Character:
                    $this->bv->put12('C', $this->cw->newInteger(($value->getValue())->charValue())->index);
                    break;
                case $value instanceof Type\Short:
                    $this->bv->put12('S', $this->cw->newInteger(($value->getValue())->shortValue())->index);
                    break;
                case $value instanceof Type:
                    $this->bv->put12('c', $this->cw->newUTF8(($value->getValue())->getDescriptor()));
                    break;
            }
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
        ++$this->size;
        if ($this->named) {
            $this->bv->putShort($this->cw->newUTF8($name));
        }
        $this->bv->put12('e', $this->cw->newUTF8($desc))->putShort($this->cw->newUTF8($value));
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
        ++$this->size;
        if ($this->named) {
            $this->bv->putShort($this->cw->newUTF8($name));
        }

        $this->bv->put12('@', $this->cw->newUTF8($desc))->putShort(0);

        return new self(
            $this->cw,
            true,
            $this->bv,
            $this->bv,
            count($this->bv) - 2
        );
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
        ++$this->size;
        if ($this->named) {
            $this->bv->putShort($this->cw->newUTF8($name));
        }
        $this->bv->put12('[', 0);
        return new self(
            $this->cw,
            false,
            $this->bv,
            $this->bv,
            count($this->bv)- 2
        );
    }

    /**
     * Visits the end of the annotation.
     *
     * @return void
     */
    public function visitEnd() : void
    {
        if (($this->parent != null)) {
            $data                      = $this->parent->data;
            $data[$this->offset]       = self::uRShift($this->size, 8);
            $data[($this->offset + 1)] = $this->size;
            $this->parent->data        = $data;
        }
    }

    /**
     * Returns the size of this annotation writer list.
     *
     * @return int the size of this annotation writer list.
     */
    protected function getSize() : int
    {
        $size = 0;
        $aw   = $this;
        while ($aw !== null) {
            $size += count($this->bv);
            $aw = $this->next;
        }

        return $size;
    }

    /**
     * Puts the annotations of this annotation writer list into the given byte
     * vector.
     *
     * @param ByteVector $out where the annotations must be put.
     *
     * @return void
     */
    protected function putByteVector(ByteVector $out) : void
    {
        $n = 0;
        $size = 2;
        $aw = $this;
        $last = null;
        while (($aw != null)) {
            ++$n;
            $size += count($aw->bv);
            $aw->visitEnd();
            $aw->prev = $last;
            $last = $aw;
            $aw = $aw->next;
        }

        $out->putInt($size);
        $out->putShort($n);
        $aw = $last;
        while ($aw !== null) {
            $out->putByteArray($aw->bv->data, 0, count($aw->bv));
            $aw = $aw->prev;
        }
    }

    /**
     * Puts the given annotation lists into the given byte vector.
     *
     * @param array      $panns an array of annotation writer lists.
     * @param int        $off   index of the first annotation to be written.
     * @param ByteVector $out   where the annotations must be put.
     *
     * @return void
     */
    public static function put(array $panns, int $off, ByteVector $out) : void
    {
        $size = (1 + (2 * ((count($panns) /*from: panns.length*/ - $off))));
        for ($i = $off; ($i < count($panns) /*from: panns.length*/); ++$i) {
            $size += ( (($panns[$i] == null)) ? 0 : $panns[$i]->getSize() );
        }

        $out->putInt($size)->putByte((count($panns) /*from: panns.length*/ - $off));
        for ($i = $off; ($i < count($panns) /*from: panns.length*/); ++$i) {
            $aw = $panns[$i];
            $last = null;
            $n = 0;
            while (($aw != null)) {
                ++$n;
                $aw->visitEnd();
                $aw->prev = $last;
                $last = $aw;
                $aw = $aw->next;
            }

            $out->putShort($n);
            $aw = $last;
            while (($aw != null)) {
                $out->putByteArray($aw->bv->data, 0, count($aw->bv) /*from: aw.bv.length*/);
                $aw = $aw->prev;
            }
        }
    }

    /**
     * Puts the given type reference and type path into the given bytevector.
     * LOCAL_VARIABLE and RESOURCE_VARIABLE target types are not supported.
     *
     * @param int        $typeRef  a reference to the annotated type. See {@link TypeReference}.
     * @param TypePath   $typePath
     *                             the path to the annotated type argument, wildcard bound, array
     *                             element type, or static inner type within 'typeRef'. May be
     *                             <tt>null</tt> if the annotation targets 'typeRef' as a whole.
     * @param ByteVector $out      where the type reference and type path must be put.
     *
     * @return void
     */
    public static function putTarget(int $typeRef, TypePath $typePath, ByteVector $out) : void
    {
        switch (self::uRShift($typeRef, 24)) {
            case 0x00:
            case 0x01:
            case 0x16:
                $out->putShort(self::uRShift($typeRef, 16));
                break;
            case 0x13:
            case 0x14:
            case 0x15:
                $out->putByte(self::uRShift($typeRef, 24));
                break;
            case 0x47:
            case 0x48:
            case 0x49:
            case 0x4A:
            case 0x4B:
                $out->putInt($typeRef);
                break;
            default:
                $out->put12(self::uRShift($typeRef, 24), ($typeRef & 0xFFFF00) >> 8);
                break;
        }

        if ($typePath === null) {
            $out->putByte(0);
        } else {
            $length = (($typePath->b[$typePath->offset] * 2) + 1);
            $out->putByteArray($typePath->b, $typePath->offset, $length);
        }
    }

    private function visitArrayValue(array $value)
    {
        // This is naive...
        $type = reset($value);

        switch (true) {
            case $type instanceof Type\Byte:
                $v = $value;
                $this->bv->put12('[', count($v));
                for ($i = 0; ($i < count($v)); ++$i) {
                    $this->bv->put12('B', $this->cw->newInteger($v[$i]->getValue())->index);
                }

                break;
            case $type instanceof Type\Boolean:
                $v = $value;
                $this->bv->put12('[', count($v));
                for ($i = 0; ($i < count($v)); ++$i) {
                    $this->bv->put12('Z', $this->cw->newInteger(( ($v[$i]) ? 1 : 0 ))->index);
                }

                break;
            case $type instanceof Type\Short:
                $v = $value;
                $this->bv->put12('[', count($v));
                for ($i = 0; ($i < count($v)); ++$i) {
                    $this->bv->put12('S', $this->cw->newInteger($v[$i])->index);
                }

                break;
            case $type instanceof Type\Character:
                $v = $value;
                $this->bv->put12('[', count($v));
                for ($i = 0; ($i < count($v)); ++$i) {
                    $this->bv->put12('C', $this->cw->newInteger($v[$i])->index);
                }

                break;
            case $type instanceof Type\Integer:
                $v = $value;
                $this->bv->put12('[', count($v));
                for ($i = 0; ($i < count($v)); ++$i) {
                    $this->bv->put12('I', $this->cw->newInteger($v[$i])->index);
                }

                break;
            case $type instanceof Type\Long:
                $v = $value;
                $this->bv->put12('[', count($v));
                for ($i = 0; ($i < count($v)); ++$i) {
                    $this->bv->put12('J', $this->cw->newLong($v[$i])->index);
                }

                break;
            case $type instanceof Type\Float_:
                $v = $value;
                $this->bv->put12('[', count($v));
                for ($i = 0; ($i < count($v)); ++$i) {
                    $this->bv->put12('F', $this->cw->newFloat($v[$i])->index);
                }

                break;
            case $type instanceof Type\Double:
                $v = $value;
                $this->bv->put12('[', count($v));
                for ($i = 0; ($i < count($v)); ++$i) {
                    $this->bv->put12('D', $this->cw->newDouble($v[$i])->index);
                }

                break;
            default:
                $item = $this->cw->newConstItem($value);
                $this->bv->put12($this->charAt(".s.IFJDCS", $item->type), $item->index);

                break;
        }
    }

    private static function uRShift($a, $b)
    {
        return ($a >> $b & 0xFF);
    }

    private function charAt($str, $pos)
    {
        return $str[$pos];
    }
}
