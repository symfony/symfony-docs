.. index::
   single: Bundle; Installation

.. _how-to-install-3rd-party-bundles:

How to Install Third Party Bundles
==================================

Most bundles provide their own installation instructions. However, the
basic steps for installing a bundle are the same:

* `A) Install the Bundle`_
* `B) Enable the Bundle`_
* `C) Configure the Bundle`_

.. _a-add-composer-dependencies:

A) Install the Bundle
---------------------

In Symfony applications, dependencies like bundles are managed with `Composer`_,
the package manager used in modern PHP applications. Make sure to have Composer
installed and working on your computer. Then, follow these steps:

.. _find-out-the-name-of-the-bundle-on-packagist:

1) Find out the Name of the Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The README file for a bundle usually tells you its name (e.g. ``friendsofsymfony
/user-bundle`` for `FOSUserBundle`_). If it doesn't, you can search for the
bundle on `Packagist.org`_, the official repository of Composer packages.

.. tip::

    Looking for Symfony bundles? Try searching for `symfony-bundle topic on GitHub`_.

2) Install the Bundle via Composer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that you know the package name, you can install it via Composer:

.. code-block:: terminal

    $ composer require friendsofsymfony/user-bundle

This will choose the best version for your project, add it to ``composer.json``
and download its code into the ``vendor/`` directory. If you need a specific
version, include it as the second argument of the `composer require`_ command:

.. code-block:: terminal

    $ composer require friendsofsymfony/user-bundle "~2.0"

B) Enable the Bundle
--------------------

If your application uses :doc:`Symfony Flex </setup/flex>`, bundles are enabled
automatically, so you can skip this step entirely.

At this point, the bundle is installed in your Symfony project (e.g.
``vendor/friendsofsymfony/``) and the autoloader recognizes its classes. The
only thing you need to do now is register the bundle for some or all
:doc:`Symfony environments </configuration/environments>` using the
``config/bundles.php`` file::

    // config/bundles.php
    return [
        // ...

        // enable the bundle for any Symfony environment:
        FOS\UserBundle\FOSUserBundle::class => ['all' => true],

        // enable the bundle only for 'dev' and 'test' environment (not 'prod'):
        FOS\UserBundle\FOSUserBundle::class => ['dev' => true, 'test' => true],

        // enable the bundle only for 'prod' environment (not 'dev', 'test', etc.):
        FOS\UserBundle\FOSUserBundle::class => ['prod' => true],
    ];

C) Configure the Bundle
-----------------------

If your application uses :doc:`Symfony Flex </setup/flex>` and the bundle
defines a `Symfony Flex recipe`_, the initial bundle configuration is created
automatically in the ``config/packages/`` directory, so you just need to review
its default values.

Otherwise, you need the create those configuration files manually following the
bundle's documentation. In case you need it, you can also get a reference of the
bundle's configuration via the ``config:dump-reference`` command:

.. code-block:: terminal

    $ bin/console config:dump-reference WebProfilerBundle

    # you can also pass the bundle alias used in its config files:
    # bin/console config:dump-reference web_profiler

The output will look like this:

.. code-block:: yaml

    web_profiler:
        toolbar:              false
        intercept_redirects:  false
        excluded_ajax_paths:  '^/(app(_[\w]+)?\.php/)?_wdt'

.. tip::

    For complex bundles that define lots of configuration options, you can pass
    a second optional argument to the ``config:dump-reference`` command to only
    display a section of the entire configuration:

    .. code-block:: terminal

        $ bin/console config:dump-reference TwigBundle date

        # Default configuration for "TwigBundle" at path "date"
        date:
            format:               'F j, Y H:i'
            interval_format:      '%d days'
            timezone:             null

Other Setup
-----------

At this point, check the ``README`` file of the bundle to see if it requires
further steps to complete its integration with your Symfony application.

.. _`Composer`: https://getcomposer.org/
.. _`Packagist.org`: https://packagist.org
.. _`FOSUserBundle`: https://github.com/FriendsOfSymfony/FOSUserBundle
.. _`composer require`: https://getcomposer.org/doc/03-cli.md#require
.. _`symfony-bundle topic on GitHub`: https://github.com/search?q=topic%3Asymfony-bundle&type=Repositories
.. `Symfony Flex recipe`: https://github.com/symfony/recipes
