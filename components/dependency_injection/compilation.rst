.. index::
    single: DependencyInjection; Compilation

Compiling the Container
=======================

The service container can be compiled for various reasons. These reasons
include checking for any potential issues such as circular references and
making the container more efficient by resolving parameters and removing
unused services. Also, certain features - like using
:doc:`parent services </service_container/parent_services>`
- require the container to be compiled.

It is compiled by running::

    $container->compile();

The compile method uses *Compiler Passes* for the compilation. The DependencyInjection
component comes with several passes which are automatically registered for
compilation. For example the
:class:`Symfony\\Component\\DependencyInjection\\Compiler\\CheckDefinitionValidityPass`
checks for various potential issues with the definitions that have been
set in the container. After this and several other passes that check the
container's validity, further compiler passes are used to optimize the
configuration before it is cached. For example, private services and abstract
services are removed and aliases are resolved.

.. _components-dependency-injection-extension:

Managing Configuration with Extensions
--------------------------------------

As well as loading configuration directly into the container as shown in
:doc:`/components/dependency_injection`, you can manage it
by registering extensions with the container. The first step in the compilation
process is to load configuration from any extension classes registered with
the container. Unlike the configuration loaded directly, they are only processed
when the container is compiled. If your application is modular then extensions
allow each module to register and manage their own service configuration.

The extensions must implement
:class:`Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface`
and can be registered with the container with::

    $container->registerExtension($extension);

The main work of the extension is done in the ``load()`` method. In the ``load()``
method you can load configuration from one or more configuration files as
well as manipulate the container definitions using the methods shown in
:doc:`/service_container/definitions`.

The ``load()`` method is passed a fresh container to set up, which is then
merged afterwards into the container it is registered with. This allows
you to have several extensions managing container definitions independently.
The extensions do not add to the containers configuration when they are
added but are processed when the container's ``compile()`` method is called.

A very simple extension may just load configuration files into the container::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
    use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
    use Symfony\Component\Config\FileLocator;

    class AcmeDemoExtension implements ExtensionInterface
    {
        public function load(array $configs, ContainerBuilder $container)
        {
            $loader = new XmlFileLoader(
                $container,
                new FileLocator(__DIR__.'/../Resources/config')
            );
            $loader->load('services.xml');
        }

        // ...
    }

This does not gain very much compared to loading the file directly into
the overall container being built. It just allows the files to be split
up amongst the modules/bundles. Being able to affect the configuration
of a module from configuration files outside of the module/bundle is needed
to make a complex application configurable. This can be done by specifying
sections of config files loaded directly into the container as being for
a particular extension. These sections on the config will not be processed
directly by the container but by the relevant Extension.

The Extension must specify a ``getAlias()`` method to implement the interface::

    // ...

    class AcmeDemoExtension implements ExtensionInterface
    {
        // ...

        public function getAlias()
        {
            return 'acme_demo';
        }
    }

For YAML configuration files specifying the alias for the extension as a
key will mean that those values are passed to the Extension's ``load()`` method:

.. code-block:: yaml

    # ...
    acme_demo:
        foo: fooValue
        bar: barValue

If this file is loaded into the configuration then the values in it are
only processed when the container is compiled at which point the Extensions
are loaded::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

    $containerBuilder = new ContainerBuilder();
    $containerBuilder->registerExtension(new AcmeDemoExtension);

    $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
    $loader->load('config.yaml');

    // ...
    $containerBuilder->compile();

.. note::

    When loading a config file that uses an extension alias as a key, the
    extension must already have been registered with the container builder
    or an exception will be thrown.

The values from those sections of the config files are passed into the first
argument of the ``load()`` method of the extension::

    public function load(array $configs, ContainerBuilder $container)
    {
        $foo = $configs[0]['foo']; //fooValue
        $bar = $configs[0]['bar']; //barValue
    }

The ``$configs`` argument is an array containing each different config file
that was loaded into the container. You are only loading a single config
file in the above example but it will still be within an array. The array
will look like this::

    [
        [
            'foo' => 'fooValue',
            'bar' => 'barValue',
        ],
    ]

Whilst you can manually manage merging the different files, it is much better
to use :doc:`the Config component </components/config>` to
merge and validate the config values. Using the configuration processing
you could access the config value this way::

    use Symfony\Component\Config\Definition\Processor;
    // ...

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $foo = $config['foo']; //fooValue
        $bar = $config['bar']; //barValue

        // ...
    }

There are a further two methods you must implement. One to return the XML
namespace so that the relevant parts of an XML config file are passed to
the extension. The other to specify the base path to XSD files to validate
the XML configuration::

    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/';
    }

    public function getNamespace()
    {
        return 'http://www.example.com/symfony/schema/';
    }

.. note::

    XSD validation is optional, returning ``false`` from the ``getXsdValidationBasePath()``
    method will disable it.

The XML version of the config would then look like this:

