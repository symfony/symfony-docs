.. index::
    single: How the front controller, ``AppKernel`` and environments
    work together

Understanding how the Front Controller, Kernel and Environments work together
=============================================================================

The section :doc:`/cookbook/configuration/environments` explained the basics
on how Symfony uses environments to run your application with different configuration
settings. This section will explain a bit more in-depth what happens when
your application is bootstrapped. To hook into this process, you need to understand
three parts that work together:

* `The Front Controller`_
* `The Kernel Class`_
* `The Environments`_

.. note::

    Usually, you will not need to define your own front controller or
    ``AppKernel`` class as the `Symfony2 Standard Edition`_ provides
    sensible default implementations.

    This documentation section is provided to explain what is going on behind
    the scenes.

The Front Controller
--------------------

The `front controller`_ is a well-known design pattern; it is a section of
code that *all* requests served by an application run through.

In the `Symfony2 Standard Edition`_, this role is taken by the `app.php`_
and `app_dev.php`_ files in the ``web/`` directory. These are the very
first PHP scripts executed when a request is processed.

The main purpose of the front controller is to create an instance of the
``AppKernel`` (more on that in a second), make it handle the request
and return the resulting response to the browser.

Because every request is routed through it, the front controller can be
used to perform global initializations prior to setting up the kernel or
to `decorate`_ the kernel with additional features. Examples include:

* Configuring the autoloader or adding additional autoloading mechanisms;
* Adding HTTP level caching by wrapping the kernel with an instance of
  :ref:`AppCache <symfony-gateway-cache>`;
* Enabling (or skipping) the :doc:`ClassCache </cookbook/debugging>`;
* Enabling the :doc:`Debug Component </components/debug/introduction>`.

The front controller can be chosen by requesting URLs like:

.. code-block:: text

     http://localhost/app_dev.php/some/path/...

As you can see, this URL contains the PHP script to be used as the front
controller. You can use that to easily switch the front controller or use
a custom one by placing it in the ``web/`` directory (e.g. ``app_cache.php``).

When using Apache and the `RewriteRule shipped with the Standard Edition`_,
you can omit the filename from the URL and the RewriteRule will use ``app.php``
as the default one.

.. note::

    Pretty much every other web server should be able to achieve a
    behavior similar to that of the RewriteRule described above.
    Check your server documentation for details or see
    :doc:`/cookbook/configuration/web_server_configuration`.

.. note::

    Make sure you appropriately secure your front controllers against unauthorized
    access. For example, you don't want to make a debugging environment
    available to arbitrary users in your production environment.

Technically, the `app/console`_ script used when running Symfony on the command
line is also a front controller, only that is not used for web, but for command
line requests.

The Kernel Class
----------------

The :class:`Symfony\\Component\\HttpKernel\\Kernel` is the core of
Symfony2. It is responsible for setting up all the bundles that make up
your application and providing them with the application's configuration.
It then creates the service container before serving requests in its
:method:`Symfony\\Component\\HttpKernel\\HttpKernelInterface::handle`
method.

There are two methods declared in the
:class:`Symfony\\Component\\HttpKernel\\KernelInterface` that are
left unimplemented in :class:`Symfony\\Component\\HttpKernel\\Kernel`
and thus serve as `template methods`_:

* :method:`Symfony\\Component\\HttpKernel\\KernelInterface::registerBundles`,
  which must return an array of all bundles needed to run the
  application;

* :method:`Symfony\\Component\\HttpKernel\\KernelInterface::registerContainerConfiguration`,
  which loads the application configuration.

To fill these (small) blanks, your application needs to subclass the
Kernel and implement these methods. The resulting class is conventionally
called the ``AppKernel``.

Again, the Symfony2 Standard Edition provides an `AppKernel`_ in the ``app/``
directory. This class uses the name of the environment - which is passed to
the Kernel's :method:`constructor <Symfony\\Component\\HttpKernel\\Kernel::__construct>`
method and is available via :method:`Symfony\\Component\\HttpKernel\\Kernel::getEnvironment` -
to decide which bundles to create. The logic for that is in ``registerBundles()``,
a method meant to be extended by you when you start adding bundles to your
application.

You are, of course, free to create your own, alternative or additional
``AppKernel`` variants. All you need is to adapt your (or add a new) front
controller to make use of the new kernel.

.. note::

    The name and location of the ``AppKernel`` is not fixed. When
    putting multiple Kernels into a single application,
    it might therefore make sense to add additional sub-directories,
    for example ``app/admin/AdminKernel.php`` and
    ``app/api/ApiKernel.php``. All that matters is that your front
    controller is able to create an instance of the appropriate
    kernel.

Having different ``AppKernels`` might be useful to enable different front
controllers (on potentially different servers) to run parts of your application
independently (for example, the admin UI, the frontend UI and database migrations).

.. note::

    There's a lot more the ``AppKernel`` can be used for, for example
    :doc:`overriding the default directory structure </cookbook/configuration/override_dir_structure>`.
    But odds are high that you don't need to change things like this on the
    fly by having several ``AppKernel`` implementations.

The Environments
----------------

As just mentioned, the ``AppKernel`` has to implement another method -
:method:`Symfony\\Component\\HttpKernel\\KernelInterface::registerContainerConfiguration`.
This method is responsible for loading the application's
configuration from the right *environment*.

Environments have been covered extensively
:doc:`in the previous chapter </cookbook/configuration/environments>`,
and you probably remember that the Standard Edition comes with three
of them - ``dev``, ``prod`` and ``test``.

More technically, these names are nothing more than strings passed from the
front controller to the ``AppKernel``'s constructor. This name can then be
used in the :method:`Symfony\\Component\\HttpKernel\\KernelInterface::registerContainerConfiguration`
method to decide which configuration files to load.

The Standard Edition's `AppKernel`_ class implements this method by simply
loading the ``app/config/config_*environment*.yml`` file. You are, of course,
free to implement this method differently if you need a more sophisticated
way of loading your configuration.

.. _front controller: http://en.wikipedia.org/wiki/Front_Controller_pattern
.. _Symfony2 Standard Edition: https://github.com/symfony/symfony-standard
.. _app.php: https://github.com/symfony/symfony-standard/blob/master/web/app.php
.. _app_dev.php: https://github.com/symfony/symfony-standard/blob/master/web/app_dev.php
.. _app/console: https://github.com/symfony/symfony-standard/blob/master/app/console
.. _AppKernel: https://github.com/symfony/symfony-standard/blob/master/app/AppKernel.php
.. _decorate: http://en.wikipedia.org/wiki/Decorator_pattern
.. _RewriteRule shipped with the Standard Edition: https://github.com/symfony/symfony-standard/blob/master/web/.htaccess
.. _template methods: http://en.wikipedia.org/wiki/Template_method_pattern
