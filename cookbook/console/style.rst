.. index::
   single: Console; Style commands

How to Style a Console Command
==============================

.. versionadded:: 2.7
    Symfony Styles for console commands were introduced in Symfony 2.7.

One of the most boring tasks when creating console commands is to deal with the
styling of the command's input and output. Displaying titles and tables or asking
questions to the user involves a lot of repetitive code.

Consider for example the code used to display the title of the following command::

    // src/AppBundle/Command/GreetCommand.php
    namespace AppBundle\Command;

    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class GreetCommand extends ContainerAwareCommand
    {
        // ...

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $output->writeln(array(
                '<info>Lorem Ipsum Dolor Sit Amet</>',
                '<info>==========================</>',
                '',
            ));

            // ...
        }
    }

Displaying a simple title requires three lines of code, to change the font color,
underline the contents and leave an additional blank line after the title. Dealing
with styles is required for well-designed commands, but it complicates their code
unnecessarily.

In order to reduce that boilerplate code, Symfony commands can optionally use the
**Symfony Style Guide**. These styles are implemented as a set of helper methods
which allow to create *semantic* commands and forget about their styling.

Basic Usage
-----------

In your command, instantiate the :class:`Symfony\\Component\\Console\\Style\\SymfonyStyle`
class and pass the ``$input`` and ``$output`` variables as its arguments. Then,
you can start using any of its helpers, such as ``title()``, which displays the
title of the command::

    // src/AppBundle/Command/GreetCommand.php
    namespace AppBundle\Command;

    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class GreetCommand extends ContainerAwareCommand
    {
        // ...

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $io = new SymfonyStyle($input, $output);
            $io->title('Lorem Ipsum Dolor Sit Amet');

            // ...
        }
    }

Helper Methods
--------------

The :class:`Symfony\\Component\\Console\\Style\\SymfonyStyle` class defines some
helper methods that cover the most common interactions performed by console commands.

Titling Methods
~~~~~~~~~~~~~~~

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::title`
    It displays the given string as the command title. This method is meant to
    be used only once in a given command, but nothing prevents you to use it
    repeatedly::

        $io->title('Lorem ipsum dolor sit amet');

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::section`
    It displays the given string as the title of some command section. This is
    only needed in complex commands which want to better separate their contents::

        $io->section('Adding a User');

        // ...

        $io->section('Generating the Password');

        // ...

Content Methods
~~~~~~~~~~~~~~~

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::text`
    It displays the given string or array of strings as regular text. This is
    useful to render help messages and instructions for the user running the
    command::

        // use simple strings for short messages
        $io->text('Lorem ipsum dolor sit amet');

        // ...

        // consider using arrays when displaying long messages
        $io->text(array(
            'Lorem ipsum dolor sit amet',
            'Consectetur adipiscing elit',
            'Aenean sit amet arcu vitae sem faucibus porta',
        ));

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::listing`
    It displays an unordered list of elements passed as an array::

        $io->listing(array(
            'Element #1 Lorem ipsum dolor sit amet',
            'Element #2 Lorem ipsum dolor sit amet',
            'Element #3 Lorem ipsum dolor sit amet',
        ));

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::table`
    It displays the given array of headers and rows as a compact table::

        $io->table(
            array('Header 1', 'Header 2'),
            array(
                array('Cell 1-1', 'Cell 1-2'),
                array('Cell 2-1', 'Cell 2-2'),
                array('Cell 3-1', 'Cell 3-2'),
            )
        );

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::newLine`
    It displays a blank line in the command output. Although it may seem useful,
    most of the times you won't need it at all. The reason is that every helper
    already adds their own blank lines, so you don't have to care about the
    vertical spacing::

        // outputs a single blank line
        $io->newLine();

        // outputs three consecutive blank lines
        $io->newLine(3);

Admonition Methods
~~~~~~~~~~~~~~~~~~

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::note`
    It displays the given string or array of strings as a highlighted admonition.
    Use this helper sparingly to avoid cluttering command's output::

        // use simple strings for short notes
        $io->note('Lorem ipsum dolor sit amet');

        // ...

        // consider using arrays when displaying long notes
        $io->note(array(
            'Lorem ipsum dolor sit amet',
            'Consectetur adipiscing elit',
            'Aenean sit amet arcu vitae sem faucibus porta',
        ));

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::caution`
    Similar to the ``note()`` helper, but the contents are more prominently
    highlighted. The resulting contents resemble an error message, so you should
    avoid using this helper unless strictly necessary::

        // use simple strings for short caution message
        $io->caution('Lorem ipsum dolor sit amet');

        // ...

        // consider using arrays when displaying long caution messages
        $io->caution(array(
            'Lorem ipsum dolor sit amet',
            'Consectetur adipiscing elit',
            'Aenean sit amet arcu vitae sem faucibus porta',
        ));

