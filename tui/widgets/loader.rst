LoaderWidget
============

``LoaderWidget`` displays an animated spinner with a text message.
The animation advances automatically via the event loop.

When to Use
-----------

Use ``LoaderWidget`` to indicate that a background operation is in
progress: loading data, waiting for a network response, processing
files, etc. For operations that can be cancelled, use
:doc:`cancellable_loader`. For operations with measurable progress,
use :doc:`progress_bar`.

Basic Usage
-----------

.. code-block:: php

    use Symfony\Component\Tui\Widget\LoaderWidget;

    $loader = new LoaderWidget('Fetching data...');
    $tui->add($loader);

The loader starts automatically on construction. Stop it when the
operation completes::

    $loader->stop();

Call ``start()`` to restart the animation after stopping it.

Message
-------

Update the message while the loader is running::

    $loader->setMessage('Processing items...');

Spinner Styles
--------------

Eight built-in spinner styles are available: ``dots`` (default),
``line``, ``bounce``, ``pulse``, ``bar``, ``shade``, ``arc`` and
``circle``.

Switch the style with ``setSpinner()``::

    $loader->setSpinner('pulse');

Register a custom spinner with ``addSpinner()``::

    LoaderWidget::addSpinner('arrows', ['←', '↑', '→', '↓']);
    $loader->setSpinner('arrows');

Animation Speed
---------------

Control the frame rate with ``setIntervalMs()``::

    $loader->setIntervalMs(120); // slower animation

The default interval is 80 ms.

Finished Indicator
------------------

Show a static character when the loader stops instead of hiding it
entirely::

    $loader->setFinishedIndicator('✓');
    $loader->stop(); // displays "✓ Fetching data..."

Styleable Elements
------------------

``LoaderWidget`` exposes two styleable elements:

* ``spinner``: the animated character.
* ``message``: the text next to the spinner.

Events
------

``LoaderWidget`` does not emit any events. Check its state with
``isRunning()``.
