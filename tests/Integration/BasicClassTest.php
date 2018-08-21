<?php

namespace Kambo\Tests\Karsk\Integration\Execution;

use Kambo\Tests\Karsk\ExecutionTestCase;
use Kambo\Karsk\ClassWriter;
use Kambo\Karsk\Opcodes;

/**
 * Integration test for simple "Hello world" class
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class BasicClassTest extends ExecutionTestCase
{
    /**
     * Tests execution of basic class with "hello world" message
     *
     * @return void
     */
    public function testExecutionBasicClass() : void
    {
        $cw = $this->getClassWriter();

        $this->assertExecutionResult(['Hello world!'], $cw);
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
            'public class Example',
            '  minor version: 0',
            '  major version: 52',
            '  flags: ACC_PUBLIC',
            'Constant pool:',
            '   #1 = Utf8               Example',
            '   #2 = Class              #1             // Example',
            '   #3 = Utf8               java/lang/Object',
            '   #4 = Class              #3             // java/lang/Object',
            '   #5 = Utf8               <init>',
            '   #6 = Utf8               ()V',
            '   #7 = NameAndType        #5:#6          // "<init>":()V',
            '   #8 = Methodref          #4.#7          // java/lang/Object."<init>":()V',
            '   #9 = Utf8               main',
            '  #10 = Utf8               ([Ljava/lang/String;)V',
            '  #11 = Utf8               java/lang/System',
            '  #12 = Class              #11            // java/lang/System',
            '  #13 = Utf8               out',
            '  #14 = Utf8               Ljava/io/PrintStream;',
            '  #15 = NameAndType        #13:#14        // out:Ljava/io/PrintStream;',
            '  #16 = Fieldref           #12.#15        // java/lang/System.out:Ljava/io/PrintStream;',
            '  #17 = Utf8               Hello world!',
            '  #18 = String             #17            // Hello world!',
            '  #19 = Utf8               java/io/PrintStream',
            '  #20 = Class              #19            // java/io/PrintStream',
            '  #21 = Utf8               println',
            '  #22 = Utf8               (Ljava/lang/String;)V',
            '  #23 = NameAndType        #21:#22        // println:(Ljava/lang/String;)V',
            '  #24 = Methodref          #20.#23        // java/io/PrintStream.println:(Ljava/lang/String;)V',
            '  #25 = Utf8               Code',
            '{',
            '  public Example();',
            '    descriptor: ()V',
            '    flags: ACC_PUBLIC',
            '    Code:',
            '      stack=1, locals=1, args_size=1',
            '         0: aload_0',
            '         1: invokespecial #8                  // Method java/lang/Object."<init>":()V',
            '         4: return',
            '',
            '  public static void main(java.lang.String[]);',
            '    descriptor: ([Ljava/lang/String;)V',
            '    flags: ACC_PUBLIC, ACC_STATIC',
            '    Code:',
            '      stack=2, locals=2, args_size=1',
            '         0: getstatic     #16                 // Field java/lang/System.out:Ljava/io/PrintStream;',
            '         3: ldc           #18                 // String Hello world!',
            '         5: invokevirtual #24                 // Method java/io/PrintStream.println:(Ljava/lang/String;)V',
            '         8: return',
            '}',
        ];

        $this->assertDisassemblerResult($expectedResult, $cw);
    }

    /**
     * Generates the bytecode corresponding to the following Java class:
     *
     * public class Example {
     *      public static void main (String[] args) {
     *          System.out.println("Hello world!");
     *      }
     * }
     *
     * @return ClassWriter
     */
    private function getClassWriter() : ClassWriter
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

        return $cw;
    }
}
