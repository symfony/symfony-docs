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

    .. code-block:: yaml

        # app/config/routing.yml
        contact:
            path:     /{_locale}/contact
            defaults: { _controller: AppBundle:Main:contact }
            requirements:
                _locale: '%app.locales%'

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="contact" path="/{_locale}/contact">
                <default key="_controller">AppBundle:Main:contact</default>
                <requirement key="_locale">%app.locales%</requirement>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('contact', new Route('/{_locale}/contact', array(
            '_controller' => 'AppBundle:Main:contact',
        ), array(
            '_locale' => '%app.locales%',
        )));

        return $collection;

You can now control and set the  ``app.locales`` parameter somewhere
in your container:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        parameters:
            app.locales: en|es

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <parameters>
            <parameter key="app.locales">en|es</parameter>
        </parameters>

    .. code-block:: php

        // app/config/config.php
        $container->setParameter('app.locales', 'en|es');

You can also use a parameter to define your route path (or part of your
path):

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        some_route:
            path:     /%app.route_prefix%/contact
            defaults: { _controller: AppBundle:Main:contact }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="some_route" path="/%app.route_prefix%/contact">
                <default key="_controller">AppBundle:Main:contact</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('some_route', new Route('/%app.route_prefix%/contact', array(
            '_controller' => 'AppBundle:Main:contact',
        )));

        return $collection;

.. note::

    Just like in normal service container configuration files, if you actually
    need a ``%`` in your route, you can escape the percent sign by doubling
    it, e.g. ``/score-50%%``, which would resolve to ``/score-50%``.

    However, as the ``%`` characters included in any URL are automatically encoded,
    the resulting URL of this example would be ``/score-50%25`` (``%25`` is the
    result of encoding the ``%`` character).

.. seealso::

    For parameter handling within a Dependency Injection Class see
    :doc:`/cookbook/configuration/using_parameters_in_dic`.
