.. index::
   single: Process
   single: Components; Process

The Process Component
=====================

    The Process component executes commands in sub-processes.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/process`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/process).

.. include:: /components/require_autoload.rst.inc

Usage
-----

The :class:`Symfony\\Component\\Process\\Process` class allows you to execute
a command in a sub-process::

    use Symfony\Component\Process\Process;
    use Symfony\Component\Process\Exception\ProcessFailedException;

    $process = new Process('ls -lsa');
    $process->run();

    // executes after the command finishes
    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    echo $process->getOutput();

The component takes care of the subtle differences between the different platforms
when executing the command.

The ``getOutput()`` method always returns the whole content of the standard
output of the command and ``getErrorOutput()`` the content of the error
output. Alternatively, the :method:`Symfony\\Component\\Process\\Process::getIncrementalOutput`
and :method:`Symfony\\Component\\Process\\Process::getIncrementalErrorOutput`
methods return the new output since the last call.

The :method:`Symfony\\Component\\Process\\Process::clearOutput` method clears
the contents of the output and
:method:`Symfony\\Component\\Process\\Process::clearErrorOutput` clears
the contents of the error output.

.. versionadded:: 3.1
    Support for streaming the output of a process was introduced in
    Symfony 3.1.

You can also use the :class:`Symfony\\Component\\Process\\Process` class with the
foreach construct to get the output while it is generated. By default, the loop waits
for new output before going to the next iteration::

    $process = new Process('ls -lsa');
    $process->start();

    foreach ($process as $type => $data) {
        if ($process::OUT === $type) {
            echo "\nRead from stdout: ".$data;
        } else { // $process::ERR === $type
            echo "\nRead from stderr: ".$data;
        }
    }

The ``mustRun()`` method is identical to ``run()``, except that it will throw
a :class:`Symfony\\Component\\Process\\Exception\\ProcessFailedException`
if the process couldn't be executed successfully (i.e. the process exited
with a non-zero code)::

    use Symfony\Component\Process\Exception\ProcessFailedException;
    use Symfony\Component\Process\Process;

    $process = new Process('ls -lsa');

    try {
        $process->mustRun();

        echo $process->getOutput();
    } catch (ProcessFailedException $e) {
        echo $e->getMessage();
    }

Getting real-time Process Output
--------------------------------

When executing a long running command (like rsync-ing files to a remote
server), you can give feedback to the end user in real-time by passing an
anonymous function to the
:method:`Symfony\\Component\\Process\\Process::run` method::

    use Symfony\Component\Process\Process;

    $process = new Process('ls -lsa');
    $process->run(function ($type, $buffer) {
        if (Process::ERR === $type) {
            echo 'ERR > '.$buffer;
        } else {
            echo 'OUT > '.$buffer;
        }
    });

Running Processes Asynchronously
--------------------------------

You can also start the subprocess and then let it run asynchronously, retrieving
output and the status in your main process whenever you need it. Use the
:method:`Symfony\\Component\\Process\\Process::start` method to start an asynchronous
process, the :method:`Symfony\\Component\\Process\\Process::isRunning` method
to check if the process is done and the
:method:`Symfony\\Component\\Process\\Process::getOutput` method to get the output::

    $process = new Process('ls -lsa');
    $process->start();

    while ($process->isRunning()) {
        // waiting for process to finish
    }

    echo $process->getOutput();

You can also wait for a process to end if you started it asynchronously and
are done doing other stuff::

    $process = new Process('ls -lsa');
    $process->start();

    // ... do other things

    $process->wait();

    // ... do things after the process has finished

.. note::

    The :method:`Symfony\\Component\\Process\\Process::wait` method is blocking,
    which means that your code will halt at this line until the external
    process is completed.

:method:`Symfony\\Component\\Process\\Process::wait` takes one optional argument:
a callback that is called repeatedly whilst the process is still running, passing
in the output and its type::

    $process = new Process('ls -lsa');
    $process->start();

    $process->wait(function ($type, $buffer) {
        if (Process::ERR === $type) {
            echo 'ERR > '.$buffer;
        } else {
            echo 'OUT > '.$buffer;
        }
    });

Streaming to the Standard Input of a Process
--------------------------------------------

.. versionadded:: 3.1
    Support for streaming the input of a process was introduced in
    Symfony 3.1.

Before a process is started, you can specify its standard input using either the
:method:`Symfony\\Component\\Process\\Process::setInput` method or the 4th argument
of the constructor. The provided input can be a string, a stream resource or a
Traversable object::

    $process = new Process('cat');
    $process->setInput('foobar');
    $process->run();

When this input is fully written to the subprocess standard input, the corresponding
pipe is closed.

In order to write to a subprocess standard input while it is running, the component
provides the :class:`Symfony\\Component\\Process\\InputStream` class::

    $input = new InputStream();
    $input->write('foo');

    $process = new Process('cat');
    $process->setInput($input);
    $process->start();

    // ... read process output or do other things

    $input->write('bar');
    $input->close();

    $process->wait();

    // will echo: foobar
    echo $process->getOutput();

The :method:`Symfony\\Component\\Process\\InputStream::write` method accepts scalars,
stream resources or Traversable objects as argument. As shown in the above example,
you need to explicitly call the :method:`Symfony\\Component\\Process\\InputStream::close`
method when you are done writing to the standard input of the subprocess.

Stopping a Process
------------------

Any asynchronous process can be stopped at any time with the
:method:`Symfony\\Component\\Process\\Process::stop` method. This method takes
two arguments: a timeout and a signal. Once the timeout is reached, the signal
is sent to the running process. The default signal sent to a process is ``SIGKILL``.
Please read the :ref:`signal documentation below<reference-process-signal>`
to find out more about signal handling in the Process component::

    $process = new Process('ls -lsa');
    $process->start();

    // ... do other things

    $process->stop(3, SIGINT);

Executing PHP Code in Isolation
-------------------------------

If you want to execute some PHP code in isolation, use the ``PhpProcess``
instead::

    use Symfony\Component\Process\PhpProcess;

    $process = new PhpProcess(<<<EOF
        <?php echo 'Hello World'; ?>
    EOF
    );
    $process->run();

To make your code work better on all platforms, you might want to use the
:class:`Symfony\\Component\\Process\\ProcessBuilder` class instead::

    use Symfony\Component\Process\ProcessBuilder;

    $builder = new ProcessBuilder(array('ls', '-lsa'));
    $builder->getProcess()->run();

In case you are building a binary driver, you can use the
:method:`Symfony\\Component\\Process\\ProcessBuilder::setPrefix` method to prefix all
the generated process commands.

The following example will generate two process commands for a tar binary
adapter::

    use Symfony\Component\Process\ProcessBuilder;

    $builder = new ProcessBuilder();
    $builder->setPrefix('/usr/bin/tar');

    // '/usr/bin/tar' '--list' '--file=archive.tar.gz'
    echo $builder
        ->setArguments(array('--list', '--file=archive.tar.gz'))
        ->getProcess()
        ->getCommandLine();

    // '/usr/bin/tar' '-xzf' 'archive.tar.gz'
    echo $builder
        ->setArguments(array('-xzf', 'archive.tar.gz'))
        ->getProcess()
        ->getCommandLine();

Process Timeout
---------------

You can limit the amount of time a process takes to complete by setting a
timeout (in seconds)::

    use Symfony\Component\Process\Process;

    $process = new Process('ls -lsa');
    $process->setTimeout(3600);
    $process->run();

If the timeout is reached, a
:class:`Symfony\\Component\\Process\\Exception\\RuntimeException` is thrown.

For long running commands, it is your responsibility to perform the timeout
check regularly::

    $process->setTimeout(3600);
    $process->start();

    while ($condition) {
        // ...

        // check if the timeout is reached
        $process->checkTimeout();

        usleep(200000);
    }

.. _reference-process-signal:

Process Idle Timeout
--------------------

In contrast to the timeout of the previous paragraph, the idle timeout only
considers the time since the last output was produced by the process::

   use Symfony\Component\Process\Process;

   $process = new Process('something-with-variable-runtime');
   $process->setTimeout(3600);
   $process->setIdleTimeout(60);
   $process->run();

In the case above, a process is considered timed out, when either the total runtime
exceeds 3600 seconds, or the process does not produce any output for 60 seconds.

Process Signals
---------------

When running a program asynchronously, you can send it POSIX signals with the
:method:`Symfony\\Component\\Process\\Process::signal` method::

    use Symfony\Component\Process\Process;

    $process = new Process('find / -name "rabbit"');
    $process->start();

    // will send a SIGKILL to the process
    $process->signal(SIGKILL);

.. caution::

    Due to some limitations in PHP, if you're using signals with the Process
    component, you may have to prefix your commands with `exec`_. Please read
    `Symfony Issue#5759`_ and `PHP Bug#39992`_ to understand why this is happening.

    POSIX signals are not available on Windows platforms, please refer to the
    `PHP documentation`_ for available signals.

