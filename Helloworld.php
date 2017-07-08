<?php

require_once("vendor/autoload.php");

use Kambo\Asm\ClassWriter;
use Kambo\Asm\MethodVisitor;
use Kambo\Asm\Opcodes;

class Helloworld
{
    public static function main()
    {
        $cw = ClassWriter::constructor__I(0);
        $cw->visit(Opcodes::V1_1, Opcodes::ACC_PUBLIC, "Example", null, "java/lang/Object", null);

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
//var_dump($cw);



        $code = $cw->toByteArray();


        $binarystring = pack("c*", ...$code);
        
//$binarystring = implode('', $code);

$file_w = fopen('Example.class', 'w+');

fwrite($file_w, $binarystring);
fclose($file_w);

/*foreach ($code as $codes) {
    echo dechex($codes);
    echo "\n";
}*/


var_dump($code);


        /*$fos = new FileOutputStream("Example.class");
        $fos->write($code);
        $fos->close();


        $loader = new Helloworld();
        $exampleClass = $loader->defineClass("Example", $code, 0, count($code));
        $exampleClass->getMethods()[0]->invoke(null, array( null ));*/
    }
}

Helloworld::main();
