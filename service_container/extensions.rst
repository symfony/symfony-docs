Container Extensions
====================

An **extension** is a class that loads and manages the service configuration
of a group of related services. Extensions are how :doc:`bundles </bundles>`
integrate with the :doc:`service container </service_container>`: when you
configure a key like ``framework`` or ``twig`` in a config file, the extension
registered with that alias processes those values and turns them into service
definitions and parameters.

You only need to create your own extensions in two cases:

* When :doc:`creating a bundle </bundles/extension>`: read that article
  instead, because it explains the utilities that Symfony provides to define
  bundle extensions;
* When using the container in
  :ref:`standalone PHP applications <service-container-standalone>`: extensions
  allow each module of the application to register and manage its own service
  configuration. This is what the rest of this article explains.

Creating an Extension
---------------------

Extensions must implement
:class:`Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface`.
The main work of the extension is done in its ``load()`` method, where you
load configuration files and manipulate the
:ref:`service definitions <service-definitions>`. A simple extension may just
load the service configuration files of its module::

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

    class AcmeDemoExtension implements ExtensionInterface
    {
        public function load(array $configs, ContainerBuilder $container): void
        {
            $loader = new XmlFileLoader(
                $container,
                new FileLocator(__DIR__.'/../Resources/config')
            );
            $loader->load('services.xml');
        }

        // ...
    }

The ``load()`` method receives a fresh container, which is merged into the
main container after the extension has set it up. This allows each extension
to manage its service definitions independently.

The interface defines three other methods. ``getAlias()`` returns the
configuration key associated with the extension::

    // ...

    class AcmeDemoExtension implements ExtensionInterface
    {
        // ...

        public function getAlias(): string
        {
            return 'acme_demo';
        }
    }

``getNamespace()`` returns the XML namespace of the extension configuration
and ``getXsdValidationBasePath()`` returns the base path of the XSD files that
validate the XML configuration (return ``false`` to disable XSD validation)::

    public function getXsdValidationBasePath(): string
    {
        return __DIR__.'/../Resources/config/';
    }

    public function getNamespace(): string
    {
        return 'http://www.example.com/symfony/schema/';
    }

.. note::

    Symfony provides a base
    :class:`Symfony\\Component\\DependencyInjection\\Extension\\Extension`
    class which implements these methods, as well as a shortcut method for
    processing the configuration. See :doc:`/bundles/extension` for more
    details.

Registering and Loading Extensions
----------------------------------

Register the extension in the container with the ``registerExtension()``
method. From then on, any section of the loaded configuration files that uses
the extension alias as its key is passed to the ``load()`` method of the
extension::

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

    $container = new ContainerBuilder();
    $container->registerExtension(new AcmeDemoExtension());

    $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
    $loader->load('config.yaml');

    // ...
    $container->compile();

If the ``config.yaml`` file of the previous example contains an ``acme_demo``
section, its values are passed to the extension:

.. code-block:: yaml

    # config.yaml
    acme_demo:
        foo: fooValue
        bar: barValue

Unlike the configuration loaded directly into the container, the extension
configuration is not processed immediately: extensions are loaded when calling
the ``compile()`` method of the container.

.. note::

    When loading a config file that uses an extension alias as a key, the
    extension must already have been registered with the container builder
    or an exception will be thrown.

.. note::

    Registering an extension is not enough to include it in the compiled
    container: the extension must receive some configuration, like the
    ``acme_demo`` section of the previous example. If there's no configuration
    to pass, tell the container builder to load the extension anyway with the
    :method:`Symfony\\Component\\DependencyInjection\\ContainerBuilder::loadFromExtension`
    method::

        use Symfony\Component\DependencyInjection\ContainerBuilder;

        $container = new ContainerBuilder();
        $extension = new AcmeDemoExtension();
        $container->registerExtension($extension);
        $container->loadFromExtension($extension->getAlias());
        $container->compile();

