FAQ and Common Issues
=====================

How do I deploy my Encore Assets?
---------------------------------

There are two important things to remember when deploying your assets.

**1) Run ``encore production``**

Optimize your assets for production by running:

.. code-block:: terminal

    $ ./node_modules/.bin/encore production

That will minify your assets and make other performance optimizations. Yay!

But, what server should you run this command on? That depends on how you deploy.
For example, you could execute this locally (or on a build server), and use rsync
or something else to transfer the built files to your server. Or, you could put your
files on your production server first (e.g. via a git pull) and then run this command
on production (ideally, before traffic hits your code). In this case, you'll need
to install Node.js on your production server.

**2) Only Deploy the Built Assets**

The *only* files that need to be deployed to your production servers are the
final, built assets (e.g. the ``web/build`` directory). You do *not* need to install
Node.js, deploy ``webpack.config.js``, the ``node_modules`` directory or even your source
asset files, **unless** you plan on running ``encore production`` on your production
machine. Once your assets are built, these are the *only* thing that need to live
on the production server.

Do I need to Install Node.js on my Production Server?
-----------------------------------------------------

No, unless you plan to build your production assets on your production server,
which is not recommended. See `How do I deploy my Encore Assets?`_.

What Files Should I commit to git? And which should I Ignore?
-------------------------------------------------------------

You should commit all of your files to git, except for the ``node_modules/`` directory
and the built files. Your ``.gitignore`` file should include:

.. code-block:: text

    /node_modules/
    # whatever path you're passing to Encore.setOutputPath()
    /web/build

You *should* commit all of your source asset files, ``package.json`` and ``yarn.lock``.

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
        + .setManifestKeyPrefix('build')
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

This dependency was not found: some-module in ./path/to/file.js
---------------------------------------------------------------

Usually, after you install a package via yarn, you can require / import it to use
it. For example, after running ``yarn add respond.js``, you try to require that module:

.. code-block:: javascript

    require('respond.js');

But, instead of working, you see an error:

    This dependency was not found:

    * respond.js in ./app/Resources/assets/js/app.js

Typically, a package will "advertise" its "main" file by adding a ``main`` key to
its ``package.json``. But sometimes, old libraries won't have this. Instead, you'll
need to specifically require the file you need. In this case, the file you should
use is located at ``node_modules/respond.js/dest/respond.src.js``. You can require
this via:

.. code-block:: javascript

    // require a non-minified file whenever possible
    require('respond.js/dest/respond.src.js');
