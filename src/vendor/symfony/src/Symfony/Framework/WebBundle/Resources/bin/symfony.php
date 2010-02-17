<?php

use Symfony\Framework\WebBundle\Console\BootstrapApplication;

require __DIR__.'/../../../../../../../../autoload.php';

$application = new BootstrapApplication();
$application->run();
