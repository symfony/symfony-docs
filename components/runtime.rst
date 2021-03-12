.. index::
   single: Runtime
   single: Components; Runtime

The Runtime Component
======================

    The Runtime Component decouples the bootstrapping logic from any global state
    to make sure the application can run with runtimes like PHP-FPM, ReactPHP,
    Swoole etc without any changes.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/runtime

.. include:: /components/require_autoload.rst.inc

Usage
-----

The Runtime component allows you to write front-controllers in a generic way
and with use of configuration you may change the behavior. Let's consider the
``public/index.php`` as an example. It will return a callable which will create
and return the application::

    <?php
    // public/index.php
    use App\Kernel;

    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

    return function (array $context) {
        return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    };

The special ``autoload_runtime.php`` is automatically created when composer dumps
the autoload files since the component includes a composer plugin. The ``autoload_runtime.php``
will instantiate a :class:`Symfony\\Component\\Runtime\\RuntimeInterface`, its job
is to take the callable and resolve the arguments (``array $context``). Then it calls
the callable to get the application ``App\Kernel``. At last it will run the application,
ie calling ``$kernel->handle(Request::createFromGlobals())->send()``.

To make a console application, the same bootstrap code would look like::

    #!/usr/bin/env php
    <?php
    // bin/console

    use App\Kernel;
    use Symfony\Bundle\FrameworkBundle\Console\Application;

    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

    return function (array $context) {
        $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
        return new Application($kernel);
    };

Selecting Runtimes
------------------

The default Runtime is :class:`Symfony\\Component\\Runtime\\SymfonyRuntime`, it
works excellent on most applications running with a webserver like Nginx and Apache,
and PHP-FPM. You may change Runtime to :class:`Symfony\\Component\\Runtime\\GenericRuntime`
or a custom Runtime for Swoole or AWS Lambda. This can be done by specifying the
Runtime class in the ``APP_RUNTIME`` environment variable or to specify the
``extra.runtime.class`` in ``composer.json``.

.. code-block:: json

    {
        "require": {
            "...": "..."
        },
        "extra": {
            "runtime": {
                "class": "Symfony\\Component\\Runtime\\GenericRuntime"
            }
        }
    }

Using the SymfonyRuntime
------------------------

The :class:`Symfony\\Component\\Runtime\\RuntimeInterface` has two methods. One
to get an instance of :class:`Symfony\\Component\\Runtime\\ResolverInterface`
that prepares the arguments to the callable and one get an instance of
:class:`Symfony\\Component\\Runtime\\RunnerInterface` to run the application.

The :class:`Symfony\\Component\\Runtime\\SymfonyRuntime` supports a number of
arguments and different applications.

Resolvable Arguments
~~~~~~~~~~~~~~~~~~~~

The closure returned from the script may have zero or more arguments.::

    <?php
    // public/index.php
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

    return function (InputInterface $input, OutputInterface $output) {
        // ...
    };


``:class:`Symfony\\Component\\HttpFoundation\\Request` $request``
    A request created from globals.

``:class:`Symfony\\Component\\Console\\Input\\InputInterface` $input``
    An input to read options and arguments.

``:class:`Symfony\\Component\\Console\\Output\\OutputInterface` $output``
    Console output to print to the CLI with style.

``:class:`Symfony\\Component\\Console\\Application` $application``
    An application for creating CLI applications.

``:class:`Symfony\\Component\\Command\\Command` $command``
    For creating one line command CLI applications.

``array $context``
    This is the same as ``$_SERVER`` + ``$_ENV``

``array $argv``
    The arguments passed to the command. Same as ``$_SERVER['argv']``

``array $request``
    With keys ``query``, ``body``, ``files`` and ``session``.

Resolvable Applications
~~~~~~~~~~~~~~~~~~~~~~~

The application returned by the closure below is a Symfony Kernel. However, the
:class:`Symfony\\Component\\Runtime\\SymfonyRuntime` supports a number of
different applications.::

    <?php
    // public/index.php
    use App\Kernel;

    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

    return function () {
        return new Kernel('prod', false);
    };

``:class:`Symfony\\Component\\HttpKernel\\HttpKernelInterface```
    The application will be executed with :class:`Symfony\\Component\\Runtime\\Runner\\Symfony\\HttpKernelRunner``
    just like a "standard" Symfony application.

``:class:`Symfony\\Component\\HttpFoundation\\Response```
    The Response will be printed by
    :class:`Symfony\\Component\\Runtime\\Runner\\Symfony\\ResponseRunner``.::

        <?php
        // public/index.php
        use Symfony\Component\HttpFoundation\Response;

        require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

        return function () {
            return new Response('Hello world');
        };

``:class:`Symfony\\Component\\Console\\Command\\Command```
    To write one command applications. This will use the
    :class:`Symfony\\Component\\Runtime\\Runner\\Symfony\\ConsoleApplicationRunner``.::

        <?php

        use Symfony\Component\Console\Command\Command;
        use Symfony\Component\Console\Input\InputInterface;
        use Symfony\Component\Console\Output\OutputInterface;

        require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

        return function (Command $command) {
            $command->setCode(function (InputInterface $input, OutputInterface $output) {
                $output->write('Hello World');
            });

            return $command;
        };

``:class:`Symfony\\Component\\Console\\Application```
    Useful with console applications with more than one command. This will use the
    :class:`Symfony\\Component\\Runtime\\Runner\\Symfony\\ConsoleApplicationRunner``.::

        <?php

        use Symfony\Component\Console\Application;
        use Symfony\Component\Console\Command\Command;
        use Symfony\Component\Console\Input\InputInterface;
        use Symfony\Component\Console\Output\OutputInterface;

        require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

        return function (array $context) {
            $command = new Command('hello');
            $command->setCode(function (InputInterface $input, OutputInterface $output) {
                $output->write('Hello World');
            });

            $app = new Application();
            $app->add($command);
            $app->setDefaultCommand('hello', true);

            return $app;
        };

