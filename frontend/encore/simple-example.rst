First Example
=============

Imagine you have a simple project with one CSS and one JS file, organized into
an ``assets/`` directory:

* ``assets/js/app.js``
* ``assets/css/app.scss``

Let's consider that the project follows the recommended practice of importing
the CSS files for their associated JavaScript files:

.. code-block:: javascript

    // assets/js/app.js
    require('../css/app.scss');

    // ...rest of JavaScript code here

With Encore, we can easily minify these files, pre-process ``app.scss``
through Sass and a *lot* more.

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

        // empty the outputPath dir before each build
        .cleanupOutputBeforeBuild()

        // will create web/build/app.js and web/build/app.css
        .addEntry('app', './assets/js/app.js')

        // allow sass/scss files to be processed
        .enableSassLoader()

        // allow legacy applications to use $/jQuery as a global variable
        .autoProvidejQuery()

        .enableSourceMaps(!Encore.isProduction())

        // create hashed filenames (e.g. app.abc123.css)
        // .enableVersioning()
    ;

    // export the final configuration
    module.exports = Encore.getWebpackConfig();

This is already a rich setup: it outputs 2 files, uses the Sass pre-processor and
enables source maps to help debugging.

.. _encore-build-assets:

To build the assets, use the ``encore`` executable:

.. code-block:: terminal

    # compile assets once
    $ ./node_modules/.bin/encore dev

    # recompile assets automatically when files change
    $ ./node_modules/.bin/encore dev --watch

    # compile assets, but also minify & optimize them
    $ ./node_modules/.bin/encore production

.. note::

    Re-run ``encore`` each time you update your ``webpack.config.js`` file.

Actually, to use ``enableSassLoader()``, you'll need to install a few
more packages. But Encore will tell you *exactly* what you need.

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
    var $ = require('jquery');

    // import the function from greet.js (the .js extension is optional)
    // ./ (or ../) means to look for a local file
    var greet = require('./greet');

    $(document).ready(function() {
        $('h1').html(greet('john'));
    });

That's it! When you build your assets, jQuery and ``greet.js`` will automatically
be added to the output file (``app.js``). For common libraries like jQuery, you
may want also to :doc:`create a shared entry </frontend/encore/shared-entry>` for better performance.

Multiple JavaScript Entries
---------------------------

The previous example is the best way to deal with SPA (Single Page Applications)
and very simple applications. However, as your application grows, you'll need to
define more entries with the JavaScript and CSS code of some specific sections
(homepage, blog, store, etc.)

.. code-block:: javascript

    Encore
        // ...
        .addEntry('homepage', './assets/js/homepage.js')
        .addEntry('blog', './assets/js/blog.js')
        .addEntry('store', './assets/js/store.js')
    ;

If those entries include CSS/Sass files (e.g. ``homepage.js`` requires
``assets/css/homepage.scss``), two files will be generated for each of them
(e.g. ``build/homepage.js`` and ``build/homepage.css``).
