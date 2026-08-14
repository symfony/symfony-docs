Framework Configuration Reference (FrameworkBundle)
===================================================

The FrameworkBundle defines the main framework configuration, from sessions and
translations to forms, validation, routing and more. All these options are
configured under the ``framework`` key in your application configuration.

.. code-block:: terminal

    # displays the default config values defined by Symfony
    $ php bin/console config:dump-reference framework

    # displays the actual config values used by your application
    $ php bin/console debug:config framework

.. _reference-assets:

assets
~~~~~~

The following options configure the behavior of the
:ref:`Twig asset() function <reference-twig-function-asset>`.

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

.. _reference-assets-base-path:

base_path
.........

**type**: ``string``

This option allows you to prepend a base path to the URLs generated for assets:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            assets:
                base_path: '/images'

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'assets' => [
                    'base_path' => '/images',
                ],
            ],
        ]);

With this configuration, a call to ``asset('logo.png')`` will generate
``/images/logo.png`` instead of ``/logo.png``.

.. _reference-templating-base-urls:
.. _reference-assets-base-urls:

base_urls
.........

**type**: ``array``

This option allows you to define base URLs to be used for assets.
If multiple base URLs are provided, Symfony will select one from the
collection each time it generates an asset's path:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            assets:
                base_urls:
                    - 'https://cdn.example.com/'

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'assets' => [
                    'base_urls' => ['https://cdn.example.com/'],
                ],
            ],
        ]);

.. _reference-assets-json-manifest-path:
.. _reference-templating-json-manifest-path:

json_manifest_path
..................

**type**: ``string`` **default**: ``null``

The file path or absolute URL to a ``manifest.json`` file containing an
associative array of asset names and their respective compiled names. A common
cache-busting technique using a "manifest" file works by writing out assets with
a "hash" appended to their file names (e.g. ``main.ae433f1cb.css``) during a
front-end compilation routine.

.. tip::

    Symfony's :ref:`Webpack Encore <frontend-webpack-encore>` supports
    :ref:`outputting hashed assets <encore-long-term-caching>`. Moreover, this
    can be incorporated into many other workflows, including Webpack using
    `webpack-manifest-plugin`_.

This option can be set globally for all assets and individually for each asset
package:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            assets:
                # this manifest is applied to every asset (including packages)
                json_manifest_path: "%kernel.project_dir%/public/build/manifest.json"
                # you can use absolute URLs too and Symfony will download them automatically
                # json_manifest_path: 'https://cdn.example.com/manifest.json'
                packages:
                    foo_package:
                        # this package uses its own manifest (the default file is ignored)
                        json_manifest_path: "%kernel.project_dir%/public/build/a_different_manifest.json"
                        # Throws an exception when an asset is not found in the manifest
                        strict_mode: %kernel.debug%
                    bar_package:
                        # this package uses the global manifest (the default file is used)
                        base_path: '/images'

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'assets' => [
                    // this manifest is applied to every asset (including packages)
                    'json_manifest_path' => '%kernel.project_dir%/public/build/manifest.json',
                    // you can use absolute URLs too and Symfony will download them automatically
                    // 'json_manifest_path' => 'https://cdn.example.com/manifest.json',
                    'packages' => [
                        'foo_package' => [
                            // this package uses its own manifest (the default file is ignored)
                            'json_manifest_path' => '%kernel.project_dir%/public/build/a_different_manifest.json',
                            // Throws an exception when an asset is not found in the manifest
                            'strict_mode' => param('kernel.debug'),
                        ],
                        'bar_package' => [
                            // this package uses the global manifest (the default file is used)
                            'base_path' => '/images',
                        ],
                    ],
                ],
            ],
        ]);

.. note::

    This parameter cannot be set at the same time as ``version`` or ``version_strategy``.
    Additionally, this option cannot be nullified at the package scope if a global manifest
    file is specified.

.. tip::

    If you request an asset that is *not found* in the ``manifest.json`` file, the original -
    *unmodified* - asset path will be returned.
    You can set ``strict_mode`` to ``true`` to get an exception when an asset is *not found*.

.. note::

    If a URL is set, the JSON manifest is downloaded on each request using the `http_client`_.

After having configured one or more asset packages, you have two ways of injecting
them in any service or controller:

**(1) Use a specific argument name**

Type-hint your constructor/method argument with ``PackageInterface`` and name
the argument using this pattern: "asset package name in camelCase". For example,
to inject the ``foo_package`` package defined earlier::

    use Symfony\Component\Asset\PackageInterface;

    class SomeService
    {
        public function __construct(
            private PackageInterface $fooPackage
        ) {
            // ...
        }
    }

**(2) Use the ``#[Target]`` attribute**

When :ref:`dealing with multiple implementations of the same type <autowiring-multiple-implementations-same-type>`
the ``#[Target]`` attribute helps you select which one to inject. Symfony creates
a target with the same name as the asset package.

For example, to select the ``foo_package`` package defined earlier::

    // ...
    use Symfony\Component\DependencyInjection\Attribute\Target;

    class SomeService
    {
        public function __construct(
            #[Target('foo_package')] private PackageInterface $package
        ) {
            // ...
        }
    }

.. _reference-framework-assets-packages:

packages
........

You can group assets into packages, to specify different base URLs for them:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            assets:
                packages:
                    avatars:
                        base_urls: 'https://static_cdn.example.com/avatars'

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                // ...
                'assets' => [
                    'packages' => [
                        'avatars' => [
                            'base_urls' => ['https://static_cdn.example.com/avatars'],
                        ],
                    ],
                ],
            ],
        ]);

Now you can use the ``avatars`` package in your templates:

.. code-block:: html+twig

    <img src="{{ asset('...', 'avatars') }}">

Each package can configure the following options:

* :ref:`base_path <reference-assets-base-path>`
* :ref:`base_urls <reference-assets-base-urls>`
* :ref:`version_strategy <reference-assets-version-strategy>`
* :ref:`version <reference-framework-assets-version>`
* :ref:`version_format <reference-assets-version-format>`
* :ref:`json_manifest_path <reference-assets-json-manifest-path>`
* :ref:`strict_mode <reference-assets-strict-mode>`

.. _reference-assets-strict-mode:

strict_mode
...........

**type**: ``boolean`` **default**: ``false``

When enabled, the strict mode asserts that all requested assets are in the
manifest file. This option is useful to detect typos or missing assets. The
recommended value is ``%kernel.debug%``.

.. _reference-framework-assets-version:
.. _ref-framework-assets-version:

version
.......

**type**: ``string``

This option is used to *bust* the cache on assets by globally adding a query
parameter to all rendered asset paths (e.g. ``/images/logo.png?v2``). This
applies only to assets rendered via the Twig ``asset()`` function (or PHP
equivalent).

For example, suppose you have the following:

.. code-block:: html+twig

    <img src="{{ asset('images/logo.png') }}" alt="Symfony!"/>

