Stylesheets
===========

``StyleSheet`` is a collection of style rules with CSS-like selectors.
It decouples visual appearance from widget code, the same way CSS
decouples presentation from HTML.

When to Use
-----------

Use a stylesheet when you want to style widgets by type, class name
or state without setting inline styles on every instance. Stylesheets
are the recommended way to theme an entire application.

Basic Usage
-----------

Create a stylesheet, add rules and attach it to the Tui::

    use Symfony\Component\Tui\Style\Border;
    use Symfony\Component\Tui\Style\Padding;
    use Symfony\Component\Tui\Style\Style;
    use Symfony\Component\Tui\Style\StyleSheet;

    $stylesheet = new StyleSheet();
    $stylesheet->addRule('.sidebar', new Style(
        padding: Padding::all(1),
        border: Border::all(1, 'rounded'),
    ));

    $tui->addStyleSheet($stylesheet);

Assign the CSS class to a widget::

    $sidebar->addStyleClass('sidebar');

Selectors
---------

Stylesheets support several selector types, listed here from lowest
to highest priority:

**Universal selector**, matches every widget::

    $stylesheet->addRule('*', new Style(color: 'white'));

**FQCN selector**, matches widgets by their PHP class (including
parent classes)::

    use Symfony\Component\Tui\Widget\TextWidget;

    $stylesheet->addRule(TextWidget::class, new Style(
        color: 'gray',
    ));

**CSS class selector**, matches widgets that have the given style
class::

    $stylesheet->addRule('.title', new Style(
        bold: true,
        color: 'cyan',
    ));

**Pseudo-class selector**, matches when a widget is in a specific
state (e.g. ``:root``, ``:focus``). Use it standalone or combined
with FQCN or CSS class selectors::

    use Symfony\Component\Tui\Style\Border;
    use Symfony\Component\Tui\Style\Style;
    use Symfony\Component\Tui\Widget\InputWidget;

    // Bare pseudo-class: matches any widget in that state
    $stylesheet->addRule(':root', new Style(gap: 1));
    $stylesheet->addRule(':focus', new Style(bold: true));

    // Combined with FQCN
    $stylesheet->addRule(
        InputWidget::class.':focus',
        new Style(bold: true),
    );

    // Combined with CSS class
    $stylesheet->addRule(
        '.sidebar:focus',
        new Style(border: Border::all(1, 'rounded', 'cyan')),
    );

The ``:root`` pseudo-class matches the root widget of the tree
(the widget with no parent). This is the recommended way to style
the root container created by the Tui.

Cascade Order
-------------

When multiple rules match a widget, they are merged in this order
(later rules override earlier ones):

1. Universal selector (``*``)
2. FQCN selector (parent classes first, concrete class last)
3. CSS class selectors
4. Pseudo-class selectors (bare, then FQCN + state, then CSS class + state)
5. Responsive breakpoint rules (see below)
6. Utility class styles (see :doc:`/tui/style/tailwind`)
7. Instance style (``widget->setStyle()``)

Within each level, ``null`` properties are skipped so that
lower-priority values show through.

Sub-elements
------------

Widgets can expose internal parts as sub-elements using CSS
pseudo-element syntax (``::``). This allows styling individual pieces
of a widget without subclassing::

    use Symfony\Component\Tui\Widget\SelectListWidget;

    // Style the selected item
    $stylesheet->addRule(
        SelectListWidget::class.'::selected',
        new Style(bold: true),
    );

    // Different style when the widget is focused
    $stylesheet->addRule(
        SelectListWidget::class.'::selected:focus',
        new Style(bold: true, color: 'cyan'),
    );

    // Target by CSS class
    $stylesheet->addRule(
        '.my-list::selected',
        new Style(color: 'green'),
    );

Responsive Breakpoints
----------------------

Rules can be scoped to a minimum terminal width, similar to CSS
``@media (min-width: ...)``. Breakpoint rules apply when the terminal
has at least the specified number of columns::

    use Symfony\Component\Tui\Style\Direction;

    $stylesheet->addBreakpoint(
        120,
        '.panes',
        new Style(direction: Direction::Horizontal),
    );

Below 120 columns, ``.panes`` uses the default vertical layout.
At 120 columns or wider, it switches to horizontal.

Multiple breakpoints can be defined. They are evaluated in ascending
order, so wider breakpoints override narrower ones when both match.

Merging Stylesheets
-------------------

Combine multiple stylesheets like CSS cascading; later rules win::

    $defaults = new StyleSheet([
        '.panel' => new Style(background: '#1e1e2e'),
    ]);

    $theme = new StyleSheet([
        '.panel' => new Style(
            border: Border::all(1, 'rounded'),
        ),
    ]);

    // Theme rules override defaults for the same selector
    $defaults->merge($theme);

To add rules only for selectors that don't already exist (lower
priority), use ``mergeDefaults()``::

    $appStylesheet->mergeDefaults($libraryDefaults);

Default Stylesheet
------------------

Tui ships with a ``DefaultStyleSheet`` that provides sensible base
styles for all built-in widgets (overlay borders, loader colors,
markdown heading styles, etc.). It is applied automatically and can
be overridden by any application stylesheet.
