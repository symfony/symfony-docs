.. index::
   single: Configuration; Semantic
   single: Bundle; Extension configuration

How to expose a Semantic Configuration for a Bundle
===================================================

If you open your application configuration file (usually ``app/config/config.yml``),
you'll see a number of different configuration "namespaces", such as ``framework``,
``twig``, and ``doctrine``. Each of these configures a specific bundle, allowing
you to configure things at a high level and then let the bundle make all the
low-level, complex changes that result.

For example, the following tells the FrameworkBundle to enable the form
integration, which involves the defining of quite a few services as well
as integration of other related components:

.. configuration-block::

    .. code-block:: yaml

        framework:
            # ...
            form: true

    .. code-block:: xml

        <framework:config>
            <framework:form />
        </framework:config>

    .. code-block:: php

        $container->loadFromExtension('framework', array(
            // ...
            'form' => true,
            // ...
        ));

When you create a bundle, you have two choices on how to handle configuration:

1. **Normal Service Configuration** (*easy*):

    You can specify your services in a configuration file (e.g. ``services.yml``)
    that lives in your bundle and then import it from your main application
    configuration. This is really easy, quick and totally effective. If you
    make use of :ref:`parameters <book-service-container-parameters>`, then
    you still have the flexibility to customize your bundle from your application
    configuration. See ":ref:`service-container-imports-directive`" for more
    details.

2. **Exposing Semantic Configuration** (*advanced*):

    This is the way configuration is done with the core bundles (as described
    above). The basic idea is that, instead of having the user override individual
    parameters, you let the user configure just a few, specifically created
    options. As the bundle developer, you then parse through that configuration
    and load services inside an "Extension" class. With this method, you won't
    need to import any configuration resources from your main application
    configuration: the Extension class can handle all of this.

The second option - which you'll learn about in this article - is much more
flexible, but also requires more time to setup. If you're wondering which
method you should use, it's probably a good idea to start with method #1,
and then change to #2 later if you need to. If you plan to distribute your
bundle, the second option is recommended.

The second method has several specific advantages:

* Much more powerful than simply defining parameters: a specific option value
  might trigger the creation of many service definitions;

* Ability to have configuration hierarchy;

* Smart merging when several configuration files (e.g. ``config_dev.yml``
  and ``config.yml``) override each other's configuration;

* Configuration validation (if you use a :ref:`Configuration Class <cookbook-bundles-extension-config-class>`);

* IDE auto-completion when you create an XSD and developers use XML.

.. sidebar:: Overriding bundle parameters

    If a Bundle provides an Extension class, then you should generally *not*
    override any service container parameters from that bundle. The idea
    is that if an Extension class is present, every setting that should be
    configurable should be present in the configuration made available by
    that class. In other words the extension class defines all the publicly
    supported configuration settings for which backward compatibility will
    be maintained.

.. seealso::

    For parameter handling within a Dependency Injection class see
    :doc:`/cookbook/configuration/using_parameters_in_dic`.

.. index::
   single: Bundle; Extension
   single: DependencyInjection; Extension

Creating an Extension Class
---------------------------

If you do choose to expose a semantic configuration for your bundle, you'll
first need to create a new "Extension" class, which will handle the process.
This class should live in the ``DependencyInjection`` directory of your bundle
and its name should be constructed by replacing the ``Bundle`` suffix of the
Bundle class name with ``Extension``. For example, the Extension class of
``AcmeHelloBundle`` would be called ``AcmeHelloExtension``::

    // Acme/HelloBundle/DependencyInjection/AcmeHelloExtension.php
    namespace Acme\HelloBundle\DependencyInjection;

    use Symfony\Component\HttpKernel\DependencyInjection\Extension;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    class AcmeHelloExtension extends Extension
    {
        public function load(array $configs, ContainerBuilder $container)
        {
            // ... where all of the heavy logic is done
        }

        public function getXsdValidationBasePath()
        {
            return __DIR__.'/../Resources/config/';
        }

        public function getNamespace()
        {
            return 'http://www.example.com/symfony/schema/';
        }
    }

