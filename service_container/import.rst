How to Import Configuration Files/Resources
===========================================

.. tip::

    In this section, service configuration files are referred to as *resources*.
    While most configuration resources are files (e.g. YAML, XML, PHP), Symfony is
    able to load configuration from anywhere (e.g. a database or even via an external
    web service).

The service container is built using a single configuration resource
(``config/services.yaml`` by default). This gives you absolute flexibility over
the services in your application.

External service configuration can be imported in two different ways. The first
method, commonly used to import other resources, is via the ``imports``
directive. The second method, using dependency injection extensions, is used by
third-party bundles to load the configuration. Read on to learn more about both
methods.

.. _service-container-imports-directive:

Importing Configuration with ``imports``
----------------------------------------

By default, service configuration lives in ``config/services.yaml``. But if that
file becomes large, you're free to organize into multiple files. Suppose you
decided to move some configuration to a new file:

.. configuration-block::

    .. code-block:: yaml

        # config/services/mailer.yaml
        parameters:
            # ... some parameters

        services:
            # ... some services

    .. code-block:: xml

        <!-- config/services/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <!-- ... some parameters -->
            </parameters>

            <services>
                <!-- ... some services -->
            </services>
        </container>

    .. code-block:: php

        // config/services/mailer.php

        // ... some parameters
        // ... some services

To import this file, use the ``imports`` key from any other file and pass either
a relative or absolute path to the imported file:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        imports:
            - { resource: services/mailer.yaml }
            # If you want to import a whole directory:
            - { resource: services/ }
        services:
            _defaults:
                autowire: true
                autoconfigure: true

            App\:
                resource: '../src/*'
                exclude: '../src/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}'

            # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <imports>
                <import resource="services/mailer.xml"/>
                <!-- If you want to import a whole directory: -->
                <import resource="services/"/>
            </imports>

            <services>
                <defaults autowire="true" autoconfigure="true"/>

                <prototype namespace="App\" resource="../src/*"
                    exclude="../src/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}"/>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $containerConfigurator) {
            $containerConfigurator->import('services/mailer.php');
            // If you want to import a whole directory:
            $containerConfigurator->import('services/');

            $services = $containerConfigurator->services()
                ->defaults()
                    ->autowire()
                    ->autoconfigure()
            ;

            $services->load('App\\', '../src/*')
                ->exclude('../src/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}');
        };

When loading a configuration file, Symfony loads first the imported files and
then it processes the parameters and services defined in the file. If you use the
:ref:`default services.yaml configuration <service-container-services-load-example>`
as in the above example, the ``App\`` definition creates services for classes
found in ``../src/*``. If your imported file defines services for those classes
too, they will be overridden.

A possible solution for this is to add the classes and/or directories of the
imported files in the ``exclude`` option of the ``App\`` definition. Another
solution is to not use imports and add the service definitions in the same file,
but after the ``App\`` definition to override it.

.. include:: /components/dependency_injection/_imports-parameters-note.rst.inc

.. _service-container-extension-configuration:

Importing Configuration via Container Extensions
------------------------------------------------

Third-party bundle container configuration, including Symfony core services,
are usually loaded using another method: a :doc:`container extension </bundles/extension>`.

Internally, each bundle defines its services in files like you've seen so far.
However, these files aren't imported using the ``import`` directive. Instead, bundles
use a *dependency injection extension* to load the files automatically. As soon
as you enable a bundle, its extension is called, which is able to load service
configuration files.

In fact, each configuration file in ``config/packages/`` is passed to the
extension of its related  bundle - e.g. ``FrameworkBundle`` or ``TwigBundle`` -
and used to configure those services further.
