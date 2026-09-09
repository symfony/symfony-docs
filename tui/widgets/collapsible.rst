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
diff. The widget attaches its content to the widget tree only while
expanded, so the widgets it hides can be neither focused nor reached by
input while the section is closed.

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

The content is a single widget: wrap several of them in a
:doc:`/tui/widgets/container`, or nest another ``CollapsibleWidget``
to build sub-sections.

The widget starts collapsed. Pass ``expanded: true`` to open it from the
start::

    $collapsible = new CollapsibleWidget(
        'Advanced settings',
        $content,
        expanded: true,
    );

Description
-----------

An optional description sits at the end of the summary line, aligned to
the right. When the line is too narrow for both, the summary is
truncated and the description moves to a second line, indented under
the summary::

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

The symbols are read-only: set them when you build the widget.

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

The widget dispatches ``ExpandEvent`` and ``CollapseEvent`` on every
change of state, whether a keystroke or your own code causes it.
Calling ``expand()`` on an already expanded widget changes nothing and
dispatches no event. See :doc:`/tui/topics/events`::

    use Symfony\Component\Tui\Event\CollapseEvent;
    use Symfony\Component\Tui\Event\ExpandEvent;
    use Symfony\Component\Tui\Widget\TextWidget;

    $status = new TextWidget('');

    $collapsible->on(ExpandEvent::class, function () use ($status) {
        $status->setText('Showing the advanced settings');
    });

    $collapsible->on(CollapseEvent::class, function () use ($status) {
        $status->setText('');
    });

Styling
-------

Three sub-elements carry the look of the summary line: ``symbol`` for
the arrow, ``summary`` for the title and ``description`` for the text
aligned to the right. The default stylesheet draws the description in
gray, and reverses the symbol and the summary while the widget holds
the focus. See :doc:`/tui/style/stylesheets`::

    use Symfony\Component\Tui\Style\Style;
    use Symfony\Component\Tui\Widget\CollapsibleWidget;

    $stylesheet->addRule(
        CollapsibleWidget::class.'::summary',
        new Style(bold: true),
    );

    $stylesheet->addRule(
        CollapsibleWidget::class.'::summary:focus',
        new Style(bold: true, color: 'cyan'),
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

Call ``setKeybindings()`` to replace the keys of any action. Each
action you list replaces its default keys entirely, so name every key
you want to keep. See :doc:`/tui/topics/keybindings`::

    use Symfony\Component\Tui\Input\Keybindings;

    $collapsible->setKeybindings(new Keybindings([
        'toggle' => ['enter', 'space', 'ctrl+o'],
    ]));

The three actions are ``toggle``, ``expand`` and ``collapse``.
