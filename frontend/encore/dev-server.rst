Using webpack-dev-server and HMR
================================

While developing, instead of using ``yarn encore dev --watch``, you can use the
`webpack-dev-server`_:

.. code-block:: terminal

    $ yarn encore dev-server

This serves the built assets from a new server at ``http://localhost:8080`` (it does
not actually write any files to disk). This means your ``script`` and ``link`` tags
need to change to point to this.

If you're using the ``encore_entry_script_tags()`` and ``encore_entry_link_tags()``
Twig shortcuts (or are :ref:`processing your assets through entrypoints.json <load-manifest-files>`
in some other way), you're done: the paths in your templates will automatically point
to the dev server.

You can also pass options to the ``dev-server`` command: any options that are supported
by the normal `webpack-dev-server`_. For example:

.. code-block:: terminal

    $ yarn encore dev-server --https --port 9000

This will start a server at ``https://localhost:9000``.

Hot Module Replacement HMR
--------------------------

Encore *does* support `HMR`_, but only in some areas. To activate it, pass the ``--hot``
option:

.. code-block:: terminal

    $ ./node_modules/.bin/encore dev-server --hot

HMR currently works for :doc:`Vue.js </frontend/encore/vuejs>`, but does *not* work
for styles anywhere at this time.

For Hot Module Replacement, CORS-errors can appear (Cross Origin Resource Sharing). To handle this, add the --disable-host-check and --port options to your command:

.. code-block:: terminal

    $ yarn encore dev-server --port 8080 --disable-host-check --hot

Or, alternatively, you can add the options to your package.json

.. code-block:: package.json

        "dev-server": "encore dev-server --port 8080 --disable-host-check",

and run the yarn command as normal:

.. code-block:: terminal

    $ yarn dev-server --hot


.. _`webpack-dev-server`: https://webpack.js.org/configuration/dev-server/
.. _`HMR`: https://webpack.js.org/concepts/hot-module-replacement/
