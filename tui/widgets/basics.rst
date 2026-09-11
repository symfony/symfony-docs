Widget Basics
=============

All widgets extend ``AbstractWidget``, which provides a set of
common APIs. This page documents the features shared by every
widget.

Widget ID
---------

Assign an id to any widget so you can retrieve it later from
anywhere in the tree::

    $input->setId('search');
    $tui->add($input);

    // Later, from anywhere
    $widget = $tui->getById('search');

IDs can also be used to find widgets within a subtree::

    $found = $container->findById('search');

Style Classes
-------------

Style classes connect widgets to stylesheet rules. Add and remove
them at any time::

    $widget->addStyleClass('sidebar');
    $widget->addStyleClass('highlighted');
    $widget->removeStyleClass('highlighted');

A widget can have multiple style classes. The stylesheet resolves
all matching rules and merges them (see :doc:`/tui/style/stylesheets`).

Tailwind-style utility classes work the same way::

    $widget->addStyleClass('p-2');
    $widget->addStyleClass('bold');
    $widget->addStyleClass('bg-red-500');

See :doc:`/tui/style/tailwind` for the full list of utility classes.

Inline Styles
-------------

Set a style directly on a widget. This has the highest priority in
the cascade and overrides all stylesheet rules::

    use Symfony\Component\Tui\Style\Style;

    $widget->setStyle(new Style(bold: true, color: 'cyan'));

Pass ``null`` to remove the inline style and fall back to the
stylesheet::

    $widget->setStyle(null);

See :doc:`/tui/style/style` for all available style properties.

Per-Widget Event Listeners
--------------------------

Register event listeners on a specific widget using the typed
shortcut methods (``onSubmit()``, ``onCancel()``, etc.) or the
generic ``on()`` method::

    use Symfony\Component\Tui\Event\SubmitEvent;

    // Typed shortcut
    $input->onSubmit(function (SubmitEvent $event) {
        // ...
    });

    // Generic form (works for any event type)
    $input->on(SubmitEvent::class, function (SubmitEvent $event) {
        // ...
    });

Per-widget listeners are automatically scoped to the target widget
and cleaned up when the widget is detached from the tree. See
:doc:`/tui/topics/events` for global listeners and the full event
system.

Vertical Expansion
------------------

By default, widgets only occupy the rows they need. Some widgets
can fill all remaining vertical space by enabling vertical
expansion::

    $container->expandVertically(true);
    $editor->expandVertically(true);

The following widgets support vertical expansion:

* ``ContainerWidget``: fills remaining height. Expansion propagates
  automatically; if any child is vertically expanded, the container
  reports itself as expanded too.
* ``EditorWidget``: grows the editing area to fill available space.
* ``AnimatedImageWidget``: grows the animation area to fill
  available space (except in ``ImageScaling::None`` mode).

This is useful for layouts where one section should grow to fill
the terminal height while others stay at their natural size.

Labels
------

Widgets have an optional label, used by container widgets like
``TabsWidget`` to display a title::

    $widget->setLabel('Overview');

Widget Tree
-----------

Widgets form a tree. Container widgets (``ContainerWidget``,
``TabsWidget``, etc.) hold child widgets. You can walk up the tree
with ``getParent()``::

    $parent = $widget->getParent();
