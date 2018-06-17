First Example
=============

Imagine you have a simple project with one CSS and one JS file, organized into
an ``assets/`` directory:

* ``assets/js/app.js``
* ``assets/css/app.css``

With Encore, you should think of CSS as a *dependency* of your JavaScript. This means,
you will *require* whatever CSS you need from inside JavaScript:

.. code-block:: javascript

    // assets/js/app.js
    require('../css/app.css');

    // ...rest of JavaScript code here

With Encore, we can easily minify these files, pre-process ``app.css``
through PostCSS and a *lot* more.

Configuring Encore/Webpack
--------------------------

Create a new file called ``webpack.config.js`` at the root of your project.
Inside, use Encore to help generate your Webpack configuration.

.. code-block:: javascript

    // webpack.config.js
    var Encore = require('@symfony/webpack-encore');

    Encore
        // the project directory where all compiled assets will be stored
        .setOutputPath('web/build/')

        // the public path used by the web server to access the previous directory
        .setPublicPath('/build')

        // will create web/build/app.js and web/build/app.css
        .addEntry('app', './assets/js/app.js')

        // allow legacy applications to use $/jQuery as a global variable
        .autoProvidejQuery()

        // enable source maps during development
        .enableSourceMaps(!Encore.isProduction())

        // empty the outputPath dir before each build
        .cleanupOutputBeforeBuild()

        // show OS notifications when builds finish/fail
        .enableBuildNotifications()

        // create hashed filenames (e.g. app.abc123.css)
        // .enableVersioning()

        // allow sass/scss files to be processed
        // .enableSassLoader()
    ;

    // export the final configuration
    module.exports = Encore.getWebpackConfig();

This is already a rich setup: it outputs 2 files and enables source maps
to help debugging.

.. _encore-build-assets:

To build the assets, use the ``encore`` executable:

.. code-block:: terminal

    # compile assets once
    $ ./node_modules/.bin/encore dev

    # recompile assets automatically when files change
    $ ./node_modules/.bin/encore dev --watch

    # compile assets, but also minify & optimize them
    $ ./node_modules/.bin/encore production

    # shorter version of the above 3 commands
    $ yarn run encore dev
    $ yarn run encore dev --watch
    $ yarn run encore production

.. note::

    Re-run ``encore`` each time you update your ``webpack.config.js`` file.

After running one of these commands, you can now add ``script`` and ``link`` tags
to the new, compiled assets (e.g. ``/build/app.css`` and ``/build/app.js``).
In Symfony, use the ``asset()`` helper:

.. code-block:: twig

    {# base.html.twig #}
    <!DOCTYPE html>
    <html>
        <head>
            <!-- ... -->
            <link rel="stylesheet" href="{{ asset('build/app.css') }}">
        </head>
        <body>
            <!-- ... -->
            <script src="{{ asset('build/app.js') }}"></script>
        </body>
    </html>

Using Sass
----------

Instead of using plain CSS you can also use Sass. In order to do so, change the
extension of the ``app.css`` file to ``.sass`` or ``.scss`` (based on the syntax
you want to use):

.. code-block:: diff

    // assets/js/app.js
    - require('../css/app.css');
    + require('../css/app.scss');

And enable the Sass pre-processor:

.. code-block:: diff

    // webpack.config.js
    Encore
        // ...

        // allow sass/scss files to be processed
    -    // .enableSassLoader()
    +    .enableSassLoader()

Using ``enableSassLoader()`` requires to install additional packages, but Encore
will tell you *exactly* which ones when running it.

Requiring JavaScript Modules
----------------------------

Webpack is a module bundler... which means that you can ``require`` other JavaScript
files. First, create a file that exports a function:

.. code-block:: javascript

    // assets/js/greet.js
    module.exports = function(name) {
        return `Yo yo ${name} - welcome to Encore!`;
    };

We'll use jQuery to print this message on the page. Install it via:

.. code-block:: terminal

    $ yarn add jquery --dev

Great! Use ``require()`` to import ``jquery`` and ``greet.js``:

.. code-block:: javascript

    // assets/js/app.js

    // loads the jquery package from node_modules
    const $ = require('jquery');

    // import the function from greet.js (the .js extension is optional)
    // ./ (or ../) means to look for a local file
    var greet = require('./greet');

    $(document).ready(function() {
        $('body').prepend('<h1>'+greet('john')+'</h1>');
    });

That's it! When you build your assets, jQuery and ``greet.js`` will automatically
be added to the output file (``app.js``). For common libraries like jQuery, you
may want to :doc:`create a shared entry </frontend/encore/shared-entry>` for better
performance.

Multiple JavaScript Entries
---------------------------

The previous example is the best way to deal with SPA (Single Page Applications)
and very simple applications. However, as your app grows, you may want to have
page-specific JavaScript or CSS (e.g. homepage, blog, store, etc.). To handle this,
add a new "entry" for each page that needs custom JavaScript or CSS:

.. code-block:: javascript

    Encore
        // ...
        .addEntry('homepage', './assets/js/homepage.js')
        .addEntry('blog', './assets/js/blog.js')
        .addEntry('store', './assets/js/store.js')
    ;

If those entries include CSS/Sass files (e.g. ``homepage.js`` requires
``assets/css/homepage.scss``), two files will be generated for each:
(e.g. ``build/homepage.js`` and ``build/homepage.css``).

Keep Going!
-----------

Go back to the :ref:`Encore Top List <encore-toc>` to learn more and add new features.
