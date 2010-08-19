<?php

require_once __DIR__.'/../hello/HelloKernel.php';

$kernel = new HelloKernel('prod', false);
$kernel->handle()->send();
