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

class MethodVisitor
{
    public $api; // int
    public $mv;  // MethodVisitor
    public static function constructor__I($api) // [final int api]
    {
        $me = new self();
        self::constructor__I_MethodVisitor($api, null);
        return $me;
    }
    public static function constructor__I_MethodVisitor($api, $mv) // [final int api, final MethodVisitor mv]
    {
        $me = new self();
        if ((($api != Opcodes::ASM4) && ($api != Opcodes::ASM5))) {
            throw new IllegalArgumentException();
        }
        $me->api = $api;
        $me->mv = $mv;
        return $me;
    }
    public function visitParameter($name, $access) // [String name, int access]
    {
        if (($this->api < Opcodes::ASM5)) {
            throw new RuntimeException();
        }
        if (($this->mv != null)) {
            $this->mv->visitParameter($name, $access);
        }
    }
    public function visitAnnotationDefault()
    {
        if (($this->mv != null)) {
            return $this->mv->visitAnnotationDefault();
        }
        return null;
    }
    public function visitAnnotation($desc, $visible) // [String desc, boolean visible]
    {
        if (($this->mv != null)) {
            return $this->mv->visitAnnotation($desc, $visible);
        }
        return null;
    }
    public function visitTypeAnnotation($typeRef, $typePath, $desc, $visible) // [int typeRef, TypePath typePath, String desc, boolean visible]
    {
        if (($this->api < Opcodes::ASM5)) {
            throw new RuntimeException();
        }
        if (($this->mv != null)) {
            return $this->mv->visitTypeAnnotation($typeRef, $typePath, $desc, $visible);
        }
        return null;
    }
    public function visitParameterAnnotation($parameter, $desc, $visible) // [int parameter, String desc, boolean visible]
    {
        if (($this->mv != null)) {
            return $this->mv->visitParameterAnnotation($parameter, $desc, $visible);
        }
        return null;
    }
    public function visitAttribute($attr) // [Attribute attr]
    {
        if (($this->mv != null)) {
            $this->mv->visitAttribute($attr);
        }
    }
    public function visitCode()
    {
        if (($this->mv != null)) {
            $this->mv->visitCode();
        }
    }
    public function visitFrame($type, $nLocal, $local, $nStack, $stack) // [int type, int nLocal, Object[] local, int nStack, Object[] stack]
    {
        if (($this->mv != null)) {
            $this->mv->visitFrame($type, $nLocal, $local, $nStack, $stack);
        }
    }
    public function visitInsn($opcode) // [int opcode]
    {
        if (($this->mv != null)) {
            $this->mv->visitInsn($opcode);
        }
    }
    public function visitIntInsn($opcode, $operand) // [int opcode, int operand]
    {
        if (($this->mv != null)) {
            $this->mv->visitIntInsn($opcode, $operand);
        }
    }
    public function visitVarInsn($opcode, $var) // [int opcode, int var]
    {
        if (($this->mv != null)) {
            $this->mv->visitVarInsn($opcode, $var);
        }
    }
    public function visitTypeInsn($opcode, $type) // [int opcode, String type]
    {
        if (($this->mv != null)) {
            $this->mv->visitTypeInsn($opcode, $type);
        }
    }
    public function visitFieldInsn($opcode, $owner, $name, $desc) // [int opcode, String owner, String name, String desc]
    {
        if (($this->mv != null)) {
            $this->mv->visitFieldInsn($opcode, $owner, $name, $desc);
        }
    }
    public function visitMethodInsn_I_String_String_String($opcode, $owner, $name, $desc) // [int opcode, String owner, String name, String desc]
    {
        if (($this->api >= Opcodes::ASM5)) {
            $itf = ($opcode == Opcodes::INVOKEINTERFACE);
            /* match: I_String_String_String_b */
            $this->visitMethodInsn_I_String_String_String_b($opcode, $owner, $name, $desc, $itf);
            return ;
        }
        if (($this->mv != null)) {
            /* match: I_String_String_String */
            $this->mv->visitMethodInsn_I_String_String_String($opcode, $owner, $name, $desc);
        }
    }
    public function visitMethodInsn_I_String_String_String_b($opcode, $owner, $name, $desc, $itf) // [int opcode, String owner, String name, String desc, boolean itf]
    {
        if (($this->api < Opcodes::ASM5)) {
            if (($itf != (($opcode == Opcodes::INVOKEINTERFACE)))) {
                throw new IllegalArgumentException("INVOKESPECIAL/STATIC on interfaces require ASM 5");
            }
            /* match: I_String_String_String */
            $this->visitMethodInsn_I_String_String_String($opcode, $owner, $name, $desc);
            return ;
        }
        if (($this->mv != null)) {
            /* match: I_String_String_String_b */
            $this->mv->visitMethodInsn_I_String_String_String_b($opcode, $owner, $name, $desc, $itf);
        }
    }
    public function visitInvokeDynamicInsn($name, $desc, $bsm, $bsmArgs) // [String name, String desc, Handle bsm, Object... bsmArgs]
    {
        if (($this->mv != null)) {
            $this->mv->visitInvokeDynamicInsn($name, $desc, $bsm, $bsmArgs);
        }
    }
    public function visitJumpInsn($opcode, $label) // [int opcode, Label label]
    {
        if (($this->mv != null)) {
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
    public function visitInsnAnnotation($typeRef, $typePath, $desc, $visible) // [int typeRef, TypePath typePath, String desc, boolean visible]
    {
        if (($this->api < Opcodes::ASM5)) {
            throw new RuntimeException();
        }
        if (($this->mv != null)) {
            return $this->mv->visitInsnAnnotation($typeRef, $typePath, $desc, $visible);
        }
        return null;
    }
    public function visitTryCatchBlock($start, $end, $handler, $type) // [Label start, Label end, Label handler, String type]
    {
        if (($this->mv != null)) {
            $this->mv->visitTryCatchBlock($start, $end, $handler, $type);
        }
    }
    public function visitTryCatchAnnotation($typeRef, $typePath, $desc, $visible) // [int typeRef, TypePath typePath, String desc, boolean visible]
    {
        if (($this->api < Opcodes::ASM5)) {
            throw new RuntimeException();
        }
        if (($this->mv != null)) {
            return $this->mv->visitTryCatchAnnotation($typeRef, $typePath, $desc, $visible);
        }
        return null;
    }
    public function visitLocalVariable($name, $desc, $signature, $start, $end, $index) // [String name, String desc, String signature, Label start, Label end, int index]
    {
        if (($this->mv != null)) {
            $this->mv->visitLocalVariable($name, $desc, $signature, $start, $end, $index);
        }
    }
    public function visitLocalVariableAnnotation($typeRef, $typePath, $start, $end, $index, $desc, $visible) // [int typeRef, TypePath typePath, Label[] start, Label[] end, int[] index, String desc, boolean visible]
    {
        if (($this->api < Opcodes::ASM5)) {
            throw new RuntimeException();
        }
        if (($this->mv != null)) {
            return $this->mv->visitLocalVariableAnnotation($typeRef, $typePath, $start, $end, $index, $desc, $visible);
        }
        return null;
    }
    public function visitLineNumber($line, $start) // [int line, Label start]
    {
        if (($this->mv != null)) {
            $this->mv->visitLineNumber($line, $start);
        }
    }
    public function visitMaxs($maxStack, $maxLocals) // [int maxStack, int maxLocals]
    {
        if (($this->mv != null)) {
            $this->mv->visitMaxs($maxStack, $maxLocals);
        }
    }
    public function visitEnd()
    {
        if (($this->mv != null)) {
            $this->mv->visitEnd();
        }
    }
}
