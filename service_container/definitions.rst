.. index::
    single: DependencyInjection; Service definitions

How to work with Service Definition Objects
===========================================

Service definitions are the instructions describing how the container should
build a service. They are not the actual services used by your applications.
The container will create the actual class instances based on the configuration
in the definition.

Normally, you would use YAML, XML or PHP to describe the service definitions.
But if you're doing advanced things with the service container, like working
with a :doc:`Compiler Pass </service_container/compiler_passes>` or creating a
:doc:`Dependency Injection Extension </bundles/extension>`, you may need to
work directly with the ``Definition`` objects that define how a service will be
instantiated.

Getting and Setting Service Definitions
---------------------------------------

There are some helpful methods for working with the service definitions::

    // finds out if there is an "app.mailer" definition
    $container->hasDefinition('app.mailer');
    // finds out if there is an "app.mailer" definition or alias
    $container->has('app.mailer');

    // gets the "app.user_config_manager" definition
    $definition = $container->getDefinition('app.user_config_manager');
    // gets the definition with the "app.user_config_manager" ID or alias
    $definition = $container->findDefinition('app.user_config_manager');

    // adds a new "app.number_generator" definition
    $definition = new Definition(\AppBundle\NumberGenerator::class);
    $container->setDefinition('app.number_generator', $definition);

    // shortcut for the previous method
    $container->register('app.number_generator', \AppBundle\NumberGenerator::class);

Working with a Definition
-------------------------

Creating a New Definition
~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to manipulating and retrieving existing definitions, you can also
define new service definitions with the :class:`Symfony\\Component\\DependencyInjection\\Definition`
class.

Class
~~~~~

The first optional argument of the ``Definition`` class is the fully qualified
class name of the object returned when the service is fetched from the container::

    use AppBundle\Config\UserConfigManager;
    use AppBundle\Config\CustomConfigManager;
    use Symfony\Component\DependencyInjection\Definition;

    $definition = new Definition(UserConfigManager::class);

    // override the class
    $definition->setClass(CustomConfigManager::class);

    // get the class configured for this definition
    $class = $definition->getClass();

Constructor Arguments
~~~~~~~~~~~~~~~~~~~~~

The second optional argument of the ``Definition`` class is an array with the
arguments passed to the constructor of the object returned when the service is
fetched from the container::

    use AppBundle\Config\DoctrineConfigManager;
    use Symfony\Component\DependencyInjection\Definition;
    use Symfony\Component\DependencyInjection\Reference;

    $definition = new Definition(DoctrineConfigManager::class, array(
        new Reference('doctrine'), // a reference to another service
        '%app.config_table_name%',  // will be resolved to the value of a container parameter
    ));

    // gets all arguments configured for this definition
    $constructorArguments = $definition->getArguments();

    // gets a specific argument
    $firstArgument = $definition->getArgument(0);

    // adds a new argument
    $definition->addArgument($argument);

    // replaces argument on a specific index (0 = first argument)
    $definition->replaceArgument($index, $argument);

    // replace all previously configured arguments with the passed array
    $definition->setArguments($arguments);

.. caution::

    Don't use ``get()`` to get a service that you want to inject as constructor
    argument, the service is not yet available. Instead, use a
    ``Reference`` instance as shown above.

Method Calls
~~~~~~~~~~~~

If the service you are working with uses setter injection then you can manipulate
any method calls in the definitions as well::

    // gets all configured method calls
    $methodCalls = $definition->getMethodCalls();

    // configures a new method call
    $definition->addMethodCall('setLogger', array(new Reference('logger')));

    // replaces all previously configured method calls with the passed array
    $definition->setMethodCalls($methodCalls);

.. tip::

    There are more examples of specific ways of working with definitions
    in the PHP code blocks of the Service Container articles such as
    :doc:`/service_container/factories` and :doc:`/service_container/parent_services`.

.. note::

    The methods here that change service definitions can only be used before
    the container is compiled. Once the container is compiled you cannot
    manipulate service definitions further. To learn more about compiling
    the container, see :doc:`/components/dependency_injection/compilation`.

Requiring Files
~~~~~~~~~~~~~~~

There might be use cases when you need to include another file just before
the service itself gets loaded. To do so, you can use the
:method:`Symfony\\Component\\DependencyInjection\\Definition::setFile` method::

    $definition->setFile('/src/path/to/file/foo.php');

Notice that Symfony will internally call the PHP statement ``require_once``,
which means that your file will be included only once per request.
