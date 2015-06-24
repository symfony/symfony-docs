.. index::
    single: Service Container; Shared Services

How to Define Not Shared Services
=================================

.. versionadded:: 2.8
    The ``shared`` setting was introduced in Symfony 2.8. Prior to Symfony 2.8,
    you had to use the ``prototype`` scope.

In the service container, all services are shared by default. This means that
each time you retrieve the service, you'll get the *same* instance. This is
often the behaviour you want, but in some cases, you might want to always get a
*new* instance.

In order to always get a new instance, set the ``shared`` setting to ``false``
in your service definition:

.. configuration-block::

    .. code-block:: yaml

        services:
            app.some_not_shared_service:
                class: ...
                shared: false
                # ...

    .. code-block:: xml

        <services>
            <service id="app.some_not_shared_service" class="..." shared="false" />
        </services>

    .. code-block:: php

        $definition = new Definition('...');
        $definition->setShared(false);

        $container->setDefinition('app.some_not_shared_service', $definition);

Now, whenever you call ``$container->get('app.some_not_shared_service')`` or
inject this service, you'll recieve a new instance.
