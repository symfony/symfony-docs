Encore: Setting up your Project
===============================

After :doc:`installing Encore </frontend/encore/installation>`, your app already has one
CSS and one JS file, organized into an ``assets/`` directory:

* ``assets/js/app.js``
* ``assets/css/app.css``

With Encore, think of your ``app.js`` file as a standalone JavaScript
application: it will *require* all of the dependencies it needs (e.g. jQuery),
*including* any CSS. Your ``app.js`` file is already doing this with a special
``require`` function:

.. code-block:: javascript

    // assets/js/app.js
    // ...

    // var $ = require('jquery');

    require('../css/app.css');

    // ... the rest of your JavaScript...

Encore's job is simple: to read *all* of ``require`` statements and create one
final ``app.js`` (and ``app.css``) that contain *everything* your app needs. Of
course, Encore can do a lot more: minify files, pre-process Sass/LESS, support
ReactVue.js and a *lot* more.

Configuring Encore/Webpack
--------------------------

Everything in Encore is configured via a ``webpack.config.js`` file at the root
of your project. It already holds the basic config you need:

.. code-block:: javascript

    // webpack.config.js
    var Encore = require('@symfony/webpack-encore');

    Encore
        // directory where compiled assets will be stored
        .setOutputPath('web/build/')
        // public path used by the web server to access the output path
        .setPublicPath('/build')

        .addEntry('app', './assets/js/app.js')

        // ...
    ;

    // ...

They *key* part is ``addEntry()``: this tells Encore to load the ``assets/js/app.js``
file and follow *all* of the ``require`` statements. It will then package everything
together and - thanks to the first ``app`` argument - output final ``app.js`` and
``app.css`` files into the ``public/build`` directory.

.. _encore-build-assets:

To build the assets, run:

.. code-block:: terminal

    # compile assets once
    $ yarn encore dev

    # or, recompile assets automatically when files change
    $ yarn encore dev --watch

    # on deploy, create a production build
    $ yarn encore production

.. note::

    Stop and restart ``encore`` each time you update your ``webpack.config.js`` file.

Congrats! You now have two new files! Next, add a ``script`` and ``link`` tag
to the new, compiled assets (e.g. ``/build/app.css`` and ``/build/app.js``) to
your layout. In Symfony, use the ``asset()`` helper:

.. code-block:: twig

    {# templates/base.html.twig #}
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
        $('body').prepend('<h1>'+greet('john')+'</h1>');
    });

That's it! When you build your assets, jQuery and ``greet.js`` will automatically
be added to the output file (``app.js``).

The import and export Statements
--------------------------------

Instead of using ``require`` and ``module.exports`` like shown above, JavaScript
has an alternate syntax, which is a more accepted standard. Choose whichever you
want: they funtion identically:

To export values, use ``exports``:

.. code-block:: diff

    // assets/js/greet.js
    - module.exports = function(name) {
    + export default function(name) {
        return `Yo yo ${name} - welcome to Encore!`;
    };

To import values, use ``import``:

.. code-block:: diff

    // assets/js/app.js
    - var $ = require('jquery');
    + import $ from 'jquery';
    
    - require('../css/app.css');    
    + import '../css/app.css';

.. _multiple-javascript-entries:

Page-Specific JavaScript or CSS (Multiple Entries)
--------------------------------------------------

So far, you only have one final JavaScript file: ``app.js``. For simple apps or
SPA's (Single Page Applications), that might be fine! However, as your app grows,
you may want to have page-specific JavaScript or CSS (e.g. homepage, blog, store,
etc.). To handle this, add a new "entry" for each page that needs custom JavaScript
or CSS:

.. code-block:: diff

    Encore
        // ...
        .addEntry('app', './assets/js/app.js')
    +     .addEntry('homepage', './assets/js/homepage.js')
    +     .addEntry('blog', './assets/js/blog.js')
    +     .addEntry('store', './assets/js/store.js')
        // ...

Encore will now render new ``homepage.js``, ``blog.js`` and ``store.js`` files.
Add a ``script`` tag to each of these only on the page where they are needed.

.. tip::

    Remember to restart Encore each time you update your ``webpack.config.js`` file.

If any entry requires CSS/Sass files (e.g. ``homepage.js`` requires
``assets/css/homepage.scss``), a CSS file will *also* be output (e.g. ``build/homepage.css``).
Add a ``link`` to the page where that CSS is needed.

To avoid duplicating the same code in different entry files, see
:doc:`create a shared entry </frontend/encore/shared-entry>`.

Using Sass
----------

Instead of using plain CSS you can also use Sass. To use Sass, rename
the ``app.css`` file to ``app.scss``. Update the ``require`` statement:

.. code-block:: diff

    // assets/js/app.js
    - require('../css/app.css');
    + require('../css/app.scss');

Then, tell Enecore to enable the Sass pre-processor:

.. code-block:: diff

    // webpack.config.js
    Encore
        // ...

    +    .enableSassLoader()
    ;

Using ``enableSassLoader()`` requires to install additional packages, but Encore
will tell you *exactly* which ones when running it. Encore also supports
LESS and Stylus. See :doc:`/frontend/encore/css-preprocessors`.

Compiling Only a CSS File
-------------------------

To compile CSS together, you should generally follow the pattern above: use ``addEntry()``
to point to a JavaScript file, then require the CSS needed from inside of that.
However, *if* you want to only compile a CSS file, that's also possible via
``addStyleEntry()``:

.. code-block:: javascript

    // webpack/config.js
    Encore
        // ...

        .addStyleEntry('some_page', './assets/css/some_page.css')
    ;

This will output a new ``some_page.css``.

Keep Going!
-----------

Go back to the :ref:`List of Encore Articles <encore-toc>` to learn more and add new features.
