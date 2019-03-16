.. index::
   single: Routing; Service Container Parameters

How to Use Service Container Parameters in your Routes
======================================================

Sometimes you may find it useful to make some parts of your routes
globally configurable. For instance, if you build an internationalized
site, you'll probably start with one or two locales. Surely you'll
add a requirement to your routes to prevent a user from matching a locale
other than the locales you support.

You *could* hardcode your ``_locale`` requirement in all your routes, but
a better solution is to use a configurable service container parameter right
inside your routing configuration:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/MainController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class MainController extends AbstractController
        {
            /**
             * @Route("/{_locale}/contact", name="contact", requirements={
             *     "_locale"="%app.locales%"
             * })
             */
            public function contact()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        contact:
            path:       /{_locale}/contact
            controller: App\Controller\MainController::contact
            requirements:
                _locale: '%app.locales%'

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="contact" path="/{_locale}/contact" controller="App\Controller\MainController::contact">
                <requirement key="_locale">%app.locales%</requirement>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\MainController;

        return function (RoutingConfigurator $routes) {
            $routes->add('contact', '/{_locale}/contact')
                ->controller([MainController::class, 'contact'])
                ->requirements([
                    '_locale' => '%app.locales%',
                ])
            ;
        };

.. versionadded:: 4.3

    Support for boolean container parameters in routes was introduced in Symfony 4.3.

You can now control and set the  ``app.locales`` parameter somewhere
in your container:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            app.locales: en|es

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" charset="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="app.locales">en|es</parameter>
            </parameters>
        </container>

    .. code-block:: php

        // config/services.php
        $container->setParameter('app.locales', 'en|es');

You can also use a parameter to define your route path (or part of your
path):

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/MainController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class MainController extends AbstractController
        {
            /**
             * @Route("/%app.route_prefix%/contact", name="contact")
             */
            public function contact()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        some_route:
            path:       /%app.route_prefix%/contact
            controller: App\Controller\MainController::contact

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="some_route"
                path="/%app.route_prefix%/contact"
                controller="App\Controller\MainController::contact"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\MainController;

        return function (RoutingConfigurator $routes) {
            $routes->add('contact', '/%app.route_prefix%/contact')
                ->controller([MainController::class, 'contact'])
            ;
        };

.. note::

    Just like in normal service container configuration files, if you actually
    need a ``%`` in your route, you can escape the percent sign by doubling
    it, e.g. ``/score-50%%``, which would resolve to ``/score-50%``.

    However, as the ``%`` characters included in any URL are automatically encoded,
    the resulting URL of this example would be ``/score-50%25`` (``%25`` is the
    result of encoding the ``%`` character).

.. seealso::

    For parameter handling within a Dependency Injection Class see
    :doc:`/configuration/using_parameters_in_dic`.
