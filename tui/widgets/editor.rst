EditorWidget
============

``EditorWidget`` is a full-featured multi-line text editor with word
wrapping, scrolling, undo/redo and a kill ring.

When to Use
-----------

Use ``EditorWidget`` when you need multi-line text editing: a message
composer, a code snippet editor, a configuration file viewer, etc.
For single-line input, use :doc:`/tui/widgets/input` instead.

Basic Usage
-----------

.. code-block:: php

    use Symfony\Component\Tui\Widget\EditorWidget;

    $editor = new EditorWidget();
    $tui->add($editor);
    $tui->setFocus($editor);

Set or read the editor content::

    $editor->setText("Line one\nLine two");
    $content = $editor->getText();

Sizing
------

Control the visible height of the editor with minimum and maximum line
counts::

    $editor->setMinVisibleLines(5);
    $editor->setMaxVisibleLines(20);

To make the editor fill all remaining vertical space, call::

    $editor->expandVertically(true);

By default, the text is displayed between two horizontal borders, which
show how many lines are scrolled out of view above and below. Remove
them with ``setFramed(false)``, for instance when the editor is inside a
panel that already has a border::

    $editor->setFramed(false);

A vertically expanded editor without borders gives their rows to the
text.

.. versionadded:: 8.3

    The ``setFramed()`` method was introduced in Symfony 8.3.

Line Wrapping
-------------

.. versionadded:: 8.3

    The ``setLineWrapping()`` method was introduced in Symfony 8.3.

Long lines wrap onto several rows by default. Call
``setLineWrapping(false)`` to keep each line on one row instead; the
editor then scrolls horizontally to keep the cursor in view::

    $editor->setLineWrapping(false);

Placeholder
-----------

.. versionadded:: 8.3

    The ``setPlaceholder()`` method was introduced in Symfony 8.3.

A placeholder is a hint displayed on the first row while the editor is
empty. It is styled by the ``placeholder`` sub-element, gray by
default::

    $editor->setPlaceholder('Write a message');

Read-only Editors
-----------------

.. versionadded:: 8.3

    The ``setReadOnly()`` method was introduced in Symfony 8.3.

A read-only editor displays a text the user can't change. It can still
get the focus, the user can still move its cursor and scroll, and
**Enter** and **Escape** still dispatch their events, but typing,
pasting, undo, redo and yanks are ignored::

    $editor->setText($logs)->setReadOnly();

Limiting the Length
-------------------

.. versionadded:: 8.3

    The ``setMaxLength()`` method was introduced in Symfony 8.3.

``setMaxLength()`` limits the number of characters of the text, line
breaks included::

    $editor->setMaxLength(500);

Typed and pasted text is cut to the room left. Any other edit that would
make the text longer than the limit, such as yanking from the kill ring
or inserting a new line, is ignored. As for inputs, the length is
counted in characters as the user sees them (grapheme clusters). A text
passed to ``setText()`` is not limited. Pass ``null`` to remove the
limit.

.. note::

    The editor counts the characters of the whole text on each edit, so
    the limit adds a small cost to each key press for very long texts.

Events
------

``EditorWidget`` dispatches three events:

* ``SubmitEvent``: Fired when the user presses **Enter** (the
  default submit binding). Use ``Shift+Enter`` to insert a newline
  instead::

      use Symfony\Component\Tui\Event\SubmitEvent;

      $editor->onSubmit(function (SubmitEvent $event) {
          $text = $event->getValue();
      });

* ``CancelEvent``: Fired when the user presses **Escape**::

      $editor->onCancel(function () {
          // discard changes or close the editor
      });

* ``ChangeEvent``: Fired on every text modification::

      use Symfony\Component\Tui\Event\ChangeEvent;

      $editor->onChange(function (ChangeEvent $event) {
          $currentText = $event->getValue();
      });

Default Keybindings
-------------------

============================  ==============================
Key                           Action
============================  ==============================
Enter                         Submit
Shift+Enter                   Insert newline
Escape                        Cancel
Ctrl+C                        Ignored (left to the application)
Shift+Space                   Insert a space
Up / Down                     Move cursor up / down
Left, Ctrl+B                  Move cursor left
Right, Ctrl+F                 Move cursor right
Alt+Left, Ctrl+Left, Alt+B    Move word left
Alt+Right, Ctrl+Right, Alt+F  Move word right
Home, Ctrl+A                  Move to line start
End, Ctrl+E                   Move to line end
Page Up / Page Down           Scroll by page
Backspace, Shift+Backspace    Delete character backward
Delete, Ctrl+D, Shift+Delete  Delete character forward
Ctrl+W, Alt+Backspace         Delete word backward
Alt+D, Alt+Delete             Delete word forward
Ctrl+Shift+K                  Delete entire line
Ctrl+U                        Delete to line start
Ctrl+K                        Delete to line end
Ctrl+- (minus)                Undo
Ctrl+Shift+Z                  Redo
Ctrl+Y                        Yank (paste from kill ring)
Alt+Y                         Yank pop (cycle kill ring)
Ctrl+]                        Jump forward to character
Ctrl+Alt+]                    Jump backward to character
============================  ==============================

``Ctrl+C`` is bound to the ``copy`` action, but the editor doesn't
support text selection, so it ignores this key and lets your application
handle it (e.g. to stop the Tui).
