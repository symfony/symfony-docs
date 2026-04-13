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

    Symfony 7.4 deprecated the XML configuration format for the service
    container; it will be removed in Symfony 8.0. The XML examples in this
    page are still accurate for 7.4, but new code should use YAML or PHP.

A typical ``services`` section is made of four kinds of entries:

* `_defaults`_: values applied by default to every service defined in the file;
* `_instanceof`_: values applied to every service whose class matches a given
  type (class or interface);
* :ref:`Service definitions <reference-dic-service-definitions>`: each entry
  defines or overrides a service identified by its key (the *service ID*);
* :ref:`Prototype loaders <reference-dic-prototype>`: entries with a
  ``resource`` key that auto-discover services from a directory.

The configuration file may also contain other top-level keys that are
siblings of ``services``: :ref:`imports <reference-dic-imports>`,
:ref:`parameters <service-container-parameters>` and
:ref:`environment-specific overrides <reference-dic-when-env>`.

This article only documents the configuration keys themselves. For detailed
explanations and examples, follow the links to the related articles.

.. _reference-dic-defaults:

``_defaults``
-------------

The ``_defaults`` key stores the values that are applied by default to every
service defined in the same file. Defaults are merged into each service unless
the service explicitly overrides them.

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
                    ->autowire()
                    ->autoconfigure()
                    ->public(false)
            ;
        };

The keys allowed inside ``_defaults`` are:
:ref:`autoconfigure <defaults-autoconfigure>`,
:ref:`autowire <defaults-autowire>`,
:ref:`bind <defaults-bind>`,
:ref:`public <defaults-public>`,
:ref:`tags <defaults-tags>` and
:ref:`resource_tags <defaults-resource-tags>`.

.. _defaults-autoconfigure:

autoconfigure
~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

When set to ``true``, Symfony inspects the class of every service and applies
to it the configurations registered via ``registerForAutoconfiguration()``
(typically tags added when the service implements a known interface). Read more
in :doc:`/service_container/autowiring` and :doc:`/service_container/tags`.

.. _defaults-autowire:

autowire
~~~~~~~~

**type**: ``boolean`` **default**: ``false``

When set to ``true``, Symfony will try to guess constructor and method
arguments based on their type-hints. Read more in
:doc:`/service_container/autowiring`.

.. _defaults-bind:

bind
~~~~

**type**: ``array`` **default**: ``[]``

Defines values that Symfony will inject in the arguments of the services. Keys
can be argument names (``$variableName``), types (``Some\Class\Name``) or both
(``Some\Class\Name $variableName``). The bindings defined here are merged with
the per-service :ref:`bind <reference-dic-bind>` option. Read more in
:doc:`/service_container/optional_dependencies`.

.. _defaults-public:

public
~~~~~~

**type**: ``boolean`` **default**: ``false``

If ``true``, services defined in the file can be retrieved directly from the
container with ``$container->get()``. If ``false`` (the recommended default),
they can only be injected as dependencies. Read more in
:doc:`/service_container/alias_private`.

.. _defaults-tags:

tags
~~~~

**type**: ``array`` **default**: ``[]``

A list of :doc:`tags </service_container/tags>` to apply to every service
defined in the file. Tags can be defined as plain strings or as arrays
containing the tag name and additional attributes:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                tags:
                    - app.custom_tag
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

.. _defaults-resource-tags:

resource_tags
~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Same as :ref:`tags <defaults-tags>`, but the listed tags are only applied to
the services discovered through a
:ref:`prototype loader <reference-dic-prototype>`, not to services defined
explicitly in the file.

.. _reference-dic-instanceof:

``_instanceof``
---------------

The ``_instanceof`` key applies a configuration to every service whose class
extends or implements a given type:

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
`autowire`_,
`bind`_,
`calls`_,
`configurator`_,
`constructor`_,
`lazy`_,
`properties`_,
`public`_,
`resource_tags`_,
`shared`_ and
`tags`_.

Read more in :doc:`/service_container/tags`.

.. _reference-dic-service-definitions:

Service Definitions
-------------------

Each entry under ``services:`` whose key is not ``_defaults`` or
``_instanceof`` defines (or overrides) a service. Its key is the *service ID*,
which is the identifier used to reference the service from other definitions
or to retrieve it from the container.

The keys allowed in a service definition are listed below. Some of them are
mutually exclusive:

* `alias`_ can only be combined with `public`_ and `deprecated`_;
* `factory`_ and `constructor`_ cannot both be set;
* `from_callable`_ cannot be combined with `alias`_, `parent`_, `synthetic`_,
  `factory`_, `file`_, `arguments`_, `properties`_, `configurator`_ or
  `calls`_;
