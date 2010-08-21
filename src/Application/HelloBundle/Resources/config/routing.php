<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();
$collection->addRoute('hello', new Route('/hello/:name', array(
    '_controller' => 'HelloBundle:Hello:index',
)));

return $collection;
