<?php

namespace Kambo\Karsk\Utils;

use Kambo\Karsk\ClassWriter;

/**
 * Save generated class into the file
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class FileWriter
{
    /**
     * Save generated class into the file
     *
     * @param ClassWriter $classWriter Class definition
     * @param string      $fileName    Class filename
     *
     * @return void
     */
    public function writeClassFile(ClassWriter $classWriter, string $fileName) : void
    {
        $code = $classWriter->toByteArray();

        $binaryString = pack('c*', ...$code);

        $file = fopen($fileName, 'w+');

        fwrite($file, $binaryString);
        fclose($file);
    }
}
