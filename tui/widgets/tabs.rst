TabsWidget
==========

``TabsWidget`` groups several widgets into tabs and shows one of them at
a time. The tab headers are drawn around the content, and only the
active tab is attached to the widget tree: the others receive neither
focus nor input.

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
    $tabs->add(new TextWidget('Deployment log'));
    $tabs->add(new TextWidget('Settings'));

    $tui->add($tabs);
    $tui->setFocus($tabs);

``add()`` takes the id and the label of the tab from the widget itself,
falling back to a generated ``tab_N`` id when the widget has none. To
name them explicitly, build the tabs from ``TabItem`` objects, which
take an id, a label and the content widget::

    use Symfony\Component\Tui\Widget\TabItem;

    $tabs = new TabsWidget([
        new TabItem('logs', 'Logs', $logWidget),
        new TabItem('settings', 'Settings', $settingsWidget),
    ]);

Tab Position
------------

The headers are drawn above the content by default. Pass another
``TabPosition`` case to the constructor, or call ``setPosition()``, to
move them::

    use Symfony\Component\Tui\Widget\TabPosition;

    $tabs = new TabsWidget($items, TabPosition::Left);
    // or
    $tabs->setPosition(TabPosition::Left);

``TabPosition::Top`` draws a row of boxes above the content and
``TabPosition::Left`` a column of boxes beside it. The position also
decides which arrow keys walk the tabs, as listed below. When the
headers do not fit, they scroll to keep the active one visible.

Managing Tabs
-------------

Add, remove or clear tabs at any time, and switch the active one by
index::

    $tabs->add($widget);
    $tabs->remove($widget);
    $tabs->clear();

    $tabs->setActiveTab(1);

``setActiveTab()`` clamps the index to the existing tabs. Read the
current state with ``getActiveTabIndex()``, ``getActiveTabId()`` and
``getTabs()``; ``all()`` returns the content of the active tab alone,
since it is the only attached child.

Focus
-----

Focus stays on the tab headers while the user walks the tabs, and
**Enter** moves it into the first focusable widget of the active tab.
Switching tabs from there brings the focus back to the headers; to send
it into the content of the new tab instead::

    $tabs->setFocusContentOnSwitch(true);

Events
------

``TabChangeEvent`` is dispatched on every change of the active tab,
including when the active tab is removed::

    use Symfony\Component\Tui\Event\TabChangeEvent;

    $tabs->onTabChange(function (TabChangeEvent $event) {
        $event->getPreviousIndex();
        $event->getPreviousId();
        $event->getIndex();
        $event->getId();
    });

Styling
-------

Three sub-elements carry the look of the headers: ``tab`` for an
inactive label, ``tab-active`` for the active one and ``separator`` for
the box borders. Each of them takes a ``:focus`` variant, applied while
the widget holds the focus::

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

==========================  ==================================
Key                         Action
==========================  ==================================
Tab                         Next tab (wraps)
Shift+Tab                   Previous tab (wraps)
Left, Right                 Previous, next tab (top headers)
Up, Down                    Previous, next tab (left headers)
Enter                       Focus the content of the active tab
==========================  ==================================

Focus navigation between widgets stays on **F6** and **Shift+F6**, so
the tab keys do not collide with it.
