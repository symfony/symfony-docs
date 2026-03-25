Compositing
===========

Compositing lets you merge multiple layers of content into a
single output. Each layer is a set of ANSI-formatted lines at a
position, and layers can be transparent so the content below shows
through.

This is useful for rendering effects like animated backgrounds with
text on top, watermarks, or any scenario where a widget needs to
combine independently rendered content.

The Compositor
--------------

The ``Compositor`` class merges layers in order. Layer 0 is the
base (typically opaque, filling the entire screen); subsequent
layers are painted on top:

.. code-block:: php

    use Symfony\Component\Tui\Render\Compositor;
    use Symfony\Component\Tui\Render\Layer;

    $lines = Compositor::composite(
        new Layer($backgroundLines),                     // opaque base
        new Layer($foregroundLines, transparent: true),   // transparent overlay
    );

``composite()`` returns ``string[]``, one ANSI-formatted string per
terminal row, ready to be returned from a widget's ``render()``
method.

The canvas dimensions are derived from the first (base) layer:
height is the number of lines, width is the visible width of the
first line.

The Layer Value Object
----------------------

A ``Layer`` describes content to composite:

.. code-block:: php

    use Symfony\Component\Tui\Render\Layer;

    $layer = new Layer(
        lines: $lines,           // string[] of ANSI-formatted content
        row: 0,                  // vertical offset (default: 0)
        col: 0,                  // horizontal offset (default: 0)
        transparent: false,      // transparency flag (default: false)
        width: null,             // explicit canvas width (base layer only)
        height: null,            // explicit canvas height (base layer only)
    );

All parameters except ``lines`` are optional.

The ``width`` and ``height`` parameters are only meaningful on the
base layer (the first one passed to ``composite()``). When set,
they define the canvas dimensions explicitly. When ``null``, the
canvas dimensions are derived from the base layer's content.

How Transparency Works
----------------------

When a layer is transparent (``transparent: true``):

* **Cells with content but no explicit background** inherit the
  background from the layer below. The foreground character and
  color are painted on top of whatever background was already
  there.

* **Plain spaces with no styling** are fully transparent: the
  entire cell from the layer below is preserved (character,
  foreground, background, and attributes).

* **Cells with an explicit background** are opaque and overwrite
  the layer below completely.

When a layer is opaque (the default), every cell overwrites the
layer below regardless of styling.

Positioned Layers
-----------------

Layers can be offset from the top-left corner. This is useful for
compositing smaller content at a specific position:

.. code-block:: php

    $lines = Compositor::composite(
        new Layer($fullScreenBackground),
        new Layer($smallWidget, row: 5, col: 10, transparent: true),
    );

The offset applies to the entire content of the layer. Cells
outside the layer's content area are unaffected.

Multiple Layers
---------------

Any number of layers can be composited. They are applied in order,
so layer N can see the merged result of layers 0 through N-1:

.. code-block:: php

    $lines = Compositor::composite(
        new Layer($background),
        new Layer($midground, transparent: true),
        new Layer($foreground, transparent: true),
    );
