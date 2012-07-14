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

In order to override/extend a service, there are two options. First, you can
set the parameter holding the service's class name to your own class by setting
it in ``app/config/config.yml``. This of course is only possible if the class name is
defined as a parameter in the service config of the bundle containing the
service. For example, to override the class used for Symfony's ``translator``
service, you would override the ``translator.class`` parameter. Knowing exactly
which parameter to override may take some research. For the translator, the
parameter is defined and used in the ``Resources/config/translation.xml`` file
in the core FrameworkBundle:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        parameters:
            translator.class:      Acme\HelloBundle\Translation\Translator

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <parameters>
            <parameter key="translator.class">Acme\HelloBundle\Translation\Translator</parameter>
        </parameters>

    .. code-block:: php

        // app/config/config.php
        $container->setParameter('translator.class', 'Acme\HelloBundle\Translation\Translator');

Secondly, if the class is not available as a parameter, you want to make sure the
class is always overridden when your bundle is used, or you need to modify
something beyond just the class name, you should use a compiler pass::

    // src/Acme/FooBundle/DependencyInjection/Compiler/OverrideServiceCompilerPass.php
    namespace Acme\DemoBundle\DependencyInjection\Compiler;

    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    class OverrideServiceCompilerPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
            $definition = $container->getDefinition('original-service-id');
            $definition->setClass('Acme\DemoBundle\YourService');
        }
    }

In this example we fetch the service definition of the original service, and set
its class name to our own class.

See :doc:`/cookbook/service_container/compiler_passes` for information on how to use
compiler passes. If you want to do something beyond just overriding the class -
like adding a method call - you can only use the compiler pass method.

Entities & Entity mapping
-------------------------

In progress...

Forms
-----

In order to override a form type, it has to be registered as a service (meaning
it is tagged as "form.type"). You can then override it as you would override any
service as explained in `Services & Configuration`_. This, of course, will only
work if the type is referred to by its alias rather than being instantiated,
e.g.::

    $builder->add('name', 'custom_type');

rather than::

    $builder->add('name', new CustomType());

Validation metadata
-------------------

In progress...

Translations
------------

In progress...
