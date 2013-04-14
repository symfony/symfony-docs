.. index::
   single: Process
   single: Components; Process

The Process Component
=====================

    The Process Component executes commands in sub-processes.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Process);
* :doc:`Install it via Composer</components/using_components>` (``symfony/process`` on `Packagist`_).

Usage
-----

The :class:`Symfony\\Component\\Process\\Process` class allows you to execute
a command in a sub-process::

    use Symfony\Component\Process\Process;

    $process = new Process('ls -lsa');
    $process->run();

    // executes after the the command finishes
    if (!$process->isSuccessful()) {
        throw new \RuntimeException($process->getErrorOutput());
    }

    print $process->getOutput();

The component takes care of the subtle differences between the different platforms
when executing the command.

.. versionadded:: 2.2
    The ``getIncrementalOutput()`` and ``getIncrementalErrorOutput()`` methods were added in Symfony 2.2.

The ``getOutput()`` method always return the whole content of the standard
output of the command and ``getErrorOutput()`` the content of the error
output. Alternatively, the :method:`Symfony\\Component\\Process\\Process::getIncrementalOutput`
and :method:`Symfony\\Component\\Process\\Process::getIncrementalErrorOutput`
methods returns the new outputs since the last call.

When executing a long running command (like rsync-ing files to a remote
server), you can give feedback to the end user in real-time by passing an
anonymous function to the
:method:`Symfony\\Component\\Process\\Process::run` method::

    use Symfony\Component\Process\Process;

    $process = new Process('ls -lsa');
    $process->run(function ($type, $buffer) {
        if ('err' === $type) {
            echo 'ERR > '.$buffer;
        } else {
            echo 'OUT > '.$buffer;
        }
    });
    
.. versionadded:: 2.1
    The non-blocking feature was added in 2.1.
    
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
    
    // do other things
    
    $process->wait(function ($type, $buffer) {
        if ('err' === $type) {
            echo 'ERR > '.$buffer;
        } else {
            echo 'OUT > '.$buffer;
        }
    });

.. note::

    Please consider that the :method:`Symfony\\Component\\Process\\Process::wait`
    method is blocking.

.. versionadded:: 2.3
    The ``signal`` parameter of the ``stop`` method was added in Symfony 2.3.

Any asynchronous process can be stopped at any time with the
:method:`Symfony\\Component\\Process\\Process::stop` method. This method takes
two arguments : a timeout and a signal. Once the timeout is reached, the signal
is sent to the running process.
The default signal sent to a process is ``SIGKILL``. Please read the signal
documentation below to know more about signal handling in the Process component.

.. code-block:: php

    $process = new Process('ls -lsa');
    $process->start();

    // ... do other things

    $process->stop(3, SIGINT);

If you want to execute some PHP code in isolation, use the ``PhpProcess``
instead::

    use Symfony\Component\Process\PhpProcess;

    $process = new PhpProcess(<<<EOF
        <?php echo 'Hello World'; ?>
    EOF
    );
    $process->run();

.. versionadded:: 2.1
    The ``ProcessBuilder`` class was added in Symfony 2.1.

To make your code work better on all platforms, you might want to use the
:class:`Symfony\\Component\\Process\\ProcessBuilder` class instead::

    use Symfony\Component\Process\ProcessBuilder;

    $builder = new ProcessBuilder(array('ls', '-lsa'));
    $builder->getProcess()->run();

Process Timeout
---------------

You can limit the amount of time a process takes to complete by setting a
timeout (in seconds)::

    use Symfony\Component\Process\Process;

    $process = new Process('ls -lsa');
    $process->setTimeout(3600);
    $process->run();

If the timeout is reached, a
:class:`Symfony\\Process\\Exception\\RuntimeException` is thrown.

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

Process Signals
---------------

.. versionadded:: 2.3
    The ``signal`` method was added in Symfony 2.3.

When running programs asynchronously, you can send it posix signals with the
:method:`Symfony\\Component\\Process\\Process::signal` method.

.. code-block:: php

    use Symfony\Component\Process\Process;

    $process = new Process('find / -name "rabbit"');
    $process->start();

    // will send a SIGKILL to the process
    $process->signal(SIGKILL);

.. caution::

    Due to some limitations in PHP, if you're using signals with the Process
    component, you may have to prefix your commands with `exec`_. Please read
    `Symfony Issue#5769`_ and `PHP Bug#3992` to understand why this is happening.

    POSIX signals are not available on Windows platforms, please refer to the
    `PHP documentation`_ for available signals.

Process Pid
-----------

.. versionadded:: 2.3
    The ``getPid`` method was added in Symfony 2.3.

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
    `Symfony Issue#5769`_ to understand why this is happening.

.. _Symfony Issue#5759: https://github.com/symfony/symfony/issues/5759
.. _PHP Bug#39992: https://bugs.php.net/bug.php?id=39992
.. _exec: http://en.wikipedia.org/wiki/Exec_(operating_system)
.. _pid: http://en.wikipedia.org/wiki/Process_identifier
.. _PHP Documentation: http://php.net/manual/en/pcntl.constants.php
.. _Packagist: https://packagist.org/packages/symfony/process
