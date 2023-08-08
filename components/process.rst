The Process Component
=====================

    The Process component executes commands in sub-processes.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/process


.. include:: /components/require_autoload.rst.inc

Usage
-----

The :class:`Symfony\\Component\\Process\\Process` class executes a command in a
sub-process, taking care of the differences between operating system and
escaping arguments to prevent security issues. It replaces PHP functions like
:phpfunction:`exec`, :phpfunction:`passthru`, :phpfunction:`shell_exec` and
:phpfunction:`system`::

    use Symfony\Component\Process\Exception\ProcessFailedException;
    use Symfony\Component\Process\Process;

    $process = new Process(['ls', '-lsa']);
    $process->run();

    // executes after the command finishes
    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    echo $process->getOutput();

The ``getOutput()`` method always returns the whole content of the standard
output of the command and ``getErrorOutput()`` the content of the error
output. Alternatively, the :method:`Symfony\\Component\\Process\\Process::getIncrementalOutput`
and :method:`Symfony\\Component\\Process\\Process::getIncrementalErrorOutput`
methods return the new output since the last call.

The :method:`Symfony\\Component\\Process\\Process::clearOutput` method clears
the contents of the output and
:method:`Symfony\\Component\\Process\\Process::clearErrorOutput` clears
the contents of the error output.

You can also use the :class:`Symfony\\Component\\Process\\Process` class with the
for each construct to get the output while it is generated. By default, the loop waits
for new output before going to the next iteration::

    $process = new Process(['ls', '-lsa']);
    $process->start();

    foreach ($process as $type => $data) {
        if ($process::OUT === $type) {
            echo "\nRead from stdout: ".$data;
        } else { // $process::ERR === $type
            echo "\nRead from stderr: ".$data;
        }
    }

.. tip::

    The Process component internally uses a PHP iterator to get the output while
    it is generated. That iterator is exposed via the ``getIterator()`` method
    to allow customizing its behavior::

        $process = new Process(['ls', '-lsa']);
        $process->start();
        $iterator = $process->getIterator($process::ITER_SKIP_ERR | $process::ITER_KEEP_OUTPUT);
        foreach ($iterator as $data) {
            echo $data."\n";
        }

The ``mustRun()`` method is identical to ``run()``, except that it will throw
a :class:`Symfony\\Component\\Process\\Exception\\ProcessFailedException`
if the process couldn't be executed successfully (i.e. the process exited
with a non-zero code)::

    use Symfony\Component\Process\Exception\ProcessFailedException;
    use Symfony\Component\Process\Process;

    $process = new Process(['ls', '-lsa']);

    try {
        $process->mustRun();

        echo $process->getOutput();
    } catch (ProcessFailedException $exception) {
        echo $exception->getMessage();
    }

.. tip::

    You can get the last output time in seconds by using the
    :method:`Symfony\\Component\\Process\\Process::getLastOutputTime` method.
    This method returns ``null`` if the process wasn't started!

Configuring Process Options
---------------------------

Symfony uses the PHP :phpfunction:`proc_open` function to run the processes.
You can configure the options passed to the ``other_options`` argument of
``proc_open()`` using the ``setOptions()`` method::

    $process = new Process(['...', '...', '...']);
    // this option allows a subprocess to continue running after the main script exited
    $process->setOptions(['create_new_console' => true]);

Using Features From the OS Shell
--------------------------------

Using an array of arguments is the recommended way to define commands. This
saves you from any escaping and allows sending signals seamlessly
(e.g. to stop processes while they run)::

    $process = new Process(['/path/command', '--option', 'argument', 'etc.']);
    $process = new Process(['/path/to/php', '--define', 'memory_limit=1024M', '/path/to/script.php']);

If you need to use stream redirections, conditional execution, or any other
feature provided by the shell of your operating system, you can also define
commands as strings using the
:method:`Symfony\\Component\\Process\\Process::fromShellCommandline` static
factory.

Each operating system provides a different syntax for their command-lines,
so it becomes your responsibility to deal with escaping and portability.

When using strings to define commands, variable arguments are passed as
environment variables using the second argument of the ``run()``,
``mustRun()`` or ``start()`` methods. Referencing them is also OS-dependent::

    // On Unix-like OSes (Linux, macOS)
    $process = Process::fromShellCommandline('echo "$MESSAGE"');

    // On Windows
    $process = Process::fromShellCommandline('echo "!MESSAGE!"');

    // On both Unix-like and Windows
    $process->run(null, ['MESSAGE' => 'Something to output']);

