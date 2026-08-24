Console Argument Value Resolvers
================================

In :ref:`invokable Symfony commands <console_creating-command>`, arguments of
the ``__invoke()`` method are resolved automatically based on their type
declarations and attributes. This is handled by the
:class:`Symfony\\Component\\Console\\ArgumentResolver\\ArgumentResolver`.

The Console component uses a system of *value resolvers* to determine which
value should be passed to each argument. Symfony provides several built-in
resolvers, and you can register custom ones to extend or customize this behavior.

.. versionadded:: 8.1

    The console argument resolver system was introduced in Symfony 8.1.

Built-In Value Resolvers
------------------------

Symfony Console ships with several built-in value resolvers.

``BackedEnumValueResolver``
~~~~~~~~~~~~~~~~~~~~~~~~~~~

:class:`Symfony\\Component\\Console\\ArgumentResolver\\ValueResolver\\BackedEnumValueResolver`
resolves backed enum cases from command input. The parameter must be annotated
with ``#[Argument]`` or ``#[Option]`` to specify the input source.

For example, if your backed enum is::

    namespace App\Model;

    enum Suit: string
    {
        case Hearts = 'H';
        case Diamonds = 'D';
        case Clubs = 'C';
        case Spades = 'S';
    }

And your command contains the following::

    use App\Model\Suit;
    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: 'app:cards')]
    class CardsCommand
    {
        public function __invoke(
            #[Argument]
            Suit $suit
        ): int {
            // ...
        }
    }

When running ``php bin/console app:cards H``, the ``$suit`` variable will
store the ``Suit::Hearts`` case.

``BuiltinTypeValueResolver``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:class:`Symfony\\Component\\Console\\ArgumentResolver\\ValueResolver\\BuiltinTypeValueResolver`
resolves parameters with PHP builtin types (``string``, ``int``, ``float``, ``bool``,
``array``) that are annotated with the ``#[Argument]`` or ``#[Option]`` attribute.
The value is retrieved from the command input::

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\Option;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: 'app:greet')]
    class GreetCommand
    {
        public function __invoke(
            #[Argument]
            string $name,

            #[Option]
            int $times = 1
        ): int {
            // $name comes from CLI argument: php bin/console app:greet John
            // $times comes from CLI option: php bin/console app:greet John --times=3
        }
    }

.. seealso::

    Read :doc:`/console/input` for more information about the ``#[Argument]``
    and ``#[Option]`` attributes.

``DateTimeValueResolver``
~~~~~~~~~~~~~~~~~~~~~~~~~

:class:`Symfony\\Component\\Console\\ArgumentResolver\\ValueResolver\\DateTimeValueResolver`
resolves a ``DateTimeInterface`` object from command input. The parameter must
be annotated with ``#[Argument]`` or ``#[Option]`` to specify the input source.

By default, any input that can be parsed as a date string by PHP is accepted.
You can restrict how the input can be formatted with the
:class:`Symfony\\Component\\Console\\Attribute\\MapDateTime` attribute::

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\MapDateTime;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: 'app:schedule')]
    class ScheduleCommand
    {
        public function __invoke(
            #[Argument]
            #[MapDateTime(format: 'Y-m-d')]
            \DateTimeImmutable $date
        ): int {
            // $date is parsed from: php bin/console app:schedule 2024-03-15
        }
    }

.. tip::

    The ``DateTimeInterface`` object is generated with the :doc:`Clock component </components/clock>`.
    This gives you full control over the date and time values the command
    receives when testing your application and using the
    :class:`Symfony\\Component\\Clock\\MockClock` implementation.

``DefaultValueResolver``
~~~~~~~~~~~~~~~~~~~~~~~~

:class:`Symfony\\Component\\Console\\ArgumentResolver\\ValueResolver\\DefaultValueResolver`
sets the default value of the argument if present and the argument is optional.

``EntityValueResolver``
~~~~~~~~~~~~~~~~~~~~~~~

