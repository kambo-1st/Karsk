<?php

require_once("vendor/autoload.php");

use Kambo\Asm\ClassWriter;
use Kambo\Asm\MethodVisitor;
use Kambo\Asm\Opcodes;
use Kambo\Asm\Label;
use Kambo\Asm\Types\Long;

class Testjval
{
    public static function main()
    {
        $cw = ClassWriter::constructor__I(0);
        $cw->visit(/*Opcodes::V1_8*/52, Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER, "testjval/Example", null, "java/lang/Object", null);

        $cw->visitSource("Example.java", null);

        $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, "<init>", "()V", NULL, NULL);
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(3, $l0);
        $mv->visitVarInsn(Opcodes::ALOAD, 0);
        $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, "java/lang/Object", "<init>", "()V",  FALSE );
        $mv->visitInsn(Opcodes::RETURN_);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLocalVariable("this", "Lkambo/Example;", NULL, $l0, $l1, 0);
        $mv->visitMaxs(1, 1);
        $mv->visitEnd();

        $mv = $cw->visitMethod((Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC), "main", "([Ljava/lang/String;)V", NULL, NULL);
        $mv->visitCode();

        $l0 = new Label();
        $mv->visitLabel($l0);
        $mv->visitLineNumber(6, $l0);
        $mv->visitTypeInsn(Opcodes::NEW_, "kambo/Jval");
        $mv->visitInsn(Opcodes::DUP);
        $mv->visitLdcInsn(new Long(21)); // new Long(21)
        $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, "kambo/Jval", "<init>", "(J)V",  FALSE );
        $mv->visitVarInsn(Opcodes::ASTORE, 1);

        $l1 = new Label();
        $mv->visitLabel($l1);
        $mv->visitLineNumber(7, $l1);
        $mv->visitTypeInsn(Opcodes::NEW_, "kambo/Jval");
        $mv->visitInsn(Opcodes::DUP);
        $mv->visitLdcInsn(new Long(10));
        $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, "kambo/Jval", "<init>", "(J)V",  FALSE );
        $mv->visitVarInsn(Opcodes::ASTORE, 2);

        $l2 = new Label();
        $mv->visitLabel($l2);
        $mv->visitLineNumber(8, $l2);
        $mv->visitTypeInsn(Opcodes::NEW_, "kambo/Jval");
        $mv->visitInsn(Opcodes::DUP);
        $mv->visitLdcInsn(new Long(10));
        $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, "kambo/Jval", "<init>", "(J)V",  FALSE );
        $mv->visitVarInsn(Opcodes::ASTORE, 3);

        $l3 = new Label();
        $mv->visitLabel($l3);
        $mv->visitLineNumber(11, $l3);
        $mv->visitVarInsn(Opcodes::ALOAD, 1);
        $mv->visitVarInsn(Opcodes::ALOAD, 2);
        $mv->visitMethodInsn(Opcodes::INVOKESTATIC, "kambo/Jval", "add", "(Lkambo/Jval;Lkambo/Jval;)Lkambo/Jval;",  FALSE );
        $mv->visitVarInsn(Opcodes::ASTORE, 4);

        $l4 = new Label();
        $mv->visitLabel($l4);
        $mv->visitLineNumber(12, $l4);
        $mv->visitVarInsn(Opcodes::ALOAD, 4);
        $mv->visitVarInsn(Opcodes::ALOAD, 3);
        $mv->visitMethodInsn(Opcodes::INVOKESTATIC, "kambo/Jval", "add", "(Lkambo/Jval;Lkambo/Jval;)Lkambo/Jval;",  FALSE );
        $mv->visitVarInsn(Opcodes::ASTORE, 5);

        $l5 = new Label();
        $mv->visitLabel($l5);
        $mv->visitLineNumber(15, $l5);
        $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
        $mv->visitVarInsn(Opcodes::ALOAD, 5);
        $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(Ljava/lang/Object;)V",  FALSE );

        $l6 = new Label();
        $mv->visitLabel($l6);
        $mv->visitLineNumber(16, $l6);
        $mv->visitInsn(Opcodes::RETURN_);

        $l7 = new Label();
        $mv->visitLabel($l7);
        $mv->visitLocalVariable("args", "[Ljava/lang/String;", NULL, $l0, $l7, 0);
        $mv->visitLocalVariable("a", "Lkambo/Jval;", NULL, $l1, $l7, 1);
        $mv->visitLocalVariable("b", "Lkambo/Jval;", NULL, $l2, $l7, 2);
        $mv->visitLocalVariable("c", "Lkambo/Jval;", NULL, $l3, $l7, 3);
        $mv->visitLocalVariable("e", "Lkambo/Jval;", NULL, $l4, $l7, 4);
        $mv->visitLocalVariable("f", "Lkambo/Jval;", NULL, $l5, $l7, 5);
        $mv->visitMaxs(4, 6);
        $mv->visitEnd();

	$cw->visitEnd();

        $code = $cw->toByteArray();
        $binarystring = pack("c*", ...$code);
        $file_w = fopen('testjval/example.class', 'w+');

        fwrite($file_w, $binarystring);
        fclose($file_w);

    }
}

Testjval::main();
