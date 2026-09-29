KeyBindingWidget
================

``KeyBindingWidget`` displays the keyboard shortcuts of the focused
widget, like the help bar found at the bottom of many terminal
applications. It updates itself when the focus moves to another widget
or when the keybindings of the focused widget change.

.. versionadded:: 8.2

    The ``KeyBindingWidget`` was introduced in Symfony 8.2.

When to Use
-----------

Use ``KeyBindingWidget`` to help users discover the available shortcuts
without a dedicated help screen. It's most useful in applications with
several focusable widgets that have their own shortcuts, because users
only see the shortcuts of the widget they are using.

Basic Usage
-----------

Add the widget once, usually as the last widget of the layout::

    use Symfony\Component\Tui\Widget\KeyBindingWidget;

    $keyBinding = new KeyBindingWidget();
    $tui->add($keyBinding);

The widget listens to focus changes and reads the keybindings of the
focused widget by itself, so it doesn't need any other configuration.

.. _tui-keybinding-labels:

Displayed Bindings
------------------

Each entry displays the key and the label of one action. Only the
**first** key of an action is displayed; the other keys are aliases
(for example, the Emacs-style shortcuts of ``InputWidget``).

Each widget defines which of its actions are displayed, in which order
and with which labels (custom widgets do this by overriding the
``getDefaultKeybindingLabels()`` method). When a widget defines no
labels, all its actions are displayed with a label created from the
action name (``select_page_up`` is displayed as "Select Page Up").

Change the labels of a widget instance with ``setKeybindingLabels()``::

    // $list is a SelectListWidget instance
    $list->setKeybindingLabels([
        'select_confirm' => 'Choose',
        'select_cancel' => 'Back',
    ]);

This array also defines the display order, and it hides all the other
actions of the widget. Pass ``null`` to restore the default labels of
the widget::

    $list->setKeybindingLabels(null);

Global Bindings
---------------

Some shortcuts work everywhere in the application (such as the one
that quits it), so they don't belong to the focused widget. Display them
with ``setGlobalKeybindings()``::

    use Symfony\Component\Tui\Input\Keybindings;

    $keyBinding->setGlobalKeybindings(new Keybindings([
        'quit' => ['ctrl+q'],
    ]), [
        'quit' => 'Quit',
    ]);

This method only displays these shortcuts; your application must still
handle the keys itself.

Global bindings are aligned to the right of the last line and separated
from the bindings of the focused widget. The second argument works like
``setKeybindingLabels()``: omit it to display all the actions with a
label created from their names.

Key Labels
----------

Keys are displayed with the labels returned by the ``Key::label()``
method: ``Esc``, ``Tab``, ``Space``, ``Home``, ``↵`` for Enter, ``▲``
and ``▼`` for the arrows, ``Ctrl+C`` for modifier combinations, etc.

Override the label of individual keys, for example to display plain
text instead of symbols::

    use Symfony\Component\Tui\Input\Key;

    $keyBinding->setKeyLabels([
        Key::ENTER => 'Enter',
        Key::UP => 'Up',
    ]);

Pass ``null`` to restore the default labels.

Layout
------

Entries wrap onto several lines when they don't fit in the available
width. Configure the space between entries and the string displayed
before the global bindings::

    // number of spaces between entries (default: 2)
    $keyBinding->setGap(4);

    // string displayed before the global bindings (default: ' │ ')
    $keyBinding->setSeparator(' • ');

Styleable Elements
------------------

``KeyBindingWidget`` exposes four styleable elements:

* ``key``: the key of a binding of the focused widget.
* ``action``: the label of a binding of the focused widget.
* ``global-key``: the key of a global binding.
* ``global-action``: the label of a global binding.

Events
------

``KeyBindingWidget`` doesn't emit events and can't be focused.
