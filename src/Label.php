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

use Kambo\Karsk\Exception\IllegalStateException;
use Kambo\Karsk\Exception\NotImplementedException;

/**
 * A label represents a position in the bytecode of a method. Labels are used
 * for jump, goto, and switch instructions, and for try catch blocks. A label
 * designates the <i>instruction</i> that is just after. Note however that there
 * can be other elements between a label and the instruction it designates (such
 * as other labels, stack map frames, line numbers, etc.).
 *
 * @author Eric Bruneton
 * @author Bohuslav Simek <bohuslav@simek.si>
 */
class Label
{
    public const DEBUG  = 1;
    public const RESOLVED = 2;
    public const RESIZED = 4;
    public const PUSHED = 8;
    public const TARGET = 16;
    public const STORE = 32;
    public const REACHABLE = 64;
    public const JSR = 128;
    public const RET = 256;
    public const SUBROUTINE = 512;
    public const VISITED = 1024;
    public const VISITED2 = 2048;

    /**
     * Field used to associate user information to a label. Warning: this field
     * is used by the ASM tree package. In order to use it with the ASM tree
     * package you must override the
     * {@link org.objectweb.asm.tree.MethodNode#getLabelNode} method.
     *
     * @var object
     */
    public $info;

    /**
     * Flags that indicate the status of this label.
     *
     * @see #DEBUG
     * @see #RESOLVED
     * @see #RESIZED
     * @see #PUSHED
     * @see #TARGET
     * @see #STORE
     * @see #REACHABLE
     * @see #JSR
     * @see #RET
     *
     * @var int
     */
    public $status;

    /**
     * The line number corresponding to this label, if known. If there are
     * several lines, each line is stored in a separate label, all linked via
     * their next field (these links are created in ClassReader and removed just
     * before visitLabel is called, so that this does not impact the rest of the
     * code).
     *
     * @var int
     */
    public $line;

    /**
     * The position of this label in the code, if known.
     *
     * @var int
     */
    public $position;

    /**
     * Number of forward references to this label, times two.
     *
     * @var int
     */
    private $referenceCount = 0;

    /**
     * Informations about forward references. Each forward reference is
     * described by two consecutive integers in this array: the first one is the
     * position of the first byte of the bytecode instruction that contains the
     * forward reference, while the second is the position of the first byte of
     * the forward reference itself. In fact the sign of the first integer
     * indicates if this reference uses 2 or 4 bytes, and its absolute value
     * gives the position of the bytecode instruction. This array is also used
     * as a bitset to store the subroutines to which a basic block belongs. This
     * information is needed in {@linked MethodWriter#visitMaxs}, after all
     * forward references have been resolved. Hence the same array can be used
     * for both purposes without problems.
     *
     * @var int[]
     */
    private $srcAndRefPositions;

    // ------------------------------------------------------------------------

    /*
     * Fields for the control flow and data flow graph analysis algorithms (used
     * to compute the maximum stack size or the stack map frames). A control
     * flow graph contains one node per "basic block", and one edge per "jump"
     * from one basic block to another. Each node (i.e., each basic block) is
     * represented by the Label object that corresponds to the first instruction
     * of this basic block. Each node also stores the list of its successors in
     * the graph, as a linked list of Edge objects.
     *
     * The control flow analysis algorithms used to compute the maximum stack
     * size or the stack map frames are similar and use two steps. The first
     * step, during the visit of each instruction, builds information about the
     * state of the local variables and the operand stack at the end of each
     * basic block, called the "output frame", <i>relatively</i> to the frame
     * state at the beginning of the basic block, which is called the "input
     * frame", and which is <i>unknown</i> during this step. The second step, in
     * {@link MethodWriter#visitMaxs}, is a fix point algorithm that computes
     * information about the input frame of each basic block, from the input
     * state of the first basic block (known from the method signature), and by
     * the using the previously computed relative output frames.
     *
     * The algorithm used to compute the maximum stack size only computes the
     * relative output and absolute input stack heights, while the algorithm
     * used to compute stack map frames computes relative output frames and
     * absolute input frames.
     */

