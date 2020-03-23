Built-in Symfony Service Tags
=============================

:doc:`Service tags </service_container/tags>` are the mechanism used by the
:doc:`DependencyInjection component </components/dependency_injection>` to flag
services that require special processing, like console commands or Twig extensions.

These are the most common tags provided by Symfony components, but in your
application there could be more tags available provided by third-party bundles:

========================================  ========================================================================
Tag Name                                  Usage
========================================  ========================================================================
`auto_alias`_                             Define aliases based on the value of container parameters
`console.command`_                        Add a command
`container.hot_path`_                     Add to list of always needed services
`controller.argument_value_resolver`_     Register a value resolver for controller arguments such as ``Request``
`data_collector`_                         Create a class that collects custom data for the profiler
`doctrine.event_listener`_                Add a Doctrine event listener
`doctrine.event_subscriber`_              Add a Doctrine event subscriber
`form.type`_                              Create a custom form field type
`form.type_extension`_                    Create a custom "form extension"
`form.type_guesser`_                      Add your own logic for "form type guessing"
`kernel.cache_clearer`_                   Register your service to be called during the cache clearing process
`kernel.cache_warmer`_                    Register your service to be called during the cache warming process
`kernel.event_listener`_                  Listen to different events/hooks in Symfony
`kernel.event_subscriber`_                To subscribe to a set of different events/hooks in Symfony
`kernel.fragment_renderer`_               Add new HTTP content rendering strategies
`kernel.reset`_                           Allows to clean up services between requests
`mime.mime_type_guesser`_                 Add your own logic for guessing MIME types
`monolog.logger`_                         Logging with a custom logging channel
`monolog.processor`_                      Add a custom processor for logging
`routing.loader`_                         Register a custom service that loads routes
`routing.expression_language_provider`_   Register a provider for expression language functions in routing
`security.expression_language_provider`_  Register a provider for expression language functions in security
`security.voter`_                         Add a custom voter to Symfony's authorization logic
`security.remember_me_aware`_             To allow remember me authentication
`serializer.encoder`_                     Register a new encoder in the ``serializer`` service
`serializer.normalizer`_                  Register a new normalizer in the ``serializer`` service
`swiftmailer.default.plugin`_             Register a custom SwiftMailer Plugin
`translation.loader`_                     Register a custom service that loads translations
`translation.extractor`_                  Register a custom service that extracts translation messages from a file
`translation.dumper`_                     Register a custom service that dumps translation messages
`twig.extension`_                         Register a custom Twig Extension
`twig.loader`_                            Register a custom service that loads Twig templates
`twig.runtime`_                           Register a lazy-loaded Twig Extension
`validator.constraint_validator`_         Create your own custom validation constraint
`validator.initializer`_                  Register a service that initializes objects before validation
========================================  ========================================================================

auto_alias
----------

**Purpose**: Define aliases based on the value of container parameters

Consider the following configuration that defines three different but related
services:

.. configuration-block::

    .. code-block:: yaml

        services:
            app.mysql_lock:
                class: App\Lock\MysqlLock
            app.postgresql_lock:
                class: App\Lock\PostgresqlLock
            app.sqlite_lock:
                class: App\Lock\SqliteLock

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.mysql_lock"
                    class="App\Lock\MysqlLock"/>
                <service id="app.postgresql_lock"
                    class="App\Lock\PostgresqlLock"/>
                <service id="app.sqlite_lock"
                    class="App\Lock\SqliteLock"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Lock\MysqlLock;
        use App\Lock\PostgresqlLock;
        use App\Lock\SqliteLock;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set('app.mysql_lock', MysqlLock::class);
            $services->set('app.postgresql_lock', PostgresqlLock::class);
            $services->set('app.sqlite_lock', SqliteLock::class);
        };

Instead of dealing with these three services, your application needs a generic
``app.lock`` service that will be an alias to one of these services, depending on
some configuration. Thanks to the ``auto_alias`` option, you can automatically create
that alias based on the value of a configuration parameter.

Considering that a configuration parameter called ``database_type`` exists. Then,
the generic ``app.lock`` service can be defined as follows:

