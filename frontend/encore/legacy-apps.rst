jQuery and Legacy Applications
==============================

Inside Webpack, when you require a module, it does *not* (usually) set a global variable.
Instead, it just returns a value:

.. code-block:: javascript

    // this loads jquery, but does *not* set a global $ or jQuery variable
    const $ = require('jquery');

In practice, this will cause problems with some outside libraries that *rely* on
jQuery to be global. It will be a problem if some of *your* JavaScript isn't being
processed through Webpack (e.g. you have some JavaScript in your templates).

Using Libraries that Expect jQuery to be Global
-----------------------------------------------

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

Internally, this ``autoProvidejQuery()`` method calls the ``autoProvideVariables()``
method from Encore. In practice, it's equivalent to doing:

.. code-block:: javascript

    Encore
        // you can use this method to provide other common global variables,
        // such as '_' for the 'underscore' library
        .autoProvideVariables({
            $: 'jquery',
            jQuery: 'jquery',
            'window.jQuery': 'jquery',
        })
        // ...
    ;

Accessing jQuery from outside of Webpack JavaScript Files
---------------------------------------------------------

If you also need to provide access to ``$`` and ``jQuery`` variables outside of
JavaScript files processed by Webpack (e.g. JavaScript that still lives in your
templates), you need to manually set these as global variables in some JavaScript
file that is loaded before your legacy code.

For example, you could define a ``common.js`` file that's processed by Webpack and
loaded on every page with the following content:

.. code-block:: javascript

    // require jQuery normally
    const $ = require('jquery');

    // create global $ and jQuery variables
    global.$ = global.jQuery = $;

.. tip::

    The ``global`` variable is a special way of setting things in the ``window``
    variable. In a web context, using ``global`` and ``window`` are equivalent,
    except that ``window.jQuery`` won't work when using ``autoProvidejQuery()``.
    In other words, use ``global``.
