TabsWidget
==========

``TabsWidget`` groups several widgets into tabs and shows one of them at
a time. The tab headers are drawn above, below or beside the content,
and only the active tab is attached to the widget tree: the others
receive neither focus nor input.

When to Use
-----------

Use ``TabsWidget`` when a screen holds several independent views the
user switches between: a log pane next to a settings pane, one panel per
environment, the sections of a wizard. To show every child at the same
time, use :doc:`/tui/widgets/container` instead.

Basic Usage
-----------

Add a widget as a tab and attach the tabs to the Tui::

    use Symfony\Component\Tui\Widget\TabsWidget;
    use Symfony\Component\Tui\Widget\TextWidget;

    $tabs = new TabsWidget();
    $tabs->add(new TextWidget('No deployment yet.')->setLabel('Logs'));
    $tabs->add($settingsWidget->setLabel('Settings'));

    $tui->add($tabs);
    $tui->setFocus($tabs);

``add()`` takes the id and the label of the tab from the widget itself.
A widget without an id gets a generated ``tab_N`` one, and a widget
without a label falls back to its id, so set them with ``setId()`` and
``setLabel()``. Building the tabs from ``TabItem`` objects, which take
an id, a label and the content widget, names them all at once::

    use Symfony\Component\Tui\Widget\TabItem;

    $items = [
        new TabItem('logs', 'Logs', $logWidget),
        new TabItem('settings', 'Settings', $settingsWidget),
    ];

    $tabs = new TabsWidget($items);

Tab Position
------------

The headers are drawn above the content by default. Pass another
``TabPosition`` case to the constructor, or call ``setPosition()``, to
move them::

    use Symfony\Component\Tui\Widget\TabPosition;

    $tabs = new TabsWidget($items, TabPosition::Bottom);
    // or
    $tabs->setPosition(TabPosition::Bottom);

``TabPosition::Top`` and ``TabPosition::Bottom`` draw a row of boxes
above or below the content, ``TabPosition::Left`` and
``TabPosition::Right`` a column of boxes on either side of it. The
position also decides which arrow keys walk the tabs, as listed below.
When the headers do not fit, they scroll to keep the active one
visible.

Managing Tabs
-------------

Add and remove tabs at any time, and switch the active one by index::

    $tabs->add($logWidget);
    $tabs->remove($logWidget);

    $tabs->setActiveTab(1);

``remove()`` takes the content widget of the tab, not its ``TabItem``,
and ``clear()`` removes every tab at once. ``setActiveTab()`` clamps the
index to the existing tabs, and does nothing while there are none.

Read the current state with ``getActiveTabIndex()`` and
``getActiveTabId()``, which returns ``null`` when the widget has no
tabs. ``getTabs()`` returns the ``TabItem`` objects, while ``all()``
returns the content widget of the active tab alone, since it is the only
attached child.

Focus
-----

Focus stays on the tab headers while the user walks the tabs, and
**Enter** moves it into the first focusable widget of the active tab, if
it has one. Switching tabs from there brings the focus back to the
headers; to send it into the content of the new tab instead::

    $tabs->setFocusContentOnSwitch(true);

This applies to the switches made while the focus is inside the tab
being left. From the headers, the focus stays on the headers.

Events
------

``TabChangeEvent`` is dispatched on every change of the active tab,
including when the active tab is removed, as long as another tab
remains. ``clear()`` dispatches nothing::

    use Symfony\Component\Tui\Event\TabChangeEvent;

    $tabs->onTabChange(function (TabChangeEvent $event) use ($statusBar) {
        $statusBar->setText(\sprintf('Viewing %s', $event->getId()));
    });

The event also carries the indexes of both tabs, with
``getPreviousIndex()`` and ``getIndex()``.

Styling
-------

Three sub-elements carry the look of the headers: ``tab`` for an
inactive label, ``tab-active`` for the active one and ``separator`` for
the box borders. The default stylesheet gives ``tab`` and ``tab-active``
a ``:focus`` variant, applied while the widget holds the focus, and your
own rules can add one to any of the three::

    use Symfony\Component\Tui\Style\Style;
    use Symfony\Component\Tui\Widget\TabsWidget;

    $stylesheet->addRule(
        TabsWidget::class.'::tab-active',
        new Style(bold: true),
    );

    $stylesheet->addRule(
        TabsWidget::class.'::tab-active:focus',
        new Style(bold: true, color: 'cyan'),
    );

Default Keybindings
-------------------

==========================  ==========================================
Key                         Action
==========================  ==========================================
Tab                         Next tab (wraps)
Shift+Tab                   Previous tab (wraps)
Left, Right                 Previous, next tab (row headers, wraps)
Up, Down                    Previous, next tab (column headers, wraps)
Enter                       Focus the active tab content
==========================  ==========================================

Custom Keybindings
------------------

Pass a ``Keybindings`` instance to the constructor to override any of
the default bindings::

    use Symfony\Component\Tui\Input\Keybindings;

    $tabs = new TabsWidget($items, keybindings: new Keybindings([
        'tab_next' => ['ctrl+n'],
        'tab_previous' => ['ctrl+p'],
    ]));

The bindings are named ``tab_next``, ``tab_previous``, ``cursor_left``,
``cursor_right``, ``select_up``, ``select_down`` and
``select_confirm``.

.. note::

    Focus navigation between widgets stays on **F6** and **Shift+F6**,
    so the tab keys do not collide with it. See
    :doc:`/tui/topics/focus` and :doc:`/tui/topics/keybindings`.
