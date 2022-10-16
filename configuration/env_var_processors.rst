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
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >
            <framework:config>
                <framework:router http-port="%env(int:HTTP_PORT)%"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return static function (ContainerConfigurator $container) {
            $container->extension('framework', [
                'router' => [
                    'http_port' => '%env(int:HTTP_PORT)%',
                ],
            ]);
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
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
            >
                <parameters>
                    <parameter key="env(SECRET)">some_secret</parameter>
                </parameters>

                <framework:config secret="%env(string:SECRET)%"/>
            </container>

        .. code-block:: php

            // config/packages/framework.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return static function (ContainerConfigurator $container) {
                $container->parameters()
                    ->set('env(SECRET)', 'some_secret')
                ;

                $container->extension('framework', [
                    'secret' => '%env(string:SECRET)%',
                ]);
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
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
            >
                <parameters>
                    <parameter key="env(HTTP_METHOD_OVERRIDE)">true</parameter>
                </parameters>

                <framework:config http-method-override="%env(bool:HTTP_METHOD_OVERRIDE)%"/>
            </container>

        .. code-block:: php

            // config/packages/framework.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return static function (ContainerConfigurator $container) {
                $container->parameters()
                    ->set('env(HTTP_METHOD_OVERRIDE)', 'true')
                ;

                $container->extension('framework', [
                    'http_method_override' => '%env(bool:HTTP_METHOD_OVERRIDE)%',
                ]);
            };

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
                # ...

                access_control:
                    - { path: '^/health-check$', methods: '%env(const:HEALTH_CHECK_METHOD)%' }

        .. code-block:: xml

            <!-- config/packages/security.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <srv:container xmlns="http://symfony.com/schema/dic/security"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:srv="http://symfony.com/schema/dic/services"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd"
            >
                <srv:parameters>
                    <srv:parameter key="env(HEALTH_CHECK_METHOD)">Symfony\Component\HttpFoundation\Request::METHOD_HEAD</srv:parameter>
                </srv:parameters>

                <config>
                    <!-- ... -->

                    <rule path="^/health-check$" methods="%env(const:HEALTH_CHECK_METHOD)%"/>
                </config>
            </srv:container>

        .. code-block:: php

            // config/packages/security.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use Symfony\Component\HttpFoundation\Request;

            return static function (ContainerConfigurator $container) {
                $container->parameters()
                    ->set('env(HEALTH_CHECK_METHOD)', Request::METHOD_HEAD)
                ;

                $container->extension('security', [
                    // ...

                    'access_control' => [
                        [
                            'path' => '^/health-check$',
                            'methods' => '%env(const:HEALTH_CHECK_METHOD)%',
                        ],
                    ],
                ]);
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
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
            >
                <parameters>
                    <parameter key="env(TRUSTED_HOSTS)">["10.0.0.1", "10.0.0.2"]</parameter>
                </parameters>

                <framework:config trusted-hosts="%env(json:TRUSTED_HOSTS)%"/>
            </container>

        .. code-block:: php

            // config/packages/framework.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return static function (ContainerConfigurator $container) {
                $container->parameters()
                    ->set('env(TRUSTED_HOSTS)', '["10.0.0.1", "10.0.0.2"]')
                ;

                $container->extension('framework', [
                    'trusted_hosts' => '%env(json:TRUSTED_HOSTS)%',
                ]);
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
            <srv:container xmlns="https://sentry.io/schema/dic/sentry-symfony"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:srv="http://symfony.com/schema/dic/services"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    https://sentry.io/schema/dic/sentry-symfony
                    https://sentry.io/schema/dic/sentry-symfony/sentry-1.0.xsd"
            >
                <srv:parameters>
                    <srv:parameter key="env(HOST)">10.0.0.1</srv:parameter>
                    <srv:parameter key="sentry_host">%env(HOST)%</srv:parameter>
                    <srv:parameter key="env(SENTRY_DSN)">http://%sentry_host%/project</srv:parameter>
                </srv:parameters>

                <config dsn="%env(resolve:SENTRY_DSN)%"/>
            </srv:container>

        .. code-block:: php

            // config/packages/sentry.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return static function (ContainerConfigurator $container) {
                $container->parameters()
                    ->set('env(HOST)', '10.0.0.1')
                    ->set('sentry_host', '%env(HOST)%')
                    ->set('env(SENTRY_DSN)', 'http://%sentry_host%/project')
                ;

                $container->extension('sentry', [
                    'dsn' => '%env(resolve:SENTRY_DSN)%',
                ]);
            };

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
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
            >
                <parameters>
                    <parameter key="env(TRUSTED_HOSTS)">10.0.0.1,10.0.0.2</parameter>
                </parameters>

                <framework:config trusted-hosts="%env(csv:TRUSTED_HOSTS)%"/>
            </container>

        .. code-block:: php

            // config/packages/framework.php
            $container->setParameter('env(TRUSTED_HOSTS)', '10.0.0.1,10.0.0.2');
            $container->loadFromExtension('framework', [
                'trusted_hosts' => '%env(csv:TRUSTED_HOSTS)%',
            ]);

