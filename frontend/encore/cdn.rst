Using a CDN with Webpack Encore
===============================

Are you deploying to a CDN? That's awesome :) Once you've made sure that your
built files are uploaded to the CDN, configure it in Encore:

.. code-block:: diff

      // webpack.config.js
      // ...

      Encore
          .setOutputPath('public/build/')
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
pages also use the CDN. Fortunately, the
:ref:`entrypoints.json <encore-entrypointsjson-simple-description>` paths are updated
to include the full URL to the CDN.

When deploying to a subdirectory of your CDN, you must add the path at the end of your URL -
e.g. ``Encore.setPublicPath('https://my-cool-app.com.global.prod.fastly.net/awesome-website')``
will generate assets URLs like ``https://my-cool-app.com.global.prod.fastly.net/awesome-website/dashboard.js``

If you are using ``Encore.enableIntegrityHashes()`` and your CDN and your domain
are not the `same-origin`_, you may need to set the ``crossorigin`` option in
your webpack_encore.yaml configuration to ``anonymous`` or ``use-credentials``
to overcome CORS errors.

.. _`same-origin`: https://en.wikipedia.org/wiki/Same-origin_policy
