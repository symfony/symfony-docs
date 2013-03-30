.. index::
   single: Bundle; Installation

How to install 3rd party bundles
================================

Most bundles provide their own installation instructions. However, the
basic steps for installing a bundle are the same.

Add composer dependencies
-------------------------

Starting from Symfony 2.1 dependencies are managed with Composer. It's
a good idea to learn some basics of Composer in `their documentation`_.

Before you can use composer to install a bundle, you should look for a
`Packagist`_ package of that bundle. For example, for the
`FOSUserBundle`_ you should look for a
``friendsofsymfony/user-bundle`` package and it does exists:
https://packagist.org/packages/friendsofsymfony/user-bundle .

.. note::

    Packagist is the main archive for Composer. If you are searching
    for a bundle, the best thing you can do is check out
    `KnpBundles`_, it is the unofficial achive of Symfony Bundles. If
    a bundle contains a ``README`` file, it is displayed there and if it
    has a Packagist package it shows a link to the package. It's a
    really usefull site to begin searching for bundles.

Now that you have the package name, you should determine the version
you want to use. Usually different versions of a bundle correspond to
a particular version of Symfony, this should be in the ``README`` file
(in the Package, which you can view on Github or KnpBundles). If it
isn't in the ``README``, you can use the version you want. In the case
of the FOSUserBundle, the ``README`` file has a caution that version
1.2.0 must be used for Symfony 2.0 and 1.3+ for Symfony
2.1+. Packagist provides require statements for all existing
versions. For the current development version it is now
``"friendsofsymfony/user-bundle": "2.0.*@dev"``.

Now we can add the bundle to our ``composer.json`` file and update the
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

Enable the bundle
-----------------

Now the bundle is installed into our Symfony project (in
``vendor/friendsofsymfony/``) and the autoloader recognizes this
bundle. The only thing we need to do now is registering the bundle in
the ``AppKernel``::

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

Configure the bundle
--------------------

Usually bundles require some configuration to be added to app's
``app/config/config.yml`` file. The bundle's documentation will likely
describe that configuration. But you can also get a reference of the
bundle's config via ``config:dump-reference`` command.

For instance, in order to look the reference of the assetic config we
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

.. _their documentation: http://getcomposer.org/doc/00-intro.md
.. _Packagist:           https://packagist.org
.. _FOSUserBundle:       https://github.com/FriendsOfSymfony/FOSUserBundle
.. _KnpBundles:          http://knpbundles.com/