``env(file:FOO)``
    Returns the contents of a file whose path is the value of the ``FOO`` env var:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                env(AUTH_FILE): '../config/auth.json'

            services:
                some_client:
                    $auth: '%env(file:AUTH_FILE)%'

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd"
            >
                <parameters>
                    <parameter key="env(AUTH_FILE)">../config/auth.json</parameter>
                </parameters>

                <services>
                    <service id="some_client">
                        <argument key="$auth">%env(file:AUTH_FILE)%</argument>
                    </service>
                </services>
            </container>

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return static function (ContainerConfigurator $container) {
                $container->parameters()
                    ->set('env(AUTH_FILE)', '../config/auth.json')
                ;

                $container->services()
                    ->set('some_client')
                        ->arg('$auth', '%env(file:AUTH_FILE)%')
                ;
            };

``env(require:FOO)``
    ``require()`` the PHP file whose path is the value of the ``FOO``
    env var and return the value returned from it.

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                env(PHP_FILE): '../config/.runtime-evaluated.php'

            services:
                some_client:
                    $auth: '%env(require:PHP_FILE)%'

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd"
            >
                <parameters>
                    <parameter key="env(PHP_FILE)">../config/.runtime-evaluated.php</parameter>
                </parameters>

                <services>
                    <service id="some_client">
                        <argument key="$auth">%env(require:PHP_FILE)%</argument>
                    </service>
                </services>
            </container>

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return static function (ContainerConfigurator $container) {
                $container->parameters()
                    ->set('env(PHP_FILE)', '../config/.runtime-evaluated.php')
                ;

                $container->services()
                    ->set('some_client')
                        ->arg('$auth', '%env(require:PHP_FILE)%')
                ;
            };

    .. versionadded:: 4.3

        The ``require`` processor was introduced in Symfony 4.3.

``env(trim:FOO)``
    Trims the content of ``FOO`` env var, removing whitespaces from the beginning
    and end of the string. This is especially useful in combination with the
    ``file`` processor, as it'll remove newlines at the end of a file.

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                env(AUTH_FILE): '../config/auth.json'

            services:
                some_client:
                    $auth: '%env(trim:file:AUTH_FILE)%'

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd"
            >
                <parameters>
                    <parameter key="env(AUTH_FILE)">../config/auth.json</parameter>
                </parameters>

                <services>
                    <service id="some_client">
                        <argument key="$auth">%env(trim:file:AUTH_FILE)%</argument>
                    </service>
                </services>
            </container>

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return static function (ContainerConfigurator $container) {
                $container->parameters()
                    ->set('env(AUTH_FILE)', '../config/auth.json')
                ;

                $container->services()
                    ->set('some_client')
                        ->arg('$auth', '%env(trim:file:AUTH_FILE)%')
                ;
            };

    .. versionadded:: 4.3

        The ``trim`` processor was introduced in Symfony 4.3.

``env(key:FOO:BAR)``
    Retrieves the value associated with the key ``FOO`` from the array whose
    contents are stored in the ``BAR`` env var:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                env(SECRETS_FILE): '/opt/application/.secrets.json'
                # if SECRETS_FILE contents are: {"database_password": "secret"} it returns "secret"
                database_password: '%env(key:database_password:json:file:SECRETS_FILE)%'

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd"
            >
                <parameters>
                    <parameter key="env(SECRETS_FILE)">/opt/application/.secrets.json</parameter>

                    <!-- if SECRETS_FILE contents are: {"database_password": "secret"} it returns "secret" -->
                    <parameter key="database_password">%env(key:database_password:json:file:SECRETS_FILE)%</parameter>
                </parameters>
            </container>

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return static function (ContainerConfigurator $container) {
                $container->parameters()
                    ->set('env(SECRETS_FILE)', '/opt/application/.secrets.json')
                    // if SECRETS_FILE contents are: {"database_password": "secret"} it returns "secret"
                    ->set('database_password', '%env(key:database_password:json:file:SECRETS_FILE)%')
                ;
            };

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
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd"
            >
                <parameters>
                    <!-- if PRIVATE_KEY is not a valid file path, the content of raw_key is returned -->
                    <parameter key="private_key">%env(default:raw_key:file:PRIVATE_KEY)%</parameter>
                    <parameter key="raw_key">%env(PRIVATE_KEY)%</parameter>
                </parameters>
            </container>

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return static function (ContainerConfigurator $container) {
                $container->parameters()
                    // if PRIVATE_KEY is not a valid file path, the content of raw_key is returned
                    ->set('private_key', '%env(default:raw_key:file:PRIVATE_KEY)%')
                    ->set('raw_key', '%env(PRIVATE_KEY)%')
                ;
            };

    When the fallback parameter is omitted (e.g. ``env(default::API_KEY)``), then the
    returned value is ``null``.

    .. versionadded:: 4.3

        The ``default`` processor was introduced in Symfony 4.3.

