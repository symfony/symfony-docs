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
`assetic.asset`_                          Register an asset to the current asset manager
`assetic.factory_worker`_                 Add a factory worker
`assetic.filter`_                         Register a filter
`assetic.formula_loader`_                 Add a formula loader to the current asset manager
`assetic.formula_resource`_               Adds a resource to the current asset manager
`assetic.templating.php`_                 Remove this service if PHP templating is disabled
`assetic.templating.twig`_                Remove this service if Twig templating is disabled
`auto_alias`_                             Define aliases based on the value of container parameters
`console.command`_                        Add a command
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
`templating.helper`_                      Make your service available in PHP templates
`translation.loader`_                     Register a custom service that loads translations
`translation.extractor`_                  Register a custom service that extracts translation messages from a file
`translation.dumper`_                     Register a custom service that dumps translation messages
`twig.extension`_                         Register a custom Twig Extension
`twig.loader`_                            Register a custom service that loads Twig templates
`validator.constraint_validator`_         Create your own custom validation constraint
`validator.initializer`_                  Register a service that initializes objects before validation
========================================  ========================================================================

assetic.asset
-------------

**Purpose**: Register an asset with the current asset manager

assetic.factory_worker
----------------------

**Purpose**: Add a factory worker

A Factory worker is a class implementing ``Assetic\Factory\Worker\WorkerInterface``.
Its ``process($asset)`` method is called for each asset after asset creation.
You can modify an asset or even return a new one.

In order to add a new worker, first create a class::

    use Assetic\Asset\AssetInterface;
    use Assetic\Factory\Worker\WorkerInterface;

    class MyWorker implements WorkerInterface
    {
        public function process(AssetInterface $asset)
        {
            // ... change $asset or return a new one
        }

    }

And then register it as a tagged service:

.. configuration-block::

    .. code-block:: yaml

        services:
            AppBundle\Assetic\CustomWorker:
                tags: [assetic.factory_worker]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Assetic\CustomWorker">
                    <tag name="assetic.factory_worker" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Assetic\CustomWorker;

        $container
            ->register(CustomWorker::class)
            ->addTag('assetic.factory_worker')
        ;

assetic.filter
--------------

**Purpose**: Register a filter

AsseticBundle uses this tag to register common filters. You can also use
this tag to register your own filters.

First, you need to create a filter::

    use Assetic\Asset\AssetInterface;
    use Assetic\Filter\FilterInterface;

    class MyFilter implements FilterInterface
    {
        public function filterLoad(AssetInterface $asset)
        {
            $asset->setContent('alert("yo");' . $asset->getContent());
        }

        public function filterDump(AssetInterface $asset)
        {
            // ...
        }
    }

Second, define a service:

.. configuration-block::

    .. code-block:: yaml

        services:
            AppBundle\Assetic\CustomFilter:
                tags:
                    - { name: assetic.filter, alias: my_filter }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Assetic\CustomFilter">
                    <tag name="assetic.filter" alias="my_filter" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Assetic\CustomFilter;

        $container
            ->register(CustomFilter::class)
            ->addTag('assetic.filter', array('alias' => 'my_filter'))
        ;

Finally, apply the filter:

.. code-block:: twig

    {% javascripts
        '@AcmeBaseBundle/Resources/public/js/global.js'
        filter='my_filter'
    %}
        <script src="{{ asset_url }}"></script>
    {% endjavascripts %}

You can also apply your filter via the ``assetic.filters.my_filter.apply_to``
config option as it's described here: :doc:`/frontend/assetic/apply_to_option`.
In order to do that, you must define your filter service in a separate xml
config file and point to this file's path via the ``assetic.filters.my_filter.resource``
configuration key.

assetic.formula_loader
----------------------

**Purpose**: Add a formula loader to the current asset manager

A Formula loader is a class implementing
``Assetic\\Factory\Loader\\FormulaLoaderInterface`` interface. This class
is responsible for loading assets from a particular kind of resources (for
instance, twig template). Assetic ships loaders for PHP and Twig templates.

An ``alias`` attribute defines the name of the loader.

assetic.formula_resource
------------------------

**Purpose**: Adds a resource to the current asset manager

A resource is something formulae can be loaded from. For instance, Twig
templates are resources.

assetic.templating.php
----------------------

**Purpose**: Remove this service if PHP templating is disabled

