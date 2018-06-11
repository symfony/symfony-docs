.. index::
   single: Templating; Render template without custom controller

How to Render a Template without a custom Controller
====================================================

Usually, when you need to create a page, you need to create a controller
and render a template from within that controller. But if you're rendering
a simple template that doesn't need any data passed into it, you can avoid
creating the controller entirely, by using the built-in
``Symfony\Bundle\FrameworkBundle\Controller\TemplateController::templateAction``
controller.

For example, suppose you want to render a ``static/privacy.html.twig``
template, which doesn't require that any variables are passed to it. You
can do this without creating a controller:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        acme_privacy:
            path:         /privacy
            controller:   Symfony\Bundle\FrameworkBundle\Controller\TemplateController::templateAction
            defaults:
                template: static/privacy.html.twig

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="acme_privacy" path="/privacy">
                <default key="_controller">Symfony\Bundle\FrameworkBundle\Controller\TemplateController::templateAction</default>
                <default key="template">static/privacy.html.twig</default>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $routes = new RouteCollection();
        $routes->add('acme_privacy', new Route('/privacy', array(
            '_controller' => 'Symfony\Bundle\FrameworkBundle\Controller\TemplateController::templateAction',
            'template'    => 'static/privacy.html.twig',
        )));

        return $routes;

The ``TemplateController`` will simply render whatever template you've passed as
the ``template`` default value.

You can of course also use this trick when rendering embedded controllers
from within a template. But since the purpose of rendering a controller from
within a template is typically to prepare some data in a custom controller,
this is probably only useful if you'd like to cache this page partial (see
:ref:`templating-no-controller-caching`).

.. configuration-block::

    .. code-block:: html+twig

        {{ render(url('acme_privacy')) }}

    .. code-block:: html+php

        <?php
        use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
        ?>

        <?php echo $view['actions']->render(
            $view['router']->url('acme_privacy', array())
        ) ?>

.. _templating-no-controller-caching:

Caching the static Template
---------------------------

Since templates that are rendered in this way are typically static, it might
make sense to cache them. Fortunately, this is easy! By configuring a few
other variables in your route, you can control exactly how your page is cached:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        acme_privacy:
            path:          /privacy
            controller:    Symfony\Bundle\FrameworkBundle\Controller\TemplateController::templateAction
            defaults:
                template:  'static/privacy.html.twig'
                maxAge:    86400
                sharedAge: 86400

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="acme_privacy" path="/privacy">
                <default key="_controller">Symfony\Bundle\FrameworkBundle\Controller\TemplateController::templateAction</default>
                <default key="template">static/privacy.html.twig</default>
                <default key="maxAge">86400</default>
                <default key="sharedAge">86400</default>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $routes = new RouteCollection();
        $routes->add('acme_privacy', new Route('/privacy', array(
            '_controller' => 'Symfony\Bundle\FrameworkBundle\Controller\TemplateController::templateAction',
            'template'    => 'static/privacy.html.twig',
            'maxAge'      => 86400,
            'sharedAge'   => 86400,
        )));

        return $routes;

The ``maxAge`` and ``sharedAge`` values are used to modify the Response
object created in the controller. For more information on caching, see
:doc:`/http_cache`.

There is also a ``private`` variable (not shown here). By default, the Response
will be made public, as long as ``maxAge`` or ``sharedAge`` are passed.
If set to ``true``, the Response will be marked as private.
