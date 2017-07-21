First Example
=============

Imagine you have a simple project with one CSS and one JS file, organized into
an ``assets/`` directory:

* ``assets/js/app.js``
* ``assets/css/app.scss``

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

        // will output as web/build/js/app.js
        .addEntry('js/app', './assets/js/app.js')

        // will output as web/build/css/app.css
        .addStyleEntry('css/app', './assets/css/app.scss')

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
to the new, compiled assets (e.g. ``/build/css/app.css`` and ``/build/js/app.js``).
In Symfony, use the ``asset()`` helper:

.. code-block:: twig

    {# base.html.twig #}
    <!DOCTYPE html>
    <html>
        <head>
            <!-- ... -->
            <link rel="stylesheet" href="{{ asset('build/css/app.css') }}">
        </head>
        <body>
            <!-- ... -->
            <script src="{{ asset('build/js/app.js') }}"></script>
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

    // assets/js/main.js

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

Requiring CSS Files from JavaScript
-----------------------------------

Above, you created an entry called ``js/app`` that pointed to ``app.js``:

.. code-block:: javascript

    Encore
        // ...
        .addEntry('js/app', './assets/js/app.js')
    ;

Once inside ``app.js``, you can even require CSS files:

.. code-block:: javascript

    // assets/js/app.js
    // ...

    // a CSS file with the same name as the entry js will be output
    require('../css/app.scss');

Now, both an ``app.js`` **and** an ``app.css`` file will be created in the
``build/js/`` dir. You'll need to add a link tag to the ``app.css`` file in your
templates:

.. code-block:: diff

    <link rel="stylesheet" href="{{ asset('build/css/app.css') }}">
    + <link rel="stylesheet" href="{{ asset('build/js/app.css') }}">

This article follows the traditional setup where you have just one main CSS file
and one main JavaScript file. In lots of modern JavaScript applications, it's
common to have one "entry" for each important section (homepage, blog, store, etc.)

In those applications, it's better to just add JavaScript entries in the Webpack
configuration file and import the CSS files from the JavaScript entries, as
shown above:

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
