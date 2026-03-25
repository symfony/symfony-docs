Style
=====

``Style`` is an immutable value object that holds all visual and layout
properties for a widget. Every ``with*()`` method returns a new
instance, so styles can be safely shared and reused.

When to Use
-----------

Use ``Style`` when you need to configure the appearance of a widget:
colors, text decoration, padding, borders, layout direction, alignment
and more. You can pass it to a widget directly, to a stylesheet rule
or combine several styles through the cascade.

Creating a Style
----------------

Pass properties to the constructor::

    use Symfony\Component\Tui\Style\Padding;
    use Symfony\Component\Tui\Style\Style;

    $style = new Style(
        bold: true,
        color: 'cyan',
        padding: new Padding(1, 2, 1, 2),
    );

Or build one incrementally with the fluent ``with*()`` API::

    $style = (new Style())
        ->withColor('cyan')
        ->withBold()
        ->withPadding([1, 2]);

Both approaches produce the same result. The constructor form is more
compact; the fluent form is easier to read when you start from an
existing style.

Applying a Style to a Widget
----------------------------

Set an inline style directly on a widget::

    $widget->setStyle(new Style(bold: true, color: 'red'));

This inline style has the highest priority in the cascade and
overrides all stylesheet rules.

Nullable Properties and Inheritance
-----------------------------------

Every property on ``Style`` is nullable. A ``null`` value means
"not set"; the property will be inherited from lower-priority rules
during the cascade. An explicit value (even ``false`` or ``0``)
means "set" and overrides inheritance.

This distinction matters when composing styles::

    // Only sets color; bold, padding, etc. will inherit
    $style = new Style(color: 'red');

    // Explicitly disables bold, overriding a parent rule
    $style = new Style(bold: false);

    // Explicit zero padding, overriding inherited padding
    $style = Style::padding([0]);

Text Formatting Properties
--------------------------

==================  ====================================
Property            Description
==================  ====================================
``color``           Foreground color
``background``      Background color
``bold``            Bold text
``dim``             Dim/faint text
``italic``          Italic text
``underline``       Underlined text
``strikethrough``   Strikethrough text
``reverse``         Reverse video (swap fg/bg)
==================  ====================================

.. code-block:: php

    $style = (new Style())
        ->withColor('#ff5500')
        ->withBackground('blue')
        ->withBold()
        ->withItalic();

Colors
------

The ``Color`` class represents a terminal color. It supports three
formats:

**Named ANSI colors**, the 16 standard terminal colors::

    use Symfony\Component\Tui\Style\Color;

    $red = Color::named('red');
    $gray = Color::named('gray');

Available names: ``black``, ``red``, ``green``, ``yellow``, ``blue``,
``magenta``, ``cyan``, ``white``, ``default``, ``bright_black``,
``bright_red``, ``bright_green``, ``bright_yellow``, ``bright_blue``,
``bright_magenta``, ``bright_cyan``, ``bright_white``, ``gray``
(alias for ``bright_black``).

**256-color palette**, integers from 0 to 255::

    $color = Color::palette(202); // orange

**True color RGB**, hex strings or explicit RGB values::

    $color = Color::hex('#ff5500');
    $color = Color::hex('#f50');   // short form
    $color = Color::rgb(255, 85, 0);

All ``Style`` methods that accept a color (``withColor()``,
``withBackground()``, ``withBorderColor()``) use ``Color::from()``
internally, so you can pass strings and integers directly::

    $style = (new Style())
        ->withColor('cyan')
        ->withBackground('#1e1e2e');

Mixing Colors
~~~~~~~~~~~~~

``mix()`` blends two colors by a percentage. At 0% the result is the
original color; at 100% it is the other color::

    $blended = Color::hex('#ff0000')->mix('#0000ff', 50);

Three shortcuts simplify common operations:

* ``tint($percentage)``: Lighten toward white.
* ``shade($percentage)``: Darken toward black.
* ``scale($percentage)``: Positive values darken, negative values
  lighten.

.. code-block:: php

    $lighter = Color::named('blue')->tint(40);
    $darker = Color::named('blue')->shade(40);

These methods are used internally by the Tailwind shade system
(e.g. ``text-blue-300`` tints the base blue by 40%).

Padding
-------

``Padding`` adds empty space between a widget's border (or edge) and
its content. Shorthand arrays work like CSS: 1, 2, 3 or 4 values::

    use Symfony\Component\Tui\Style\Padding;

    Padding::from([1]);           // all sides = 1
    Padding::from([1, 2]);        // top/bottom = 1, left/right = 2
    Padding::from([1, 2, 3]);     // top = 1, left/right = 2, bottom = 3
    Padding::from([1, 2, 3, 4]); // top, right, bottom, left

