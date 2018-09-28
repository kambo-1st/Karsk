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
 * A visitor to visit a Java method. The methods of this class must be called in
 * the following order: ( <tt>visitParameter</tt> )* [
 * <tt>visitAnnotationDefault</tt> ] ( <tt>visitAnnotation</tt> |
 * <tt>visitParameterAnnotation</tt> <tt>visitTypeAnnotation</tt> |
 * <tt>visitAttribute</tt> )* [ <tt>visitCode</tt> ( <tt>visitFrame</tt> |
 * <tt>visit<i>X</i>Insn</tt> | <tt>visitLabel</tt> |
 * <tt>visitInsnAnnotation</tt> | <tt>visitTryCatchBlock</tt> |
 * <tt>visitTryCatchAnnotation</tt> | <tt>visitLocalVariable</tt> |
 * <tt>visitLocalVariableAnnotation</tt> | <tt>visitLineNumber</tt> )*
 * <tt>visitMaxs</tt> ] <tt>visitEnd</tt>. In addition, the
 * <tt>visit<i>X</i>Insn</tt> and <tt>visitLabel</tt> methods must be called in
 * the sequential order of the bytecode instructions of the visited code,
 * <tt>visitInsnAnnotation</tt> must be called <i>after</i> the annotated
 * instruction, <tt>visitTryCatchBlock</tt> must be called <i>before</i> the
 * labels passed as arguments have been visited,
 * <tt>visitTryCatchBlockAnnotation</tt> must be called <i>after</i> the
 * corresponding try catch block has been visited, and the
 * <tt>visitLocalVariable</tt>, <tt>visitLocalVariableAnnotation</tt> and
 * <tt>visitLineNumber</tt> methods must be called <i>after</i> the labels
 * passed as arguments have been visited.
 *
 * @author  Eric Bruneton
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class MethodVisitor
{
    /**
     * The ASM API version implemented by this visitor. The value of this field
     * must be one of {@link Opcodes#ASM4} or {@link Opcodes#ASM5}.
     *
     * @var int
     */
    public $api;

    /**
     * The method visitor to which this visitor must delegate method calls. May
     * be null.
     *
     * @var MethodVisitor
     */
    public $mv;

    /**
     * Constructs a new {@link MethodVisitor}.
     *
     * @param string        $api
     *                      the ASM API version implemented by this visitor. Must be one
     *                      of {@link Opcodes#ASM4} or {@link Opcodes#ASM5}.
     * @param MethodVisitor $mv
     *                      the method visitor to which this visitor must delegate method
     *                      calls. May be null.
     */
    public function __construct($api, $mv = null)
    {
        if ($api != Opcodes::ASM4 && ($api != Opcodes::ASM5)) {
            throw new IllegalArgumentException();
        }

        $this->api = $api;
        $this->mv  = $mv;
    }

    /**
     * Visits a parameter of this method.
     *
     * @param string $name
     *               parameter name or null if none is provided.
     * @param int    $access
     *               the parameter's access flags, only <tt>ACC_FINAL</tt>,
     *               <tt>ACC_SYNTHETIC</tt> or/and <tt>ACC_MANDATED</tt> are
     *               allowed (see {@link Opcodes}).
     */
    public function visitParameter($name, $access)
    {
        if ($this->api < Opcodes::ASM5) {
            throw new \RuntimeException();
        }

        if ($this->mv != null) {
            $this->mv->visitParameter($name, $access);
        }
    }

    /**
     * Visits the default value of this annotation interface method.
     *
     * @return AnnotationVisitor a visitor to the visit the actual default value of this
     *                           annotation interface method, or <tt>null</tt> if this visitor is
     *                           not interested in visiting this default value. The 'name'
     *                           parameters passed to the methods of this annotation visitor are
     *                           ignored. Moreover, exacly one visit method must be called on this
     *                           annotation visitor, followed by visitEnd.
     */
    public function visitAnnotationDefault()
    {
        if ($this->mv != null) {
            return $this->mv->visitAnnotationDefault();
        }

        return null;
    }

    /**
     * Visits an annotation of this method.
     *
     * @param string $desc
     *               the class descriptor of the annotation class.
     * @param bool   $visible
     *               <tt>true</tt> if the annotation is visible at runtime.
     *
     * @return AnnotationVisitor a visitor to visit the annotation values, or <tt>null</tt> if
     *                           this visitor is not interested in visiting this annotation.
     */
    public function visitAnnotation($desc, $visible)
    {
        if ($this->mv != null) {
            return $this->mv->visitAnnotation($desc, $visible);
        }

        return null;
    }

    /**
     * Visits an annotation on a type in the method signature.
     *
     * @param int      $typeRef
     *                 a reference to the annotated type. The sort of this type
     *                 reference must be {@link TypeReference#METHOD_TYPE_PARAMETER
     *                 METHOD_TYPE_PARAMETER},
     *                 {@link TypeReference#METHOD_TYPE_PARAMETER_BOUND
     *                 METHOD_TYPE_PARAMETER_BOUND},
     *                 {@link TypeReference#METHOD_RETURN METHOD_RETURN},
     *                 {@link TypeReference#METHOD_RECEIVER METHOD_RECEIVER},
     *                 {@link TypeReference#METHOD_FORMAL_PARAMETER
     *                 METHOD_FORMAL_PARAMETER} or {@link TypeReference#THROWS
     *                 THROWS}. See {@link TypeReference}.
     * @param TypePath $typePath
     *                 the path to the annotated type argument, wildcard bound, array
     *                 element type, or static inner type within 'typeRef'. May be
     *                 <tt>null</tt> if the annotation targets 'typeRef' as a whole.
     * @param string   $desc
     *                 the class descriptor of the annotation class.
     * @param bool     $visible
     *                 <tt>true</tt> if the annotation is visible at runtime.
     *
     * @return AnnotationVisitor a visitor to visit the annotation values, or <tt>null</tt> if
     *                           this visitor is not interested in visiting this annotation.
     */
    public function visitTypeAnnotation($typeRef, $typePath, $desc, $visible)
    {
        if ($this->api < Opcodes::ASM5) {
            throw new RuntimeException();
        }

        if ($this->mv != null) {
            return $this->mv->visitTypeAnnotation($typeRef, $typePath, $desc, $visible);
        }

        return null;
    }

    /**
     * Visits an annotation of a parameter this method.
     *
     * @param int    $parameter
     *               the parameter index.
     * @param string $desc
     *               the class descriptor of the annotation class.
     * @param bool   $visible
     *               <tt>true</tt> if the annotation is visible at runtime.
     *
     * @return AnnotationVisitor a visitor to visit the annotation values, or <tt>null</tt> if
     *                           this visitor is not interested in visiting this annotation.
     */
    public function visitParameterAnnotation($parameter, $desc, $visible)
    {
        if ($this->mv != null) {
            return $this->mv->visitParameterAnnotation($parameter, $desc, $visible);
        }

        return null;
    }

    /**
     * Visits a non standard attribute of this method.
     *
     * @param Attribute $attr an attribute.
     *
     * @return void
     */
    public function visitAttribute($attr) // [Attribute attr]
    {
        if ($this->mv != null) {
            $this->mv->visitAttribute($attr);
        }
    }

    /**
     * Starts the visit of the method's code, if any (i.e. non abstract method).
     *
     * @return void
     */
    public function visitCode()
    {
        if ($this->mv != null) {
            $this->mv->visitCode();
        }
    }

    /**
     * Visits the current state of the local variables and operand stack
     * elements. This method must(*) be called <i>just before</i> any
     * instruction <b>i</b> that follows an unconditional branch instruction
     * such as GOTO or THROW, that is the target of a jump instruction, or that
     * starts an exception handler block. The visited types must describe the
     * values of the local variables and of the operand stack elements <i>just
     * before</i> <b>i</b> is executed.<br>
     * <br>
     * (*) this is mandatory only for classes whose version is greater than or
     * equal to {@link Opcodes#V1_6 V1_6}. <br>
     * <br>
     * The frames of a method must be given either in expanded form, or in
     * compressed form (all frames must use the same format, i.e. you must not
     * mix expanded and compressed frames within a single method):
     * <ul>
     * <li>In expanded form, all frames must have the F_NEW type.</li>
     * <li>In compressed form, frames are basically "deltas" from the state of
     * the previous frame:
     * <ul>
     * <li>{@link Opcodes#F_SAME} representing frame with exactly the same
     * locals as the previous frame and with the empty stack.</li>
     * <li>{@link Opcodes#F_SAME1} representing frame with exactly the same
     * locals as the previous frame and with single value on the stack (
     * <code>nStack</code> is 1 and <code>stack[0]</code> contains value for the
     * type of the stack item).</li>
     * <li>{@link Opcodes#F_APPEND} representing frame with current locals are
     * the same as the locals in the previous frame, except that additional
     * locals are defined (<code>nLocal</code> is 1, 2 or 3 and
     * <code>local</code> elements contains values representing added types).</li>
     * <li>{@link Opcodes#F_CHOP} representing frame with current locals are the
     * same as the locals in the previous frame, except that the last 1-3 locals
     * are absent and with the empty stack (<code>nLocals</code> is 1, 2 or 3).</li>
     * <li>{@link Opcodes#F_FULL} representing complete frame data.</li>
     * </ul>
     * </li>
     * </ul>
     * <br>
     * In both cases the first frame, corresponding to the method's parameters
     * and access flags, is implicit and must not be visited. Also, it is
     * illegal to visit two or more frames for the same code location (i.e., at
     * least one instruction must be visited between two calls to visitFrame).
     *
     * @param int      $type
     *                 the type of this stack map frame. Must be
     *                 {@link Opcodes#F_NEW} for expanded frames, or
     *                 {@link Opcodes#F_FULL}, {@link Opcodes#F_APPEND},
     *                 {@link Opcodes#F_CHOP}, {@link Opcodes#F_SAME} or
     *                 {@link Opcodes#F_APPEND}, {@link Opcodes#F_SAME1} for
     *                 compressed frames.
     * @param int      $nLocal
     *                 the number of local variables in the visited frame.
     * @param Object[] $local
     *                 the local variable types in this frame. This array must not be
     *                 modified. Primitive types are represented by
     *                 {@link Opcodes#TOP}, {@link Opcodes#INTEGER},
     *                 {@link Opcodes#FLOAT}, {@link Opcodes#LONG},
     *                 {@link Opcodes#DOUBLE},{@link Opcodes#NULL} or
     *                 {@link Opcodes#UNINITIALIZED_THIS} (long and double are
     *                 represented by a single element). Reference types are
     *                 represented by String objects (representing internal names),
     *                 and uninitialized types by Label objects (this label
     *                 designates the NEW instruction that created this uninitialized
     *                 value).
     * @param int      $nStack
     *                 the number of operand stack elements in the visited frame.
     * @param Object[] $stack
     *                 the operand stack types in this frame. This array must not be
     *                 modified. Its content has the same format as the "local" array.
     *
     * @return void
     */
    public function visitFrame($type, $nLocal, $local, $nStack, $stack)
    {
        if ($this->mv != null) {
            $this->mv->visitFrame($type, $nLocal, $local, $nStack, $stack);
        }
    }

    /**
     * Visits a zero operand instruction.
     *
     * @param int $opcode
     *            the opcode of the instruction to be visited. This opcode is
     *            either NOP, ACONST_NULL, ICONST_M1, ICONST_0, ICONST_1,
     *            ICONST_2, ICONST_3, ICONST_4, ICONST_5, LCONST_0, LCONST_1,
     *            FCONST_0, FCONST_1, FCONST_2, DCONST_0, DCONST_1, IALOAD,
     *            LALOAD, FALOAD, DALOAD, AALOAD, BALOAD, CALOAD, SALOAD,
     *            IASTORE, LASTORE, FASTORE, DASTORE, AASTORE, BASTORE, CASTORE,
     *            SASTORE, POP, POP2, DUP, DUP_X1, DUP_X2, DUP2, DUP2_X1,
     *            DUP2_X2, SWAP, IADD, LADD, FADD, DADD, ISUB, LSUB, FSUB, DSUB,
     *            IMUL, LMUL, FMUL, DMUL, IDIV, LDIV, FDIV, DDIV, IREM, LREM,
     *            FREM, DREM, INEG, LNEG, FNEG, DNEG, ISHL, LSHL, ISHR, LSHR,
     *            IUSHR, LUSHR, IAND, LAND, IOR, LOR, IXOR, LXOR, I2L, I2F, I2D,
     *            L2I, L2F, L2D, F2I, F2L, F2D, D2I, D2L, D2F, I2B, I2C, I2S,
     *            LCMP, FCMPL, FCMPG, DCMPL, DCMPG, IRETURN, LRETURN, FRETURN,
     *            DRETURN, ARETURN, RETURN, ARRAYLENGTH, ATHROW, MONITORENTER,
     *            or MONITOREXIT.
     *
     * @return void
     */
    public function visitInsn($opcode)
    {
        if ($this->mv != null) {
            $this->mv->visitInsn($opcode);
        }
    }

    /**
     * Visits an instruction with a single int operand.
     *
     * @param int $opcode
     *            the opcode of the instruction to be visited. This opcode is
     *            either BIPUSH, SIPUSH or NEWARRAY.
     * @param int $operand
     *            the operand of the instruction to be visited.<br>
     *            When opcode is BIPUSH, operand value should be between
     *            Byte.MIN_VALUE and Byte.MAX_VALUE.<br>
     *            When opcode is SIPUSH, operand value should be between
     *            Short.MIN_VALUE and Short.MAX_VALUE.<br>
     *            When opcode is NEWARRAY, operand value should be one of
     *            {@link Opcodes#T_BOOLEAN}, {@link Opcodes#T_CHAR},
     *            {@link Opcodes#T_FLOAT}, {@link Opcodes#T_DOUBLE},
     *            {@link Opcodes#T_BYTE}, {@link Opcodes#T_SHORT},
     *            {@link Opcodes#T_INT} or {@link Opcodes#T_LONG}.
     *
     * @return void
     */
    public function visitIntInsn($opcode, $operand)
    {
        if ($this->mv != null) {
            $this->mv->visitIntInsn($opcode, $operand);
        }
    }

    /**
     * Visits a local variable instruction. A local variable instruction is an
     * instruction that loads or stores the value of a local variable.
     *
     * @param int $opcode
     *            the opcode of the local variable instruction to be visited.
     *            This opcode is either ILOAD, LLOAD, FLOAD, DLOAD, ALOAD,
     *            ISTORE, LSTORE, FSTORE, DSTORE, ASTORE or RET.
     * @param int $var
     *            the operand of the instruction to be visited. This operand is
     *            the index of a local variable.
     *
     * @return void
     */
    public function visitVarInsn($opcode, $var)
    {
        if ($this->mv != null) {
            $this->mv->visitVarInsn($opcode, $var);
        }
    }

    /**
     * Visits a type instruction. A type instruction is an instruction that
     * takes the internal name of a class as parameter.
     *
     * @param int    $opcode
     *               the opcode of the type instruction to be visited. This opcode
     *               is either NEW, ANEWARRAY, CHECKCAST or INSTANCEOF.
     * @param string $type
     *               the operand of the instruction to be visited. This operand
     *               must be the internal name of an object or array class (see
     *               {@link Type#getInternalName() getInternalName}).
     *
     * @return void
     */
    public function visitTypeInsn($opcode, $type)
    {
        if ($this->mv != null) {
            $this->mv->visitTypeInsn($opcode, $type);
        }
    }

    /**
     * Visits a field instruction. A field instruction is an instruction that
     * loads or stores the value of a field of an object.
     *
     * @param int    $opcode
     *               the opcode of the type instruction to be visited. This opcode
     *               is either GETSTATIC, PUTSTATIC, GETFIELD or PUTFIELD.
     * @param string $owner
     *               the internal name of the field's owner class (see
     *               {@link Type#getInternalName() getInternalName}).
     * @param string $name
     *               the field's name.
     * @param string $desc
     *               the field's descriptor (see {@link Type Type}).
     *
     * @return void
     */
    public function visitFieldInsn($opcode, $owner, $name, $desc)
    {
        if ($this->mv != null) {
            $this->mv->visitFieldInsn($opcode, $owner, $name, $desc);
        }
    }

    /**
     * Visits a method instruction. A method instruction is an instruction that
     * invokes a method.
     *
     * @param int    $opcode
     *               the opcode of the type instruction to be visited. This opcode
     *               is either INVOKEVIRTUAL, INVOKESPECIAL, INVOKESTATIC or
     *               INVOKEINTERFACE.
     * @param string $owner
     *               the internal name of the method's owner class (see
     *               {@link Type#getInternalName() getInternalName}).
     * @param string $name
     *               the method's name.
     * @param string $desc
     *               the method's descriptor (see {@link Type Type}).
     * @param bool   $itf
     *               if the method's owner class is an interface.
     *
     * @return void
     */
    public function visitMethodInsn($opcode, $owner, $name, $desc, $itf = null)
    {
        if ($itf == null) {
            $itf = ($opcode == Opcodes::INVOKEINTERFACE);
        }

        if ($this->api < Opcodes::ASM5) {
            if ($itf != ($opcode == Opcodes::INVOKEINTERFACE)) {
                throw new \InvalidArgumentException("INVOKESPECIAL/STATIC on interfaces require ASM 5");
            }

            $this->visitMethodInsn($opcode, $owner, $name, $desc, $itf);
            return ;
        }
        if ($this->mv != null) {
            $this->mv->visitMethodInsn($opcode, $owner, $name, $desc, $itf);
        }
    }

    /**
     * Visits an invokedynamic instruction.
     *
     * @param string   $name
     *                 the method's name.
     * @param string   $desc
     *                 the method's descriptor (see {@link Type Type}).
     * @param Handle   $bsm
     *                 the bootstrap method.
     * @param Object[] $bsmArgs
     *                 the bootstrap method constant arguments. Each argument must be
     *                 an {@link Integer}, {@link Float}, {@link Long},
     *                 {@link Double}, {@link String}, {@link Type} or {@link Handle}
     *                 value. This method is allowed to modify the content of the
     *                 array so a caller should expect that this array may change.
     *
     * @return void
     */
    public function visitInvokeDynamicInsn($name, $desc, $bsm, ...$bsmArgs)
    {
        if ($this->mv != null) {
            $this->mv->visitInvokeDynamicInsn($name, $desc, $bsm, ...$bsmArgs);
        }
    }

    /**
     * Visits a jump instruction. A jump instruction is an instruction that may
     * jump to another instruction.
     *
     * @param int   $opcode
     *              the opcode of the type instruction to be visited. This opcode
     *              is either IFEQ, IFNE, IFLT, IFGE, IFGT, IFLE, IF_ICMPEQ,
     *              IF_ICMPNE, IF_ICMPLT, IF_ICMPGE, IF_ICMPGT, IF_ICMPLE,
     *              IF_ACMPEQ, IF_ACMPNE, GOTO, JSR, IFNULL or IFNONNULL.
     * @param label $label
     *              the operand of the instruction to be visited. This operand is
     *              a label that designates the instruction to which the jump
     *              instruction may jump.
     *
     * @return void
     */
    public function visitJumpInsn($opcode, $label)
    {
        if ($this->mv != null) {
            $this->mv->visitJumpInsn($opcode, $label);
        }
    }

    public function visitLabel($label) // [Label label]
    {
        if (($this->mv != null)) {
            $this->mv->visitLabel($label);
        }
    }

    public function visitLdcInsn($cst) // [Object cst]
    {
        if (($this->mv != null)) {
            $this->mv->visitLdcInsn($cst);
        }
    }

    public function visitIincInsn($var, $increment) // [int var, int increment]
    {
        if (($this->mv != null)) {
            $this->mv->visitIincInsn($var, $increment);
        }
    }

    public function visitTableSwitchInsn($min, $max, $dflt, $labels) // [int min, int max, Label dflt, Label... labels]
    {
        if (($this->mv != null)) {
            $this->mv->visitTableSwitchInsn($min, $max, $dflt, $labels);
        }
    }

    public function visitLookupSwitchInsn($dflt, $keys, $labels) // [Label dflt, int[] keys, Label[] labels]
    {
        if (($this->mv != null)) {
            $this->mv->visitLookupSwitchInsn($dflt, $keys, $labels);
        }
    }

    public function visitMultiANewArrayInsn($desc, $dims) // [String desc, int dims]
    {
        if (($this->mv != null)) {
            $this->mv->visitMultiANewArrayInsn($desc, $dims);
        }
    }

    /**
     * Visits an annotation on an instruction. This method must be called just
     * <i>after</i> the annotated instruction. It can be called several times
     * for the same instruction.
     *
     * @param int      $typeRef
     *                 a reference to the annotated type. The sort of this type
     *                 reference must be {@link TypeReference#INSTANCEOF INSTANCEOF},
     *                 {@link TypeReference#NEW NEW},
     *                 {@link TypeReference#CONSTRUCTOR_REFERENCE
     *                 CONSTRUCTOR_REFERENCE}, {@link TypeReference#METHOD_REFERENCE
     *                 METHOD_REFERENCE}, {@link TypeReference#CAST CAST},
     *                 {@link TypeReference#CONSTRUCTOR_INVOCATION_TYPE_ARGUMENT
     *                 CONSTRUCTOR_INVOCATION_TYPE_ARGUMENT},
     *                 {@link TypeReference#METHOD_INVOCATION_TYPE_ARGUMENT
     *                 METHOD_INVOCATION_TYPE_ARGUMENT},
     *                 {@link TypeReference#CONSTRUCTOR_REFERENCE_TYPE_ARGUMENT
     *                 CONSTRUCTOR_REFERENCE_TYPE_ARGUMENT}, or
     *                 {@link TypeReference#METHOD_REFERENCE_TYPE_ARGUMENT
     *                 METHOD_REFERENCE_TYPE_ARGUMENT}. See {@link TypeReference}.
     * @param TypePath $typePath
     *                 the path to the annotated type argument, wildcard bound, array
     *                 element type, or static inner type within 'typeRef'. May be
     *                 <tt>null</tt> if the annotation targets 'typeRef' as a whole.
     * @param string   $desc
     *                 the class descriptor of the annotation class.
     * @param bool     $visible
     *                 <tt>true</tt> if the annotation is visible at runtime.
     *
     * @return AnnotationVisitor a visitor to visit the annotation values, or <tt>null</tt> if
     *                           this visitor is not interested in visiting this annotation.
     */
    public function visitInsnAnnotation($typeRef, $typePath, $desc, $visible)
    {
        if ($this->api < Opcodes::ASM5) {
            throw new RuntimeException();
        }

        if ($this->mv != null) {
            return $this->mv->visitInsnAnnotation($typeRef, $typePath, $desc, $visible);
        }

        return null;
    }

    /**
     * Visits a try catch block.
     *
     * @param Label  $start
     *               beginning of the exception handler's scope (inclusive).
     * @param Label  $end
     *               end of the exception handler's scope (exclusive).
     * @param Label  $handler
     *               beginning of the exception handler's code.
     * @param string $type
     *               internal name of the type of exceptions handled by the handler,
     *               or <tt>null</tt> to catch any exceptions (for "finally" blocks).
     *
     * @return void
     */
    public function visitTryCatchBlock($start, $end, $handler, $type)
    {
        if ($this->mv != null) {
            $this->mv->visitTryCatchBlock($start, $end, $handler, $type);
        }
    }

    /**
     * Visits an annotation on an exception handler type. This method must be
     * called <i>after</i> the {@link #visitTryCatchBlock} for the annotated
     * exception handler. It can be called several times for the same exception
     * handler.
     *
     * @param int      $typeRef
     *                 a reference to the annotated type. The sort of this type
     *                 reference must be {@link TypeReference#EXCEPTION_PARAMETER
     *                 EXCEPTION_PARAMETER}. See {@link TypeReference}.
     * @param TypePath $typePath
     *                 the path to the annotated type argument, wildcard bound, array
     *                 element type, or static inner type within 'typeRef'. May be
     *                 <tt>null</tt> if the annotation targets 'typeRef' as a whole.
     * @param string   $desc
     *                 the class descriptor of the annotation class.
     * @param bool     $visible
     *                 <tt>true</tt> if the annotation is visible at runtime.
     *
     * @return AnnotationVisitor a visitor to visit the annotation values, or <tt>null</tt> if
     *                           this visitor is not interested in visiting this annotation.
     */
    public function visitTryCatchAnnotation($typeRef, $typePath, $desc, $visible)
    {
        if ($this->api < Opcodes::ASM5) {
            throw new \RuntimeException();
        }

        if ($this->mv != null) {
            return $this->mv->visitTryCatchAnnotation($typeRef, $typePath, $desc, $visible);
        }

        return null;
    }

    /**
     * Visits a local variable declaration.
     *
     * @param string $name
     *               the name of a local variable.
     * @param string $desc
     *               the type descriptor of this local variable.
     * @param string $signature
     *               the type signature of this local variable. May be
     *               <tt>null</tt> if the local variable type does not use generic
     *               types.
     * @param Label  $start
     *               the first instruction corresponding to the scope of this local
     *               variable (inclusive).
     * @param Label  $end
     *               the last instruction corresponding to the scope of this local
     *               variable (exclusive).
     * @param int    $index
     *               the local variable's index.
     *
     * @return void
     */
    public function visitLocalVariable($name, $desc, $signature, $start, $end, $index)
    {
        if ($this->mv != null) {
            $this->mv->visitLocalVariable($name, $desc, $signature, $start, $end, $index);
        }
    }

    /**
     * Visits an annotation on a local variable type.
     *
     * @param int      $typeRef
     *                 a reference to the annotated type. The sort of this type
     *                 reference must be {@link TypeReference#LOCAL_VARIABLE
     *                 LOCAL_VARIABLE} or {@link TypeReference#RESOURCE_VARIABLE
     *                 RESOURCE_VARIABLE}. See {@link TypeReference}.
     * @param TypePath $typePath
     *                 the path to the annotated type argument, wildcard bound, array
     *                 element type, or static inner type within 'typeRef'. May be
     *                 <tt>null</tt> if the annotation targets 'typeRef' as a whole.
     * @param Label[]  $start
     *                 the fist instructions corresponding to the continuous ranges
     *                 that make the scope of this local variable (inclusive).
     * @param Label[]  $end
     *                 the last instructions corresponding to the continuous ranges
     *                 that make the scope of this local variable (exclusive). This
     *                 array must have the same size as the 'start' array.
     * @param int[]    $index
     *                 the local variable's index in each range. This array must have
     *                 the same size as the 'start' array.
     * @param string   $desc
     *                 the class descriptor of the annotation class.
     * @param bool     $visible
     *                 <tt>true</tt> if the annotation is visible at runtime.
     *
     * @return AnnotationVisitor a visitor to visit the annotation values, or <tt>null</tt> if
     *         t                 his visitor is not interested in visiting this annotation.
     */
    public function visitLocalVariableAnnotation($typeRef, $typePath, $start, $end, $index, $desc, $visible)
    {
        if ($this->api < Opcodes::ASM5) {
            throw new \RuntimeException();
        }

        if ($this->mv != null) {
            return $this->mv->visitLocalVariableAnnotation($typeRef, $typePath, $start, $end, $index, $desc, $visible);
        }

        return null;
    }

    /**
     * Visits a line number declaration.
     *
     * @param int   $line
     *              a line number. This number refers to the source file from
     *              which the class was compiled.
     * @param Label $start
     *              the first instruction corresponding to this line number.
     *
     * @return void
     */
    public function visitLineNumber($line, $start)
    {
        if ($this->mv != null) {
            $this->mv->visitLineNumber($line, $start);
        }
    }

    /**
     * Visits the maximum stack size and the maximum number of local variables
     * of the method.
     *
     * @param int $maxStack  maximum stack size of the method.
     * @param int $maxLocals maximum number of local variables for the method.
     *
     * @return void
     */
    public function visitMaxs($maxStack, $maxLocals)
    {
        if ($this->mv != null) {
            $this->mv->visitMaxs($maxStack, $maxLocals);
        }
    }

    /**
     * Visits the end of the method. This method, which is the last one to be
     * called, is used to inform the visitor that all the annotations and
     * attributes of the method have been visited.
     *
     * @return void
     */
    public function visitEnd()
    {
        if ($this->mv != null) {
            $this->mv->visitEnd();
        }
    }
}
