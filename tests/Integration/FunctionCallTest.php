<?php

namespace Kambo\Tests\Karsk\Integration;

use Kambo\Tests\Karsk\ExecutionTestCase;
use Kambo\Karsk\ClassWriter;
use Kambo\Karsk\Opcodes;
use Kambo\Karsk\Label;

/**
 * Integration test for call defined function
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class FunctionCallTest extends ExecutionTestCase
{
    /**
     * Tests execution of basic class with "hello world" message
     *
     * @return void
     */
    public function testExecutionFunctionCall() : void
    {
        $cw = $this->getClassWriter();

        $this->assertExecutionResult(['20'], $cw);
    }

    /**
     * Tests disassembling of basic class with "hello world" message
     *
     * @return void
     */
    public function testDisassembleBasicClass() : void
    {
        $cw = $this->getClassWriter();

        $expectedResult = [
            '  Compiled from "AddFunction.java"',
            'public class AddFunction',
            '  minor version: 0',
            '  major version: 52',
            '  flags: ACC_PUBLIC, ACC_SUPER',
            'Constant pool:',
            '   #1 = Utf8               AddFunction',
            '   #2 = Class              #1             // AddFunction',
            '   #3 = Utf8               java/lang/Object',
            '   #4 = Class              #3             // java/lang/Object',
            '   #5 = Utf8               AddFunction.java',
            '   #6 = Utf8               <init>',
            '   #7 = Utf8               ()V',
            '   #8 = NameAndType        #6:#7          // "<init>":()V',
            '   #9 = Methodref          #4.#8          // java/lang/Object."<init>":()V',
            '  #10 = Utf8               this',
            '  #11 = Utf8               LAddFunction;',
            '  #12 = Utf8               main',
            '  #13 = Utf8               ([Ljava/lang/String;)V',
            '  #14 = Utf8               java/lang/System',
            '  #15 = Class              #14            // java/lang/System',
            '  #16 = Utf8               out',
            '  #17 = Utf8               Ljava/io/PrintStream;',
            '  #18 = NameAndType        #16:#17        // out:Ljava/io/PrintStream;',
            '  #19 = Fieldref           #15.#18        // java/lang/System.out:Ljava/io/PrintStream;',
            '  #20 = Utf8               add',
            '  #21 = Utf8               (II)I',
            '  #22 = NameAndType        #20:#21        // add:(II)I',
            '  #23 = Methodref          #2.#22         // AddFunction.add:(II)I',
            '  #24 = Utf8               java/io/PrintStream',
            '  #25 = Class              #24            // java/io/PrintStream',
            '  #26 = Utf8               println',
            '  #27 = Utf8               (I)V',
            '  #28 = NameAndType        #26:#27        // println:(I)V',
            '  #29 = Methodref          #25.#28        // java/io/PrintStream.println:(I)V',
            '  #30 = Utf8               args',
            '  #31 = Utf8               [Ljava/lang/String;',
            '  #32 = Utf8               a',
            '  #33 = Utf8               I',
            '  #34 = Utf8               b',
            '  #35 = Utf8               Code',
            '  #36 = Utf8               LocalVariableTable',
            '  #37 = Utf8               LineNumberTable',
            '  #38 = Utf8               SourceFile',
            '{',
            '  public AddFunction();',
            '    descriptor: ()V',
            '    flags: ACC_PUBLIC',
            '    Code:',
            '      stack=1, locals=1, args_size=1',
            '         0: aload_0',
            '         1: invokespecial #9                  // Method java/lang/Object."<init>":()V',
            '         4: return',
            '      LocalVariableTable:',
            '        Start  Length  Slot  Name   Signature',
            '            0       5     0  this   LAddFunction;',
            '      LineNumberTable:',
            '        line 3: 0',
            '',
            '  public static void main(java.lang.String[]);',
            '    descriptor: ([Ljava/lang/String;)V',
            '    flags: ACC_PUBLIC, ACC_STATIC',
            '    Code:',
            '      stack=3, locals=16, args_size=1',
            '         0: getstatic     #19                 // Field java/lang/System.out:Ljava/io/PrintStream;',
            '         3: bipush        10',
            '         5: bipush        10',
            '         7: invokestatic  #23                 // Method add:(II)I',
            '        10: invokevirtual #29                 // Method java/io/PrintStream.println:(I)V',
            '        13: return',
            '      LocalVariableTable:',
            '        Start  Length  Slot  Name   Signature',
            '            0      14     0  args   [Ljava/lang/String;',
            '      LineNumberTable:',
            '        line 6: 0',
            '        line 7: 13',
            '',
            '  public static int add(int, int);',
            '    descriptor: (II)I',
            '    flags: ACC_PUBLIC, ACC_STATIC',
            '    Code:',
            '      stack=2, locals=2, args_size=2',
            '         0: iload_0',
            '         1: iload_1',
            '         2: iadd',
            '         3: ireturn',
            '      LocalVariableTable:',
            '        Start  Length  Slot  Name   Signature',
            '            0       4     0     a   I',
            '            0       4     1     b   I',
            '      LineNumberTable:',
            '        line 10: 0',
            '}',
            'SourceFile: "AddFunction.java"',
        ];

        $this->assertDisassemblerResult($expectedResult, $cw);
    }

    /**
     * Generates the bytecode corresponding to the following Java class:
     *
     *       public class AddFunction {
     *           public static void main(String[] args) {
     *               System.out.println(add(10,10));
     *           }
     *
     *           public static int add(int a, int b) {
     *               return a + b;
     *           }
     *       }
     *
     *
     * @return ClassWriter
     */
    private function getClassWriter() : ClassWriter
    {
        $cw = new ClassWriter(1);

        $cw->visit(
            Opcodes::V1_8,
            Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER,
            "AddFunction",
            null,
            "java/lang/Object",
            null
        );

        $cw->visitSource(
            "AddFunction.java",
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
        $mv->visitLocalVariable(
            "this",
            "LAddFunction;",
            null,
            $l0,
            $l1,
            0
        );
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
        $mv->visitFieldInsn(
            Opcodes::GETSTATIC,
            "java/lang/System",
            "out",
            "Ljava/io/PrintStream;"
        );
        $mv->visitIntInsn(Opcodes::BIPUSH, 10);
        $mv->visitIntInsn(Opcodes::BIPUSH, 10);
        $mv->visitMethodInsn(
            Opcodes::INVOKESTATIC,
            "AddFunction",
            "add",
            "(II)I",
            false
        );
        $mv->visitMethodInsn(
            Opcodes::INVOKEVIRTUAL,
            "java/io/PrintStream",
            "println",
            "(I)V",
            false
        );

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLineNumber(7, $l1);
        $mv->visitInsn(Opcodes::RETURN_);

        $l2 = new Label();
        $mv->visitLabel($l2);
        $mv->visitLocalVariable(
            "args",
            "[Ljava/lang/String;",
            null,
            $l0,
            $l2,
            0
        );
        $mv->visitMaxs(3, 1);
        $mv->visitEnd();

        $mv = $cw->visitMethod(
            Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC,
            "add",
            "(II)I",
            null,
            null
        );
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(10, $l0);
        $mv->visitVarInsn(Opcodes::ILOAD, 0);
        $mv->visitVarInsn(Opcodes::ILOAD, 1);
        $mv->visitInsn(Opcodes::IADD);
        $mv->visitInsn(Opcodes::IRETURN);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLocalVariable("a", "I", null, $l0, $l1, 0);
        $mv->visitLocalVariable("b", "I", null, $l0, $l1, 1);
        $mv->visitMaxs(2, 2);
        $mv->visitEnd();

        $cw->visitEnd();

        return $cw;
    }
}
