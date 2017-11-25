.. index::
    single: Console; Usage

How to Use the Console
======================

The :doc:`/components/console/usage` page of the components documentation looks
at the global console options. When you use the console as part of the full-stack
framework, some additional global options are available as well.

Console commands run in the environment defined in the ``APP_ENV`` variable of
the ``.env`` file, which is ``dev`` by default. The result of some commands will
be different depending on the environment (e.g. the ``cache:clear`` command
clears the cache for the given environment only). To run the command in other
environment, edit the value of ``APP_ENV``.

In addition to changing the environment, you can also choose to disable debug
mode. This can be useful where you want to run commands in the ``dev``
environment but avoid the performance hit of collecting debug data. To do that,
set the value of the ``APP_DEBUG`` env var to ``0`` in the same ``.env`` file.
