Defining Service Dependencies Automatically (Autowiring)
========================================================

Autowiring allows you to manage services in the container with minimal
configuration. It reads the type-hints on your constructor (or other methods)
and automatically passes the correct services to each method. Symfony's
autowiring is designed to be predictable: if it is not absolutely clear which
dependency should be passed, you'll see an actionable exception.

.. tip::

    Thanks to Symfony's compiled container, there is no runtime overhead for using
    autowiring.

An Autowiring Example
---------------------

Imagine you're building an application that shows random happy messages to
users and that these messages are formatted before displaying them.

Start by creating a formatter class::

    // src/Formatter/TextFormatter.php
    namespace App\Formatter;

    class TextFormatter
    {
        public function format(string $message): string
        {
            return wordwrap($message, 70);
        }
    }

And now a message generator using this formatter::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use App\Formatter\TextFormatter;

    class MessageGenerator
    {
        public function __construct(
            private TextFormatter $formatter,
        ) {
        }

        public function getHappyMessage(): string
        {
            $messages = [
                // ...
            ];

            return $this->formatter->format($messages[array_rand($messages)]);
        }
    }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
**both classes are automatically registered as services and configured to be autowired**.
This means you can use them immediately without *any* configuration.

However, to understand autowiring better, the following examples explicitly configure
both services:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                autowire: true
                autoconfigure: true
            # ...

            App\Service\MessageGenerator:
                # redundant thanks to _defaults, but value is overridable on each service
                autowire: true

            App\Formatter\TextFormatter:
                autowire: true

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <defaults autowire="true" autoconfigure="true"/>
                <!-- ... -->

                <!-- autowire is redundant thanks to defaults, but value is overridable on each service -->
                <service id="App\Service\MessageGenerator" autowire="true"/>

                <service id="App\Formatter\TextFormatter" autowire="true"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Formatter\TextFormatter;
        use App\Service\MessageGenerator;

        return function(ContainerConfigurator $container): void {
            $services = $container->services()
                ->defaults()
                    ->autowire()
                    ->autoconfigure()
            ;

            $services->set(MessageGenerator::class)
                // redundant thanks to defaults, but value is overridable on each service
                ->autowire();

            $services->set(TextFormatter::class)
                ->autowire();
        };

Now, you can use the ``MessageGenerator`` service immediately in a controller::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use App\Service\MessageGenerator;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;

    class DefaultController extends AbstractController
    {
        #[Route('/message')]
        public function message(MessageGenerator $messageGenerator): Response
        {
            $message = $messageGenerator->getHappyMessage();

            // ...
        }
    }

This works automatically! The container knows to pass the ``TextFormatter`` service
as the first argument when creating the ``MessageGenerator`` service.

.. note::

    In this example, the ``MessageGenerator`` service is injected as an
    argument of the controller *action method*. This is a special convenience
    feature of the framework, **available only in controllers**: in all other
    services, dependencies can only be autowired in the constructor and in the
    :ref:`methods and properties marked with #[Required] <autowiring-calls>`.
    See :ref:`controller-accessing-services` for more details.

.. _autowiring-logic-explained:

Autowiring Logic Explained
--------------------------

Autowiring works by reading the ``TextFormatter`` *type-hint* in ``MessageGenerator``::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use App\Formatter\TextFormatter;

    class MessageGenerator
    {
        // ...

        public function __construct(
            private TextFormatter $formatter,
        ) {
        }
    }

The autowiring system **looks for a service whose id exactly matches the type-hint**:
so ``App\Formatter\TextFormatter``. In this case, that exists! When you configured
the ``TextFormatter`` service, you used its fully-qualified class name as its
id. Autowiring isn't magic: it looks for a service whose id matches the type-hint.
If you :ref:`load services automatically <service-container-services-load-example>`,
each service's id is its class name.

If there is *not* a service whose id exactly matches the type, a clear exception
will be thrown.

Autowiring is a great way to automate configuration, and Symfony tries to be as
*predictable* and as clear as possible.

.. _service-autowiring-alias:
.. _services-alias:

Using Aliases to Enable Autowiring
----------------------------------

The main way to configure autowiring is to create a service whose id exactly matches
its class. In the previous example, the service's id is ``App\Formatter\TextFormatter``,
which allows to autowire this type automatically.

This can also be accomplished using an **alias**: a definition that gives an
additional name to an existing service. Suppose that for some reason, the id of
the service was instead ``app.text_formatter``. In this case, any arguments
type-hinted with the class name (``App\Formatter\TextFormatter``) can no longer
be autowired.

