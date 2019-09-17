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
use Kambo\Karsk\Exception\IOException;
use Kambo\Karsk\Utils\InputStream;
use Kambo\Karsk\Utils\FileInputStream;

/**
 * A Java class parser to make a {@link ClassVisitor} visit an existing class.
 * This class parses a byte array conforming to the Java class file format and
 * calls the appropriate visit methods of a given class visitor for each field,
 * method and bytecode instruction encountered.
 *
 * @author Eric Bruneton
 * @author Eugene Kuleshov
 * @author Bohuslav Simek <bohuslav@simek.si>
 */
class ClassReader
{
    /**
     * True to enable signatures support.
     */
    const SIGNATURES = true;

    /**
     * True to enable annotations support.
     */
    const ANNOTATIONS = true;

    /**
     * True to enable stack map frames support.
     */
    const FRAMES = true;

    /**
     * True to enable bytecode writing support.
     */
    const WRITER = true;

    /**
     * True to enable JSR_W and GOTO_W support.
     */
    const RESIZE = true;

    /**
     * Flag to skip method code. If this class is set <code>CODE</code>
     * attribute won't be visited. This can be used, for example, to retrieve
     * annotations for methods and method parameters.
     */
    const SKIP_CODE = 1;

    /**
     * Flag to skip the debug information in the class. If this flag is set the
     * debug information of the class is not visited, i.e. the
     * {@link MethodVisitor#visitLocalVariable visitLocalVariable} and
     * {@link MethodVisitor#visitLineNumber visitLineNumber} methods will not be
     * called.
     */
    const SKIP_DEBUG = 2;

    /**
     * Flag to skip the stack map frames in the class. If this flag is set the
     * stack map frames of the class is not visited, i.e. the
     * {@link MethodVisitor#visitFrame visitFrame} method will not be called.
     * This flag is useful when the {@link ClassWriter#COMPUTE_FRAMES} option is
     * used: it avoids visiting frames that will be ignored and recomputed from
     * scratch in the class writer.
     */
    const SKIP_FRAMES = 4;

    /**
     * Flag to expand the stack map frames. By default stack map frames are
     * visited in their original format (i.e. "expanded" for classes whose
     * version is less than V1_6, and "compressed" for the other classes). If
     * this flag is set, stack map frames are always visited in expanded format
     * (this option adds a decompression/recompression step in ClassReader and
     * ClassWriter which degrades performances quite a lot).
     */
    const EXPAND_FRAMES = 8;

    /**
     * Flag to expand the ASM pseudo instructions into an equivalent sequence of
     * standard bytecode instructions. When resolving a forward jump it may
     * happen that the signed 2 bytes offset reserved for it is not sufficient
     * to store the bytecode offset. In this case the jump instruction is
     * replaced with a temporary ASM pseudo instruction using an unsigned 2
     * bytes offset (see Label#resolve). This internal flag is used to re-read
     * classes containing such instructions, in order to replace them with
     * standard instructions. In addition, when this flag is used, GOTO_W and
     * JSR_W are <i>not</i> converted into GOTO and JSR, to make sure that
     * infinite loops where a GOTO_W is replaced with a GOTO in ClassReader and
     * converted back to a GOTO_W in ClassWriter cannot occur.
     */
    const EXPAND_ASM_INSNS = 256;

    /**
     * The class to be parsed. <i>The content of this array must not be
     * modified. This field is intended for {@link Attribute} sub classes, and
     * is normally not needed by class generators or adapters.</i>
     *
     * @var int[]
     */
    public $b;

    /**
     * The start index of each constant pool item in {@link #b b}, plus one. The
     * one byte offset skips the constant pool item tag that indicates its type.
     *
     * @var int[]
     */
    protected $items;

    /**
     * The String objects corresponding to the CONSTANT_Utf8 items. This cache
     * avoids multiple parsing of a given CONSTANT_Utf8 constant pool item,
     * which GREATLY improves performances (by a factor 2 to 3). This caching
     * strategy could be extended to all constant pool items, but its benefit
     * would not be so great for these items (because they are much less
     * expensive to parse than CONSTANT_Utf8 items).
     *
     * @var string[]
     */
    protected $strings;

    /**
     * Maximum length of the strings contained in the constant pool of the
     * class.
     *
     * @var int
     */
    protected $maxStringLength;

    /**
     * Start index of the class header information (access, name...) in
     * {@link #b b}.
     *
     * @var int
     */
    public $header;

    /**
     * Constructs a new {@link ClassReader} object.
     *
     * @param array $byteCode the bytecode of the class to be read.
     */
    public function __construct(array $byteCode, int $off = 0, int $len = null)
    {
        if ($len === null) {
            $len = count($byteCode);
        }

        $this->b = $byteCode;
        if (($this->readShort(($off + 6)) > Opcodes::V1_8)) {
            throw new IllegalArgumentException();
        }

        $this->items   = [];//$this->readUnsignedShort($off + 8);
        $n             = $this->readUnsignedShort($off + 8);//count($this->items);
        $this->strings = [];
        $max = 0;
        $index = ($off + 10);
        for ($i = 1; ($i < $n); ++$i) {
            $this->items[$i] = ($index + 1);
            $size = null;
            switch ($byteCode[$index]) {
                case ClassWriter::$FIELD:
                case ClassWriter::$METH:
                case ClassWriter::$IMETH:
                case ClassWriter::$INT:
                case ClassWriter::$FLOAT:
                case ClassWriter::$NAME_TYPE:
                case ClassWriter::$INDY:
                    $size = 5;
                    break;
                case ClassWriter::$LONG:
                case ClassWriter::$DOUBLE:
                    $size = 9;
                    ++$i;
                    break;
                case ClassWriter::$UTF8:
                    // TODO [SIMEK, i] why "3" ??
                    $size = (3 + $this->readUnsignedShort(($index + 1)));
                    if (($size > $max)) {
                        $max = $size;
                    }
                    break;
                case ClassWriter::$HANDLE:
                    $size = 4;
                    break;
                default:
                    $size = 3;
                    break;
            }
            $index += $size;
        }

        $this->maxStringLength = $max;
        $this->header          = $index;
    }

    /**
     * Returns the class's access flags (see {@link Opcodes}). This value may
     * not reflect Deprecated and Synthetic flags when bytecode is before 1.5
     * and those flags are represented by attributes.
     *
     * @return int the class access flags
     *
     * @see ClassVisitor#visit(int, int, String, String, String, String[])
     */
    public function getAccess() : int
    {
        return $this->readUnsignedShort($this->header);
    }

    /**
     * Returns the internal name of the class (see
     * {@link Type#getInternalName() getInternalName}).
     *
     * @return string the internal class name
     *
     * @see ClassVisitor#visit(int, int, String, String, String, String[])
     */
    public function getClassName() : string
    {
        return $this->readClass($this->header + 2, []);
    }

    /**
     * Returns the internal of name of the super class (see
     * {@link Type#getInternalName() getInternalName}). For interfaces, the
     * super class is {@link Object}.
     *
     * @return string the internal name of super class, or <tt>null</tt> for
     *         {@link Object} class.
     *
     * @see ClassVisitor#visit(int, int, String, String, String, String[])
     */
    public function getSuperName() : string
    {
        return $this->readClass($this->header + 4, []);
    }

    /**
     * Returns the internal names of the class's interfaces (see
     * {@link Type#getInternalName() getInternalName}).
     *
     * @return string[] the array of internal names for all implemented interfaces or
     *         <tt>null</tt>.
     *
     * @see ClassVisitor#visit(int, int, String, String, String, String[])
     */
    public function getInterfaces() : array
    {
        $index      = ($this->header + 6);
        $n          = $this->readUnsignedShort($index);
        $interfaces = [];
        if ($n > 0) {
            $buf = [];
            for ($i = 0; ($i < $n); ++$i) {
                $index += 2;
                $interfaces[$i] = $this->readClass($index, $buf);
            }
        }

        return $interfaces;
    }

    /**
     * Copies the constant pool data into the given {@link ClassWriter}. Should
     * be called before the {@link #accept(ClassVisitor,int)} method.
     *
     * @param classWriter $classWriter the {@link ClassWriter} to copy constant pool into.
     */
    public function copyPool(ClassWriter $classWriter)
    {
        $buf          = [];
        $ll           = count($this->items);
        $items2       = [];
        $items2Length = $ll;

        for ($i = 1; ($i < $ll); ++$i) {
            $index    = $this->items[$i];
            $tag      = $this->b[$index - 1];
            $item     = new Item($i);
            $nameType = null;

            switch ($tag) {
                case ClassWriter::$FIELD:
                case ClassWriter::$METH:
                case ClassWriter::$IMETH:
                    $nameType = $this->items[$this->readUnsignedShort(($index + 2))];
                    $item->setComplex(
                        $tag,
                        $this->readClass($index, $buf),
                        $this->readUTF8($nameType, $buf),
                        $this->readUTF8(($nameType + 2), $buf)
                    );
                    break;
                case ClassWriter::$INT:
                    $item->setInteger($this->readInt($index));
                    break;
                case ClassWriter::$FLOAT:
                    $item->setFloat($this->intBitsToFloat($this->readInt($index)));
                    break;
                case ClassWriter::$NAME_TYPE:
                    $item->setComplex($tag, $this->readUTF8($index, $buf), $this->readUTF8(($index + 2), $buf), null);
                    break;
                case ClassWriter::$LONG:
                    $item->setLong($this->readLong($index));
                    ++$i;
                    break;
                case ClassWriter::$DOUBLE:
                    $item->setDouble($this->longBitsToDouble($this->readLong($index)));
                    ++$i;
                    break;
                case ClassWriter::$UTF8:
                    if (!array_key_exists($i, $this->strings)) {
                        $index = $this->items[$i];
                        $s = $this->strings[$i] = $this->readUTF(
                            ($index + 1),
                            $this->readUnsignedShort($index),
                            $buf
                        );
                    } else {
                        $s = $this->strings[$i];
                    }

                    $item->setComplex($tag, $s, null, null);
                    break;
                case ClassWriter::$HANDLE:
                    $fieldOrMethodRef = $this->items[$this->readUnsignedShort(($index + 1))];
                    $nameType = $this->items[$this->readUnsignedShort(($fieldOrMethodRef + 2))];
                    $item->setComplex(
                        (ClassWriter::$HANDLE_BASE + $this->readByte($index)),
                        $this->readClass($fieldOrMethodRef, $buf),
                        $this->readUTF8($nameType, $buf),
                        $this->readUTF8(($nameType + 2), $buf)
                    );
                    break;
                case ClassWriter::$INDY:
                    if (($classWriter->bootstrapMethods == null)) {
                        $this->copyBootstrapMethods($classWriter, $items2, $buf);
                    }
                    $nameType = $this->items[$this->readUnsignedShort(($index + 2))];
                    $item->setInvokeDynamic(
                        $this->readUTF8($nameType, $buf),
                        $this->readUTF8(($nameType + 2), $buf),
                        $this->readUnsignedShort($index)
                    );
                    break;
                default:
                    $item->setComplex($tag, $this->readUTF8($index, $buf), null, null);
                    break;
            }

            $index2 = ($item->hashCode % $items2Length);
            // In the case of collision store previous item into next item property
            if (array_key_exists($index2, $items2)) {
                $item->next = $items2[$index2];
            }

            $items2[$index2] = $item;
        }

        $off = ($this->items[1] - 1);
        $classWriter->pool->putByteArray($this->b, $off, ($this->header - $off));
        $classWriter->items = $items2;
        $classWriter->threshold = ((doubleval(0.75) * $ll));
        $classWriter->index = $ll;
    }

