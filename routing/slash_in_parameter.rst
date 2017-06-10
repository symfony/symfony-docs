.. index::
   single: Routing; Allow / in route parameter

How to Allow a "/" Character in a Route Parameter
=================================================

Sometimes, you need to compose URLs with parameters that can contain a slash
``/``. For example, consider the ``/share/{token}`` route. If the ``token``
value contains a ``/`` character this route won't match. This is because Symfony
uses this character as separator between route parts.

This article explains how you can modify a route definition so that placeholders
can contain the ``/`` character too.

Configure the Route
-------------------

By default, the Symfony Routing component requires that the parameters match
the following regular expression: ``[^/]+``. This means that all characters are
allowed except ``/``.

You must explicitly allow ``/`` to be part of your placeholder by specifying
a more permissive regular expression for it:

.. configuration-block::

    .. code-block:: php-annotations

        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

        class DefaultController
        {
            /**
             * @Route("/share/{token}", name="share", requirements={"token"=".+"})
             */
            public function shareAction($token)
            {
                // ...
            }
        }

    .. code-block:: yaml

        share:
            path:     /share/{token}
            defaults: { _controller: AppBundle:Default:share }
            requirements:
                token: .+

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="share" path="/share/{token}">
                <default key="_controller">AppBundle:Default:share</default>
                <requirement key="token">.+</requirement>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('share', new Route('/share/{token}', array(
            '_controller' => 'AppBundle:Default:share',
        ), array(
            'token' => '.+',
        )));

        return $collection;

That's it! Now, the ``{token}`` parameter can contain the ``/`` character.

.. note::

    If the route defines several placeholders and you apply this permissive
    regular expression to all of them, the results won't be the expected. For
    example, if the route definition is ``/share/{path}/{token}`` and both
    ``path`` and ``token`` accept ``/``, then ``path`` will contain its contents
    and the token, whereas ``token`` will be empty.
