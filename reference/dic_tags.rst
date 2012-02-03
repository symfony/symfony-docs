The Dependency Injection Tags
=============================

Tags:

* ``data_collector``
* ``form.type``
* ``form.type_extension``
* ``form.type_guesser``
* ``kernel.cache_warmer``
* ``kernel.event_listener``
* ``kernel.event_subscriber``
* ``monolog.logger``
* ``monolog.processor``
* ``templating.helper``
* ``routing.loader``
* ``translation.loader``
* ``twig.extension``
* ``validator.initializer``

Enabling Custom PHP Template Helpers
------------------------------------

To enable a custom template helper, add it as a regular service in one
of your configuration, tag it with ``templating.helper`` and define an
``alias`` attribute (the helper will be accessible via this alias in the
templates):

.. configuration-block::

    .. code-block:: yaml

        services:
            templating.helper.your_helper_name:
                class: Fully\Qualified\Helper\Class\Name
                tags:
                    - { name: templating.helper, alias: alias_name }

    .. code-block:: xml

        <service id="templating.helper.your_helper_name" class="Fully\Qualified\Helper\Class\Name">
            <tag name="templating.helper" alias="alias_name" />
        </service>

    .. code-block:: php

        $container
            ->register('templating.helper.your_helper_name', 'Fully\Qualified\Helper\Class\Name')
            ->addTag('templating.helper', array('alias' => 'alias_name'))
        ;

.. _reference-dic-tags-twig-extension:

Enabling Custom Twig Extensions
-------------------------------

To enable a Twig extension, add it as a regular service in one of your
configuration, and tag it with ``twig.extension``:

.. configuration-block::

    .. code-block:: yaml

        services:
            twig.extension.your_extension_name:
                class: Fully\Qualified\Extension\Class\Name
                tags:
                    - { name: twig.extension }

    .. code-block:: xml

        <service id="twig.extension.your_extension_name" class="Fully\Qualified\Extension\Class\Name">
            <tag name="twig.extension" />
        </service>

    .. code-block:: php

        $container
            ->register('twig.extension.your_extension_name', 'Fully\Qualified\Extension\Class\Name')
            ->addTag('twig.extension')
        ;

For information on how to create the actual Twig Extension class, see
`Twig's documentation`_ on the topic.

Before writing your own extensions, have a look at the
`Twig official extension repository`_ which already includes several
useful extensions. For example ``Intl`` and its ``localizeddate`` filter
that formats a date according to user's locale. These official Twig extensions
also have to be added as regular services:

.. configuration-block::

    .. code-block:: yaml

        services:
            twig.extension.intl:
                class: Twig_Extensions_Extension_Intl
                tags:
                    - { name: twig.extension }

    .. code-block:: xml

        <service id="twig.extension.intl" class="Twig_Extensions_Extension_Intl">
            <tag name="twig.extension" />
        </service>

    .. code-block:: php

        $container
            ->register('twig.extension.intl', 'Twig_Extensions_Extension_Intl')
            ->addTag('twig.extension')
        ;

.. _dic-tags-kernel-event-listener:

Enabling Custom Listeners
-------------------------

To enable a custom listener, add it as a regular service in one of your
configuration, and tag it with ``kernel.event_listener``. You must provide
the name of the event your service listens to, as well as the method that
will be called:

.. configuration-block::

    .. code-block:: yaml

        services:
            kernel.listener.your_listener_name:
                class: Fully\Qualified\Listener\Class\Name
                tags:
                    - { name: kernel.event_listener, event: xxx, method: onXxx }

    .. code-block:: xml

        <service id="kernel.listener.your_listener_name" class="Fully\Qualified\Listener\Class\Name">
            <tag name="kernel.event_listener" event="xxx" method="onXxx" />
        </service>

    .. code-block:: php

        $container
            ->register('kernel.listener.your_listener_name', 'Fully\Qualified\Listener\Class\Name')
            ->addTag('kernel.event_listener', array('event' => 'xxx', 'method' => 'onXxx'))
        ;

.. note::

    You can also specify priority as an attribute of the kernel.event_listener 
    tag (much like the method or event attributes), with either a positive 
    or negative integer. This allows you to make sure your listener will always 
    be called before or after another listener listening for the same event.



.. _dic-tags-kernel-event-subscriber:

Enabling Custom Subscribers
---------------------------

.. versionadded:: 2.1

   The ability to add kernel event subscribers is new to 2.1.

To enable a custom subscriber, add it as a regular service in one of your
configuration, and tag it with ``kernel.event_subscriber``:

.. configuration-block::

    .. code-block:: yaml

        services:
            kernel.subscriber.your_subscriber_name:
                class: Fully\Qualified\Subscriber\Class\Name
                tags:
                    - { name: kernel.event_subscriber }

    .. code-block:: xml

        <service id="kernel.subscriber.your_subscriber_name" class="Fully\Qualified\Subscriber\Class\Name">
            <tag name="kernel.event_subscriber" />
        </service>

    .. code-block:: php

        $container
            ->register('kernel.subscriber.your_subscriber_name', 'Fully\Qualified\Subscriber\Class\Name')
            ->addTag('kernel.event_subscriber')
        ;

.. note::

    Your service must implement the :class:`Symfony\Component\EventDispatcher\EventSubscriberInterface`
    interface.

