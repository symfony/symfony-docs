Process Helper
==============

The Process Helper shows processes as they're running and reports useful
information about process status.

To display process details, use the
:class:`Symfony\\Component\\Console\\Helper\\ProcessHelper` and run your command
with verbosity. For example, running the following code with
a very verbose verbosity (e.g. ``-vv``)::

    use Symfony\Component\Process\Process;

    $helper = new ProcessHelper();
    $process = new Process(['figlet', 'Symfony']);

    $helper->run($output, $process);

will result in this output:

.. image:: /_images/components/console/process-helper-verbose.png
    :alt: Console output showing two lines: "RUN 'figlet' 'Symfony'" and "RES Command ran successfully".

It will result in more detailed output with debug verbosity (e.g. ``-vvv``):

.. image:: /_images/components/console/process-helper-debug.png
    :alt: In between the command line and the result line, the command's output is now shown prefixed by "OUT".

In case the process fails, debugging is easier:

.. image:: /_images/components/console/process-helper-error-debug.png
    :alt: The last line shows "RES 127 Command did not run successfully", and the output lines show more the error information from the command.

.. note::

    By default, the process helper uses the error output (``stderr``) as
    its default output. This behavior can be changed by passing an instance of
    :class:`Symfony\\Component\\Console\\Output\\StreamOutput` to the
    :method:`Symfony\\Component\\Console\\Helper\\ProcessHelper::run`
    method.

Arguments
---------

There are two ways to use the process helper:

* An array of arguments::

    // ...
    $helper->run($output, ['figlet', 'Symfony']);

  .. note::

      When running the helper against an array of arguments, be aware that
      these will be automatically escaped.

* Passing a :class:`Symfony\\Component\\Process\\Process` instance::

    use Symfony\Component\Process\Process;

    // ...
    $process = new Process(['figlet', 'Symfony']);

    $helper->run($output, $process);

Customized Display
------------------

You can display a customized error message using the third argument of the
:method:`Symfony\\Component\\Console\\Helper\\ProcessHelper::run` method::

    $helper->run($output, $process, 'The process failed :(');

A custom process callback can be passed as the fourth argument. Refer to the
:doc:`Process Component </components/process>` for callback documentation::

    use Symfony\Component\Process\Process;

    $helper->run($output, $process, 'The process failed :(', function (string $type, string $data): void {
        if (Process::ERR === $type) {
            // ... do something with the stderr output
        } else {
            // ... do something with the stdout
        }
    });