.. note::

    The ``getXsdValidationBasePath`` and ``getNamespace`` methods are only
    required if the bundle provides optional XSD's for the configuration.

The presence of the previous class means that you can now define an ``acme_hello``
configuration namespace in any configuration file. The namespace ``acme_hello``
is constructed from the extension's class name by removing the word ``Extension``
and then lowercasing and underscoring the rest of the name. In other words,
``AcmeHelloExtension`` becomes ``acme_hello``.

You can begin specifying configuration under this namespace immediately:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        acme_hello: ~

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:acme_hello="http://www.example.com/symfony/schema/"
            xsi:schemaLocation="http://www.example.com/symfony/schema/ http://www.example.com/symfony/schema/hello-1.0.xsd">

           <acme_hello:config />

           <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('acme_hello', array());

.. tip::

    If you follow the naming conventions laid out above, then the ``load()``
    method of your extension code is always called as long as your bundle
    is registered in the Kernel. In other words, even if the user does not
    provide any configuration (i.e. the ``acme_hello`` entry doesn't even
    appear), the ``load()`` method will be called and passed an empty ``$configs``
    array. You can still provide some sensible defaults for your bundle if
    you want.

Registering the Extension Class
-------------------------------

An Extension class will automatically be registered by Symfony2 when
following these simple conventions:

* The extension must be stored in the ``DependencyInjection`` sub-namespace;

* The extension must be named after the bundle name and suffixed with
  ``Extension`` (``AcmeHelloExtension`` for ``AcmeHelloBundle``);

* The extension *should* provide an XSD schema (but will be registered automatically
  regardless).

Manually Registering an Extension Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When not following the conventions, you will have to manually register your
extension. To manually register an extension class override the
:method:`Bundle::build() <Symfony\\Component\\HttpKernel\\Bundle\\Bundle::build>`
method in your bundle::

    // ...
    use Acme\HelloBundle\DependencyInjection\UnconventionalExtensionClass;

    class AcmeHelloBundle extends Bundle
    {
        public function build(ContainerBuilder $container)
        {
            parent::build($container);

            // register extensions that do not follow the conventions manually
            $container->registerExtension(new UnconventionalExtensionClass());
        }
    }

In this case, the extension class must also implement a ``getAlias()`` method
and return a unique alias named after the bundle (e.g. ``acme_hello``). This
is required because the class name doesn't follow the conventions by ending
in ``Extension``.

Additionally, the ``load()`` method of your extension will *only* be called
if the user specifies the ``acme_hello`` alias in at least one configuration
file. Once again, this is because the Extension class doesn't follow the
conventions set out above, so nothing happens automatically.

Parsing the ``$configs`` Array
------------------------------

Whenever a user includes the ``acme_hello`` namespace in a configuration file,
the configuration under it is added to an array of configurations and
passed to the ``load()`` method of your extension (Symfony2 automatically
converts XML and YAML to an array).

Take the following configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        acme_hello:
            foo: fooValue
            bar: barValue

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:acme_hello="http://www.example.com/symfony/schema/"
            xsi:schemaLocation="http://www.example.com/symfony/schema/ http://www.example.com/symfony/schema/hello-1.0.xsd">

            <acme_hello:config foo="fooValue">
                <acme_hello:bar>barValue</acme_hello:bar>
            </acme_hello:config>

        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('acme_hello', array(
            'foo' => 'fooValue',
            'bar' => 'barValue',
        ));

The array passed to your ``load()`` method will look like this::

    array(
        array(
            'foo' => 'fooValue',
            'bar' => 'barValue',
        ),
    )

Notice that this is an *array of arrays*, not just a single flat array of the
configuration values. This is intentional. For example, if ``acme_hello``
appears in another configuration file - say ``config_dev.yml`` - with different
values beneath it, then the incoming array might look like this::

    array(
        array(
            'foo' => 'fooValue',
            'bar' => 'barValue',
        ),
        array(
            'foo' => 'fooDevValue',
            'baz' => 'newConfigEntry',
        ),
    )