* `stack`_ can only be combined with `public`_ and `deprecated`_.

.. _reference-dic-abstract:

abstract
~~~~~~~~

**type**: ``boolean`` **default**: ``false``

When ``true``, the service cannot be retrieved from the container; it only
serves as a template for child services (see `parent`_). Read more in
:doc:`/service_container/parent_services`.

.. _reference-dic-alias:

alias
~~~~~

**type**: ``string`` **default**: ``null``

When set, the service is an :ref:`alias <services-alias>` to another service.
The value is the ID of the referenced service. When defining an alias, the
only other keys allowed are `public`_ and `deprecated`_. Read more in
:doc:`/service_container/alias_private`.

.. _reference-dic-arguments:

arguments
~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Lists the arguments passed to the constructor (or to the method defined by
`factory`_). Arguments can be scalars, :ref:`parameters
<service-container-parameters>` (``%kernel.cache_dir%``), service references
(``@service.id``) or any of the special argument types described in
:doc:`/service_container/injection_types`.

.. _reference-dic-autoconfigure:

autoconfigure
~~~~~~~~~~~~~

**type**: ``boolean`` **default**: value set in :ref:`_defaults <defaults-autoconfigure>`, or ``false`` when unset

Overrides the default ``autoconfigure`` value for this service. See
:ref:`_defaults autoconfigure <defaults-autoconfigure>` for details.

.. _reference-dic-autowire:

autowire
~~~~~~~~

**type**: ``boolean`` **default**: value set in :ref:`_defaults <defaults-autowire>`, or ``false`` when unset

Overrides the default ``autowire`` value for this service. See
:ref:`_defaults autowire <defaults-autowire>` for details.

.. _reference-dic-bind:

bind
~~~~

**type**: ``array`` **default**: ``[]`` (merged with :ref:`_defaults bind <defaults-bind>`)

Per-service argument bindings; see :ref:`_defaults bind <defaults-bind>` for
the syntax. Per-service bindings with the same key override those declared in
``_defaults``. Read more in :doc:`/service_container/optional_dependencies`.

.. _reference-dic-calls:

calls
~~~~~

**type**: ``array`` **default**: ``[]``

A list of methods called on the service after instantiation (so-called
*setter injection*). Each item defines the method to call, the arguments to
pass and, optionally, whether the method returns a clone of the service
(``returns_clone``):

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
            ->call('withDispatcher', [service('event_dispatcher')], true)
        ;

Read more in :doc:`/service_container/calls`.

.. _reference-dic-class:

class
~~~~~

**type**: ``string`` **default**: ``null``

The fully-qualified class name to instantiate. When the service ID itself is
a fully-qualified class name and that class exists, this option can be omitted
and Symfony will use the ID as the class.

.. deprecated:: 7.4

    Since Symfony 7.4, using a service ID that *looks like* a fully-qualified
    class name but for which no matching class or interface exists triggers a
    deprecation. Either rename the service to a non-FQCN identifier (for
    example, by using dots) or create the missing class.

.. _reference-dic-configurator:

configurator
~~~~~~~~~~~~

**type**: ``string`` | ``array`` **default**: ``null``

A callable executed once the service has been instantiated and its
arguments/properties/method calls have been processed. Its purpose is to apply
last-minute configuration to the service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\NewsletterManager:
                configurator: ['@App\Service\NewsletterConfigurator', 'configure']

    .. code-block:: xml

        <!-- config/services.xml -->
        <service id="App\Service\NewsletterManager">
            <configurator service="App\Service\NewsletterConfigurator" method="configure"/>
        </service>

    .. code-block:: php

        // config/services.php
        $services->set(\App\Service\NewsletterManager::class)
            ->configurator([service(\App\Service\NewsletterConfigurator::class), 'configure'])
        ;

Read more in :doc:`/service_container/configurators`.

.. _reference-dic-constructor:

constructor
~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

The name of a static method to use instead of the default ``__construct()``
method to create the service. Cannot be combined with `factory`_.

.. _reference-dic-decorates:

decorates
~~~~~~~~~

**type**: ``string`` **default**: ``null``

When set, the service decorates the service whose ID is given. The decorated
service is replaced in the container by the new one and the original service
becomes available under a new ID (see `decoration_inner_name`_). Read more in
:doc:`/service_container/service_decoration`.

.. _reference-dic-decoration-inner-name:

decoration_inner_name
~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``<decorating_service_id>.inner``

The ID under which the decorated service becomes available. Only useful when
combined with `decorates`_.

.. _reference-dic-decoration-on-invalid:

