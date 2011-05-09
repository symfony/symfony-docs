.. index::
   single: Environments; Apache SetEnv

How to store parameters in Apache using SetEnv
=========================================

In the chapter :doc:`/cookbook/configuration/environments`, you saw how 
to manage your project configuration. At times, it may benefit your application 
to store certain credentials outside of your project code. Database configuration
is one such example. The flexibility of the symfony service container allows
you to do this with relative ease.

First, using the `SetEnv`__ directive in your apache configuration, declare
your server-specific credentials

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

    Your Apache configuration may differ significantly from the one above. For
    more information on server configuration and the `SetEnv`_ directive, see
    the apache documentation.

Now that you have declared an apache environment variable, it will be available
to your application in the PHP ``$_SERVER`` global. Use the ``imports``
directive in your configuration in order to import a ``parameters.php``
file like so:

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

In `parameters.php`, tell the service container of the parameters set in
apache.

.. code-block:: php

    // app/config/parameters.php
    $container->setParameter('database_user', $_SERVER['SYMFONY__DATABASE__USER']);
    $container->setParameter('database_password', $_SERVER['SYMFONY__DATABASE__PASSWORD']);

Finally, we'll reference these parameters wherever we need them.

.. configuration-block::

    .. code-block:: yaml

        doctrine:
            dbal:
                driver                pdo_mysql
                dbname:               Symfony2
                user:                 %database_user%
                password:             %database_password%

    .. code-block:: xml

        <!-- xmlns:doctrine="http://symfony.com/schema/dic/doctrine" -->
        <!-- xsi:schemaLocation="http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd"> -->

        <doctrine:config>
            <doctrine:dbal
                driver="pdo_mysql"
                dbname="database"
                user="%database_user%"
                password="%database_password%"
            />
        </doctrine:config>

    .. code-block:: php

        $container->loadFromExtension('doctrine', array('dbal' => array(
            'driver'   => 'pdo_mysql',
            'dbname'   => 'database',
            'user'     => '%database_user%',
            'password' => '%database_password%',
        ));


.. _SetEnv: http://httpd.apache.org/docs/current/env.html

__ SetEnv_
