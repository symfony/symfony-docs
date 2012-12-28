.. index::
    single: Bundle; Removing AcmeDemoBundle

How to remove the AcmeDemoBundle
================================

The Symfony2 Standard Edition comes with a complete demo, that lives inside a
bundle called ``AcmeDemoBundle``. It is a great boilerplate to refer to while
starting with your project, but later on the project, it will become usefull
to remove this bundle.

.. tip::

    This article uses the AcmeDemoBundle as an example, you can use this
    article on every bundle you want to remove.

1. Unregister the bundle in the ``AppKernel``
---------------------------------------------

To disconnect the bundle from the framework, you should remove the bundle from
the ``Appkernel::registerBundles()`` method. The bundle is normally found in
the ``$bundles`` array but the ``AcmeDemoBundle`` is only registered in a
development environment and you can find him in the if statement thereafter::

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

2. Remove bundle configuration
------------------------------

Now Symfony doesn't know about the bundle, you need to remove any
configuration and routing configuration inside the ``app/config`` directory
that refers to the bundle.

2.1 Remove bundle routing
~~~~~~~~~~~~~~~~~~~~~~~~~

The routing for the AcmeDemoBundle can be found in
``app/config/routing_dev.yml``. The routes are ``_welcome``, ``_demo_secured``
and ``_demo``.

2.2 Remove bundle configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Some bundles contains configuration in one of the ``app/config/config*.yml``
files. Be sure to remove the related configuration from these files. You can
quickly spot bundle configuration by looking at a ``acme_demo`` (or whatever
the name of the bundle is, e.g. ``fos_user`` with the FOSUserBundle) string in
the configuration files.

The AcmeDemoBundle doesn't have configuration. However, the bundle has set up
the ``app/config/security.yml`` file. You can use it as a boilerplate for your
own security, but you **can** also remove everything: It doesn't make sense to
Symfony if you remove it or not.

3. Remove integration in other bundles
--------------------------------------

Some bundles rely on other bundles, if you remove one of the two, the other
will properbly not work. Be sure that no other bundles, third party or self
made, relies on the bundle you are about to remove.

.. tip::

    If a bundle relies on another bundle, it means in most of the cases that
    it uses some services from the other bundle. Searching for a ``acme_demo``
    string will help you spot them.

.. tip::

    If a third party bundle relies on another bundle, you can find the bundle
    in the ``composer.json`` file included in the bundle directory.

4. Remove the bundle from the filesystem
----------------------------------------

Now you have removed every reference to the bundle in the Symfony2
application, the last thing you should do is removing the bundle from the file
system. The bundle is located in the ``src/Acme/DemoBundle`` directory. You
should remove this directory and you can remove the ``Acme`` directory as
well, you likely won't get other bundles in that vendor.

.. tip::

    If you don't know the location of a bundle, you can use the
    :method:`Symfony\\Bundle\\FrameworkBundle\\Bundle\\Bundle::getPath` method
    to get the path of the bundle::

        echo $this->container->get('kernel')->getBundle('AcmeDemoBundle')->getPath();
