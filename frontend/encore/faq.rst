WebpackEncore: FAQ and Common Issues
====================================

.. _how-do-i-deploy-my-encore-assets:

How Do I Deploy My Encore Assets?
---------------------------------

There are two important things to remember when deploying your assets.

**1) Compile Assets for Production**

Optimize your assets for production by running:

.. code-block:: terminal

    $ ./node_modules/.bin/encore production

That will minify your assets and make other performance optimizations. Yay!

But, what server should you run this command on? That depends on how you deploy.
For example, you could execute this locally (or on a build server), and use
`rsync`_ or something else to transfer the generated files to your production
server. Or, you could put your files on your production server first (e.g. via
``git pull``) and then run this command on production (ideally, before traffic
hits your code). In this case, you'll need to install Node.js on your production
server.

**2) Only Deploy the Built Assets**

The *only* files that need to be deployed to your production servers are the
final, built assets (e.g. the ``public/build`` directory). You do *not* need to install
Node.js, deploy ``webpack.config.js``, the ``node_modules`` directory or even your source
asset files, **unless** you plan on running ``encore production`` on your production
machine. Once your assets are built, these are the *only* thing that need to live
on the production server.

Do I Need to Install Node.js on My Production Server?
-----------------------------------------------------

No, unless you plan to build your production assets on your production server,
which is not recommended. See `How Do I Deploy my Encore Assets?`_.

What Files Should I Commit to git? And which Should I Ignore?
-------------------------------------------------------------

You should commit all of your files to git, except for the ``node_modules/`` directory
and the built files. Your ``.gitignore`` file should include:

.. code-block:: text

    /node_modules/
    # whatever path you're passing to Encore.setOutputPath()
    /public/build

You *should* commit all of your source asset files, ``package.json`` and ``package-lock.json``.

My App Lives under a Subdirectory
---------------------------------

If your app does not live at the root of your web server (i.e. it lives under a subdirectory,
like ``/myAppSubdir``), you will need to configure that when calling ``Encore.setPublicPath()``:

.. code-block:: diff

      // webpack.config.js
      Encore
          // ...

          .setOutputPath('public/build/')

    -     .setPublicPath('/build')
    +     // this is your *true* public path
    +     .setPublicPath('/myAppSubdir/build')

    +     // this is now needed so that your manifest.json keys are still `build/foo.js`
    +     // (which is a file that's used by Symfony's `asset()` function)
    +     .setManifestKeyPrefix('build')
      ;

If you're using the ``encore_entry_script_tags()`` and ``encore_entry_link_tags()``
Twig shortcuts (or are :ref:`processing your assets through entrypoints.json <load-manifest-files>`
in some other way) you're done! These shortcut methods read from an
:ref:`entrypoints.json <encore-entrypointsjson-simple-description>` file that will
now contain the subdirectory.

"jQuery is not defined" or "$ is not defined"
---------------------------------------------

This error happens when your code (or some library that you are using) expects ``$``
or ``jQuery`` to be a global variable. But, when you use Webpack and ``require('jquery')``,
no global variables are set.

The fix depends on if the error is happening in your code or inside some third-party
code that you're using. See :doc:`/frontend/encore/legacy-applications` for the fix.

Uncaught ReferenceError: webpackJsonp is not defined
----------------------------------------------------

If you get this error, it's probably because you've forgotten to add a ``script``
tag for the ``runtime.js`` file that contains Webpack's runtime. If you're using
the ``encore_entry_script_tags()`` Twig function, this should never happen: the
file script tag is rendered automatically.

This dependency was not found: some-module in ./path/to/file.js
---------------------------------------------------------------

Usually, after you install a package via npm, you can require / import
it to use it. For example, after running ``npm install respond.js``,
you try to require that module:

.. code-block:: javascript

    require('respond.js');

But, instead of working, you see an error:

    This dependency was not found:

    * respond.js in ./assets/app.js

Typically, a package will "advertise" its "main" file by adding a ``main`` key to
its ``package.json``. But sometimes, old libraries won't have this. Instead, you'll
need to specifically require the file you need. In this case, the file you should
use is located at ``node_modules/respond.js/dest/respond.src.js``. You can require
this via:

.. code-block:: javascript

    // require a non-minified file whenever possible
    require('respond.js/dest/respond.src.js');

I need to execute Babel on a third-party Module
-----------------------------------------------

For performance, Encore does not process libraries inside ``node_modules/`` through
Babel. But, you can change that via the ``configureBabel()`` method. See
:doc:`/frontend/encore/babel` for details.

How Do I Integrate my Encore Configuration with my IDE?
-------------------------------------------------------

`Webpack integration in PhpStorm`_ and other IDEs makes your development more
productive (for example by resolving aliases). However, you may face this error:

.. code-block:: text

    Encore.setOutputPath() cannot be called yet because the runtime environment
    doesn't appear to be configured. Make sure you're using the encore executable
    or call Encore.configureRuntimeEnvironment() first if you're purposely not
    calling Encore directly.

It fails because the Encore Runtime Environment is only configured when you are
running it (e.g. when executing ``npx encore dev``). Fix this issue calling to
``Encore.isRuntimeEnvironmentConfigured()`` and
``Encore.configureRuntimeEnvironment()`` methods:

.. code-block:: javascript

    // webpack.config.js
    const Encore = require('@symfony/webpack-encore')

    if (!Encore.isRuntimeEnvironmentConfigured()) {
        Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
    }

    // ... the rest of the Encore configuration

My Tests are Failing Because of ``entrypoints.json`` File
---------------------------------------------------------

After installing Encore, you might see the following error when running tests
locally or on your Continuous Integration server:

.. code-block:: text

    Uncaught PHP Exception Twig\Error\RuntimeError:
    "An exception has been thrown during the rendering of a template
    ("Could not find the entrypoints file from Webpack:
    the file "/var/www/html/public/build/entrypoints.json" does not exist.

This is happening because you did not build your Encore assets, hence no
``entrypoints.json`` file. To solve this error, either build Encore assets or
set the ``strict_mode`` option to ``false`` (this prevents Encore's Twig
functions to trigger exceptions when there's no ``entrypoints.json`` file):

.. code-block:: yaml

    # config/packages/test/webpack_encore.yaml
    webpack_encore:
        strict_mode: false
        # ...

.. _`rsync`: https://rsync.samba.org/
.. _`Webpack integration in PhpStorm`: https://www.jetbrains.com/help/phpstorm/using-webpack.html
