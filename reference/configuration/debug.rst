.. index::
    single: Configuration reference; Framework

DebugBundle Configuration ("debug")
===================================

The DebugBundle allows greater integration of the
:doc:`VarDumper component </components/var_dumper/introduction>` in the
Symfony full-stack framework and can be configured under the ``debug`` key
in your application configuration. When using XML, you must use the
``http://symfony.com/schema/dic/debug`` namespace.

.. tip::

   The XSD schema is available at
   ``http://symfony.com/schema/dic/debug/debug-1.0.xsd``.

Configuration
-------------

* `max_items`_
* `max_string_length`_
* `dump_destination`_

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

dump_destination
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

Configures the output destination of the dumps.

By default, the dumps are shown in the toolbar. Since this is not always
possible (e.g. when working on a JSON API), you can have an alternate output
destination for dumps. Typically, you would set this to ``php://stderr``:

.. configuration-block::

    .. code-block:: yaml

        debug:
           dump_destination: php://stderr

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/debug"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:debug="http://symfony.com/schema/dic/debug"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/debug http://symfony.com/schema/dic/debug/debug-1.0.xsd">

            <debug:config dump-destination="php://stderr" />
        </container>

    .. code-block:: php

        $container->loadFromExtension('debug', array(
           'dump_destination' => 'php://stderr',
        ));
