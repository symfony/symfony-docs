.. index::
    single: Routing; Conditions

How to Restrict Route Matching through Conditions
=================================================

A route can be made to match only certain routing placeholders (via regular
expressions), HTTP methods, or host names. If you need more flexibility to
define arbitrary matching logic, use the ``condition`` routing setting:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/DefaultController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class DefaultController extends AbstractController
        {
            /**
             * @Route(
             *     "/contact",
             *     name="contact",
             *     condition="context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'"
             * )
             *
             * expressions can also include config parameters
             * condition: "request.headers.get('User-Agent') matches '%app.allowed_browsers%'"
             */
            public function contact()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        contact:
            path:       /contact
            controller: 'App\Controller\DefaultController::contact'
            condition:  "context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'"
            # expressions can also include config parameters
            # condition: "request.headers.get('User-Agent') matches '%app.allowed_browsers%'"

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="contact" path="/contact" controller="App\Controller\DefaultController::contact">
                <condition>context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'</condition>
                <!-- expressions can also include config parameters -->
                <!-- <condition>request.headers.get('User-Agent') matches '%app.allowed_browsers%'</condition> -->
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\DefaultController;

        return function (RoutingConfigurator $routes) {
            $routes->add('contact', '')
                ->controller([DefaultController::class, 'contact'])
                ->condition('context.getMethod() in ["GET", "HEAD"] and request.headers.get("User-Agent") matches "/firefox/i"')
                // expressions can also include config parameters
                // 'request.headers.get("User-Agent") matches "%app.allowed_browsers%"'
            ;
        };

The ``condition`` is an expression, and you can learn more about its syntax
here: :doc:`/components/expression_language/syntax`. With this, the route
won't match unless the HTTP method is either GET or HEAD *and* if the ``User-Agent``
header matches ``firefox``.

You can do any complex logic you need in the expression by leveraging two
variables that are passed into the expression:

``context``
    An instance of :class:`Symfony\\Component\\Routing\\RequestContext`,
    which holds the most fundamental information about the route being matched.
``request``
    The Symfony :class:`Symfony\\Component\\HttpFoundation\\Request` object
    (see :ref:`component-http-foundation-request`).

.. caution::

    Conditions are *not* taken into account when generating a URL.

.. sidebar:: Expressions are Compiled to PHP

    Behind the scenes, expressions are compiled down to raw PHP. Our example
    would generate the following PHP in the cache directory::

        if (rtrim($pathInfo, '/contact') === '' && (
            in_array($context->getMethod(), [0 => "GET", 1 => "HEAD"])
            && preg_match("/firefox/i", $request->headers->get("User-Agent"))
        )) {
            // ...
        }

    Because of this, using the ``condition`` key causes no extra overhead
    beyond the time it takes for the underlying PHP to execute.