    /**
     * Start of the output stack relatively to the input stack. The exact
     * semantics of this field depends on the algorithm that is used.
     *
     * When only the maximum stack size is computed, this field is the number of
     * elements in the input stack.
     *
     * When the stack map frames are completely computed, this field is the
     * offset of the first output stack element relatively to the top of the
     * input stack. This offset is always negative or null. A null offset means
     * that the output stack must be appended to the input stack. A -n offset
     * means that the first n output stack elements must replace the top n input
     * stack elements, and that the other elements must be appended to the input
     * stack.
     *
     * @var int
     */
    public $inputStackTop;

    /**
     * Maximum height reached by the output stack, relatively to the top of the
     * input stack. This maximum is always positive or null.
     *
     * @var int
     */
    public $outputStackMax;

    /**
     * Information about the input and output stack map frames of this basic
     * block. This field is only used when {@link ClassWriter#COMPUTE_FRAMES}
     * option is used.
     *
     * @var Frame
     */
    public $frame;

    /**
     * The successor of this label, in the order they are visited. This linked
     * list does not include labels used for debug info only. If
     * {@link ClassWriter#COMPUTE_FRAMES} option is used then, in addition, it
     * does not contain successive labels that denote the same bytecode position
     * (in this case only the first label appears in this list).
     *
     * @var Label
     */
    public $successor;

    /**
     * The successors of this node in the control flow graph. These successors
     * are stored in a linked list of {@link Edge Edge} objects, linked to each
     * other by their {@link Edge#next} field.
     *
     * @var Edge
     */
    public $successors;

    /**
     * The next basic block in the basic block stack. This stack is used in the
     * main loop of the fix point algorithm used in the second step of the
     * control flow analysis algorithms. It is also used in
     * {@link #visitSubroutine} to avoid using a recursive method, and in
     * ClassReader to temporarily store multiple source lines for a label.
     *
     * @see MethodWriter#visitMaxs
     *
     * @var Label
     */
    public $next;

    /**
     * Returns the offset corresponding to this label. This offset is computed
     * from the start of the method's bytecode. <i>This method is intended for
     * {@link Attribute} sub classes, and is normally not needed by class
     * generators or adapters.</i>
     *
     * @return int the offset corresponding to this label.
     *
     * @throws IllegalStateException
     *             if this label is not resolved yet.
     */
    public function getOffset()
    {
        if (($this->status & self::RESOLVED) == 0) {
            throw new IllegalStateException(
                'Label offset position has not been resolved yet
            '
            );
        }

        return $this->position;
    }

    /**
     * Puts a reference to this label in the bytecode of a method. If the
     * position of the label is known, the offset is computed and written
     * directly. Otherwise, a null offset is written and a new forward reference
     * is declared for this label.
     *
     * @param MethodWriter $owner
     *            the code writer that calls this method.
     * @param ByteVector   $out
     *            the bytecode of the method.
     * @param int          $source
     *            the position of first byte of the bytecode instruction that
     *            contains this label.
     * @param bool         $wideOffset
     *            <tt>true</tt> if the reference must be stored in 4 bytes, or
     *            <tt>false</tt> if it must be stored with 2 bytes.
     */
    public function put(MethodWriter $owner, ByteVector $out, int $source, bool $wideOffset) : void
    {
        if (($this->status & self::RESOLVED) == 0) {
            if ($wideOffset) {
                $this->addReference((-1 - $source), count($out));
                $out->putInt(-1);
            } else {
                $this->addReference($source, count($out));
                $out->putShort(-1);
            }
        } else {
            if ($wideOffset) {
                $out->putInt(($this->position - $source));
            } else {
                $out->putShort(($this->position - $source));
            }
        }
    }

