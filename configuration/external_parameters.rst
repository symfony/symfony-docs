.. index::
    single: Environments; External parameters

How to Set external Parameters in the Service Container
=======================================================

In the article :doc:`/configuration`, you learned how to manage your application
configuration. At times, it may benefit your application to store certain
credentials outside of your project code. Database configuration is one such
example. The flexibility of the Symfony service container allows you to easily
do this.

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
                    host="%env(DATABASE_HOST)%"
                />
            </doctrine:config>

        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine', array(
            'dbal' => array(
                'host' => '%env(DATABASE_HOST)%',
            ),
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

        fastcgi_param DATABASE_USER user;
        fastcgi_param DATABASE_PASSWORD secret;

    .. code-block:: terminal

        $ export DATABASE_USER=user
        $ export DATABASE_PASSWORD=secret

.. tip::

    .. versionadded:: 3.3
        The support of the special ``SYMFONY__`` environment variables was
        deprecated in Symfony 3.3 and it will be removed in 4.0. Instead of
        using those variables, define regular environment variables and get
        their values using the ``%env(...)%`` syntax in your config files.

    You can also define the default value of any existing parameters using
    special environment variables named after their corresponding parameter
    prefixed with ``SYMFONY__`` after replacing dots by double underscores
    (e.g. ``SYMFONY__KERNEL__CHARSET`` to set the default value of the
    ``kernel.charset`` parameter). These default values are resolved when
    compiling the service container and won't change at runtime once dumped.

    The values of the env vars are also exposed in the web interface of the
    :doc:`Symfony profiler </profiler>`. In practice this shouldn't be a
    problem because the web profiler must **never** be enabled in production.

Environment Variable Processors
-------------------------------

.. versionadded:: 3.4
    Environment variable processors were introduced in Symfony 3.4.

The values of environment variables are considered strings by default.
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
                <framework:router http-port="%env(int:HTTP_PORT)%" />
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        $container->loadFromExtension('framework', array(
            'router' => array(
                'http_port' => '%env(int:HTTP_PORT)%',
            ),
        ));

Symfony provides the following env var processors:

``env(string:FOO)``
    Casts ``FOO`` to a string:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/framework.yaml
            parameters:
                env(SECRET): 'some_secret'
            framework:
               secret: '%env(string:SECRET)%'

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

                <parameters>
                    <parameter key="env(SECRET)">some_secret</parameter>
                </parameters>

                <framework:config secret="%env(string:SECRET)%" />
            </container>

        .. code-block:: php

            // config/packages/framework.php
            $container->setParameter('env(SECRET)', 'some_secret');
            $container->loadFromExtension('framework', array(
                'secret' => '%env(string:SECRET)%',
            ));

``env(bool:FOO)``
    Casts ``FOO`` to a bool:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/framework.yaml
            parameters:
                env(HTTP_METHOD_OVERRIDE): 'true'
            framework:
               http_method_override: '%env(bool:HTTP_METHOD_OVERRIDE)%'

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

                <parameters>
                    <parameter key="env(HTTP_METHOD_OVERRIDE)">true</parameter>
                </parameters>

                <framework:config http-methode-override="%env(bool:HTTP_METHOD_OVERRIDE)%" />
            </container>

        .. code-block:: php

            // config/packages/framework.php
            $container->setParameter('env(HTTP_METHOD_OVERRIDE)', 'true');
            $container->loadFromExtension('framework', array(
                'http_method_override' => '%env(bool:HTTP_METHOD_OVERRIDE)%',
            ));

``env(int:FOO)``
    Casts ``FOO`` to an int.

``env(float:FOO)``
    Casts ``FOO`` to a float.