decoration_on_invalid
~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``'exception'``

Controls what happens when the service to decorate does not exist. Allowed
values are:

* ``'exception'``: an exception is thrown when the decorated service is
  missing (default behavior, equivalent to
  ``ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE``);
* ``'ignore'``: the decoration is ignored and the decorating service is
  registered as a regular service
  (``ContainerInterface::IGNORE_ON_INVALID_REFERENCE``);
* ``null`` (without quotes in YAML): the decorated service is replaced by
  ``null`` when injected into the decorator
  (``ContainerInterface::NULL_ON_INVALID_REFERENCE``). In XML, use the string
  ``"null"``.

The corresponding integer constants of
:class:`Symfony\\Component\\DependencyInjection\\ContainerInterface` are also
accepted. Only useful when combined with `decorates`_.

.. _reference-dic-decoration-priority:

decoration_priority
~~~~~~~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``0``

When several services decorate the same one, this option controls the order
in which they are applied. The decorator with the highest priority wraps the
others. Only useful when combined with `decorates`_.

.. _reference-dic-deprecated:

deprecated
~~~~~~~~~~

**type**: ``array`` **default**: ``[]`` (no deprecation)

Marks the service as deprecated, triggering a deprecation notice when the
service is referenced. The ``package`` and ``version`` keys are required;
the ``message`` key is optional and defaults to an empty string:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\OldService:
                deprecated:
                    package: 'acme/legacy-bundle'
                    version: '1.2'
                    message: 'The "%service_id%" service is deprecated, use AcmeNewService instead.'

    .. code-block:: xml

        <!-- config/services.xml -->
        <service id="App\Service\OldService">
            <deprecated package="acme/legacy-bundle" version="1.2">
                The "%service_id%" service is deprecated, use AcmeNewService instead.
            </deprecated>
        </service>

    .. code-block:: php

        // config/services.php
        $services->set(\App\Service\OldService::class)
            ->deprecate('acme/legacy-bundle', '1.2', 'The "%service_id%" service is deprecated, use AcmeNewService instead.')
        ;

The placeholder ``%service_id%`` is automatically replaced by the service ID
in the deprecation message.

.. _reference-dic-factory:

factory
~~~~~~~

**type**: ``string`` | ``array`` **default**: ``null``

Defines a callable used to create the service instead of calling its
constructor. The callable can be a static method (``Acme\Factory::build``),
a method on another service (``['@factory_service', 'build']``), a global
function (``my_factory_function``) or an :doc:`expression
</service_container/expression_language>` (``@=...``). When the value is a
single service reference (``@my_factory``), the ``__invoke()`` method of the
service is called. Cannot be combined with `constructor`_. Read more in
:doc:`/service_container/factories`.

.. _reference-dic-file:

file
~~~~

**type**: ``string`` **default**: ``null``

A PHP file that Symfony will ``require`` before instantiating the service.
Useful for classes that are not registered with Composer autoloading.

.. _reference-dic-from-callable:

from_callable
~~~~~~~~~~~~~

**type**: ``string`` | ``array`` **default**: ``null``

Builds the service as a ``Closure`` created from the given callable. Useful
to register first-class callables as services. When ``from_callable`` is
set, the keys `alias`_, `parent`_, `synthetic`_, `factory`_, `file`_,
`arguments`_, `properties`_, `configurator`_ and `calls`_ are not allowed.
Read more in :doc:`/service_container/service_closures`.

.. _reference-dic-lazy:

lazy
~~~~

**type**: ``boolean`` | ``string`` **default**: ``false``

When ``true``, Symfony creates a proxy that postpones the instantiation of
the service until it is actually used. When set to a string, that string is
used as the interface to proxy. Read more in
:doc:`/service_container/lazy_services`.

.. _reference-dic-parent:

parent
~~~~~~

**type**: ``string`` **default**: ``null``

The ID of a parent service from which the configuration is inherited. The
current service becomes a ``ChildDefinition`` and only needs to define the
keys that differ from the parent. Read more in
:doc:`/service_container/parent_services`.

.. _reference-dic-properties:

properties
~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

A map of public properties to set on the service after instantiation. The
keys are property names and the values follow the same syntax as `arguments`_.

.. _reference-dic-public:

public
~~~~~~

**type**: ``boolean`` **default**: value set in :ref:`_defaults <defaults-public>`, or ``false`` when unset (`synthetic`_ services default to ``true`` unless overridden)

Overrides the default ``public`` value for this service. See
:ref:`_defaults public <defaults-public>` for details.

.. _reference-dic-resource-tags:

