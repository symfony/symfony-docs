Extending Widgets
=================

Three primitives let a widget of your own take over what the component
draws: ``postRender()`` rewrites the finished box, and two ``AnsiUtils``
helpers walk and cut the ANSI lines a render produces.

Post-Processing a Render
------------------------

``render()`` returns the content of a widget, before the component
applies its padding, border and background. To rewrite the box as a
whole, override ``postRender()``, which the renderer calls with the
finished lines and whose result the parent receives::

    use Symfony\Component\Tui\Render\RenderContext;
    use Symfony\Component\Tui\Widget\AbstractWidget;

    final class DimmedPanel extends AbstractWidget
    {
        public function render(RenderContext $context): array
        {
            // ...
        }

        public function postRender(array $lines, RenderContext $context): array
        {
            return array_map(
                static fn (string $line) => "\e[2m".$line."\e[22m",
                $lines,
            );
        }
    }

This is the place to paint a layer under the border of the widget, to
dim it or to mask a region of it, none of which ``render()`` can do,
since the chrome is applied after it returns.

The width contract of ``render()`` holds for the lines you return, and
the renderer caches the result along with the render: a widget that
animates its post-processing calls ``invalidate()`` to produce a new
frame.

Walking the Cells of a Line
---------------------------

``AnsiUtils::walkCells()`` splits a rendered line into tokens, one per
escape sequence and one per grapheme cluster, and yields them in
order::

    use Symfony\Component\Tui\Ansi\AnsiUtils;

    $rebuilt = '';

    foreach (AnsiUtils::walkCells($line) as $token) {
        $token['text'];  // the grapheme, or the escape sequence
        $token['col'];   // the column the token starts at
        $token['width']; // the display width, 0 for an escape sequence
        $token['bg'];    // whether an SGR background is active here

        $rebuilt .= $token['text'];
    }

    // $rebuilt === $line

Concatenating the ``text`` of every token gives the original line back,
so a pass over the tokens can rebuild a line while changing only the
cells it cares about: mask a region, recolor a column, overlay another
line cell by cell.

``bg`` says whether an SGR background is active at that point, which is
what a compositing pass reads to let the background of a widget win over
a painted layer. The escape sequences that are not SGR, such as cursor
moves, erases, OSC hyperlinks and APC markers, pass through as
zero-width tokens and never change ``bg``.

Slicing a Line to an Exact Width
--------------------------------

``AnsiUtils::sliceToWidth()`` cuts a line to a range of columns and
always returns exactly the requested number of them::

    $slice = AnsiUtils::sliceToWidth($line, 10, 20); // 20 columns wide

A wide character straddling either boundary becomes spaces for the
columns that fall inside the range, and a line shorter than the range is
padded with spaces. Adjacent slices of the same line therefore cover
every column exactly once, which is what composing fixed-width regions
out of slices requires. The escape sequences before the range are
carried into the slice, so the style in force is preserved; those past
the range are dropped.
