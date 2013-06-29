.. index::
   single: Stopwatch
   single: Components; Stopwatch

The Stopwatch Component
=======================

    Stopwatch component provides a way to profile code.

.. versionadded:: 2.2
    The Stopwatch Component is new to Symfony 2.2. Previously, the ``Stopwatch``
    class was located in the ``HttpKernel`` component.

Installation
------------

You can install the component in two different ways:

* Use the official Git repository (https://github.com/symfony/Stopwatch);
* :doc:`Install it via Composer</components/using_components>` (``symfony/stopwatch`` on `Packagist`_).

Usage
-----

The Stopwatch component provides an easy and consistent way to measure execution
time of certain parts of code so that you don't constantly have to parse
microtime by yourself. Instead, use the simple
:class:`Symfony\\Component\\Stopwatch\\Stopwatch` class::

    use Symfony\Component\Stopwatch\Stopwatch;

    $stopwatch = new Stopwatch();
    // Start event named 'eventName'
    $stopwatch->start('eventName');
    // ... some code goes here
    $event = $stopwatch->stop('eventName');

You can also provide a category name to an event::

    $stopwatch->start('eventName', 'categoryName');

You can consider categories as a way of tagging events. For example, the
Symfony Profiler tool uses categories to nicely color-code different events.

Periods
-------

As you know from the real world, all stopwatches come with two buttons:
one to start and stop the stopwatch, and another to measure the lap time.
This is exactly what the :method:`Symfony\\Component\\Stopwatch\\Stopwatch::lap``
method does::

    $stopwatch = new Stopwatch();
    // Start event named 'foo'
    $stopwatch->start('foo');
    // ... some code goes here
    $stopwatch->lap('foo');
    // ... some code goes here
    $stopwatch->lap('foo');
    // ... some other code goes here
    $event = $stopwatch->stop('foo');

Lap information is stored as "periods" within the event. To get lap information
call::

    $event->getPeriods();

In addition to periods, you can get other useful information from the event object.
For example::

    $event->getCategory();      // Returns the category the event was started in
    $event->getOrigin();        // Returns the event start time in milliseconds
    $event->ensureStopped();    // Stops all periods not already stopped
    $event->getStartTime();     // Returns the start time of the very first period
    $event->getEndTime();       // Returns the end time of the very last period
    $event->getDuration();      // Returns the event duration, including all periods
    $event->getMemory();        // Returns the max memory usage of all periods

Sections
--------

Sections are a way to logically split the timeline into groups. You can see
how Symfony uses sections to nicely visualize the framework lifecycle in the
Symfony Profiler tool. Here is a basic usage example using sections::

    $stopwatch = new Stopwatch();

    $stopwatch->openSection();
    $stopwatch->start('parsing_config_file', 'filesystem_operations');
    $stopwatch->stopSection('routing');

    $events = $stopwatch->getSectionEvents('routing');

You can reopen a closed section by calling the :method:`Symfony\\Component\\Stopwatch\\Stopwatch::openSection``
method and specifying the id of the section to be reopened::

    $stopwatch->openSection('routing');
    $stopwatch->start('building_config_tree');
    $stopwatch->stopSection('routing');

.. _Packagist: https://packagist.org/packages/symfony/stopwatch
