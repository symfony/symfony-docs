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

In order to override/extend a service, there are two options. Firstly, you can
set the parameter holding the service's class name to your own class by setting
it in the config.yml. This of course is only possible if the class name is
defined as a parameter in the service config of the bundle containing the
service. Secondly, if this is not the case, or if you want to make sure the
class is always overridden when your bundle is used, you should use a compiler
pass:

.. code-block:: php
    namespace Foo\BarBundle\DependencyInjection\Compiler;

    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    class OverrideServiceCompilerPass implements CompilerPassInterface
    {

        public function process(ContainerBuilder $container)
        {
            $definition = $container->getDefinition('original-service-id');
            $definition->setClass('Foo\BarBundle\YourService');
        }
    }

In this example we fetch the service definition of the original service, and set
it's class name to our own class.

See `/cookbook/service_container/compiler_passes` for information on how to use
compiler passes. If you want to do something beyond just overriding the class -
like adding a method call - You can only use the compiler pass method.

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