If you prefer to create portable commands that are independent from the
operating system, you can write the above command as follows::

    // works the same on Windows , Linux and macOS
    $process = Process::fromShellCommandline('echo "${:MESSAGE}"');

Portable commands require using a syntax that is specific to the component: when
enclosing a variable name into ``"${:`` and ``}"`` exactly, the process object
will replace it with its escaped value, or will fail if the variable is not
found in the list of environment variables attached to the command.

Setting Environment Variables for Processes
-------------------------------------------

The constructor of the :class:`Symfony\\Component\\Process\\Process` class and
all of its methods related to executing processes (``run()``, ``mustRun()``,
``start()``, etc.) allow passing an array of environment variables to set while
running the process::

    $process = new Process(['...'], null, ['ENV_VAR_NAME' => 'value']);
    $process = Process::fromShellCommandline('...', null, ['ENV_VAR_NAME' => 'value']);
    $process->run(null, ['ENV_VAR_NAME' => 'value']);

In addition to the env vars passed explicitly, processes inherit all the env
vars defined in your system. You can prevent this by setting to ``false`` the
env vars you want to remove::

    $process = new Process(['...'], null, [
        'APP_ENV' => false,
        'SYMFONY_DOTENV_VARS' => false,
    ]);

Getting real-time Process Output
--------------------------------

When executing a long running command (like ``rsync`` to a remote
server), you can give feedback to the end user in real-time by passing an
anonymous function to the
:method:`Symfony\\Component\\Process\\Process::run` method::

    use Symfony\Component\Process\Process;

    $process = new Process(['ls', '-lsa']);
    $process->run(function ($type, $buffer): void {
        if (Process::ERR === $type) {
            echo 'ERR > '.$buffer;
        } else {
            echo 'OUT > '.$buffer;
        }
    });

.. note::

    This feature won't work as expected in servers using PHP output buffering.
    In those cases, either disable the `output_buffering`_ PHP option or use the
    :phpfunction:`ob_flush` PHP function to force sending the output buffer.

Running Processes Asynchronously
--------------------------------

You can also start the subprocess and then let it run asynchronously, retrieving
output and the status in your main process whenever you need it. Use the
:method:`Symfony\\Component\\Process\\Process::start` method to start an asynchronous
process, the :method:`Symfony\\Component\\Process\\Process::isRunning` method
to check if the process is done and the
:method:`Symfony\\Component\\Process\\Process::getOutput` method to get the output::

    $process = new Process(['ls', '-lsa']);
    $process->start();

    while ($process->isRunning()) {
        // waiting for process to finish
    }

    echo $process->getOutput();

You can also wait for a process to end if you started it asynchronously and
are done doing other stuff::

    $process = new Process(['ls', '-lsa']);
    $process->start();

    // ... do other things

    $process->wait();

    // ... do things after the process has finished

.. note::

    The :method:`Symfony\\Component\\Process\\Process::wait` method is blocking,
    which means that your code will halt at this line until the external
    process is completed.

.. note::

    If a ``Response`` is sent **before** a child process had a chance to complete,
    the server process will be killed (depending on your OS). It means that
    your task will be stopped right away. Running an asynchronous process
    is not the same as running a process that survives its parent process.

    If you want your process to survive the request/response cycle, you can
    take advantage of the ``kernel.terminate`` event, and run your command
    **synchronously** inside this event. Be aware that ``kernel.terminate``
    is called only if you use PHP-FPM.

.. caution::

    Beware also that if you do that, the said PHP-FPM process will not be
    available to serve any new request until the subprocess is finished. This
    means you can quickly block your FPM pool if you're not careful enough.
    That is why it's generally way better not to do any fancy things even
    after the request is sent, but to use a job queue instead.

:method:`Symfony\\Component\\Process\\Process::wait` takes one optional argument:
a callback that is called repeatedly whilst the process is still running, passing
in the output and its type::

    $process = new Process(['ls', '-lsa']);
    $process->start();

    $process->wait(function ($type, $buffer): void {
        if (Process::ERR === $type) {
            echo 'ERR > '.$buffer;
        } else {
            echo 'OUT > '.$buffer;
        }
    });

Instead of waiting until the process has finished, you can use the
:method:`Symfony\\Component\\Process\\Process::waitUntil` method to keep or stop
waiting based on some PHP logic. The following example starts a long running
process and checks its output to wait until its fully initialized::

    $process = new Process(['/usr/bin/php', 'slow-starting-server.php']);
    $process->start();

    // ... do other things

    // waits until the given anonymous function returns true
    $process->waitUntil(function ($type, $output): bool {
        return $output === 'Ready. Waiting for commands...';
    });

    // ... do things after the process is ready

