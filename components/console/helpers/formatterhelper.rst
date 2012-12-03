.. index::
    single: Console Helpers; Formatter Helper

Formatter Helper
================

The Formatter helpers provides functions to format the output with colors.
You can do more advanced things with this helper than the things in
:ref:`components-console-coloring`.

The Formatter Helper is included in the default helper set, which you can
get by calling
:method:`Symfony\\Component\\Console\\Command\\Command::getHelperSet`::

    $formatter = $this->getHelperSet()->get('formatter');

The methods return a string which in most cases you want to write
that data to the console with
:method:`Symfony\\Component\\Console\\Output\\Output::writeln`.

Print messages in a section
---------------------------

Symfony uses a defined style when printing to the command line.
It prints the section in color and with brackets around and the
actual message behind this section indication.

To reproduce this style, you can use the `` formatSection`` method like this::

    $formattedLine = $formatter->formatSection('SomeCommand', 'Some output from within the SomeCommand');
    $output->writeln($formattedLine);
    
Print messages in a block
-------------------------

Sometimes you want to be able to print a whole block of text with a background
color. Symfony uses this when printing error messages.

If you print your error message on more than one line manually, you will 
notice that the background is only as long as each individual line. You
can use the `formatBlock` method to create a real block output::

    $errorMessages = array('Error!', 'Something went wrong');
    $formattedBlock = $formatter->formatBlock($errorMessages, 'error');
    
As you can see, passing an array of messages to the `formatBlock` method creates
the desired output. If you pass `true` as third parameter, the block will be 
formatted with more padding (one blank line above and below the messages and 2 
spaces on the left and right).