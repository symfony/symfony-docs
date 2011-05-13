.. index::
   single: Environments; External Parameters

How to Set External Parameters in the Dependency Injection Container
====================================================================

In the chapter :doc:`/cookbook/configuration/environments`, you saw how 
to manage your project configuration. At times, it may benefit your application 
to store certain credentials outside of your project code. Database configuration
is one such example. The flexibility of the symfony service container allows
you to easily do this.

Environment Variables
---------------------

Symfony will grab any environment variable set with the ``SYMFONY__`` prefix
and set it as a parameter in the Dependency Injection Container.  Double
underscores are replaced with a period, as a period is not a valid character 
in an environment variable name.

.. code-block:: apache

    <VirtualHost *:80>
        ServerName      Symfony2
        DocumentRoot    "/path/to/symfony_2_app/web"
        DirectoryIndex  index.php index.html
        SetEnv          SYMFONY__DATABASE__USER user
        SetEnv          SYMFONY__DATABASE__PASSWORD secret

        <Directory "/path/to/my_symfony_2_app/web">
            AllowOverride All
            Allow from All
        </Directory>
    </VirtualHost>

.. note::

    The example above is for an Apache configuration, using the SetEnv_ 
    directive.  However, this will work for any web server which supports
    the setting of environment variables.

Now that you have declared an environment variable, it will be present
in the PHP ``$_SERVER`` global.  Symfony then moves all properly prefixed 
variables into the dependency injection container.

You can now reference these parameters wherever you need them.

.. configuration-block::

    .. code-block:: yaml

        doctrine:
            dbal:
                driver                pdo_mysql
                dbname:               Symfony2
                user:                 %database.user%
                password:             %database.password%

    .. code-block:: xml

        <!-- xmlns:doctrine="http://symfony.com/schema/dic/doctrine" -->
        <!-- xsi:schemaLocation="http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd"> -->

        <doctrine:config>
            <doctrine:dbal
                driver="pdo_mysql"
                dbname="database"
                user="%database.user%"
                password="%database.password%"
            />
        </doctrine:config>

    .. code-block:: php

        $container->loadFromExtension('doctrine', array('dbal' => array(
            'driver'   => 'pdo_mysql',
            'dbname'   => 'database',
            'user'     => '%database.user%',
            'password' => '%database.password%',
        ));

Constants
---------

The container has support for setting constants to parameters.
To take advantage of this feature, map the name of your constant 
to a parameter key, and define the type as ``constant``.

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8"?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        >

            <parameters>
                <parameter key="global.constant.value" type="constant">GLOBAL_CONSTANT</parameter>
                <parameter key="my_class.constant.value" type="constant">My_Class::CONSTANT_NAME</parameter>
            </parameters>
        </container>

Miscellaneous Configuration
---------------------------

The ``imports`` directive can be used to pull in parameters stored elsewhere. 
Importing a PHP file gives you the flexibility to add whatever is needed 
to the container. The following imports a file named ``parameters.php``.

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

    A resource file can be one of many types.  PHP, XML, YAML, INI, and
    closure resources are all supported by the ``imports`` directive.

In `parameters.php`, tell the service container the parameters that you wish
to set. This is useful when important configuration is in a nonstandard
format.  The example below includes a Drupal database's configuration in
the symfony dependency injection container.

.. code-block:: php

    // app/config/parameters.php
    include_once('/path/to/drupal/sites/all/default/settings.php');
    $container->setParameter('drupal.database.url', $db_url);

.. _SetEnv: http://httpd.apache.org/docs/current/env.html
