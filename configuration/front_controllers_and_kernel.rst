Understanding how the Front Controller, Kernel and Environments Work together
=============================================================================

The :ref:`configuration environments <configuration-environments>` section
explained the basics on how Symfony uses environments to run your application
with different configuration settings. This section will explain a bit more
in-depth what happens when your application is bootstrapped. To hook into this
process, you need to understand three parts that work together:

* `The Front Controller`_
* `The Kernel Class`_
* `The Environments`_

.. note::

    Usually, you will not need to define your own front controller or
    ``Kernel`` class as Symfony provides sensible default implementations.
    This article is provided to explain what is going on behind the scenes.

.. _architecture-front-controller:

The Front Controller
--------------------

The `front controller`_ is a design pattern; it is a section of code that *all*
requests served by an application run through.

In the Symfony Skeleton, this role is taken by the ``index.php`` file in the
``public/`` directory. This is the very first PHP script that is run when a
request is processed.

The main purpose of the front controller is to create an instance of the
``Kernel`` (more on that in a second), make it handle the request and return
the resulting response to the browser.

Because every request is routed through it, the front controller can be
used to perform global initialization prior to setting up the kernel or
to `decorate`_ the kernel with additional features. Examples include:

* Configuring the autoloader or adding additional autoloading mechanisms;
* Adding HTTP level caching by wrapping the kernel with an instance of
  :ref:`HttpCache <symfony-gateway-cache>`;
* Enabling the `Debug component`_.

You can choose the front controller that's used by adding it in the URL, like:

.. code-block:: text

     http://localhost/index.php/some/path/...

As you can see, this URL contains the PHP script to be used as the front
controller. You can use that to switch to a custom made front controller
that is located in the ``public/`` directory.

.. seealso::

    You almost never want to show the front controller in the URL. This is
    achieved by configuring the web server, as shown in
    :doc:`/setup/web_server_configuration`.

Technically, the ``bin/console`` script used when running Symfony on the command
line is also a front controller, only that is not used for web, but for command
line requests.

The Kernel Class
----------------

The :class:`Symfony\\Component\\HttpKernel\\Kernel` is the core of
Symfony. It is responsible for setting up all the bundles used by
your application and providing them with the application's configuration.
It then creates the service container before serving requests in its
:method:`Symfony\\Component\\HttpKernel\\HttpKernelInterface::handle`
method.

The kernel used in Symfony applications extends from :class:`Symfony\\Component\\HttpKernel\\Kernel`
and uses the :class:`Symfony\\Bundle\\FrameworkBundle\\Kernel\\MicroKernelTrait`.
The ``Kernel`` class leaves some methods from :class:`Symfony\\Component\\HttpKernel\\KernelInterface`
unimplemented and the ``MicroKernelTrait`` defines several abstract methods, so
you must implement them all:

:method:`Symfony\\Component\\HttpKernel\\KernelInterface::registerBundles`
    It must return an array of all bundles needed to run the application.

:method:`Symfony\\Bundle\\FrameworkBundle\\Kernel\\MicroKernelTrait::configureRoutes`
    It adds individual routes or collections of routes to the application (for
    example loading the routes defined in some config file).

:method:`Symfony\\Bundle\\FrameworkBundle\\Kernel\\MicroKernelTrait::configureContainer`
    It loads the application configuration from config files or using the
    ``loadFromExtension()`` method and can also register new container parameters
    and services.

To fill these (small) blanks, your application needs to extend the Kernel class
and use the MicroKernelTrait to implement these methods. Symfony provides by
default that kernel in the ``src/Kernel.php`` file.

This class uses the name of the environment - which is passed to the Kernel's
:method:`constructor <Symfony\\Component\\HttpKernel\\Kernel::__construct>`
method and is available via :method:`Symfony\\Component\\HttpKernel\\Kernel::getEnvironment` -
to decide which bundles to enable. The logic for that is in ``registerBundles()``.

You are free to create your own, alternative or additional ``Kernel`` variants.
All you need is to adapt your (or add a new) front controller to make use of the
new kernel.

.. note::

    The name and location of the ``Kernel`` is not fixed. When putting
    :doc:`multiple kernels into a single application </configuration/multiple_kernels>`,
    it might therefore make sense to add additional sub-directories, for example
    ``src/admin/AdminKernel.php`` and ``src/api/ApiKernel.php``. All that matters
    is that your front controller is able to create an instance of the appropriate kernel.