No problem! To fix this, you can *create* a service whose id matches the class by
adding a service alias:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            # the id is not a class, so it won't be used for autowiring
            app.text_formatter:
                class: App\Formatter\TextFormatter
                # ...

            # but this fixes it!
            # the "app.text_formatter" service will be injected when
            # an App\Formatter\TextFormatter type-hint is detected
            App\Formatter\TextFormatter: '@app.text_formatter'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="app.text_formatter" class="App\Formatter\TextFormatter" autowire="true"/>
                <service id="App\Formatter\TextFormatter" alias="app.text_formatter"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Formatter\TextFormatter;

        return function(ContainerConfigurator $container): void {
            // ...

            // the id is not a class, so it won't be used for autowiring
            $services->set('app.text_formatter', TextFormatter::class)
                ->autowire();

            // but this fixes it!
            // the "app.text_formatter" service will be injected when
            // an App\Formatter\TextFormatter type-hint is detected
            $services->alias(TextFormatter::class, 'app.text_formatter');
        };

This creates a service "alias", whose id is ``App\Formatter\TextFormatter``.
Thanks to this, autowiring sees this and uses it whenever the ``TextFormatter``
class is type-hinted.

.. tip::

    Aliases are used by the core bundles to allow services to be autowired. For
    example, MonologBundle creates a service whose id is ``logger``. But it also
    adds an alias: ``Psr\Log\LoggerInterface`` that points to the ``logger`` service.
    This is why arguments type-hinted with ``Psr\Log\LoggerInterface`` can be autowired.

.. tip::

    In YAML, you can also use a shortcut to alias a service:

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...
            app.text_formatter: '@App\Formatter\TextFormatter'

The ``#[AsAlias]`` Attribute
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of defining the alias in a configuration file, you can use the
``#[AsAlias]`` attribute on the class of the service::

    // src/Formatter/TextFormatter.php
    namespace App\Formatter;

    use Symfony\Component\DependencyInjection\Attribute\AsAlias;

    #[AsAlias('app.text_formatter')]
    class TextFormatter
    {
        // ...
    }

The attribute also allows you to make the alias :ref:`public <container-public>`
by setting its ``public`` option to ``true``.

.. tip::

    When using the ``#[AsAlias]`` attribute, you may omit passing the ``id``
    argument if the class implements exactly one interface. In that case, the
    interface name becomes the alias of the service::

        // src/Formatter/TextFormatter.php
        namespace App\Formatter;

        use Symfony\Component\DependencyInjection\Attribute\AsAlias;

        // this defines FormatterInterface as an alias of this service
        #[AsAlias]
        class TextFormatter implements FormatterInterface
        {
            // ...
        }

The ``#[AsAlias]`` attribute can also be limited to one or more specific
:ref:`config environments <configuration-environments>` using the ``when`` argument::

    // src/Formatter/TextFormatter.php
    namespace App\Formatter;

    // ...
    use Symfony\Component\DependencyInjection\Attribute\AsAlias;

    #[AsAlias(id: 'app.text_formatter', when: 'dev')]
    class TextFormatter
    {
        // ...
    }

    // pass an array to apply it in multiple config environments
    #[AsAlias(id: 'app.text_formatter', when: ['dev', 'test'])]
    class TextFormatter
    {
        // ...
    }

.. versionadded:: 7.3

    The ``when`` argument of the ``#[AsAlias]`` attribute was introduced in Symfony 7.3.

.. tip::

    Aliases can be deprecated to warn about their removal in future versions.
    See :ref:`how to deprecate services and aliases <deprecating-service-definitions>`.

.. _autowiring-interface-alias:

Working with Interfaces
-----------------------

You might also find yourself type-hinting abstractions (e.g. interfaces) instead
of concrete classes as it replaces your dependencies with other objects.
To follow this best practice, suppose you decide to create a ``FormatterInterface``::

    // src/Formatter/FormatterInterface.php
    namespace App\Formatter;

    interface FormatterInterface
    {
        public function format(string $message): string;
    }

Then, you update ``TextFormatter`` to implement it::

    // ...
    class TextFormatter implements FormatterInterface
    {
        // ...
    }

Now that you have an interface, you should use this as your type-hint::

    class MessageGenerator
    {
        public function __construct(
            private FormatterInterface $formatter,
        ) {
            // ...
        }

        // ...
    }

But now, the type-hint (``App\Formatter\FormatterInterface``) no longer matches
the id of the service (``App\Formatter\TextFormatter``). This means that the
argument can no longer be autowired.

