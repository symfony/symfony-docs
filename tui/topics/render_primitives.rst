Render Primitives
=================

Tui applies the padding, the border and the background of a widget after
its ``render()`` method returns, so ``render()`` can't change them. When a
widget needs to rewrite its whole box (paint a layer under the border, dim
it, mask part of it), override ``postRender()`` and use the ``AnsiUtils``
helpers to walk and cut the ANSI lines of the box.

Post-Processing a Render
------------------------

The renderer calls ``postRender()`` with the finished lines of the widget
(content, padding, border and background included) and passes its result
to the parent::

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

            // $context->getColumns() is the width of the whole box, border included
            return array_map(
                static fn (): string => str_repeat('░', $context->getColumns()),
                $lines,
            );
        }

        public function reveal(): void
        {
            $this->revealed = true;
            // the result of postRender() is cached along with the render
            $this->invalidate();
        }
    }

Each returned line is one terminal row. The renderer throws a
``RenderException`` when any of them is wider than ``$context->getColumns()``.

Because the result is cached, a widget that animates its post-processing
must call ``invalidate()`` for every new frame. See
:doc:`/tui/topics/tick_loop` to drive such a widget over time.

Walking the Cells of a Line
---------------------------

``AnsiUtils::walkCells()`` walks a rendered line in a single pass and
yields one token per escape sequence and one per grapheme cluster.
Concatenating the ``text`` of all tokens gives back the original line, so
you can rebuild a line while changing only some of its cells (mask a
region, recolor a column, overlay another line cell by cell)::

    use Symfony\Component\Tui\Ansi\AnsiUtils;

    // each token is an array with these keys:
    //   'text':  the grapheme, the escape sequence, or a single byte
    //            when the line is not valid UTF-8
    //   'col':   the column where the token starts
    //   'width': the display width (0 for escape sequences)
    //   'bg':    whether an SGR background is active at this point
    $maskedLine = '';
    foreach (AnsiUtils::walkCells($line) as $token) {
        $isMasked = $token['width'] > 0
            && $token['col'] >= 10 && $token['col'] < 20;
        $maskedLine .= $isMasked ? str_repeat('*', $token['width']) : $token['text'];
    }

Escape sequences other than SGR (cursor moves, erases, OSC hyperlinks, APC
markers) are yielded as zero-width tokens and never change ``bg``. When
overlaying one line on another, use ``bg`` to keep the cells that set their
own background on top, as
:doc:`transparent layers </tui/topics/compositing>` do.

Slicing a Line to an Exact Width
--------------------------------

``AnsiUtils::sliceToWidth()`` extracts a range of columns from a line and
always returns exactly the requested number of columns::

    $left = AnsiUtils::sliceToWidth($line, 0, 20);
    $right = AnsiUtils::sliceToWidth($line, 20, 20);

A cell wider than one column (a wide character or a tab) that crosses a
boundary of the range becomes spaces for the columns inside the range, and
a line shorter than the range is padded with spaces. This way, adjacent
slices of the same line cover every column exactly once, which is needed
to compose fixed-width regions out of slices.

The slice keeps the escape sequences found before the range, so its
content renders with the style in force at that point, and drops the ones
found after the range.