.. configuration-block::

    .. code-block:: yaml

        services:
            app.mysql_lock:
                # ...
            app.postgresql_lock:
                # ...
            app.sqlite_lock:
                # ...
            app.lock:
                tags:
                    - { name: auto_alias, format: "app.%database_type%_lock" }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.mysql_lock"
                    class="App\Lock\MysqlLock"/>
                <service id="app.postgresql_lock"
                    class="App\Lock\PostgresqlLock"/>
                <service id="app.sqlite_lock"
                    class="App\Lock\SqliteLock"/>

                <service id="app.lock">
                    <tag name="auto_alias" format="app.%database_type%_lock"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Lock\MysqlLock;
        use App\Lock\PostgresqlLock;
        use App\Lock\SqliteLock;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set('app.mysql_lock', MysqlLock::class);
            $services->set('app.postgresql_lock', PostgresqlLock::class);
            $services->set('app.sqlite_lock', SqliteLock::class);

            $services->set('app.lock')
                ->tag('auto_alias', ['format' => 'app.%database_type%_lock'])
            ;
        };

The ``format`` option defines the expression used to construct the name of the service
to alias. This expression can use any container parameter (as usual,
wrapping their names with ``%`` characters).

.. note::

    When using the ``auto_alias`` tag, it's not mandatory to define the aliased
    services as private. However, doing that (like in the above example) makes
    sense most of the times to prevent accessing those services directly instead
    of using the generic service alias.

.. versionadded:: 5.1

    In Symfony versions prior to 5.1, you needed to manually add the
    ``Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass``
    compiler pass to the container for this feature to work. This compiler pass
    is now added automatically.

console.command
---------------

**Purpose**: Add a command to the application

For details on registering your own commands in the service container, read
:doc:`/console/commands_as_services`.

container.hot_path
------------------

**Purpose**: Add to list of always needed services

This tag identifies the services that are always needed. It is only applied to
a very short list of bootstrapping services (like ``router``, ``event_dispatcher``,
``http_kernel``, ``request_stack``, etc.). Then, it is propagated to all dependencies
of these services, with a special case for event listeners, where only listed events
are propagated to their related listeners.

It will replace, in cache for generated service factories, the PHP autoload by
plain inlined ``include_once``. The benefit is a complete bypass of the autoloader
for services and their class hierarchy. The result is as significant performance improvement.

Use this tag with great caution, you have to be sure that the tagged service is always used.

controller.argument_value_resolver
----------------------------------

**Purpose**: Register a value resolver for controller arguments such as ``Request``

