# FcPhp Repository

Abstract class to Repository FcPhp

[![Build Status](https://travis-ci.org/00F100/fcphp-repository.svg?branch=master)](https://travis-ci.org/00F100/fcphp-repository) [![codecov](https://codecov.io/gh/00F100/fcphp-repository/branch/master/graph/badge.svg)](https://codecov.io/gh/00F100/fcphp-repository)

[![PHP Version](https://img.shields.io/packagist/php-v/00f100/fcphp-repository.svg)](https://packagist.org/packages/00F100/fcphp-repository) [![Packagist Version](https://img.shields.io/packagist/v/00f100/fcphp-repository.svg)](https://packagist.org/packages/00F100/fcphp-repository) [![Total Downloads](https://poser.pugx.org/00F100/fcphp-repository/downloads)](https://packagist.org/packages/00F100/fcphp-repository)

## How to install

Composer:
```sh
$ composer require 00f100/fcphp-repository
```

or add in composer.json
```json
{
    "require": {
        "00f100/fcphp-repository": "*"
    }
}
```

## How to use

```php
namespace Path\To
{
    use FcPhp\Repository\Repository;

    class ExampleRepository extends Repository
    {

    }
}
```

##### Configure dependencies

```php

use Exception;
use FcPhp\Di\Facades\DiFacade;
use FcPhp\Datasource\Factories\Factory;
use FcPhp\Cache\Facades\CacheFacade;
use FcPhp\Datasource\Interfaces\IQuery;

$di = DiFacade::getInstance();
$factory = new Factory($di);
$cache = CacheFacade::getInstance('path/to/cache');
```

##### Create instance and define error callback

```php
use Path\To\ExampleRepository;

// See: github.com/00f100/fcphp-datasource
$datasource = new Datasource();

$callbackError = function(IQuery $query, Exception $e) {
    $this->assertInstanceOf(IQuery::class, $query);
    $this->assertInstanceOf(Exception::class, $e);
};

$instance = new ExampleRepository($datasource, $cache, $factory, $callbackError);
```