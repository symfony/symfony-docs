.. index::
   single: Configuration; Semantic
   single: Bundle; Extension configuration

How to Load Service Configuration inside a Bundle
=================================================

Services created by bundles are not defined in the main ``config/services.yaml``
file used by the application but in the bundles themselves. This article
explains how to create and load service files using the bundle directory
structure.

Creating an Extension Class
---------------------------

In order to load service configuration, you have to create a Dependency
Injection (DI) Extension for your bundle. By default, the Extension class must
follow these conventions (but later you'll learn how to skip them if needed):

* It has to live in the ``DependencyInjection`` namespace of the bundle;

* It has to implement the :class:`Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface`,
  which is usually achieved by extending the
  :class:`Symfony\\Component\\DependencyInjection\\Extension\\Extension` class;

* The name is equal to the bundle name with the ``Bundle`` suffix replaced by
  ``Extension`` (e.g. the Extension class of the AcmeBundle would be called
  ``AcmeExtension`` and the one for AcmeHelloBundle would be called
  ``AcmeHelloExtension``).

This is how the extension of an AcmeHelloBundle should look like::

    // src/Acme/HelloBundle/DependencyInjection/AcmeHelloExtension.php
    namespace Acme\HelloBundle\DependencyInjection;

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Extension\Extension;

    class AcmeHelloExtension extends Extension
    {
        public function load(array $configs, ContainerBuilder $container)
        {
            // ... you'll load the files here later
        }
    }

Manually Registering an Extension Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When not following the conventions, you will have to manually register your
extension. To do this, you should override the
:method:`Bundle::getContainerExtension() <Symfony\\Component\\HttpKernel\\Bundle\\Bundle::build>`
method to return the instance of the extension::

    // ...
    use Acme\HelloBundle\DependencyInjection\UnconventionalExtensionClass;

    class AcmeHelloBundle extends Bundle
    {
        public function getContainerExtension()
        {
            return new UnconventionalExtensionClass();
        }
    }

In addition, when the new Extension class name doesn't follow the naming
conventions, you must also override the
:method:`Extension::getAlias() <Symfony\\Component\\DependencyInjection\\Extension\\Extension::getAlias>`
method to return the correct DI alias. The DI alias is the name used to refer to
the bundle in the container (e.g. in the ``config/packages/`` files). By
default, this is done by removing the ``Extension`` suffix and converting the
class name to underscores (e.g. ``AcmeHelloExtension``'s DI alias is
``acme_hello``).

Using the ``load()`` Method
---------------------------

In the ``load()`` method, all services and parameters related to this extension
will be loaded. This method doesn't get the actual container instance, but a
copy. This container only has the parameters from the actual container. After
loading the services and parameters, the copy will be merged into the actual
container, to ensure all services and parameters are also added to the actual
container.

In the ``load()`` method, you can use PHP code to register service definitions,
but it is more common if you put these definitions in a configuration file
(using the YAML, XML or PHP format).

For instance, assume you have a file called ``services.xml`` in the
``Resources/config/`` directory of your bundle, your ``load()`` method looks like::

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

    // ...
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');
    }

The other available loaders are ``YamlFileLoader`` and ``PhpFileLoader``.

Using Configuration to Change the Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The Extension is also the class that handles the configuration for that
particular bundle (e.g. the configuration in ``config/packages/<bundle_alias>.yaml``).
To read more about it, see the ":doc:`/bundles/configuration`" article.

Adding Classes to Compile
-------------------------

Bundles can hint Symfony about which of their classes contain annotations so
they are compiled when generating the application cache to improve the overall
performance. Define the list of annotated classes to compile in the
``addAnnotatedClassesToCompile()`` method::

    use App\Manager\UserManager;
    use App\Utils\Slugger;

    // ...
    public function load(array $configs, ContainerBuilder $container)
    {
        // ...

        $this->addAnnotatedClassesToCompile([
            // you can define the fully qualified class names...
            'App\\Controller\\DefaultController',
            // ... but glob patterns are also supported:
            '**Bundle\\Controller\\',

            // ...
        ]);
    }

.. note::

    If some class extends from other classes, all its parents are automatically
    included in the list of classes to compile.

Patterns are transformed into the actual class namespaces using the classmap
generated by Composer. Therefore, before using these patterns, you must generate
the full classmap executing the ``dump-autoload`` command of Composer.

.. caution::

    This technique can't be used when the classes to compile use the ``__DIR__``
    or ``__FILE__`` constants, because their values will change when loading
    these classes from the ``classes.php`` file.
