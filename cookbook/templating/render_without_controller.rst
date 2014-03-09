.. index::
   single: Templating; Render template without custom controller

How to render a Template without a custom Controller
====================================================

Usually, when you need to create a page, you need to create a controller
and render a template from within that controller. But if you're rendering
a simple template that doesn't need any data passed into it, you can avoid
creating the controller entirely, by using the built-in ``FrameworkBundle:Template:template``
controller.

For example, suppose you want to render a ``AcmeBundle:Static:privacy.html.twig``
template, which doesn't require that any variables are passed to it. You
can do this without creating a controller:

.. configuration-block::

    .. code-block:: yaml

        acme_privacy:
            path: /privacy
            defaults:
                _controller: FrameworkBundle:Template:template
                template:    'AcmeBundle:Static:privacy.html.twig'

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="acme_privacy" path="/privacy">
                <default key="_controller">FrameworkBundle:Template:template</default>
                <default key="template">AcmeBundle:Static:privacy.html.twig</default>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('acme_privacy', new Route('/privacy', array(
            '_controller'  => 'FrameworkBundle:Template:template',
            'template'     => 'AcmeBundle:Static:privacy.html.twig',
        )));

        return $collection;

The ``FrameworkBundle:Template:template`` controller will simply render whatever
template you've passed as the ``template`` default value.

You can of course also use this trick when rendering embedded controllers
from within a template. But since the purpose of rendering a controller from
within a template is typically to prepare some data in a custom controller,
this is probably only useful if you'd like to cache this page partial (see
:ref:`cookbook-templating-no-controller-caching`).

.. configuration-block::

    .. code-block:: html+jinja

        {{ render(url('acme_privacy')) }}

    .. code-block:: html+php

        <?php echo $view['actions']->render(
            $view['router']->generate('acme_privacy', array(), true)
        ) ?>

.. _cookbook-templating-no-controller-caching:

Caching the static Template
---------------------------

Since templates that are rendered in this way are typically static, it might
make sense to cache them. Fortunately, this is easy! By configuring a few
other variables in your route, you can control exactly how your page is cached:

.. configuration-block::

    .. code-block:: yaml

        acme_privacy:
            path: /privacy
            defaults:
                _controller:  FrameworkBundle:Template:template
                template:     'AcmeBundle:Static:privacy.html.twig'
                maxAge:       86400
                sharedAge:    86400

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="acme_privacy" path="/privacy">
                <default key="_controller">FrameworkBundle:Template:template</default>
                <default key="template">AcmeBundle:Static:privacy.html.twig</default>
                <default key="maxAge">86400</default>
                <default key="sharedAge">86400</default>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('acme_privacy', new Route('/privacy', array(
            '_controller'  => 'FrameworkBundle:Template:template',
            'template'     => 'AcmeBundle:Static:privacy.html.twig',
            'maxAge'       => 86400,
            'sharedAge' => 86400,
        )));

        return $collection;

The ``maxAge`` and ``sharedAge`` values are used to modify the Response
object created in the controller. For more information on caching, see
:doc:`/book/http_cache`.

There is also a ``private`` variable (not shown here). By default, the Response
will be made public, as long as ``maxAge`` or ``sharedAge`` are passed.
If set to ``true``, the Response will be marked as private.
