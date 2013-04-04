Understanding how Front Controller, Kernel and Environments work together
=========================================================================

The previous section, understanding and mastering environments,
explained the basics on how Symfony uses environments to run your application
with different configuration settings. This section will explain a bit more
in-depth what happens when your application is bootstrapped and how the
environment it runs in is made up.

To fully control this environment, you need to understand three parts that
work together:
* The front controller
* The Kernel class
* The environment

The front controller
====================

A *front controller* is a well-known design pattern; it is a section of
code that *all* requests served by an application run through.

In the Symfony Standard Edition, this role is taken by the ``app.php`` and
``app_dev.php`` files in the ``web/`` directory. These are the very first PHP
scripts executed when a request is processed.

The main purpose of the front controller is to create an instance of the
``AppKernel`` (more on that in a second), make it handle the request and
return the resulting response to the browser.

Because every request is routed through it, the front controller can be used
to perform global initializations prior to setting up the kernel or to
*decorate* the kernel with additional features. Examples include:
* Configure the autoloader or add additional autoloading mechanisms
* Add HTTP level caching by wrapping the kernel with an instance of AppCache
(http://symfony.com/doc/2.0/book/http_cache.html#symfony2-reverse-proxy)
* Enabling the Debug component (add link)

When using Apache and the RewriteRule (https://github
.com/symfony/symfony-standard/blob/master/web/.htaccess) shipped with the
Standard Edition, the front controller can be chosen by requesting URLs like

   http://localhost/app_dev.php/some/path/...

As you can see, this URL contains the PHP script to be used as
the front controller. You can use that to easily switch the front controller
or use a custom one by placing it in the ``web/`` directory. If the front
controller file is missing from the URL, the RewriteRule will use ``app
.php`` as the default one.

Technically, the ``app/console`` script (https://github
.com/symfony/symfony-standard/blob/master/app/console) used when running
Symfony on the command line is also a front controller,
only that is not used for web, but for command line requests.

The AppKernel
=============

The Kernel object is the core of Symfony2. The Kernel is responsible for
setting up all the bundles that make up your application and providing them
with the application's configuration. It then creates the service container
before serving requests in its ``handle()`` method.

There are two methods related to the first two of these steps which the base
HttpKernel
(https://github
.com/symfony/symfony/blob/master/src/Symfony/Component/HttpKernel/HttpKernel
.php) does not implement:

* ``registerBundles()``, which must return an array of all bundles needed to
run the application;
* ``registerContainerConfiguration()``, which loads the application
configuration.

The Symfony2 Standard Edition comes with a so-called ``AppKernel`` in the
``app/`` directory (https://github
.com/symfony/symfony-standard/blob/master/app/AppKernel.php) which fills
these blanks. This class
uses the name of the environment, which is passed into the Kernel's
``__construct()`` method and is available via ``getEnvironment()``,
to decide which bundles to create in ``registerBundles()``. This method is
meant to be extended by you when you start adding bundles to your application.

You are, of course, free to create alternative or additional
``AppKernel`` variants. All you need is to adapt (or add a) your front
controller script to make use of the new kernel. Adding additional kernel
implementations might be useful to
* easily switch between different set of bundles to work with (without
creating too complicated ``if...else...`` constructs in the ``registerBundles
()`` method
* add more sophisticated ways of loading the application's configuration from
 a different set of files.

The environments
================

Environments have been covered extensively in the previous chapter (add link).
You probably remember that an environment is nothing more than a name (a
string) passed to the Kernel's constructor which is in turn used to
determine which set of configuration files the Kernel is supposed to load.

For that, the Standard Edition's ``AppKernel`` class implements the
``registerContainerConfiguration()`` method to load the
``app/config/config_*environment*.yml`` file.