By default, this will render a path to your image such as ``/images/logo.png``.
Now, activate the ``version`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            assets:
                version: 'v2'

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'assets' => [
                    'version' => 'v2',
                ],
            ],
        ]);

Now, the same asset will be rendered as ``/images/logo.png?v2``. If you use
this feature, you **must** manually increment the ``version`` value
before each deployment so that the query parameters change.

You can also control how the query string works via the `version_format`_
option.

.. note::

    This parameter cannot be set at the same time as ``version_strategy`` or ``json_manifest_path``.

.. tip::

    As with all settings, you can use a parameter as value for the
    ``version``. This makes it easier to increment the cache on each
    deployment.

.. _reference-templating-version-format:
.. _reference-assets-version-format:

version_format
..............

**type**: ``string`` **default**: ``%%s?%%s``

This specifies a :phpfunction:`sprintf` pattern that will be used with the
`version`_ option to construct an asset's path. By default, the pattern
adds the asset's version as a query string. For example, if
``version_format`` is set to ``%%s?version=%%s`` and ``version``
is set to ``5``, the asset's path would be ``/images/logo.png?version=5``.

.. note::

    All percentage signs (``%``) in the format string must be doubled to
    escape the character. Without escaping, values might inadvertently be
    interpreted as :ref:`service-container-parameters`.

.. tip::

    Some CDN's do not support cache-busting via query strings, so injecting
    the version into the actual file path is necessary. Thankfully,
    ``version_format`` is not limited to producing versioned query
    strings.

    The pattern receives the asset's original path and version as its first
    and second parameters, respectively. Since the asset's path is one
    parameter, you cannot modify it in-place (e.g. ``/images/logo-v5.png``);
    however, you can prefix the asset's path using a pattern of
    ``version-%%2$s/%%1$s``, which would result in the path
    ``version-5/images/logo.png``.

    URL rewrite rules could then be used to disregard the version prefix
    before serving the asset. Alternatively, you could copy assets to the
    appropriate version path as part of your deployment process and skip
    any URL rewriting. The latter option is useful if you would like older
    asset versions to remain accessible at their original URL.

.. _reference-assets-version-strategy:
.. _reference-templating-version-strategy:

version_strategy
................

**type**: ``string`` **default**: ``null``

The service id of the :doc:`asset version strategy </frontend/custom_version_strategy>`
applied to the assets. This option can be set globally for all assets and
individually for each asset package:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            assets:
                # this strategy is applied to every asset (including packages)
                version_strategy: 'app.asset.my_versioning_strategy'
                packages:
                    foo_package:
                        # this package removes any versioning (its assets won't be versioned)
                        version: ~
                    bar_package:
                        # this package uses its own strategy (the default strategy is ignored)
                        version_strategy: 'app.asset.another_version_strategy'
                    baz_package:
                        # this package inherits the default strategy
                        base_path: '/images'

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'assets' => [
                    // this strategy is applied to every asset (including packages)
                    'version_strategy' => 'app.asset.my_versioning_strategy',
                    'packages' => [
                        'foo_package' => [
                            // this package removes any versioning (its assets won't be versioned)
                            'version' => null,
                        ],
                        'bar_package' => [
                            // this package uses its own strategy (the default strategy is ignored)
                            'version_strategy' => 'app.asset.another_version_strategy',
                        ],
                        'baz_package' => [
                            // this package inherits the default strategy
                            'base_path' => '/images',
                        ],
                    ],
                ],
            ],
        ]);

.. note::

    This parameter cannot be set at the same time as ``version`` or ``json_manifest_path``.

.. _reference-asset-mapper:

asset_mapper
~~~~~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable the AssetMapper component in your application.

paths
.....

**type**: ``array`` **default**: ``['assets/']``

Directories that hold assets that should be available through the asset mapper.
Can be a simple array of paths or an associative array of
``"path/to/assets": "namespace"`` pairs.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/asset_mapper.yaml
        framework:
            asset_mapper:
                paths:
                    - assets/

    .. code-block:: xml

        <!-- config/packages/asset_mapper.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">
            <framework:config>
                <framework:asset-mapper>
                    <framework:path>assets/</framework:path>
                </framework:asset-mapper>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/asset_mapper.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->assetMapper()
                ->path('assets/', null)
            ;
        };

excluded_patterns
.................

**type**: ``array``

Array of glob patterns of asset file paths that should not be in the asset
mapper (e.g. ``['*/assets/build/*', '*/*_.scss']``).

exclude_dotfiles
................

**type**: ``boolean`` **default**: ``true``

If ``true``, any files starting with ``.`` will be excluded from the asset mapper.

server
......

**type**: ``boolean`` **default**: ``%kernel.debug%``

If ``true``, a "dev server" will return the assets from the public directory.
This is enabled by default in debug mode only.

public_prefix
.............

**type**: ``string`` **default**: ``'/assets/'``

The public path where the assets will be written to (and served from when
``server`` is ``true``).

missing_import_mode
...................

**type**: ``string`` **default**: ``'warn'`` **possible values**: ``'strict'``, ``'warn'`` or ``'ignore'``

Behavior if an asset cannot be found when imported from JavaScript or CSS files
(e.g. ``import './non-existent.js'``). ``strict`` means an exception is thrown,
``warn`` means a warning is logged, ``ignore`` means the import is left as-is.

extensions
..........

**type**: ``array``

Key-value pair of file extensions set to their MIME type
(e.g. ``['.zip': 'application/zip']``).

importmap_path
..............

**type**: ``string`` **default**: ``'%kernel.project_dir%/importmap.php'``

The path of the ``importmap.php`` file.

importmap_polyfill
..................

**type**: ``string`` or ``false`` **default**: ``'es-module-shims'``

The importmap name that will be used to load the polyfill. Set to ``false``
to disable.

importmap_script_attributes
...........................

**type**: ``array``

Key-value pair of attributes to add to script tags output for the importmap
(e.g. ``['data-turbo-track': 'reload']``).

importmap_integrity_algorithms
..............................

**type**: ``array`` **default**: ``[]``

A list of hash algorithms used to :ref:`add integrity metadata <importmap-integrity>`
to the importmap and to its preloaded module and stylesheet tags. Allowed values
are ``sha256``, ``sha384`` and ``sha512``; the default empty array disables
this feature.

.. versionadded:: 8.2

    The ``importmap_integrity_algorithms`` option was introduced in Symfony 8.2.

vendor_dir
..........

**type**: ``string`` **default**: ``'%kernel.project_dir%/assets/vendor'``

The directory to store JavaScript vendors.

precompress
...........

**type**: ``boolean`` **default**: ``false``

When enabled, assets are precompressed with Brotli, Zstandard and gzip during
compilation.

precompress.formats
"""""""""""""""""""

**type**: ``array``

Array of compression formats to enable. Supported values: ``brotli``,
``zstandard`` and ``gzip``. Defaults to all formats supported by the system.

precompress.extensions
""""""""""""""""""""""

**type**: ``array``

Array of file extensions to compress. The entire list must be provided, no
merging occurs.

.. _reference-cache:

cache
~~~~~

.. _reference-cache-app:

app
...

**type**: ``string`` **default**: ``cache.adapter.filesystem``

The cache adapter used by the ``cache.app`` service. The FrameworkBundle
ships with multiple adapters: ``cache.adapter.apcu``, ``cache.adapter.system``,
``cache.adapter.filesystem``, ``cache.adapter.psr6``, ``cache.adapter.redis``,
``cache.adapter.memcached``, ``cache.adapter.pdo`` and
``cache.adapter.doctrine_dbal``.

There's also a special adapter called ``cache.adapter.array`` which stores
contents in memory using a PHP array and it's used to disable caching (mostly on
the ``dev`` environment).

.. tip::

    It might be tough to understand at the beginning, so to avoid confusion
    remember that all pools perform the same actions but on different media,
    depending on the adapter they are based on. Internally, a pool wraps the definition
    of an adapter.

default_doctrine_dbal_provider
..............................

**type**: ``string`` **default**: ``database_connection``

The service ID of the Doctrine DBAL connection to use as the default cache
provider. The provider is available as the ``cache.default_doctrine_dbal_provider``
service.

default_doctrine_provider
.........................

**type**: ``string``

The service name to use as your default Doctrine provider. The provider is
available as the ``cache.default_doctrine_provider`` service.

default_memcached_provider
..........................

**type**: ``string`` **default**: ``memcached://localhost``

The DSN used by the Memcached provider. The provider is available as the ``cache.default_memcached_provider``
service.

default_pdo_provider
....................

**type**: ``string`` **default**: ``doctrine.dbal.default_connection``

The service id of the database connection, which should be either a PDO or a
Doctrine DBAL instance. The provider is available as the ``cache.default_pdo_provider``
service.

default_psr6_provider
.....................

**type**: ``string``

The service name to use as your default PSR-6 provider. It is available as
the ``cache.default_psr6_provider`` service.

default_redis_provider
......................

**type**: ``string`` **default**: ``redis://localhost``

The DSN to use by the Redis provider. The provider is available as the ``cache.default_redis_provider``
service.

default_valkey_provider
.......................

**type**: ``string`` **default**: ``valkey://localhost``

The DSN to use by the Valkey provider. The provider is available as the ``cache.default_valkey_provider``
service.

directory
.........

**type**: ``string`` **default**: ``%kernel.cache_dir%/pools``

The path to the cache directory used by services inheriting from the
``cache.adapter.filesystem`` adapter (including ``cache.app``).

pools
.....

**type**: ``array``

A list of cache pools to be created by the framework extension.

.. seealso::

    For more information about how pools work, see :ref:`cache pools <component-cache-cache-pools>`.

To configure a Redis cache pool with a default lifetime of 1 hour, do the following:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            cache:
                pools:
                    cache.mycache:
                        adapter: cache.adapter.redis
                        default_lifetime: 3600

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'cache' => [
                    'pools' => [
                        'cache.mycache' => [
                            'adapter' => 'cache.adapter.redis',
                            'default_lifetime' => 3600,
                        ],
                    ],
                ],
            ],
        ]);

adapter
"""""""

**type**: ``string`` **default**: ``cache.app``

The service name of the adapter to use. You can specify one of the default
services that follow the pattern ``cache.adapter.[type]``. Alternatively you
can specify another cache pool as base, which will make this pool inherit the
settings from the base pool as defaults.

.. note::

    Your service needs to implement the ``Psr\Cache\CacheItemPoolInterface`` interface.

clearer
"""""""

**type**: ``string``

The cache clearer used to clear your PSR-6 cache.

.. seealso::

    For more information, see :class:`Symfony\\Component\\HttpKernel\\CacheClearer\\Psr6CacheClearer`.

default_lifetime
""""""""""""""""

**type**: ``integer`` | ``string``

Default lifetime of your cache items. Give an integer value to set the default
lifetime in seconds. A string value could be an ISO 8601 time interval, like ``"PT5M"``
or a PHP date expression that is accepted by ``strtotime()``, like ``"5 minutes"``.

If no value is provided, the cache adapter will fall back to the default value on
the actual cache storage.

.. _reference-cache-pools-name:

name
""""

**type**: ``prototype``

Name of the pool you want to create.

.. note::

    Your pool name must differ from ``cache.app`` or ``cache.system``.

provider
""""""""

**type**: ``string``

Overwrite the default service name or DSN respectively, if you do not want to
use what is configured as ``default_X_provider`` under ``cache``. See the
description of the default provider setting above for information on how to
specify your specific provider.

public
""""""

**type**: ``boolean`` **default**: ``false``

Whether your service should be public or not.

tags
""""

**type**: ``boolean`` | ``string`` **default**: ``null``

Whether your service should be able to handle tags or not.
Can also be the service id of another cache pool where tags will be stored.

marshaller
""""""""""

**type**: ``string``

.. versionadded:: 8.1

    The ``marshaller`` option was introduced in Symfony 8.1.

The service ID of a custom marshaller to use for this pool. When not set,
the pool uses the global ``cache.default_marshaller`` service. Use it when you
need different serialization strategies per pool (e.g. encrypting or compressing
data only in specific pools).

See :ref:`configuring a custom marshaller per cache pool <cache-custom-marshaller-per-pool>`
for a detailed explanation and examples.

.. _reference-cache-prefix-seed:

prefix_seed
...........

**type**: ``string`` **default**: ``_%kernel.project_dir%.%kernel.container_class%``

This value is used as part of the "namespace" generated for the
cache item keys. A common practice is to use the unique name of the application
(e.g. ``symfony.com``) because that prevents naming collisions when deploying
multiple applications into the same path (on different servers) that share the
same cache backend.

It's also useful when using `blue/green deployment`_ strategies and more
generally, when you need to abstract out the actual deployment directory (for
example, when warming caches offline).

.. note::

    The ``prefix_seed`` option is used at compile time. This means
    that any change made to this value after container's compilation
    will have no effect.

.. _reference-cache-system:

system
......

**type**: ``string`` **default**: ``cache.adapter.system``

The cache adapter used by the ``cache.system`` service. It supports the same
adapters available for the ``cache.app`` service.

.. _reference-framework-csrf-protection:

csrf_protection
~~~~~~~~~~~~~~~

.. seealso::

    For more information about CSRF protection, see :doc:`/security/csrf`.

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

This option can be used to disable CSRF protection on *all* Symfony features,
but you can also disable CSRF protection only on forms. See :ref:`form.csrf_protection <reference-framework-form-csrf-protection>`.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            csrf_protection: true

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'csrf_protection' => true,
            ],
        ]);

If you're using forms, but want to avoid starting your session (e.g. using
forms in an API-only website), ``csrf_protection`` will need to be set to
``false``.

stateless_token_ids
...................

**type**: ``array`` **default**: ``[]``

The list of CSRF token ids that will use :ref:`stateless CSRF protection <csrf-stateless-tokens>`.

check_header
............

**type**: ``integer`` or ``bool`` **default**: ``false``

Whether to check the CSRF token in an HTTP header in addition to the cookie when
using :ref:`stateless CSRF protection <csrf-stateless-tokens>`. You can also set
this to ``2`` (the value of the ``CHECK_ONLY_HEADER`` constant on the
:class:`Symfony\\Component\\Security\\Csrf\\SameOriginCsrfTokenManager` class)
to check only the header and ignore the cookie.

cookie_name
...........

**type**: ``string`` **default**: ``csrf-token``

The name of the cookie (and HTTP header) to use for the double-submit when using
:ref:`stateless CSRF protection <csrf-stateless-tokens>`.

.. _config-framework-default_locale:

default_locale
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``en``

The default locale is used if no ``_locale`` routing parameter has been
set. It is available with the
:method:`Request::getDefaultLocale <Symfony\\Component\\HttpFoundation\\Request::getDefaultLocale>`
method.

.. seealso::

    You can read more information about the default locale in
    :ref:`translation-default-locale`.

.. _reference-translator-enabled-locales:
.. _reference-enabled-locales:

enabled_locales
~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]`` (empty array = enable all locales)

Symfony applications generate by default the translation files for validation
and security messages in all locales. If your application only uses some
locales, use this option to restrict the files generated by Symfony and improve
performance a bit:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
            enabled_locales: ['en', 'es']

    .. code-block:: php

        // config/packages/translation.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'enabled_locales' => ['en', 'es'],
            ],
        ]);

You can also use environment variables in the list of enabled locales. Empty
values are automatically filtered out, so you can define a fixed number of
slots and leave some of them empty:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
            enabled_locales:
                - '%env(LOCALE_1)%'
                - '%env(LOCALE_2)%'
                - '%env(LOCALE_3)%'

    .. code-block:: php

        // config/packages/translation.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'enabled_locales' => [
                    '%env(LOCALE_1)%',
                    '%env(LOCALE_2)%',
                    '%env(LOCALE_3)%',
                ],
            ],
        ]);

.. versionadded:: 8.1

    Support for environment variables in ``enabled_locales`` was introduced
    in Symfony 8.1.

An added bonus of defining the enabled locales is that they are automatically
added as a requirement of the :ref:`special _locale parameter <routing-locale-parameter>`.
For example, if you define this value as ``['ar', 'he', 'ja', 'zh']``, the
``_locale`` routing parameter will have an ``ar|he|ja|zh`` requirement. If some
user makes requests with a locale not included in this option, they'll see a 404 error.

set_content_language_from_locale
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

If this option is set to ``true``, the response will have a ``Content-Language``
HTTP header set with the ``Request`` locale.

set_locale_from_accept_language
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

If this option is set to ``true``, the ``Request`` locale will automatically be
set to the value of the ``Accept-Language`` HTTP header.

When the ``_locale`` request attribute is passed, the ``Accept-Language`` header
is ignored.

disallow_search_engine_index
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true`` when the debug mode is enabled, ``false`` otherwise.

If ``true``, Symfony adds a ``X-Robots-Tag: noindex`` HTTP tag to all responses
(unless your own app adds that header, in which case it's not modified). This
`X-Robots-Tag HTTP header`_ tells search engines to not index your web site.
This option is a protection measure in case you accidentally publish your site
in debug mode.

.. _config-framework-error_controller:

error_controller
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``error_controller``

This is the controller that is called when an exception is thrown anywhere in
your application. The default controller
(:class:`Symfony\\Component\\HttpKernel\\Controller\\ErrorController`)
renders specific templates under different error conditions (see
:doc:`/controller/error_pages`).

esi
~~~

.. seealso::

    You can read more about Edge Side Includes (ESI) in :ref:`edge-side-includes`.

.. _reference-esi-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the edge side includes support in the framework.

You can also set ``esi`` to ``true`` to enable it:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            esi: true

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'esi' => [
                    'enabled' => true,
                ],
            ],
        ]);

.. _framework_exceptions:

exceptions
~~~~~~~~~~

**type**: ``array``

Defines the :ref:`log level </logging>`, :doc:`log channel </logging/channels_handlers>`
and HTTP status code applied to the exceptions that match the given exception class:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/exceptions.yaml
        framework:
            exceptions:
                Symfony\Component\HttpKernel\Exception\BadRequestHttpException:
                    log_level: 'debug'
                    status_code: 422
                    log_channel: 'custom_channel'

    .. code-block:: php

        // config/packages/exceptions.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

        return App::config([
            'framework' => [
                'exceptions' => [
                    BadRequestHttpException::class => [
                        'log_level' => 'debug',
                        'status_code' => 422,
                        'log_channel' => 'custom_channel',
                    ],
                ],
            ],
        ]);

The order in which you configure exceptions is important because Symfony will
use the configuration of the first exception that matches ``instanceof``:

.. code-block:: yaml

        # config/packages/exceptions.yaml
        framework:
            exceptions:
                Exception:
                    log_level: 'debug'
                    status_code: 404
                # The following configuration will never be used because \RuntimeException extends \Exception
                RuntimeException:
                    log_level: 'debug'
                    status_code: 422

You can map a status code and a set of headers to an exception thanks
to the ``#[WithHttpStatus]`` attribute on the exception class::

    namespace App\Exception;

    use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

    #[WithHttpStatus(422, [
       'Retry-After' => 10,
       'X-Custom-Header' => 'header-value',
    ])]
    class CustomException extends \Exception
    {
    }

It is also possible to map a log level on a custom exception class using
the ``#[WithLogLevel]`` attribute::

    namespace App\Exception;

    use Psr\Log\LogLevel;
    use Symfony\Component\HttpKernel\Attribute\WithLogLevel;

    #[WithLogLevel(LogLevel::WARNING)]
    class CustomException extends \Exception
    {
    }

The attributes can also be added to interfaces directly::

    namespace App\Exception;

    use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

    #[WithHttpStatus(422)]
    interface CustomExceptionInterface
    {
    }

    class CustomException extends \Exception implements CustomExceptionInterface
    {
    }

.. _reference-framework-form:

form
~~~~

.. _reference-form-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable the form services or not in the service container. If
you don't use forms, setting this to ``false`` may increase your application's
performance because fewer services will be loaded into the container.

This option will automatically be set to ``true`` when one of the child
settings is configured.

.. note::

    This will automatically enable the `validation`_.

.. seealso::

    For more details, see :doc:`/forms`.

.. _reference-framework-form-csrf-protection:

csrf_protection
...............

enabled
'''''''

**type**: ``boolean`` **default**: ``true``

Whether to enable CSRF protection for all forms. You can also
:ref:`disable CSRF protection for individual forms <form-csrf-customization>`
or disable it for your entire application (see
:ref:`framework.csrf_protection <reference-framework-csrf-protection>`).

field_name
''''''''''

**type**: ``string`` **default**: ``_token``

This is the field name that you should give to the CSRF token field of your forms.

field_attr
''''''''''

**type**: ``array`` **default**: ``['data-controller' => 'csrf-protection']``

HTML attributes to add to the CSRF token field of your forms.

token_id
''''''''

**type**: ``string`` **default**: ``null``

The CSRF token ID used to validate the CSRF tokens of your forms. This setting
applies only to form types that use :ref:`service autoconfiguration <services-autoconfigure>`,
which typically means your own form types, not those registered by third-party bundles.

fragments
~~~~~~~~~

.. seealso::

    Learn more about fragments in the
    :ref:`HTTP Cache article <http_cache-fragments>`.

.. _reference-fragments-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the fragment listener or not. The fragment listener is
used to render ESI fragments independently of the rest of the page.

This setting is automatically set to ``true`` when one of the child settings
is configured.

hinclude_default_template
.........................

**type**: ``string`` **default**: ``null``

Sets the content shown during the loading of the fragment or when JavaScript
is disabled. This can be either a template name or the content itself.

.. seealso::

    See :ref:`templates-hinclude` for more information about hinclude.

.. _reference-fragments-path:

path
....

**type**: ``string`` **default**: ``/_fragment``

The path prefix for fragments. The fragment listener will only be executed
when the request starts with this path.

handle_all_throwables
~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

When set to ``true``, the Symfony kernel will catch all ``\Throwable`` exceptions
thrown by the application and will turn them into HTTP responses.

html_sanitizer
~~~~~~~~~~~~~~

The ``html_sanitizer`` option is used to configure custom HTML sanitizers.
Read more about the usage in the
:ref:`HTML sanitizer documentation <html-sanitizer-configuration>`.

sanitizers
..........

**type**: ``prototype``

Defines the list of custom HTML sanitizers. Each key is the sanitizer name
(used as a service identifier), and the value is the sanitizer configuration.

default_action
""""""""""""""

**type**: ``string`` **possible values**: ``'drop'``, ``'block'`` or ``'allow'``

Defines how the sanitizer must behave by default for elements not explicitly
configured. ``drop`` removes the element and its children, ``block`` removes
the element but keeps its children, and ``allow`` keeps the element.

allow_safe_elements
"""""""""""""""""""

**type**: ``boolean`` **default**: ``false``

Allows all "safe" elements and attributes. This includes all static elements
except those that can lead to CSS injection or click-jacking.

allow_static_elements
"""""""""""""""""""""

**type**: ``boolean`` **default**: ``false``

Allows all static elements and attributes from the `W3C Sanitizer API standard`_.

allow_elements
""""""""""""""

**type**: ``array``

Configures the elements that the sanitizer should retain from the input.
The key is the element name, the value is either ``*`` to allow the
default set of attributes or a list of allowed attribute names:

.. code-block:: yaml

    # config/packages/html_sanitizer.yaml
    framework:
        html_sanitizer:
            sanitizers:
                app.post_sanitizer:
                    allow_elements:
                        i: '*'
                        a: ['title']
                        span: 'class'

block_elements
""""""""""""""

**type**: ``array``

Configures elements as blocked. Blocked elements are elements the sanitizer
should remove from the input, but retain their children.

drop_elements
"""""""""""""

**type**: ``array``

Configures elements as dropped. Dropped elements are elements the sanitizer
should remove from the input, including their children.

allow_attributes
""""""""""""""""

**type**: ``array``

Configures attributes as allowed globally. Allowed attributes are attributes
the sanitizer should retain from the input. The key is the attribute name,
the value is a list of elements on which this attribute is allowed (or ``*``
to allow on all elements).

drop_attributes
"""""""""""""""

**type**: ``array``

Configures attributes as dropped globally. Dropped attributes are attributes
the sanitizer should remove from the input. The key is the attribute name,
the value is a list of elements on which this attribute is dropped (or ``*``
to drop on all elements).

force_attributes
""""""""""""""""

**type**: ``array``

Forcefully sets the values of certain attributes on certain elements. The key
is the element name, the value is an array of attribute name/value pairs:

.. code-block:: yaml

    # config/packages/html_sanitizer.yaml
    framework:
        html_sanitizer:
            sanitizers:
                app.post_sanitizer:
                    force_attributes:
                        a:
                            rel: noopener noreferrer

force_https_urls
""""""""""""""""

**type**: ``boolean`` **default**: ``false``

Transforms URLs using the HTTP scheme to use the HTTPS scheme instead.

allowed_link_schemes
""""""""""""""""""""

**type**: ``array``

Allows only a given list of schemes to be used in links ``href`` attributes.

allowed_link_hosts
""""""""""""""""""

**type**: ``array`` **default**: ``null``

Allows only a given list of hosts to be used in links ``href`` attributes.
By default (``null``), all hosts are allowed.

allow_relative_links
""""""""""""""""""""

**type**: ``boolean`` **default**: ``false``

Allows relative URLs to be used in links ``href`` attributes.

allowed_media_schemes
"""""""""""""""""""""

**type**: ``array``

Allows only a given list of schemes to be used in media source attributes
(``img``, ``audio``, ``video``, ...).

allowed_media_hosts
"""""""""""""""""""

**type**: ``array`` **default**: ``null``

Allows only a given list of hosts to be used in media source attributes
(``img``, ``audio``, ``video``, ...). By default (``null``), all hosts are
allowed.

allow_relative_medias
"""""""""""""""""""""

**type**: ``boolean`` **default**: ``false``

Allows relative URLs to be used in media source attributes (``img``,
``audio``, ``video``, ...).

with_attribute_sanitizers
"""""""""""""""""""""""""

**type**: ``array``

Registers custom attribute sanitizer services. Each entry is a service
identifier implementing
:class:`Symfony\\Component\\HtmlSanitizer\\Visitor\\AttributeSanitizer\\AttributeSanitizerInterface`.

without_attribute_sanitizers
""""""""""""""""""""""""""""

**type**: ``array``

Unregisters custom attribute sanitizer services that were previously
registered (e.g. by the default configuration).

max_input_length
""""""""""""""""

**type**: ``integer`` **default**: ``0``

The maximum length allowed for the sanitized input. When set to ``0`` (the
default), the length is unlimited.

.. _configuration-framework-http_cache:

http_cache
~~~~~~~~~~

allow_reload
............

**type**: ``boolean`` **default**: ``false``

Specifies whether the client can force a cache reload by including a
Cache-Control "no-cache" directive in the request. Set it to ``true``
for compliance with RFC 2616.

allow_revalidate
................

**type**: ``boolean`` **default**: ``false``

Specifies whether the client can force a cache revalidate by including a
Cache-Control "max-age=0" directive in the request. Set it to ``true``
for compliance with RFC 2616.

debug
.....

**type**: ``boolean`` **default**: ``%kernel.debug%``

If true, exceptions are thrown when things go wrong. Otherwise, the cache will
try to carry on and deliver a meaningful response.

default_ttl
...........

**type**: ``integer`` **default**: ``0``

The number of seconds that a cache entry should be considered fresh when no
explicit freshness information is provided in a response. Explicit
Cache-Control or Expires headers override this value.

enabled
.......

**type**: ``boolean`` **default**: ``false``

private_headers
...............

**type**: ``array`` **default**: ``['Authorization', 'Cookie']``

Set of request headers that trigger "private" cache-control behavior on responses
that don't explicitly state whether the response is public or private via a
Cache-Control directive.

skip_response_headers
.....................

**type**: ``array`` **default**: ``Set-Cookie``

Set of response headers that will never be cached even when the response is cacheable
and public.

stale_if_error
..............

**type**: ``integer`` **default**: ``60``

Specifies the default number of seconds (the granularity is the second) during
which the cache can serve a stale response when an error is encountered.
This setting is overridden by the stale-if-error HTTP
Cache-Control extension (see RFC 5861).

stale_while_revalidate
......................

**type**: ``integer`` **default**: ``2``

Specifies the default number of seconds (the granularity is the second as the
Response TTL precision is a second) during which the cache can immediately return
a stale response while it revalidates it in the background.
This setting is overridden by the stale-while-revalidate HTTP Cache-Control
extension (see RFC 5861).

terminate_on_cache_hit
......................

**type**: ``boolean``

.. deprecated:: 8.1

    The ``terminate_on_cache_hit`` option is deprecated since Symfony 8.1
    because the underlying ``HttpCache`` option it configured was removed in
    Symfony 7.0. Remove it from your configuration.

If ``true``, the :ref:`kernel.terminate <component-http-kernel-kernel-terminate>`
event is dispatched even when the cache is hit.

Unless your application needs to process events on cache hits, it's recommended
to set this to ``false`` to improve performance, because it avoids having to
bootstrap the Symfony framework on a cache hit.

trace_header
............

**type**: ``string`` **default**: ``'X-Symfony-Cache'``

Header name to use for traces.

trace_level
...........

**type**: ``string`` **possible values**: ``'none'``, ``'short'`` or ``'full'``

For 'short', a concise trace of the main request will be added as an HTTP header.
'full' will add traces for all requests (including ESI subrequests).
(default: ``'full'`` if in debug; ``'none'`` otherwise)

.. _reference-http-client:

http_client
~~~~~~~~~~~

When the HttpClient component is installed, an HTTP client is available
as a service named ``http_client`` or using the autowiring alias
:class:`Symfony\\Contracts\\HttpClient\\HttpClientInterface`.

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

.. _reference-http-client-default-options:

This service can be configured using ``framework.http_client.default_options``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            http_client:
                max_host_connections: 10
                default_options:
                    headers: { 'X-Powered-By': 'ACME App' }
                    max_redirects: 7

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'http_client' => [
                    'max_host_connections' => 10,
                    'default_options' => [
                        'headers' => ['X-Powered-By' => 'ACME App'],
                        'max_redirects' => 7,
                    ],
                ],
            ],
        ]);

    .. code-block:: php-standalone

        $client = HttpClient::create([
            'headers' => ['X-Powered-By' => 'ACME App'],
            'max_redirects' => 7,
        ], 10);

.. _reference-http-client-scoped-clients:

Multiple pre-configured HTTP client services can be defined, each with its
service name defined as a key under ``scoped_clients``. Scoped clients inherit
the default options defined for the ``http_client`` service. You can override
these options and can define a few others:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            http_client:
                scoped_clients:
                    my_api.client:
                        auth_bearer: secret_bearer_token
                        # ...

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'http_client' => [
                    'scoped_clients' => [
                        'my_api.client' => [
                            'auth_bearer' => 'secret_bearer_token',
                            // ...
                        ],
                    ],
                ],
            ],
        ]);

    .. code-block:: php-standalone

        $client = HttpClient::createForBaseUri('https://...', [
            'auth_bearer' => 'secret_bearer_token',
            // ...
        ]);

Options defined for scoped clients apply only to URLs that match either their
`base_uri`_ or the `scope`_ option when it is defined. Non-matching URLs always
use default options.

Each scoped client also defines a corresponding named autowiring alias.
If you use for example
``Symfony\Contracts\HttpClient\HttpClientInterface $myApiClient``
as the type and name of an argument, autowiring will inject the ``my_api.client``
service into your autowired classes.

auth_basic
..........

**type**: ``string``

The username and password used to create the ``Authorization`` HTTP header
used in HTTP Basic authentication. The value of this option must follow the
format ``username:password``.

auth_bearer
...........

**type**: ``string``

The token used to create the ``Authorization`` HTTP header used in HTTP Bearer
authentication (also called token authentication).

auth_ntlm
.........

**type**: ``string``

The username and password used to create the ``Authorization`` HTTP header used
in the `Microsoft NTLM authentication protocol`_. The value of this option must
follow the format ``username:password``. This authentication mechanism requires
using the cURL-based transport.

.. _reference-http-client-base-uri:

base_uri
........

**type**: ``string``

URI that is merged into relative URIs, following the rules explained in the
`RFC 3986`_ standard. This is useful when all the requests you make share a
common prefix (e.g. ``https://api.github.com/``) so you can avoid adding it to
every request.

Here are some common examples of how ``base_uri`` merging works in practice:

==========================  ==================  =============================
``base_uri``                Relative URI        Actual Requested URI
==========================  ==================  =============================
http://example.org          /bar                http://example.org/bar
http://example.org/foo      /bar                http://example.org/bar
http://example.org/foo      bar                 http://example.org/bar
http://example.org/foo/     /bar                http://example.org/bar
http://example.org/foo/     bar                 http://example.org/foo/bar
http://example.org          http://symfony.com  http://symfony.com
http://example.org/?bar     bar                 http://example.org/bar
http://example.org/api/v4   /bar                http://example.org/bar
http://example.org/api/v4/  /bar                http://example.org/bar
http://example.org/api/v4   bar                 http://example.org/api/bar
http://example.org/api/v4/  bar                 http://example.org/api/v4/bar
==========================  ==================  =============================

bindto
......

**type**: ``string``

A network interface name, IP address, a host name or a UNIX socket to use as the
outgoing network interface.

buffer
......

**type**: ``boolean`` | ``Closure``

Buffering the response means that you can access its content multiple times
without performing the request again. Buffering is enabled by default when the
content type of the response is ``text/*``, ``application/json`` or ``application/xml``.

If this option is a boolean value, the response is buffered when the value is
``true``. If this option is a closure, the response is buffered when the
returned value is ``true`` (the closure receives as argument an array with the
response headers).

caching
.......

**type**: ``array``

This option configures the behavior of the :ref:`HTTP client caching <http-client_caching>`,
including which types of requests to cache and how many times. The behavior is
defined with the following options:

* :ref:`cache_pool <reference-http-client-caching-cache-pool>`
* :ref:`shared <reference-http-client-caching-shared>`
* :ref:`max_ttl <reference-http-client-caching-max-ttl>`

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        # ...
        http_client:
            # ...
            default_options:
                caching:
                    cache_pool: cache.app
                    shared: true
                    max_ttl: 86400

            scoped_clients:
                my_api.client:
                    # ...
                    caching:
                        cache_pool: my_taggable_pool

.. _reference-http-client-caching-cache-pool:

cache_pool
""""""""""

**type**: ``string``

The service ID of the cache pool used to store the cached responses. The service
must implement the :class:`Symfony\\Contracts\\Cache\\TagAwareCacheInterface`.

By default, it uses an instance of :class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter`
wrapping the ``cache.app`` pool.

