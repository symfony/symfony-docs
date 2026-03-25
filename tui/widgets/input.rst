InputWidget
===========

``InputWidget`` is a single-line text input with cursor,
horizontal scrolling and configurable keybindings.

When to Use
-----------

Use ``InputWidget`` when you need a short, single-line text field:
a search box, a filename prompt, a command palette filter, etc.
For multi-line editing, use :doc:`editor` instead.

Basic Usage
-----------

.. code-block:: php

    use Symfony\Component\Tui\Widget\InputWidget;

    $input = new InputWidget();
    $tui->add($input);
    $tui->setFocus($input);

Set an initial value or read the current value::

    $input->setValue('initial text');
    $value = $input->getValue();

Prompt
------

The prompt string is displayed before the cursor. Change it with
``setPrompt()``::

    $input->setPrompt('Search: ');

The default prompt is ``> ``.

Events
------

``InputWidget`` dispatches three events:

* ``SubmitEvent``: Fired when the user presses **Enter**. The event
  carries the current text value::

      use Symfony\Component\Tui\Event\SubmitEvent;

      $input->onSubmit(function (SubmitEvent $event) {
          $value = $event->getValue();
          // process the submitted text
      });

* ``CancelEvent``: Fired when the user presses **Escape** or
  **Ctrl+C**::

      $input->onCancel(function () {
          // close the input or revert changes
      });

* ``ChangeEvent``: Fired on every text modification (typing,
  deleting, pasting)::

      use Symfony\Component\Tui\Event\ChangeEvent;

      $input->onChange(function (ChangeEvent $event) {
          $currentText = $event->getValue();
      });

You can check after the fact whether the input was submitted or
cancelled with ``wasSubmitted()``.

Default Keybindings
-------------------

========================  ==================================
Key                       Action
========================  ==================================
Enter                     Submit
Escape, Ctrl+C            Cancel
Left, Ctrl+B              Move cursor left
Right, Ctrl+F             Move cursor right
Alt+Left, Ctrl+Left       Move word left
Alt+Right, Ctrl+Right     Move word right
Home, Ctrl+A              Move to line start
End, Ctrl+E               Move to line end
Backspace                 Delete character backward
Delete, Ctrl+D            Delete character forward
Ctrl+W, Alt+Backspace     Delete word backward
Ctrl+U                    Delete to line start
Ctrl+K                    Delete to line end
========================  ==================================

Custom Keybindings
------------------

Pass a ``Keybindings`` instance to the constructor to override any
of the default bindings::

    use Symfony\Component\Tui\Input\Keybindings;

    $input = new InputWidget(keybindings: new Keybindings([
        'submit' => ['ctrl+s'],
    ]));
