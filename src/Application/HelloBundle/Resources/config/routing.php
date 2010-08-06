<?php

use Symfony\Components\Routing\RouteCollection;
use Symfony\Components\Routing\Route;

$collection = new RouteCollection();
$collection->addRoute('hello', new Route('/hello/:name', array(
    '_controller' => 'HelloBundle:Hello:index',
)));

return $collection;