Value resolvers implement the
:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolverInterface`
and are used to resolve argument values for controllers as described here:
:doc:`/controller/argument_value_resolver`.

data_collector
--------------

**Purpose**: Create a class that collects custom data for the profiler

For details on creating your own custom data collection, read the
:doc:`/profiler/data_collector` article.

doctrine.event_listener
-----------------------

**Purpose**: Add a Doctrine event listener

For details on creating Doctrine event listeners, read the
:doc:`Doctrine events </doctrine/events>` article.

doctrine.event_subscriber
-------------------------

**Purpose**: Add a Doctrine event subscriber

For details on creating Doctrine event subscribers, read the
:doc:`Doctrine events </doctrine/events>` article.

.. _dic-tags-form-type:

form.type
---------

**Purpose**: Create a custom form field type

For details on creating your own custom form type, read the
:doc:`/form/create_custom_field_type` article.

form.type_extension
-------------------

**Purpose**: Create a custom "form extension"

For details on creating Form type extensions, read the
:doc:`/form/create_form_type_extension` article.

.. _reference-dic-type_guesser:

form.type_guesser
-----------------

**Purpose**: Add your own logic for "form type guessing"

This tag allows you to add your own logic to the :ref:`form guessing <form-type-guessing>`
process. By default, form guessing is done by "guessers" based on the validation
metadata and Doctrine metadata (if you're using Doctrine) or Propel metadata
(if you're using Propel).

.. seealso::

    For information on how to create your own type guesser, see
    :doc:`/form/type_guesser`.

kernel.cache_clearer
--------------------

**Purpose**: Register your service to be called during the cache clearing
process

Cache clearing occurs whenever you call ``cache:clear`` command. If your
bundle caches files, you should add custom cache clearer for clearing those
files during the cache clearing process.

In order to register your custom cache clearer, first you must create a
service class::

    // src/Cache/MyClearer.php
    namespace App\Cache;

    use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

    class MyClearer implements CacheClearerInterface
    {
        public function clear($cacheDirectory)
        {
            // clear your cache
        }
    }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
your service will be automatically tagged with ``kernel.cache_clearer``. But, you
can also register it manually:

.. configuration-block::

    .. code-block:: yaml

        services:
            App\Cache\MyClearer:
                tags: [kernel.cache_clearer]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Cache\MyClearer">
                    <tag name="kernel.cache_clearer"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use App\Cache\MyClearer;

        $container
            ->register(MyClearer::class)
            ->addTag('kernel.cache_clearer')
        ;

kernel.cache_warmer
-------------------

**Purpose**: Register your service to be called during the cache warming
process

Cache warming occurs whenever you run the ``cache:warmup`` or ``cache:clear``
command (unless you pass ``--no-warmup`` to ``cache:clear``). It is also run
when handling the request, if it wasn't done by one of the commands yet.

The purpose is to initialize any cache that will be needed by the application
and prevent the first user from any significant "cache hit" where the cache
is generated dynamically.

To register your own cache warmer, first create a service that implements
the :class:`Symfony\\Component\\HttpKernel\\CacheWarmer\\CacheWarmerInterface` interface::

    // src/Cache/MyCustomWarmer.php
    namespace App\Cache;

    use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

    class MyCustomWarmer implements CacheWarmerInterface
    {
        public function warmUp($cacheDirectory)
        {
            // ... do some sort of operations to "warm" your cache
        }

        public function isOptional()
        {
            return true;
        }
    }

The ``isOptional()`` method should return true if it's possible to use the
application without calling this cache warmer. In Symfony, optional warmers
are always executed by default (you can change this by using the
``--no-optional-warmers`` option when executing the command).

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
your service will be automatically tagged with ``kernel.cache_warmer``. But, you
can also register it manually:

.. configuration-block::

    .. code-block:: yaml

        services:
            App\Cache\MyCustomWarmer:
                tags:
                    - { name: kernel.cache_warmer, priority: 0 }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Cache\MyCustomWarmer">
                    <tag name="kernel.cache_warmer" priority="0"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use App\Cache\MyCustomWarmer;

        $container
            ->register(MyCustomWarmer::class)
            ->addTag('kernel.cache_warmer', ['priority' => 0])
        ;

.. note::

    The ``priority`` is optional and its value is a positive or negative integer
    that defaults to ``0``. The higher the number, the earlier that warmers are
    executed.

.. caution::

    If your cache warmer fails its execution because of any exception, Symfony
    won't try to execute it again for the next requests. Therefore, your
    application and/or bundles should be prepared for when the contents
    generated by the cache warmer are not available.

.. _core-cache-warmers:

In addition to your own cache warmers, Symfony components and third-party
bundles define cache warmers too for their own purposes. You can list them all
with the following command:

.. code-block:: terminal

    $ php bin/console debug:container --tag=kernel.cache_warmer

.. _dic-tags-kernel-event-listener:

kernel.event_listener
---------------------

**Purpose**: To listen to different events/hooks in Symfony

During the execution of a Symfony application, different events are triggered
and you can also dispatch custom events. This tag allows you to *hook* your own
classes into any of those events.

For a full example of this listener, read the :doc:`/event_dispatcher`
article.

Core Event Listener Reference
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For the reference of Event Listeners associated with each kernel event,
see the :doc:`Symfony Events Reference </reference/events>`.

.. _dic-tags-kernel-event-subscriber:

kernel.event_subscriber
-----------------------

**Purpose**: To subscribe to a set of different events/hooks in Symfony

This is an alternative way to create an event listener, and is the recommended
way (instead of using ``kernel.event_listener``). See :ref:`events-subscriber`.

kernel.fragment_renderer
------------------------

**Purpose**: Add a new HTTP content rendering strategy

To add a new rendering strategy - in addition to the core strategies like
``EsiFragmentRenderer`` - create a class that implements
:class:`Symfony\\Component\\HttpKernel\\Fragment\\FragmentRendererInterface`,
register it as a service, then tag it with ``kernel.fragment_renderer``.

kernel.reset
------------

**Purpose**: Clean up services between requests

During the ``kernel.terminate`` event, Symfony looks for any service tagged
with the ``kernel.reset`` tag to reinitialize their state. This is done by
calling to the method whose name is configured in the ``method`` argument of
the tag.

This is mostly useful when running your projects in application servers that
reuse the Symfony application between requests to improve performance. This tag
is applied for example to the built-in :doc:`data collectors </profiler/data_collector>`
of the profiler to delete all their information.

.. _dic_tags-mime:

mime.mime_type_guesser
----------------------

**Purpose**: Add your own logic for guessing MIME types

This tag is used to register your own :ref:`MIME type guessers <components-mime-type-guess>`
in case the guessers provided by the :doc:`Mime component </components/mime>`
don't fit your needs.

.. _dic_tags-monolog:

monolog.logger
--------------

**Purpose**: To use a custom logging channel with Monolog

Monolog allows you to share its handlers between several logging channels.
The logger service uses the channel ``app`` but you can change the
channel when injecting the logger in a service.

.. configuration-block::

    .. code-block:: yaml

        services:
            App\Log\CustomLogger:
                arguments: ['@logger']
                tags:
                    - { name: monolog.logger, channel: app }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Log\CustomLogger">
                    <argument type="service" id="logger"/>
                    <tag name="monolog.logger" channel="app"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use App\Log\CustomLogger;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register(CustomLogger::class)
            ->addArgument(new Reference('logger'))
            ->addTag('monolog.logger', ['channel' => 'app']);

.. tip::

    You can create :doc:`custom channels </logging/channels_handlers>` and
    even :ref:`autowire logging channels <monolog-autowire-channels>`.

.. _dic_tags-monolog-processor:

monolog.processor
-----------------

**Purpose**: Add a custom processor for logging

Monolog allows you to add processors in the logger or in the handlers to
add extra data in the records. A processor receives the record as an argument
and must return it after adding some extra data in the ``extra`` attribute
of the record.

The built-in ``IntrospectionProcessor`` can be used to add the file, the
line, the class and the method where the logger was triggered.

You can add a processor globally:

.. configuration-block::

    .. code-block:: yaml

        services:
            Monolog\Processor\IntrospectionProcessor:
                tags: [monolog.processor]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Monolog\Processor\IntrospectionProcessor">
                    <tag name="monolog.processor"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use Monolog\Processor\IntrospectionProcessor;

        $container
            ->register(IntrospectionProcessor::class)
            ->addTag('monolog.processor')
        ;

.. tip::

    If your service is not a callable (using ``__invoke()``) you can add the
    ``method`` attribute in the tag to use a specific method.

You can add also a processor for a specific handler by using the ``handler``
attribute:

.. configuration-block::

    .. code-block:: yaml

        services:
            Monolog\Processor\IntrospectionProcessor:
                tags:
                    - { name: monolog.processor, handler: firephp }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Monolog\Processor\IntrospectionProcessor">
                    <tag name="monolog.processor" handler="firephp"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use Monolog\Processor\IntrospectionProcessor;

        $container
            ->register(IntrospectionProcessor::class)
            ->addTag('monolog.processor', ['handler' => 'firephp'])
        ;

You can also add a processor for a specific logging channel by using the
``channel`` attribute. This will register the processor only for the
``security`` logging channel used in the Security component:

.. configuration-block::

    .. code-block:: yaml

        services:
            Monolog\Processor\IntrospectionProcessor:
                tags:
                    - { name: monolog.processor, channel: security }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Monolog\Processor\IntrospectionProcessor">
                    <tag name="monolog.processor" channel="security"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use Monolog\Processor\IntrospectionProcessor;

        $container
            ->register(IntrospectionProcessor::class)
            ->addTag('monolog.processor', ['channel' => 'security'])
        ;

.. note::

    You cannot use both the ``handler`` and ``channel`` attributes for the
    same tag as handlers are shared between all channels.

routing.loader
--------------

**Purpose**: Register a custom service that loads routes

To enable a custom routing loader, add it as a regular service in one
of your configuration and tag it with ``routing.loader``:

.. configuration-block::

    .. code-block:: yaml

        services:
            App\Routing\CustomLoader:
                tags: [routing.loader]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Routing\CustomLoader">
                    <tag name="routing.loader"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use App\Routing\CustomLoader;

        $container
            ->register(CustomLoader::class)
            ->addTag('routing.loader')
        ;

For more information, see :doc:`/routing/custom_route_loader`.

routing.expression_language_provider
------------------------------------

**Purpose**: Register a provider for expression language functions in routing

This tag is used to automatically register
:ref:`expression function providers <components-expression-language-provider>`
for the routing expression component. Using these providers, you can add custom
functions to the routing expression language.

security.expression_language_provider
-------------------------------------

**Purpose**: Register a provider for expression language functions in security

This tag is used to automatically register :ref:`expression function providers
<components-expression-language-provider>` for the security expression
component. Using these providers, you can add custom functions to the security
expression language.

security.remember_me_aware
--------------------------

**Purpose**: To allow remember me authentication

This tag is used internally to allow remember-me authentication to work.
If you have a custom authentication method where a user can be remember-me
authenticated, then you may need to use this tag.

If your custom authentication factory extends
:class:`Symfony\\Bundle\\SecurityBundle\\DependencyInjection\\Security\\Factory\\AbstractFactory`
and your custom authentication listener extends
:class:`Symfony\\Component\\Security\\Http\\Firewall\\AbstractAuthenticationListener`,
then your custom authentication listener will automatically have this tag
applied and it will function automatically.

security.voter
--------------

**Purpose**: To add a custom voter to Symfony's authorization logic

When you call ``isGranted()`` on Symfony's authorization checker, a system of "voters"
is used behind the scenes to determine if the user should have access. The
``security.voter`` tag allows you to add your own custom voter to that system.

For more information, read the :doc:`/security/voters` article.

.. _reference-dic-tags-serializer-encoder:

serializer.encoder
------------------

**Purpose**: Register a new encoder in the ``serializer`` service

The class that's tagged should implement the :class:`Symfony\\Component\\Serializer\\Encoder\\EncoderInterface`
and :class:`Symfony\\Component\\Serializer\\Encoder\\DecoderInterface`.

For more details, see :doc:`/serializer`.

.. _reference-dic-tags-serializer-normalizer:

serializer.normalizer
---------------------

**Purpose**: Register a new normalizer in the Serializer service

The class that's tagged should implement the :class:`Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface`
and :class:`Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface`.

For more details, see :doc:`/serializer`.

The priorities of the default normalizers can be found in the
:method:`Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\FrameworkExtension::registerSerializerConfiguration`
method.

swiftmailer.default.plugin
--------------------------

**Purpose**: Register a custom SwiftMailer Plugin

If you're using a custom SwiftMailer plugin (or want to create one), you
can register it with SwiftMailer by creating a service for your plugin and
tagging it with ``swiftmailer.default.plugin`` (it has no options).

.. note::

    ``default`` in this tag is the name of the mailer. If you have multiple
    mailers configured or have changed the default mailer name for some
    reason, you should change it to the name of your mailer in order to
    use this tag.

A SwiftMailer plugin must implement the ``Swift_Events_EventListener`` interface.
For more information on plugins, see `SwiftMailer's Plugin Documentation`_.

