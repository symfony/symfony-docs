The Runtime Component
=====================

    The Runtime Component decouples the bootstrapping logic from any global state
    to make sure the application can run with runtimes like PHP-PM, ReactPHP,
    Swoole, etc. without any changes.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/runtime

.. include:: /components/require_autoload.rst.inc

Usage
-----

The Runtime component abstracts most bootstrapping logic as so-called
*runtimes*, allowing you to write front-controllers in a generic way.
For instance, the Runtime component allows Symfony's ``public/index.php``
to look like this::

    // public/index.php
    use App\Kernel;

    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

    return function (array $context) {
        return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    };

So how does this front-controller work? At first, the special
``autoload_runtime.php`` file is automatically created by the Composer plugin in
the component. This file runs the following logic:

#. It instantiates a :class:`Symfony\\Component\\Runtime\\RuntimeInterface`;
#. The callable (returned by ``public/index.php``) is passed to the Runtime, whose job
   is to resolve the arguments (in this example: ``array $context``);
#. Then, this callable is called to get the application (``App\Kernel``);
#. At last, the Runtime is used to run the application (i.e. calling
   ``$kernel->handle(Request::createFromGlobals())->send()``).

.. caution::

    If you use the Composer ``--no-plugins`` option, the ``autoload_runtime.php``
    file won't be created.

    If you use the Composer ``--no-scripts`` option, make sure your Composer version
    is ``>=2.1.3``; otherwise the ``autoload_runtime.php`` file won't be created.

To make a console application, the bootstrap code would look like::

    #!/usr/bin/env php
    <?php
    // bin/console

    use App\Kernel;
    use Symfony\Bundle\FrameworkBundle\Console\Application;

    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

    return function (array $context) {
        $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

        // returning an "Application" makes the Runtime run a Console
        // application instead of the HTTP Kernel
        return new Application($kernel);
    };

Selecting Runtimes
------------------

The default Runtime is :class:`Symfony\\Component\\Runtime\\SymfonyRuntime`. It
works excellent on most applications running with a webserver using PHP-FPM like
Nginx or Apache.

The component also provides a :class:`Symfony\\Component\\Runtime\\GenericRuntime`,
which uses PHP's ``$_SERVER``, ``$_POST``, ``$_GET``, ``$_FILES`` and
``$_SESSION`` superglobals. You may also use a custom Runtime (e.g. to
integrate with Swoole or AWS Lambda).

Use the ``APP_RUNTIME`` environment variable or by specifying the
``extra.runtime.class`` in ``composer.json`` to change the Runtime class:

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

Using the Runtime
-----------------

A Runtime is responsible for passing arguments into the closure and run the
application returned by the closure. The :class:`Symfony\\Component\\Runtime\\SymfonyRuntime` and
:class:`Symfony\\Component\\Runtime\\GenericRuntime` supports a number of
arguments and different applications that you can use in your
front-controllers.

Resolvable Arguments
~~~~~~~~~~~~~~~~~~~~

The closure returned from the front-controller may have zero or more arguments::

    // public/index.php
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

    return function (InputInterface $input, OutputInterface $output) {
        // ...
    };

The following arguments are supported by the ``SymfonyRuntime``:

:class:`Symfony\\Component\\HttpFoundation\\Request`
    A request created from globals.

:class:`Symfony\\Component\\Console\\Input\\InputInterface`
    An input to read options and arguments.

:class:`Symfony\\Component\\Console\\Output\\OutputInterface`
    Console output to print to the CLI with style.

:class:`Symfony\\Component\\Console\\Application`
    An application for creating CLI applications.

:class:`Symfony\\Component\\Command\\Command`
    For creating one line command CLI applications (using
    ``Command::setCode()``).

And these arguments are supported by both the ``SymfonyRuntime`` and
``GenericRuntime`` (both type and variable name are important):

``array $context``
    This is the same as ``$_SERVER`` + ``$_ENV``.