.. note::

    There's a lot more the ``Kernel`` can be used for, for example
    :doc:`overriding the default directory structure </configuration/override_dir_structure>`.
    But odds are high that you don't need to change things like this on the
    fly by having several ``Kernel`` implementations.

.. _debug-mode:

Debug Mode
~~~~~~~~~~

The second argument to the ``Kernel`` constructor specifies if the application
should run in "debug mode". Regardless of the
:ref:`configuration environment <configuration-environments>`, a Symfony
application can be run with debug mode set to ``true`` or ``false``.

This affects many things in the application, such as displaying stack traces on
error pages or if cache files are dynamically rebuilt on each request. Though
not a requirement, debug mode is generally set to ``true`` for the ``dev`` and
``test`` environments and ``false`` for the ``prod`` environment.

Similar to :ref:`configuring the environment <selecting-the-active-environment>`
you can also enable/disable the debug mode using :ref:`the .env file <config-dot-env>`:

.. code-block:: bash

    # .env
    # set it to 1 to enable the debug mode
    APP_DEBUG=0

This value can be overridden for commands by passing the ``APP_DEBUG`` value
before running them:

.. code-block:: terminal

    # Use the debug mode defined in the .env file
    $ php bin/console command_name

    # Ignore the .env file and enable the debug mode for this command
    $ APP_DEBUG=1 php bin/console command_name

Internally, the value of the debug mode becomes the ``kernel.debug``
parameter used inside the :doc:`service container </service_container>`.
If you look inside the application configuration file, you'll see the
parameter used, for example, to turn Twig's debug mode on:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            debug: '%kernel.debug%'

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config debug="%kernel.debug%"/>

        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig) {
            // ...
            $twig->debug('%kernel.debug%');
        };

The Environments
----------------

As mentioned above, the ``Kernel`` has to implement another method -
:method:`Symfony\\Bundle\\FrameworkBundle\\Kernel\\MicroKernelTrait::configureContainer`.
This method is responsible for loading the application's configuration from the
right *environment*.

:ref:`Configuration environments <configuration-environments>` allow to execute
the same code using different configuration. Symfony provides three environments
by default called ``dev``, ``prod`` and ``test``.

More technically, these names are nothing more than strings passed from the
front controller to the ``Kernel``'s constructor. This name can then be used in
the ``configureContainer()`` method to decide which configuration files to load.

Symfony's default ``Kernel`` class implements this method by loading first the
config files found on ``config/packages/*`` and then, the files found on
``config/packages/ENVIRONMENT_NAME/``. You are free to implement this method
differently if you need a more sophisticated way of loading your configuration.

Environments and the Cache Directory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony takes advantage of caching in many ways: the application configuration,
routing configuration, Twig templates and more are cached to PHP objects
stored in files on the filesystem.

By default, these cached files are largely stored in the ``var/cache/`` directory.
However, each environment caches its own set of files:

.. code-block:: text

    your-project/
    ├─ var/
    │  ├─ cache/
    │  │  ├─ dev/   # cache directory for the *dev* environment
    │  │  └─ prod/  # cache directory for the *prod* environment
    │  ├─ ...

Sometimes, when debugging, it may be helpful to inspect a cached file to
understand how something is working. When doing so, remember to look in
the directory of the environment you're using (most commonly ``dev/`` while
developing and debugging). While it can vary, the ``var/cache/dev/`` directory
includes the following:

``srcApp_KernelDevDebugContainer.php``
    The cached "service container" that represents the cached application
    configuration.

``url_generating_routes.php``
    The cached routing configuration used when generating URLs.

``url_matching_routes.php``
    The cached configuration used for route matching - look here to see the compiled
    regular expression logic used to match incoming URLs to different routes.

``twig/``
    This directory contains all the cached Twig templates.

.. note::

    You can change the cache directory location and name. For more information
    read the article :doc:`/configuration/override_dir_structure`.

.. _`front controller`: https://en.wikipedia.org/wiki/Front_Controller_pattern
.. _`decorate`: https://en.wikipedia.org/wiki/Decorator_pattern
.. _Debug component: https://github.com/symfony/debug
