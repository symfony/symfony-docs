.. index::
   single: Routing; Scheme requirement

How to Force Routes to Always Use HTTPS or HTTP
===============================================

Sometimes, you want to secure some routes and be sure that they are always
accessed via the HTTPS protocol. The Routing component allows you to enforce
the URI scheme via schemes:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/MainController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

        class MainController extends Controller
        {
            /**
             * @Route("/secure", name="secure", schemes={"https"})
             */
            public function secure()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        secure:
            path:       /secure
            controller: App\Controller\MainController::secure
            schemes:    [https]

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="secure" path="/secure" schemes="https">
                <default key="_controller">App\Controller\MainController::secure</default>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $routes = new RouteCollection();
        $routes->add('secure', new Route('/secure', array(
            '_controller' => 'App\Controller\MainController::secure',
        ), array(), array(), '', array('https')));

        return $routes;

The above configuration forces the ``secure`` route to always use HTTPS.

When generating the ``secure`` URL, and if the current scheme is HTTP, Symfony
will automatically generate an absolute URL with HTTPS as the scheme, even when
using the ``path()`` function:

.. code-block:: twig

    {# If the current scheme is HTTPS #}
    {{ path('secure') }}
    {# generates a relative URL: /secure #}

    {# If the current scheme is HTTP #}
    {{ path('secure') }}
    {# generates an absolute URL: https://example.com/secure #}

The requirement is also enforced for incoming requests. If you try to access
the ``/secure`` path with HTTP, you will automatically be redirected to the
same URL, but with the HTTPS scheme.

The above example uses ``https`` for the scheme, but you can also force a URL
to always use ``http``.

.. note::

    The Security component provides another way to enforce HTTP or HTTPS via
    the ``requires_channel`` setting. This alternative method is better suited
    to secure an "area" of your website (all URLs under ``/admin``) or when
    you want to secure URLs defined in a third party bundle (see
    :doc:`/security/force_https` for more details).
