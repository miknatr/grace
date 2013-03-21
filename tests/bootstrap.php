<?php

error_reporting(E_ALL | E_STRICT);
ini_set("display_errors", 1);


require_once  __DIR__ . '/../vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';
$loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'      => __DIR__ . '/../vendor/symfony/src/',
    'Grace'        => array(__DIR__ . '/../src/', __DIR__ . '/../tests/'),
));
$loader->register();

require_once __DIR__ . '/config.php';