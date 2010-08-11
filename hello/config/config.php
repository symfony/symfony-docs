<?php

$container->loadFromExtension('kernel', 'config');

$container->loadFromExtension('web', 'config', array(
    'router'     => array('resource' => '%kernel.root_dir%/config/routing.php'),
    'validation' => array('enabled' => true, 'annotations' => true),
));

$container->loadFromExtension('kernel', 'session', array(
    'default_locale' => "fr",
    'session' => array(
        'name' => "SYMFONY",
        'type' => "Native",
        'lifetime' => "3600",
    )
));

$container->loadFromExtension('web', 'templating');

// Twig Configuration
/*
$container->loadFromExtension('twig', 'config', array('auto_reload' => true));
*/

// Doctrine Configuration
/*
$container->loadFromExtension('doctrine', 'dbal', array(
    'dbname'   => 'xxxxxxxx',
    'user'     => 'xxxxxxxx',
    'password' => '',
));
$container->loadFromExtension('doctrine', 'orm');
*/

// Swiftmailer Configuration
/*
$container->loadFromExtension('swift', 'mailer', array(
    'transport'  => "smtp",
    'encryption' => "ssl",
    'auth_mode'  => "login",
    'host'       => "smtp.gmail.com",
    'username'   => "xxxxxxxx",
    'password'   => "xxxxxxxx",
));
*/