    /**
     * Copies the bootstrap method data into the given {@link ClassWriter}.
     * Should be called before the {@link #accept(ClassVisitor,int)} method.
     *
     * @param classWriter $classWriter the {@link ClassWriter} to copy bootstrap methods into.
     * @param Item[]      $items
     * @param string[]    $c
     *
     * @return void
     */
    protected function copyBootstrapMethods(ClassWriter $classWriter, array $items, array $c)
    {
        $u = $this->getAttributes();
        $found =  false ;
        for ($i = $this->readUnsignedShort($u); ($i > 0); --$i) {
            $attrName = $this->readUTF8(($u + 2), $c);
            if ('BootstrapMethods' === $attrName) {
                $found = true;
                break;
            }

            $u += (6 + $this->readInt(($u + 4)));
        }

        if (!$found) {
            return ;
        }

        // copies the bootstrap methods in the class writer
        $boostrapMethodCount = $this->readUnsignedShort(($u + 8));
        for ($j = 0, $v = ($u + 10); ($j < $boostrapMethodCount); ++$j) {
            $position = (($v - $u) - 10);
            $hashCode = $this->readConst($this->readUnsignedShort($v), $c)->hashCode();
            for ($k = $this->readUnsignedShort(($v + 2)); ($k > 0); --$k) {
                $hashCode ^= $this->readConst($this->readUnsignedShort(($v + 4)), $c)->hashCode();
                $v += 2;
            }

            $v += 4;
            $item = new Item($j);
            $item->set($position, ($hashCode & 0x7FFFFFFF));
            $index = ($item->hashCode % count($items) /*from: items.length*/);
            $item->next = $items[$index];
            $items[$index] = $item;
        }

        $attrSize = $this->readInt(($u + 4));
        $bootstrapMethods = new ByteVector();
        $bootstrapMethods->putByteArray($this->b, ($u + 10), ($attrSize - 2));
        $classWriter->bootstrapMethodsCount = $boostrapMethodCount;
        $classWriter->bootstrapMethods = $bootstrapMethods;
    }

    /**
     * Constructs a new {@link ClassReader} object.
     *
     * @param InputStream $is an input stream from which to read the class.
     *
     * @return self Class reader created from input stream
     */
    public static function createFromInputStream(InputStream $is) : self
    {
        return new self(self::readClassFromStream($is, false));
    }

    /**
     * Constructs a new {@link ClassReader} object.
     *
     * @param string $name the binary qualified name of the class to be read.
     *
     * @return self Class reader created from file path
     */
    public static function createFromPath(string $name) : self
    {
        return new self(self::readClassFromStream(new FileInputStream($name), true));
    }

    /**
     * Reads the bytecode of a class.
     *
     * @param InputStream $is    an input stream from which to read the class.
     * @param bool        $close true to close the input stream after reading.
     *
     * @return int[] the bytecode read from the given input stream.
     */
    protected static function readClassFromStream(?InputStream $is, bool $close) : array
    {
        if ($is === null) {
            throw new IOException('Class not found');
        }

        try {
            $b   = [];
            $len = 0;
            while (true) {
                $n = $is->read($b, $len, ($is->available() /*from: b.length*/ - $len));

                if (($n == -1)) {
                    if (($len < count($b) /*from: b.length*/)) {
                        $c = [];
                        foreach (range(0, ($len + 0)) as $_upto) {
                            $c[$_upto] = $b[$_upto - (0) + 0];
                        } /* from: System.arraycopy(b, 0, c, 0, len) */;
                        $b = $c;
                    }

                    return $b;
                }


                $len += $n;
                if (($len == count($b))) {
                    $last = $is->read();
                    if (($last < 0)) {
                        return $b;
                    }

                    $c = [];
                    foreach (range(0, ($len - 1)) as $_upto) {
                        $c[$_upto] = $b[$_upto];
                    }

                    $c[++$len] = $last;
                    $b = $c;
                }
            }
        } finally {
            {
            if ($close) {
                $is->close();
            }
            }
        }
    }

    // ------------------------------------------------------------------------
    // Public methods
    // ------------------------------------------------------------------------

    /**
     * Makes the given visitor visit the Java class of this {@link ClassReader}.
     * This class is the one specified in the constructor (see
     * {@link #ClassReader(byte[]) ClassReader}).
     *
     * @param ClassVisitor $classVisitor
     *                     the visitor that must visit this class.
     * @param Attribute[]  $attrs
     *                    prototypes of the attributes that must be parsed during the
     *                    visit of the class. Any attribute whose type is not equal to
     *                    the type of one the prototypes will not be parsed: its byte
     *                    array value will be passed unchanged to the ClassWriter.
     *                    <i>This may corrupt it if this value contains references to
     *                    the constant pool, or has syntactic or semantic links with a
     *                    class element that has been transformed by a class adapter
     *                    between the reader and the writer</i>.
     * @param int          $flags
     *                    option flags that can be used to modify the default behavior
     *                    of this class. See {@link #SKIP_DEBUG}, {@link #EXPAND_FRAMES}
     *                    , {@link #SKIP_FRAMES}, {@link #SKIP_CODE}.
     */
    public function accept(
        ClassVisitor $classVisitor,
        array $attrs = [],
        int $flags = null
    ) : void {
        $u = $this->header;
        $c = [];

        $context = new Context();
        $context->attrs = $attrs;
        $context->flags = $flags;
        $context->buffer = $c;

        $access     = $this->readUnsignedShort($u);
        $name       = $this->readClass(($u + 2), $c);
        $superClass = $this->readClass(($u + 4), $c);

        $countOfInterfaces = $this->readUnsignedShort($u + 6);
        $interfaces        = [];

        $u += 8;
        for ($i = 0; $i < $countOfInterfaces; ++$i) {
            $interfaces[$i] = $this->readClass($u, $c);
            $u += 2;
        }

        $signature = null;
        $sourceFile = null;
        $sourceDebug = null;
        $enclosingOwner = null;
        $enclosingName = null;
        $enclosingDesc = null;
        $anns = 0;
        $ianns = 0;
        $tanns = 0;
        $itanns = 0;
        $innerClasses = 0;
        $attributes = null;

        $u = $this->getAttributes();
        for ($i = $this->readUnsignedShort($u); ($i > 0); --$i) {
            $attrName = $this->readUTF8(($u + 2), $c);
            if ($attrName === 'SourceFile') {
                $sourceFile = $this->readUTF8(($u + 8), $c);
            } elseif ($attrName === 'InnerClasses') {
                $innerClasses = ($u + 8);
            } elseif ($attrName ===  'EnclosingMethod') {
                $enclosingOwner = $this->readClass(($u + 8), $c);
                $item = $this->readUnsignedShort(($u + 10));
                if (($item != 0)) {
                    $enclosingName = $this->readUTF8($this->items[$item], $c);
                    $enclosingDesc = $this->readUTF8(($this->items[$item] + 2), $c);
                }
            } elseif ((self::SIGNATURES && $attrName === 'Signature')) {
                $signature = $this->readUTF8(($u + 8), $c);
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeVisibleAnnotations')) {
                $anns = ($u + 8);
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeVisibleTypeAnnotations')) {
                $tanns = ($u + 8);
            } elseif ($attrName === 'Deprecated') {
                $access |= Opcodes::ACC_DEPRECATED;
            } elseif ($attrName === 'Synthetic') {
                $access |= (Opcodes::ACC_SYNTHETIC | ClassWriter::$ACC_SYNTHETIC_ATTRIBUTE);
            } elseif ($attrName === 'SourceDebugExtension') {
                $len = $this->readInt(($u + 4));
                $sourceDebug = $this->readUTF(($u + 8), $len, []);
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeInvisibleAnnotations')) {
                $ianns = ($u + 8);
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeInvisibleTypeAnnotations')) {
                $itanns = ($u + 8);
            } elseif ($attrName ===  'BootstrapMethods') {
                $bootstrapMethods = [];
                for ($j = 0, $v = ($u + 10); ($j < count($bootstrapMethods)); ++$j) {
                    $bootstrapMethods[$j] = $v;
                    $v += ((2 + $this->readUnsignedShort(($v + 2))) << 1);
                }
                $context->bootstrapMethods = $bootstrapMethods;
            } else {
                $attr = $this->readAttribute($attrs, $attrName, ($u + 8), $this->readInt(($u + 4)), $c, -1, null);
                if (($attr != null)) {
                    $attr->next = $attributes;
                    $attributes = $attr;
                }
            }

            $u += (6 + $this->readInt(($u + 4)));
        }

        $classVisitor->visit(
            $this->readInt($this->items[1] - 7),
            $access,
            $name,
            $signature,
            $superClass,
            $interfaces
        );
        if ((((($flags & self::SKIP_DEBUG)) == 0) && ((($sourceFile != null) || ($sourceDebug != null))))) {
            $classVisitor->visitSource($sourceFile, $sourceDebug);
        }

        if (($enclosingOwner != null)) {
            $classVisitor->visitOuterClass($enclosingOwner, $enclosingName, $enclosingDesc);
        }

        if ((self::ANNOTATIONS && ($anns != 0))) {
            for ($i = $this->readUnsignedShort($anns), $v = ($anns + 2); ($i > 0); --$i) {
                $v = $this->readAnnotationValues(
                    ($v + 2),
                    $c,
                    true,
                    $classVisitor->visitAnnotation($this->readUTF8($v, $c), true)
                );
            }
        }

        if ((self::ANNOTATIONS && ($ianns != 0))) {
            for ($i = $this->readUnsignedShort($ianns), $v = ($ianns + 2); ($i > 0); --$i) {
                $v = $this->readAnnotationValues(
                    ($v + 2),
                    $c,
                    true,
                    $classVisitor->visitAnnotation($this->readUTF8($v, $c), false)
                );
            }
        }

        if ((self::ANNOTATIONS && ($tanns != 0))) {
            for ($i = $this->readUnsignedShort($tanns), $v = ($tanns + 2); ($i > 0); --$i) {
                $v = $this->readAnnotationTarget($context, $v);
                $v = $this->readAnnotationValues(
                    ($v + 2),
                    $c,
                    true,
                    $classVisitor->visitTypeAnnotation(
                        $context->typeRef,
                        $context->typePath,
                        $this->readUTF8($v, $c),
                        true
                    )
                );
            }
        }

        if ((self::ANNOTATIONS && ($itanns != 0))) {
            for ($i = $this->readUnsignedShort($itanns), $v = ($itanns + 2); ($i > 0); --$i) {
                $v = $this->readAnnotationTarget($context, $v);
                $v = $this->readAnnotationValues(
                    ($v + 2),
                    $c,
                    true,
                    $classVisitor->visitTypeAnnotation(
                        $context->typeRef,
                        $context->typePath,
                        $this->readUTF8($v, $c),
                        false
                    )
                );
            }
        }

        while (($attributes != null)) {
            $attr = $attributes->next;
            $attributes->next = null;
            $classVisitor->visitAttribute($attributes);
            $attributes = $attr;
        }

        if (($innerClasses != 0)) {
            $v = ($innerClasses + 2);
            for ($i = $this->readUnsignedShort($innerClasses); ($i > 0); --$i) {
                $classVisitor->visitInnerClass(
                    $this->readClass($v, $c),
                    $this->readClass(($v + 2), $c),
                    $this->readUTF8(($v + 4), $c),
                    $this->readUnsignedShort(($v + 6))
                );
                $v += 8;
            }
        }

        $u = (($this->header + 10) + (2 * count($interfaces) /*from: interfaces.length*/));
        for ($i = $this->readUnsignedShort(($u - 2)); ($i > 0); --$i) {
            $u = $this->readField($classVisitor, $context, $u);
        }

        $u += 2;
        for ($i = $this->readUnsignedShort(($u - 2)); ($i > 0); --$i) {
            $u = $this->readMethod($classVisitor, $context, $u);
        }

        $classVisitor->visitEnd();
    }

