<?php

namespace Kambo\Tests\Karsk\Unit;

use PHPUnit\Framework\TestCase;

use Kambo\Karsk\ClassReader;
use Kambo\Karsk\Opcodes;
use Kambo\Karsk\ClassWriter;
use Kambo\Karsk\ClassVisitor;
use Kambo\Karsk\MethodVisitor;

/**
 * Test for the Kambo\Karsk\ClassWriter
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class ClassReaderTest extends TestCase
{
    public function testGetClassInfo()
    {
        $result = new ClassReader($this->getTestedClassByteCode());

        $this->assertEquals('Helloworld', $result->getClassName());
        $this->assertEquals(Opcodes::ACC_PUBLIC, ($result->getAccess() & Opcodes::ACC_PUBLIC));
        $this->assertEquals('java/lang/Object', $result->getSuperName());
        $this->assertEquals([], $result->getInterfaces());
        $this->assertEquals(25, $result->getItemCount());
        $this->assertEquals(11, $result->getItem(1));
        $this->assertEquals(25, $result->getMaxStringLength());
    }

    public function testCopyPool()
    {
        $result = new ClassReader($this->getTestedClassByteCode());
        $cw = new ClassWriter(0);
        $result->copyPool($cw);

        $all = [];
        foreach ($cw->items as $item) {
            if ($item->next === null) {
                $all[] = $item;
            } else {
                while (true) {
                    $all[] = $item;
                    if ($item->next === null) {
                        break;
                    }

                    $item = $item->next;
                }
            }
        }

        $strings = [];
        foreach ($all as $item) {
            $strings[] = $item->strVal1;
        }

        sort($strings);

        $expectedArray = [
            '()V',
            '(Ljava/lang/String;)V',
            '([Ljava/lang/String;)V',
            '<init>',
            '<init>',
            'Hello world!',
            'Hello world!',
            'Helloworld',
            'Helloworld',
            'Ljava/io/PrintStream;',
            'java/io/PrintStream',
            'java/io/PrintStream',
            'java/io/PrintStream',
            'java/lang/Object',
            'java/lang/Object',
            'java/lang/Object',
            'java/lang/System',
            'java/lang/System',
            'java/lang/System',
            'main',
            'out',
            'out',
            'println',
            'println',
        ];

        $this->assertEquals($expectedArray, $strings);
    }

    public function testAccept()
    {
        $result = new ClassReader($this->getTestedClassByteCode());

        $stub = $this->getMockForAbstractClass(
            ClassVisitor::class,
            [Opcodes::ASM4],
            '',
            true,
            true,
            true,
            ['visit', 'visitMethod']
        );

        $stub->expects($this->once())
            ->method('visit')
            ->with(
                $this->equalTo(Opcodes::V1_8),       // version
                $this->equalTo(Opcodes::ACC_PUBLIC), // access flag
                $this->equalTo('Helloworld'),        // class name
                $this->equalTo(null),                // signature
                $this->equalTo('java/lang/Object'),  // super class (parent)
                $this->equalTo([])                        // implemented interfaces
            );
        $stub->expects($this->exactly(2))
            ->method('visitMethod')
            ->withConsecutive(
                [
                    $this->equalTo(Opcodes::ACC_PUBLIC), // access flag
                    $this->equalTo('<init>'),            // method name
                    $this->equalTo('()V'),               // desc
                    $this->equalTo(null),                // signature
                    $this->equalTo(null),                // exceptions
                ],
                [
                    $this->equalTo(Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC), // access flag
                    $this->equalTo('main'),                                    // method name
                    $this->equalTo('([Ljava/lang/String;)V'),                  // desc
                    $this->equalTo(null),                                      // signature
                    $this->equalTo(null),                                      // exceptions
                ]
            );

        $this->assertNull($result->accept($stub));
    }

    public function testAcceptInstructions()
    {
        $result = new ClassReader($this->getTestedClassByteCode());

        $stub = $this->getMockForAbstractClass(
            ClassVisitor::class,
            [Opcodes::ASM4],
            '',
            true,
            true,
            true,
            ['visit', 'visitMethod']
        );

        $mvMock = $this->createMock(MethodVisitor::class);
        $mvMock->expects($this->exactly(2))
            ->method('visitInsn')
            ->withConsecutive(
                [
                    Opcodes::RETURN_
                ],
                [
                    Opcodes::RETURN_
                ]
            );

        $mvMock->expects($this->exactly(1))
            ->method('visitVarInsn')
            ->withConsecutive(
                [
                    Opcodes::ALOAD
                ]
            );

        $mvMock->expects($this->exactly(2))
            ->method('visitMethodInsn')
            ->withConsecutive(
                [
                    Opcodes::INVOKESPECIAL,
                    'java/lang/Object',
                    '<init>',
                    '()V',
                    false,
                ],
                [
                    Opcodes::INVOKEVIRTUAL,
                    'java/io/PrintStream',
                    'println',
                    '(Ljava/lang/String;)V',
                    false,
                ]
            );

        $mvMock->expects($this->exactly(1))
            ->method('visitLdcInsn')
            ->withConsecutive(
                [
                    'Hello world!'
                ]
            );

        $mvMock->expects($this->exactly(1))
            ->method('visitFieldInsn')
            ->withConsecutive(
                [
                    Opcodes::GETSTATIC,
                    'java/lang/System',
                    'out',
                    'Ljava/io/PrintStream;',
                ]
            );

        $stub->expects($this->exactly(2))
            ->method('visitMethod')
            ->willReturn(
                $mvMock
            );

        $this->assertNull($result->accept($stub));
    }

    private function getTestedClassByteCode() : array
    {
        return [
            202,
            254,
            186,
            190,
            0,
            0,
            0,
            52,
            0,
            26,
            1,
            0,
            10,
            72,
            101,
            108,
            108,
            111,
            119,
            111,
            114,
            108,
            100,
            7,
            0,
            1,
            1,
            0,
            16,
            106,
            97,
            118,
            97,
            47,
            108,
            97,
            110,
            103,
            47,
            79,
            98,
            106,
            101,
            99,
            116,
            7,
            0,
            3,
            1,
            0,
            6,
            60,
            105,
            110,
            105,
            116,
            62,
            1,
            0,
            3,
            40,
            41,
            86,
            12,
            0,
            5,
            0,
            6,
            10,
            0,
            4,
            0,
            7,
            1,
            0,
            4,
            109,
            97,
            105,
            110,
            1,
            0,
            22,
            40,
            91,
            76,
            106,
            97,
            118,
            97,
            47,
            108,
            97,
            110,
            103,
            47,
            83,
            116,
            114,
            105,
            110,
            103,
            59,
            41,
            86,
            1,
            0,
            16,
            106,
            97,
            118,
            97,
            47,
            108,
            97,
            110,
            103,
            47,
            83,
            121,
            115,
            116,
            101,
            109,
            7,
            0,
            11,
            1,
            0,
            3,
            111,
            117,
            116,
            1,
            0,
            21,
            76,
            106,
            97,
            118,
            97,
            47,
            105,
            111,
            47,
            80,
            114,
            105,
            110,
            116,
            83,
            116,
            114,
            101,
            97,
            109,
            59,
            12,
            0,
            13,
            0,
            14,
            9,
            0,
            12,
            0,
            15,
            1,
            0,
            12,
            72,
            101,
            108,
            108,
            111,
            32,
            119,
            111,
            114,
            108,
            100,
            33,
            8,
            0,
            17,
            1,
            0,
            19,
            106,
            97,
            118,
            97,
            47,
            105,
            111,
            47,
            80,
            114,
            105,
            110,
            116,
            83,
            116,
            114,
            101,
            97,
            109,
            7,
            0,
            19,
            1,
            0,
            7,
            112,
            114,
            105,
            110,
            116,
            108,
            110,
            1,
            0,
            21,
            40,
            76,
            106,
            97,
            118,
            97,
            47,
            108,
            97,
            110,
            103,
            47,
            83,
            116,
            114,
            105,
            110,
            103,
            59,
            41,
            86,
            12,
            0,
            21,
            0,
            22,
            10,
            0,
            20,
            0,
            23,
            1,
            0,
            4,
            67,
            111,
            100,
            101,
            0,
            1,
            0,
            2,
            0,
            4,
            0,
            0,
            0,
            0,
            0,
            2,
            0,
            1,
            0,
            5,
            0,
            6,
            0,
            1,
            0,
            25,
            0,
            0,
            0,
            17,
            0,
            1,
            0,
            1,
            0,
            0,
            0,
            5,
            42,
            183,
            0,
            8,
            177,
            0,
            0,
            0,
            0,
            0,
            9,
            0,
            9,
            0,
            10,
            0,
            1,
            0,
            25,
            0,
            0,
            0,
            21,
            0,
            2,
            0,
            2,
            0,
            0,
            0,
            9,
            178,
            0,
            16,
            18,
            18,
            182,
            0,
            24,
            177,
            0,
            0,
            0,
            0,
            0,
            0,
        ];
    }
}
