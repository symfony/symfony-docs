.. index::
   single: Dependency Injection; Compilation

Compiling the Container
=======================

The service container can be compiled for various reasons. These reasons
include checking for any potential issues such as circular references and
making the container more efficient by resolving parameters and removing 
unused services.

It is compiled by running::

    $container->compile();

The compile method uses *Compiler Passes* for the compilation. The *Dependency Injection*
component comes with several passes which are automatically registered for
compilation. For example the :class:`Symfony\\Component\\DependencyInjection\\Compiler\\CheckDefinitionValidityPass`
checks for various potential issues with the definitions that have been set
in the container. After this and several other passes that check the container's
validity, further compiler passes are used to optimize the configuration
before it is cached. For example, private services and abstract services
are removed, and aliases are resolved.

Creating a Compiler Pass
------------------------

You can also create and register your own compiler passes with the container.
To create a compiler pass it needs to implements the :class:`Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface`
interface. The compiler pass gives you an opportunity to manipulate the service
definitions that have been compiled. This can be very powerful, but is not
something needed in everyday use.

The compiler pass must have the ``process`` method which is passed the container
being compiled::

    class CustomCompilerPass
    {
        public function process(ContainerBuilder $container)
        {
           // ...
        }
    }

The container's parameters and definitions can be manipulated using the
methods described in the :doc:`/components/dependency_injection/definitions`.
One common thing to do in a compiler pass is to search for all services that
have a certain tag in order to process them in some way or dynamically plug
each into some other service.

Registering a Compiler Pass
---------------------------

You need to register your custom pass with the container. Its process method
will then be called when the container is compiled::

    use Symfony\Component\DependencyInjection\ContainerBuilder;

    $container = new ContainerBuilder();
    $container->addCompilerPass(new CustomCompilerPass);

Controlling the Pass Ordering
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The default compiler passes are grouped into optimization passes and removal
passes. The optimization passes run first and include tasks such as resolving
references within the definitions. The removal passes perform tasks such as removing
private aliases and unused services. You can choose where in the order any custom
passes you add are run. By default they will be run before the optimization passes.

You can use the following constants as the second argument when registering
a pass with the container to control where it goes in the order:

* ``PassConfig::TYPE_BEFORE_OPTIMIZATION``
* ``PassConfig::TYPE_OPTIMIZE``
* ``PassConfig::TYPE_BEFORE_REMOVING``
* ``PassConfig::TYPE_REMOVE``
* ``PassConfig::TYPE_AFTER_REMOVING``

For example, to run your custom pass after the default removal passes have been run::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Compiler\PassConfig;

    $container = new ContainerBuilder();
    $container->addCompilerPass(new CustomCompilerPass, PassConfig::TYPE_AFTER_REMOVING);


Managing Configuration with Extensions
--------------------------------------

As well as loading configuration directly into the container as shown in 
:doc:`/components/dependency_injection/introduction`, you can manage it by registering
extensions with the container. The extensions must implement  :class:`Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface`
and can be registered with the container with::

    $container->registerExtension($extension);

The main work of the extension is done in the ``load`` method. In the load method 
you can load configuration from one or more configuration files as well as
manipulate the container definitions using the methods shown in :doc:`/components/dependency_injection/definitions`. 

The ``load`` method is passed a fresh container to set up, which is then
merged afterwards into the container it is registered with. This allows you
to have several extensions managing container definitions independently.
The extensions do not add to the containers configuration when they are added
but are processed when the container's ``compile`` method is called.

.. note::
 
    If you need to manipulate the configuration loaded by an extension then
    you cannot do it from another extension as it uses a fresh container.
    You should instead use a compiler pass which works with the full container
    after the extensions have been processed. 

Dumping the Configuration for Performance
-----------------------------------------

Using configuration files to manage the service container can be much easier
to understand than using PHP once there are a lot of services. This ease comes
at a price though when it comes to performance as the config files need to be
parsed and the PHP configuration built from them. The compilation process makes
the container more efficient but it takes time to run. You can have the best of both
worlds though by using configuration files and then dumping and caching the resulting
configuration. The ``PhpDumper`` makes dumping the compiled container easy::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Dumper\PhpDumper

    $file = __DIR__ .'/cache/container.php';

    if (file_exists($file)) {
        require_once $file;
        $container = new ProjectServiceContiner();
    } else {
        $container = new ContainerBuilder();
        //--
        $container->compile();

        $dumper = new PhpDumper($container);
        file_put_contents($file, $dumper->dump());
    }

``ProjectServiceContiner`` is the default name given to the dumped container
class, you can change this though this with the ``class`` option when you dump
it::

    // ...
    $file = __DIR__ .'/cache/container.php';

    if (file_exists($file)) {
        require_once $file;
        $container = new MyCachedContainer();
    } else {
        $container = new ContainerBuilder();
        // ...
        $container->compile();

        $dumper = new PhpDumper($container);
        file_put_contents($file, $dumper->dump(array('class' => 'MyCachedContainer')));
    }

You will now get the speed of the PHP configured container with the ease of using
configuration files. Additionally dumping the container in this way further optimizes
how the services are created by the container.

In the above example you will need to delete the cached container file whenever
you make any changes. Adding a check for a variable that determines if you are
in debug mode allows you to keep the speed of the cached container in production
but getting an up to date configuration whilst developing your application::

    // ...

    // set $isDebug based on something in your project

    $file = __DIR__ .'/cache/container.php';

    if (!$isDebug && file_exists($file)) {
        require_once $file;
        $container = new MyCachedContainer();
    } else {
        $container = new ContainerBuilder();
        //--
        $container->compile();

        if(!$isDebug) 
            $dumper = new PhpDumper($container);
            file_put_contents($file, $dumper->dump(array('class' => 'MyCachedContainer')));
        }
    }

This could be further improved by only recompiling the container in debug
mode when changes have been made to its configuration rather than on every
request. This can be done by caching the resource files used to configure
the container in the way describe in ":doc:`/components/config/caching`"
in the config component documentation.

You do not need to work out which files to cache as the container builder
keeps track of all the resources used to configure it, not just the configuration
files but the extension classes and compiler passes as well. This means that
any changes to any of these files will invalidate the cache and trigger the
container being rebuilt. You just need to ask the container for these resources
and use them as metadata for the cache::

    // ...

    // set $isDebug based on something in your project

    $file = __DIR__ .'/cache/container.php';
    $containerConfigCache = new ConfigCache($file, $isDebug);

    if (!$containerConfigCache->isFresh()) {
        $containerBuilder = new ContainerBuilder();
        //--
        $containerBuilder->compile();

        $dumper = new PhpDumper($containerBuilder);
        $containerConfigCache->write(
            $dumper->dump(array('class' => 'MyCachedContainer')),
            $containerBuilder->getResources()
        );
    }

    require_once $file;
    $container = new MyCachedContainer();

Now the cached dumped container is used regardless of whether debug mode is on or not.
The difference is that the ``ConfigCache`` is set to debug mode with its second
constructor argument. When the cache is not in debug mode the cached container
will always be used if it exists. In debug mode, an additional metadata file
is written with the timestamps of all the resource files. These are then checked
to see if the files have changed, if they have the cache will be considered stale.