    /**
     * Reads a field and makes the given visitor visit it.
     *
     * @param classVisitor $classVisitor the visitor that must visit the field.
     * @param context      $context      information about the class being parsed.
     * @param int          $u            the start offset of the field in the class file.
     *
     * @return int the offset of the first byte following the field in the class.
     */
    protected function readField(ClassVisitor $classVisitor, Context $context, int $u) : int
    {
        // reads the field declaration
        $c = $context->buffer;
        $access = $this->readUnsignedShort($u);
        $name = $this->readUTF8(($u + 2), $c);
        $desc = $this->readUTF8(($u + 4), $c);
        $u += 6;

        // reads the field attributes
        $signature = null;
        $anns = 0;
        $ianns = 0;
        $tanns = 0;
        $itanns = 0;
        $value = null;
        $attributes = null;

        for ($i = $this->readUnsignedShort($u); ($i > 0); --$i) {
            $attrName = $this->readUTF8(($u + 2), $c);
            if ($attrName === 'ConstantValue') {
                $item = $this->readUnsignedShort(($u + 8));
                $value = ( (($item == 0)) ? null : $this->readConst($item, $c) );
            } elseif ((self::SIGNATURES && $attrName === 'Signature')) {
                $signature = $this->readUTF8(($u + 8), $c);
            } elseif ($attrName === 'Deprecated') {
                $access |= Opcodes::ACC_DEPRECATED;
            } elseif ($attrName === 'Synthetic') {
                $access |= (Opcodes::ACC_SYNTHETIC | ClassWriter::$ACC_SYNTHETIC_ATTRIBUTE);
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeVisibleAnnotations' )) {
                $anns = ($u + 8);
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeVisibleTypeAnnotations')) {
                $tanns = ($u + 8);
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeInvisibleAnnotations')) {
                $ianns = ($u + 8);
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeInvisibleTypeAnnotations')) {
                $itanns = ($u + 8);
            } else {
                $attr = $this->readAttribute(
                    $context->attrs,
                    $attrName,
                    ($u + 8),
                    $this->readInt(($u + 4)),
                    $c,
                    -1,
                    null
                );
                if (($attr != null)) {
                    $attr->next = $attributes;
                    $attributes = $attr;
                }
            }
            $u += (6 + $this->readInt(($u + 4)));
        }
        $u += 2;
        $fv = $classVisitor->visitField($access, $name, $desc, $signature, $value);
        if (($fv == null)) {
            return $u;
        }
        if ((self::ANNOTATIONS && ($anns != 0))) {
            for ($i = $this->readUnsignedShort($anns), $v = ($anns + 2); ($i > 0); --$i) {
                $v = $this->readAnnotationValues(
                    ($v + 2),
                    $c,
                    true,
                    $fv->visitAnnotation($this->readUTF8($v, $c), true)
                );
            }
        }
        if ((self::ANNOTATIONS && ($ianns != 0))) {
            for ($i = $this->readUnsignedShort($ianns), $v = ($ianns + 2); ($i > 0); --$i) {
                $v = $this->readAnnotationValues(
                    ($v + 2),
                    $c,
                    true,
                    $fv->visitAnnotation($this->readUTF8($v, $c), false)
                );
            }
        }
        if ((self::ANNOTATIONS && ($tanns != 0))) {
            for ($i = $this->readUnsignedShort($tanns), $v = ($tanns + 2); ($i > 0); --$i) {
                $v = $this->readAnnotationTarget($context, $v);
                $v = $this->readAnnotationValues(
                    ($v + 2),
                    $c,
                    true,
                    $fv->visitTypeAnnotation(
                        $context->typeRef,
                        $context->typePath,
                        $this->readUTF8($v, $c),
                        true
                    )
                );
            }
        }
        if ((self::ANNOTATIONS && ($itanns != 0))) {
            for ($i = $this->readUnsignedShort($itanns), $v = ($itanns + 2); ($i > 0); --$i) {
                $v = $this->readAnnotationTarget($context, $v);
                $v = $this->readAnnotationValues(
                    ($v + 2),
                    $c,
                    true,
                    $fv->visitTypeAnnotation(
                        $context->typeRef,
                        $context->typePath,
                        $this->readUTF8($v, $c),
                        false
                    )
                );
            }
        }
        while (($attributes != null)) {
            $attr = $attributes->next;
            $attributes->next = null;
            $fv->visitAttribute($attributes);
            $attributes = $attr;
        }
        $fv->visitEnd();
        return $u;
    }

    /**
     * Reads a method and makes the given visitor visit it.
     *
     * @param ClassVisitor $classVisitor the visitor that must visit the method.
     * @param Context      $context      information about the class being parsed.
     * @param int          $u            the start offset of the method in the class file.
     *
     * @return int the offset of the first byte following the method in the class.
     */
    protected function readMethod(ClassVisitor $classVisitor, Context $context, int $u) : int
    {
        $c = $context->buffer;
        $context->access = $this->readUnsignedShort($u);
        $context->name = $this->readUTF8(($u + 2), $c);
        $context->desc = $this->readUTF8(($u + 4), $c);
        $u += 6;
        $code = 0;
        $exception = 0;
        $exceptions = null;
        $signature = null;
        $methodParameters = 0;
        $anns = 0;
        $ianns = 0;
        $tanns = 0;
        $itanns = 0;
        $dann = 0;
        $mpanns = 0;
        $impanns = 0;
        $firstAttribute = $u;
        $attributes = null;

        for ($i = $this->readUnsignedShort($u); ($i > 0); --$i) {
            $attrName = $this->readUTF8(($u + 2), $c);

            // tests are sorted in decreasing frequency order
            // (based on frequencies observed on typical classes)
            if ($attrName === 'Code') {
                if (((($context->flags & self::SKIP_CODE)) == 0)) {
                    $code = ($u + 8);
                }
            } elseif ($attrName === 'Exceptions') {
                $exceptions = [];
                $exception = ($u + 10);
                for ($j = 0; ($j < count($exceptions) /*from: exceptions.length*/); ++$j) {
                    $exceptions[$j] = $this->readClass($exception, $c);
                    $exception += 2;
                }
            } elseif ((self::SIGNATURES && $attrName ===  'Signature')) {
                $signature = $this->readUTF8(($u + 8), $c);
            } elseif ($attrName === 'Deprecated') {
                $context->access |= Opcodes::ACC_DEPRECATED;
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeVisibleAnnotations')) {
                $anns = ($u + 8);
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeVisibleTypeAnnotations')) {
                $tanns = ($u + 8);
            } elseif ((self::ANNOTATIONS && $attrName === 'AnnotationDefault')) {
                $dann = ($u + 8);
            } elseif ($attrName === 'Synthetic') {
                $context->access |= (Opcodes::ACC_SYNTHETIC | ClassWriter::$ACC_SYNTHETIC_ATTRIBUTE);
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeInvisibleAnnotations')) {
                $ianns = ($u + 8);
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeInvisibleTypeAnnotations')) {
                $itanns = ($u + 8);
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeVisibleParameterAnnotations')) {
                $mpanns = ($u + 8);
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeInvisibleParameterAnnotations')) {
                $impanns = ($u + 8);
            } elseif ($attrName === 'MethodParameters') {
                $methodParameters = ($u + 8);
            } else {
                $attr = $this->readAttribute(
                    $context->attrs,
                    $attrName,
                    ($u + 8),
                    $this->readInt(($u + 4)),
                    $c,
                    -1,
                    null
                );
                if (($attr != null)) {
                    $attr->next = $attributes;
                    $attributes = $attr;
                }
            }
            $u += (6 + $this->readInt(($u + 4)));
        }

        $u += 2;
        $mv = $classVisitor->visitMethod($context->access, $context->name, $context->desc, $signature, $exceptions);

        if (($mv == null)) {
            return $u;
        }

        /*
         * if the returned MethodVisitor is in fact a MethodWriter, it means
         * there is no method adapter between the reader and the writer. If, in
         * addition, the writer's constant pool was copied from this reader
         * (mw.cw.cr == this), and the signature and exceptions of the method
         * have not been changed, then it is possible to skip all visit events
         * and just copy the original code of the method to the writer (the
         * access, name and descriptor can have been changed, this is not
         * important since they are not copied as is from the reader).
         */
        if ((self::WRITER && $mv instanceof MethodWriter)) {
            $mw = $mv;
            if ((($mw->cw->cr == $this) && ($signature == $mw->signature))) {
                $sameExceptions =  false ;
                if (($exceptions == null)) {
                    $sameExceptions = ($mw->exceptionCount == 0);
                } elseif ((count($exceptions) /*from: exceptions.length*/ == $mw->exceptionCount)) {
                    $sameExceptions =  true ;
                    for ($j = (count($exceptions) /*from: exceptions.length*/ - 1); ($j >= 0); --$j) {
                        $exception -= 2;
                        if (($mw->exceptions[$j] != $this->readUnsignedShort($exception))) {
                            $sameExceptions =  false ;
                            break;
                        }
                    }
                }
                if ($sameExceptions) {
                    $mw->classReaderOffset = $firstAttribute;
                    $mw->classReaderLength = ($u - $firstAttribute);
                    return $u;
                }
            }
        }

        if (($methodParameters != 0)) {
            for ($i = ($this->b[$methodParameters] & 0xFF), $v = ($methodParameters + 1); ($i > 0); --$i, $v = ($v + 4)
            ) {
                $mv->visitParameter($this->readUTF8($v, $c), $this->readUnsignedShort(($v + 2)));
            }
        }

        if ((self::ANNOTATIONS && ($dann != 0))) {
            $dv = $mv->visitAnnotationDefault();
            $this->readAnnotationValue($dann, $c, null, $dv);
            if (($dv != null)) {
                $dv->visitEnd();
            }
        }

        if ((self::ANNOTATIONS && ($anns != 0))) {
            for ($i = $this->readUnsignedShort($anns), $v = ($anns + 2); ($i > 0); --$i) {
                $v = $this->readAnnotationValues(
                    ($v + 2),
                    $c,
                    true,
                    $mv->visitAnnotation($this->readUTF8($v, $c), true)
                );
            }
        }

        if ((self::ANNOTATIONS && ($ianns != 0))) {
            for ($i = $this->readUnsignedShort($ianns), $v = ($ianns + 2); ($i > 0); --$i) {
                $v = $this->readAnnotationValues(
                    ($v + 2),
                    $c,
                    true,
                    $mv->visitAnnotation($this->readUTF8($v, $c), false)
                );
            }
        }

        if ((self::ANNOTATIONS && ($tanns != 0))) {
            for ($i = $this->readUnsignedShort($tanns), $v = ($tanns + 2); ($i > 0); --$i) {
                $v = $this->readAnnotationTarget($context, $v);
                $v = $this->readAnnotationValues(
                    ($v + 2),
                    $c,
                    true,
                    $mv->visitTypeAnnotation(
                        $context->typeRef,
                        $context->typePath,
                        $this->readUTF8($v, $c),
                        true
                    )
                );
            }
        }

        if ((self::ANNOTATIONS && ($itanns != 0))) {
            for ($i = $this->readUnsignedShort($itanns), $v = ($itanns + 2); ($i > 0); --$i) {
                $v = $this->readAnnotationTarget($context, $v);
                $v = $this->readAnnotationValues(
                    ($v + 2),
                    $c,
                    true,
                    $mv->visitTypeAnnotation(
                        $context->typeRef,
                        $context->typePath,
                        $this->readUTF8($v, $c),
                        false
                    )
                );
            }
        }

        if ((self::ANNOTATIONS && ($mpanns != 0))) {
            $this->readParameterAnnotations($mv, $context, $mpanns, true);
        }

        if ((self::ANNOTATIONS && ($impanns != 0))) {
            $this->readParameterAnnotations($mv, $context, $impanns, false);
        }

        while (($attributes != null)) {
            $attr = $attributes->next;
            $attributes->next = null;
            $mv->visitAttribute($attributes);
            $attributes = $attr;
        }

        if (($code != 0)) {
            $mv->visitCode();
            $this->readCode($mv, $context, $code);
        }
        $mv->visitEnd();
        return $u;
    }