``env(const:FOO)``
    Finds the const value named in ``FOO``:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/security.yaml
            parameters:
                env(HEALTH_CHECK_METHOD): 'Symfony\Component\HttpFoundation\Request::METHOD_HEAD'
            security:
               access_control:
                 - { path: '^/health-check$', methods: '%env(const:HEALTH_CHECK_METHOD)%' }

        .. code-block:: xml

            <!-- config/packages/security.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:security="http://symfony.com/schema/dic/security"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd">

                <parameters>
                    <parameter key="env(HEALTH_CHECK_METHOD)">Symfony\Component\HttpFoundation\Request::METHOD_HEAD</parameter>
                </parameters>

                <security:config>
                    <rule path="^/health-check$" methods="%env(const:HEALTH_CHECK_METHOD)%" />
                </security:config>
            </container>

        .. code-block:: php

            // config/packages/security.php
            $container->setParameter('env(HEALTH_CHECK_METHOD)', 'Symfony\Component\HttpFoundation\Request::METHOD_HEAD');
            $container->loadFromExtension('security', array(
                'access_control' => array(
                    array(
                        'path' => '^/health-check$',
                        'methods' => '%env(const:HEALTH_CHECK_METHOD)%',
                    ),
                ),
            ));

``env(base64:FOO)``
    Decodes the content of ``FOO``, which is a base64 encoded string.

``env(json:FOO)``
    Decodes the content of ``FOO``, which is a JSON encoded string. It returns
    either an array or ``null``:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/framework.yaml
            parameters:
                env(TRUSTED_HOSTS): '["10.0.0.1", "10.0.0.2"]'
            framework:
               trusted_hosts: '%env(json:TRUSTED_HOSTS)%'

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

                <parameters>
                    <parameter key="env(TRUSTED_HOSTS)">["10.0.0.1", "10.0.0.2"]</parameter>
                </parameters>

                <framework:config trusted-hosts="%env(json:TRUSTED_HOSTS)%" />
            </container>

        .. code-block:: php

            // config/packages/framework.php
            $container->setParameter('env(TRUSTED_HOSTS)', '["10.0.0.1", "10.0.0.2"]');
            $container->loadFromExtension('framework', array(
                'trusted_hosts' => '%env(json:TRUSTED_HOSTS)%',
            ));

``env(resolve:FOO)``
    Replaces the string ``FOO`` by the value of a config parameter with the
    same name:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/sentry.yaml
            parameters:
                env(HOST): '10.0.0.1'
                env(SENTRY_DSN): 'http://%env(HOST)%/project'
            sentry:
                dsn: '%env(resolve:SENTRY_DSN)%'

        .. code-block:: xml

            <!-- config/packages/sentry.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd">

                <parameters>
                    <parameter key="env(HOST)">10.0.0.1</parameter>
                    <parameter key="env(SENTRY_DSN)">http://%env(HOST)%/project</parameter>
                </parameters>

                <sentry:config dsn="%env(resolve:SENTRY_DSN)%" />
            </container>

        .. code-block:: php

            // config/packages/sentry.php
            $container->setParameter('env(HOST)', '10.0.0.1');
            $container->setParameter('env(SENTRY_DSN)', 'http://%env(HOST)%/project');
            $container->loadFromExtension('sentry', array(
                'dsn' => '%env(resolve:SENTRY_DSN)%',
            ));

``env(file:FOO)``
    Returns the contents of a file whose path is the value of the ``FOO`` env var:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/framework.yaml
            parameters:
                env(AUTH_FILE): '../config/auth.json'
            google:
               auth: '%env(file:AUTH_FILE)%'

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

                <parameters>
                    <parameter key="env(AUTH_FILE)">../config/auth.json</parameter>
                </parameters>

                <google auth="%env(file:AUTH_FILE)%" />
            </container>

        .. code-block:: php

            // config/packages/framework.php
            $container->setParameter('env(AUTH_FILE)', '../config/auth.json');
            $container->loadFromExtension('google', array(
                'auth' => '%env(file:AUTH_FILE)%',
            ));

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
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <imports>
                <import resource="parameters.php" />
            </imports>

        </container>

    .. code-block:: php

        // app/config/config.php
        $loader->import('parameters.php');

.. note::

    A resource file can be one of many types. PHP, XML, YAML, INI, and
    closure resources are all supported by the ``imports`` directive.

In ``parameters.php``, tell the service container the parameters that you wish
to set. This is useful when important configuration is in a non-standard
format. The example below includes a Drupal database configuration in
the Symfony service container::

    // app/config/parameters.php
    include_once('/path/to/drupal/sites/default/settings.php');
    $container->setParameter('drupal.database.url', $db_url);

.. _`SetEnv`: http://httpd.apache.org/docs/current/env.html
.. _`fastcgi_param`: http://nginx.org/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_param
