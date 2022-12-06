.. index::
    single: Environment Variable Processors; env vars

.. _env-var-processors:

Environment Variable Processors
===============================

:ref:`Using env vars to configure Symfony applications <config-env-vars>` is a
common practice to make your applications truly dynamic.

The main issue of env vars is that their values can only be strings and your
application may need other data types (integer, boolean, etc.). Symfony solves
this problem with "env var processors", which transform the original contents of
the given environment variables. The following example uses the integer
processor to turn the value of the ``HTTP_PORT`` env var into an integer:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            router:
                http_port: '%env(int:HTTP_PORT)%'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:router http-port="%env(int:HTTP_PORT)%"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->router()
                ->httpPort('%env(int:HTTP_PORT)%')
                // or
                ->httpPort(env('HTTP_PORT')->int())
            ;
        };

Built-In Environment Variable Processors
----------------------------------------

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
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <parameters>
                    <parameter key="env(SECRET)">some_secret</parameter>
                </parameters>

                <framework:config secret="%env(string:SECRET)%"/>
            </container>

        .. code-block:: php

            // config/packages/framework.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use Symfony\Component\DependencyInjection\ContainerBuilder;
            use Symfony\Config\FrameworkConfig;

            return static function (ContainerBuilder $container, FrameworkConfig $framework) {
                $container->setParameter('env(SECRET)', 'some_secret');
                $framework->secret(env('SECRET')->string());
            };

``env(bool:FOO)``
    Casts ``FOO`` to a bool (``true`` values are ``'true'``, ``'on'``, ``'yes'``
    and all numbers except ``0`` and ``0.0``; everything else is ``false``):

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
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <parameters>
                    <parameter key="env(HTTP_METHOD_OVERRIDE)">true</parameter>
                </parameters>

                <framework:config http-method-override="%env(bool:HTTP_METHOD_OVERRIDE)%"/>
            </container>

        .. code-block:: php

            // config/packages/framework.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use Symfony\Component\DependencyInjection\ContainerBuilder;
            use Symfony\Config\FrameworkConfig;

            return static function (ContainerBuilder $container, FrameworkConfig $framework) {
                $container->setParameter('env(HTTP_METHOD_OVERRIDE)', 'true');
                $framework->httpMethodOverride(env('HTTP_METHOD_OVERRIDE')->bool());
            };

``env(not:FOO)``
    Casts ``FOO`` to a bool (just as ``env(bool:...)`` does) except it returns the inverted value
    (falsy values are returned as ``true``, truthy values are returned as ``false``):

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                safe_for_production: '%env(not:APP_DEBUG)%'

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <parameters>
                    <parameter key="safe_for_production">%env(not:APP_DEBUG)%</parameter>
                </parameters>

            </container>

        .. code-block:: php

            // config/services.php
            $container->setParameter('safe_for_production', '%env(not:APP_DEBUG)%');

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
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/security
                    https://symfony.com/schema/dic/security/security-1.0.xsd">

                <parameters>
                    <parameter key="env(HEALTH_CHECK_METHOD)">Symfony\Component\HttpFoundation\Request::METHOD_HEAD</parameter>
                </parameters>

                <security:config>
                    <rule path="^/health-check$" methods="%env(const:HEALTH_CHECK_METHOD)%"/>
                </security:config>
            </container>

        .. code-block:: php

            // config/packages/security.php
            use Symfony\Component\DependencyInjection\ContainerBuilder;
            use Symfony\Config\SecurityConfig;

            return static function (ContainerBuilder $container, SecurityConfig $security) {
                $container->setParameter('env(HEALTH_CHECK_METHOD)', 'Symfony\Component\HttpFoundation\Request::METHOD_HEAD');
                $security->accessControl()
                    ->path('^/health-check$')
                    ->methods([env('HEALTH_CHECK_METHOD')->const()]);
            };

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
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <parameters>
                    <parameter key="env(TRUSTED_HOSTS)">["10.0.0.1", "10.0.0.2"]</parameter>
                </parameters>

                <framework:config trusted-hosts="%env(json:TRUSTED_HOSTS)%"/>
            </container>

        .. code-block:: php

            // config/packages/framework.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use Symfony\Component\DependencyInjection\ContainerBuilder;
            use Symfony\Config\FrameworkConfig;

            return static function (ContainerBuilder $container, FrameworkConfig $framework) {
                $container->setParameter('env(TRUSTED_HOSTS)', '["10.0.0.1", "10.0.0.2"]');
                $framework->trustedHosts(env('TRUSTED_HOSTS')->json());
            };

