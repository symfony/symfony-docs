<?php

$loader->import('config_dev.php');

$container->loadFromExtension('web', 'config', array(
    'toolbar' => false,
));

$container->loadFromExtension('zend', 'logger', array(
    'priority' => 'debug',
));

$container->loadFromExtension('kernel', 'test');
