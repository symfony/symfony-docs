.. index::
    single: Console Helpers; Formatter Helper

Formatter Helper
================

The Formatter helpers provides functions to format the output with colors.
You can do more advanced things with this helper than the things in
:ref:`components-console-coloring`.

The FormatterHelper is included in the default helper set, which you can
get by calling
:method:`Symfony\\Component\\Console\\Command\\Command::getHelperSet`::

    $formatter = $this->getHelperSet()->get('formatter');

The methods return a string with the data for the console, you should decide
what you are going to do with that data. In most cases you want to write
that data to the console with
:method:`Symfony\\Component\\Console\\Output\\Output::writeln`.

Print messages in a section
---------------------------

Assume you want to print
