.. index::
   single: Configuration; Semantic
   single: Bundle; Extension configuration

How to simplify configuration of multiple Bundles
=================================================

When building reusable and extensible applications, developers are often
faced with a choice: either create a single large Bundle or multiple smaller
Bundles. Creating a single Bundle has the draw back that it's impossible for
users to choose to remove functionality they are not using. Creating multiple
Bundles has the draw back that configuration becomes more tedious and settings
often need to be repeated for various Bundles.

Using the below approach, it is possible to remove the disadvantage of the
multiple Bundle approach by enabling a single Extension to prepend the settings
for any Bundle. It can use the settings defined in the ``app/config/config.yml``
to prepend settings just as if they would have been written explicitly by the
user in the application configuration.

For example, this could be used to configure the entity manager name to use in
multiple Bundles. Or it can be used to enable an optional feature that depends
on another Bundle being loaded as well.

To give an Extension the power to do this, it needs to implement
:class:`Symfony\\Component\\DependencyInjection\\Extension\\PrependExtensionInterface`::

    // src/Acme/HelloBundle/DependencyInjection/AcmeHelloExtension.php
    namespace Acme\HelloBundle\DependencyInjection;

    use Symfony\Component\HttpKernel\DependencyInjection\Extension;
    use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

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
method is called on each of the registered Bundle Extensions. In order to
prepend settings to a Bundle extension developers can use the
:method:`Symfony\\Component\\DependencyInjection\\ContainerBuilder::prependExtensionConfig`
method on the :class:`Symfony\\Component\\DependencyInjection\\ContainerBuilder`
instance. As this method only prepends settings, any other settings done explicitly
inside the ``app/config/config.yml`` would override these prepended settings.

The following example illustrates how to prepend
a configuration setting in multiple Bundles as well as disable a flag in multiple Bundles
in case a specific other Bundle is not registered::

    public function prepend(ContainerBuilder $container)
    {
        // get all Bundles
        $bundles = $container->getParameter('kernel.bundles');
        // determine if AcmeGoodbyeBundle is registered
        if (!isset($bundles['AcmeGoodbyeBundle'])) {
            // disable AcmeGoodbyeBundle in Bundles
            $config = array('use_acme_goodbye' => false);
            foreach ($container->getExtensions() as $name => $extension) {
                switch ($name) {
                    case 'acme_something':
                    case 'acme_other':
                        // set use_acme_goodbye to false in the config of acme_something and acme_other
                        // note that if the user manually configured use_acme_goodbye to true in the
                        // app/config/config.yml then the setting would in the end be true and not false
                        $container->prependExtensionConfig($name, $config);
                        break;
                }
            }
        }

        // process the configuration of AcmeHelloExtension
        $configs = $container->getExtensionConfig($this->getAlias());
        // use the Configuration class to generate a config array with the settings "acme_hello"
        $config = $this->processConfiguration(new Configuration(), $configs);

        // check if entity_manager_name is set in the "acme_hello" configuration
        if (isset($config['entity_manager_name'])) {
            // prepend the acme_something settings with the entity_manager_name
            $config = array('entity_manager_name' => $config['entity_manager_name']);
            $container->prependExtensionConfig('acme_something', $config);
        }
    }

The above would be the equivalent of writing the following into the ``app/config/config.yml``
in case ``AcmeGoodbyeBundle`` is not registered and the ``entity_manager_name`` setting
for ``acme_hello`` is set to ``non_default``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        acme_something:
            # ...
            use_acme_goodbye: false
            entity_manager_name: non_default

        acme_other:
            # ...
            use_acme_goodbye: false

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <acme-something:config use-acme-goodbye="false">
            <acme-something:entity-manager-name>non_default</acme-something:entity-manager-name>
        </acme-something:config>

        <acme-other:config use-acme-goodbye="false" />

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('acme_something', array(
            ...,
            'use_acme_goodbye' => false,
            'entity_manager_name' => 'non_default',
        ));
        $container->loadFromExtension('acme_other', array(
            ...,
            'use_acme_goodbye' => false,
        ));
