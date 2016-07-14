.. index::
    single: DependencyInjection; Service definitions

Working with Container Service Definitions
==========================================

Service definitions are the instructions describing how the container should
build a service. They are not the actual services used by your applications

Getting and Setting Service Definitions
---------------------------------------

There are some helpful methods for working with the service definitions::

    // find out if there is an "app.mailer" definition
    $container->hasDefinition('app.mailer');
    // find out if there is an "app.mailer" definition or alias
    $container->has('app.mailer');

    // get the "app.user_config_manager" definition
    $definition = $container->getDefinition('app.user_config_manager');
    // get the definition with the "app.user_config_manager" ID or alias
    $definition = $container->findDefinition($serviceId);

    // add a new "app.number_generator" definitions
    $definition = new Definition('AppBundle\NumberGenerator');
    $container->setDefinition('app.number_generator', $definition);

    // shortcut for the previous method
    $container->register('app.number_generator', 'AppBundle\NumberGenerator');

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

    use Symfony\Component\DependencyInjection\Definition;

    $definition = new Definition('AppBundle\Config\UserConfigManager');

    // override the class
    $definition->setClass('AppBundle\Config\CustomConfigManager');

    // get the class configured for this definition
    $class = $definition->getClass();

Constructor Arguments
~~~~~~~~~~~~~~~~~~~~~

The second optional argument of the ``Definition`` class is an array with the
arguments passed to the constructor of the object returned when the service is
fetched from the container::

    use Symfony\Component\DependencyInjection\Definition;

    $definition = new Definition('AppBundle\Config\DoctrineConfigManager', array(
        new Reference('doctrine'), // a reference to another service
        '%app.config_table_name%'  // will be resolved to the value of a container parameter
    ));

    // get all arguments configured for this definition
    $constructorArguments = $definition->getArguments();

    // get a specific argument
    $firstArgument = $definition->getArgument(0);

    // add a new argument
    $definition->addArgument($argument);

    // replace argument on a specific index (0 = first argument)
    $definition->replaceArgument($index, $argument);

    // replace all previously configured arguments with the passed array
    $definition->setArguments($arguments);

.. caution::

    Don't use ``get()`` to get a service that you want to inject as constructor
    argument, the service is not yet availabe. Instead, use inject a
    ``Reference`` instance as shown above.

Method Calls
~~~~~~~~~~~~

If the service you are working with uses setter injection then you can manipulate
any method calls in the definitions as well::

    // get all configured method calls
    $methodCalls = $definition->getMethodCalls();

    // configure a new method call
    $definition->addMethodCall('setLogger', array(new Reference('logger')));

    // replace all previously configured method calls with the passed array
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