Named constructors for common patterns::

    Padding::all(2);     // all sides = 2
    Padding::xy(3, 1);   // left/right = 3, top/bottom = 1

Apply padding through a style::

    $widget->setStyle(new Style(padding: Padding::xy(2, 1)));

    // Or with the shorthand static constructor
    $widget->setStyle(Style::padding([1, 2]));

Borders
-------

``Border`` draws a visual frame around the widget. Like padding, it
accepts 1–4 values for per-side widths::

    use Symfony\Component\Tui\Style\Border;

    Border::from([1]);             // all sides = 1
    Border::all(1);                // all sides = 1
    Border::xy(1, 0);             // left/right = 1, top/bottom = 0
    Border::from([1, 0, 1, 0]);   // top and bottom only

Apply a border through a style::

    $widget->setStyle(Style::border([1]));

    // With pattern and color
    $widget->setStyle(Style::border([1], 'rounded', 'cyan'));

Border Patterns
~~~~~~~~~~~~~~~

The border pattern controls the characters used to draw the frame.
Ten built-in patterns are available:

==================  =======================================
Pattern             Description
==================  =======================================
``normal``          Standard box-drawing (``┌─┐│└─┘``)
``rounded``         Rounded corners (``╭─╮│╰─╯``)
``double``          Double-line box (``╔═╗║╚═╝``)
``tall``            Block-style vertical emphasis
``wide``            Block-style horizontal emphasis
``tall-medium``     ~4px balanced border (tall corners)
``wide-medium``     ~4px balanced border (wide corners)
``tall-large``      ~8px balanced border (tall corners)
``wide-large``      ~8px balanced border (wide corners)
==================  =======================================

Set a pattern when creating the border::

    $border = Border::all(1, 'rounded');
    $border = Border::all(1, BorderPattern::rounded());

Or change the pattern on an existing border::

    $border = $border->withPattern('double');

Border Color
~~~~~~~~~~~~

Set a color for the border characters::

    $border = Border::all(1, 'rounded', 'cyan');

    // Or change the color on an existing border
    $border = $border->withColor('#505050');

The border color can also be set through the ``Style`` fluent API::

    $style = (new Style())
        ->withBorder(Border::all(1, 'rounded'))
        ->withBorderColor('cyan');

Combining Padding and Borders
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Padding and borders compose naturally. The border wraps the padded
content area::

    $style = new Style(
        padding: Padding::xy(2, 1),
        border: Border::all(1, 'rounded', 'gray'),
        background: '#1e1e2e',
    );
    $widget->setStyle($style);

The rendering order from outside to inside is: border → padding →
content.

Layout Properties
-----------------

These properties control how containers arrange their children. See
:doc:`/widgets/container` for usage examples.

=====================  ==========================================
Property               Description
=====================  ==========================================
``direction``          ``Vertical`` or ``Horizontal`` layout
``gap``                Spacing between children (rows or columns)
``hidden``             Hide the widget (like CSS ``display: none``)
``maxColumns``         Cap the widget's available width
``align``              Horizontal alignment (``Left``, ``Center``,
                       ``Right``)
``verticalAlign``      Vertical alignment (``Top``, ``Center``,
                       ``Bottom``)
``flex``               Flex grow weight for horizontal layouts
                       (``0`` = intrinsic width, ``1+`` =
                       proportional)
=====================  ==========================================

.. code-block:: php

    use Symfony\Component\Tui\Style\Direction;

    $style = new Style(
        direction: Direction::Horizontal,
        gap: 2,
        padding: Padding::xy(2, 1),
    );

Content Properties
------------------

=====================  ==========================================
Property               Description
=====================  ==========================================
``font``               FIGlet font name (``big``, ``small``,
                       ``slant``, ``standard``, ``mini``)
``textAlign``          Text alignment within the content area
                       (``Left``, ``Center``, ``Right``)
``cursorShape``        Cursor shape for input widgets
=====================  ==========================================

Merging Styles
--------------

When you need to combine multiple styles programmatically (outside
the stylesheet cascade), use ``Style::mergeAll()``::

    $merged = Style::mergeAll([$base, $theme, $override]);

Later styles override earlier ones for non-null properties. This
allocates a single ``Style`` object regardless of how many inputs
you pass.