``array $argv``
    The arguments passed to the command (same as ``$_SERVER['argv']``).

``array $request``
    With keys ``query``, ``body``, ``files`` and ``session``.

Resolvable Applications
~~~~~~~~~~~~~~~~~~~~~~~

The application returned by the closure below is a Symfony Kernel. However,
a number of different applications are supported::

    // public/index.php
    use App\Kernel;

    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

    return function () {
        return new Kernel('prod', false);
    };

The ``SymfonyRuntime`` can handle these applications:

:class:`Symfony\\Component\\HttpKernel\\HttpKernelInterface`
    The application will be run with :class:`Symfony\\Component\\Runtime\\Runner\\Symfony\\HttpKernelRunner`
    like a "standard" Symfony application.

:class:`Symfony\\Component\\HttpFoundation\\Response`
    The Response will be printed by
    :class:`Symfony\\Component\\Runtime\\Runner\\Symfony\\ResponseRunner`::

        // public/index.php
        use Symfony\Component\HttpFoundation\Response;

        require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

        return function () {
            return new Response('Hello world');
        };

:class:`Symfony\\Component\\Console\\Command\\Command`
    To write single command applications. This will use the
    :class:`Symfony\\Component\\Runtime\\Runner\\Symfony\\ConsoleApplicationRunner`::

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

:class:`Symfony\\Component\\Console\\Application`
    Useful with console applications with more than one command. This will use the
    :class:`Symfony\\Component\\Runtime\\Runner\\Symfony\\ConsoleApplicationRunner`::

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

The ``GenericRuntime`` and ``SymfonyRuntime`` also support these generic
applications:

:class:`Symfony\\Component\\Runtime\\RunnerInterface`
    The ``RunnerInterface`` is a way to use a custom application with the
    generic Runtime::

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
    the "application" and the second callable is the "application" itself::

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
    everything is fine::

        require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

        return function () {
            echo 'Hello world';
        };

Using Options
~~~~~~~~~~~~~

Some behavior of the Runtimes can be modified through runtime options. They
can be set using the ``APP_RUNTIME_OPTIONS`` environment variable::

    $_SERVER['APP_RUNTIME_OPTIONS'] = [
        'project_dir' => '/var/task',
    ];

    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

    // ...

You can also configure ``extra.runtime`` in ``composer.json``:

.. code-block:: json

    {
        "require": {
            "...": "..."
        },
        "extra": {
            "runtime": {
                "project_dir": "/var/task"
            }
        }
    }

Then, update your Composer files (running ``composer dump-autoload``, for instance),
so that the ``vendor/autoload_runtime.php`` files gets regenerated with the new option.

The following options are supported by the ``SymfonyRuntime``:

``env`` (default: ``APP_ENV`` environment variable, or ``"dev"``)
    To define the name of the environment the app runs in.
``disable_dotenv`` (default: ``false``)
    To disable looking for ``.env`` files.
``dotenv_path`` (default: ``.env``)
    To define the path of dot-env files.
``dotenv_overload`` (default: ``false``)
    To tell Dotenv whether to override ``.env`` vars with ``.env.local`` (or other ``.env.*`` files)
``use_putenv``
    To tell Dotenv to set env vars using ``putenv()`` (NOT RECOMMENDED).
``prod_envs`` (default: ``["prod"]``)
    To define the names of the production envs.
``test_envs`` (default: ``["test"]``)
    To define the names of the test envs.

Besides these, the ``GenericRuntime`` and ``SymfonyRuntime`` also support
these options:

``debug`` (default: the value of the env var defined by ``debug_var_name`` option
    (usually, ``APP_DEBUG``), or ``true`` if such env var is not defined)
    Toggles the :ref:`debug mode <debug-mode>` of Symfony applications (e.g. to
    display errors)
``runtimes``
    Maps "application types" to a ``GenericRuntime`` implementation that
    knows how to deal with each of them.