.. _reference-http-client-caching-shared:

shared
""""""

**type**: ``boolean`` **default**: ``true``

If ``true``, it uses a `shared cache`_ so cached responses can be reused across
users. Set it to ``false`` to use a `private cache`_.

.. _reference-http-client-caching-max-ttl:

max_ttl
"""""""

**type**: ``integer`` **default**: ``86400``

The maximum time-to-live (in seconds) for cached responses. Server-provided
TTLs are capped at this value to prevent cache items from living forever.

.. deprecated:: 8.1

    Setting ``max_ttl`` to ``null`` is deprecated since Symfony 8.1. Use a
    positive integer instead.

cafile
......

**type**: ``string``

The path of the certificate authority file that contains one or more
certificates used to verify the other servers' certificates.

capath
......

**type**: ``string``

The path to a directory that contains one or more certificate authority files.

ciphers
.......

**type**: ``string``

A list of the names of the ciphers allowed for the TLS connections. They
can be separated by colons, commas or spaces (e.g. ``'RC4-SHA:TLS13-AES-128-GCM-SHA256'``).

crypto_method
.............

**type**: ``integer``

The minimum version of TLS to accept. The value must be one of the
``STREAM_CRYPTO_METHOD_TLSv*_CLIENT`` constants defined by PHP.

extra
.....

**type**: ``array``

Arbitrary additional data to pass to the HTTP client for further use.
This can be particularly useful when :ref:`decorating an existing client <extensibility>`.

.. _http-headers:

headers
.......

**type**: ``array``

An associative array of the HTTP headers added before making the request. This
value must use the format ``['header-name' => 'value0, value1, ...']``.

http_version
............

**type**: ``string`` | ``null`` **default**: ``null``

The HTTP version to use, typically ``'1.1'``  or ``'2.0'``. Leave it to ``null``
to let Symfony select the best version automatically.

local_cert
..........

**type**: ``string``

The path to a file that contains the `PEM formatted`_ certificate used by the
HTTP client. This is often combined with the ``local_pk`` and ``passphrase``
options.

local_pk
........

**type**: ``string``

The path of a file that contains the `PEM formatted`_ private key of the
certificate defined in the ``local_cert`` option.

