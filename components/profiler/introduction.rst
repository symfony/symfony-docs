.. index::
   single: Profiler
   single: Components; Profiler

The Profiler Component
======================

    The Profiler component provides tools to collected and present a profile of executed code.

.. versionadded:: 2.8
    The Profiler component was introduced in Symfony 2.8. Previously, the classes
    were located in the HttpKernel component.

Installation
------------

You can install the component in many different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/profiler`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/Profiler).

Usage
-----

The Profiler component provides several tools to help you debug PHP code.
Enabling them all is as easy as it can get::

    use Symfony\Component\Profiler\Profiler;
    use Symfony\Component\Profiler\Storage\FileProfilerStorage;

    $storage = new FileProfilerStorage(__DIR__.'/cache/profiler');
    $profiler = new Profiler($storage);

    // $profile is an implementation of ProfileInterface.
    $profile = $profiler->profile();

    $profiler->save(
        $profile,
        array(
            'url' => http://localhost/',
            'ip' => '127.0.0.1',
            'method' => 'GET',
            'response_code' => 200,
            'profile_type' => 'http',
        )
    );

if your project makes use of the :doc:`EventDispatcher component </components/event_dispatcher/introduction>`, you can automate the profiling by using the corresponding
EventListeners :class:`Symfony\\Component\\Profiler\\EventListener\\HttpProfilerListener`.

.. caution::

    You should limit the profiler tools in a production environment to only profile on Exceptions as
    profile every request will generate a significant portion of data and increase the response time.

Collecting Data with DataCollectors
-----------------------------------

The Profiler assembles a Profile with data it gets from DataCollectors.

A good deal of Components already provide usefull DataCollectors:

* :doc:`Debug component </components/debug/introduction>`: :class:`Symfony\\Component\\Debug\\Profiler\\ExceptionDataCollector`
* :doc:`EventDispatcher component </components/event_dispatcher/introduction>`: :class:`Symfony\\Component\\EventDispatcher\\Profiler\\EventDataCollector`
* :doc:`Form component </components/form/introduction>`: :class:`Symfony\\Component\\Form\\Extension\\Profiler\\FormDataCollector`
* :doc:`HttpKernel component </components/http_kernel/introduction>`: :class:`Symfony\\Component\\HttpKernel\\Profiler\\RequestDataCollector` & :class:`Symfony\\Component\\HttpKernel\\Profiler\\RouteDataCollector`
* :doc:`Security component </components/security/introduction>`: :class:`Symfony\\Component\\Security\\Core\\Profiler\\SecurityDataCollector`
* :doc:`Translation component </components/translation/introduction>`: :class:`Symfony\\Component\\Translation\\Profiler\\TranslationDataCollector`
* :doc:`VarDumper component </components/var_dumper/introduction>`: :class:`Symfony\\Component\\VarDumper\\Profiler\\DumpDataCollector`
* `Monolog bridge`: :class:`Symfony\\Bridge\\Monolog\\Profiler\\LoggerDataCollector`
* `Twig bridge`: :class:`Symfony\\Bridge\\Twig\\Profiler\\TwigDataCollector`

.. _Packagist: https://packagist.org/packages/symfony/profiler
