.. index::
    single: How the front controller, ``Kernel`` and environments
    work together

Understanding how the Front Controller, Kernel and Environments Work together
=============================================================================

The section :doc:`/configuration/environments` explained the basics on how
Symfony uses environments to run your application with different configuration
settings. This section will explain a bit more in-depth what happens when your
application is bootstrapped. To hook into this process, you need to understand
three parts that work together:

* `The Front Controller`_
* `The Kernel Class`_
* `The Environments`_

.. note::

    Usually, you will not need to define your own front controller or
    ``Kernel`` class as Symfony provides sensible default implementations.
    This article is provided to explain what is going on behind the scenes.

The Front Controller
--------------------

The `front controller`_ is a design pattern; it is a section of code that *all*
requests served by an application run through.

In the Symfony Skeleton, this role is taken by the ``index.php`` file in the
``public/`` directory. This is the very first PHP script executed when a
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
* Enabling the :doc:`Debug Component </components/debug>`.

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

You are, of course, free to create your own, alternative or additional
``Kernel`` variants. All you need is to adapt your (or add a new) front
controller to make use of the new kernel.

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

The Environments
----------------

As mentioned above, the ``Kernel`` has to implement another method -
:method:`Symfony\\Bundle\\FrameworkBundle\\Kernel\\MicroKernelTrait::configureContainer`.
This method is responsible for loading the application's configuration from the
right *environment*.

Environments have been covered extensively :doc:`in the previous article
</configuration/environments>`, and you probably remember that the Symfony uses
by default three of them - ``dev``, ``prod`` and ``test``.

More technically, these names are nothing more than strings passed from the
front controller to the ``Kernel``'s constructor. This name can then be used in
the ``configureContainer()`` method to decide which configuration files to load.

Symfony's default ``Kernel`` class implements this method by loading first the
config files found on ``config/packages/*`` and then, the files found on
``config/packages/ENVIRONMENT_NAME/``. You are free to implement this method
differently if you need a more sophisticated way of loading your configuration.

.. _front controller: https://en.wikipedia.org/wiki/Front_Controller_pattern
.. _decorate: https://en.wikipedia.org/wiki/Decorator_pattern
