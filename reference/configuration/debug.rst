Debug Configuration Reference (DebugBundle)
===========================================

The DebugBundle integrates the :doc:`VarDumper component </components/var_dumper>`
in Symfony applications. All these options are configured under the ``debug``
key in your application configuration.

.. code-block:: terminal

    # displays the default config values defined by Symfony
    $ php bin/console config:dump-reference debug

    # displays the actual config values used by your application
    $ php bin/console debug:config debug

    # displays the config values used by your application and replaces the
    # environment variables with their actual values
    $ php bin/console debug:config --resolve-env debug

.. _configuration-debug-dump_destination:

dump_destination
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

Configures the output destination of the dumps.

By default, dumps are shown in the WebDebugToolbar when returning HTML.
Since this is not always possible (e.g. when working on a JSON API),
you can have an alternate output destination for dumps.
Typically, you would set this to ``php://stderr``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/debug.yaml
        debug:
            dump_destination: php://stderr

    .. code-block:: php

        // config/packages/debug.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'debug' => [
                'dump_destination' => 'php://stderr',
            ],
        ]);

Configure it to ``"tcp://%env(VAR_DUMPER_SERVER)%"`` in order to use the :ref:`ServerDumper feature <var-dumper-dump-server>`.

max_items
~~~~~~~~~

**type**: ``integer`` **default**: ``2500``

This is the maximum number of items to dump. Setting this option to ``-1``
disables the limit.

max_string_length
~~~~~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``-1``

This option configures the maximum string length before truncating the
string. The default value (``-1``) means that strings are never truncated.

min_depth
~~~~~~~~~

**type**: ``integer`` **default**: ``1``

Configures the minimum tree depth until which all items are guaranteed to
be cloned. After this depth is reached, only ``max_items`` items will be
cloned. The default value is ``1``, which is consistent with older Symfony
versions.

theme
~~~~~

**type**: ``string`` **default**: ``dark``

Changes the color of the ``dump()`` output when rendered directly on the
templating. The value of this option can be ``dark`` or ``light``.
