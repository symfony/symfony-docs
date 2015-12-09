.. index::
    single: Environments; External parameters

How to Set external Parameters in the Service Container
=======================================================

In the chapter :doc:`/cookbook/configuration/environments`, you learned how
to manage your application configuration. At times, it may benefit your application
to store certain credentials outside of your project code. Database configuration
is one such example. The flexibility of the Symfony service container allows
you to easily do this.

Environment Variables
---------------------

Symfony will grab any environment variable prefixed with ``SYMFONY__`` and
set it as a parameter in the service container. Some transformations are
applied to the resulting parameter name:

* ``SYMFONY__`` prefix is removed;
* Parameter name is lowercased;
* Double underscores are replaced with a period, as a period is not
  a valid character in an environment variable name.

For example, if you're using Apache, environment variables can be set using
the following ``VirtualHost`` configuration:

.. code-block:: apache

    <VirtualHost *:80>
        ServerName      Symfony
        DocumentRoot    "/path/to/symfony_2_app/web"
        DirectoryIndex  index.php index.html
        SetEnv          SYMFONY__DATABASE__USER user
        SetEnv          SYMFONY__DATABASE__PASSWORD secret

        <Directory "/path/to/symfony_2_app/web">
            AllowOverride All
            Allow from All
        </Directory>
    </VirtualHost>

.. note::

    The example above is for an Apache configuration, using the `SetEnv`_
    directive. However, this will work for any web server which supports
    the setting of environment variables.

    Also, in order for your console to work (which does not use Apache),
    you must export these as shell variables. On a Unix system, you can run
    the following:

    .. code-block:: bash

        $ export SYMFONY__DATABASE__USER=user
        $ export SYMFONY__DATABASE__PASSWORD=secret

Now that you have declared an environment variable, it will be present
in the PHP ``$_SERVER`` global variable. Symfony then automatically sets all
``$_SERVER`` variables prefixed with ``SYMFONY__`` as parameters in the service
container.

You can now reference these parameters wherever you need them.

.. configuration-block::

    .. code-block:: yaml

        doctrine:
            dbal:
                driver    pdo_mysql
                dbname:   symfony_project
                user:     '%database.user%'
                password: '%database.password%'

    .. code-block:: xml

        <!-- xmlns:doctrine="http://symfony.com/schema/dic/doctrine" -->
        <!-- xsi:schemaLocation="http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd"> -->

        <doctrine:config>
            <doctrine:dbal
                driver="pdo_mysql"
                dbname="symfony_project"
                user="%database.user%"
                password="%database.password%"
            />
        </doctrine:config>

    .. code-block:: php

        $container->loadFromExtension('doctrine', array(
            'dbal' => array(
                'driver'   => 'pdo_mysql',
                'dbname'   => 'symfony_project',
                'user'     => '%database.user%',
                'password' => '%database.password%',
            )
        ));

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
