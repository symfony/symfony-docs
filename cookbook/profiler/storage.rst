.. index::
    single: Profiling; Storage Configuration

Switching the Profiler Storage
==============================

In Symfony versions previous to 3.0, profiles could be stored in files, databases,
services like Redis and Memcache, etc. Starting from Symfony 3.0, the only storage
mechanism with built-in support is the filesystem.

By default the profile stores the collected data in the ``%kernel.cache_dir%/profiler/``
directory. If you want to use another location to store the profiles, define the
``dsn`` option of the ``framework.profiler``:

.. code-block:: yaml

    # app/config/config.yml
    framework:
        profiler:
            dsn: 'file:/tmp/symfony/profiler'

You can also create your own profile storage service implementing the
:class:``Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface`` and
overriding the ``profiler.storage`` service.
