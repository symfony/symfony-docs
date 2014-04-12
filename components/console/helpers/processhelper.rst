.. index::
    single: Console Helpers; Process Helper

Process Helper
==============

.. versionadded:: 2.5
    The Process Helper was introduced in Symfony 2.5.

The Process Helper shows processes as they're running and reports
useful information about process status.

To display process details, use the :class:`Symfony\\Component\\Console\\Helper\\ProcessHelper`
and run your command with verbosity. For example, running the following code with
a very verbose verbosity (e.g. -vv)::

    use Symfony\Component\Process\ProcessBuilder;

    $helper = $this->getHelperSet()->get('process');
    $process = ProcessBuilder::create(array('figlet', 'Symfony'))->getProcess();

    $helper->run($output, $process);

will result in this output:

.. image:: /images/components/console/process-helper-verbose.png

It will result in more detailed output with debug verbosity (e.g. -vvv):

.. image:: /images/components/console/process-helper-debug.png

In case the process fails, debugging is easier:

.. image:: /images/components/console/process-helper-error-debug.png

There are three ways to use the process helper: using a command line string, an array
of arguments that would be escaped or a :class:`Symfony\\Component\\Process\\Process`
object.

You can display a customized error message using the third argument of the
:method:`Symfony\\Component\\Console\\Helper\\ProcessHelper::run` method::

    $helper->run($output, $process, 'The process failed :(');

A custom process callback can be passed as fourth argument, refer to the
:doc:`Process Component </components/process>` for callback documentation::

    use Symfony\Component\Process\Process;

    $helper->run($output, $process, 'The process failed :(', function ($type, $data) {
        if (Process::ERR === $type) {
            // do something with the stderr output
        } else {
            // do something with the stdout
        }
    });
