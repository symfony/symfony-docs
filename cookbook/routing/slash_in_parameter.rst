.. index::
   single: Routing; Allow / in route parameter

How to Allow a "/" Character in a Route Parameter
=================================================

Sometimes, you need to compose URLs with parameters that can contain a slash
``/``. For example, take the classic ``/hello/{username}`` route. By default,
``/hello/Fabien`` will match this route but not ``/hello/Fabien/Kris``. This
is because Symfony uses this character as separator between route parts.

This guide covers how you can modify a route so that ``/hello/Fabien/Kris``
matches the ``/hello/{username}`` route, where ``{username}`` equals ``Fabien/Kris``.

Configure the Route
-------------------

By default, the Symfony Routing component requires that the parameters
match the following regex path: ``[^/]+``. This means that all characters
are allowed except ``/``.

You must explicitly allow ``/`` to be part of your parameter by specifying
a more permissive regex path.

.. configuration-block::

    .. code-block:: php-annotations

        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

        class DemoController
        {
            /**
             * @Route("/hello/{username}", name="_hello", requirements={"username"=".+"})
             */
            public function helloAction($username)
            {
                // ...
            }
        }

    .. code-block:: yaml

        _hello:
            path:     /hello/{username}
            defaults: { _controller: AppBundle:Demo:hello }
            requirements:
                username: .+

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="_hello" path="/hello/{username}">
                <default key="_controller">AppBundle:Demo:hello</default>
                <requirement key="username">.+</requirement>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('_hello', new Route('/hello/{username}', array(
            '_controller' => 'AppBundle:Demo:hello',
        ), array(
            'username' => '.+',
        )));

        return $collection;

That's it! Now, the ``{username}`` parameter can contain the ``/`` character.