.. note::

    If your service is created by a factory, you **MUST** correctly set the ``class``
    parameter for this tag to work correctly.

Enabling Custom Template Engines
--------------------------------

To enable a custom template engine, add it as a regular service in one
of your configuration, tag it with ``templating.engine``:

.. configuration-block::

    .. code-block:: yaml

        services:
            templating.engine.your_engine_name:
                class: Fully\Qualified\Engine\Class\Name
                tags:
                    - { name: templating.engine }

    .. code-block:: xml

        <service id="templating.engine.your_engine_name" class="Fully\Qualified\Engine\Class\Name">
            <tag name="templating.engine" />
        </service>

    .. code-block:: php

        $container
            ->register('templating.engine.your_engine_name', 'Fully\Qualified\Engine\Class\Name')
            ->addTag('templating.engine')
        ;

Enabling Custom Routing Loaders
-------------------------------

To enable a custom routing loader, add it as a regular service in one
of your configuration, and tag it with ``routing.loader``:

.. configuration-block::

    .. code-block:: yaml

        services:
            routing.loader.your_loader_name:
                class: Fully\Qualified\Loader\Class\Name
                tags:
                    - { name: routing.loader }

    .. code-block:: xml

        <service id="routing.loader.your_loader_name" class="Fully\Qualified\Loader\Class\Name">
            <tag name="routing.loader" />
        </service>

    .. code-block:: php

        $container
            ->register('routing.loader.your_loader_name', 'Fully\Qualified\Loader\Class\Name')
            ->addTag('routing.loader')
        ;

.. _dic_tags-monolog:

Using a custom logging channel with Monolog
-------------------------------------------

Monolog allows you to share its handlers between several logging channels.
The logger service uses the channel ``app`` but you can change the
channel when injecting the logger in a service.

.. configuration-block::

    .. code-block:: yaml

        services:
            my_service:
                class: Fully\Qualified\Loader\Class\Name
                arguments: [@logger]
                tags:
                    - { name: monolog.logger, channel: acme }

    .. code-block:: xml

        <service id="my_service" class="Fully\Qualified\Loader\Class\Name">
            <argument type="service" id="logger" />
            <tag name="monolog.logger" channel="acme" />
        </service>

    .. code-block:: php

        $definition = new Definition('Fully\Qualified\Loader\Class\Name', array(new Reference('logger'));
        $definition->addTag('monolog.logger', array('channel' => 'acme'));
        $container->register('my_service', $definition);;

.. note::

    This works only when the logger service is a constructor argument,
    not when it is injected through a setter.

.. _dic_tags-monolog-processor:

Adding a processor for Monolog
------------------------------

Monolog allows you to add processors in the logger or in the handlers to add
extra data in the records. A processor receives the record as an argument and
must return it after adding some extra data in the ``extra`` attribute of
the record.

Let's see how you can use the built-in ``IntrospectionProcessor`` to add
the file, the line, the class and the method where the logger was triggered.

You can add a processor globally:

.. configuration-block::

    .. code-block:: yaml

        services:
            my_service:
                class: Monolog\Processor\IntrospectionProcessor
                tags:
                    - { name: monolog.processor }

    .. code-block:: xml

        <service id="my_service" class="Monolog\Processor\IntrospectionProcessor">
            <tag name="monolog.processor" />
        </service>

    .. code-block:: php

        $definition = new Definition('Monolog\Processor\IntrospectionProcessor');
        $definition->addTag('monolog.processor');
        $container->register('my_service', $definition);

.. tip::

    If your service is not a callable (using ``__invoke``) you can add the
    ``method`` attribute in the tag to use a specific method.

You can add also a processor for a specific handler by using the ``handler``
attribute:

.. configuration-block::

    .. code-block:: yaml

        services:
            my_service:
                class: Monolog\Processor\IntrospectionProcessor
                tags:
                    - { name: monolog.processor, handler: firephp }

    .. code-block:: xml

        <service id="my_service" class="Monolog\Processor\IntrospectionProcessor">
            <tag name="monolog.processor" handler="firephp" />
        </service>

    .. code-block:: php

        $definition = new Definition('Monolog\Processor\IntrospectionProcessor');
        $definition->addTag('monolog.processor', array('handler' => 'firephp');
        $container->register('my_service', $definition);

You can also add a processor for a specific logging channel by using the ``channel``
attribute. This will register the processor only for the ``security`` logging
channel used in the Security component:

.. configuration-block::

    .. code-block:: yaml

        services:
            my_service:
                class: Monolog\Processor\IntrospectionProcessor
                tags:
                    - { name: monolog.processor, channel: security }

    .. code-block:: xml

        <service id="my_service" class="Monolog\Processor\IntrospectionProcessor">
            <tag name="monolog.processor" channel="security" />
        </service>

    .. code-block:: php

        $definition = new Definition('Monolog\Processor\IntrospectionProcessor');
        $definition->addTag('monolog.processor', array('channel' => 'security');
        $container->register('my_service', $definition);

.. note::

    You cannot use both the ``handler`` and ``channel`` attributes for the
    same tag as handlers are shared between all channels.

..  _`Twig's documentation`: http://twig.sensiolabs.org/doc/extensions.html
..  _`Twig official extension repository`: http://github.com/fabpot/Twig-extensions
