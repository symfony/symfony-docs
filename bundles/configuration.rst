How to Create Friendly Configuration for a Bundle
=================================================

If you open your main application configuration directory (usually
``config/packages/``), you'll see a number of different files, such as
``framework.yaml``, ``twig.yaml`` and ``doctrine.yaml``. Each of these
configures a specific bundle, allowing you to define options at a high level and
then let the bundle make all the low-level, complex changes based on your
settings.

For example, the following configuration tells the FrameworkBundle to enable the
form integration, which involves the definition of quite a few services as well
as integration of other related components:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            form: true

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'form' => true,
            ],
        ]);

There are two different ways of creating friendly configuration for a bundle:

#. :ref:`Using the main bundle class <bundle-friendly-config-bundle-class>`:
   this is recommended for new bundles and for bundles following the
   :ref:`recommended directory structure <bundles-directory-structure>`;
#. :ref:`Using the Bundle extension class <bundle-friendly-config-extension>`:
   this was the traditional way of doing it, but nowadays it's only recommended for
   bundles following the :ref:`legacy directory structure <bundles-legacy-directory-structure>`.

.. _using-the-bundle-class:
.. _bundle-friendly-config-bundle-class:

Using the AbstractBundle Class
------------------------------

In bundles extending the :class:`Symfony\\Component\\HttpKernel\\Bundle\\AbstractBundle`
class, you can add all the logic related to processing the configuration in that class::

    // src/AcmeSocialBundle.php
    namespace Acme\SocialBundle;

    use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
    use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

    class AcmeSocialBundle extends AbstractBundle
    {
        public function configure(DefinitionConfigurator $definition): void
        {
            $definition->rootNode()
                ->children()
                    ->arrayNode('twitter')
                        ->children()
                            ->integerNode('client_id')->end()
                            ->scalarNode('client_secret')->end()
                        ->end()
                    ->end() // twitter
                ->end()
            ;
        }

        public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
        {
            // the "$config" variable is already merged and processed so you can
            // use it directly to configure the service container (when defining an
            // extension class, you also have to do this merging and processing)
            $container->services()
                ->get('acme_social.twitter_client')
                ->arg(0, $config['twitter']['client_id'])
                ->arg(1, $config['twitter']['client_secret'])
            ;
        }
    }

.. note::

    The ``configure()`` and ``loadExtension()`` methods are called only at compile time.

.. tip::

    The ``AbstractBundle::configure()`` method also allows importing the
    configuration definition from one or more files::

        // src/AcmeSocialBundle.php
        namespace Acme\SocialBundle;

        // ...
        class AcmeSocialBundle extends AbstractBundle
        {
            public function configure(DefinitionConfigurator $definition): void
            {
                $definition->import('../config/definition.php');
                // you can also use glob patterns
                //$definition->import('../config/definition/*.php');
            }

            // ...
        }

    .. code-block:: php

        // config/definition.php
        use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

        return static function (DefinitionConfigurator $definition): void {
            $definition->rootNode()
                ->children()
                    ->scalarNode('foo')->defaultValue('bar')->end()
                ->end()
            ;
        };

.. _bundle-friendly-config-extension:

Using the Bundle Extension
--------------------------

This is the traditional way of creating friendly configuration for bundles. For new
bundles it's recommended to :ref:`use the main bundle class <bundle-friendly-config-bundle-class>`,
but the traditional way of creating an extension class still works.

Imagine you are creating a new bundle - AcmeSocialBundle - which provides
integration with X/Twitter. To make your bundle configurable to the user, you
can add some configuration that looks like this:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/acme_social.yaml
        acme_social:
            twitter:
                client_id: 123
                client_secret: your_secret

    .. code-block:: php

        // config/packages/acme_social.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'acme_social' => [
                'twitter' => [
                    'client_id' => 123,
                    'client_secret' => 'your_secret',
                ],
            ],
        ]);

The basic idea is that instead of having the user override individual
parameters, you let the user configure just a few, specifically created,
options. As the bundle developer, you then parse through that configuration and
load correct services and parameters inside an "Extension" class.

.. note::

    The root key of your bundle configuration (``acme_social`` in the previous
    example) is automatically determined from your bundle name (it's the
    `snake case`_ of the bundle name without the ``Bundle`` suffix).

.. seealso::

    Read more about the extension in :doc:`/bundles/extension`.

.. tip::

    If a bundle provides an Extension class, then you should *not* generally
    override any service container parameters from that bundle. The idea
    is that if an extension class is present, every setting that should be
    configurable should be present in the configuration made available by
    that class. In other words, the extension class defines all the public
    configuration settings for which backward compatibility will be maintained.

.. seealso::

    For parameter handling within a dependency injection container see
    :doc:`/configuration/using_parameters_in_dic`.

Processing the ``$configs`` Array
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

First things first, you have to create an extension class as explained in
:doc:`/bundles/extension`.

Whenever a user includes the ``acme_social`` key (which is the DI alias) in a
configuration file, the configuration under it is added to an array of
configurations and passed to the ``load()`` method of your extension (Symfony
automatically converts the configuration to an array).

For the configuration example in the previous section, the array passed to your
``load()`` method will look like this::

    [
        [
            'twitter' => [
                'client_id' => 123,
                'client_secret' => 'your_secret',
            ],
        ],
    ]

