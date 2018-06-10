<?php

namespace Kambo\Karsk\Types;

/**
 * Description of Long
 *
 * Lorem ipsum dolor
 *
 * @package 
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Long
{
    private $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }
}
