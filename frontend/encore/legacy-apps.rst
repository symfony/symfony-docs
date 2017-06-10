jQuery and Legacy Applications
==============================

Some legacy JavaScript applications use programming practices that don't play
well with the new practices promoted by Webpack. The most common of these
problems is using code (e.g. jQuery plugins) that assume that jQuery is already
available via the the ``$`` or ``jQuery`` global variables. If those variables
are not defined, you'll get these errors:

.. code-block:: text

    Uncaught ReferenceError: $ is not defined at [...]
    Uncaught ReferenceError: jQuery is not defined at [...]

Instead of rewriting everything, Encore allows for a different solution. Thanks
to the ``autoProvidejQuery()`` method, whenever a JavaScript file uses the ``$``
or ``jQuery`` variables, Webpack automatically requires ``jquery`` and creates
those variables for you.

So, when working with legacy applications, you may need to add the following to
``webpack.config.js``:

.. code-block:: diff

    Encore
        // ...
    +     .autoProvidejQuery()
    ;

Internally, this ``autoProvidejQuery()`` method uses the ``autoProvideVariables()``
method from Encore. In practice, it's equivalent to doing:

.. code-block:: javascript

    Encore
        // you can use this method to provide other common global variables,
        // such as '_' for the 'underscore' library
        .autoProvideVariables({
            $: 'jquery',
            jQuery: 'jquery'
        })
        // ...
    ;

If you also need to provide access to ``$`` and ``jQuery`` variables outside of
JavaScript files processed by Webpack, you must create the global variables
yourself in some file loaded before the legacy JavaScript code. For example, you
can define a ``common.js`` file processed by Webpack and loaded in every page
with the following content:

.. code-block:: javascript

    window.$ = window.jQuery = require('jquery');
