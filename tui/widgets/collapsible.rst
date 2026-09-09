CollapsibleWidget
=================

``CollapsibleWidget`` shows a summary line that is always visible and a
content widget shown only while the widget is expanded. It is the
terminal counterpart of the HTML ``<details>`` and ``<summary>``
elements.

When to Use
-----------

Use ``CollapsibleWidget`` to keep a dense screen readable: the details
of a failed job, an advanced options panel, one section per file of a
diff. The content is attached to the widget tree only while expanded,
so a collapsed section takes neither focus nor input.

Basic Usage
-----------

Give the widget a summary and the content it hides::

    use Symfony\Component\Tui\Widget\CollapsibleWidget;
    use Symfony\Component\Tui\Widget\TextWidget;

    $collapsible = new CollapsibleWidget(
        'Advanced settings',
        new TextWidget('Nothing to see here yet.'),
    );

    $tui->add($collapsible);
    $tui->setFocus($collapsible);

The widget starts collapsed. Pass ``expanded: true`` to open it from
the start::

    $collapsible = new CollapsibleWidget(
        'Advanced settings',
        $content,
        expanded: true,
    );

Description
-----------

An optional description is drawn at the end of the summary line,
aligned to the right, and moves to a line of its own when the two do
not fit::

    $collapsible = new CollapsibleWidget(
        'Advanced settings',
        $content,
        description: '3 items',
    );

Symbols
-------

The summary line opens with ``▶`` while collapsed and ``▼`` while
expanded. Pass your own symbols to the constructor::

    $collapsible = new CollapsibleWidget(
        'Advanced settings',
        $content,
        collapsedSymbol: '+',
        expandedSymbol: '-',
    );

Changing the State
------------------

Open and close the widget from your code with ``expand()``,
``collapse()`` and ``toggle()``, and read the current state with
``isExpanded()``::

    $collapsible->expand();
    $collapsible->collapse();
    $collapsible->toggle();

    if ($collapsible->isExpanded()) {
        // ...
    }

Updating the Content
--------------------

The summary, the description and the content widget can all be
replaced at runtime::

    $collapsible->setSummary('Advanced settings (updated)');
    $collapsible->setDescription('5 items');
    $collapsible->setContent($newWidget);

``getSummary()``, ``getDescription()`` and ``getContent()`` return them,
and ``all()`` returns the content widget while expanded and nothing
while collapsed.

Events
------

``ExpandEvent`` and ``CollapseEvent`` are dispatched on every change of
state, whether it comes from a keystroke or from your code::

    use Symfony\Component\Tui\Event\CollapseEvent;
    use Symfony\Component\Tui\Event\ExpandEvent;

    $collapsible->on(ExpandEvent::class, function () use ($bar) {
        $bar->setText('Showing the advanced settings');
    });

    $collapsible->on(CollapseEvent::class, function () use ($bar) {
        $bar->setText('');
    });

Styling
-------

Three sub-elements carry the look of the summary line: ``symbol`` for
the arrow, ``summary`` for the title and ``description`` for the text
aligned to the right. The default stylesheet reverses the symbol and
the summary while the widget holds the focus::

    use Symfony\Component\Tui\Style\Style;
    use Symfony\Component\Tui\Widget\CollapsibleWidget;

    $stylesheet->addRule(
        CollapsibleWidget::class.'::summary',
        new Style(bold: true),
    );

Default Keybindings
-------------------

==========================  ==================================
Key                         Action
==========================  ==================================
Enter, Space                Toggle the widget
Right                       Expand the widget
Left                        Collapse the widget
==========================  ==================================

Custom Keybindings
------------------

Call ``setKeybindings()`` to override any of the default bindings::

    use Symfony\Component\Tui\Input\Keybindings;

    $collapsible->setKeybindings(new Keybindings([
        'toggle' => ['ctrl+o'],
    ]));

The bindings are named ``toggle``, ``expand`` and ``collapse``.
