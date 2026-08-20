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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Twig\AppExtension;

        return App::config([
            'services' => [
                AppExtension::class => [
                    'tags' => ['twig.extension'],
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\CustomInterface;

        return App::config([
            'services' => [
                // this config only applies to the services created by this file
                '_instanceof' => [
                    // services whose classes are instances of CustomInterface will be tagged automatically
                    CustomInterface::class => [
                        'tags' => ['app.custom_tag'],
                    ],
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Handler\One;
        use App\Handler\Two;

        return App::config([
            'services' => [
                One::class => [
                    'tags' => [
                        ['app.handler' => ['key' => 'handler_one']],
                    ],
                ],
                Two::class => [
                    'tags' => [
                        ['app.handler' => ['key' => 'handler_two']],
                    ],
                ],
            ],
        ]);

Tag attributes are used, for example, to :ref:`index tagged services <tags_index-by>`
or to pass extra information to the :ref:`compiler pass that processes the tag
<service-container-compiler-pass-tags>`.

.. tip::

    The ``name`` attribute is used by default to define the name of the tag.
    If you want to add a ``name`` attribute to some tag in YAML format,
    you need to use this special syntax:

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Handler\One:
                tags:
                    # this is a tag called 'app.handler'
                    - { name: 'app.handler', key: 'handler_one' }
                    # this is a tag called 'app.handler' with two attributes ('name' and 'key')
                    - app.handler: { name: 'arbitrary-value', key: 'handler_one' }

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

.. _di-tag-attributes-per-tagged-service:

Computing Tag Attributes per Tagged Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 8.2

    Support for computing tag attributes per tagged service was introduced in
    Symfony 8.2.

Autoconfigured tags attach the same attributes to every tagged service. To
compute the attributes of each service instead, pass a callable in the form
``[SomeClass::class, 'someMethod']`` as the tag attributes. When compiling
the container, Symfony calls that static method on each concrete class
implementing the interface::

    // src/Handler/BatchHandlerInterface.php
    namespace App\Handler;

    use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

    #[AutoconfigureTag('app.handler', attributes: [self::class, 'getTagAttributes'])]
    interface BatchHandlerInterface
    {
        /**
         * @return array<string, mixed>
         */
        public static function getTagAttributes(): array;
    }

Because the method is declared by the interface, PHP requires every class
implementing it to define the method, and static analysis tools can detect
mistakes in the callable. The method is called on the concrete class, so it
can return attributes that depend on each class::

    // src/Handler/RefundHandler.php
    namespace App\Handler;

    class RefundHandler implements BatchHandlerInterface
    {
        public static function getTagAttributes(): array
        {
            return ['key' => 'refund'];
        }
    }

The same callable can be used with the ``#[Autoconfigure]`` attribute and with
the ``_instanceof`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _instanceof:
                App\Handler\BatchHandlerInterface:
                    tags:
                        - app.handler: [App\Handler\BatchHandlerInterface, getTagAttributes]

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Handler\BatchHandlerInterface;

        return App::config([
            'services' => [
                '_instanceof' => [
                    BatchHandlerInterface::class => [
                        'tags' => [
                            ['app.handler' => [BatchHandlerInterface::class, 'getTagAttributes']],
                        ],
                    ],
                ],
            ],
        ]);

On PHP 8.5 and later, where closures are allowed in attributes, you can pass a
closure receiving the concrete class-string instead of a static method::

    // src/Handler/BatchHandlerInterface.php

    // ...
    use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

    #[AutoconfigureTag('app.handler', attributes: static function (string $class): array {
        return ['key' => $class::getSupportedBatchAction()];
    })]
    interface BatchHandlerInterface
    {
        public static function getSupportedBatchAction(): string;
    }

Closures cannot be used in YAML files, so the static method callable is the
only form available in that format.

.. note::

    Tag attributes are computed when the container is compiled, once per
    concrete and instantiable class; abstract classes and interfaces are skipped.
    The callable must return an array; otherwise an exception is thrown.

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Handler\One;
        use App\Handler\Two;
        use App\HandlerCollection;

        return App::config([
            'services' => [
                One::class => [
                    'tags' => ['app.handler'],
                ],
                Two::class => [
                    'tags' => ['app.handler'],
                ],
                HandlerCollection::class => [
                    // inject all services tagged with app.handler as first argument
                    'arguments' => [tagged_iterator('app.handler')],
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Handler\Three as HandlerThree;
        use App\HandlerCollection;

        return App::config([
            'services' => [
                // This is the service we want to exclude, even if the 'app.handler' tag is attached
                HandlerThree::class => [
                    'tags' => ['app.handler'],
                ],
                HandlerCollection::class => [
                    // inject all services tagged with app.handler as first argument
                    'arguments' => [tagged_iterator('app.handler', exclude: [HandlerThree::class])],
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Handler\Three;
        use App\HandlerCollection;

        return App::config([
            'services' => [
                // This is the service we want to exclude, even if the 'app.handler' tag is attached
                Three::class => [
                    'tags' => ['app.handler'],
                ],
                HandlerCollection::class => [
                    // inject all services tagged with app.handler as first argument
                    'arguments' => [tagged_iterator('app.handler', exclude: [Three::class], excludeSelf: false)],
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Handler\One;

        return App::config([
            'services' => [
                One::class => [
                    'tags' => [
                        ['app.handler' => ['priority' => 20]],
                    ],
                ],
            ],
        ]);

.. deprecated:: 8.1

    The ``getDefaultPriority()`` method is deprecated since Symfony 8.1.
    Use the :ref:`#[AsTaggedItem] attribute <tags_as-tagged-item>` instead.

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

.. deprecated:: 8.1

    The ``default_priority_method`` option is deprecated since Symfony 8.1.
    Use the :ref:`#[AsTaggedItem] attribute <tags_as-tagged-item>` instead.

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\HandlerCollection;

        return App::config([
            'services' => [
                HandlerCollection::class => [
                    // inject all services tagged with app.handler as first argument
                    'arguments' => [tagged_iterator('app.handler', defaultPriorityMethod: 'getPriority')],
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Handler\One;
        use App\Handler\Two;
        use App\HandlerCollection;

        return App::config([
            'services' => [
                One::class => [
                    'tags' => [
                        ['app.handler' => ['key' => 'handler_one']],
                    ],
                ],
                Two::class => [
                    'tags' => [
                        ['app.handler' => ['key' => 'handler_two']],
                    ],
                ],
                HandlerCollection::class => [
                    // 2nd argument is the index attribute name
                    'arguments' => [tagged_iterator('app.handler', 'key')],
                ],
            ],
        ]);

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

.. deprecated:: 8.1

    The ``getDefault<CamelCase index_by value>Name()`` method convention is
    deprecated since Symfony 8.1. Use the
    :ref:`#[AsTaggedItem] attribute <tags_as-tagged-item>` instead.

The ``default_index_method`` Option
...................................

.. deprecated:: 8.1

    The ``default_index_method`` option is deprecated since Symfony 8.1.
    Use the :ref:`#[AsTaggedItem] attribute <tags_as-tagged-item>` instead.

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\HandlerCollection;

        return App::config([
            'services' => [
                HandlerCollection::class => [
                    'arguments' => [tagged_iterator('app.handler', defaultIndexMethod: 'getIndex')],
                ],
            ],
        ]);

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

.. _service-tags-resource-tags:
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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Model\Invoice;

        return App::config([
            'services' => [
                Invoice::class => [
                    'resource_tags' => [
                        ['app.report_item' => ['type' => 'invoice']],
                        'app.exportable',
                    ],
                ],
            ],
        ]);

.. tip::

    Resource tags can also be declared in the ``_defaults`` section to apply
    them to every service defined in the same file, using the same
    ``resource_tags`` option name in YAML and PHP.

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

.. _service-tags-resource-tags-class-map:

Injecting a Class Map of Tagged Classes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For the most common use case (injecting the map of tagged classes into a
service) you don't need a compiler pass: use the ``#[AutowireClassMap]``
attribute to inject an array that maps an index to each tagged class name::

    // src/Report/ReportGenerator.php
    namespace App\Report;

    use Symfony\Component\DependencyInjection\Attribute\AutowireClassMap;

    class ReportGenerator
    {
        public function __construct(
            // e.g. ['invoice' => Invoice::class, 'order' => Order::class]
            #[AutowireClassMap('app.report_item', indexAttribute: 'type')]
            private array $reportItemClasses,
        ) {
        }
    }

The ``indexAttribute`` argument defines which tag attribute provides the key
of each class in the map. When not passed, it defaults to the last dot-segment
of the tag name (``report_item`` for the ``app.report_item`` tag). Classes
whose tag doesn't define the index attribute are keyed by the index of their
``#[AsTaggedItem]`` attribute if they have one, or by their own fully-qualified
class name; a ``priority`` tag attribute (or the priority of ``#[AsTaggedItem]``)
decides which class wins when several of them share the same index. Use the
``exclude`` argument to leave some class names out of the map.

.. versionadded:: 8.2

    The ``#[AutowireClassMap]`` attribute was introduced in Symfony 8.2.

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
