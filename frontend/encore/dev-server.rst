Using webpack-dev-server and HMR
================================

While developing, instead of using ``encore dev --watch``, you can use the
`webpack-dev-server`_:

.. code-block:: terminal

    $ ./node_modules/.bin/encore dev-server

This serves the built assets from a new server at ``http://localhost:8080`` (it does
not actually write any files to disk). This means your ``script`` and ``link`` tags
need to change to point to this.

If you've activated the :ref:`manifest.json versioning <load-manifest-files>`
you're done: the paths in your templates will automatically point to the dev server.

You can also pass options to the ``dev-server`` command: any options that are supported
by the normal `webpack-dev-server`_. For example:

.. code-block:: terminal

    $ ./node_modules/.bin/encore dev-server --https --port 9000

This will start a server at ``https://localhost:9000``.

.. note::

    Hot module replacement is not currently supported.

.. _`webpack-dev-server`: https://webpack.js.org/configuration/dev-server/
