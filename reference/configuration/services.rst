Service Container Configuration Reference (DependencyInjection)
===============================================================

The :doc:`service container </service_container>` is configured under the
``services`` key of any of the configuration files loaded by the application
(typically ``config/services.yaml``). This reference describes all the keys
available to define and configure services in YAML, XML and PHP.

.. note::

    When using XML, you must use the ``http://symfony.com/schema/dic/services``
    namespace and the related XSD schema is available at:
    ``https://symfony.com/schema/dic/services/services-1.0.xsd``

.. deprecated:: 7.4

    The XML format to configure services was deprecated in Symfony 7.4.

A ``services`` section is made of four kinds of entries:

* `_defaults`_: values applied by default to every service defined in the
  same file;
* `_instanceof`_: values applied to every service defined in the same file
  whose class matches a given type (class or interface);
* :ref:`Service definitions <reference-services-definitions>`: each entry
  defines or overrides a service identified by its key (the *service ID*);
* :ref:`Prototypes <reference-services-prototype>`: entries with a
  ``resource`` key that register as services all the classes found in a
  directory.

The configuration file may also contain other top-level keys that are
siblings of ``services``: :ref:`imports <reference-services-imports>`,
:ref:`parameters <service-container-parameters>` and
:ref:`environment-specific configuration <reference-services-when-env>`.

This article only documents the configuration keys themselves. For detailed
explanations and examples, follow the links to the related articles.

.. _reference-services-defaults:

``_defaults``
~~~~~~~~~~~~~

The ``_defaults`` key stores the values that are applied by default to every
service defined in the same file. Each service can override any of these
values:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                autowire: true
                autoconfigure: true
                public: false

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <defaults autowire="true" autoconfigure="true" public="false"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

        return function (ContainerConfigurator $container): void {
            $services = $container->services()
                ->defaults()
                    ->autowire(true)
                    ->autoconfigure(true)
                    ->public(false)
            ;
        };

The keys allowed inside ``_defaults`` are:
:ref:`autoconfigure <reference-services-defaults-autoconfigure>`,
:ref:`autowire <reference-services-defaults-autowire>`,
:ref:`bind <reference-services-defaults-bind>`,
:ref:`public <reference-services-defaults-public>`,
:ref:`resource_tags <reference-services-defaults-resource-tags>` and
:ref:`tags <reference-services-defaults-tags>`.

.. _reference-services-defaults-autoconfigure:

autoconfigure
.............

**type**: ``boolean`` **default**: ``false``

When set to ``true``, Symfony applies to every service the configuration
registered for its class (for example, the tags added to all the classes that
implement a given interface or that use a given PHP attribute). Read more in
:ref:`services-autoconfigure` and :doc:`/service_container/tags`.

.. _reference-services-defaults-autowire:

autowire
........

**type**: ``boolean`` **default**: ``false``

When set to ``true``, Symfony guesses the constructor and method arguments of
the services based on their type-hints. Read more in
:doc:`/service_container/autowiring`.

.. _reference-services-defaults-bind:

bind
....

**type**: ``array`` **default**: ``[]``

Defines the values that Symfony injects in the arguments of the services. Keys
can be argument names (``$variableName``), types (``Some\Class\Name``) or both
(``Some\Class\Name $variableName``). These bindings are merged with the
per-service :ref:`bind <reference-services-bind>` option. Read more in
:ref:`services-binding`.

.. _reference-services-defaults-public:

public
......

**type**: ``boolean`` **default**: ``false``

If ``true``, services defined in the file can be retrieved directly from the
container with ``$container->get()``. If ``false`` (the recommended value),
they can only be injected as dependencies. Read more in
:ref:`container-public`.

.. _reference-services-defaults-resource-tags:

resource_tags
.............

**type**: ``array`` **default**: ``[]``

A list of :ref:`resource tags <di-resource-tags>` to apply to every class
defined in the file. It uses the same syntax as the
:ref:`tags <reference-services-defaults-tags>` option.

.. versionadded:: 7.4

    The ``resource_tags`` option was introduced in Symfony 7.4.

.. _reference-services-defaults-tags:

tags
....

**type**: ``array`` **default**: ``[]``

