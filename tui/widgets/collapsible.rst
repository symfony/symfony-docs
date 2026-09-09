CollapsibleWidget
=================

``CollapsibleWidget`` shows a summary line that is always visible and a
content widget that is only visible while the widget is expanded. It's
the terminal version of the HTML ``<details>`` and ``<summary>``
elements.

When to Use
-----------

Use ``CollapsibleWidget`` to keep dense screens readable: the details of
a failed job, a panel with advanced options, one section per file in a
diff. The content is only added to the widget tree while the widget is
expanded, so the widgets it hides can't get the focus or receive input
while it's collapsed.

Basic Usage
-----------

Pass the summary and the content widget to the constructor::

    use Symfony\Component\Tui\Widget\CollapsibleWidget;
    use Symfony\Component\Tui\Widget\TextWidget;

    $collapsible = new CollapsibleWidget(
        'Advanced settings',
        new TextWidget('Retry failed jobs up to 3 times'),
    );

    $tui->add($collapsible);
    $tui->setFocus($collapsible);

The content is a single widget. To hide several widgets, wrap them in a
:doc:`/tui/widgets/container`. You can also nest ``CollapsibleWidget``
instances to create sub-sections.

The widget is collapsed by default. Pass ``true`` as the third argument
to show it expanded::

    $collapsible = new CollapsibleWidget('Advanced settings', $content, true);

Open and close the widget from your code with ``expand()``,
``collapse()`` and ``toggle()``, and check its state with
``isExpanded()``::

    $collapsible->expand();
    $collapsible->collapse();
    $collapsible->toggle();

    if ($collapsible->isExpanded()) {
        // ...
    }

The summary and the content can be changed at any time with
``setSummary()`` and ``setContent()``. If the widget is expanded, the new
content replaces the old one immediately.

Description
-----------

The description is an optional text displayed at the end of the summary
line, aligned to the right::

    $collapsible->setDescription('3 items');

When the line is too narrow to fit both texts, the description is
displayed on a second line, aligned with the summary text.

Symbols
-------

The summary line starts with ``▶`` while the widget is collapsed and
with ``▼`` while it's expanded. The symbols can't be changed later, so
pass your own symbols as the fifth and sixth constructor arguments::

    // the third and fourth arguments are the expanded state and the description
    $collapsible = new CollapsibleWidget(
        'Advanced settings', $content, false, null, '+', '-'
    );

Events
------

The widget dispatches an ``ExpandEvent`` or a ``CollapseEvent`` every
time its state changes, both when the user presses a key and when your
code calls ``expand()``, ``collapse()`` or ``toggle()``::

    use Symfony\Component\Tui\Event\CollapseEvent;
    use Symfony\Component\Tui\Event\ExpandEvent;

    // $status is a TextWidget displayed somewhere else on the screen
    $collapsible->on(ExpandEvent::class, function () use ($status): void {
        $status->setText('Showing the advanced settings');
    });

    // calling expand() on an expanded widget (or collapse() on a
    // collapsed one) doesn't dispatch any event
    $collapsible->on(CollapseEvent::class, function () use ($status): void {
        $status->setText('');
    });

.. seealso::

    Read :doc:`/tui/topics/events` to learn more about widget events.

Styling
-------

The summary line defines three sub-elements: ``symbol`` (the arrow),
``summary`` (the title) and ``description`` (the text aligned to the
right). The default stylesheet displays the description in gray and
reverses the colors of the symbol and the summary while the widget has
the focus. Override these styles in your own
:doc:`stylesheet </tui/style/stylesheets>`::

    use Symfony\Component\Tui\Style\Style;
    use Symfony\Component\Tui\Widget\CollapsibleWidget;

    $stylesheet->addRule(
        CollapsibleWidget::class.'::summary',
        new Style()->withBold(),
    );

    $stylesheet->addRule(
        CollapsibleWidget::class.'::summary:focus',
        new Style()->withBold()->withColor('cyan'),
    );

Default Keybindings
-------------------

==========================  ==================================
Key                         Action
==========================  ==================================
Enter, Space                Toggle the widget (``toggle``)
Right                       Expand the widget (``expand``)
Left                        Collapse the widget (``collapse``)
==========================  ==================================

Use ``setKeybindings()`` to change the keys of any of these actions. The
keys you define for an action replace all its default keys, so include
the default keys you want to keep::

    use Symfony\Component\Tui\Input\Keybindings;

    $collapsible->setKeybindings(new Keybindings([
        'toggle' => ['enter', 'space', 'ctrl+o'],
    ]));

.. seealso::

    Read :doc:`/tui/topics/keybindings` to learn more about keybindings.
