.. index::
    :single: Routing; Conditions

How to Restrict Route Matching through Conditions
=================================================

As you've seen, a route can be made to match only certain routing wildcards
(via regular expressions), HTTP methods, or host names. But the routing system
can be extended to have an almost infinite flexibility using ``conditions``:

.. configuration-block::

    .. code-block:: yaml

        contact:
            path:     /contact
            defaults: { _controller: AcmeDemoBundle:Main:contact }
            condition: "context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'"

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="contact" path="/contact">
                <default key="_controller">AcmeDemoBundle:Main:contact</default>
                <condition>context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'</condition>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('contact', new Route(
            '/contact', array(
                '_controller' => 'AcmeDemoBundle:Main:contact',
            ),
            array(),
            array(),
            '',
            array(),
            array(),
            'context.getMethod() in ["GET", "HEAD"] and request.headers.get("User-Agent") matches "/firefox/i"'
        ));

        return $collection;

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

        if (rtrim($pathinfo, '/contact') === '' && (
            in_array($context->getMethod(), array(0 => "GET", 1 => "HEAD"))
            && preg_match("/firefox/i", $request->headers->get("User-Agent"))
        )) {
            // ...
        }

    Because of this, using the ``condition`` key causes no extra overhead
    beyond the time it takes for the underlying PHP to execute.
