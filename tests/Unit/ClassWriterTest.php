<?php
namespace Kambo\Tests\Karsk\Unit;

use PHPUnit\Framework\TestCase;

use Kambo\Karsk\ClassWriter;
use Kambo\Karsk\Opcodes;
use Kambo\Karsk\Label;

use Kambo\Karsk\Type;

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
        $cw->visit(
            Opcodes::V1_8,
            Opcodes::ACC_PUBLIC,
            "Example",
            null,
            "java/lang/Object",
            null
        );

        $mw = $cw->visitMethod(
            Opcodes::ACC_PUBLIC,
            "<init>",
            "()V",
            null,
            null
        );
        $mw->visitVarInsn(Opcodes::ALOAD, 0);

        $mw->visitMethodInsn(
            Opcodes::INVOKESPECIAL,
            "java/lang/Object",
            "<init>",
            "()V",
            false
        );
        $mw->visitInsn(Opcodes::RETURN_);
        $mw->visitMaxs(1, 1);
        $mw->visitEnd();

        $mw2 = $cw->visitMethod(
            (Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC),
            "main",
            "([Ljava/lang/String;)V",
            null,
            null
        );
        $mw2->visitFieldInsn(
            Opcodes::GETSTATIC,
            "java/lang/System",
            "out",
            "Ljava/io/PrintStream;"
        );
        $mw2->visitLdcInsn("Hello world!");

        $mw2->visitMethodInsn(
            Opcodes::INVOKEVIRTUAL,
            "java/io/PrintStream",
            "println",
            "(Ljava/lang/String;)V",
            false
        );
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

        $mv = $cw->visitMethod(
            Opcodes::ACC_PUBLIC,
            "<init>",
            "()V",
            null,
            null
        );
        $mv->visitCode();

        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitMethodInsn(
            Opcodes::INVOKESPECIAL,
            "java/lang/Object",
            "<init>",
            "()V",
            false
        );
        $mv->visitInsn(Opcodes::RETURN_);

        $mv->visitMaxs(1, 1);
        $mv->visitEnd();

        $mv = $cw->visitMethod(
            Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC,
            "main",
            "([Ljava/lang/String;)V",
            null,
            null
        );
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

        $cw->visit(
            Opcodes::V1_8,
            Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER,
            "SimpleMath",
            null,
            "java/lang/Object",
            null
        );

        $mv = $cw->visitMethod(
            Opcodes::ACC_PUBLIC,
            "<init>",
            "()V",
            null,
            null
        );
        $mv->visitCode();

        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitMethodInsn(
            Opcodes::INVOKESPECIAL,
            "java/lang/Object",
            "<init>",
            "()V",
            false
        );
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

    /**
     * Tests generating basic class with simple condition
     *
     * Generates the bytecode corresponding to the following Java class:
     *
     *   public class SimpleCondition {
     *      public static void main(String[] args) {
     *          int a = 10;
     *          float c = 5;
     *          int b = 5;
     *
     *          if (a == 10) {
     *              System.out.println(b);
     *          }
     *
     *          System.out.println(a);
     *      }
     *   }
     *
     * @return void
     */
    public function testGenerateSimpleCondition() : void
    {
        $cw = new ClassWriter(0);

        $cw->visit(
            Opcodes::V1_8,
            Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER,
            "SimpleCondition",
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

        $mv->visitIntInsn(Opcodes::BIPUSH, 5);
        $mv->visitVarInsn(Opcodes::ISTORE, 2);

        $mv->visitInsn(Opcodes::ICONST_5);
        $mv->visitVarInsn(Opcodes::ISTORE, 3);


        $mv->visitVarInsn(Opcodes::ILOAD, 1);
        $mv->visitIntInsn(Opcodes::BIPUSH, 10);

        $l4 = new Label();
        $mv->visitJumpInsn(Opcodes::IF_ICMPNE, $l4);

        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 3);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);
        $mv->visitLabel($l4);
        $mv->visitLineNumber(14, $l4);
        $mv->visitFrame(Opcodes::F_APPEND, 3, [Opcodes::INTEGER, Opcodes::FLOAT, Opcodes::INTEGER], 0, null);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 1);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

        $mv->visitInsn(Opcodes::RETURN_);

        $mv->visitMaxs(2, 4);
        $mv->visitEnd();

        $cw->visitEnd();

        $code = $cw->toByteArray();

        $expectedClassStructure = [
            202, 254, 186, 190, 0, 0, 0, 52, 0, 26, 1, 0, 15, 83, 105, 109, 112, 108, 101, 67, 111, 110,
            100, 105, 116, 105, 111, 110, 7, 0, 1, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47,
            79, 98, 106, 101, 99, 116, 7, 0, 3, 1, 0, 6, 60, 105, 110, 105, 116, 62, 1, 0, 3, 40, 41, 86,
            12, 0, 5, 0, 6, 10, 0, 4, 0, 7, 1, 0, 4, 109, 97, 105, 110, 1, 0, 22, 40, 91, 76, 106, 97,
            118, 97, 47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 41, 86, 1, 0, 16, 106,
            97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 121, 115, 116, 101, 109, 7, 0, 11, 1, 0, 3, 111,
            117, 116, 1, 0, 21, 76, 106, 97, 118, 97, 47, 105, 111, 47, 80, 114, 105, 110, 116, 83, 116,
            114, 101, 97, 109, 59, 12, 0, 13, 0, 14, 9, 0, 12, 0, 15, 1, 0, 19, 106, 97, 118, 97, 47, 105,
            111, 47, 80, 114, 105, 110, 116, 83, 116, 114, 101, 97, 109, 7, 0, 17, 1, 0, 7, 112, 114, 105,
            110, 116, 108, 110, 1, 0, 4, 40, 73, 41, 86, 12, 0, 19, 0, 20, 10, 0, 18, 0, 21, 1, 0, 4, 67,
            111, 100, 101, 1, 0, 15, 76, 105, 110, 101, 78, 117, 109, 98, 101, 114, 84, 97, 98, 108, 101,
            1, 0, 13, 83, 116, 97, 99, 107, 77, 97, 112, 84, 97, 98, 108, 101, 0, 33, 0, 2, 0, 4, 0, null,
            0, 0, 0, 2, 0, 1, 0, 5, 0, 6, 0, 1, 0, 23, 0, 0, 0, 17, 0, 1, 0, 1, 0, 0, 0, 5, 42, 183, 0,
            8, 177, 0, null, 0, 0, 0, 9, 0, 9, 0, 10, 0, 1, 0, 23, 0, 0, 0, 67, 0, 2, 0, 4, 0, 0, 0, 29,
            16, 10, 60, 16, 5, 61, 8, 62, 27, 16, 10, 160, 0, 10, 178, 0, 16, 29, 182, 0, 22, 178, 0, 16,
            27, 182, 0, 22, 177, 0, null, 0, 2, 0, 24, 0, 0, 0, 6, 0, 1, 0, 21, 0, 14, 0, 25, 0, 0, 0, 8,
            0, 1, 254, 0, 21, 1, 2, 1, 0, 0
        ];

        $this->assertEquals($expectedClassStructure, $code);
    }

    /**
     * Tests generating basic class with switch statement
     *
     * Generates the bytecode corresponding to the following Java class:
     *
     *
     * public class Switch {
     *
     *     public static void main(String[] args) {
     *         int month = 2;
     *         String monthString;
     *
     *         switch (month) {
     *             case 1:  monthString = "January";
     *                 break;
     *             case 2:  monthString = "February";
     *                 break;
     *             default: monthString = "Invalid month";
     *                 break;
     *         }
     *
     *         System.out.println(monthString);
     *     }
     * }
     *
     *
     * @return void
     */
    public function testGenerateSwitch() : void
    {
        $cw = new ClassWriter(0);

        $cw->visit(Opcodes::V1_8, Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER, "Switch", null, "java/lang/Object", null);

        $cw->visitSource("Switch.java", null);

        $mv =  $cw->visitMethod(Opcodes::ACC_PUBLIC, "<init>", "()V", null, null);
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(3, $l0);
        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, "java/lang/Object", "<init>", "()V", false);
        $mv->visitInsn(Opcodes::RETURN_);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLocalVariable("this", "Lkambo/Switch;", null, $l0, $l1, 0);
        $mv->visitMaxs(1, 1);
        $mv->visitEnd();

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC, "main", "([Ljava/lang/String;)V", null, null);
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(6, $l0);
        $mv->visitInsn(Opcodes::ICONST_2);
        $mv->visitVarInsn(Opcodes::ISTORE, 1);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLineNumber(9, $l1);
        $mv->visitVarInsn(Opcodes::ILOAD, 1);

        $l2 = new Label();
        $l3 = new Label();
        $l4 = new Label();
        $mv->visitTableSwitchInsn(1, 2, $l4, [$l2, $l3]);
        $mv->visitLabel($l2);
        $mv->visitLineNumber(10, $l2);
        $mv->visitFrame(Opcodes::F_APPEND, 1, [Opcodes::INTEGER], 0, null);
        $mv->visitLdcInsn("January");
        $mv->visitVarInsn(Opcodes::ASTORE, 2);

        $l5 = new Label();
        $mv->visitLabel($l5);
        $mv->visitLineNumber(11, $l5);

        $l6 = new Label();
        $mv->visitJumpInsn(Opcodes::GOTO_, $l6);
        $mv->visitLabel($l3);
        $mv->visitLineNumber(12, $l3);
        $mv->visitFrame(Opcodes::F_SAME, 0, null, 0, null);
        $mv->visitLdcInsn("February");
        $mv->visitVarInsn(Opcodes::ASTORE, 2);

        $l7 = new Label();
        $mv->visitLabel($l7);
        $mv->visitLineNumber(13, $l7);
        $mv->visitJumpInsn(Opcodes::GOTO_, $l6);

        $mv->visitLabel($l4);
        $mv->visitLineNumber(14, $l4);
        $mv->visitFrame(Opcodes::F_SAME, 0, null, 0, null);
        $mv->visitLdcInsn("Invalid month");
        $mv->visitVarInsn(Opcodes::ASTORE, 2);

        $mv->visitLabel($l6);
        $mv->visitLineNumber(18, $l6);
        $mv->visitFrame(Opcodes::F_APPEND, 1, ["java/lang/String"], 0, null);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ALOAD, 2);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(Ljava/lang/String;)V", false);

        $l8 = new Label();
        $mv->visitLabel($l8);
        $mv->visitLineNumber(19, $l8);
        $mv->visitInsn(Opcodes::RETURN_);

        $l9 = new Label();
        $mv->visitLabel($l9);
        $mv->visitLocalVariable("args", "[Ljava/lang/String;", null, $l0, $l9, 0);
        $mv->visitLocalVariable("month", "I", null, $l1, $l9, 1);
        $mv->visitLocalVariable("monthString", "Ljava/lang/String;", null, $l5, $l3, 2);
        $mv->visitLocalVariable("monthString", "Ljava/lang/String;", null, $l7, $l4, 2);
        $mv->visitLocalVariable("monthString", "Ljava/lang/String;", null, $l6, $l9, 2);
        $mv->visitMaxs(2, 3);
        $mv->visitEnd();

        $cw->visitEnd();

        $code = $cw->toByteArray();

        $expectedClassStructure = [
            202, 254, 186, 190, 0, 0, 0, 52, 0, 45, 1, 0, 6, 83, 119, 105, 116, 99, 104, 7, 0, 1, 1,
            0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 79, 98, 106, 101, 99, 116, 7, 0, 3,
            1, 0, 11, 83, 119, 105, 116, 99, 104, 46, 106, 97, 118, 97, 1, 0, 6, 60, 105, 110, 105,
            116, 62, 1, 0, 3, 40, 41, 86, 12, 0, 6, 0, 7, 10, 0, 4, 0, 8, 1, 0, 4, 116, 104, 105, 115,
            1, 0, 14, 76, 107, 97, 109, 98, 111, 47, 83, 119, 105, 116, 99, 104, 59, 1, 0, 4, 109, 97,
            105, 110, 1, 0, 22, 40, 91, 76, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 116, 114,
            105, 110, 103, 59, 41, 86, 1, 0, 7, 74, 97, 110, 117, 97, 114, 121, 8, 0, 14, 1, 0, 8, 70,
            101, 98, 114, 117, 97, 114, 121, 8, 0, 16, 1, 0, 13, 73, 110, 118, 97, 108, 105, 100, 32, 109,
            111, 110, 116, 104, 8, 0, 18, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 116,
            114, 105, 110, 103, 7, 0, 20, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 121,
            115, 116, 101, 109, 7, 0, 22, 1, 0, 3, 111, 117, 116, 1, 0, 21, 76, 106, 97, 118, 97, 47, 105,
            111, 47, 80, 114, 105, 110, 116, 83, 116, 114, 101, 97, 109, 59, 12, 0, 24, 0, 25, 9, 0, 23,
            0, 26, 1, 0, 19, 106, 97, 118, 97, 47, 105, 111, 47, 80, 114, 105, 110, 116, 83, 116, 114,
            101, 97, 109, 7, 0, 28, 1, 0, 7, 112, 114, 105, 110, 116, 108, 110, 1, 0, 21, 40, 76, 106, 97,
            118, 97, 47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 41, 86, 12, 0, 30, 0, 31,
            10, 0, 29, 0, 32, 1, 0, 4, 97, 114, 103, 115, 1, 0, 19, 91, 76, 106, 97, 118, 97, 47, 108, 97,
            110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 1, 0, 5, 109, 111, 110, 116, 104, 1, 0, 1, 73, 1,
            0, 11, 109, 111, 110, 116, 104, 83, 116, 114, 105, 110, 103, 1, 0, 18, 76, 106, 97, 118, 97, 47,
            108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 1, 0, 4, 67, 111, 100, 101, 1, 0, 18, 76,
            111, 99, 97, 108, 86, 97, 114, 105, 97, 98, 108, 101, 84, 97, 98, 108, 101, 1, 0, 15, 76, 105, 110,
            101, 78, 117, 109, 98, 101, 114, 84, 97, 98, 108, 101, 1, 0, 13, 83, 116, 97, 99, 107, 77, 97, 112,
            84, 97, 98, 108, 101, 1, 0, 10, 83, 111, 117, 114, 99, 101, 70, 105, 108, 101, 0, 33, 0, 2, 0, 4, 0,
            null, 0, 0, 0, 2, 0, 1, 0, 6, 0, 7, 0, 1, 0, 40, 0, 0, 0, 47, 0, 1, 0, 1, 0, 0, 0, 5, 42, 183, 0, 9,
            177, 0, null, 0, 2, 0, 41, 0, 0, 0, 12, 0, 1, 0, 0, 0, 5, 0, 10, 0, 11, 0, 0, 0, 42, 0, 0, 0, 6, 0,
            1, 0, 0, 0, 3, 0, 9, 0, 12, 0, 13, 0, 1, 0, 40, 0, 0, 0, 181, 0, 2, 0, 3, 0, 0, 0, 47, 5, 60, 27,
            170, 0, 0, 0, 33, 0, 0, 0, 1, 0, 0, 0, 2, 0, 0, 0, 21, 0, 0, 0, 27, 18, 15, 77, 167, 0, 12, 18, 17,
            77, 167, 0, 6, 18, 19, 77, 178, 0, 27, 44, 182, 0, 33, 177, 0, null, 0, 3, 0, 41, 0, 0, 0, 52, 0, 5,
            0, 0, 0, 47, 0, 34, 0, 35, 0, 0, 0, 2, 0, 45, 0, 36, 0, 37, 0, 1, 0, 27, 0, 3, 0, 38, 0, 39, 0, 2, 0,
            33, 0, 3, 0, 38, 0, 39, 0, 2, 0, 39, 0, 8, 0, 38, 0, 39, 0, 2, 0, 42, 0, 0, 0, 38, 0, 9, 0, 0, 0, 6,
            0, 2, 0, 9, 0, 24, 0, 10, 0, 27, 0, 11, 0, 30, 0, 12, 0, 33, 0, 13, 0, 36, 0, 14, 0, 39, 0, 18, 0, 46,
            0, 19, 0, 43, 0, 0, 0, 14, 0, 4, 252, 0, 24, 1, 5, 5, 252, 0, 2, 7, 0, 21, 0, 1, 0, 44, 0, 0, 0, 2, 0, 5,
        ];

        $this->assertEquals($expectedClassStructure, $code);
    }

    /**
     * Tests generating basic class with switch statement
     *
     * Generates the bytecode corresponding to the following Java class:
     *
     *    public class ForLoop {
     *       public static void main(String[] args) {
     *           for (int i = 0; i < 15; i++) {
     *               System.out.println(i);
     *           }
     *       }
     *    }
     *
     * @return void
     */
    public function testGenerateForLoop() : void
    {
        $cw = new ClassWriter(0);

        $cw->visit(
            Opcodes::V1_8,
            Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER,
            "ForLoop",
            null,
            "java/lang/Object",
            null
        );

        $cw->visitSource("ForLoop.java", null);

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, "<init>", "()V", null, null);
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(3, $l0);
        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitMethodInsn(
            Opcodes::INVOKESPECIAL,
            "java/lang/Object",
            "<init>",
            "()V",
            false
        );
        $mv->visitInsn(Opcodes::RETURN_);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLocalVariable("this", "Lkambo/ForLoop;", null, $l0, $l1, 0);
        $mv->visitMaxs(1, 1);
        $mv->visitEnd();

        $mv = $cw->visitMethod(
            Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC,
            "main",
            "([Ljava/lang/String;)V",
            null,
            null
        );
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(6, $l0);
        $mv->visitInsn(Opcodes::ICONST_0);
        $mv->visitVarInsn(Opcodes::ISTORE, 1);

        $l1 = new Label();
        $mv->visitLabel($l1);

        $l2 = new Label();
        $mv->visitJumpInsn(Opcodes::GOTO_, $l2);

        $l3 = new Label();
        $mv->visitLabel($l3);
        $mv->visitLineNumber(7, $l3);
        $mv->visitFrame(Opcodes::F_APPEND, 1, [Opcodes::INTEGER], 0, null);
        $mv->visitFieldInsn(
            Opcodes::GETSTATIC,
            "java/lang/System",
            "out",
            "Ljava/io/PrintStream;"
        );
        $mv->visitVarInsn(Opcodes::ILOAD, 1);
        $mv->visitMethodInsn(
            Opcodes::INVOKEVIRTUAL,
            "java/io/PrintStream",
            "println",
            "(I)V",
            false
        );

        $l4 = new Label();
        $mv->visitLabel($l4);
        $mv->visitLineNumber(6, $l4);
        $mv->visitIincInsn(1, 1);
        $mv->visitLabel($l2);
        $mv->visitFrame(Opcodes::F_SAME, 0, null, 0, null);
        $mv->visitVarInsn(Opcodes::ILOAD, 1);
        $mv->visitIntInsn(Opcodes::BIPUSH, 15);
        $mv->visitJumpInsn(Opcodes::IF_ICMPLT, $l3);

        $l5 = new Label();
        $mv->visitLabel($l5);
        $mv->visitLineNumber(9, $l5);
        $mv->visitInsn(Opcodes::RETURN_);

        $l6 = new Label();
        $mv->visitLabel($l6);
        $mv->visitLocalVariable("args", "[Ljava/lang/String;", null, $l0, $l6, 0);
        $mv->visitLocalVariable("i", "I", null, $l1, $l5, 1);
        $mv->visitMaxs(2, 2);
        $mv->visitEnd();

        $cw->visitEnd();

        $code = $cw->toByteArray();

        $expectedClassStructure = [
            202, 254, 186, 190, 0, 0, 0, 52, 0, 35, 1, 0, 7, 70, 111, 114, 76, 111, 111, 112, 7,
            0, 1, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 79, 98, 106, 101, 99,
            116, 7, 0, 3, 1, 0, 12, 70, 111, 114, 76, 111, 111, 112, 46, 106, 97, 118, 97, 1, 0,
            6, 60, 105, 110, 105, 116, 62, 1, 0, 3, 40, 41, 86, 12, 0, 6, 0, 7, 10, 0, 4, 0, 8,
            1, 0, 4, 116, 104, 105, 115, 1, 0, 15, 76, 107, 97, 109, 98, 111, 47, 70, 111, 114,
            76, 111, 111, 112, 59, 1, 0, 4, 109, 97, 105, 110, 1, 0, 22, 40, 91, 76, 106, 97, 118,
            97, 47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 41, 86, 1, 0, 16, 106,
            97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 121, 115, 116, 101, 109, 7, 0, 14, 1, 0,
            3, 111, 117, 116, 1, 0, 21, 76, 106, 97, 118, 97, 47, 105, 111, 47, 80, 114, 105, 110,
            116, 83, 116, 114, 101, 97, 109, 59, 12, 0, 16, 0, 17, 9, 0, 15, 0, 18, 1, 0, 19, 106,
            97, 118, 97, 47, 105, 111, 47, 80, 114, 105, 110, 116, 83, 116, 114, 101, 97, 109, 7,
            0, 20, 1, 0, 7, 112, 114, 105, 110, 116, 108, 110, 1, 0, 4, 40, 73, 41, 86, 12, 0, 22,
            0, 23, 10, 0, 21, 0, 24, 1, 0, 4, 97, 114, 103, 115, 1, 0, 19, 91, 76, 106, 97, 118,
            97, 47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 1, 0, 1, 105, 1, 0, 1,
            73, 1, 0, 4, 67, 111, 100, 101, 1, 0, 18, 76, 111, 99, 97, 108, 86, 97, 114, 105, 97,
            98, 108, 101, 84, 97, 98, 108, 101, 1, 0, 15, 76, 105, 110, 101, 78, 117, 109, 98, 101,
            114, 84, 97, 98, 108, 101, 1, 0, 13, 83, 116, 97, 99, 107, 77, 97, 112, 84, 97, 98, 108,
            101, 1, 0, 10, 83, 111, 117, 114, 99, 101, 70, 105, 108, 101, 0, 33, 0, 2, 0, 4, 0,  null,
            0, 0, 0, 2, 0, 1, 0, 6, 0, 7, 0, 1, 0, 30, 0, 0, 0, 47, 0, 1, 0, 1, 0, 0, 0, 5, 42, 183,
            0, 9, 177, 0,  null, 0, 2, 0, 31, 0, 0, 0, 12, 0, 1, 0, 0, 0, 5, 0, 10, 0, 11, 0, 0, 0,
            32, 0, 0, 0, 6, 0, 1, 0, 0, 0, 3, 0, 9, 0, 12, 0, 13, 0, 1, 0, 30, 0, 0, 0, 99, 0, 2, 0,
            2, 0, 0, 0, 22, 3, 60, 167, 0, 13, 178, 0, 19, 27, 182, 0, 25, 132, 1, 1, 27, 16, 15, 161,
            255, -13, 177, 0,  null, 0, 3, 0, 31, 0, 0, 0, 22, 0, 2, 0, 0, 0, 22, 0, 26, 0, 27, 0, 0, 0,
            2, 0, 19, 0, 28, 0, 29, 0, 1, 0, 32, 0, 0, 0, 18, 0, 4, 0, 0, 0, 6, 0, 5, 0, 7, 0, 12, 0,
            6, 0, 21, 0, 9, 0, 33, 0, 0, 0, 7, 0, 2, 252, 0, 5, 1, 9, 0, 1, 0, 34, 0, 0, 0, 2, 0, 5,
        ];
        
        $this->assertEquals($expectedClassStructure, $code);
    }

    /**
     * Tests generating simple class property and second method
     *
     * Generates the bytecode corresponding to the following Java class:
     *
     *       public class SimpleClass {
     *
     *           private String classname;
     *
     *           public SimpleClass(String name) {
     *               System.out.println(name );
     *               classname = name;
     *           }
     *
     *           public String getName() {
     *               return classname;
     *           }
     *
     *           public static void main(String[] args) {
     *               SimpleClass simpleInstance = new SimpleClass( "cool class" );
     *
     *               System.out.println(simpleInstance.getName());
     *           }
     *       }
     *
     * @return void
     */
    public function testGenerateSimpleClass() : void
    {
        $cw = new ClassWriter(0);

        $cw->visit(
            Opcodes::V1_8,
            Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER,
            "SimpleClass",
            null,
            "java/lang/Object",
            null
        );

        $cw->visitSource("SimpleClass.java", null);

        $fv = $cw->visitField(Opcodes::ACC_PRIVATE, "classname", "Ljava/lang/String;", null, null);
        $fv->visitEnd();

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, "<init>", "(Ljava/lang/String;)V", null, null);
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(7, $l0);
        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, "java/lang/Object", "<init>", "()V", false);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLineNumber(8, $l1);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ALOAD, 1);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(Ljava/lang/String;)V", false);

        $l2 = new Label();
        $mv->visitLabel($l2);
        $mv->visitLineNumber(9, $l2);
        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitVarInsn(Opcodes::ALOAD, 1);
        $mv->visitFieldInsn(Opcodes::PUTFIELD, "SimpleClass", "classname", "Ljava/lang/String;");

        $l3 = new Label();
        $mv->visitLabel($l3);
        $mv->visitLineNumber(10, $l3);
        $mv->visitInsn(Opcodes::RETURN_);

        $l4 = new Label();
        $mv->visitLabel($l4);
        $mv->visitLocalVariable("this", "LSimpleClass;", null, $l0, $l4, 0);
        $mv->visitLocalVariable("name", "Ljava/lang/String;", null, $l0, $l4, 1);
        $mv->visitMaxs(2, 2);
        $mv->visitEnd();

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, "getName", "()Ljava/lang/String;", null, null);
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(13, $l0);
        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitFieldInsn(Opcodes::GETFIELD, "SimpleClass", "classname", "Ljava/lang/String;");
        $mv->visitInsn(Opcodes::ARETURN);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLocalVariable("this", "LSimpleClass;", null, $l0, $l1, 0);
        $mv->visitMaxs(1, 1);
        $mv->visitEnd();

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC, "main", "([Ljava/lang/String;)V", null, null);
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(17, $l0);
        $mv->visitTypeInsn(Opcodes::NEW_, "SimpleClass");
        $mv->visitInsn(Opcodes::DUP);
        $mv->visitLdcInsn("cool class");
        $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, "SimpleClass", "<init>", "(Ljava/lang/String;)V", false);
        $mv->visitVarInsn(Opcodes::ASTORE, 1);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLineNumber(19, $l1);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ALOAD, 1);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "SimpleClass", "getName", "()Ljava/lang/String;", false);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(Ljava/lang/String;)V", false);

        $l2 = new Label();
        $mv->visitLabel($l2);
        $mv->visitLineNumber(20, $l2);
        $mv->visitInsn(Opcodes::RETURN_);

        $l3 = new Label();
        $mv->visitLabel($l3);
        $mv->visitLocalVariable("args", "[Ljava/lang/String;", null, $l0, $l3, 0);
        $mv->visitLocalVariable("simpleInstance", "LSimpleClass;", null, $l1, $l3, 1);
        $mv->visitMaxs(3, 2);
        $mv->visitEnd();

        $cw->visitEnd();

        $code = $cw->toByteArray();

        $expectedClassStructure = [
            202, 254, 186, 190, 0, 0, 0, 52, 0, 46, 1, 0, 11, 83, 105, 109, 112, 108, 101, 67, 108,
            97, 115, 115, 7, 0, 1, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 79, 98,
            106, 101, 99, 116, 7, 0, 3, 1, 0, 16, 83, 105, 109, 112, 108, 101, 67, 108, 97, 115, 115,
            46, 106, 97, 118, 97, 1, 0, 9, 99, 108, 97, 115, 115, 110, 97, 109, 101, 1, 0, 18, 76,
            106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 1, 0, 6, 60,
            105, 110, 105, 116, 62, 1, 0, 21, 40, 76, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47,
            83, 116, 114, 105, 110, 103, 59, 41, 86, 1, 0, 3, 40, 41, 86, 12, 0, 8, 0, 10, 10, 0, 4,
            0, 11, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 121, 115, 116, 101, 109,
            7, 0, 13, 1, 0, 3, 111, 117, 116, 1, 0, 21, 76, 106, 97, 118, 97, 47, 105, 111, 47, 80,
            114, 105, 110, 116, 83, 116, 114, 101, 97, 109, 59, 12, 0, 15, 0, 16, 9, 0, 14, 0, 17, 1,
            0, 19, 106, 97, 118, 97, 47, 105, 111, 47, 80, 114, 105, 110, 116, 83, 116, 114, 101, 97,
            109, 7, 0, 19, 1, 0, 7, 112, 114, 105, 110, 116, 108, 110, 12, 0, 21, 0, 9, 10, 0, 20, 0,
            22, 12, 0, 6, 0, 7, 9, 0, 2, 0, 24, 1, 0, 4, 116, 104, 105, 115, 1, 0, 13, 76, 83, 105, 109,
            112, 108, 101, 67, 108, 97, 115, 115, 59, 1, 0, 4, 110, 97, 109, 101, 1, 0, 7, 103, 101, 116,
            78, 97, 109, 101, 1, 0, 20, 40, 41, 76, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 116,
            114, 105, 110, 103, 59, 1, 0, 4, 109, 97, 105, 110, 1, 0, 22, 40, 91, 76, 106, 97, 118, 97,
            47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 41, 86, 1, 0, 10, 99, 111, 111,
            108, 32, 99, 108, 97, 115, 115, 8, 0, 33, 12, 0, 8, 0, 9, 10, 0, 2, 0, 35, 12, 0, 29, 0, 30,
            10, 0, 2, 0, 37, 1, 0, 4, 97, 114, 103, 115, 1, 0, 19, 91, 76, 106, 97, 118, 97, 47, 108, 97,
            110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 1, 0, 14, 115, 105, 109, 112, 108, 101, 73, 110,
            115, 116, 97, 110, 99, 101, 1, 0, 4, 67, 111, 100, 101, 1, 0, 18, 76, 111, 99, 97, 108, 86, 97,
            114, 105, 97, 98, 108, 101, 84, 97, 98, 108, 101, 1, 0, 15, 76, 105, 110, 101, 78, 117, 109,
            98, 101, 114, 84, 97, 98, 108, 101, 1, 0, 10, 83, 111, 117, 114, 99, 101, 70, 105, 108, 101,
            0, 33, 0, 2, 0, 4, 0, null, 0, 1, 0, 2, 0, 6, 0, 7, 0, 0, 0, 3, 0, 1, 0, 8, 0, 9, 0, 1, 0, 42,
            0, 0, 0, 81, 0, 2, 0, 2, 0, 0, 0, 17, 42, 183, 0, 12, 178, 0, 18, 43, 182, 0, 23, 42, 43, 181,
            0, 25, 177, 0, null, 0, 2, 0, 43, 0, 0, 0, 22, 0, 2, 0, 0, 0, 17, 0, 26, 0, 27, 0, 0, 0, 0, 0,
            17, 0, 28, 0, 7, 0, 1, 0, 44, 0, 0, 0, 18, 0, 4, 0, 0, 0, 7, 0, 4, 0, 8, 0, 11, 0, 9, 0, 16, 0,
            10, 0, 1, 0, 29, 0, 30, 0, 1, 0, 42, 0, 0, 0, 47, 0, 1, 0, 1, 0, 0, 0, 5, 42, 180, 0, 25, 176,
            0, null, 0, 2, 0, 43, 0, 0, 0, 12, 0, 1, 0, 0, 0, 5, 0, 26, 0, 27, 0, 0, 0, 44, 0, 0, 0, 6, 0,
            1, 0, 0, 0, 13, 0, 9, 0, 31, 0, 32, 0, 1, 0, 42, 0, 0, 0, 81, 0, 3, 0, 2, 0, 0, 0, 21, 187, 0,
            2, 89, 18, 34, 183, 0, 36, 76, 178, 0, 18, 43, 182, 0, 38, 182, 0, 23, 177, 0, null, 0, 2, 0,
            43, 0, 0, 0, 22, 0, 2, 0, 0, 0, 21, 0, 39, 0, 40, 0, 0, 0, 10, 0, 11, 0, 41, 0, 27, 0, 1, 0,
            44, 0, 0, 0, 14, 0, 3, 0, 0, 0, 17, 0, 10, 0, 19, 0, 20, 0, 20, 0, 1, 0, 45, 0, 0, 0, 2, 0, 5,
        ];

        $this->assertEquals($expectedClassStructure, $code);
    }

    /**
     * Tests generating simple class property and second method
     *
     * Generates the bytecode corresponding to the following Java class:
     *
     *       public class Variables {
     *
     *           public static void main(String[] args) {
     *               boolean bool = true;
     *               char c = 'C';
     *               byte b = 100;
     *               short s = 10000;
     *               int i = 100000;
     *               double d = 3.14;
     *               int[] anArray = new int[10];
     *               String string = "im a string";
     *
     *               anArray[1] = 42;
     *
     *               System.out.println(bool);
     *               System.out.println(c);
     *               System.out.println(b);
     *               System.out.println(s);
     *               System.out.println(i);
     *               System.out.println(d);
     *               System.out.println(string);
     *               System.out.println(anArray.length);
     *           }
     *       }
     *
     *
     * @return void
     */
    public function testGenerateVariables() : void
    {
        $cw = new ClassWriter(0);

        $cw->visit(
            Opcodes::V1_8,
            Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER,
            "Variables",
            null,
            "java/lang/Object",
            null
        );

        $cw->visitSource("Variables.java", null);

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, "<init>", "()V", null, null);
        $mv->visitCode();
        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(3, $l0);
        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitMethodInsn(
            Opcodes::INVOKESPECIAL,
            "java/lang/Object",
            "<init>",
            "()V",
            false
        );
        $mv->visitInsn(Opcodes::RETURN_);
        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLocalVariable("this", "LVariables;", null, $l0, $l1, 0);
        $mv->visitMaxs(1, 1);
        $mv->visitEnd();

        $mv = $cw->visitMethod(
            Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC,
            "main",
            "([Ljava/lang/String;)V",
            null,
            null
        );
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(6, $l0);
        $mv->visitInsn(Opcodes::ICONST_1);
        $mv->visitVarInsn(Opcodes::ISTORE, 1);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLineNumber(7, $l1);
        $mv->visitIntInsn(Opcodes::BIPUSH, 67);
        $mv->visitVarInsn(Opcodes::ISTORE, 2);

        $l2 = new Label();
        $mv->visitLabel($l2);
        $mv->visitLineNumber(8, $l2);
        $mv->visitIntInsn(Opcodes::BIPUSH, 100);
        $mv->visitVarInsn(Opcodes::ISTORE, 3);

        $l3 = new Label();
        $mv->visitLabel($l3);
        $mv->visitLineNumber(9, $l3);
        $mv->visitIntInsn(Opcodes::SIPUSH, 10000);
        $mv->visitVarInsn(Opcodes::ISTORE, 4);

        $l4 = new Label();
        $mv->visitLabel($l4);
        $mv->visitLineNumber(10, $l4);
        $mv->visitLdcInsn(new Type\Integer(100000));
        $mv->visitVarInsn(Opcodes::ISTORE, 5);

        $l5 = new Label();
        $mv->visitLabel($l5);
        $mv->visitLineNumber(11, $l5);
        $mv->visitLdcInsn(new Type\Double("3.14"));
        $mv->visitVarInsn(Opcodes::DSTORE, 6);

        $l6 = new Label();
        $mv->visitLabel($l6);
        $mv->visitLineNumber(12, $l6);
        $mv->visitIntInsn(Opcodes::BIPUSH, 10);
        $mv->visitIntInsn(Opcodes::NEWARRAY, Opcodes::T_INT);
        $mv->visitVarInsn(Opcodes::ASTORE, 8);

        $l7 = new Label();
        $mv->visitLabel($l7);
        $mv->visitLineNumber(13, $l7);
        $mv->visitLdcInsn("im a string");
        $mv->visitVarInsn(Opcodes::ASTORE, 9);

        $l8 = new Label();
        $mv->visitLabel($l8);
        $mv->visitLineNumber(15, $l8);
        $mv->visitVarInsn(Opcodes::ALOAD, 8);
        $mv->visitInsn(Opcodes::ICONST_1);
        $mv->visitIntInsn(Opcodes::BIPUSH, 42);
        $mv->visitInsn(Opcodes::IASTORE);

        $l9 = new Label();
        $mv->visitLabel($l9);
        $mv->visitLineNumber(17, $l9);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 1);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(Z)V", false);

        $l10 = new Label();
        $mv->visitLabel($l10);
        $mv->visitLineNumber(18, $l10);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 2);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(C)V", false);

        $l11 = new Label();
        $mv->visitLabel($l11);
        $mv->visitLineNumber(19, $l11);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 3);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

        $l12 = new Label();
        $mv->visitLabel($l12);
        $mv->visitLineNumber(20, $l12);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 4);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

        $l13 = new Label();
        $mv->visitLabel($l13);
        $mv->visitLineNumber(21, $l13);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 5);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

        $l14 = new Label();
        $mv->visitLabel($l14);
        $mv->visitLineNumber(22, $l14);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::DLOAD, 6);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(D)V", false);

        $l15 = new Label();
        $mv->visitLabel($l15);
        $mv->visitLineNumber(23, $l15);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ALOAD, 9);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(Ljava/lang/String;)V", false);

        $l16 = new Label();
        $mv->visitLabel($l16);
        $mv->visitLineNumber(24, $l16);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ALOAD, 8);
        $mv->visitInsn(Opcodes::ARRAYLENGTH);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

        $l17 = new Label();
        $mv->visitLabel($l17);
        $mv->visitLineNumber(25, $l17);
        $mv->visitInsn(Opcodes::RETURN_);

        $l18 = new Label();
        $mv->visitLabel($l18);
        $mv->visitLocalVariable("args", "[Ljava/lang/String;", null, $l0, $l18, 0);
        $mv->visitLocalVariable("bool", "Z", null, $l1, $l18, 1);
        $mv->visitLocalVariable("c", "C", null, $l2, $l18, 2);
        $mv->visitLocalVariable("b", "B", null, $l3, $l18, 3);
        $mv->visitLocalVariable("s", "S", null, $l4, $l18, 4);
        $mv->visitLocalVariable("i", "I", null, $l5, $l18, 5);
        $mv->visitLocalVariable("d", "D", null, $l6, $l18, 6);
        $mv->visitLocalVariable("anArray", "[I", null, $l7, $l18, 8);
        $mv->visitLocalVariable("string", "Ljava/lang/String;", null, $l8, $l18, 9);
        $mv->visitMaxs(3, 10);
        $mv->visitEnd();

        $cw->visitEnd();

        $code = $cw->toByteArray();

        $expectedClassStructure = [
            202, 254, 186, 190, 0, 0, 0, 52, 0, 65, 1, 0, 9, 86, 97, 114, 105, 97, 98, 108, 101,
            115, 7, 0, 1, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 79, 98, 106, 101,
            99, 116, 7, 0, 3, 1, 0, 14, 86, 97, 114, 105, 97, 98, 108, 101, 115, 46, 106, 97, 118,
            97, 1, 0, 6, 60, 105, 110, 105, 116, 62, 1, 0, 3, 40, 41, 86, 12, 0, 6, 0, 7, 10, 0, 4,
            0, 8, 1, 0, 4, 116, 104, 105, 115, 1, 0, 11, 76, 86, 97, 114, 105, 97, 98, 108, 101,
            115, 59, 1, 0, 4, 109, 97, 105, 110, 1, 0, 22, 40, 91, 76, 106, 97, 118, 97, 47, 108,
            97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 41, 86, 3, 0, 1, 134, 160, 6, 0, 0,
            0, 0, 0, 0, 0, 3, 1, 0, 11, 105, 109, 32, 97, 32, 115, 116, 114, 105, 110, 103, 8, 0, 17,
            1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 121, 115, 116, 101, 109, 7, 0,
            19, 1, 0, 3, 111, 117, 116, 1, 0, 21, 76, 106, 97, 118, 97, 47, 105, 111, 47, 80, 114,
            105, 110, 116, 83, 116, 114, 101, 97, 109, 59, 12, 0, 21, 0, 22, 9, 0, 20, 0, 23, 1, 0,
            19, 106, 97, 118, 97, 47, 105, 111, 47, 80, 114, 105, 110, 116, 83, 116, 114, 101, 97, 109,
            7, 0, 25, 1, 0, 7, 112, 114, 105, 110, 116, 108, 110, 1, 0, 4, 40, 90, 41, 86, 12, 0, 27,
            0, 28, 10, 0, 26, 0, 29, 1, 0, 4, 40, 67, 41, 86, 12, 0, 27, 0, 31, 10, 0, 26, 0, 32, 1,
            0, 4, 40, 73, 41, 86, 12, 0, 27, 0, 34, 10, 0, 26, 0, 35, 1, 0, 4, 40, 68, 41, 86, 12, 0,
            27, 0, 37, 10, 0, 26, 0, 38, 1, 0, 21, 40, 76, 106, 97, 118, 97, 47, 108, 97, 110, 103,
            47, 83, 116, 114, 105, 110, 103, 59, 41, 86, 12, 0, 27, 0, 40, 10, 0, 26, 0, 41, 1, 0, 4,
            97, 114, 103, 115, 1, 0, 19, 91, 76, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 116,
            114, 105, 110, 103, 59, 1, 0, 4, 98, 111, 111, 108, 1, 0, 1, 90, 1, 0, 1, 99, 1, 0, 1, 67,
            1, 0, 1, 98, 1, 0, 1, 66, 1, 0, 1, 115, 1, 0, 1, 83, 1, 0, 1, 105, 1, 0, 1, 73, 1, 0, 1, 100,
            1, 0, 1, 68, 1, 0, 7, 97, 110, 65, 114, 114, 97, 121, 1, 0, 2, 91, 73, 1, 0, 6, 115, 116,
            114, 105, 110, 103, 1, 0, 18, 76, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 116, 114,
            105, 110, 103, 59, 1, 0, 4, 67, 111, 100, 101, 1, 0, 18, 76, 111, 99, 97, 108, 86, 97, 114,
            105, 97, 98, 108, 101, 84, 97, 98, 108, 101, 1, 0, 15, 76, 105, 110, 101, 78, 117, 109, 98,
            101, 114, 84, 97, 98, 108, 101, 1, 0, 10, 83, 111, 117, 114, 99, 101, 70, 105, 108, 101, 0,
            33, 0, 2, 0, 4, 0, null, 0, 0, 0, 2, 0, 1, 0, 6, 0, 7, 0, 1, 0, 61, 0, 0, 0, 47, 0, 1, 0, 1,
            0, 0, 0, 5, 42, 183, 0, 9, 177, 0, null, 0, 2, 0, 62, 0, 0, 0, 12, 0, 1, 0, 0, 0, 5, 0, 10,
            0, 11, 0, 0, 0, 63, 0, 0, 0, 6, 0, 1, 0, 0, 0, 3, 0, 9, 0, 12, 0, 13, 0, 1, 0, 61, 0, 0, 1,
            35, 0, 3, 0, 10, 0, 0, 0, 101, 4, 60, 16, 67, 61, 16, 100, 62, 17, 39, 16, 54, 4, 18, 14, 54,
            5, 20, 0, 15, 57, 6, 16, 10, 188, 10, 58, 8, 18, 18, 58, 9, 25, 8, 4, 16, 42, 79, 178, 0, 24,
            27, 182, 0, 30, 178, 0, 24, 28, 182, 0, 33, 178, 0, 24, 29, 182, 0, 36, 178, 0, 24, 21, 4, 182,
            0, 36, 178, 0, 24, 21, 5, 182, 0, 36, 178, 0, 24, 24, 6, 182, 0, 39, 178, 0, 24, 25, 9, 182,
            0, 42, 178, 0, 24, 25, 8, 190, 182, 0, 36, 177, 0, null, 0, 2, 0, 62, 0, 0, 0, 92, 0, 9, 0, 0,
            0, 101, 0, 43, 0, 44, 0, 0, 0, 2, 0, 99, 0, 45, 0, 46, 0, 1, 0, 5, 0, 96, 0, 47, 0, 48, 0, 2,
            0, 8, 0, 93, 0, 49, 0, 50, 0, 3, 0, 13, 0, 88, 0, 51, 0, 52, 0, 4, 0, 17, 0, 84, 0, 53, 0, 54,
            0, 5, 0, 22, 0, 79, 0, 55, 0, 56, 0, 6, 0, 28, 0, 73, 0, 57, 0, 58, 0, 8, 0, 32, 0, 69, 0, 59,
            0, 60, 0, 9, 0, 63, 0, 0, 0, 74, 0, 18, 0, 0, 0, 6, 0, 2, 0, 7, 0, 5, 0, 8, 0, 8, 0, 9, 0, 13,
            0, 10, 0, 17, 0, 11, 0, 22, 0, 12, 0, 28, 0, 13, 0, 32, 0, 15, 0, 38, 0, 17, 0, 45, 0, 18, 0,
            52, 0, 19, 0, 59, 0, 20, 0, 67, 0, 21, 0, 75, 0, 22, 0, 83, 0, 23, 0, 91, 0, 24, 0, 100, 0,
            25, 0, 1, 0, 64, 0, 0, 0, 2, 0, 5,
        ];

        $this->assertEquals($expectedClassStructure, $code);
    }

    /**
     * Tests generating simple class property and second method
     *
     * Generates the bytecode corresponding to the following Java class:
     *
     *       public class Variables {
     *
     *           public static void main(String[] args) {
     *               boolean bool = true;
     *               char c = 'C';
     *               byte b = 100;
     *               short s = 10000;
     *               int i = 100000;
     *               double d = 3.14;
     *               int[] anArray = new int[10];
     *               String string = "im a string";
     *
     *               anArray[1] = 42;
     *
     *               System.out.println(bool);
     *               System.out.println(c);
     *               System.out.println(b);
     *               System.out.println(s);
     *               System.out.println(i);
     *               System.out.println(d);
     *               System.out.println(string);
     *               System.out.println(anArray.length);
     *           }
     *       }
     *
     *
     * @return void
     */
    public function testGenerateVariablesAlternativeSyntax() : void
    {
        $cw = new ClassWriter(0);

        $cw->visit(
            Opcodes::V1_8,
            Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER,
            "Variables",
            null,
            "java/lang/Object",
            null
        );

        $cw->visitSource("Variables.java", null);

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, "<init>", "()V", null, null);
        $mv->visitCode();
        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(3, $l0);
        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitMethodInsn(
            Opcodes::INVOKESPECIAL,
            "java/lang/Object",
            "<init>",
            "()V",
            false
        );
        $mv->visitInsn(Opcodes::RETURN_);
        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLocalVariable("this", "LVariables;", null, $l0, $l1, 0);
        $mv->visitMaxs(1, 1);
        $mv->visitEnd();

        $mv = $cw->visitMethod(
            Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC,
            "main",
            "([Ljava/lang/String;)V",
            null,
            null
        );
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(6, $l0);
        $mv->visitLdcInsn(new Type\Boolean(true));
        $mv->visitVarInsn(Opcodes::ISTORE, 1);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLineNumber(7, $l1);
        $mv->visitLdcInsn(new Type\Character('C'));
        $mv->visitVarInsn(Opcodes::ISTORE, 2);

        $l2 = new Label();
        $mv->visitLabel($l2);
        $mv->visitLineNumber(8, $l2);
        $mv->visitIntInsn(Opcodes::BIPUSH, 100); // An integer should be used instead of Byte
        $mv->visitVarInsn(Opcodes::ISTORE, 3);

        $l3 = new Label();
        $mv->visitLabel($l3);
        $mv->visitLineNumber(9, $l3);
        $mv->visitLdcInsn(new Type\Short(10000));
        $mv->visitVarInsn(Opcodes::ISTORE, 4);

        $l4 = new Label();
        $mv->visitLabel($l4);
        $mv->visitLineNumber(10, $l4);
        $mv->visitLdcInsn(new Type\Integer(100000));
        $mv->visitVarInsn(Opcodes::ISTORE, 5);

        $l5 = new Label();
        $mv->visitLabel($l5);
        $mv->visitLineNumber(11, $l5);
        $mv->visitLdcInsn(new Type\Double("3.14"));
        $mv->visitVarInsn(Opcodes::DSTORE, 6);

        $l6 = new Label();
        $mv->visitLabel($l6);
        $mv->visitLineNumber(12, $l6);
        $mv->visitIntInsn(Opcodes::BIPUSH, 10);
        $mv->visitIntInsn(Opcodes::NEWARRAY, Opcodes::T_INT);
        $mv->visitVarInsn(Opcodes::ASTORE, 8);

        $l7 = new Label();
        $mv->visitLabel($l7);
        $mv->visitLineNumber(13, $l7);
        $mv->visitLdcInsn(new Type\String_("im a string"));
        $mv->visitVarInsn(Opcodes::ASTORE, 9);

        $l8 = new Label();
        $mv->visitLabel($l8);
        $mv->visitLineNumber(15, $l8);
        $mv->visitVarInsn(Opcodes::ALOAD, 8);
        $mv->visitInsn(Opcodes::ICONST_1);
        $mv->visitIntInsn(Opcodes::BIPUSH, 42);
        $mv->visitInsn(Opcodes::IASTORE);

        $l9 = new Label();
        $mv->visitLabel($l9);
        $mv->visitLineNumber(17, $l9);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 1);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(Z)V", false);

        $l10 = new Label();
        $mv->visitLabel($l10);
        $mv->visitLineNumber(18, $l10);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 2);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(C)V", false);

        $l11 = new Label();
        $mv->visitLabel($l11);
        $mv->visitLineNumber(19, $l11);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 3);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

        $l12 = new Label();
        $mv->visitLabel($l12);
        $mv->visitLineNumber(20, $l12);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 4);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

        $l13 = new Label();
        $mv->visitLabel($l13);
        $mv->visitLineNumber(21, $l13);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ILOAD, 5);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

        $l14 = new Label();
        $mv->visitLabel($l14);
        $mv->visitLineNumber(22, $l14);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::DLOAD, 6);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(D)V", false);

        $l15 = new Label();
        $mv->visitLabel($l15);
        $mv->visitLineNumber(23, $l15);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ALOAD, 9);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(Ljava/lang/String;)V", false);

        $l16 = new Label();
        $mv->visitLabel($l16);
        $mv->visitLineNumber(24, $l16);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ALOAD, 8);
        $mv->visitInsn(Opcodes::ARRAYLENGTH);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

        $l17 = new Label();
        $mv->visitLabel($l17);
        $mv->visitLineNumber(25, $l17);
        $mv->visitInsn(Opcodes::RETURN_);

        $l18 = new Label();
        $mv->visitLabel($l18);
        $mv->visitLocalVariable("args", "[Ljava/lang/String;", null, $l0, $l18, 0);
        $mv->visitLocalVariable("bool", "Z", null, $l1, $l18, 1);
        $mv->visitLocalVariable("c", "C", null, $l2, $l18, 2);
        $mv->visitLocalVariable("b", "B", null, $l3, $l18, 3);
        $mv->visitLocalVariable("s", "S", null, $l4, $l18, 4);
        $mv->visitLocalVariable("i", "I", null, $l5, $l18, 5);
        $mv->visitLocalVariable("d", "D", null, $l6, $l18, 6);
        $mv->visitLocalVariable("anArray", "[I", null, $l7, $l18, 8);
        $mv->visitLocalVariable("string", "Ljava/lang/String;", null, $l8, $l18, 9);
        $mv->visitMaxs(3, 10);
        $mv->visitEnd();

        $cw->visitEnd();

        $code = $cw->toByteArray();
        $expectedClassStructure = [
            202, 254, 186, 190, 0, 0, 0, 52, 0, 68, 1, 0, 9, 86, 97, 114, 105, 97, 98, 108, 101, 115,
            7, 0, 1, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 79, 98, 106, 101, 99, 116,
            7, 0, 3, 1, 0, 14, 86, 97, 114, 105, 97, 98, 108, 101, 115, 46, 106, 97, 118, 97, 1, 0, 6,
            60, 105, 110, 105, 116, 62, 1, 0, 3, 40, 41, 86, 12, 0, 6, 0, 7, 10, 0, 4, 0, 8, 1, 0, 4,
            116, 104, 105, 115, 1, 0, 11, 76, 86, 97, 114, 105, 97, 98, 108, 101, 115, 59, 1, 0, 4, 109,
            97, 105, 110, 1, 0, 22, 40, 91, 76, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 116,
            114, 105, 110, 103, 59, 41, 86, 3, 0, 0, 0, 1, 3, 0, 0, 0, 67, 3, 0, 0, 39, 16, 3, 0, 1, 134,
            160, 6, 0, 0, 0, 0, 0, 0, 0, 3, 1, 0, 11, 105, 109, 32, 97, 32, 115, 116, 114, 105, 110, 103,
            8, 0, 20, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 121, 115, 116, 101, 109,
            7, 0, 22, 1, 0, 3, 111, 117, 116, 1, 0, 21, 76, 106, 97, 118, 97, 47, 105, 111, 47, 80, 114,
            105, 110, 116, 83, 116, 114, 101, 97, 109, 59, 12, 0, 24, 0, 25, 9, 0, 23, 0, 26, 1, 0, 19,
            106, 97, 118, 97, 47, 105, 111, 47, 80, 114, 105, 110, 116, 83, 116, 114, 101, 97, 109, 7, 0,
            28, 1, 0, 7, 112, 114, 105, 110, 116, 108, 110, 1, 0, 4, 40, 90, 41, 86, 12, 0, 30, 0, 31, 10,
            0, 29, 0, 32, 1, 0, 4, 40, 67, 41, 86, 12, 0, 30, 0, 34, 10, 0, 29, 0, 35, 1, 0, 4, 40, 73, 41,
            86, 12, 0, 30, 0, 37, 10, 0, 29, 0, 38, 1, 0, 4, 40, 68, 41, 86, 12, 0, 30, 0, 40, 10, 0, 29,
            0, 41, 1, 0, 21, 40, 76, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110,
            103, 59, 41, 86, 12, 0, 30, 0, 43, 10, 0, 29, 0, 44, 1, 0, 4, 97, 114, 103, 115, 1, 0, 19, 91,
            76, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 1, 0, 4, 98,
            111, 111, 108, 1, 0, 1, 90, 1, 0, 1, 99, 1, 0, 1, 67, 1, 0, 1, 98, 1, 0, 1, 66, 1, 0, 1, 115,
            1, 0, 1, 83, 1, 0, 1, 105, 1, 0, 1, 73, 1, 0, 1, 100, 1, 0, 1, 68, 1, 0, 7, 97, 110, 65, 114,
            114, 97, 121, 1, 0, 2, 91, 73, 1, 0, 6, 115, 116, 114, 105, 110, 103, 1, 0, 18, 76, 106, 97,
            118, 97, 47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 1, 0, 4, 67, 111, 100,
            101, 1, 0, 18, 76, 111, 99, 97, 108, 86, 97, 114, 105, 97, 98, 108, 101, 84, 97, 98, 108, 101,
            1, 0, 15, 76, 105, 110, 101, 78, 117, 109, 98, 101, 114, 84, 97, 98, 108, 101, 1, 0, 10, 83,
            111, 117, 114, 99, 101, 70, 105, 108, 101, 0, 33, 0, 2, 0, 4, 0, null, 0, 0, 0, 2, 0, 1, 0, 6,
            0, 7, 0, 1, 0, 64, 0, 0, 0, 47, 0, 1, 0, 1, 0, 0, 0, 5, 42, 183, 0, 9, 177, 0, null, 0, 2, 0,
            65, 0, 0, 0, 12, 0, 1, 0, 0, 0, 5, 0, 10, 0, 11, 0, 0, 0, 66, 0, 0, 0, 6, 0, 1, 0, 0, 0, 3, 0,
            9, 0, 12, 0, 13, 0, 1, 0, 64, 0, 0, 1, 35, 0, 3, 0, 10, 0, 0, 0, 101, 18, 14, 60, 18, 15, 61,
            16, 100, 62, 18, 16, 54, 4, 18, 17, 54, 5, 20, 0, 18, 57, 6, 16, 10, 188, 10, 58, 8, 18, 21,
            58, 9, 25, 8, 4, 16, 42, 79, 178, 0, 27, 27, 182, 0, 33, 178, 0, 27, 28, 182, 0, 36, 178, 0,
            27, 29, 182, 0, 39, 178, 0, 27, 21, 4, 182, 0, 39, 178, 0, 27, 21, 5, 182, 0, 39, 178, 0, 27,
            24, 6, 182, 0, 42, 178, 0, 27, 25, 9, 182, 0, 45, 178, 0, 27, 25, 8, 190, 182, 0, 39, 177, 0,
            null, 0, 2, 0, 65, 0, 0, 0, 92, 0, 9, 0, 0, 0, 101, 0, 46, 0, 47, 0, 0, 0, 3, 0, 98, 0, 48, 0,
            49, 0, 1, 0, 6, 0, 95, 0, 50, 0, 51, 0, 2, 0, 9, 0, 92, 0, 52, 0, 53, 0, 3, 0, 13, 0, 88, 0, 54,
            0, 55, 0, 4, 0, 17, 0, 84, 0, 56, 0, 57, 0, 5, 0, 22, 0, 79, 0, 58, 0, 59, 0, 6, 0, 28, 0, 73, 0,
            60, 0, 61, 0, 8, 0, 32, 0, 69, 0, 62, 0, 63, 0, 9, 0, 66, 0, 0, 0, 74, 0, 18, 0, 0, 0, 6, 0, 3,
            0, 7, 0, 6, 0, 8, 0, 9, 0, 9, 0, 13, 0, 10, 0, 17, 0, 11, 0, 22, 0, 12, 0, 28, 0, 13, 0, 32, 0,
            15, 0, 38, 0, 17, 0, 45, 0, 18, 0, 52, 0, 19, 0, 59, 0, 20, 0, 67, 0, 21, 0, 75, 0, 22, 0, 83,
            0, 23, 0, 91, 0, 24, 0, 100, 0, 25, 0, 1, 0, 67, 0, 0, 0, 2, 0, 5,
        ];

        $this->assertEquals($expectedClassStructure, $code);
    }

    /**
     * Tests generating class which contains inner class.
     *
     * Generates the bytecode corresponding to the following Java class:
     *
     *  public class InnerClass {
     *      private static class Inner_Demo {
     *          public void print() {
     *              System.out.println("This is an inner class");
     *          }
     *      }
     *
     *      public static void main(String[] args) {
     *          Inner_Demo inner = new Inner_Demo();
     *          inner.print();
     *      }
     *  }
     *
     * @return void
     */
    public function testGenerateInnerClass() : void
    {
        $cw = new ClassWriter(0);

        $cw->visit(
            Opcodes::V1_8,
            Opcodes::ACC_SUPER,
            'InnerClass$Inner_Demo',
            null,
            "java/lang/Object",
            null
        );
        $cw->visitSource("InnerClass.java", null);
        $cw->visitInnerClass(
            'InnerClass$Inner_Demo',
            "InnerClass",
            "Inner_Demo",
            Opcodes::ACC_PRIVATE + Opcodes::ACC_STATIC
        );

        $mv = $cw->visitMethod(Opcodes::ACC_PRIVATE, "<init>", "()V", null, null);
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(4, $l0);
        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, "java/lang/Object", "<init>", "()V", false);
        $mv->visitInsn(Opcodes::RETURN_);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLocalVariable("this", 'LInnerClass$Inner_Demo;', null, $l0, $l1, 0);
        $mv->visitMaxs(1, 1);
        $mv->visitEnd();

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, "print", "()V", null, null);
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(6, $l0);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitLdcInsn("This is an inner class");
        $mv->visitMethodInsn(
            Opcodes::INVOKEVIRTUAL,
            "java/io/PrintStream",
            "println",
            "(Ljava/lang/String;)V",
            false
        );

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLineNumber(7, $l1);
        $mv->visitInsn(Opcodes::RETURN_);

        $l2 = new Label();
        $mv->visitLabel($l2);
        $mv->visitLocalVariable(
            "this",
            'LInnerClass$Inner_Demo;',
            null,
            $l0,
            $l2,
            0
        );
        $mv->visitMaxs(2, 1);
        $mv->visitEnd();

        $mv = $cw->visitMethod(
            Opcodes::ACC_SYNTHETIC,
            "<init>",
            '(LInnerClass$Inner_Demo;)V',
            null,
            null
        );
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(4, $l0);
        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitMethodInsn(
            Opcodes::INVOKESPECIAL,
            'InnerClass$Inner_Demo',
            "<init>",
            "()V",
            false
        );
        $mv->visitInsn(Opcodes::RETURN_);

        $mv->visitMaxs(1, 2);
        $mv->visitEnd();

        $cw->visitEnd();

        $code = $cw->toByteArray();

        $expectedClassStructure = [
            202, 254, 186, 190, 0, 0, 0, 52, 0, 37, 1, 0, 21, 73, 110, 110, 101, 114, 67, 108, 97,
            115, 115, 36, 73, 110, 110, 101, 114, 95, 68, 101, 109, 111, 7, 0, 1, 1, 0, 16, 106, 97,
            118, 97, 47, 108, 97, 110, 103, 47, 79, 98, 106, 101, 99, 116, 7, 0, 3, 1, 0, 15, 73,
            110, 110, 101, 114, 67, 108, 97, 115, 115, 46, 106, 97, 118, 97, 1, 0, 10, 73, 110, 110,
            101, 114, 67, 108, 97, 115, 115, 7, 0, 6, 1, 0, 10, 73, 110, 110, 101, 114, 95, 68, 101,
            109, 111, 1, 0, 6, 60, 105, 110, 105, 116, 62, 1, 0, 3, 40, 41, 86, 12, 0, 9, 0, 10, 10,
            0, 4, 0, 11, 1, 0, 4, 116, 104, 105, 115, 1, 0, 23, 76, 73, 110, 110, 101, 114, 67, 108,
            97, 115, 115, 36, 73, 110, 110, 101, 114, 95, 68, 101, 109, 111, 59, 1, 0, 5, 112, 114, 105,
            110, 116, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 121, 115, 116, 101,
            109, 7, 0, 16, 1, 0, 3, 111, 117, 116, 1, 0, 21, 76, 106, 97, 118, 97, 47, 105, 111, 47,
            80, 114, 105, 110, 116, 83, 116, 114, 101, 97, 109, 59, 12, 0, 18, 0, 19, 9, 0, 17, 0, 20,
            1, 0, 22, 84, 104, 105, 115, 32, 105, 115, 32, 97, 110, 32, 105, 110, 110, 101, 114, 32,
            99, 108, 97, 115, 115, 8, 0, 22, 1, 0, 19, 106, 97, 118, 97, 47, 105, 111, 47, 80, 114, 105,
            110, 116, 83, 116, 114, 101, 97, 109, 7, 0, 24, 1, 0, 7, 112, 114, 105, 110, 116, 108, 110,
            1, 0, 21, 40, 76, 106, 97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103,
            59, 41, 86, 12, 0, 26, 0, 27, 10, 0, 25, 0, 28, 1, 0, 26, 40, 76, 73, 110, 110, 101, 114, 67,
            108, 97, 115, 115, 36, 73, 110, 110, 101, 114, 95, 68, 101, 109, 111, 59, 41, 86, 10, 0, 2,
            0, 11, 1, 0, 4, 67, 111, 100, 101, 1, 0, 18, 76, 111, 99, 97, 108, 86, 97, 114, 105, 97, 98,
            108, 101, 84, 97, 98, 108, 101, 1, 0, 15, 76, 105, 110, 101, 78, 117, 109, 98, 101, 114, 84,
            97, 98, 108, 101, 1, 0, 10, 83, 111, 117, 114, 99, 101, 70, 105, 108, 101, 1, 0, 12, 73, 110,
            110, 101, 114, 67, 108, 97, 115, 115, 101, 115, 0, 32, 0, 2, 0, 4, 0, null, 0, 0, 0, 3, 0, 2,
            0, 9, 0, 10, 0, 1, 0, 32, 0, 0, 0, 47, 0, 1, 0, 1, 0, 0, 0, 5, 42, 183, 0, 12, 177, 0, null, 0,
            2, 0, 33, 0, 0, 0, 12, 0, 1, 0, 0, 0, 5, 0, 13, 0, 14, 0, 0, 0, 34, 0, 0, 0, 6, 0, 1, 0, 0, 0,
            4, 0, 1, 0, 15, 0, 10, 0, 1, 0, 32, 0, 0, 0, 55, 0, 2, 0, 1, 0, 0, 0, 9, 178, 0, 21, 18, 23,
            182, 0, 29, 177, 0, null, 0, 2, 0, 33, 0, 0, 0, 12, 0, 1, 0, 0, 0, 9, 0, 13, 0, 14, 0, 0, 0,
            34, 0, 0, 0, 10, 0, 2, 0, 0, 0, 6, 0, 8, 0, 7, 16, 4096, 0, 9, 0, 30, 0, 1, 0, 32, 0, 0, 0, 29,
            0, 1, 0, 2, 0, 0, 0, 5, 42, 183, 0, 31, 177, 0, null, 0, 1, 0, 34, 0, 0, 0, 6, 0, 1, 0, 0, 0,
            4, 0, 2, 0, 35, 0, 0, 0, 2, 0, 5, 0, 36, 0, 0, 0, 10, 0, 1, 0, 2, 0, 7, 0, 8, 0, 10,
        ];

        $this->assertEquals($expectedClassStructure, $code);

        /************************* Generate main class ******************************/

        $cw = new ClassWriter(0);

        $cw->visit(
            Opcodes::V1_8,
            Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER,
            "InnerClass",
            null,
            "java/lang/Object",
            null
        );
        $cw->visitSource("InnerClass.java", null);
        $cw->visitInnerClass(
            'InnerClass$Inner_Demo',
            "InnerClass",
            "Inner_Demo",
            Opcodes::ACC_PRIVATE + Opcodes::ACC_STATIC
        );

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, "<init>", "()V", null, null);
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(3, $l0);
        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, "java/lang/Object", "<init>", "()V", false);
        $mv->visitInsn(Opcodes::RETURN_);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLocalVariable("this", "LInnerClass;", null, $l0, $l1, 0);
        $mv->visitMaxs(1, 1);
        $mv->visitEnd();

        $mv = $cw->visitMethod(
            Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC,
            "main",
            "([Ljava/lang/String;)V",
            null,
            null
        );
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(11, $l0);
        $mv->visitTypeInsn(Opcodes::NEW_, 'InnerClass$Inner_Demo');
        $mv->visitInsn(Opcodes::DUP);
        $mv->visitInsn(Opcodes::ACONST_NULL);
        $mv->visitMethodInsn(
            Opcodes::INVOKESPECIAL,
            'InnerClass$Inner_Demo',
            "<init>",
            '(LInnerClass$Inner_Demo;)V',
            false
        );
        $mv->visitVarInsn(Opcodes::ASTORE, 1);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLineNumber(12, $l1);
        $mv->visitVarInsn(
            Opcodes::ALOAD,
            1
        );
        $mv->visitMethodInsn(
            Opcodes::INVOKEVIRTUAL,
            'InnerClass$Inner_Demo',
            "print",
            "()V",
            false
        );

        $l2 = new Label();
        $mv->visitLabel($l2);
        $mv->visitLineNumber(13, $l2);
        $mv->visitInsn(Opcodes::RETURN_);

        $l3 = new Label();
        $mv->visitLabel($l3);
        $mv->visitLocalVariable(
            "args",
            "[Ljava/lang/String;",
            null,
            $l0,
            $l3,
            0
        );
        $mv->visitLocalVariable(
            "inner",
            'LInnerClass$Inner_Demo;',
            null,
            $l1,
            $l3,
            1
        );

        $mv->visitMaxs(3, 2);
        $mv->visitEnd();

        $cw->visitEnd();

        $code = $cw->toByteArray();

        $expectedClassStructure = [
            202, 254, 186, 190, 0, 0, 0, 52, 0, 32, 1, 0, 10, 73, 110, 110, 101, 114,
            67, 108, 97, 115, 115, 7, 0, 1, 1, 0, 16, 106, 97, 118, 97, 47, 108, 97, 110,
            103, 47, 79, 98, 106, 101, 99, 116, 7, 0, 3, 1, 0, 15, 73, 110, 110, 101, 114,
            67, 108, 97, 115, 115, 46, 106, 97, 118, 97, 1, 0, 21, 73, 110, 110, 101, 114,
            67, 108, 97, 115, 115, 36, 73, 110, 110, 101, 114, 95, 68, 101, 109, 111, 7,
            0, 6, 1, 0, 10, 73, 110, 110, 101, 114, 95, 68, 101, 109, 111, 1, 0, 6, 60, 105,
            110, 105, 116, 62, 1, 0, 3, 40, 41, 86, 12, 0, 9, 0, 10, 10, 0, 4, 0, 11, 1, 0,
            4, 116, 104, 105, 115, 1, 0, 12, 76, 73, 110, 110, 101, 114, 67, 108, 97, 115, 115,
            59, 1, 0, 4, 109, 97, 105, 110, 1, 0, 22, 40, 91, 76, 106, 97, 118, 97, 47, 108, 97,
            110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 41, 86, 1, 0, 26, 40, 76, 73, 110,
            110, 101, 114, 67, 108, 97, 115, 115, 36, 73, 110, 110, 101, 114, 95, 68, 101, 109,
            111, 59, 41, 86, 12, 0, 9, 0, 17, 10, 0, 7, 0, 18, 1, 0, 5, 112, 114, 105, 110, 116,
            12, 0, 20, 0, 10, 10, 0, 7, 0, 21, 1, 0, 4, 97, 114, 103, 115, 1, 0, 19, 91, 76, 106,
            97, 118, 97, 47, 108, 97, 110, 103, 47, 83, 116, 114, 105, 110, 103, 59, 1, 0, 5, 105,
            110, 110, 101, 114, 1, 0, 23, 76, 73, 110, 110, 101, 114, 67, 108, 97, 115, 115, 36,
            73, 110, 110, 101, 114, 95, 68, 101, 109, 111, 59, 1, 0, 4, 67, 111, 100, 101, 1, 0,
            18, 76, 111, 99, 97, 108, 86, 97, 114, 105, 97, 98, 108, 101, 84, 97, 98, 108, 101, 1,
            0, 15, 76, 105, 110, 101, 78, 117, 109, 98, 101, 114, 84, 97, 98, 108, 101, 1, 0, 10,
            83, 111, 117, 114, 99, 101, 70, 105, 108, 101, 1, 0, 12, 73, 110, 110, 101, 114, 67,
            108, 97, 115, 115, 101, 115, 0, 33, 0, 2, 0, 4, 0, null, 0, 0, 0, 2, 0, 1, 0, 9, 0, 10,
            0, 1, 0, 27, 0, 0, 0, 47, 0, 1, 0, 1, 0, 0, 0, 5, 42, 183, 0, 12, 177, 0, null, 0, 2,
            0, 28, 0, 0, 0, 12, 0, 1, 0, 0, 0, 5, 0, 13, 0, 14, 0, 0, 0, 29, 0, 0, 0, 6, 0, 1, 0,
            0, 0, 3, 0, 9, 0, 15, 0, 16, 0, 1, 0, 27, 0, 0, 0, 74, 0, 3, 0, 2, 0, 0, 0, 14, 187, 0,
            7, 89, 1, 183, 0, 19, 76, 43, 182, 0, 22, 177, 0, null, 0, 2, 0, 28, 0, 0, 0, 22, 0, 2,
            0, 0, 0, 14, 0, 23, 0, 24, 0, 0, 0, 9, 0, 5, 0, 25, 0, 26, 0, 1, 0, 29, 0, 0, 0, 14, 0,
            3, 0, 0, 0, 11, 0, 9, 0, 12, 0, 13, 0, 13, 0, 2, 0, 30, 0, 0, 0, 2, 0, 5, 0, 31, 0, 0,
            0, 10, 0, 1, 0, 7, 0, 2, 0, 8, 0, 10,
        ];

        $this->assertEquals($expectedClassStructure, $code);
    }
}
