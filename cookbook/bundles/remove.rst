.. index::
    single: Bundle; Removing AcmeDemoBundle

How to Remove the AcmeDemoBundle
================================

The Symfony Standard Edition comes with a complete demo that lives inside a
bundle called AcmeDemoBundle. It is a great boilerplate to refer to while
starting a project, but you'll probably want to eventually remove it.

.. tip::

    This article uses the AcmeDemoBundle as an example, but you can use
    these steps to remove any bundle.

1. Unregister the Bundle in the ``AppKernel``
---------------------------------------------

To disconnect the bundle from the framework, you should remove the bundle from
the ``AppKernel::registerBundles()`` method. The bundle is normally found in
the ``$bundles`` array but the AcmeDemoBundle is only registered in the
development environment and you can find it inside the if statement below::

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(...);

            if (in_array($this->getEnvironment(), array('dev', 'test'))) {
                // comment or remove this line:
                // $bundles[] = new Acme\DemoBundle\AcmeDemoBundle();
                // ...
            }
        }
    }

2. Remove Bundle Configuration
------------------------------

Now that Symfony doesn't know about the bundle, you need to remove any
configuration and routing configuration inside the ``app/config`` directory
that refers to the bundle.

2.1 Remove Bundle Routing
~~~~~~~~~~~~~~~~~~~~~~~~~

The routing for the AcmeDemoBundle can be found in ``app/config/routing_dev.yml``.
Remove the ``_acme_demo`` entry at the bottom of this file.

2.2 Remove Bundle Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Some bundles contain configuration in one of the ``app/config/config*.yml``
files. Be sure to remove the related configuration from these files. You can
quickly spot bundle configuration by looking for an ``acme_demo`` (or whatever
the name of the bundle is, e.g. ``fos_user`` for the FOSUserBundle) string in
the configuration files.

The AcmeDemoBundle doesn't have configuration. However, the bundle is
used in the configuration for the ``app/config/security.yml`` file. You can
use it as a boilerplate for your own security, but you **can** also remove
everything: it doesn't matter to Symfony if you remove it or not.

3. Remove the Bundle from the Filesystem
----------------------------------------

Now you have removed every reference to the bundle in your application, you
should remove the bundle from the filesystem. The bundle is located in the
``src/Acme/DemoBundle`` directory. You should remove this directory and you
can remove the ``Acme`` directory as well.

.. tip::

    If you don't know the location of a bundle, you can use the
    :method:`Symfony\\Component\\HttpKernel\\Bundle\\BundleInterface::getPath` method
    to get the path of the bundle::

        dump($this->container->get('kernel')->getBundle('AcmeDemoBundle')->getPath());
        die();

3.1 Remove Bundle Assets
~~~~~~~~~~~~~~~~~~~~~~~~

Remove the assets of the bundle in the web/ directory (e.g.
``web/bundles/acmedemo`` for the AcmeDemoBundle).

4. Remove Integration in other Bundles
--------------------------------------

.. note::

    This doesn't apply to the AcmeDemoBundle - no other bundles depend
    on it, so you can skip this step.

Some bundles rely on other bundles, if you remove one of the two, the other
will probably not work. Be sure that no other bundles, third party or self-made,
rely on the bundle you are about to remove.

.. tip::

    If one bundle relies on another, in most cases it means that it uses
    some services from the bundle. Searching for the bundle alias string may
    help you spot them (e.g. ``acme_demo`` for bundles depending on AcmeDemoBundle).

.. tip::

    If a third party bundle relies on another bundle, you can find that bundle
    mentioned in the ``composer.json`` file included in the bundle directory.
