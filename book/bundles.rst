.. index::
   single: Bundles

.. _page-creation-bundles:

The Bundle System
=================

A bundle is similar to a plugin in other software, but even better. The key
difference is that *everything* is a bundle in Symfony, including both the
core framework functionality and the code written for your application.
Bundles are first-class citizens in Symfony. This gives you the flexibility
to use pre-built features packaged in `third-party bundles`_ or to distribute
your own bundles. It makes it easy to pick and choose which features to enable
in your application and to optimize them the way you want.

.. note::

   While you'll learn the basics here, an entire cookbook entry is devoted
   to the organization and best practices of :doc:`bundles </cookbook/bundles/best_practices>`.

A bundle is simply a structured set of files within a directory that implement
a single feature. You might create a BlogBundle, a ForumBundle or
a bundle for user management (many of these exist already as open source
bundles). Each directory contains everything related to that feature, including
PHP files, templates, stylesheets, JavaScript files, tests and anything else.
Every aspect of a feature exists in a bundle and every feature lives in a
bundle.

Bundles used in your applications must be enabled by registering them in
the ``registerBundles()`` method of the ``AppKernel`` class::

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new AppBundle\AppBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

With the ``registerBundles()`` method, you have total control over which bundles
are used by your application (including the core Symfony bundles).

.. tip::

   A bundle can live *anywhere* as long as it can be autoloaded (via the
   autoloader configured at ``app/autoload.php``).

Creating a Bundle
-----------------

The Symfony Standard Edition comes with a handy task that creates a fully-functional
bundle for you. Of course, creating a bundle by hand is pretty easy as well.

To show you how simple the bundle system is, create a new bundle called
AcmeTestBundle and enable it.

.. tip::

    The ``Acme`` portion is just a dummy name that should be replaced by
    some "vendor" name that represents you or your organization (e.g.
    ABCTestBundle for some company named ``ABC``).

Start by creating a ``src/Acme/TestBundle/`` directory and adding a new file
called ``AcmeTestBundle.php``::

    // src/Acme/TestBundle/AcmeTestBundle.php
    namespace Acme\TestBundle;

    use Symfony\Component\HttpKernel\Bundle\Bundle;

    class AcmeTestBundle extends Bundle
    {
    }

.. tip::

   The name AcmeTestBundle follows the standard
   :ref:`Bundle naming conventions <bundles-naming-conventions>`. You could
   also choose to shorten the name of the bundle to simply TestBundle by naming
   this class TestBundle (and naming the file ``TestBundle.php``).

This empty class is the only piece you need to create the new bundle. Though
commonly empty, this class is powerful and can be used to customize the behavior
of the bundle.

Now that you've created the bundle, enable it via the ``AppKernel`` class::

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            // ...

            // register your bundle
            new Acme\TestBundle\AcmeTestBundle(),
        );
        // ...

        return $bundles;
    }

And while it doesn't do anything yet, AcmeTestBundle is now ready to be used.

And as easy as this is, Symfony also provides a command-line interface for
generating a basic bundle skeleton:

.. code-block:: bash

    $ php bin/console generate:bundle --namespace=Acme/TestBundle

The bundle skeleton generates a basic controller, template and routing
resource that can be customized. You'll learn more about Symfony's command-line
tools later.

.. tip::

   Whenever creating a new bundle or using a third-party bundle, always make
   sure the bundle has been enabled in ``registerBundles()``. When using
   the ``generate:bundle`` command, this is done for you.

Bundle Directory Structure
--------------------------

The directory structure of a bundle is simple and flexible. By default, the
bundle system follows a set of conventions that help to keep code consistent
between all Symfony bundles. Take a look at AcmeDemoBundle, as it contains some
of the most common elements of a bundle:

``Controller/``
    Contains the controllers of the bundle (e.g. ``RandomController.php``).

``DependencyInjection/``
    Holds certain Dependency Injection Extension classes, which may import service
    configuration, register compiler passes or more (this directory is not
    necessary).

``Resources/config/``
    Houses configuration, including routing configuration (e.g. ``routing.yml``).

``Resources/views/``
    Holds templates organized by controller name (e.g. ``Hello/index.html.twig``).

``Resources/public/``
    Contains web assets (images, stylesheets, etc) and is copied or symbolically
    linked into the project ``web/`` directory via the ``assets:install`` console
    command.

``Tests/``
    Holds all tests for the bundle.

A bundle can be as small or large as the feature it implements. It contains
only the files you need and nothing else.

As you move through the book, you'll learn how to persist objects to a database,
create and validate forms, create translations for your application, write
tests and much more. Each of these has their own place and role within the
bundle.

_`third-party bundles`: http://knpbundles.com
