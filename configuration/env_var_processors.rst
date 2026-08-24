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

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'router' => [
                    'http_port' => env('HTTP_PORT')->int(),
                ],
            ],
        ]);

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

        .. code-block:: php

            // config/packages/framework.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'env(SECRET)' => 'some_secret',
                ],
                'framework' => [
                    'secret' => env('SECRET')->string(),
                ],
            ]);

``env(bool:FOO)``
    Casts ``FOO`` to a bool (``true`` values are ``'true'``, ``'on'``, ``'yes'``,
    all numbers except ``0`` and ``0.0`` and all numeric strings except ``'0'``
    and ``'0.0'``; everything else is ``false``):

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/framework.yaml
            parameters:
                env(HTTP_METHOD_OVERRIDE): 'true'
            framework:
                http_method_override: '%env(bool:HTTP_METHOD_OVERRIDE)%'

        .. code-block:: php

            // config/packages/framework.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'env(HTTP_METHOD_OVERRIDE)' => 'true',
                ],
                'framework' => [
                    'http_method_override' => env('HTTP_METHOD_OVERRIDE')->bool(),
                ],
            ]);

``env(not:FOO)``
    Casts ``FOO`` to a bool (just as ``env(bool:...)`` does) except it returns the inverted value
    (falsy values are returned as ``true``, truthy values are returned as ``false``):

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                safe_for_production: '%env(not:APP_DEBUG)%'

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'safe_for_production' => env('APP_DEBUG')->not(),
                ],
            ]);

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

        .. code-block:: php

            // config/packages/security.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'env(HEALTH_CHECK_METHOD)' => 'Symfony\Component\HttpFoundation\Request::METHOD_HEAD',
                ],
                'security' => [
                    'access_control' => [
                        ['path' => '^/health-check$', 'methods' => env('HEALTH_CHECK_METHOD')->const()],
                    ],
                ],
            ]);

``env(base64:FOO)``
    Decodes the content of ``FOO``, which is a base64 encoded string.

``env(json:FOO)``
    Decodes the content of ``FOO``, which is a JSON encoded string. It returns
    either an array or ``null``:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                env(ALLOWED_LANGUAGES): '["en","de","es"]'
                app_allowed_languages: '%env(json:ALLOWED_LANGUAGES)%'

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'env(ALLOWED_LANGUAGES)' => '["en","de","es"]',
                    'app_allowed_languages' => env('ALLOWED_LANGUAGES')->json(),
                ],
            ]);

``env(resolve:FOO)``
    If the content of ``FOO`` includes container parameters (with the syntax
    ``%parameter_name%``), it replaces the parameters by their values:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/sentry.yaml
            parameters:
                sentry_host: '10.0.0.1'
                env(SENTRY_DSN): 'http://%sentry_host%/project'
            sentry:
                dsn: '%env(resolve:SENTRY_DSN)%'

        .. code-block:: php

            // config/packages/sentry.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'sentry_host' => '10.0.0.1',
                    'env(SENTRY_DSN)' => 'http://%sentry_host%/project',
                ],
                'sentry' => [
                    'dsn' => env('SENTRY_DSN')->resolve(),
                ],
            ]);

``env(csv:FOO)``
    Decodes the content of ``FOO``, which is a CSV-encoded string and returns
    an array. An empty string will result in an empty array.

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                env(ALLOWED_LANGUAGES): "en,de,es"
                app_allowed_languages: '%env(csv:ALLOWED_LANGUAGES)%'

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'env(ALLOWED_LANGUAGES)' => 'en,de,es',
                    'app_allowed_languages' => env('ALLOWED_LANGUAGES')->csv(),
                ],
            ]);

``env(shuffle:FOO)``
    Randomly shuffles values of the ``FOO`` env var, which must be an array.

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                env(REDIS_NODES): "127.0.0.1:6380,127.0.0.1:6381"
            services:
                RedisCluster:
                    class: RedisCluster
                    arguments: [null, "%env(shuffle:csv:REDIS_NODES)%"]

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'env(REDIS_NODES)' => '127.0.0.1:6380,127.0.0.1:6381',
                ],
                'services' => [
                    \RedisCluster::class => [
                        'class' => \RedisCluster::class,
                        'arguments' => [null, env('REDIS_NODES')->csv()->shuffle()],
                    ],
                ],
            ]);

