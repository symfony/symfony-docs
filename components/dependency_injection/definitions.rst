.. index::
    single: DependencyInjection; Service definitions

Working with Container Service Definitions
==========================================

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

    $container->setDefinition($id, $definition);

Working with a Definition
-------------------------

Creating a New Definition
~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to create a new definition rather than manipulate one retrieved
from the container then the definition class is :class:`Symfony\\Component\\DependencyInjection\\Definition`.

Class
~~~~~

First up is the class of a definition, this is the class of the object returned
when the service is requested from the container.

To find out what class is set for a definition::

    $definition->getClass();

and to set a different class::

    $definition->setClass($class); // Fully qualified class name as string

Constructor Arguments
~~~~~~~~~~~~~~~~~~~~~

To get an array of the constructor arguments for a definition you can use::

    $definition->getArguments();

or to get a single argument by its position::

    $definition->getArgument($index);
    // e.g. $definition->getArgument(0) for the first argument

You can add a new argument to the end of the arguments array using::

    $definition->addArgument($argument);

The argument can be a string, an array, a service parameter by using ``%parameter_name%``
or a service id by using::

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