Several SwiftMailer plugins are core to Symfony and can be activated via
different configuration. For details, see :doc:`/reference/configuration/swiftmailer`.

.. _dic-tags-translation-loader:

translation.loader
------------------

**Purpose**: To register a custom service that loads translations

By default, translations are loaded from the filesystem in a variety of
different formats (YAML, XLIFF, PHP, etc).

Now, register your loader as a service and tag it with ``translation.loader``:

.. configuration-block::

    .. code-block:: yaml

        services:
            App\Translation\MyCustomLoader:
                tags:
                    - { name: translation.loader, alias: bin }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Translation\MyCustomLoader">
                    <tag name="translation.loader" alias="bin"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use App\Translation\MyCustomLoader;

        $container
            ->register(MyCustomLoader::class)
            ->addTag('translation.loader', ['alias' => 'bin'])
        ;

The ``alias`` option is required and very important: it defines the file
"suffix" that will be used for the resource files that use this loader.
For example, suppose you have some custom ``bin`` format that you need to
load. If you have a ``bin`` file that contains French translations for
the ``messages`` domain, then you might have a file ``translations/messages.fr.bin``.

When Symfony tries to load the ``bin`` file, it passes the path to your
custom loader as the ``$resource`` argument. You can then perform any logic
you need on that file in order to load your translations.

