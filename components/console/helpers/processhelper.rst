.. index::
    single: Console Helpers; Process Helper

Process Helper
==============

The Process Helper shows processes as they're running and reports
useful information about process status.

To display process details, use the :class:`Symfony\\Component\\Console\\Helper\\ProcessHelper`
and run your command with verbosity. For example, running the following code with
a very verbose verbosity (e.g. -vv)::

    use Symfony\Component\Process\ProcessBuilder;

    $helper = $this->getHelper('process');
    $process = ProcessBuilder::create(array('figlet', 'Symfony'))->getProcess();

    $helper->run($output, $process);

will result in this output:

.. image:: /images/components/console/process-helper-verbose.png

It will result in more detailed output with debug verbosity (e.g. ``-vvv``):

.. image:: /images/components/console/process-helper-debug.png

In case the process fails, debugging is easier:

.. image:: /images/components/console/process-helper-error-debug.png

Arguments
---------

There are three ways to use the process helper:

* Using a command line string::

    // ...
    $helper->run($output, 'figlet Symfony');

* An array of arguments::

    // ...
    $helper->run($output, array('figlet', 'Symfony'));

  .. note::

      When running the helper against an array of arguments, be aware that
      these will be automatically escaped.

* Passing a :class:`Symfony\\Component\\Process\\Process` instance::

    use Symfony\Component\Process\ProcessBuilder;

    // ...
    $process = ProcessBuilder::create(array('figlet', 'Symfony'))->getProcess();

    $helper->run($output, $process);

Customized Display
------------------

You can display a customized error message using the third argument of the
:method:`Symfony\\Component\\Console\\Helper\\ProcessHelper::run` method::

    $helper->run($output, $process, 'The process failed :(');

A custom process callback can be passed as the fourth argument. Refer to the
:doc:`Process Component </components/process>` for callback documentation::

    use Symfony\Component\Process\Process;

    $helper->run($output, $process, 'The process failed :(', function ($type, $data) {
        if (Process::ERR === $type) {
            // ... do something with the stderr output
        } else {
            // ... do something with the stdout
        }
    });
