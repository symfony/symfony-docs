Service Decoration
==================

Sometimes you need to change the behavior of an existing service without
modifying its original class; for example, because the class belongs to Symfony
or to a third-party package, or because you don't want to mix unrelated
features in the same class. **Service decoration** solves this problem: it
wraps the existing service inside a new service that adds the new behavior and
calls the original service when needed. This is an implementation of the
`Decorator pattern`_.

Decoration is different from redefining a service. When you redefine an existing
service definition, the original service is lost and you can't call it anymore.
When you decorate a service, the container replaces it with your own service but
keeps the original one available, so your decorator can still use it.

Decorating Services
-------------------

Suppose you need to log every email sent by the ``App\Mailer`` service. Adding
the logging code to the ``Mailer`` class is not always possible (e.g. when the
class belongs to a third-party package) or desirable (e.g. to keep the class
focused on sending emails). Instead, create an ``App\LoggingMailer`` service
that decorates it:

.. configuration-block::

    .. code-block:: php-attributes

        // src/LoggingMailer.php
        namespace App;

        // ...
        use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

        #[AsDecorator(decorates: Mailer::class)]
        class LoggingMailer
        {
            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer: ~

            App\LoggingMailer:
                # overrides the App\Mailer service
                decorates: App\Mailer

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\LoggingMailer;
        use App\Mailer;

        return App::config([
            'services' => [
                Mailer::class => null,

                LoggingMailer::class => [
                    // overrides the App\Mailer service
                    'decorates' => Mailer::class,
                ],
            ],
        ]);

From now on, any service that asks for ``App\Mailer`` receives an instance of
``App\LoggingMailer`` instead. You don't have to change anything in the
services that inject ``App\Mailer``: the container turns its ID into an alias
of the decorating service, and that alias keeps the same visibility
(:ref:`public or private <container-public>`) as the original ``App\Mailer``
service. The original service is not lost: it's still available so the
decorating service can inject and call it, as explained in the next section.

.. tip::

    You can apply multiple ``#[AsDecorator]`` attributes to the same class to
    decorate multiple services with it.

.. note::

    All custom :doc:`service tags </service_container/tags>` from the decorated
    service are removed and added to the new service. Only certain built-in service tags
    defined by Symfony are kept: ``container.service_locator``, ``container.service_subscriber``,
    ``kernel.event_subscriber``, ``kernel.event_listener``, ``kernel.locale_aware``,
    and ``kernel.reset``.

Accessing the Decorated Service
-------------------------------

Most decorators need to call the service they decorate to do their work. In
this example, ``LoggingMailer`` logs a message and then calls the original
``Mailer`` service to actually send the email. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
the decorated service is injected automatically when the constructor of the
decorating service has one argument type-hinted with the decorated service class.

If you are not using autowiring, or the decorating service has more than one
constructor argument type-hinted with the decorated service class, inject the
decorated service explicitly. Its ID is ``'.inner'`` in configuration files,
whereas in PHP classes you use the ``#[AutowireDecorated]`` attribute:

.. configuration-block::

    .. code-block:: php-attributes

        // src/LoggingMailer.php
        namespace App;

        // ...
        use Psr\Log\LoggerInterface;
        use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
        use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

        #[AsDecorator(decorates: Mailer::class)]
        class LoggingMailer
        {
            public function __construct(
                // unlike other configuration formats (that must use $inner as the name of the
                // decorated service) when using attributes you can choose any variable name
                #[AutowireDecorated] private Mailer $mailer,
                private LoggerInterface $logger,
            ) {
            }

            public function send(string $to, string $subject, string $body): void
            {
                $this->logger->info('Sending email to '.$to);

                $this->mailer->send($to, $subject, $body);
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer: ~

            App\LoggingMailer:
                decorates: App\Mailer
                # pass the old service as an argument
                arguments: ['@.inner']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\LoggingMailer;
        use App\Mailer;

        return App::config([
            'services' => [
                Mailer::class => null,

                LoggingMailer::class => [
                    'decorates' => Mailer::class,
                    // pass the old service as an argument
                    'arguments' => [service('.inner')],
                ],
            ],
        ]);

Behind the scenes, the original service keeps a real ID built as the decorating
service ID plus the ``.inner`` suffix (e.g. ``'App\LoggingMailer.inner'``).
In configuration files you can change that ID with the ``decoration_inner_name``
option:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\LoggingMailer:
                # ...
                # you can use any name here; if you decorate a lot of services,
                # consider adding the full original service ID as part of the new ID
                decoration_inner_name: 'App\Mailer.original'
                arguments: ['@App\Mailer.original']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\LoggingMailer;
        use App\Mailer;

        return App::config([
            'services' => [
                LoggingMailer::class => [
                    // ...
                    // you can use any name here; if you decorate a lot of services,
                    // consider adding the full original service ID as part of the new ID
                    'decoration_inner_name' => Mailer::class.'.original',
                    'arguments' => [service(Mailer::class.'.original')],
                ],
            ],
        ]);

When using the ``#[AutowireDecorated]`` attribute you don't need this option,
because you can name the constructor argument that holds the decorated service
however you like.

Decoration Priority
-------------------

You can apply several decorators to the same service. For example, in addition
to logging all emails with ``App\LoggingMailer``, you may need an
``App\RateLimitingMailer`` decorator that only sends emails while the sending
quota of your email provider is not exceeded. Use the ``decoration_priority`` option
to control the order in which decorators are applied. Its value is an integer
that defaults to ``0`` and higher priorities mean that decorators will be
applied earlier.

.. configuration-block::

    .. code-block:: php-attributes

        namespace App;

        // ...
        use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
        use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

        #[AsDecorator(decorates: Mailer::class, priority: 5)]
        class LoggingMailer
        {
            public function __construct(
                #[AutowireDecorated] private Mailer $mailer,
            ) {
            }

            // ...
        }

        #[AsDecorator(decorates: Mailer::class, priority: 1)]
        class RateLimitingMailer
        {
            public function __construct(
                // don't add the Mailer type to this argument: the injected service
                // is the next decorator in the chain (LoggingMailer in this example),
                // not the original Mailer; add a type only when the original service
                // and all its decorators implement the same interface
                #[AutowireDecorated] private $mailer,
            ) {
            }

            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer: ~

            App\LoggingMailer:
                decorates: App\Mailer
                decoration_priority: 5
                arguments: ['@.inner']

            App\RateLimitingMailer:
                decorates: App\Mailer
                decoration_priority: 1
                arguments: ['@.inner']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\LoggingMailer;
        use App\Mailer;
        use App\RateLimitingMailer;

        return App::config([
            'services' => [
                Mailer::class => null,

                LoggingMailer::class => [
                    'decorates' => Mailer::class,
                    'decoration_priority' => 5,
                    'arguments' => [service('.inner')],
                ],

                RateLimitingMailer::class => [
                    'decorates' => Mailer::class,
                    'decoration_priority' => 1,
                    'arguments' => [service('.inner')],
                ],
            ],
        ]);

In practice, the container now creates the ``App\Mailer`` service like this
(shown as simplified PHP code)::

    $mailer = new RateLimitingMailer(new LoggingMailer(new Mailer()));

Since higher priority decorators are applied earlier, they end up closer to the
original service. When using the decorated service, the outermost decorator
(i.e. the one with the lowest priority) runs first. In this example,
``RateLimitingMailer`` checks the sending quota before ``LoggingMailer`` logs
anything, so no email is logged unless it's actually sent.

.. _decoration-tagged-services:

Decorating All Services with a Tag
----------------------------------

.. versionadded:: 8.1

    The ``#[AsTagDecorator]`` attribute and the ``decorates_tag`` option were
    introduced in Symfony 8.1.

Instead of decorating a single service, you can decorate every service that has
a given tag. Symfony creates one decorator per tagged service, and each one
wraps its original service as ``.inner``.

For example, if your application defines several mailer services and all of them
are tagged with ``app.mailer``, you can log the emails sent by all of them with
a single decorator:

.. configuration-block::

    .. code-block:: php-attributes

        // src/LoggingMailer.php
        namespace App;

        // ...
        use Symfony\Component\DependencyInjection\Attribute\AsTagDecorator;

        #[AsTagDecorator(tag: 'app.mailer')]
        class LoggingMailer
        {
            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\LoggingMailer:
                decorates_tag: app.mailer

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\LoggingMailer;

        return App::config([
            'services' => [
                LoggingMailer::class => [
                    'decorates_tag' => 'app.mailer',
                ],
            ],
        ]);

Stacking Decorators
-------------------

An alternative to decoration priorities is to define a **stack**: a list of
ordered services where each one decorates the next one. Both approaches create
the same nested objects, but they work differently:

* When using ``decorates``, the container replaces the original service
  everywhere it's injected. By default, a ``stack`` doesn't replace any service:
  it defines a new service (``mailer_stack`` in this example) and only the
  services injecting it explicitly get the decorated behavior (although stacks
  can also decorate existing services, as explained later in this section);
* When using priorities, each decorator is defined independently, maybe even in
  different files or bundles, and the final order depends on all the priority
  values. In a ``stack``, you define the whole chain in a single place, in the
  exact order in which decorators are applied;
* The same classes can be included in different stacks, allowing you to create
  several variants of the same decorated service.

This is how the example of the previous section looks when defined as a ``stack``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            mailer_stack:
                stack:
                    - class: App\RateLimitingMailer
                      arguments: ['@.inner']
                    - class: App\LoggingMailer
                      arguments: ['@.inner']
                    - class: App\Mailer

            # alternatively, use this short syntax, where each frame is a class
            # name followed by its arguments; thanks to autowiring, you can even
            # omit the arguments:
            # mailer_stack:
            #     stack:
            #         - App\RateLimitingMailer: ~
            #         - App\LoggingMailer: ~
            #         - App\Mailer: ~

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\LoggingMailer;
        use App\Mailer;
        use App\RateLimitingMailer;

        return App::config([
            'services' => [
                'mailer_stack' => [
                    'stack' => [
                        ['class' => RateLimitingMailer::class, 'arguments' => [service('.inner')]],
                        ['class' => LoggingMailer::class, 'arguments' => [service('.inner')]],
                        ['class' => Mailer::class],
                    ],
                ],

                // alternatively, use this short syntax, where each frame is a class
                // name followed by its arguments; thanks to autowiring, you can even
                // omit the arguments:
                // 'mailer_stack' => [
                //     'stack' => [
                //         [RateLimitingMailer::class => null],
                //         [LoggingMailer::class => null],
                //         [Mailer::class => null],
                //     ],
                // ],
            ],
        ]);

The ``mailer_stack`` service created by the container is the same nested object
as in the previous section::

    $mailer = new RateLimitingMailer(new LoggingMailer(new Mailer()));

Each frame of the ``stack`` can be either an :ref:`inlined service <anonymous-services>`,
a :ref:`reference to an existing service <services-constructor-injection>` or a
:ref:`child definition <parent-services>`. The latter allows embedding ``stack``
definitions into each others.

For example, imagine that legal regulations require your application to audit
all sent emails and to add a legal disclaimer to their contents. You can define
that compliance behavior as its own stack and embed it into the mailer stack:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            audit_mailer:
                class: App\AuditMailer

            mailer_compliance_stack:
                stack:
                    - alias: audit_mailer
                    - App\DisclaimerMailer: ~

            mailer_stack:
                stack:
                    - parent: mailer_compliance_stack
                    - App\RateLimitingMailer: ~
                    - App\LoggingMailer: ~
                    - App\Mailer: ~

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\AuditMailer;
        use App\DisclaimerMailer;
        use App\LoggingMailer;
        use App\Mailer;
        use App\RateLimitingMailer;

        return App::config([
            'services' => [
                'audit_mailer' => [
                    'class' => AuditMailer::class,
                ],

                'mailer_compliance_stack' => [
                    'stack' => [
                        ['alias' => 'audit_mailer'],
                        [DisclaimerMailer::class => null],
                    ],
                ],

                'mailer_stack' => [
                    'stack' => [
                        ['parent' => 'mailer_compliance_stack'],
                        [RateLimitingMailer::class => null],
                        [LoggingMailer::class => null],
                        [Mailer::class => null],
                    ],
                ],
            ],
        ]);

The result will be::

    $mailer = new AuditMailer(new DisclaimerMailer(
        new RateLimitingMailer(new LoggingMailer(new Mailer()))
    ));

.. note::

    To change existing stacks (i.e. from a :doc:`compiler pass </service_container/compiler_passes>`),
    you can access each frame by its generated id with the following structure:
    ``.stack_id.frame_key``. From the example above, ``.mailer_stack.1`` would
    be a reference to the inlined ``App\RateLimitingMailer`` service and
    ``.mailer_stack.0`` to the embedded stack. To get more explicit ids, you
    can give a name to each frame:

    .. configuration-block::

        .. code-block:: yaml

            # ...
            mailer_stack:
                stack:
                    first:
                        parent: mailer_compliance_stack
                    second:
                        App\RateLimitingMailer: ~
                    # ...

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use App\RateLimitingMailer;

            return App::config([
                'services' => [
                    'mailer_stack' => [
                        'stack' => [
                            'first' => ['parent' => 'mailer_compliance_stack'],
                            'second' => [RateLimitingMailer::class => null],
                            // ...
                        ],
                    ],
                ],
            ]);

The ``App\RateLimitingMailer`` frame id will now be ``.mailer_stack.second``.

Using Stacks to Decorate Existing Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 8.1

    The ``decorates`` and ``decorates_tag`` options on stacks were introduced
    in Symfony 8.1.

By default, stacks define a self-contained chain of services. But you can also
use a stack to decorate an existing service by adding the ``decorates`` option.
The innermost frame of the stack wraps the original service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            mailer_stack:
                decorates: App\Mailer
                stack:
                    - class: App\RateLimitingMailer
                      arguments: ['@.inner']
                    - class: App\LoggingMailer
                      arguments: ['@.inner']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\LoggingMailer;
        use App\Mailer;
        use App\RateLimitingMailer;

        return App::config([
            'services' => [
                'mailer_stack' => [
                    'decorates' => Mailer::class,
                    'stack' => [
                        ['class' => RateLimitingMailer::class, 'arguments' => [service('.inner')]],
                        ['class' => LoggingMailer::class, 'arguments' => [service('.inner')]],
                    ],
                ],
            ],
        ]);

Unlike the previous examples, the stack doesn't define the ``App\Mailer`` frame
itself. The container injects the original service instead::

    $mailer = new RateLimitingMailer(new LoggingMailer($originalMailer));

You can also use the ``decorates_tag`` option to decorate all the services
tagged with a specific tag. The stack is cloned once per tagged service and
each clone decorates one of the tagged services:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            mailer_stack:
                decorates_tag: app.mailer
                stack:
                    - class: App\RateLimitingMailer
                      arguments: ['@.inner']
                    - class: App\LoggingMailer
                      arguments: ['@.inner']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\LoggingMailer;
        use App\RateLimitingMailer;

        return App::config([
            'services' => [
                'mailer_stack' => [
                    'decorates_tag' => 'app.mailer',
                    'stack' => [
                        ['class' => RateLimitingMailer::class, 'arguments' => [service('.inner')]],
                        ['class' => LoggingMailer::class, 'arguments' => [service('.inner')]],
                    ],
                ],
            ],
        ]);

.. note::

    The ``decoration_inner_name``, ``decoration_priority`` and
    ``decoration_on_invalid`` options can also be used on stacks, as with
    regular decorated services.

Control the Behavior When the Decorated Service Does Not Exist
--------------------------------------------------------------

When you decorate a service that doesn't exist, the ``decoration_on_invalid``
option allows you to choose the behavior to adopt.

Three different behaviors are available:

* ``exception``: (default) A ``ServiceNotFoundException`` will be thrown telling
  that decorator's dependency is missing.
* ``ignore``: The container will remove the decorator.
* ``null``: The container will keep the decorator service and will set the
  decorated one to ``null``; declare the constructor argument that receives
  the decorated service as nullable (e.g. ``private ?Mailer $mailer``) and
  check for ``null`` before calling it.

.. configuration-block::

    .. code-block:: php-attributes

        // src/LoggingMailer.php
        namespace App;

        // ...
        use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
        use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
        use Symfony\Component\DependencyInjection\ContainerInterface;

        #[AsDecorator(decorates: Mailer::class, onInvalid: ContainerInterface::IGNORE_ON_INVALID_REFERENCE)]
        class LoggingMailer
        {
            public function __construct(
                #[AutowireDecorated] private $mailer,
            ) {
            }

            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer: ~

            App\LoggingMailer:
                decorates: App\Mailer
                decoration_on_invalid: ignore
                arguments: ['@.inner']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\LoggingMailer;
        use App\Mailer;

        return App::config([
            'services' => [
                Mailer::class => null,

                LoggingMailer::class => [
                    'decorates' => Mailer::class,
                    'decoration_on_invalid' => 'ignore',
                    'arguments' => [service('.inner')],
                ],
            ],
        ]);

.. note::

    Sometimes, you may want to add a compiler pass that creates service
    definitions dynamically. If you want to decorate such a service,
    be sure that your compiler pass is registered with ``PassConfig::TYPE_BEFORE_OPTIMIZATION``
    type so that the decoration pass will be able to find the created services.

.. _`Decorator pattern`: https://en.wikipedia.org/wiki/Decorator_pattern
