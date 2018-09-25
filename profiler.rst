Profiler
========

The profiler is a powerful **development tool** that gives detailed information
about the execution of any request.

**Never** enable the profiler in production environments as it will lead to
major security vulnerabilities in your project.

Installation
------------

In applications using :doc:`Symfony Flex </setup/flex>`, run this command to
install the profiler before using it:

.. code-block:: terminal

    $ composer require --dev symfony/profiler-pack

.. toctree::
    :maxdepth: 1

    profiler/data_collector
    profiler/profiling_data
    profiler/matchers
    profiler/wdt_follow_ajax
