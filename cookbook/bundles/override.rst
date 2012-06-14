.. index::
   single: Bundle; Inheritance

How to Override any Part of a Bundle
====================================

This document is a quick reference for how to override different parts of
third-party bundles.

Templates
---------

For information on overriding templates, see
* :ref:`overriding-bundle-templates`.
* :doc:`/cookbook/bundles/inheritance`

Routing
-------

Routing is never automatically imported in Symfony2. If you want to include
the routes from any bundle, then they must be manually imported from somewhere
in your application (e.g. ``app/config/routing.yml``).

The easiest way to "override" a bundle's routing is to never import it at
all. Instead of importing a third-party bundle's routing, simply copying
that routing file into your application, modify it, and import it instead.

Controllers
-----------

Assuming the third-party bundle involved uses non-service controllers (which
is almost always the case), you can easily override controllers via bundle
inheritance. For more information, see :doc:`/cookbook/bundles/inheritance`.

Services & Configuration
------------------------

.. note::

    Whenever you are extending a (part of a) bundle, make sure that your bundle
    is registered in the kernel **after** the bundle you're trying to override
    parts of. Otherwise, your config that is supposed to override bundle
    configuration, is instead overridden by it!

In order to completely override a service, just define the service as you would
usual, but making sure the id of the service is identical to the one you are
overriding.

In order to extend a service (e.g. just add a method, but leaving the
dependencies or tags intact), make sure the class name is defined as a parameter
in the service config of the bundle containing the service. You can then either
set this parameter in your config.yml, or, if you're going to reuse your bundle
and it should always override the class, in your bundle you can override the
class name by setting the parameter directly in the container in the Extension
class of your bundle:

.. code-block:: html+php
    <?php

    namespace Foo\BarBundle\DependencyInjection;

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\HttpKernel\DependencyInjection\Extension;
    use Symfony\Component\DependencyInjection\Loader;

    class FooBarExtension extends Extension
    {

        public function load(array $configs, ContainerBuilder $container)
        {
            $configuration = new Configuration();
            $config = $this->processConfiguration($configuration, $configs);

            $container->setParameter('parameter_name.containing.service_class', 'Foo\BarBundle\Service\Service');

            $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('services.xml');
        }
    }

If you want to do something beyond just overriding a parameter - like adding a
method call - it must be done as a compiler pass. See
`/cookbook/service_container/compiler_passes`

Entities & Entity mapping
-------------------------

In progress...

Forms
-----

In progress...

Validation metadata
-------------------

In progress...

Translations
------------

In progress...