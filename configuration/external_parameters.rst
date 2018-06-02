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

    The values of the env vars are also exposed in the web interface of the
    :doc:`Symfony profiler </profiler>`. In practice this shouldn't be a
    problem because the web profiler must **never** be enabled in production.

Environment Variable Processors
-------------------------------

.. versionadded:: 3.4
    Environment variable processors were introduced in Symfony 3.4.

The values of the environment variables are considered strings by default.
However, your code may expect other data types, like integers or booleans.
Symfony solves this problem with *processors*, which modify the contents of the
given environment variables. The following example uses the integer processor to
turn the value of the ``HTTP_PORT`` env var into an integer:

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

Symfony provides the following env var processors:

``env(string:FOO)``
    Casts ``FOO`` to a string:

    .. code-block:: yaml

        parameters:
            env(SECRET): "some_secret"
        framework:
           secret: '%env(string:SECRET)%'

``env(bool:FOO)``
    Casts ``FOO`` to a bool:

    .. code-block:: yaml

        parameters:
            env(HTTP_METHOD_OVERRIDE): "true"
        framework:
           http_method_override: '%env(bool:HTTP_METHOD_OVERRIDE)%'

``env(int:FOO)``
    Casts ``FOO`` to an int.

``env(float:FOO)``
    Casts ``FOO`` to an float.

``env(const:FOO)``
    Finds the const value named in ``FOO``:

    .. code-block:: yaml

        parameters:
            env(HEALTH_CHECK_METHOD): "Symfony\Component\HttpFoundation\Request:METHOD_HEAD"
        security:
           access_control:
             - { path: '^/health-check$', methods: '%env(const:HEALTH_CHECK_METHOD)%' }

``env(base64:FOO)``
    Decodes the content of ``FOO``, which is a base64 encoded string.

``env(json:FOO)``
    Decodes the content of ``FOO``, which is a JSON encoded string. It returns
    either an array or ``null``:

    .. code-block:: yaml

        parameters:
            env(TRUSTED_HOSTS): "['10.0.0.1', '10.0.0.2']"
        framework:
           trusted_hosts: '%env(json:TRUSTED_HOSTS)%'

``env(resolve:FOO)``
    Replaces the string ``FOO`` by the value of a config parameter with the
    same name:

    .. code-block:: yaml

        parameters:
            env(HOST): '10.0.0.1'
            env(SENTRY_DSN): "http://%env(HOST)%/project"
        sentry:
            dsn: '%env(resolve:SENTRY_DSN)%'

``env(file:FOO)``
    Returns the contents of a file whose path is the value of the ``FOO`` env var:

    .. code-block:: yaml

        parameters:
            env(AUTH_FILE): "../config/auth.json"
        google:
           auth: '%env(file:AUTH_FILE)%'

It is also possible to combine any number of processors:

.. code-block:: yaml

    parameters:
        env(AUTH_FILE): "%kernel.project_dir%/config/auth.json"
    google:
        # 1. gets the value of the AUTH_FILE env var
        # 2. replaces the values of any config param to get the config path
        # 3. gets the content of the file stored in that path
        # 4. JSON-decodes the content of the file and returns it
        auth: '%env(json:file:resolve:AUTH_FILE)%'

Custom Environment Variable Processors
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It's also possible to add your own processors for environment variables. First,
create a class that implements
:class:`Symfony\\Component\\DependencyInjection\\EnvVarProcessorInterface` and
then, define a service for that class::

    class LowercasingEnvVarProcessor implements EnvVarProcessorInterface
    {
        private $container;

        public function __construct(ContainerInterface $container)
        {
            $this->container = $container;
        }

        public function getEnv($prefix, $name, \Closure $getEnv)
        {
            $env = $getEnv($name);

            return strtolower($env);
        }

        public static function getProvidedTypes()
        {
            return [
                'lowercase' => 'string',
            ];
        }
    }

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
