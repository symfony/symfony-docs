Profiler
========

Symfony provides a powerful profiler to get detailed information about the
execution of any request.

Installation
------------

In applications using :doc:`Symfony Flex </setup/flex>`, run this command to
install the profiler before using it:

.. code-block:: terminal

    $ composer require profiler --dev

.. toctree::
    :maxdepth: 1

    profiler/data_collector
    profiler/profiling_data
    profiler/matchers
    profiler/storage
    profiler/wdt_follow_ajax
