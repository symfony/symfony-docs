.. index::
    single: Console Helpers; Formatter Helper

Formatter Helper
================

The Formatter helpers provides functions to format the output with colors.
You can do more advanced things with this helper than you can in
:ref:`components-console-coloring`.

The :class:`Symfony\\Component\\Console\\Helper\\FormatterHelper` is included
in the default helper set, which you can get by calling
:method:`Symfony\\Component\\Console\\Command\\Command::getHelperSet`::

    $formatter = $this->getHelperSet()->get('formatter');

The methods return a string, which you'll usually render to the console by
passing it to the
:method:`OutputInterface::writeln <Symfony\\Component\\Console\\Output\\OutputInterface::writeln>` method.

Print Messages in a Section
---------------------------

Symfony offers a defined style when printing a message that belongs to some
"section". It prints the section in color and with brackets around it and the
actual message to the right of this. Minus the color, it looks like this:

.. code-block:: text

    [SomeSection] Here is some message related to that section

To reproduce this style, you can use the
:method:`Symfony\\Component\\Console\\Helper\\FormatterHelper::formatSection`
method::

    $formattedLine = $formatter->formatSection(
        'SomeSection',
        'Here is some message related to that section'
    );
    $output->writeln($formattedLine);

Print Messages in a Block
-------------------------

Sometimes you want to be able to print a whole block of text with a background
color. Symfony uses this when printing error messages.

If you print your error message on more than one line manually, you will
notice that the background is only as long as each individual line. Use the
:method:`Symfony\\Component\\Console\\Helper\\FormatterHelper::formatBlock`
to generate a block output::

    $errorMessages = array('Error!', 'Something went wrong');
    $formattedBlock = $formatter->formatBlock($errorMessages, 'error');
    $output->writeln($formattedBlock);

As you can see, passing an array of messages to the
:method:`Symfony\\Component\\Console\\Helper\\FormatterHelper::formatBlock`
method creates the desired output. If you pass ``true`` as third parameter, the
block will be formatted with more padding (one blank line above and below the
messages and 2 spaces on the left and right).

The exact "style" you use in the block is up to you. In this case, you're using
the pre-defined ``error`` style, but there are other styles, or you can create
your own. See :ref:`components-console-coloring`.