``env(resolve:FOO)``
    If the content of ``FOO`` includes container parameters (with the syntax
    ``%parameter_name%``), it replaces the parameters by their values:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/sentry.yaml
            parameters:
                env(HOST): '10.0.0.1'
                sentry_host: '%env(HOST)%'
                env(SENTRY_DSN): 'http://%sentry_host%/project'
            sentry:
                dsn: '%env(resolve:SENTRY_DSN)%'

        .. code-block:: xml

            <!-- config/packages/sentry.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd">

                <parameters>
                    <parameter key="env(HOST)">10.0.0.1</parameter>
                    <parameter key="sentry_host">%env(HOST)%</parameter>
                    <parameter key="env(SENTRY_DSN)">http://%sentry_host%/project</parameter>
                </parameters>

                <sentry:config dsn="%env(resolve:SENTRY_DSN)%"/>
            </container>

        .. code-block:: php

            // config/packages/sentry.php
            $container->setParameter('env(HOST)', '10.0.0.1');
            $container->setParameter('sentry_host', '%env(HOST)%');
            $container->setParameter('env(SENTRY_DSN)', 'http://%sentry_host%/project');
            $container->loadFromExtension('sentry', [
                'dsn' => '%env(resolve:SENTRY_DSN)%',
            ]);

``env(csv:FOO)``
    Decodes the content of ``FOO``, which is a CSV-encoded string:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/framework.yaml
            parameters:
                env(TRUSTED_HOSTS): "10.0.0.1,10.0.0.2"
            framework:
               trusted_hosts: '%env(csv:TRUSTED_HOSTS)%'

        .. code-block:: xml

            <!-- config/packages/framework.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <parameters>
                    <parameter key="env(TRUSTED_HOSTS)">10.0.0.1,10.0.0.2</parameter>
                </parameters>

                <framework:config trusted-hosts="%env(csv:TRUSTED_HOSTS)%"/>
            </container>

        .. code-block:: php

            // config/packages/framework.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use Symfony\Component\DependencyInjection\ContainerBuilder;
            use Symfony\Config\FrameworkConfig;

            return static function (ContainerBuilder $container, FrameworkConfig $framework) {
                $container->setParameter('env(TRUSTED_HOSTS)', '10.0.0.1,10.0.0.2');
                $framework->trustedHosts(env('TRUSTED_HOSTS')->csv());
            };

``env(shuffle:FOO)``
    Randomly shuffles values of the ``FOO`` env var, which must be an array.

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/framework.yaml
            parameters:
                env(REDIS_NODES): "127.0.0.1:6380,127.0.0.1:6381"
            services:
                RedisCluster:
                    class: RedisCluster
                    arguments: [null, "%env(shuffle:csv:REDIS_NODES)%"]

        .. code-block:: xml

            <!-- config/packages/framework.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <parameters>
                    <parameter key="env(REDIS_NODES)">redis://127.0.0.1:6380,redis://127.0.0.1:6381</parameter>
                </parameters>

                <services>
                    <service id="RedisCluster" class="RedisCluster">
                        <argument>null</argument>
                        <argument>%env(shuffle:csv:REDIS_NODES)%</argument>
                    </service>
                </services>
            </container>

        .. code-block:: php

            // config/services.php
            use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

            return static function (ContainerConfigurator $configurator): void {
                $container = $configurator->services()
                    ->set(\RedisCluster::class, \RedisCluster::class)->args([null, '%env(shuffle:csv:REDIS_NODES)%']);
            };

    .. versionadded:: 6.2

        The ``env(shuffle:...)`` env var processor was introduced in Symfony 6.2.

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
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <parameters>
                    <parameter key="env(AUTH_FILE)">../config/auth.json</parameter>
                </parameters>

                <google auth="%env(file:AUTH_FILE)%"/>
            </container>

        .. code-block:: php

            // config/packages/framework.php
            $container->setParameter('env(AUTH_FILE)', '../config/auth.json');
            $container->loadFromExtension('google', [
                'auth' => '%env(file:AUTH_FILE)%',
            ]);

``env(require:FOO)``
    ``require()`` the PHP file whose path is the value of the ``FOO``
    env var and return the value returned from it.

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/framework.yaml
            parameters:
                env(PHP_FILE): '../config/.runtime-evaluated.php'
            app:
                auth: '%env(require:PHP_FILE)%'

        .. code-block:: xml

            <!-- config/packages/framework.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <parameters>
                    <parameter key="env(PHP_FILE)">../config/.runtime-evaluated.php</parameter>
                </parameters>

                <app auth="%env(require:PHP_FILE)%"/>
            </container>

        .. code-block:: php

            // config/packages/framework.php
            $container->setParameter('env(PHP_FILE)', '../config/.runtime-evaluated.php');
            $container->loadFromExtension('app', [
                'auth' => '%env(require:PHP_FILE)%',
            ]);