.. code-block:: xml

    <?xml version="1.0" ?>
    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:acme_demo="http://www.example.com/symfony/schema/"
        xsi:schemaLocation="http://www.example.com/symfony/schema/ https://www.example.com/symfony/schema/hello-1.0.xsd">

        <acme_demo:config>
            <acme_demo:foo>fooValue</acme_demo:foo>
            <acme_demo:bar>barValue</acme_demo:bar>
        </acme_demo:config>
    </container>

.. note::

    In the Symfony full-stack Framework there is a base Extension class
    which implements these methods as well as a shortcut method for processing
    the configuration. See :doc:`/bundles/extension` for more details.

The processed config value can now be added as container parameters as if
it were listed in a ``parameters`` section of the config file but with the
additional benefit of merging multiple files and validation of the configuration::

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('acme_demo.FOO', $config['foo']);

        // ...
    }

More complex configuration requirements can be catered for in the Extension
classes. For example, you may choose to load a main service configuration
file but also load a secondary one only if a certain parameter is set::

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');

        if ($config['advanced']) {
            $loader->load('advanced.xml');
        }
    }

.. note::

    Just registering an extension with the container is not enough to get
    it included in the processed extensions when the container is compiled.
    Loading config which uses the extension's alias as a key as in the above
    examples will ensure it is loaded. The container builder can also be
    told to load it with its
    :method:`Symfony\\Component\\DependencyInjection\\ContainerBuilder::loadFromExtension`
    method::

        use Symfony\Component\DependencyInjection\ContainerBuilder;

        $containerBuilder = new ContainerBuilder();
        $extension = new AcmeDemoExtension();
        $containerBuilder->registerExtension($extension);
        $containerBuilder->loadFromExtension($extension->getAlias());
        $containerBuilder->compile();

.. note::

    If you need to manipulate the configuration loaded by an extension then
    you cannot do it from another extension as it uses a fresh container.
    You should instead use a compiler pass which works with the full container
    after the extensions have been processed.

.. _components-dependency-injection-compiler-passes:

Prepending Configuration Passed to the Extension
------------------------------------------------

An Extension can prepend the configuration of any Bundle before the ``load()``
method is called by implementing
:class:`Symfony\\Component\\DependencyInjection\\Extension\\PrependExtensionInterface`::

    use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
    // ...

    class AcmeDemoExtension implements ExtensionInterface, PrependExtensionInterface
    {
        // ...

        public function prepend(ContainerBuilder $container)
        {
            // ...

            $container->prependExtensionConfig($name, $config);

            // ...
        }
    }

For more details, see :doc:`/bundles/prepend_extension`, which
is specific to the Symfony Framework, but contains more details about this
feature.

.. _creating-a-compiler-pass:
.. _components-di-compiler-pass:

Execute Code During Compilation
-------------------------------

You can also execute custom code during compilation by writing your own
compiler pass. By implementing
:class:`Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface`
in your extension, the added ``process()`` method will be called during
compilation::

    // ...
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

    class AcmeDemoExtension implements ExtensionInterface, CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
           // ... do something during the compilation
        }

        // ...
    }

As ``process()`` is called *after* all extensions are loaded, it allows you to
edit service definitions of other extensions as well as retrieving information
about service definitions.

The container's parameters and definitions can be manipulated using the
methods described in :doc:`/service_container/definitions`.

.. note::

    Please note that the ``process()`` method in the extension class is
    called during the optimization step. You can read
    :ref:`the next section <components-di-separate-compiler-passes>` if you
    need to edit the container during another step.

.. note::

    As a rule, only work with services definition in a compiler pass and do not
    create service instances. In practice, this means using the methods
    ``has()``, ``findDefinition()``, ``getDefinition()``, ``setDefinition()``,
    etc. instead of ``get()``, ``set()``, etc.

.. tip::

    Make sure your compiler pass does not require services to exist. Abort the
    method call if some required service is not available.

A common use-case of compiler passes is to search for all service definitions
that have a certain tag in order to process dynamically plug each into some
other service. See the section on :ref:`service tags <service-container-compiler-pass-tags>`
for an example.

.. _components-di-separate-compiler-passes:

Creating Separate Compiler Passes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes, you need to do more than one thing during compilation, want to use
compiler passes without an extension or you need to execute some code at
another step in the compilation process. In these cases, you can create a new
class implementing the ``CompilerPassInterface``::

    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    class CustomPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
           // ... do something during the compilation
        }
    }

You then need to register your custom pass with the container::

    use Symfony\Component\DependencyInjection\ContainerBuilder;

    $containerBuilder = new ContainerBuilder();
    $containerBuilder->addCompilerPass(new CustomPass());

.. note::

    Compiler passes are registered differently if you are using the full-stack
    framework, see :doc:`/service_container/compiler_passes` for
    more details.

Controlling the Pass Ordering
.............................

The default compiler passes are grouped into optimization passes and removal
passes. The optimization passes run first and include tasks such as resolving
references within the definitions. The removal passes perform tasks such
as removing private aliases and unused services. When registering compiler
passes using ``addCompilerPass()``, you can configure when your compiler pass
is run. By default, they are run before the optimization passes.