    /**
     * Reads the bytecode of a method and makes the given visitor visit it.
     *
     * @param MethodVisitor $mv      the visitor that must visit the method's code.
     * @param Context       $context information about the class being parsed.
     * @param int           $u       the start offset of the code attribute in the class file.
     *
     * @return void
     */
    protected function readCode(MethodVisitor $mv, Context $context, int $u) : void
    {
        $b = $this->b;
        $c = $context->buffer;
        $maxStack = $this->readUnsignedShort($u);
        $maxLocals = $this->readUnsignedShort(($u + 2));
        $codeLength = $this->readInt(($u + 4));
        $u += 8;
        $codeStart = $u;
        $codeEnd = ($u + $codeLength);
        $labels = $context->labels = [];
        $this->readLabel(($codeLength + 1), $labels);
        while (($u < $codeEnd)) {
            $offset = ($u - $codeStart);
            $opcode = ($b[$u] & 0xFF);
            switch (ClassWriter::$TYPE[$opcode]) {
                case ClassWriter::$NOARG_INSN:
                case ClassWriter::$IMPLVAR_INSN:
                    $u += 1;
                    break;
                case ClassWriter::$LABEL_INSN:
                    $this->readLabel(($offset + $this->readShort(($u + 1))), $labels);
                    $u += 3;
                    break;
                case ClassWriter::$ASM_LABEL_INSN:
                    $this->readLabel(($offset + $this->readUnsignedShort(($u + 1))), $labels);
                    $u += 3;
                    break;
                case ClassWriter::$LABELW_INSN:
                    $this->readLabel(($offset + $this->readInt(($u + 1))), $labels);
                    $u += 5;
                    break;
                case ClassWriter::$WIDE_INSN:
                    $opcode = ($b[($u + 1)] & 0xFF);
                    if (($opcode == Opcodes::IINC)) {
                        $u += 6;
                    } else {
                        $u += 4;
                    }
                    break;
                case ClassWriter::$TABL_INSN:
                    $u = (($u + 4) - (($offset & 3)));
                    $this->readLabel(($offset + $this->readInt($u)), $labels);
                    for ($i = (($this->readInt(($u + 8)) - $this->readInt(($u + 4))) + 1); ($i > 0); --$i) {
                        $this->readLabel(($offset + $this->readInt(($u + 12))), $labels);
                        $u += 4;
                    }
                    $u += 12;
                    break;
                case ClassWriter::$LOOK_INSN:
                    $u = (($u + 4) - (($offset & 3)));
                    $this->readLabel(($offset + $this->readInt($u)), $labels);
                    for ($i = $this->readInt(($u + 4)); ($i > 0); --$i) {
                        $this->readLabel(($offset + $this->readInt(($u + 12))), $labels);
                        $u += 8;
                    }
                    $u += 8;
                    break;
                case ClassWriter::$VAR_INSN:
                case ClassWriter::$SBYTE_INSN:
                case ClassWriter::$LDC_INSN:
                    $u += 2;
                    break;
                case ClassWriter::$SHORT_INSN:
                case ClassWriter::$LDCW_INSN:
                case ClassWriter::$FIELDORMETH_INSN:
                case ClassWriter::$TYPE_INSN:
                case ClassWriter::$IINC_INSN:
                    $u += 3;
                    break;
                case ClassWriter::$ITFMETH_INSN:
                case ClassWriter::$INDYMETH_INSN:
                    $u += 5;
                    break;
                default:
                    $u += 4;
                    break;
            }
        }
        for ($i = $this->readUnsignedShort($u); ($i > 0); --$i) {
            $start = $this->readLabel($this->readUnsignedShort(($u + 2)), $labels);
            $end = $this->readLabel($this->readUnsignedShort(($u + 4)), $labels);
            $handler = $this->readLabel($this->readUnsignedShort(($u + 6)), $labels);
            $type = $this->readUTF8($this->items[$this->readUnsignedShort(($u + 8))], $c);
            $mv->visitTryCatchBlock($start, $end, $handler, $type);
            $u += 8;
        }
        $u += 2;
        $tanns = null;
        $itanns = null;
        $tann = 0;
        $itann = 0;
        $ntoff = -1;
        $nitoff = -1;
        $varTable = 0;
        $varTypeTable = 0;
        $zip =  true ;
        $unzip = ((($context->flags & self::EXPAND_FRAMES)) != 0);
        $stackMap = 0;
        $stackMapSize = 0;
        $frameCount = 0;
        $frame = null;
        $attributes = null;
        for ($i = $this->readUnsignedShort($u); ($i > 0); --$i) {
            $attrName = $this->readUTF8(($u + 2), $c);
            if ($attrName === 'LocalVariableTable') {
                if (((($context->flags & self::SKIP_DEBUG)) == 0)) {
                    $varTable = ($u + 8);
                    for ($j = $this->readUnsignedShort(($u + 8)), $v = $u; ($j > 0); --$j) {
                        $label = $this->readUnsignedShort(($v + 10));
                        if (!array_key_exists($label, $labels)) {
                            $this->readLabel($label, $labels)->status |= Label::DEBUG;
                        }

                        $label += $this->readUnsignedShort(($v + 12));
                        if (!array_key_exists($label, $labels)) {
                            $this->readLabel($label, $labels)->status |= Label::DEBUG;
                        }

                        $v += 10;
                    }
                }
            } elseif ($attrName === 'LocalVariableTypeTable') {
                $varTypeTable = ($u + 8);
            } elseif ($attrName === 'LineNumberTable') {
                if (((($context->flags & self::SKIP_DEBUG)) == 0)) {
                    for ($j = $this->readUnsignedShort(($u + 8)), $v = $u; ($j > 0); --$j) {
                        $label = $this->readUnsignedShort(($v + 10));
                        if (!array_key_exists($label, $labels)) {
                            $this->readLabel($label, $labels)->status |= Label::DEBUG;
                        }

                        $l = $labels[$label];
                        while (($l->line > 0)) {
                            if (($l->next == null)) {
                                $l->next = new Label();
                            }
                            $l = $l->next;
                        }
                        $l->line = $this->readUnsignedShort(($v + 12));
                        $v += 4;
                    }
                }
            } elseif (self::ANNOTATIONS && $attrName === 'RuntimeVisibleTypeAnnotations') {
                $tanns = $this->readTypeAnnotations($mv, $context, ($u + 8), true);
                $ntoff = (
                    (((count($tanns) /*from: tanns.length*/ == 0)
                        || ($this->readByte($tanns[0]) < 0x43))) ? -1 : $this->readUnsignedShort(($tanns[0] + 1))
                );
            } elseif ((self::ANNOTATIONS && $attrName === 'RuntimeInvisibleTypeAnnotations')) {
                $itanns = $this->readTypeAnnotations($mv, $context, ($u + 8), false);
                $nitoff = ( (((count($itanns) /*from: itanns.length*/ == 0)
                    || ($this->readByte($itanns[0]) < 0x43))) ? -1 : $this->readUnsignedShort(($itanns[0] + 1)) );
            } elseif ((self::FRAMES && $attrName === 'StackMapTable')) {
                if (((($context->flags & self::SKIP_FRAMES)) == 0)) {
                    $stackMap = ($u + 10);
                    $stackMapSize = $this->readInt(($u + 4));
                    $frameCount = $this->readUnsignedShort(($u + 8));
                }
            } elseif ((self::FRAMES && $attrName === 'StackMap')) {
                if (((($context->flags & self::SKIP_FRAMES)) == 0)) {
                    $zip =  false ;
                    $stackMap = ($u + 10);
                    $stackMapSize = $this->readInt(($u + 4));
                    $frameCount = $this->readUnsignedShort(($u + 8));
                }
            } else {
                for ($j = 0; ($j < count($context->attrs) /*from: context.attrs.length*/); ++$j) {
                    if ($context->attrs[$j]->type->equals($attrName)) {
                        $attr = $context->attrs[$j]->read(
                            $this,
                            ($u + 8),
                            $this->readInt(($u + 4)),
                            $c,
                            ($codeStart - 8),
                            $labels
                        );
                        if (($attr != null)) {
                            $attr->next = $attributes;
                            $attributes = $attr;
                        }
                    }
                }
            }
            $u += (6 + $this->readInt(($u + 4)));
        }
        $u += 2;
        if ((self::FRAMES && ($stackMap != 0))) {
            $frame = $context;
            $frame->offset = -1;
            $frame->mode = 0;
            $frame->localCount = 0;
            $frame->localDiff = 0;
            $frame->stackCount = 0;
            $frame->local = [];
            $frame->stack = [];
            if ($unzip) {
                $this->getImplicitFrame($context);
            }
            for ($i = $stackMap; ($i < (($stackMap + $stackMapSize) - 2)); ++$i) {
                if (($b[$i] == 8)) {
                    $v = $this->readUnsignedShort(($i + 1));
                    if ((($v >= 0) && ($v < $codeLength))) {
                        if (((($b[($codeStart + $v)] & 0xFF)) == Opcodes::NEW_)) {
                            $this->readLabel($v, $labels);
                        }
                    }
                }
            }
        }
        if (((($context->flags & self::EXPAND_ASM_INSNS)) != 0)) {
            $mv->visitFrame(Opcodes::F_NEW, $maxLocals, null, 0, null);
        }
        $opcodeDelta = ( (((($context->flags & self::EXPAND_ASM_INSNS)) == 0)) ? -33 : 0 );
        $u = $codeStart;
        while (($u < $codeEnd)) {
            $offset = ($u - $codeStart);
            $l = array_key_exists($offset, $labels) ? $labels[$offset] : null;
            if ($l != null) {
                $next = $l->next;
                $l->next = null;
                $mv->visitLabel($l);
                if ((((($context->flags & self::SKIP_DEBUG)) == 0) && ($l->line > 0))) {
                    $mv->visitLineNumber($l->line, $l);
                    while (($next != null)) {
                        $mv->visitLineNumber($next->line, $l);
                        $next = $next->next;
                    }
                }
            }

            while (((self::FRAMES && ($frame != null)) && ((($frame->offset == $offset) || ($frame->offset == -1))))) {
                if (($frame->offset != -1)) {
                    if ((!$zip || $unzip)) {
                        $mv->visitFrame(
                            Opcodes::F_NEW,
                            $frame->localCount,
                            $frame->local,
                            $frame->stackCount,
                            $frame->stack
                        );
                    } else {
                        $mv->visitFrame(
                            $frame->mode,
                            $frame->localDiff,
                            $frame->local,
                            $frame->stackCount,
                            $frame->stack
                        );
                    }
                }
                if (($frameCount > 0)) {
                    $stackMap = $this->readFrame($stackMap, $zip, $unzip, $frame);
                    --$frameCount;
                } else {
                    $frame = null;
                }
            }
            $opcode = ($b[$u] & 0xFF);
            switch (ClassWriter::$TYPE[$opcode]) {
                case ClassWriter::$NOARG_INSN:
                    $mv->visitInsn($opcode);
                    $u += 1;
                    break;
                case ClassWriter::$IMPLVAR_INSN:
                    if (($opcode > Opcodes::ISTORE)) {
                        $opcode -= 59;
                        $mv->visitVarInsn((Opcodes::ISTORE + (($opcode >> 2))), ($opcode & 0x3));
                    } else {
                        $opcode -= 26;
                        $mv->visitVarInsn((Opcodes::ILOAD + (($opcode >> 2))), ($opcode & 0x3));
                    }
                    $u += 1;
                    break;
                case ClassWriter::$LABEL_INSN:
                    $mv->visitJumpInsn($opcode, $labels[($offset + $this->readShort(($u + 1)))]);
                    $u += 3;
                    break;
                case ClassWriter::$LABELW_INSN:
                    $mv->visitJumpInsn(($opcode + $opcodeDelta), $labels[($offset + $this->readInt(($u + 1)))]);
                    $u += 5;
                    break;
                case ClassWriter::$ASM_LABEL_INSN:
                    $opcode = ( (($opcode < 218)) ? ($opcode - 49) : ($opcode - 20) );
                    $target = $labels[($offset + $this->readUnsignedShort(($u + 1)))];
                    if ((($opcode == Opcodes::GOTO_) || ($opcode == Opcodes::JSR))) {
                        $mv->visitJumpInsn(($opcode + 33), $target);
                    } else {
                        $opcode = ( (($opcode <= 166)) ? ((((($opcode + 1)) ^ 1)) - 1) : ($opcode ^ 1) );
                        $endif = new Label();
                        $mv->visitJumpInsn($opcode, $endif);
                        $mv->visitJumpInsn(200, $target);
                        $mv->visitLabel($endif);
                        if (((self::FRAMES && ($stackMap != 0)) && ((($frame == null)
                        || ($frame->offset != ($offset + 3)))))
                        ) {
                            $mv->visitFrame(ClassWriter::$F_INSERT, 0, null, 0, null);
                        }
                    }
                    $u += 3;
                    break;

                case ClassWriter::$WIDE_INSN:
                    $opcode = ($b[($u + 1)] & 0xFF);
                    if (($opcode == Opcodes::IINC)) {
                        $mv->visitIincInsn($this->readUnsignedShort(($u + 2)), $this->readShort(($u + 4)));
                        $u += 6;
                    } else {
                        $mv->visitVarInsn($opcode, $this->readUnsignedShort(($u + 2)));
                        $u += 4;
                    }

                    break;
                case ClassWriter::$TABL_INSN:
                    $u = (($u + 4) - (($offset & 3)));
                    $label = ($offset + $this->readInt($u));
                    $min = $this->readInt(($u + 4));
                    $max = $this->readInt(($u + 8));
                    $table = [];
                    $u += 12;
                    for ($i = 0; ($i < count($table) /*from: table.length*/); ++$i) {
                        $table[$i] = $labels[($offset + $this->readInt($u))];
                        $u += 4;
                    }

                    $mv->visitTableSwitchInsn($min, $max, $labels[$label], $table);
                    break;
                case ClassWriter::$LOOK_INSN:
                    $u = (($u + 4) - (($offset & 3)));
                    $label = ($offset + $this->readInt($u));
                    $len = $this->readInt(($u + 4));
                    $keys = [];
                    $values = [];
                    $u += 8;
                    for ($i = 0; ($i < $len); ++$i) {
                        $keys[$i] = $this->readInt($u);
                        $values[$i] = $labels[($offset + $this->readInt(($u + 4)))];
                        $u += 8;
                    }

                    $mv->visitLookupSwitchInsn($labels[$label], $keys, $values);
                    break;

                case ClassWriter::$VAR_INSN:
                    $mv->visitVarInsn($opcode, ($b[($u + 1)] & 0xFF));
                    $u += 2;
                    break;
                case ClassWriter::$SBYTE_INSN:
                    $mv->visitIntInsn($opcode, $b[($u + 1)]);
                    $u += 2;
                    break;
                case ClassWriter::$SHORT_INSN:
                    $mv->visitIntInsn($opcode, $this->readShort(($u + 1)));
                    $u += 3;
                    break;
                case ClassWriter::$LDC_INSN:
                    $mv->visitLdcInsn($this->readConst(($b[($u + 1)] & 0xFF), $c));
                    $u += 2;
                    break;
                case ClassWriter::$LDCW_INSN:
                    $mv->visitLdcInsn($this->readConst($this->readUnsignedShort(($u + 1)), $c));
                    $u += 3;
                    break;
                case ClassWriter::$FIELDORMETH_INSN:
                case ClassWriter::$ITFMETH_INSN:
                    $cpIndex = $this->items[$this->readUnsignedShort(($u + 1))];
                    $itf = ($b[($cpIndex - 1)] == ClassWriter::$IMETH);
                    $iowner = $this->readClass($cpIndex, $c);
                    $cpIndex = $this->items[$this->readUnsignedShort(($cpIndex + 2))];
                    $iname = $this->readUTF8($cpIndex, $c);
                    $idesc = $this->readUTF8(($cpIndex + 2), $c);
                    if (($opcode < Opcodes::INVOKEVIRTUAL)) {
                        $mv->visitFieldInsn($opcode, $iowner, $iname, $idesc);
                    } else {
                        $mv->visitMethodInsn($opcode, $iowner, $iname, $idesc, $itf);
                    }
                    if (($opcode == Opcodes::INVOKEINTERFACE)) {
                        $u += 5;
                    } else {
                        $u += 3;
                    }

                    break;
                case ClassWriter::$INDYMETH_INSN:
                    $cpIndex = $this->items[$this->readUnsignedShort(($u + 1))];
                    $bsmIndex = $context->bootstrapMethods[$this->readUnsignedShort($cpIndex)];
                    $bsm = $this->readConst($this->readUnsignedShort($bsmIndex), $c);
                    $bsmArgCount = $this->readUnsignedShort(($bsmIndex + 2));
                    $bsmArgs = [];
                    $bsmIndex += 4;
                    for ($i = 0; ($i < $bsmArgCount); ++$i) {
                        $bsmArgs[$i] = $this->readConst($this->readUnsignedShort($bsmIndex), $c);
                        $bsmIndex += 2;
                    }

                    $cpIndex = $this->items[$this->readUnsignedShort(($cpIndex + 2))];
                    $iname = $this->readUTF8($cpIndex, $c);
                    $idesc = $this->readUTF8(($cpIndex + 2), $c);
                    $mv->visitInvokeDynamicInsn($iname, $idesc, $bsm, $bsmArgs);
                    $u += 5;

                    break;
                case ClassWriter::$TYPE_INSN:
                    $mv->visitTypeInsn($opcode, $this->readClass(($u + 1), $c));
                    $u += 3;
                    break;
                case ClassWriter::$IINC_INSN:
                    $mv->visitIincInsn(($b[($u + 1)] & 0xFF), $b[($u + 2)]);
                    $u += 3;
                    break;
                default:
                    $mv->visitMultiANewArrayInsn($this->readClass(($u + 1), $c), ($b[($u + 3)] & 0xFF));
                    $u += 4;
                    break;
            }

            while (((($tanns != null) && ($tann < count($tanns) /*from: tanns.length*/)) && ($ntoff <= $offset))) {
                if (($ntoff == $offset)) {
                    $v = $this->readAnnotationTarget($context, $tanns[$tann]);
                    $this->readAnnotationValues(
                        ($v + 2),
                        $c,
                        true,
                        $mv->visitInsnAnnotation(
                            $context->typeRef,
                            $context->typePath,
                            $this->readUTF8($v, $c),
                            true
                        )
                    );
                }
                $ntoff = ( (((++$tann >= count($tanns)) || ($this->readByte($tanns[$tann]) < 0x43)))
                    ? -1 : $this->readUnsignedShort(($tanns[$tann] + 1)) );
            }

            while (((($itanns != null) && ($itann < count($itanns))) && ($nitoff <= $offset))) {
                if (($nitoff == $offset)) {
                    $v = $this->readAnnotationTarget($context, $itanns[$itann]);
                    $this->readAnnotationValues(
                        ($v + 2),
                        $c,
                        true,
                        $mv->visitInsnAnnotation(
                            $context->typeRef,
                            $context->typePath,
                            $this->readUTF8($v, $c),
                            false
                        )
                    );
                }

                $nitoff = (((++$itann >= count($itanns)) || ($this->readByte($itanns[$itann]) < 0x43)))
                    ? -1 : $this->readUnsignedShort(($itanns[$itann] + 1));
            }
        }


        if (array_key_exists($codeLength, $labels) && ($labels[$codeLength] != null)) {
            $mv->visitLabel($labels[$codeLength]);
        }

        if ((((($context->flags & self::SKIP_DEBUG)) == 0) && ($varTable != 0))) {
            $typeTable = null;
            if (($varTypeTable != 0)) {
                $u = ($varTypeTable + 2);
                $typeTable = [];
                for ($i = count($typeTable) /*from: typeTable.length*/; ($i > 0);) {
                    $typeTable[--$i] = ($u + 6);
                    $typeTable[--$i] = $this->readUnsignedShort(($u + 8));
                    $typeTable[--$i] = $this->readUnsignedShort($u);
                    $u += 10;
                }
            }
            $u = ($varTable + 2);
            for ($i = $this->readUnsignedShort($varTable); ($i > 0); --$i) {
                $start = $this->readUnsignedShort($u);
                $length = $this->readUnsignedShort(($u + 2));
                $index = $this->readUnsignedShort(($u + 8));
                $vsignature = null;
                if (($typeTable != null)) {
                    for ($j = 0; ($j < count($typeTable) /*from: typeTable.length*/); $j += 3) {
                        if ((($typeTable[$j] == $start) && ($typeTable[($j + 1)] == $index))) {
                            $vsignature = $this->readUTF8($typeTable[($j + 2)], $c);
                            break;
                        }
                    }
                }
                $mv->visitLocalVariable(
                    $this->readUTF8(($u + 4), $c),
                    $this->readUTF8(($u + 6), $c),
                    $vsignature,
                    $labels[$start],
                    $labels[($start + $length)],
                    $index
                );
                $u += 10;
            }
        }

        if (($tanns != null)) {
            for ($i = 0; ($i < count($tanns) /*from: tanns.length*/); ++$i) {
                if (((($this->readByte($tanns[$i]) >> 1)) == ((0x40 >> 1)))) {
                    $v = $this->readAnnotationTarget($context, $tanns[$i]);
                    $v = $this->readAnnotationValues(
                        ($v + 2),
                        $c,
                        true,
                        $mv->visitLocalVariableAnnotation(
                            $context->typeRef,
                            $context->typePath,
                            $context->start,
                            $context->end,
                            $context->index,
                            $this->readUTF8($v, $c),
                            true
                        )
                    );
                }
            }
        }

        if (($itanns != null)) {
            for ($i = 0; ($i < count($itanns) /*from: itanns.length*/); ++$i) {
                if (((($this->readByte($itanns[$i]) >> 1)) == ((0x40 >> 1)))) {
                    $v = $this->readAnnotationTarget($context, $itanns[$i]);
                    $v = $this->readAnnotationValues(
                        ($v + 2),
                        $c,
                        true,
                        $mv->visitLocalVariableAnnotation(
                            $context->typeRef,
                            $context->typePath,
                            $context->start,
                            $context->end,
                            $context->index,
                            $this->readUTF8($v, $c),
                            false
                        )
                    );
                }
            }
        }

        while (($attributes != null)) {
            $attr = $attributes->next;
            $attributes->next = null;
            $mv->visitAttribute($attributes);
            $attributes = $attr;
        }

        $mv->visitMaxs($maxStack, $maxLocals);
    }