A list of :doc:`tags </service_container/tags>` to apply to every service
defined in the file. Each tag can be a string (the tag name) or an array with
a ``name`` key and any number of attributes:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                tags:
                    - 'app.custom_tag'
                    - { name: 'app.another_tag', priority: 100 }

    .. code-block:: xml

        <!-- config/services.xml -->
        <services>
            <defaults>
                <tag name="app.custom_tag"/>
                <tag name="app.another_tag" priority="100"/>
            </defaults>
        </services>

    .. code-block:: php

        // config/services.php
        $services->defaults()
            ->tag('app.custom_tag')
            ->tag('app.another_tag', ['priority' => 100])
        ;

.. _reference-services-instanceof:

``_instanceof``
~~~~~~~~~~~~~~~

The ``_instanceof`` key applies some configuration to every service defined in
the same file whose class extends or implements a given type:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _instanceof:
                App\Domain\LoaderInterface:
                    public: true
                    tags: ['app.loader']

    .. code-block:: xml

        <!-- config/services.xml -->
        <services>
            <instanceof id="App\Domain\LoaderInterface" public="true">
                <tag name="app.loader"/>
            </instanceof>
        </services>

    .. code-block:: php

        // config/services.php
        $services->instanceof(\App\Domain\LoaderInterface::class)
            ->public()
            ->tag('app.loader')
        ;

The keys allowed inside ``_instanceof`` are a subset of the per-service keys:
:ref:`autowire <reference-services-autowire>`,
:ref:`bind <reference-services-bind>`,
`calls`_,
`configurator`_,
`constructor`_,
`lazy`_,
`properties`_,
:ref:`public <reference-services-public>`,
:ref:`resource_tags <reference-services-resource-tags>`,
`shared`_ and
:ref:`tags <reference-services-tags>`.

Read more in :ref:`di-instanceof`.

.. _reference-services-definitions:

Service Definitions
~~~~~~~~~~~~~~~~~~~

Each entry under ``services`` whose key is not ``_defaults`` or
``_instanceof`` defines (or overrides) a service. Its key is the *service ID*,
which is the identifier used to reference the service from other definitions
or to retrieve it from the container.

In YAML, the entry value can also be ``~`` (the service only uses the default
configuration) or a string starting with ``@`` (the service is an
:ref:`alias <reference-services-alias>` of that other service).

.. _reference-services-abstract:

abstract
........

**type**: ``boolean`` **default**: ``false``

When ``true``, the service can't be retrieved from the container; it only
serves as a template for child services (see `parent`_). Read more in
:ref:`parent-services`.

.. _reference-services-alias:

alias
.....

**type**: ``string`` **default**: ``null``

When set, the service is an alias of another service, whose ID is given as
the value. When defining an alias, the only other keys allowed are
:ref:`public <reference-services-public>` and `deprecated`_. Read more in
:ref:`services-alias`.

.. _reference-services-arguments:

arguments
.........

**type**: ``array`` **default**: ``[]``

The arguments passed to the constructor (or to the method defined by
`factory`_). Arguments can be scalars, :ref:`parameters
<service-container-parameters>` (``%kernel.cache_dir%``), service references
(``@service.id``) or any of the special argument types described in
:doc:`/service_container/injection_types`.

.. _reference-services-autoconfigure:

autoconfigure
.............

**type**: ``boolean`` **default**: ``false``

Overrides the ``autoconfigure`` value set in
:ref:`_defaults <reference-services-defaults-autoconfigure>` for this service.

.. _reference-services-autowire:

autowire
........

**type**: ``boolean`` **default**: ``false``

Overrides the ``autowire`` value set in
:ref:`_defaults <reference-services-defaults-autowire>` for this service.

.. _reference-services-bind:

bind
....

**type**: ``array`` **default**: ``[]``

Argument bindings of this service, using the same syntax as the
:ref:`_defaults bind <reference-services-defaults-bind>` option. They are
merged with the bindings defined in ``_defaults``; when both define the same
key, the value of the service wins. Read more in :ref:`services-binding`.

.. _reference-services-calls:

calls
.....

**type**: ``array`` **default**: ``[]``