You can use the following constants to determine when your pass is executed:

* ``PassConfig::TYPE_BEFORE_OPTIMIZATION``
* ``PassConfig::TYPE_OPTIMIZE``
* ``PassConfig::TYPE_BEFORE_REMOVING``
* ``PassConfig::TYPE_REMOVE``
* ``PassConfig::TYPE_AFTER_REMOVING``

For example, to run your custom pass after the default removal passes have
been run, use::

    // ...
    $containerBuilder->addCompilerPass(
        new CustomPass(),
        PassConfig::TYPE_AFTER_REMOVING
    );

You can also control the order in which compiler passes are run for each
compilation phase. Use the optional third argument of ``addCompilerPass()`` to
set the priority as an integer number. The default priority is ``0`` and the higher
its value, the earlier it's executed::

    // ...
    // FirstPass is executed after SecondPass because its priority is lower
    $container->addCompilerPass(
        new FirstPass(), PassConfig::TYPE_AFTER_REMOVING, 10
    );
    $container->addCompilerPass(
        new SecondPass(), PassConfig::TYPE_AFTER_REMOVING, 30
    );

.. _components-dependency-injection-dumping:

Dumping the Configuration for Performance
-----------------------------------------

Using configuration files to manage the service container can be much easier
to understand than using PHP once there are a lot of services. This ease
comes at a price though when it comes to performance as the config files
need to be parsed and the PHP configuration built from them. The compilation
process makes the container more efficient but it takes time to run. You
can have the best of both worlds though by using configuration files and
then dumping and caching the resulting configuration. The ``PhpDumper``
serves at dumping the compiled container::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

    $file = __DIR__ .'/cache/container.php';

    if (file_exists($file)) {
        require_once $file;
        $container = new ProjectServiceContainer();
    } else {
        $containerBuilder = new ContainerBuilder();
        // ...
        $containerBuilder->compile();

        $dumper = new PhpDumper($containerBuilder);
        file_put_contents($file, $dumper->dump());
    }

``ProjectServiceContainer`` is the default name given to the dumped container
class. However you can change this with the ``class`` option when you
dump it::

    // ...
    $file = __DIR__ .'/cache/container.php';

    if (file_exists($file)) {
        require_once $file;
        $container = new MyCachedContainer();
    } else {
        $containerBuilder = new ContainerBuilder();
        // ...
        $containerBuilder->compile();

        $dumper = new PhpDumper($containerBuilder);
        file_put_contents(
            $file,
            $dumper->dump(['class' => 'MyCachedContainer'])
        );
    }

You will now get the speed of the PHP configured container with the ease
of using configuration files. Additionally dumping the container in this
way further optimizes how the services are created by the container.

In the above example you will need to delete the cached container file whenever
you make any changes. Adding a check for a variable that determines if you
are in debug mode allows you to keep the speed of the cached container in
production but getting an up to date configuration whilst developing your
application::

    // ...

    // based on something in your project
    $isDebug = ...;

    $file = __DIR__ .'/cache/container.php';

    if (!$isDebug && file_exists($file)) {
        require_once $file;
        $container = new MyCachedContainer();
    } else {
        $containerBuilder = new ContainerBuilder();
        // ...
        $containerBuilder->compile();

        if (!$isDebug) {
            $dumper = new PhpDumper($containerBuilder);
            file_put_contents(
                $file,
                $dumper->dump(['class' => 'MyCachedContainer'])
            );
        }
    }

This could be further improved by only recompiling the container in debug
mode when changes have been made to its configuration rather than on every
request. This can be done by caching the resource files used to configure
the container in the way described in ":doc:`/components/config/caching`"
in the config component documentation.

You do not need to work out which files to cache as the container builder
keeps track of all the resources used to configure it, not just the
configuration files but the extension classes and compiler passes as well.
This means that any changes to any of these files will invalidate the cache
and trigger the container being rebuilt. You need to ask the container
for these resources and use them as metadata for the cache::

    // ...

    // based on something in your project
    $isDebug = ...;

    $file = __DIR__ .'/cache/container.php';
    $containerConfigCache = new ConfigCache($file, $isDebug);

    if (!$containerConfigCache->isFresh()) {
        $containerBuilder = new ContainerBuilder();
        // ...
        $containerBuilder->compile();

        $dumper = new PhpDumper($containerBuilder);
        $containerConfigCache->write(
            $dumper->dump(['class' => 'MyCachedContainer']),
            $containerBuilder->getResources()
        );
    }

    require_once $file;
    $container = new MyCachedContainer();

Now the cached dumped container is used regardless of whether debug mode
is on or not. The difference is that the ``ConfigCache`` is set to debug
mode with its second constructor argument. When the cache is not in debug
mode the cached container will always be used if it exists. In debug mode,
an additional metadata file is written with the timestamps of all the resource
files. These are then checked to see if the files have changed, if they
have the cache will be considered stale.

.. note::

    In the full-stack framework the compilation and caching of the container
    is taken care of for you.
