.. index::
   single: Dependency Injection; Workflow

Container Building Workflow
===========================

In the preceding pages of this section of the components there has been
little to say where the various files and classes should be located. This
is because this depends on the application, library or framework you want
to use the container in. Looking at how the container is configured and built
in the Symfony2 full stack framework will help you see how this all fits
together whether you are using the full stack framework or looking to use
the service container in another application.

The full stack framework uses the ``HTTPKernel`` component to manage the loading
of service container configuration from the app level and from bundles as well
as with the compilation and caching. Even if you are not using the ``HTTPKernel``
it should give you an idea of a way of organising configuration in a modular
application.

Working with cached controller
------------------------------

Before building the container a cached version is checked for. The ``HTTPKernel``
has a debug setting, if this is false then the cached version is used if it
exists. If debug is true then the cached configuration is checked for freshness
and the cached version of the container used if it is. If not then the container
is built from the app level configuration and the bundle's extension configuration.
Read :ref:`Dumping the Configuration for Performance<components-dependency-injection-dumping>`
for more details.

Application level configuration
-------------------------------

Application level config is loaded from the ``app/config`` directory. Multiple
files are loaded which are then merged when the Extensions are processed. This
allows for different config for different environments e.g. dev, prod.

These files contain parameters and services to be be loaded directly into
the container as per :ref:`Setting Up the Container with Configuration Files<components-dependency-injection-loading-config>`.
They all contain config to be processed by Extensions as per :ref:`Managing Configuration with Extensions<components-dependency-injection-extension>`.
These are considered to be bundle configuration since each bundle contains
an Extension class.

Bundle level config with extensions
-----------------------------------

By convention each bundle contains an Extension class which is in the bundle's
Dependency Injection directory. These are registered with the ``ContainerBuilder``
when the Kernel is booted. When the ContainerBundle is :doc:`compiled<components/dependency-injection/compilation>`
the app level config relevant to the bundle's extension is passed to the Extension
which also usually loads its own config file(s), typically from the bundle's
``Resources/config`` directory. The app level config is usually processed with
a Configuration object also stored in the bundle's ``DependencyInjection``
directory.

Compiler passes to allow interaction between bundles
----------------------------------------------------

:ref:`Compiler passes<components-dependency-injection-compiler-passes>` are
used to allow interaction between different bundles as they cannot affect
each others configuration in the extension classes. One of the main uses is
to process tagged services, allowing bundles to register services to picked
up by other bundle, such as Monolog loggers, Twig extensions and Data Collectors
for the Web Profiler. Compiler passes are usually placed in the bundle's
``DependencyInjection/Compiler`` directory.

Compilation and caching
-----------------------

After the compilation process has loaded the services from the configuration,
extensions and the compiler passes it is dumped so that the cache can be used
next time and the dumped version then used during the request as it is more
efficient.