To fix that, add an :ref:`alias <service-autowiring-alias>`:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Formatter\TextFormatter: ~

            # the App\Formatter\TextFormatter service will be injected when
            # an App\Formatter\FormatterInterface type-hint is detected
            App\Formatter\FormatterInterface: '@App\Formatter\TextFormatter'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->
                <service id="App\Formatter\TextFormatter"/>

                <service id="App\Formatter\FormatterInterface" alias="App\Formatter\TextFormatter"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Formatter\FormatterInterface;
        use App\Formatter\TextFormatter;

        return function(ContainerConfigurator $container): void {
            // ...

            $services->set(TextFormatter::class);

            // the App\Formatter\TextFormatter service will be injected when
            // an App\Formatter\FormatterInterface type-hint is detected
            $services->alias(FormatterInterface::class, TextFormatter::class);
        };

Thanks to the ``App\Formatter\FormatterInterface`` alias, the autowiring subsystem
knows that the ``App\Formatter\TextFormatter`` service should be injected when
dealing with the ``FormatterInterface``.

.. tip::

    When :ref:`loading services automatically with resource <service-psr4-loader>`,
    if only one service is discovered that implements an interface, configuring
    the alias is not mandatory and Symfony will automatically create one.

.. tip::

    Autowiring is powerful enough to guess which service to inject even when using
    union and intersection types. This means you're able to type-hint argument with
    complex types like this::

        use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
        use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
        use Symfony\Component\Serializer\SerializerInterface;

        class DataFormatter
        {
            public function __construct(
                private (NormalizerInterface&DenormalizerInterface)|SerializerInterface $transformer,
            ) {
                // ...
            }

            // ...
        }

.. _autowiring-multiple-implementations-same-type:

Dealing with Multiple Implementations of the Same Type
------------------------------------------------------

Suppose you create a second class called ``HtmlFormatter`` that implements
``FormatterInterface``::

    // src/Formatter/HtmlFormatter.php
    namespace App\Formatter;

    class HtmlFormatter implements FormatterInterface
    {
        public function format(string $message): string
        {
            return sprintf('<p>%s</p>', $message);
        }
    }

If you register this as a service, you now have two services that implement the
``App\Formatter\FormatterInterface`` type. The autowiring subsystem can not decide
which one to use. Remember, autowiring isn't magic; it looks for a service
whose id matches the type-hint. So you need to choose one by :ref:`creating an alias
<autowiring-interface-alias>` from the type to the correct service id. That
alias defines the *default* implementation: the one injected whenever the
interface is type-hinted.

.. _autowiring-alias:

For the rest of the implementations, define a **named autowiring alias**: an
alias whose id is a special string containing the interface followed by a name
of your choice, formatted like a PHP variable:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Formatter\TextFormatter: ~
            App\Formatter\HtmlFormatter: ~

            # this creates a named autowiring alias called 'htmlFormatter'
            # for the HtmlFormatter implementation
            App\Formatter\FormatterInterface $htmlFormatter: '@App\Formatter\HtmlFormatter'

            # this defines TextFormatter as the default implementation,
            # injected whenever the FormatterInterface type-hint is detected
            App\Formatter\FormatterInterface: '@App\Formatter\TextFormatter'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->
                <service id="App\Formatter\TextFormatter"/>
                <service id="App\Formatter\HtmlFormatter"/>

                <!-- this creates a named autowiring alias called 'htmlFormatter'
                     for the HtmlFormatter implementation -->
                <service
                    id="App\Formatter\FormatterInterface $htmlFormatter"
                    alias="App\Formatter\HtmlFormatter"/>

                <!-- this defines TextFormatter as the default implementation,
                     injected whenever the FormatterInterface type-hint is detected -->
                <service id="App\Formatter\FormatterInterface" alias="App\Formatter\TextFormatter"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Formatter\FormatterInterface;
        use App\Formatter\HtmlFormatter;
        use App\Formatter\TextFormatter;

        return function(ContainerConfigurator $container): void {
            // ...

            $services->set(TextFormatter::class);
            $services->set(HtmlFormatter::class);

            // this creates a named autowiring alias called 'htmlFormatter'
            // for the HtmlFormatter implementation
            $services->alias(FormatterInterface::class.' $htmlFormatter', HtmlFormatter::class);

            // this defines TextFormatter as the default implementation,
            // injected whenever the FormatterInterface type-hint is detected
            $services->alias(FormatterInterface::class, TextFormatter::class);
        };

Then, add the ``#[Target]`` attribute with the name of the named alias to the
arguments where you want to inject a non-default implementation::

    // src/Service/NewsletterGenerator.php
    namespace App\Service;

    use App\Formatter\FormatterInterface;
    use Symfony\Component\DependencyInjection\Attribute\Target;

    class NewsletterGenerator
    {
        public function __construct(
            #[Target('htmlFormatter')]
            private FormatterInterface $formatter,
        ) {
        }

        public function generate(string $contents): string
        {
            return $this->formatter->format($contents);
        }
    }

