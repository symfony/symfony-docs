.. index::
    single: Service Container; Shared Services

How to Define Non Shared Services
=================================

In the service container, all services are shared by default. This means that
each time you retrieve the service, you'll get the *same* instance. This is
usually the behavior you want, but in some cases, you might want to always get a
*new* instance.

In order to always get a new instance, set the ``shared`` setting to ``false``
in your service definition:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            AppBundle\SomeNonSharedService:
                shared: false
                # ...

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <services>
            <service id="AppBundle\SomeNonSharedService" shared="false" />
        </services>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\SomeNonSharedService;

        $container->register(SomeNonSharedService::class)
            ->setShared(false);

Now, whenever you request an the ``AppBundle\SomeNonSharedService`` from the container,
you will be passed a new instance.
