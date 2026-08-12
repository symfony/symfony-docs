Service Tags
============

In Symfony applications, it's common that some services must be processed in
a special way by the framework or by third-party bundles. For example, you may
create a class that adds some custom filters to `Twig`_ templates. Not every
class can act as a :ref:`Twig extension <templates-twig-extension>`, so how
can Twig know that, among all the classes of your application, this specific
one is a Twig extension and must be registered as such?

**Service tags** solve this problem. Tags are labels that you add to service
definitions to mark the purpose of each service. For example, thanks to service
tags, Twig can collect all the services tagged with a tag called ``twig.extension``
to register them as Twig extensions.

This is how you would apply that tag to your service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Twig\AppExtension:
                tags: ['twig.extension']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Twig\AppExtension">
                    <tag name="twig.extension"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Twig\AppExtension;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(AppExtension::class)
                ->tag('twig.extension');
        };

Other tags are used to integrate your services into other systems. For a list of
all the tags available in the core Symfony Framework, check out
:doc:`/reference/dic_tags`. Each of these has a different effect on your service
and many tags require additional arguments (beyond the ``name`` parameter).

**For most Symfony developers, this is all you need to know**. If you want to go
further and learn how to consume tagged services or create your own custom
tags, keep reading.

.. _di-instanceof:

Autoconfiguring Tags
--------------------

If you enable :ref:`autoconfigure <services-autoconfigure>`, then some tags are
automatically applied for you. That's true for the ``twig.extension`` tag: the
container sees that your class extends ``AbstractExtension`` (or more accurately,
that it implements ``ExtensionInterface``) and adds the tag for you.

If you want to apply tags automatically for your own services, use the
``#[AutoconfigureTag]`` attribute directly on the base class or interface::

    // src/Security/CustomInterface.php
    namespace App\Security;

    use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

    #[AutoconfigureTag('app.custom_tag')]
    interface CustomInterface
    {
        // ...
    }

When no tag name is specified, the fully qualified class name (FQCN) of the target is used::

    // src/Security/CustomInterface.php

    #[AutoconfigureTag] // equivalent to #[AutoconfigureTag(self::class)]
    interface CustomInterface
    {
        // ...
    }

.. tip::

    If you need more capabilities to autoconfigure instances of your base class
    like their laziness, their bindings or their calls for example, you may rely
    on the :class:`Symfony\\Component\\DependencyInjection\\Attribute\\Autoconfigure` attribute.