The ``#[Target]`` attribute keeps the argument name separate from any
implementation name and, in addition, you'll get an exception in case you make
any typo in the target name.

.. warning::

    The ``#[Target]`` attribute only accepts the name of a named autowiring
    alias; it **does not** accept service ids or service aliases.

.. tip::

    Since the ``#[Target]`` attribute normalizes the string passed to it to its
    camelCased form, name variations (e.g. ``html.formatter``) also work.

.. note::

    Some IDEs will show an error when using ``#[Target]`` as in the previous example:
    *"Attribute cannot be applied to a property because it does not contain the 'Attribute::TARGET_PROPERTY' flag"*.
    The reason is that thanks to `PHP constructor promotion`_ this constructor
    argument is both a parameter and a class property. You can safely ignore this error message.

You can get a list of named autowiring aliases by running the ``debug:autowiring``
command:

.. code-block:: terminal

    $ php bin/console debug:autowiring LoggerInterface

    Autowirable Types
    =================

     The following classes & interfaces can be used as type-hints when autowiring:
     (only showing classes/interfaces matching LoggerInterface)

     Describes a logger instance.
     Psr\Log\LoggerInterface - alias:monolog.logger
     Psr\Log\LoggerInterface $assetMapperLogger - target:asset_mapperLogger - alias:monolog.logger.asset_mapper
     Psr\Log\LoggerInterface $cacheLogger - alias:monolog.logger.cache
     Psr\Log\LoggerInterface $httpClientLogger - target:http_clientLogger - alias:monolog.logger.http_client
     Psr\Log\LoggerInterface $mailerLogger - alias:monolog.logger.mailer

     [...]

Injecting the Service Based on the Argument Name
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Named autowiring aliases also work without the ``#[Target]`` attribute: when
an argument is type-hinted with the interface and its name matches the name of
a named alias, the aliased service is injected automatically::

    // src/Service/NewsletterGenerator.php
    namespace App\Service;

    use App\Formatter\FormatterInterface;

    class NewsletterGenerator
    {
        public function __construct(
            // the argument name matches the 'htmlFormatter' named alias,
            // so the container injects the HtmlFormatter service
            private FormatterInterface $htmlFormatter,
        ) {
        }

        // ...
    }

Although this works, **it's strongly discouraged** because it's fragile: the
wiring depends on the name of a PHP variable. If you later rename the argument,
the container won't fail; it will silently inject the default implementation
instead, which can create bugs that are hard to find. That's why you should
always prefer the ``#[Target]`` attribute to inject non-default implementations.

Registering Named Autowiring Aliases from PHP Code
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to register named autowiring aliases from PHP code (e.g. in a
:doc:`compiler pass </service_container/compiler_passes>` or a
:ref:`bundle extension <bundle-load-services-extension>`), use the
:method:`Symfony\\Component\\DependencyInjection\\ContainerBuilder::registerAliasForArgument`
method::

    use App\Formatter\FormatterInterface;

    $container->registerAliasForArgument(
        'app.html_formatter',       // the service id
        FormatterInterface::class,  // the type-hint
        'htmlFormatter'             // the argument name
    );

This registers the alias ``FormatterInterface $htmlFormatter``, so any
argument type-hinted with ``FormatterInterface`` and named ``$htmlFormatter``
will receive the ``app.html_formatter`` service.

You can also pass a fourth argument to define a distinct name for the
``#[Target]`` attribute, separate from the argument name::

    $container->registerAliasForArgument(
        'app.html_formatter',
        FormatterInterface::class,
        'htmlFormatter',  // the argument name
        'html'            // the #[Target] name
    );

This allows the service to be injected using ``#[Target('html')]`` on any
argument, regardless of its name.

.. versionadded:: 7.4

    The ``$target`` argument of ``registerAliasForArgument()`` was introduced
    in Symfony 7.4.

.. _autowire-attribute:

Fixing Non-Autowireable Arguments
---------------------------------

Autowiring only works when your argument is an *object*. But if you have a scalar
argument (e.g. a string), this cannot be autowired: Symfony will throw a clear
exception.

To fix this, you can :ref:`manually wire the problematic argument <services-manually-wire-args>`
in the service configuration. You wire up only the difficult arguments,
Symfony takes care of the rest.

You can also use the ``#[Autowire]`` parameter attribute to instruct the autowiring
logic about those arguments::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;

    class MessageGenerator
    {
        public function __construct(
            #[Autowire(service: 'monolog.logger.request')]
            private LoggerInterface $logger,
        ) {
            // ...
        }
    }