Notice that this is an *array of arrays*, not just a single flat array of the
configuration values. This is intentional, as it allows Symfony to parse several
configuration resources. For example, if ``acme_social`` appears in another
configuration file - say ``config/packages/dev/acme_social.yaml`` - with
different values beneath it, the incoming array might look like this::

    [
        // values from config/packages/acme_social.yaml
        [
            'twitter' => [
                'client_id' => 123,
                'client_secret' => 'your_secret',
            ],
        ],
        // values from config/packages/dev/acme_social.yaml
        [
            'twitter' => [
                'client_id' => 456,
            ],
        ],
    ]

The order of the two arrays depends on which one is set first.

But don't worry! Symfony's Config component will help you merge these values,
provide defaults and give the user validation errors on bad configuration.
Here's how it works. Create a ``Configuration`` class in the
``DependencyInjection`` directory and build a tree that defines the structure
of your bundle's configuration.

The ``Configuration`` class to handle the sample configuration looks like::

    // src/DependencyInjection/Configuration.php
    namespace Acme\SocialBundle\DependencyInjection;

    use Symfony\Component\Config\Definition\Builder\TreeBuilder;
    use Symfony\Component\Config\Definition\ConfigurationInterface;

    class Configuration implements ConfigurationInterface
    {
        public function getConfigTreeBuilder(): TreeBuilder
        {
            $treeBuilder = new TreeBuilder('acme_social');

            $treeBuilder->getRootNode()
                ->children()
                    ->arrayNode('twitter')
                        ->children()
                            ->integerNode('client_id')->end()
                            ->scalarNode('client_secret')->end()
                        ->end()
                    ->end() // twitter
                ->end()
            ;

            return $treeBuilder;
        }
    }

.. seealso::

    The ``Configuration`` class can be much more complicated than shown here,
    supporting "prototype" nodes, advanced validation, plural/singular normalization
    and advanced merging. You can read more about this in
    :doc:`the Config component documentation </components/config/definition>`. You
    can also see it in action by checking out some core Configuration
    classes, such as the one from the `FrameworkBundle Configuration`_ or the
    `TwigBundle Configuration`_.

This class can now be used in your ``load()`` method to merge configurations and
force validation (e.g. if an additional option was passed, an exception will be
thrown)::

    // src/DependencyInjection/AcmeSocialExtension.php
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        // you now have these 2 config keys
        // $config['twitter']['client_id'] and $config['twitter']['client_secret']
    }

The ``processConfiguration()`` method uses the configuration tree you've defined
in the ``Configuration`` class to validate, normalize and merge all the
configuration arrays together.

Now, you can use the ``$config`` variable to modify a service provided by your bundle.
For example, imagine your bundle has the following example config::

    // src/Resources/config/services.php
    namespace Symfony\Component\DependencyInjection\Loader\Configurator;

    use Acme\SocialBundle\TwitterClient;

    return function (ContainerConfigurator $container) {
        $container->services()
            ->set('acme_social.twitter_client', TwitterClient::class)
                ->args([abstract_arg('client_id'), abstract_arg('client_secret')]);
    };

In your extension, you can load this and dynamically set its arguments::

    // src/DependencyInjection/AcmeSocialExtension.php
    namespace Acme\SocialBundle\DependencyInjection;

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('acme_social.twitter_client');
        $definition->replaceArgument(0, $config['twitter']['client_id']);
        $definition->replaceArgument(1, $config['twitter']['client_secret']);
    }

.. tip::

    Instead of calling ``processConfiguration()`` in your extension each time you
    provide some configuration options, you might want to use the
    :class:`Symfony\\Component\\HttpKernel\\DependencyInjection\\ConfigurableExtension`
    to do this automatically for you::

        // src/DependencyInjection/HelloExtension.php
        namespace Acme\HelloBundle\DependencyInjection;

        use Symfony\Component\DependencyInjection\ContainerBuilder;
        use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

        class AcmeHelloExtension extends ConfigurableExtension
        {
            // note that this method is called loadInternal and not load
            protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
            {
                // ...
            }
        }

    This class uses the ``getConfiguration()`` method to get the Configuration
    instance.

.. sidebar:: Processing the Configuration yourself

    Using the Config component is fully optional. The ``load()`` method gets an
    array of configuration values. You can instead parse these arrays yourself
    (e.g. by overriding configurations and using :phpfunction:`isset` to check
    for the existence of a value)::

        public function load(array $configs, ContainerBuilder $container): void
        {
            $config = [];
            // let resources override the previous set value
            foreach ($configs as $subConfig) {
                $config = array_merge($config, $subConfig);
            }

            // ... now use the flat $config array
        }

Modifying the Configuration of Another Bundle
---------------------------------------------

If you have multiple bundles that depend on each other, it may be useful to
allow one ``Extension`` class to modify the configuration passed to another
bundle's ``Extension`` class. This can be achieved using a prepend extension.
For more details, see :doc:`/bundles/prepend_extension`.

Dump the Configuration
----------------------

The ``config:dump-reference`` command dumps the default configuration of a
bundle in the console using the Yaml format.

As long as your bundle's configuration is located in the standard location
(``<YourBundle>/src/DependencyInjection/Configuration``) and does not have
a constructor, it will work automatically. If you
have something different, your ``Extension`` class must override the
:method:`Extension::getConfiguration() <Symfony\\Component\\DependencyInjection\\Extension\\Extension::getConfiguration>`
method and return an instance of your ``Configuration``.

.. _`FrameworkBundle Configuration`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/Configuration.php
.. _`TwigBundle Configuration`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/TwigBundle/DependencyInjection/Configuration.php
.. _`snake case`: https://en.wikipedia.org/wiki/Snake_case
