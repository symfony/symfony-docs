.. index::
   single: Bundle; Installation

How to install 3rd party Bundles
================================

Most bundles provide their own installation instructions. However, the
basic steps for installing a bundle are the same.

Add Composer Dependencies
-------------------------

In Symfony, dependencies are managed with Composer. It's a good idea to learn
some basics of Composer in `their documentation`_.

Before you can use Composer to install a bundle, you should look for a
`Packagist`_ package of that bundle. For example, if you search for the popular
`FOSUserBundle`_ you will find a package called `friendsofsymfony/user-bundle`_.

.. note::

    Packagist is the main archive for Composer. If you are searching
    for a bundle, the best thing you can do is check out
    `KnpBundles`_, it is the unofficial archive of Symfony Bundles. If
    a bundle contains a ``README`` file, it is displayed there and if it
    has a Packagist package it shows a link to the package. It's a
    really useful site to begin searching for bundles.

Now that you have the package name, you should determine the version
you want to use. Usually different versions of a bundle correspond to
a particular version of Symfony. This information should be in the ``README``
file. If it isn't, you can use the version you want. If you choose an incompatible
version, Composer will throw dependency errors when you try to install. If
this happens, you can try a different version.

Now you can add the bundle to your ``composer.json`` file and update the
dependencies. You can do this manually:

1. **Add it to the ``composer.json`` file:**

   .. code-block:: json

       {
           ...,
           "require": {
               ...,
               "friendsofsymfony/user-bundle": "2.0.*@dev"
           }
       }

2. **Update the dependency:**

   .. code-block:: bash

       $ php composer.phar update friendsofsymfony/user-bundle

   or update all dependencies

   .. code-block:: bash

       $ php composer.phar update

Or you can do this in one command:

.. code-block:: bash

    $ php composer.phar require friendsofsymfony/user-bundle:2.0.*@dev

Enable the Bundle
-----------------

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
                // ...,
                new FOS\UserBundle\FOSUserBundle(),
            );

            // ...
        }
    }

Configure the Bundle
--------------------

Usually a bundle requires some configuration to be added to app's
``app/config/config.yml`` file. The bundle's documentation will likely
describe that configuration. But you can also get a reference of the
bundle's config via the ``config:dump-reference`` command.

For instance, in order to look the reference of the ``assetic`` config you
can use this:

.. code-block:: bash

    $ app/console config:dump-reference AsseticBundle

or this:

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
what to do next.

.. _their documentation: http://getcomposer.org/doc/00-intro.md
.. _Packagist:           https://packagist.org
.. _FOSUserBundle:       https://github.com/FriendsOfSymfony/FOSUserBundle
.. _`friendsofsymfony/user-bundle`: https://packagist.org/packages/friendsofsymfony/user-bundle
.. _KnpBundles:          http://knpbundles.com/