Streaming to the Standard Input of a Process
--------------------------------------------

Before a process is started, you can specify its standard input using either the
:method:`Symfony\\Component\\Process\\Process::setInput` method or the 4th argument
of the constructor. The provided input can be a string, a stream resource or a
``Traversable`` object::

    $process = new Process(['cat']);
    $process->setInput('foobar');
    $process->run();

When this input is fully written to the subprocess standard input, the corresponding
pipe is closed.

In order to write to a subprocess standard input while it is running, the component
provides the :class:`Symfony\\Component\\Process\\InputStream` class::

    $input = new InputStream();
    $input->write('foo');

    $process = new Process(['cat']);
    $process->setInput($input);
    $process->start();

    // ... read process output or do other things

    $input->write('bar');
    $input->close();

    $process->wait();

    // will echo: foobar
    echo $process->getOutput();

The :method:`Symfony\\Component\\Process\\InputStream::write` method accepts scalars,
stream resources or ``Traversable`` objects as arguments. As shown in the above example,
you need to explicitly call the :method:`Symfony\\Component\\Process\\InputStream::close`
method when you are done writing to the standard input of the subprocess.

Using PHP Streams as the Standard Input of a Process
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The input of a process can also be defined using `PHP streams`_::

    $stream = fopen('php://temporary', 'w+');

    $process = new Process(['cat']);
    $process->setInput($stream);
    $process->start();

    fwrite($stream, 'foo');

    // ... read process output or do other things

    fwrite($stream, 'bar');
    fclose($stream);

    $process->wait();

    // will echo: 'foobar'
    echo $process->getOutput();

Using TTY and PTY Modes
-----------------------

All examples above show that your program has control over the input of a
process (using ``setInput()``) and the output from that process (using
``getOutput()``). The Process component has two special modes that tweak
the relationship between your program and the process: teletype (tty) and
pseudo-teletype (pty).

In TTY mode, you connect the input and output of the process to the input
and output of your program. This allows for instance to open an editor like
Vim or Nano as a process. You enable TTY mode by calling
:method:`Symfony\\Component\\Process\\Process::setTty`::

    $process = new Process(['vim']);
    $process->setTty(true);
    $process->run();

    // As the output is connected to the terminal, it is no longer possible
    // to read or modify the output from the process!
    dump($process->getOutput()); // null

In PTY mode, your program behaves as a terminal for the process instead of
a plain input and output. Some programs behave differently when
interacting with a real terminal instead of another program. For instance,
some programs prompt for a password when talking with a terminal. Use
:method:`Symfony\\Component\\Process\\Process::setPty` to enable this
mode.

Stopping a Process
------------------

Any asynchronous process can be stopped at any time with the
:method:`Symfony\\Component\\Process\\Process::stop` method. This method takes
two arguments: a timeout and a signal. Once the timeout is reached, the signal
is sent to the running process. The default signal sent to a process is ``SIGKILL``.
Please read the :ref:`signal documentation below <reference-process-signal>`
to find out more about signal handling in the Process component::

    $process = new Process(['ls', '-lsa']);
    $process->start();

    // ... do other things

    $process->stop(3, SIGINT);

Executing PHP Code in Isolation
-------------------------------

If you want to execute some PHP code in isolation, use the ``PhpProcess``
instead::

    use Symfony\Component\Process\PhpProcess;

    $process = new PhpProcess(<<<EOF
        <?= 'Hello World' ?>
    EOF
    );
    $process->run();

Executing a PHP Child Process with the Same Configuration
---------------------------------------------------------

.. versionadded:: 6.4

    The ``PhpSubprocess`` helper was introduced in Symfony 6.4.

When you start a PHP process, it uses the default configuration defined in
your ``php.ini`` file. You can bypass these options with the ``-d`` command line
option. For example, if ``memory_limit`` is set to ``256M``, you can disable this
memory limit when running some command like this:
``php -d memory_limit=-1 bin/console app:my-command``.

