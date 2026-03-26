Tick Loop
=========

The tick loop is the heartbeat of a Tui application. It runs
repeatedly while the Tui is active, giving your code a chance to
check async results, update widgets and control the rendering pace.

Registering a Tick Callback
---------------------------

Use ``Tui::onTick()`` to register a callback that runs on every
tick::

    use Symfony\Component\Tui\Event\TickEvent;

    $tui->onTick(function (TickEvent $event) {
        // check async work, update widgets, etc.
    });

Pass ``null`` to remove the callback.

The TickEvent
-------------

The ``TickEvent`` provides:

* ``getDeltaTime()``: seconds elapsed since the previous tick. Use
  this for time-based updates (animations, elapsed timers, etc.).

Busy and Idle Hints
-------------------

The Tui adapts its tick frequency based on whether work is in
progress:

* **Busy** (10 ms interval): the Tui ticks at high frequency for
  responsive updates while async work is running.
* **Idle** (no ticking): the Tui stops ticking entirely when there
  is nothing to poll, saving CPU.
* **Fallback** (250 ms interval): when the Tui can't determine
  busy/idle, it uses a slow poll.

You control this by signaling whether you are busy. There are two
equivalent ways:

**Return a boolean** from the callback::

    $tui->onTick(function (TickEvent $event) use ($future) {
        if ($future->isComplete()) {
            $result = $future->await();
            // update widgets with the result

            return false; // idle, stop ticking
        }

        return true; // busy, keep ticking fast
    });

**Call** ``setBusy()`` **on the event**::

    $tui->onTick(function (TickEvent $event) use ($runner) {
        $runner->tick();

        $event->setBusy($runner->isStreaming());
    });

If you neither return a boolean nor call ``setBusy()``, the Tui
falls back to slow polling (250 ms).

Typical Pattern
---------------

A common pattern is to start async work, poll it in the tick
callback, then stop when done::

    use Amp\Process\Process;
    use Symfony\Component\Tui\Event\TickEvent;
    use function Amp\async;

    $loader = new LoaderWidget('Installing...');
    $tui->add($loader);

    $future = async(function () {
        $process = Process::start('composer install');

        return $process->join();
    });

    $tui->onTick(function (TickEvent $event) use (
        $future, $tui, $loader,
    ) {
        if ($future->isComplete()) {
            $exitCode = $future->await();
            $loader->stop();
            $tui->onTick(null);

            return false;
        }

        return true;
    });

See :doc:`/integrations/revolt` for more on non-blocking I/O.

Game Loops
----------

Games and real-time animations need the tick callback to run
continuously. Call ``$event->setBusy()`` so the Tui keeps ticking
at 10 ms intervals instead of going idle::

    use Symfony\Component\Tui\Event\TickEvent;

    $tui->onTick(function (TickEvent $event) use ($game, $widget) {
        $game->update($event->getDeltaTime());
        $widget->invalidate();
        $event->setBusy();
    });

The simplest approach is to pass ``getDeltaTime()`` directly to
your game logic. Movement and timers scale with real elapsed time,
so the game runs at the same speed regardless of tick rate.

Fixed Timestep
~~~~~~~~~~~~~~

When game logic must run at a deterministic rate (physics,
collision detection), use ``PeriodicStepper`` to convert variable
delta time into a fixed number of steps per tick::

    use Symfony\Component\Tui\Loop\PeriodicStepper;

    // Run game logic 60 times per second
    $stepper = PeriodicStepper::everyMs(16);

    $tui->onTick(function (TickEvent $event) use (
        $stepper, $game, $widget,
    ) {
        $steps = $stepper->advance();
        for ($i = 0; $i < $steps; ++$i) {
            $game->step(); // always the same time increment
        }
        $widget->invalidate();
        $event->setBusy();
    });

If a tick takes longer than expected (e.g. a slow render),
``PeriodicStepper`` catches up by returning multiple steps, capped
at a maximum to prevent spirals.
