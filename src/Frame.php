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
 * Information about the input and output stack map frames of a basic block.
 *
 * @author  Eric Bruneton
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class Frame
{
    /**
     * The stack size variation corresponding to each JVM instruction. This
     * stack variation is equal to the size of the values produced by an
     * instruction, minus the size of the values consumed by this instruction.
     *
     * @var int[]
     */
    const SIZE = [
        0, //NOP, // visitInsn
        1, //ACONST_NULL, // -
        1, //ICONST_M1, // -
        1, //ICONST_0, // -
        1, //ICONST_1, // -
        1, //ICONST_2, // -
        1, //ICONST_3, // -
        1, //ICONST_4, // -
        1, //ICONST_5, // -
        2, //LCONST_0, // -
        2, //LCONST_1, // -
        1, //FCONST_0, // -
        1, //FCONST_1, // -
        1, //FCONST_2, // -
        2, //DCONST_0, // -
        2, //DCONST_1, // -
        1, //BIPUSH, // visitIntInsn
        1, //SIPUSH, // -
        1, //LDC, // visitLdcInsn
        0,  //LDC_W, // -
        0,  //LDC2_W, // -
        1, //ILOAD, // visitVarInsn
        2, //LLOAD, // -
        1, //FLOAD, // -
        2, //DLOAD, // -
        1, //ALOAD, // -
        0, //ILOAD_0, // -
        0, //ILOAD_1, // -
        0, //ILOAD_2, // -
        0, //ILOAD_3, // -
        0, //LLOAD_0, // -
        0, //LLOAD_1, // -
        0, //LLOAD_2, // -
        0, //LLOAD_3, // -
        0, //FLOAD_0, // -
        0, //FLOAD_1, // -
        0, //FLOAD_2, // -
        0, //FLOAD_3, // -
        0, //DLOAD_0, // -
        0, //DLOAD_1, // -
        0, //DLOAD_2, // -
        0, //DLOAD_3, // -
        0, //ALOAD_0, // -
        0, //ALOAD_1, // -
        0, //ALOAD_2, // -
        0, //ALOAD_3, // -
        -1, //IALOAD, // visitInsn
        0,  //LALOAD, // -
        -1, //FALOAD, // -
        0, //DALOAD, // -
        -1, //AALOAD, // -
        -1, //BALOAD, // -
        -1, //CALOAD, // -
        -1, //SALOAD, // -
        -1, //ISTORE, // visitVarInsn
        -2, //LSTORE, // -
        -1, //FSTORE, // -
        -2, //DSTORE, // -
        -1, //ASTORE, // -
        0,  //ISTORE_0, // -
        0,  //ISTORE_1, // -
        0,  //ISTORE_2, // -
        0,  //ISTORE_3, // -
        0,  //LSTORE_0, // -
        0,  //LSTORE_1, // -
        0,  //LSTORE_2, // -
        0,  //LSTORE_3, // -
        0,  //FSTORE_0, // -
        0,  //FSTORE_1, // -
        0,  //FSTORE_2, // -
        0,  //FSTORE_3, // -
        0,  //DSTORE_0, // -
        0,  //DSTORE_1, // -
        0,  //DSTORE_2, // -
        0,  //DSTORE_3, // -
        0,  //ASTORE_0, // -
        0,  //ASTORE_1, // -
        0,  //ASTORE_2, // -
        0,  //ASTORE_3, // -
        -3, //IASTORE, // visitInsn
        -4, //LASTORE, // -
        -3, //FASTORE, // -
        -4, //DASTORE, // -
        -3, //AASTORE, // -
        -3, //BASTORE, // -
        -3, //CASTORE, // -
        -3, //SASTORE, // -
        -1, //POP, // -
        -2, //POP2, // -
        1,  //DUP, // -
        1,  //DUP_X1, // -
        1,  //DUP_X2, // -
        2,  //DUP2, // -
        2,  //DUP2_X1, // -
        2,  //DUP2_X2, // -
        0,  //SWAP, // -
        -1, //IADD, // -
        -2, //LADD, // -
        -1, //FADD, // -
        -2, //DADD, // -
        -1, //ISUB, // -
        -2, //LSUB, // -
        -1, //FSUB, // -
        -2, //DSUB, // -
        -1, //IMUL, // -
        -2, //LMUL, // -
        -1, //FMUL, // -
        -2, //DMUL, // -
        -1, //IDIV, // -
        -2, //LDIV, // -
        -1, //FDIV, // -
        -2, //DDIV, // -
        -1, //IREM, // -
        -2, //LREM, // -
        -1, //FREM, // -
        -2, //DREM, // -
        0,  //INEG, // -
        0,  //LNEG, // -
        0,  //FNEG, // -
        0,  //DNEG, // -
        -1, //ISHL, // -
        -1, //LSHL, // -
        -1, //ISHR, // -
        -1, //LSHR, // -
        -1, //IUSHR, // -
        -1, //LUSHR, // -
        -1, //IAND, // -
        -2, //LAND, // -
        -1, //IOR, // -
        -2, //LOR, // -
        -1, //IXOR, // -
        -2, //LXOR, // -
        0,  //IINC, // visitIincInsn
        1,  //I2L, // visitInsn
        0,  //I2F, // -
        1,  //I2D, // -
        -1, //L2I, // -
        -1, //L2F, // -
        0,  //L2D, // -
        0,  //F2I, // -
        1,  //F2L, // -
        1,  //F2D, // -
        -1, //D2I, // -
        0,  //D2L, // -
        -1, //D2F, // -
        0, //I2B, // -
        0, //I2C, // -
        0, //I2S, // -
        -3, //LCMP, // -
        -1, //FCMPL, // -
        -1, //FCMPG, // -
        -3, //DCMPL, // -
        -3, //DCMPG, // -
        -1, //IFEQ, // visitJumpInsn
        -1, //IFNE, // -
        -1, //IFLT, // -
        -1, //IFGE, // -
        -1, //IFGT, // -
        -1, //IFLE, // -
        -2, //IF_ICMPEQ, // -
        -2, //IF_ICMPNE, // -
        -2, //IF_ICMPLT, // -
        -2, //IF_ICMPGE, // -
        -2, //IF_ICMPGT, // -
        -2, //IF_ICMPLE, // -
        -2, //IF_ACMPEQ, // -
        -2, //IF_ACMPNE, // -
        0,//GOTO, // -
        1,//JSR, // -
        0,//RET, // visitVarInsn
        -1, //TABLESWITCH, // visiTableSwitchInsn
        -1, //LOOKUPSWITCH, // visitLookupSwitch
        -1, //IRETURN, // visitInsn
        -2, //LRETURN, // -
        -1, //FRETURN, // -
        -2, //DRETURN, // -
        -1, //ARETURN, // -
        0,//RETURN, // -
        0, //GETSTATIC, // visitFieldInsn
        0, //PUTSTATIC, // -
        0, //GETFIELD, // -
        0, //PUTFIELD, // -
        0, //INVOKEVIRTUAL, // visitMethodInsn
        0, //INVOKESPECIAL, // -
        0, //INVOKESTATIC, // -
        0, //INVOKEINTERFACE, // -
        0, //INVOKEDYNAMIC, // visitInvokeDynamicInsn
        1,//NEW, // visitTypeInsn
        0,//NEWARRAY, // visitIntInsn
        0,//ANEWARRAY, // visitTypeInsn
        0,//ARRAYLENGTH, // visitInsn
        0, //ATHROW, // -
        0,//CHECKCAST, // visitTypeInsn
        0,//INSTANCEOF, // -
        -1, //MONITORENTER, // visitInsn
        -1, //MONITOREXIT, // -
        0, //WIDE, // NOT VISITED
        0, //MULTIANEWARRAY, // visitMultiANewArrayInsn
        -1, //IFNULL, // visitJumpInsn
        -1, //IFNONNULL, // -
        0, //GOTO_W, // -
        0, //JSR_W, // -
    ];

    public static $DIM; // int
    public static $ARRAY_OF;    // int
    public static $ELEMENT_OF;  // int
    public static $KIND;    // int
    public static $TOP_IF_LONG_OR_DOUBLE;   // int
    public static $VALUE;   // int
    public static $BASE_KIND;   // int
    public static $BASE_VALUE;  // int
    public static $BASE;    // int
    public static $OBJECT;  // int
    public static $UNINITIALIZED;   // int
    public static $LOCAL;   // int
    public static $STACK;   // int
    public static $TOP; // int
    public static $BOOLEAN; // int
    public static $BYTE;    // int
    public static $CHAR;    // int
    public static $SHORT;   // int
    public static $INTEGER; // int
    public static $FLOAT;   // int
    public static $DOUBLE;  // int
    public static $LONG;    // int
    public static $NULL;    // int
    public static $UNINITIALIZED_THIS;  // int

    protected $owner;   // Label
    protected $inputLocals; // int[]
    protected $inputStack;  // int[]
    protected $outputLocals;    // int[]
    protected $outputStack; // int[]
    protected $outputStackTop;  // int
    protected $initializationCount; // int
    protected $initializations; // int[]

    public static function __staticinit()
    {
        // static class members
        self::$DIM = 0xF0000000;
        self::$ARRAY_OF = 0x10000000;
        self::$ELEMENT_OF = 0xF0000000;
        self::$KIND = 0xF000000;
        self::$TOP_IF_LONG_OR_DOUBLE = 0x800000;
        self::$VALUE = 0x7FFFFF;
        self::$BASE_KIND = 0xFF00000;
        self::$BASE_VALUE = 0xFFFFF;
        self::$BASE = 0x1000000;
        self::$OBJECT = (self::$BASE | 0x700000);
        self::$UNINITIALIZED = (self::$BASE | 0x800000);
        self::$LOCAL = 0x2000000;
        self::$STACK = 0x3000000;
        self::$TOP = (self::$BASE | 0);
        self::$BOOLEAN = (self::$BASE | 9);
        self::$BYTE = (self::$BASE | 10);
        self::$CHAR = (self::$BASE | 11);
        self::$SHORT = (self::$BASE | 12);
        self::$INTEGER = (self::$BASE | 1);
        self::$FLOAT = (self::$BASE | 2);
        self::$DOUBLE = (self::$BASE | 3);
        self::$LONG = (self::$BASE | 4);
        self::$NULL = (self::$BASE | 5);
        self::$UNINITIALIZED_THIS = (self::$BASE | 6);
    }

    /**
     * Computes the stack size variation corresponding to each JVM instruction.
     *
     * @return void
     */
    public static function calculate() : void
    {
        $size = [];
        $s = 'EFFFFFFFFGGFFFGGFFFEEFGFGFEEEEEEEEEEEEEEEEEEEEDEDEDDDDD'
        . 'CDCDEEEEEEEEEEEEEEEEEEEEBABABBBBDCFFFGGGEDCDCDCDCDCDCDCDCD'
        . 'CDCEEEEDDDDDDDCDCDCEFEFDDEEFFDEDEEEBDDBBDDDDDDCCCCCCCCEFED'
        . 'DDCDCDEEEEEEEEEEFEEEEEEDDEEDDEE';

        for ($i = 0; $i < 202; ++$i) {
            $size[$i] = ord(self::charAt($s, $i)) - ord('E');
        }
    }

    /**
     * Sets this frame to the given value.
     *
     * @param ClassWriter $cw
     *                    the ClassWriter to which this label belongs.
     * @param int         $nLocal
     *                    the number of local variables.
     * @param array       $local
     *                    the local variable types. Primitive types are represented by
     *                    {@link Opcodes#TOP}, {@link Opcodes#INTEGER},
     *                    {@link Opcodes#FLOAT}, {@link Opcodes#LONG},
     *                    {@link Opcodes#DOUBLE},{@link Opcodes#NULL} or
     *                    {@link Opcodes#UNINITIALIZED_THIS} (long and double are
     *                    represented by a single element). Reference types are
     *                    represented by String objects (representing internal names),
     *                    and uninitialized types by Label objects (this label
     *                    designates the NEW instruction that created this uninitialized
     *                    value).
     * @param int         $nStack
     *                    the number of operand stack elements.
     * @param array       $stack
     *                    the operand stack types (same format as the "local" array).
     *
     * @return void
     */
    protected function set(ClassWriter $cw, int $nLocal, array $local, int $nStack, array $stack) : void
    {
        $i = Frame::convert($cw, $nLocal, $local, $this->inputLocals);
        while ($i < count($local)) {
            $this->inputLocals[++$i] = self::$TOP;
        }

        $nStackTop = 0;
        for ($j = 0; ($j < $nStack); ++$j) {
            if ((($stack[$j] == Opcodes::LONG) || ($stack[$j] == Opcodes::DOUBLE))) {
                ++$nStackTop;
            }
        }

        $this->inputStack = [];
        Frame::convert($cw, $nStack, $stack, $this->inputStack);
        $this->outputStackTop = 0;
        $this->initializationCount = 0;
    }

    /**
     * Converts types from the MethodWriter.visitFrame() format to the Frame
     * format.
     *
     * @param ClassWriter $cw
     *                    the ClassWriter to which this label belongs.
     * @param int         $nInput
     *                    the number of types to convert.
     * @param array       $input
     *                    the types to convert. Primitive types are represented by
     *                    {@link Opcodes#TOP}, {@link Opcodes#INTEGER},
     *                    {@link Opcodes#FLOAT}, {@link Opcodes#LONG},
     *                    {@link Opcodes#DOUBLE},{@link Opcodes#NULL} or
     *                    {@link Opcodes#UNINITIALIZED_THIS} (long and double are
     *                    represented by a single element). Reference types are
     *                    represented by String objects (representing internal names),
     *                    and uninitialized types by Label objects (this label
     *                    designates the NEW instruction that created this uninitialized
     *                    value).
     * @param array       $output
     *                    where to store the converted types.
     *
     * @return int the number of output elements.
     */
    protected static function convert(ClassWriter $cw, int $nInput, array $input, array &$output) : int
    {
        $i = 0;
        for ($j = 0; ($j < $nInput); ++$j) {
            if (is_int($input[$j])) {
                $output[++$i] = (self::$BASE | ($input[$j])->intValue());
                if ((($input[$j] == Opcodes::LONG) || ($input[$j] == Opcodes::DOUBLE))) {
                    $output[++$i] = self::$TOP;
                }
            } elseif (is_string($input[$j])) {
                $output[++$i] = Frame::type($cw, Type::getObjectType($input[$j])->getDescriptor());
            } else {
                $output[++$i] = (self::$UNINITIALIZED | $cw->addUninitializedType('', ($input[$j])::$position));
            }
        }

        return $i;
    }

    /**
     * Sets this frame to the value of the given frame. WARNING: after this
     * method is called the two frames share the same data structures. It is
     * recommended to discard the given frame f to avoid unexpected side
     * effects.
     *
     * @param Frame $f The new frame value.
     *
     * @return void
     */
    protected function setFrame(Frame $f) : void
    {
        $this->inputLocals = $f->inputLocals;
        $this->inputStack = $f->inputStack;
        $this->outputLocals = $f->outputLocals;
        $this->outputStack = $f->outputStack;
        $this->outputStackTop = $f->outputStackTop;
        $this->initializationCount = $f->initializationCount;
        $this->initializations = $f->initializations;
    }

    /**
     * Returns the output frame local variable type at the given index.
     *
     * @param int $local the index of the local that must be returned.
     *
     * @return int the output frame local variable type at the given index.
     */
    protected function get(int $local) : int
    {
        if ((($this->outputLocals == null) || ($local >= count($this->outputLocals)))) {
            return (self::$LOCAL | $local);
        }

        $type = $this->outputLocals[$local];
        if ($type === 0) {
            $type = $this->outputLocals[$local] = (self::$LOCAL | $local);
        }

        return $type;
    }

    /**
     * Sets the output frame local variable type at the given index.
     *
     * @param int $local the index of the local that must be set.
     * @param int $type  the value of the local that must be set.
     *
     * @return void
     */
    protected function setLocalVariable(int $local, int $type) : void
    {
        if ($this->outputLocals === null) {
            $this->outputLocals = [];
        }

        $n = count($this->outputLocals);
        if ($local >= $n) {
            $t = [];
            foreach (range(0, ($n + 0)) as $_upto) {
                $t[$_upto] = $this->outputLocals[$_upto - (0) + 0];
            } /* from: System.arraycopy(outputLocals, 0, t, 0, n) */;
            $this->outputLocals = $t;
        }

        $this->outputLocals[$local] = $type;
    }

    /**
     * Pushes a new type onto the output frame stack.
     *
     * @param int $type the type that must be pushed.
     *
     * @return void
     */
    protected function pushType($type) : void
    {
        if (($this->outputStack == null)) {
            $this->outputStack = [];
        }

        $n = count($this->outputStack) /*from: outputStack.length*/;
        if (($this->outputStackTop >= $n)) {
            $t = [];
            foreach (range(0, ($n + 0)) as $_upto) {
                $t[$_upto] = $this->outputStack[$_upto - (0) + 0];
            } /* from: System.arraycopy(outputStack, 0, t, 0, n) */;
            $this->outputStack = $t;
        }

        $this->outputStack[++$this->outputStackTop] = $type;
        $top = ($this->owner->inputStackTop + $this->outputStackTop);
        if (($top > $this->owner->outputStackMax)) {
            $this->owner->outputStackMax = $top;
        }
    }

    /**
     * Pushes a new type onto the output frame stack.
     *
     * @param ClassWriter $cw
     *                    the ClassWriter to which this label belongs.
     * @param string      $desc
     *               the descriptor of the type to be pushed. Can also be a method
     *               descriptor (in this case this method pushes its return type
     *               onto the output frame stack).
     *
     * @return void
     */
    protected function pushDescription(ClassWriter $cw, string $desc) : void
    {
        $type = Frame::type($cw, $desc);
        if (($type != 0)) {
            /* match: I */
            $this->pushType($type);
            if ((($type == self::$LONG) || ($type == self::$DOUBLE))) {
                /* match: I */
                $this->pushType(self::$TOP);
            }
        }
    }

    /**
     * Returns the int encoding of the given type.
     *
     * @param ClassWriter $cw   the ClassWriter to which this label belongs.
     * @param string      $desc a type descriptor.
     *
     * @return int the int encoding of the given type.
     */
    protected static function type(ClassWriter $cw, string $desc) : int
    {
        $t = null;
        $index = ( (($desc->charAt(0) . '(')) ? ($desc->indexOf(')') + 1) : 0 );
        switch ($desc->charAt($index)) {
            case 'V':
                return 0;
            case 'Z':
            case 'C':
            case 'B':
            case 'S':
            case 'I':
                return self::$INTEGER;
            case 'F':
                return self::$FLOAT;
            case 'J':
                return self::$LONG;
            case 'D':
                return self::$DOUBLE;
            case 'L':
                $t = $desc->substring(($index + 1), ($desc->length() - 1));
                return (self::$OBJECT | $cw->addType($t));
            default:
                $data = null;
                $dims = ($index + 1);
                while (($desc->charAt($dims) . '[')) {
                    ++$dims;
                }
                switch ($desc->charAt($dims)) {
                    case 'Z':
                        $data = self::$BOOLEAN;
                        break;
                    case 'C':
                        $data = self::$CHAR;
                        break;
                    case 'B':
                        $data = self::$BYTE;
                        break;
                    case 'S':
                        $data = self::$SHORT;
                        break;
                    case 'I':
                        $data = self::$INTEGER;
                        break;
                    case 'F':
                        $data = self::$FLOAT;
                        break;
                    case 'J':
                        $data = self::$LONG;
                        break;
                    case 'D':
                        $data = self::$DOUBLE;
                        break;
                    default:
                        $t = $desc->substring(($dims + 1), ($desc->length() - 1));
                        $data = (self::$OBJECT | $cw->addType($t));
                }
                return (((($dims - $index)) << 28) | $data);
        }
    }

    /**
     * Pops a type from the output frame stack and returns its value.
     *
     * @return int the type that has been popped from the output frame stack.
     */
    protected function pop() : int
    {
        if (($this->outputStackTop > 0)) {
            return $this->outputStack[--$this->outputStackTop];
        }

        return (self::$STACK | -(--$this->owner->inputStackTop));
    }

    /**
     * Pops the given number of types from the output frame stack.
     *
     * @param int $elements the number of types that must be popped.
     *
     * @return void
     */
    protected function popElements(int $elements) : void
    {
        if (($this->outputStackTop >= $elements)) {
            $this->outputStackTop -= $elements;
        } else {
            $this->owner->inputStackTop -= ($elements - $this->outputStackTop);
            $this->outputStackTop = 0;
        }
    }

    /**
     * Pops a type from the output frame stack.
     *
     * @param string $desc the descriptor of the type to be popped. Can also be a method
     *                     descriptor (in this case this method pops the types
     *                     corresponding to the method arguments).
     *
     * @return void
     */
    protected function popType(string $desc) : void
    {
        $c = $desc->charAt(0);
        if (($c == '(')) {
            $this->popElements((((Type::getArgumentsAndReturnSizesFromDescription($desc) >> 2)) - 1));
        } elseif ((($c == 'J') || ($c == 'D'))) {
            $this->popElements(2);
        } else {
            $this->popElements(1);
        }
    }

    /**
     * Adds a new type to the list of types on which a constructor is invoked in
     * the basic block.
     *
     * @param int $var a type on a which a constructor is invoked.
     *
     * @return void
     */
    protected function addType(int $var) : void
    {
        if (($this->initializations == null)) {
            $this->initializations = [];
        }
        $n = count($this->initializations) /*from: initializations.length*/;
        if (($this->initializationCount >= $n)) {
            $t = [];
            foreach (range(0, ($n + 0)) as $_upto) {
                $t[$_upto] = $this->initializations[$_upto - (0) + 0];
            } /* from: System.arraycopy(initializations, 0, t, 0, n) */

            $this->initializations = $t;
        }
        $this->initializations[++$this->initializationCount] = $var;
    }

    /**
     * Replaces the given type with the appropriate type if it is one of the
     * types on which a constructor is invoked in the basic block.
     *
     * @param ClassWriter $cw the ClassWriter to which this label belongs.
     * @param int         $t  a type
     *
     * @return int t or, if t is one of the types on which a constructor is invoked in the basic block,
     *             the type corresponding to this constructor.
     */
    protected function replaceType(ClassWriter $cw, int $t) : int
    {
        $s = null;
        if (($t == self::$UNINITIALIZED_THIS)) {
            $s = (self::$OBJECT | $cw->addType($cw->thisName));
        } elseif (((($t & ((self::$DIM | self::$BASE_KIND)))) == self::$UNINITIALIZED)) {
            $type = $cw->typeTable[$t & BASE_VALUE];
            $s    = (self::$OBJECT | $cw->addType($type));
        } else {
            return $t;
        }

        for ($j = 0; ($j < $this->initializationCount); ++$j) {
            $u = $this->initializations[$j];
            $dim = ($u & self::$DIM);
            $kind = ($u & self::$KIND);
            if (($kind == self::$LOCAL)) {
                $u = ($dim + $this->inputLocals[($u & self::$VALUE)]);
            } elseif (($kind == self::$STACK)) {
                $u = ($dim + $this->inputStack[(count($this->inputStack)  - (($u & self::$VALUE)))]);
            }
            if (($t == $u)) {
                return $s;
            }
        }

        return $t;
    }

    /**
     * Initializes the input frame of the first basic block from the method
     * descriptor.
     *
     * @param ClassWriter $cw        the ClassWriter to which this label belongs.
     * @param int         $access    the access flags of the method to which this label belongs.
     * @param array       $args      the formal parameter types of this method.
     * @param int         $maxLocals the maximum number of local variables of this method.
     *
     * @return void
     */
    protected function initInputFrame(ClassWriter $cw, int $access, array $args, int $maxLocals) : void
    {
        $this->inputLocals = [];
        $this->inputStack = [];
        $i = 0;
        if (((($access & Opcodes::ACC_STATIC)) == 0)) {
            if (((($access & MethodWriter::$ACC_CONSTRUCTOR)) == 0)) {
                $this->inputLocals[++$i] = (self::$OBJECT | $cw->addType($cw->thisName));
            } else {
                $this->inputLocals[++$i] = self::$UNINITIALIZED_THIS;
            }
        }

        for ($j = 0; ($j < count($args) /*from: args.length*/); ++$j) {
            $t = Frame::type($cw, $args[$j]->getDescriptor());
            $this->inputLocals[++$i] = $t;
            if ((($t == self::$LONG) || ($t == self::$DOUBLE))) {
                $this->inputLocals[++$i] = self::$TOP;
            }
        }

        while (($i < $maxLocals)) {
            $this->inputLocals[++$i] = self::$TOP;
        }
    }

    /**
     * Simulates the action of the given instruction on the output stack frame.
     *
     * @param int         $opcode the opcode of the instruction.
     * @param int         $arg    the operand of the instruction, if any.
     * @param ClassWriter $cw     the class writer to which this label belongs.
     * @param Item        $item   the operand of the instructions, if any.
     *
     * @return void
     */
    protected function execute(int $opcode, int $arg, ClassWriter $cw, Item $item) : void
    {
        $t1 = null;
        $t2 = null;
        $t3 = null;
        $t4 = null;
        switch ($opcode) {
            case Opcodes::NOP:
            case Opcodes::INEG:
            case Opcodes::LNEG:
            case Opcodes::FNEG:
            case Opcodes::DNEG:
            case Opcodes::I2B:
            case Opcodes::I2C:
            case Opcodes::I2S:
            case Opcodes::GOTO_:
            case Opcodes::RETURN_:
                break;
            case Opcodes::ACONST_NULL:
                /* match: I */
                $this->pushType(self::$NULL);
                break;
            case Opcodes::ICONST_M1:
            case Opcodes::ICONST_0:
            case Opcodes::ICONST_1:
            case Opcodes::ICONST_2:
            case Opcodes::ICONST_3:
            case Opcodes::ICONST_4:
            case Opcodes::ICONST_5:
            case Opcodes::BIPUSH:
            case Opcodes::SIPUSH:
            case Opcodes::ILOAD:
                /* match: I */
                $this->pushType(self::$INTEGER);
                break;
            case Opcodes::LCONST_0:
            case Opcodes::LCONST_1:
            case Opcodes::LLOAD:
                /* match: I */
                $this->pushType(self::$LONG);
                /* match: I */
                $this->pushType(self::$TOP);
                break;
            case Opcodes::FCONST_0:
            case Opcodes::FCONST_1:
            case Opcodes::FCONST_2:
            case Opcodes::FLOAD:
                /* match: I */
                $this->pushType(self::$FLOAT);
                break;
            case Opcodes::DCONST_0:
            case Opcodes::DCONST_1:
            case Opcodes::DLOAD:
                /* match: I */
                $this->pushType(self::$DOUBLE);
                /* match: I */
                $this->pushType(self::$TOP);
                break;
            case Opcodes::LDC:
                switch ($item->type) {
                    case ClassWriter::$INT:
                        /* match: I */
                        $this->pushType(self::$INTEGER);
                        break;
                    case ClassWriter::$LONG:
                        /* match: I */
                        $this->pushType(self::$LONG);
                        /* match: I */
                        $this->pushType(self::$TOP);
                        break;
                    case ClassWriter::$FLOAT:
                        /* match: I */
                        $this->pushType(self::$FLOAT);
                        break;
                    case ClassWriter::$DOUBLE:
                        /* match: I */
                        $this->pushType(self::$DOUBLE);
                        /* match: I */
                        $this->pushType(self::$TOP);
                        break;
                    case ClassWriter::$CLASS:
                        /* match: I */
                        $this->pushType((self::$OBJECT | $cw->addType('java/lang/Class')));
                        break;
                    case ClassWriter::$STR:
                        /* match: I */
                        $this->pushType((self::$OBJECT | $cw->addType('java/lang/String')));
                        break;
                    case ClassWriter::$MTYPE:
                        /* match: I */
                        $this->pushType((self::$OBJECT | $cw->addType('java/lang/invoke/MethodType')));
                        break;
                    default:
                        /* match: I */
                        $this->pushType((self::$OBJECT | $cw->addType('java/lang/invoke/MethodHandle')));
                }
                break;
            case Opcodes::ALOAD:
                /* match: I */
                $this->pushType($this->get($arg));
                break;
            case Opcodes::IALOAD:
            case Opcodes::BALOAD:
            case Opcodes::CALOAD:
            case Opcodes::SALOAD:
                $this->popElements(2);
                /* match: I */
                $this->pushType(self::$INTEGER);
                break;
            case Opcodes::LALOAD:
            case Opcodes::D2L:
                $this->popElements(2);
                /* match: I */
                $this->pushType(self::$LONG);
                /* match: I */
                $this->pushType(self::$TOP);
                break;
            case Opcodes::FALOAD:
                $this->popElements(2);
                /* match: I */
                $this->pushType(self::$FLOAT);
                break;
            case Opcodes::DALOAD:
            case Opcodes::L2D:
                $this->popElements(2);
                /* match: I */
                $this->pushType(self::$DOUBLE);
                /* match: I */
                $this->pushType(self::$TOP);
                break;
            case Opcodes::AALOAD:
                $this->popElements(1);
                $t1 = $this->pop();
                /* match: I */
                $this->pushType((self::$ELEMENT_OF + $t1));
                break;
            case Opcodes::ISTORE:
            case Opcodes::FSTORE:
            case Opcodes::ASTORE:
                $t1 = $this->pop();
                /* match: I_I */
                $this->setLocalVariable($arg, $t1);
                if (($arg > 0)) {
                    $t2 = $this->get(($arg - 1));
                    if ((($t2 == self::$LONG) || ($t2 == self::$DOUBLE))) {
                        /* match: I_I */
                        $this->setLocalVariable(($arg - 1), self::$TOP);
                    } elseif (((($t2 & self::$KIND)) != self::$BASE)) {
                        /* match: I_I */
                        $this->setLocalVariable(($arg - 1), ($t2 | self::$TOP_IF_LONG_OR_DOUBLE));
                    }
                }
                break;
            case Opcodes::LSTORE:
            case Opcodes::DSTORE:
                $this->popElements(1);
                $t1 = $this->pop();
                /* match: I_I */
                $this->setLocalVariable($arg, $t1);
                /* match: I_I */
                $this->setLocalVariable(($arg + 1), self::$TOP);
                if (($arg > 0)) {
                    $t2 = $this->get(($arg - 1));
                    if ((($t2 == self::$LONG) || ($t2 == self::$DOUBLE))) {
                        /* match: I_I */
                        $this->setLocalVariable(($arg - 1), self::$TOP);
                    } elseif (((($t2 & self::$KIND)) != self::$BASE)) {
                        /* match: I_I */
                        $this->setLocalVariable(($arg - 1), ($t2 | self::$TOP_IF_LONG_OR_DOUBLE));
                    }
                }
                break;
            case Opcodes::IASTORE:
            case Opcodes::BASTORE:
            case Opcodes::CASTORE:
            case Opcodes::SASTORE:
            case Opcodes::FASTORE:
            case Opcodes::AASTORE:
                $this->popElements(3);
                break;
            case Opcodes::LASTORE:
            case Opcodes::DASTORE:
                $this->popElements(4);
                break;
            case Opcodes::POP:
            case Opcodes::IFEQ:
            case Opcodes::IFNE:
            case Opcodes::IFLT:
            case Opcodes::IFGE:
            case Opcodes::IFGT:
            case Opcodes::IFLE:
            case Opcodes::IRETURN:
            case Opcodes::FRETURN:
            case Opcodes::ARETURN:
            case Opcodes::TABLESWITCH:
            case Opcodes::LOOKUPSWITCH:
            case Opcodes::ATHROW:
            case Opcodes::MONITORENTER:
            case Opcodes::MONITOREXIT:
            case Opcodes::IFNULL:
            case Opcodes::IFNONNULL:
                $this->popElements(1);
                break;
            case Opcodes::POP2:
            case Opcodes::IF_ICMPEQ:
            case Opcodes::IF_ICMPNE:
            case Opcodes::IF_ICMPLT:
            case Opcodes::IF_ICMPGE:
            case Opcodes::IF_ICMPGT:
            case Opcodes::IF_ICMPLE:
            case Opcodes::IF_ACMPEQ:
            case Opcodes::IF_ACMPNE:
            case Opcodes::LRETURN:
            case Opcodes::DRETURN:
                $this->popElements(2);
                break;
            case Opcodes::DUP:
                $t1 = $this->pop();
                /* match: I */
                $this->pushType($t1);
                /* match: I */
                $this->pushType($t1);
                break;
            case Opcodes::DUP_X1:
                $t1 = $this->pop();
                $t2 = $this->pop();
                /* match: I */
                $this->pushType($t1);
                /* match: I */
                $this->pushType($t2);
                /* match: I */
                $this->pushType($t1);
                break;
            case Opcodes::DUP_X2:
                $t1 = $this->pop();
                $t2 = $this->pop();
                $t3 = $this->pop();
                /* match: I */
                $this->pushType($t1);
                /* match: I */
                $this->pushType($t3);
                /* match: I */
                $this->pushType($t2);
                /* match: I */
                $this->pushType($t1);
                break;
            case Opcodes::DUP2:
                $t1 = $this->pop();
                $t2 = $this->pop();
                /* match: I */
                $this->pushType($t2);
                /* match: I */
                $this->pushType($t1);
                /* match: I */
                $this->pushType($t2);
                /* match: I */
                $this->pushType($t1);
                break;
            case Opcodes::DUP2_X1:
                $t1 = $this->pop();
                $t2 = $this->pop();
                $t3 = $this->pop();
                /* match: I */
                $this->pushType($t2);
                /* match: I */
                $this->pushType($t1);
                /* match: I */
                $this->pushType($t3);
                /* match: I */
                $this->pushType($t2);
                /* match: I */
                $this->pushType($t1);
                break;
            case Opcodes::DUP2_X2:
                $t1 = $this->pop();
                $t2 = $this->pop();
                $t3 = $this->pop();
                $t4 = $this->pop();
                /* match: I */
                $this->pushType($t2);
                /* match: I */
                $this->pushType($t1);
                /* match: I */
                $this->pushType($t4);
                /* match: I */
                $this->pushType($t3);
                /* match: I */
                $this->pushType($t2);
                /* match: I */
                $this->pushType($t1);
                break;
            case Opcodes::SWAP:
                $t1 = $this->pop();
                $t2 = $this->pop();
                /* match: I */
                $this->pushType($t1);
                /* match: I */
                $this->pushType($t2);
                break;
            case Opcodes::IADD:
            case Opcodes::ISUB:
            case Opcodes::IMUL:
            case Opcodes::IDIV:
            case Opcodes::IREM:
            case Opcodes::IAND:
            case Opcodes::IOR:
            case Opcodes::IXOR:
            case Opcodes::ISHL:
            case Opcodes::ISHR:
            case Opcodes::IUSHR:
            case Opcodes::L2I:
            case Opcodes::D2I:
            case Opcodes::FCMPL:
            case Opcodes::FCMPG:
                $this->popElements(2);
                /* match: I */
                $this->pushType(self::$INTEGER);
                break;
            case Opcodes::LADD:
            case Opcodes::LSUB:
            case Opcodes::LMUL:
            case Opcodes::LDIV:
            case Opcodes::LREM:
            case Opcodes::LAND:
            case Opcodes::LOR:
            case Opcodes::LXOR:
                $this->popElements(4);
                /* match: I */
                $this->pushType(self::$LONG);
                /* match: I */
                $this->pushType(self::$TOP);
                break;
            case Opcodes::FADD:
            case Opcodes::FSUB:
            case Opcodes::FMUL:
            case Opcodes::FDIV:
            case Opcodes::FREM:
            case Opcodes::L2F:
            case Opcodes::D2F:
                $this->popElements(2);
                /* match: I */
                $this->pushType(self::$FLOAT);
                break;
            case Opcodes::DADD:
            case Opcodes::DSUB:
            case Opcodes::DMUL:
            case Opcodes::DDIV:
            case Opcodes::DREM:
                $this->popElements(4);
                /* match: I */
                $this->pushType(self::$DOUBLE);
                /* match: I */
                $this->pushType(self::$TOP);
                break;
            case Opcodes::LSHL:
            case Opcodes::LSHR:
            case Opcodes::LUSHR:
                $this->popElements(3);
                /* match: I */
                $this->pushType(self::$LONG);
                /* match: I */
                $this->pushType(self::$TOP);
                break;
            case Opcodes::IINC:
                /* match: I_I */
                $this->setLocalVariable($arg, self::$INTEGER);
                break;
            case Opcodes::I2L:
            case Opcodes::F2L:
                $this->popElements(1);
                /* match: I */
                $this->pushType(self::$LONG);
                /* match: I */
                $this->pushType(self::$TOP);
                break;
            case Opcodes::I2F:
                $this->popElements(1);
                /* match: I */
                $this->pushType(self::$FLOAT);
                break;
            case Opcodes::I2D:
            case Opcodes::F2D:
                $this->popElements(1);
                /* match: I */
                $this->pushType(self::$DOUBLE);
                /* match: I */
                $this->pushType(self::$TOP);
                break;
            case Opcodes::F2I:
            case Opcodes::ARRAYLENGTH:
            case Opcodes::INSTANCEOF_:
                $this->popElements(1);
                /* match: I */
                $this->pushType(self::$INTEGER);
                break;
            case Opcodes::LCMP:
            case Opcodes::DCMPL:
            case Opcodes::DCMPG:
                $this->popElements(4);
                /* match: I */
                $this->pushType(self::$INTEGER);
                break;
            case Opcodes::JSR:
            case Opcodes::RET:
                throw new \Kambo\Karsk\Exception\RuntimeException(
                    'JSR/RET are not supported with computeFrames option'
                );
            case Opcodes::GETSTATIC:
                /* match: ClassWriter_String */
                $this->pushDescription($cw, $item->strVal3);
                break;
            case Opcodes::PUTSTATIC:
                $this->popElements($item->strVal3);
                break;
            case Opcodes::GETFIELD:
                $this->popElements(1);
                /* match: ClassWriter_String */
                $this->pushDescription($cw, $item->strVal3);
                break;
            case Opcodes::PUTFIELD:
                $this->popElements($item->strVal3);
                $this->pop();
                break;
            case Opcodes::INVOKEVIRTUAL:
            case Opcodes::INVOKESPECIAL:
            case Opcodes::INVOKESTATIC:
            case Opcodes::INVOKEINTERFACE:
                $this->popElements($item->strVal3);
                if (($opcode != Opcodes::INVOKESTATIC)) {
                    $t1 = $this->pop();
                    if ((($opcode == Opcodes::INVOKESPECIAL) . ($item->strVal2->charAt(0) . '<'))) {
                        /* match: I */
                        $this->addType($t1);
                    }
                }
                /* match: ClassWriter_String */
                $this->pushDescription($cw, $item->strVal3);
                break;
            case Opcodes::INVOKEDYNAMIC:
                $this->popElements($item->strVal2);
                /* match: ClassWriter_String */
                $this->pushDescription($cw, $item->strVal2);
                break;
            case Opcodes::NEW_:
                /* match: I */
                $this->pushType((self::$UNINITIALIZED | $cw->addUninitializedType($item->strVal1, $arg)));
                break;
            case Opcodes::NEWARRAY:
                $this->pop();
                switch ($arg) {
                    case Opcodes::T_BOOLEAN:
                        /* match: I */
                        $this->pushType((self::$ARRAY_OF | self::$BOOLEAN));
                        break;
                    case Opcodes::T_CHAR:
                        /* match: I */
                        $this->pushType((self::$ARRAY_OF | self::$CHAR));
                        break;
                    case Opcodes::T_BYTE:
                        /* match: I */
                        $this->pushType((self::$ARRAY_OF | self::$BYTE));
                        break;
                    case Opcodes::T_SHORT:
                        /* match: I */
                        $this->pushType((self::$ARRAY_OF | self::$SHORT));
                        break;
                    case Opcodes::T_INT:
                        /* match: I */
                        $this->pushType((self::$ARRAY_OF | self::$INTEGER));
                        break;
                    case Opcodes::T_FLOAT:
                        /* match: I */
                        $this->pushType((self::$ARRAY_OF | self::$FLOAT));
                        break;
                    case Opcodes::T_DOUBLE:
                        /* match: I */
                        $this->pushType((self::$ARRAY_OF | self::$DOUBLE));
                        break;
                    default:
                        /* match: I */
                        $this->pushType((self::$ARRAY_OF | self::$LONG));
                        break;
                }
                break;
            case Opcodes::ANEWARRAY:
                $s = $item->strVal1;
                $this->pop();
                if (($s->charAt(0) . '[')) {
                    /* match: ClassWriter_String */
                    $this->pushDescription($cw, ('[' . $s));
                } else {
                    /* match: I */
                    $this->pushType(((self::$ARRAY_OF | self::$OBJECT) | $cw->addType($s)));
                }
                break;
            case Opcodes::CHECKCAST:
                $s = $item->strVal1;
                $this->pop();
                if (($s->charAt(0) . '[')) {
                    /* match: ClassWriter_String */
                    $this->pushDescription($cw, $s);
                } else {
                    /* match: I */
                    $this->pushType((self::$OBJECT | $cw->addType($s)));
                }
                break;
            default:
                $this->popElements($arg);
                /* match: ClassWriter_String */
                $this->pushDescription($cw, $item->strVal1);
                break;
        }
    }

    /**
     * Merges the input frame of the given basic block with the input and output
     * frames of this basic block. Returns <tt>true</tt> if the input frame of
     * the given label has been changed by this operation.
     *
     * @param ClassWriter $cw    the ClassWriter to which this label belongs.
     * @param Frame       $frame the basic block whose input frame must be updated.
     * @param int         $edge  the kind of the {@link Edge} between this label and 'label'. See {@link Edge#info}.
     *
     * @return bool <tt>true</tt> if the input frame of the given label has been
     *              changed by this operation.
     */
    public function mergeFrame(ClassWriter $cw, Frame  $frame, int $edge) : bool
    {
        $changed =  false;
        $i = null;
        $s = null;
        $dim = null;
        $kind = null;
        $t = null;
        $nLocal = count($this->inputLocals) /*from: inputLocals.length*/;
        $nStack = count($this->inputStack) /*from: inputStack.length*/;
        if (($frame->inputLocals == null)) {
            $frame->inputLocals = [];
            $changed =  true ;
        }

        for ($i = 0; ($i < $nLocal); ++$i) {
            if ((($this->outputLocals != null) && ($i < count($this->outputLocals) /*from: outputLocals.length*/))) {
                $s = $this->outputLocals[$i];
                if (($s == 0)) {
                    $t = $this->inputLocals[$i];
                } else {
                    $dim = ($s & self::$DIM);
                    $kind = ($s & self::$KIND);
                    if (($kind == self::$BASE)) {
                        $t = $s;
                    } else {
                        if (($kind == self::$LOCAL)) {
                            $t = ($dim + $this->inputLocals[($s & self::$VALUE)]);
                        } else {
                            $t = ($dim + $this->inputStack[($nStack - (($s & self::$VALUE)))]);
                        }
                        if ((((($s & self::$TOP_IF_LONG_OR_DOUBLE)) != 0)
                            && ((($t == self::$LONG) || ($t == self::$DOUBLE))))
                        ) {
                            $t = self::$TOP;
                        }
                    }
                }
            } else {
                $t = $this->inputLocals[$i];
            }
            if (($this->initializations != null)) {
                /* match: ClassWriter_I */
                $t = $this->replaceType($cw, $t);
            }
            /* match: ClassWriter_I_aI_I */
            $changed |= $this->merge($cw, $t, $frame->inputLocals, $i);
        }

        if (($edge > 0)) {
            for ($i = 0; ($i < $nLocal); ++$i) {
                $t = $this->inputLocals[$i];
                /* match: ClassWriter_I_aI_I */
                $changed |= $this->merge($cw, $t, $frame->inputLocals, $i);
            }
            if (($frame->inputStack == null)) {
                $frame->inputStack = [];
                $changed =  true ;
            }
            /* match: ClassWriter_I_aI_I */
            $changed |= $this->merge($cw, $edge, $frame->inputStack, 0);
            return $changed;
        }

        $nInputStack = (count($this->inputStack) /*from: inputStack.length*/ + $this->owner->inputStackTop);
        if (($frame->inputStack == null)) {
            $frame->inputStack = [];
            $changed =  true ;
        }

        for ($i = 0; ($i < $nInputStack); ++$i) {
            $t = $this->inputStack[$i];
            if (($this->initializations != null)) {
                /* match: ClassWriter_I */
                $t = $this->replaceType($cw, $t);
            }
            /* match: ClassWriter_I_aI_I */
            $changed |= $this->merge($cw, $t, $frame->inputStack, $i);
        }

        for ($i = 0; ($i < $this->outputStackTop); ++$i) {
            $s = $this->outputStack[$i];
            $dim = ($s & self::$DIM);
            $kind = ($s & self::$KIND);
            if (($kind == self::$BASE)) {
                $t = $s;
            } else {
                if (($kind == self::$LOCAL)) {
                    $t = ($dim + $this->inputLocals[($s & self::$VALUE)]);
                } else {
                    $t = ($dim + $this->inputStack[($nStack - (($s & self::$VALUE)))]);
                }
                if (((($s & self::$TOP_IF_LONG_OR_DOUBLE)) != 0) && ((($t == self::$LONG) || ($t == self::$DOUBLE)))) {
                    $t = self::$TOP;
                }
            }
            if (($this->initializations != null)) {
                /* match: ClassWriter_I */
                $t = $this->replaceType($cw, $t);
            }
            /* match: ClassWriter_I_aI_I */
            $changed |= $this->merge($cw, $t, $frame->inputStack, ($nInputStack + $i));
        }

        return $changed;
    }

    /**
     * Merges the type at the given index in the given type array with the given
     * type. Returns <tt>true</tt> if the type array has been modified by this
     * operation.
     *
     * @param ClassWriter $cw    the ClassWriter to which this label belongs.
     * @param int         $t     the type with which the type array element must be merged.
     * @param array       $types an array of types.
     * @param int         $index the index of the type that must be merged in 'types'.
     *
     * @return bool <tt>true</tt> if the type array has been modified by this operation.
     */
    public static function merge(ClassWriter $cw, int $t, array $types, int $index) : bool
    {
        $u = $types[$index];
        if (($u == $t)) {
            return  false ;
        }
        if (((($t & ~self::$DIM)) == self::$NULL)) {
            if (($u == self::$NULL)) {
                return  false ;
            }
            $t = self::$NULL;
        }
        if (($u == 0)) {
            $types[$index] = $t;
            return  true ;
        }
        $v = null;
        if ((((($u & self::$BASE_KIND)) == self::$OBJECT) || ((($u & self::$DIM)) != 0))) {
            if (($t == self::$NULL)) {
                return  false ;
            } elseif (((($t & ((self::$DIM | self::$BASE_KIND)))) == (($u & ((self::$DIM | self::$BASE_KIND)))))) {
                if (((($u & self::$BASE_KIND)) == self::$OBJECT)) {
                    $v = (((($t & self::$DIM))
                            | self::$OBJECT)
                        | $cw->getMergedType(($t & self::$BASE_VALUE), ($u & self::$BASE_VALUE)));
                } else {
                    $vdim = (self::$ELEMENT_OF + (($u & self::$DIM)));
                    $v = (($vdim | self::$OBJECT) | $cw->addType('java/lang/Object'));
                }
            } elseif ((((($t & self::$BASE_KIND)) == self::$OBJECT) || ((($t & self::$DIM)) != 0))) {
                $tdim = ((( (((((($t & self::$DIM)) == 0) || ((($t & self::$BASE_KIND)) == self::$OBJECT))))
                        ? 0 : self::$ELEMENT_OF )) + (($t & self::$DIM)));
                $udim = (((($u & self::$DIM) == 0) || (($u & self::$BASE_KIND) == self::$OBJECT))
                        ? 0 : self::$ELEMENT_OF ) + (($u & self::$DIM));
                $v = ((min($tdim, $udim) | self::$OBJECT) | $cw->addType('java/lang/Object'));
            } else {
                $v = self::$TOP;
            }
        } elseif (($u == self::$NULL)) {
            $v = ( ((((($t & self::$BASE_KIND)) == self::$OBJECT) || ((($t & self::$DIM)) != 0))) ? $t : self::$TOP );
        } else {
            $v = self::$TOP;
        }
        if (($u != $v)) {
            $types[$index] = $v;
            return  true ;
        }
        return  false ;
    }

    private static function charAt($str, $pos)
    {
        return $str[$pos];
    }
}

Frame::__staticinit(); // initialize static vars for this class on load
