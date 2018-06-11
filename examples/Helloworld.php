<?php

    require_once("../vendor/autoload.php");

    use Kambo\Karsk\ClassWriter;
    use Kambo\Karsk\Opcodes;
    
    /*
    Generates the bytecode corresponding to the following Java class:

    public class Helloworld {
        public static void main (String[] args) {
            System.out.println("Hello world!");
        }
    }
    */
    
    $cw = new ClassWriter(0);
    $cw->visit(
        Opcodes::V1_8,
        Opcodes::ACC_PUBLIC,
        "Helloworld",
        null,
        "java/lang/Object",
        null
    );

    $mw = $cw->visitMethod(Opcodes::ACC_PUBLIC, "<init>", "()V", null, null);
    $mw->visitVarInsn(Opcodes::ALOAD, 0);
    
    $mw->visitMethodInsn(Opcodes::INVOKESPECIAL, "java/lang/Object", "<init>", "()V", false);
    $mw->visitInsn(Opcodes::RETURN_);
    $mw->visitMaxs(1, 1);
    $mw->visitEnd();
    
    $mainMethod = $cw->visitMethod(
        (Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC),
        "main",
        "([Ljava/lang/String;)V",
        null,
        null
    );
    $mainMethod->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
    $mainMethod->visitLdcInsn("Hello world!");

    $mainMethod->visitMethodInsn(
        Opcodes::INVOKEVIRTUAL,
        "java/io/PrintStream",
        "println",
        "(Ljava/lang/String;)V",
        false
    );
    $mainMethod->visitInsn(Opcodes::RETURN_);
    $mainMethod->visitMaxs(2, 2);
    $mainMethod->visitEnd();
    
    $code = $cw->toByteArray();
    
    $binarystring = pack("c*", ...$code);
    
    $file_w = fopen('Helloworld.class', 'w+');
    
    fwrite($file_w, $binarystring);
    fclose($file_w);
