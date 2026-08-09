profiler
~~~~~~~~

collect
.......

**type**: ``boolean`` **default**: ``true``

This option configures the way the profiler behaves when it is enabled. If set
to ``true``, the profiler collects data for all requests. If you want to only
collect information on-demand, you can set the ``collect`` flag to ``false`` and
activate the data collectors manually::

    $profiler->enable();

collect_parameter
.................

**type**: ``string`` **default**: ``null``

This specifies the name of a query parameter, a body parameter or a request attribute
used to enable or disable collection of data by the profiler for each request.
Combine it with the ``collect`` option to enable/disable the profiler on demand:

* If the ``collect`` option is set to ``true`` but this parameter exists in a
  request and has any value other than ``true``, ``yes``, ``on`` or ``1``, the
  request data will not be collected;
* If the ``collect`` option is set to ``false``, but this parameter exists in a
  request and has value of ``true``, ``yes``, ``on`` or ``1``, the request data
  will be collected.

.. _collect_serializer_data:

collect_serializer_data
.......................

**type**: ``boolean`` **default**: ``false``

When this option is ``true``, all normalizers and encoders are
decorated by traceable implementations that collect profiling information about them.

.. deprecated:: 7.3

    Setting the ``collect_serializer_data`` option to ``false`` is deprecated
    since Symfony 7.3.

.. _profiler-dsn:

dsn
...

**type**: ``string`` **default**: ``file:%kernel.cache_dir%/profiler``

The DSN where to store the profiling information.

.. _reference-profiler-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``false``

The profiler can be enabled by setting this option to ``true``. When you
install it using Symfony Flex, the profiler is enabled in the ``dev``
and ``test`` environments.

.. note::

    The profiler works independently from the Web Developer Toolbar, see
    the :doc:`WebProfilerBundle configuration </reference/configuration/web_profiler>`
    on how to disable/enable the toolbar.

only_exceptions
...............

**type**: ``boolean`` **default**: ``false``

When this is set to ``true``, the profiler will only be enabled when an
exception is thrown during the handling of the request.

.. _only_master_requests:

only_main_requests
..................

**type**: ``boolean`` **default**: ``false``

When this is set to ``true``, the profiler will only be enabled on the main
requests (and not on the subrequests).
