.. index::
    single: Environments; External parameters

How to Set external Parameters in the Service Container
=======================================================

In the chapter :doc:`/configuration`, you learned how to manage your application
configuration. At times, it may benefit your application
to store certain credentials outside of your project code. Database configuration
is one such example. The flexibility of the Symfony service container allows
you to easily do this.

Environment Variables
---------------------

.. versionadded:: 3.2
    ``env()`` parameters were introduced in Symfony 3.2.

You can reference environment variables by using special parameters named after
the variables you want to use enclosed between ``env()``. Their actual values
will be resolved at runtime (once per request), so that dumped containers can be
reconfigured dynamically even after being compiled.

For example, if you want to use the value of the ``DATABASE_HOST`` environment
variable in your service container configuration, you can reference it using
``%env(DATABASE_HOST)%`` in your configuration files:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine:
            dbal:
                host: '%env(DATABASE_HOST)%'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <!-- xmlns:doctrine="http://symfony.com/schema/dic/doctrine" -->
        <!-- xsi:schemaLocation="http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd"> -->

        <doctrine:config>
            <doctrine:dbal
                host="%env(DATABASE_HOST)%"
            />
        </doctrine:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine', array(
            'dbal' => array(
                'host' => '%env(DATABASE_HOST)%',
            )
        ));

You can also give the ``env()`` parameters a default value: the default value
will be used whenever the corresponding environment variable is *not* found:

.. configuration-block::

    .. code-block:: yaml

        # app/config/parameters.yml
        parameters:
            database_host: '%env(DATABASE_HOST)%'
            env(DATABASE_HOST): localhost

    .. code-block:: xml

        <!-- app/config/parameters.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="database_host">%env(DATABASE_HOST)%</parameter>
                <parameter key="env(DATABASE_HOST)">localhost</parameter>
            </parameters>
         </container>

    .. code-block:: php

        // app/config/parameters.php
        $container->setParameter('database_host', '%env(DATABASE_HOST)%');
        $container->setParameter('env(DATABASE_HOST)', 'localhost');

Setting environment variables is generally done at the web server level or in the
terminal. If you're using Apache, Nginx or just the console, you can use e.g. one
of the following:

.. configuration-block::

    .. code-block:: apache

        <VirtualHost *:80>
            # ...

            SetEnv DATABASE_USER user
            SetEnv DATABASE_PASSWORD secret
        </VirtualHost>

    .. code-block:: nginx

        fastcgi_param DATABASE_USER user
        fastcgi_param DATABASE_PASSWORD secret

    .. code-block:: terminal

        $ export DATABASE_USER=user
        $ export DATABASE_PASSWORD=secret

.. tip::

    You can also define the default value of any existing parameters using
    special environment variables named after their corresponding parameter
    prefixed with ``SYMFONY__`` after replacing dots by double underscores
    (e.g. ``SYMFONY__KERNEL__CHARSET`` to set the default value of the
    ``kernel.charset`` parameter). These default values are resolved when
    compiling the service container and won't change at runtime once dumped.

Constants
---------

The container also has support for setting PHP constants as parameters.
See :ref:`component-di-parameters-constants` for more details.

Miscellaneous Configuration
---------------------------

The ``imports`` directive can be used to pull in parameters stored elsewhere.
Importing a PHP file gives you the flexibility to add whatever is needed
in the container. The following imports a file named ``parameters.php``.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        imports:
            - { resource: parameters.php }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <imports>
            <import resource="parameters.php" />
        </imports>

    .. code-block:: php

        // app/config/config.php
        $loader->import('parameters.php');

.. note::

    A resource file can be one of many types. PHP, XML, YAML, INI, and
    closure resources are all supported by the ``imports`` directive.

In ``parameters.php``, tell the service container the parameters that you wish
to set. This is useful when important configuration is in a non-standard
format. The example below includes a Drupal database configuration in
the Symfony service container.

.. code-block:: php

    // app/config/parameters.php
    include_once('/path/to/drupal/sites/default/settings.php');
    $container->setParameter('drupal.database.url', $db_url);

.. _`SetEnv`: http://httpd.apache.org/docs/current/env.html
.. _`fastcgi_param`: http://nginx.org/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_param
