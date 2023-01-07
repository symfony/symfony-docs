.. index::
   single: Configuration; Semantic
   single: Bundle; Extension configuration

How to Simplify Configuration of Multiple Bundles
=================================================

When building reusable and extensible applications, developers are often
faced with a choice: either create a single large bundle or multiple smaller
bundles. Creating a single bundle has the drawback that it's impossible for
users to remove unused functionality. Creating multiple
bundles has the drawback that configuration becomes more tedious and settings
often need to be repeated for various bundles.

It is possible to remove the disadvantage of the multiple bundle approach by
enabling a single Extension to prepend the settings for any bundle. It can use
the settings defined in the ``config/*`` files to prepend settings just as if
they had been written explicitly by the user in the application configuration.

For example, this could be used to configure the entity manager name to use in
multiple bundles. Or it can be used to enable an optional feature that depends
on another bundle being loaded as well.

To give an Extension the power to do this, it needs to implement
:class:`Symfony\\Component\\DependencyInjection\\Extension\\PrependExtensionInterface`::

    // src/Acme/HelloBundle/DependencyInjection/AcmeHelloExtension.php
    namespace Acme\HelloBundle\DependencyInjection;

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
    use Symfony\Component\HttpKernel\DependencyInjection\Extension;

    class AcmeHelloExtension extends Extension implements PrependExtensionInterface
    {
        // ...

        public function prepend(ContainerBuilder $container)
        {
            // ...
        }
    }

Inside the :method:`Symfony\\Component\\DependencyInjection\\Extension\\PrependExtensionInterface::prepend`
method, developers have full access to the :class:`Symfony\\Component\\DependencyInjection\\ContainerBuilder`
instance just before the :method:`Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface::load`
method is called on each of the registered bundle Extensions. In order to
prepend settings to a bundle extension developers can use the
:method:`Symfony\\Component\\DependencyInjection\\ContainerBuilder::prependExtensionConfig`
method on the :class:`Symfony\\Component\\DependencyInjection\\ContainerBuilder`
instance. As this method only prepends settings, any other settings done explicitly
inside the ``config/*`` files would override these prepended settings.

The following example illustrates how to prepend
a configuration setting in multiple bundles as well as disable a flag in multiple bundles
in case a specific other bundle is not registered::

    // src/Acme/HelloBundle/DependencyInjection/AcmeHelloExtension.php
    public function prepend(ContainerBuilder $container)
    {
        // get all bundles
        $bundles = $container->getParameter('kernel.bundles');
        // determine if AcmeGoodbyeBundle is registered
        if (!isset($bundles['AcmeGoodbyeBundle'])) {
            // disable AcmeGoodbyeBundle in bundles
            $config = ['use_acme_goodbye' => false];
            foreach ($container->getExtensions() as $name => $extension) {
                match ($name) {
                    // set use_acme_goodbye to false in the config of
                    // acme_something and acme_other
                    //
                    // note that if the user manually configured
                    // use_acme_goodbye to true in config/services.yaml
                    // then the setting would in the end be true and not false
                    'acme_something', 'acme_other' => $container->prependExtensionConfig($name, $config),
                    default => null
                };
            }
        }

        // get the configuration of AcmeHelloExtension (it's a list of configuration)
        $configs = $container->getExtensionConfig($this->getAlias());

        // iterate in reverse to preserve the original order after prepending the config
        foreach (array_reverse($configs) as $config) {
            // check if entity_manager_name is set in the "acme_hello" configuration
            if (isset($config['entity_manager_name'])) {
                // prepend the acme_something settings with the entity_manager_name
                $container->prependExtensionConfig('acme_something', [
                    'entity_manager_name' => $config['entity_manager_name'],
                ]);
            }
        }
    }

The above would be the equivalent of writing the following into the
``config/packages/acme_something.yaml`` in case AcmeGoodbyeBundle is not
registered and the ``entity_manager_name`` setting for ``acme_hello`` is set to
``non_default``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/acme_something.yaml
        acme_something:
            # ...
            use_acme_goodbye: false
            entity_manager_name: non_default

        acme_other:
            # ...
            use_acme_goodbye: false

    .. code-block:: xml

        <!-- config/packages/acme_something.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:acme-something="http://example.org/schema/dic/acme_something"
            xmlns:acme-other="http://example.org/schema/dic/acme_other"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://example.org/schema/dic/acme_something
                https://example.org/schema/dic/acme_something/acme_something-1.0.xsd
                http://example.org/schema/dic/acme_other
                https://example.org/schema/dic/acme_something/acme_other-1.0.xsd"
        >
            <acme-something:config use-acme-goodbye="false">
                <!-- ... -->
                <acme-something:entity-manager-name>non_default</acme-something:entity-manager-name>
            </acme-something:config>

            <acme-other:config use-acme-goodbye="false">
                <!-- ... -->
            </acme-other:config>

        </container>

    .. code-block:: php

        // config/packages/acme_something.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return static function (ContainerConfigurator $containerConfigurator) {
            $containerConfigurator->extension('acme_something', [
                // ...
                'use_acme_goodbye' => false,
                'entity_manager_name' => 'non_default',
            ]);
            $containerConfigurator->extension('acme_other', [
                // ...
                'use_acme_goodbye' => false,
            ]);
        };

Prepending Extension in the Bundle Class
----------------------------------------

.. versionadded:: 6.1

    The ``AbstractBundle`` class was introduced in Symfony 6.1.

You can also append or prepend extension configuration directly in your
Bundle class if you extend from the :class:`Symfony\\Component\\HttpKernel\\Bundle\\AbstractBundle`
class and define the :method:`Symfony\\Component\\HttpKernel\\Bundle\\AbstractBundle::prependExtension`
method::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
    use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

    class FooBundle extends AbstractBundle
    {
        public function prependExtension(ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void
        {
            // prepend
            $builder->prependExtensionConfig('framework', [
                'cache' => ['prefix_seed' => 'foo/bar'],
            ]);

            // append
            $containerConfigurator->extension('framework', [
                'cache' => ['prefix_seed' => 'foo/bar'],
            ]);

            // append from file
            $containerConfigurator->import('../config/packages/cache.php');
        }
    }

.. note::

    The ``prependExtension()`` method, like ``prepend()``, is called only at compile time.

More than one Bundle using PrependExtensionInterface
----------------------------------------------------

If there is more than one bundle that prepends the same extension and defines
the same key, the bundle that is registered **first** will take priority:
next bundles won't override this specific config setting.
