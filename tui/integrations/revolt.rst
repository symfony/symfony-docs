Revolt and Amp
==============

Tui runs on the Revolt event loop. This means all I/O performed
while the Tui is running must be non-blocking. Blocking calls
(``file_get_contents()``, ``proc_open()``, ``sleep()``, etc.)
freeze the entire UI: no rendering, no input handling, no
animations.

Why Non-Blocking Matters
------------------------

The Tui shares a single thread with your application code. The
event loop alternates between reading terminal input, running your
tick callback and rendering the screen. A blocking call stalls the
entire cycle: the screen stops updating, keyboard input queues up
and animations freeze until the call returns.

Use Amp Libraries
-----------------

The Amp ecosystem provides non-blocking replacements for common
blocking operations. Since Amp is built on Revolt (the same event
loop Tui uses), they integrate without any glue code.

**Processes**: use ``amphp/process`` instead of ``proc_open()`` or
Symfony Process:

.. code-block:: php

    use Amp\Process\Process;

    $process = Process::start('ls -la');
    $stdout = $process->getStdout()->read();

**HTTP**: use Symfony HttpClient with its Amp integration
(``symfony/http-client`` + ``amphp/http-client``):

.. code-block:: php

    use Symfony\Component\HttpClient\AmpHttpClient;

    $client = new AmpHttpClient();
    $response = $client->request('GET', 'https://example.com');
    $body = $response->getContent();

**Timers and delays**: use ``Amp\delay()`` instead of ``sleep()``:

.. code-block:: php

    \Amp\delay(2); // yields to the event loop for 2 seconds

Running Async Work
------------------

Use ``Amp\async()`` to run work in a fiber. The fiber yields to the
event loop whenever it performs I/O, keeping the UI responsive:

.. code-block:: php

    use Amp\Process\Process;
    use function Amp\async;

    $future = async(function () {
        $process = Process::start('composer install');
        $output = $process->getStdout()->read();
        $exitCode = $process->join();

        return $output;
    });

Check whether the future is complete in your tick callback:

.. code-block:: php

    use Symfony\Component\Tui\Event\TickEvent;

    $tui->onTick(function (TickEvent $event) use ($future, $tui) {
        if ($future->isComplete()) {
            $result = $future->await();
            $tui->stop();
        }

        return !$future->isComplete();
    });

The tick callback returns ``true`` while work is in progress (the
Tui ticks at high frequency) and ``false`` when idle. See
:doc:`/topics/tick_loop` for the full tick loop API.

Common Pitfalls
---------------

**Symfony Process is blocking.** ``Process::run()`` and
``Process::wait()`` block the event loop. Use ``amphp/process``
instead.

**PDO and database queries are blocking.** Use an async database
library or run queries inside ``Amp\async()`` with
``Amp\Parallel\Worker`` if you need long-running queries.

**``sleep()`` blocks.** Use ``\Amp\delay()`` for non-blocking
delays.

**``file_get_contents()`` with URLs blocks.** Use
``AmpHttpClient`` for HTTP requests.