:class:`Symfony\\Bridge\\Doctrine\\Console\\ArgumentResolver\\EntityValueResolver`
automatically queries for a Doctrine entity and passes it as an argument to your
command. The parameter must be annotated with ``#[Argument]`` or ``#[Option]`` to
specify the input source. You can optionally use the ``#[MapEntity]`` attribute
to configure entity resolution (e.g., which field to use for lookup).

For example, the following will query the ``User`` entity using the input value::

    use App\Entity\User;
    use Symfony\Bridge\Doctrine\Attribute\MapEntity;
    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: 'app:user:show')]
    class ShowUserCommand
    {
        public function __invoke(
            #[Argument]
            #[MapEntity]
            User $user
        ): int {
            // $user is fetched from: php bin/console app:user:show 42
            // By default, uses the entity's primary key
        }
    }

You can configure which field to use for the lookup::

    #[AsCommand(name: 'app:user:show')]
    class ShowUserCommand
    {
        public function __invoke(
            #[Argument]
            #[MapEntity(mapping: ['user' => 'email'])]
            User $user
        ): int {
            // $user is fetched by email: php bin/console app:user:show john@example.com
        }
    }

To learn more about the use of the ``EntityValueResolver``, see the dedicated
section :ref:`Automatically Fetching Objects <doctrine-entity-value-resolver>`.

``ServiceValueResolver``
~~~~~~~~~~~~~~~~~~~~~~~~

:class:`Symfony\\Component\\Console\\ArgumentResolver\\ValueResolver\\ServiceValueResolver`
injects a service if type-hinted with a valid service class or interface. This
works like :doc:`autowiring </service_container/autowiring>`::

    use App\Service\UserManager;
    use Psr\Log\LoggerInterface;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: 'app:create-user')]
    class CreateUserCommand
    {
        public function __invoke(
            LoggerInterface $logger,
            UserManager $userManager
        ): int {
            // $logger and $userManager are autowired!
            $logger->info('Creating a new user...');
            $userManager->create('john');

            return Command::SUCCESS;
        }
    }

``UidValueResolver``
~~~~~~~~~~~~~~~~~~~~

:class:`Symfony\\Component\\Console\\ArgumentResolver\\ValueResolver\\UidValueResolver`
resolves Symfony UID types (``Uuid``, ``Ulid``, ``UuidV4``, etc.) from command
input. The parameter must be annotated with ``#[Argument]`` or ``#[Option]``
to specify the input source::

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\Option;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Uid\Ulid;
    use Symfony\Component\Uid\Uuid;

    #[AsCommand(name: 'app:user:show')]
    class ShowUserCommand
    {
        public function __invoke(
            #[Argument]
            Uuid $id,

            #[Option]
            ?Ulid $reference = null
        ): int {
            // $id resolved from: php bin/console app:user:show 550e8400-e29b-41d4-a716-446655440000
            // $reference resolved from: --reference=01ARZ3NDEKTSV4RRFFQ69G5FAV
        }
    }

``VariadicValueResolver``
~~~~~~~~~~~~~~~~~~~~~~~~~

:class:`Symfony\\Component\\Console\\ArgumentResolver\\ValueResolver\\VariadicValueResolver`
resolves variadic parameters from command input arrays. The parameter must be
annotated with ``#[Argument]`` or ``#[Option]``::

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Output\OutputInterface;

    #[AsCommand(name: 'app:process')]
    class ProcessCommand
    {
        public function __invoke(
            OutputInterface $output,
            #[Argument]
            string ...$files
        ): int {
            // $files contains all arguments: php bin/console app:process a.txt b.txt c.txt
            foreach ($files as $file) {
                $output->writeln("Processing: $file");
            }

            return Command::SUCCESS;
        }
    }

Managing Value Resolvers
------------------------

For each argument, every resolver tagged with ``console.argument_value_resolver``
is called until one provides a value. The order in which they are called depends
on their priority.