The order of the two arrays depends on which one is set first.

It's your job, then, to decide how these configurations should be merged
together. You might, for example, have later values override previous values
or somehow merge them together.

Later, in the :ref:`Configuration Class <cookbook-bundles-extension-config-class>`
section, you'll learn of a truly robust way to handle this. But for now,
you might just merge them manually::

    public function load(array $configs, ContainerBuilder $container)
    {
        $config = array();
        foreach ($configs as $subConfig) {
            $config = array_merge($config, $subConfig);
        }

        // ... now use the flat $config array
    }

.. caution::

    Make sure the above merging technique makes sense for your bundle. This
    is just an example, and you should be careful to not use it blindly.

Using the ``load()`` Method
---------------------------

Within ``load()``, the ``$container`` variable refers to a container that only
knows about this namespace configuration (i.e. it doesn't contain service
information loaded from other bundles). The goal of the ``load()`` method
is to manipulate the container, adding and configuring any methods or services
needed by your bundle.

Loading External Configuration Resources
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

One common thing to do is to load an external configuration file that may
contain the bulk of the services needed by your bundle. For example, suppose
you have a ``services.xml`` file that holds much of your bundle's service
configuration::

    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
    use Symfony\Component\Config\FileLocator;

    public function load(array $configs, ContainerBuilder $container)
    {
        // ... prepare your $config variable

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');
    }

You might even do this conditionally, based on one of the configuration values.
For example, suppose you only want to load a set of services if an ``enabled``
option is passed and set to true::

    public function load(array $configs, ContainerBuilder $container)
    {
        // ... prepare your $config variable

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        if (isset($config['enabled']) && $config['enabled']) {
            $loader->load('services.xml');
        }
    }

Configuring Services and Setting Parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Once you've loaded some service configuration, you may need to modify the
configuration based on some of the input values. For example, suppose you
have a service whose first argument is some string "type" that it will use
internally. You'd like this to be easily configured by the bundle user, so
in your service configuration file (e.g. ``services.xml``), you define this
service and use a blank parameter - ``acme_hello.my_service_type`` - as
its first argument:

.. code-block:: xml

    <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

        <parameters>
            <parameter key="acme_hello.my_service_type" />
        </parameters>

        <services>
            <service id="acme_hello.my_service" class="Acme\HelloBundle\MyService">
                <argument>%acme_hello.my_service_type%</argument>
            </service>
        </services>
    </container>

But why would you define an empty parameter and then pass it to your service?
The answer is that you'll set this parameter in your extension class, based
on the incoming configuration values. Suppose, for example, that you want
to allow the user to define this *type* option under a key called ``my_type``.
Add the following to the ``load()`` method to do this::

    public function load(array $configs, ContainerBuilder $container)
    {
        // ... prepare your $config variable

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');

        if (!isset($config['my_type'])) {
            throw new \InvalidArgumentException(
                'The "my_type" option must be set'
            );
        }

        $container->setParameter(
            'acme_hello.my_service_type',
            $config['my_type']
        );
    }

Now, the user can effectively configure the service by specifying the ``my_type``
configuration value:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        acme_hello:
            my_type: foo
            # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:acme_hello="http://www.example.com/symfony/schema/"
            xsi:schemaLocation="http://www.example.com/symfony/schema/ http://www.example.com/symfony/schema/hello-1.0.xsd">

            <acme_hello:config my_type="foo">
                <!-- ... -->
            </acme_hello:config>

        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('acme_hello', array(
            'my_type' => 'foo',
            ...,
        ));

Global Parameters
~~~~~~~~~~~~~~~~~

When you're configuring the container, be aware that you have the following
global parameters available to use:

* ``kernel.name``
* ``kernel.environment``
* ``kernel.debug``
* ``kernel.root_dir``
* ``kernel.cache_dir``
* ``kernel.logs_dir``
* ``kernel.bundles``
* ``kernel.charset``

