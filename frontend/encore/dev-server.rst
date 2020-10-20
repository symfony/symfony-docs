Using webpack-dev-server and HMR
================================

While developing, instead of using ``yarn encore dev --watch``, you can use the
`webpack-dev-server`_:

.. code-block:: terminal

    $ yarn encore dev-server

This builds and serves the front-end assets from a new server. This server runs at 
``localhost:8080`` by default, meaning your build assets are available at ``localhost:8080/build``. 
This server does not actually write the files to disk; instead it servers them from memory, 
allowing for hot module reloading.

As a consequence, the ``link`` and ``script`` tags need to point to the new server. If you're using the 
``encore_entry_script_tags()`` and ``encore_entry_link_tags()`` Twig shortcuts (or are 
:ref:`processing your assets through entrypoints.json <load-manifest-files>` in some other way), 
you're done: the paths in your templates will automatically point to the dev server.

Enabling HTTPS using the Symfony Web Server
-------------------------------------------

If you're using the :doc:`Symfony web server </setup/symfony_server>` locally with HTTPS,
you'll need to also tell the dev-server to use HTTPS. To do this, you can reuse the Symfony web
server SSL certificate:

.. code-block:: terminal

    # Unix-based systems
    $ yarn dev-server --https --pfx=$HOME/.symfony/certs/default.p12

    # Windows
    $ encore dev-server --https --pfx=%UserProfile%\.symfony\certs\default.p12

dev-server Options
------------------

The ``dev-server`` command supports all the options defined by `webpack-dev-server`_.
You can set these options via command line options:

.. code-block:: terminal

    $ yarn encore dev-server --https --port 9000

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

Hot Module Replacement HMR
--------------------------

Encore *does* support `HMR`_ for :doc:`Vue.js </frontend/encore/vuejs>`, but
does *not* work for styles anywhere at this time. To activate it, pass the ``--hot``
option:

.. code-block:: terminal

    $ ./node_modules/.bin/encore dev-server --hot

If you want to use SSL with self-signed certificates, add the ``--https``,
``--pfx=``, and  ``--allowed-hosts`` options to the ``dev-server`` command in
the ``package.json`` file:

.. code-block:: diff

    {
        ...
        "scripts": {
    -        "dev-server": "encore dev-server",
    +        "dev-server": "encore dev-server --https --pfx=$HOME/.symfony/certs/default.p12 --allowed-hosts=mydomain.wip",
            ...
        }
    }

If you experience issues related to CORS (Cross Origin Resource Sharing), add
the ``--disable-host-check`` and ``--port`` options to the ``dev-server``
command in the ``package.json`` file:

.. code-block:: diff

    {
        ...
        "scripts": {
    -        "dev-server": "encore dev-server",
    +        "dev-server": "encore dev-server --port 8080 --disable-host-check",
            ...
        }
    }

.. caution::

    Beware that `it's not recommended to disable host checking`_ in general, but
    here it's required to solve the CORS issue.


.. _`webpack-dev-server`: https://webpack.js.org/configuration/dev-server/
.. _`HMR`: https://webpack.js.org/concepts/hot-module-replacement/
.. _`it's not recommended to disable host checking`: https://webpack.js.org/configuration/dev-server/#devserverdisablehostcheck