max_connect_duration
....................

**type**: ``float`` **default**: ``0``

The maximum time, in seconds, allowed to establish the connection (DNS
resolution, TCP connection and TLS handshake). A value lower than or equal
to 0 means it is unlimited.

.. versionadded:: 8.2

    The ``max_connect_duration`` option was introduced in Symfony 8.2.

max_duration
............

**type**: ``float`` **default**: ``0``

The maximum execution time, in seconds, that the request and the response are
allowed to take. A value lower than or equal to 0 means it is unlimited.

.. _reference-http-client-max-host-connections:

max_host_connections
....................

**type**: ``integer`` **default**: ``6``

Defines the maximum number of simultaneously open connections to a single host
(considering a "host" the same as a "host name + port number" pair). This limit
also applies for proxy connections, where the proxy is considered to be the host
for which this limit is applied.

mock_response_factory
.....................

**type**: ``string``

The ID of the service that should generate mock responses. The service must
be either an invokable or an iterable.

max_redirects
.............

**type**: ``integer`` **default**: ``20``

The maximum number of redirects to follow. Use ``0`` to not follow any
redirection.

no_proxy
........

**type**: ``string`` | ``null`` **default**: ``null``

A comma separated list of hosts that do not require a proxy to be reached, even
if one is configured. Use the ``'*'`` wildcard to match all hosts and an empty
string to match none (disables the proxy).

passphrase
..........

**type**: ``string``

The passphrase used to encrypt the certificate stored in the file defined in the
``local_cert`` option.

peer_fingerprint
................

**type**: ``array``

When negotiating a TLS connection, the server sends a certificate
indicating its identity. A public key is extracted from this certificate and if
it does not exactly match any of the public keys provided in this option, the
connection is aborted before sending or receiving any data.

The value of this option is an associative array of ``algorithm => hash``
(e.g. ``['pin-sha256' => '...']``).

proxy
.....

**type**: ``string`` | ``null``

The HTTP proxy to use to make the requests. Leave it to ``null`` to detect the
proxy automatically based on your system configuration.

query
.....

**type**: ``array``

An associative array of the query string values added to the URL before making
the request. This value must use the format ``['parameter-name' => parameter-value, ...]``.

rate_limiter
............

**type**: ``string``

The service ID of the rate limiter used to limit the number of HTTP requests
within a certain period. The service must implement the
:class:`Symfony\\Component\\RateLimiter\\LimiterInterface`.

resolve
.......

**type**: ``array``

A list of hostnames and their IP addresses to pre-populate the DNS cache used by
the HTTP client in order to avoid a DNS lookup for those hosts. This option is
useful to improve security when IPs are checked before the URL is passed to the
client and to make your tests easier.

The value of this option is an associative array of ``domain => IP address``
(e.g. ``['symfony.com' => '46.137.106.254', ...]``).

.. _reference-http-client-retry-failed:

retry_failed
............

**type**: ``array``

This option configures the behavior of the HTTP client when some request fails,
including which types of requests to retry and how many times. The behavior is
defined with the following options:

* :ref:`delay <reference-http-client-retry-delay>`
* :ref:`enabled <reference-http-client-retry-enabled>`
* :ref:`http_codes <reference-http-client-retry-http-codes>`
* :ref:`jitter <reference-http-client-retry-jitter>`
* :ref:`max_delay <reference-http-client-retry-max-delay>`
* :ref:`max_retries <reference-http-client-retry-max-retries>`
* :ref:`multiplier <reference-http-client-retry-multiplier>`
* :ref:`retry_strategy <reference-http-client-retry-retry-strategy>`

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        # ...
        http_client:
            # ...
            default_options:
                retry_failed:
                    # retry_strategy: app.custom_strategy
                    http_codes:
                        0: ['GET', 'HEAD']   # retry network errors if request method is GET or HEAD
                        429: true            # retry all responses with 429 status code
                        500: ['GET', 'HEAD']
                    max_retries: 2
                    delay: 1000
                    multiplier: 3
                    max_delay: 5000
                    jitter: 0.3

            scoped_clients:
                my_api.client:
                    # ...
                    retry_failed:
                        max_retries: 4

.. _reference-http-client-retry-delay:

delay
"""""

**type**: ``integer`` **default**: ``1000``

The initial delay in milliseconds used to compute the waiting time between retries.

.. _reference-http-client-retry-enabled:

enabled
"""""""

**type**: ``boolean`` **default**: ``false``

Whether to enable support for retrying failed HTTP requests.
This setting is automatically set to true when one of the child settings is configured.

.. _reference-http-client-retry-http-codes:

http_codes
""""""""""

**type**: ``array`` **default**: :method:`Symfony\\Component\\HttpClient\\Retry\\GenericRetryStrategy::DEFAULT_RETRY_STATUS_CODES`

The list of HTTP status codes that triggers a retry of the request.

.. _reference-http-client-retry-jitter:

jitter
""""""

**type**: ``float`` **default**: ``0.1`` (must be between 0.0 and 1.0)

This option adds some randomness to the delay. It's useful to avoid sending
multiple requests to the server at the exact same time. The randomness is
calculated as ``delay * jitter``. For example: if delay is ``1000ms`` and jitter
is ``0.2``, the actual delay will be a number between ``800`` and ``1200`` (1000 +/- 20%).

.. _reference-http-client-retry-max-delay:

max_delay
"""""""""

**type**: ``integer`` **default**: ``0``

The maximum initial number of milliseconds to wait between retries.
Use ``0`` to not limit the duration.

.. _reference-http-client-retry-max-retries:

max_retries
"""""""""""

**type**: ``integer`` **default**: ``3``

The maximum number of retries for failing requests. When the maximum is reached,
the client returns the last received response.

.. _reference-http-client-retry-multiplier:

multiplier
""""""""""

**type**: ``float`` **default**: ``2``

This value is multiplied by the delay each time a retry occurs, to distribute
retries in time instead of making all of them sequentially.

.. _reference-http-client-retry-retry-strategy:

retry_strategy
""""""""""""""

**type**: ``string``

The service is used to decide if a request should be retried and to compute the
time to wait between retries. By default, it uses an instance of
:class:`Symfony\\Component\\HttpClient\\Retry\\GenericRetryStrategy` configured
with ``http_codes``, ``delay``, ``max_delay``, ``multiplier`` and ``jitter``
options. This class has to implement
:class:`Symfony\\Component\\HttpClient\\Retry\\RetryStrategyInterface`.

scope
.....

**type**: ``string``

For scoped clients only: the regular expression that the URL must match before
applying all other non-default options. By default, the scope is derived from
`base_uri`_.

timeout
.......

**type**: ``float`` **default**: depends on your PHP config

Time, in seconds, to wait for network activity. If the connection is idle for longer, a
:class:`Symfony\\Component\\HttpClient\\Exception\\TransportException` is thrown.
Its default value is the same as the value of PHP's `default_socket_timeout`_
config option.

verify_host
...........

**type**: ``boolean`` **default**: ``true``

If ``true``, the certificate sent by other servers is verified to ensure that
their common name matches the host included in the URL. This is usually
combined with ``verify_peer`` to also verify the certificate authenticity.

verify_peer
...........

**type**: ``boolean`` **default**: ``true``

If ``true``, the certificate sent by other servers when negotiating a TLS
connection is verified for authenticity. Authenticating the certificate is not
enough to be sure about the server, so you should combine this with the
``verify_host`` option.

 .. _configuration-framework-http_method_override:

http_method_override
~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

This determines whether the ``_method`` request parameter is used as the
intended HTTP method on POST requests. If enabled, the
:method:`Request::enableHttpMethodParameterOverride <Symfony\\Component\\HttpFoundation\\Request::enableHttpMethodParameterOverride>`
method gets called automatically. It becomes the service container parameter
named ``kernel.http_method_override``.

.. seealso::

    :ref:`Changing the Action and HTTP Method <forms-change-action-method>` of
    Symfony forms.

.. warning::

    If you're using the :ref:`HttpCache Reverse Proxy <symfony2-reverse-proxy>`
    with this option, the kernel will ignore the ``_method`` parameter,
    which could lead to errors.

    To fix this, invoke the ``enableHttpMethodParameterOverride()`` method
    before creating the ``Request`` object::

        // public/index.php

        // ...
        $kernel = new CacheKernel($kernel);

        Request::enableHttpMethodParameterOverride(); // <-- add this line
        $request = Request::createFromGlobals();
        // ...

.. _configuration-framework-allowed_http_method_override:

allowed_http_method_override
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``null``

This option controls which HTTP methods can be overridden via the ``_method``
request parameter or the ``X-HTTP-METHOD-OVERRIDE`` header when
:ref:`http_method_override <configuration-framework-http_method_override>` is enabled.

When set to ``null`` (the default), all HTTP methods can be overridden (except
``GET``, ``HEAD``, ``TRACE``, and ``CONNECT`` methods). When set to an empty
array (``[]``), HTTP method overriding is completely disabled. When set to a
specific list of methods, only those methods will be allowed as overrides:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            http_method_override: true
            # Only allow PUT, PATCH, and DELETE to be overridden
            allowed_http_method_override: ['PUT', 'PATCH', 'DELETE']

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework
                ->httpMethodOverride(true)
                ->allowedHttpMethodOverride(['PUT', 'PATCH', 'DELETE'])
            ;
        };

This security feature is useful for hardening your application by explicitly
defining which methods can be tunneled through POST requests. For example, if
your application only needs to override POST requests to PUT and DELETE, you
can restrict the allowed methods accordingly.

You can also configure this programmatically using the
:method:`Request::setAllowedHttpMethodOverride <Symfony\\Component\\HttpFoundation\\Request::setAllowedHttpMethodOverride>`
method::

    // public/index.php

    // ...
    $kernel = new CacheKernel($kernel);

    Request::enableHttpMethodParameterOverride();
    Request::setAllowedHttpMethodOverride(['PUT', 'PATCH', 'DELETE']);
    $request = Request::createFromGlobals();
    // ...

.. _reference-framework-ide:

ide
~~~

**type**: ``string`` **default**: ``%env(default::SYMFONY_IDE)%``

.. deprecated:: 8.2

    The ``ide`` option is deprecated since Symfony 8.2, and already stopped
    working in lower versions. Use the ``SYMFONY_IDE`` environment variable instead.

Symfony turns file paths seen in variable dumps and exception messages into
links that open those files right inside your browser. If you prefer to open
those files in your favorite IDE or text editor, set this option to any of the
following values: ``phpstorm``, ``sublime``, ``textmate``, ``macvim``, ``emacs``,
``atom`` and ``vscode``.

.. note::

    The ``phpstorm`` option is supported natively by PhpStorm on macOS and
    Windows; Linux requires installing `phpstorm-url-handler`_.

If you use another editor, the expected configuration value is a URL template
that contains an ``%f`` placeholder where the file path is expected and ``%l``
placeholder for the line number (percentage signs (``%``) must be escaped by
doubling them to prevent Symfony from interpreting them as container parameters).

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            ide: 'myide://open?url=file://%%f&line=%%l'

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'ide' => 'myide://open?url=file://%%f&line=%%l',
            ],
        ]);

Since every developer uses a different IDE, the recommended way to enable this
feature is to configure it on a system level. First, you can define this option
in the ``SYMFONY_IDE`` environment variable, which Symfony reads automatically
when ``framework.ide`` config is not set.

Another alternative is to set the ``xdebug.file_link_format`` option in your
``php.ini`` configuration file. The format to use is the same as for the
``framework.ide`` option, but without the need to escape the percent signs
(``%``) by doubling them:

.. code-block:: ini

    // example for PhpStorm
    xdebug.file_link_format="phpstorm://open?file=%f&line=%l"

    // example for PhpStorm with Jetbrains Toolbox
    xdebug.file_link_format="jetbrains://phpstorm/navigate/reference?project=example&path=%f:%l"

    // example for Sublime Text
    xdebug.file_link_format="subl://open?url=file://%f&line=%l"

.. note::

    If both ``framework.ide`` and ``xdebug.file_link_format`` are defined,
    Symfony uses the value of the ``xdebug.file_link_format`` option.

.. tip::

    Setting the ``xdebug.file_link_format`` ini option works even if the Xdebug
    extension is not enabled.

.. tip::

    When running your app in a container or in a virtual machine, you can tell
    Symfony to map files from the guest to the host by changing their prefix.
    This map should be specified at the end of the URL template, using ``&`` and
    ``>`` as guest-to-host separators:

    .. code-block:: text

        // /path/to/guest/.../file will be opened
        // as /path/to/host/.../file on the host
        // and /var/www/app/ as /projects/my_project/ also
        'myide://%%f:%%l&/path/to/guest/>/path/to/host/&/var/www/app/>/projects/my_project/&...'

        // example for PhpStorm
        'phpstorm://open?file=%%f&line=%%l&/var/www/app/>/projects/my_project/'

        // example for JetBrains Toolbox, using a relative path
        // by removing the `/path/to/root/` (note trailing `/`), it becomes relative
        'jetbrains://php-storm/navigate/reference?project=my-project&path=%%f:%%l&/path/to/root/>'

json_streamer
~~~~~~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable or not the JSON Streamer support.

.. seealso::

    For more details, see the :doc:`Streaming JSON </serializer/streaming_json>`
    documentation.

.. _reference-lock:

lock
~~~~

**type**: ``string`` | ``array``

The default lock adapter. If not defined, the value is set to ``semaphore`` when
available, or to ``flock`` otherwise. Store DSNs are also allowed.

.. _reference-lock-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true``

Whether to enable the support for lock or not. This setting is
automatically set to ``true`` when one of the child settings is configured.

.. _reference-lock-resources:

resources
.........

**type**: ``array``

A map of lock stores to be created by the framework extension, with
the name as key and DSN or service id as value:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/lock.yaml
        framework:
            lock: '%env(LOCK_DSN)%'

    .. code-block:: php

        // config/packages/lock.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'lock' => [
                    'resources' => [
                        'default' => [env('LOCK_DSN')],
                    ],
                ],
            ],
        ]);

.. seealso::

    For more details, see :doc:`/lock`.

.. _reference-lock-resources-name:

name
""""

**type**: ``prototype``

Name of the lock you want to create.

mailer
~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

.. _mailer-dsn:

dsn
...

**type**: ``string`` **default**: ``null``

The DSN used by the mailer. When several DSN may be used, use
``transports`` option (see below) instead.

envelope
........

