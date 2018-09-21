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

/**
 * Information about the input stack map frame at the "current" instruction of a
 * method. This is implemented as a Frame subclass for a "basic block"
 * containing only one instruction.
 *
 * @author Eric Bruneton
 * @author Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class CurrentFrame extends Frame
{
    /**
     * Sets this CurrentFrame to the input stack map frame of the next "current"
     * instruction, i.e. the instruction just after the given one. It is assumed
     * that the value of this object when this method is called is the stack map
     * frame status just before the given instruction is executed.
     *
     * @param $opcode the opcode of the instruction.
     * @param $arg    the operand of the instruction, if any.
     * @param $cw     the class writer to which this label belongs.
     * @param $item   the operand of the instructions, if any.
     */
    protected function execute($opcode, $arg, $cw, $item) : void
    {
        parent::execute($opcode, $arg, $cw, $item);
        $successor = new Frame();
        $this->merge($cw, $successor, 0);
        $this->set($successor);
        $this->owner->inputStackTop = 0;
    }
}