    /**
     * Parses a type annotation table to find the labels, and to visit the try
     * catch block annotations.
     *
     * @param MethodVisitor $mv      the method visitor to be used to visit the try catch block annotations.
     * @param Context       $context information about the class being parsed.
     * @param int           $u       the start offset of a type annotation table.
     * @param bool          $visible if the type annotation table to parse contains runtime visible annotations.
     *
     * @return int the start offset of each type annotation in the parsed table.
     */
    protected function readTypeAnnotations(MethodVisitor $mv, Context $context, int $u, bool $visible) : int
    {
        $c = $context->buffer;
        $offsets = [];
        $u += 2;
        for ($i = 0; ($i < count($offsets) /*from: offsets.length*/); ++$i) {
            $offsets[$i] = $u;
            $target = $this->readInt($u);
            switch ($this->uRShift($target, 24)) {
                case 0x00:
                case 0x01:
                case 0x16:
                    $u += 2;
                    break;
                case 0x13:
                case 0x14:
                case 0x15:
                    $u += 1;
                    break;
                case 0x40:
                case 0x41:
                    for ($j = $this->readUnsignedShort(($u + 1)); ($j > 0); --$j) {
                        $start = $this->readUnsignedShort(($u + 3));
                        $length = $this->readUnsignedShort(($u + 5));
                        $this->readLabel($start, $context->labels);
                        $this->readLabel(($start + $length), $context->labels);
                        $u += 6;
                    }
                    $u += 3;
                    break;
                case 0x47:
                case 0x48:
                case 0x49:
                case 0x4A:
                case 0x4B:
                    $u += 4;
                    break;
                default:
                    $u += 3;
                    break;
            }
            $pathLength = $this->readByte($u);
            if ($this->uRShift($target, 24) == 0x42) {
                $path = ( (($pathLength == 0)) ? null : new TypePath($this->b, $u) );
                $u += (1 + (2 * $pathLength));
                $u = $this->readAnnotationValues(
                    $u + 2,
                    $c,
                    true,
                    $mv->visitTryCatchAnnotation($target, $path, $this->readUTF8($u, $c), $visible)
                );
            } else {
                $u = $this->readAnnotationValues((($u + 3) + (2 * $pathLength)), $c, true, null);
            }
        }
        return $offsets;
    }

