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
Shift+Space                   Insert a space
Up / Down                     Move cursor
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
Ctrl+C                        Copy (not handled, see below)
============================  ==============================

``Ctrl+C`` is bound to the ``copy`` action, but the editor has no
selection model and does not implement it: the key event is ignored
by the widget and left for the parent application to handle (the
conventional copy or abort signal). Rebind ``copy`` if that key is
needed for something else.
