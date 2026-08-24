Logging Configuration Reference (MonologBundle)
===============================================

The MonologBundle integrates the Monolog :doc:`logging </logging>` library in
Symfony applications. All these options are configured under the ``monolog`` key
in your application configuration.

.. code-block:: terminal

    # displays the default config values defined by Symfony
    $ php bin/console config:dump-reference monolog

    # displays the actual config values used by your application
    $ php bin/console debug:config monolog

.. tip::

    For a full list of handler types and related configuration options, see
    `Monolog Configuration`_.

.. note::

    When the profiler is enabled, a handler is added to store the logs'
    messages in the profiler. The profiler uses the name "debug" so it
    is reserved and cannot be used in the configuration.

.. _`Monolog Configuration`: https://github.com/symfony/monolog-bundle/blob/4.x/src/DependencyInjection/Configuration.php
