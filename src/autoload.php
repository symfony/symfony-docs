<?php

require_once __DIR__.'/vendor/symfony/src/Symfony/Foundation/UniversalClassLoader.php';

use Symfony\Foundation\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
  'Symfony'     => __DIR__.'/vendor/symfony/src',
  'Application' => __DIR__,
  'Bundle'      => __DIR__,
  'Doctrine'    => __DIR__.'/vendor/doctrine/lib',
));
$loader->registerPrefixes(array(
  'Swift_' => __DIR__.'/vendor/swiftmailer/lib/classes',
  'Zend_'  => __DIR__.'/vendor/zend/library',
));
$loader->register();

// for Zend Framework & SwiftMailer
set_include_path(__DIR__.'/vendor/zend/library'.PATH_SEPARATOR.__DIR__.'/vendor/swiftmailer/lib'.PATH_SEPARATOR.get_include_path());