A list of methods called on the service after creating it (this is called
*setter injection*). Each item defines the method to call, its arguments and,
optionally, whether the method returns a new instance of the service instead
of modifying it (``!returns_clone`` in YAML):

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\MessageGenerator:
                calls:
                    - setLogger: ['@logger']
                    - withDispatcher: !returns_clone ['@event_dispatcher']

    .. code-block:: xml

        <!-- config/services.xml -->
        <service id="App\Service\MessageGenerator">
            <call method="setLogger">
                <argument type="service" id="logger"/>
            </call>
            <call method="withDispatcher" returns-clone="true">
                <argument type="service" id="event_dispatcher"/>
            </call>
        </service>

    .. code-block:: php

        // config/services.php
        $services->set(\App\Service\MessageGenerator::class)
            ->call('setLogger', [service('logger')])
            // the third argument tells whether the method returns a clone
            ->call('withDispatcher', [service('event_dispatcher')], true)
        ;

Read more in :ref:`injection-types-setter`.

.. _reference-services-class:

class
.....

**type**: ``string`` **default**: ``null``

The fully-qualified class name of the service. When omitted, Symfony uses the
service ID as the class name, so you only need this option when the service ID
is not a class name.

.. deprecated:: 7.4

    Omitting ``class`` when the service ID looks like a class name but no such
    class or interface exists was deprecated in Symfony 7.4.

.. _reference-services-configurator:

configurator
............

**type**: ``string`` | ``array`` **default**: ``null``

A callable executed after the service is created and its arguments,
properties and method calls are set. It receives the service as its argument
and applies some last-minute configuration to it:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\NewsletterManager:
                configurator: ['@App\Service\NewsletterConfigurator', 'configure']

    .. code-block:: xml

        <!-- config/services.xml -->
        <service id="App\Service\NewsletterManager">
            <configurator service="App\Service\NewsletterConfigurator"
                method="configure"/>
        </service>

    .. code-block:: php

        // config/services.php
        $services->set(\App\Service\NewsletterManager::class)
            ->configurator([
                service(\App\Service\NewsletterConfigurator::class),
                'configure',
            ])
        ;

Read more in :ref:`service-configurators`.

.. _reference-services-constructor:

constructor
...........

**type**: ``string`` **default**: ``null``

The name of a static method of the service class used to create the service
instead of the ``__construct()`` method. It can't be combined with
`factory`_.

.. _reference-services-decorates:

decorates
.........

**type**: ``string`` **default**: ``null``

The ID of the service decorated by this service. The decorating service
replaces the decorated one in the container, and the original service remains
available under another ID (see `decoration_inner_name`_). Read more in
:doc:`/service_container/decoration`.

.. _reference-services-decoration-inner-name:

decoration_inner_name
.....................

**type**: ``string`` **default**: ``<decorating_service_id>.inner``

The ID under which the decorated service remains available. It's only used
together with `decorates`_.

.. _reference-services-decoration-on-invalid:

decoration_on_invalid
.....................

**type**: ``string`` **default**: ``exception``

Defines what happens when the decorated service doesn't exist. It's only used
together with `decorates`_. Allowed values are:

* ``exception``: an exception is thrown;
* ``ignore``: the decoration is ignored and the decorating service is removed;
* ``null`` (without quotes in YAML): the decorating service is kept and
  ``null`` is injected instead of the decorated service.

.. _reference-services-decoration-priority:

decoration_priority
...................

**type**: ``integer`` **default**: ``0``

When several services decorate the same service, this option defines the
order in which they are applied. Decorators with a higher priority are applied
first, so they end up closer to the decorated service. It's only used
together with `decorates`_.

.. _reference-services-deprecated:

deprecated
..........

**type**: ``array`` **default**: ``[]``

Marks the service (or the alias) as deprecated, which triggers a deprecation
when it's used. The ``package`` and ``version`` keys are required. The
optional ``message`` key must contain the ``%service_id%`` placeholder; when
omitted, Symfony uses a generic deprecation message:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\OldService:
                deprecated:
                    package: 'acme/legacy-bundle'
                    version: '1.2'
                    message: 'Use NewService instead of "%service_id%".'

    .. code-block:: xml

        <!-- config/services.xml -->
        <service id="App\Service\OldService">
            <deprecated package="acme/legacy-bundle"
                version="1.2">Use NewService instead of "%service_id%".</deprecated>
        </service>

    .. code-block:: php

        // config/services.php
        $services->set(\App\Service\OldService::class)
            ->deprecate(
                'acme/legacy-bundle',
                '1.2',
                'Use NewService instead of "%service_id%".'
            )
        ;

.. _reference-services-factory:

factory
.......

