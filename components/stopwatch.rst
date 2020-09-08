.. index::
   single: Stopwatch
   single: Components; Stopwatch

The Stopwatch Component
=======================

    The Stopwatch component provides a way to profile code.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/stopwatch

.. include:: /components/require_autoload.rst.inc

Usage
-----

The Stopwatch component provides a consistent way to measure execution
time of certain parts of code so that you don't constantly have to parse
:phpfunction:`microtime` by yourself. Instead, use the
:class:`Symfony\\Component\\Stopwatch\\Stopwatch` class::

    use Symfony\Component\Stopwatch\Stopwatch;

    $stopwatch = new Stopwatch();

    // starts event named 'eventName'
    $stopwatch->start('eventName');

    // ... run your code here

    $event = $stopwatch->stop('eventName');
    // you can convert $event into a string for a quick summary
    // e.g. (string) $event = '4.50 MiB - 26 ms'

The :class:`Symfony\\Component\\Stopwatch\\StopwatchEvent` object can be retrieved
from the  :method:`Symfony\\Component\\Stopwatch\\Stopwatch::start`,
:method:`Symfony\\Component\\Stopwatch\\Stopwatch::stop`,
:method:`Symfony\\Component\\Stopwatch\\Stopwatch::lap` and
:method:`Symfony\\Component\\Stopwatch\\Stopwatch::getEvent` methods.
The latter should be used when you need to retrieve the duration of an event
while it is still running.

.. tip::

    By default, the stopwatch truncates any sub-millisecond time measure to ``0``,
    so you can't measure microseconds or nanoseconds. If you need more precision,
    pass ``true`` to the ``Stopwatch`` class constructor to enable full precision::

        $stopwatch = new Stopwatch(true);

The stopwatch can be reset to its original state at any given time with the
:method:`Symfony\\Component\\Stopwatch\\Stopwatch::reset` method, which deletes
all the data measured so far.

You can also provide a category name to an event::

    $stopwatch->start('eventName', 'categoryName');

You can consider categories as a way of tagging events. For example, the
Symfony Profiler tool uses categories to nicely color-code different events.

.. tip::

    Read :ref:`this article <profiler-timing-execution>` to learn more about
    integrating the Stopwatch component into the Symfony profiler.

Periods
-------

As you know from the real world, all stopwatches come with two buttons:
one to start and stop the stopwatch, and another to measure the lap time.
This is exactly what the :method:`Symfony\\Component\\Stopwatch\\Stopwatch::lap`
method does::

    $stopwatch = new Stopwatch();
    // starts event named 'foo'
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

    $event->getCategory();   // returns the category the event was started in
    $event->getOrigin();     // returns the event start time in milliseconds
    $event->ensureStopped(); // stops all periods not already stopped
    $event->getStartTime();  // returns the start time of the very first period
    $event->getEndTime();    // returns the end time of the very last period
    $event->getDuration();   // returns the event duration, including all periods
    $event->getMemory();     // returns the max memory usage of all periods

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

You can reopen a closed section by calling the :method:`Symfony\\Component\\Stopwatch\\Stopwatch::openSection`
method and specifying the id of the section to be reopened::

    $stopwatch->openSection('routing');
    $stopwatch->start('building_config_tree');
    $stopwatch->stopSection('routing');
