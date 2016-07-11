.. index::
   single: Configuration; Semantic
   single: Bundle; Extension configuration

How to Load Service Configuration inside a Bundle
=================================================

In Symfony, you'll find yourself using many services. These services can be
registered in the ``app/config/`` directory of your application. But when you
want to decouple the bundle for use in other projects, you want to include the
service configuration in the bundle itself. This article will teach you how to
do that.

Creating an Extension Class
---------------------------

In order to load service configuration, you have to create a Dependency
Injection (DI) Extension for your bundle. This class has some conventions in order
to be detected automatically. But you'll later see how you can change it to
your own preferences. By default, the Extension has to comply with the
following conventions:

* It has to live in the ``DependencyInjection`` namespace of the bundle;

* The name is equal to the bundle name with the ``Bundle`` suffix replaced by
  ``Extension`` (e.g. the Extension class of the AppBundle would be called
  ``AppExtension`` and the one for AcmeHelloBundle would be called
  ``AcmeHelloExtension``).

The Extension class should implement the
:class:`Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface`,
but usually you would simply extend the
:class:`Symfony\\Component\\DependencyInjection\\Extension\\Extension` class::

    // src/Acme/HelloBundle/DependencyInjection/AcmeHelloExtension.php
    namespace Acme\HelloBundle\DependencyInjection;

    use Symfony\Component\HttpKernel\DependencyInjection\Extension;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

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

Since the new Extension class name doesn't follow the naming conventions, you
should also override
:method:`Extension::getAlias() <Symfony\\Component\\DependencyInjection\\Extension\\Extension::getAlias>`
to return the correct DI alias. The DI alias is the name used to refer to the
bundle in the container (e.g. in the ``app/config/config.yml`` file). By
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
(using the Yaml, XML or PHP format). Luckily, you can use the file loaders in
the extension!

For instance, assume you have a file called ``services.xml`` in the
``Resources/config`` directory of your bundle, your load method looks like::

    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
    use Symfony\Component\Config\FileLocator;

    // ...
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');
    }

Other available loaders are the ``YamlFileLoader``, ``PhpFileLoader`` and
``IniFileLoader``.

.. note::

    The ``IniFileLoader`` can only be used to load parameters and it can only
    load them as strings.

.. caution::

    If you removed the default file with service definitions (i.e.
    ``app/config/services.yml``), make sure to also remove it from the
    ``imports`` key in ``app/config/config.yml``.

Using Configuration to Change the Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The Extension is also the class that handles the configuration for that
particular bundle (e.g. the configuration in ``app/config/config.yml``). To
read more about it, see the ":doc:`/cookbook/bundles/configuration`" article.

Adding Classes to Compile
-------------------------

Symfony creates a big ``classes.php`` file in the cache directory to aggregate
the contents of the PHP classes that are used in every request. This reduces the
I/O operations and increases the application performance.

Your bundles can also add their own classes into this file thanks to the
``addClassesToCompile()`` method. Define the classes to compile as an array of
their fully qualified class names::

    // ...
    public function load(array $configs, ContainerBuilder $container)
    {
        // ...

         $this->addClassesToCompile(array(
            'AppBundle\\Manager\\UserManager',
            'AppBundle\\Utils\\Slugger',
            // ...
        ));
    }

.. note::

    If some class extends from other classes, all its parents are automatically
    included in the list of classes to compile.

Beware that this technique **can't be used in some cases**:

* When classes contain annotations, such as controllers with ``@Route``
  annotations and entities with ``@ORM`` or ``@Assert`` annotations, because
  the file location retrieved from PHP reflection changes;
* When classes use the ``__DIR__`` and ``__FILE__`` constants, because their
  values will change when loading these classes from the ``classes.php`` file.