recipients
""""""""""

**type**: ``array``

The "envelope recipient" which is used as the value of ``RCPT TO`` during the
`SMTP session`_. This value overrides any other recipient set in the code.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/mailer.yaml
        framework:
            mailer:
                dsn: 'smtp://localhost:25'
                envelope:
                    recipients: ['admin@symfony.com', 'lead@symfony.com']

    .. code-block:: php

        // config/packages/mailer.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'mailer' => [
                    'dsn' => 'smtp://localhost:25',
                    'envelope' => [
                        'recipients' => ['admin@symfony.com', 'lead@symfony.com'],
                    ],
                ],
            ],
        ]);

sender
""""""

**type**: ``string``

The "envelope sender" which is used as the value of ``MAIL FROM`` during the
`SMTP session`_. This value overrides any other sender set in the code.

allowed_recipients
""""""""""""""""""

**type**: ``array``

A list of regular expressions that allow recipients to still receive their
original emails when the ``recipients`` option is defined. These messages
will also be sent to the address(es) defined in ``recipients``.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/mailer.yaml
        framework:
            mailer:
                envelope:
                    recipients: ['youremail@example.com']
                    allowed_recipients:
                        - 'internal@example.com'
                        - 'internal-.*@example.(com|fr)'

    .. code-block:: xml

        <!-- config/packages/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">
            <framework:config>
                <framework:mailer>
                    <framework:envelope>
                        <framework:recipient>youremail@example.com</framework:recipient>
                        <framework:allowed-recipient>internal@example.com</framework:allowed-recipient>
                        <framework:allowed-recipient>internal-.*@example.(com|fr)</framework:allowed-recipient>
                    </framework:envelope>
                </framework:mailer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/mailer.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->mailer()
                ->envelope()
                    ->recipients(['youremail@example.com'])
                    ->allowedRecipients([
                        'internal@example.com',
                        'internal-.*@example.(com|fr)',
                    ])
            ;
        };

.. _mailer-headers:

headers
.......

**type**: ``array``

Headers to add to emails. The key (``name`` attribute in xml format) is the
header name and value the header value.

.. seealso::

    For more information, see :ref:`Configuring Emails Globally <mailer-configure-email-globally>`

message_bus
...........

**type**: ``string`` **default**: ``null`` or default bus if Messenger component is installed

Service identifier of the message bus to use when using the
:doc:`Messenger component </messenger>` (e.g. ``messenger.default_bus``).

transports
..........

**type**: ``array``

A :ref:`list of DSN <multiple-email-transports>` that can be used by the
mailer. A transport name is the key and the dsn is the value.

dkim_signer
...........

Configures a global DKIM signer that automatically signs all outgoing messages.

key
"""

**type**: ``string`` **default**: ``''``

The DKIM signing key. This can be the key content or a path to the key file
in PEM format (prefixed with ``file://``).

domain
""""""

**type**: ``string`` **default**: ``''``

The signing domain name.

select
""""""

**type**: ``string`` **default**: ``''``

The DKIM selector (a string used to point to a specific DKIM public key record
in your DNS).

passphrase
""""""""""

**type**: ``string`` **default**: ``''``

The passphrase for the private key.

options
"""""""

**type**: ``array``

Additional DKIM signing options (e.g. algorithm, headers to ignore).

.. seealso::

    For more information, see :ref:`Signing and Encrypting Messages <signing-and-encrypting-messages>`.

smime_signer
............

Configures a global S/MIME signer that automatically signs all outgoing messages.

key
"""

**type**: ``string`` **default**: ``''``

The path to the S/MIME private key in PEM format.

certificate
"""""""""""

**type**: ``string`` **default**: ``''``

The path to the S/MIME certificate in PEM format.

passphrase
""""""""""

**type**: ``string`` **default**: ``null``

The passphrase for the private key.

extra_certificates
""""""""""""""""""

**type**: ``string`` **default**: ``null``

The path to a file containing extra certificates (e.g. intermediate
certificates) to include in the signature.

sign_options
""""""""""""

**type**: ``integer`` **default**: ``null``

Bitwise operator options for :phpfunction:`openssl_pkcs7_sign`.

smime_encrypter
...............

Configures a global S/MIME encrypter that automatically encrypts all outgoing
messages.

repository
""""""""""

**type**: ``string`` **default**: ``''``

The service ID of a class implementing
:class:`Symfony\\Component\\Mailer\\EventListener\\SmimeCertificateRepositoryInterface`.
This service is used to find the certificate path for each email recipient.

cipher
""""""

**type**: ``integer`` **default**: ``null``

The `OpenSSL cipher`_ algorithm constant used for encryption.

messenger
~~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable or not Messenger.

.. seealso::

    For more details, see the :doc:`Messenger component </messenger>`
    documentation.

default_bus
...........

**type**: ``string`` **default**: ``null``

The name of the default bus. When you define more than one bus, you must set
this option.

failure_transport
.................

**type**: ``string`` **default**: ``null``

The transport name to send failed messages to (after all retries have failed).

routing
.......

**type**: ``array``

Defines the routing of messages to transports. The keys are the fully qualified
class names (or a parent class/interface) and the values are the transport names
(or a list of transport names) the messages should be routed to:

.. code-block:: yaml

    framework:
        messenger:
            routing:
                'App\Message\SmsNotification': async
                'App\Message\OtherMessage': [async, audit]

senders
"""""""

**type**: ``array``

A list of transport names to route the message to.

.. deprecated:: 8.1

    The ``senders`` nesting level in the routing configuration is deprecated
    since Symfony 8.1. Use a string or a list of strings directly instead.

serializer
..........

default_serializer
""""""""""""""""""

**type**: ``string`` **default**: ``messenger.transport.native_php_serializer``

Service id to use as the default serializer for the transports.

symfony_serializer
""""""""""""""""""

format
^^^^^^

**type**: ``string`` **default**: ``json``

Serialization format for the ``messenger.transport.symfony_serializer``
service (which is not the serializer used by default).

context
^^^^^^^

**type**: ``array`` **default**: ``[]``

Context array for the ``messenger.transport.symfony_serializer`` service
(which is not the serializer used by default).

transports
..........

**type**: ``array``

Defines the transports used to send messages. Each transport has a name (used
in routing), a DSN and optional configuration:

.. code-block:: yaml

    # config/packages/messenger.yaml
    framework:
        messenger:
            transports:
                async:
                    dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                    retry_strategy:
                        max_retries: 3
                        delay: 1000
                        multiplier: 2

dsn
"""

**type**: ``string``

The DSN of the transport (e.g. ``doctrine://default``, ``amqp://guest:guest@localhost/%2f/messages``).

serializer
""""""""""

**type**: ``string`` **default**: ``null``

Service id of a custom serializer to use for this transport.

options
"""""""

**type**: ``array`` **default**: ``[]``

Transport-specific options.

failure_transport
"""""""""""""""""

**type**: ``string`` **default**: ``null``

Transport name to send failed messages to (after all retries have failed).

rate_limiter
""""""""""""

**type**: ``string`` **default**: ``null``

Rate limiter name to use when processing messages.

retry_strategy
""""""""""""""

service
^^^^^^^

**type**: ``string`` **default**: ``null``

Service id to override the retry strategy entirely. When set, the other
``retry_strategy`` options are not allowed.

max_retries
^^^^^^^^^^^

**type**: ``integer`` **default**: ``3``

Maximum number of retries before the message is sent to the failure transport.
A value of ``0`` means no retries.

delay
^^^^^

**type**: ``integer`` **default**: ``1000``

Time in milliseconds to delay (or the initial value when ``multiplier`` is used).

multiplier
^^^^^^^^^^

**type**: ``float`` **default**: ``2``

If greater than 1, the delay will grow exponentially for each retry:
``delay = delay * (multiplier ^ retries)``.

max_delay
^^^^^^^^^

**type**: ``integer`` **default**: ``0``

Maximum time in milliseconds that a retry should ever be delayed. A value of
``0`` means infinite.

jitter
^^^^^^

**type**: ``float`` **default**: ``0.1``

Randomness to apply to the delay (between ``0`` and ``1``).

stop_worker_on_signals
......................

**type**: ``array`` **default**: ``[]``

A list of signals that should stop the worker. Defaults to ``SIGTERM`` and
``SIGINT`` when empty:

.. code-block:: yaml

    # config/packages/messenger.yaml
    framework:
        messenger:
            stop_worker_on_signals: ['SIGTERM', 'SIGINT']

buses
.....

**type**: ``array`` **default**: ``{ messenger.bus.default: { default_middleware: { enabled: true, allow_no_handlers: false, allow_no_senders: true }, middleware: [] } }``

Defines the message buses used in the application:

.. code-block:: yaml

    # config/packages/messenger.yaml
    framework:
        messenger:
            default_bus: command.bus
            buses:
                command.bus: ~
                event.bus:
                    default_middleware:
                        allow_no_handlers: true

default_middleware
""""""""""""""""""

**type**: ``boolean`` | ``array`` **default**: ``true``

Whether to add the default middleware to the bus. When set to ``true`` or an
array, the following options are available:

enabled
^^^^^^^

**type**: ``boolean`` **default**: ``true``

Whether default middleware is enabled.

allow_no_handlers
^^^^^^^^^^^^^^^^^

**type**: ``boolean`` **default**: ``false``

Whether to allow dispatching a message without any handler.

allow_no_senders
^^^^^^^^^^^^^^^^

**type**: ``boolean`` **default**: ``true``

Whether to allow dispatching a message without any sender.

middleware
""""""""""

**type**: ``array`` **default**: ``[]``

A list of middleware service ids to add to the bus, ordered by priority:

.. code-block:: yaml

    framework:
        messenger:
            buses:
                command.bus:
                    middleware:
                        - 'App\Middleware\MyMiddleware'
                        - doctrine_transaction

php_errors
~~~~~~~~~~

log
...

**type**: ``boolean``, ``int`` or ``array<int, string>`` **default**: ``true``

Use the application logger instead of the PHP logger for logging PHP errors.
When an integer value is used, it defines a bitmask of PHP errors that will
be logged. Those integer values must be the same used in the
`error_reporting PHP option`_. The default log levels will be used for each
PHP error.
When a boolean value is used, ``true`` enables logging for all PHP errors
while ``false`` disables logging entirely.

This option also accepts a map of PHP errors to log levels:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            php_errors:
                log:
                    !php/const \E_DEPRECATED: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_USER_DEPRECATED: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_NOTICE: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_USER_NOTICE: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_STRICT: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_WARNING: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_USER_WARNING: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_COMPILE_WARNING: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_CORE_WARNING: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_USER_ERROR: !php/const Psr\Log\LogLevel::CRITICAL
                    !php/const \E_RECOVERABLE_ERROR: !php/const Psr\Log\LogLevel::CRITICAL
                    !php/const \E_COMPILE_ERROR: !php/const Psr\Log\LogLevel::CRITICAL
                    !php/const \E_PARSE: !php/const Psr\Log\LogLevel::CRITICAL
                    !php/const \E_ERROR: !php/const Psr\Log\LogLevel::CRITICAL
                    !php/const \E_CORE_ERROR: !php/const Psr\Log\LogLevel::CRITICAL

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Psr\Log\LogLevel;

        return App::config([
            'framework' => [
                'php_errors' => [
                    'log' => [
                        \E_DEPRECATED => LogLevel::ERROR,
                        \E_USER_DEPRECATED => LogLevel::ERROR,
                        \E_NOTICE => LogLevel::ERROR,
                        \E_USER_NOTICE => LogLevel::ERROR,
                        \E_STRICT => LogLevel::ERROR,
                        \E_WARNING => LogLevel::ERROR,
                        \E_USER_WARNING => LogLevel::ERROR,
                        \E_COMPILE_WARNING => LogLevel::ERROR,
                        \E_CORE_WARNING => LogLevel::ERROR,
                        \E_USER_ERROR => LogLevel::CRITICAL,
                        \E_COMPILE_ERROR => LogLevel::CRITICAL,
                        \E_PARSE => LogLevel::CRITICAL,
                        \E_ERROR => LogLevel::CRITICAL,
                        \E_CORE_ERROR => LogLevel::CRITICAL,
                    ],
                ],
            ],
        ]);

throw
.....

**type**: ``boolean`` **default**: ``%kernel.debug%``

Throw PHP errors as ``\ErrorException`` instances. The parameter
``debug.error_handler.throw_at`` controls the threshold.

profiler
~~~~~~~~

collect
.......

**type**: ``boolean`` **default**: ``true``

This option configures the way the profiler behaves when it is enabled. If set
to ``true``, the profiler collects data for all requests. If you want to only
collect information on-demand, you can set the ``collect`` flag to ``false`` and
activate the data collectors manually::

    $profiler->enable();

collect_parameter
.................

**type**: ``string`` **default**: ``null``

This specifies the name of a query parameter, a body parameter or a request attribute
used to enable or disable collection of data by the profiler for each request.
Combine it with the ``collect`` option to enable/disable the profiler on demand:

* If the ``collect`` option is set to ``true`` but this parameter exists in a
  request and has any value other than ``true``, ``yes``, ``on`` or ``1``, the
  request data will not be collected;
* If the ``collect`` option is set to ``false``, but this parameter exists in a
  request and has value of ``true``, ``yes``, ``on`` or ``1``, the request data
  will be collected.

.. _profiler-dsn:

dsn
...

**type**: ``string`` **default**: ``file:%kernel.cache_dir%/profiler``

The DSN where to store the profiling information.

.. _reference-profiler-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

The profiler can be enabled by setting this option to ``true``. When you
install it using Symfony Flex, the profiler is enabled in the ``dev``
and ``test`` environments.

.. note::

    The profiler works independently from the Web Developer Toolbar, see
    the :doc:`WebProfilerBundle configuration </reference/configuration/web_profiler>`
    on how to disable/enable the toolbar.

only_exceptions
...............

**type**: ``boolean`` **default**: ``false``

When this is set to ``true``, the profiler will only be enabled when an
exception is thrown during the handling of the request.

.. _only_master_requests:

only_main_requests
..................

**type**: ``boolean`` **default**: ``false``

When this is set to ``true``, the profiler will only be enabled on the main
requests (and not on the subrequests).

property_access
~~~~~~~~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

magic_call
..........

**type**: ``boolean`` **default**: ``false``

When enabled, the ``property_accessor`` service uses PHP's
:ref:`magic __call() method <components-property-access-magic-call>` when
its ``getValue()`` method is called.

magic_get
.........

**type**: ``boolean`` **default**: ``true``

When enabled, the ``property_accessor`` service uses PHP's
:ref:`magic __get() method <components-property-access-magic-get>` when
its ``getValue()`` method is called.

magic_set
.........

**type**: ``boolean`` **default**: ``true``

When enabled, the ``property_accessor`` service uses PHP's
:ref:`magic __set() method <components-property-access-writing-to-objects>` when
its ``setValue()`` method is called.

throw_exception_on_invalid_index
................................

**type**: ``boolean`` **default**: ``false``

When enabled, the ``property_accessor`` service throws an exception when you
try to access an invalid index of an array.

throw_exception_on_invalid_property_path
........................................

**type**: ``boolean`` **default**: ``true``

When enabled, the ``property_accessor`` service throws an exception when you
try to access an invalid property path of an object.

property_info
~~~~~~~~~~~~~

.. _reference-property-info-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

with_constructor_extractor
..........................

**type**: ``boolean`` **default**: ``true``

Configures the ``property_info`` service to extract property information from the constructor arguments
using the :ref:`ConstructorExtractor <components-property-information-constructor-extractor>`.

.. versionadded:: 8.0

    The default value of the ``with_constructor_extractor`` option was changed
    to ``true`` in Symfony 8.0.

rate_limiter
~~~~~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

.. _reference-rate-limiter-name:

limiters
........

**type**: ``prototype``

A list of rate limiters to create, keyed by the limiter name.

cache_pool
""""""""""

**type**: ``string`` **default**: ``cache.rate_limiter``

The cache pool to use for storing the current limiter state.

anchor_at
"""""""""

**type**: ``string`` **default**: ``null``

A reference datetime (any string accepted by ``DateTimeImmutable``) used by the
``fixed_window`` policy to align windows on a calendar. When set, windows reset
at ``anchor_at + (n × interval)`` instead of starting on the first hit. This is
useful for billing cycles, fiscal years or fixed-day weekly resets.

.. versionadded:: 8.1

    The ``anchor_at`` option was introduced in Symfony 8.1.

interval
""""""""

**type**: ``string``

Configures the fixed interval if ``policy`` is set to ``fixed_window`` or
``sliding_window``. The value must be a number followed by ``second``, ``minute``,
``hour``, ``day``, ``week`` or ``month`` (or their plural equivalent).

limit
"""""

**type**: ``integer``

The maximum allowed hits in a fixed interval or burst. Required when the
``policy`` is not ``compound`` or ``no_limit``.

limiters
""""""""

**type**: ``array``

The limiter names to use when using the ``compound`` policy.

lock_factory
""""""""""""

**type**: ``string`` **default**: ``auto``

The service ID of the lock factory used by this limiter (or ``null`` to disable
locking).

policy
""""""

**type**: ``string`` **required**

The name of the rate limiting algorithm to use. Allowed values are ``fixed_window``,
``token_bucket``, ``sliding_window``, ``compound`` and ``no_limit``.
See :ref:`Rate Limiter Policies <rate-limiter-policies>`)
for more information.

rate
""""

**type**: ``array``

Configures the fill rate if ``policy`` is set to ``token_bucket``.

* ``interval`` (**type**: ``string``) the rate interval. The value must be a
  number followed by ``second``, ``minute``, ``hour``, ``day``, ``week`` or
  ``month`` (or their plural equivalent).
* ``amount`` (**type**: ``integer`` **default**: ``1``) the amount of tokens to
  add each interval.

storage_service
"""""""""""""""

**type**: ``string`` **default**: ``null``

The service ID of a custom storage implementation. This takes precedence over
any configured ``cache_pool``.

remote-event
~~~~~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable or not the RemoteEvent support.

.. seealso::

    For more details, see the :doc:`Webhook </webhook>`
    documentation.
request
~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``false``

formats
.......

**type**: ``array`` **default**: ``[]``

This setting is used to associate additional request formats (e.g. ``html``)
to one or more mime types (e.g. ``text/html``), which will allow you to use the
format & mime types to call
:method:`Request::getFormat($mimeType) <Symfony\\Component\\HttpFoundation\\Request::getFormat>` or
:method:`Request::getMimeType($format) <Symfony\\Component\\HttpFoundation\\Request::getMimeType>`.

In practice, this is important because Symfony uses it to automatically set the
``Content-Type`` header on the ``Response`` (if you don't explicitly set one).
If you pass an array of mime types, the first will be used for the header.

To configure a ``jsonp`` format:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            request:
                formats:
                    jsonp: 'application/javascript'

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'request' => [
                    'formats' => [
                        'jsonp' => 'application/javascript',
                    ],
                ],
            ],
        ]);

