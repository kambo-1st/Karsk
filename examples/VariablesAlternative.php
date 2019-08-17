<?php

    require_once("../vendor/autoload.php");

    use Kambo\Karsk\ClassWriter;
    use Kambo\Karsk\Opcodes;
    use Kambo\Karsk\Label;
    use Kambo\Karsk\Type;

    /*
    Generates the bytecode corresponding to the following Java class:

    public class Variables {

        public static void main(String[] args) {
            boolean bool = true;
            char c = 'C';
            byte b = 100;
            short s = 10000;
            int i = 100000;
            double d = 3.14;
            int[] anArray = new int[10];
            String string = "im a string";

            anArray[1] = 42;

            System.out.println(bool);
            System.out.println(c);
            System.out.println(b);
            System.out.println(s);
            System.out.println(i);
            System.out.println(d);
            System.out.println(string);
            System.out.println(anArray.length);
        }
    }

    */

    $cw = new ClassWriter(0);

    $cw->visit(
        Opcodes::V1_8,
        Opcodes::ACC_PUBLIC + Opcodes::ACC_SUPER,
        "VariablesAlternative",
        null,
        "java/lang/Object",
        null
    );

    $cw->visitSource("VariablesAlternative.java", null);

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
    $mv->visitLocalVariable("this", "LVariables;", null, $l0, $l1, 0);
    $mv->visitMaxs(1, 1);
    $mv->visitEnd();

    $mv = $cw->visitMethod(
        Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC,
        "main",
        "([Ljava/lang/String;)V",
        null,
        null
    );
    $mv->visitCode();

    $l0 = new Label();
    $mv->visitLabel($l0);
    $mv->visitLineNumber(6, $l0);
    $mv->visitLdcInsn(new Type\Boolean(true));
    $mv->visitVarInsn(Opcodes::ISTORE, 1);

    $l1 = new Label();
    $mv->visitLabel($l1);
    $mv->visitLineNumber(7, $l1);
    $mv->visitLdcInsn(new Type\Character('C'));
    $mv->visitVarInsn(Opcodes::ISTORE, 2);

    $l2 = new Label();
    $mv->visitLabel($l2);
    $mv->visitLineNumber(8, $l2);
    $mv->visitIntInsn(Opcodes::BIPUSH, 100); // An integer should be used instead of Byte
    $mv->visitVarInsn(Opcodes::ISTORE, 3);

    $l3 = new Label();
    $mv->visitLabel($l3);
    $mv->visitLineNumber(9, $l3);
    $mv->visitLdcInsn(new Type\Short(10000));
    $mv->visitVarInsn(Opcodes::ISTORE, 4);

    $l4 = new Label();
    $mv->visitLabel($l4);
    $mv->visitLineNumber(10, $l4);
    $mv->visitLdcInsn(new Type\Integer(100000));
    $mv->visitVarInsn(Opcodes::ISTORE, 5);

    $l5 = new Label();
    $mv->visitLabel($l5);
    $mv->visitLineNumber(11, $l5);
    $mv->visitLdcInsn(new Type\Double("3.14"));
    $mv->visitVarInsn(Opcodes::DSTORE, 6);

    $l6 = new Label();
    $mv->visitLabel($l6);
    $mv->visitLineNumber(12, $l6);
    $mv->visitIntInsn(Opcodes::BIPUSH, 10);
    $mv->visitIntInsn(Opcodes::NEWARRAY, Opcodes::T_INT);
    $mv->visitVarInsn(Opcodes::ASTORE, 8);

    $l7 = new Label();
    $mv->visitLabel($l7);
    $mv->visitLineNumber(13, $l7);
    $mv->visitLdcInsn(new Type\String_("im a string"));
    $mv->visitVarInsn(Opcodes::ASTORE, 9);

    $l8 = new Label();
    $mv->visitLabel($l8);
    $mv->visitLineNumber(15, $l8);
    $mv->visitVarInsn(Opcodes::ALOAD, 8);
    $mv->visitInsn(Opcodes::ICONST_1);
    $mv->visitIntInsn(Opcodes::BIPUSH, 42);
    $mv->visitInsn(Opcodes::IASTORE);

    $l9 = new Label();
    $mv->visitLabel($l9);
    $mv->visitLineNumber(17, $l9);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
    $mv->visitVarInsn(Opcodes::ILOAD, 1);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(Z)V", false);

    $l10 = new Label();
    $mv->visitLabel($l10);
    $mv->visitLineNumber(18, $l10);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
    $mv->visitVarInsn(Opcodes::ILOAD, 2);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(C)V", false);

    $l11 = new Label();
    $mv->visitLabel($l11);
    $mv->visitLineNumber(19, $l11);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
    $mv->visitVarInsn(Opcodes::ILOAD, 3);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

    $l12 = new Label();
    $mv->visitLabel($l12);
    $mv->visitLineNumber(20, $l12);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
    $mv->visitVarInsn(Opcodes::ILOAD, 4);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

    $l13 = new Label();
    $mv->visitLabel($l13);
    $mv->visitLineNumber(21, $l13);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
    $mv->visitVarInsn(Opcodes::ILOAD, 5);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

    $l14 = new Label();
    $mv->visitLabel($l14);
    $mv->visitLineNumber(22, $l14);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
    $mv->visitVarInsn(Opcodes::DLOAD, 6);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(D)V", false);

    $l15 = new Label();
    $mv->visitLabel($l15);
    $mv->visitLineNumber(23, $l15);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
    $mv->visitVarInsn(Opcodes::ALOAD, 9);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(Ljava/lang/String;)V", false);

    $l16 = new Label();
    $mv->visitLabel($l16);
    $mv->visitLineNumber(24, $l16);
    $mv->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
    $mv->visitVarInsn(Opcodes::ALOAD, 8);
    $mv->visitInsn(Opcodes::ARRAYLENGTH);
    $mv->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(I)V", false);

    $l17 = new Label();
    $mv->visitLabel($l17);
    $mv->visitLineNumber(25, $l17);
    $mv->visitInsn(Opcodes::RETURN_);

    $l18 = new Label();
    $mv->visitLabel($l18);
    $mv->visitLocalVariable("args", "[Ljava/lang/String;", null, $l0, $l18, 0);
    $mv->visitLocalVariable("bool", "Z", null, $l1, $l18, 1);
    $mv->visitLocalVariable("c", "C", null, $l2, $l18, 2);
    $mv->visitLocalVariable("b", "B", null, $l3, $l18, 3);
    $mv->visitLocalVariable("s", "S", null, $l4, $l18, 4);
    $mv->visitLocalVariable("i", "I", null, $l5, $l18, 5);
    $mv->visitLocalVariable("d", "D", null, $l6, $l18, 6);
    $mv->visitLocalVariable("anArray", "[I", null, $l7, $l18, 8);
    $mv->visitLocalVariable("string", "Ljava/lang/String;", null, $l8, $l18, 9);
    $mv->visitMaxs(3, 10);
    $mv->visitEnd();

    $cw->visitEnd();

    $code = $cw->toByteArray();

    $binarystring = pack("c*", ...$code);

    $file_w = fopen('VariablesAlternative.class', 'w+');

    fwrite($file_w, $binarystring);
    fclose($file_w);
