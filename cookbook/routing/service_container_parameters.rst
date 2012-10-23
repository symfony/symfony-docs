.. index::
   single: Routing; _service_container_parameters

How to use Service Container parameters in your routes
======================================================

.. versionadded:: 2.1
    This feature was added in Symfony 2.1

Sometimes you may find useful to make some parts of your routes
globally configurable. For instance, if you build an internationalized
site, you'll probably start with one or two locales. Surely you'll
add requirements to avoid a user specify a locale other than those you
support, or simple to increase the power of your routes.

Suppose that you have a lot of routes and you want to add yet another locale
to your application. If you hardcode the requirements directly in your code,
then you'll need to search everywhere and change the requirements.

Then, why not use a configurable parameter, defined in the Service Container
and make your life easier?

Here you have an example on how to make the ``_locale`` of your routes configurable.

.. configuration-block::

    .. code-block:: yaml

        contact:
            pattern:  /{_locale}/contact
            defaults: { _controller: AcmeDemoBundle:Main:contact }
            requirements:
                _locale: %acme_demo.locales%

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="contact" pattern="/{_locale}/contact">
                <default key="_controller">AcmeDemoBundle:Main:contact</default>
                <requirement key="_locale">%acme_demo.locales%</requirement>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('contact', new Route('/{_locale}/contact', array(
            '_controller' => 'AcmeDemoBundle:Main:contact',
        ), array(
            '_locale' => '%acme_demo.locales%',
        )));

        return $collection;

Easy like that, then simply define the ``acme_demo.locales`` parameter in your container.

You can also define patterns which use parameters defined in the Service Container.

.. configuration-block::

    .. code-block:: yaml

        some_route:
            pattern:  /%acme_demo.parameter_name%
            defaults: { _controller: AcmeDemoBundle:Main:index }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="some_route" pattern="/%acme_demo.parameter_name%">
                <default key="_controller">AcmeDemoBundle:Main:index</default>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('some_route', new Route('/%acme_demo.parameter_name%', array(
            '_controller' => 'AcmeDemoBundle:Main:contact',
        )));

        return $collection;

.. note::
    You can escape a parameter by doubling the ``%``, e.g. ``/%%acme_demo.parameter_name%%``

