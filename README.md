# Asm

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Asm - write java bytecode in PHP

## Install

Prefered way to install framework is with composer:
```sh
composer require kambo/asm
```

## Basic usage

```php
<?php

require 'vendor/autoload.php';

$app = new Kambo\Matryoshka\App();

$app->get('/hello/{name}', function ($request, $response, $args) {
    return $response->write("Hello, " . $args['name']);
});

$app->run();
```


## License
The MIT License (MIT), https://opensource.org/licenses/MIT