Process Pid
-----------

You can access the `pid`_ of a running process with the
:method:`Symfony\\Component\\Process\\Process::getPid` method.

.. code-block:: php

    use Symfony\Component\Process\Process;

    $process = new Process('/usr/bin/php worker.php');
    $process->start();

    $pid = $process->getPid();

.. caution::

    Due to some limitations in PHP, if you want to get the pid of a symfony Process,
    you may have to prefix your commands with `exec`_. Please read
    `Symfony Issue#5759`_ to understand why this is happening.

Disabling Output
----------------

As standard output and error output are always fetched from the underlying process,
it might be convenient to disable output in some cases to save memory.
Use :method:`Symfony\\Component\\Process\\Process::disableOutput` and
:method:`Symfony\\Component\\Process\\Process::enableOutput` to toggle this feature::

    use Symfony\Component\Process\Process;

    $process = new Process('/usr/bin/php worker.php');
    $process->disableOutput();
    $process->run();

.. caution::

    You cannot enable or disable the output while the process is running.

    If you disable the output, you cannot access ``getOutput()``,
    ``getIncrementalOutput()``, ``getErrorOutput()``, ``getIncrementalErrorOutput()`` or
    ``setIdleTimeout()``.

    However, it is possible to pass a callback to the ``start``, ``run`` or ``mustRun``
    methods to handle process output in a streaming fashion.

    .. versionadded:: 3.1
        The ability to pass a callback to these methods when output is disabled
        was added in Symfony 3.1.

.. _`Symfony Issue#5759`: https://github.com/symfony/symfony/issues/5759
.. _`PHP Bug#39992`: https://bugs.php.net/bug.php?id=39992
.. _`exec`: https://en.wikipedia.org/wiki/Exec_(operating_system)
.. _`pid`: https://en.wikipedia.org/wiki/Process_identifier
.. _`PHP Documentation`: http://php.net/manual/en/pcntl.constants.php
.. _Packagist: https://packagist.org/packages/symfony/process
