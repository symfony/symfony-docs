Using webpack-dev-server and HMR
================================

While developing, instead of using ``yarn encore dev --watch``, you can use the
`webpack-dev-server`_:

.. code-block:: terminal

    # if you use the Yarn package manager
    $ yarn encore dev-server

    # if you use the npm package manager
    $ npm run dev-server

This builds and serves the front-end assets from a new server. This server runs at
``localhost:8080`` by default, meaning your build assets are available at ``localhost:8080/build``.
This server does not actually write the files to disk; instead it serves them from memory,
allowing for hot module reloading.

As a consequence, the ``link`` and ``script`` tags need to point to the new server. If you're using the
``encore_entry_script_tags()`` and ``encore_entry_link_tags()`` Twig shortcuts (or are
:ref:`processing your assets through entrypoints.json <load-manifest-files>` in some other way),
you're done: the paths in your templates will automatically point to the dev server.

dev-server Options
------------------

The ``dev-server`` command supports all the options defined by `webpack-dev-server`_.
You can set these options via command line options:

.. code-block:: terminal

    # if you use the Yarn package manager
    $ yarn encore dev-server --port 9000

    # if you use the npm package manager
    $ npm run dev-server -- --port 9000

You can also set these options using the ``Encore.configureDevServerOptions()``
method in your ``webpack.config.js`` file:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...

        .configureDevServerOptions(options => {
            options.server = {
                type: 'https',
                options: {
                    key: '/path/to/server.key',
                    cert: '/path/to/server.crt',
                }
            }
        })
    ;

Enabling HTTPS using the Symfony Web Server
-------------------------------------------

If you're using the :doc:`Symfony web server </setup/symfony_server>` locally with HTTPS,
you'll need to also tell the dev-server to use HTTPS. To do this, you can reuse the Symfony web
server SSL certificate:

.. code-block:: diff

      // webpack.config.js
      // ...
    + const path = require('path');

      Encore
          // ...

    +     .configureDevServerOptions(options => {
    +         options.server = {
    +             type: 'https',
    +             options: {
    +                 pfx: path.join(process.env.HOME, '.symfony5/certs/default.p12'),
    +             }
    +         }
    +     })

.. note::

    If you are using Node.js 17 or newer, you have to run the ``dev-server`` command with the
    ``--openssl-legacy-provider`` option:

    .. code-block:: terminal

        # if you use the Yarn package manager
        $ NODE_OPTIONS=--openssl-legacy-provider yarn encore dev-server

        # if you use the npm package manager
        $ NODE_OPTIONS=--openssl-legacy-provider npm run dev-server

CORS Issues
-----------

If you experience issues related to CORS (Cross Origin Resource Sharing), set
the following option:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...

        .configureDevServerOptions(options => {
            options.allowedHosts = 'all';
            // in older Webpack Dev Server versions, use this option instead:
            // options.firewall = false;
        })

Beware that this is not a recommended security practice in general, but here
it's required to solve the CORS issue.

Hot Module Replacement HMR
--------------------------

Hot module replacement is a superpower of the ``dev-server`` where styles and
(in some cases) JavaScript can automatically update without needing to reload
your page. HMR works automatically with CSS (as long as you're using the
``dev-server`` and Encore 1.0 or higher) but only works with some JavaScript
(like :doc:`Vue.js </frontend/encore/vuejs>`).

.. versionadded:: 1.0.0

    Before Encore 1.0, you needed to pass a ``--hot`` flag at the command line
    to enable HMR. You also needed to disable CSS extraction to enable HMR for
    CSS. That is no longer needed.

.. _`webpack-dev-server`: https://webpack.js.org/configuration/dev-server/