    /**
     * Adds a forward reference to this label. This method must be called only
     * for a true forward reference, i.e. only if this label is not resolved
     * yet. For backward references, the offset of the reference can be, and
     * must be, computed and stored directly.
     *
     * @param int $sourcePosition
     *            the position of the referencing instruction. This position
     *            will be used to compute the offset of this forward reference.
     * @param int $referencePosition
     *            the position where the offset for this forward reference must
     *            be stored.
     */
    public function addReference(int $sourcePosition, int $referencePosition)
    {
        if (($this->srcAndRefPositions == null)) {
            $this->srcAndRefPositions = [];
        }

        if (($this->referenceCount > count($this->srcAndRefPositions))) {
            $a = [];
            foreach (range(0, (count($this->srcAndRefPositions)  + 0)) as $_upto) {
                $a[$_upto] = $this->srcAndRefPositions[$_upto - (0) + 0];
            }

            $this->srcAndRefPositions = $a;
        }

        $this->srcAndRefPositions[$this->referenceCount++] = $sourcePosition;
        $this->srcAndRefPositions[$this->referenceCount++] = $referencePosition;
    }

    /**
     * Resolves all forward references to this label. This method must be called
     * when this label is added to the bytecode of the method, i.e. when its
     * position becomes known. This method fills in the blanks that where left
     * in the bytecode by each forward reference previously added to this label.
     *
     * @param $owner
     *            the code writer that calls this method.
     * @param $position
     *            the position of this label in the bytecode.
     * @param $data
     *            the bytecode of the method.
     *
     * @return bool <tt>true</tt> if a blank that was left for this label was to
     *         small to store the offset. In such a case the corresponding jump
     *         instruction is replaced with a pseudo instruction (using unused
     *         opcodes) using an unsigned two bytes offset. These pseudo
     *         instructions will be replaced with standard bytecode instructions
     *         with wider offsets (4 bytes instead of 2), in ClassReader.
     */
    public function resolve(MethodWriter $owner, int $position, array &$data) : bool
    {
        $needUpdate     = false;
        $this->status  |= self::RESOLVED;
        $this->position = $position;

        $i = 0;

        while ($i < $this->referenceCount) {
            $source    = $this->srcAndRefPositions[$i++];
            $reference = $this->srcAndRefPositions[$i++];
            $offset    = null;

            if ($source >= 0) {
                $offset = ($position - $source);
                // TODO SIMEK, taken from java short...
                // Use constant for this...
                // https://docs.oracle.com/javase/7/docs/api/constant-values.html#java.lang.Short.MIN_VALUE
                // -32768 to 32767
                if ((($offset < -32768) || ($offset > 32767))) {
                    /*
                     * changes the opcode of the jump instruction, in order to
                     * be able to find it later (see resizeInstructions in
                     * MethodWriter). These temporary opcodes are similar to
                     * jump instruction opcodes, except that the 2 bytes offset
                     * is unsigned (and can therefore represent values from 0 to
                     * 65535, which is sufficient since the size of a method is
                     * limited to 65535 bytes).
                     */
                    $opcode = ($data[($reference - 1)] & 0xFF);
                    if (($opcode <= Opcodes::JSR)) {
                        // changes IFEQ ... JSR to opcodes 202 to 217
                        $data[($reference - 1)] = (($opcode + 49));
                    } else {
                        // changes IFNULL and IFNONNULL to opcodes 218 and 219
                        $data[($reference - 1)] = (($opcode + 20));
                    }

                    $needUpdate = true;
                }

                $data[$reference++] = $this->uRShift($offset, 8);
                $data[$reference]   = $offset;
            } else {
                $offset = $position + $source + 1;

                $data[$reference++] = $this->uRShift($offset, 24);
                $data[$reference++] = $this->uRShift($offset, 16);
                $data[$reference++] = $this->uRShift($offset, 8);

                $data[$reference] = $offset;
            }
        }

        return $needUpdate;
    }

