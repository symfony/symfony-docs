.. index::
    single: Using Parameters within a Dependency Injection Class

Using Parameters within a Dependency Injection Class
----------------------------------------------------

You have seen how to use configuration parameters within
:ref:`Symfony service containers <book-service-container-parameters>`.
There are special cases such as when you want, for instance, to use the
``%kernel.debug%`` parameter to make the services in your bundle enter
debug mode. For this case there is more work to do in order
to make the system understand the parameter value. By default
your parameter ``%kernel.debug%`` will be treated as a
simple string. Consider this example with the AcmeDemoBundle::

    // Inside Configuration class
    $rootNode
        ->children()
            ->booleanNode('logging')->defaultValue('%kernel.debug%')->end()
            // ...
        ->end()
    ;

    // Inside the Extension class
    $config = $this->processConfiguration($configuration, $configs);
    var_dump($config['logging']);

Now, examine the results to see this closely:

.. configuration-block::

    .. code-block:: yaml

        my_bundle:
            logging: true
            # true, as expected

        my_bundle:
            logging: %kernel.debug%
            # true/false (depends on 2nd parameter of AppKernel),
            # as expected, because %kernel.debug% inside configuration
            # gets evaluated before being passed to the extension

        my_bundle: ~
        # passes the string "%kernel.debug%".
        # Which is always considered as true.
        # The Configurator does not know anything about
        # "%kernel.debug%" being a parameter.

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:my-bundle="http://example.org/schema/dic/my_bundle">

            <my-bundle:config logging="true" />
            <!-- true, as expected -->

             <my-bundle:config logging="%kernel.debug%" />
             <!-- true/false (depends on 2nd parameter of AppKernel),
                  as expected, because %kernel.debug% inside configuration
                  gets evaluated before being passed to the extension -->

            <my-bundle:config />
            <!-- passes the string "%kernel.debug%".
                 Which is always considered as true.
                 The Configurator does not know anything about
                 "%kernel.debug%" being a parameter. -->
        </container>

    .. code-block:: php

        $container->loadFromExtension('my_bundle', array(
                'logging' => true,
                // true, as expected
            )
        );

        $container->loadFromExtension('my_bundle', array(
                'logging' => "%kernel.debug%",
                // true/false (depends on 2nd parameter of AppKernel),
                // as expected, because %kernel.debug% inside configuration
                // gets evaluated before being passed to the extension
            )
        );

        $container->loadFromExtension('my_bundle');
        // passes the string "%kernel.debug%".
        // Which is always considered as true.
        // The Configurator does not know anything about
        // "%kernel.debug%" being a parameter.

In order to support this use case, the ``Configuration`` class has to
be injected with this parameter via the extension as follows::

    namespace Acme\DemoBundle\DependencyInjection;

    use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
    use Symfony\Component\Config\Definition\Builder\TreeBuilder;
    use Symfony\Component\Config\Definition\ConfigurationInterface;

    class Configuration implements ConfigurationInterface
    {
        private $debug;

        public function  __construct($debug)
        {
            $this->debug = (Boolean) $debug;
        }

        public function getConfigTreeBuilder()
        {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('acme_demo');

            $rootNode
                ->children()
                    // ...
                    ->booleanNode('logging')->defaultValue($this->debug)->end()
                    // ...
                ->end()
            ;

            return $treeBuilder;
        }
    }

And set it in the constructor of ``Configuration`` via the ``Extension`` class::

    namespace Acme\DemoBundle\DependencyInjection;

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
    use Symfony\Component\HttpKernel\DependencyInjection\Extension;
    use Symfony\Component\Config\FileLocator;

    class AcmeDemoExtension extends Extension
    {
        // ...

        public function getConfiguration(array $config, ContainerBuilder $container)
        {
            return new Configuration($container->getParameter('kernel.debug'));
        }
    }

.. sidebar:: Setting the Default in the Extension

    There are some instances of ``%kernel.debug%`` usage within a ``Configurator``
    class in TwigBundle and AsseticBundle, however this is because the default
    parameter value is set by the Extension class. For example in AsseticBundle,
    you can find::

        $container->setParameter('assetic.debug', $config['debug']);

    The string ``%kernel.debug%`` passed here as an argument handles the
    interpreting job to the container which in turn does the evaluation.
    Both ways accomplish similar goals. AsseticBundle will not use
    ``%kernel.debug%`` but rather the new ``%assetic.debug%`` parameter.
