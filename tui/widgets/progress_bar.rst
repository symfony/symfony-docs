ProgressBarWidget
=================

``ProgressBarWidget`` renders an animated progress bar with
configurable format, characters and placeholders. It supports both
determinate (known total) and indeterminate (unknown total) modes.

When to Use
-----------

Use ``ProgressBarWidget`` when you can measure or estimate the
progress of an operation: file downloads, batch processing, indexing,
etc. For operations without measurable progress, use :doc:`loader`
instead.

Basic Usage
-----------

Determinate mode (known total)::

    use Symfony\Component\Tui\Widget\ProgressBarWidget;

    $progress = new ProgressBarWidget(max: 100);
    $tui->add($progress);
    $progress->start();

    // In your processing loop:
    $progress->advance();     // +1 step
    $progress->advance(10);   // +10 steps
    $progress->setProgress(50); // jump to step 50

    $progress->finish();      // complete the bar

Indeterminate mode (unknown total)::

    $progress = new ProgressBarWidget(); // max: 0
    $progress->start();
    $progress->advance(); // increments the counter without a percentage

Format Strings
--------------

The format string controls what is displayed. Several built-in formats
are available as class constants:

``FORMAT_NORMAL``
    ``3/10 [━━━━━━━━━━━━━━━━]  30%``

``FORMAT_VERBOSE``
    ``3/10 [━━━━━━━━━━━━━━━━]  30% 0:05``

``FORMAT_VERY_VERBOSE``
    ``3/10 [━━━━━━━━━━━━━━━━]  30% 0:05/0:15``

``FORMAT_DEBUG``
    ``3/10 [━━━━━━━━━━━━━━━━]  30% 0:05/0:15 12.0 MiB``

``FORMAT_INDETERMINATE``
    ``42 [━━━━━━━━━━━━━━━━]``

Set a custom format at any time::

    $progress->setFormat(' %current%/%max% [%bar%] %percent%% %message%');

Built-in Placeholders
---------------------

==================  ==========================================
Placeholder         Description
==================  ==========================================
``%current%``       Current step
``%max%``           Maximum steps
``%bar%``           The visual bar
``%percent%``       Percentage (0–100)
``%elapsed%``       Elapsed time (``m:ss``)
``%remaining%``     Estimated remaining time
``%estimated%``     Estimated total time
``%memory%``        Current memory usage
``%message%``       Custom message (see below)
==================  ==========================================

Custom Placeholders
-------------------

Register a custom placeholder formatter::

    $progress->setPlaceholderFormatter(
        'rate',
        function (ProgressBarWidget $bar) {
            $elapsed = time() - $bar->getStartTime();

            return $elapsed > 0
                ? sprintf('%.1f items/s', $bar->getProgress() / $elapsed)
                : '0 items/s';
        },
    );
    $progress->setFormat(' %current%/%max% [%bar%] %rate%');

Messages
--------

Attach named messages for use in the format string::

    $progress->setMessage('Downloading archive...');
    $progress->setMessage('archive.tar.gz', 'filename');

Retrieve them with ``getMessage('filename')``.

Bar Characters
--------------

Customize the bar appearance::

    $progress->setBarCharacter('█');       // filled portion
    $progress->setEmptyBarCharacter('░');  // empty portion
    $progress->setProgressCharacter('▸');  // cursor at progress position
    $progress->setBarWidth(40);           // bar width in characters

Styleable Elements
------------------

``ProgressBarWidget`` exposes four styleable elements:

* ``text``: the full rendered line.
* ``bar-fill``: the filled portion of the bar.
* ``bar-empty``: the empty portion of the bar.
* ``bar-progress``: the progress character.

Events
------

``ProgressBarWidget`` does not emit events. Query its state with
``isRunning()``, ``getProgress()`` and ``getProgressPercent()``.
