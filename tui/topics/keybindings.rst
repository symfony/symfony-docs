Keybindings
===========

The keybindings system maps action names to key patterns, allowing
keyboard shortcuts to be customized without changing widget code.

How It Works
------------

A ``Keybindings`` instance is a map from action names (strings) to
lists of key identifiers::

    use Symfony\Component\Tui\Input\Key;
    use Symfony\Component\Tui\Input\Keybindings;

    $kb = new Keybindings([
        'submit' => [Key::ENTER],
        'cancel' => [Key::ESCAPE, 'ctrl+c'],
        'delete_word' => ['ctrl+w', 'alt+backspace'],
    ]);

Key identifiers can be:

* Constants from the ``Key`` class: ``Key::ENTER``, ``Key::ESCAPE``,
  ``Key::UP``, ``Key::DOWN``, ``Key::TAB``, ``Key::F6``, etc.
* Modifier combinations: ``'ctrl+c'``, ``'alt+d'``,
  ``'shift+enter'``, ``'ctrl+shift+k'``, ``'super+f1'``.
* Single characters: ``'+'``, ``'-'``, ``'q'``, ``'r'``.

The modifiers are ``shift``, ``ctrl``, ``alt``, ``super``, ``hyper`` and
``meta``. Terminals only report ``super``, ``hyper`` and ``meta`` in CSI
sequences, such as the ones of the Kitty keyboard protocol, so a key
identifier using them never matches on a terminal that doesn't send them.

.. warning::

    ``meta`` is the Meta modifier of the Kitty keyboard protocol, not the
    key that terminals and readline call "Meta". The Option key of macOS
    configured as Meta (e.g. Terminal.app's **Use Option as Meta key**)
    sends Alt key combinations: bind ``'alt+x'``, not ``'meta+x'``, for
    ``Option+X``.

Creating a ``Keybindings`` instance with a key identifier that names an
unknown modifier (e.g. ``'ctlr+x'`` or ``'cmd+c'``) or no key (e.g.
``'ctrl+'``) throws an ``InvalidArgumentException``.

.. versionadded:: 8.2

    The ``super``, ``hyper`` and ``meta`` modifiers, the ``Key::super()``
    method and the validation of the key identifiers were introduced in
    Symfony 8.2.

Always use these key identifiers instead of raw escape sequences
(``"\x03"``, ``"\x1b[A"``, etc.). The key abstraction handles
differences between terminal encodings (legacy and Kitty protocol)
transparently. The same identifiers work everywhere: keybindings
and ``InputEvent`` listeners.

Widget Default Keybindings
--------------------------

Every focusable widget defines its own default keybindings via
``getDefaultKeybindings()``. For example, ``InputWidget`` maps
``submit`` to Enter, ``select_cancel`` to Escape, ``cursor_left``
to Left arrow, etc.

You can see a widget's defaults in its documentation page under
"Default Keybindings."

Overriding Widget Keybindings
-----------------------------

Pass a ``Keybindings`` instance to the widget constructor to
override specific actions::

    use Symfony\Component\Tui\Input\Key;
    use Symfony\Component\Tui\Input\Keybindings;
    use Symfony\Component\Tui\Widget\EditorWidget;

    $editor = new EditorWidget(keybindings: new Keybindings([
        'submit' => ['ctrl+s'],
        'new_line' => [Key::ENTER],
    ]));

Only the actions you specify are overridden; the remaining actions
keep their defaults.

The Three-Layer Merge
---------------------

When a widget checks its keybindings, three layers are merged
(later layers override earlier ones):

1. **Widget defaults**: the built-in bindings defined by the widget
   class.
2. **Tui-level keybindings**: bindings passed to the ``Tui``
   constructor, shared across all widgets.
3. **Widget-level keybindings**: bindings set on the widget
   instance via ``setKeybindings()`` or the constructor.

This lets you set application-wide overrides at the Tui level while
still allowing per-widget customization::

    use Symfony\Component\Tui\Input\Keybindings;
    use Symfony\Component\Tui\Tui;

    // Application-wide: remap submit to Ctrl+S for all widgets
    $tui = new Tui(keybindings: new Keybindings([
        'submit' => ['ctrl+s'],
    ]));

Display Labels
--------------

The :doc:`/tui/widgets/key_binding` displays the keybindings of the
focused widget. It only displays the first key of each action, so put
the main shortcut first when you override a binding. Use the
``setKeybindingLabels()`` method of the widgets to choose which actions
are displayed and with which labels (see
:ref:`how to change the displayed bindings <tui-keybinding-labels>`).

.. versionadded:: 8.2

    The ``setKeybindingLabels()`` method was introduced in Symfony 8.2.

Action Names
------------

Action names use **snake_case** by convention. Common action names
shared across widgets:

==========================  ==========================================
Action                      Meaning
==========================  ==========================================
``submit``                  Confirm / send
``select_cancel``           Cancel / dismiss
``cursor_up``               Move cursor up
``cursor_down``             Move cursor down
``cursor_left``             Move cursor left
``cursor_right``            Move cursor right
``cursor_word_left``        Move cursor one word left
``cursor_word_right``       Move cursor one word right
``cursor_line_start``       Move cursor to line start
``cursor_line_end``         Move cursor to line end
``delete_char_backward``    Delete character before cursor
``delete_char_forward``     Delete character after cursor
``delete_word_backward``    Delete word before cursor
``select_up``               Move selection up
``select_down``             Move selection down
``select_confirm``          Confirm selection
``select_page_up``          Page up in a list
``select_page_down``        Page down in a list
``choice_toggle``           Toggle the highlighted item in a list
==========================  ==========================================

macOS: Option Key as Alt/Meta
-----------------------------

On macOS, the Option key acts as a compose key by default (for typing
accented characters like e, n). Terminal applications that rely on Alt
key combinations, such as ``alt+backspace`` (delete word),
``alt+d`` (delete word forward), ``alt+b``/``alt+f`` (word
navigation), require the Option key to be configured as Meta/Alt in
your terminal emulator:

====================  =====================================================================
Terminal              Setting
====================  =====================================================================
**Ghostty**           ``macos-option-as-alt = true`` in ``~/.config/ghostty/config``
**iTerm2**            Preferences > Profiles > Keys > Left Option Key > **Esc+**
**Terminal.app**      Preferences > Profiles > Keyboard > **Use Option as Meta key**
**Kitty**             ``macos_option_as_alt yes`` in ``kitty.conf``
====================  =====================================================================

Without this setting, pressing ``Option+Backspace`` is handled by macOS
itself and never reaches the application.

With these settings, the Option key sends Alt key combinations, even
when the terminal calls it "Meta": bind them with ``alt``, not ``meta``.