.. versionadded:: 7.4

    In Symfony 7.4, autoconfiguration attributes (such as ``#[AutoconfigureTag]``)
    placed on abstract classes are also parsed when using
    :ref:`resource-based service loading <service-container-services-load-example>`.
    Previously, abstract classes were skipped during this process, meaning their
    attributes were not fully processed.

You can achieve the same in configuration files with the ``_instanceof`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # this config only applies to the services created by this file
            _instanceof:
                # services whose classes are instances of CustomInterface will be tagged automatically
                App\Security\CustomInterface:
                    tags: ['app.custom_tag']
            # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
            <services>
                <!-- this config only applies to the services created by this file -->
                <instanceof id="App\Security\CustomInterface" autowire="true">
                    <!-- services whose classes are instances of CustomInterface will be tagged automatically -->
                    <tag name="app.custom_tag"/>
                </instanceof>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\CustomInterface;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            // this config only applies to the services created by this file
            $services
                ->instanceof(CustomInterface::class)
                    // services whose classes are instances of CustomInterface will be tagged automatically
                    ->tag('app.custom_tag');
        };

.. warning::

    If you're using PHP configuration, you need to call ``instanceof`` before
    any service registration to make sure tags are correctly applied.

.. note::

    For more advanced needs, you can define the automatic tags from PHP code
    with the ``registerForAutoconfiguration()`` and
    ``registerAttributeForAutoconfiguration()`` methods. See
    :ref:`how to autoconfigure tags from a compiler pass or kernel <compiler-pass-autoconfiguration>`.

.. _tags_additional-attributes:

Adding Attributes to Tags
-------------------------

Tags can define additional attributes besides the tag name. These attributes
store extra information about each tagged service. For example, the services
tagged in the following example define a ``key`` attribute:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Handler\One:
                tags:
                    - { name: 'app.handler', key: 'handler_one' }

            App\Handler\Two:
                tags:
                    - { name: 'app.handler', key: 'handler_two' }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Handler\One">
                    <tag name="app.handler" key="handler_one"/>
                </service>

                <service id="App\Handler\Two">
                    <tag name="app.handler" key="handler_two"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Handler\One;
        use App\Handler\Two;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(One::class)
                ->tag('app.handler', ['key' => 'handler_one'])
            ;

            $services->set(Two::class)
                ->tag('app.handler', ['key' => 'handler_two'])
            ;
        };

Tag attributes are used, for example, to :ref:`index tagged services <tags_index-by>`
or to pass extra information to the :ref:`compiler pass that processes the tag
<service-container-compiler-pass-tags>`.

.. tip::

    The ``name`` attribute is used by default to define the name of the tag.
    If you want to add a ``name`` attribute to some tag in XML or YAML formats,
    you need to use this special syntax:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            services:
                App\Handler\One:
                    tags:
                        # this is a tag called 'app.handler'
                        - { name: 'app.handler', key: 'handler_one' }
                        # this is a tag called 'app.handler' with two attributes ('name' and 'key')
                        - app.handler: { name: 'arbitrary-value', key: 'handler_one' }

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <service id="App\Handler\One">
                        <!-- this is a tag called 'app.handler' -->
                        <tag name="app.handler" key="handler_one"/>
                        <!-- this is a tag called 'app.handler' with two attributes ('name' and 'key') -->
                        <tag name="arbitrary-value" key="handler_one">app.handler</tag>
                    </service>
                </services>
            </container>

.. tip::

    In YAML format, you may provide the tag as a simple string as long as
    you don't need to specify additional attributes. The following definitions
    are equivalent.

    .. code-block:: yaml

        # config/services.yaml
        services:
            # Compact syntax
            App\Handler\One:
                tags: ['app.handler']

            # Verbose syntax
            App\Handler\One:
                tags:
                    - { name: 'app.handler' }

.. _tags_reference-tagged-services:

Referencing Tagged Services
---------------------------

A common need is to inject *all* the services tagged with a specific tag into
another service (e.g. to implement a chain, a registry or a collection of
handlers). Symfony provides the *tagged iterator* shortcut for this, so you
don't have to write a :ref:`compiler pass <service-container-compiler-pass-tags>`
for this common use case.

Consider the following ``HandlerCollection`` class where you want to inject
all services tagged with ``app.handler`` into its constructor argument:

.. configuration-block::

    .. code-block:: php-attributes

        // src/HandlerCollection.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

        class HandlerCollection
        {
            public function __construct(
                // the attribute must be applied directly to the argument to autowire
                #[AutowireIterator('app.handler')]
                iterable $handlers
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Handler\One:
                tags: ['app.handler']

            App\Handler\Two:
                tags: ['app.handler']

            App\HandlerCollection:
                # inject all services tagged with app.handler as first argument
                arguments:
                    - !tagged_iterator app.handler

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Handler\One">
                    <tag name="app.handler"/>
                </service>

                <service id="App\Handler\Two">
                    <tag name="app.handler"/>
                </service>

                <service id="App\HandlerCollection">
                    <!-- inject all services tagged with app.handler as first argument -->
                    <argument type="tagged_iterator" tag="app.handler"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(App\Handler\One::class)
                ->tag('app.handler')
            ;

            $services->set(App\Handler\Two::class)
                ->tag('app.handler')
            ;

            $services->set(App\HandlerCollection::class)
                // inject all services tagged with app.handler as first argument
                ->args([tagged_iterator('app.handler')])
            ;
        };

.. note::

    Some IDEs will show an error when using ``#[AutowireIterator]`` together
    with the `PHP constructor promotion`_:
    *"Attribute cannot be applied to a property because it does not contain the 'Attribute::TARGET_PROPERTY' flag"*.
    The reason is that those constructor arguments are both parameters and class
    properties. You can safely ignore this error message.

.. seealso::

    If you need to fetch the tagged services lazily by their id instead of
    iterating over all of them, use a *service locator* with the
    ``#[AutowireLocator]`` attribute. See
    :ref:`service locators <service-locator_autowire-locator>`.

Excluding Services from the Iterator
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If for some reason you need to exclude one or more services when using a tagged
iterator, add the ``exclude`` option:

.. configuration-block::

    .. code-block:: php-attributes

        // src/HandlerCollection.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

        class HandlerCollection
        {
            public function __construct(
                #[AutowireIterator('app.handler', exclude: ['App\Handler\Three'])]
                iterable $handlers
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            # This is the service we want to exclude, even if the 'app.handler' tag is attached
            App\Handler\Three:
                tags: ['app.handler']

            App\HandlerCollection:
                arguments:
                    - !tagged_iterator { tag: app.handler, exclude: ['App\Handler\Three'] }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <!-- This is the service we want to exclude, even if the 'app.handler' tag is attached -->
                <service id="App\Handler\Three">
                    <tag name="app.handler"/>
                </service>

                <service id="App\HandlerCollection">
                    <!-- inject all services tagged with app.handler as first argument -->
                    <argument type="tagged_iterator" tag="app.handler">
                        <exclude>App\Handler\Three</exclude>
                    </argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            // ...

            // This is the service we want to exclude, even if the 'app.handler' tag is attached
            $services->set(App\Handler\Three::class)
                ->tag('app.handler')
            ;

            $services->set(App\HandlerCollection::class)
                // inject all services tagged with app.handler as first argument
                ->args([tagged_iterator('app.handler', exclude: [App\Handler\Three::class])])
            ;
        };

In the case the referencing service is itself tagged with the tag being used in the tagged
iterator, it is automatically excluded from the injected iterable. This behavior can be
disabled by setting the ``exclude_self`` option to ``false``:

.. configuration-block::

    .. code-block:: php-attributes

        // src/HandlerCollection.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

        class HandlerCollection
        {
            public function __construct(
                #[AutowireIterator('app.handler', exclude: ['App\Handler\Three'], excludeSelf: false)]
                iterable $handlers
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            # This is the service we want to exclude, even if the 'app.handler' tag is attached
            App\Handler\Three:
                tags: ['app.handler']

            App\HandlerCollection:
                arguments:
                    - !tagged_iterator { tag: app.handler, exclude: ['App\Handler\Three'], exclude_self: false }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <!-- This is the service we want to exclude, even if the 'app.handler' tag is attached -->
                <service id="App\Handler\Three">
                    <tag name="app.handler"/>
                </service>

                <service id="App\HandlerCollection">
                    <!-- inject all services tagged with app.handler as first argument -->
                    <argument type="tagged_iterator" tag="app.handler" exclude-self="false">
                        <exclude>App\Handler\Three</exclude>
                    </argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            // ...

            // This is the service we want to exclude, even if the 'app.handler' tag is attached
            $services->set(App\Handler\Three::class)
                ->tag('app.handler')
            ;

            $services->set(App\HandlerCollection::class)
                // inject all services tagged with app.handler as first argument
                ->args([tagged_iterator('app.handler', exclude: [App\Handler\Three::class], excludeSelf: false)])
            ;
        };

Tagged Services with Priority
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The tagged services can be prioritized using the ``priority`` attribute. The
priority is a positive or negative integer that defaults to ``0``. The higher
the number, the earlier the tagged service will be located in the collection:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Handler/One.php
        namespace App\Handler;

        use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

        #[AsTaggedItem(priority: 20)]
        class One
        {
            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Handler\One:
                tags:
                    - { name: 'app.handler', priority: 20 }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Handler\One">
                    <tag name="app.handler" priority="20"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Handler\One;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(One::class)
                ->tag('app.handler', ['priority' => 20])
            ;
        };

Another option, which is particularly useful when using autoconfiguring
tags, is to implement the static ``getDefaultPriority()`` method on the
service itself::

    // src/Handler/One.php
    namespace App\Handler;

    class One
    {
        public static function getDefaultPriority(): int
        {
            return 3;
        }
    }

If you want to have another method defining the priority
(e.g. ``getPriority()`` rather than ``getDefaultPriority()``),
you can define it in the configuration of the collecting service:

.. configuration-block::

    .. code-block:: php-attributes

        // src/HandlerCollection.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

        class HandlerCollection
        {
            public function __construct(
                #[AutowireIterator('app.handler', defaultPriorityMethod: 'getPriority')]
                iterable $handlers
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\HandlerCollection:
                # inject all services tagged with app.handler as first argument
                arguments:
                    - !tagged_iterator { tag: app.handler, default_priority_method: getPriority }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">
            <services>
                <service id="App\HandlerCollection">
                    <argument type="tagged_iterator" tag="app.handler" default-priority-method="getPriority"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function (ContainerConfigurator $container): void {
            $services = $container->services();

            // ...

            $services->set(App\HandlerCollection::class)
                ->args([
                    tagged_iterator('app.handler', null, null, 'getPriority'),
                ])
            ;
        };

.. _tags_index-by:

Tagged Services with Index
~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, tagged services are indexed using their service IDs. You can change
this behavior with two options of the tagged iterator (``index_by`` and
``default_index_method``) which can be used independently or combined.

The ``index_by`` / ``indexAttribute`` Option
............................................

This option defines the name of the option/attribute that stores the value used
to index the services:

.. configuration-block::

    .. code-block:: php-attributes

        // src/HandlerCollection.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

        class HandlerCollection
        {
            public function __construct(
                #[AutowireIterator('app.handler', indexAttribute: 'key')]
                iterable $handlers
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Handler\One:
                tags:
                    - { name: 'app.handler', key: 'handler_one' }

            App\Handler\Two:
                tags:
                    - { name: 'app.handler', key: 'handler_two' }

            App\HandlerCollection:
                arguments: [!tagged_iterator { tag: 'app.handler', index_by: 'key' }]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Handler\One">
                    <tag name="app.handler" key="handler_one"/>
                </service>

                <service id="App\Handler\Two">
                    <tag name="app.handler" key="handler_two"/>
                </service>

                <service id="App\HandlerCollection">
                    <argument type="tagged_iterator" tag="app.handler" index-by="key"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Handler\One;
        use App\Handler\Two;

        return function (ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(One::class)
                ->tag('app.handler', ['key' => 'handler_one']);

            $services->set(Two::class)
                ->tag('app.handler', ['key' => 'handler_two']);

            $services->set(App\HandlerCollection::class)
                ->args([
                    // 2nd argument is the index attribute name
                    tagged_iterator('app.handler', 'key'),
                ])
            ;
        };

In this example, the ``index_by`` option is ``key``. All services define that
option/attribute, so that will be the value used to index the services. For example,
to get the ``App\Handler\Two`` service::

    // src/Handler/HandlerCollection.php
    namespace App\Handler;

    class HandlerCollection
    {
        public function __construct(iterable $handlers)
        {
            $handlers = $handlers instanceof \Traversable ? iterator_to_array($handlers) : $handlers;

            // this value is defined in the `key` option of the service
            $handlerTwo = $handlers['handler_two'];
        }
    }

If some service doesn't define the option/attribute configured in ``index_by``,
Symfony applies this fallback process:

#. If the service class defines a static method called ``getDefault<CamelCase index_by value>Name``
   (in this example, ``getDefaultKeyName()``), call it and use the returned value;
#. Otherwise, fall back to the default behavior and use the service ID.

The ``default_index_method`` Option
...................................

This option defines the name of the service class method that will be called to
get the value used to index the services:

.. configuration-block::

    .. code-block:: php-attributes

        // src/HandlerCollection.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

        class HandlerCollection
        {
            public function __construct(
                #[AutowireIterator('app.handler', defaultIndexMethod: 'getIndex')]
                iterable $handlers
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\HandlerCollection:
                arguments: [!tagged_iterator { tag: 'app.handler', default_index_method: 'getIndex' }]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="App\HandlerCollection">
                    <argument type="tagged_iterator"
                        tag="app.handler"
                        default-index-method="getIndex"
                    />
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\HandlerCollection;

        return function (ContainerConfigurator $container) {
            $services = $container->services();

            // ...

            $services->set(HandlerCollection::class)
                ->args([
                    tagged_iterator('app.handler', null, 'getIndex'),
                ])
            ;
        };

If some service class doesn't define the method configured in ``default_index_method``,
Symfony will fall back to using the service ID as its index inside the tagged services.

Combining the ``index_by`` and ``default_index_method`` Options
...............................................................

You can combine both options in the same collection of tagged services. Symfony
will process them in the following order:

#. If the service defines the option/attribute configured in ``index_by``, use it;
#. If the service class defines the method configured in ``default_index_method``, use it;
#. Otherwise, fall back to using the service ID as its index inside the tagged services collection.

.. _tags_as-tagged-item:

The ``#[AsTaggedItem]`` Attribute
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It is possible to define both the priority and the index of a tagged
item thanks to the ``#[AsTaggedItem]`` attribute. This attribute must
be used directly on the class of the service you want to configure::

    // src/Handler/One.php
    namespace App\Handler;

    use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

    #[AsTaggedItem(index: 'handler_one', priority: 10)]
    class One
    {
        // ...
    }

You can apply the ``#[AsTaggedItem]`` attribute multiple times to register the
same service under different indexes::

    #[AsTaggedItem(index: 'handler_one', priority: 5)]
    #[AsTaggedItem(index: 'handler_two', priority: 20)]
    class SomeService
    {
        // ...
    }

.. versionadded:: 7.3

    The feature to apply the ``#[AsTaggedItem]`` attribute multiple times was
    introduced in Symfony 7.3.

.. _di-resource-tags:

Tagging Non-Service Classes
---------------------------

Not all classes need to be registered as services. Entities, value objects, and
DTOs, for example, should be discoverable at compile time but must not be instantiated
by the container. Use **resource tags** to tag such classes while keeping them
excluded from the service container.

The most direct option is the ``#[AutoconfigureResourceTag]`` attribute, which
is repeatable and accepts an optional second argument with the tag attributes::

    // src/Model/Invoice.php
    namespace App\Model;

    use Symfony\Component\DependencyInjection\Attribute\AutoconfigureResourceTag;

    #[AutoconfigureResourceTag('app.report_item', ['type' => 'invoice'])]
    #[AutoconfigureResourceTag('app.exportable')]
    class Invoice
    {
        // ...
    }

The attribute also works on interfaces and base classes. In that case, every
class that implements the interface or extends the base class receives the
resource tag through autoconfiguration, the same way ``#[AutoconfigureTag]``
applies service tags::

    // src/Model/ReportItemInterface.php
    namespace App\Model;

    use Symfony\Component\DependencyInjection\Attribute\AutoconfigureResourceTag;

    #[AutoconfigureResourceTag('app.report_item')]
    interface ReportItemInterface
    {
        // ...
    }

The same definitions can be declared per service in configuration files:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Model\Invoice:
                resource_tags:
                    - { name: 'app.report_item', type: 'invoice' }
                    - 'app.exportable'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Model\Invoice">
                    <resource-tag name="app.report_item" type="invoice"/>
                    <resource-tag>app.exportable</resource-tag>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Model\Invoice;

        return function (ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(Invoice::class)
                ->resourceTag('app.report_item', ['type' => 'invoice'])
                ->resourceTag('app.exportable')
            ;
        };

.. tip::

    Resource tags can also be declared in the ``_defaults`` section to apply
    them to every service defined in the same file, using the same option names
    (``resource_tags:`` in YAML, ``<resource-tag>`` inside ``<defaults>`` in
    XML, ``->defaults()->resourceTag(...)`` in PHP).

.. versionadded:: 7.4

    Declaring resource tags through configuration files, and the
    ``#[AutoconfigureResourceTag]`` attribute, was introduced in Symfony 7.4.

When the resource tag setup requires custom logic, use the
:method:`Symfony\\Component\\DependencyInjection\\Definition::addResourceTag`
method from PHP code::

    // src/Attribute/AppModel.php
    namespace App\Attribute;

    #[\Attribute(\Attribute::TARGET_CLASS)]
    class AppModel
    {
    }

    // src/Kernel.php
    use App\Attribute\AppModel;
    use Symfony\Component\DependencyInjection\ChildDefinition;

    class Kernel extends BaseKernel
    {
        // ...

        protected function build(ContainerBuilder $container): void
        {
            // classes annotated with ``#[AppModel]`` will be tagged with ``app.model`` and
            // automatically excluded from the container (they won't be instantiated as services)
            $container->registerAttributeForAutoconfiguration(
                AppModel::class,
                static function (ChildDefinition $definition): void {
                    $definition->addResourceTag('app.model');
                }
            );
        }
    }

You can then retrieve these classes in a :ref:`compiler pass <service-container-compiler-pass-tags>`
using :method:`Symfony\\Component\\DependencyInjection\\ContainerBuilder::findTaggedResourceIds`::

    // src/DependencyInjection/Compiler/ModelDiscoveryPass.php
    namespace App\DependencyInjection\Compiler;

    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    class ModelDiscoveryPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container): void
        {
            $classes = [];

            foreach ($container->findTaggedResourceIds('app.model') as $id => $tags) {
                $classes[] = $container->getDefinition($id)->getClass();
            }

            $container->setParameter('app.model_classes', $classes);
        }
    }

.. versionadded:: 7.3

    The ``addResourceTag()`` and ``findTaggedResourceIds()`` methods were
    introduced in Symfony 7.3.

Creating Custom Tags and Processing Them with Compiler Passes
-------------------------------------------------------------

Tags on their own don't actually alter the functionality of your services in
any way. You can create and apply any custom tag (e.g. ``app.report_generator``)
to your services and nothing will happen. Tags "mean" something when some code
asks the container for the list of services tagged with them.

For the most common use case (injecting all the tagged services into another
service) use the :ref:`tagged iterators <tags_reference-tagged-services>`
explained earlier in this article. When you need full control (e.g. calling a
specific method on a collecting service for each tagged service, or using tag
attributes with custom logic), write a **compiler pass**. Compiler passes ask
the container for the tagged services with the ``findTaggedServiceIds()``
method and modify the service definitions before the container is compiled.

Read :ref:`how to process tagged services in a compiler pass <service-container-compiler-pass-tags>`
for the full explanation and examples.

.. _`PHP constructor promotion`: https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion
.. _`Twig`: https://twig.symfony.com/