resource_tags
~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Same as `tags`_, but limited to services discovered through a
:ref:`prototype loader <reference-dic-prototype>`. Tags listed here are only
applied to the auto-discovered services, not to the prototype entry itself.

.. _reference-dic-shared:

shared
~~~~~~

**type**: ``boolean`` **default**: ``true``

When ``true`` (the default), the container always returns the same instance
when the service is requested. When ``false``, a new instance is created on
each request. Read more in :doc:`/service_container/shared`.

.. _reference-dic-stack:

stack
~~~~~

**type**: ``array`` **default**: ``null``

Defines a chain of decorating services registered under a single ID. Each
entry of the stack decorates the next one and the resulting service is
exposed under the outer ID. When ``stack`` is set, the only other keys
allowed in the definition are `public`_ and `deprecated`_:

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
            inline_service(\App\Cache\TraceableCache::class)->args([service('.inner')]),
            inline_service(\App\Cache\RedisCache::class),
        ]);

In XML, ``<stack>`` is an element sibling to ``<service>`` inside
``<services>`` (it is not nested inside a ``<service>`` element). Read more
in :doc:`/service_container/service_decoration`.

.. _reference-dic-synthetic:

synthetic
~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

When ``true``, the service is not created by the container itself; it must be
injected at runtime via ``Container::set()``. Synthetic services are public
by default. Read more in :doc:`/service_container/synthetic_services`.

.. _reference-dic-tags:

tags
~~~~

**type**: ``array`` **default**: ``[]``

A list of :doc:`tags </service_container/tags>` to apply to the service. Each
tag can be a plain string (the tag name) or an array containing a ``name``
key and any number of additional attributes:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\EventSubscriber\OrderSubscriber:
                tags:
                    - kernel.event_subscriber
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

.. _reference-dic-prototype:

Prototype Loaders (auto-discovery)
----------------------------------

A *prototype* is a special entry that registers as services every class found
in a directory. It is identified by the presence of the ``resource`` key:

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
            <prototype namespace="App\" resource="../src/" exclude="../src/{DependencyInjection,Entity,Kernel.php}"/>
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

A prototype loader accepts the keys listed below in addition to most of the
keys allowed in a service definition (``abstract``, ``arguments``,
``autowire``, ``autoconfigure``, ``bind``, ``calls``, ``configurator``,
``constructor``, ``deprecated``, ``factory``, ``lazy``, ``parent``,
``properties``, ``public``, ``shared``, ``tags`` and ``resource_tags``);
those keys apply to every service discovered by the prototype.

.. _reference-dic-prototype-resource:

resource
~~~~~~~~

**type**: ``string`` **(required)**

A directory or glob pattern from which service classes are loaded. Read more
in :doc:`/service_container/import`.

.. _reference-dic-prototype-namespace:

namespace
~~~~~~~~~

**type**: ``string`` **default**: the entry key

The PHP namespace used to derive service IDs from the discovered files. In
YAML it defaults to the entry key (``App\`` in the example above); in XML and
PHP it must be set explicitly.

.. _reference-dic-prototype-exclude:

exclude
~~~~~~~

**type**: ``string`` | ``array`` **default**: ``[]``

A path or list of paths to exclude from the discovery. Glob patterns are
supported.

.. _reference-dic-when-env:

Environment-specific Configuration
----------------------------------

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

.. _reference-dic-imports:

Importing other Configuration Files
-----------------------------------

The top-level ``imports`` key loads additional configuration files. Each
entry accepts the following keys:

* ``resource`` (**required**, ``string``): path of the file or directory to
  import. Glob patterns are supported.
* ``type`` (``string``, **default**: ``null``): forces a specific loader
  (``yaml``, ``xml``, ``php``, ``glob``, ``directory``); when omitted, the
  loader is guessed from the file extension.
* ``ignore_errors`` (``boolean`` | ``string``, **default**: ``false``): when
  ``true``, errors that occur while loading the resource are silently
  ignored. When set to ``not_found``, only "file not found" errors are
  ignored.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        imports:
            - { resource: legacy_services.yaml }
            - { resource: '../vendor/acme/foo-bundle/services.xml', ignore_errors: not_found }

    .. code-block:: xml

        <!-- config/services.xml -->
        <imports>
            <import resource="legacy_services.yaml"/>
            <import resource="../vendor/acme/foo-bundle/services.xml" ignore-errors="not_found"/>
        </imports>

    .. code-block:: php

        // config/services.php
        $container->import('legacy_services.yaml');
        $container->import('../vendor/acme/foo-bundle/services.xml', null, 'not_found');

Read more in :doc:`/service_container/import`.
