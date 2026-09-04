TextWidget
==========

``TextWidget`` displays static text in the terminal. It is the most
basic widget and the one you will use most often for labels, paragraphs
and decorative headings.

When to Use
-----------

Use ``TextWidget`` whenever you need to display a read-only piece of
text: a title, a status message, a paragraph of instructions, etc.
For user-editable text, use :doc:`/tui/widgets/input` or :doc:`/tui/widgets/editor` instead.

Basic Usage
-----------

Create a ``TextWidget`` with a string and add it to the Tui::

    use Symfony\Component\Tui\Widget\TextWidget;

    $text = new TextWidget('Hello, terminal!');
    $tui->add($text);

Update the text at any time with ``setText()``::

    $text->setText('New content');

Word Wrapping and Truncation
----------------------------

By default, text wraps to multiple lines when it exceeds the available
width. If you prefer to truncate long lines with an ellipsis instead,
pass ``truncate: true`` to the constructor::

    $text = new TextWidget('A very long line...', truncate: true);

FIGlet Fonts
------------

When a FIGlet font is set via the Style system, the text is rendered as
large ASCII art. Five fonts are bundled: ``big``, ``small``, ``slant``,
``standard`` and ``mini``.

Apply a font with a Tailwind utility class::

    $title = new TextWidget('Welcome');
    $title->addStyleClass('font-big');

Or through a stylesheet rule::

    use Symfony\Component\Tui\Style\Style;

    $stylesheet->addRule('.title', new Style(font: 'big'));
    $title->addStyleClass('title');

Custom FIGlet fonts can be registered via the ``FontRegistry``
passed to the ``Renderer``::

    use Symfony\Component\Tui\Render\Renderer;
    use Symfony\Component\Tui\Widget\Figlet\FontRegistry;

    $fontRegistry = new FontRegistry();
    $fontRegistry->register('brand', '/path/to/font.flf');

    $renderer = new Renderer(fontRegistry: $fontRegistry);
    $tui = new Tui(renderer: $renderer);

Events
------

``TextWidget`` does not emit any events. It is a pure display widget
with no focus or input handling.
