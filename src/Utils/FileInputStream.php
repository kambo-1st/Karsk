<?php

namespace Kambo\Karsk\Utils;

use Kambo\Karsk\Exception;

/**
 * FileInputStream
 * Based on https://docs.oracle.com/javase/7/docs/api/java/io/FileInputStream.html
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class FileInputStream implements InputStream
{
    /**
     * File pointer
     *
     * @var resource
     */
    private $fp;

    private $filename;

    private $offset = 0;

    /**
     * Creates a FileInputStream by opening a connection to an actual file, the file named
     * by the path name in the file system.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        if (!file_exists($name)) {
            throw new Exception\FileNotFoundException('File '.$name.' not found.');
        }

        $this->filename = $name;

        /*$fp = fopen($name, 'r');

        if ($fp === false) {
            throw new Exception\IOException('Error during opening file: '.error_get_last()['message']);
        }

        $this->fp = $fp;*/
    }

    /**
     * Closes this file input stream and releases any system resources associated with the stream.
     *
     * @return void
     */
    public function close() : void
    {
        //fclose($this->fp);
    }

    /**
     * Reads up to len bytes of data from this input stream into an array of bytes. If len is not zero,
     * the method blocks until some input is available; otherwise, no bytes are read and 0 is returned.
     *
     * @param array|null $b   the buffer into which the data is read.
     * @param int|null   $off the start offset in the destination array b
     * @param int|null   $len the maximum number of bytes read.
     *
     * @return int the total number of bytes read into the buffer, or -1 if there is no more data because
     *             the end of the file has been reached.
     */
    public function read(array &$b = null, ?int $off = null, ?int $len = null) : int
    {
        if ($b === null && $off === null && $len === null) {
            // read one byte
            $readData = file_get_contents(
                $this->filename,
                false,
                null,
                ++$this->offset,
                1
            );

            if (empty($readData)) {
                return -1;
            }

            $result = unpack(
                'C*',
                $readData
            );

            return reset($result);
        }

        if ($len < 0) {
            return -1;
        }

        if ($off+$len > $this->available()) {
            return -1;
        }

        $readData = file_get_contents(
            $this->filename,
            false,
            null,
            $off,
            $len
        );

        if (empty($readData)) {
            return -1;
        }

        $this->offset = $off+$len+$this->offset;

        $unpacked = unpack('C*', $readData);

        foreach ($unpacked as $byte) {
            $b[] = $byte;
        }

        return strlen($readData);
    }

    public function available(): int
    {
        return filesize($this->filename);
    }
}