``:class:`Symfony\\Component\\Runtime\\RunnerInterface```
    The ``RuntimeInterface`` is a way to use a custom application with the
    generic Runtime.::

        <?php
        // public/index.php
        use Symfony\Component\Runtime\RunnerInterface;

        require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

        return function () {
            return new class implements RunnerInterface {
                public function run(): int
                {
                    echo 'Hello World';

                    return 0;
                }

            };
        };

``callable``
    Your "application" can also be a ``callable``. The first callable will return
    the "application" and the second callable is the "application" itself.::

        <?php
        // public/index.php

        require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

        return function () {
            $app = function() {
                echo 'Hello World';

                return 0;
            };

            return $app;
        };

``void``
    If the callable doesn't return anything, the ``SymfonyRuntime`` will assume
    everything is fine.::

        <?php

        require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

        return function () {
            echo 'Hello world';
        };


Using Options
~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Runtime\\SymfonyRuntime` supports a number of
options. They can be passed to the constructor in two ways. First by specifying
options to ``APP_RUNTIME_OPTIONS`` environment variable.::

    <?php

    $_SERVER['APP_RUNTIME_OPTIONS'] = [
        'project_dir' => '/var/task',
    ];

    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

    // ...

The second way to pass an option to ``SymfonyRuntime::__construct()`` is to use
``extra.runtime.options`` in ``composer.json``.

.. code-block:: json

    {
        "require": {
            "...": "..."
        },
        "extra": {
            "runtime": {
                "options": {
                    "project_dir": "/var/task"
                }
            }
        }
    }

.. note::

    The environment variable ``APP_DEBUG`` has special support to easily
    turn on and off debugging.

Create Your Own Runtime
-----------------------

This is an advanced topic that describes the internals of the Runtime component.

Using the runtime component will benefit maintainers because the bootstrap logic
could be versioned as a part of a normal package. If the application author decides
to use this component, the package maintainer of the Runtime class will have more
control and can fix bugs and add features.

.. note::

    Before Symfony 5.3, the boostrap logic was part of a Flex recipe. Since recipes
    are rarely updated by users, bug patches would rarely be installed.

The Runtime component is designed to be totally generic and able to run any application
outside of the global state in 6 steps:

 1. The main entry point returns a callable (A) that wraps the application
 2. Callable (A) is passed to ``RuntimeInterface::getResolver()``, which returns a
    ``ResolverInterface``. This resolver returns an array with the callable (A)
    (or something that decorates the callable (A)) at index 0, and all its resolved
    arguments at index 1.
 3. The callable A is invoked with its arguments, it will return an object that
    represents the application (B).
 4. That object (B) is passed to ``RuntimeInterface::getRunner()``, which returns a
    ``RunnerInterface``: an instance that knows how to "run" the object (B).
 5. The ``RunnerInterface::run($objectB)`` is executed and it returns the exit status
    code as `int`.
 6. The PHP engine is exited with this status code.

When creating a new runtime, there are two things to consider: First, what arguments
will the end user use? Second, what will the user's application look like?

To create a runtime for ReactPHP, we see that no special arguments are typically
required. We will use the standard arguments provided by :class:`Symfony\\Component\\Runtime\\GenericRuntime`
by extending tha class. But a ReactPHP application will need some special logic
to run. That logic is added in a new class implementing :class:`Symfony\\Component\\Runtime\\RunnerInterface`::

    use Psr\Http\Message\ServerRequestInterface;
    use Symfony\Component\Runtime\RunnerInterface;

    class ReactPHPRunner implements RunnerInterface
    {
        private $application;
        private $port;

        public function __construct(RequestHandlerInterface $application, int $port)
        {
            $this->application = $application;
            $this->port = $port;
        }

        public function run(): int
        {
            $application = $this->application;
            $loop = \React\EventLoop\Factory::create();

            $server = new \React\Http\Server($loop, function (ServerRequestInterface $request) use ($application) {
                return $application->handle($request);
            });

            $socket = new \React\Socket\Server($this->port, $loop);
            $server->listen($socket);

            $loop->run();

            return 0;
        }
    }

Now we should create a new :class:`Symfony\\Component\\Runtime\\RuntimeInterface`
that is using our ``ReactPHPRunner``::

    use Symfony\Component\Runtime\GenericRuntime;
    use Symfony\Component\Runtime\RunnerInterface;

    class ReactPHPRuntime extends GenericRuntime
    {
        private $port;

        public function __construct(array $options)
        {
            $this->port = $options['port'] ?? 8080;
            parent::__construct($options);
        }

        public function getRunner(?object $application): RunnerInterface
        {
            if ($application instanceof RequestHandlerInterface) {
                return new ReactPHPRunner($application, $this->port);
            }

            return parent::getRunner($application);
        }
    }

The end user will now be able to create front controller like::

    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

    return function (array $context) {
        return new Psr15Application();
    };