``env(file:FOO)``
    Returns the contents of a file whose path is the value of the ``FOO`` env var:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/google.yaml
            parameters:
                env(AUTH_FILE): '%kernel.project_dir%/config/auth.json'
            google:
                auth: '%env(file:AUTH_FILE)%'

        .. code-block:: php

            // config/packages/google.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'env(AUTH_FILE)' => '../config/auth.json',
                ],
                'google' => [
                    'auth' => env('AUTH_FILE')->file(),
                ],
            ]);

``env(require:FOO)``
    ``require()`` the PHP file whose path is the value of the ``FOO``
    env var and return the value returned from it.

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/google.yaml
            parameters:
                env(PHP_FILE): '%kernel.project_dir%/config/.runtime-evaluated.php'
            google:
                auth: '%env(require:PHP_FILE)%'

        .. code-block:: php

            // config/packages/google.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'env(PHP_FILE)' => '../config/.runtime-evaluated.php',
                ],
                'google' => [
                    'auth' => env('PHP_FILE')->require(),
                ],
            ]);

``env(trim:FOO)``
    Trims the content of ``FOO`` env var, removing whitespaces from the beginning
    and end of the string. This is especially useful in combination with the
    ``file`` processor, as it'll remove newlines at the end of a file.

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/google.yaml
            parameters:
                env(AUTH_FILE): '%kernel.project_dir%/config/auth.json'
            google:
                auth: '%env(trim:file:AUTH_FILE)%'

        .. code-block:: php

            // config/packages/google.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'env(AUTH_FILE)' => '../config/auth.json',
                ],
                'google' => [
                    'auth' => env('AUTH_FILE')->file()->trim(),
                ],
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

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'env(SECRETS_FILE)' => '/opt/application/.secrets.json',
                    'database_password' => env('SECRETS_FILE')->file()->json()->key('database_password'),
                    // if SECRETS_FILE contents are: {"database_password": "secret"} it returns "secret"
                ],
            ]);

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

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    // if PRIVATE_KEY is not a valid file path, the content of raw_key is returned
                    'private_key' => env('PRIVATE_KEY')->file()->default('raw_key'),
                    'raw_key' => env('PRIVATE_KEY'),
                ],
            ]);

    When the fallback parameter is omitted (e.g. ``env(default::API_KEY)``), then the
    returned value is ``null``.

``env(url:FOO)``
    Parses an absolute URL and returns its components as an associative array.

    .. code-block:: bash

        # .env
        DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/db_name"

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/doctrine_mongodb.yaml
            doctrine_mongodb:
                clients:
                    default:
                        hosts:
                            - { host: '%env(string:key:host:url:MONGODB_URL)%', port: '%env(int:key:port:url:MONGODB_URL)%' }
                        username: '%env(string:key:user:url:MONGODB_URL)%'
                        password: '%env(string:key:pass:url:MONGODB_URL)%'
                connections:
                    default:
                        database_name: '%env(key:path:url:MONGODB_URL)%'

        .. code-block:: php

            // config/packages/doctrine_mongodb.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'doctrine_mongodb' => [
                    'clients' => [
                        'default' => [
                            'hosts' => [
                                [
                                    'host' => env('MONGODB_URL')->url()->key('host')->string(),
                                    'port' => env('MONGODB_URL')->url()->key('port')->int(),
                                ],
                            ],
                            'username' => env('MONGODB_URL')->url()->key('user')->string(),
                            'password' => env('MONGODB_URL')->url()->key('pass')->string(),
                        ],
                    ],
                    'connections' => [
                        'default' => [
                            'database_name' => env('MONGODB_URL')->url()->key('path'),
                        ],
                    ],
                ],
            ]);

    .. warning::

        In order to ease extraction of the resource from the URL, the leading
        ``/`` is trimmed from the ``path`` component.