**type**: ``string`` | ``array`` **default**: ``null``

A callable used to create the service instead of calling its constructor. It
can be a static method (``Acme\Factory::build``), a method of another service
(``['@factory_service', 'build']``), a PHP function (``my_factory_function``)
or an :ref:`expression <services-expressions>` (``@=...``). When the value is
a service reference (``@factory_service``), the ``__invoke()`` method of that
service is called. It can't be combined with `constructor`_. Read more in
:doc:`/service_container/factories`.

.. _reference-services-file:

file
....

**type**: ``string`` **default**: ``null``

A PHP file that Symfony requires before creating the service. It's useful for
classes that can't be autoloaded.

.. _reference-services-from-callable:

from_callable
.............

**type**: ``string`` | ``array`` **default**: ``null``

Creates the service by calling ``Closure::fromCallable()`` with the given
callable, so the service is a ``Closure``. When `class`_ is set to an
interface with a single method, Symfony creates instead an object that
implements that interface and calls the given callable. It can't be combined
with `factory`_. Read more in :ref:`autowiring_closures`.

.. _reference-services-lazy:

lazy
....

**type**: ``boolean`` | ``string`` **default**: ``false``

When ``true``, Symfony injects a proxy that delays the creation of the service
until it's actually used. When set to a string, that string is the interface
implemented by the proxy. Read more in
:doc:`/service_container/lazy_services`.

.. _reference-services-parent:

parent
......

**type**: ``string`` **default**: ``null``

The ID of the service from which this service inherits its configuration.
This service only needs to define the keys that are different from the parent
service. Read more in :ref:`parent-services`.

.. _reference-services-properties:

properties
..........

**type**: ``array`` **default**: ``[]``

The public properties to set on the service after creating it. The keys are
the property names and the values use the same syntax as `arguments`_.

.. _reference-services-public:

public
......

**type**: ``boolean`` **default**: ``false``

Overrides the ``public`` value set in
:ref:`_defaults <reference-services-defaults-public>` for this service.
Services with the `synthetic`_ option enabled are public unless you set this
option explicitly.

.. _reference-services-resource-tags:

resource_tags
.............

**type**: ``array`` **default**: ``[]``

A list of :ref:`resource tags <di-resource-tags>` to apply to this class. They
use the same syntax as :ref:`tags <reference-services-tags>`, but the class is
excluded from the container, so it's not registered as a service. Compiler
passes can still find the class through its tags.

.. versionadded:: 7.4

    The ``resource_tags`` option was introduced in Symfony 7.4.

.. _reference-services-shared:

shared
......

**type**: ``boolean`` **default**: ``true``

When ``true``, the container always returns the same instance of the service.
When ``false``, it creates a new instance every time the service is requested.
Read more in :ref:`services-shared`.

.. _reference-services-stack:

stack
.....

**type**: ``array`` **default**: ``null``

Defines a stack of decorators registered under a single ID. Each item of the
stack decorates the next one, and the service ID refers to the first item.
When using ``stack``, the only other keys allowed are
:ref:`public <reference-services-public>` and `deprecated`_:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            app.cache_chain:
                stack:
                    - class: App\Cache\TraceableCache
                      arguments: ['@.inner']
                    - class: App\Cache\RedisCache

    .. code-block:: xml

        <!-- config/services.xml -->
        <services>
            <stack id="app.cache_chain">
                <service class="App\Cache\TraceableCache">
                    <argument type="service" id=".inner"/>
                </service>
                <service class="App\Cache\RedisCache"/>
            </stack>
        </services>

    .. code-block:: php

        // config/services.php
        $services->stack('app.cache_chain', [
            inline_service(\App\Cache\TraceableCache::class)
                ->args([service('.inner')]),
            inline_service(\App\Cache\RedisCache::class),
        ]);

Read more in :doc:`/service_container/decoration`.

.. _reference-services-synthetic:

synthetic
.........

**type**: ``boolean`` **default**: ``false``

When ``true``, the container doesn't create the service; your code must set it
at runtime with ``$container->set()``. Read more in
:ref:`services-synthetic`.

.. _reference-services-tags:

tags
....

**type**: ``array`` **default**: ``[]``

