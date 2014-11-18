.. index::
    single: Console Helpers; DebugFormatter Helper

DebugFormatter Helper
=====================

.. versionadded:: 2.6
    The DebugFormatter helper was introduced in Symfony 2.6.

The :class:`Symfony\\Component\\Console\\Helper\\DebugFormatterHelper` provides
functions to output debug information when running an external program, for
instance a process or HTTP request. It is included in the default helper set,
which you can get by calling
:method:`Symfony\\Component\\Console\\Command\\Command::getHelperSet`::

    $debugFormatter = $this->getHelper('debug_formatter');

The formatter only formats strings, which you can use to output to the console,
but also to log the information or anything else.

All methods of this helper have an identifier as the first argument. This is an
unique value for each program. This way, the helper can debug information for
multiple programs at the same time. When using the
:doc:`Process component </components/process>`, you probably want to use
:phpfunction:`spl_object_hash`.

.. tip::

    This information is often too verbose to show by default. You can use
    :ref:`verbosity levels <verbosity-levels>` to only show it when in
    debugging mode (``-vvv``).

Starting a Program
------------------

As soon as you start a program, you can use
:method:`Symfony\\Component\\Console\\Helper\\DebugFormatterHelper::start` to
display information that the program is started::

    // ...
    $process = new Process(...);
    $process->run();

    $output->writeln($debugFormatter->start(spl_object_hash($process), 'Some process description'));

This will output:

.. code-block:: text

     RUN Some process description

You can tweak the prefix using the third argument::

    $output->writeln($debugFormatter->start(spl_object_hash($process), 'Some process description', 'STARTED');
    // will output:
    //  STARTED Some process description

Output Progress Information
---------------------------

Some programs give output while they are running. This information can be shown
using
:method:`Symfony\\Component\\Console\\Helper\\DebugFormatterHelper::progress`::

    // ...
    $output->writeln($debugFormatter->progress(spl_object_hash($process), $buffer, Process::ERR === $type));

In case of success, this will output:

.. code-block:: text

    OUT The output of the process

And this in case of failure:

.. code-block:: text

    ERR The output of the process

The third argument is a boolean which tells the function if the output is error
output or not. When ``true``, the output is considered error output.

The fourth and fifth argument allow you to override the prefix for respectively
the normal output and error output.

Stopping a Program
------------------

When a program is stopped, you can use
:method:`Symfony\\Component\\Console\\Helper\\DebugFormatterHelper::progress`
to notify this to the users::

    // ...
    $output->writeln($debugFormatter->progress(spl_object_hash($process), 'Some command description', $process->isSuccesfull()));

This will output:

.. code-block:: text

    RES Some command description

In case of failure, this will be in red and in case of success it will be green.

Using multiple Programs
-----------------------

As said before, you can also use the helper to display more programs at the
same time. Information about different programs will be shown in different
colors, to make it clear which output belongs to which command.
