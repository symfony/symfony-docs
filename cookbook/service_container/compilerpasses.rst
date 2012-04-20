How to use Compiler Passes for Container Configuration
======================================================

What are Compiler Passes
------------------------

The lifecycle of Symfony2 service container is along these lines. The configuration
is loaded from the various bundles configurations and the app level config
files are processed by the Extension classes in each bundle. Various compiler
passes are then run against this configuration before it is all cached to file.
This cached configuration is then used on subsequent requests.

The compilation of the configuration is itself done first using a compiler
pass. Further compiler passes are then used for various tasks to optimise
the configuration before it is cached. For example, private services and 
abstract services are removed, and aliases are resolved. 

Many of these compiler passes are part of the :doc:`/components/dependency_injection`
component but individual bundles can register their own compiler passes. A 
common use is to inject tagged services into that bundle's services. This
functionality allows for services to be defined outside of a bundles config
but still be used by that bundle. The bundle is not aware of these services
in its own static config and a fresh container is passed to the Extension class
meaning it does not have access to any other services, so compiler passes which
are executed after all the configuration has been loaded are necessary to inject
tagged services. By using a compiler pass you know that all the other service config
files have been already been processed.

Creating a Compiler Pass
------------------------

To create a compiler pass it needs to implements the ``Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface``
interface.

It is standard practice to put the compiler passes in the ``DependencyInjection/Compiler``
folder of a bundle. They are not automatically registered though, to add the
pass to the container, override the build method of the bundle definition class:

.. code-block:: php

    namespace Acme\DemoBundle;

    use Acme\DemoBundle\DependencyInjection\Compiler\ExamplePass;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Bundle\Bundle;

    class AcmeDemoBundle extends Bundle
    {
        public function build(ContainerBuilder $container)
        {
            $container->addCompilerPass(new ExamplePass());
        }
    }


Uses for Compiler Passes
------------------------

You can implement tags for your own services to allow other people to create
services to be injected into your bundle. This of course only make sense for
shared bundles which are released for other people to use. For bundles only
used in a single application you have full control over its config and can
just inject the services in there. See :doc:`/cookbook/service_container/tags`
for how to create a compiler pass to work with tagged services.

Basically the compiler gives you an opportunity to manipulate the service
definitions that have been compiled. Hence this being not something needed
in everyday use. In most cases the service definitions in the config can be
changed.

There are other uses such as providing more complicated checks on the configuration
than is possible during configuration processing.

Working with the Service Container in Compiler Passes
-----------------------------------------------------

The important method of the compiler pass which gets called when the container
is being built is the ``process`` method which looks like this:

.. code-block:: php

    public function process(ContainerBuilder $container)
    {
       //--
    }


In this method you can work with the passed in container which has been set
up with parameters and service definitions from the various configuration files.

Getting and Setting Container Parameters
----------------------------------------

Working with container parameters is straight forward using the container's
accessor methods for parameters. You can check if a parameter has been defined
in the container with

.. code-block:: php

     $container->hasParameter($name);

You can retrieve parameters set in the container with:

.. code-block:: php
    $container->getParameter($name);

and set a parameter in the container with:

.. code-block:: php
    $container->setParameter($name, $value);

Getting and Setting Service Definitions
---------------------------------------

There are also some helpful methods of the passed in container builder for
working with the service definitions.

To find out if there is a definition for a service id: 

.. code-block:: php
    $container->hasDefinition($serviceId);

This is useful if you only want to do something if a particular definition exists.

You can retrieve a definition with 

.. code-block:: php
    $container->getDefinition($serviceId);

or 

.. code-block:: php
    $container->findDefinition($serviceId);

which unlike ``getDefinition()`` also resolves aliases so if the ``$serviceId``
argument is an alias you will get the underlying definition.

The service definitions themselves are objects so if you retrieve a definition
with these methods and make changes to it these will be reflected in the
container. If, however, you are creating a new definition then you can add
it to the container using:

.. code-block:: php
    $container->setDefinition($id, $definition);

Working with a definition
-------------------------

Creating a new definition
~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to create a new definition rather than manipulate one retrieved
from then container then the definition class is ``Symfony\Component\DependencyInjection\Definition``.

Class
~~~~~

First up is the class of a definition, this is the class of the object returned
when the service is requested from the container.

You may want to change the class used by a definition, if for example there is
functionality which can only be used if a service from another bundle exists
then you may have a class which make use of that other service and one that
does not. The one that does not could be used for the service and then the
one with the extra functionality swapped in using a compiler pass if the other
service is available.

To find out what class is set for a definition:

.. code-block:: php
    $definition->getClass();

and to set a different class:

.. code-block:: php
    $definition->setClass($class); //Fully qualified class name as string

Constructor Arguments
~~~~~~~~~~~~~~~~~~~~~

To get an array of the constructor arguments for a definition you can use 

.. code-block:: php
    $definition->getArguments();

or to get a single argument by its position

.. code-block:: php
    $definition->getArgument($index); 
    //e.g. $definition->getArguments(0) for the first argument

You can add a new argument to the end of the arguments array using

.. code-block:: php
    $definition->addArgument($argument);

The argument can be a string, an array, a service parameter by using ``%paramater_name%``
or a service id by using 

.. code-block:: php
    use Symfony\Component\DependencyInjection\Reference;
  
    //--

    $definition->addArgument(new Reference('service_id'));

In a similar way you can replace an already set argument by index using:


.. code-block:: php
    $definition->replaceArgument($index, $argument);

You can also replace all the arguments (or set some if there are none) with
an array of arguments

.. code-block:: php
    $definition->replaceArguments($arguments);

Method Calls
~~~~~~~~~~~~

If the service you are working with uses setter injection then you can manipulate
any method calls in the definitions as well.

You can get an array of all the method calls with:

.. code-block:: php
    $definition->getMethodCalls();

Add a method call with:

.. code-block:: php
   $definition->addMethodCall($method, $arguments);

Where ``$method`` is the method name and $arguments is an array of the arguments
to call the method with. The arguments can be strings, arrays, parameters or
service ids as with the constructor arguments.

You can also replace any existing method calls with an array of new ones with:

.. code-block:: php
    $definition->setMethodCalls($methodCalls);

