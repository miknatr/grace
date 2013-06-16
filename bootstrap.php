<?php

error_reporting(E_ALL | E_STRICT);
ini_set("display_errors", 1);


require_once  __DIR__ . '/vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';
$loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                              => __DIR__ . '/vendor/symfony/src/',
    'Sensio\\Bundle\\FrameworkExtraBundle' => __DIR__ . '/vendor/sensio-extra-bundle/',
    'Doctrine\\Common'                     => __DIR__ . '/vendor/doctrine-common/lib/',
    'Monolog'                              => __DIR__ . '/vendor/monolog/src/',
    'Grace\\Bundle'                        => __DIR__ . '/symfony-bundle/',
    'Grace'                                => __DIR__ . '/core/',
));
$loader->register();

if (file_exists(__DIR__ . '/config.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once __DIR__ . '/config.php';
} else {
    require_once __DIR__ . '/config.php.dist';
}