``env(url:FOO)``
    Parses an absolute URL and returns its components as an associative array.

    .. code-block:: bash

        # .env
        MONGODB_URL="mongodb://db_user:db_password@127.0.0.1:27017/db_name"

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/mongo_db_bundle.yaml
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

            <!-- config/packages/mongo_db_bundle.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:mongo-db="http://example.org/schema/dic/mongo-db-bundle"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    https://example.org/schema/dic/mongo-db-bundle
                    https://example.org/schema/dic/mongo-db-bundle/mongo-db-1.0.xsd"
            >
                <mongo-db:config>
                    <mongo-db:client name="default"
                        username="%env(string:key:user:url:MONGODB_URL)%"
                        password="%env(string:key:pass:url:MONGODB_URL)%"
                    >
                        <mongo-db:host
                            host="%env(string:key:host:url:MONGODB_URL)%"
                            port="%env(int:key:port:url:MONGODB_URL)%"
                        />
                    </mongo-db:client>
                    <mongo-db:connections name="default"
                        database_name="%env(key:path:url:MONGODB_URL)%"
                    />
                </mongo-db:config>
            </container>

        .. code-block:: php

            // config/packages/mongo_db_bundle.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return static function (ContainerConfigurator $container) {
                $container->extension('mongo_db_bundle', [
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
            };

    .. caution::

        In order to ease extraction of the resource from the URL, the leading
        ``/`` is trimmed from the ``path`` component.

    .. versionadded:: 4.3

        The ``url`` processor was introduced in Symfony 4.3.

``env(query_string:FOO)``
    Parses the query string part of the given URL and returns its components as
    an associative array.

    .. code-block:: bash

        # .env
        MONGODB_URL="mongodb://db_user:db_password@127.0.0.1:27017/db_name?timeout=3000"

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/mongo_db_bundle.yaml
            mongo_db_bundle:
                clients:
                    default:
                        # ...
                        connect_timeout_ms: '%env(int:key:timeout:query_string:MONGODB_URL)%'

        .. code-block:: xml

            <!-- config/packages/mongo_db_bundle.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:mongo-db="http://example.org/schema/dic/mongo-db-bundle"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    https://example.org/schema/dic/mongo-db-bundle
                    https://example.org/schema/dic/mongo-db-bundle/mongo-db-1.0.xsd"
            >
                <mongo-db:config>
                    <mongo-db:client name="default"
                        connect-timeout-ms="%env(int:key:timeout:query_string:MONGODB_URL)%"
                    >
                        <!-- ... -->
                    </mongo-db:client>
                </mongo-db:config>
            </container>

        .. code-block:: php

            // config/packages/mongo_db_bundle.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return static function (ContainerConfigurator $container) {
                $container->extension('mongo_db_bundle', [
                    'clients' => [
                        'default' => [
                            // ...
                            'connect_timeout_ms' => '%env(int:key:timeout:query_string:MONGODB_URL)%',
                        ],
                    ],
                ]);
            };

    .. versionadded:: 4.3

        The ``query_string`` processor was introduced in Symfony 4.3.

It is also possible to combine any number of processors:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            env(AUTH_FILE): "%kernel.project_dir%/config/auth.json"

        services:
            # ...

            # 1. gets the value of the AUTH_FILE env var
            # 2. replaces the values of any config param to get the config path
            # 3. gets the content of the file stored in that path
            # 4. JSON-decodes the content of the file and returns it
            App\SomeAuthenticator:
                $auth: '%env(json:file:resolve:AUTH_FILE)%'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd"
        >
            <parameters>
                <parameter key="env(AUTH_FILE)">%kernel.project_dir%/config/auth.json</parameter>
            </parameters>

            <services>
                <!-- ... -->

                <!-- 1. gets the value of the AUTH_FILE env var -->
                <!-- 2. replaces the values of any config param to get the config path -->
                <!-- 3. gets the content of the file stored in that path -->
                <!-- 4. JSON-decodes the content of the file and returns it -->
                <service id="App\SomeAuthenticator">
                    <argument index="$auth">%env(json:file:resolve:AUTH_FILE)%</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\SomeAuthenticator;

        return static function (ContainerConfigurator $container) {
            $container->parameters()
                ->set('env(AUTH_FILE)', '%kernel.project_dir%/config/auth.json')
            ;

            $container->services()
                // ...

                // 1. gets the value of the AUTH_FILE env var
                // 2. replaces the values of any config param to get the config path
                // 3. gets the content of the file stored in that path
                // 4. JSON-decodes the content of the file and returns it
                ->set(SomeAuthenticator::class)
                    ->arg('$auth', '%env(json:file:resolve:AUTH_FILE)%')
            ;
        };

Custom Environment Variable Processors
--------------------------------------

It's also possible to add your own processors for environment variables. First,
create a class that implements
:class:`Symfony\\Component\\DependencyInjection\\EnvVarProcessorInterface`::

    use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

    class LowercasingEnvVarProcessor implements EnvVarProcessorInterface
    {
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

To enable the new processor in the app, register it as a service and
:doc:`tag it </service_container/tags>` with the ``container.env_var_processor``
tag. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
this is already done for you, thanks to :ref:`autoconfiguration <services-autoconfigure>`.
