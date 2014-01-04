.. index::
single: Configuration

Configuration Usages
====================

Parameters are common in configuration such as ``%kernel.root_dir%``
(which is used for passing a path) or ``%kernel.debug%`` (which is used for
passing a boolean indicating if mode is debug). The syntax wrapping an
alias string with ``%`` conveys that this is a value defined as a parameter
and will be evaluated somewhere down the line and used. Within Symfony
we can have parameters defined in a bundle and used only in that bundle and
also parameters defined in a bundle and used in another. Usually they would
have a namespace like ``demo_bundle.service_doer.local_parameter`` making
explicit where this parameter comes from. If the parameter is global then
it may not have a namespace, e.g. ``%some_global_option_here%``.

We can have parameters being used inside ``parameters.yml``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/parameters.yml
        parameters:
            payment_test_mode: %kernel.root_dir%

Inside ``config.yml`` and other configuration files building larger
strings:

.. configuration-block::

    .. code-block:: yaml


        # app/config/config.yml
        my_bundle:
            local:
                directory:  %kernel.root_dir%/../web/media/image

We can also use parameters in service configuration files:

.. configuration-block::

    .. code-block:: xml

        <parameters>
            <parameter id="some_service.class" class="\Acme\DemoBundle\Service\Doer">
        </parameters>

        <services>
            <service id="%some_service%" class="%some_service.class%" />
        </services>

For more information on how parameters are used in Symfony please see
:ref:`parameters <book-service-container-parameters>`.

Besides these usages above we can use this syntax in routing files and handle
parameters in special cases as discussed below.

If for instance, there is a use case in which we want to use the
``%kernel.debug%`` debug mode parameter to make our bundle adapt its
configuration depending on this. For this case we cannot use
the syntax directly and expect this to work. The configuration handling
will just tread this ``%kernel.debug%`` as a string. Let's consider
this example with ``AcmeDemoBundle``:

.. code-block:: php

    // Inside Configuration class
    ->booleanNode('logging')->defaultValue('%kernel.debug%')->end()

    // Inside the Extension class
    $config = $this->processConfiguration($configuration, $configs);
    var_dump($config['logging']);

Now let's examine the results to see this closely:

.. configuration-block::

    .. code-block:: xml

        my_bundle:
            logging: true
            # true, as expected

        my_bundle:
            logging: %kernel.debug%
            # true/false (depends on 2nd parameter of AppKernel),
            # as expected, because %kernel.debug% inside configuration
            # gets evaluated before being passed to the extension

        my_bundle: ~
        # passes the string ``%kernel.debug%``.
        # Which is always considered as true.
        # Configurator class does not know anything about the default
        # string ``%kernel.debug%``.

In order to support this use case ``Configuration`` class has to
be injected with this parameter via the extension as follows:

.. code-block:: php

    <?php

    namespace Acme\DemoBundle\DependencyInjection;

    use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
    use Symfony\Component\Config\Definition\Builder\TreeBuilder;
    use Symfony\Component\Config\Definition\ConfigurationInterface;

    class Configuration implements ConfigurationInterface
    {
        private $debug;

        /**
         * Constructor
         *
         * @param Boolean $debug Whether to use the debug mode
         */
        public function  __construct($debug)
        {
            $this->debug = (Boolean) $debug;
        }

         /**
         * {@inheritDoc}
         */
        public function getConfigTreeBuilder()
        {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('acme_demo');

            $rootNode
                // ...
                ->booleanNode('logging')->defaultValue($this->debug)->end()
                // ...
            ;

            return $treeBuilder;
        }
    }

And set it in the constructor of ``Configuration`` via the Extension class:

.. code-block:: php

    <?php

    namespace Acme\DemoBundle\DependencyInjection;

    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Definition;
    use Symfony\Component\Config\FileLocator;

    class AcmeDemoExtension extends AbstractDoctrineExtension
    {
        // ...

        /**
         * {@inheritDoc}
         */
        public function getConfiguration(array $config, ContainerBuilder $container)
        {
            return new Configuration($container->getParameter('kernel.debug'));
        }
    }

There are some instances of ``%kernel.debug%`` usage within a ``Configurator``
class in ``TwigBundle`` and ``AsseticBundle``, however this is because they are
setting the parameter value in the container via the Extension class. For
example in ``AsseticBundle`` we have:

.. code-block:: php

    $container->setParameter('assetic.debug', $config['debug']);

The string ``%kernel.debug%`` passed here as an argument handles the
interpreting job to the container which in turn does the evaluation.
Both ways accomplish similar goals. ``AsseticBundle`` will not use
anymore ``%kernel.debug%`` but rather the new ``%assetic.debug%`` parameter.