If you're loading translations from a database, you'll still need a resource
file, but it might either be blank or contain a little bit of information
about loading those resources from the database. The file is key to trigger
the ``load()`` method on your custom loader.

.. _reference-dic-tags-translation-extractor:

translation.extractor
---------------------

**Purpose**: To register a custom service that extracts messages from a
file

When executing the ``translation:update`` command, it uses extractors to
extract translation messages from a file. By default, the Symfony Framework
has a :class:`Symfony\\Bridge\\Twig\\Translation\\TwigExtractor` and a
:class:`Symfony\\Component\\Translation\\Extractor\\PhpExtractor`, which
help to find and extract translation keys from Twig templates and PHP files.

You can create your own extractor by creating a class that implements
:class:`Symfony\\Component\\Translation\\Extractor\\ExtractorInterface`
and tagging the service with ``translation.extractor``. The tag has one
required option: ``alias``, which defines the name of the extractor::

    // src/Acme/DemoBundle/Translation/FooExtractor.php
    namespace Acme\DemoBundle\Translation;

    use Symfony\Component\Translation\Extractor\ExtractorInterface;
    use Symfony\Component\Translation\MessageCatalogue;

    class FooExtractor implements ExtractorInterface
    {
        protected $prefix;

        /**
         * Extracts translation messages from a template directory to the catalog.
         */
        public function extract($directory, MessageCatalogue $catalog)
        {
            // ...
        }

        /**
         * Sets the prefix that should be used for new found messages.
         */
        public function setPrefix($prefix)
        {
            $this->prefix = $prefix;
        }
    }

