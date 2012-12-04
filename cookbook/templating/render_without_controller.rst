.. index::
   single: Templating; Render template without custom controller

How to render a template without a custom controller
====================================================

This guide explains how to render a template within another template and
how to configure a page without a custom controller.

The intention is, that there may be page in your application, that doesn't
need a controller, because there is no action associated with them.

Rendering a template in twig:

.. code-block:: jinja

    {% render "FrameworkBundle:Template:template" with {template: 'AcmeBundle::static.html.twig'} %}

Directly routing to a template without custom controller with additional
caching parameters:

.. configuration-block::

    .. code-block:: yaml

        acme_static:
            pattern: /static
            defaults:
                _controller: FrameworkBundle:Template:template
                template: 'AcmeBundle::static.html.twig'
                maxAge: 86400
                sharedMaxAge: 86400

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="acme_static" pattern="/static">
                <default key="_controller">FrameworkBundle:Template:template</default>
                <default key="template">AcmeBundle::static.html.twig</default>
                <default key="maxAge">86400</default>
                <default key="sharedMaxAge">86400</default>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('acme_static', new Route('/static', array(
            '_controller'  => 'FrameworkBundle:Template:template',
            'template'     => 'Acmebundle::static.html.twig',
            'maxAge'       => 86400,
            'sharedMaxAge' => 86400,
        )));

        return $collection;

By default no caching headers were set. If you want to disable proxy
caching, but want to keep browser caching enabled, set ``private`` to
``false`` explictly.
