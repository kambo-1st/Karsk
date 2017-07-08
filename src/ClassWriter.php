<?php

namespace Kambo\Asm;

use Kambo\Asm\ClassVisitor;

class ClassWriter extends ClassVisitor
{
public static $COMPUTE_MAXS;    // int
public static $COMPUTE_FRAMES;  // int
public static $ACC_SYNTHETIC_ATTRIBUTE;  // int
public static $TO_ACC_SYNTHETIC; // int
public static $NOARG_INSN;   // int
public static $SBYTE_INSN;   // int
public static $SHORT_INSN;   // int
public static $VAR_INSN; // int
public static $IMPLVAR_INSN; // int
public static $TYPE_INSN;    // int
public static $FIELDORMETH_INSN; // int
public static $ITFMETH_INSN; // int
public static $INDYMETH_INSN;    // int
public static $LABEL_INSN;   // int
public static $LABELW_INSN;  // int
public static $LDC_INSN; // int
public static $LDCW_INSN;    // int
public static $IINC_INSN;    // int
public static $TABL_INSN;    // int
public static $LOOK_INSN;    // int
public static $MANA_INSN;    // int
public static $WIDE_INSN;    // int
public static $ASM_LABEL_INSN;   // int
public static $F_INSERT; // int
public static $TYPE; // byte[]
public static $CLASS;    // int
public static $FIELD;    // int
public static $METH; // int
public static $IMETH;    // int
public static $STR;  // int
public static $INT;  // int
public static $FLOAT;    // int
public static $LONG; // int
public static $DOUBLE;   // int
public static $NAME_TYPE;    // int
public static $UTF8; // int
public static $MTYPE;    // int
public static $HANDLE;   // int
public static $INDY; // int
public static $HANDLE_BASE;  // int
public static $TYPE_NORMAL;  // int
public static $TYPE_UNINIT;  // int
public static $TYPE_MERGED;  // int
public static $BSM;  // int
public $cr;  // ClassReader
public $version; // int
public $index;   // int
public $pool;    // ByteVector
public $items;   // Item[]
public $threshold;   // int
public $key; // Item
public $key2;    // Item
public $key3;    // Item
public $key4;    // Item
public $typeTable;   // Item[]
public $typeCount;   // short
public $access;  // int
public $name;    // int
public $thisName;    // String
public $signature;   // int
public $superName;   // int
public $interfaceCount;  // int
public $interfaces;  // int[]
public $sourceFile;  // int
public $sourceDebug; // ByteVector
public $enclosingMethodOwner;    // int
public $enclosingMethod; // int
public $anns;    // AnnotationWriter
public $ianns;   // AnnotationWriter
public $tanns;   // AnnotationWriter
public $itanns;  // AnnotationWriter
public $attrs;   // Attribute
public $innerClassesCount;   // int
public $innerClasses;    // ByteVector
public $bootstrapMethodsCount;   // int
public $bootstrapMethods;    // ByteVector
public $firstField;  // FieldWriter
public $lastField;   // FieldWriter
public $firstMethod; // MethodWriter
public $lastMethod;  // MethodWriter
public $compute; // int
public $hasAsmInsns; // boolean

public static function __staticinit()
{
 // static class members
    self::$COMPUTE_MAXS = 1;
    self::$COMPUTE_FRAMES = 2;
    self::$ACC_SYNTHETIC_ATTRIBUTE = 0x40000;
    self::$TO_ACC_SYNTHETIC = (self::$ACC_SYNTHETIC_ATTRIBUTE / Opcodes::ACC_SYNTHETIC);
    self::$NOARG_INSN = 0;
    self::$SBYTE_INSN = 1;
    self::$SHORT_INSN = 2;
    self::$VAR_INSN = 3;
    self::$IMPLVAR_INSN = 4;
    self::$TYPE_INSN = 5;
    self::$FIELDORMETH_INSN = 6;
    self::$ITFMETH_INSN = 7;
    self::$INDYMETH_INSN = 8;
    self::$LABEL_INSN = 9;
    self::$LABELW_INSN = 10;
    self::$LDC_INSN = 11;
    self::$LDCW_INSN = 12;
    self::$IINC_INSN = 13;
    self::$TABL_INSN = 14;
    self::$LOOK_INSN = 15;
    self::$MANA_INSN = 16;
    self::$WIDE_INSN = 17;
    self::$ASM_LABEL_INSN = 18;
    self::$F_INSERT = 256;
    self::$CLASS = 7;
    self::$FIELD = 9;
    self::$METH = 10;
    self::$IMETH = 11;
    self::$STR = 8;
    self::$INT = 3;
    self::$FLOAT = 4;
    self::$LONG = 5;
    self::$DOUBLE = 6;
    self::$NAME_TYPE = 12;
    self::$UTF8 = 1;
    self::$MTYPE = 16;
    self::$HANDLE = 15;
    self::$INDY = 18;
    self::$HANDLE_BASE = 20;
    self::$TYPE_NORMAL = 30;
    self::$TYPE_UNINIT = 31;
    self::$TYPE_MERGED = 32;
    self::$BSM = 33;
}
public static function constructor__I($flags) // [final int flags]
{
    $me = new self();
    parent::constructor__I(Opcodes::ASM5);
    $me->index = 1;
    $me->pool = new ByteVector();

    for ($i = 0; $i <= 255; $i++){
        $me->items[] = new Item();
    }

    $me->threshold = ((doubleval(0.75) * count($me->items) /*from: items.length*/));
    $me->key = new Item();
    $me->key2 = new Item();
    $me->key3 = new Item();
    $me->key4 = new Item();
    $me->compute = ( (((($flags & self::$COMPUTE_FRAMES)) != 0)) ? MethodWriter::$FRAMES : (( (((($flags & self::$COMPUTE_MAXS)) != 0)) ? MethodWriter::$MAXS : MethodWriter::$NOTHING )) );
    return $me;
}
public static function constructor__ClassReader_I($classReader, $flags) // [final ClassReader classReader, final int flags]
{
    $me = new self();
    self::constructor__I($flags);
    $classReader->copyPool($me);
    $me->cr = $classReader;
    return $me;
}

public function visit(int $version, int $access, string $name, $signature, string $superName, $interfaces=null) // [final int version, final int access, final String name, final String signature, final String superName, final String[] interfaces]
{
    $this->version = $version;
    $this->access = $access;
    $this->name = $this->newClass($name);
    $this->thisName = $name;

    if ((ClassReader::SIGNATURES && ($signature != null))) {
        $this->signature = $this->newUTF8($signature);
    }

    $this->superName = ( (($superName == null)) ? 0 : $this->newClass($superName) );
    if ((($interfaces != null) && (count($interfaces) /*from: interfaces.length*/ > 0))) {
        $this->interfaceCount = count($interfaces) /*from: interfaces.length*/;
        $this->interfaces = array();
        for ($i = 0; ($i < $this->interfaceCount); ++$i) {
            $this->interfaces[$i] = $this->newClass($interfaces[$i]);
        }
    }
}

public function visitSource(string $source, string $debug) // [final String file, final String debug]
{
    if ($file != null) {
        $this->sourceFile = $this->newUTF8($file);
    }

    if ($debug != null) {
        $this->sourceDebug = (new ByteVector())->encodeUTF8($debug, 0, $Integer->MAX_VALUE); //TODO
    }
}

public function visitOuterClass(string $owner, string $name, string $desc) // [final String owner, final String name, final String desc]
{
    $this->enclosingMethodOwner = $this->newClass($owner);

    if ((($name != null) && ($desc != null))) {
        $this->enclosingMethod = $this->newNameType($name, $desc);
    }
}

public function visitAnnotation(string $desc, string $visible) // [final String desc, final boolean visible]
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

public function visitTypeAnnotation(int $typeRef, $typePath, string $desc, bool $visible) // [int typeRef, TypePath typePath, final String desc, final boolean visible]
{
    if (!ClassReader::ANNOTATIONS) {
        return null;
    }
    $bv = new ByteVector();
    $AnnotationWriter->putTarget($typeRef, $typePath, $bv);
    $bv->putShort($this->newUTF8($desc))->putShort(0);
    $aw = new AnnotationWriter($this, true, $bv, $bv, (count($bv) /*from: bv.length*/ - 2));
    if ($visible) {
        $aw->next = $this->tanns;
        $this->tanns = $aw;
    } else {
        $aw->next = $this->itanns;
        $this->itanns = $aw;
    }
    return $aw;
}
public function visitAttribute($attr) // [final Attribute attr]
{
    $attr->next = $this->attrs;
    $this->attrs = $attr;
}
public function visitInnerClass(string $name, string $outerName, string $innerName, int $access) // [final String name, final String outerName, final String innerName, final int access]
{
    if (($this->innerClasses == null)) {
        $this->innerClasses = new ByteVector();
    }
    $nameItem = $this->newClassItem($name);
    if (($nameItem->intVal == 0)) {
        ++$this->innerClassesCount;
        $this->innerClasses->putShort($nameItem->index);
        $this->innerClasses->putShort(( (($outerName == null)) ? 0 : $this->newClass($outerName) ));
        $this->innerClasses->putShort(( (($innerName == null)) ? 0 : $this->newUTF8($innerName) ));
        $this->innerClasses->putShort($access);
        $nameItem->intVal = $this->innerClassesCount;
    } else {
    }
}
    public function visitField($access, $name, $desc, $signature, $value) // [final int access, final String name, final String desc, final String signature, final Object value]
    {
        return new FieldWriter($this, $access, $name, $desc, $signature, $value);
    }
    public function visitMethod($access, $name, $desc, $signature, $exceptions) // [final int access, final String name, final String desc, final String signature, final String[] exceptions]
    {
        return MethodWriter::constructor__ClassWriter_I_String_String_String_aString_I($this, $access, $name, $desc, $signature, $exceptions, $this->compute);
    }
    function visitEnd()
    {
        
    }
    public function toByteArray()
    {
        if (($this->index > 0xFFFF)) {
            throw new RuntimeException("Class file too large!");
        }
        // Get the basic size
        $size = (24 + (2 * $this->interfaceCount));

        $nbFields = 0;
        $fb = $this->firstField;
        while (($fb != null)) {
            ++$nbFields;
            $size += $fb->getSize();
            $fb = $fb->fv;
        }

        $nbMethods = 0;
        $mb = $this->firstMethod;
        while (($mb != null)) {
            ++$nbMethods;
            $size += $mb->getSize();
            $mb = $mb->mv;
        }

        $attributeCount = 0;
        if (($this->bootstrapMethods != null)) {
            ++$attributeCount;
            $size += (8 + count($this->bootstrapMethods) /*from: bootstrapMethods.length*/);
            $this->newUTF8("BootstrapMethods");
        }
        if ((ClassReader::SIGNATURES && ($this->signature != 0))) {
            ++$attributeCount;
            $size += 8;
            $this->newUTF8("Signature");
        }
        if (($this->sourceFile != 0)) {
            ++$attributeCount;
            $size += 8;
            $this->newUTF8("SourceFile");
        }
        if (($this->sourceDebug != null)) {
            ++$attributeCount;
            $size += (count($this->sourceDebug) /*from: sourceDebug.length*/ + 6);
            $this->newUTF8("SourceDebugExtension");
        }
        if (($this->enclosingMethodOwner != 0)) {
            ++$attributeCount;
            $size += 10;
            $this->newUTF8("EnclosingMethod");
        }
        if (((($this->access & Opcodes::ACC_DEPRECATED)) != 0)) {
            ++$attributeCount;
            $size += 6;
            $this->newUTF8("Deprecated");
        }
        if (((($this->access & Opcodes::ACC_SYNTHETIC)) != 0)) {
            if ((((($this->version & 0xFFFF)) < Opcodes::V1_5) || ((($this->access & self::$ACC_SYNTHETIC_ATTRIBUTE)) != 0))) {
                ++$attributeCount;
                $size += 6;
                $this->newUTF8("Synthetic");
            }
        }
        if (($this->innerClasses != null)) {
            ++$attributeCount;
            $size += (8 + count($this->innerClasses) /*from: innerClasses.length*/);
            $this->newUTF8("InnerClasses");
        }
        if ((ClassReader::ANNOTATIONS && ($this->anns != null))) {
            ++$attributeCount;
            $size += (8 + $this->anns->getSize());
            $this->newUTF8("RuntimeVisibleAnnotations");
        }
        if ((ClassReader::ANNOTATIONS && ($this->ianns != null))) {
            ++$attributeCount;
            $size += (8 + $this->ianns->getSize());
            $this->newUTF8("RuntimeInvisibleAnnotations");
        }
        if ((ClassReader::ANNOTATIONS && ($this->tanns != null))) {
            ++$attributeCount;
            $size += (8 + $this->tanns->getSize());
            $this->newUTF8("RuntimeVisibleTypeAnnotations");
        }
        if ((ClassReader::ANNOTATIONS && ($this->itanns != null))) {
            ++$attributeCount;
            $size += (8 + $this->itanns->getSize());
            $this->newUTF8("RuntimeInvisibleTypeAnnotations");
        }
        if (($this->attrs != null)) {
            $attributeCount += $this->attrs->getCount();
            $size += $this->attrs->getSize($this, null, 0, -1, -1);
        }

        $size += count($this->pool);

        // Starting building header
        $out = new ByteVector($size);
        $out->putInt(0xCAFEBABE)->putInt($this->version);
        $out->putShort($this->index)->putByteArray($this->pool->data, 0, count($this->pool));
        $mask = ((Opcodes::ACC_DEPRECATED | self::$ACC_SYNTHETIC_ATTRIBUTE) | (((($this->access & self::$ACC_SYNTHETIC_ATTRIBUTE)) / self::$TO_ACC_SYNTHETIC)));
        $out->putShort(($this->access & ~$mask))->putShort($this->name)->putShort($this->superName);

        $out->putShort($this->interfaceCount);
        for ($i = 0; ($i < $this->interfaceCount); ++$i) {
            $out->putShort($this->interfaces[$i]);
        }

        $out->putShort($nbFields);
        $fb = $this->firstField;
        while (($fb != null)) {
            $fb->put($out);
            $fb = $fb->fv;
        }

        $out->putShort($nbMethods);
        $mb = $this->firstMethod;
        while (($mb != null)) {
            $mb->put($out);
            $mb = $mb->mv;
        }
        
        $out->putShort($attributeCount);
        if (($this->bootstrapMethods != null)) {
            $out->putShort($this->newUTF8("BootstrapMethods"));
            $out->putInt((count($this->bootstrapMethods) /*from: bootstrapMethods.length*/ + 2))->putShort($this->bootstrapMethodsCount);
            $out->putByteArray($this->bootstrapMethods->data, 0, count($this->bootstrapMethods) /*from: bootstrapMethods.length*/);
        }
        if ((ClassReader::SIGNATURES && ($this->signature != 0))) {
            $out->putShort($this->newUTF8("Signature"))->putInt(2)->putShort($this->signature);
        }
        if (($this->sourceFile != 0)) {
            $out->putShort($this->newUTF8("SourceFile"))->putInt(2)->putShort($this->sourceFile);
        }
        if (($this->sourceDebug != null)) {
            $len = count($this->sourceDebug) /*from: sourceDebug.length*/;
            $out->putShort($this->newUTF8("SourceDebugExtension"))->putInt($len);
            $out->putByteArray($this->sourceDebug->data, 0, $len);
        }
        if (($this->enclosingMethodOwner != 0)) {
            $out->putShort($this->newUTF8("EnclosingMethod"))->putInt(4);
            $out->putShort($this->enclosingMethodOwner)->putShort($this->enclosingMethod);
        }
        if (((($this->access & Opcodes::ACC_DEPRECATED)) != 0)) {
            $out->putShort($this->newUTF8("Deprecated"))->putInt(0);
        }
        if (((($this->access & Opcodes::ACC_SYNTHETIC)) != 0)) {
            if ((((($this->version & 0xFFFF)) < Opcodes::V1_5) || ((($this->access & self::$ACC_SYNTHETIC_ATTRIBUTE)) != 0))) {
                $out->putShort($this->newUTF8("Synthetic"))->putInt(0);
            }
        }
        if (($this->innerClasses != null)) {
            $out->putShort($this->newUTF8("InnerClasses"));
            $out->putInt((count($this->innerClasses) /*from: innerClasses.length*/ + 2))->putShort($this->innerClassesCount);
            $out->putByteArray($this->innerClasses->data, 0, count($this->innerClasses) /*from: innerClasses.length*/);
        }
        if ((ClassReader::ANNOTATIONS && ($this->anns != null))) {
            $out->putShort($this->newUTF8("RuntimeVisibleAnnotations"));
            $this->anns->put($out);
        }
        if ((ClassReader::ANNOTATIONS && ($this->ianns != null))) {
            $out->putShort($this->newUTF8("RuntimeInvisibleAnnotations"));
            $this->ianns->put($out);
        }
        if ((ClassReader::ANNOTATIONS && ($this->tanns != null))) {
            $out->putShort($this->newUTF8("RuntimeVisibleTypeAnnotations"));
            $this->tanns->put($out);
        }
        if ((ClassReader::ANNOTATIONS && ($this->itanns != null))) {
            $out->putShort($this->newUTF8("RuntimeInvisibleTypeAnnotations"));
            $this->itanns->put($out);
        }

        if (($this->attrs != null)) {
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
    public function newConstItem($cst) // [final Object cst]
    {
        if (is_integer($cst)) {
            $val = (int) $cst;
            return $this->newInteger($val);
        /*} elseif ($cst instanceof Byte) {
            $val = (int) $cst; // TODO this is to byte
            return $this->newInteger($val);*/
        } elseif ($cst instanceof Character) {
            $val = (int) $cst; // TODO this is to Character
            return $this->newInteger($val);
        } /*elseif ($cst instanceof Short) {
            $val = (int) $cst; // TODO this is to Short
            return $this->newInteger($val);
        }*/ elseif (is_bool($cst)) {
            $val = (bool)$cst;
            return $this->newInteger($val);
        } elseif (is_float($cst)) {
            $val = (float)$cst;
            return $this->newFloat($val);
        } elseif ($cst instanceof Long) {
            $val = (float)$cst;
            return $this->newLong($val);
        } elseif ($cst instanceof Double) {
            $val = (float)$cst;
            return $this->newDouble($val);
        } elseif (is_string($cst)) {
            return $this->newString($cst);
        } elseif ($cst instanceof Type) {
            $t = $cst;
            $s = $t->getSort();
            if (($s == $Type->OBJECT)) {
                return $this->newClassItem($t->getInternalName());
            } elseif (($s == $Type->METHOD)) {
                return $this->newMethodTypeItem($t->getDescriptor());
            } else {
                return $this->newClassItem($t->getDescriptor());
            }
        } elseif ($cst instanceof Handle) {
            $h = $cst;
            return $this->newHandleItem($h->tag, $h->owner, $h->name, $h->desc, $h->itf);
        } else {
            throw new IllegalArgumentException(("value " . $cst));
        }
    }

    public function newConst($cst) // [final Object cst]
    {
        return $this->newConstItem($cst)->index;
    }
    public function newUTF8($value) // [final String value]
    {
        $this->key->set_I_String_String_String(self::$UTF8, $value, null, null);
        $result = $this->get($this->key);
        if (($result == null)) {
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
     * @return a new or already existing class reference item.
     */
    protected function newClassItem($value) // [final String value]
    {
        $this->key2->set_I_String_String_String(self::$CLASS, $value, null, null);
        $result = $this->get($this->key2);

        if (($result == null)) {
            $this->pool->put12(self::$CLASS, $this->newUTF8($value));
            $result = new Item(/*++$this->index*/$this->index++, $this->key2);
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
     * @return the index of a new or already existing class reference item.
     */
    public function newClass($value) // [final String value]
    {
        return $this->newClassItem($value)->index;
    }

    protected function newMethodTypeItem($methodDesc) // [final String methodDesc]
    {
        $this->key2->set_I_String_String_String(self::$MTYPE, $methodDesc, null, null);
        $result = $this->get($this->key2);
        if (($result == null)) {
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
    protected function newHandleItem($tag, $owner, $name, $desc, $itf) // [final int tag, final String owner, final String name, final String desc, final boolean itf]
    {
        $this->key4->set_I_String_String_String((self::$HANDLE_BASE + $tag), $owner, $name, $desc);
        $result = $this->get($this->key4);
        if (($result == null)) {
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
    public function newHandle_I_String_String_String($tag, $owner, $name, $desc) // [final int tag, final String owner, final String name, final String desc]
    {
            /* match: I_String_String_String_b */
        return $this->newHandle_I_String_String_String_b($tag, $owner, $name, $desc, ($tag == Opcodes::H_INVOKEINTERFACE));
    }
    public function newHandle_I_String_String_String_b($tag, $owner, $name, $desc, $itf) // [final int tag, final String owner, final String name, final String desc, final boolean itf]
    {
        return $this->newHandleItem($tag, $owner, $name, $desc, $itf)->index;
    }
    protected function newInvokeDynamicItem($name, $desc, $bsm, $bsmArgs) // [final String name, final String desc, final Handle bsm, final Object... bsmArgs]
    {
        $bootstrapMethods = $this->bootstrapMethods;
        if (($bootstrapMethods == null)) {
            $bootstrapMethods = $this->bootstrapMethods = new ByteVector();
        }
        $position = count($bootstrapMethods) /*from: bootstrapMethods.length*/;
        $hashCode = $bsm->hashCode();
            /* match: I_String_String_String_b */
        $bootstrapMethods->putShort($this->newHandle_I_String_String_String_b($bsm->tag, $bsm->owner, $bsm->name, $bsm->desc, $bsm->isInterface()));
        $argsLength = count($bsmArgs) /*from: bsmArgs.length*/;
        $bootstrapMethods->putShort($argsLength);
        for ($i = 0; ($i < $argsLength); ++$i) {
            $bsmArg = $bsmArgs[$i];
            $hashCode ^= $bsmArg->hashCode();
            $bootstrapMethods->putShort($this->newConst($bsmArg));
        }
        $data = $bootstrapMethods->data;
        $length = ((((1 + 1) + $argsLength)) << 1);
        $hashCode &= 0x7FFFFFFF;
        $result = $this->items[($hashCode % count($this->items) /*from: items.length*/)];

        while ($result != null) {
            if ($result->type != BSM || $result->hashCode != hashCode) {
                $result = $result->next;
                continue;
            }

            $resultPosition = $result->intVal();
            for ($p = 0; $p < $length; $p++) {
                if ($data[position + p] != $data[resultPosition + p]) {
                    $result = $result->next();
                    continue;
                }
            }
            break;
        }

        $bootstrapMethodIndex = null;
        if (($result != null)) {
            $bootstrapMethodIndex = $result->index;
            // TODO $bootstrapMethods should be object
            //count($bootstrapMethods) /*from: bootstrapMethods.length*/ = $position;
            
            die;
        } else {
            $bootstrapMethodIndex = ++$this->bootstrapMethodsCount;
            $result = new Item($bootstrapMethodIndex);
            $result->set($position, $hashCode);
            $this->put($result);
        }
        $this->key3->set($name, $desc, $bootstrapMethodIndex);
        $result = $this->get($this->key3);
        if (($result == null)) {
            $this->put122(self::$INDY, $bootstrapMethodIndex, $this->newNameType($name, $desc));
            $result = new Item(/*++$this->index*/$this->index++, $this->key3);
            $this->put($result);
        }
        return $result;
    }
    public function newInvokeDynamic($name, $desc, $bsm, $bsmArgs) // [final String name, final String desc, final Handle bsm, final Object... bsmArgs]
    {
        return $this->newInvokeDynamicItem($name, $desc, $bsm, $bsmArgs)->index;
    }
    public function newFieldItem($owner, $name, $desc) // [final String owner, final String name, final String desc]
    {
        $this->key3->set_I_String_String_String(self::$FIELD, $owner, $name, $desc);
        $result = $this->get($this->key3);
        if (($result == null)) {
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
    public function newMethodItem($owner, $name, $desc, $itf) // [final String owner, final String name, final String desc, final boolean itf]
    {
        $type = ( ($itf) ? self::$IMETH : self::$METH );
        $this->key3->set_I_String_String_String($type, $owner, $name, $desc);
        $result = $this->get($this->key3);
        if (($result == null)) {
            $this->put122($type, $this->newClass($owner), $this->newNameType($name, $desc));
            $result = new Item(/*++$this->index*/$this->index++, $this->key3);
            $this->put($result);
        }
        return $result;
    }
    public function newMethod($owner, $name, $desc, $itf) // [final String owner, final String name, final String desc, final boolean itf]
    {
        return $this->newMethodItem($owner, $name, $desc, $itf)->index;
    }
    protected function newInteger($value) // [final int value]
    {
        $this->key->set($value);
        $result = $this->get($this->key);
        if (($result == null)) {
            $this->pool->putByte(self::$INT)->putInt($value);
            $result = new Item(/*++$this->index*/$this->index++, $this->key);
            $this->put($result);
        }
        return $result;
    }
    protected function newFloat($value) // [final float value]
    {
        $this->key->set($value);
        $result = $this->get($this->key);
        if (($result == null)) {
            $this->pool->putByte(self::$FLOAT)->putInt($this->key->intVal);
            $result = new Item(/*++$this->index*/$this->index++, $this->key);
            $this->put($result);
        }
        return $result;
    }
    protected function newLong($value) // [final long value]
    {
        $this->key->set($value);
        $result = $this->get($this->key);
        if (($result == null)) {
            $this->pool->putByte(self::$LONG)->putLong($value);
            $result = new Item($this->index, $this->key);
            $this->index += 2;
            $this->put($result);
        }
        return $result;
    }
    protected function newDouble($value) // [final double value]
    {
        $this->key->set($value);
        $result = $this->get($this->key);
        if (($result == null)) {
            $this->pool->putByte(self::$DOUBLE)->putLong($this->key->longVal);
            $result = new Item($this->index, $this->key);
            $this->index += 2;
            $this->put($result);
        }
        return $result;
    }
    protected function newString($value) // [final String value]
    {
        $this->key2->set_I_String_String_String(self::$STR, $value, null, null);
        $result = $this->get($this->key2);
        if (($result == null)) {
            $this->pool->put12(self::$STR, $this->newUTF8($value));
            $result = new Item(/*++$this->index*/$this->index++, $this->key2);
            $this->put($result);
        }
        return $result;
    }
    public function newNameType($name, $desc) // [final String name, final String desc]
    {
        return $this->newNameTypeItem($name, $desc)->index;
    }
    protected function newNameTypeItem($name, $desc) // [final String name, final String desc]
    {
        $this->key2->set_I_String_String_String(self::$NAME_TYPE, $name, $desc, null);
        $result = $this->get($this->key2);
        if (($result == null)) {
            $this->put122(self::$NAME_TYPE, $this->newUTF8($name), $this->newUTF8($desc));
            $result = new Item(/*++$this->index*/$this->index++, $this->key2);
            $this->put($result);
        }
        return $result;
    }
    protected function addType_String($type) // [final String type]
    {
        $this->key->set(self::$TYPE_NORMAL, $type, null, null);
        $result = $this->get($this->key);
        if (($result == null)) {
            $result = $this->addType_Item($this->key);
        }
        return $result->index;
    }
    protected function addUninitializedType($type, $offset) // [final String type, final int offset]
    {
        $this->key->type = self::$TYPE_UNINIT;
        $this->key->intVal = $offset;
        $this->key->strVal1 = $type;
        $this->key->hashCode = (0x7FFFFFFF & (((self::$TYPE_UNINIT + $type->hashCode()) + $offset)));
        $result = $this->get($this->key);
        if (($result == null)) {
            $result = $this->addType_Item($this->key);
        }
        return $result->index;
    }
    protected function addType_Item($item) // [final Item item]
    {
        ++$this->typeCount;
        $result = new Item($this->typeCount, $this->key);
        $this->put($result);
        if (($this->typeTable == null)) {
            $this->typeTable = array();
        }
        if (($this->typeCount == count($this->typeTable) /*from: typeTable.length*/)) {
            $newTable = array();
            foreach (range(0, (count($this->typeTable) /*from: typeTable.length*/ + 0)) as $_upto) {
                $newTable[$_upto] = $this->typeTable[$_upto - (0) + 0];
            } /* from: System.arraycopy(typeTable, 0, newTable, 0, typeTable.length) */;
            $this->typeTable = $newTable;
        }
        $this->typeTable[$this->typeCount] = $result;
        return $result;
    }
    protected function getMergedType($type1, $type2) // [final int type1, final int type2]
    {
        $this->key2->type = self::$TYPE_MERGED;
        $this->key2->longVal = ($type1 | ((($type2) << 32)));
        $this->key2->hashCode = (0x7FFFFFFF & (((self::$TYPE_MERGED + $type1) + $type2)));
        $result = $this->get($this->key2);
        if (($result == null)) {
            $t = $this->typeTable[$type1]->strVal1;
            $u = $this->typeTable[$type2]->strVal1;
            $this->key2->intVal = $this->addType_String($this->getCommonSuperClass($t, $u));
            $result = new Item(0, $this->key2);
            $this->put($result);
        }
        return $result->intVal;
    }
    protected function getCommonSuperClass($type1, $type2) // [final String type1, final String type2]
    {
        $c = null;
        $d = null;
        $classLoader = $this->getClass()->getClassLoader();
        try {
            $c = $Class->forName($type1->replace('/', '.'), false, $classLoader);
            $d = $Class->forName($type2->replace('/', '.'), false, $classLoader);
        } catch (Exception $e) {
            throw new RuntimeException($e->toString());
        }
        if ($c->isAssignableFrom($d)) {
            return $type1;
        }
        if ($d->isAssignableFrom($c)) {
            return $type2;
        }
        if (($c->isInterface() || $d->isInterface())) {
            return "java/lang/Object";
        } else {
            do {
                $c = $c->getSuperclass();
            } while (!$c->isAssignableFrom($d));
            return $c->getName()->replace('.', '/');
        }
    }
    protected function get($key) // [final Item key]
    {
        $i = $this->items[($key->hashCode % count($this->items) /*from: items.length*/)];
        while ((($i != null) && ((($i->type != $key->type) || !$key->isEqualTo($i))))) {
            $i = $i->next;
        }
        return $i;
    }
    protected function put($i) // [final Item i]
    {
        if ((($this->index + $this->typeCount) > $this->threshold)) {
            $ll = count($this->items) /*from: items.length*/;
            $nl = (($ll * 2) + 1);
            $newItems = array();
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
        $index = ($i->hashCode % count($this->items) /*from: items.length*/);
        $i->next = $this->items[$index];
        $this->items[$index] = $i;
    }
    protected function put122($b, $s1, $s2) // [final int b, final int s1, final int s2]
    {
        $this->pool->put12($b, $s1)->putShort($s2);
    }
    protected function put112($b1, $b2, $s) // [final int b1, final int b2, final int s]
    {
        $this->pool->put11($b1, $b2)->putShort($s);
    }
}
ClassWriter::__staticinit(); // initialize static vars for this class on load
