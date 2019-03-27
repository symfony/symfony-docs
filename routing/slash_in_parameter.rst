.. index::
   single: Routing; Allow / in route parameter

.. _routing/slash_in_parameter:

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

        use Symfony\Component\Routing\Annotation\Route;

        class DefaultController
        {
            /**
             * @Route("/share/{token}", name="share", requirements={"token"=".+"})
             */
            public function share($token)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        share:
            path:       /share/{token}
            controller: App\Controller\DefaultController::share
            requirements:
                token: .+

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="share" path="/share/{token}" controller="App\Controller\DefaultController::share">
                <requirement key="token">.+</requirement>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\DefaultController;

        return function (RoutingConfigurator $routes) {
            $routes->add('share', '/share/{token}')
                ->controller([DefaultController::class, 'share'])
                ->requirements([
                    'token' => '.+',
                ])
            ;
        };

That's it! Now, the ``{token}`` parameter can contain the ``/`` character.

.. note::

    If the route includes the special ``{_format}`` placeholder, you shouldn't
    use the ``.+`` requirement for the parameters that allow slashes. For example,
    if the pattern is ``/share/{token}.{_format}`` and ``{token}`` allows any
    character, the ``/share/foo/bar.json`` URL will consider ``foo/bar.json``
    as the token and the format will be empty. This can be solved replacing the
    ``.+`` requirement by ``[^.]+`` to allow any character except dots.

.. note::

    If the route defines several placeholders and you apply this permissive
    regular expression to all of them, the results won't be the expected. For
    example, if the route definition is ``/share/{path}/{token}`` and both
    ``path`` and ``token`` accept ``/``, then ``path`` will contain its contents
    and the token, and ``token`` will be empty.