    /**
     * Parses the header of a type annotation to extract its target_type and
     * target_path (the result is stored in the given context), and returns the
     * start offset of the rest of the type_annotation structure (i.e. the
     * offset to the type_index field, which is followed by
     * num_element_value_pairs and then the name,value pairs).
     *
     * @param Context $context information about the class being parsed. This is where the extracted target_type and
     *                         target_path must be stored.
     * @param int     $u       the start offset of a type_annotation structure.
     *
     * @return int the start offset of the rest of the type_annotation structure.
     */
    protected function readAnnotationTarget(Context $context, int $u) : int
    {
        $target = $this->readInt($u);
        switch ($this->uRShift($target, 24)) {
            case 0x00:
            case 0x01:
            case 0x16:
                $target &= 0xFFFF0000;
                $u += 2;
                break;
            case 0x13:
            case 0x14:
            case 0x15:
                $target &= 0xFF000000;
                $u += 1;
                break;
            case 0x40:
            case 0x41:
                $target &= 0xFF000000;
                $n = $this->readUnsignedShort(($u + 1));
                $context->start = [];
                $context->end = [];
                $context->index = [];
                $u += 3;
                for ($i = 0; ($i < $n); ++$i) {
                    $start = $this->readUnsignedShort($u);
                    $length = $this->readUnsignedShort(($u + 2));
                    $context->start[$i] = $this->readLabel($start, $context->labels);
                    $context->end[$i] = $this->readLabel(($start + $length), $context->labels);
                    $context->index[$i] = $this->readUnsignedShort(($u + 4));
                    $u += 6;
                }

                break;
            case 0x47:
            case 0x48:
            case 0x49:
            case 0x4A:
            case 0x4B:
                $target &= 0xFF0000FF;
                $u += 4;
                break;
            default:
                $target &= ( ((($this->uRShift($target, 24) < 0x43))) ? 0xFFFFFF00 : 0xFF000000 );
                $u += 3;
                break;
        }
        $pathLength = $this->readByte($u);
        $context->typeRef = $target;
        $context->typePath = ( (($pathLength == 0)) ? null : new TypePath($this->b, $u) );
        return (($u + 1) + (2 * $pathLength));
    }

    /**
     * Reads parameter annotations and makes the given visitor visit them.
     *
     * @param MethodVisitor $mv      the visitor that must visit the annotations.
     * @param Context       $context information about the class being parsed.
     * @param int           $v       start offset in {@link #b b} of the annotations to be read.
     * @param bool          $visible <tt>true</tt> if the annotations to be read are visible at runtime.
     *
     * @return void
     */
    protected function readParameterAnnotations(
        MethodVisitor $mv,
        Context $context,
        int $v,
        bool $visible
    ) : void {
        $i = null;
        $n = ($this->b[++$v] & 0xFF);
        $synthetics = (count(Type::getArgumentTypesFromDescription($context->desc)) - $n);
        $av = null;
        for ($i = 0; ($i < $synthetics); ++$i) {
            $av = $mv->visitParameterAnnotation($i, 'Ljava/lang/Synthetic;', false);
            if (($av != null)) {
                $av->visitEnd();
            }
        }
        $c = $context->buffer;
        for (; ($i < ($n + $synthetics)); ++$i) {
            $j = $this->readUnsignedShort($v);
            $v += 2;
            for (; ($j > 0); --$j) {
                $av = $mv->visitParameterAnnotation($i, $this->readUTF8($v, $c), $visible);
                $v = $this->readAnnotationValues(($v + 2), $c, true, $av);
            }
        }
    }

    /**
     * Reads the values of an annotation and makes the given visitor visit them.
     *
     * @param int               $v     the start offset in {@link #b b} of the values to be read
     *                                 (including the unsigned short that gives the number of values).
     * @param array             $buf   buffer to be used to call {@link #readUTF8 readUTF8},
     *                                 {@link #readClass(int,char[]) readClass} or {@link #readConst readConst}.
     * @param bool              $named if the annotation values are named or not.
     * @param AnnotationVisitor $av    the visitor that must visit the values.
     *
     * @return int the end offset of the annotation values.
     */
    protected function readAnnotationValues(int $v, array $buf, bool $named, AnnotationVisitor $av) : int
    {
        $i = $this->readUnsignedShort($v);
        $v += 2;

        if ($named) {
            for (; ($i > 0); --$i) {
                $v = $this->readAnnotationValue(($v + 2), $buf, $this->readUTF8($v, $buf), $av);
            }
        } else {
            for (; ($i > 0); --$i) {
                $v = $this->readAnnotationValue($v, $buf, null, $av);
            }
        }

        if (($av != null)) {
            $av->visitEnd();
        }

        return $v;
    }

    /**
     * Reads a value of an annotation and makes the given visitor visit it.
     *
     * @param int               $v    the start offset in {@link #b b} of the value to be read
     *                                (<i>not including the value name constant pool index</i>).
     * @param array             $buf  buffer to be used to call {@link #readUTF8 readUTF8},
     *                                {@link #readClass(int,char[]) readClass} or {@link #readConst readConst}.
     * @param string            $name the name of the value to be read.
     * @param AnnotationVisitor $av   the visitor that must visit the value.
     *
     * @return int the end offset of the annotation value.
     */
    protected function readAnnotationValue(int $v, array $buf, string $name, AnnotationVisitor $av) : int
    {
        $i = null;
        if (($av == null)) {
            switch (($this->b[$v] & 0xFF)) {
                case 'e':
                    return ($v + 5);
                case '@':
                    return $this->readAnnotationValues(($v + 3), $buf, true, null);
                case '[':
                    return $this->readAnnotationValues(($v + 1), $buf, false, null);
                default:
                    return ($v + 3);
            }
        }

        switch (($this->b[++$v] & 0xFF)) {
            case 'I':
            case 'J':
            case 'F':
            case 'D':
                $av->visit($name, $this->readConst($this->readUnsignedShort($v), $buf));
                $v += 2;
                break;
            case 'B':
                $av->visit($name, $this->readInt($this->items[$this->readUnsignedShort($v)]));
                $v += 2;
                break;
            case 'Z':
                $av->visit(
                    $name,
                    ((($this->readInt($this->items[$this->readUnsignedShort($v)]) == 0)) ? false : true )
                );
                $v += 2;
                break;
            case 'S':
                $av->visit($name, $this->readInt($this->items[$this->readUnsignedShort($v)]));
                $v += 2;
                break;
            case 'C':
                $av->visit($name, $this->readInt($this->items[$this->readUnsignedShort($v)]));
                $v += 2;
                break;
            case 's':
                $av->visit($name, $this->readUTF8($v, $buf));
                $v += 2;
                break;
            case 'e':
                $av->visitEnum($name, $this->readUTF8($v, $buf), $this->readUTF8(($v + 2), $buf));
                $v += 4;
                break;
            case 'c':
                $av->visit($name, Type::getType($this->readUTF8($v, $buf)));
                $v += 2;
                break;
            case '@':
                $v = $this->readAnnotationValues(
                    ($v + 2),
                    $buf,
                    true,
                    $av->visitAnnotation($name, $this->readUTF8($v, $buf))
                );
                break;
            case '[':
                $size = $this->readUnsignedShort($v);
                $v += 2;
                if (($size == 0)) {
                    return $this->readAnnotationValues(($v - 2), $buf, false, $av->visitArray($name));
                }
                switch (($this->b[++$v] & 0xFF)) {
                    case 'B':
                        $bv = [];
                        for ($i = 0; ($i < $size); ++$i) {
                            $bv[$i] = $this->readInt($this->items[$this->readUnsignedShort($v)]);
                            $v += 3;
                        }
                        $av->visit($name, $bv);
                        --$v;
                        break;
                    case 'Z':
                        $zv = [];
                        for ($i = 0; ($i < $size); ++$i) {
                            $zv[$i] = ($this->readInt($this->items[$this->readUnsignedShort($v)]) != 0);
                            $v += 3;
                        }
                        $av->visit($name, $zv);
                        --$v;
                        break;
                    case 'S':
                        $sv = [];
                        for ($i = 0; ($i < $size); ++$i) {
                            $sv[$i] = $this->readInt($this->items[$this->readUnsignedShort($v)]);
                            $v += 3;
                        }
                        $av->visit($name, $sv);
                        --$v;
                        break;
                    case 'C':
                        $cv = [];
                        for ($i = 0; ($i < $size); ++$i) {
                            $cv[$i] = $this->readInt($this->items[$this->readUnsignedShort($v)]);
                            $v += 3;
                        }
                        $av->visit($name, $cv);
                        --$v;
                        break;
                    case 'I':
                        $iv = [];
                        for ($i = 0; ($i < $size); ++$i) {
                            $iv[$i] = $this->readInt($this->items[$this->readUnsignedShort($v)]);
                            $v += 3;
                        }
                        $av->visit($name, $iv);
                        --$v;
                        break;
                    case 'J':
                        $lv = [];
                        for ($i = 0; ($i < $size); ++$i) {
                            $lv[$i] = $this->readLong($this->items[$this->readUnsignedShort($v)]);
                            $v += 3;
                        }
                        $av->visit($name, $lv);
                        --$v;
                        break;
                    case 'F':
                        $fv = [];
                        for ($i = 0; ($i < $size); ++$i) {
                            $fv[$i] = $this->intBitsToFloat($this->readInt($this->items[$this->readUnsignedShort($v)]));
                            $v += 3;
                        }
                        $av->visit($name, $fv);
                        --$v;
                        break;
                    case 'D':
                        $dv = [];
                        for ($i = 0; ($i < $size); ++$i) {
                            $dv[$i] = $this->longBitsToDouble(
                                $this->readLong($this->items[$this->readUnsignedShort($v)])
                            );
                            $v += 3;
                        }
                        $av->visit($name, $dv);
                        --$v;
                        break;
                    default:
                        $v = $this->readAnnotationValues(($v - 3), $buf, false, $av->visitArray($name));
                }
        }
        return $v;
    }