However, if you run the command via the Symfony ``Process`` class, PHP will use
the settings defined in the ``php.ini`` file. You can solve this issue by using
the :class:`Symfony\\Component\\Process\\PhpSubprocess` class to run the command::

    use Symfony\Component\Process\Process;

    class MyCommand extends Command
    {
        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            // the memory_limit (and any other config option) of this command is
            // the one defined in php.ini instead of the new values (optionally)
            // passed via the '-d' command option
            $childProcess = new Process(['bin/console', 'cache:pool:prune']);

            // the memory_limit (and any other config option) of this command takes
            // into account the values (optionally) passed via the '-d' command option
            $childProcess = new PhpSubprocess(['bin/console', 'cache:pool:prune']);
        }
    }

Process Timeout
---------------

By default processes have a timeout of 60 seconds, but you can change it passing
a different timeout (in seconds) to the ``setTimeout()`` method::

    use Symfony\Component\Process\Process;

    $process = new Process(['ls', '-lsa']);
    $process->setTimeout(3600);
    $process->run();

If the timeout is reached, a
:class:`Symfony\\Component\\Process\\Exception\\ProcessTimedOutException` is thrown.

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

.. tip::

    You can get the process start time using the ``getStartTime()`` method.

.. _reference-process-signal:

Process Idle Timeout
--------------------

In contrast to the timeout of the previous paragraph, the idle timeout only
considers the time since the last output was produced by the process::

    use Symfony\Component\Process\Process;

    $process = new Process(['something-with-variable-runtime']);
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

    $process = new Process(['find', '/', '-name', 'rabbit']);
    $process->start();

    // will send a SIGKILL to the process
    $process->signal(SIGKILL);

Process Pid
-----------

You can access the `pid`_ of a running process with the
:method:`Symfony\\Component\\Process\\Process::getPid` method::

    use Symfony\Component\Process\Process;

    $process = new Process(['/usr/bin/php', 'worker.php']);
    $process->start();

    $pid = $process->getPid();

Disabling Output
----------------

As standard output and error output are always fetched from the underlying process,
it might be convenient to disable output in some cases to save memory.
Use :method:`Symfony\\Component\\Process\\Process::disableOutput` and
:method:`Symfony\\Component\\Process\\Process::enableOutput` to toggle this feature::

    use Symfony\Component\Process\Process;

    $process = new Process(['/usr/bin/php', 'worker.php']);
    $process->disableOutput();
    $process->run();

.. caution::

    You cannot enable or disable the output while the process is running.

    If you disable the output, you cannot access ``getOutput()``,
    ``getIncrementalOutput()``, ``getErrorOutput()``, ``getIncrementalErrorOutput()`` or
    ``setIdleTimeout()``.

    However, it is possible to pass a callback to the ``start``, ``run`` or ``mustRun``
    methods to handle process output in a streaming fashion.

Finding an Executable
---------------------

The Process component provides a utility class called
:class:`Symfony\\Component\\Process\\ExecutableFinder` which finds
and returns the absolute path of an executable::

    use Symfony\Component\Process\ExecutableFinder;

    $executableFinder = new ExecutableFinder();
    $chromedriverPath = $executableFinder->find('chromedriver');
    // $chromedriverPath = '/usr/local/bin/chromedriver' (the result will be different on your computer)

The :method:`Symfony\\Component\\Process\\ExecutableFinder::find` method also takes extra parameters to specify a default value
to return and extra directories where to look for the executable::

    use Symfony\Component\Process\ExecutableFinder;

    $executableFinder = new ExecutableFinder();
    $chromedriverPath = $executableFinder->find('chromedriver', '/path/to/chromedriver', ['local-bin/']);

Finding the Executable PHP Binary
---------------------------------

This component also provides a special utility class called
:class:`Symfony\\Component\\Process\\PhpExecutableFinder` which returns the
absolute path of the executable PHP binary available on your server::

    use Symfony\Component\Process\PhpExecutableFinder;

    $phpBinaryFinder = new PhpExecutableFinder();
    $phpBinaryPath = $phpBinaryFinder->find();
    // $phpBinaryPath = '/usr/local/bin/php' (the result will be different on your computer)

Checking for TTY Support
------------------------

Another utility provided by this component is a method called
:method:`Symfony\\Component\\Process\\Process::isTtySupported` which returns
whether `TTY`_ is supported on the current operating system::

    use Symfony\Component\Process\Process;

    $process = (new Process())->setTty(Process::isTtySupported());

.. _`pid`: https://en.wikipedia.org/wiki/Process_identifier
.. _`PHP streams`: https://www.php.net/manual/en/book.stream.php
.. _`output_buffering`: https://www.php.net/manual/en/outcontrol.configuration.php
.. _`TTY`: https://en.wikipedia.org/wiki/Tty_(unix)
