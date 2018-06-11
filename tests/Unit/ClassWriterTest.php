<?php
namespace Kambo\Tests\Karsk\Unit;

use PHPUnit\Framework\TestCase;

use Kambo\Karsk\ClassWriter;
use Kambo\Karsk\Opcodes;

/**
 * Test for the Kambo\Karsk\ClassWriter
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class ClassWriterTest extends TestCase
{
    /**
     * Test creating class with hello world message.
     *
     * Generates the bytecode corresponding to the following Java class:
     *
     * public class Example {
     *      public static void main (String[] args) {
     *          System.out.println("Hello world!");
     *      }
     * }
     *
     * @return void
     */
    public function testGenerateHelloWorld()
    {
        $cw = new ClassWriter(0);
        $cw->visit(Opcodes::V1_8, Opcodes::ACC_PUBLIC, "Example", null, "java/lang/Object", null);

        $mw = $cw->visitMethod(Opcodes::ACC_PUBLIC, "<init>", "()V", null, null);
        $mw->visitVarInsn(Opcodes::ALOAD, 0);

        $mw->visitMethodInsn(Opcodes::INVOKESPECIAL, "java/lang/Object", "<init>", "()V", false);
        $mw->visitInsn(Opcodes::RETURN_);
        $mw->visitMaxs(1, 1);
        $mw->visitEnd();

        $mw2 = $cw->visitMethod((Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC), "main", "([Ljava/lang/String;)V", null, null);
        $mw2->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mw2->visitLdcInsn("Hello world!");

        $mw2->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(Ljava/lang/String;)V", false);
        $mw2->visitInsn(Opcodes::RETURN_);
        $mw2->visitMaxs(2, 2);
        $mw2->visitEnd();

        $code = $cw->toByteArray();

        $expectedClassStructure = [
            202, 254, 186, 190, 0, 0, 0, 52, 0, 26, 1, 0, 7, 69, 120, 97, 109, 112, 108, 101, 7, 0,
            1, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 79, 98, 106, 101, 99, 116, 7,
            0, 3, 1, 0, 6, 60, 105, 110, 105, 116, 62, 1, 0, 3, 40, 41, 86, 12, 0, 5, 0, 6, 10, 0, 4,
            0, 7, 1, 0, 4, 109, 97, 105, 110, 1, 0, 22, 40, 91, 76, 106, 97, 118, 97, 47, 108, 97, 110,
            103, 47, 83, 116, 114, 105, 110, 103, 59, 41, 86, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97,
            110, 103, 47, 83, 121, 115, 116, 101, 109, 7, 0, 11, 1, 0, 3, 111, 117, 116, 1, 0, 21, 76,
            106, 97, 118, 97, 47, 105, 111, 47, 80, 114, 105, 110, 116, 83, 116, 114, 101, 97, 109, 59,
            12, 0, 13, 0, 14, 9, 0, 12, 0, 15, 1, 0, 12, 72, 101, 108, 108, 111, 32, 119, 111, 114, 108,
            100, 33, 8, 0, 17, 1, 0, 19, 106, 97, 118, 97, 47, 105, 111, 47, 80, 114, 105, 110, 116, 83,
            116, 114, 101, 97, 109, 7, 0, 19, 1, 0, 7, 112, 114, 105, 110, 116, 108, 110, 1, 0, 21, 40,
            76, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 41, 86,
            12, 0, 21, 0, 22, 10, 0, 20, 0, 23, 1, 0, 4, 67, 111, 100, 101, 0, 1, 0, 2, 0, 4, 0, null,
            0, 0, 0, 2, 0, 1, 0, 5, 0, 6, 0, 1, 0, 25, 0, 0, 0, 17, 0, 1, 0, 1, 0, 0, 0, 5, 42, 183, 0,
            8, 177, 0, null, 0, 0, 0, 9, 0, 9, 0, 10, 0, 1, 0, 25, 0, 0, 0, 21, 0, 2, 0, 2, 0, 0, 0, 9,
            178, 0, 16, 18, 18, 182, 0, 24, 177, 0, null, 0, 0, 0, 0,
        ];

        $this->assertEquals($expectedClassStructure, $code);
    }
}
