Using webpack-dev-server and HMR
================================

While developing, instead of using ``yarn encore dev --watch``, you can use the
`webpack-dev-server`_:

.. code-block:: terminal

    $ yarn encore dev-server

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

.. caution::

    Encore uses ``webpack-dev-server`` version 4, which at the time of Encore's
    1.0 release was still in beta and was not documented. See the `4.0 CHANGELOG`_
    for changes.

The ``dev-server`` command supports all the options defined by `webpack-dev-server`_.
You can set these options via command line options:

.. code-block:: terminal

    $ yarn encore dev-server --port 9000

You can also set these options using the ``Encore.configureDevServerOptions()``
method in your ``webpack.config.js`` file:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...

        .configureDevServerOptions(options => {
            options.https = {
                key: '/path/to/server.key',
                cert: '/path/to/server.crt',
            }
        })
    ;

.. versionadded:: 0.28.4

    The ``Encore.configureDevServerOptions()`` method was introduced in Encore 0.28.4.

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
    +         options.https = {
    +             pfx: path.join(process.env.HOME, '.symfony/certs/default.p12'),
    +         }
    +     })


.. caution::

    Make sure to **not** pass the ``--https`` flag at the command line when
    running ``encore dev-server``. This flag was required before 1.0, but now
    will cause your config to be overridden.

CORS Issues
-----------

If you experience issues related to CORS (Cross Origin Resource Sharing), set
the ``firewall`` option:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...

        .configureDevServerOptions(options => {
            options.firewall = false;
        })

Beware that `it's not recommended to disable the firewall`_ in general, but
here it's required to solve the CORS issue.

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
.. _`it's not recommended to disable the firewall`: https://webpack.js.org/configuration/dev-server/#devserverdisablehostcheck
.. _`4.0 CHANGELOG`: https://github.com/webpack/webpack-dev-server/blob/master/CHANGELOG.md#400-beta0-2020-11-27
