.. index::
   single: Bundle; Installation

How to Install 3rd Party Bundles
================================

Most bundles provide their own installation instructions. However, the
basic steps for installing a bundle are the same:

* `A) Add Composer Dependencies`_
* `B) Enable the Bundle`_
* `C) Configure the Bundle`_

A) Add Composer Dependencies
----------------------------

Dependencies are managed with Composer, so if Composer is new to you, learn
some basics in `their documentation`_. This involves two steps:

1) Find out the Name of the Bundle on Packagist
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The README for a bundle (e.g. `FOSUserBundle`_) usually tells you its name
(e.g. ``friendsofsymfony/user-bundle``). If it doesn't, you can search for
the bundle on the `Packagist.org`_ site.

.. tip::

    Looking for bundles? Try searching at `KnpBundles.com`_: the unofficial
    archive of Symfony Bundles.

2) Install the Bundle via Composer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that you know the package name, you can install it via Composer:

.. code-block:: bash

    $ composer require friendsofsymfony/user-bundle

This will choose the best version for your project, add it to ``composer.json``
and download its code into the ``vendor/`` directory. If you need a specific
version, include it as the second argument of the `composer require`_ command:

.. code-block:: bash

    $ composer require friendsofsymfony/user-bundle "~2.0@dev"

B) Enable the Bundle
--------------------

At this point, the bundle is installed in your Symfony project (in
``vendor/friendsofsymfony/``) and the autoloader recognizes its classes.
The only thing you need to do now is register the bundle in ``AppKernel``::

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        // ...

        public function registerBundles()
        {
            $bundles = array(
                // ...
                new FOS\UserBundle\FOSUserBundle(),
            );

            // ...
        }
    }

By default, Symfony bundles are registered in all the application
:doc:`execution environments </cookbook/configuration/environments>`. If the bundle
is meant to be used only in some environment, register it within an ``if`` statement,
like the following example, where the FOSUserBundle is only enabled for the
``dev`` and ``test`` environments::

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        // ...

        public function registerBundles()
        {
            $bundles = array(
                // ...
            );

            if (in_array($this->getEnvironment(), array('dev', 'test'))) {
                $bundles[] = new FOS\UserBundle\FOSUserBundle();
            }

            // ...
        }
    }

C) Configure the Bundle
-----------------------

It's pretty common for a bundle to need some additional setup or configuration
in ``app/config/config.yml``. The bundle's documentation will tell you about
the configuration, but you can also get a reference of the bundle's configuration
via the ``config:dump-reference`` command:

.. code-block:: bash

    $ app/console config:dump-reference AsseticBundle

Instead of the full bundle name, you can also pass the short name used as the root
of the bundle's configuration:

.. code-block:: bash

    $ app/console config:dump-reference assetic

The output will look like this:

.. code-block:: text

    assetic:
        debug:                %kernel.debug%
        use_controller:
            enabled:              %kernel.debug%
            profiler:             false
        read_from:            %kernel.root_dir%/../web
        write_to:             %assetic.read_from%
        java:                 /usr/bin/java
        node:                 /usr/local/bin/node
        node_paths:           []
        # ...

Other Setup
-----------

At this point, check the ``README`` file of your brand new bundle to see
what to do next. Have fun!

.. _their documentation: https://getcomposer.org/doc/00-intro.md
.. _Packagist.org:       https://packagist.org
.. _FOSUserBundle:       https://github.com/FriendsOfSymfony/FOSUserBundle
.. _KnpBundles.com:      http://knpbundles.com/
.. _`composer require`:  https://getcomposer.org/doc/03-cli.md#require
