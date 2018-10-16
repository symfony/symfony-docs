.. index::
    single: Configuration reference; Framework

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

.. note::

    When using XML, you must use the ``http://symfony.com/schema/dic/debug``
    namespace and the related XSD schema is available at:
    ``http://symfony.com/schema/dic/debug/debug-1.0.xsd``

Configuration
-------------

.. class:: list-config-options

* `dump_destination`_
* `max_items`_
* `min_depth`_
* `max_string_length`_

max_items
~~~~~~~~~

**type**: ``integer`` **default**: ``2500``

This is the maximum number of items to dump. Setting this option to ``-1``
disables the limit.

min_depth
~~~~~~~~~

**type**: ``integer`` **default**: ``1``

Configures the minimum tree depth until which all items are guaranteed to
be cloned. After this depth is reached, only ``max_items`` items will be
cloned. The default value is ``1``, which is consistent with older Symfony
versions.

max_string_length
~~~~~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``-1``

This option configures the maximum string length before truncating the
string. The default value (``-1``) means that strings are never truncated.

.. _configuration-debug-dump_destination:

dump_destination
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

Configures the output destination of the dumps.

By default, the dumps are shown in the toolbar. Since this is not always
possible (e.g. when working on a JSON API), you can have an alternate output
destination for dumps. Typically, you would set this to ``php://stderr``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/debug.yaml
        debug:
           dump_destination: php://stderr

    .. code-block:: xml

        <!-- config/packages/debug.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/debug"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:debug="http://symfony.com/schema/dic/debug"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/debug http://symfony.com/schema/dic/debug/debug-1.0.xsd">

            <debug:config dump-destination="php://stderr" />
        </container>

    .. code-block:: php

        // config/packages/debug.php
        $container->loadFromExtension('debug', array(
           'dump_destination' => 'php://stderr',
        ));

Configure it to ``"tcp://%env(VAR_DUMPER_SERVER)%"`` in order to use the :ref:`ServerDumper feature <var-dumper-dump-server>`.
