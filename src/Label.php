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

/**
 * A label represents a position in the bytecode of a method. Labels are used
 * for jump, goto, and switch instructions, and for try catch blocks. A label
 * designates the <i>instruction</i> that is just after. Note however that there
 * can be other elements between a label and the instruction it designates (such
 * as other labels, stack map frames, line numbers, etc.).
 *
 * @author  Eric Bruneton
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class Label
{
    public static $DEBUG = 1;	// int
    public static $RESOLVED = 2;	// int
    public static $RESIZED = 4;	// int
    public static $PUSHED = 8;	// int
    public static $TARGET = 16;	// int
    public static $STORE = 32;	// int
    public static $REACHABLE = 64;	// int
    public static $JSR = 128;	// int
    public static $RET = 256;	// int
    public static $SUBROUTINE = 512;	// int
    public static $VISITED = 1024;	// int
    public static $VISITED2 = 2048;	// int

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
        if (((($this->status & self::$RESOLVED)) == 0)) {
            throw new IllegalStateException('Label offset position has not been resolved yet');
        }

        return $this->position;
    }

    public function put ($owner, $out, $source, $wideOffset) // [final MethodWriter owner, final ByteVector out, final int source, final boolean wideOffset]
	{
		if (($this->status & self::$RESOLVED) == 0) {
			if ($wideOffset)
			{
				$this->addReference((-1 - $source), count($out) /*from: out.length*/);
				$out->putInt(-1);
			}
			else
			{
				$this->addReference($source, count($out) /*from: out.length*/);
				$out->putShort(-1);
			}
		} else {
			if ($wideOffset)
			{
				$out->putInt(($this->position - $source));
			}
			else
			{
				$out->putShort(($this->position - $source));
			}
		}
	}
    public function addReference ($sourcePosition, $referencePosition) // [final int sourcePosition, final int referencePosition]
	{
		if (($this->srcAndRefPositions == NULL)) {
			$this->srcAndRefPositions = array();
		}

		if (($this->referenceCount > count($this->srcAndRefPositions))) {
			$a = array();
			// TODO STRANGE it is empty...
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
     * @return <tt>true</tt> if a blank that was left for this label was to
     *         small to store the offset. In such a case the corresponding jump
     *         instruction is replaced with a pseudo instruction (using unused
     *         opcodes) using an unsigned two bytes offset. These pseudo
     *         instructions will be replaced with standard bytecode instructions
     *         with wider offsets (4 bytes instead of 2), in ClassReader.
     *
     * @throws IllegalArgumentException
     *             if this label has already been resolved, or if it has not
     *             been created by the given code writer.
     */
    public function resolve ($owner, $position, &$data) // [final MethodWriter owner, final int position, final byte[] data]
    {
        $needUpdate     =  FALSE ;
        $this->status  |= self::$RESOLVED;
        $this->position = $position;

        $i = 0;

        while (($i < $this->referenceCount))
        {
            $source    = $this->srcAndRefPositions[$i++];
            $reference = $this->srcAndRefPositions[$i++];
            $offset    = null;

            if ($source >= 0) {
                $offset = ($position - $source);
                // TODO SIMEK, taken from java short...
                // Use constant for this...
                // https://docs.oracle.com/javase/7/docs/api/constant-values.html#java.lang.Short.MIN_VALUE
                //-32768 to 32767
                if ((($offset < -32768) || ($offset > 32767))) {
                    $opcode = ($data[($reference - 1)] & 0xFF);
                    if (($opcode <= Opcodes::JSR)) {
                        $data[($reference - 1)] = (($opcode + 49));
                    } else {
                        $data[($reference - 1)] = (($opcode + 20));
                    }

                    $needUpdate = true ;
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

    private function uRShift($a, $b)
    {
        return ($a >> $b & 0xFF);
    }

	protected function getFirst () 
	{
		return ( ((!ClassReader::FRAMES || ($this->frame == NULL))) ? $this : $this->frame->owner );
	}

    /**
     * Returns true is this basic block belongs to the given subroutine.
     *
     * @param int $id
     *            a subroutine id.
     *
     * @return bool true is this basic block belongs to the given subroutine.
     */
    protected function inSubroutine ($id) // [final long id]
    {
        if (((($this->status & Label::$VISITED)) != 0)) {
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
    protected function inSameSubroutine(Label $block) // [final Label block]
    {
        if ((((($this->status & self::$VISITED)) == 0) || ((($block->status & self::$VISITED)) == 0)))
        {
            return false;
        }

        for ($i = 0; ($i < count($this->srcAndRefPositions) /*from: srcAndRefPositions.length*/); ++$i)
        {
            if (((($this->srcAndRefPositions[$i] & $block->srcAndRefPositions[$i])) != 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Marks this basic block as belonging to the given subroutine.
     *
     * @param int $id
     *            a subroutine id.
     * @param int $nbSubroutines
     *            the total number of subroutines in the method.
     */
    protected function addToSubroutine ($id, $nbSubroutines) // [final long id, final int nbSubroutines]
    {
        if (((($this->status & self::$VISITED)) == 0)) {
            $this->status |= self::$VISITED;
            $this->srcAndRefPositions = array();
        }

        $this->srcAndRefPositions[$this->uRShift($id, 32)] |= $id;
    }

	protected function visitSubroutine ($JSR, $id, $nbSubroutines) // [final Label JSR, final long id, final int nbSubroutines]
	{
		$stack = $this;
		while (($stack != NULL)) 
		{
			$l = $stack;
			$stack = $l->next;
			$l->next = NULL;
			if (($JSR != NULL))
			{
				if (((($l->status & self::$VISITED2)) != 0))
				{
					continue;
				}
				$l->status |= self::$VISITED2;
				if (((($l->status & self::$RET)) != 0))
				{
					if (!$l->inSameSubroutine($JSR))
					{
						$e = new Edge();
						$e->info = $l->inputStackTop;
						$e->successor = $JSR->successors->successor;
						$e->next = $l->successors;
						$l->successors = $e;
					}
				}
			}
			else
			{
				if ($l->inSubroutine($id))
				{
					continue;
				}
				$l->addToSubroutine($id, $nbSubroutines);
			}
			$e = $l->successors;
			while (($e != NULL)) 
			{
				if ((((($l->status & Label::$JSR)) == 0) || ($e != $l->successors->next)))
				{
					if (($e->successor->next == NULL))
					{
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
     * @return a string representation of this label.
     */
    public function toString()
    {
        return ("L" . $System->identityHashCode($this));
    }
}
