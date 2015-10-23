.. index::
    single: Console; Console arguments

Understanding how Console Arguments Are Handled
===============================================

It can be difficult to understand the way arguments are handled by the console application.
The Symfony Console application, like many other CLI utility tools, follows the behavior
described in the `docopt`_ standards.

Have a look at the following command that has three options::

    namespace Acme\Console\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputDefinition;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

    class DemoArgsCommand extends Command
    {
        protected function configure()
        {
            $this
                ->setName('demo:args')
                ->setDescription('Describe args behaviors')
                ->setDefinition(
                    new InputDefinition(array(
                        new InputOption('foo', 'f'),
                        new InputOption('bar', 'b', InputOption::VALUE_REQUIRED),
                        new InputOption('cat', 'c', InputOption::VALUE_OPTIONAL),
                    ))
                );
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
           // ...
        }
    }

Since the ``foo`` option doesn't accept a value, it will be either ``false``
(when it is not passed to the command) or ``true`` (when ``--foo`` was passed
by the user). The value of the ``bar`` option (and its ``b`` shortcut respectively)
is required. It can be separated from the option name either by spaces or
``=`` characters. The ``cat`` option (and its ``c`` shortcut) behaves similar
except that it doesn't require a value. Have a look at the following table
to get an overview of the possible ways to pass options:

=====================  =========  ============  ============
Input                  ``foo``    ``bar``       ``cat``
=====================  =========  ============  ============
``--bar=Hello``        ``false``  ``"Hello"``   ``null``
``--bar Hello``        ``false``  ``"Hello"``   ``null``
``-b=Hello``           ``false``  ``"=Hello"``  ``null``
``-b Hello``           ``false``  ``"Hello"``   ``null``
``-bHello``            ``false``  ``"Hello"``   ``null``
``-fcWorld -b Hello``  ``true``   ``"Hello"``   ``"World"``
``-cfWorld -b Hello``  ``false``  ``"Hello"``   ``"fWorld"``
``-cbWorld``           ``false``  ``null``      ``"bWorld"``
=====================  =========  ============  ============

Things get a little bit more tricky when the command also accepts an optional
argument::

    // ...

    new InputDefinition(array(
        // ...
        new InputArgument('arg', InputArgument::OPTIONAL),
    ));

You might have to use the special ``--`` separator to separate options from
arguments. Have a look at the fifth example in the following table where it
is used to tell the command that ``World`` is the value for ``arg`` and not
the value of the optional ``cat`` option:

==============================  =================  ===========  ===========
Input                           ``bar``            ``cat``      ``arg``
==============================  =================  ===========  ===========
``--bar Hello``                 ``"Hello"``        ``null``     ``null``
``--bar Hello World``           ``"Hello"``        ``null``     ``"World"``
``--bar "Hello World"``         ``"Hello World"``  ``null``     ``null``
``--bar Hello --cat World``     ``"Hello"``        ``"World"``  ``null``
``--bar Hello --cat -- World``  ``"Hello"``        ``null``     ``"World"``
``-b Hello -c World``           ``"Hello"``        ``"World"``  ``null``
==============================  =================  ===========  ===========

.. _docopt: http://docopt.org/