    /**
     * Returns the first label of the series to which this label belongs. For an
     * isolated label or for the first label in a series of successive labels,
     * this method returns the label itself. For other labels it returns the
     * first label of the series.
     *
     * @return Label the first label of the series to which this label belongs.
     */
    public function getFirst() : Label
    {
        return (!ClassReader::FRAMES || ($this->frame == null)) ? $this : $this->frame->owner;
    }

    /**
     * Returns true is this basic block belongs to the given subroutine.
     *
     * @param int $id a subroutine id.
     *
     * @return bool true is this basic block belongs to the given subroutine.
     */
    public function inSubroutine(int $id) : bool
    {
        if (($this->status & Label::VISITED) != 0) {
            return ((($this->srcAndRefPositions[$this->uRShift($id, 32)] & $id)) != 0);
        }

        return  false;
    }

    /**
     * Returns true if this basic block and the given one belong to a common
     * subroutine.
     *
     * @param Label $block
     *              another basic block.
     *
     * @return bool true if this basic block and the given one belong to a common
     *         subroutine.
     */
    public function inSameSubroutine(Label $block) : bool
    {
        if ((($this->status & self::VISITED) == 0) || (($block->status & self::VISITED) == 0)) {
            return false;
        }

        for ($i = 0; $i < count($this->srcAndRefPositions); ++$i) {
            if (($this->srcAndRefPositions[$i] & $block->srcAndRefPositions[$i]) != 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Marks this basic block as belonging to the given subroutine.
     *
     * @param int $id a subroutine id.
     *
     * @return void
     */
    public function addToSubroutine(int $id) : void
    {
        if (($this->status & self::VISITED) == 0) {
            $this->status |= self::VISITED;
            $this->srcAndRefPositions = [];
        }

        $this->srcAndRefPositions[$this->uRShift($id, 32)] |= $id;
    }

    /**
     * Finds the basic blocks that belong to a given subroutine, and marks these
     * blocks as belonging to this subroutine. This method follows the control
     * flow graph to find all the blocks that are reachable from the current
     * block WITHOUT following any JSR target.
     *
     * @param Label $JSR
     *            a JSR block that jumps to this subroutine. If this JSR is not
     *            null it is added to the successor of the RET blocks found in
     *            the subroutine.
     * @param Int   $id
     *            the id of this subroutine.
     * @param Int   $nbSubroutines
     *            the total number of subroutines in the method.
     *
     * @return void
     */
    public function visitSubroutine(Label $JSR, int $id, int $nbSubroutines) : void
    {
        $stack = $this;
        while (($stack != null)) {
            $l = $stack;
            $stack = $l->next;
            $l->next = null;
            if (($JSR != null)) {
                if (((($l->status & self::VISITED2)) != 0)) {
                    continue;
                }

                $l->status |= self::VISITED2;
                if (((($l->status & self::RET)) != 0)) {
                    if (!$l->inSameSubroutine($JSR)) {
                        $e = new Edge();
                        $e->info = $l->inputStackTop;
                        $e->successor = $JSR->successors->successor;
                        $e->next = $l->successors;
                        $l->successors = $e;
                    }
                }
            } else {
                if ($l->inSubroutine($id)) {
                    continue;
                }
                $l->addToSubroutine($id, $nbSubroutines);
            }
            $e = $l->successors;
            while (($e != null)) {
                if ((((($l->status & Label::JSR)) == 0) || ($e != $l->successors->next))) {
                    if (($e->successor->next == null)) {
                        $e->successor->next = $stack;
                        $stack = $e->successor;
                    }
                }
                $e = $e->next;
            }
        }
    }

    /**
     * Returns a string representation of this label.
     *
     * @return string a string representation of this label.
     *
     * @throws NotImplementedException - method is not yet implemented
     */
    public function toString()
    {
        throw new NotImplementedException('This method is not implemented.');
    }

    private function uRShift($a, $b)
    {
        return ($a >> $b & 0xFF);
    }
}
