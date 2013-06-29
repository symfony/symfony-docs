.. index::
   single: symfony1

How Symfony2 differs from symfony1
==================================

The Symfony2 framework embodies a significant evolution when compared with
the first version of the framework. Fortunately, with the MVC architecture
at its core, the skills used to master a symfony1 project continue to be
very relevant when developing in Symfony2. Sure, ``app.yml`` is gone, but
routing, controllers and templates all remain.

This chapter walks through the differences between symfony1 and Symfony2.
As you'll see, many tasks are tackled in a slightly different way. You'll
come to appreciate these minor differences as they promote stable, predictable,
testable and decoupled code in your Symfony2 applications.

So, sit back and relax as you travel from "then" to "now".

Directory Structure
-------------------

When looking at a Symfony2 project - for example, the `Symfony2 Standard Edition`_ -
you'll notice a very different directory structure than in symfony1. The
differences, however, are somewhat superficial.

The ``app/`` Directory
~~~~~~~~~~~~~~~~~~~~~~

In symfony1, your project has one or more applications, and each lives inside
the ``apps/`` directory (e.g. ``apps/frontend``). By default in Symfony2,
you have just one application represented by the ``app/`` directory. Like
in symfony1, the ``app/`` directory contains configuration specific to that
application. It also contains application-specific cache, log and template
directories as well as a ``Kernel`` class (``AppKernel``), which is the base
object that represents the application.

Unlike symfony1, almost no PHP code lives in the ``app/`` directory. This
directory is not meant to house modules or library files as it did in symfony1.
Instead, it's simply the home of configuration and other resources (templates,
translation files).

The ``src/`` Directory
~~~~~~~~~~~~~~~~~~~~~~

Put simply, your actual code goes here. In Symfony2, all actual application-code
lives inside a bundle (roughly equivalent to a symfony1 plugin) and, by default,
each bundle lives inside the ``src`` directory. In that way, the ``src``
directory is a bit like the ``plugins`` directory in symfony1, but much more
flexible. Additionally, while *your* bundles will live in the ``src/`` directory,
third-party bundles will live somewhere in the ``vendor/`` directory.

To get a better picture of the ``src/`` directory, let's first think of a
symfony1 application. First, part of your code likely lives inside one or
more applications. Most commonly these include modules, but could also include
any other PHP classes you put in your application. You may have also created
a ``schema.yml`` file in the ``config`` directory of your project and built
several model files. Finally, to help with some common functionality, you're
using several third-party plugins that live in the ``plugins/`` directory.
In other words, the code that drives your application lives in many different
places.

In Symfony2, life is much simpler because *all* Symfony2 code must live in
a bundle. In the pretend symfony1 project, all the code *could* be moved
into one or more plugins (which is a very good practice, in fact). Assuming
that all modules, PHP classes, schema, routing configuration, etc were moved
into a plugin, the symfony1 ``plugins/`` directory would be very similar
to the Symfony2 ``src/`` directory.

Put simply again, the ``src/`` directory is where your code, assets,
templates and most anything else specific to your project will live.

The ``vendor/`` Directory
~~~~~~~~~~~~~~~~~~~~~~~~~

The ``vendor/`` directory is basically equivalent to the ``lib/vendor/``
directory in symfony1, which was the conventional directory for all vendor
libraries and bundles. By default, you'll find the Symfony2 library files in
this directory, along with several other dependent libraries such as Doctrine2,
Twig and Swiftmailer. 3rd party Symfony2 bundles live somewhere in the
``vendor/``.

The ``web/`` Directory
~~~~~~~~~~~~~~~~~~~~~~

Not much has changed in the ``web/`` directory. The most noticeable difference
is the absence of the ``css/``, ``js/`` and ``images/`` directories. This
is intentional. Like with your PHP code, all assets should also live inside
a bundle. With the help of a console command, the ``Resources/public/``
directory of each bundle is copied or symbolically-linked to the ``web/bundles/``
directory. This allows you to keep assets organized inside your bundle, but
still make them available to the public. To make sure that all bundles are
available, run the following command:

.. code-block:: bash

    $ php app/console assets:install web

.. note::

   This command is the Symfony2 equivalent to the symfony1 ``plugin:publish-assets``
   command.

Autoloading
-----------

One of the advantages of modern frameworks is never needing to worry about
requiring files. By making use of an autoloader, you can refer to any class
in your project and trust that it's available. Autoloading has changed in
Symfony2 to be more universal, faster, and independent of needing to clear
your cache.

In symfony1, autoloading was done by searching the entire project for the
presence of PHP class files and caching this information in a giant array.
That array told symfony1 exactly which file contained each class. In the
production environment, this caused you to need to clear the cache when classes
were added or moved.

In Symfony2, a tool named `Composer`_ handles this process.
The idea behind the autoloader is simple: the name of your class (including
the namespace) must match up with the path to the file containing that class.
Take the ``FrameworkExtraBundle`` from the Symfony2 Standard Edition as an
example::

    namespace Sensio\Bundle\FrameworkExtraBundle;

    use Symfony\Component\HttpKernel\Bundle\Bundle;
    // ...

    class SensioFrameworkExtraBundle extends Bundle
    {
        // ...
    }

The file itself lives at
``vendor/sensio/framework-extra-bundle/Sensio/Bundle/FrameworkExtraBundle/SensioFrameworkExtraBundle.php``.
As you can see, the location of the file follows the namespace of the class.
Specifically, the namespace, ``Sensio\Bundle\FrameworkExtraBundle``, spells out
the directory that the file should live in
(``vendor/sensio/framework-extra-bundle/Sensio/Bundle/FrameworkExtraBundle/``).
Composer can then look for the file at this specific place and load it very fast.