.. caution::

    All parameter and service names starting with a ``_`` are reserved for the
    framework, and new ones must not be defined by bundles.

.. _cookbook-bundles-extension-config-class:

Validation and Merging with a Configuration Class
-------------------------------------------------

So far, you've done the merging of your configuration arrays by hand and
are checking for the presence of config values manually using the ``isset()``
PHP function. An optional *Configuration* system is also available which
can help with merging, validation, default values, and format normalization.

.. note::

    Format normalization refers to the fact that certain formats - largely XML -
    result in slightly different configuration arrays and that these arrays
    need to be "normalized" to match everything else.

To take advantage of this system, you'll create a ``Configuration`` class
and build a tree that defines your configuration in that class::

    // src/Acme/HelloBundle/DependencyInjection/Configuration.php
    namespace Acme\HelloBundle\DependencyInjection;

    use Symfony\Component\Config\Definition\Builder\TreeBuilder;
    use Symfony\Component\Config\Definition\ConfigurationInterface;

    class Configuration implements ConfigurationInterface
    {
        public function getConfigTreeBuilder()
        {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('acme_hello');

            $rootNode
                ->children()
                ->scalarNode('my_type')->defaultValue('bar')->end()
                ->end();

            return $treeBuilder;
        }
    }

This is a *very* simple example, but you can now use this class in your ``load()``
method to merge your configuration and force validation. If any options other
than ``my_type`` are passed, the user will be notified with an exception
that an unsupported option was passed::

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        // ...
    }

The ``processConfiguration()`` method uses the configuration tree you've defined
in the ``Configuration`` class to validate, normalize and merge all of the
configuration arrays together.

The ``Configuration`` class can be much more complicated than shown here,
supporting array nodes, "prototype" nodes, advanced validation, XML-specific
normalization and advanced merging. You can read more about this in
:doc:`the Config component documentation </components/config/definition>`.
You can also see it in action by checking out some of the core Configuration classes,
such as the one from the `FrameworkBundle Configuration`_ or the `TwigBundle Configuration`_.

Modifying the configuration of another Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you have multiple bundles that depend on each other, it may be useful
to allow one ``Extension`` class to modify the configuration passed to another
bundle's ``Extension`` class, as if the end-developer has actually placed that
configuration in their ``app/config/config.yml`` file.

For more details, see :doc:`/cookbook/bundles/prepend_extension`.

Default Configuration Dump
~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``config:dump-reference`` command allows a bundle's default configuration to
be output to the console in YAML.

As long as your bundle's configuration is located in the standard location
(``YourBundle\DependencyInjection\Configuration``) and does not have a
``__construct()`` it will work automatically. If you have something
different, your ``Extension`` class must override the
:method:`Extension::getConfiguration() <Symfony\\Component\\HttpKernel\\DependencyInjection\\Extension::getConfiguration>`
method and return an instance of your
``Configuration``.

Comments and examples can be added to your configuration nodes using the
``->info()`` and ``->example()`` methods::

    // src/Acme/HelloBundle/DependencyExtension/Configuration.php
    namespace Acme\HelloBundle\DependencyInjection;

    use Symfony\Component\Config\Definition\Builder\TreeBuilder;
    use Symfony\Component\Config\Definition\ConfigurationInterface;

    class Configuration implements ConfigurationInterface
    {
        public function getConfigTreeBuilder()
        {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('acme_hello');

            $rootNode
                ->children()
                    ->scalarNode('my_type')
                        ->defaultValue('bar')
                        ->info('what my_type configures')
                        ->example('example setting')
                    ->end()
                ->end()
            ;

            return $treeBuilder;
        }
    }

This text appears as YAML comments in the output of the ``config:dump-reference``
command.

.. index::
   pair: Convention; Configuration

.. _`FrameworkBundle Configuration`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/Configuration.php
.. _`TwigBundle Configuration`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/TwigBundle/DependencyInjection/Configuration.php
