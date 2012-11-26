.. index::
   single: Stopwatch
   single: Components; Stopwatch

The Stopwatch Component
=======================

    Stopwatch component provides a way to profile code.

Installation
------------

You can install the component in two different ways:

* Use the official Git repository (https://github.com/symfony/Stopwatch);
* :doc:`Install it via Composer</components/using_components>` (``symfony/stopwatch`` on `Packagist`_).

Usage
-----

The Stopwatch component provides an easy and consistent way to measure execution
time of certain parts of code, so that you don't constantly have to parse
microtime by yourself. The basic usage is as simple as this::

    use Symfony\Component\Stopwatch\Stopwatch;

    $stopwatch = new Stopwatch();
    // Start event named 'eventName'
    $stopwatch->start('eventName');
    // some code goes here
    $event = $stopwatch->stop('eventName');

You also can provide a category name to an event::

    $stopwatch->start('eventName', 'categoryName');

You can consider categories as a way of tagging events. The Symfony Profiler
tool uses categories to nicely colorcode different events. 

Periods
-------

As we all know from the real world, all stopwatches come with two buttons.
One for starting and stopping the stopwatch, another to measure the lap time.
And that's exactly what lap method does. ::

    $stopwatch = new Stopwatch();
    // Start event named 'foo'
    $stopwatch->start('foo');
    // some code goes here
    $stopwatch->lap('foo');
    // some code goes here
    $stopwatch->lap('foo');
    // some other code goes here
    $event = $stopwatch->stop('foo');

Lap information is stored in periods within the event. To get lap information aka periods call ::

    $event->getPeriods();

Besides getting periods, we can get other useful information from the event object. E.g::

    $event->getCategory();      // Returns the category the evenent was started in
    $event->getOrigin();        // Returns the start time of the Event in milliseconds
    $event->ensureStopped();    // Stops all non already stopped periods
    $event->getStartTime();     // Returns the start of the very first period
    $event->getEndTime();       // Returns the end time of the very last period
    $event->getDuration();      // Gets the duration (including all periods) of the event
    $event->getMemory();        // Gets the max memory usage of all periods


Sections
--------

Sections are a way to logically split the timeline into groups. You can see
how Symfony uses sections to nicely visualize framework lifecycle in the
Symfony Profiler tool. Here is a basic usage of sections.::

    $stopwatch = new Stopwatch();

    $stopwatch->openSection();
    $stopwatch->start('parisng_config_file', 'filesystem_operations');
    $stopwatch->stopSection('routing');

    $events = $stopwatch->getSectionEvents('section');


You can reopen a closed section by calling the openSection method and specifying
an id of the section to be reopened. e.g.::

    $stopwatch->openSection('routing');
    $stopwatch->start('building_config_tree');
    $stopwatch->stopSection('routing');

.. _Packagist: https://packagist.org/packages/symfony/stopwatch
