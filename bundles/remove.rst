.. index::
    single: Bundle; Removing a bundle

How to Remove a Bundle
======================

1. Unregister the Bundle in the ``AppKernel``
---------------------------------------------

To disconnect the bundle from the framework, you should remove the bundle from
the ``AppKernel::registerBundles()`` method. The bundle will likely be found in
the ``$bundles`` array declaration or added to it in a later statement if the
bundle is only registered in the development environment::

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                new Acme\DemoBundle\AcmeDemoBundle(),
            );

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

*Some* bundles require you to import routing configuration. Check for references
to the bundle in the routing configuration (inside ``config/routes/``).  If you
find any references, remove them completely.

2.2 Remove Bundle Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Some bundles contain configuration in one of the ``app/config/config*.yml``
files. Be sure to remove the related configuration from these files. You can
quickly spot bundle configuration by looking for an ``acme_demo`` (or whatever
the name of the bundle is, e.g. ``fos_user`` for the FOSUserBundle) string in
the configuration files.

3. Remove the Bundle from the Filesystem
----------------------------------------

Now you have removed every reference to the bundle in your application, you
should remove the bundle from the filesystem. The bundle will be located in
`src/` for example the ``src/Acme/DemoBundle`` directory. You should remove this
directory, and any parent directories that are now empty (e.g. ``src/Acme/``).

.. tip::

    If you don't know the location of a bundle, you can use the
    :method:`Symfony\\Component\\HttpKernel\\Bundle\\BundleInterface::getPath` method
    to get the path of the bundle::

        dump($this->container->get('kernel')->getBundle('AcmeDemoBundle')->getPath());
        die();

3.1 Remove Bundle Assets
~~~~~~~~~~~~~~~~~~~~~~~~

Remove the assets of the bundle in the public/ directory (e.g.
``public/bundles/acmedemo`` for the AcmeDemoBundle).

4. Remove Integration in other Bundles
--------------------------------------

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