``env(query_string:FOO)``
    Parses the query string part of the given URL and returns its components as
    an associative array.

    .. code-block:: bash

        # .env
        DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=12.19&charset=utf8"

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/doctrine_mongodb.yaml
            doctrine_mongodb:
                clients:
                    default:
                        # ...
                        connectTimeoutMS: '%env(int:key:timeout:query_string:MONGODB_URL)%'

        .. code-block:: php

            // config/packages/doctrine_mongodb.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'doctrine_mongodb' => [
                    'clients' => [
                        'default' => [
                            // ...
                            'connectTimeoutMS' => env('MONGODB_URL')->queryString()->key('timeout')->int(),
                        ],
                    ],
                ],
            ]);

``env(enum:FooEnum:BAR)``
    Tries to convert an environment variable to an actual ``\BackedEnum`` value.
    This processor takes the fully qualified name of the ``\BackedEnum`` as an argument::

        // App\Enum\Suit.php
        enum Suit: string
        {
            case Clubs = 'clubs';
            case Spades = 'spades';
            case Diamonds = 'diamonds';
            case Hearts = 'hearts';
        }

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                suit: '%env(enum:App\Enum\Suit:CARD_SUIT)%'

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use App\Enum\Suit;

            return App::config([
                'parameters' => [
                    'suit' => env('CARD_SUIT')->enum(Suit::class),
                ],
            ]);

    The value stored in the ``CARD_SUIT`` env var would be a string (e.g. ``'spades'``)
    but the application will use the enum value (e.g. ``Suit::Spades``).

``env(defined:NO_FOO)``
    Evaluates to ``true`` if the env var exists and its value is not ``''``
    (an empty string) or ``null``; it returns ``false`` otherwise.

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                typed_env: '%env(defined:FOO)%'

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'typed_env' => env('FOO')->defined(),
                ],
            ]);

.. _urlencode_environment_variable_processor:

``env(urlencode:FOO)``
    Encodes the content of the ``FOO`` env var using the :phpfunction:`urlencode`
    PHP function. This is especially useful when ``FOO`` value is not compatible
    with DSN syntax.

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                env(DATABASE_URL): 'mysql://db_user:foo@b$r@127.0.0.1:3306/db_name'
                encoded_database_url: '%env(urlencode:DATABASE_URL)%'

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'parameters' => [
                    'env(DATABASE_URL)' => 'mysql://db_user:foo@b$r@127.0.0.1:3306/db_name',
                    'encoded_database_url' => env('DATABASE_URL')->urlencode(),
                ],
            ]);

It is also possible to combine any number of processors:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/google.yaml
        parameters:
            env(AUTH_FILE): "%kernel.project_dir%/config/auth.json"
        google:
            # 1. gets the value of the AUTH_FILE env var
            # 2. replaces the values of any config param to get the config path
            # 3. gets the content of the file stored in that path
            # 4. JSON-decodes the content of the file and returns it
            auth: '%env(json:file:resolve:AUTH_FILE)%'

    .. code-block:: php

        // config/packages/google.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'parameters' => [
                'env(AUTH_FILE)' => '%kernel.project_dir%/config/auth.json',
            ],
            'google' => [
                // 1. gets the value of the AUTH_FILE env var
                // 2. replaces the values of any config param to get the config path
                // 3. gets the content of the file stored in that path
                // 4. JSON-decodes the content of the file and returns it
                'auth' => env('AUTH_FILE')->resolve()->file()->json(),
            ],
        ]);

Custom Environment Variable Processors
--------------------------------------

It's also possible to add your own processors for environment variables. First,
create a class that implements
:class:`Symfony\\Component\\DependencyInjection\\EnvVarProcessorInterface`::

    use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

    class LowercasingEnvVarProcessor implements EnvVarProcessorInterface
    {
        public function getEnv(string $prefix, string $name, \Closure $getEnv): string
        {
            $env = $getEnv($name);

            return strtolower($env);
        }

        public static function getProvidedTypes(): array
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

Resolving Environment Variable At Compile Time
----------------------------------------------

Environment variables are resolved at runtime, but you can also resolve them
:ref:`at compile time <resolving-env-vars-at-compile-time>`.
