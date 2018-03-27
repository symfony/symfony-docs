.. index::
    single: Environments; External parameters

How to Set external Parameters in the Service Container
=======================================================

In :doc:`/configuration`, you learned how to manage your application
configuration. At times, it may benefit your application to store certain
credentials outside of your project code. Database configuration is one such
example. The flexibility of the Symfony service container allows you to easily
do this.

.. _config-env-vars:

Environment Variables
---------------------

You can reference environment variables by using special parameters named after
the variables you want to use enclosed between ``env()``. Their actual values
will be resolved at runtime (once per request), so that dumped containers can be
reconfigured dynamically even after being compiled.

For example, when installing the ``doctrine`` recipe, database configuration is
put in a ``DATABASE_URL`` environment variable:

.. code-block:: bash

    # .env
    DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"

This variable is referenced in the service container configuration using
``%env(DATABASE_URL)%``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            dbal:
                url: '%env(DATABASE_URL)%'
            # ...

    .. code-block:: xml

        <!-- config/packages/doctrine.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal
                    url="%env(DATABASE_URL)%"
                />
            </doctrine:config>

        </container>

    .. code-block:: php

        // config/packages/doctrine.php
        $container->loadFromExtension('doctrine', array(
            'dbal' => array(
                'url' => '%env(DATABASE_URL)%',
            )
        ));

You can also give the ``env()`` parameters a default value: the default value
will be used whenever the corresponding environment variable is *not* found:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            env(DATABASE_HOST): localhost

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="env(DATABASE_HOST)">localhost</parameter>
            </parameters>
         </container>

    .. code-block:: php

        // config/services.php
        $container->setParameter('env(DATABASE_HOST)', 'localhost');

Environment Variable Processors
-------------------------------

When using environment variables they are always strings by default, but sometimes
you will want to have specific types so that they match the types expected by your code.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            router:
                http_port: env(int:HTTP_PORT)

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:router http_port="%env(int:HTTP_PORT)%" />
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/doctrine.php
        $container->loadFromExtension('framework', array(
            'router' => array(
                'http_port' => '%env(int:HTTP_PORT)%',
            )
        ));

A number of different types are supported:

``env(string):FOO)``
    Casts ``FOO`` to a string
    
``env(bool:FOO)``
    Casts ``FOO`` to a bool

``env(int:FOO)``
    Casts ``FOO`` to an int

``env(float:FOO)``
    Casts ``FOO`` to an float

``env(const:FOO)``
    Finds the const value named in ``FOO``

``env(base64:FOO)``
    Decodes ``FOO`` that is a base64 encoded string
    
``env(json:FOO)``
    Decodes ``FOO`` that is a json encoded string into either an array or ``null``
    
``env(resolve:FOO)``
    Resolves references in the string ``FOO`` to other parameters
    
``env(csv:FOO)``
    Decodes ``FOO`` that is a single row of comma seperated values

``env(file:FOO)``
    Reads the contents of a file named in ``FOO``
    
It is also possible to combine the processors:

``env(json:file:FOO)``
    Reads the contents of a file named in ``FOO``, and then decode it from json, resulting in an array or ``null``


.. _configuration-env-var-in-prod:

Configuring Environment Variables in Production
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

During development, you'll use the ``.env`` file to configure your environment
variables. On your production server, it is recommended to configure these at
the web server level. If you're using Apache or Nginx, you can use e.g. one of
the following:

.. configuration-block::

    .. code-block:: apache

        <VirtualHost *:80>
            # ...

            SetEnv DATABASE_URL "mysql://db_user:db_password@127.0.0.1:3306/db_name"
        </VirtualHost>

    .. code-block:: nginx

        fastcgi_param DATABASE_URL "mysql://db_user:db_password@127.0.0.1:3306/db_name";

.. caution::

    Beware that dumping the contents of the ``$_SERVER`` and ``$_ENV`` variables
    or outputting the ``phpinfo()`` contents will display the values of the
    environment variables, exposing sensitive information such as the database
    credentials.

Constants
---------

The container also has support for setting PHP constants as parameters.
See :ref:`component-di-parameters-constants` for more details.

Miscellaneous Configuration
---------------------------

You can mix whatever configuration format you like (YAML, XML and PHP) in
``config/packages/``.  Importing a PHP file gives you the flexibility to add
whatever is needed in the container. For instance, you can create a
``drupal.php`` file in which you set a database URL based on Drupal's database
configuration::

    // config/packages/drupal.php

    // import Drupal's configuration
    include_once('/path/to/drupal/sites/default/settings.php');

    // set a app.database_url parameter
    $container->setParameter('app.database_url', $db_url);

.. _`SetEnv`: http://httpd.apache.org/docs/current/env.html
.. _`fastcgi_param`: http://nginx.org/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_param
