.. index::
   single: Dependency Injection; Lazy Services

Lazy Services
=============

.. versionadded:: 2.3
   Lazy services were added in Symfony 2.3.

Configuring lazy services
-------------------------

In some particular cases where a very heavy service is always requested,
but not always used, you may want to mark it as ``lazy`` to delay its instantiation.

In order to have services to lazily instantiate, you will first need to install
the `ProxyManager bridge`_::

    php composer.phar require symfony/proxy-manager-bridge:2.3.*

You can mark the service as ``lazy`` by manipulating its definitions:


.. configuration-block::

    .. code-block:: yaml

        services:
           foo:
             class: Example\Foo
             lazy: true

    .. code-block:: xml

        <service id="foo" class="Example\Foo" lazy="true" />

    .. code-block:: php

        $definition = new Definition('Example\Foo');
        $definition->setLazy(true);
        $container->setDefinition('foo', $definition);

You can then require the service from the container::

    $service = $container->get($serviceId);

At this point the retrieved ``$service`` should be a virtual `proxy`_ with the same
signature of the class representing the service.

.. note::

    If you don't install the `ProxyManager bridge`_, the container will just skip
    over the ``lazy`` flag and simply instantiate the service as it would normally do.

The proxy gets initialized and the actual service is instantiated as soon as you interact
in any way with this object.

Additional resources
--------------------


You can read more about how proxies are instantiated, generated and initialized in
the `documentation of ProxyManager`_.


.. _`ProxyManager bridge`: https://github.com/symfony/symfony/tree/2.3/src/Symfony/Bridge/ProxyManager
.. _`proxy`: http://en.wikipedia.org/wiki/Proxy_pattern
.. _`documentation of ProxyManager`: https://github.com/Ocramius/ProxyManager/blob/master/docs/lazy-loading-value-holder.md