The :class:`Symfony\\Component\\Console\\Attribute\\ValueResolver` attribute
lets you "target" a specific resolver, skipping all others::

    use App\ValueResolver\CustomIdResolver;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\ValueResolver;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: 'app:process')]
    class ProcessCommand
    {
        public function __invoke(
            #[ValueResolver(CustomIdResolver::class)]
            string $id
        ): int {
            // only CustomIdResolver will be used to resolve $id
        }
    }

You can target a resolver by passing its name as the first argument of ``ValueResolver``.
For convenience, the names of built-in resolvers are their FQCN.

You can also disable a targeted resolver by setting the ``$disabled`` argument
of ``ValueResolver`` to ``true``::

    use Symfony\Component\Console\ArgumentResolver\ValueResolver\ServiceValueResolver;
    use Symfony\Component\Console\Attribute\ValueResolver;

    public function __invoke(
        #[ValueResolver(ServiceValueResolver::class, disabled: true)]
        MyService $service
    ): int {
        // ServiceValueResolver won't be used for $service
    }

Adding a Custom Value Resolver
------------------------------

In the next example, you'll create a value resolver to inject a custom ID value
object whenever a command argument has a type implementing
``IdentifierInterface`` (e.g. ``BookingId``)::

    use App\Reservation\BookingId;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: 'app:booking:show')]
    class ShowBookingCommand
    {
        public function __invoke(BookingId $id): int
        {
            // ... do something with $id
        }
    }

Adding a new value resolver requires creating a class that implements
:class:`Symfony\\Component\\Console\\ArgumentResolver\\ValueResolverInterface`
and defining a service for it.

This interface contains a ``resolve()`` method, which is called for each
argument of the command. It receives the argument name, the current
``InputInterface`` object and a
:class:`Symfony\\Component\\Console\\ArgumentResolver\\ReflectionMember`
instance, which contains all information from the method signature.

The ``resolve()`` method should return either an empty array (if it cannot resolve
this argument) or an array with the resolved value(s). Usually arguments are
resolved as a single value, but variadic arguments require resolving multiple
values. That's why you must always return an array, even for single values::

    // src/Console/ValueResolver/BookingIdValueResolver.php
    namespace App\Console\ValueResolver;

    use App\IdentifierInterface;
    use Symfony\Component\Console\ArgumentResolver\ReflectionMember;
    use Symfony\Component\Console\ArgumentResolver\ValueResolverInterface;
    use Symfony\Component\Console\Input\InputInterface;

    class BookingIdValueResolver implements ValueResolverInterface
    {
        public function resolve(string $argumentName, InputInterface $input, ReflectionMember $member): iterable
        {
            // get the argument type (e.g. BookingId)
            $argumentType = $member->getType();
            if (
                !$argumentType
                || !is_subclass_of($argumentType, IdentifierInterface::class, true)
            ) {
                return [];
            }

            // get the value from the input, based on the argument name
            $value = $input->getArgument($argumentName);
            if (!is_string($value)) {
                return [];
            }

            // create and return the value object
            return [$argumentType::fromString($value)];
        }
    }

This method first checks whether it can resolve the value:

* The argument must be type-hinted with a class implementing the custom ``IdentifierInterface``;
* The argument name must match a defined input argument.

When those requirements are met, the method creates a new instance of the
custom value object and returns it as the value for this argument.

That's it! Now all you have to do is add the configuration for the service
container. This can be done by adding one of the following tags to your value resolver.

