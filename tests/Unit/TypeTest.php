<?php

namespace Kambo\Tests\Karsk\Unit;

use PHPUnit\Framework\TestCase;
use Kambo\Karsk\Type;
use Kambo\Karsk\Opcodes;

/**
 * Unit tests for class Kambo\Karsk\Type
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class TypeTest extends TestCase
{
    /**
     * Tests right inference of the variable type
     *
     * @return void
     * @throws \Kambo\Karsk\Exception\IllegalArgumentException
     */
    public function testRightInferenceOfVariableType() : void
    {
        $this->assertEquals(Type::VOID, Type::getType(new Type\Void_())->getSort());
        $this->assertEquals(Type::BOOLEAN, Type::getType(new Type\Boolean())->getSort());
        $this->assertEquals(Type::CHAR, Type::getType(new Type\Character())->getSort());
        $this->assertEquals(Type::BYTE, Type::getType(new Type\Byte())->getSort());
        $this->assertEquals(Type::SHORT, Type::getType(new Type\Short())->getSort());
        $this->assertEquals(Type::INT, Type::getType(new Type\Integer())->getSort());
        $this->assertEquals(Type::FLOAT, Type::getType(new Type\Float_())->getSort());
        $this->assertEquals(Type::LONG, Type::getType(new Type\Long())->getSort());
        $this->assertEquals(Type::DOUBLE, Type::getType(new Type\Double())->getSort());
        $this->assertEquals(Type::ARRAY, Type::getType(new Type\Array_(new Type\Integer()))->getSort());
    }

    /**
     * Tests getting internal name of the class
     *
     * @return void
     * @throws \Kambo\Karsk\Exception\IllegalArgumentException
     */
    public function testGetInternalName() : void
    {
        $expectedClassName = 'Kambo/Tests/Karsk/Unit/TypeTest';
        $typeTest          = new Type\Object_('Kambo.Tests.Karsk.Unit.TypeTest');

        $this->assertEquals($expectedClassName, Type::getType($typeTest)->getInternalName());
        $this->assertEquals($expectedClassName, Type::getInternalNameOfClass($typeTest));
    }

    /**
     * Tests getting opcode for load operation
     *
     * @return void
     * @throws \Kambo\Karsk\Exception\IllegalArgumentException
     */
    public function testGetOpcodeLoad() : void
    {
        $this->assertEquals(
            Opcodes::BALOAD,
            Type::getType(new Type\Boolean())->getOpcode(Opcodes::IALOAD)
        );
        $this->assertEquals(
            Opcodes::BALOAD,
            Type::getType(new Type\Byte())->getOpcode(Opcodes::IALOAD)
        );
        $this->assertEquals(
            Opcodes::CALOAD,
            Type::getType(new Type\Character())->getOpcode(Opcodes::IALOAD)
        );
        $this->assertEquals(
            Opcodes::SALOAD,
            Type::getType(new Type\Short())->getOpcode(Opcodes::IALOAD)
        );
        $this->assertEquals(
            Opcodes::IALOAD,
            Type::getType(new Type\Integer())->getOpcode(Opcodes::IALOAD)
        );
        $this->assertEquals(
            Opcodes::FALOAD,
            Type::getType(new Type\Float_())->getOpcode(Opcodes::IALOAD)
        );
        $this->assertEquals(
            Opcodes::LALOAD,
            Type::getType(new Type\Long())->getOpcode(Opcodes::IALOAD)
        );
        $this->assertEquals(
            Opcodes::DALOAD,
            Type::getType(new Type\Double())->getOpcode(Opcodes::IALOAD)
        );

        $typeTest = new Type\Object_('Kambo.Tests.Karsk.Unit.TypeTest');

        $this->assertEquals(
            Opcodes::AALOAD,
            Type::getType($typeTest)->getOpcode(Opcodes::IALOAD)
        );
    }

    /**
     * Tests getting opcode for add operation
     *
     * @return void
     * @throws \Kambo\Karsk\Exception\IllegalArgumentException
     */
    public function testGetOpcodeAdd() : void
    {
        $this->assertEquals(
            Opcodes::IADD,
            Type::getType(new Type\Boolean())->getOpcode(Opcodes::IADD)
        );

        $this->assertEquals(
            Opcodes::IADD,
            Type::getType(new Type\Byte())->getOpcode(Opcodes::IADD)
        );

        $this->assertEquals(
            Opcodes::IADD,
            Type::getType(new Type\Short())->getOpcode(Opcodes::IADD)
        );

        $this->assertEquals(
            Opcodes::IADD,
            Type::getType(new Type\Character())->getOpcode(Opcodes::IADD)
        );

        $this->assertEquals(
            Opcodes::IADD,
            Type::getType(new Type\Integer())->getOpcode(Opcodes::IADD)
        );

        $this->assertEquals(
            Opcodes::FADD,
            Type::getType(new Type\Float_())->getOpcode(Opcodes::IADD)
        );

        $this->assertEquals(
            Opcodes::LADD,
            Type::getType(new Type\Long())->getOpcode(Opcodes::IADD)
        );

        $this->assertEquals(
            Opcodes::DADD,
            Type::getType(new Type\Double())->getOpcode(Opcodes::IADD)
        );
    }

    /**
     * Tests getting type, class name and descriptor of object type.
     *
     * @return void
     * @throws \Kambo\Karsk\Exception\IllegalArgumentException
     */
    public function testObjectType() : void
    {
        $sort       = Type::OBJECT;
        $className  = 'java.lang.Object';
        $descriptor = 'Ljava/lang/Object;';

        $t1 = Type::getObjectType('java/lang/Object');
        $t2 = Type::getType('Ljava/lang/Object;');

        $this->assertEquals($sort, $t1->getSort());
        $this->assertEquals($className, $t1->getClassName());
        $this->assertEquals($descriptor, $t1->getDescriptor());

        $this->assertEquals($sort, $t2->getSort());
        $this->assertEquals($className, $t2->getClassName());
        $this->assertEquals($descriptor, $t2->getDescriptor());
    }

    /**
     * Tests getting a string representation of type.
     *
     * @return void
     * @throws \Kambo\Karsk\Exception\IllegalArgumentException
     */
    public function testToString() : void
    {
        $typeTest = new Type\Object_('java.lang.Object');
        $type     = Type::getType($typeTest);

        $this->assertEquals('Ljava/lang/Object;', (string)$type);
    }

    /**
     * Tests getting a hashing representation of type.
     *
     * @return void
     * @throws \Kambo\Karsk\Exception\IllegalArgumentException
     */
    public function testHashCode() : void
    {
        $typeTest = new Type\Object_('java.lang.Object');
        $type     = Type::getType($typeTest);

        $this->assertEquals(1.1782556940202905E+22, $type->hashCode());
    }

    /**
     * Tests if the type equals to another types
     *
     * @return void
     * @throws \Kambo\Karsk\Exception\IllegalArgumentException
     */
    public function testEquals() : void
    {
        $this->assertFalse(Type::getObjectType("I")->equals(null));
        $this->assertFalse(Type::getObjectType("I")->equals(Type::getObjectType("HI")));
        $this->assertFalse(Type::getObjectType("I")->equals(Type::getObjectType("J")));

        $this->assertTrue(Type::getObjectType("I")->equals(Type::getObjectType("I")));
        $this->assertTrue(Type::getObjectType("I")->equals(Type::getType("LI;")));
        $this->assertTrue(Type::getType("LI;")->equals(Type::getObjectType("I")));
    }

    public function testGetDimensions() : void
    {
        $this->assertEquals(1, Type::getType("[I")->getDimensions());
        $this->assertEquals(3, Type::getType("[[[LI;")->getDimensions());
    }

    public function testGetReturnType() : void
    {
        $this->assertEquals(Type::getType(new Type\Integer()), Type::getReturnType("()I"));
        $this->assertEquals(Type::getType(new Type\Integer()), Type::getReturnType("(Lpkg/classMethod();)I"));
    }
}