When using XML configuration files, define the values inside the XML namespace
of the extension:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:acme-demo="http://www.example.com/schema/dic/acme_demo"
        xsi:schemaLocation="http://symfony.com/schema/dic/services
            https://symfony.com/schema/dic/services/services-1.0.xsd
            http://www.example.com/schema/dic/acme_demo
            https://www.example.com/schema/dic/acme_demo/acme_demo-1.0.xsd"
    >
        <acme-demo:config>
            <acme_demo:foo>fooValue</acme_demo:foo>
            <acme_demo:bar>barValue</acme_demo:bar>
        </acme-demo:config>
    </container>

.. note::

    If you need to manipulate the configuration loaded by an extension, you
    cannot do it from another extension, because each extension uses a fresh
    container. Use a :doc:`compiler pass </service_container/compiler_passes>`
    instead, which works with the full container after all the extensions have
    been processed. The extension itself can also
    :ref:`act as a compiler pass <components-di-compiler-pass>`.

Processing the Configuration Values
-----------------------------------

The first argument of the ``load()`` method is an array with the values of
every configuration section that uses the alias of the extension. In the
previous example, it looks like this::

    $configs = [
        [
            'foo' => 'fooValue',
            'bar' => 'barValue',
        ],
    ];

There's one array item per config file that defines values for the extension
(even when a single file was loaded, like in this example). You could merge
the different arrays yourself, but it's much better to use the
:doc:`Config component </components/config>` to merge and validate them::

    use Symfony\Component\Config\Definition\Processor;
    // ...

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $foo = $config['foo']; // fooValue
        $bar = $config['bar']; // barValue

        // ...
    }

Then, use the processed values in your extension; for example, to define
container parameters or to load additional configuration files
conditionally::

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('acme_demo.foo', $config['foo']);

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');

        if ($config['advanced']) {
            $loader->load('advanced.xml');
        }
    }

Deprecating Extension Parameters
--------------------------------

You can deprecate the container parameters defined by your extension to warn
users about not using them anymore. This helps with the migration across major
versions of the extension. Deprecation is only possible when using PHP to
configure the extension, not when using XML or YAML. Use the
``ContainerBuilder::deprecateParameter()`` method to provide the deprecation
details::

    public function load(array $configs, ContainerBuilder $container): void
    {
        // ...

        $container->setParameter('acme_demo.database_user', $configs['db_user']);

        $container->deprecateParameter(
            'acme_demo.database_user',
            'acme/database-package',
            '1.3',
            // optionally you can set a custom deprecation message
            '"acme_demo.database_user" is deprecated, you should configure database credentials with the "acme_demo.database_dsn" parameter instead.'
        );
    }

The parameter being deprecated must be set before being declared as deprecated.
Otherwise a :class:`Symfony\\Component\\DependencyInjection\\Exception\\ParameterNotFoundException`
exception will be thrown.

Prepending Configuration
------------------------

An extension can prepend the configuration of any other extension before the
``load()`` method is called. To do so, implement
:class:`Symfony\\Component\\DependencyInjection\\Extension\\PrependExtensionInterface`::

    use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
    // ...

    class AcmeDemoExtension implements ExtensionInterface, PrependExtensionInterface
    {
        // ...

        public function prepend(ContainerBuilder $container): void
        {
            // ...

            $container->prependExtensionConfig($name, $config);

            // ...
        }
    }

For more details, see :doc:`/bundles/prepend_extension`, which is specific to
the Symfony Framework, but contains more details about this feature.

.. _components-di-compiler-pass:

Using the Extension as a Compiler Pass
--------------------------------------

The ``load()`` method of an extension can't modify the services of other
extensions, because each extension works on its own fresh container. To do
that you need a :doc:`compiler pass </service_container/compiler_passes>`,
which runs after all the extensions are loaded and works with the full
container.

Instead of creating a separate compiler pass class, the extension itself can
act as one: implement
:class:`Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface`
and its ``process()`` method will be called during the compilation, without
needing to register the extension as a compiler pass::

    // ...
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

    class AcmeDemoExtension implements ExtensionInterface, CompilerPassInterface
    {
        public function process(ContainerBuilder $container): void
        {
            // ... do something during the compilation
        }

        // ...
    }

.. note::

    The ``process()`` method of the extension class is called during the
    ``PassConfig::TYPE_BEFORE_OPTIMIZATION`` step. Create a
    :ref:`separate compiler pass <creating-a-compiler-pass>` if you need to
    edit the container during another step.
