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
 * @author Eric Bruneton
 * @author Bohuslav Simek <bohuslav@simek.si>
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

    /**
     * Computes the stack size variation corresponding to each JVM instruction.
     *
     * @return void
     */
    public static function calculate() : void
    {
        $size = [];
        $s = "EFFFFFFFFGGFFFGGFFFEEFGFGFEEEEEEEEEEEEEEEEEEEEDEDEDDDDD"
        . "CDCDEEEEEEEEEEEEEEEEEEEEBABABBBBDCFFFGGGEDCDCDCDCDCDCDCDCD"
        . "CDCEEEEDDDDDDDCDCDCEFEFDDEEFFDEDEEEBDDBBDDDDDDCCCCCCCCEFED"
        . "DDCDCDEEEEEEEEEEFEEEEEEDDEEDDEE";

        for ($i = 0; $i < 202; ++$i) {
            $size[$i] = ord(self::charAt($s, $i)) - ord('E');
        }
    }

    private static function charAt($str, $pos)
    {
        return $str{$pos};
    }
}
