How to Color and Style the Console Output
=========================================

Symfony provides an optional :doc:`console style </console/style>` to render the
input and output of commands in a consistent way. If you prefer to apply your
own style, use the utilities explained in this article to show colors in the command
output (e.g. to differentiate between important messages, titles, comments, etc.).

.. note::

    By default, the Windows command console doesn't support output coloring. The
    Console component disables output coloring for Windows systems, but if your
    commands invoke other scripts which emit color sequences, they will be
    wrongly displayed as raw escape characters. Install the `Cmder`_, `ConEmu`_,
    `ANSICON`_, `Mintty`_ (used by default in GitBash and Cygwin) or `Hyper`_
    free applications to add coloring support to your Windows command console.

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
    $outputStyle = new OutputFormatterStyle('red', '#ff0', ['bold', 'blink']);
    $output->getFormatter()->setStyle('fire', $outputStyle);

    $output->writeln('<fire>foo</>');

Any hex color is supported for foreground and background colors. Besides that, these named colors are supported:
``black``, ``red``, ``green``, ``yellow``, ``blue``, ``magenta``, ``cyan``, ``white``,
``gray``, ``bright-red``, ``bright-green``, ``bright-yellow``, ``bright-blue``,
``bright-magenta``, ``bright-cyan`` and ``bright-white``.

.. note::

    If the terminal doesn't support true colors, the given color is replaced by
    the nearest color depending on the terminal capabilities. E.g. ``#c0392b`` is
    degraded to ``#d75f5f`` in 256-color terminals and to ``red`` in 8-color terminals.

And available options are: ``bold``, ``underscore``, ``blink``, ``reverse``
(enables the "reverse video" mode where the background and foreground colors
are swapped) and ``conceal`` (sets the foreground color to transparent, making
the typed text invisible - although it can be selected and copied; this option is
commonly used when asking the user to type sensitive information).

You can also set these colors and options directly inside the tag name::

    // using named colors
    $output->writeln('<fg=green>foo</>');

    // using hexadecimal colors
    $output->writeln('<fg=#c0392b>foo</>');

    // black text on a cyan background
    $output->writeln('<fg=black;bg=cyan>foo</>');

    // bold text on a yellow background
    $output->writeln('<bg=yellow;options=bold>foo</>');

    // bold text with underscore
    $output->writeln('<options=bold,underscore>foo</>');

.. note::

    If you need to render a tag literally, escape it with a backslash: ``\<info>``
    or use the :method:`Symfony\\Component\\Console\\Formatter\\OutputFormatter::escape`
    method to escape all the tags included in the given string.

Displaying Clickable Links
~~~~~~~~~~~~~~~~~~~~~~~~~~

Commands can use the special ``<href>`` tag to display links similar to the
``<a>`` elements of web pages::

    $output->writeln('<href=https://symfony.com>Symfony Homepage</>');

If your terminal belongs to the `list of terminal emulators that support links`_
you can click on the *"Symfony Homepage"* text to open its URL in your default
browser. Otherwise, you'll see *"Symfony Homepage"* as regular text and the URL
will be lost.

.. _Cmder: https://github.com/cmderdev/cmder
.. _ConEmu: https://conemu.github.io/
.. _ANSICON: https://github.com/adoxa/ansicon/releases
.. _Mintty: https://mintty.github.io/
.. _Hyper: https://hyper.is/
.. _`list of terminal emulators that support links`: https://gist.github.com/egmontkob/eb114294efbcd5adb1944c9f3cb5feda
