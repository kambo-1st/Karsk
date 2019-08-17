<?php

    require_once("../vendor/autoload.php");

    use Kambo\Karsk\ClassWriter;
    use Kambo\Karsk\Opcodes;
    use Kambo\Karsk\Label;

    /*
    public class AddFunction {

        public static void main(String[] args) {
            System.out.println(add(10,10));
        }

        public static int add(int a, int b) {
            return a + b;
        }

    }
    */

    $cw = new ClassWriter(1);

    $cw->visit(Opcodes::V1_8, Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER, "AddFunction", null, "java/lang/Object", null);

    $cw->visitSource("AddFunction.java", null);

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
    $mv->visitLocalVariable("this", "LAddFunction;", null, $l0, $l1, 0);
    $mv->visitMaxs(1, 1);
    $mv->visitEnd();

    $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC, "main", "([Ljava/lang/String;)V", null, null);
    $mv->visitCode();

    $l0 = new Label();
    $mv->visitLabel($l0);
    $mv->visitLineNumber(6, $l0);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
    $mv->visitIntInsn(Opcodes::BIPUSH, 10);
    $mv->visitIntInsn(Opcodes::BIPUSH, 10);
    $mv->visitMethodInsn(Opcodes::INVOKESTATIC, "AddFunction", "add", "(II)I", false);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLineNumber(7, $l1);
    $mv->visitInsn(Opcodes::RETURN_);

    $l2 = new Label();
    $mv->visitLabel($l2);
    $mv->visitLocalVariable("args", "[Ljava/lang/String;", null, $l0, $l2, 0);
    $mv->visitMaxs(3, 1);
    $mv->visitEnd();

    $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC, "add", "(II)I", null, null);
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

    $code = $cw->toByteArray();

    $binarystring = pack("c*", ...$code);

    $file_w = fopen('AddFunction.class', 'w+');

    fwrite($file_w, $binarystring);
    fclose($file_w);