router
~~~~~~

.. _config-framework-router-default-uri:

default_uri
...........

**type**: ``string``

The default URI used to generate URLs in a non-HTTP context (see
:ref:`Generating URLs in Commands <router-generate-urls-commands>`).

enabled
.......

**type**: ``boolean`` **default**: ``false``

http_port
.........

**type**: ``integer`` **default**: ``80``

The port for normal http requests (this is used when matching the scheme).

https_port
..........

**type**: ``integer`` **default**: ``443``

The port for https requests (this is used when matching the scheme).

resource
........

**type**: ``string`` **required**

The path to the main routing resource (e.g. a YAML file) that contains the
routes and imports the router should load.

strict_requirements
...................

**type**: ``mixed`` **default**: ``true``

Determines the routing generator behavior. When generating a route that
has specific :ref:`parameter requirements <routing-requirements>`, the generator
can behave differently in case the used parameters do not meet these requirements.

The value can be one of:

``true``
    Throw an exception when the requirements are not met;
``false``
    Disable exceptions when the requirements are not met and return ``''``
    instead;
``null``
    Disable checking the requirements (thus, match the route even when the
    requirements don't match).

``true`` is recommended in the development environment, while ``false``
or ``null`` might be preferred in production.

.. _reference-router-type:

type
....

**type**: ``string``

The type of the resource to hint the loaders about the format. This isn't
needed when you use the default routers with the expected file extensions
(``.xml``, ``.yaml``, ``.php``).

utf8
....

**type**: ``boolean`` **default**: ``true``

When this option is set to ``true``, the regular expressions used in the
:ref:`requirements of route parameters <routing-requirements>` will be run
using the `utf-8 modifier`_. This will for example match any UTF-8 character
when using ``.``, instead of matching only a single byte.

If the charset of your application is UTF-8 (as defined in the
:ref:`getCharset() method <configuration-kernel-charset>` of your kernel) it's
recommended setting it to ``true``. This will make non-UTF8 URLs generate 404
errors.

.. _configuration-framework-secret:

secret
~~~~~~

**type**: ``string`` **required**

This is a string that should be unique to your application and it's commonly
used to add more entropy to security related operations. Its value should
be a series of characters, numbers and symbols chosen randomly and the
recommended length is around 32 characters.

In practice, Symfony uses this value for encrypting the cookies used
in the :doc:`remember me functionality </security/remember_me>` and for
creating signed URIs when using :ref:`ESI (Edge Side Includes) <edge-side-includes>`.
That's why you should treat this value as if it were a sensitive credential and
**never make it public**.

This option becomes the service container parameter named ``kernel.secret``,
which you can use whenever the application needs an immutable random string
to add more entropy.

If ``APP_SECRET`` is not set but the ``SYMFONY_DECRYPTION_SECRET`` environment
variable is available (used by the :doc:`secrets vault </configuration/secrets>`),
the ``kernel.secret`` parameter is automatically derived from it. This means
applications using the secrets vault don't need a separate ``APP_SECRET`` value.

As with any other security-related parameter, it is a good practice to change
this value from time to time. However, keep in mind that changing this value
will invalidate all signed URIs and Remember Me cookies. That's why, after
changing this value, you should regenerate the application cache and log
out all the application users.

secrets
~~~~~~~

decryption_env_var
..................

**type**: ``string`` **default**: ``base64:default::SYMFONY_DECRYPTION_SECRET``

The env var name that contains the vault decryption secret. By default, this
value will be decoded from base64.

enabled
.......

**type**: ``boolean`` **default**: ``true``

Whether to enable or not secrets managements.

local_dotenv_file
.................

**type**: ``string`` **default**: ``%kernel.project_dir%/.env.%kernel.environment%.local``

The path to the local ``.env`` file. This file must contain the vault
decryption key, given by the ``decryption_env_var`` option.

vault_directory
...............

**type**: ``string`` **default**: ``%kernel.project_dir%/config/secrets/%kernel.runtime_environment%``

The directory to store the secret vault. By default, the path includes the value
of the :ref:`kernel.runtime_environment <configuration-kernel-runtime-environment>`
parameter.

semaphore
~~~~~~~~~

**type**: ``string`` | ``array``

The default semaphore adapter. Store DSNs are also allowed. Use ``lock://``
to use the :doc:`Lock component </lock>` as the semaphore backend (e.g.
``lock://`` for the default lock, or ``lock://my_locks`` for a named lock
resource).

.. _reference-semaphore-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true``

Whether to enable the support for semaphore or not. This setting is
automatically set to ``true`` when one of the child settings is configured.

.. _reference-semaphore-resources:

resources
.........

**type**: ``array``

A map of semaphore stores to be created by the framework extension, with
the name as key and DSN or service id as value:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/semaphore.yaml
        framework:
            semaphore: '%env(SEMAPHORE_DSN)%'

    .. code-block:: php

        // config/packages/semaphore.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'semaphore' => env('SEMAPHORE_DSN'),
            ],
        ]);

.. _reference-semaphore-resources-name:

name
""""

**type**: ``prototype``

Name of the semaphore you want to create.


scheduler
~~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable or not the Scheduler support.

.. seealso::

    For more details, see the :doc:`Scheduler </scheduler>`
    documentation.

.. _configuration-framework-serializer:

serializer
~~~~~~~~~~

.. _reference-serializer-circular_reference_handler:

circular_reference_handler
..........................

**type** ``string``

The service id that is used as the circular reference handler of the default
serializer. The service has to implement the magic ``__invoke($object)``
method.

.. seealso::

    For more information, see
    :ref:`component-serializer-handling-circular-references`.

default_context
...............

**type**: ``array`` **default**: ``[]``

A map with default context options that will be used with each ``serialize`` and ``deserialize``
call. This can be used for example to set the json encoding behavior by setting ``json_encode_options``
to a `json_encode flags bitmask`_.

You can inspect the :ref:`serializer context builders <serializer-using-context-builders>`
to discover the available settings.

.. _reference-serializer-enable_annotations:

enable_attributes
.................

**type**: ``boolean`` **default**: ``true``

Enables support for `PHP attributes`_ in the serializer component.

.. seealso::

    See :ref:`the reference <reference-attributes-serializer>` for a list of supported attributes.

.. _reference-serializer-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable the ``serializer`` service or not in the service container.

.. _reference-serializer-mapping:

mapping
.......

.. _reference-serializer-mapping-paths:

paths
"""""

**type**: ``array`` **default**: ``[]``

This option allows you to define an array of paths with files or directories where
the component will look for additional serialization files.

max_depth_handler
.................

**type**: ``string``

The service ID used as the max depth handler of the default serializer.

.. _reference-serializer-name_converter:

name_converter
..............

**type**: ``string``

The name converter to use.
The :class:`Symfony\\Component\\Serializer\\NameConverter\\CamelCaseToSnakeCaseNameConverter`
name converter can enabled by using the ``serializer.name_converter.camel_case_to_snake_case``
value.

.. seealso::

    For more information, see :ref:`serializer-name-conversion`.

named_serializers
.................

**type**: ``array``

A list of named serializers to create, keyed by the serializer name. The
``default`` name is reserved and cannot be used.

Each named serializer can define the following options:

* ``name_converter`` (**type**: ``string``) the service ID of a custom name
  converter.
* ``default_context`` (**type**: ``array`` **default**: ``[]``) a map with
  default context options.
* ``include_built_in_normalizers`` (**type**: ``boolean`` **default**: ``true``)
  whether to include the built-in normalizers.
* ``include_built_in_encoders`` (**type**: ``boolean`` **default**: ``true``)
  whether to include the built-in encoders.

.. _config-framework-session:

session
~~~~~~~

cache_limiter
.............

**type**: ``string`` **default**: ``0``

If set to ``0``, Symfony won't set any particular header related to the cache
and it will rely on ``php.ini``'s `session.cache_limiter`_ directive.

Unlike the other session options, ``cache_limiter`` is set as a regular
:ref:`container parameter <configuration-parameters>`:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            session.storage.options:
                cache_limiter: 0

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'parameters' => [
                'session.storage.options' => [
                    'cache_limiter' => 0,
                ],
            ],
        ]);

Be aware that if you configure it, you'll have to set other session-related options
as parameters as well.

cookie_domain
.............

**type**: ``string``

This determines the domain to set in the session cookie.

If not set, ``php.ini``'s `session.cookie_domain`_ directive will be relied on.

cookie_httponly
...............

**type**: ``boolean`` **default**: ``true``

This determines whether cookies should only be accessible through the HTTP
protocol. This means that the cookie won't be accessible by scripting
languages, such as JavaScript. This setting can effectively help to reduce
identity theft through :ref:`XSS attacks <xss-attacks>`.

cookie_lifetime
...............

**type**: ``integer``

This determines the lifetime of the session - in seconds.
Setting this value to ``0`` means the cookie is valid for
the length of the browser session.

If not set, ``php.ini``'s `session.cookie_lifetime`_ directive will be relied on.

cookie_path
...........

**type**: ``string``

This determines the path to set in the session cookie.

If not set, ``php.ini``'s `session.cookie_path`_ directive will be relied on.

cookie_samesite
...............

**type**: ``string`` or ``null`` **default**: ``'lax'``

It controls the way cookies are sent when the HTTP request did not originate
from the same domain that is associated with the cookies. Setting this option is
recommended to mitigate `CSRF security attacks`_.

By default, browsers send all cookies related to the domain of the HTTP request.
This may be a problem for example when you visit a forum and some malicious
comment includes a link like ``https://some-bank.com/?send_money_to=attacker&amount=1000``.
If you were previously logged into your bank website, the browser will send all
those cookies when making that HTTP request.

The possible values for this option are:

* ``null``, use ``php.ini``'s `session.cookie_samesite`_ directive.
* ``'none'`` (or the ``Symfony\Component\HttpFoundation\Cookie::SAMESITE_NONE`` constant), use it to allow
  sending of cookies when the HTTP request originated from a different domain
  (previously this was the default behavior of null, but in newer browsers ``'lax'``
  would be applied when the header has not been set)
* ``'strict'`` (or the ``Cookie::SAMESITE_STRICT`` constant), use it to never
  send any cookie when the HTTP request did not originate from the same domain.
* ``'lax'`` (or the ``Cookie::SAMESITE_LAX`` constant), use it to allow sending
  cookies when the request originated from a different domain, but only when the
  user consciously made the request (by clicking a link or submitting a form
  with the ``GET`` method).

