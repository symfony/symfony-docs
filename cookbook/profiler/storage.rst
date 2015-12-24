.. index::
    single: Profiling; Storage Configuration

Switching the Profiler Storage
==============================

By default the profile stores the collected data in files in the ``%kernel.cache_dir%/profiler/`` directory.
You can control the storage by implementing the ``Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface`` in 
your own service and override the ``profiler.storage`` service.
