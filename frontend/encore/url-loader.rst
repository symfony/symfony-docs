Using the URL Loader
===============================

The `URL Loader`_ allows you to convert files into Data URLs and embed them
directly into the compiled version of your code. This can be useful if you
want to avoid extra network requests for some of the ``url()`` calls present
in your CSS files.

In Encore that loader is disabled by default, but you can easily enable it for
images and fonts.

First, add the loader to your project:

.. code-block:: terminal

    $ yarn add --dev url-loader

Then enable it in your ``webpack.config.js``:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...
        .configureUrlLoader({
            fonts: { limit: 4096 },
            images: { limit: 4096 }
        })
    ;

Every fonts and images files having a size below or equal to 4 KB will now be
inlined directly where they are required. If their size is over 4 KB the default
behavior will be used instead. You can change that threshold by modifying the
``limit`` option.

You can also use all of the other options supported by the `URL Loader`_.

If you wish to disable that loader for either images or fonts simply remove the
corresponding key from the object that is passed to the ``configureUrlLoader()``
method.

.. _`URL loader`: https://github.com/webpack-contrib/url-loader
