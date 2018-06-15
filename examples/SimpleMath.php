<?php

    require_once("../vendor/autoload.php");

    use Kambo\Karsk\ClassWriter;
    use Kambo\Karsk\Opcodes;

    /*
    Generates the bytecode corresponding to the following Java class:

    public class SimpleMath {
        public static void main(String[] args) {
            int a = 10;
            int b = 1;
            int c = 2;
            int d = 5;

            a = a*(b + c)/d;

            System.out.println(a);
        }
    }
    */

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

    $binarystring = pack("c*", ...$code);

    $file_w = fopen('SimpleMath.class', 'w+');

    fwrite($file_w, $binarystring);
    fclose($file_w);
