<?php

$vendorDir = __DIR__.'/vendor';

require_once $vendorDir.'/symfony/src/Symfony/Framework/UniversalClassLoader.php';

use Symfony\Framework\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                    => $vendorDir.'/symfony/src',
    'Application'                => __DIR__,
    'Bundle'                     => __DIR__,
    'Doctrine\\Common'           => $vendorDir.'/doctrine-common/lib',
    'Doctrine\\DBAL\\Migrations' => $vendorDir.'/doctrine-migrations/lib',
    'Doctrine\\ODM\\MongoDB'     => $vendorDir.'/doctrine-mongodb/lib',
    'Doctrine\\DBAL'             => $vendorDir.'/doctrine-dbal/lib',
    'Doctrine'                   => $vendorDir.'/doctrine/lib',
    'Zend'                       => $vendorDir.'/zend/library',
));
$loader->registerPrefixes(array(
    'Swift_' => $vendorDir.'/swiftmailer/lib/classes',
    'Twig_'  => $vendorDir.'/twig/lib',
));
$loader->register();
