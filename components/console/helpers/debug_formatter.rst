.. index::
    single: Console Helpers; DebugFormatter Helper

Debug Formatter Helper
======================

.. versionadded:: 2.6
    The Debug Formatter helper was introduced in Symfony 2.6.

The :class:`Symfony\\Component\\Console\\Helper\\DebugFormatterHelper` provides
functions to output debug information when running an external program, for
instance a process or HTTP request. For example, if you used it to output
the results of running ``ls -la`` on a UNIX system, it might output something
like this:

.. image:: /images/components/console/debug_formatter.png
   :align: center

Using the debug_formatter
-------------------------

The formatter is included in the default helper set and you can get it by
calling :method:`Symfony\\Component\\Console\\Command\\Command::getHelper`::

    $debugFormatter = $this->getHelper('debug_formatter');

The formatter accepts strings and returns a formatted string, which you then
output to the console (or even log the information or do anything else).

All methods of this helper have an identifier as the first argument. This is a
unique value for each program. This way, the helper can debug information for
multiple programs at the same time. When using the
:doc:`Process component </components/process>`, you probably want to use
:phpfunction:`spl_object_hash`.

.. tip::

    This information is often too verbose to be shown by default. You can use
    :ref:`verbosity levels <verbosity-levels>` to only show it when in
    debugging mode (``-vvv``).

Starting a Program
------------------

As soon as you start a program, you can use
:method:`Symfony\\Component\\Console\\Helper\\DebugFormatterHelper::start` to
display information that the program is started::

    // ...
    $process = new Process(...);

    $output->writeln($debugFormatter->start(
        spl_object_hash($process),
        'Some process description'
    ));

    $process->run();

This will output:

.. code-block:: text

     RUN Some process description

You can tweak the prefix using the third argument::

    $output->writeln($debugFormatter->start(
        spl_object_hash($process),
        'Some process description',
        'STARTED'
    ));
    // will output:
    //  STARTED Some process description

Output Progress Information
---------------------------

Some programs give output while they are running. This information can be shown
using
:method:`Symfony\\Component\\Console\\Helper\\DebugFormatterHelper::progress`::

    use Symfony\Component\Process\Process;

    // ...
    $process = new Process(...);

    $process->run(function ($type, $buffer) use ($output, $debugFormatter, $process) {
        $output->writeln(
            $debugFormatter->progress(
                spl_object_hash($process),
                $buffer,
                Process::ERR === $type
            )
        );
    });
    // ...

In case of success, this will output:

.. code-block:: text

    OUT The output of the process

And this in case of failure:

.. code-block:: text

    ERR The output of the process

The third argument is a boolean which tells the function if the output is error
output or not. When ``true``, the output is considered error output.

The fourth and fifth argument allow you to override the prefix for the normal
output and error output respectively.

Stopping a Program
------------------

When a program is stopped, you can use
:method:`Symfony\\Component\\Console\\Helper\\DebugFormatterHelper::run` to
notify this to the users::

    // ...
    $output->writeln(
        $debugFormatter->stop(
            spl_object_hash($process),
            'Some command description',
            $process->isSuccessfull()
        )
    );

This will output:

.. code-block:: text

    RES Some command description

In case of failure, this will be in red and in case of success it will be green.

Using multiple Programs
-----------------------

As said before, you can also use the helper to display more programs at the
same time. Information about different programs will be shown in different
colors, to make it clear which output belongs to which command.
