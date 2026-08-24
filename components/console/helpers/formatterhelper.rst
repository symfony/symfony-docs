Formatter Helper
================

The :class:`Symfony\\Component\\Console\\Helper\\FormatterHelper` helper provides
functions to format the output with colors. You can do more advanced things with
this helper than you can with the :doc:`basic colors and styles </console/style>`::

    $formatter = new FormatterHelper();

The methods return a string, which you'll usually render to the console by
passing it to the
:method:`OutputInterface::writeln <Symfony\\Component\\Console\\Output\\OutputInterface::writeln>`
method.

.. note::

    As an alternative, consider using the
    :ref:`SymfonyStyle <symfony-style-blocks>` to display stylized blocks.

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

    $errorMessages = ['Error!', 'Something went wrong'];
    $formattedBlock = $formatter->formatBlock($errorMessages, 'error');
    $output->writeln($formattedBlock);

As you can see, passing an array of messages to the
:method:`Symfony\\Component\\Console\\Helper\\FormatterHelper::formatBlock`
method creates the desired output. If you pass ``true`` as third parameter, the
block will be formatted with more padding (one blank line above and below the
messages and 2 spaces on the left and right).

The exact "style" you use in the block is up to you. In this case, you're using
the pre-defined ``error`` style, but there are other styles (``info``,
``comment``, ``question``), or you can create your own.
See :doc:`/console/style`.

Print Truncated Messages
------------------------

Sometimes you want to print a message truncated to an explicit character length.
This is possible with the
:method:`Symfony\\Component\\Console\\Helper\\FormatterHelper::truncate` method.

If you would like to truncate a very long message, for example, to 7 characters,
you can write::

    $message = "This is a very long message, which should be truncated";
    $truncatedMessage = $formatter->truncate($message, 7);
    $output->writeln($truncatedMessage);

And the output will be:

.. code-block:: text

    This is...

The message is truncated to the given length, then the suffix is appended to the end
of that string.

Negative String Length
~~~~~~~~~~~~~~~~~~~~~~

If the length is negative, the number of characters to truncate is counted
from the end of the string::

    $truncatedMessage = $formatter->truncate($message, -5);

This will result in:

.. code-block:: text

    This is a very long message, which should be trun...

Custom Suffix
~~~~~~~~~~~~~

By default, the ``...`` suffix is used. If you wish to use a different suffix,
pass it as the third argument to the method.
The suffix is always appended, unless the truncated length is longer than the
message plus the suffix length.
If you don't want to use suffix at all, pass an empty string::

    $truncatedMessage = $formatter->truncate($message, 7, '!!'); // result: This is!!
    $truncatedMessage = $formatter->truncate($message, 7, '');   // result: This is

    $truncatedMessage = $formatter->truncate('test', 10);
    // result: test
    // because length of the "test..." string is shorter than 10

Formatting Time
---------------

Sometimes you want to format seconds to time. This is possible with the
:method:`Symfony\\Component\\Console\\Helper\\Helper::formatTime` method.
The first argument is the seconds to format and the second argument is the
precision (default ``1``) of the result::

    Helper::formatTime(0.001);         // 1 ms
    Helper::formatTime(42);            // 42 s
    Helper::formatTime(125);           // 2 min
    Helper::formatTime(125, 2);        // 2 min, 5 s
    Helper::formatTime(172799, 4);     // 1 d, 23 h, 59 min, 59 s
    Helper::formatTime(172799.056, 5); // 1 d, 23 h, 59 min, 59 s, 56 ms

Formatting Memory
-----------------

Sometimes you want to format memory to GiB, MiB, KiB and B. This is possible with the
:method:`Symfony\\Component\\Console\\Helper\\Helper::formatMemory` method.
The only argument is the memory size to format::

    Helper::formatMemory(512);                // 512 B
    Helper::formatMemory(1024);               // 1 KiB
    Helper::formatMemory(1024 * 1024);        // 1.0 MiB
    Helper::formatMemory(1024 * 1024 * 1024); // 1 GiB