    /**
     * Computes the implicit frame of the method currently being parsed (as
     * defined in the given {@link Context}) and stores it in the given context.
     *
     * @param Context $frame information about the class being parsed.
     */
    protected function getImplicitFrame(Context $frame) : void
    {
        $desc   = &$frame->desc;
        $locals = &$frame->local;
        $local  = 0;
        if (((($frame->access & Opcodes::ACC_STATIC)) == 0)) {
            if ($frame->name === '<init>') {
                $locals[++$local] = Opcodes::UNINITIALIZED_THIS;
            } else {
                $locals[++$local] = $this->readClass(($this->header + 2), $frame->buffer);
            }
        }

        $i = 1;
        while (true) {
            $j = $i;
            switch ($desc[$i++]) {
                case 'Z':
                case 'C':
                case 'B':
                case 'S':
                case 'I':
                    $locals[$local++] = Opcodes::INTEGER;
                    break;
                case 'F':
                    $locals[$local++] = Opcodes::FLOAT;
                    break;
                case 'J':
                    $locals[$local++] = Opcodes::LONG;
                    break;
                case 'D':
                    $locals[$local++] = Opcodes::DOUBLE;
                    break;
                case '[':
                    while ($desc[$i] === '[') {
                        ++$i;
                    }

                    if ($desc[$i] === 'L') {
                        ++$i;
                        while ($desc[$i] !== ';') {
                            ++$i;
                        }
                    }

                    $locals[$local++] = mb_substr($desc, $j, ++$i - $j);
                    break;
                case 'L':
                    while ($desc[$i] != ';') {
                        ++$i;
                    }

                    $locals[$local++] = mb_substr($desc, $j+1, ++$i - ($j+1));
                    break;
                default:
                    break 2;
            }
        }
        $frame->localCount = $local;
    }

    /**
     * Reads a stack map frame and stores the result in the given
     * {@link Context} object.
     *
     * @param int     $stackMap the start offset of a stack map frame in the class file.
     * @param bool    $zip      if the stack map frame at stackMap is compressed or not.
     * @param bool    $unzip    if the stack map frame must be uncompressed.
     * @param Context $frame    where the parsed stack map frame must be stored.
     *
     * @return int the offset of the first byte following the parsed frame.
     */
    protected function readFrame(int $stackMap, bool $zip, bool $unzip, Context $frame)
    {
        $c = $frame->buffer;
        $labels = $frame->labels;
        $tag = null;
        $delta = null;
        if ($zip) {
            $tag = ($this->b[++$stackMap] & 0xFF);
        } else {
            $tag = MethodWriter::$FULL_FRAME;
            $frame->offset = -1;
        }
        $frame->localDiff = 0;
        if (($tag < MethodWriter::$SAME_LOCALS_1_STACK_ITEM_FRAME)) {
            $delta = $tag;
            $frame->mode = Opcodes::F_SAME;
            $frame->stackCount = 0;
        } elseif (($tag < MethodWriter::$RESERVED)) {
            $delta = ($tag - MethodWriter::$SAME_LOCALS_1_STACK_ITEM_FRAME);
            $stackMap = $this->readFrameType($frame->stack, 0, $stackMap, $c, $labels);
            $frame->mode = Opcodes::F_SAME1;
            $frame->stackCount = 1;
        } else {
            $delta = $this->readUnsignedShort($stackMap);
            $stackMap += 2;
            if (($tag == MethodWriter::$SAME_LOCALS_1_STACK_ITEM_FRAME_EXTENDED)) {
                $stackMap = $this->readFrameType($frame->stack, 0, $stackMap, $c, $labels);
                $frame->mode = Opcodes::F_SAME1;
                $frame->stackCount = 1;
            } elseif ((($tag >= MethodWriter::$CHOP_FRAME) && ($tag < MethodWriter::$SAME_FRAME_EXTENDED))) {
                    $frame->mode = Opcodes::F_CHOP;
                    $frame->localDiff = (MethodWriter::$SAME_FRAME_EXTENDED - $tag);
                    $frame->localCount -= $frame->localDiff;
                    $frame->stackCount = 0;
            } elseif (($tag == MethodWriter::$SAME_FRAME_EXTENDED)) {
                    $frame->mode = Opcodes::F_SAME;
                    $frame->stackCount = 0;
            } elseif (($tag < MethodWriter::$FULL_FRAME)) {
                    $local = ( ($unzip) ? $frame->localCount : 0 );
                for ($i = ($tag - MethodWriter::$SAME_FRAME_EXTENDED); ($i > 0); --$i) {
                        $stackMap = $this->readFrameType($frame->local, ++$local, $stackMap, $c, $labels);
                }
                    $frame->mode = Opcodes::F_APPEND;
                    $frame->localDiff = ($tag - MethodWriter::$SAME_FRAME_EXTENDED);
                    $frame->localCount += $frame->localDiff;
                            $frame->stackCount = 0;
            } else {
                $frame->mode = Opcodes::F_FULL;
                $n = $this->readUnsignedShort($stackMap);
                $stackMap += 2;
                $frame->localDiff = $n;
                $frame->localCount = $n;
                for ($local = 0; ($n > 0); --$n) {
                    $stackMap = $this->readFrameType($frame->local, ++$local, $stackMap, $c, $labels);
                }
                $n = $this->readUnsignedShort($stackMap);
                $stackMap += 2;
                $frame->stackCount = $n;
                for ($stack = 0; ($n > 0); --$n) {
                    $stackMap = $this->readFrameType($frame->stack, ++$stack, $stackMap, $c, $labels);
                }
            }
        }
        $frame->offset += ($delta + 1);
        $this->readLabel($frame->offset, $labels);
        return $stackMap;
    }

    /**
     * Reads a stack map frame type and stores it at the given index in the
     * given array.
     *
     * @param array    $frame
     *                         the array where the parsed type must be stored.
     * @param int      $index
     *                         the index in 'frame' where the parsed type must be stored.
     * @param int      $v
     *                         the start offset of the stack map frame type to read.
     * @param string[] $buf
     *                         a buffer to read strings.
     * @param Label[]  $labels
     *                         the labels of the method currently being parsed, indexed by
     *                         their offset. If the parsed type is an Uninitialized type, a
     *                         new label for the corresponding NEW instruction is stored in
     *                         this array if it does not already exist.
     *
     * @return int the offset of the first byte after the parsed type.
     */
    protected function readFrameType(array &$frame, int $index, int $v, array $buf, array $labels) : int
    {
        $type = ($this->b[++$v] & 0xFF);
        switch ($type) {
            case 0:
                $frame[$index] = Opcodes::TOP;
                break;
            case 1:
                $frame[$index] = Opcodes::INTEGER;
                break;
            case 2:
                $frame[$index] = Opcodes::FLOAT;
                break;
            case 3:
                $frame[$index] = Opcodes::DOUBLE;
                break;
            case 4:
                $frame[$index] = Opcodes::LONG;
                break;
            case 5:
                $frame[$index] = Opcodes::NULL;
                break;
            case 6:
                $frame[$index] = Opcodes::UNINITIALIZED_THIS;
                break;
            case 7:
                $frame[$index] = $this->readClass($v, $buf);
                $v += 2;
                break;
            default:
                $frame[$index] = $this->readLabel($this->readUnsignedShort($v), $labels);
                $v += 2;
        }

        return $v;
    }

    /**
     * Returns the label corresponding to the given offset. The default
     * implementation of this method creates a label for the given offset if it
     * has not been already created.
     *
     * @param int     $offset a bytecode offset in a method.
     * @param Label[] $labels the already created labels, indexed by their offset. If a
     *                        label already exists for offset this method must not create a
     *                        new one. Otherwise it must store the new label in this array.
     *
     * @return Label a non null Label, which must be equal to labels[offset].
     */
    protected function readLabel(int $offset, array &$labels) : Label
    {
        if (!array_key_exists($offset, $labels) || $labels[$offset] == null) {
            $labels[$offset] = new Label();
        }

        return $labels[$offset];
    }

    /**
     * Returns the start index of the attribute_info structure of this class.
     *
     * @return int the start index of the attribute_info structure of this class.
     */
    protected function getAttributes() : int
    {
        $u = (($this->header + 8) + ($this->readUnsignedShort(($this->header + 6)) * 2));
        for ($i = $this->readUnsignedShort($u); ($i > 0); --$i) {
            for ($j = $this->readUnsignedShort(($u + 8)); ($j > 0); --$j) {
                $u += (6 + $this->readInt(($u + 12)));
            }

            $u += 8;
        }

        $u += 2;
        for ($i = $this->readUnsignedShort($u); ($i > 0); --$i) {
            for ($j = $this->readUnsignedShort(($u + 8)); ($j > 0); --$j) {
                $u += (6 + $this->readInt(($u + 12)));
            }
            $u += 8;
        }

        return ($u + 2);
    }

