.. index::
   single: Routing; Scheme requirement

How to force routes to always use HTTPS
=======================================

Sometimes, you want to secure some routes and be sure that they are always
accessed via the HTTPS protocol. The Routing component allows you to enforce
the HTTP scheme via the ``_scheme`` requirement:

.. configuration-block::

    .. code-block:: yaml

        secure:
            pattern:  /secure
            defaults: { _controller: AcmeDemoBundle:Main:secure }
            requirements:
                _scheme:  https

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="secure" pattern="/secure">
                <default key="_controller">AcmeDemoBundle:Main:secure</default>
                <requirement key="_scheme">https</requirement>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('secure', new Route('/secure', array(
            '_controller' => 'AcmeDemoBundle:Main:secure',
        ), array(
            '_scheme' => 'https',
        )));

        return $collection;

The above configuration forces the ``secure`` route to always use HTTPS.

When generating the ``secure`` URL, and if the current scheme is HTTP, Symfony
will automatically generate an absolute URL with HTTPS as the scheme:

.. code-block:: text

    # If the current scheme is HTTPS
    {{ path('secure') }}
    # generates /secure

    # If the current scheme is HTTP
    {{ path('secure') }}
    # generates https://example.com/secure

The requirement is also enforced for incoming requests. If you try to access
the ``/secure`` path with HTTP, you will automatically be redirected to the
same URL, but with the HTTPS scheme.

The above example uses ``https`` for the ``_scheme``, but you can also force a
URL to always use ``http``.

.. note::

    The Security component provides another way to enforce the HTTP scheme via
    the ``requires_channel`` setting. This alternative method is better suited
    to secure an "area" of your website (all URLs under ``/admin``) or when
    you want to secure URLs defined in a third party bundle.