.. configuration-block::

    .. code-block:: yaml

        services:
            App\Translation\CustomExtractor:
                tags:
                    - { name: translation.extractor, alias: foo }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Translation\CustomExtractor">
                    <tag name="translation.extractor" alias="foo"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use App\Translation\CustomExtractor;

        $container->register(CustomExtractor::class)
            ->addTag('translation.extractor', ['alias' => 'foo']);

translation.dumper
------------------

**Purpose**: To register a custom service that dumps messages to a file

After a :ref:`translation extractor <reference-dic-tags-translation-extractor>`
has extracted all messages from the templates, the dumpers are executed to dump
the messages to a translation file in a specific format.

Symfony already comes with many dumpers:

* :class:`Symfony\\Component\\Translation\\Dumper\\CsvFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\IcuResFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\IniFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\MoFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\PoFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\QtFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\XliffFileDumper`
* :class:`Symfony\\Component\\Translation\\Dumper\\YamlFileDumper`

You can create your own dumper by extending
:class:`Symfony\\Component\\Translation\\Dumper\\FileDumper` or implementing
:class:`Symfony\\Component\\Translation\\Dumper\\DumperInterface` and tagging
the service with ``translation.dumper``. The tag has one option: ``alias``
This is the name that's used to determine which dumper should be used.

.. configuration-block::

    .. code-block:: yaml

        services:
            App\Translation\JsonFileDumper:
                tags:
                    - { name: translation.dumper, alias: json }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Translation\JsonFileDumper">
                    <tag name="translation.dumper" alias="json"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use App\Translation\JsonFileDumper;

        $container->register(JsonFileDumper::class)
            ->addTag('translation.dumper', ['alias' => 'json']);

.. _reference-dic-tags-twig-extension:

twig.extension
--------------

**Purpose**: To register a custom Twig Extension