The ``#[Autowire]`` attribute can also be used for :ref:`container parameters <service-container-parameters>`,
:ref:`complex expressions <services-expressions>` and even
:ref:`environment variables <config-env-vars>`,
:doc:`including env variable processors </configuration/env_var_processors>`::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;

    class MessageGenerator
    {
        public function __construct(
            // use the %...% syntax for container parameters
            #[Autowire('%kernel.project_dir%/data')]
            private string $dataDir,

            // or use argument "param"
            #[Autowire(param: 'kernel.debug')]
            private bool $debugMode,

            // expressions
            #[Autowire(expression: 'service("App\\\\Mail\\\\MailerConfiguration").getMailerMethod()')]
            private string $mailerMethod,

            // environment variables
            #[Autowire(env: 'SOME_ENV_VAR')]
            private string $senderName,

            // environment variables with processors
            #[Autowire(env: 'bool:SOME_BOOL_ENV_VAR')]
            private bool $allowAttachments,
        ) {
        }
    }

.. _services-binding:

Binding Arguments by Name or Type
---------------------------------

Instead of configuring the same argument service by service, you can use the
``bind`` keyword to bind arguments by name or type for all the services
defined in a configuration file:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                bind:
                    # pass this value to any $adminEmail argument for any service
                    # that's defined in this file (including controller arguments)
                    $adminEmail: 'manager@example.com'

                    # pass this service to any $requestLogger argument for any
                    # service that's defined in this file
                    $requestLogger: '@monolog.logger.request'

                    # pass this service for any LoggerInterface type-hint for any
                    # service that's defined in this file
                    Psr\Log\LoggerInterface: '@monolog.logger.request'

                    # optionally you can define both the name and type of the argument to match
                    string $adminEmail: 'manager@example.com'
                    Psr\Log\LoggerInterface $requestLogger: '@monolog.logger.request'
                    iterable $rules: !tagged_iterator app.foo.rule

            # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <defaults autowire="true" autoconfigure="true">
                    <bind key="$adminEmail">manager@example.com</bind>
                    <bind key="$requestLogger"
                        type="service"
                        id="monolog.logger.request"
                    />
                    <bind key="Psr\Log\LoggerInterface"
                        type="service"
                        id="monolog.logger.request"
                    />

                    <!-- optionally you can define both the name and type of the argument to match -->
                    <bind key="string $adminEmail">manager@example.com</bind>
                    <bind key="Psr\Log\LoggerInterface $requestLogger"
                        type="service"
                        id="monolog.logger.request"
                    />
                    <bind key="iterable $rules"
                        type="tagged_iterator"
                        tag="app.foo.rule"
                    />
                </defaults>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Psr\Log\LoggerInterface;

        return function(ContainerConfigurator $container): void {
            $services = $container->services()
                ->defaults()
                    // pass this value to any $adminEmail argument for any service
                    // that's defined in this file (including controller arguments)
                    ->bind('$adminEmail', 'manager@example.com')

                    // pass this service to any $requestLogger argument for any
                    // service that's defined in this file
                    ->bind('$requestLogger', service('monolog.logger.request'))

                    // pass this service for any LoggerInterface type-hint for any
                    // service that's defined in this file
                    ->bind(LoggerInterface::class, service('monolog.logger.request'))

                    // optionally you can define both the name and type of the argument to match
                    ->bind('string $adminEmail', 'manager@example.com')
                    ->bind(LoggerInterface::class.' $requestLogger', service('monolog.logger.request'))
                    ->bind('iterable $rules', tagged_iterator('app.foo.rule'))
            ;

            // ...
        };

.. tip::

    The ``bind`` config can also be applied to specific services or when
    :ref:`loading many services at once <service-psr4-loader>`.

.. _autowiring_closures:
.. _container_closure-as-argument:

Generating Closures with Autowiring
-----------------------------------

A **service closure** is an anonymous function that returns a service. This type
of instantiation is handy when you are dealing with lazy-loading. It is also
useful for :ref:`non-shared service dependencies <services-shared>`. If you want
to delay the instantiation of a service without changing the code that uses it,
inject it as a :doc:`lazy service </service_container/lazy_services>` instead;
if you need a whole set of services that may not be known in advance, use a
:doc:`service locator </service_container/subscribers_locators>`.

