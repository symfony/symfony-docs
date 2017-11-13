.. index::
   single: Bundle; Inheritance

How to Override any Part of a Bundle
====================================

This document is a quick reference for how to override different parts of
third-party bundles.

.. tip::

    The bundle overriding mechanism means that you cannot use physical paths to
    refer to bundle's resources (e.g. ``__DIR__/config/services.xml``). Always
    use logical paths in your bundles (e.g. ``@AppBundle/Resources/config/services.xml``)
    and call the :ref:`locateResource() method <http-kernel-resource-locator>`
    to turn them into physical paths when needed.

Templates
---------

For information on overriding templates, see

* :doc:`/templating/overriding`.
* :doc:`/bundles/inheritance`

Routing
-------

Routing is never automatically imported in Symfony. If you want to include
the routes from any bundle, then they must be manually imported from somewhere
in your application (e.g. ``app/config/routing.yml``).

The easiest way to "override" a bundle's routing is to never import it at
all. Instead of importing a third-party bundle's routing, simply copy
that routing file into your application, modify it, and import it instead.

Controllers
-----------

Assuming the third-party bundle involved uses non-service controllers (which
is almost always the case), you can easily override controllers via bundle
inheritance. For more information, see :doc:`/bundles/inheritance`.
If the controller is a service, see the next section on how to override it.

Services & Configuration
------------------------

If you want to modify service definitions of another bundle, you can use a compiler
pass to change the class of the service or to modify method calls. In the following
example, the implementing class for the ``original-service-id`` is changed to
``Acme\DemoBundle\YourService``::

    // src/Acme/DemoBundle/DependencyInjection/Compiler/OverrideServiceCompilerPass.php
    namespace Acme\DemoBundle\DependencyInjection\Compiler;

    use Acme\DemoBundle\YourService;
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    class OverrideServiceCompilerPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
            $definition = $container->getDefinition('original-service-id');
            $definition->setClass(YourService::class);
        }
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

        # src/Acme/UserBundle/Resources/config/validation.yml
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

        <!-- src/Acme/UserBundle/Resources/config/validation.xml -->
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

.. caution::

    Translation files are not aware of :doc:`bundle inheritance </bundles/inheritance>`.
    If you want to override translations from the parent bundle or another bundle,
    make sure that the bundle containing *your* translations is loaded after any
    bundle whose translations you're overriding. This is done in ``AppKernel``.

    Finally, translations located in ``app/Resources/translations`` will override
    all the other translations since those files are always loaded last.
.. _`the Doctrine documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html#overrides