The tagged service will be removed from the container if the
``framework.templating.engines`` config section does not contain php.

assetic.templating.twig
-----------------------

**Purpose**: Remove this service if Twig templating is disabled

The tagged service will be removed from the container if
``framework.templating.engines`` config section does not contain ``twig``.

auto_alias
----------

**Purpose**: Define aliases based on the value of container parameters

Consider the following configuration that defines three different but related
services:

.. configuration-block::

    .. code-block:: yaml

        services:
            app.mysql_lock:
                class: AppBundle\Lock\MysqlLock
                public: false
            app.postgresql_lock:
                class: AppBundle\Lock\PostgresqlLock
                public: false
            app.sqlite_lock:
                class: AppBundle\Lock\SqliteLock
                public: false

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.mysql_lock" public="false"
                         class="AppBundle\Lock\MysqlLock" />
                <service id="app.postgresql_lock" public="false"
                         class="AppBundle\Lock\PostgresqlLock" />
                <service id="app.sqlite_lock" public="false"
                         class="AppBundle\Lock\SqliteLock" />
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Lock\MysqlLock;
        use AppBundle\Lock\PostgresqlLock;
        use AppBundle\Lock\SqliteLock;

        $container->register('app.mysql_lock', MysqlLock::class)->setPublic(false);
        $container->register('app.postgresql_lock', PostgresqlLock::class)->setPublic(false);
        $container->register('app.sqlite_lock', SqliteLock::class)->setPublic(false);

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
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.mysql_lock" public="false"
                         class="AppBundle\Lock\MysqlLock" />
                <service id="app.postgresql_lock" public="false"
                         class="AppBundle\Lock\PostgresqlLock" />
                <service id="app.sqlite_lock" public="false"
                         class="AppBundle\Lock\SqliteLock" />

                <service id="app.lock">
                    <tag name="auto_alias" format="app.%database_type%_lock" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Lock\MysqlLock;
        use AppBundle\Lock\PostgresqlLock;
        use AppBundle\Lock\SqliteLock;

        $container->register('app.mysql_lock', MysqlLock::class)->setPublic(false);
        $container->register('app.postgresql_lock', PostgresqlLock::class)->setPublic(false);
        $container->register('app.sqlite_lock', SqliteLock::class)->setPublic(false);

        $container->register('app.lock')
            ->addTag('auto_alias', array('format' => 'app.%database_type%_lock'));

The ``format`` option defines the expression used to construct the name of the service
to alias. This expression can use any container parameter (as usual,
wrapping their names with ``%`` characters).

.. note::

    When using the ``auto_alias`` tag, it's not mandatory to define the aliased
    services as private. However, doing that (like in the above example) makes
    sense most of the times to prevent accessing those services directly instead
    of using the generic service alias.

.. note::

    You need to manually add the ``Symfony\Component\DependencyInjection\Compiler\AutoAliasServicePass``
    compiler pass to the container for this feature to work.

console.command
---------------

**Purpose**: Add a command to the application

For details on registering your own commands in the service container, read
:doc:`/console/commands_as_services`.

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
:doc:`/doctrine/event_listeners_subscribers` article.

doctrine.event_subscriber
-------------------------

**Purpose**: Add a Doctrine event subscriber

For details on creating Doctrine event subscribers, read the
:doc:`/doctrine/event_listeners_subscribers` article.

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

This tag allows you to add your own logic to the :ref:`form guessing <forms-field-guessing>`
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

    // src/AppBundle/Cache/MyClearer.php
    namespace AppBundle\Cache;

    use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

    class MyClearer implements CacheClearerInterface
    {
        public function clear($cacheDirectory)
        {
            // clear your cache
        }
    }

If you're using the :ref:`default services.yml configuration <service-container-services-load-example>`,
your service will be automatically tagged with ``kernel.cache_clearer``. But, you
can also register it manually:

.. configuration-block::

    .. code-block:: yaml

        services:
            AppBundle\Cache\MyClearer:
                tags: [kernel.cache_clearer]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Cache\MyClearer">
                    <tag name="kernel.cache_clearer" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Cache\MyClearer;

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

    // src/Acme/MainBundle/Cache/MyCustomWarmer.php
    namespace AppBundle\Cache;

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

If you're using the :ref:`default services.yml configuration <service-container-services-load-example>`,
your service will be automatically tagged with ``kernel.cache_warmer``. But, you
can also register it manually:

