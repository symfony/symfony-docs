How to Decorate Services
========================

When overriding an existing definition, the original service is lost:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer: ~

            # this replaces the old App\Mailer definition with the new one, the
            # old definition is lost
            App\Mailer:
                class: App\NewMailer

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mailer;
        use App\NewMailer;

        return App::config([
            'services' => [
                Mailer::class => null,

                // this replaces the old App\Mailer definition with the new one, the
                // old definition is lost
                Mailer::class => [
                    'class' => NewMailer::class,
                ],
            ],
        ]);

Most of the time, that's exactly what you want to do. But sometimes,
you might want to decorate the old one instead (i.e. apply the `Decorator pattern`_).
In this case, the old service should be kept around to be able to reference
it in the new one. This configuration replaces ``App\Mailer`` with a new one,
but keeps a reference of the old one as ``.inner``:

.. configuration-block::

    .. code-block:: php-attributes

        // src/DecoratingMailer.php
        namespace App;

        // ...
        use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

        #[AsDecorator(decorates: Mailer::class)]
        class DecoratingMailer
        {
            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer: ~

            App\DecoratingMailer:
                # overrides the App\Mailer service
                # but that service is still available as ".inner"
                decorates: App\Mailer

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\DecoratingMailer;
        use App\Mailer;

        return App::config([
            'services' => [
                Mailer::class => null,
                DecoratingMailer::class => [
                    // overrides the App\Mailer service
                    // but that service is still available as ".inner"
                    'decorates' => Mailer::class,
                ],
            ],
        ]);

.. tip::

    You can apply multiple ``#[AsDecorator]`` attributes to the same class to
    decorate multiple services with it.

The ``decorates`` option tells the container that the ``App\DecoratingMailer``
service replaces the ``App\Mailer`` service. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
the decorated service is automatically injected when the constructor of the
decorating service has one argument type-hinted with the decorated service class.

If you are not using autowiring or the decorating service has more than one
constructor argument type-hinted with the decorated service class, you must
inject the decorated service explicitly (the ID of the decorated service is
automatically changed to ``'.inner'``):

.. configuration-block::

    .. code-block:: php-attributes

        // src/DecoratingMailer.php
        namespace App;

        // ...
        use App\Mailer;
        use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
        use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

        #[AsDecorator(decorates: Mailer::class)]
        class DecoratingMailer
        {
            public function __construct(
                // unlike other configuration formats, that name the decorated service argument
                // as `$inner` by default, when using attributes you can choose any variable name
                #[AutowireDecorated] private Mailer $mailer,
            ) {
            }

            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer: ~

            App\DecoratingMailer:
                decorates: App\Mailer
                # pass the old service as an argument
                arguments: ['@.inner']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\DecoratingMailer;
        use App\Mailer;

        return App::config([
            'services' => [
                Mailer::class => null,
                DecoratingMailer::class => [
                    'decorates' => Mailer::class,
                    // pass the old service as an argument
                    'arguments' => [service('.inner')],
                ],
            ],
        ]);

When decorating a service, the original service (e.g. ``App\Mailer``) is available
inside the decorating service (e.g. ``App\DecoratingMailer``) using an ID constructed
as "Decorating service ID" + the ``.inner`` suffix (e.g. in the previous example,
the ID is ``'App\DecoratingMailer.inner'``). You can control the inner service
name via the ``decoration_inner_name`` option:

.. configuration-block::

    .. code-block:: php-attributes

        // when using the #[AutowireDecorated] attribute, you can name the argument
        // that holds the decorated service however you like, without needing to
        // configure that name explicitly
        #[AutowireDecorated] private Mailer $originalMailer,

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\DecoratingMailer:
                # ...
                decoration_inner_name: 'original_mailer'
                arguments: ['@original_mailer']

                # if you decorate a lot of services, consider adding the full
                # original service ID as part of the new ID
                decoration_inner_name: 'App\Mailer.original'
                arguments: ['@App\Mailer.original']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\DecoratingMailer;
        use App\Mailer;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(Mailer::class);

            $services->set(DecoratingMailer::class)
                ->decorate(Mailer::class, 'original_mailer')
                ->args([service('original_mailer')]);

            // if you decorate a lot of services, consider adding the full
            // original service ID as part of the new ID
            $services->set(DecoratingMailer::class)
                ->decorate(Mailer::class, DecoratingMailer::class.'.original')
                ->args([service(DecoratingMailer::class.'.original')]);
        };

.. note::

    The visibility of the decorated ``App\Mailer`` service (which is an alias
    for the new service) will still be the same as the original ``App\Mailer``
    visibility.

.. note::

    All custom :doc:`service tags </service_container/tags>` from the decorated
    service are removed and added to the new service. Only certain built-in service tags
    defined by Symfony are kept: ``container.service_locator``, ``container.service_subscriber``,
    ``kernel.event_subscriber``, ``kernel.event_listener``, ``kernel.locale_aware``,
    and ``kernel.reset``.

.. note::

    The generated inner id is based on the id of the decorator service
    (``App\DecoratingMailer`` here), not of the decorated service (``App\Mailer``
    here). You can control the inner service name via the ``decoration_inner_name``
    option:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            services:
                App\DecoratingMailer:
                    # ...
                    decoration_inner_name: App\DecoratingMailer.wooz
                    arguments: ['@App\DecoratingMailer.wooz']

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use App\DecoratingMailer;
            use App\Mailer;

            return App::config([
                'services' => [
                    Mailer::class => null,
                    DecoratingMailer::class => [
                        'decorates' => Mailer::class,
                        'decoration_inner_name' => DecoratingMailer::class.'.wooz',
                        'arguments' => [service(DecoratingMailer::class.'.wooz')],
                    ],
                ],
            ]);

Decoration Priority
-------------------

When applying multiple decorators to a service, you can control their order with
the ``decoration_priority`` option. Its value is an integer that defaults to
``0`` and higher priorities mean that decorators will be applied earlier.

.. configuration-block::

    .. code-block:: php-attributes

        // ...
        use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
        use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

        #[AsDecorator(decorates: Foo::class, priority: 5)]
        class Bar
        {
            public function __construct(
                #[AutowireDecorated]
                private $inner,
            ) {
            }
            // ...
        }

        #[AsDecorator(decorates: Foo::class, priority: 1)]
        class Baz
        {
            public function __construct(
                #[AutowireDecorated]
                private $inner,
            ) {
            }

            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            Foo: ~

            Bar:
                decorates: Foo
                decoration_priority: 5
                arguments: ['@.inner']

            Baz:
                decorates: Foo
                decoration_priority: 1
                arguments: ['@.inner']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Bar;
        use App\Baz;
        use App\Foo;

        return App::config([
            'services' => [
                Foo::class => null,
                Bar::class => [
                    'decorates' => Foo::class,
                    'decoration_priority' => 5,
                    'arguments' => [service('.inner')],
                ],
                Baz::class => [
                    'decorates' => Foo::class,
                    'decoration_priority' => 1,
                    'arguments' => [service('.inner')],
                ],
            ],
        ]);

The generated code will be the following::

    $this->services[Foo::class] = new Baz(new Bar(new Foo()));

Decorating All Services with a Tag
----------------------------------

.. versionadded:: 8.1

    The ``#[AsTagDecorator]`` attribute and the ``decorates_tag`` option were
    introduced in Symfony 8.1.

Instead of decorating a single service, you can decorate every service that
has a given tag. Symfony creates one decorator per tagged service, each
wrapping the original service as ``.inner``:

.. configuration-block::

    .. code-block:: php-attributes

        // src/LoggingDecorator.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\AsTagDecorator;

        #[AsTagDecorator(tag: 'app.processor')]
        class LoggingDecorator
        {
            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\LoggingDecorator:
                decorates_tag: app.processor

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\LoggingDecorator;

        return App::config([
            'services' => [
                LoggingDecorator::class => [
                    'decorates_tag' => 'app.processor',
                ],
            ],
        ]);

Stacking Decorators
-------------------

An alternative to using decoration priorities is to create a ``stack`` of
ordered services, each one decorating the next:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            decorated_foo_stack:
                stack:
                    - class: Baz
                      arguments: ['@.inner']
                    - class: Bar
                      arguments: ['@.inner']
                    - class: Foo

            # using the short syntax:
            decorated_foo_stack:
                stack:
                    - Baz: ['@.inner']
                    - Bar: ['@.inner']
                    - Foo: ~

            # can be simplified when autowiring is enabled:
            decorated_foo_stack:
                stack:
                    - Baz: ~
                    - Bar: ~
                    - Foo: ~

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Bar;
        use App\Baz;
        use App\Foo;

        return App::config([
            'services' => [
                'decorated_foo_stack' => [
                    'stack' => [
                        ['class' => Baz::class, 'arguments' => [service('.inner')]],
                        ['class' => Bar::class, 'arguments' => [service('.inner')]],
                        ['class' => Foo::class],
                    ],
                ],
                // using the short syntax:
                'decorated_foo_stack' => [
                    'stack' => [
                        [Baz::class => ['arguments' => [service('.inner')]]],
                        [Bar::class => ['arguments' => [service('.inner')]]],
                        [Foo::class => null],
                    ],
                ],
                // can be simplified when autowiring is enabled:
                'decorated_foo_stack' => [
                    'stack' => [
                        [Baz::class => null],
                        [Bar::class => null],
                        [Foo::class => null],
                    ],
                ],
            ],
        ]);

The result will be the same as in the previous section::

    $this->services['decorated_foo_stack'] = new Baz(new Bar(new Foo()));

Each frame of the ``stack`` can be either an inlined service, a reference or a
child definition.
The latter allows embedding ``stack`` definitions into each others, here's an
advanced example of composition:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            some_decorator:
                class: App\Decorator

            embedded_stack:
                stack:
                    - alias: some_decorator
                    - App\Decorated: ~

            decorated_foo_stack:
                stack:
                    - parent: embedded_stack
                    - Baz: ~
                    - Bar: ~
                    - Foo: ~

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Bar;
        use App\Baz;
        use App\Decorated;
        use App\Decorator;
        use App\Foo;

        return App::config([
            'services' => [
                'some_decorator' => [
                    'class' => Decorator::class,
                ],
                'embedded_stack' => [
                    'stack' => [
                        ['alias' => 'some_decorator'],
                        [Decorated::class => null],
                    ],
                ],
                'decorated_foo_stack' => [
                    'stack' => [
                        ['parent' => 'embedded_stack'],
                        [Baz::class => null],
                        [Bar::class => null],
                        [Foo::class => null],
                    ],
                ],
            ],
        ]);

The result will be::

    $this->services['decorated_foo_stack'] = new App\Decorator(new App\Decorated(new Baz(new Bar(new Foo()))));

.. note::

    To change existing stacks (i.e. from a compiler pass), you can access each
    frame by its generated id with the following structure:
    ``.stack_id.frame_key``.
    From the example above, ``.decorated_foo_stack.1`` would be a reference to
    the inlined ``Baz`` service and ``.decorated_foo_stack.0`` to the embedded
    stack.
    To get more explicit ids, you can give a name to each frame:

    .. configuration-block::

        .. code-block:: yaml

            # ...
            decorated_foo_stack:
                stack:
                    first:
                        parent: embedded_stack
                    second:
                        Baz: ~
                    # ...

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use App\Baz;

            return App::config([
                'services' => [
                    'decorated_foo_stack' => [
                        'stack' => [
                            'first' => ['parent' => 'embedded_stack'],
                            'second' => [Baz::class => null],
                        ],
                    ],
                ],
            ]);

    The ``Baz`` frame id will now be ``.decorated_foo_stack.second``.

Using Stacks to Decorate Existing Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 8.1

    The ``decorates`` and ``decorates_tag`` options on stacks were introduced
    in Symfony 8.1.

By default, stacks define a self-contained chain of services. But you can also
use a stack to decorate an existing service by adding the ``decorates`` option.
The innermost frame of the stack will wrap the original service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            my_stack:
                decorates: api_platform.serializer.context_builder
                stack:
                    - class: App\Decorator\AddGroupsContextBuilder
                      arguments: ['@.inner']
                    - class: App\Decorator\AddFiltersContextBuilder
                      arguments: ['@.inner']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Decorator\AddFiltersContextBuilder;
        use App\Decorator\AddGroupsContextBuilder;

        return App::config([
            'services' => [
                'my_stack' => [
                    'decorates' => 'api_platform.serializer.context_builder',
                    'stack' => [
                        ['class' => AddGroupsContextBuilder::class, 'arguments' => [service('.inner')]],
                        ['class' => AddFiltersContextBuilder::class, 'arguments' => [service('.inner')]],
                    ],
                ],
            ],
        ]);

The result will be::

    $this->services['api_platform.serializer.context_builder'] = new AddGroupsContextBuilder(
        new AddFiltersContextBuilder(
            $originalContextBuilder
        )
    );

You can also use the ``decorates_tag`` option to decorate all services tagged
with a specific tag. The stack is cloned once per tagged service, each clone
decorating one of the tagged services:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            my_stack:
                decorates_tag: my_tag
                stack:
                    - class: App\Decorator\LoggingDecorator
                      arguments: ['@.inner']
                    - class: App\Decorator\CachingDecorator
                      arguments: ['@.inner']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Decorator\CachingDecorator;
        use App\Decorator\LoggingDecorator;

        return App::config([
            'services' => [
                'my_stack' => [
                    'decorates_tag' => 'my_tag',
                    'stack' => [
                        ['class' => LoggingDecorator::class, 'arguments' => [service('.inner')]],
                        ['class' => CachingDecorator::class, 'arguments' => [service('.inner')]],
                    ],
                ],
            ],
        ]);

.. note::

    The ``decoration_inner_name``, ``decoration_priority``, and
    ``decoration_on_invalid`` options can also be used on stacks, as with
    regular decorated services.

Control the Behavior When the Decorated Service Does Not Exist
--------------------------------------------------------------

When you decorate a service that doesn't exist, the ``decoration_on_invalid``
option allows you to choose the behavior to adopt.

Three different behaviors are available:

* ``exception``: A ``ServiceNotFoundException`` will be thrown telling that decorator's dependency is missing. (default)
* ``ignore``: The container will remove the decorator.
* ``null``: The container will keep the decorator service and will set the decorated one to ``null``.

.. configuration-block::

    .. code-block:: php-attributes

        // ...
        use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
        use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
        use Symfony\Component\DependencyInjection\ContainerInterface;

        #[AsDecorator(decorates: Mailer::class, onInvalid: ContainerInterface::IGNORE_ON_INVALID_REFERENCE)]
        class Bar
        {
            public function __construct(
                #[AutowireDecorated] private $inner,
            ) {
            }

            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            Foo: ~

            Bar:
                decorates: Foo
                decoration_on_invalid: ignore
                arguments: ['@.inner']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Bar;
        use App\Foo;

        return App::config([
            'services' => [
                Foo::class => null,
                Bar::class => [
                    'decorates' => Foo::class,
                    'decoration_on_invalid' => 'ignore',
                    'arguments' => [service('.inner')],
                ],
            ],
        ]);

.. warning::

    When using ``null``, you may have to update the decorator constructor in
    order to make decorated dependency nullable::

        // src/Service/DecoratorService.php
        namespace App\Service;

        use Acme\OptionalBundle\Service\OptionalService;

        class DecoratorService
        {
            public function __construct(
                private ?OptionalService $decorated,
            ) {
            }

            public function tellInterestingStuff(): string
            {
                if (!$this->decorated) {
                    return 'Just one interesting thing';
                }

                return $this->decorated->tellInterestingStuff().' + one more interesting thing';
            }
        }

.. note::

    Sometimes, you may want to add a compiler pass that creates service
    definitions dynamically. If you want to decorate such a service,
    be sure that your compiler pass is registered with ``PassConfig::TYPE_BEFORE_OPTIMIZATION``
    type so that the decoration pass will be able to find the created services.

.. _`Decorator pattern`: https://en.wikipedia.org/wiki/Decorator_pattern
