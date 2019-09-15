<?php

    require_once '../vendor/autoload.php';

    use Kambo\Karsk\ClassWriter;
    use Kambo\Karsk\Opcodes;
    use Kambo\Karsk\Label;
    use Kambo\Karsk\Utils\FileWriter;
    
    $cw = new ClassWriter(0);

    $cw->visit(
        Opcodes::V1_8,
        Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER,
        'Interface',
        null,
        'java/lang/Object',
        ['java/lang/Cloneable']
    );

    $cw->visitSource('Interface.java', null);

    $mv = $cw->visitMethod(
        Opcodes::ACC_PUBLIC,
        '<init>',
        '()V',
        null,
        null
    );
    $mv->visitCode();

    $l0 = new Label();
    $mv->visitLabel($l0);
    $mv->visitLineNumber(3, $l0);
    $mv->visitVarInsn(Opcodes::ALOAD, 0);
    $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, 'java/lang/Object', '<init>', '()V', false);
    $mv->visitInsn(Opcodes::RETURN_);

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLocalVariable(
        'this',
        'LInterface;',
        null,
        $l0,
        $l1,
        0
    );
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
    $mv->visitLineNumber(6, $l0);
    $mv->visitTypeInsn(Opcodes::NEW_, 'Interface');
    $mv->visitInsn(Opcodes::DUP);
    $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, 'Interface', '<init>', '()V', false);
    $mv->visitVarInsn(Opcodes::ASTORE, 1);

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLineNumber(8, $l1);
    $mv->visitInsn(Opcodes::RETURN_);

    $l2 = new Label();
    $mv->visitLabel($l2);
    $mv->visitLocalVariable('args', '[Ljava/lang/String;', null, $l0, $l2, 0);
    $mv->visitLocalVariable('sheep', 'LInterface;', null, $l1, $l2, 1);
    $mv->visitMaxs(2, 2);
    $mv->visitEnd();

    $cw->visitEnd();

    $fileWriter = new FileWriter;
    $fileWriter->writeClassFile($cw, 'Interface.class');
