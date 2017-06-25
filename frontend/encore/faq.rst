FAQ and Common Issues
=====================

My App Lives under a Subdirectory
---------------------------------

If your app does not live at the root of your web server (i.e. it lives under a subdirectory,
like ``/myAppSubdir``), you just need to configure that when calling ``Encore.setPublicPrefix()``:

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

"jQuery is not defined" or "$ is not defined"
---------------------------------------------

This error happens when your code (or some library that you are using) expects ``$``
or ``jQuery`` to be a global variable. But, when you use Webpack and ``require('jquery')``,
no global variables are set.

The fix depends on if the error is happening in your code or inside some third-party
code that you're using. See :doc:`/frontend/encore/legacy-apps` for the fix.

Uncaught ReferenceError: webpackJsonp is not defined
----------------------------------------------------

If you get this error, it's probably because you've just added a :doc:`shared entry </frontend/encore/shared-entry>`
but you *forgot* to add a ``script`` tag for the new ``manifest.js`` file. See the
information about the :ref:`script tags <encore-shared-entry-script>` in that section.
