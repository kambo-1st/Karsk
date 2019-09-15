<?php

    require_once('../vendor/autoload.php');

    use Kambo\Karsk\ClassWriter;
    use Kambo\Karsk\Opcodes;
    use Kambo\Karsk\Label;
    use Kambo\Karsk\Utils\FileWriter;

    /*
    Generates the bytecode corresponding to the following Java class:

    public class InnerClass {
        private static class Inner_Demo {
            public void print() {
                System.out.println("This is an inner class");
            }
        }

        public static void main(String[] args) {
            Inner_Demo inner = new Inner_Demo();
            inner.print();
        }

    }

    */

    $cw = new ClassWriter(0);

    $cw->visit(
        Opcodes::V1_8,
        Opcodes::ACC_SUPER,
        'InnerClass$Inner_Demo',
        null,
        'java/lang/Object',
        null
    );
    $cw->visitSource('InnerClass.java', null);
    $cw->visitInnerClass(
        'InnerClass$Inner_Demo',
        'InnerClass',
        'Inner_Demo',
        Opcodes::ACC_PRIVATE + Opcodes::ACC_STATIC
    );

    $mv = $cw->visitMethod(Opcodes::ACC_PRIVATE, '<init>', '()V', null, null);
    $mv->visitCode();

    $l0 = new Label();
    $mv->visitLabel($l0);
    $mv->visitLineNumber(4, $l0);
    $mv->visitVarInsn(Opcodes::ALOAD, 0);
    $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, 'java/lang/Object', '<init>', '()V', false);
    $mv->visitInsn(Opcodes::RETURN_);

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLocalVariable('this', 'LInnerClass$Inner_Demo;', null, $l0, $l1, 0);
    $mv->visitMaxs(1, 1);
    $mv->visitEnd();

    $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, 'print', '()V', null, null);
    $mv->visitCode();

    $l0 = new Label();
    $mv->visitLabel($l0);
    $mv->visitLineNumber(6, $l0);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, 'java/lang/System', 'out', 'Ljava/io/PrintStream;');
    $mv->visitLdcInsn('This is an inner class');
    $mv->visitMethodInsn(
        Opcodes::INVOKEVIRTUAL,
        'java/io/PrintStream',
        'println',
        '(Ljava/lang/String;)V',
        false
    );

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLineNumber(7, $l1);
    $mv->visitInsn(Opcodes::RETURN_);

    $l2 = new Label();
    $mv->visitLabel($l2);
    $mv->visitLocalVariable(
        'this',
        'LInnerClass$Inner_Demo;',
        null,
        $l0,
        $l2,
        0
    );
    $mv->visitMaxs(2, 1);
    $mv->visitEnd();

    $mv = $cw->visitMethod(
        Opcodes::ACC_SYNTHETIC,
        '<init>',
        '(LInnerClass$Inner_Demo;)V',
        null,
        null
    );
    $mv->visitCode();

    $l0 = new Label();
    $mv->visitLabel($l0);
    $mv->visitLineNumber(4, $l0);
    $mv->visitVarInsn(Opcodes::ALOAD, 0);
    $mv->visitMethodInsn(
        Opcodes::INVOKESPECIAL,
        'InnerClass$Inner_Demo',
        '<init>',
        '()V',
        false
    );
    $mv->visitInsn(Opcodes::RETURN_);

    $mv->visitMaxs(1, 2);
    $mv->visitEnd();

    $cw->visitEnd();

    $fileWriter = new FileWriter;
    $fileWriter->writeClassFile($cw, 'InnerClass$Inner_Demo.class');

/*******************************************************/

    $cw = new ClassWriter(0);

    $cw->visit(
        Opcodes::V1_8,
        Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER,
        'InnerClass',
        null,
        'java/lang/Object',
        null
    );
    $cw->visitSource('InnerClass.java', null);
    $cw->visitInnerClass(
        'InnerClass$Inner_Demo',
        'InnerClass',
        'Inner_Demo',
        Opcodes::ACC_PRIVATE + Opcodes::ACC_STATIC
    );

    $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, '<init>', '()V', null, null);
    $mv->visitCode();

    $l0 = new Label();
    $mv->visitLabel($l0);
    $mv->visitLineNumber(3, $l0);
    $mv->visitVarInsn(Opcodes::ALOAD, 0);
    $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, 'java/lang/Object', '<init>', '()V', false);
    $mv->visitInsn(Opcodes::RETURN_);

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLocalVariable('this', 'LInnerClass;', null, $l0, $l1, 0);
    $mv->visitMaxs(1, 1);
    $mv->visitEnd();

    $mv = $cw->visitMethod(
        Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC,
        'main',
        '([Ljava/lang/String;)V',
        null,
        null
    );
    $mv->visitCode();

    $l0 = new Label();
    $mv->visitLabel($l0);
    $mv->visitLineNumber(11, $l0);
    $mv->visitTypeInsn(Opcodes::NEW_, 'InnerClass$Inner_Demo');
    $mv->visitInsn(Opcodes::DUP);
    $mv->visitInsn(Opcodes::ACONST_NULL);
    $mv->visitMethodInsn(
        Opcodes::INVOKESPECIAL,
        'InnerClass$Inner_Demo',
        '<init>',
        '(LInnerClass$Inner_Demo;)V',
        false
    );
    $mv->visitVarInsn(Opcodes::ASTORE, 1);

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLineNumber(12, $l1);
    $mv->visitVarInsn(
        Opcodes::ALOAD,
        1
    );
    $mv->visitMethodInsn(
        Opcodes::INVOKEVIRTUAL,
        'InnerClass$Inner_Demo',
        'print',
        '()V',
        false
    );

    $l2 = new Label();
    $mv->visitLabel($l2);
    $mv->visitLineNumber(13, $l2);
    $mv->visitInsn(Opcodes::RETURN_);

    $l3 = new Label();
    $mv->visitLabel($l3);
    $mv->visitLocalVariable(
        'args',
        '[Ljava/lang/String;',
        null,
        $l0,
        $l3,
        0
    );
    $mv->visitLocalVariable(
        'inner',
        'LInnerClass$Inner_Demo;',
        null,
        $l1,
        $l3,
        1
    );

    $mv->visitMaxs(3, 2);
    $mv->visitEnd();

    $cw->visitEnd();

    $fileWriter = new FileWriter;
    $fileWriter->writeClassFile($cw, 'InnerClass.class');
