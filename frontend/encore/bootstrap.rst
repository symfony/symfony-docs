Using Bootstrap CSS & JS
========================

Want to use Bootstrap (or something similar) in your project? No problem!
First, install it. To be able to customize things further, we'll install
``bootstrap-sass``:

.. code-block:: terminal

    $ yarn add bootstrap-sass --dev

Importing Bootstrap Sass
------------------------

Now that ``bootstrap-sass`` lives in your ``node_modules`` directory, you can
import it from any Sass or JavaScript file. For example, if you already have
a ``global.scss`` file, import it from there:

.. code-block:: css

    // assets/css/global.scss

    // customize some Bootstrap variables
    $brand-primary: darken(#428bca, 20%);

    // the ~ allows you to reference things in node_modules
    @import '~bootstrap-sass/assets/stylesheets/bootstrap';

That's it! This imports the ``node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss``
file into ``global.scss``. You can even customize the Bootstrap variables first!

.. tip::

    If you don't need *all* of Bootstrap's features, you can include specific files
    in the ``bootstrap`` directory instead - e.g. ``~bootstrap-sass/assets/stylesheets/bootstrap/alerts``.

After including ``bootstrap-sass``, your Webpack builds might become slow. To fix
this, you can use the ``resolve_url_loader`` option:

.. code-block:: diff

    // webpack.config.js
    Encore
    +     .enableSassLoader({
    +         resolve_url_loader: false
    +     })
    ;

This disables the ``resolve-url-loader`` in Webpack, which means that any
``url()`` paths in your Sass files must now be relative to the original source
entry file instead of whatever file you're inside of (see `Problems with url()`_).
To load Bootstrap, you'll need to override the path to its icons:

.. code-block:: diff

    // assets/css/global.scss

    + $icon-font-path: "~bootstrap-sass/assets/fonts/bootstrap/";

    + // set if you're also including font-awesome
    + // $fa-font-path: "~font-awesome/fonts";

    @import '~bootstrap-sass/assets/stylesheets/bootstrap';

Importing Bootstrap JavaScript
------------------------------

Bootstrap JavaScript requires jQuery, so make sure you have this installed:

.. code-block:: terminal

    $ yarn add jquery --dev

Next, make sure to call ``.autoProvidejQuery()`` in your ``webpack.config.js`` file:

.. code-block:: diff

    // webpack.config.js
    Encore
        // ...
    +     .autoProvidejQuery()
    ;

This is needed because Bootstrap expects jQuery to be available as a global
variable. Now, require bootstrap from any of your JavaScript files:

.. code-block:: javascript

    // main.js

    var $ = require('jquery');
    // JS is equivalent to the normal "bootstrap" package
    // no need to set this to a variable, just require it
    require('bootstrap-sass');

    // or you can include specific pieces
    // require('bootstrap-sass/javascripts/bootstrap/tooltip');
    // require('bootstrap-sass/javascripts/bootstrap/popover');

    $(document).ready(function() {
        $('[data-toggle="popover"]').popover();
    });

Thanks to ``autoProvidejQuery()``, you can require any other jQuery
plugins in a similar way:

.. code-block:: javascript

    // ...

    // require the JavaScript
    require('bootstrap-star-rating');
    // require 2 CSS files needed
    require('bootstrap-star-rating/css/star-rating.css');
    require('bootstrap-star-rating/themes/krajee-svg/theme.css');

.. _`Problems with url()`: https://github.com/webpack-contrib/sass-loader#problems-with-url