.. configuration-block::

    .. code-block:: yaml

        services:
            AppBundle\Cache\MyCustomWarmer:
                tags:
                    - { name: kernel.cache_warmer, priority: 0 }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Cache\MyCustomWarmer">
                    <tag name="kernel.cache_warmer" priority="0" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Cache\MyCustomWarmer;

        $container
            ->register(MyCustomWarmer::class)
            ->addTag('kernel.cache_warmer', array('priority' => 0))
        ;

.. note::

    The ``priority`` value is optional and defaults to 0. The higher the
    priority, the sooner it gets executed.

.. caution::

    If your cache warmer fails its execution because of any exception, Symfony
    won't try to execute it again for the next requests. Therefore, your
    application and/or bundles should be prepared for when the contents
    generated by the cache warmer are not available.

Core Cache Warmers
~~~~~~~~~~~~~~~~~~

+-------------------------------------------------------------------------------------------+-----------+
| Cache Warmer Class Name                                                                   | Priority  |
+===========================================================================================+===========+
| :class:`Symfony\\Bundle\\FrameworkBundle\\CacheWarmer\\TemplatePathsCacheWarmer`          | 20        |
+-------------------------------------------------------------------------------------------+-----------+
| :class:`Symfony\\Bundle\\FrameworkBundle\\CacheWarmer\\RouterCacheWarmer`                 | 0         |
+-------------------------------------------------------------------------------------------+-----------+
| :class:`Symfony\\Bundle\\TwigBundle\\CacheWarmer\\TemplateCacheCacheWarmer`               | 0         |
+-------------------------------------------------------------------------------------------+-----------+

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
            AppBundle\Log\CustomLogger:
                arguments: ['@logger']
                tags:
                    - { name: monolog.logger, channel: app }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Log\CustomLogger">
                    <argument type="service" id="logger" />
                    <tag name="monolog.logger" channel="app" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Log\CustomLogger;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register(CustomLogger::class)
            ->addArgument(new Reference('logger'))
            ->addTag('monolog.logger', array('channel' => 'app'));

.. tip::

    You can also configure custom channels in the configuration and retrieve
    the corresponding logger service from the service container directly (see
    :ref:`monolog-channels-config`).

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
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Monolog\Processor\IntrospectionProcessor">
                    <tag name="monolog.processor" />
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
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Monolog\Processor\IntrospectionProcessor">
                    <tag name="monolog.processor" handler="firephp" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use Monolog\Processor\IntrospectionProcessor;

        $container
            ->register(IntrospectionProcessor::class)
            ->addTag('monolog.processor', array('handler' => 'firephp'))
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
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Monolog\Processor\IntrospectionProcessor">
                    <tag name="monolog.processor" channel="security" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use Monolog\Processor\IntrospectionProcessor;

        $container
            ->register(IntrospectionProcessor::class)
            ->addTag('monolog.processor', array('channel' => 'security'))
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
            AppBundle\Routing\CustomLoader:
                tags: [routing.loader]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Routing\CustomLoader">
                    <tag name="routing.loader" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Routing\CustomLoader;

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
then your custom authentication listener will automatically have this tagged
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

templating.helper
-----------------

**Purpose**: Make your service available in PHP templates

To enable a custom template helper, add it as a regular service in one
of your configuration, tag it with ``templating.helper`` and define an
``alias`` attribute (the helper will be accessible via this alias in the
templates):

.. configuration-block::

    .. code-block:: yaml

        services:
            AppBundle\Templating\AppHelper:
                tags:
                    - { name: templating.helper, alias: alias_name }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Templating\AppHelper">
                    <tag name="templating.helper" alias="alias_name" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Templating\AppHelper;

        $container->register(AppHelper::class)
            ->addTag('templating.helper', array('alias' => 'alias_name'))
        ;

.. _dic-tags-translation-loader:

translation.loader
------------------

**Purpose**: To register a custom service that loads translations

By default, translations are loaded from the filesystem in a variety of
different formats (YAML, XLIFF, PHP, etc).

.. seealso::

    Learn how to :ref:`load custom formats <components-translation-custom-loader>`
    in the components section.

Now, register your loader as a service and tag it with ``translation.loader``:

