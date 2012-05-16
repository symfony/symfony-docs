<?php

namespace Symfony\Component\Routing;

use Symfony\Component\Routing\Resource\ResourceInterface;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * A RouteCollection represents a set of Route instances.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class RouteCollection
{
    protected $routes;
    protected $resources;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->routes = array();
        $this->resources = array();
    }

    /**
     * Adds a route.
     *
     * @param string $name  The route name
     * @param Route  $route A Route instance
     *
     * @throws \InvalidArgumentException When route name contains non valid characters
     */
    public function addRoute($name, Route $route)
    {
        if (!preg_match('/^[a-z0-9A-Z_]+$/', $name)) {
            throw new \InvalidArgumentException(sprintf('Name "%s" contains non valid characters for a route name.', $name));
        }

        $this->routes[$name] = $route;
    }

    /**
     * Returns the array of routes.
     *
     * @return array An array of routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Gets a route by name.
     *
     * @param  string $name  The route name
     *
     * @return Route  $route A Route instance
     */
    public function getRoute($name)
    {
        return isset($this->routes[$name]) ? $this->routes[$name] : null;
    }

    /**
     * Adds a route collection to the current set of routes (at the end of the current set).
     *
     * @param RouteCollection $collection A RouteCollection instance
     * @param string          $prefix     An optional prefix to add before each pattern of the route collection
     */
    public function addCollection(RouteCollection $collection, $prefix = '')
    {
        $collection->addPrefix($prefix);

        foreach ($collection->getResources() as $resource) {
            $this->addResource($resource);
        }

        $this->routes = array_merge($this->routes, $collection->getRoutes());
    }

    /**
     * Adds a prefix to all routes in the current set.
     *
     * @param string          $prefix     An optional prefix to add before each pattern of the route collection
     */
    public function addPrefix($prefix)
    {
        if (!$prefix) {
            return;
        }

        foreach ($this->getRoutes() as $route) {
            $route->setPattern($prefix.$route->getPattern());
        }
    }

    /**
     * Returns an array of resources loaded to build this collection.
     *
     * @return ResourceInterface[] An array of resources
     */
    public function getResources()
    {
        return array_unique($this->resources);
    }

    /**
     * Adds a resource for this collection.
     *
     * @param ResourceInterface $resource A resource instance
     */
    public function addResource(ResourceInterface $resource)
    {
        $this->resources[] = $resource;
    }
}