Automatically creating a closure encapsulating the service instantiation can be
done with the :class:`Symfony\\Component\\DependencyInjection\\Attribute\\AutowireServiceClosure`
attribute::

    // src/Service/Remote/MessageFormatter.php
    namespace App\Service\Remote;

    use Symfony\Component\DependencyInjection\Attribute\AsAlias;

    #[AsAlias('third_party.remote_message_formatter')]
    class MessageFormatter
    {
        public function __construct()
        {
            // ...
        }

        public function format(string $message): string
        {
            // ...
        }
    }

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use App\Service\Remote\MessageFormatter;
    use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;

    class MessageGenerator
    {
        public function __construct(
            #[AutowireServiceClosure('third_party.remote_message_formatter')]
            private \Closure $messageFormatterResolver,
        ) {
        }

        public function generate(string $message): void
        {
            $formattedMessage = ($this->messageFormatterResolver)()->format($message);

            // ...
        }
    }

It is common that a service accepts a closure with a specific signature.
In this case, you can use the :class:`Symfony\\Component\\DependencyInjection\\Attribute\\AutowireCallable`
attribute to generate a closure with the same signature as a specific method of
a service. When this closure is called, it will pass all its arguments to the
underlying service function. If the closure needs to be called more than once,
the service instance is reused for repeated calls. Unlike a service closure,
this will not create extra instances of a non-shared service::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use Symfony\Component\DependencyInjection\Attribute\AutowireCallable;

    class MessageGenerator
    {
        public function __construct(
            #[AutowireCallable(service: 'third_party.remote_message_formatter', method: 'format')]
            private \Closure $formatCallable,
        ) {
        }

        public function generate(string $message): void
        {
            $formattedMessage = ($this->formatCallable)($message);

            // ...
        }
    }

Finally, you can pass the ``lazy: true`` option to the
:class:`Symfony\\Component\\DependencyInjection\\Attribute\\AutowireCallable`
attribute. By doing so, the callable will automatically be lazy, which means
that the encapsulated service will be instantiated **only** at the
closure's first call.

