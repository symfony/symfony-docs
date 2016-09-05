How to Color and Style the Console Output
=========================================

By using colors in the command output, you can distinguish different types of
output (e.g. important messages, titles, comments, etc.).

.. note::

    By default, the Windows command console doesn't support output coloring. The
    Console component disables output coloring for Windows systems, but if your
    commands invoke other scripts which emit color sequences, they will be
    wrongly displayed as raw escape characters. Install the `Cmder`_, `ConEmu`_, `ANSICON`_
    or `Mintty`_ (used by default in GitBash and Cygwin) free applications
    to add coloring support to your Windows command console.

Using Color Styles
------------------

Whenever you output text, you can surround the text with tags to color its
output. For example::

    // green text
    $output->writeln('<info>foo</info>');

    // yellow text
    $output->writeln('<comment>foo</comment>');

    // black text on a cyan background
    $output->writeln('<question>foo</question>');

    // white text on a red background
    $output->writeln('<error>foo</error>');

The closing tag can be replaced by ``</>``, which revokes all formatting options
established by the last opened tag.

It is possible to define your own styles using the
:class:`Symfony\\Component\\Console\\Formatter\\OutputFormatterStyle` class::

    use Symfony\Component\Console\Formatter\OutputFormatterStyle;

    // ...
    $style = new OutputFormatterStyle('red', 'yellow', array('bold', 'blink'));
    $output->getFormatter()->setStyle('fire', $style);

    $output->writeln('<fire>foo</fire>');

Available foreground and background colors are: ``black``, ``red``, ``green``,
``yellow``, ``blue``, ``magenta``, ``cyan`` and ``white``.

And available options are: ``bold``, ``underscore``, ``blink``, ``reverse``
(enables the "reverse video" mode where the background and foreground colors
are swapped) and ``conceal`` (sets the foreground color to transparent, making
the typed text invisible - although it can be selected and copied; this option is
commonly used when asking the user to type sensitive information).

You can also set these colors and options directly inside the tagname::

    // green text
    $output->writeln('<fg=green>foo</>');

    // black text on a cyan background
    $output->writeln('<fg=black;bg=cyan>foo</>');

    // bold text on a yellow background
    $output->writeln('<bg=yellow;options=bold>foo</>');

.. _Cmder: http://cmder.net/
.. _ConEmu: https://conemu.github.io/
.. _ANSICON: https://github.com/adoxa/ansicon/releases
.. _Mintty: https://mintty.github.io/
