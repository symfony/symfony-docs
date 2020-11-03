.. index::
    single: Using Parameters within a Dependency Injection Class

Using Parameters within a Dependency Injection Class
----------------------------------------------------

You have seen how to use configuration parameters within
:ref:`Symfony service containers <service-container-parameters>`.
There are special cases such as when you want, for instance, to use the
``%kernel.debug%`` parameter to make the services in your bundle enter
debug mode. For this case there is more work to do in order
to make the system understand the parameter value. By default,
your parameter ``%kernel.debug%`` will be treated as a string. Consider the
following example::

    // inside Configuration class
    $rootNode
        ->children()
            ->booleanNode('logging')->defaultValue('%kernel.debug%')->end()
            // ...
        ->end()
    ;

    // inside the Extension class
    $config = $this->processConfiguration($configuration, $configs);
    var_dump($config['logging']);

Now, examine the results to see this closely:

.. configuration-block::

    .. code-block:: yaml

        my_bundle:
            logging: true
            # true, as expected

        my_bundle:
            logging: '%kernel.debug%'
            # true/false (depends on 2nd argument of the Kernel class),
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
            xmlns:my-bundle="http://example.org/schema/dic/my_bundle"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://example.org/schema/dic/my_bundle
                https://example.org/schema/dic/my_bundle/my_bundle-1.0.xsd">

            <my-bundle:config logging="true"/>
            <!-- true, as expected -->

            <my-bundle:config logging="%kernel.debug%"/>
            <!-- true/false (depends on 2nd parameter of Kernel),
                 as expected, because %kernel.debug% inside configuration
                 gets evaluated before being passed to the extension -->

            <my-bundle:config/>
            <!-- passes the string "%kernel.debug%".
                 Which is always considered as true.
                 The Configurator does not know anything about
                 "%kernel.debug%" being a parameter. -->
        </container>

    .. code-block:: php

        $container->loadFromExtension('my_bundle', [
                'logging' => true,
                // true, as expected
            ]
        );

        $container->loadFromExtension('my_bundle', [
                'logging' => "%kernel.debug%",
                // true/false (depends on 2nd parameter of Kernel),
                // as expected, because %kernel.debug% inside configuration
                // gets evaluated before being passed to the extension
            ]
        );

        $container->loadFromExtension('my_bundle');
        // passes the string "%kernel.debug%".
        // Which is always considered as true.
        // The Configurator does not know anything about
        // "%kernel.debug%" being a parameter.

In order to support this use case, the ``Configuration`` class has to
be injected with this parameter via the extension as follows::

    namespace App\DependencyInjection;

    use Symfony\Component\Config\Definition\Builder\TreeBuilder;
    use Symfony\Component\Config\Definition\ConfigurationInterface;

    class Configuration implements ConfigurationInterface
    {
        private $debug;

        public function __construct($debug)
        {
            $this->debug = (bool) $debug;
        }

        public function getConfigTreeBuilder()
        {
            $treeBuilder = new TreeBuilder('my_bundle');

            $treeBuilder->getRootNode()
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

    namespace App\DependencyInjection;

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\DependencyInjection\Extension;

    class AppExtension extends Extension
    {
        // ...

        public function getConfiguration(array $config, ContainerBuilder $container)
        {
            return new Configuration($container->getParameter('kernel.debug'));
        }
    }

.. tip::

    There are some instances of ``%kernel.debug%`` usage within a
    ``Configurator`` class for example in TwigBundle. However, this is because
    the default parameter value is set by the Extension class.
