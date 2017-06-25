Frequently Asked Questions
==========================

My App Lives under a Subdirectory
---------------------------------

    My app doesn't live at the root of my web server: it lives under a subdirectory
    (e.g. ``/myAppSubdir/``). How can I configure Encore to work?

If your app lives under a subdirectory, you just need to include that when calling
``Encore.setPublicPrefix()``:

.. code-block:: diff

    // webpack.config.js
    Encore
        // ...

        .setOutputPath('web/build/')

        - .setPublicPath('/build')
        + // this is your *true* public path
        + .setPublicPath('/myAppSubdir/build')

        + // this is now needed so that your manifest.json keys are still `build/foo.js`
        + // i.e. you won't need to change anything in your Symfony app
        + config.setManifestKeyPrefix('build')
    ;

If you're :ref:`processing your assets through manifest.json <load-manifest-files>`,
you're done! The ``manifest.json`` file will now include the subdirectory in the
final paths:

.. code-block:: json

    {
        "build/app.js": "/myAppSubdir/build/app.123abc.js",
        "build/dashboard.css": "/myAppSubdir/build/dashboard.a4bf2d.css"
    }
