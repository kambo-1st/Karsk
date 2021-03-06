<?php
/**
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
use Kambo\Karsk\Exception\NotImplementedException;
use Kambo\Karsk\Exception\IllegalArgumentException;

/**
 * A {@link ClassVisitor} that generates classes in bytecode form. More
 * precisely this visitor generates a byte array conforming to the Java class
 * file format. It can be used alone, to generate a Java class "from scratch",
 * or with one or more {@link ClassReader ClassReader} and adapter class visitor
 * to generate a modified class from one or more existing Java classes.
 *
 * @author  Eric Bruneton
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class ClassWriter extends ClassVisitor
{
    /**
     * Flag to automatically compute the maximum stack size and the maximum
     * number of local variables of methods. If this flag is set, then the
     * arguments of the {@link MethodVisitor#visitMaxs visitMaxs} method of the
     * {@link MethodVisitor} returned by the {@link #visitMethod visitMethod}
     * method will be ignored, and computed automatically from the signature and
     * the bytecode of each method.
     *
     * @see #ClassWriter(int)
     */
    public static $COMPUTE_MAXS = 1;

    /**
     * Flag to automatically compute the stack map frames of methods from
     * scratch. If this flag is set, then the calls to the
     * {@link MethodVisitor#visitFrame} method are ignored, and the stack map
     * frames are recomputed from the methods bytecode. The arguments of the
     * {@link MethodVisitor#visitMaxs visitMaxs} method are also ignored and
     * recomputed from the bytecode. In other words, COMPUTE_FRAMES implies
     * COMPUTE_MAXS.
     *
     * @see #ClassWriter(int)
     */
    public static $COMPUTE_FRAMES = 2;

    /**
     * Pseudo access flag to distinguish between the synthetic attribute and the
     * synthetic access flag.
     */
    public static $ACC_SYNTHETIC_ATTRIBUTE = 0x40000;
    /**
     * Factor to convert from ACC_SYNTHETIC_ATTRIBUTE to Opcode::ACC_SYNTHETIC.
     */
    public static $TO_ACC_SYNTHETIC = 0x40000 / Opcodes::ACC_SYNTHETIC;

    /**
     * The type of instructions without any argument.
     */
    public static $NOARG_INSN = 0;

    /**
     * The type of instructions with an signed byte argument.
     */
    public static $SBYTE_INSN = 1;

    /**
     * The type of instructions with an signed short argument.
     */
    public static $SHORT_INSN = 2;

    /**
     * The type of instructions with a local variable index argument.
     */
    public static $VAR_INSN = 3;

    /**
     * The type of instructions with an implicit local variable index argument.
     */
    public static $IMPLVAR_INSN = 4;

    /**
     * The type of instructions with a type descriptor argument.
     */
    public static $TYPE_INSN = 5;

    /**
     * The type of field and method invocations instructions.
     */
    public static $FIELDORMETH_INSN = 6;
    /**
     * The type of the INVOKEINTERFACE/INVOKEDYNAMIC instruction.
     */
    public static $ITFMETH_INSN = 7;

    /**
     * The type of the INVOKEDYNAMIC instruction.
     */
    public static $INDYMETH_INSN = 8;

    /**
     * The type of instructions with a 2 bytes bytecode offset label.
     */
    public static $LABEL_INSN = 9;

    /**
     * The type of instructions with a 4 bytes bytecode offset label.
     */
    public static $LABELW_INSN = 10;

    /**
     * The type of the LDC instruction.
     */
    public static $LDC_INSN = 11;

    /**
     * The type of the LDC_W and LDC2_W instructions.
     */
    public static $LDCW_INSN = 12;

    /**
     * The type of the IINC instruction.
     */
    public static $IINC_INSN = 13;

    /**
     * The type of the TABLESWITCH instruction.
     */
    public static $TABL_INSN = 14;

    /**
     * The type of the LOOKUPSWITCH instruction.
     */
    public static $LOOK_INSN = 15;

    /**
     * The type of the MULTIANEWARRAY instruction.
     */
    public static $MANA_INSN = 16;

    /**
     * The type of the WIDE instruction.
     */
    public static $WIDE_INSN = 17;

    /**
     * The type of the ASM pseudo instructions with an unsigned 2 bytes offset
     * label (see Label#resolve).
     */
    public static $ASM_LABEL_INSN = 18;

    /**
     * Represents a frame inserted between already existing frames. This kind of
     * frame can only be used if the frame content can be computed from the
     * previous existing frame and from the instructions between this existing
     * frame and the inserted one, without any knowledge of the type hierarchy.
     * This kind of frame is only used when an unconditional jump is inserted in
     * a method while expanding an ASM pseudo instruction (see ClassReader).
     */
    public static $F_INSERT = 256;

    /**
     * The instruction types of all JVM opcodes.
     */
    public static $TYPE = [
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        1,
        2,
        11,
        12,
        12,
        3,
        3,
        3,
        3,
        3,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        3,
        3,
        3,
        3,
        3,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        4,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        13,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        9,
        9,
        9,
        9,
        9,
        9,
        9,
        9,
        9,
        9,
        9,
        9,
        9,
        9,
        9,
        9,
        3,
        14,
        15,
        0,
        0,
        0,
        0,
        0,
        0,
        6,
        6,
        6,
        6,
        6,
        6,
        6,
        7,
        8,
        5,
        1,
        5,
        0,
        0,
        5,
        5,
        0,
        0,
        17,
        16,
        9,
        9,
        10,
        10,
        18,
        18,
        18,
        18,
        18,
        18,
        18,
        18,
        18,
        18,
        18,
        18,
        18,
        18,
        18,
        18,
        18,
        18,
    ];

    /**
     * The type of CONSTANT_Class constant pool items.
     */
    public static $CLASS = 7;

    /**
     * The type of CONSTANT_Fieldref constant pool items.
     */
    public static $FIELD = 9;

    /**
     * The type of CONSTANT_Methodref constant pool items.
     */
    public static $METH = 10;

    /**
     * The type of CONSTANT_InterfaceMethodref constant pool items.
     */
    public static $IMETH = 11;

    /**
     * The type of CONSTANT_String constant pool items.
     */
    public static $STR = 8;

    /**
     * The type of CONSTANT_Integer constant pool items.
     */
    public static $INT = 3;

    /**
     * The type of CONSTANT_Float constant pool items.
     */
    public static $FLOAT = 4;

    /**
     * The type of CONSTANT_Long constant pool items.
     */
    public static $LONG = 5;

    /**
     * The type of CONSTANT_Double constant pool items.
     */
    public static $DOUBLE = 6;

    /**
     * The type of CONSTANT_NameAndType constant pool items.
     */
    public static $NAME_TYPE = 12;

    /**
     * The type of CONSTANT_Utf8 constant pool items.
     */
    public static $UTF8 = 1;

    /**
     * The type of CONSTANT_MethodType constant pool items.
     */
    public static $MTYPE = 16;
    /**
     * The type of CONSTANT_MethodHandle constant pool items.
     */
    public static $HANDLE = 15;

    /**
     * The type of CONSTANT_InvokeDynamic constant pool items.
     */
    public static $INDY = 18;

    /**
     * The base value for all CONSTANT_MethodHandle constant pool items.
     * Internally, ASM store the 9 variations of CONSTANT_MethodHandle into 9
     * different items.
     */
    public static $HANDLE_BASE = 20;

    /**
     * Normal type Item stored in the ClassWriter {@link ClassWriter#typeTable},
     * instead of the constant pool, in order to avoid clashes with normal
     * constant pool items in the ClassWriter constant pool's hash table.
     */
    public static $TYPE_NORMAL = 30;

    /**
     * Uninitialized type Item stored in the ClassWriter
     * {@link ClassWriter#typeTable}, instead of the constant pool, in order to
     * avoid clashes with normal constant pool items in the ClassWriter constant
     * pool's hash table.
     */
    public static $TYPE_UNINIT = 31;

    /**
     * Merged type Item stored in the ClassWriter {@link ClassWriter#typeTable},
     * instead of the constant pool, in order to avoid clashes with normal
     * constant pool items in the ClassWriter constant pool's hash table.
     */
    public static $TYPE_MERGED = 32;

    /**
     * The type of BootstrapMethods items. These items are stored in a special
     * class attribute named BootstrapMethods and not in the constant pool.
     */
    public static $BSM  = 33;

    /**
     * The class reader from which this class writer was constructed, if any.
     *
     * @var ClassReader
     */
    public $cr;

    /**
     * Minor and major version numbers of the class to be generated.
     *
     * @var int
     */
    public $version;

    /**
     * Index of the next item to be added in the constant pool.
     *
     * @var int
     */
    public $index;

    /**
     * The constant pool of this class.
     *
     * @var ByteVector
     */
    public $pool;

    /**
     * The constant pool's hash table data.
     *
     * @var Item[]
     */
    public $items;

    /**
     * The threshold of the constant pool's hash table.
     *
     * @var int
     */
    public $threshold;

    /**
     * A reusable key used to look for items in the {@link #items} hash table.
     *
     * @var Item
     */
    public $key;

    /**
     * A reusable key used to look for items in the {@link #items} hash table.
     *
     * @var Item
     */
    public $key2;

    /**
     * A reusable key used to look for items in the {@link #items} hash table.
     *
     * @var Item
     */
    public $key3;

    /**
     * A reusable key used to look for items in the {@link #items} hash table.
     *
     * @var Item
     */
    public $key4;

    /**
     * A type table used to temporarily store internal names that will not
     * necessarily be stored in the constant pool. This type table is used by
     * the control flow and data flow analysis algorithm used to compute stack
     * map frames from scratch. This array associates to each index <tt>i</tt>
     * the Item whose index is <tt>i</tt>. All Item objects stored in this array
     * are also stored in the {@link #items} hash table. These two arrays allow
     * to retrieve an Item from its index or, conversely, to get the index of an
     * Item from its value. Each Item stores an internal name in its
     * {@link Item#strVal1} field.
     *
     * @var Item[]
     */
    public $typeTable;

    /**
     * Number of elements in the {@link #typeTable} array.
     *
     * @var int
     */
    public $typeCount;

    /**
     * The access flags of this class.
     *
     * @var int
     */
    public $access;

    /**
     * The constant pool item that contains the internal name of this class.
     *
     * @var int
     */
    public $name;

    /**
     * The internal name of this class.
     *
     * @var string
     */
    public $thisName;

    /**
     * The constant pool item that contains the signature of this class.
     *
     * @var int
     */
    public $signature;

    /**
     * The constant pool item that contains the internal name of the super class
     * of this class.
     *
     * @var int
     */
    public $superName;

    /**
     * Number of interfaces implemented or extended by this class or interface.
     *
     * @var int
     */
    public $interfaceCount;

    /**
     * The interfaces implemented or extended by this class or interface. More
     * precisely, this array contains the indexes of the constant pool items
     * that contain the internal names of these interfaces.
     *
     * @var int[]
     */
    public $interfaces;

    /**
     * The index of the constant pool item that contains the name of the source
     * file from which this class was compiled.
     *
     * @var int
     */
    public $sourceFile;

    /**
     * The SourceDebug attribute of this class.
     *
     * @var ByteVector
     */
    public $sourceDebug;

    /**
     * The constant pool item that contains the name of the enclosing class of
     * this class.
     *
     * @var int
     */
    public $enclosingMethodOwner;

    /**
     * The constant pool item that contains the name and descriptor of the
     * enclosing method of this class.
     *
     * @var int
     */
    public $enclosingMethod;

    /**
     * The runtime visible annotations of this class.
     *
     * @var AnnotationWriter
     */
    public $anns;

    /**
     * The runtime invisible annotations of this class.
     *
     * @var AnnotationWriter
     */
    public $ianns;

    /**
     * The runtime visible type annotations of this class.
     *
     * @var AnnotationWriter
     */
    public $tanns;

    /**
     * The runtime invisible type annotations of this class.
     *
     * @var AnnotationWriter
     */
    public $itanns;

    /**
     * The non standard attributes of this class.
     *
     * @var Attribute
     */
    public $attrs;

    /**
     * The number of entries in the InnerClasses attribute.
     *
     * @var int
     */
    public $innerClassesCount;

    /**
     * The InnerClasses attribute.
     *
     * @var ByteVector
     */
    public $innerClasses;

    /**
     * The number of entries in the BootstrapMethods attribute.
     *
     * @var int
     */
    public $bootstrapMethodsCount;

    /**
     * The BootstrapMethods attribute.
     *
     * @var ByteVector
     */
    public $bootstrapMethods;

    /**
     * The fields of this class. These fields are stored in a linked list of
     * {@link FieldWriter} objects, linked to each other by their
     * {@link FieldWriter#fv} field. This field stores the first element of this
     * list.
     *
     * @var FieldWriter
     */
    public $firstField;

    /**
     * The fields of this class. These fields are stored in a linked list of
     * {@link FieldWriter} objects, linked to each other by their
     * {@link FieldWriter#fv} field. This field stores the last element of this
     * list.
     *
     * @var FieldWriter
     */
    public $lastField;

    /**
     * The methods of this class. These methods are stored in a linked list of
     * {@link MethodWriter} objects, linked to each other by their
     * {@link MethodWriter#mv} field. This field stores the first element of
     * this list.
     *
     * @var MethodWriter
     */
    public $firstMethod;

    /**
     * The methods of this class. These methods are stored in a linked list of
     * {@link MethodWriter} objects, linked to each other by their
     * {@link MethodWriter#mv} field. This field stores the last element of this
     * list.
     *
     * @var MethodWriter
     */
    public $lastMethod;

    /**
     * Indicates what must be automatically computed.
     *
     * @see MethodWriter#compute
     *
     * @var int
     */
    public $compute;

    /**
     * <tt>true</tt> if some methods have wide forward jumps using ASM pseudo
     * instructions, which need to be expanded into sequences of standard
     * bytecode instructions. In this case the class is re-read and re-written
     * with a ClassReader -> ClassWriter chain to perform this transformation.
     *
     * @var bool
     */
    public $hasAsmInsns;

    /**
     * Constructs a new {@link ClassWriter} object.
     *
     * @param int $flags option flags that can be used to modify the default behavior
     *                   of this class. See {@link #COMPUTE_MAXS}, {@link #COMPUTE_FRAMES}.
     */
    public function __construct(int $flags)
    {
        //self::calculate();die;
        parent::__construct(Opcodes::ASM5);
        $this->index = 1;
        $this->pool  = new ByteVector();

        for ($i = 0; $i <= 255; $i++) {
            $this->items[] = new Item();
        }

        $this->threshold = (doubleval(0.75) * count($this->items));

        $this->key  = new Item();
        $this->key2 = new Item();
        $this->key3 = new Item();
        $this->key4 = new Item();

        $this->compute = ( (((($flags & self::$COMPUTE_FRAMES)) != 0))
            ? MethodWriter::$FRAMES : (( (((($flags & self::$COMPUTE_MAXS)) != 0))
                ? MethodWriter::$MAXS : MethodWriter::$NOTHING )) );
    }

    /**
     * Constructs a new {@link ClassWriter} object and enables optimizations for
     * "mostly add" bytecode transformations. These optimizations are the
     * following:
     *
     * <ul>
     * <li>The constant pool from the original class is copied as is in the new
     * class, which saves time. New constant pool entries will be added at the
     * end if necessary, but unused constant pool entries <i>won't be
     * removed</i>.</li>
     * <li>Methods that are not transformed are copied as is in the new class,
     * directly from the original class bytecode (i.e. without emitting visit
     * events for all the method instructions), which saves a <i>lot</i> of
     * time. Untransformed methods are detected by the fact that the
     * {@link ClassReader} receives {@link MethodVisitor} objects that come from
     * a {@link ClassWriter} (and not from any other {@link ClassVisitor}
     * instance).</li>
     * </ul>
     *
     * @param classReader $classReader
     *                    the {@link ClassReader} used to read the original
     *                    class. It will be used to copy the entire constant
     *                    pool from the original class and also to copy other
     *                    fragments of original bytecode where applicable.
     * @param int         $flags
     *                    option flags that can be used to modify the default
     *                    behavior of this class. <i>These option flags do not
     *                    affect methods that are copied as is in the new class.
     *                    This means that neither the maximum stack size nor
     *                    the stack frames will becomputed for these
     *                    methods</i>. See {@link #COMPUTE_MAXS},
     *                    {@link #COMPUTE_FRAMES}.
     *
     * @notYetImplemented
     * @return            ClassWriter
     */
    public static function createFromClass($classReader, int $flags)
    {
        $newInstance = new self($flags);

        $classReader->copyPool($newInstance);
        $newInstance->cr = $classReader;

        return $newInstance;
    }

    /**
     * Visits the header of the class.
     *
     * @param int    $version
     *               the class version.
     * @param int    $access
     *               the class's access flags (see {@link Opcodes}).
     *               This parameter also indicates if the class is deprecated.
     * @param string $name
     *               the internal name of the class (see
     *               {@link Type#getInternalName() getInternalName}).
     * @param string $signature
     *               the signature of this class. May be <tt>null</tt> if
     *               the class is not a generic one, and does not extend
     *               or implement generic classes or interfaces.
     * @param string $superName
     *               the internal of name of the super class (see
     *               {@link Type#getInternalName() getInternalName}). For
     *               interfaces, the super class is {@link Object}. May be
     *               <tt>null</tt>, but only for the {@link Object} class.
     * @param array  $interfaces
     *              the internal names of the class's interfaces (see
     *              {@link Type#getInternalName() getInternalName}). May be
     *              <tt>null</tt>.
     *
     * @return void
     */
    public function visit(
        int $version,
        int $access,
        string $name,
        string $signature = null,
        string $superName = null,
        array $interfaces = null
    ) {
        $this->version = $version;
        $this->access = $access;
        $this->name = $this->newClass($name);
        $this->thisName = $name;

        if ((ClassReader::SIGNATURES && ($signature != null))) {
            $this->signature = $this->newUTF8($signature);
        }

        $this->superName = ( (($superName == null)) ? 0 : $this->newClass($superName) );
        if ((($interfaces != null) && (count($interfaces) > 0))) {
            $this->interfaceCount = count($interfaces);
            $this->interfaces = [];
            for ($i = 0; ($i < $this->interfaceCount); ++$i) {
                $this->interfaces[$i] = $this->newClass($interfaces[$i]);
            }
        }
    }

    /**
     * Visits the source of the class.
     *
     * @param string $source
     *               the name of the source file from which the class was
     *               compiled. May be <tt>null</tt>.
     * @param string $debug
     *               additional debug information to compute the correspondence
     *               between source and compiled elements of the class. May be
     *               <tt>null</tt>.
     *
     * @return void
     */
    public function visitSource(string $source = null, string $debug = null)
    {
        if ($source != null) {
            $this->sourceFile = $this->newUTF8($source);
        }

        if ($debug != null) {
            $bVector = new ByteVector();

            $this->sourceDebug = $bVector->encodeUTF8($debug, 0, PHP_INT_MAX);
        }
    }

    /**
     * Visits the enclosing class of the class. This method must be called only
     * if the class has an enclosing class.
     *
     * @param string $owner
     *               internal name of the enclosing class of the class.
     * @param string $name
     *               the name of the method that contains the class, or
     *               <tt>null</tt> if the class is not enclosed in a method of
     *               its enclosing class.
     * @param string $desc
     *               the descriptor of the method that contains the class, or
     *               <tt>null</tt> if the class is not enclosed in a method of
     *               its enclosing class.
     *
     * @return void
     */
    public function visitOuterClass(
        string $owner,
        string $name = null,
        string $desc = null
    ) {
        $this->enclosingMethodOwner = $this->newClass($owner);

        if (($name != null) && ($desc != null)) {
            $this->enclosingMethod = $this->newNameType($name, $desc);
        }
    }

    /**
     * Visits an annotation of the class.
     *
     * @param string $desc
     *               the class descriptor of the annotation class.
     * @param bool   $visible
     *               <tt>true</tt> if the annotation is visible at runtime.
     *
     * @return AnnotationWriter
     *         a visitor to visit the annotation values, or <tt>null</tt> if
     *         this visitor is not interested in visiting this annotation.
     *
     * @notYetImplemented
     */
    public function visitAnnotation(string $desc, bool $visible)
    {
        if (!ClassReader::ANNOTATIONS) {
            return null;
        }

        $bv = new ByteVector();
        $bv->putShort($this->newUTF8($desc))->putShort(0);
        $aw = new AnnotationWriter($this, true, $bv, $bv, 2);
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
     * Visits an annotation on a type in the class signature.
     *
     * @param int      $typeRef
     *                 a reference to the annotated type. The sort of this type
     *                 reference must be {@link
     *                 TypeReference#CLASS_TYPE_PARAMETER CLASS_TYPE_PARAMETER},
     *                 {@link TypeReference#CLASS_TYPE_PARAMETER_BOUND
     *                 CLASS_TYPE_PARAMETER_BOUND} or
     *                 {@link TypeReference#CLASS_EXTENDS CLASS_EXTENDS}. See
     *                 {@link TypeReference}.
     * @param TypePath $typePath
     *                 the path to the annotated type argument, wildcard bound,
     *                 array element type, or static inner type within
     *                 'typeRef'. May be <tt>null</tt> if the annotation
     *                 targets 'typeRef' as a whole.
     * @param string   $desc
     *                 the class descriptor of the annotation class.
     * @param bool     $visible
     *                 <tt>true</tt> if the annotation is visible at runtime.
     *
     * @return AnnotationWriter
     *         a visitor to visit the annotation values, or <tt>null</tt> if
     *         this visitor is not interested in visiting this annotation.
     *
     * @notYetImplemented
     */
    public function visitTypeAnnotation(
        int $typeRef,
        $typePath,
        string $desc,
        bool $visible
    ) {
        if (!ClassReader::ANNOTATIONS) {
            return null;
        }

        $bv = new ByteVector();
        AnnotationWriter::putTarget($typeRef, $typePath, $bv);
        $bv->putShort($this->newUTF8($desc))->putShort(0);
        $aw = new AnnotationWriter($this, true, $bv, $bv, (count($bv) - 2));
        if ($visible) {
            $aw->next    = $this->tanns;
            $this->tanns = $aw;
        } else {
            $aw->next     = $this->itanns;
            $this->itanns = $aw;
        }

        return $aw;
    }
    /**
     * Visits a non standard attribute of the class.
     *
     * @param Attribute $attr
     *                  an attribute.
     *
     * @return void
     *
     * @notYetImplemented
     */
    public function visitAttribute($attr)
    {
        $attr->next  = $this->attrs;
        $this->attrs = $attr;
    }

    /**
     * Visits information about an inner class. This inner class is not
     * necessarily a member of the class being visited.
     *
     * @param string $name
     *               the internal name of an inner class (see
     *               {@link Type#getInternalName() getInternalName}).
     * @param string $outerName
     *               the internal name of the class to which the inner class
     *               belongs (see {@link Type#getInternalName()
     *               getInternalName}). May be <tt>null</tt> for not member
     *               classes.
     * @param string $innerName
     *               the (simple) name of the inner class inside its enclosing
     *               class. May be <tt>null</tt> for anonymous inner classes.
     * @param int    $access
     *               the access flags of the inner class as originally
     *               declared in the enclosing class.
     *
     * @return void
     */
    public function visitInnerClass(
        string $name,
        string $outerName,
        string $innerName,
        int $access
    ) {
        if (($this->innerClasses == null)) {
            $this->innerClasses = new ByteVector();
        }

        // Sec. 4.7.6 of the JVMS states "Every CONSTANT_Class_info entry in the
        // constant_pool table which represents a class or interface C that is
        // not a package member must have exactly one corresponding entry in the
        // classes array". To avoid duplicates we keep track in the intVal field
        // of the Item of each CONSTANT_Class_info entry C whether an inner
        // class entry has already been added for C (this field is unused for
        // class entries, and changing its value does not change the hashcode
        // and equality tests). If so we store the index of this inner class
        // entry (plus one) in intVal. This hack allows duplicate detection in
        // O(1) time.
        $nameItem = $this->newClassItem($name);
        if (($nameItem->intVal == 0)) {
            ++$this->innerClassesCount;
            $this->innerClasses->putShort($nameItem->index);
            $this->innerClasses->putShort(( (($outerName == null)) ? 0 : $this->newClass($outerName) ));
            $this->innerClasses->putShort(( (($innerName == null)) ? 0 : $this->newUTF8($innerName) ));
            $this->innerClasses->putShort($access);
            $nameItem->intVal = $this->innerClassesCount;
        } else {
            // Compare the inner classes entry nameItem.intVal - 1 with the
            // arguments of this method and throw an exception if there is a
            // difference?
        }
    }

    /**
     * Visits a field of the class.
     *
     * @param int    $access
     *               the field's access flags (see {@link Opcodes}). This
     *               parameter also indicates if the field is synthetic and/or
     *               deprecated.
     * @param string $name
     *               the field's name.
     * @param string $desc
     *               the field's descriptor (see {@link Type Type}).
     * @param string $signature
     *               the field's signature. May be <tt>null</tt> if the field's
     *               type does not use generic types.
     * @param object $value
     *               the field's initial value. This parameter, which may be
     *               <tt>null</tt> if the field does not have an initial value,
     *               must be an {@link Integer}, a {@link Float},
     *               a {@link Long}, a {@link Double} or a {@link String}
     *               (for <tt>int</tt>, <tt>float</tt>, <tt>long</tt> or
     *               <tt>String</tt> fields respectively). <i>This parameter is
     *               only used for static fields</i>. Its value is ignored for
     *               non static fields, which must be initialized through
     *               bytecode instructions in constructors or methods.
     *
     * @return FieldWriter
     *         a visitor to visit field annotations and attributes, or
     *         <tt>null</tt> if this class visitor is not interested in visiting
     *         these annotations and attributes.
     *
     * @throws Exception\IllegalArgumentException
     */
    public function visitField(
        int $access,
        string $name,
        string $desc,
        string $signature = null,
        $value = null
    ) {
        return new FieldWriter($this, $access, $name, $desc, $signature, $value);
    }

    /**
     * Visits a method of the class. This method <i>must</i> return a new
     * {@link MethodVisitor} instance (or <tt>null</tt>) each time it is called,
     * i.e., it should not return a previously returned visitor.
     *
     * @param int    $access
     *               the method's access flags (see {@link Opcodes}). This
     *               parameter also indicates if the method is synthetic and/or
     *               deprecated.
     * @param string $name
     *               the method's name.
     * @param string $desc
     *               the method's descriptor (see {@link Type Type}).
     * @param string $signature
     *               the method's signature. May be <tt>null</tt> if the method
     *               parameters, return type and exceptions do not use generic
     *               types.
     * @param array  $exceptions
     *               the internal names of the method's exception classes (see
     *               {@link Type#getInternalName() getInternalName}). May be
     *               <tt>null</tt>.
     *
     * @return MethodWriter
     *         an object to visit the byte code of the method, or <tt>null</tt>
     *         if this class visitor is not interested in visiting the code of
     *         this method.
     */
    public function visitMethod(
        int $access,
        string $name,
        string $desc,
        string $signature = null,
        array $exceptions = null
    ) {
        return new MethodWriter(
            $this,
            $access,
            $name,
            $desc,
            $signature,
            $exceptions,
            $this->compute
        );
    }

    /**
     * Visits the end of the class. This method, which is the last one to be
     * called, is used to inform the visitor that all the fields and methods of
     * the class have been visited.
     *
     * @return void
     */
    public function visitEnd()
    {
    }

    /**
     * Returns the bytecode of the class that was build with this class writer.
     *
     * @return array
     *         the bytecode of the class that was build with this class writer.
     */
    public function toByteArray()
    {
        if ($this->index > 0xFFFF) {
            throw new \RuntimeException('Class file too large!');
        }

        // Get the basic size
        $size = (24 + (2 * $this->interfaceCount));

        $nbFields = 0;
        $fb = $this->firstField;
        while ($fb != null) {
            ++$nbFields;
            $size += $fb->getSize();
            $fb = $fb->fv;
        }

        $nbMethods = 0;
        $mb = $this->firstMethod;
        while ($mb != null) {
            ++$nbMethods;
            $size += $mb->getSize();
            $mb = $mb->mv;
        }

        $attributeCount = 0;
        if ($this->bootstrapMethods != null) {
            ++$attributeCount;
            $size += (8 + count($this->bootstrapMethods));
            $this->newUTF8('BootstrapMethods');
        }

        if (ClassReader::SIGNATURES && ($this->signature != 0)) {
            ++$attributeCount;
            $size += 8;
            $this->newUTF8('Signature');
        }

        if ($this->sourceFile != 0) {
            ++$attributeCount;
            $size += 8;
            $this->newUTF8('SourceFile');
        }

        if ($this->sourceDebug != null) {
            ++$attributeCount;
            $size += (count($this->sourceDebug) + 6);
            $this->newUTF8('SourceDebugExtension');
        }

        if ($this->enclosingMethodOwner != 0) {
            ++$attributeCount;
            $size += 10;
            $this->newUTF8('EnclosingMethod');
        }

        if (($this->access & Opcodes::ACC_DEPRECATED) != 0) {
            ++$attributeCount;
            $size += 6;
            $this->newUTF8('Deprecated');
        }

        if (($this->access & Opcodes::ACC_SYNTHETIC) != 0) {
            if (((($this->version & 0xFFFF)) < Opcodes::V1_5)
                || ((($this->access & self::$ACC_SYNTHETIC_ATTRIBUTE)) != 0)
            ) {
                ++$attributeCount;
                $size += 6;
                $this->newUTF8('Synthetic');
            }
        }

        if ($this->innerClasses != null) {
            ++$attributeCount;
            $size += (8 + count($this->innerClasses) /*from: innerClasses.length*/);
            $this->newUTF8('InnerClasses');
        }

        if (ClassReader::ANNOTATIONS && ($this->anns != null)) {
            ++$attributeCount;
            $size += (8 + $this->anns->getSize());
            $this->newUTF8('RuntimeVisibleAnnotations');
        }

        if (ClassReader::ANNOTATIONS && ($this->ianns != null)) {
            ++$attributeCount;
            $size += (8 + $this->ianns->getSize());
            $this->newUTF8('RuntimeInvisibleAnnotations');
        }

        if (ClassReader::ANNOTATIONS && ($this->tanns != null)) {
            ++$attributeCount;
            $size += (8 + $this->tanns->getSize());
            $this->newUTF8('RuntimeVisibleTypeAnnotations');
        }

        if (ClassReader::ANNOTATIONS && ($this->itanns != null)) {
            ++$attributeCount;
            $size += (8 + $this->itanns->getSize());
            $this->newUTF8('RuntimeInvisibleTypeAnnotations');
        }

        if ($this->attrs != null) {
            $attributeCount += $this->attrs->getCount();
            $size += $this->attrs->getSize($this, null, 0, -1, -1);
        }

        $size += count($this->pool);

        // Starting building individual bytecode section's
        // There are 10 basic sections to the Java Class File structure.
        $out = new ByteVector();
        // 4 bytes header (in hexadecimal), magic name: CA FE BA BE
        $out->putInt(0xCAFEBABE);
        // Version of Class File Format (4 bytes) - the minor and major
        // versions of the class file.
        $out->putInt($this->version);
        // Constant Pool - Pool of constants for the class.
        $out->putShort($this->index)->putByteArray($this->pool->data, 0, count($this->pool));
        // Access Flags for the class - eg. abstract, static, etc.
        $mask = ((Opcodes::ACC_DEPRECATED | self::$ACC_SYNTHETIC_ATTRIBUTE)
            | ((($this->access & self::$ACC_SYNTHETIC_ATTRIBUTE)) / self::$TO_ACC_SYNTHETIC));
        $out->putShort(($this->access & ~$mask));
        // This Class - the name of the current class.
        $out->putShort($this->name);
        // Super Class - the name of the super class
        $out->putShort($this->superName);
        // Interfaces - number of interfaces + their indexes.
        $out->putShort($this->interfaceCount);
        for ($i = 0; ($i < $this->interfaceCount); ++$i) {
            $out->putShort($this->interfaces[$i]);
        }

        // Fields - number of fields in the class + all fields fields.
        $out->putShort($nbFields);
        $fb = $this->firstField;
        while ($fb != null) {
            $fb->put($out);
            $fb = $fb->fv;
        }

        // Methods - number of all methods in the class + all method definition.
        $out->putShort($nbMethods);
        $mb = $this->firstMethod;
        while ($mb != null) {
            $mb->put($out);
            $mb = $mb->mv;
        }

        // Attributes - number of all attributes + definition all attributes of
        // the class (for example the name of the sourcefile, etc.)
        $out->putShort($attributeCount);
        if ($this->bootstrapMethods != null) {
            $out->putShort($this->newUTF8('BootstrapMethods'));
            $out->putInt((count($this->bootstrapMethods) + 2))->putShort($this->bootstrapMethodsCount);
            $out->putByteArray($this->bootstrapMethods->data, 0, count($this->bootstrapMethods));
        }

        if (ClassReader::SIGNATURES && ($this->signature != 0)) {
            $out->putShort($this->newUTF8('Signature'))->putInt(2)->putShort($this->signature);
        }

        if ($this->sourceFile != 0) {
            $out->putShort($this->newUTF8('SourceFile'))->putInt(2)->putShort($this->sourceFile);
        }

        if ($this->sourceDebug != null) {
            $len = count($this->sourceDebug) /*from: sourceDebug.length*/;
            $out->putShort($this->newUTF8('SourceDebugExtension'))->putInt($len);
            $out->putByteArray($this->sourceDebug->data, 0, $len);
        }

        if ($this->enclosingMethodOwner != 0) {
            $out->putShort($this->newUTF8('EnclosingMethod'))->putInt(4);
            $out->putShort($this->enclosingMethodOwner)->putShort($this->enclosingMethod);
        }

        if (($this->access & Opcodes::ACC_DEPRECATED) != 0) {
            $out->putShort($this->newUTF8('Deprecated'))->putInt(0);
        }

        if (($this->access & Opcodes::ACC_SYNTHETIC) != 0) {
            if (((($this->version & 0xFFFF)) < Opcodes::V1_5)
                || (($this->access & self::$ACC_SYNTHETIC_ATTRIBUTE) != 0)
            ) {
                $out->putShort($this->newUTF8('Synthetic'))->putInt(0);
            }
        }

        if ($this->innerClasses != null) {
            $out->putShort($this->newUTF8('InnerClasses'));
            $out->putInt((count($this->innerClasses) + 2))->putShort($this->innerClassesCount);
            $out->putByteArray($this->innerClasses->data, 0, count($this->innerClasses));
        }

        if (ClassReader::ANNOTATIONS && ($this->anns != null)) {
            $out->putShort($this->newUTF8('RuntimeVisibleAnnotations'));
            $this->anns->put($out);
        }

        if (ClassReader::ANNOTATIONS && ($this->ianns != null)) {
            $out->putShort($this->newUTF8('RuntimeInvisibleAnnotations'));
            $this->ianns->put($out);
        }

        if (ClassReader::ANNOTATIONS && ($this->tanns != null)) {
            $out->putShort($this->newUTF8('RuntimeVisibleTypeAnnotations'));
            $this->tanns->put($out);
        }

        if (ClassReader::ANNOTATIONS && ($this->itanns != null)) {
            $out->putShort($this->newUTF8('RuntimeInvisibleTypeAnnotations'));
            $this->itanns->put($out);
        }

        if ($this->attrs != null) {
            $this->attrs->put($this, null, 0, -1, -1, $out);
        }

        if ($this->hasAsmInsns) {
            $this->anns = null;
            $this->ianns = null;
            $this->attrs = null;
            $this->innerClassesCount = 0;
            $this->innerClasses = null;
            $this->firstField = null;
            $this->lastField = null;
            $this->firstMethod = null;
            $this->lastMethod = null;
            $this->compute = MethodWriter::$INSERTED_FRAMES;
            $this->hasAsmInsns =  false ;
            (new ClassReader($out->data))->accept($this, (ClassReader::EXPAND_FRAMES | ClassReader::EXPAND_ASM_INSNS));

            return $this->toByteArray();
        }

        return $out->data;
    }

    /**
     * Adds a number or string constant to the constant pool of the class being
     * build. Does nothing if the constant pool already contains a similar item.
     * Type of the constant is auto detected according if it's provided as a
     * simple php type. But this is not recommended.
     *
     * @param mixed $cst
     *              the value of the constant to be added to the constant pool.
     *              This parameter can be an {@link Integer}, a {@link Float},
     *              a {@link Long}, a {@link Double}, a {@link String} a
     *              {@link Type} or a plain PHP type, in which case the type
     *              will be auto detected (not recommended).
     *
     * @return Item
     *         a new or already existing constant item with the given value.
     *
     * @throws IllegalArgumentException Thrown if the unsupported type is provided.
     */
    public function newConstItem($cst) : Item
    {
        // If the type is not specified by it's class use naive auto detection
        if (!is_object($cst)) {
            return $this->typeAutoDetection($cst);
        }

        switch (true) {
            case $cst instanceof Type\Long:
                $val = (float)$cst->getValue();
                return $this->newLong($val);
            case $cst instanceof Type\Float_:
                $val = (float)$cst->getValue();
                return $this->newFloat($val);
            case $cst instanceof Type\Integer:
                $val = (int) $cst->getValue();
                return $this->newInteger($val);
            case $cst instanceof Type\Character:
                $val = ord($cst->getValue()[0]);
                return $this->newInteger($val);
            case $cst instanceof Type\Short:
                $val = (int) $cst->getValue();
                return $this->newInteger($val);
            case $cst instanceof Type\Boolean:
                $val = $cst->getValue() === true ? 1 : 0;
                return $this->newInteger($val);
            case $cst instanceof Type\Double:
                $val = (float)$cst->getValue();
                return $this->newDouble($val);
            case $cst instanceof Type\String_:
                return $this->newString($cst->getValue());
            case $cst instanceof Type:
                $t = $cst;
                $s = $t->getSort();
                if (($s == Type::OBJECT)) {
                    return $this->newClassItem($t->getInternalName());
                } elseif (($s == Type::METHOD)) {
                    return $this->newMethodTypeItem($t->getDescriptor());
                } else {
                    return $this->newClassItem($t->getDescriptor());
                }
            case $cst instanceof Handle:
                $h = $cst;
                return $this->newHandleItem($h->tag, $h->owner, $h->name, $h->desc, $h->itf);
            default:
                throw new IllegalArgumentException('value ' . $cst);
        }
    }

    /**
     * Adds a number or string constant to the constant pool of the class being
     * build. Does nothing if the constant pool already contains a similar item.
     * <i>This method is intended for {@link Attribute} sub classes, and is
     * normally not needed by class generators or adapters.</i>
     *
     * @param mixed $cst
     *              the value of the constant to be added to the constant pool.
     *              This parameter can be an {@link Integer}, a {@link Float},
     *              a {@link Long}, a {@link Double}, a {@link String} a
     *              {@link Type} or a plain PHP type, in which case the type
     *              will be auto detected (not recommended).
     *
     * @return int the index of a new or already existing constant item with the
     *             given value.
     *
     * @throws IllegalArgumentException Thrown if the unsupported type is provided.
     */
    public function newConst($cst) : int
    {
        return $this->newConstItem($cst)->index;
    }

    /**
     * Adds an UTF8 string to the constant pool of the class being build. Does
     * nothing if the constant pool already contains a similar item. <i>This
     * method is intended for {@link Attribute} sub classes, and is normally not
     * needed by class generators or adapters.</i>
     *
     * @param string $value
     *               the String value.
     *
     * @return int the index of a new or already existing UTF8 item.
     */
    public function newUTF8(string $value) : int
    {
        $this->key->setComplex(self::$UTF8, $value, null, null);
        $result = $this->get($this->key);
        if ($result == null) {
            $this->pool->putByte(self::$UTF8)->putUTF8($value);
            $result = new Item(/*++$this->index*/$this->index++, $this->key);
            $this->put($result);
        }

        return $result->index;
    }

    /**
     * Adds a class reference to the constant pool of the class being build.
     * Does nothing if the constant pool already contains a similar item.
     * <i>This method is intended for {@link Attribute} sub classes, and is
     * normally not needed by class generators or adapters.</i>
     *
     * @param string $value the internal name of the class.
     *
     * @return Item a new or already existing class reference item.
     */
    public function newClassItem(string $value) : Item
    {
        $this->key2->setComplex(self::$CLASS, $value, null, null);
        $result = $this->get($this->key2);

        if ($result == null) {
            $this->pool->put12(self::$CLASS, $this->newUTF8($value));
            $result = new Item($this->index++, $this->key2);
            $this->put($result);
        }

        return $result;
    }

    /**
     * Adds a class reference to the constant pool of the class being build.
     * Does nothing if the constant pool already contains a similar item.
     * <i>This method is intended for {@link Attribute} sub classes, and is
     * normally not needed by class generators or adapters.</i>
     *
     * @param string $value the internal name of the class.
     *
     * @return int the index of a new or already existing class reference item.
     */
    public function newClass(string $value) : int
    {
        return $this->newClassItem($value)->index;
    }

    protected function newMethodTypeItem($methodDesc) // [final String methodDesc]
    {
        $this->key2->setComplex(self::$MTYPE, $methodDesc, null, null);
        $result = $this->get($this->key2);
        if ($result == null) {
            $this->pool->put12(self::$MTYPE, $this->newUTF8($methodDesc));
            $result = new Item(/*++$this->index*/$this->index++, $this->key2);
            $this->put($result);
        }

        return $result;
    }

    public function newMethodType($methodDesc) // [final String methodDesc]
    {
        return $this->newMethodTypeItem($methodDesc)->index;
    }

    /**
     * Adds a handle to the constant pool of the class being build. Does nothing
     * if the constant pool already contains a similar item. <i>This method is
     * intended for {@link Attribute} sub classes, and is normally not needed by
     * class generators or adapters.</i>
     *
     * @param int    $tag
     *               the kind of this handle. Must be {@link Opcodes#H_GETFIELD},
     *               {@link Opcodes#H_GETSTATIC}, {@link Opcodes#H_PUTFIELD},
     *               {@link Opcodes#H_PUTSTATIC}, {@link Opcodes#H_INVOKEVIRTUAL},
     *               {@link Opcodes#H_INVOKESTATIC},
     *               {@link Opcodes#H_INVOKESPECIAL},
     *               {@link Opcodes#H_NEWINVOKESPECIAL} or
     *               {@link Opcodes#H_INVOKEINTERFACE}.
     * @param string $owner
     *               the internal name of the field or method owner class.
     * @param string $name
     *               the name of the field or method.
     * @param string $desc
     *               the descriptor of the field or method.
     * @param bool   $itf
     *               true if the owner is an interface.
     *
     * @return Item a new or an already existing method type reference item.
     */
    public function newHandleItem(int $tag, string $owner, string $name, string $desc, bool $itf) : Item
    {
        $this->key4->setComplex((self::$HANDLE_BASE + $tag), $owner, $name, $desc);
        $result = $this->get($this->key4);
        if ($result == null) {
            if (($tag <= Opcodes::H_PUTSTATIC)) {
                $this->put112(self::$HANDLE, $tag, $this->newField($owner, $name, $desc));
            } else {
                $this->put112(self::$HANDLE, $tag, $this->newMethod($owner, $name, $desc, $itf));
            }

            $result = new Item(/*++$this->index*/$this->index++, $this->key4);
            $this->put($result);
        }

        return $result;
    }

    /**
     * Adds a handle to the constant pool of the class being build. Does nothing
     * if the constant pool already contains a similar item. <i>This method is
     * intended for {@link Attribute} sub classes, and is normally not needed by
     * class generators or adapters.</i>
     *
     * @param int    $tag
     *               the kind of this handle. Must be {@link Opcodes#H_GETFIELD},
     *               {@link Opcodes#H_GETSTATIC}, {@link Opcodes#H_PUTFIELD},
     *               {@link Opcodes#H_PUTSTATIC}, {@link Opcodes#H_INVOKEVIRTUAL},
     *               {@link Opcodes#H_INVOKESTATIC},
     *               {@link Opcodes#H_INVOKESPECIAL},
     *               {@link Opcodes#H_NEWINVOKESPECIAL} or
     *               {@link Opcodes#H_INVOKEINTERFACE}.
     * @param string $owner
     *               the internal name of the field or method owner class.
     * @param string $name
     *               the name of the field or method.
     * @param string $desc
     *               the descriptor of the field or method.
     * @param bool   $itf
     *               true if the owner is an interface.
     *
     * @return int the index of a new or already existing method type reference item.
     */
    public function newHandle(
        int $tag,
        string $owner,
        string $name,
        string $desc,
        bool $itf = null
    ) : int {
        if ($itf == null) {
            $itf = ($tag == Opcodes::H_INVOKEINTERFACE);
        }

        return $this->newHandleItem($tag, $owner, $name, $desc, $itf)->index;
    }

    public function newFieldItem($owner, $name, $desc) // [final String owner, final String name, final String desc]
    {
        $this->key3->setComplex(self::$FIELD, $owner, $name, $desc);
        $result = $this->get($this->key3);
        if ($result == null) {
            $this->put122(self::$FIELD, $this->newClass($owner), $this->newNameType($name, $desc));
            $result = new Item(/*++$this->index*/$this->index++, $this->key3);
            $this->put($result);
        }

        return $result;
    }

    public function newField($owner, $name, $desc) // [final String owner, final String name, final String desc]
    {
        return $this->newFieldItem($owner, $name, $desc)->index;
    }

    /**
     * Adds a method reference to the constant pool of the class being build.
     * Does nothing if the constant pool already contains a similar item.
     *
     * @param string $owner
     *            the internal name of the method's owner class.
     * @param string $name
     *            the method's name.
     * @param string $desc
     *            the method's descriptor.
     * @param bool   $itf
     *            <tt>true</tt> if <tt>owner</tt> is an interface.
     *
     * @return Item a new or already existing method reference item.
     */
    public function newMethodItem(string $owner, string $name, string $desc, bool $itf) : Item
    {
        $type = ( ($itf) ? self::$IMETH : self::$METH );
        $this->key3->setComplex($type, $owner, $name, $desc);
        $result = $this->get($this->key3);
        if ($result == null) {
            $this->put122($type, $this->newClass($owner), $this->newNameType($name, $desc));
            $result = new Item(/*++$this->index*/$this->index++, $this->key3);
            $this->put($result);
        }

        return $result;
    }

    /**
     * Adds a method reference to the constant pool of the class being build.
     * Does nothing if the constant pool already contains a similar item.
     * <i>This method is intended for {@link Attribute} sub classes, and is
     * normally not needed by class generators or adapters.</i>
     *
     * @param string $owner
     *            the internal name of the method's owner class.
     * @param string $name
     *            the method's name.
     * @param string $desc
     *            the method's descriptor.
     * @param bool   $itf
     *            <tt>true</tt> if <tt>owner</tt> is an interface.
     *
     * @return int the index of a new or already existing method reference item.
     */
    public function newMethod(string $owner, string $name, string $desc, bool $itf) : int
    {
        return $this->newMethodItem($owner, $name, $desc, $itf)->index;
    }

    protected function newInteger($value) // [final int value]
    {
        $this->key->setInteger($value);
        $result = $this->get($this->key);
        if ($result == null) {
            $this->pool->putByte(self::$INT)->putInt($value);
            $result = new Item(/*++$this->index*/$this->index++, $this->key);
            $this->put($result);
        }

        return $result;
    }

    protected function newFloat($value) // [final float value]
    {
        $this->key->setFloat($value);
        $result = $this->get($this->key);
        if ($result == null) {
            $this->pool->putByte(self::$FLOAT)->putInt($this->key->intVal);
            $result = new Item(/*++$this->index*/$this->index++, $this->key);
            $this->put($result);
        }

        return $result;
    }

    protected function newLong($value) // [final long value]
    {
        $this->key->setLong($value);
        $result = $this->get($this->key);
        if ($result == null) {
            $this->pool->putByte(self::$LONG)->putLong($value);
            $result = new Item($this->index, $this->key);
            $this->index += 2;
            $this->put($result);
        }

        return $result;
    }

    protected function newDouble($value) // [final double value]
    {
        $this->key->setDouble($value);
        $result = $this->get($this->key);
        if ($result == null) {
            $this->pool->putByte(self::$DOUBLE)->putLong($this->key->longVal);
            $result = new Item($this->index, $this->key);
            $this->index += 2;
            $this->put($result);
        }

        return $result;
    }

    protected function newString($value) // [final String value]
    {
        $this->key2->setComplex(self::$STR, $value, null, null);
        $result = $this->get($this->key2);
        if ($result == null) {
            $this->pool->put12(self::$STR, $this->newUTF8($value));
            $result = new Item(/*++$this->index*/$this->index++, $this->key2);
            $this->put($result);
        }

        return $result;
    }

    /**
     * Adds a name and type to the constant pool of the class being build. Does
     * nothing if the constant pool already contains a similar item. <i>This
     * method is intended for {@link Attribute} sub classes, and is normally not
     * needed by class generators or adapters.</i>
     *
     * @param string $name a name.
     * @param string $desc a type descriptor.
     *
     * @return int the index of a new or already existing name and type item.
     */
    public function newNameType(string $name, string $desc) : int
    {
        return $this->newNameTypeItem($name, $desc)->index;
    }

    /**
     * Adds a name and type to the constant pool of the class being build. Does
     * nothing if the constant pool already contains a similar item.
     *
     * @param string $name a name.
     * @param string $desc a type descriptor.
     *
     * @return Item a new or already existing name and type item.
     */
    protected function newNameTypeItem(string $name, string $desc) : Item
    {
        $this->key2->setComplex(self::$NAME_TYPE, $name, $desc, null);
        $result = $this->get($this->key2);
        if ($result == null) {
            $this->put122(self::$NAME_TYPE, $this->newUTF8($name), $this->newUTF8($desc));
            $result = new Item(/*++$this->index*/$this->index++, $this->key2);
            $this->put($result);
        }

        return $result;
    }

    public function addUninitializedType($type, $offset) // [final String type, final int offset]
    {
        $this->key->type = self::$TYPE_UNINIT;
        $this->key->intVal = $offset;
        $this->key->strVal1 = $type;
        $this->key->hashCode = (0x7FFFFFFF & (((self::$TYPE_UNINIT + $type->hashCode()) + $offset)));
        $result = $this->get($this->key);
        if ($result == null) {
            $result = $this->addTypeItem();
        }

        return $result->index;
    }

    /**
     * Adds the given internal name to {@link #typeTable} and returns its index.
     * Does nothing if the type table already contains this internal name.
     *
     * @param string $type
     *               the internal name to be added to the type table.
     *
     * @return int the index of this internal name in the type table.
     */
    public function addType(string $type) : int
    {
        $this->key->setComplex(self::$TYPE_NORMAL, $type, null, null);
        $result = $this->get($this->key);
        if ($result == null) {
            $result = $this->addTypeItem();
        }

        return $result->index;
    }

    /**
     * Adds the given Item to {@link #typeTable}.
     *
     * @return Item the added Item, which a new Item instance with the same value as
     *         the given Item.
     */
    public function addTypeItem()
    {
        ++$this->typeCount;
        $result = new Item($this->typeCount, $this->key);
        $this->put($result);
        if ($this->typeTable == null) {
            $this->typeTable = [];
        }

        if ($this->typeCount == count($this->typeTable)) {
            $newTable = [];
            foreach (range(0, (count($this->typeTable) /*from: typeTable.length*/ + 0)) as $_upto) {
                $newTable[$_upto] = $this->typeTable[$_upto - (0) + 0];
            }

            $this->typeTable = $newTable;
        }

        $this->typeTable[$this->typeCount] = $result;

        return $result;
    }

    /**
     * Returns the index of the common super type of the two given types. This
     * method calls {@link #getCommonSuperClass} and caches the result in the
     * {@link #items} hash table to speedup future calls with the same
     * parameters.
     *
     * @param int $type1 index of an internal name in {@link #typeTable}.
     * @param int $type2 index of an internal name in {@link #typeTable}.
     *
     * @return int the index of the common super type of the two given types.
     *
     * @throws NotImplementedException Original code depends on Java class loader
     *                                 and therefor cannot be easily rewritten.
     */
    public function getMergedType(int $type1, int $type2) : int
    {
        $this->key2->type = self::$TYPE_MERGED;
        $this->key2->longVal = ($type1 | ((($type2) << 32)));
        $this->key2->hashCode = (0x7FFFFFFF & (((self::$TYPE_MERGED + $type1) + $type2)));
        $result = $this->get($this->key2);
        if ($result == null) {
            $t = $this->typeTable[$type1]->strVal1;
            $u = $this->typeTable[$type2]->strVal1;
            $this->key2->intVal = $this->addType($this->getCommonSuperClass($t, $u));
            $result = new Item(0, $this->key2);
            $this->put($result);
        }

        return $result->intVal;
    }

    /**
     * Returns the common super type of the two given types. The default
     * implementation of this method <i>loads</i> the two given classes and uses
     * the java.lang.Class methods to find the common super class. It can be
     * overridden to compute this common super type in other ways, in particular
     * without actually loading any class, or to take into account the class
     * that is currently being generated by this ClassWriter, which can of
     * course not be loaded since it is under construction.
     *
     * @param string $type1 the internal name of a class.
     * @param string $type2 the internal name of another class.
     *
     * @return string the internal name of the common super class of the two given
     *         classes.
     *
     * @throws NotImplementedException Original code depends on Java class loader
     *                                 and therefor cannot be easily rewritten.
     */
    protected function getCommonSuperClass(string $type1, string $type2)
    {
        throw new NotImplementedException();
    }

    /**
     * Returns the constant pool's hash table item which is equal to the given
     * item.
     *
     * @param Item $key a constant pool item.
     *
     * @return Item the constant pool's hash table item which is equal to the given
     *         item, or <tt>null</tt> if there is no such item.
     */
    protected function get(Item $key) : ?Item
    {
        $i = $this->items[($key->hashCode % count($this->items) /*from: items.length*/)];
        while ((($i != null) && ((($i->type != $key->type) || !$key->isEqualTo($i))))) {
            $i = $i->next;
        }

        return $i;
    }

    /**
     * Puts the given item in the constant pool's hash table. The hash table
     * <i>must</i> not already contains this item.
     *
     * @param Item $i
     *             the item to be added to the constant pool's hash table.
     *
     * @return void
     */
    protected function put($i) // [final Item i]
    {
        if ((($this->index + $this->typeCount) > $this->threshold)) {
            $ll = count($this->items);
            $nl = (($ll * 2) + 1);
            $newItems = [];
            for ($l = ($ll - 1); ($l >= 0); --$l) {
                $j = $this->items[$l];
                while (($j != null)) {
                    $index = ($j->hashCode % count($newItems) /*from: newItems.length*/);
                    $k = $j->next;
                    $j->next = $newItems[$index];
                    $newItems[$index] = $j;
                    $j = $k;
                }
            }

            $this->items = $newItems;
            $this->threshold = (($nl * doubleval(0.75)));
        }

        $index = ($i->hashCode % count($this->items));
        $i->next = $this->items[$index];
        $this->items[$index] = $i;
    }

    /**
     * Puts one byte and two shorts into the constant pool.
     *
     * @param int $b
     *            a byte.
     * @param int $s1
     *            a short.
     * @param int $s2
     *            another short.
     *
     * @return void
     */
    protected function put122($b, $s1, $s2) // [final int b, final int s1, final int s2]
    {
        $this->pool->put12($b, $s1)->putShort($s2);
    }

    /**
     * Puts two bytes and one short into the constant pool.
     *
     * @param int $b1
     *            a byte.
     * @param int $b2
     *            another byte.
     * @param int $s
     *            a short.
     *
     * @return void
     */
    protected function put112($b1, $b2, $s) // [final int b1, final int b2, final int s]
    {
        $this->pool->put11($b1, $b2)->putShort($s);
    }

    /**
     * Automatically detect java variable type according their PHP value and
     * Adds into the constant pool of the class being build. Does nothing if
     * the constant pool already contains a similar item.
     *
     * @param mixed $cst the value of the constant to be added to the constant pool.
     *
     * @return Item
     *         a new or already existing constant item with the given value.
     *
     * @throws IllegalArgumentException Thrown if the unsupported type is provided.
     */
    private function typeAutoDetection($cst)
    {
        switch (gettype($cst)) {
            case 'boolean':
                $val = $cst === true ? 1 : 0;
                return $this->newInteger($val);
            case 'integer':
                return $this->newInteger($cst);
            case 'double': // for historical reasons "double" is returned in case of a float, and not simply "float"
                return $this->newDouble($cst);
            case 'string':
                return $this->newString($cst);
            default:
                throw new IllegalArgumentException('value ' . $cst);
        }
    }
}
