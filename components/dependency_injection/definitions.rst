.. index::
    single: DependencyInjection; Service definitions

Working with Container Service Definitions
==========================================

Service definitions are the instructions describing how the container should
build a service. They are not the actual services used by your applications

Getting and Setting Service Definitions
---------------------------------------

There are some helpful methods for working with the service definitions.

To find out if there is a definition for a service id::

    $container->hasDefinition($serviceId);

This is useful if you only want to do something if a particular definition
exists.

You can retrieve a definition with::

    $container->getDefinition($serviceId);

or::

    $container->findDefinition($serviceId);

which unlike ``getDefinition()`` also resolves aliases so if the ``$serviceId``
argument is an alias you will get the underlying definition.

The service definitions themselves are objects so if you retrieve a definition
with these methods and make changes to it these will be reflected in the
container. If, however, you are creating a new definition then you can add
it to the container using::

    use Symfony\Component\DependencyInjection\Definition;

    $definition = new Definition('Acme\Service\MyService');
    $container->setDefinition('acme.my_service', $definition);

.. tip::

    Registering service definitions is so common that the container provides a
    shortcut method called ``register()``::

        $container->register('acme.my_service', 'Acme\Service\MyService');

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

    $definition = new Definition('Acme\Service\MyService');

If the class is unknown when instantiating the ``Definition`` class, use the
``setClass()`` method to set it later::

    $definition->setClass('Acme\Service\MyService');

To find out what class is set for a definition::

    $class = $definition->getClass();
    // $class = 'Acme\Service\MyService'

Constructor Arguments
~~~~~~~~~~~~~~~~~~~~~

The second optional argument of the ``Definition`` class is an array with the
arguments passed to the constructor of the object returned when the service is
fetched from the container::

    use Symfony\Component\DependencyInjection\Definition;

    $definition = new Definition(
        'Acme\Service\MyService',
        array('argument1' => 'value1', 'argument2' => 'value2')
    );

If the arguments are unknown when instantiating the ``Definition`` class or if
you want to add new arguments, use the ``addArgument()`` method, which adds them
at the end of the arguments array::

    $definition->addArgument($argument);

To get an array of the constructor arguments for a definition you can use::

    $definition->getArguments();

or to get a single argument by its position::

    $definition->getArgument($index);
    // e.g. $definition->getArgument(0) for the first argument

The argument can be a string, an array or a service parameter by using the
``%parameter_name%`` syntax::

    $definition->addArgument('%kernel_debug%');

If the argument is another service, don't use the ``get()`` method to fetch it,
because it won't be available when defining services. Instead, use the
:class:`Symfony\\Component\\DependencyInjection\\Reference` class to get a
reference to the service which will be available once the service container is
fully built::

    use Symfony\Component\DependencyInjection\Reference;

    // ...

    $definition->addArgument(new Reference('service_id'));

In a similar way you can replace an already set argument by index using::

    $definition->replaceArgument($index, $argument);

You can also replace all the arguments (or set some if there are none) with
an array of arguments::

    $definition->setArguments($arguments);

Method Calls
~~~~~~~~~~~~

If the service you are working with uses setter injection then you can manipulate
any method calls in the definitions as well.

You can get an array of all the method calls with::

    $definition->getMethodCalls();

Add a method call with::

   $definition->addMethodCall($method, $arguments);

Where ``$method`` is the method name and ``$arguments`` is an array of the
arguments to call the method with. The arguments can be strings, arrays,
parameters or service ids as with the constructor arguments.

You can also replace any existing method calls with an array of new ones
with::

    $definition->setMethodCalls($methodCalls);

.. tip::

    There are more examples of specific ways of working with definitions
    in the PHP code blocks of the configuration examples on pages such as
    :doc:`/components/dependency_injection/factories` and
    :doc:`/components/dependency_injection/parentservices`.

.. note::

    The methods here that change service definitions can only be used before
    the container is compiled. Once the container is compiled you cannot
    manipulate service definitions further. To learn more about compiling
    the container see :doc:`/components/dependency_injection/compilation`.

Requiring Files
~~~~~~~~~~~~~~~

There might be use cases when you need to include another file just before
the service itself gets loaded. To do so, you can use the
:method:`Symfony\\Component\\DependencyInjection\\Definition::setFile` method::

    $definition->setFile('/src/path/to/file/foo.php');

Notice that Symfony will internally call the PHP statement ``require_once``,
which means that your file will be included only once per request.
