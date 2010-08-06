<?php

use Symfony\Components\Routing\RouteCollection;
use Symfony\Components\Routing\Route;

$collection = new RouteCollection();
$collection->addRoute('homepage', new Route('/', array(
    '_controller' => 'FrameworkBundle:Default:index',
)));
$collection->addCollection($loader->import("HelloBundle/Resources/config/routing.php"));

return $collection;
