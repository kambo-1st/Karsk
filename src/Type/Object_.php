<?php
// phpcs:disable Squiz.Classes.ValidClassName

/***
 * Karsk - write java bytecode in PHP!
 * Copyright (c) 2018, Bohuslav Šimek
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
namespace Kambo\Karsk\Type;

/**
 * Class Object_
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class Object_ implements DataType
{
    /**
     * Value of the data type
     *
     * @var mixed
     */
    protected $value;

    protected $type;

    /**
     * Constructor
     *
     * @param DataType $type  object type
     * @param mixed    $value simple data type value
     */
    public function __construct($type, array $value = null)
    {
        $this->value = $value;
        $this->type  = $type;
    }

    /**
     * Get stored value
     *
     * @return mixed Stored value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get stored value
     *
     * @return mixed Stored value
     */
    public function getType()
    {
        return $this->type;
    }
}
