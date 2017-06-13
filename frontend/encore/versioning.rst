Asset Versioning
================

.. _encore-long-term-caching:

Tired of deploying and having browser's cache the old version of your assets?
By calling ``enableVersioning()``, each filename will now include a hash that
changes whenever the *contents* of that file change (e.g. ``app.123abc.js``
instead of ``app.js``). This allows you to use aggressive caching strategies
(e.g. a far future ``Expires``) because, whenever a file change, its hash will change,
ignoring any existing cache:

.. code-block:: diff

    // webpack.config.js
    // ...

    Encore
        .setOutputPath('web/build/')
        // ...
    +     .enableVersioning()

To link to these assets, Encore creates a ``manifest.json`` file with a map to
the new filenames.

.. _load-manifest-files:

Loading Assets from the manifest.json File
------------------------------------------

Whenever you run Encore, a ``manifest.json`` file is automatically
created in your ``outputPath`` directory:

.. code-block:: json

    {
        "build/app.js": "/build/app.123abc.js",
        "build/dashboard.css": "/build/dashboard.a4bf2d.css"
    }

In your app, you need to read this file to dynamically render the correct paths
in your ``script`` and ``link`` tags. If you're using Symfony, just activate the
``json_manifest_file`` versioning strategy in ``config.yml``:

.. code-block:: yaml

    # app/config/config.yml
    framework:
        # ...
        assets:
            # feature is supported in Symfony 3.3 and higher
            json_manifest_path: '%kernel.project_dir%/web/build/manifest.json'

That's it! Just be sure to wrap each path in the Twig ``asset()`` function
like normal:

.. code-block:: twig

    <script src="{{ asset('build/app.js') }}"></script>

    <link href="{{ asset('build/dashboard.css') }}" rel="stylesheet" />
