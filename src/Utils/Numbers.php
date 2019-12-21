<?php

namespace Kambo\Karsk\Utils;

/**
 * Useful methods for working with numbers
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class Numbers
{

    /**
     * Returns a representation of the specified floating-point value according to the IEEE 754 floating-point
     * "single format" bit layout, preserving Not-a-Number (NaN) values.
     *
     * Bit 31 (the bit that is selected by the mask 0x80000000) represents the sign of the floating-point number.
     * Bits 30-23 (the bits that are selected by the mask 0x7f800000) represent the exponent.
     * Bits 22-0 (the bits that are selected by the mask 0x007fffff) represent
     * the significand (sometimes called the mantissa) of the floating-point number.
     *
     * @param float|int $number floating-point number.
     *
     * @return int the bits that represent the floating-point number.
     */
    public function floatToRawIntBits($number)
    {
        [$whole, $fraction] = $this->numberBreakdown($number);

        $wholeBinary = decbin($whole);
        $fractionBinary = '';

        while ($fraction != 0) {
            $temp = $fraction*2;
            [$whole, $fraction] = $this->numberBreakdown($temp);

            $fractionBinary .= ($whole > 0) ? '1' : '0';
        }

        $sign      = $number > 0 ? '0': '1';
        $precision = strlen($wholeBinary)-1;

        $exponent       = $precision + 127;
        $exponentBinary = str_pad(decbin($exponent), 8, '0', STR_PAD_LEFT);
        $mantisaBinary  = str_pad(substr($wholeBinary, 1).$fractionBinary, 23, '0');

        return bindec($sign.$exponentBinary.$mantisaBinary);
    }

    private function numberBreakdown($number)
    {
        if ($number < 0) {
            $number *= -1;
        }

        return [
            floor($number),
            ($number - floor($number))
        ];
    }
}
