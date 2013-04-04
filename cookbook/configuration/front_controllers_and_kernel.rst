Understanding how Front Controller, Kernel and Environments work together
=========================================================================

The section :doc:`/cookbook/configuration/environments`
explained the basics on how Symfony uses environments to run your application
with different configuration settings. This section will explain a bit more
in-depth what happens when your application is bootstrapped and how you can hook into this process. You need to understand three parts that
work together:

* The front controller
* The Kernel class
* The environment

The front controller
====================

The [`front controller`](http://en.wikipedia.org/wiki/Front_Controller_pattern) is a well-known design pattern; it is a section of
code that *all* requests served by an application run through.

In the [Symfony 2 Standard Edition](https://github.com/symfony/symfony-standard), this role is taken by the [``app.php``](https://github.com/symfony/symfony-standard/blob/master/web/app.php) and
[``app_dev.php``](https://github.com/symfony/symfony-standard/blob/master/web/app_dev.php) files in the ``web/`` directory. These are the very first PHP
scripts executed when a request is processed.

The main purpose of the front controller is to create an instance of the
``AppKernel`` (more on that in a second), make it handle the request and
return the resulting response to the browser.

Because every request is routed through it, the front controller can be used
to perform global initializations prior to setting up the kernel or to
[*decorate*](http://en.wikipedia.org/wiki/Decorator_pattern) the kernel with additional features. Examples include:

* Configure the autoloader or add additional autoloading mechanisms
* Add HTTP level caching by wrapping the kernel with an instance of :doc:`AppCache</book/http_cache#symfony2-reverse-proxy>`
* Enabling the [Debug component](https://github.com/symfony/symfony/pull/7441)

When using Apache and the [RewriteRule shipped with the
Standard Edition](https://github
.com/symfony/symfony-standard/blob/master/web/.htaccess), the front controller can be chosen by requesting URLs like::

   http://localhost/app_dev.php/some/path/...

As you can see, this URL contains the PHP script to be used as
the front controller. You can use that to easily switch the front controller
or use a custom one by placing it in the ``web/`` directory. If the front
controller file is missing from the URL, the RewriteRule will use ``app
.php`` as the default one.

Technically, the [``app/console`` script](https://github
.com/symfony/symfony-standard/blob/master/app/console) used when running
Symfony on the command line is also a front controller,
only that is not used for web, but for command line requests.

The AppKernel
=============

The Kernel object is the core of Symfony2. The Kernel is responsible for
setting up all the bundles that make up your application and providing them
with the application's configuration. It then creates the service container
before serving requests in its ``handle()`` method.

There are two methods related to the first two of these steps which [the base
HttpKernel](https://github
.com/symfony/symfony/blob/master/src/Symfony/Component/HttpKernel/HttpKernel
.php) does not implement:

* :method:`registerBundles()<Symfony\\Component\\HttpKernel\\HttpKernel::registerBundles>`, which must return an array of all bundles needed to
run the application;
* :method:`registerContainerConfiguration()<Symfony\\Component\\HttpKernel\\KernelInterface::registerContainerConfiguration>`, which loads the application
configuration.

To fill these (small) blanks, your application needs to subclass the Kernel
and implement these methods. The resulting class is called the ``AppKernel``.

Again, the Symfony2 Standard Edition provides an [``AppKernel``](https://github
.com/symfony/symfony-standard/blob/master/app/AppKernel.php) in the
``app/`` directory. This class
uses the name of the environment, which is passed to the Kernel's :method:`constructor<Symfony\\Component\\HttpKernel\\Kernel::__construct>`  and is available via :method:`getEnvironment()<Symfony\\Component\\HttpKernel\\Kernel::getEnvironment>`,
to decide which bundles to create in ``registerBundles()``. This method is
meant to be extended by you when you start adding bundles to your application.

You are, of course, free to create your own, alternative or additional
``AppKernel`` variants. All you need is to adapt your (or add a new) front
controller to make use of the new kernel. Adding additional kernel
implementations might be useful to

* easily switch between different set of bundles to work with, without
creating too complicated ``if...else...`` constructs in the ``registerBundles
()`` method or
* add more sophisticated ways of loading the application's configuration from
 a different set of files.

The environments
================

Environments have been covered extensively :doc:`in the previous chapter</cookbook/configuration/environments>`. You probably remember that an environment is nothing more than a name (a
string) passed to the Kernel's constructor which is in turn used to
determine which set of configuration files the Kernel is supposed to load - and this is what the missing :method:`registerContainerConfiguration()<Symfony\\Component\\HttpKernel\\KernelInterface::registerContainerConfiguration>` method is used for.

The Standard Edition's [``AppKernel``](https://github.com/symfony/symfony-standard/blob/master/app/AppKernel.php) class implements this method by simply loading the ``app/config/config_*environment*.yml`` file.