``env(trim:FOO)``
    Trims the content of ``FOO`` env var, removing whitespaces from the beginning
    and end of the string. This is especially useful in combination with the
    ``file`` processor, as it'll remove newlines at the end of a file.

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/framework.yaml
            parameters:
                env(AUTH_FILE): '../config/auth.json'
            google:
                auth: '%env(trim:file:AUTH_FILE)%'

        .. code-block:: xml

            <!-- config/packages/framework.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <parameters>
                    <parameter key="env(AUTH_FILE)">../config/auth.json</parameter>
                </parameters>

                <google auth="%env(trim:file:AUTH_FILE)%"/>
            </container>

        .. code-block:: php

            // config/packages/framework.php
            $container->setParameter('env(AUTH_FILE)', '../config/auth.json');
            $container->loadFromExtension('google', [
                'auth' => '%env(trim:file:AUTH_FILE)%',
            ]);

``env(key:FOO:BAR)``
    Retrieves the value associated with the key ``FOO`` from the array whose
    contents are stored in the ``BAR`` env var:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                env(SECRETS_FILE): '/opt/application/.secrets.json'
                database_password: '%env(key:database_password:json:file:SECRETS_FILE)%'
                # if SECRETS_FILE contents are: {"database_password": "secret"} it returns "secret"

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <parameters>
                    <parameter key="env(SECRETS_FILE)">/opt/application/.secrets.json</parameter>
                    <parameter key="database_password">%env(key:database_password:json:file:SECRETS_FILE)%</parameter>
                </parameters>
            </container>

        .. code-block:: php

            // config/services.php
            $container->setParameter('env(SECRETS_FILE)', '/opt/application/.secrets.json');
            $container->setParameter('database_password', '%env(key:database_password:json:file:SECRETS_FILE)%');

``env(default:fallback_param:BAR)``
    Retrieves the value of the parameter ``fallback_param`` when the ``BAR`` env
    var is not available:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                # if PRIVATE_KEY is not a valid file path, the content of raw_key is returned
                private_key: '%env(default:raw_key:file:PRIVATE_KEY)%'
                raw_key: '%env(PRIVATE_KEY)%'

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">
                <parameters>
                    <!-- if PRIVATE_KEY is not a valid file path, the content of raw_key is returned -->
                    <parameter key="private_key">%env(default:raw_key:file:PRIVATE_KEY)%</parameter>
                    <parameter key="raw_key">%env(PRIVATE_KEY)%</parameter>
                </parameters>
            </container>

        .. code-block:: php

            // config/services.php

            // if PRIVATE_KEY is not a valid file path, the content of raw_key is returned
            $container->setParameter('private_key', '%env(default:raw_key:file:PRIVATE_KEY)%');
            $container->setParameter('raw_key', '%env(PRIVATE_KEY)%');

    When the fallback parameter is omitted (e.g. ``env(default::API_KEY)``), then the
    returned value is ``null``.

``env(url:FOO)``
    Parses an absolute URL and returns its components as an associative array.

    .. code-block:: bash

        # .env
        MONGODB_URL="mongodb://db_user:db_password@127.0.0.1:27017/db_name"

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/mongodb.yaml
            mongo_db_bundle:
                clients:
                    default:
                        hosts:
                            - { host: '%env(string:key:host:url:MONGODB_URL)%', port: '%env(int:key:port:url:MONGODB_URL)%' }
                        username: '%env(string:key:user:url:MONGODB_URL)%'
                        password: '%env(string:key:pass:url:MONGODB_URL)%'
                connections:
                    default:
                        database_name: '%env(key:path:url:MONGODB_URL)%'

        .. code-block:: xml

            <!-- config/packages/mongodb.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd">

                <mongodb:config>
                    <mongodb:client name="default" username="%env(string:key:user:url:MONGODB_URL)%" password="%env(string:key:pass:url:MONGODB_URL)%">
                        <mongodb:host host="%env(string:key:host:url:MONGODB_URL)%" port="%env(int:key:port:url:MONGODB_URL)%"/>
                    </mongodb:client>
                    <mongodb:connections name="default" database_name="%env(key:path:url:MONGODB_URL)%"/>
                </mongodb:config>
            </container>

        .. code-block:: php

            // config/packages/mongodb.php
            $container->loadFromExtension('mongodb', [
                'clients' => [
                    'default' => [
                        'hosts' => [
                            [
                                'host' => '%env(string:key:host:url:MONGODB_URL)%',
                                'port' => '%env(int:key:port:url:MONGODB_URL)%',
                            ],
                        ],
                        'username' => '%env(string:key:user:url:MONGODB_URL)%',
                        'password' => '%env(string:key:pass:url:MONGODB_URL)%',
                    ],
                ],
                'connections' => [
                    'default' => [
                        'database_name' => '%env(key:path:url:MONGODB_URL)%',
                    ],
                ],
            ]);

    .. caution::

        In order to ease extraction of the resource from the URL, the leading
        ``/`` is trimmed from the ``path`` component.

