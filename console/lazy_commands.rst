How to Make Commands Lazily Loaded
==================================

.. note::

    If you are using the Symfony full-stack framework, you are probably looking for
    details about :ref:`creating lazy commands <console-command-service-lazy-loading>`

The traditional way of adding commands to your application is to use
:method:`Symfony\\Component\\Console\\Application::add`, which expects a
``Command`` instance as an argument.

This approach can have downsides as some commands might be expensive to
instantiate in which case you may want to lazy-load them. Note however that lazy-loading
is not absolute. Indeed a few commands such as ``list``, ``help`` or ``_complete`` can
require to instantiate other commands although they are lazy. For example ``list`` needs
to get the name and description of all commands, which might require the command to be
instantiated to get.

In order to lazy-load commands, you need to register an intermediate loader
which will be responsible for returning ``Command`` instances::

    use App\Command\HeavyCommand;
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

    $commandLoader = new FactoryCommandLoader([
        // Note that the `list` command will still instantiate that command
        // in this example.
        'app:heavy' => static fn(): Command => new HeavyCommand(),
    ]);

    $application = new Application();
    $application->setCommandLoader($commandLoader);
    $application->run();

This way, the ``HeavyCommand`` instance will be created only when the ``app:heavy``
command is actually called.

This example makes use of the built-in
:class:`Symfony\\Component\\Console\\CommandLoader\\FactoryCommandLoader` class,
but the :method:`Symfony\\Component\\Console\\Application::setCommandLoader`
method accepts any
:class:`Symfony\\Component\\Console\\CommandLoader\\CommandLoaderInterface`
instance so you can use your own implementation.

Another way to do so is to take advantage of ``Symfony\Component\Console\Command\LazyCommand``::

    use App\Command\HeavyCommand;
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

    // In this case although the command is instantiated, the underlying command factory
    // will not be executed unless the command is actually executed or one tries to access
    // its input definition to know its argument or option inputs.
    $lazyCommand = new LazyCommand(
        'app:heavy',
        [],
        'This is another more complete form of lazy command.',
        false,
        static fn (): Command => new HeavyCommand(),
    );

    $application = new Application();
    $application->add($lazyCommand);
    $application->run();

Built-in Command Loaders
------------------------

``FactoryCommandLoader``
~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Console\\CommandLoader\\FactoryCommandLoader`
class provides a way of getting commands lazily loaded as it takes an
array of ``Command`` factories as its only constructor argument::

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

    $commandLoader = new FactoryCommandLoader([
        'app:foo' => function (): Command { return new FooCommand(); },
        'app:bar' => [BarCommand::class, 'create'],
    ]);

Factories can be any PHP callable and will be executed each time
:method:`Symfony\\Component\\Console\\CommandLoader\\FactoryCommandLoader::get`
is called.

``ContainerCommandLoader``
~~~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Console\\CommandLoader\\ContainerCommandLoader`
class can be used to load commands from a PSR-11 container. As such, its
constructor takes a PSR-11 ``ContainerInterface`` implementation as its first
argument and a command map as its last argument. The command map must be an array
with command names as keys and service identifiers as values::

    use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    $container = new ContainerBuilder();
    $container->register(FooCommand::class, FooCommand::class);
    $container->compile();

    $commandLoader = new ContainerCommandLoader($container, [
        'app:foo' => FooCommand::class,
    ]);

Like this, executing the ``app:foo`` command will load the ``FooCommand`` service
by calling ``$container->get(FooCommand::class)``.
