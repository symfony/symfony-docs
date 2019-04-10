Configuring Watching Options and Polling
========================================

Encore provides the method ``configureWatchOptions()`` to configure `Watching Options`_ when running ``encore dev --watch`` or ``encore dev-server``:

.. code-block:: javascript

    Encore.configureWatchOptions(function(watchOptions) {
        // enable polling and check for changes every 250ms
        // polling is useful when running Encore inside a Virtual Machine
        watchOptions.poll = 250;
    });

.. _`Watching Options`: https://webpack.js.org/configuration/watch/#watchoptions
