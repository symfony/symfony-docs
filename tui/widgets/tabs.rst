TabsWidget
==========

``TabsWidget`` groups several widgets into tabs and displays one of them
at a time. Only the content of the active tab is attached to the widget
tree, so the widgets of the other tabs can't receive focus or input.

When to Use
-----------

Use ``TabsWidget`` when a screen holds several independent views that
the user switches between: a log pane and a settings pane, one panel per
environment, the steps of a wizard, etc. To display all the child
widgets at the same time, use :doc:`/tui/widgets/container` instead.

Basic Usage
-----------

Add each widget as a tab and attach the tabs widget to the Tui::

    use Symfony\Component\Tui\Widget\TabsWidget;
    use Symfony\Component\Tui\Widget\TextWidget;

    $tabs = new TabsWidget();
    $tabs->add(new TextWidget('No deployment yet.')->setLabel('Logs'));
    $tabs->add($settingsWidget->setLabel('Settings'));

    $tui->add($tabs);
    $tui->setFocus($tabs);

``add()`` takes the tab id and label from the widget itself (set them
with ``setId()`` and ``setLabel()``). A widget without an id gets a
generated one (``tab_0``, ``tab_1``, etc.) and a widget without a label
uses its id as the label.

You can also pass a list of ``TabItem`` objects to the constructor. Each
item defines the id, the label and the content widget of a tab::

    use Symfony\Component\Tui\Widget\TabItem;
    use Symfony\Component\Tui\Widget\TabsWidget;

    $tabs = new TabsWidget([
        new TabItem('logs', 'Logs', $logWidget),
        new TabItem('settings', 'Settings', $settingsWidget),
    ]);

Tab Position
------------

The tab headers are displayed above the content by default. Call
``setPosition()`` (or pass the position as the second constructor
argument) to display them on another side::

    use Symfony\Component\Tui\Widget\TabPosition;

    $tabs->setPosition(TabPosition::Left);

``TabPosition::Top`` and ``TabPosition::Bottom`` display the headers as
a row of boxes, while ``TabPosition::Left`` and ``TabPosition::Right``
display them as a column of boxes. The position also defines which arrow
keys switch tabs, as listed below. When the headers don't
fit in the available space, they scroll to keep the active tab visible.

Managing Tabs
-------------

Add and remove tabs at any time, and change the active tab by its
index::

    $tabs->add($logWidget);
    // pass the content widget, not its TabItem
    $tabs->remove($logWidget);
    // removes all tabs
    $tabs->clear();

    // the index is clamped to the existing tabs
    $tabs->setActiveTab(1);

Use ``getActiveTabIndex()`` and ``getActiveTabId()`` to get the active
tab (the id is ``null`` when there are no tabs) and ``getTabs()`` to get
all the ``TabItem`` objects. The ``all()`` method only returns the
content widget of the active tab, because it's the only attached child.

Focus
-----

While the ``TabsWidget`` has the focus, the keys switch tabs and
**Enter** moves the focus to the first focusable widget of the active
tab (if any). When the user switches tabs while the focus is inside the
active tab, the focus goes back to the tab headers. Call
``setFocusContentOnSwitch()`` to move it to the first focusable widget
of the new tab instead::

    $tabs->setFocusContentOnSwitch(true);

The same happens when the active tab is removed while it has the focus.

Events
------

``TabChangeEvent`` is dispatched every time the active tab changes,
including when the active tab is removed and another tab takes its
place. ``clear()`` doesn't dispatch any event::

    use Symfony\Component\Tui\Event\TabChangeEvent;

    $tabs->onTabChange(function (TabChangeEvent $event) use ($statusBar) {
        $statusBar->setText(\sprintf('Viewing %s', $event->getId()));

        // getPreviousId(), getIndex() and getPreviousIndex() are also available
    });

Styling
-------

The tab headers define three sub-elements (see
:doc:`/tui/style/stylesheets`): ``tab`` (the label of inactive tabs),
``tab-active`` (the label of the active tab) and ``separator`` (the
borders of the boxes). The default
stylesheet also defines ``:focus`` variants of ``tab`` and
``tab-active``, which are applied while the widget has the focus. These
variants override your rules without ``:focus``, so define both::

    use Symfony\Component\Tui\Style\Style;
    use Symfony\Component\Tui\Widget\TabsWidget;

    $stylesheet->addRule(
        TabsWidget::class.'::tab-active',
        new Style()->withBold()->withColor('green'),
    );

    $stylesheet->addRule(
        TabsWidget::class.'::tab-active:focus',
        new Style()->withBold()->withColor('green')->withBackground('#253041'),
    );

Default Keybindings
-------------------

==========================  ===============================================
Key                         Action
==========================  ===============================================
Tab                         Next tab (wraps)
Shift+Tab                   Previous tab (wraps)
Left, Right                 Previous, next tab (top/bottom headers, wraps)
Up, Down                    Previous, next tab (left/right headers, wraps)
Enter                       Focus the content of the active tab
==========================  ===============================================

Because this widget uses the **Tab** key, the focus moves between
widgets with **F6** and **Shift+F6** as usual (see
:doc:`/tui/topics/focus`).

Custom Keybindings
------------------

Pass a ``Keybindings`` instance to ``setKeybindings()`` to override any
of the default bindings::

    use Symfony\Component\Tui\Input\Keybindings;

    $tabs->setKeybindings(new Keybindings([
        'tab_next' => ['ctrl+n'],
        'tab_previous' => ['ctrl+p'],
    ]));

The actions of this widget are ``tab_next``, ``tab_previous``,
``cursor_left``, ``cursor_right``, ``select_up``, ``select_down`` and
``select_confirm``.
