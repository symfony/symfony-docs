.. index::
    single: DependencyInjection; Importing Resources
    single: Service Container; Importing Resources

How to Import Configuration Files/Resources
===========================================

.. tip::

    In this section, service configuration files are referred to as *resources*.
    This is to highlight the fact that, while most configuration resources
    will be files (e.g. YAML, XML, PHP), Symfony is so flexible that configuration
    could be loaded from anywhere (e.g. a database or even via an external
    web service).

The service container is built using a single configuration resource
(``app/config/config.yml`` by default). All other service configuration
(including the core Symfony and third-party bundle configuration) must
be imported from inside this file in one way or another. This gives you absolute
flexibility over the services in your application.

External service configuration can be imported in two different ways. The first
method, commonly used to import other resources, is via the ``imports``
directive. The second method, using dependency injection extensions, is used by
third-party bundles to load the configuration. Read on to learn more about both
methods.

.. index::
    single: Service Container; Imports

.. _service-container-imports-directive:

Importing Configuration with ``imports``
----------------------------------------

So far, you've placed your ``app.mailer`` service container definition directly
in the services configuration file (e.g. ``app/config/services.yml``). If your
application ends up having many services, this file becomes huge and hard to
maintain. To avoid this, you can split your service configuration into multiple
service files:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services/mailer.yml
        parameters:
            app.mailer.transport: sendmail

        services:
            app.mailer:
                class:        AppBundle\Mailer\Mailer
                arguments:    ['%app.mailer.transport%']

    .. code-block:: xml

        <!-- app/config/services/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="app.mailer.transport">sendmail</parameter>
            </parameters>

            <services>
                <service id="app.mailer" class="AppBundle\Mailer\Mailer">
                    <argument>%app.mailer.transport%</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services/mailer.php
        use AppBundle\Mailer\Mailer;
        use Symfony\Component\DependencyInjection\Definition;

        $container->setParameter('app.mailer.transport', 'sendmail');

        $container->setDefinition('app.mailer', new Definition(
            Mailer::class,
            array('%app.mailer.transport%')
        ));

The definition itself hasn't changed, only its location. To make the service
container load the definitions in this resource file, use the ``imports`` key
in any already loaded resource (e.g. ``app/config/services.yml`` or
``app/config/config.yml``):

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        imports:
            - { resource: services/mailer.yml }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <imports>
                <import resource="services/mailer.xml"/>
            </imports>
        </container>

    .. code-block:: php

        // app/config/services.php
        $loader->import('services/mailer.php');

The ``resource`` location, for files, is either a relative path from the
current file or an absolute path.

.. include:: /components/dependency_injection/_imports-parameters-note.rst.inc

.. index::
    single: Service Container; Extension configuration

.. _service-container-extension-configuration:

Importing Configuration via Container Extensions
------------------------------------------------

Third-party bundle container configuration, including Symfony core services,
are usually loaded using another method that's more flexible and easy to
configure in your application.

Internally, each bundle defines its services like you've seen so far. However,
these files aren't imported using the ``import`` directive. These bundles use a
*dependency injection extension* to load the files. The extension also allows
bundles to provide configuration to dynamically load some services.

Take the FrameworkBundle - the core Symfony Framework bundle - as an
example. The presence of the following code in your application configuration
invokes the service container extension inside the FrameworkBundle:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            secret: xxxxxxxxxx
            form:   true
            # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >

            <framework:config secret="xxxxxxxxxx">
                <framework:form />

                <!-- ... -->
            </framework>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'secret' => 'xxxxxxxxxx',
            'form'   => array(),

            // ...
        ));

When the resources are parsed, the container looks for an extension that
can handle the ``framework`` directive. The extension in question, which lives
in the FrameworkBundle, is invoked and the service configuration for the
FrameworkBundle is loaded.

The settings under the ``framework`` directive (e.g. ``form: true``) indicate
that the extension should load all services related to the Form component. If
form was disabled, these services wouldn't be loaded and Form integration would
not be available.

When installing or configuring a bundle, see the bundle's documentation for
how the services for the bundle should be installed and configured. The options
available for the core bundles can be found inside the :doc:`Reference Guide </reference/index>`.

.. seealso::

    If you want to use dependency injection extensions in your own shared
    bundles and provide user friendly configuration, take a look at the
    :doc:`/bundles/extension` article.
