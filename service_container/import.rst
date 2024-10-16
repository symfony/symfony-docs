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

        return function(ContainerConfigurator $container): void {
            $container->import('services/mailer.php');
            // If you want to import a whole directory:
            $container->import('services/');

            $services = $container->services()
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

There are exactly three possible solutions in order services not to get overriden:
1. Include the file with ``App\`` statement in the ``imports`` as the first element.
In order to the fact that the ``imports`` statement not override existing services, it checks if the services exists,
also take into account that the last element of the ``imports`` has the highest priority and will be executed first,
having included ``App\`` as a first element of ``imports`` (with the lowest priority) it will be imported in the end.
And being the last import element it will only add not existing services in the container.
2. Include the path to the service in the ``exclude`` section.
3. Write service definitions down the ``App\`` statement to override it

It's recommended to use the 1st approach to define services in the container
Using the first approach the whole ``services.yaml`` file will look the foolowing way:

.. configuration-block::
    .. code-block:: yaml
    ###> imports are loaded first (imports not overrides existing services) ###
    imports:
        -   resource: 'services_yaml/resource_services.yaml'    # PRIORITY 1 (last) (contains App\ with resource statement)
        -   resource: 'services_yaml/services/'                 # PRIORITY 2
        -   resource: 'services_yaml/parameters/'               # PRIORITY 3 (first)

    ###> then services.yaml (what below overrides imports) ###
    ###>... it's better to use only imports

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
