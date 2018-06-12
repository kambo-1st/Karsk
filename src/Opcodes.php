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
 * Defines the JVM opcodes, access flags and array type codes. This interface
 * does not define all the JVM opcodes because some opcodes are automatically
 * handled. For example, the xLOAD and xSTORE opcodes are automatically replaced
 * by xLOAD_n and xSTORE_n opcodes when possible. The xLOAD_n and xSTORE_n
 * opcodes are therefore not defined in this interface. Likewise for LDC,
 * automatically replaced by LDC_W or LDC2_W when necessary, WIDE, GOTO_W and
 * JSR_W.
 *
 * @author  Eric Bruneton
 * @author  Eugene Kuleshov
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class Opcodes
{
    // ASM API versions

    const ASM4 = 4 << 16 | 0 << 8 | 0;
    const ASM5 = 5 << 16 | 0 << 8 | 0;

    // versions

    const V1_1 = 3 << 16 | 45;
    const V1_2 = 0 << 16 | 46;
    const V1_3 = 0 << 16 | 47;
    const V1_4 = 0 << 16 | 48;
    const V1_5 = 0 << 16 | 49;
    const V1_6 = 0 << 16 | 50;
    const V1_7 = 0 << 16 | 51;
    const V1_8 = 0 << 16 | 52;

    // access flags

    const ACC_PUBLIC = 0x0001; // class, field, method
    const ACC_PRIVATE = 0x0002; // class, field, method
    const ACC_PROTECTED = 0x0004; // class, field, method
    const ACC_STATIC = 0x0008; // field, method
    const ACC_FINAL = 0x0010; // class, field, method, parameter
    const ACC_SUPER = 0x0020; // class
    const ACC_SYNCHRONIZED = 0x0020; // method
    const ACC_VOLATILE = 0x0040; // field
    const ACC_BRIDGE = 0x0040; // method
    const ACC_VARARGS = 0x0080; // method
    const ACC_TRANSIENT = 0x0080; // field
    const ACC_NATIVE = 0x0100; // method
    const ACC_INTERFACE = 0x0200; // class
    const ACC_ABSTRACT = 0x0400; // class, method
    const ACC_STRICT = 0x0800; // method
    const ACC_SYNTHETIC = 0x1000; // class, field, method, parameter
    const ACC_ANNOTATION = 0x2000; // class
    const ACC_ENUM = 0x4000; // class(?) field inner
    const ACC_MANDATED = 0x8000; // parameter

    // ASM specific pseudo access flags

    const ACC_DEPRECATED = 0x20000; // class, field, method

    // types for NEWARRAY

    const T_BOOLEAN = 4;
    const T_CHAR = 5;
    const T_FLOAT = 6;
    const T_DOUBLE = 7;
    const T_BYTE = 8;
    const T_SHORT = 9;
    const T_INT = 10;
    const T_LONG = 11;

    // tags for Handle

    const H_GETFIELD = 1;
    const H_GETSTATIC = 2;
    const H_PUTFIELD = 3;
    const H_PUTSTATIC = 4;
    const H_INVOKEVIRTUAL = 5;
    const H_INVOKESTATIC = 6;
    const H_INVOKESPECIAL = 7;
    const H_NEWINVOKESPECIAL = 8;
    const H_INVOKEINTERFACE = 9;

    // stack map frame types

    /**
     * Represents an expanded frame. See {@link ClassReader#EXPAND_FRAMES}.
     */
    const F_NEW = -1;

    /**
     * Represents a compressed frame with complete frame data.
     */
    const F_FULL = 0;

    /**
     * Represents a compressed frame where locals are the same as the locals in
     * the previous frame, except that additional 1-3 locals are defined, and
     * with an empty stack.
     */
    const F_APPEND = 1;

    /**
     * Represents a compressed frame where locals are the same as the locals in
     * the previous frame, except that the last 1-3 locals are absent and with
     * an empty stack.
     */
    const F_CHOP = 2;

    /**
     * Represents a compressed frame with exactly the same locals as the
     * previous frame and with an empty stack.
     */
    const F_SAME = 3;

    /**
     * Represents a compressed frame with exactly the same locals as the
     * previous frame and with a single value on the stack.
     */
    const F_SAME1 = 4;

    // opcodes // visit method (- = idem)

    const NOP = 0; // visitInsn
    const ACONST_NULL = 1; // -
    const ICONST_M1 = 2; // -
    const ICONST_0 = 3; // -
    const ICONST_1 = 4; // -
    const ICONST_2 = 5; // -
    const ICONST_3 = 6; // -
    const ICONST_4 = 7; // -
    const ICONST_5 = 8; // -
    const LCONST_0 = 9; // -
    const LCONST_1 = 10; // -
    const FCONST_0 = 11; // -
    const FCONST_1 = 12; // -
    const FCONST_2 = 13; // -
    const DCONST_0 = 14; // -
    const DCONST_1 = 15; // -
    const BIPUSH = 16; // visitIntInsn
    const SIPUSH = 17; // -
    const LDC = 18; // visitLdcInsn
    // const LDC_W = 19; // -
    // const LDC2_W = 20; // -
    const ILOAD = 21; // visitVarInsn
    const LLOAD = 22; // -
    const FLOAD = 23; // -
    const DLOAD = 24; // -
    const ALOAD = 25; // -
    // const ILOAD_0 = 26; // -
    // const ILOAD_1 = 27; // -
    // const ILOAD_2 = 28; // -
    // const ILOAD_3 = 29; // -
    // const LLOAD_0 = 30; // -
    // const LLOAD_1 = 31; // -
    // const LLOAD_2 = 32; // -
    // const LLOAD_3 = 33; // -
    // const FLOAD_0 = 34; // -
    // const FLOAD_1 = 35; // -
    // const FLOAD_2 = 36; // -
    // const FLOAD_3 = 37; // -
    // const DLOAD_0 = 38; // -
    // const DLOAD_1 = 39; // -
    // const DLOAD_2 = 40; // -
    // const DLOAD_3 = 41; // -
    // const ALOAD_0 = 42; // -
    // const ALOAD_1 = 43; // -
    // const ALOAD_2 = 44; // -
    // const ALOAD_3 = 45; // -
    const IALOAD = 46; // visitInsn
    const LALOAD = 47; // -
    const FALOAD = 48; // -
    const DALOAD = 49; // -
    const AALOAD = 50; // -
    const BALOAD = 51; // -
    const CALOAD = 52; // -
    const SALOAD = 53; // -
    const ISTORE = 54; // visitVarInsn
    const LSTORE = 55; // -
    const FSTORE = 56; // -
    const DSTORE = 57; // -
    const ASTORE = 58; // -
    // const ISTORE_0 = 59; // -
    // const ISTORE_1 = 60; // -
    // const ISTORE_2 = 61; // -
    // const ISTORE_3 = 62; // -
    // const LSTORE_0 = 63; // -
    // const LSTORE_1 = 64; // -
    // const LSTORE_2 = 65; // -
    // const LSTORE_3 = 66; // -
    // const FSTORE_0 = 67; // -
    // const FSTORE_1 = 68; // -
    // const FSTORE_2 = 69; // -
    // const FSTORE_3 = 70; // -
    // const DSTORE_0 = 71; // -
    // const DSTORE_1 = 72; // -
    // const DSTORE_2 = 73; // -
    // const DSTORE_3 = 74; // -
    // const ASTORE_0 = 75; // -
    // const ASTORE_1 = 76; // -
    // const ASTORE_2 = 77; // -
    // const ASTORE_3 = 78; // -
    const IASTORE = 79; // visitInsn
    const LASTORE = 80; // -
    const FASTORE = 81; // -
    const DASTORE = 82; // -
    const AASTORE = 83; // -
    const BASTORE = 84; // -
    const CASTORE = 85; // -
    const SASTORE = 86; // -
    const POP = 87; // -
    const POP2 = 88; // -
    const DUP = 89; // -
    const DUP_X1 = 90; // -
    const DUP_X2 = 91; // -
    const DUP2 = 92; // -
    const DUP2_X1 = 93; // -
    const DUP2_X2 = 94; // -
    const SWAP = 95; // -
    const IADD = 96; // -
    const LADD = 97; // -
    const FADD = 98; // -
    const DADD = 99; // -
    const ISUB = 100; // -
    const LSUB = 101; // -
    const FSUB = 102; // -
    const DSUB = 103; // -
    const IMUL = 104; // -
    const LMUL = 105; // -
    const FMUL = 106; // -
    const DMUL = 107; // -
    const IDIV = 108; // -
    const LDIV = 109; // -
    const FDIV = 110; // -
    const DDIV = 111; // -
    const IREM = 112; // -
    const LREM = 113; // -
    const FREM = 114; // -
    const DREM = 115; // -
    const INEG = 116; // -
    const LNEG = 117; // -
    const FNEG = 118; // -
    const DNEG = 119; // -
    const ISHL = 120; // -
    const LSHL = 121; // -
    const ISHR = 122; // -
    const LSHR = 123; // -
    const IUSHR = 124; // -
    const LUSHR = 125; // -
    const IAND = 126; // -
    const LAND = 127; // -
    const IOR = 128; // -
    const LOR = 129; // -
    const IXOR = 130; // -
    const LXOR = 131; // -
    const IINC = 132; // visitIincInsn
    const I2L = 133; // visitInsn
    const I2F = 134; // -
    const I2D = 135; // -
    const L2I = 136; // -
    const L2F = 137; // -
    const L2D = 138; // -
    const F2I = 139; // -
    const F2L = 140; // -
    const F2D = 141; // -
    const D2I = 142; // -
    const D2L = 143; // -
    const D2F = 144; // -
    const I2B = 145; // -
    const I2C = 146; // -
    const I2S = 147; // -
    const LCMP = 148; // -
    const FCMPL = 149; // -
    const FCMPG = 150; // -
    const DCMPL = 151; // -
    const DCMPG = 152; // -
    const IFEQ = 153; // visitJumpInsn
    const IFNE = 154; // -
    const IFLT = 155; // -
    const IFGE = 156; // -
    const IFGT = 157; // -
    const IFLE = 158; // -
    const IF_ICMPEQ = 159; // -
    const IF_ICMPNE = 160; // -
    const IF_ICMPLT = 161; // -
    const IF_ICMPGE = 162; // -
    const IF_ICMPGT = 163; // -
    const IF_ICMPLE = 164; // -
    const IF_ACMPEQ = 165; // -
    const IF_ACMPNE = 166; // -
    const GOTO_ = 167; // -
    const JSR = 168; // -
    const RET = 169; // visitVarInsn
    const TABLESWITCH = 170; // visiTableSwitchInsn
    const LOOKUPSWITCH = 171; // visitLookupSwitch
    const IRETURN = 172; // visitInsn
    const LRETURN = 173; // -
    const FRETURN = 174; // -
    const DRETURN = 175; // -
    const ARETURN = 176; // -
    const RETURN_ = 177; // -
    const GETSTATIC = 178; // visitFieldInsn
    const PUTSTATIC = 179; // -
    const GETFIELD = 180; // -
    const PUTFIELD = 181; // -
    const INVOKEVIRTUAL = 182; // visitMethodInsn
    const INVOKESPECIAL = 183; // -
    const INVOKESTATIC = 184; // -
    const INVOKEINTERFACE = 185; // -
    const INVOKEDYNAMIC = 186; // visitInvokeDynamicInsn
    const NEW_ = 187; // visitTypeInsn
    const NEWARRAY = 188; // visitIntInsn
    const ANEWARRAY = 189; // visitTypeInsn
    const ARRAYLENGTH = 190; // visitInsn
    const ATHROW = 191; // -
    const CHECKCAST = 192; // visitTypeInsn
    const INSTANCEOF_ = 193; // -
    const MONITORENTER = 194; // visitInsn
    const MONITOREXIT = 195; // -
    // const WIDE = 196; // NOT VISITED
    const MULTIANEWARRAY = 197; // visitMultiANewArrayInsn
    const IFNULL = 198; // visitJumpInsn
    const IFNONNULL = 199; // -
    // const GOTO_W = 200; // -
    // const JSR_W = 201; // -
}