cookie_secure
.............

**type**: ``boolean`` or ``'auto'``

This determines whether cookies should only be sent over secure connections. In
addition to ``true`` and ``false``, there's a special ``'auto'`` value that
means ``true`` for HTTPS requests and ``false`` for HTTP requests.

If not set, ``php.ini``'s `session.cookie_secure`_ directive will be relied on.

.. _reference-session-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true``

Whether to enable the session support in the framework.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                enabled: true

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'session' => [
                    'enabled' => true,
                ],
            ],
        ]);

gc_divisor
..........

**type**: ``integer``

See `gc_probability`_.

If not set, ``php.ini``'s `session.gc_divisor`_ directive will be relied on.

gc_maxlifetime
..............

**type**: ``integer``

This determines the number of seconds after which data will be seen as "garbage"
and potentially cleaned up. Garbage collection may occur during session
start and depends on `gc_divisor`_ and `gc_probability`_.

If not set, ``php.ini``'s `session.gc_maxlifetime`_ directive will be relied on.

gc_probability
..............

**type**: ``integer``

This defines the probability that the garbage collector (GC) process is
started on every session initialization. The probability is calculated by
using ``gc_probability`` / ``gc_divisor``, e.g. 1/100 means there is a 1%
chance that the GC process will start on each request.

If not set, Symfony will use the value of the `session.gc_probability`_ directive
in the ``php.ini`` configuration file.

.. _config-framework-session-handler-id:

handler_id
..........

**type**: ``string`` | ``null`` **default**: ``null``

If ``framework.session.save_path`` is not set, the default value of this option
is ``null``, which means to use the session handler configured in php.ini. If the
``framework.session.save_path`` option is set, then Symfony stores sessions using
the native file session handler.

It is possible to :ref:`store sessions in a database <session-database>`,
and also to configure the session handler with a DSN:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                # a few possible examples
                handler_id: 'redis://localhost'
                handler_id: '%env(REDIS_URL)%'
                handler_id: '%env(DATABASE_URL)%'
                handler_id: 'file://%kernel.project_dir%/var/sessions'

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'session' => [
                    // a few possible examples
                    'handler_id' => 'redis://localhost',
                    'handler_id' => env('REDIS_URL'),
                    'handler_id' => env('DATABASE_URL'),
                    'handler_id' => 'file://%kernel.project_dir%/var/sessions',
                ],
            ],
        ]);

.. note::

    Supported DSN protocols are the following:

    * ``file``
    * ``redis``
    * ``rediss`` (Redis over TLS)
    * ``memcached`` (requires :doc:`symfony/cache </cache>`)
    * ``pdo_oci`` (requires :doc:`doctrine/dbal </doctrine/dbal>`)
    * ``mssql``
    * ``mysql``
    * ``mysql2``
    * ``pgsql``
    * ``postgres``
    * ``postgresql``
    * ``sqlsrv``
    * ``sqlite``
    * ``sqlite3``

.. _reference-session-metadata-update-threshold:

metadata_update_threshold
.........................

**type**: ``integer`` **default**: ``0``

This is how many seconds to wait between updating/writing the session metadata.
This can be useful if, for some reason, you want to limit the frequency at which
the session persists, instead of doing that on every request.

.. _name:

name
....

**type**: ``string``

This specifies the name of the session cookie.

If not set, ``php.ini``'s `session.name`_ directive will be relied on.

save_path
.........

**type**: ``string`` | ``null`` **default**: ``%kernel.cache_dir%/sessions``

This option defines the directory where session files are stored when using
the native file session handler (i.e. when ``handler_id`` is ``null`` and no
DSN is configured). It overrides PHP's `session.save_path`_ directive.

By default, Symfony stores session files in ``%kernel.cache_dir%/sessions/``.
If you set this option to ``null``, PHP's default path from ``php.ini`` will
be used instead:

.. note::

    When ``handler_id`` is set to a DSN (e.g. ``redis://localhost`` or
    ``file://%kernel.project_dir%/var/sessions``), this option is ignored
    because the storage location is already part of the DSN.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                save_path: ~

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'session' => [
                    'save_path' => null,
                ],
            ],
        ]);

.. _storage_id:

storage_factory_id
..................

**type**: ``string`` **default**: ``session.storage.factory.native``

