.. index::
   single: Profiling; Stopwatch

How to show custom timing
=========================

Inject :class:`Symfony\\Component\\Stopwatch\\Stopwatch` by autowiring. Or do ``$stopwatch = $this->get('debug.stopwatch');`` in a Controller.

All timing mesurements done by this stopwatch are shown in the profiler on the page performance.

short example::

    $stopwatch->start('anEvent', 'customCategory');
    $stopwatch->lapse('anEvent');
    $stopwatch->stop('anEvent');

:doc:`/components/stopwatch` explains the methods in more details.
