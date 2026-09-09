Render Primitives
=================

A widget returns its content from ``render()``, and Tui applies the
padding, the border and the background around it. Three primitives let a
widget of your own take over the result: ``postRender()`` rewrites the
finished box, and two ``AnsiUtils`` helpers walk and cut the ANSI lines
a render produces. Writing a widget itself is covered in
:doc:`/tui/widgets/basics`.

Post-Processing a Render
------------------------

Tui applies the padding, the border and the background after
``render()`` returns, so ``render()`` cannot touch them. Override
``postRender()``, which the renderer calls with the finished lines and
whose result the parent receives::

    use Symfony\Component\Tui\Render\RenderContext;
    use Symfony\Component\Tui\Widget\AbstractWidget;

    final class SpoilerPanel extends AbstractWidget
    {
        private bool $revealed = false;

        public function render(RenderContext $context): array
        {
            // ...
        }

        public function postRender(array $lines, RenderContext $context): array
        {
            if ($this->revealed) {
                return $lines;
            }

            return array_map(
                static fn () => str_repeat('░', $context->getColumns()),
                $lines
            );
        }

        public function reveal(): void
        {
            $this->revealed = true;
            $this->invalidate();
        }
    }

This is the place to paint a layer under the border of a widget, to dim
it or to mask a region of it. The lines you return must not be wider
than ``$context->getColumns()``, the width of the whole box, border
included, and each of them stands for one terminal row: the renderer
throws a ``RenderException`` otherwise.

The renderer caches the result along with the render, so a widget that
animates its post-processing calls ``invalidate()`` to produce a new
frame, as the example above does. See :doc:`/tui/topics/tick_loop` to
drive such a widget over time.

Walking the Cells of a Line
---------------------------

``AnsiUtils::walkCells()`` returns a generator that walks a rendered
line in a single pass, yielding one token per escape sequence and one
per grapheme cluster::

    use Symfony\Component\Tui\Ansi\AnsiUtils;

    // each token carries:
    //   "text":  the grapheme, the escape sequence, or a single byte
    //            when the line is not valid UTF-8
    //   "col":   the column the token starts at
    //   "width": the display width, 0 for an escape sequence
    //   "bg":    whether an SGR background is active at this point
    $rebuilt = '';

    foreach (AnsiUtils::walkCells($line) as $token) {
        $rebuilt .= $token['text'];
    }

    // $rebuilt === $line

Concatenating the ``text`` of every token gives the original line back,
so a pass over the tokens can rebuild a line while changing only the
cells it cares about: mask a region, recolor a column, overlay another
line cell by cell.

``bg`` says whether an SGR background is active at that point, which is
what a :doc:`compositing </tui/topics/compositing>` pass reads to let
the background of a widget win over a painted layer. The escape
sequences that are not SGR, such as cursor moves, erases, OSC
hyperlinks and APC markers, pass through as zero-width tokens and never
change ``bg``.

Slicing a Line to an Exact Width
--------------------------------

``AnsiUtils::sliceToWidth()`` cuts a line to a range of columns and
always returns exactly the requested number of them::

    $left = AnsiUtils::sliceToWidth($line, 0, 20);
    $right = AnsiUtils::sliceToWidth($line, 20, 20);

A cell wider than one column, a wide character or a tab, becomes spaces
for the columns that fall inside the range, and ``sliceToWidth()`` pads
a line shorter than the range with spaces. Adjacent slices of the same
line therefore cover every column exactly once, which is what a
fixed-width region composed of slices needs. A slice keeps the escape
sequences from before the range, so it renders with the style in force,
and drops those past it.