The service ID used for creating the ``SessionStorageInterface`` that stores
the session. This service is available in the Symfony application via the
``session.storage.factory`` service alias. The class has to implement
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\SessionStorageFactoryInterface`.
To see a list of all available storages, run:

.. code-block:: terminal

    $ php bin/console debug:container session.storage.factory.

use_cookies
...........

**type**: ``boolean``

This specifies if the session ID is stored on the client side using cookies or
not.

If not set, ``php.ini``'s `session.use_cookies`_ directive will be relied on.

ssi
~~~

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable or not SSI support in your application.

.. _reference-framework-test:

test
~~~~

**type**: ``boolean``

If this configuration setting is present (and not ``false``), then the services
related to testing your application (e.g. ``test.client``) are loaded. This
setting should be present in your ``test`` environment (usually via
``config/packages/test/framework.yaml``).

.. seealso::

    For more information, see :doc:`/testing`.

translator
~~~~~~~~~~

cache_dir
.........

**type**: ``string`` | ``null`` **default**: ``%kernel.cache_dir%/translations``

Defines the directory where the translation cache is stored. Use ``null`` to
disable this cache.

.. _reference-translator-default_path:

default_path
............

**type**: ``string`` **default**: ``%kernel.project_dir%/translations``

This option allows you to define the path where the application translations files
are stored.

.. _reference-translator-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether or not to enable the ``translator`` service in the service container.

.. _fallback:

fallbacks
.........

**type**: ``string|array`` **default**: value of `default_locale`_

This option is used when the translation key for the current locale wasn't
found.

.. seealso::

    For more details, see :doc:`/translation`.

.. _reference-framework-translator-formatter:

formatter
.........

**type**: ``string`` **default**: ``translator.formatter.default``

The ID of the service used to format translation messages. The service class
must implement the :class:`Symfony\\Component\\Translation\\Formatter\\MessageFormatterInterface`.

globals
.......

**type**: ``array``

Global parameters available in all translations. Each global parameter can be
either a simple value or an array with the following options:

* ``value`` (**type**: ``mixed``) the direct value of the parameter.
* ``message`` (**type**: ``string``) a translation message key.
* ``parameters`` (**type**: ``array``) the translation parameters.
* ``domain`` (**type**: ``string``) the translation domain.

Each global parameter must define either ``value`` or ``message``, but not both.

.. _reference-framework-translator-logging:

logging
.......

**default**: ``true`` when the debug mode is enabled, ``false`` otherwise.

When ``true``, a log entry is made whenever the translator cannot find a translation
for a given key. The logs are made to the ``translation`` channel at the
``debug`` level for keys where there is a translation in the fallback
locale, and the ``warning`` level if there is no translation to use at all.

.. _reference-translator-paths:

paths
.....

**type**: ``array`` **default**: ``[]``

This option allows you to define an array of paths where the component will look
for translation files. The later a path is added, the more priority it has
(translations from later paths overwrite earlier ones). Translations from the
:ref:`default_path <reference-translator-default_path>` have more priority than
translations from all these paths.

.. _reference-translator-providers:

providers
.........

**type**: ``array`` **default**: ``[]``

This option enables and configures :ref:`translation providers <translation-providers>`
to push and pull your translations to/from third party translation services.

pseudo_localization
...................

**type**: ``array``

This option enables and configures the
:ref:`pseudolocalization <translation-pseudo-localization>` of translations,
which is a testing method that replaces the text elements of the application
with altered versions of the original language (e.g. replacing ``a`` with ``à``,
wrapping strings with brackets, etc.):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
            translator:
                pseudo_localization:
                    enabled: true
                    accents: true
                    brackets: true
                    expansion_factor: 1.0
                    parse_html: false
                    localizable_html_attributes: []

    .. code-block:: php

        // config/packages/translation.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->translator()
                ->pseudoLocalization()
                    ->enabled(true)
                    ->accents(true)
                    ->brackets(true)
                    ->expansionFactor(1.0)
                    ->parseHtml(false)
                    ->localizableHtmlAttributes([])
            ;
        };

enabled
"""""""

**type**: ``boolean`` **default**: ``false``

Whether or not to enable pseudolocalization.

accents
"""""""

**type**: ``boolean`` **default**: ``true``

When ``true``, replaces characters with accented versions of themselves (e.g.
``a`` is replaced by ``à``, ``c`` by ``ç``, etc.). This helps test the
application with special and accented characters.

brackets
""""""""

**type**: ``boolean`` **default**: ``true``

When ``true``, wraps each translated string with brackets (e.g. ``[!!! ... !!!]``).
This helps detect strings that are not being processed by the translator.

expansion_factor
""""""""""""""""

**type**: ``float`` **default**: ``1.0``

Controls the number of extra characters added to translated strings to simulate
the expansion of text when translating to more verbose languages. The minimum
value is ``1.0`` (no expansion). For example, a value of ``1.4`` means that
strings will be approximately 40% longer than the original text.

parse_html
""""""""""

**type**: ``boolean`` **default**: ``false``

When ``true``, HTML tags within translated content are preserved and only the
text content is pseudo-localized. This prevents HTML from being broken by the
pseudolocalization process.

localizable_html_attributes
"""""""""""""""""""""""""""

**type**: ``array`` **default**: ``[]``

Defines which HTML attributes should also be pseudo-localized (e.g.
``['title', 'alt']``). This option is only relevant when ``parse_html`` is
set to ``true``.

trust_x_sendfile_type_header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``%env(bool:default::SYMFONY_TRUST_X_SENDFILE_TYPE_HEADER)%``

``X-Sendfile`` is a special HTTP header that tells web servers to replace the
response contents by the file that is defined in that header. This improves
performance because files are no longer served by your application but directly
by the web server.

This configuration option determines whether to trust ``x-sendfile`` header for
BinaryFileResponse. If enabled, Symfony calls the
:method:`BinaryFileResponse::trustXSendfileTypeHeader <Symfony\\Component\\HttpFoundation\\BinaryFileResponse::trustXSendfileTypeHeader>`
method automatically. It becomes the service container parameter named
``kernel.trust_x_sendfile_type_header``.

.. _reference-framework-trusted-headers:

trusted_headers
~~~~~~~~~~~~~~~

The ``trusted_headers`` option is needed to configure which client information
should be trusted (e.g. their host) when running Symfony behind a load balancer
or a reverse proxy. See :doc:`/deployment/proxies`.

.. _configuration-framework-trusted-hosts:

trusted_hosts
~~~~~~~~~~~~~

**type**: ``array`` | ``string`` **default**: ``['%env(default::SYMFONY_TRUSTED_HOSTS)%']``

A lot of different attacks have been discovered relying on inconsistencies
in handling the ``Host`` header by various software (web servers, reverse
proxies, web frameworks, etc.). In short, every time the framework is
generating an absolute URL (when sending an email to reset a password for
instance), the host might have been manipulated by an attacker.

.. seealso::

    You can read `HTTP Host header attacks`_ for more information about
    these kinds of attacks.

The Symfony :method:`Request::getHost() <Symfony\\Component\\HttpFoundation\\Request::getHost>`
method might be vulnerable to some of these attacks because it depends on
the configuration of your web server. One simple solution to avoid these
attacks is to configure a list of hosts that your Symfony application can respond
to. That's the purpose of this ``trusted_hosts`` option. If the incoming
request's hostname doesn't match one of the regular expressions in this list,
the application won't respond and the user will receive a 400 response.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            trusted_hosts:  ['^example\.com$', '^example\.org$']

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'trusted_hosts' => ['^example\.com$', '^example\.org$'],
            ],
        ]);

Hosts can also be configured to respond to any subdomain, via
``^(.+\.)?example\.com$`` for instance.

In addition, you can also set the trusted hosts in the front controller
using the ``Request::setTrustedHosts()`` method::

    // public/index.php
    Request::setTrustedHosts(['^(.+\.)?example\.com$', '^(.+\.)?example\.org$']);

The default value for this option is an empty array, meaning that the application
can respond to any given host.

.. seealso::

    Read more about this in the `Security Advisory Blog post`_.

.. _reference-framework-trusted-proxies:

trusted_proxies
~~~~~~~~~~~~~~~

The ``trusted_proxies`` option is needed to get precise information about the
client (e.g. their IP address) when running Symfony behind a load balancer or a
reverse proxy. See :doc:`/deployment/proxies`.

type_info
~~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Enables the TypeInfo component integration in the framework.

aliases
.......

**type**: ``array`` **default**: ``[]``

Defines global type aliases for the TypeInfo component. The keys are the alias
names and the values are the type definitions (using PHPStan/Psalm syntax):

.. code-block:: yaml

    framework:
        type_info:
            aliases:
                MoneyAmount: int
                UserData: 'array{name: string, email: string, age: int}'

See the :doc:`TypeInfo component documentation </components/type_info>` for more
details about type aliases.

uid
~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Read the :doc:`UID component documentation </components/uid>` for complete
details about each option.

default_uuid_version
....................

**type**: ``integer`` **default**: ``7``

The version of UUID to generate by default (used by the ``create()`` method of
the UUID factory and for time-based UUIDs).

name_based_uuid_version
.......................

**type**: ``integer`` **default**: ``5``

The version of UUID to generate for name-based UUIDs.

name_based_uuid_namespace
.........................

**type**: ``string`` **default**: ``null``

A UUID that serves as the namespace for generating name-based UUIDs
(e.g. ``'6ba7b810-9dad-11d1-80b4-00c04fd430c8'``).

time_based_uuid_version
.......................

**type**: ``integer`` **default**: ``7``

The version of UUID to generate for time-based UUIDs.

time_based_uuid_node
....................

**type**: ``string`` | ``integer`` **default**: ``null``

The node to use for generating time-based UUIDs (e.g. ``'121212121212'``).

.. _reference-validation:

validation
~~~~~~~~~~

.. _reference-validation-auto-mapping:

auto_mapping
............

**type**: ``array`` **default**: ``[]``

Defines the Doctrine entities that will be introspected to add
:ref:`automatic validation constraints <automatic_object_validation>` to them:

.. configuration-block::

    .. code-block:: yaml

        framework:
            validation:
                auto_mapping:
                    # an empty array means that all entities that belong to that
                    # namespace will add automatic validation
                    'App\Entity\': []
                    'Foo\': ['Foo\Some\Entity', 'Foo\Another\Entity']

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Entity\AnotherEntity;
        use App\Entity\SomeEntity;

        return App::config([
            'framework' => [
                'auto_mapping' => [
                    // an empty array means that all entities that belong to that
                    // namespace will add automatic validation
                    'App\\Entity\\' => [],
                    'Foo\\' => [SomeEntity::class, AnotherEntity::class],
                ],
            ],
        ]);

.. _reference-validation-disable_translation:

disable_translation
...................

**type**: ``boolean`` **default**: ``false``

Validation error messages are automatically translated to the current application
locale. Set this option to ``true`` to disable translation of validation messages.
This is useful to avoid "missing translation" errors in applications that use
only a single language.

.. _reference-validation-email_validation_mode:

email_validation_mode
.....................

**type**: ``string`` **default**: ``html5``

Sets the default value for the
:ref:`"mode" option of the Email validator <reference-constraint-email-mode>`.

.. _reference-validation-enable_annotations:

enable_attributes
.................

**type**: ``boolean`` **default**: ``true``

If this option is enabled, validation constraints can be defined using `PHP attributes`_.

.. _reference-validation-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether or not to enable validation support.

This option will automatically be set to ``true`` when one of the child
settings is configured.

.. _reference-validation-mapping:

mapping
.......

.. _reference-validation-mapping-paths:

paths
"""""

**type**: ``array`` **default**: ``['config/validation/']``

This option allows you to define an array of paths with files or directories where
the component will look for additional validation files:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            validation:
                mapping:
                    paths:
                        - "%kernel.project_dir%/config/validation/"

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'validation' => [
                    'mapping' => [
                        'paths' => ['%kernel.project_dir%/config/validation/'],
                    ],
                ],
            ],
        ]);

.. _reference-validation-not-compromised-password:

not_compromised_password
........................

The :doc:`NotCompromisedPassword </reference/constraints/NotCompromisedPassword>`
constraint makes HTTP requests to a public API to check if the given password
has been compromised in a data breach.

.. _reference-validation-not-compromised-password-enabled:

enabled
"""""""

**type**: ``boolean`` **default**: ``true``

If you set this option to ``false``, no HTTP requests will be made and the given
password will be considered valid. This is useful when you don't want or can't
make HTTP requests, such as in ``dev`` and ``test`` environments or in
continuous integration servers.

endpoint
""""""""

**type**: ``string`` **default**: ``null``

By default, the :doc:`NotCompromisedPassword </reference/constraints/NotCompromisedPassword>`
constraint uses the public API provided by `haveibeenpwned.com`_. This option
allows you to define a different, but compatible, API endpoint to make the password
checks. It's useful for example when the Symfony application is run in an
intranet without public access to the internet.

property_metadata_existence_check
.................................

**type**: ``boolean`` **default**: ``false``

.. versionadded:: 8.1

    The ``property_metadata_existence_check`` option was introduced in Symfony 8.1.

When set to ``true``, the ``validateProperty()`` and ``validatePropertyValue()``
methods throw a ``ValidatorException`` when no validation metadata is found for
the given property.

static_method
.............

**type**: ``string | array`` **default**: ``['loadValidatorMetadata']``

Defines the name of the static method which is called to load the validation
metadata of the class. You can define an array of strings with the names of
several methods. In that case, all of them will be called in that order to load
the metadata.

translation_domain
..................

**type**: ``string | false`` **default**: ``validators``

The translation domain that is used when translating validation constraint
error messages. Use false to disable translations.

notifier
~~~~~~~~

chatter_transports
..................

**type**: ``array`` **default**: ``[]``

Configures the chatter transports used for chat notifications (e.g. Slack, Telegram).
The transport name is the key and the DSN is the value:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                chatter_transports:
                    slack: '%env(SLACK_DSN)%'
                    telegram: '%env(TELEGRAM_DSN)%'

    .. code-block:: php

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                ->chatterTransport('slack', '%env(SLACK_DSN)%')
                ->chatterTransport('telegram', '%env(TELEGRAM_DSN)%')
            ;
        };

texter_transports
.................

**type**: ``array`` **default**: ``[]``

Configures the texter transports used for SMS notifications (e.g. Twilio, Vonage).
The transport name is the key and the DSN is the value:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                texter_transports:
                    twilio: '%env(TWILIO_DSN)%'

    .. code-block:: php

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                ->texterTransport('twilio', '%env(TWILIO_DSN)%')
            ;
        };

channel_policy
..............

**type**: ``array`` **default**: ``[]``

Defines which channels are used for each notification importance level.
The key is the importance level (``urgent``, ``high``, ``medium``, ``low``)
and the value is an array of channel names:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                channel_policy:
                    urgent: ['sms', 'chat/slack', 'email']
                    high: ['chat/slack']
                    medium: ['browser']
                    low: ['browser']

    .. code-block:: php

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                ->channelPolicy('urgent', ['sms', 'chat/slack', 'email'])
                ->channelPolicy('high', ['chat/slack'])
                ->channelPolicy('medium', ['browser'])
                ->channelPolicy('low', ['browser'])
            ;
        };

admin_recipients
................

**type**: ``array`` **default**: ``[]``

Configures the recipients of notifications sent to administrators. Each recipient
can have an ``email`` and/or a ``phone`` number:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                admin_recipients:
                    - { email: 'admin@example.com' }
                    - { email: 'lead@example.com', phone: '+1555123456' }

    .. code-block:: php

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                ->adminRecipient()
                    ->email('admin@example.com')
            ;
            $framework->notifier()
                ->adminRecipient()
                    ->email('lead@example.com')
                    ->phone('+1555123456')
            ;
        };

message_bus
...........

**type**: ``string`` | ``false`` **default**: ``null`` or default bus if Messenger component is installed

Defines which message bus is used to dispatch notification messages. Set to
``false`` to call the notifier transport directly instead of dispatching
through the bus:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/notifier.yaml
        framework:
            notifier:
                message_bus: false

    .. code-block:: php

        // config/packages/notifier.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->notifier()
                ->messageBus(false)
            ;
        };

.. seealso::

    For more details, see the :doc:`Notifier component </notifier>` documentation.

web_link
~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Adds a `Link HTTP header`_ to the response.

webhook
~~~~~~~

The ``webhook`` option (and its children) are used to configure the webhooks
defined in your application. Read more about the options in the
:doc:`Webhook documentation </webhook>`.

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable the webhook support in the framework. This is automatically
enabled when the ``symfony/webhook`` package is installed.

message_bus
...........

**type**: ``string`` **default**: ``'messenger.default_bus'``

The service id of the Messenger bus used to dispatch incoming webhook messages.

event_header_name
.................

**type**: ``string`` **default**: ``'Webhook-Event'``

The name of the HTTP header used to transmit the event name.

.. versionadded:: 8.1

    The ``event_header_name`` option was introduced in Symfony 8.1.

id_header_name
..............

**type**: ``string`` **default**: ``'Webhook-Id'``

The name of the HTTP header used to transmit the event ID.

.. versionadded:: 8.1

    The ``id_header_name`` option was introduced in Symfony 8.1.

signature_header_name
.....................

**type**: ``string`` **default**: ``'Webhook-Signature'``

The name of the HTTP header used to transmit the HMAC signature.

.. versionadded:: 8.1

    The ``signature_header_name`` option was introduced in Symfony 8.1.

signing_algorithm
.................

**type**: ``string`` **default**: ``'sha256'``

The hash algorithm used to sign outgoing webhooks (e.g. ``sha256``, ``sha512``).

.. versionadded:: 8.1

    The ``signing_algorithm`` option was introduced in Symfony 8.1.

routing
.......

**type**: ``array``

Defines the mapping between webhook types and their request parsers. Each key
is the webhook type name (used in the URL ``/webhook/{type}``) and its value
is an array with the following options:

.. code-block:: yaml

    framework:
        webhook:
            routing:
                github:
                    service: App\Webhook\GithubRequestParser
                    secret: '%env(GITHUB_WEBHOOK_SECRET)%'
                stripe:
                    service: App\Webhook\StripeRequestParser
                    secret: '%env(STRIPE_WEBHOOK_SECRET)%'

service
'''''''

**type**: ``string``

The service id of the request parser (a class implementing
:class:`Symfony\\Component\\Webhook\\Client\\RequestParserInterface`) that will
handle incoming requests for this webhook type. This option is required.

secret
''''''

**type**: ``string`` **default**: ``''``

The secret used to verify the webhook signature. The value depends on the
third-party service sending the webhook.

workflows
~~~~~~~~~

**type**: ``array``

A list of workflows to be created by the framework extension:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/workflow.yaml
        framework:
            workflows:
                my_workflow:
                    # ...

    .. code-block:: php

        // config/packages/workflow.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'workflows' => [
                    'my_workflow' => [
                        // ...
                    ],
                ],
            ],
        ]);

.. seealso::

    See also the article about :doc:`using workflows in Symfony applications </workflow>`.

.. _reference-workflows-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

Whether to enable the support for workflows or not. This setting is
automatically set to ``true`` when one of the child settings is configured.

.. _reference-workflows-name:

workflows
.........

**type**: ``prototype``

A list of workflows to create, keyed by the workflow name.

audit_trail
"""""""""""

**type**: ``boolean``

If set to ``true``, the :class:`Symfony\\Component\\Workflow\\EventListener\\AuditTrailListener`
will be enabled.

definition_validators
"""""""""""""""""""""

**type**: ``array``

A list of fully-qualified class names of definition validators. Each class must
implement :class:`Symfony\\Component\\Workflow\\Validator\\DefinitionValidatorInterface`
and its constructor must have no required parameters.

events_to_dispatch
""""""""""""""""""

**type**: ``array`` **default**: ``null``

Selects which transition events should be dispatched for this workflow.
If ``null``, all events are dispatched. Otherwise, it must be an array of
workflow event names (e.g. ``['workflow.enter', 'workflow.transition']``).

initial_marking
"""""""""""""""

**type**: ``string`` | ``array``

One of the ``places`` or ``empty``. If not null and the supported object is not
already initialized via the workflow, this place will be set.

marking_store
"""""""""""""

**type**: ``array``

Each marking store can define any of these options:

* ``property`` (**type**: ``string`` **default**: ``marking``)
* ``service`` (**type**: ``string``)
* ``type`` (**type**: ``string`` **allow value**: ``'method'``)

metadata
""""""""

**type**: ``array``

Metadata available for the workflow configuration.
Note that ``places`` and ``transitions`` can also have their own
``metadata`` entry.

places
""""""

**type**: ``array``

All available places (**type**: ``string``) for the workflow configuration.

supports
""""""""

**type**: ``string`` | ``array``

The FQCN (fully-qualified class name) of the object supported by the workflow
configuration or an array of FQCN if multiple objects are supported.

support_strategy
""""""""""""""""

**type**: ``string``

transitions
"""""""""""

**type**: ``array``

Each marking store can define any of these options:

* ``from`` (**type**: ``string`` or ``array``) value from the ``places``,
  multiple values are allowed for both ``workflow`` and ``state_machine``;
* ``guard`` (**type**: ``string``) an :doc:`ExpressionLanguage </expression_language>`
  compatible expression to block the transition;
* ``name`` (**type**: ``string``) the name of the transition;
* ``to`` (**type**: ``string`` or ``array``) value from the ``places``,
  multiple values are allowed only for ``workflow``.

.. _reference-workflows-type:

type
""""

**type**: ``string`` **possible values**: ``'workflow'`` or ``'state_machine'``

Defines the kind of workflow that is going to be created, which can be either
a normal workflow or a state machine. Read :doc:`this article </workflow/workflow-and-state-machine>`
to know their differences.

.. _`HTTP Host header attacks`: https://www.skeletonscribe.net/2013/05/practical-http-host-header-attacks.html
.. _`Security Advisory Blog post`: https://symfony.com/blog/security-releases-symfony-2-0-24-2-1-12-2-2-5-and-2-3-3-released#cve-2013-4752-request-gethost-poisoning
.. _`phpstorm-url-handler`: https://github.com/sanduhrs/phpstorm-url-handler
.. _`blue/green deployment`: https://martinfowler.com/bliki/BlueGreenDeployment.html
.. _`webpack-manifest-plugin`: https://www.npmjs.com/package/webpack-manifest-plugin
.. _`json_encode flags bitmask`: https://www.php.net/json_encode
.. _`error_reporting PHP option`: https://www.php.net/manual/en/errorfunc.configuration.php#ini.error-reporting
.. _`CSRF security attacks`: https://en.wikipedia.org/wiki/Cross-site_request_forgery
.. _`X-Robots-Tag HTTP header`: https://developers.google.com/search/reference/robots_meta_tag
.. _`RFC 3986`: https://www.ietf.org/rfc/rfc3986.txt
.. _`default_socket_timeout`: https://www.php.net/manual/en/filesystem.configuration.php#ini.default-socket-timeout
.. _`PEM formatted`: https://en.wikipedia.org/wiki/Privacy-Enhanced_Mail
.. _`haveibeenpwned.com`: https://haveibeenpwned.com/
.. _`session.name`: https://www.php.net/manual/en/session.configuration.php#ini.session.name
.. _`session.cookie_lifetime`: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-lifetime
.. _`session.cookie_path`: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-path
.. _`session.cache_limiter`: https://www.php.net/manual/en/session.configuration.php#ini.session.cache-limiter
.. _`session.cookie_domain`: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-domain
.. _`session.cookie_samesite`: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-samesite
.. _`session.cookie_secure`: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-secure
.. _`session.gc_divisor`: https://www.php.net/manual/en/session.configuration.php#ini.session.gc-divisor
.. _`session.gc_probability`: https://www.php.net/manual/en/session.configuration.php#ini.session.gc-probability
.. _`session.gc_maxlifetime`: https://www.php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime
.. _`session.save_path`: https://www.php.net/manual/en/session.configuration.php#ini.session.save-path
.. _`session.use_cookies`: https://www.php.net/manual/en/session.configuration.php#ini.session.use-cookies
.. _`Microsoft NTLM authentication protocol`: https://docs.microsoft.com/en-us/windows/win32/secauthn/microsoft-ntlm
.. _`utf-8 modifier`: https://www.php.net/reference.pcre.pattern.modifiers
.. _`Link HTTP header`: https://tools.ietf.org/html/rfc5988
.. _`SMTP session`: https://en.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol#SMTP_transport_example
.. _`OpenSSL cipher`: https://www.php.net/manual/en/openssl.ciphers.php
.. _`PHP attributes`: https://www.php.net/manual/en/language.attributes.overview.php
.. _`shared cache`: https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Caching#shared_cache
.. _`private cache`: https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Caching#private_caches
.. _`W3C Sanitizer API standard`: https://wicg.github.io/sanitizer-api/#default-configuration
