jQuery Plugins and Legacy Applications with Webpack Encore
==========================================================

Inside Webpack, when you require a module, it does *not* (usually) set a global variable.
Instead, it just returns a value:

.. code-block:: javascript

    // this loads jquery, but does *not* set a global $ or jQuery variable
    const $ = require('jquery');

In practice, this will cause problems with some outside libraries that *rely* on
jQuery to be global *or* if *your* JavaScript isn't being processed through Webpack
(e.g. you have some JavaScript in your templates) and you need jQuery. Both will
cause similar errors:

.. code-block:: text

    Uncaught ReferenceError: $ is not defined at [...]
    Uncaught ReferenceError: jQuery is not defined at [...]

The fix depends on what code is causing the problem.

.. _encore-autoprovide-jquery:

Fixing jQuery Plugins that Expect jQuery to be Global
-----------------------------------------------------

jQuery plugins often expect that jQuery is already available via the ``$`` or
``jQuery`` global variables. To fix this, call ``autoProvidejQuery()`` from your
``webpack.config.js`` file:

.. code-block:: diff

      // webpack.config.js
      Encore
          // ...
    +     .autoProvidejQuery()
      ;

After restarting Encore, Webpack will look for all uninitialized ``$`` and ``jQuery``
variables and automatically require ``jquery`` and set those variables for you.
It "rewrites" the "bad" code to be correct.

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

If *your* code needs access to ``$`` or ``jQuery`` and you are inside of a file
that's processed by Webpack/Encore, you should remove any "$ is not defined" errors
by requiring jQuery: ``var $ = require('jquery')``.

But if you also need to provide access to ``$`` and ``jQuery`` variables outside of
JavaScript files processed by Webpack (e.g. JavaScript that still lives in your
templates), you need to manually set these as global variables in some JavaScript
file that is loaded before your legacy code.

For example, in your ``app.js`` file that's processed by Webpack and loaded on every
page, add:

.. code-block:: diff

      // app.js

      // require jQuery normally
      const $ = require('jquery');

    + // create global $ and jQuery variables
    + global.$ = global.jQuery = $;

The ``global`` variable is a special way of setting things in the ``window``
variable. In a web context, using ``global`` and ``window`` are equivalent,
except that ``window.jQuery`` won't work when using ``autoProvidejQuery()``.
In other words, use ``global``.

Additionally, be sure to set the ``script_attributes.defer`` option to ``false``
in your ``webpack_encore.yaml`` file:

.. code-block:: yaml

    # config/packages/webpack_encore.yaml
    webpack_encore:
        # ...
        script_attributes:
            defer: false

This will make sure there is *not* a ``defer`` attribute on your ``script``
tags. For more information, see `Moving <script> inside <head> and the "defer" Attribute`_

.. _`Moving <script> inside <head> and the "defer" Attribute`: https://symfony.com/blog/moving-script-inside-head-and-the-defer-attribute
