<?php

    require_once("../vendor/autoload.php");

    use Kambo\Asm\ClassWriter;
    use Kambo\Asm\Opcodes;
    
    /*
    Generates the bytecode corresponding to the following Java class:

    public class Example {
        public static void main (String[] args) {
            System.out.println("Hello world!");
        }
    }
    */
    
    $cw = ClassWriter::constructor__I(0);
    $cw->visit(Opcodes::V1_8, Opcodes::ACC_PUBLIC, "Example", null, "java/lang/Object", null);
    
    $mw = $cw->visitMethod(Opcodes::ACC_PUBLIC, "<init>", "()V", null, null);
    $mw->visitVarInsn(Opcodes::ALOAD, 0);
    
    $mw->visitMethodInsn(Opcodes::INVOKESPECIAL, "java/lang/Object", "<init>", "()V", false);
    $mw->visitInsn(Opcodes::RETURN_);
    $mw->visitMaxs(1, 1);
    $mw->visitEnd();
    
    $mw2 = $cw->visitMethod((Opcodes::ACC_PUBLIC + Opcodes::ACC_STATIC), "main", "([Ljava/lang/String;)V", null, null);
    $mw2->visitFieldInsn(Opcodes::GETSTATIC, "java/lang/System", "out", "Ljava/io/PrintStream;");
    $mw2->visitLdcInsn("Hello world!");
    
    $mw2->visitMethodInsn(Opcodes::INVOKEVIRTUAL, "java/io/PrintStream", "println", "(Ljava/lang/String;)V", false);
    $mw2->visitInsn(Opcodes::RETURN_);
    $mw2->visitMaxs(2, 2);
    $mw2->visitEnd();
    
    $code = $cw->toByteArray();
    
    $binarystring = pack("c*", ...$code);
    
    $file_w = fopen('Example.class', 'w+');
    
    fwrite($file_w, $binarystring);
    fclose($file_w);
