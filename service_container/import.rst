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
            xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="https://symfony.com/schema/services-1.0.xsd"
        >
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
            xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="https://symfony.com/schema/services-1.0.xsd"
        >
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

When loading a configuration file, Symfony first processes all imported files in
the order they are listed under the ``imports`` key. After all imports are processed,
it then processes the parameters and services defined directly in the current file.
In practice, this means that **later definitions override earlier ones**.

For example, if you use the :ref:`default services.yaml configuration <service-container-services-load-example>`
as in the above example, your main ``config/services.yaml`` file uses the ``App\``
namespace to auto-discover services and loads them after all imported files.
If an imported file (e.g. ``config/services/mailer.yaml``) defines a service that
is also auto-discovered, the definition from ``services.yaml`` will take precedence.

To make sure your specific service definitions are not overridden by auto-discovery,
consider one of the following strategies:

#. :ref:`Exclude services from auto-discovery <import-exclude-services-from-auto-discovery>`
#. :ref:`Override services in the same file <import-override-services-in-the-same-file>`
#. :ref:`Control import order <import-control-import-order>`

.. _import-exclude-services-from-auto-discovery:

**Exclude services from auto-discovery**

Adjust the ``App\`` definition to use the ``exclude`` option. This prevents Symfony
from auto-registering classes that are defined manually elsewhere:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        imports:
            - { resource: services/mailer.yaml }
            # ... other imports

        services:
            _defaults:
                autowire: true
                autoconfigure: true

            App\:
                resource: '../src/*'
                exclude:
                    - '../src/Mailer/'
                    - '../src/SpecificClass.php'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="https://symfony.com/schema/services-1.0.xsd"
        >
            <imports>
                <import resource="services/mailer.xml"/>
                <!-- If you want to import a whole directory: -->
                <import resource="services/"/>
            </imports>

            <services>
                <defaults autowire="true" autoconfigure="true"/>

                <prototype namespace="App\" resource="../src/*">
                    <exclude>../src/Mailer/</exclude>
                    <exclude>../src/SpecificClass.php</exclude>
                </prototype>

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
                ->exclude([
                    '../src/Mailer/',
                    '../src/SpecificClass.php',
                ]);
        };

.. _import-override-services-in-the-same-file:

**Override services in the same file**

You can define specific services after the ``App\`` auto-discovery block in the
same file. These later definitions will override the auto-registered ones:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                autowire: true
                autoconfigure: true

            App\:
                resource: '../src/*'

            App\Mailer\MyMailer:
                arguments: ['%env(MAILER_DSN)%']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="https://symfony.com/schema/services-1.0.xsd"
        >
            <imports>
                <import resource="services/mailer.xml"/>
                <!-- If you want to import a whole directory: -->
                <import resource="services/"/>
            </imports>

            <services>
                <defaults autowire="true" autoconfigure="true"/>

                <prototype namespace="App\" resource="../src/*"/>

                <service id="App\Mailer\MyMailer">
                    <argument>%env(MAILER_DSN)%</argument>
                </service>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $container): void {
            $services = $container->services()
                ->defaults()
                    ->autowire()
                    ->autoconfigure();

            $services->load('App\\', '../src/*');

            $services->set(App\Mailer\MyMailer::class)
                ->arg(0, '%env(MAILER_DSN)%');
        };

.. _import-control-import-order:

**Control import order**

Move the ``App\`` auto-discovery config to a separate file and import it
before more specific service files. This way, specific service definitions
can override the auto-discovered ones.

.. configuration-block::

    .. code-block:: yaml

        # config/services/autodiscovery.yaml
        services:
            _defaults:
                autowire: true
                autoconfigure: true

            App\:
                resource: '../../src/*'
                exclude:
                    - '../../src/Mailer/'

        # config/services/mailer.yaml
        services:
            App\Mailer\SpecificMailer:
                # ... custom configuration

        # config/services.yaml
        imports:
            - { resource: services/autodiscovery.yaml }
            - { resource: services/mailer.yaml }
            - { resource: services/ }

        services:
            # definitions here override anything from the imports above
            # consider keeping most definitions inside imported files

    .. code-block:: xml

        <!-- config/services/autodiscovery.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="https://symfony.com/schema/services-1.0.xsd"
        >
            <services>
                <defaults autowire="true" autoconfigure="true"/>

                <prototype namespace="App\" resource="../../src/*">
                    <exclude>../../src/Mailer/</exclude>
                </prototype>
            </services>
        </container>

        <!-- config/services/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="https://symfony.com/schema/services-1.0.xsd"
        >
            <services>
                <service id="App\Mailer\SpecificMailer">
                    <!-- ... custom configuration -->
                </service>
            </services>
        </container>

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="https://symfony.com/schema/services-1.0.xsd"
        >
            <imports>
                <import resource="services/autodiscovery.xml"/>
                <import resource="services/mailer.xml"/>
                <import resource="services/"/>
            </imports>

            <services>
                <!-- definitions here override anything from the imports above -->
                <!-- consider keeping most definitions inside imported files -->
            </services>
        </container>

    .. code-block:: php

        // config/services/autodiscovery.php
        use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

        return function (ContainerConfigurator $container): void {
            $services = $container->services()
                ->defaults()
                    ->autowire()
                    ->autoconfigure();

            $services->load('App\\', '../../src/*')
                ->exclude([
                    '../../src/Mailer/',
                ]);
        };

        // config/services/mailer.php
        use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

        return function (ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(App\Mailer\SpecificMailer::class);
            // Add any custom configuration here if needed
        };

        // config/services.php
        use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

        return function (ContainerConfigurator $container): void {
            $container->import('services/autodiscovery.php');
            $container->import('services/mailer.php');
            $container->import('services/');

            $services = $container->services();

            // definitions here override anything from the imports above
            // consider keeping most definitions inside imported files
        };

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
