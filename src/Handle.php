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
 * A reference to a field or a method.
 *
 * @author  Remi Forax
 * @author  Eric Bruneton
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class Handle
{
    /**
     * The kind of field or method designated by this Handle. Should be
     * {@link Opcodes#H_GETFIELD}, {@link Opcodes#H_GETSTATIC},
     * {@link Opcodes#H_PUTFIELD}, {@link Opcodes#H_PUTSTATIC},
     * {@link Opcodes#H_INVOKEVIRTUAL}, {@link Opcodes#H_INVOKESTATIC},
     * {@link Opcodes#H_INVOKESPECIAL}, {@link Opcodes#H_NEWINVOKESPECIAL} or
     * {@link Opcodes#H_INVOKEINTERFACE}.
     *
     * @var int
     */
    public $tag;

    /**
     * The internal name of the class that owns the field or method designated
     * by this handle.
     *
     * @var String
     */
    public $owner;

    /**
     * The name of the field or method designated by this handle.
     *
     * @var String
     */
    public $name;

    /**
     * The descriptor of the field or method designated by this handle.
     *
     * @var String
     */
    public $desc;

    /**
     * Indicate if the owner is an interface or not.
     *
     * @var boolean
     */
    public $itf;

    /**
     * Constructs a new field or method handle.
     *
     * @param int    $tag
     *               the kind of field or method designated by this Handle. Must be
     *               {@link Opcodes#H_GETFIELD}, {@link Opcodes#H_GETSTATIC},
     *               {@link Opcodes#H_PUTFIELD}, {@link Opcodes#H_PUTSTATIC},
     *               {@link Opcodes#H_INVOKEVIRTUAL},
     *               {@link Opcodes#H_INVOKESTATIC},
     *               {@link Opcodes#H_INVOKESPECIAL},
     *               {@link Opcodes#H_NEWINVOKESPECIAL} or
     *               {@link Opcodes#H_INVOKEINTERFACE}.
     * @param string $owner
     *               the internal name of the class that owns the field or method
     *               designated by this handle.
     * @param string $name
     *               the name of the field or method designated by this handle.
     * @param string $desc
     *              the descriptor of the field or method designated by this handle.
     * @param bool   $itf
     *               true if the owner is an interface.
     */
    public function __construct($tag, $owner, $name, $desc, $itf = null)
    {
        if ($itf === null) {
            $itf = ($tag == Opcodes::H_INVOKEINTERFACE);
        }

        $this->tag = $tag;
        $this->owner = $owner;
        $this->name = $name;
        $this->desc = $desc;
        $this->itf = $itf;
    }

    /**
     * Returns the kind of field or method designated by this handle.
     *
     * @return int {@link Opcodes#H_GETFIELD}, {@link Opcodes#H_GETSTATIC},
     *             {@link Opcodes#H_PUTFIELD}, {@link Opcodes#H_PUTSTATIC},
     *             {@link Opcodes#H_INVOKEVIRTUAL}, {@link Opcodes#H_INVOKESTATIC},
     *             {@link Opcodes#H_INVOKESPECIAL},
     *             {@link Opcodes#H_NEWINVOKESPECIAL} or
     *             {@link Opcodes#H_INVOKEINTERFACE}.
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Returns the internal name of the class that owns the field or method
     * designated by this handle.
     *
     * @return string the internal name of the class that owns the field or method designated by this handle.
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Returns the name of the field or method designated by this handle.
     *
     * @return string the name of the field or method designated by this handle.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the descriptor of the field or method designated by this handle.
     *
     * @return string the descriptor of the field or method designated by this handle.
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * Returns true if the owner of the field or method designated
     * by this handle is an interface.
     *
     * @return bool true if the owner of the field or method designated by this handle is an interface.
     */
    public function isInterface()
    {
        return $this->itf;
    }

    /**
     * Indicates whether some other object is "equal to" this one.
     *
     * @param object $obj the reference object with which to compare.
     *
     * @return true if this object is the same as the obj argument; false otherwise.
     */
    public function equals($obj)
    {
        if (($obj == $this)) {
            return  true ;
        }

        if (!($obj instanceof Handle)) {
            return  false ;
        }

        return ($this->tag == $obj->tag)
            && ($this->itf == $obj->itf)
            && $this->owner->equals($obj->owner)
            && $this->name->equals($obj->name)
            && $this->desc->equals($obj->desc);
    }

    /**
     * Returns a hash code value for the object.
     *
     * @return string a hash code value for this object.
     */
    public function hashCode()
    {
        return $this->tag + ( !is_null($this->itf) ? 64 : 0 )
            + ($this->owner->hashCode() * $this->name->hashCode() * $this->desc->hashCode());
    }

    /**
     * Returns the textual representation of this handle. The textual
     * representation is:
     *
     * <pre>
     * for a reference to a class:
     * owner '.' name desc ' ' '(' tag ')'
     * for a reference to an interface:
     * owner '.' name desc ' ' '(' tag ' ' itf ')'
     * </pre>
     *
     * . As this format is unambiguous, it can be parsed if necessary.
     *
     * @return string Textual representation of this handle
     */
    public function toString()
    {
        return $this->owner . '.' . $this->name . $this->desc . ' (' . $this->tag
            . ( !is_null($this->itf) ? ' itf' : '' ) . ')';
    }
}
