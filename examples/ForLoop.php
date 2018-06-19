<?php
    require_once("../vendor/autoload.php");

    use Kambo\Karsk\ClassWriter;
    use Kambo\Karsk\Opcodes;
    use Kambo\Karsk\Label;

    /*
    Generates the bytecode corresponding to the following Java class:

        public class ForLoop {

            public static void main(String[] args) {
                for (int i = 0; i < 15; i++) {
                    System.out.println(i);
                }
            }
        }
    */

    $cw = new ClassWriter(0);

    $cw->visit(Opcodes::V1_8, Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER, "ForLoop", null, "java/lang/Object", null);

    $cw->visitSource("ForLoop.java", null);

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
    $mv->visitLocalVariable("this", "Lkambo/ForLoop;", null, $l0, $l1, 0);
    $mv->visitMaxs(1, 1);
    $mv->visitEnd();

    $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC, "main", "([Ljava/lang/String;)V", null, null);
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
    $mv->visitFrame(Opcodes::F_APPEND,1, [Opcodes::INTEGER], 0, null);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
    $mv->visitVarInsn(Opcodes::ILOAD, 1);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

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

    $binarystring = pack("c*", ...$code);

    $file_w = fopen('ForLoop.class', 'w+');

    fwrite($file_w, $binarystring);
    fclose($file_w);
