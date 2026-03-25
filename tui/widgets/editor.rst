EditorWidget
============

``EditorWidget`` is a full-featured multi-line text editor with word
wrapping, scrolling, undo/redo, a kill ring and autocomplete support.

When to Use
-----------

Use ``EditorWidget`` when you need multi-line text editing: a message
composer, a code snippet editor, a configuration file viewer, etc.
For single-line input, use :doc:`input` instead.

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

Autocomplete
------------

Attach an autocomplete provider to get context-aware suggestions when
the user presses **Tab**::

    use Symfony\Component\Tui\Autocomplete\FilePathProvider;

    $editor->setAutocompleteProvider(new FilePathProvider());

The autocomplete dropdown appears inline and can be navigated with
**Up**/**Down** and confirmed with **Enter**. Press **Escape** to
dismiss it.

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

* ``CancelEvent``: Fired when the user presses **Escape** or
  **Ctrl+C** (if no autocomplete dropdown is open)::

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
Escape, Ctrl+C                Cancel / close autocomplete
Tab                           Trigger autocomplete
Up / Down                     Move cursor or navigate autocomplete
Left, Ctrl+B                  Move cursor left
Right, Ctrl+F                 Move cursor right
Alt+Left, Ctrl+Left           Move word left
Alt+Right, Ctrl+Right         Move word right
Home, Ctrl+A                  Move to line start
End, Ctrl+E                   Move to line end
Page Up / Page Down           Scroll by page
Backspace                     Delete character backward
Delete, Ctrl+D                Delete character forward
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
