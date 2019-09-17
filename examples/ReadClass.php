<?php

require_once('../vendor/autoload.php');

use Kambo\Karsk\ClassReader;
use Kambo\Karsk\Opcodes;
use Kambo\Karsk\ClassVisitor;
use Kambo\Karsk\MethodVisitor;

/*
Implementation of simple javap like tool for disassembling content of the class
*/

if (!array_key_exists(1, $argv)) {
    echo "Missing class name - usage php ReadClass.php classname.class \n";
    exit(1);
}

$result = ClassReader::createFromPath($argv[1]);

$cv = new class(Opcodes::ASM5) extends ClassVisitor {
    public function visitSource(?string $source, ?string $debug)
    {
        if ($source !== null) {
            echo '  compiled from "'.$source."\"\n";
        }
    }

    public function visit(
        int $version,
        int $access,
        string $name,
        string $signature = null,
        string $superName = null,
        array $interfaces = null
    ) {
        echo 'class '.$name;
        if ($superName !== null) {
            echo ' extends '.$superName;
        }

        if (!empty($interfaces)) {
            echo ' implements '.implode(',', $interfaces);
        }

        echo "\n";
        echo '  major version: '.$version."\n";
        echo '  flags: '.$access."\n";
    }

    public function visitMethod(
        int $access,
        string $name,
        string $desc,
        string $signature = null,
        array $exceptions = null
    ) {
        echo "\nmethod ".$name.$desc."\n";
        echo '  descriptor: '.$desc."\n";
        echo '  flags: '.$access."\n";

        echo "  code: \n";

        return new class(Opcodes::ASM5) extends MethodVisitor {
            public function visitInsn($opcode)
            {
                echo  '    '.Opcodes::toReadableFormat($opcode)."\n";
            }

            public function visitVarInsn($opcode, $var)
            {
                echo '    '.Opcodes::toReadableFormat($opcode, $var)."\n";
            }

            public function visitMethodInsn($opcode, $owner, $name, $desc, $itf = null)
            {
                echo '    '.Opcodes::toReadableFormat($opcode);
                echo ' // Method '.$owner.'.'.$name.':'.$desc."\n";
            }

            public function visitLdcInsn($cst)
            {
                echo '    LDC '.' // '.gettype($cst).' '.$cst." \n";
            }

            public function visitFieldInsn($opcode, $owner, $name, $desc)
            {
                echo '    '.Opcodes::toReadableFormat($opcode);
                echo ' // Field '.$owner.'.'.$name.':'.$desc."\n";
            }

            public function visitMaxs($maxStack, $maxLocals)
            {
                echo '  stack='.$maxStack.', locals='.$maxLocals."\n";
            }
        };
    }
};

$result->accept($cv);
