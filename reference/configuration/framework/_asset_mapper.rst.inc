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
