ContainerWidget
===============

``ContainerWidget`` groups child widgets and arranges them using
vertical or horizontal layout. It is the primary tool for composing
complex terminal interfaces from smaller widgets.

When to Use
-----------

Use ``ContainerWidget`` whenever you need to combine several widgets
into a single visual region: a sidebar next to a main content area,
a stack of form fields, a toolbar of buttons, etc.

Basic Usage
-----------

Create a container, add children and attach it to the Tui::

    use Symfony\Component\Tui\Widget\ContainerWidget;
    use Symfony\Component\Tui\Widget\TextWidget;

    $container = new ContainerWidget();
    $container->add(new TextWidget('First'));
    $container->add(new TextWidget('Second'));
    $tui->add($container);

By default, children are stacked vertically (top to bottom).

Layout Direction
----------------

Set the layout direction to arrange children horizontally::

    use Symfony\Component\Tui\Style\Direction;
    use Symfony\Component\Tui\Style\Style;

    $container->setStyle(new Style(direction: Direction::Horizontal));

Or use a stylesheet rule::

    $stylesheet->addRule('.toolbar', new Style(
        direction: Direction::Horizontal,
        gap: 2,
    ));
    $container->addStyleClass('toolbar');

Gap
---

The ``gap`` style property adds spacing (in terminal rows for vertical
layout, columns for horizontal layout) between children::

    $container->setStyle(new Style(gap: 1));

Flex Column Widths
------------------

In horizontal layouts, children share space equally by default. The
``flex`` style property gives you control over how space is distributed:

- ``flex: 0``: the child gets its intrinsic (content) width
- ``flex: 1+``: the child gets a proportional share of the remaining space

A fixed sidebar next to a flexible main area::

    $sidebar->setStyle(new Style(flex: 0));
    $main->setStyle(new Style(flex: 1));

Three columns with a 1:2:1 ratio::

    $left->setStyle(new Style(flex: 1));
    $center->setStyle(new Style(flex: 2));
    $right->setStyle(new Style(flex: 1));

Combine ``flex: 0`` with ``maxColumns`` to cap the intrinsic width::

    $sidebar->setStyle(new Style(flex: 0, maxColumns: 30));

When no child has ``flex`` set, horizontal layout falls back to equal
distribution (backward compatible).

Vertical Expansion
------------------

A container can fill all available vertical space by calling
``expandVertically(true)``. This is useful for layouts where one
section should grow to fill the remaining terminal height::

    $container->expandVertically(true);

Vertical expansion also propagates automatically: if any child is
vertically expanded, the container reports itself as expanded too.

Managing Children
-----------------

Add, remove or clear children at any time::

    $container->add($widget);
    $container->remove($widget);
    $container->clear();

Call ``all()`` to retrieve the current list of children::

    $children = $container->all();

Styling
-------

Like all widgets, ``ContainerWidget`` supports padding, borders and
background colors through the Style system::

    use Symfony\Component\Tui\Style\Border;
    use Symfony\Component\Tui\Style\Color;
    use Symfony\Component\Tui\Style\Padding;
    use Symfony\Component\Tui\Style\Style;

    $container->setStyle(new Style(
        padding: Padding::all(1),
        border: Border::all(1, 'rounded'),
        background: Color::named('gray'),
    ));

Events
------

``ContainerWidget`` does not emit any events. It is a layout-only
widget and does not handle input directly.
