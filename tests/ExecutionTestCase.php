<?php

namespace Kambo\Tests\Karsk;

use PHPUnit\Framework\TestCase;
use Kambo\Karsk\Utils\FileWriter;
use Kambo\Tests\Karsk\Constraint\ExecutionResult;

/**
 * Base test case for the integration tests
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class ExecutionTestCase extends TestCase
{
    static private $disableDisassembler = false;
    static private $disableDisassemblerReason = '';

    const EXECUTION_COMMAND = 'java -cp #target-dir# #class-name#';

    const DISASSEMBLER_COMMAND = 'javap -v -cp #target-dir# #class-name# | awk \'{if(NR>3)print}\'';

    public function assertExecutionResult(
        array $expectedExecutionResult,
        $classWriters,
        string $message = ''
    ) : void {
        $classPaths = self::writeClasses($classWriters);

        foreach ($classPaths as $classPath) {
            if ($classPath['entryPoint'] === true) {
                $executionCommand = strtr(
                    self::EXECUTION_COMMAND,
                    [
                        '#target-dir#' => self::getTemporaryDir(),
                        '#class-name#' => $classPath['name'],
                    ]
                );

                break;
            }
        }

        self::assertThat(
            $expectedExecutionResult,
            new ExecutionResult($executionCommand),
            $message
        );
    }

    public function assertDisassemblerResult(
        array $expectedExecutionResult,
        $classWriters,
        string $message = ''
    ) : void {

        if (self::$disableDisassembler === true) {
            $this->markTestSkipped("Test skipped reason:\n ".self::$disableDisassemblerReason);
        }

        $classPaths = self::writeClasses($classWriters);

        foreach ($classPaths as $classPath) {
            if ($classPath['entryPoint'] === true) {
                $executionCommand = strtr(
                    self::DISASSEMBLER_COMMAND,
                    [
                        '#target-dir#' => self::getTemporaryDir(),
                        '#class-name#' => $classPath['name'],
                    ]
                );

                break;
            }
        }

        self::assertThat(
            $expectedExecutionResult,
            new ExecutionResult($executionCommand),
            $message
        );
    }

    public static function writeClasses($classWriters) : array
    {
        $classPaths = [];
        $fileWriter = new FileWriter;

        if (!is_array($classWriters)) {
            $classWriters = [
                [
                    'classWritter' => $classWriters,
                    'entryPoint' => true
                ]
            ];
        }

        foreach ($classWriters as $classWriter) {
            $path         = self::getTemporaryDir().'/'.$classWriter['classWritter']->thisName.'.class';
            $classPaths[] = [
                'name' => $classWriter['classWritter']->thisName,
                'entryPoint' => $classWriter['entryPoint']
            ];

            $fileWriter->writeClassFile($classWriter['classWritter'], $path);
        }

        return $classPaths;
    }

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        self::checkJvm();
        self::checkDecompiler();
    }

    protected static function checkJvm()
    {
        $output     = [];
        $returnCode = 0;

        exec(
            'java -fullversion 2>&1',
            $output,
            $returnCode
        );

        if ($returnCode !== 0) {
            throw new \Exception('Java execution error');
        }
    }

    protected static function checkDecompiler()
    {
        $output     = [];
        $returnCode = 0;

        exec(
            'javac -version 2>&1',
            $output,
            $returnCode
        );

        if ($returnCode !== 0) {
            self::$disableDisassembler = true;
            self::$disableDisassemblerReason .= implode("\n", $output);
        }

        exec(
        'awk -V 2>&1',
            $output,
            $returnCode
        );

        if ($returnCode !== 0) {
            self::$disableDisassembler = true;
            self::$disableDisassemblerReason .= implode("\n", $output);
        }
    }

    protected static function getTemporaryDir() : string
    {
        return sys_get_temp_dir();
    }
}
