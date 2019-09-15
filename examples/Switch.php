<?php

    require_once('../vendor/autoload.php');
    
    use Kambo\Karsk\ClassWriter;
    use Kambo\Karsk\Opcodes;
    use Kambo\Karsk\Label;

    /*
    Generates the bytecode corresponding to the following Java class:

        public class Switch {

            public static void main(String[] args) {
                int month = 2;
                String monthString;

                switch (month) {
                    case 1:  monthString = "January";
                             break;
                    case 2:  monthString = "February";
                             break;
                    default: monthString = "Invalid month";
                             break;
                }

                System.out.println(monthString);
            }
        }
    */
    
    $cw = new ClassWriter(0);

    $cw->visit(Opcodes::V1_8, Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER, 'Switch', null, 'java/lang/Object', null);

    $cw->visitSource('Switch.java', null);

    $mv =  $cw->visitMethod(Opcodes::ACC_PUBLIC, '<init>', '()V', null, null);
    $mv->visitCode();

    $l0 = new Label();
    $mv->visitLabel($l0);
    $mv->visitLineNumber(3, $l0);
    $mv->visitVarInsn(Opcodes::ALOAD, 0);
    $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, 'java/lang/Object', '<init>', '()V', false);
    $mv->visitInsn(Opcodes::RETURN_);

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLocalVariable('this', 'Lkambo/Switch;', null, $l0, $l1, 0);
    $mv->visitMaxs(1, 1);
    $mv->visitEnd();

    $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC, 'main', '([Ljava/lang/String;)V', null, null);
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
    $mv->visitLdcInsn('January');
    $mv->visitVarInsn(Opcodes::ASTORE, 2);

    $l5 = new Label();
    $mv->visitLabel($l5);
    $mv->visitLineNumber(11, $l5);

    $l6 = new Label();
    $mv->visitJumpInsn(Opcodes::GOTO_, $l6);
    $mv->visitLabel($l3);
    $mv->visitLineNumber(12, $l3);
    $mv->visitFrame(Opcodes::F_SAME, 0, null, 0, null);
    $mv->visitLdcInsn('February');
    $mv->visitVarInsn(Opcodes::ASTORE, 2);

    $l7 = new Label();
    $mv->visitLabel($l7);
    $mv->visitLineNumber(13, $l7);
    $mv->visitJumpInsn(Opcodes::GOTO_, $l6);

    $mv->visitLabel($l4);
    $mv->visitLineNumber(14, $l4);
    $mv->visitFrame(Opcodes::F_SAME, 0, null, 0, null);
    $mv->visitLdcInsn('Invalid month');
    $mv->visitVarInsn(Opcodes::ASTORE, 2);

    $mv->visitLabel($l6);
    $mv->visitLineNumber(18, $l6);
    $mv->visitFrame(Opcodes::F_APPEND, 1, ['java/lang/String'], 0, null);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, 'java/lang/System', 'out', 'Ljava/io/PrintStream;');
    $mv->visitVarInsn(Opcodes::ALOAD, 2);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, 'java/io/PrintStream', 'println', '(Ljava/lang/String;)V', false);

    $l8 = new Label();
    $mv->visitLabel($l8);
    $mv->visitLineNumber(19, $l8);
    $mv->visitInsn(Opcodes::RETURN_);

    $l9 = new Label();
    $mv->visitLabel($l9);
    $mv->visitLocalVariable('args', '[Ljava/lang/String;', null, $l0, $l9, 0);
    $mv->visitLocalVariable('month', 'I', null, $l1, $l9, 1);
    $mv->visitLocalVariable('monthString', 'Ljava/lang/String;', null, $l5, $l3, 2);
    $mv->visitLocalVariable('monthString', 'Ljava/lang/String;', null, $l7, $l4, 2);
    $mv->visitLocalVariable('monthString', 'Ljava/lang/String;', null, $l6, $l9, 2);
    $mv->visitMaxs(2, 3);
    $mv->visitEnd();

    $cw->visitEnd();

    $code = $cw->toByteArray();

    $binarystring = pack('c*', ...$code);

    $file_w = fopen('Switch.class', 'w+');

    fwrite($file_w, $binarystring);
    fclose($file_w);
