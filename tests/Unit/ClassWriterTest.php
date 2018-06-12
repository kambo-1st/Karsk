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
     * Tests generating basic class with "hello world" message
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
    public function testGenerateBasicClass()
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

    /**
     * Tests generating basic class with assign one variable to another
     *
     * Generates the bytecode corresponding to the following Java class:
     *
     * public class AssignVariable {
     *     public static void main(String[] args) {
     *         int a = 10;
     *         int b = 40;
     *
     *         a = b;
     *
     *         System.out.println(a);
     *     }
     *
     * }
     *
     * @return void
     */
    public function testGenerateAssign()
    {
        $cw = new ClassWriter(0);
        $cw->visit(
            Opcodes::V1_8,
            Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER,
            "AssignVariable",
            null,
            "java/lang/Object",
            null
        );

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, "<init>", "()V", null, null);
        $mv->visitCode();

        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, "java/lang/Object", "<init>", "()V", false);
        $mv->visitInsn(Opcodes::RETURN_);

        $mv->visitMaxs(1, 1);
        $mv->visitEnd();

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC, "main", "([Ljava/lang/String;)V", null, null);
        $mv->visitCode();

        $mv->visitIntInsn(Opcodes::BIPUSH, 10);
        $mv->visitVarInsn(Opcodes::ISTORE, 1);

        $mv->visitIntInsn(Opcodes::BIPUSH, 40);
        $mv->visitVarInsn(Opcodes::ISTORE, 2);

        $mv->visitVarInsn(Opcodes::ILOAD, 2);
        $mv->visitVarInsn(Opcodes::ISTORE, 1);

        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 1);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

        $mv->visitInsn(Opcodes::RETURN_);

        $mv->visitMaxs(2, 3);
        $mv->visitEnd();

        $cw->visitEnd();

        $code = $cw->toByteArray();

        $expectedClassStructure = [
            202, 254, 186, 190, 0, 0, 0, 52, 0, 24, 1, 0, 14, 65, 115, 115, 105, 103, 110, 86, 97,
            114, 105, 97, 98, 108, 101, 7, 0, 1, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103,
            47, 79, 98, 106, 101, 99, 116, 7, 0, 3, 1, 0, 6, 60, 105, 110, 105, 116, 62, 1, 0, 3, 40,
            41, 86, 12, 0, 5, 0, 6, 10, 0, 4, 0, 7, 1, 0, 4, 109, 97, 105, 110, 1, 0, 22, 40, 91, 76,
            106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 41, 86, 1,
            0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 121, 115, 116, 101, 109, 7, 0, 11,
            1, 0, 3, 111, 117, 116, 1, 0, 21, 76, 106, 97, 118, 97, 47, 105, 111, 47, 80, 114, 105,
            110, 116, 83, 116, 114, 101, 97, 109, 59, 12, 0, 13, 0, 14, 9, 0, 12, 0, 15, 1, 0, 19, 106,
            97, 118, 97, 47, 105, 111, 47, 80, 114, 105, 110, 116, 83, 116, 114, 101, 97, 109, 7, 0, 17,
            1, 0, 7, 112, 114, 105, 110, 116, 108, 110, 1, 0, 4, 40, 73, 41, 86, 12, 0, 19, 0, 20, 10,
            0, 18, 0, 21, 1, 0, 4, 67, 111, 100, 101, 0, 33, 0, 2, 0, 4, 0, null, 0, 0, 0, 2, 0, 1, 0,
            5, 0, 6, 0, 1, 0, 23, 0, 0, 0, 17, 0, 1, 0, 1, 0, 0, 0, 5, 42, 183, 0, 8, 177, 0, null, 0,
            0, 0, 9, 0, 9, 0, 10, 0, 1, 0, 23, 0, 0, 0, 28, 0, 2, 0, 3, 0, 0, 0, 16, 16, 10, 60, 16, 40,
            61, 28, 60, 178, 0, 16, 27, 182, 0, 22, 177, 0, null, 0, 0, 0, 0,
        ];

        $this->assertEquals($expectedClassStructure, $code);
    }

    /**
     * Tests generating basic class with assign one variable to another
     *
     * Generates the bytecode corresponding to the following Java class:
     *
     * public class AssignVariable {
     *     public static void main(String[] args) {
     *         int a = 10;
     *         int b = 40;
     *
     *         a = b;
     *
     *         System.out.println(a);
     *     }
     *
     * }
     *
     * @return void
     */
    public function testGenerateSimpleMath()
    {
        $cw = new ClassWriter(0);

        $cw->visit(Opcodes::V1_8, Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER, "SimpleMath", null, "java/lang/Object", null);

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, "<init>", "()V", null, null);
        $mv->visitCode();

        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, "java/lang/Object", "<init>", "()V", false);
        $mv->visitInsn(Opcodes::RETURN_);

        $mv->visitMaxs(1, 1);
        $mv->visitEnd();

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC, "main", "([Ljava/lang/String;)V", null, null);
        $mv->visitCode();

        $mv->visitIntInsn(Opcodes::BIPUSH, 10);
        $mv->visitVarInsn(Opcodes::ISTORE, 1);

        $mv->visitInsn(Opcodes::ICONST_1);
        $mv->visitVarInsn(Opcodes::ISTORE, 2);

        $mv->visitInsn(Opcodes::ICONST_2);
        $mv->visitVarInsn(Opcodes::ISTORE, 3);

        $mv->visitInsn(Opcodes::ICONST_5);
        $mv->visitVarInsn(Opcodes::ISTORE, 4);

        $mv->visitVarInsn(Opcodes::ILOAD, 1);
        $mv->visitVarInsn(Opcodes::ILOAD, 2);
        $mv->visitVarInsn(Opcodes::ILOAD, 3);
        $mv->visitInsn(Opcodes::IADD);
        $mv->visitInsn(Opcodes::IMUL);
        $mv->visitVarInsn(Opcodes::ILOAD, 4);
        $mv->visitInsn(Opcodes::IDIV);
        $mv->visitVarInsn(Opcodes::ISTORE, 1);

        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 1);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

        $mv->visitInsn(Opcodes::RETURN_);

        $mv->visitMaxs(3, 5);
        $mv->visitEnd();

        $cw->visitEnd();

        $code = $cw->toByteArray();

        $expectedClassStructure = [
            202, 254, 186, 190, 0, 0, 0, 52, 0, 24, 1, 0, 10, 83, 105, 109, 112, 108, 101, 77, 97,
            116, 104, 7, 0, 1, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 79, 98, 106,
            101, 99, 116, 7, 0, 3, 1, 0, 6, 60, 105, 110, 105, 116, 62, 1, 0, 3, 40, 41, 86, 12, 0,
            5, 0, 6, 10, 0, 4, 0, 7, 1, 0, 4, 109, 97, 105, 110, 1, 0, 22, 40, 91, 76, 106, 97, 118,
            97, 47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 41, 86, 1, 0, 16, 106,
            97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 121, 115, 116, 101, 109, 7, 0, 11, 1, 0, 3,
            111, 117, 116, 1, 0, 21, 76, 106, 97, 118, 97, 47, 105, 111, 47, 80, 114, 105, 110, 116,
            83, 116, 114, 101, 97, 109, 59, 12, 0, 13, 0, 14, 9, 0, 12, 0, 15, 1, 0, 19, 106, 97, 118,
            97, 47, 105, 111, 47, 80, 114, 105, 110, 116, 83, 116, 114, 101, 97, 109, 7, 0, 17, 1, 0,
            7, 112, 114, 105, 110, 116, 108, 110, 1, 0, 4, 40, 73, 41, 86, 12, 0, 19, 0, 20, 10, 0, 18,
            0, 21, 1, 0, 4, 67, 111, 100, 101, 0, 33, 0, 2, 0, 4, 0,  null, 0, 0, 0, 2, 0, 1, 0, 5, 0,
            6, 0, 1, 0, 23, 0, 0, 0, 17, 0, 1, 0, 1, 0, 0, 0, 5, 42, 183, 0, 8, 177, 0,  null, 0, 0, 0,
            9, 0, 9, 0, 10, 0, 1, 0, 23, 0, 0, 0, 39, 0, 3, 0, 5, 0, 0, 0, 27, 16, 10, 60, 4, 61, 5,
            62, 8, 54, 4, 27, 28, 29, 96, 104, 21, 4, 108, 60, 178, 0, 16, 27, 182, 0, 22, 177, 0,  null,
            0, 0, 0, 0,
        ];

        $this->assertEquals($expectedClassStructure, $code);
    }
}
