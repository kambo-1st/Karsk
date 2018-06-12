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


class Label {
    public static $DEBUG;	// int
    public static $RESOLVED;	// int
    public static $RESIZED;	// int
    public static $PUSHED;	// int
    public static $TARGET;	// int
    public static $STORE;	// int
    public static $REACHABLE;	// int
    public static $JSR;	// int
    public static $RET;	// int
    public static $SUBROUTINE;	// int
    public static $VISITED;	// int
    public static $VISITED2;	// int

    public $info;	// Object
    public $status;	// int
    public $line;	// int
    public $position;	// int
    public $referenceCount;	// int
    public $srcAndRefPositions;	// int[]
    public $inputStackTop;	// int
    public $outputStackMax;	// int
    public $frame;	// Frame
    public $successor;	// Label
    public $successors;	// Edge
    public $next;	// Label

    public static function __staticinit() { // static class members
        self::$DEBUG = 1;
        self::$RESOLVED = 2;
        self::$RESIZED = 4;
        self::$PUSHED = 8;
        self::$TARGET = 16;
        self::$STORE = 32;
        self::$REACHABLE = 64;
        self::$JSR = 128;
        self::$RET = 256;
        self::$SUBROUTINE = 512;
        self::$VISITED = 1024;
        self::$VISITED2 = 2048;
    }

    /**
     * Constructs a new label.
     */
    public static function constructor__ ()
    {
        $me = new self();
        return $me;
    }

    /**
     * Returns the offset corresponding to this label. This offset is computed
     * from the start of the method's bytecode. <i>This method is intended for
     * {@link Attribute} sub classes, and is normally not needed by class
     * generators or adapters.</i>
     *
     * @return the offset corresponding to this label.
     * @throws IllegalStateException
     *             if this label is not resolved yet.
     */
    public function getOffset ()
    {
        if (((($this->status & self::$RESOLVED)) == 0)) {
            throw new IllegalStateException("Label offset position has not been resolved yet");
        }

        return $this->position;
    }
	protected function put ($owner, $out, $source, $wideOffset) // [final MethodWriter owner, final ByteVector out, final int source, final boolean wideOffset]
	{
		if (((($this->status & self::$RESOLVED)) == 0))
		{
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
		}
		else
		{
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
	protected function addReference ($sourcePosition, $referencePosition) // [final int sourcePosition, final int referencePosition]
	{
		if (($this->srcAndRefPositions == NULL))
		{
			$this->srcAndRefPositions = array();
		}
		if (($this->referenceCount >= count($this->srcAndRefPositions) /*from: srcAndRefPositions.length*/))
		{
			$a = array();
			foreach (range(0, (count($this->srcAndRefPositions) /*from: srcAndRefPositions.length*/ + 0)) as $_upto) $a[$_upto] = $this->srcAndRefPositions[$_upto - (0) + 0]; /* from: System.arraycopy(srcAndRefPositions, 0, a, 0, srcAndRefPositions.length) */;
			$this->srcAndRefPositions = $a;
		}
		$this->srcAndRefPositions[++$this->referenceCount] = $sourcePosition;
		$this->srcAndRefPositions[++$this->referenceCount] = $referencePosition;
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
                var_dump($source);
                if ((($offset < $Short->MIN_VALUE) || ($offset > $Short->MAX_VALUE))) {
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
		return ( ((!$ClassReader->FRAMES || ($this->frame == NULL))) ? $this : $this->frame->owner );
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
    public function toString ()
    {
        return ("L" . $System->identityHashCode($this));
    }
}

Label::__staticinit(); // initialize static vars for this class on load
