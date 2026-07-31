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

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets base-path="/images"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            // ...
            $framework->assets()
                ->basePath('/images');
        };

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
                    - 'http://cdn.example.com/'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets base-url="http://cdn.example.com/"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            // ...
            $framework->assets()
                ->baseUrls(['http://cdn.example.com/']);
        };

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

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- this manifest is applied to every asset (including packages) -->
                <framework:assets json-manifest-path="%kernel.project_dir%/public/build/manifest.json">
                <!-- you can use absolute URLs too and Symfony will download them automatically -->
                <!-- <framework:assets json-manifest-path="https://cdn.example.com/manifest.json"> -->
                    <!-- this package uses its own manifest (the default file is ignored) -->
                    <!-- Throws an exception when an asset is not found in the manifest -->
                    <framework:package
                        name="foo_package"
                        json-manifest-path="%kernel.project_dir%/public/build/a_different_manifest.json" strict-mode="%kernel.debug%"/>
                    <!-- this package uses the global manifest (the default file is used) -->
                    <framework:package
                        name="bar_package"
                        base-path="/images"/>
                </framework:assets>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            // ...
            $framework->assets()
                // this manifest is applied to every asset (including packages)
                ->jsonManifestPath('%kernel.project_dir%/public/build/manifest.json');

            // you can use absolute URLs too and Symfony will download them automatically
            // 'json_manifest_path' => 'https://cdn.example.com/manifest.json',
            $framework->assets()->package('foo_package')
                // this package uses its own manifest (the default file is ignored)
                ->jsonManifestPath('%kernel.project_dir%/public/build/a_different_manifest.json')
                // Throws an exception when an asset is not found in the manifest
                ->setStrictMode('%kernel.debug%');

            $framework->assets()->package('bar_package')
                // this package uses the global manifest (the default file is used)
                ->basePath('/images');
        };

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

.. versionadded:: 7.4

    Before Symfony 7.4, the target name had to include the ``.package``
    suffix (e.g. ``#[Target('foo_package.package')]``).

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
                        base_urls: 'http://static_cdn.example.com/avatars'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets>
                    <framework:package
                        name="avatars"
                        base-url="http://static_cdn.example.com/avatars"/>
                </framework:assets>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            // ...
            $framework->assets()
                ->package('avatars')
                    ->baseUrls(['http://static_cdn.example.com/avatars']);
        };

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

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets version="v2"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            // ...
            $framework->assets()
                ->version('v2');
        };

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

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:assets version-strategy="app.asset.my_versioning_strategy">
                    <!-- this package removes any versioning (its assets won't be versioned) -->
                    <framework:package
                        name="foo_package"
                        version="null"/>
                    <!-- this package uses its own strategy (the default strategy is ignored) -->
                    <framework:package
                        name="bar_package"
                        version-strategy="app.asset.another_version_strategy"/>
                    <!-- this package inherits the default strategy -->
                    <framework:package
                        name="baz_package"
                        base_path="/images"/>
                </framework:assets>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            // ...
            $framework->assets()
                ->versionStrategy('app.asset.my_versioning_strategy');

            $framework->assets()->package('foo_package')
                // this package removes any versioning (its assets won't be versioned)
                ->version(null);

            $framework->assets()->package('bar_package')
                // this package uses its own strategy (the default strategy is ignored)
                ->versionStrategy('app.asset.another_version_strategy');

            $framework->assets()->package('baz_package')
                // this package inherits the default strategy
                ->basePath('/images');
        };

.. note::

    This parameter cannot be set at the same time as ``version`` or ``json_manifest_path``.

.. _`webpack-manifest-plugin`: https://www.npmjs.com/package/webpack-manifest-plugin
