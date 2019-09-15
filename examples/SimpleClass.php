<?php

    require_once('../vendor/autoload.php');

    use Kambo\Karsk\ClassWriter;
    use Kambo\Karsk\Opcodes;
    use Kambo\Karsk\Label;

    /*
    Generates the bytecode corresponding to the following Java class:

    public class SimpleClass {

        private String classname;

        public SimpleClass(String name) {
            System.out.println(name );
            classname = name;
        }

        public String getName() {
            return classname;
        }

        public static void main(String[] args) {
            SimpleClass simpleInstance = new SimpleClass( "cool class" );

            System.out.println(simpleInstance.getName());
        }
    }

    */

    $cw = new ClassWriter(0);

    $cw->visit(Opcodes::V1_8, Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER, 'SimpleClass', null, 'java/lang/Object', null);

    $cw->visitSource('SimpleClass.java', null);
    
    $fv = $cw->visitField(Opcodes::ACC_PRIVATE, 'classname', 'Ljava/lang/String;', null, null);
    $fv->visitEnd();

    $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, '<init>', '(Ljava/lang/String;)V', null, null);
    $mv->visitCode();

    $l0 = new Label();
    $mv->visitLabel($l0);
    $mv->visitLineNumber(7, $l0);
    $mv->visitVarInsn(Opcodes::ALOAD, 0);
    $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, 'java/lang/Object', '<init>', '()V', false);

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLineNumber(8, $l1);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, 'java/lang/System', 'out', 'Ljava/io/PrintStream;');
    $mv->visitVarInsn(Opcodes::ALOAD, 1);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, 'java/io/PrintStream', 'println', '(Ljava/lang/String;)V', false);

    $l2 = new Label();
    $mv->visitLabel($l2);
    $mv->visitLineNumber(9, $l2);
    $mv->visitVarInsn(Opcodes::ALOAD, 0);
    $mv->visitVarInsn(Opcodes::ALOAD, 1);
    $mv->visitFieldInsn(Opcodes::PUTFIELD, 'SimpleClass', 'classname', 'Ljava/lang/String;');

    $l3 = new Label();
    $mv->visitLabel($l3);
    $mv->visitLineNumber(10, $l3);
    $mv->visitInsn(Opcodes::RETURN_);

    $l4 = new Label();
    $mv->visitLabel($l4);
    $mv->visitLocalVariable('this', 'LSimpleClass;', null, $l0, $l4, 0);
    $mv->visitLocalVariable('name', 'Ljava/lang/String;', null, $l0, $l4, 1);
    $mv->visitMaxs(2, 2);
    $mv->visitEnd();

    $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC, 'getName', '()Ljava/lang/String;', null, null);
    $mv->visitCode();

    $l0 = new Label();
    $mv->visitLabel($l0);
    $mv->visitLineNumber(13, $l0);
    $mv->visitVarInsn(Opcodes::ALOAD, 0);
    $mv->visitFieldInsn(Opcodes::GETFIELD, 'SimpleClass', 'classname', 'Ljava/lang/String;');
    $mv->visitInsn(Opcodes::ARETURN);

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLocalVariable('this', 'LSimpleClass;', null, $l0, $l1, 0);
    $mv->visitMaxs(1, 1);
    $mv->visitEnd();

    $mv = $cw->visitMethod(Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC, 'main', '([Ljava/lang/String;)V', null, null);
    $mv->visitCode();

    $l0 = new Label();
    $mv->visitLabel($l0);
    $mv->visitLineNumber(17, $l0);
    $mv->visitTypeInsn(Opcodes::NEW_, 'SimpleClass');
    $mv->visitInsn(Opcodes::DUP);
    $mv->visitLdcInsn('cool class');
    $mv->visitMethodInsn(Opcodes::INVOKESPECIAL, 'SimpleClass', '<init>', '(Ljava/lang/String;)V', false);
    $mv->visitVarInsn(Opcodes::ASTORE, 1);

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLineNumber(19, $l1);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, 'java/lang/System', 'out', 'Ljava/io/PrintStream;');
    $mv->visitVarInsn(Opcodes::ALOAD, 1);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, 'SimpleClass', 'getName', '()Ljava/lang/String;', false);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, 'java/io/PrintStream', 'println', '(Ljava/lang/String;)V', false);

    $l2 = new Label();
    $mv->visitLabel($l2);
    $mv->visitLineNumber(20, $l2);
    $mv->visitInsn(Opcodes::RETURN_);

    $l3 = new Label();
    $mv->visitLabel($l3);
    $mv->visitLocalVariable('args', '[Ljava/lang/String;', null, $l0, $l3, 0);
    $mv->visitLocalVariable('simpleInstance', 'LSimpleClass;', null, $l1, $l3, 1);
    $mv->visitMaxs(3, 2);
    $mv->visitEnd();

    $cw->visitEnd();

    $code = $cw->toByteArray();

    $binarystring = pack('c*', ...$code);

    $file_w = fopen('SimpleClass.class', 'w+');

    fwrite($file_w, $binarystring);
    fclose($file_w);