To enable a Twig extension, add it as a regular service in one of your
configuration and tag it with ``twig.extension``. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
the service is auto-registered and auto-tagged. But, you can also register it manually:

.. configuration-block::

    .. code-block:: yaml

        services:
            App\Twig\AppExtension:
                tags: [twig.extension]

            # optionally you can define the priority of the extension (default = 0).
            # Extensions with higher priorities are registered earlier. This is mostly
            # useful to register late extensions that override other extensions.
            App\Twig\AnotherExtension:
                tags: [{ name: twig.extension, priority: -100 }]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Twig\AppExtension">
                    <tag name="twig.extension"/>
                </service>

                <service id="App\Twig\AnotherExtension">
                    <tag name="twig.extension" priority="-100"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use App\Twig\AnotherExtension;
        use App\Twig\AppExtension;

        $container
            ->register(AppExtension::class)
            ->addTag('twig.extension')
        ;
        $container
            ->register(AnotherExtension::class)
            ->addTag('twig.extension', ['priority' => -100])
        ;

For information on how to create the actual Twig Extension class, see
`Twig's documentation`_ on the topic or read the
:doc:`/templating/twig_extension` article.

twig.loader
-----------

**Purpose**: Register a custom service that loads Twig templates

By default, Symfony uses only one `Twig Loader`_ -
:class:`Symfony\\Bundle\\TwigBundle\\Loader\\FilesystemLoader`. If you need
to load Twig templates from another resource, you can create a service for
the new loader and tag it with ``twig.loader``.

If you use the :ref:`default services.yaml configuration <service-container-services-load-example>`,
the service will be automatically tagged thanks to autoconfiguration. But, you can
also register it manually:

.. configuration-block::

    .. code-block:: yaml

        services:
            App\Twig\CustomLoader:
                tags:
                    - { name: twig.loader, priority: 0 }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Twig\CustomLoader">
                    <tag name="twig.loader" priority="0"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use App\Twig\CustomLoader;

        $container
            ->register(CustomLoader::class)
            ->addTag('twig.loader', ['priority' => 0])
        ;

.. note::

    The ``priority`` is optional and its value is a positive or negative integer
    that defaults to ``0``. Loaders with higher numbers are tried first.

.. _reference-dic-tags-twig-runtime:

twig.runtime
------------

**Purpose**: To register a custom Lazy-Loaded Twig Extension

:ref:`Lazy-Loaded Twig Extensions <lazy-loaded-twig-extensions>` are defined as
regular services but the need to be tagged with ``twig.runtime``. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
the service is auto-registered and auto-tagged. But, you can also register it manually:

.. configuration-block::

    .. code-block:: yaml

        services:
            App\Twig\AppExtension:
                tags: [twig.runtime]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Twig\AppExtension">
                    <tag name="twig.runtime"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use App\Twig\AppExtension;

        $container
            ->register(AppExtension::class)
            ->addTag('twig.runtime')
        ;

validator.constraint_validator
------------------------------

**Purpose**: Create your own custom validation constraint

This tag allows you to create and register your own custom validation constraint.
For more information, read the :doc:`/validation/custom_constraint` article.

validator.initializer
---------------------

**Purpose**: Register a service that initializes objects before validation

This tag provides a very uncommon piece of functionality that allows you
to perform some sort of action on an object right before it's validated.
For example, it's used by Doctrine to query for all of the lazily-loaded
data on an object before it's validated. Without this, some data on a Doctrine
entity would appear to be "missing" when validated, even though this is
not really the case.

If you do need to use this tag, just make a new class that implements the
:class:`Symfony\\Component\\Validator\\ObjectInitializerInterface` interface.
Then, tag it with the ``validator.initializer`` tag (it has no options).

For an example, see the ``DoctrineInitializer`` class inside the Doctrine
Bridge.

.. _`Twig's documentation`: https://twig.symfony.com/doc/2.x/advanced.html#creating-an-extension
.. _`SwiftMailer's Plugin Documentation`: https://swiftmailer.symfony.com/docs/plugins.html
.. _`Twig Loader`: https://twig.symfony.com/doc/2.x/api.html#loaders
