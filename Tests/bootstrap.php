<?php

require_once $_SERVER['SYMFONY'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace('Symfony', $_SERVER['SYMFONY']);
$loader->register();

spl_autoload_register(function($class)
{
    if (0 === strpos($class, 'Spraed\\PDFGeneratorBundle\\') &&
        file_exists($file = __DIR__.'/../'.implode('/', array_slice(explode('\\', $class), 2)).'.php')) {
        require_once $file;
    }
});