If the file did *not* live at this exact location, you'd receive a
``Class "Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle" does not exist.``
error. In Symfony2, a "class does not exist" means that the suspect class
namespace and physical location do not match. Basically, Symfony2 is looking
in one exact location for that class, but that location doesn't exist (or
contains a different class). In order for a class to be autoloaded, you
**never need to clear your cache** in Symfony2.

As mentioned before, for the autoloader to work, it needs to know that the
``Sensio`` namespace lives in the ``vendor/bundles`` directory and that, for
example, the ``Doctrine`` namespace lives in the ``vendor/doctrine/orm/lib/``
directory. This mapping is entirely controlled by Composer. Each
third-party library you load through composer has their settings defined
and Composer takes care of everything for you.

For this to work, all third-party libraries used by your project must be
defined in the ``composer.json`` file.

If you look at the ``HelloController`` from the Symfony2 Standard Edition you
can see that it lives in the ``Acme\DemoBundle\Controller`` namespace. Yet, the
``AcmeDemoBundle`` is not defined in your ``composer.json`` file. Nonetheless are
the files autoloaded. This is because you can tell composer to autoload files
from specific directories without defining a dependency:

.. code-block:: yaml

    "autoload": {
        "psr-0": { "": "src/" }
    }

Using the Console
-----------------

In symfony1, the console is in the root directory of your project and is
called ``symfony``:

.. code-block:: bash

    $ php symfony

In Symfony2, the console is now in the app sub-directory and is called
``console``:

.. code-block:: bash

    $ php app/console

Applications
------------

In a symfony1 project, it is common to have several applications: one for the
frontend and one for the backend for instance.

In a Symfony2 project, you only need to create one application (a blog
application, an intranet application, ...). Most of the time, if you want to
create a second application, you might instead create another project and
share some bundles between them.

And if you need to separate the frontend and the backend features of some
bundles, you can create sub-namespaces for controllers, sub-directories for
templates, different semantic configurations, separate routing configurations,
and so on.

Of course, there's nothing wrong with having multiple applications in your
project, that's entirely up to you. A second application would mean a new
directory, e.g. ``my_app/``, with the same basic setup as the ``app/`` directory.

.. tip::

    Read the definition of a :term:`Project`, an :term:`Application`, and a
    :term:`Bundle` in the glossary.

Bundles and Plugins
-------------------

In a symfony1 project, a plugin could contain configuration, modules, PHP
libraries, assets and anything else related to your project. In Symfony2,
the idea of a plugin is replaced by the "bundle". A bundle is even more powerful
than a plugin because the core Symfony2 framework is brought in via a series
of bundles. In Symfony2, bundles are first-class citizens that are so flexible
that even core code itself is a bundle.

In symfony1, a plugin must be enabled inside the ``ProjectConfiguration``
class::

    // config/ProjectConfiguration.class.php
    public function setup()
    {
        // some plugins here
        $this->enableAllPluginsExcept(array(...));
    }

In Symfony2, the bundles are activated inside the application kernel::

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            ...,
            new Acme\DemoBundle\AcmeDemoBundle(),
        );

        return $bundles;
    }

Routing (``routing.yml``) and Configuration (``config.yml``)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In symfony1, the ``routing.yml`` and ``app.yml`` configuration files were
automatically loaded inside any plugin. In Symfony2, routing and application
configuration inside a bundle must be included manually. For example, to
include a routing resource from a bundle called ``AcmeDemoBundle``, you can
do the following:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        _hello:
            resource: "@AcmeDemoBundle/Resources/config/routing.yml"

    .. code-block:: xml

        <!-- app/config/routing.yml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="@AcmeDemoBundle/Resources/config/routing.xml" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();
        $collection->addCollection($loader->import("@AcmeHelloBundle/Resources/config/routing.php"));

        return $collection;

This will load the routes found in the ``Resources/config/routing.yml`` file
of the ``AcmeDemoBundle``. The special ``@AcmeDemoBundle`` is a shortcut syntax
that, internally, resolves to the full path to that bundle.

You can use this same strategy to bring in configuration from a bundle:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        imports:
            - { resource: "@AcmeDemoBundle/Resources/config/config.yml" }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <imports>
            <import resource="@AcmeDemoBundle/Resources/config/config.xml" />
        </imports>

    .. code-block:: php

        // app/config/config.php
        $this->import('@AcmeDemoBundle/Resources/config/config.php')

In Symfony2, configuration is a bit like ``app.yml`` in symfony1, except much
more systematic. With ``app.yml``, you could simply create any keys you wanted.
By default, these entries were meaningless and depended entirely on how you
used them in your application:

.. code-block:: yaml

    # some app.yml file from symfony1
    all:
      email:
        from_address:  foo.bar@example.com

In Symfony2, you can also create arbitrary entries under the ``parameters``
key of your configuration:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            email.from_address: foo.bar@example.com

    .. code-block:: xml

        <parameters>
            <parameter key="email.from_address">foo.bar@example.com</parameter>
        </parameters>

    .. code-block:: php

        $container->setParameter('email.from_address', 'foo.bar@example.com');

You can now access this from a controller, for example::

    public function helloAction($name)
    {
        $fromAddress = $this->container->getParameter('email.from_address');
    }

In reality, the Symfony2 configuration is much more powerful and is used
primarily to configure objects that you can use. For more information, see
the chapter titled ":doc:`/book/service_container`".

.. _`Composer`: http://getcomposer.org
.. _`Symfony2 Standard Edition`: https://github.com/symfony/symfony-standard
