# Karsk - write java bytecode in PHP!

[![Software License](https://img.shields.io/badge/license-BSD-brightgreen.svg?style=flat-square)](LICENSE)

Karsk is direct port of ASM "a very small and fast Java bytecode manipulation framework".

**This is highly experimental and very much work in progress at this moment. Any help is welcome!**

## Install

Preferred way to install framework is with composer:
```sh
composer require kambo/karsk
```

## Basic usage
```java
public class Helloworld {
    public static void main (String[] args) {
        System.out.println("Hello world!");
    }
}
```

```php
<?php
use Kambo\Karsk\ClassWriter;
use Kambo\Karsk\Opcodes;

$cw = ClassWriter::constructor__I(0);
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
```

## License
3-Clause BSD, https://opensource.org/licenses/BSD-3-Clause