.. configuration-block::

    .. code-block:: yaml

        services:
            AppBundle\Translation\MyCustomLoader:
                tags:
                    - { name: translation.loader, alias: bin }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Translation\MyCustomLoader">
                    <tag name="translation.loader" alias="bin" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Translation\MyCustomLoader;

        $container
            ->register(MyCustomLoader::class)
            ->addTag('translation.loader', array('alias' => 'bin'))
        ;

The ``alias`` option is required and very important: it defines the file
"suffix" that will be used for the resource files that use this loader.
For example, suppose you have some custom ``bin`` format that you need to
load. If you have a ``bin`` file that contains French translations for
the ``messages`` domain, then you might have a file
``app/Resources/translations/messages.fr.bin``.

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
:class:`Symfony\\Bundle\\FrameworkBundle\\Translation\\PhpExtractor`, which
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
         * Extracts translation messages from a template directory to the catalogue.
         */
        public function extract($directory, MessageCatalogue $catalogue)
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
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Translation\CustomExtractor">
                    <tag name="translation.extractor" alias="foo" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Translation\CustomExtractor;

        $container->register(CustomExtractor::class)
            ->addTag('translation.extractor', array('alias' => 'foo'));

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
            AppBundle\Translation\JsonFileDumper:
                tags:
                    - { name: translation.dumper, alias: json }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Translation\JsonFileDumper">
                    <tag name="translation.dumper" alias="json" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Translation\JsonFileDumper;

        $container->register(JsonFileDumper::class)
            ->addTag('translation.dumper', array('alias' => 'json'));

.. seealso::

    Learn how to :ref:`dump to custom formats <components-translation-custom-dumper>`
    in the components section.

.. _reference-dic-tags-twig-extension:

twig.extension
--------------

**Purpose**: To register a custom Twig Extension

To enable a Twig extension, add it as a regular service in one of your
configuration and tag it with ``twig.extension``. If you're using the
:ref:`default services.yml configuration <service-container-services-load-example>`,
the service is auto-registered and auto-tagged. But, you can also register it manually:

.. configuration-block::

    .. code-block:: yaml

        services:
            AppBundle\Twig\AppExtension:
                tags: [twig.extension]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Twig\AppExtension">
                    <tag name="twig.extension" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Twig\AppExtension;

        $container
            ->register(AppExtension::class)
            ->addTag('twig.extension')
        ;

For information on how to create the actual Twig Extension class, see
`Twig's documentation`_ on the topic or read the
:doc:`/templating/twig_extension` article.

Before writing your own extensions, have a look at the
`Twig official extension repository`_ which already includes several
useful extensions. For example ``Intl`` and its ``localizeddate`` filter
that formats a date according to user's locale. These official Twig extensions
also have to be added as regular services:

.. configuration-block::

    .. code-block:: yaml

        services:
            Twig_Extensions_Extension_Intl:
                tags: [twig.extension]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Twig_Extensions_Extension_Intl">
                    <tag name="twig.extension" />
                </service>
            </services>
        </container>

    .. code-block:: php

        $container
            ->register('Twig_Extensions_Extension_Intl')
            ->addTag('twig.extension')
        ;

twig.loader
-----------

**Purpose**: Register a custom service that loads Twig templates

By default, Symfony uses only one `Twig Loader`_ -
:class:`Symfony\\Bundle\\TwigBundle\\Loader\\FilesystemLoader`. If you need
to load Twig templates from another resource, you can create a service for
the new loader and tag it with ``twig.loader``.

If you use the :ref:`default services.yml configuration <service-container-services-load-example>`,
the service will be automatically tagged thanks to autoconfiguration. But, you can
also register it manually:

.. configuration-block::

    .. code-block:: yaml

        services:
            AppBundle\Twig\CustomLoader:
                tags:
                    - { name: twig.loader, priority: 0 }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Twig\CustomLoader">
                    <tag name="twig.loader" priority="0" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Twig\CustomLoader;

        $container
            ->register(CustomLoader::class)
            ->addTag('twig.loader', array('priority' => 0))
        ;

.. note::

    The ``priority`` value is optional and defaults to ``0``.
    The higher priority loaders are tried first.

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

.. _`Twig's documentation`: http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
.. _`Twig official extension repository`: https://github.com/fabpot/Twig-extensions
.. _`KernelEvents`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpKernel/KernelEvents.php
.. _`SwiftMailer's Plugin Documentation`: http://swiftmailer.org/docs/plugins.html
.. _`Twig Loader`: http://twig.sensiolabs.org/doc/api.html#loaders