Progress Bar Methods
~~~~~~~~~~~~~~~~~~~~

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::progressStart`
    It displays a progress bar with a number of steps equal to the argument passed
    to the method (don't pass any value if the length of the progress bar is
    unknown)::

        // displays a progress bar of unknown length
        $io->progressStart();

        // displays a 100-step length progress bar
        $io->progressStart(100);

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::progressAdvance`
    It makes the progress bar advance the given number of steps (or ``1`` step
    if no argument is passed)::

        // advances the progress bar 1 step
        $io->progressAdvance();

        // advances the progress bar 10 steps
        $io->progressAdvance(10);

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::progressFinish`
    It finishes the progress bar (filling up all the remaining steps when its
    length is known)::

        $io->progressFinish();

User Input Methods
~~~~~~~~~~~~~~~~~~

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::ask`
    It asks the user to provide some value::

        $io->ask('What is your name?');

    You can pass the default value as the second argument so the user can simply
    hit the <Enter> key to select that value::

        $io->ask('Where are you from?', 'United States');

    In case you need to validate the given value, pass a callback validator as
    the third argument::

        $io->ask('Number of workers to start', 1, function ($number) {
            if (!is_integer($number)) {
                throw new \RuntimeException('You must type an integer.');
            }

            return $number;
        });

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::askHidden`
    It's very similar to the ``ask()`` method but the user's input will be hidden
    and it cannot define a default value. Use it when asking for sensitive information::

        $io->askHidden('What is your password?');

        // validates the given answer
        $io->askHidden('What is your password?', function ($password) {
            if (empty($password)) {
                throw new \RuntimeException('Password cannot be empty.');
            }

            return $password;
        });

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::confirm`
    It asks a Yes/No question to the user and it only returns ``true`` or ``false``::

        $io->confirm('Restart the web server?');

    You can pass the default value as the second argument so the user can simply
    hit the <Enter> key to select that value::

        $io->confirm('Restart the web server?', true);

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::choice`
    It asks a question whose answer is constrained to the given list of valid
    answers::

        $io->choice('Select the queue to analyze', array('queue1', 'queue2', 'queue3'));

    You can pass the default value as the third argument so the user can simply
    hit the <Enter> key to select that value::

        $io->choice('Select the queue to analyze', array('queue1', 'queue2', 'queue3'), 'queue1');

Result Methods
~~~~~~~~~~~~~~

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::success`
    It displays the given string or array of strings highlighted as a successful
    message (with a green background and the ``[OK]`` label). It's meant to be
    used once to display the final result of executing the given command, but you
    can use it repeatedly during the execution of the command::

        // use simple strings for short success messages
        $io->success('Lorem ipsum dolor sit amet');

        // ...

        // consider using arrays when displaying long success messages
        $io->success(array(
            'Lorem ipsum dolor sit amet',
            'Consectetur adipiscing elit',
        ));

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::warning`
    It displays the given string or array of strings highlighted as a warning
    message (with a read background and the ``[WARNING]`` label). It's meant to be
    used once to display the final result of executing the given command, but you
    can use it repeatedly during the execution of the command::

        // use simple strings for short warning messages
        $io->warning('Lorem ipsum dolor sit amet');

        // ...

        // consider using arrays when displaying long warning messages
        $io->warning(array(
            'Lorem ipsum dolor sit amet',
            'Consectetur adipiscing elit',
        ));

:method:`Symfony\\Component\\Console\\Style\\StyleInterface::error`
    It displays the given string or array of strings highlighted as an error
    message (with a read background and the ``[ERROR]`` label). It's meant to be
    used once to display the final result of executing the given command, but you
    can use it repeatedly during the execution of the command::

        // use simple strings for short error messages
        $io->error('Lorem ipsum dolor sit amet');

        // ...

        // consider using arrays when displaying long error messages
        $io->error(array(
            'Lorem ipsum dolor sit amet',
            'Consectetur adipiscing elit',
        ));

Defining your Own Styles
------------------------

If you don't like the design of the commands that use the Symfony Style, you can
define your own set of console styles. Just create a class that implements the
:class:`Symfony\\Component\\Console\\Style\\StyleInterface`::

    namespace AppBundle\Console;

    use Symfony\Component\Console\Style\StyleInterface;

    class CustomStyle implements StyleInterface
    {
        // ...implement the methods of the interface
    }

Then, instantiate this custom class instead of the default ``SymfonyStyle`` in
your commands. Thanks to the ``StyleInterface`` you won't need to change the code
of your commands to change their appearance::

    namespace AppBundle\Console;

    use AppBundle\Console\CustomStyle;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;

    class GreetCommand extends ContainerAwareCommand
    {
        // ...

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            // Before
            // $io = new SymfonyStyle($input, $output);

            // After
            $io = new CustomStyle($input, $output);

            // ...
        }
    }
