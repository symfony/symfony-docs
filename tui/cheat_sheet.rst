Interactive Widgets Cheat Sheet
===============================

A quick reference of all keyboard shortcuts and
interactive features for every focusable widget.

.. tip::

    **F6** / **Shift+F6** cycle focus between widgets in any
    application. See :doc:`/tui/topics/focus` for details.

Bracketed Paste
---------------

When you paste text into a terminal (``Cmd+V`` on macOS,
``Ctrl+Shift+V`` on Linux), most modern terminals use a protocol
called **bracketed paste mode**. The terminal wraps the pasted
content between two special escape sequences so the application can
tell the difference between text you typed and text you pasted.

Why does it matter? Without this protocol, pasting a multi-line
block into an editor would be indistinguishable from typing each
character one by one. Every newline would trigger the "submit"
action, every ``Escape`` character would cancel, and so on. With
bracketed paste, the application receives the entire block as a
single paste event and inserts it at the cursor without triggering
any shortcut.

Both ``InputWidget`` and ``EditorWidget`` support bracketed paste.
The details are noted in each widget's section below.

InputWidget
-----------

Single-line text field with horizontal scrolling and paste support.

**Keyboard:**

============================  ==============================
Key                           Action
============================  ==============================
Enter                         Submit
Escape, Ctrl+C                Cancel
Left, Ctrl+B                  Move cursor left
Right, Ctrl+F                 Move cursor right
Alt+Left, Ctrl+Left, Alt+B    Move word left
Alt+Right, Ctrl+Right, Alt+F  Move word right
Home, Ctrl+A                  Move to start of line
End, Ctrl+E                   Move to end of line
Backspace, Shift+Backspace    Delete character backward
Delete, Ctrl+D, Shift+Delete  Delete character forward
Ctrl+W, Alt+Backspace         Delete word backward
Ctrl+U                        Delete to start of line
Ctrl+K                        Delete to end of line
============================  ==============================

**Paste:**

* `Bracketed paste`_ is supported. Pasted text is inserted at the
  cursor; newlines are stripped since this is a single-line widget.

EditorWidget
------------

Multi-line text editor with word wrap, scrolling, undo/redo, kill
ring and autocomplete.

**Keyboard:**

============================  ==============================
Key                           Action
============================  ==============================
Enter                         Submit
Shift+Enter                   Insert newline
Escape, Ctrl+C                Cancel (or close autocomplete)
Tab                           Trigger autocomplete
Shift+Space                   Insert a space
Up                            Move up (start of first line)
Down                          Move down (end of last line)
Left, Ctrl+B                  Move cursor left
Right, Ctrl+F                 Move cursor right
Alt+Left, Ctrl+Left, Alt+B    Move word left
Alt+Right, Ctrl+Right, Alt+F  Move word right
Home, Ctrl+A                  Move to start of line
End, Ctrl+E                   Move to end of line
Page Up                       Scroll up by page
Page Down                     Scroll down by page
Ctrl+]                        Jump forward to a character
Ctrl+Alt+]                    Jump backward to a character
Backspace, Shift+Backspace    Delete character backward
Delete, Ctrl+D, Shift+Delete  Delete character forward
Ctrl+W, Alt+Backspace         Delete word backward
Alt+D, Alt+Delete             Delete word forward
Ctrl+Shift+K                  Delete entire line
Ctrl+U                        Delete to start of line
Ctrl+K                        Delete to end of line
Ctrl+- (minus)                Undo
Ctrl+Shift+Z                  Redo
Ctrl+Y                        Yank (paste from kill ring)
Alt+Y                         Yank pop (cycle kill ring)
============================  ==============================

**Autocomplete (when the dropdown is open):**

====================  ==============================
Key                   Action
====================  ==============================
Up                    Previous suggestion
Down                  Next suggestion
Page Up               Page up through suggestions
Page Down             Page down through suggestions
Enter                 Accept suggestion
Escape                Close dropdown
====================  ==============================

**Paste:**

* `Bracketed paste`_ is supported. Pasted text is inserted at the
  cursor with newlines preserved.
* Large pastes (more than 10 lines) are collapsed into a compact
  marker; the full content is available via ``getPasteMarkers()``.

SelectListWidget
----------------

Scrollable list with single-item selection.

**Keyboard:**

==========================  ==============================
Key                         Action
==========================  ==============================
Up                          Move selection up (wraps)
Down                        Move selection down (wraps)
Page Up, Left, Ctrl+B       Page up
Page Down, Right, Ctrl+F    Page down
Enter                       Confirm selection
Escape, Ctrl+C              Cancel
==========================  ==============================

SettingsListWidget
------------------

Navigable settings panel with value cycling and submenus.

**Keyboard:**

========================  =========================================
Key                       Action
========================  =========================================
Up                        Move selection up
Down                      Move selection down
Page Up                   Page up
Page Down                 Page down
Enter, Space              Activate (cycle value or open submenu)
Right, Ctrl+F             Cycle value forward
Left, Ctrl+B              Cycle value backward
Escape, Ctrl+C            Cancel
========================  =========================================

CancellableLoaderWidget
-----------------------

Animated spinner that can be interrupted by the user.

**Keyboard:**

====================  ==============================
Key                   Action
====================  ==============================
Escape, Ctrl+C        Cancel the operation
====================  ==============================