``error_handler`` (default: :class:`Symfony\\Component\\Runtime\\Internal\\BasicErrorHandler` or :class:`Symfony\\Component\\Runtime\\Internal\\SymfonyErrorHandler` for ``SymfonyRuntime``)
    Defines the class to use to handle PHP errors.
``env_var_name`` (default: ``"APP_ENV"``)
    Defines the name of the env var that stores the name of the
    :ref:`configuration environment <configuration-environments>`
    to use when running the application.
``debug_var_name`` (default: ``"APP_DEBUG"``)
    Defines the name of the env var that stores the value of the
    :ref:`debug mode <debug-mode>` flag to use when running the application.

Create Your Own Runtime
-----------------------

This is an advanced topic that describes the internals of the Runtime component.

Using the Runtime component will benefit maintainers because the bootstrap
logic could be versioned as a part of a normal package. If the application
author decides to use this component, the package maintainer of the Runtime
class will have more control and can fix bugs and add features.

The Runtime component is designed to be totally generic and able to run any
application outside of the global state in 6 steps:

#. The main entry point returns a *callable* (the "app") that wraps the application;
#. The *app callable* is passed to ``RuntimeInterface::getResolver()``, which returns
   a :class:`Symfony\\Component\\Runtime\\ResolverInterface`. This resolver returns
   an array with the app callable (or something that decorates this callable) at
   index 0 and all its resolved arguments at index 1.
#. The *app callable* is invoked with its arguments, it will return an object that
   represents the application.
#. This *application object* is passed to ``RuntimeInterface::getRunner()``, which
   returns a :class:`Symfony\\Component\\Runtime\\RunnerInterface`: an instance
   that knows how to "run" the application object.
#. The ``RunnerInterface::run(object $application)`` is called and it returns the
   exit status code as ``int``.
#. The PHP engine is terminated with this status code.

When creating a new runtime, there are two things to consider: First, what arguments
will the end user use? Second, what will the user's application look like?

For instance, imagine you want to create a runtime for `ReactPHP`_:

**What arguments will the end user use?**

For a generic ReactPHP application, no special arguments are
typically required. This means that you can use the
:class:`Symfony\\Component\\Runtime\\GenericRuntime`.

**What will the user's application look like?**

There is also no typical React application, so you might want to rely on
the `PSR-15`_ interfaces for HTTP request handling.

However, a ReactPHP application will need some special logic to *run*. That logic
is added in a new class implementing :class:`Symfony\\Component\\Runtime\\RunnerInterface`::

    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    use React\EventLoop\Factory as ReactFactory;
    use React\Http\Server as ReactHttpServer;
    use React\Socket\Server as ReactSocketServer;
    use Symfony\Component\Runtime\RunnerInterface;

    class ReactPHPRunner implements RunnerInterface
    {
        public function __construct(
            private RequestHandlerInterface $application,
            private int $port,
        ) {
        }

        public function run(): int
        {
            $application = $this->application;
            $loop = ReactFactory::create();

            // configure ReactPHP to correctly handle the PSR-15 application
            $server = new ReactHttpServer(
                $loop,
                function (ServerRequestInterface $request) use ($application) {
                    return $application->handle($request);
                }
            );

            // start the ReactPHP server
            $socket = new ReactSocketServer($this->port, $loop);
            $server->listen($socket);

            $loop->run();

            return 0;
        }
    }

By extending the ``GenericRuntime``, you make sure that the application is
always using this ``ReactPHPRunner``::

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

            // if it's not a PSR-15 application, use the GenericRuntime to
            // run the application (see "Resolvable Applications" above)
            return parent::getRunner($application);
        }
    }

The end user will now be able to create front controller like::

    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

    return function (array $context) {
        return new SomeCustomPsr15Application();
    };

.. _ReactPHP: https://reactphp.org/
.. _`PSR-15`: https://www.php-fig.org/psr/psr-15/
