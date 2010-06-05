A Quick Tour of Symfony 2.0: The Architecture
=============================================

The first four parts of this tutorial give you a quick overview of Symfony
2.0. But they don't have a deep look at the default directory structure of a
project. As it makes Symfony stand apart from the framework crowd, let's dive
into it now.

The directory structure of a Symfony application is rather flexible. This
tutorial describes the recommended structure but as you will soon understand,
everything is customizable.

The Directory Structure
-----------------------

The directory structure of a sandbox reflects the typical and recommended
structure of a Symfony application:

 * `hello/`: This directory, named after your application, contains the
   configuration files;

 * `src/`: All the PHP code is stored under this directory;

 * `web/`: This should be the web root directory.

### The Web Directory

The web root directory is the home of all public and static files like images,
stylesheets, and JavaScript files. It is also where the front controllers
live:

    [php]
    # web/index.php
    <?php

    require_once __DIR__.'/../hello/HelloKernel.php';

    $kernel = new HelloKernel('prod', false);
    $kernel->run();

Like any front controller, `index.php` uses a Kernel Class, `HelloKernel`, to
bootstrap the application.

### The Application Directory

The `HelloKernel` class is the main entry point of the application
configuration and as such, it is stored in the `hello/` directory.

This class must implement five methods:

  * `registerRootDir()`: Returns the configuration root directory;

  * `registerBundles()`: Returns an array of all bundles needed to run the
    application (notice the reference to `Application\HelloBundle\Bundle`);

  * `registerBundleDirs()`: Returns an array associating namespaces and their
    home directories;

  * `registerContainerConfiguration()`: Returns the main configuration object
    (more on this later);

  * `registerRoutes()`: Returns the routing configuration.

Have a look at the default implementation of these methods to better
understand the flexibility of the framework. At the beginning of this
tutorial, you opened the `hello/config/routing.yml` file. The path is
configured in the `registerRoutes()`:

    [php]
    public function registerRoutes()
    {
      $loader = new RoutingLoader($this->getBundleDirs());

      return $loader->load(__DIR__.'/config/routing.yml');
    }

This is also where you can switch from using YAML configuration files to XML
ones or plain PHP code if that fits you better.

To make things work together, the kernel requires two files from the `src/`
directory:

    [php]
    # hello/HelloKernel.php
    require_once __DIR__.'/../src/autoload.php';
    require_once __DIR__.'/../src/vendor/symfony/src/Symfony/Foundation/bootstrap.php';

### The Source Directory

The `src/autoload.php` file is responsible for autoloading all the files
stored in the `src/` directory:

    [php]
    # src/autoload.php
    require_once __DIR__.'/vendor/symfony/src/Symfony/Foundation/UniversalClassLoader.php';

    use Symfony\Foundation\UniversalClassLoader;

    $loader = new UniversalClassLoader();
    $loader->registerNamespaces(array(
      'Symfony'     => __DIR__.'/vendor/symfony/src',
      'Application' => __DIR__,
      'Bundle'      => __DIR__,
      'Doctrine'    => __DIR__.'/vendor/doctrine/lib',
    ));
    $loader->registerPrefixes(array(
      'Swift_' => __DIR__.'/vendor/swiftmailer/lib/classes',
      'Zend_'  => __DIR__.'/vendor/zend/library',
    ));
    $loader->register();

    // for Zend Framework & SwiftMailer
    set_include_path(__DIR__.'/vendor/zend/library'.PATH_SEPARATOR.__DIR__.'/vendor/swiftmailer/lib'.PATH_SEPARATOR.get_include_path());

The `UniversalClassLoader` from Symfony is used to autoload files that
respect either the technical interoperability [standards][1] for PHP 5.3
namespaces or the PEAR naming [convention][2] for classes. As you can see
here, all dependencies are stored under the `vendor/` directory, but this is
just a convention. You can store them wherever you want, globally on your
server or locally in your projects.

More about Bundles
------------------

As we have seen in the previous part, an application is made of bundles as
defined in the `registerBundles()` method:

    [php]
    # hello/HelloKernel.php
    public function registerBundles()
    {
      return array(
        new Symfony\Foundation\Bundle\KernelBundle(),
        new Symfony\Framework\WebBundle\Bundle(),
        new Symfony\Framework\DoctrineBundle\Bundle(),
        new Symfony\Framework\SwiftmailerBundle\Bundle(),
        new Symfony\Framework\ZendBundle\Bundle(),
        new Application\HelloBundle\Bundle(),
      );
    }

But how does Symfony know where to look for bundles? Symfony is quite flexible
in this regard. The `registerBundleDirs()` method must return an associative
array that maps namespaces to any valid directory (local or global ones):

    [php]
    public function registerBundleDirs()
    {
      return array(
        'Application'        => __DIR__.'/../src/Application',
        'Bundle'             => __DIR__.'/../src/Bundle',
        'Symfony\\Framework' => __DIR__.'/../src/vendor/symfony/src/Symfony/Framework',
      );
    }

So, when you reference the `HelloBundle` in a controller name or in a template
name, Symfony will look for it under the given directories.

Do you understand now why Symfony is so flexible? Share your bundles between
applications, store them locally or globally, your choice.

Vendors
-------

Odds are your application will depend on third-party libraries. Those should
be stored in the `src/vendor/` directory. It already contains the Symfony
libraries, the SwiftMailer library, the Doctrine ORM, and a selection of the
Zend Framework classes.

Cache and Logs
--------------

Symfony is probably one of the fastest full-stack frameworks around. But how
can it be so fast if it parses and interprets tens of YAML and XML files for
each request? This is partly due to its cache system. The application
configuration is only parsed for the very first request and then compiled down
to plain PHP code stored in the `cache/` application directory. In the
development environment, Symfony is smart enough to flush the cache when you
change a file. But in the production one, it is your responsibility to clear
the cache when you update your code or change its configuration.

When developing a web application, things can go wrong in many ways. The log
files in the `logs/` application directory tell you everything about the
requests and helps you fix the problem in no time.

The Command Line Interface
--------------------------

Each application comes with a command line interface tool (`console`) that
helps you maintain your application. It provides commands that boost your
productivity by automating tedious and repetitive tasks.

Run it without any arguments to learn more about its capabilities:

    $ php hello/console

The `--help` option helps you discover the usage of a command:

    $ php hello/console router:debug --help

Final Thoughts
--------------

Call me crazy, but after reading this part, you should be comfortable with
moving things around and making Symfony work for you. Everything is done in
Symfony to stand out of your way. So, feel free to rename and move directories
around as you see fit.

You are just one step away from becoming a Symfony master. That's right, after
reading the next part about how to extend the framework, you will be able to
code the most demanding applications with Symfony.

[1]: http://groups.google.com/group/php-standards/web/psr-0-final-proposal
[2]: http://pear.php.net/
