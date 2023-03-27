Container Building Workflow
===========================

The location of the files and classes related to the Dependency Injection
component depends on the application, library or framework in which you want
to use the container. Looking at how the container is configured and built
in the Symfony full-stack Framework will help you see how this all fits together,
whether you are using the full-stack framework or looking to use the service
container in another application.

The full-stack framework uses the HttpKernel component to manage the loading
of the service container configuration from the application and bundles
and also handles the compilation and caching. Even if you are not using
HttpKernel, it should give you an idea of one way of organizing configuration
in a modular application.

Working with a Cached Container
-------------------------------

Before building it, the kernel checks to see if a cached version of the
container exists. The kernel has a debug setting and if this is false,
the cached version is used if it exists. If debug is true then the kernel
:doc:`checks to see if configuration is fresh </components/config/caching>`
and if it is, the cached version of the container is used. If not then the
container is built from the application-level configuration and the bundles'
extension configuration.

Read :ref:`Dumping the Configuration for Performance <components-dependency-injection-dumping>`
for more details.

Application-level Configuration
-------------------------------

Application level config is loaded from the ``config`` directory. Multiple
files are loaded which are then merged when the extensions are processed.
This allows for different configuration for different environments e.g.
dev, prod.

These files contain parameters and services that are loaded directly into
the container as per
:ref:`Setting Up the Container with Configuration Files <components-dependency-injection-loading-config>`.
They also contain configuration that is processed by extensions as per
:ref:`Managing Configuration with Extensions <components-dependency-injection-extension>`.
These are considered to be bundle configuration since each bundle contains
an Extension class.

Bundle-level Configuration with Extensions
------------------------------------------

By convention, each bundle contains an Extension class which is in the bundle's
``DependencyInjection`` directory. These are registered with the ``ContainerBuilder``
when the kernel is booted. When the ``ContainerBuilder`` is
:doc:`compiled </components/dependency_injection/compilation>`, the application-level
configuration relevant to the bundle's extension is passed to the Extension
which also usually loads its own config file(s), typically from the bundle's
``Resources/config`` directory. The application-level config is usually
processed with a :doc:`Configuration object </components/config/definition>`
also stored in the bundle's ``DependencyInjection`` directory.

Compiler Passes to Allow Interaction between Bundles
----------------------------------------------------

:ref:`Compiler passes <components-dependency-injection-compiler-passes>`
are used to allow interaction between different bundles as they cannot affect
each other's configuration in the extension classes. One of the main uses
is to process tagged services, allowing bundles to register services to
be picked up by other bundles, such as Monolog loggers, Twig extensions
and Data Collectors for the Web Profiler. Compiler passes are usually placed
in the bundle's ``DependencyInjection/Compiler`` directory.

Compilation and Caching
-----------------------

After the compilation process has loaded the services from the configuration,
extensions and the compiler passes, it is dumped so that the cache can be
used next time. The dumped version is then used during subsequent requests
as it is more efficient.