``AutowireMethodOf``
~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\DependencyInjection\\Attribute\\AutowireMethodOf`
attribute provides a simpler way of specifying the name of the service method
by using the property name as method name::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf;

    class MessageGenerator
    {
        public function __construct(
            #[AutowireMethodOf('third_party.remote_message_formatter')]
            private \Closure $format,
        ) {
        }

        public function generate(string $message): void
        {
            $formattedMessage = ($this->format)($message);

            // ...
        }
    }

The ``#[AutowireMethodOf]`` attribute is particularly useful in the following scenarios:

**Decoupling from heavy dependencies**: Instead of injecting an entire repository
or service, you can inject only the specific method you need. This makes your
code more modular and explicit about its dependencies::

    // src/Controller/ConferenceController.php
    namespace App\Controller;

    use App\Repository\CommentRepository;
    use Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf;

    class ConferenceController
    {
        public function __construct(
            // instead of injecting the entire repository...
            // private CommentRepository $commentRepository,

            // ...inject only the method you need
            #[AutowireMethodOf(CommentRepository::class)]
            private \Closure $getCommentPaginator,
        ) {
        }

        public function show(Conference $conference, int $page): Response
        {
            $paginator = ($this->getCommentPaginator)($conference, $page);

            // ...
        }
    }

**Simplified testing**: When you inject closures instead of full services, testing
becomes simpler because you can replace the closure with a test double without
complex mocking frameworks::

    // tests/Controller/ConferenceControllerTest.php
    namespace App\Tests\Controller;

    use App\Controller\ConferenceController;

    class ConferenceControllerTest extends TestCase
    {
        public function testShow(): void
        {
            // create a mock closure instead of mocking the entire repository
            $getCommentPaginator = function (Conference $conference, int $page) {
                return new MockPaginator([/* test comments */]);
            };

            $controller = new ConferenceController($getCommentPaginator);

            // Test your controller...
        }
    }

**Using interfaces for type safety**: For better IDE support and static analysis,
you can define a functional interface and use it as the parameter type instead
of ``\Closure``::

    // src/Repository/Function/GetCommentPaginatorInterface.php
    namespace App\Repository\Function;

    use App\Entity\Conference;
    use Doctrine\ORM\Tools\Pagination\Paginator;

    interface GetCommentPaginatorInterface
    {
        public function __invoke(Conference $conference, int $page): Paginator;
    }

Then use this interface in your service::

    // src/Controller/ConferenceController.php
    namespace App\Controller;

    use App\Repository\CommentRepository;
    use App\Repository\Function\GetCommentPaginatorInterface;
    use Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf;

    class ConferenceController
    {
        public function __construct(
            #[AutowireMethodOf(CommentRepository::class)]
            private GetCommentPaginatorInterface $getCommentPaginator,
        ) {
        }

        public function show(Conference $conference, int $page): Response
        {
            // Call directly without parentheses around the property
            $paginator = ($this->getCommentPaginator)($conference, $page);

            // ...
        }
    }

Injecting Service Closures in Configuration Files
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Service closures can also be defined in configuration files, without using any
attribute in the service class. The service is instantiated the first time the
closure is called, while all subsequent calls return the same instance, unless
the service is :ref:`not shared <services-shared>`::

    // src/Service/MyService.php
    namespace App\Service;

    use Symfony\Component\Mailer\MailerInterface;

    class MyService
    {
        /**
         * @param callable(): MailerInterface
         */
        public function __construct(
            private \Closure $mailer,
        ) {
        }

        public function doSomething(): void
        {
            // ...

            $this->getMailer()->send($email);
        }

        private function getMailer(): MailerInterface
        {
            return ($this->mailer)();
        }
    }

To define a service closure and inject it to another service, create an
argument of type ``service_closure``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\MyService:
                arguments: [!service_closure '@mailer']

                # In case the dependency is optional
                # arguments: [!service_closure '@?mailer']

            # you can also use the special '@>' syntax as a shortcut of '!service_closure'
            App\Service\AnotherService:
                arguments: ['@>mailer']

                # the shortcut also works for optional dependencies
                # arguments: ['@>?mailer']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Service\MyService">
                    <argument type="service_closure" id="mailer"/>

                    <!--
                    In case the dependency is optional
                    <argument type="service_closure" id="mailer" on-invalid="ignore"/>
                    -->
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MyService;

        return function (ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(MyService::class)
                ->args([service_closure('mailer')]);

            // In case the dependency is optional
            // $services->set(MyService::class)
            //     ->args([service_closure('mailer')->ignoreOnInvalid()]);
        };

.. versionadded:: 7.3

    The ``@>`` shortcut syntax for YAML was introduced in Symfony 7.3.

If the injected closure is not meant to return a service but to be *called* by
your class, use the ``closure`` argument type instead. It wraps any callable
service into a closure::

    // src/Hash/MessageHashGenerator.php
    namespace App\Hash;

    class MessageHashGenerator
    {
        public function __invoke(): string
        {
            // compute and return a message hash
        }
    }

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\MessageGenerator:
                arguments:
                    $generateMessageHash: !closure '@App\Hash\MessageHashGenerator'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Service\MessageGenerator">
                    <argument key="$generateMessageHash" type="closure" id="App\Hash\MessageHashGenerator"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MessageGenerator;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(MessageGenerator::class)
                ->arg('$generateMessageHash', closure('App\Hash\MessageHashGenerator'))
            ;
        };

.. seealso::

    Another way to inject services lazily is via a
    :doc:`service locator </service_container/subscribers_locators>`.

Using a Service Closure in a Compiler Pass
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In :doc:`compiler passes </service_container/compiler_passes>` you can create
a service closure by wrapping the service reference into an instance of
:class:`Symfony\\Component\\DependencyInjection\\Argument\\ServiceClosureArgument`::

    use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    public function process(ContainerBuilder $container): void
    {
        // ...

        $myService->addArgument(new ServiceClosureArgument(new Reference('mailer')));
    }

Generating Adapters for Functional Interfaces
---------------------------------------------

Functional interfaces are interfaces with a single method.
They are conceptually very similar to a closure except that their only method
has a name. Moreover, they can be used as type-hints across your code.

The :class:`Symfony\\Component\\DependencyInjection\\Attribute\\AutowireCallable`
attribute can be used to generate an adapter for a functional interface.
Let's say you have the following functional interface::

    // src/Service/MessageFormatterInterface.php
    namespace App\Service;

    interface MessageFormatterInterface
    {
        public function format(string $message, array $parameters): string;
    }

You also have a service that defines many methods and one of them is the same
``format()`` method of the previous interface::

    // src/Service/MessageUtils.php
    namespace App\Service;

    class MessageUtils
    {
        // other methods...

        public function format(string $message, array $parameters): string
        {
            // ...
        }
    }

Thanks to the ``#[AutowireCallable]`` attribute, you can now inject this
``MessageUtils`` service as a functional interface implementation::

    namespace App\Service\Mail;

    use App\Service\MessageFormatterInterface;
    use App\Service\MessageUtils;
    use Symfony\Component\DependencyInjection\Attribute\AutowireCallable;

    class Mailer
    {
        public function __construct(
            #[AutowireCallable(service: MessageUtils::class, method: 'format')]
            private MessageFormatterInterface $formatter
        ) {
        }

        public function sendMail(string $message, array $parameters): string
        {
            $formattedMessage = $this->formatter->format($message, $parameters);

            // ...
        }
    }

Instead of using the ``#[AutowireCallable]`` attribute, you can also generate
an adapter for a functional interface through configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:

            # ...

            app.message_formatter:
                class: App\Service\MessageFormatterInterface
                from_callable: [!service {class: 'App\Service\MessageUtils'}, 'format']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="app.message_formatter" class="App\Service\MessageFormatterInterface">
                    <from-callable method="format">
                        <service class="App\Service\MessageUtils"/>
                    </from-callable>
                </service>

            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MessageFormatterInterface;
        use App\Service\MessageUtils;

        return function(ContainerConfigurator $container) {
            // ...

            $container
                ->set('app.message_formatter', MessageFormatterInterface::class)
                ->fromCallable([inline_service(MessageUtils::class), 'format'])
                ->alias(MessageFormatterInterface::class, 'app.message_formatter')
            ;
        };

By doing so, Symfony will generate a class (also called an *adapter*)
implementing ``MessageFormatterInterface`` that will forward calls of
``MessageFormatterInterface::format()`` to your underlying service's method
``MessageUtils::format()``, with all its arguments.

.. _autowiring-anonymous-services-inline:

The ``#[AutowireInline]`` Attribute
-----------------------------------

.. versionadded:: 7.1

   The ``#[AutowireInline]`` attribute was added in Symfony 7.1.

Sometimes a dependency is only used by one service and doesn't need to be
registered as a full service. In those cases, you can define an
:ref:`anonymous service <anonymous-services>` and inject it with the
:class:`Symfony\\Component\\DependencyInjection\\Attribute\\AutowireInline`
attribute, directly next to the corresponding argument::

    public function __construct(
        #[AutowireInline(
            class: [ScopingHttpClient::class, 'forBaseUri'],
            arguments: [
                '$baseUri' => 'https://api.example.com',
                '$defaultOptions' => [
                    'auth_bearer' => '%env(EXAMPLE_TOKEN)%',
                ],
            ]
        )]
        private HttpClientInterface $client,
    ) {
    }

This example tells Symfony to inject an object created by calling the
``ScopingHttpClient::forBaseUri()`` factory with the specified base URI and
default options. This is just one example: you can use the ``#[AutowireInline]``
attribute to define any kind of anonymous service.

While this approach is convenient for simple service definitions, consider moving
complex or heavily configured services to a configuration file to ease maintenance.

.. _autowiring-calls:

Autowiring Other Methods (e.g. Setters and Public Typed Properties)
-------------------------------------------------------------------

When autowiring is enabled for a service, you can also configure the container
to call methods on your class when it's instantiated. For example, suppose you want
to inject the ``logger`` service, and decide to use :ref:`setter-injection <injection-types-setter>`::

    // src/Formatter/TextFormatter.php
    namespace App\Formatter;

    use Psr\Log\LoggerInterface;
    use Symfony\Contracts\Service\Attribute\Required;

    class TextFormatter
    {
        private LoggerInterface $logger;

        #[Required]
        public function setLogger(LoggerInterface $logger): void
        {
            $this->logger = $logger;
        }

        public function format(string $message): string
        {
            $this->logger->info('Formatting '.$message);
            // ...
        }
    }

Autowiring will automatically call *any* method with the ``#[Required]``
attribute, autowiring each argument. If you need to manually wire some of the
arguments to a method, you can always explicitly
:ref:`configure the method call <injection-types-setter>`.

Despite :ref:`property injection <property-injection>` having some drawbacks,
autowiring with ``#[Required]`` can also be applied to public typed properties.
The service to inject is resolved in the same way as a constructor argument:
the property type defines the service to look for (and the property name is
used to match :ref:`named autowiring aliases <autowiring-alias>`, if any).
Then, right after creating your service, the container assigns the resolved
service directly to the property::

    namespace App\Formatter;

    use Psr\Log\LoggerInterface;
    use Symfony\Contracts\Service\Attribute\Required;

    class TextFormatter
    {
        // the container resolves the service associated with the LoggerInterface
        // type and runs the equivalent of '$textFormatter->logger = $logger;'
        // right after instantiating the TextFormatter object
        #[Required]
        public LoggerInterface $logger;

        public function format(string $message): string
        {
            $this->logger->info('Formatting '.$message);
            // ...
        }
    }

Performance Consequences
------------------------

Thanks to Symfony's compiled container, there is no performance penalty for
using autowiring. However, there is a small performance penalty in the ``dev``
environment, as the container may be rebuilt more often as you modify classes.
If rebuilding your container is slow (possible on very large projects), you may
not be able to use autowiring.

.. note::

    Public bundles should explicitly configure their services and not rely on
    autowiring, because they have no control over the service container of the
    applications they are included in. See :doc:`/bundles/best_practices` for
    more details.

.. _`PHP constructor promotion`: https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion
