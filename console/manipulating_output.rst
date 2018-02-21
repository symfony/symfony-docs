How To Manipulate Console Output
================================

The console comes with a powerful class to print information to the terminal. This
information can be manipulated by clearing or overwriting the displayed content.

In order to manipulate the content, you need to create a new output section. An output section is
a part in the terminal where information will be displayed from the console.

A section can be manipulated individually, and multiple sections can appended to the output.

To create a new output section, you need to use the
:method:`Symfony\\Component\\Console\\Output\\ConsoleOutput::section` method::

    class MyCommand extends Command
    {
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $section = $output->section();
        }
    }

This will return an instance of of the :class:`Symfony\\Component\\Console\\Output\\ConsoleSectionOutput`

.. tip::

    You can create multiple sections by calling the
    :method:`Symfony\\Component\\Console\\Output\\ConsoleOutput::section` method multiple times.
    This will append a new section after the previous one.

.. caution::

    Displaying information in a section will always append a new line to the output.


Overwriting Output
------------------

When displaying information in the console, you can overwrite the output by using the
:method:`Symfony\\Component\\Console\\Output\\ConsoleSectionOutput::overwrite` method::

    $section->writeln('Hello');
    $section->overwrite('World!');

The only information displayed in the terminal will be ``World!`` as the first part will
be overwritten.

Clearing s Section
------------------

You can clear all the content in a section by using the
:method:`Symfony\\Component\\Console\\Output\\ConsoleSectionOutput::overwrite` method.

Clearing a section will erase all the content that is displayed in that section::

    $section->writeln('Hello World!');
    $section->clear();

This will leave your terminal clean without any output displayed.

You can also clear a specific number of lines from the output instead of clearing all the
output::

    $section->writeln('One!');
    $section->writeln('Two!');
    $section->writeln('Three!');
    $section->writeln('Four!');

    $section->clear(2);

This will only leave the lines ``One!`` and ``Two!`` displaying in the terminal.

Modifying Content In Previous Sections
--------------------------------------

When you append multiple sections to the terminal, you can manipulate the output of
only a specific section, leaving the rest intact::

    $section1 = $output->section();
    $section2 = $output->section();

    $section1->writeln('Hello World!');
    $section2->writeln('This is comes second');

    $section1->overwrite('This comes first');

This will result in the following output in the terminal:

.. code-block:: text

    This comes first
    This comes second

Displaying Multiple Progress Bars
---------------------------------

You can display multiple progress bars underneath each other, and changing the progress
of one of the bars at a time::

    $section1 = $output->section();
    $section2 = $output->section();

    $progress1 = new ProgressBar($section1);
    $progress2 = new ProgressBar($section2);

    $progress1->start(100);
    $progress2->start(100);

    $c = 0;
    while (++$c < 100) {
        $progress1->advance();

        if ($c % 2 === 0) {
            $progress2->advance(4);
        }

        usleep(500000);
    }

After a couple of iterations, the output in the terminal will look like this:

.. code-block:: text

    34/100 [=========>------------------]  34%
    68/100 [===================>--------]  68%

Appending Rows To a Table
-------------------------

If you are displaying a table in the terminal, you can append rows to an already rendered table
by using the :method:`Symfony\\Component\\Console\\Helper\\Table::appendRow` method.

This method takes the same arguments as the :method:`Symfony\\Component\\Console\\Helper\\Table::addRow`
method, but if the table is already rendered, then it will append the row to the table.

    $section = $output->section();
    $table = new Table($section);

    $table->addRow(['Row 1']);
    $table->render();

    $table->addRow(['Row 2']);

This will display the following table in the terminal:

.. code-block:: text

    +-------+
    | Row 1 |
    | Row 2 |
    +-------+
