KeyBindingWidget
================

``KeyBindingWidget`` displays the keyboard shortcuts of the currently
focused widget, like the help bar found at the bottom of many terminal
applications. It updates itself when the focus moves to another widget
or when the keybindings of the focused widget change.

.. versionadded:: 8.2

    The ``KeyBindingWidget`` was introduced in Symfony 8.2.

When to Use
-----------

Use ``KeyBindingWidget`` to make the available shortcuts discoverable
without a dedicated help screen. It's most useful in applications where
several focusable widgets each have their own shortcuts, since users see
only the ones that apply to the widget they are interacting with.

Basic Usage
-----------

Add the widget once, usually as the last widget of the layout::

    use Symfony\Component\Tui\Widget\KeyBindingWidget;

    $tui->add(new KeyBindingWidget());

There is nothing else to wire: the widget listens to focus changes and
reads the keybindings of the focused widget by itself.

Displayed Bindings
------------------

Each entry displays the key and the label of one action. Only the
**first** key of an action is displayed; the other ones are functional
aliases (for example the Emacs-style shortcuts of ``InputWidget``).

Widgets decide which of their actions are displayed, in which order and
with which labels. Actions that a widget doesn't list are not displayed.
When a widget defines no label at all, every one of its actions is
displayed with a humanized version of the action name (``select_page_up``
becomes "Select Page Up").

Change the labels of a widget with ``setKeybindingLabels()``::

    $list->setKeybindingLabels([
        'select_confirm' => 'Choose',
        'select_cancel' => 'Back',
    ]);

The array above also defines the display order, and it hides every other
action of the widget. Pass ``null`` to restore the labels of the widget::

    $list->setKeybindingLabels(null);

Global Bindings
---------------

Shortcuts that work everywhere in the application, such as the one that
quits it, don't belong to any focused widget. Declare them with
``setGlobalKeybindings()``::

    use Symfony\Component\Tui\Input\Keybindings;

    $keyBinding->setGlobalKeybindings(new Keybindings([
        'quit' => ['ctrl+c'],
    ]), [
        'quit' => 'Quit',
    ]);

Global bindings are displayed at the end of the last line, separated
from the bindings of the focused widget. The second argument works like
``setKeybindingLabels()``: omit it to display every action with a
humanized name.

Key Labels
----------

Keys are displayed with the labels of the ``Key::label()`` method:
``Esc``, ``Tab``, ``Space``, ``Home``, ``↵`` for Enter, ``▲`` and ``▼``
for the arrows, ``Ctrl+C`` for modifier combinations, etc.

Override the label of individual keys, for example to replace the
symbols with plain text::

    $keyBinding->setKeyLabels([
        Key::ENTER => 'Enter',
        Key::UP => 'Up',
    ]);

Pass ``null`` to go back to the default labels.

Layout
------

Entries are separated by two spaces and wrap onto several lines when
they don't fit the width of the terminal. Change the space between
entries with ``setGap()`` and the string placed before the global
bindings with ``setSeparator()``::

    $keyBinding->setGap(4);
    $keyBinding->setSeparator(' • ');

The default separator is ``' │ '``.

Styleable Elements
------------------

``KeyBindingWidget`` exposes four styleable elements:

* ``key``: the key of a binding of the focused widget.
* ``action``: the label of a binding of the focused widget.
* ``global-key``: the key of a global binding.
* ``global-action``: the label of a global binding.

Events
------

``KeyBindingWidget`` doesn't emit any event and can't be focused.
