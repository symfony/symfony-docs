.. index::
    single: Console; Console arguments

Understand how Console Arguments are Handled
============================================

It can be difficult to understand the way arguments are handled by the console application.
The Symfony Console application, like many other CLI utility tools, follows the behavior
described in the `docopt`_ standards.

Let's see a complete example on how the arguments are understood by Console application,
regarding to the Console Options or Arguments defined in the application::

   namespace Acme\Console\Command;

      use Symfony\Component\Console\Command\Command;
      use Symfony\Component\Console\Input\InputArgument;
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
                        new InputOption('bar', 'br', InputOption::VALUE_REQUIRED),
                        new InputOption('baz', 'bz', InputOption::VALUE_OPTIONAL)
                    )
                )
            ;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
           // ...
        }
      }

Let's take a look to the values results for differents inputs:

====================  =========================================
Input                 Values
====================  =========================================
--bar=Hello           foo = false, bar = "Hello"
--bar Hello           foo = false, bar = "Hello"
-br=Hello             foo = false, bar = "Hello"
-br Hello             foo = false, bar = "Hello"
-brHello              foo = false, bar = "Hello"
-fbzWorld -br Hello   foo = true, bar = "Hello", baz = "World"
-bzfWorld -br Hello   foo = false, bar = "Hello", baz ="fWorld"
-bzbrWorld            foo = false, bz = "brWorld", baz = null
====================  =========================================


Now, assume there is also an optional argument in the input definition::

   new InputDefinition(array(
       // ...
       new InputArgument('arg', InputArgument::OPTIONAL),
   ));

==========================  ========================================
Input                       Values
==========================  ========================================
--bar Hello                 bar = "Hello", arg = null
--bar Hello World           bar = "Hello", arg = "World"
--bar Hello --baz World     bar = "Hello", baz = "World", arg = null
--bar Hello --baz -- World  bar = "Hello", baz = null, arg = "World"
-b Hello -bz World          bar = "Hello", baz = "World", arg = null
==========================  ========================================

The fourth example shows the special ``--`` seperator which -as you can read
in docopt- seperates the options from the arguments. By that, ``World`` is
no longer interpreted as a value of the ``baz`` option (which has an optional value),
but as the value for the argument.

.. _docopt: http://docopt.org/

