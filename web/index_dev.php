<?php

require_once __DIR__.'/../hello/HelloKernel.php';

$kernel = new HelloKernel('dev', true);
$kernel->run();
