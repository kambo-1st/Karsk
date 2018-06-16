<?php
namespace Kambo\Tests\Karsk\Unit;

use PHPUnit\Framework\TestCase;

use Kambo\Karsk\ClassWriter;
use Kambo\Karsk\Opcodes;
use Kambo\Karsk\Label;

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

        $cw->visit(Opcodes::V1_8, Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER, "SimpleCondition", null, "java/lang/Object", null);

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
        $mv->visitFrame(Opcodes::F_APPEND,3, [Opcodes::INTEGER, Opcodes::FLOAT, Opcodes::INTEGER], 0, null);
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
        $mv->visitFrame(Opcodes::F_APPEND,1, [Opcodes::INTEGER], 0, null);
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
        $mv->visitFrame(Opcodes::F_APPEND,1, ["java/lang/String"], 0, null);
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
}
