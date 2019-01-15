How to Make Commands Lazily Loaded
==================================

.. note::

    If you are using the Symfony full-stack framework, you are probably looking for
    details about :ref:`creating lazy commands <console-command-service-lazy-loading>`

The traditional way of adding commands to your application is to use
:method:`Symfony\\Component\\Console\\Application::add`, which expects a
``Command`` instance as an argument.

In order to lazy-load commands, you need to register an intermediate loader
which will be responsible for returning ``Command`` instances::

    use App\Command\HeavyCommand;
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

    $commandLoader = new FactoryCommandLoader([
        'app:heavy' => function () { return new HeavyCommand(); },
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

Built-in Command Loaders
------------------------

``FactoryCommandLoader``
~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Console\\CommandLoader\\FactoryCommandLoader`
class provides a way of getting commands lazily loaded as it takes an
array of ``Command`` factories as its only constructor argument::

    use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

    $commandLoader = new FactoryCommandLoader([
        'app:foo' => function () { return new FooCommand(); },
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

    $containerBuilder = new ContainerBuilder();
    $containerBuilder->register(FooCommand::class, FooCommand::class);
    $containerBuilder->compile();

    $commandLoader = new ContainerCommandLoader($containerBuilder, [
        'app:foo' => FooCommand::class,
    ]);

Like this, executing the ``app:foo`` command will load the ``FooCommand`` service
by calling ``$containerBuilder->get(FooCommand::class)``.