    /**
     * Reads an attribute in {@link #b b}.
     *
     * @param Attribute[] $attrs
     *                             prototypes of the attributes that must be parsed during the
     *                             visit of the class. Any attribute whose type is not equal to
     *                             the type of one the prototypes is ignored (i.e. an empty
     *                             {@link Attribute} instance is returned).
     * @param string      $type
     *                             the type of the attribute.
     * @param int         $off
     *                             index of the first byte of the attribute's content in
     *                             {@link #b b}. The 6 attribute header bytes, containing the
     *                             type and the length of the attribute, are not taken into
     *                             account here (they have already been read).
     * @param int         $len
     *                             the length of the attribute's content.
     * @param string[]    $buf
     *                             buffer to be used to call {@link #readUTF8 readUTF8},
     *                             {@link #readClass(int,char[]) readClass} or {@link #readConst readConst}.
     * @param int         $codeOff
     *                             index of the first byte of code's attribute content in
     *                             {@link #b b}, or -1 if the attribute to be read is not a code
     *                             attribute. The 6 attribute header bytes, containing the type
     *                             and the length of the attribute, are not taken into account here.
     *
     * @param Label[]     $labels
     *                             the labels of the method's code, or <tt>null</tt> if the
     *                             attribute to be read is not a code attribute.
     *
     * @return Attribute the attribute that has been read, or <tt>null</tt> to skip this attribute.
     */
    protected function readAttribute(
        array $attrs,
        string $type,
        int $off,
        int $len,
        array $buf,
        int $codeOff,
        ?array $labels
    ) : Attribute {
        for ($i = 0; ($i < count($attrs) /*from: attrs.length*/); ++$i) {
            if ($attrs[$i]->type->equals($type)) {
                return $attrs[$i]->read($this, $off, $len, $buf, $codeOff, $labels);
            }
        }

        return (new Attribute($type))->read($this, $off, $len, null, -1, null);
    }

    // ------------------------------------------------------------------------
    // Utility methods: low level parsing
    // ------------------------------------------------------------------------

    /**
     * Returns the number of constant pool items in {@link #b b}.
     *
     * @return int the number of constant pool items in {@link #b b}.
     */
    public function getItemCount() : int
    {
        return count($this->items);
    }

    /**
     * Returns the start index of the constant pool item in {@link #b b}, plus
     * one. <i>This method is intended for {@link Attribute} sub classes, and is
     * normally not needed by class generators or adapters.</i>
     *
     * @param int $item the index a constant pool item.
     *
     * @return int the start index of the constant pool item in {@link #b b}, plus one.
     */
    public function getItem(int $item) : int
    {
        return $this->items[$item];
    }

    /**
     * Returns the maximum length of the strings contained in the constant pool
     * of the class.
     *
     * @return int the maximum length of the strings contained in the constant pool of the class.
     */
    public function getMaxStringLength() : int
    {
        return $this->maxStringLength;
    }

    /**
     * Reads a byte value in {@link #b b}. <i>This method is intended for
     * {@link Attribute} sub classes, and is normally not needed by class
     * generators or adapters.</i>
     *
     * @param int $index the start index of the value to be read in {@link #b b}.
     *
     * @return int the read value.
     */
    public function readByte(int $index) : int
    {
        return ($this->b[$index] & 0xFF);
    }

    /**
     * Reads an unsigned short value in {@link #b b}. <i>This method is intended
     * for {@link Attribute} sub classes, and is normally not needed by class
     * generators or adapters.</i>
     *
     * @param int $index the start index of the value to be read in {@link #b b}.
     *
     * @return int the read value.
     */
    public function readUnsignedShort(int $index) /*: int*/
    {
        $b = $this->b;
        return ((((($b[$index] & 0xFF)) << 8)) | (($b[($index + 1)] & 0xFF)));
    }

    /**
     * Reads a signed short value in {@link #b b}. <i>This method is intended
     * for {@link Attribute} sub classes, and is normally not needed by class
     * generators or adapters.</i>
     *
     * @param int $index the start index of the value to be read in {@link #b b}.
     *
     * @return int the read value.
     */
    public function readShort(int $index) : int
    {
        $b = $this->b;
        return (((((($b[$index] & 0xFF)) << 8)) | (($b[($index + 1)] & 0xFF))));
    }

    /**
     * Reads a signed int value in {@link #b b}. <i>This method is intended for
     * {@link Attribute} sub classes, and is normally not needed by class
     * generators or adapters.</i>
     *
     * @param int $index the start index of the value to be read in {@link #b b}.
     *
     * @return int the read value.
     */
    public function readInt(int $index) : int
    {
        $b = $this->b;
        return ((((((($b[$index] & 0xFF)) << 24)) | (((($b[($index + 1)] & 0xFF)) << 16)))
                | (((($b[($index + 2)] & 0xFF)) << 8))) | (($b[($index + 3)] & 0xFF)));
    }

    /**
     * Reads a signed long value in {@link #b b}. <i>This method is intended for
     * {@link Attribute} sub classes, and is normally not needed by class
     * generators or adapters.</i>
     *
     * @param int $index the start index of the value to be read in {@link #b b}.
     *
     * @return float the read value.
     */
    public function readLong(int $index) : float
    {
        $l1 = $this->readInt($index);
        $l0 = ($this->readInt(($index + 4)) & 0xFFFFFFFF);
        return ((($l1 << 32)) | $l0);
    }

    /**
     * Reads an UTF8 string constant pool item in {@link #b b}. <i>This method
     * is intended for {@link Attribute} sub classes, and is normally not needed
     * by class generators or adapters.</i>
     *
     * @param int      $index
     *                         the start index of an unsigned short value in {@link #b b},
     *                         whose value is the index of an UTF8 constant pool item.
     * @param string[] $buf
     *                         buffer to be used to read the item.
     *
     * @return string the String corresponding to the specified UTF8 item.
     */
    public function readUTF8($index, $buf) : string
    {
        $item = $this->readUnsignedShort($index);
        if ((($index == 0) || ($item == 0))) {
            return null;
        }

        if (array_key_exists($item, $this->strings)) {
            return $this->strings[$item];
        }

        $index = $this->items[$item];

        // TODO [SIMEK, i] why there was this $this->readUTF(($index + 2) ??
        return $this->strings[$item] = $this->readUTF(($index + 1), $this->readUnsignedShort($index), $buf);
    }

    /**
     * Reads UTF8 string in {@link #b b}.
     *
     * @param int   $index  start offset of the UTF8 string to be read.
     * @param int   $utfLen length of the UTF8 string to be read.
     * @param array $buf    buffer to be used to read the string.
     *
     * @return string the String corresponding to the specified UTF8 string.
     */
    protected function readUTF(int $index, int $utfLen, array $buf) : string
    {
        $endIndex = ($index + $utfLen);
        $b = $this->b;
        $strLen = 0;
        $c = null;
        $st = 0;
        $cc = 0;

        while (($index < $endIndex)) {
            $c = $b[++$index];
            switch ($st) {
                case 0:
                    $c = ($c & 0xFF);
                    if (($c < 0x80)) {
                        $buf[++$strLen] = chr($c);
                    } elseif ((($c < 0xE0) && ($c > 0xBF))) {
                        $cc = chr(($c & 0x1F));
                        $st = 1;
                    } else {
                        $cc = chr(($c & 0x0F));
                        $st = 2;
                    }

                    break;
                case 1:
                    $buf[++$strLen] = chr((((($cc << 6)) | (($c & 0x3F)))));
                    $st = 0;

                    break;
                case 2:
                    $cc = chr(((($cc << 6)) | (($c & 0x3F))));
                    $st = 1;

                    break;
            }
        }

        return implode('', $buf);
    }

    /**
     * Reads a class constant pool item in {@link #b b}. <i>This method is
     * intended for {@link Attribute} sub classes, and is normally not needed by
     * class generators or adapters.</i>
     *
     * @param int   $index the start index of an unsigned short value in {@link #b b}, whose value is the index of
     *                     a class constant pool item.
     * @param array $buf   buffer to be used to read the item.
     *
     * @return string the String corresponding to the specified class item.
     */
    public function readClass(int $index, array $buf) : string
    {
        // computes the start index of the CONSTANT_Class item in b
        // and reads the CONSTANT_Utf8 item designated by
        // the first two bytes of this CONSTANT_Class item
        return $this->readUTF8($this->items[$this->readUnsignedShort($index)], $buf);
    }

    /**
     * Reads a numeric or string constant pool item in {@link #b b}. <i>This
     * method is intended for {@link Attribute} sub classes, and is normally not
     * needed by class generators or adapters.</i>
     *
     * @param int   $item the index of a constant pool item.
     * @param array $buf  buffer to be used to read the item.
     *
     * @return mixed the {@link Integer}, {@link Float}, {@link Long}, {@link Double},
     *         {@link String}, {@link Type} or {@link Handle} corresponding to
     *         the given constant pool item.
     */
    public function readConst(int $item, array $buf)
    {
        $index = $this->items[$item];
        switch ($this->b[($index - 1)]) {
            case ClassWriter::$INT:
                return $this->readInt($index);
            case ClassWriter::$FLOAT:
                return $this->intBitsToFloat($this->readInt($index));
            case ClassWriter::$LONG:
                return $this->readLong($index);
            case ClassWriter::$DOUBLE:
                return $this->longBitsToDouble($this->readLong($index));
            case ClassWriter::$CLASS:
                return Type::getObjectType($this->readUTF8($index, $buf));
            case ClassWriter::$STR:
                return $this->readUTF8($index, $buf);
            case ClassWriter::$MTYPE:
                /* match: String */
                return Type::getMethodTypeFromDescriptor($this->readUTF8($index, $buf));
            default:
                $tag = $this->readByte($index);
                $items = $this->items;
                $cpIndex = $items[$this->readUnsignedShort(($index + 1))];
                $itf = ($this->b[($cpIndex - 1)] == ClassWriter::$IMETH);
                $owner = $this->readClass($cpIndex, $buf);
                $cpIndex = $items[$this->readUnsignedShort(($cpIndex + 2))];
                $name = $this->readUTF8($cpIndex, $buf);
                $desc = $this->readUTF8(($cpIndex + 2), $buf);
                return new Handle($tag, $owner, $name, $desc, $itf);
        }
    }

    /**
     * Returns the float value corresponding to a given bit representation. The argument is considered to
     * be a representation of a floating-point value according to the IEEE 754 floating-point
     * "single format" bit layout.
     *
     * @param int $bits an integer.
     *
     * @return float the float floating-point value with the same bit pattern.
     */
    private function intBitsToFloat(int $bits)
    {
        // based on https://docs.oracle.com/javase/6/docs/api/java/lang/Float.html#intBitsToFloat(int)
        $s = (($bits >> 31) == 0) ? 1 : -1;
        $e = (($bits >> 23) & 0xff);
        $m = ($e == 0) ? ($bits & 0x7fffff) << 1 : ($bits & 0x7fffff) | 0x800000;

        return $s * $m * 2 ** ($e-150);
    }

    /**
     * Returns the double value corresponding to a given bit representation. The argument is considered to
     * be a representation of a floating-point value according to the IEEE 754 floating-point
     * "double format" bit layout
     *
     * @param float $bits any long integer.
     *
     * @return float the double floating-point value with the same bit pattern.
     */
    private function longBitsToDouble(float $bits)
    {
        // based on https://docs.oracle.com/javase/6/docs/api/java/lang/Double.html#longBitsToDouble(long)
        $s = (($bits >> 63) == 0) ? 1 : -1;
        $e = (int)(($bits >> 52) & 0x7ff);
        $m = ($e == 0) ? ($bits & 0xfffffffffffff) << 1 : ($bits & 0xfffffffffffff) | 0x10000000000000;

        return $s * $m * 2 ** ($e-1075);
    }

    private function uRShift($a, $b)
    {
        return ($a >> $b & 0xFF);
    }
}
