.. index::
    single: DependencyInjection; Importing Resources
    single: Service Container; Importing Resources

How to Import Configuration Files/Resources
===========================================

.. tip::

    In this section, service configuration files are referred to as *resources*.
    While most configuration resources are files (e.g. YAML, XML, PHP), Symfony is
    able to load configuration from anywhere (e.g. a database or even via an external
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

By default, service configuration lives in ``app/config/services.yml``. But if that
file becomes large, you're free to organize into multiple files. For suppose you
decided to move some configuration to a new file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services/mailer.yml
        parameters:
            # ... some parameters

        services:
            # ... some services

    .. code-block:: xml

        <!-- app/config/services/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <!-- ... some parameters -->
            </parameters>

            <services>
                <!-- ... some services -->
            </services>
        </container>

    .. code-block:: php

        // app/config/services/mailer.php

        // ... some parameters
        // ... some services

To import this file, use the ``imports`` key from a file that *is* loaded:

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
are usually loaded using another method: a container extension.

Internally, each bundle defines its services in files like you've seen so far.
However, these files aren't imported using the ``import`` directive. Instead, bundles
use a *dependency injection extension* to load the files automatically. As soon
as you enable a bundle, its extension is called, which is able to load service
configuration files.

In fact, each configuration block in ``config.yml`` - e.g. ``framework`` or ``twig``-
is passed to the extension for that bundle - e.g. ``FrameworkBundle`` or ``TwigBundle`` -
and used to configure those services further.

If you want to use dependency injection extensions in your own shared
bundles and provide user friendly configuration, take a look at the
:doc:`/bundles/extension` article.
