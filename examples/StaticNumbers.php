<?php

    require_once '../vendor/autoload.php';

    use Kambo\Karsk\ClassWriter;
    use Kambo\Karsk\Opcodes;
    use Kambo\Karsk\Label;
    use Kambo\Karsk\Utils\FileWriter;
    use Kambo\Karsk\Type;

    $cw = new ClassWriter(0);

    $cw->visit(
        Opcodes::V1_8,
        Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER,
        'StaticNumbers',
        null,
        'java/lang/Object',
        null
    );

    $cw->visitSource('Numbers.java', null);
    
    $fv = $cw->visitField(Opcodes::ACC_PRIVATE + Opcodes::ACC_STATIC, 'integerVar', 'I', null, null);
    $fv->visitEnd();

    $fv = $cw->visitField(Opcodes::ACC_PRIVATE + Opcodes::ACC_STATIC, 'floatVar', 'F', null, null);
    $fv->visitEnd();

    $fv = $cw->visitField(Opcodes::ACC_PRIVATE + Opcodes::ACC_STATIC, 'longVar', 'J', null, null);
    $fv->visitEnd();

    $fv = $cw->visitField(Opcodes::ACC_PRIVATE + Opcodes::ACC_STATIC, 'doubleVar', 'D', null, null);
    $fv->visitEnd();

    $mv = $cw->visitMethod(Opcodes::ACC_STATIC, '<clinit>', '()V', null, null);
    $mv->visitCode();

    $l0 = new Label();
    $mv->visitLabel($l0);
    $mv->visitLineNumber(5, $l0);
    $mv->visitIntInsn(Opcodes::BIPUSH, 10);
    $mv->visitFieldInsn(Opcodes::PUTSTATIC, 'StaticNumbers', 'integerVar', 'I');

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLineNumber(6, $l1);
    $mv->visitLdcInsn(new Type\Float_(10));
    $mv->visitFieldInsn(Opcodes::PUTSTATIC, 'StaticNumbers', 'floatVar', 'F');

    $l2 = new Label();
    $mv->visitLabel($l2);
    $mv->visitLineNumber(7, $l2);
    $mv->visitLdcInsn(new Type\Long(10));
    $mv->visitFieldInsn(Opcodes::PUTSTATIC, 'StaticNumbers', 'longVar', 'J');

    $l3 = new Label();
    $mv->visitLabel($l3);
    $mv->visitLineNumber(8, $l3);
    $mv->visitLdcInsn(new Type\Double(10));
    $mv->visitFieldInsn(Opcodes::PUTSTATIC, 'StaticNumbers', 'doubleVar', 'D');
    $mv->visitInsn(Opcodes::RETURN_);

    $mv->visitMaxs(2, 0);
    $mv->visitEnd();

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
    $mv->visitLocalVariable('this', 'LStaticNumbers;', null, $l0, $l1, 0);
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
    $mv->visitFieldInsn(Opcodes::GETSTATIC, 'java/lang/System', 'out', 'Ljava/io/PrintStream;');
    $mv->visitFieldInsn(Opcodes::GETSTATIC, 'StaticNumbers', 'integerVar', 'I');
    $mv->visitInsn(Opcodes::I2F);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, 'StaticNumbers', 'floatVar', 'F');
    $mv->visitInsn(Opcodes::FADD);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, 'StaticNumbers', 'longVar', 'J');
    $mv->visitInsn(Opcodes::L2F);
    $mv->visitInsn(Opcodes::FADD);
    $mv->visitInsn(Opcodes::F2D);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, 'StaticNumbers', 'doubleVar', 'D');
    $mv->visitInsn(Opcodes::DADD);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, 'java/io/PrintStream', 'println', '(D)V', false);

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLineNumber(12, $l1);
    $mv->visitInsn(Opcodes::RETURN_);

    $l2 = new Label();
    $mv->visitLabel($l2);
    $mv->visitLocalVariable('args', '[Ljava/lang/String;', null, $l0, $l2, 0);
    $mv->visitMaxs(5, 1);
    $mv->visitEnd();

    $cw->visitEnd();

    $fileWriter = new FileWriter;
    $fileWriter->writeClassFile($cw, 'StaticNumbers.class');