A list of :doc:`tags </service_container/tags>` to apply to the service. Each
tag can be a string (the tag name) or an array with a ``name`` key and any
number of attributes:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\EventSubscriber\OrderSubscriber:
                tags:
                    - 'kernel.event_subscriber'
                    - { name: 'app.handler', priority: 10 }

    .. code-block:: xml

        <!-- config/services.xml -->
        <service id="App\EventSubscriber\OrderSubscriber">
            <tag name="kernel.event_subscriber"/>
            <tag name="app.handler" priority="10"/>
        </service>

    .. code-block:: php

        // config/services.php
        $services->set(\App\EventSubscriber\OrderSubscriber::class)
            ->tag('kernel.event_subscriber')
            ->tag('app.handler', ['priority' => 10])
        ;

.. _reference-services-prototype:

Prototypes (Loading Services from a Directory)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

A *prototype* is an entry that registers as services all the classes found in
a directory. It's identified by the ``resource`` key:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\:
                resource: '../src/'
                exclude:
                    - '../src/DependencyInjection/'
                    - '../src/Entity/'
                    - '../src/Kernel.php'

    .. code-block:: xml

        <!-- config/services.xml -->
        <services>
            <prototype namespace="App\"
                resource="../src/"
                exclude="../src/{DependencyInjection,Entity,Kernel.php}"/>
        </services>

    .. code-block:: php

        // config/services.php
        $services->load('App\\', '../src/')
            ->exclude([
                '../src/DependencyInjection/',
                '../src/Entity/',
                '../src/Kernel.php',
            ])
        ;

Besides the keys listed below, prototypes accept the following keys of the
service definitions, which are applied to all the services they register:
``abstract``, ``arguments``, ``autoconfigure``, ``autowire``, ``bind``,
``calls``, ``configurator``, ``constructor``, ``deprecated``, ``factory``,
``lazy``, ``parent``, ``properties``, ``public``, ``resource_tags``,
``shared`` and ``tags``.

.. _reference-services-prototype-exclude:

exclude
.......

**type**: ``string`` | ``array`` **default**: ``null``

A path or list of paths to exclude from the ``resource``. Glob patterns are
supported.

.. _reference-services-prototype-namespace:

namespace
.........

**type**: ``string`` **default**: the entry key

The PHP namespace of the classes found in ``resource``, used to build the
service IDs. In YAML it defaults to the entry key (``App\`` in the example
above); in XML and PHP it's required.

.. _reference-services-prototype-resource:

resource
........

**type**: ``string`` **required**

A directory or glob pattern with the classes to register as services. Read
more in :ref:`service-container-services-load-example`.

.. _reference-services-when-env:

Environment-specific Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``when@<env>`` key (``<when env="<env>">`` in XML) loads the nested
configuration only when the application runs in the given environment:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        when@dev:
            services:
                App\Debug\Profiler:
                    public: true

    .. code-block:: xml

        <!-- config/services.xml -->
        <when env="dev">
            <services>
                <service id="App\Debug\Profiler" public="true"/>
            </services>
        </when>

    .. code-block:: php

        // config/services.php
        if ('dev' === $container->env()) {
            $services->set(\App\Debug\Profiler::class)->public();
        }

Read more in :ref:`configuration-environments`.

.. _reference-services-imports:

Importing Other Configuration Files
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The top-level ``imports`` key loads other configuration files. Each item
accepts the following keys:

* ``resource`` (``string``, **required**): the path of the file or directory
  to import. Glob patterns are supported;
* ``type`` (``string``, **default**: ``null``): the loader to use (``yaml``,
  ``xml``, ``php``, ``glob``, ``directory``, etc.). When omitted, it's guessed
  from the resource (e.g. from the file extension);
* ``ignore_errors`` (``boolean`` | ``string``, **default**: ``false``): when
  ``true``, all the errors that occur while loading the resource are ignored.
  When set to ``not_found``, only the errors about missing files are ignored.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        imports:
            - { resource: 'legacy_services.yaml' }
            - { resource: 'services_local.yaml', ignore_errors: not_found }

    .. code-block:: xml

        <!-- config/services.xml -->
        <imports>
            <import resource="legacy_services.yaml"/>
            <import resource="services_local.yaml" ignore-errors="not_found"/>
        </imports>

    .. code-block:: php

        // config/services.php
        $container->import('legacy_services.yaml');
        // the third argument configures the "ignore_errors" option
        $container->import('services_local.yaml', null, 'not_found');

Read more in :ref:`service-container-imports-directive`.