``env(query_string:FOO)``
    Parses the query string part of the given URL and returns its components as
    an associative array.

    .. code-block:: bash

        # .env
        MONGODB_URL="mongodb://db_user:db_password@127.0.0.1:27017/db_name?timeout=3000"

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/mongodb.yaml
            mongo_db_bundle:
                clients:
                    default:
                        # ...
                        connectTimeoutMS: '%env(int:key:timeout:query_string:MONGODB_URL)%'

        .. code-block:: xml

            <!-- config/packages/mongodb.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd">

                <mongodb:config>
                    <mongodb:client name="default" connectTimeoutMS="%env(int:key:timeout:query_string:MONGODB_URL)%"/>
                </mongodb:config>
            </container>

        .. code-block:: php

            // config/packages/mongodb.php
            $container->loadFromExtension('mongodb', [
                'clients' => [
                    'default' => [
                        // ...
                        'connectTimeoutMS' => '%env(int:key:timeout:query_string:MONGODB_URL)%',
                    ],
                ],
            ]);

``env(enum:FooEnum:BAR)``
    Tries to convert an environment variable to an actual ``\BackedEnum`` value.
    This processor takes the fully qualified name of the ``\BackedEnum`` as an argument.

    .. code-block:: php

        # App\Enum\Environment
        enum Environment: string
        {
            case Development = 'dev';
            case Production = 'prod';
        }

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                typed_env: '%env(enum:App\Enum\Environment:APP_ENV)%'

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <parameters>
                    <parameter key="typed_env">%env(enum:App\Enum\Environment:APP_ENV)%</parameter>
                </parameters>
            </container>

        .. code-block:: php

            // config/services.php
            $container->setParameter('typed_env', '%env(enum:App\Enum\Environment:APP_ENV)%');

    .. versionadded:: 6.2.1

        The ``env(enum:...)`` env var processor was introduced in Symfony 6.2.1.

It is also possible to combine any number of processors:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        parameters:
            env(AUTH_FILE): "%kernel.project_dir%/config/auth.json"
        google:
            # 1. gets the value of the AUTH_FILE env var
            # 2. replaces the values of any config param to get the config path
            # 3. gets the content of the file stored in that path
            # 4. JSON-decodes the content of the file and returns it
            auth: '%env(json:file:resolve:AUTH_FILE)%'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <parameters>
                <parameter key="env(AUTH_FILE)">%kernel.project_dir%/config/auth.json</parameter>
            </parameters>

            <!-- 1. gets the value of the AUTH_FILE env var -->
            <!-- 2. replaces the values of any config param to get the config path -->
            <!-- 3. gets the content of the file stored in that path -->
            <!-- 4. JSON-decodes the content of the file and returns it -->
            <google auth="%env(json:file:resolve:AUTH_FILE)%"/>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        $container->setParameter('env(AUTH_FILE)', '%kernel.project_dir%/config/auth.json');
        // 1. gets the value of the AUTH_FILE env var
        // 2. replaces the values of any config param to get the config path
        // 3. gets the content of the file stored in that path
        // 4. JSON-decodes the content of the file and returns it
        $container->loadFromExtension('google', [
            'auth' => '%env(json:file:resolve:AUTH_FILE)%',
        ]);

Custom Environment Variable Processors
--------------------------------------

It's also possible to add your own processors for environment variables. First,
create a class that implements
:class:`Symfony\\Component\\DependencyInjection\\EnvVarProcessorInterface`::

    use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

    class LowercasingEnvVarProcessor implements EnvVarProcessorInterface
    {
        public function getEnv(string $prefix, string $name, \Closure $getEnv)
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

To enable the new processor in the app, register it as a service and
:doc:`tag it </service_container/tags>` with the ``container.env_var_processor``
tag. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
this is already done for you, thanks to :ref:`autoconfiguration <services-autoconfigure>`.
