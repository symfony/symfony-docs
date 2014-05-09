.. index::
   single: Dependency Injection; Lazy Services

Lazy Services
=============

.. versionadded:: 2.3
   Lazy services were introduced in Symfony 2.3.

Why lazy Services?
------------------

In some cases, you may want to inject a service that is a bit heavy to instantiate,
but is not always used inside your object. For example, imagine you have
a ``NewsletterManager`` and you inject a ``mailer`` service into it. Only
a few methods on your ``NewsletterManager`` actually use the ``mailer``,
but even when you don't need it, a ``mailer`` service is always instantiated
in order to construct your ``NewsletterManager``.

Configuring lazy services is one answer to this. With a lazy service, a "proxy"
of the ``mailer`` service is actually injected. It looks and acts just like
the ``mailer``, except that the ``mailer`` isn't actually instantiated until
you interact with the proxy in some way.

Installation
------------

In order to use the lazy service instantiation, you will first need to install
the `ProxyManager bridge`_:

.. code-block:: bash

    $ php composer.phar require symfony/proxy-manager-bridge:2.3.*

.. note::

    If you're using the full-stack framework, the proxy manager bridge is already
    included but the actual proxy manager needs to be included. Therefore add

    .. code-block:: json

        "require": {
            "ocramius/proxy-manager": "0.5.*"
        }

    to your ``composer.json``. Afterwards compile your container and check
    to make sure that you get a proxy for your lazy services.

Configuration
-------------

You can mark the service as ``lazy`` by manipulating its definition:

.. configuration-block::

    .. code-block:: yaml

        services:
           foo:
             class: Acme\Foo
             lazy: true

    .. code-block:: xml

        <service id="foo" class="Acme\Foo" lazy="true" />

    .. code-block:: php

        $definition = new Definition('Acme\Foo');
        $definition->setLazy(true);
        $container->setDefinition('foo', $definition);

You can then require the service from the container::

    $service = $container->get('foo');

At this point the retrieved ``$service`` should be a virtual `proxy`_ with
the same signature of the class representing the service. You can also inject
the service just like normal into other services. The object that's actually
injected will be the proxy.

To check if your proxy works you can simply check the interface of the
received object.

.. code-block:: php

    var_dump(class_implements($service));

If the class implements the ``ProxyManager\Proxy\LazyLoadingInterface`` your
lazy loaded services are working.

.. note::

    If you don't install the `ProxyManager bridge`_, the container will just
    skip over the ``lazy`` flag and simply instantiate the service as it would
    normally do.

The proxy gets initialized and the actual service is instantiated as soon
as you interact in any way with this object.

Additional Resources
--------------------

You can read more about how proxies are instantiated, generated and initialized
in the `documentation of ProxyManager`_.


.. _`ProxyManager bridge`: https://github.com/symfony/symfony/tree/master/src/Symfony/Bridge/ProxyManager
.. _`proxy`: http://en.wikipedia.org/wiki/Proxy_pattern
.. _`documentation of ProxyManager`: https://github.com/Ocramius/ProxyManager/blob/master/docs/lazy-loading-value-holder.md
