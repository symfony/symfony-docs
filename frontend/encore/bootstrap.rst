Using Bootstrap 4 CSS & JS
========================

Want to use Bootstrap (or something similar) in your project? No problem!
First, install it. To be able to customize things further, we'll install
``bootstrap`` and popper.js:

.. code-block:: terminal

    $ yarn add bootstrap popper.js --dev

or if you prefer to use npm:

.. code-block:: terminal

    $ npm install bootstrap popper.js --save-dev
    
Importing Bootstrap 4
------------------------

Now that ``bootstrap`` lives in your ``node_modules`` directory, you can
import it from any Sass or JavaScript file. For example, if you already have
a ``global.scss`` file, import it from there:

.. code-block:: css

    // assets/css/global.scss

    // customize some Bootstrap variables
    $primary: darken(#428bca, 20%);

    // the ~ allows you to reference things in node_modules
    @import "~bootstrap/scss/bootstrap";

That's it! This imports the ``node_modules/bootstrap/scss/bootstrap.scss``
file into ``global.scss``. You can even customize the Bootstrap variables first!

.. tip::

    If you don't need *all* of Bootstrap's features, you can include specific files
    in the ``bootstrap`` directory instead - e.g. ``~bootstrap/scss/alert``.

Importing Bootstrap JavaScript
------------------------------

Bootstrap JavaScript requires jQuery, so make sure you have this installed:

.. code-block:: terminal

    $ yarn add jquery --dev
    
.. code-block:: terminal

    $ npm install jquery --save-dev

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

    // app.js

    const $ = require('jquery');
    // JS is equivalent to the normal "bootstrap" package
    // no need to set this to a variable, just require it
    require('bootstrap');

    // or you can include specific pieces
    // require('bootstrap/js/dist/tooltip');
    // require('bootstrap/js/dist/popover');

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