``console.argument_value_resolver``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This tag is automatically added to every service implementing ``ValueResolverInterface``,
but you can set it yourself to change its ``priority`` or ``name`` attributes.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Console/ValueResolver/BookingIdValueResolver.php
        namespace App\Console\ValueResolver;

        use Symfony\Component\Console\ArgumentResolver\ValueResolverInterface;
        use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

        #[AsTaggedItem(index: 'booking_id', priority: 150)]
        class BookingIdValueResolver implements ValueResolverInterface
        {
            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                # ... be sure autowiring is enabled
                autowire: true
            # ...

            App\Console\ValueResolver\BookingIdValueResolver:
                tags:
                    - console.argument_value_resolver:
                        name: booking_id
                        priority: 150

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Console\ValueResolver\BookingIdValueResolver;

        return App::config([
            'services' => [
                // ...
                BookingIdValueResolver::class => [
                    'tags' => [
                        ['console.argument_value_resolver' => ['name' => 'booking_id', 'priority' => 150]],
                    ],
                ],
            ],
        ]);

While adding a priority is optional, it's recommended to add one to make sure
the expected value is injected.

To ensure your resolvers are added in the right position, you can run the following
command to see which argument resolvers are present and in which order they run:

.. code-block:: terminal

    $ php bin/console debug:container console.argument_resolver

You can also configure the name passed to the ``ValueResolver`` attribute to
target your resolver. Otherwise, it defaults to the service's ID.

.. _console-value-resolver-targeted:

``console.targeted_value_resolver``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Set this tag if you want your resolver to be called only when targeted by a
``ValueResolver`` attribute. Like ``console.argument_value_resolver``, you
can customize the name by which your resolver can be targeted.

As an alternative, you can add the
:class:`Symfony\\Component\\Console\\Attribute\\AsTargetedValueResolver` attribute
to your resolver and pass your custom name as its first argument::

    // src/Console/ValueResolver/BookingIdValueResolver.php
    namespace App\Console\ValueResolver;

    use Symfony\Component\Console\ArgumentResolver\ValueResolverInterface;
    use Symfony\Component\Console\Attribute\AsTargetedValueResolver;

    #[AsTargetedValueResolver('booking_id')]
    class BookingIdValueResolver implements ValueResolverInterface
    {
        // ...
    }

You can then pass this name as the first argument of ``ValueResolver`` to target
your resolver::

    use App\Reservation\BookingId;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\ValueResolver;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: 'app:booking:show')]
    class ShowBookingCommand
    {
        public function __invoke(
            #[ValueResolver('booking_id')]
            BookingId $id
        ): int {
            // ... do something with $id
        }
    }

Advanced Service Injection
--------------------------

The console argument resolver system supports all the same dependency injection
features as controller argument resolvers. You can use attributes like ``#[Autowire]``,
``#[Target]``, and DI bindings in your command's ``__invoke()`` method.

Using ``#[Autowire]``
~~~~~~~~~~~~~~~~~~~~~

The ``#[Autowire]`` attribute gives you explicit control over which service or
parameter is injected::

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    use Symfony\Component\Messenger\MessageBusInterface;

    #[AsCommand(name: 'app:dispatch')]
    class DispatchCommand
    {
        public function __invoke(
            #[Autowire(service: 'messenger.bus.async')]
            MessageBusInterface $bus,

            #[Autowire('%kernel.environment%')]
            string $env
        ): int {
            // $bus is explicitly the async message bus
            // $env is the value of kernel.environment parameter
        }
    }

Using ``#[Target]``
~~~~~~~~~~~~~~~~~~~

When you have multiple services implementing the same interface, use the
``#[Target]`` attribute to specify which one to inject::

    use Psr\Log\LoggerInterface;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\DependencyInjection\Attribute\Target;

    #[AsCommand(name: 'app:audit')]
    class AuditCommand
    {
        public function __invoke(
            #[Target('app')]
            LoggerInterface $appLogger,

            #[Target('security')]
            LoggerInterface $securityLogger
        ): int {
            // Two different logger channels!
        }
    }

Using DI Bindings
~~~~~~~~~~~~~~~~~

You can also use service container bindings to inject values:

.. code-block:: yaml

    # config/services.yaml
    services:
        App\Command\DeployCommand:
            autowire: true
            autoconfigure: true
            bind:
                $apiKey: '%app.api_key%'
                LoggerInterface $customLogger: '@monolog.logger.custom'

Then in your command::

    use Psr\Log\LoggerInterface;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: 'app:deploy')]
    class DeployCommand
    {
        public function __invoke(
            string $apiKey,
            LoggerInterface $customLogger
        ): int {
            // $apiKey and $customLogger are injected via bindings
        }
    }
