Compiling the Container
=======================

The service container can be compiled for various reasons. These reasons
include checking for any potential issues such as circular references and
making the container more efficient by resolving parameters and removing 
unused services.

It is compiled by running::

    $container->compile();

The compile method uses ``Compiler Passes`` for the compilation. The ``Dependency Injection``
component comes with several passes which are automatically registered for
compilation. For example the :class:`Symfony\\Component\\DependencyInjection\\Compiler\\CheckDefinitionValidityPass`
checks for various potential issues with the definitions that have been set
in the container. After this and several other passes are used to check
the container's validity, further compiler passes are used for various tasks to optimize
the configuration before it is cached. For example, private services and 
abstract services are removed, and aliases are resolved.

Creating a Compiler Pass
------------------------

You can also create and register your own compiler passes with the container.
To create a compiler pass it needs to implements the :class:`Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface`
interface. The compiler gives you an opportunity to manipulate the service
definitions that have been compiled. Hence this being not something needed
in everyday use.

The compiler pass must have the ``process`` method which is passed the container being compiled::

    public function process(ContainerBuilder $container)
    {
       //--
    }

The container's parameters and definitions can be manipulated using the
methods described in the :doc:`/components/dependency_injection/definitions`.

Managing Configuration with Extensions
--------------------------------------

As well as loading configuration directly into the container as shown in 
:doc:`/components/dependency_injection/introduction` you can manage it by registering
extensions with the container. The extensions must implement  :class:`Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface`
and can be registered with the container with::

    $container->registerExtension($extension);

The main work of the extension is done in the ``load`` method. In the load method 
you can load configuration from one or more configuration files as well as
manipulating the container definitions using the methods shown in :doc:`/components/dependency_injection/definitions`. 

The ``load`` is passed a fresh container to set up which is then merged into
the container it is registered with. This allows you to have several extension
managing container definitions independently. The extensions do not add
to the containers configuration when they are added but are processed when
the container's ``compile`` method is called.

.. note::
 
    If you need to manipulate the configuration loaded by an extension then
    you cannot do it from another extension as it uses a fresh container.
    You should instead use a compiler pass which works with the full container
    after the extension have been processed. 

