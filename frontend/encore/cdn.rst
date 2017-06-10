Using a CDN
===========

Are you deploying to a CDN? That's awesome :) - and configuring
Encore for that is easy. Once you've made sure that your built files
are uploaded to the CDN, configure it in Encore:

.. code-block:: diff

    // webpack.config.js
    // ...

    Encore
        .setOutputPath('web/build/')
        // in dev mode, don't use the CDN
        .setPublicPath('/build');
        // ...
    ;

    + if (Encore.isProduction()) {
    +     Encore.setPublicPath('https://my-cool-app.com.global.prod.fastly.net');
    +
    +     // guarantee that the keys in manifest.json are *still*
    +     // prefixed with build/
    +     // (e.g. "build/dashboard.js": "https://my-cool-app.com.global.prod.fastly.net/dashboard.js")
    +     Encore.setManifestKeyPrefix('build/');
    + }

That's it! Internally, Webpack will now know to load assets from your CDN -
e.g. ``https://my-cool-app.com.global.prod.fastly.net/dashboard.js``.

.. note::

    It's still your responsibility to put your assets on the CDN - e.g. by
    uploading them or by using "origin pull", where your CDN pulls assets
    directly from your web server.

You *do* need to make sure that the ``script`` and ``link`` tags you include on your
pages also uses the CDN. Fortunately, the ``manifest.json`` paths are
updated to point to the CDN. In Symfony, as long as you've configured
:doc:`Asset Versioning </frontend/encore/versioning>`, you're done! The ``manifest.json``
file includes the full CDN URL:

.. code-block:: js

    {# Your script/link tags don't need to change at all to support the CDN #}
    <script src="{{ asset('build/dashboard.js') }}"></script>
