.. index::
   single: Bundle; Inheritance

How to Override any Part of a Bundle
====================================

When using a third-party bundle, you might want to customize or override some of
its features. This document describes ways of overriding the most common
features of a bundle.

.. tip::

    The bundle overriding mechanism means that you cannot use physical paths to
    refer to bundle's resources (e.g. ``__DIR__/config/services.xml``). Always
    use logical paths in your bundles (e.g. ``@FooBundle/Resources/config/services.xml``)
    and call the :ref:`locateResource() method <http-kernel-resource-locator>`
    to turn them into physical paths when needed.

Templates
---------

See :doc:`/templating/overriding`.

Routing
-------

Routing is never automatically imported in Symfony. If you want to include
the routes from any bundle, then they must be manually imported from somewhere
in your application (e.g. ``config/routes.yaml``).

The easiest way to "override" a bundle's routing is to never import it at
all. Instead of importing a third-party bundle's routing, simply copy
that routing file into your application, modify it, and import it instead.

Controllers
-----------

If the controller is a service, see the next section on how to override it.
Otherwise, define a new route + controller with the same path associated to the
controller you want to override (and make sure that the new route is loaded
before the bundle one).

Services & Configuration
------------------------

If you want to modify service definitions of another bundle, you can use a compiler
pass to change the class of the service or to modify method calls. In the following
example, the implementing class for the ``original-service-id`` is changed to
``App\YourService``:

.. code-block:: diff

    // src/Kernel.php
    namespace App;

    // ...
    + use App\Service\YourService;
    + use Symfony\Component\DependencyInjection\ContainerBuilder;
    + use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

    class Kernel extends BaseKernel implements CompilerPassInterface
    {
    +     public function process(ContainerBuilder $container)
    +     {
    +         $definition = $container->findDefinition('original-service-id');
    +         $definition->setClass(YourService::class);
    +     }
    }

For more information on compiler passes, see :doc:`/service_container/compiler_passes`.

Entities & Entity Mapping
-------------------------

Due to the way Doctrine works, it is not possible to override entity mapping
of a bundle. However, if a bundle provides a mapped superclass (such as the
``User`` entity in the FOSUserBundle) one can override attributes and
associations. Learn more about this feature and its limitations in
`the Doctrine documentation`_.

Forms
-----

Existing form types can be modified defining
:doc:`form type extensions </form/create_form_type_extension>`.

.. _override-validation:

Validation Metadata
-------------------

Symfony loads all validation configuration files from every bundle and
combines them into one validation metadata tree. This means you are able to
add new constraints to a property, but you cannot override them.

To overcome this, the 3rd party bundle needs to have configuration for
:doc:`validation groups </validation/groups>`. For instance, the FOSUserBundle
has this configuration. To create your own validation, add the constraints
to a new validation group:

.. configuration-block::

    .. code-block:: yaml

        # config/validator/validation.yaml
        FOS\UserBundle\Model\User:
            properties:
                plainPassword:
                    - NotBlank:
                        groups: [AcmeValidation]
                    - Length:
                        min: 6
                        minMessage: fos_user.password.short
                        groups: [AcmeValidation]

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="FOS\UserBundle\Model\User">
                <property name="plainPassword">
                    <constraint name="NotBlank">
                        <option name="groups">
                            <value>AcmeValidation</value>
                        </option>
                    </constraint>

                    <constraint name="Length">
                        <option name="min">6</option>
                        <option name="minMessage">fos_user.password.short</option>
                        <option name="groups">
                            <value>AcmeValidation</value>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

Now, update the FOSUserBundle configuration, so it uses your validation groups
instead of the original ones.

.. _override-translations:

Translations
------------

Translations are not related to bundles, but to domains. That means that you
can override the translations from any translation file, as long as it is in
:ref:`the correct domain <using-message-domains>`.

.. _`the Doctrine documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html#overrides
