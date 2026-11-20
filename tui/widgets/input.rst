InputWidget
===========

``InputWidget`` is a single-line text input with cursor,
horizontal scrolling and configurable keybindings.

When to Use
-----------

Use ``InputWidget`` when you need a short, single-line text field:
a search box, a filename prompt, a command palette filter, etc.
For multi-line editing, use :doc:`/tui/widgets/editor` instead.

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

The default prompt is ``> ``. Read it with ``getPrompt()``.

.. versionadded:: 8.3

    The ``getPrompt()`` method was introduced in Symfony 8.3.

Placeholder
-----------

.. versionadded:: 8.3

    The ``setPlaceholder()`` method was introduced in Symfony 8.3.

A placeholder is a hint displayed while the input is empty::

    $input->setPlaceholder('Search packages');

It is styled by the ``placeholder`` sub-element, gray by default.

Read-only Inputs
----------------

.. versionadded:: 8.3

    The ``setReadOnly()`` method was introduced in Symfony 8.3.

A read-only input displays a value the user can't change. It can still
get the focus, the user can still move its cursor, and **Enter** and
**Escape** still dispatch their events, but typing and pasting are
ignored::

    $input->setValue('prod-eu-1')->setReadOnly();

Limiting the Length
-------------------

.. versionadded:: 8.3

    The ``setMaxLength()`` method was introduced in Symfony 8.3.

``setMaxLength()`` limits the number of characters the user can type,
paste or accept from a suggestion. A pasted text is cut to the room
left::

    $input->setMaxLength(64);

The length is counted in characters as the user sees them (grapheme
clusters): an accented letter or an emoji made of several code points
counts as one, and is never cut in two. Values passed to ``setValue()``
are not limited, and the user can always delete characters. Pass
``null`` to remove the limit.

Masked Inputs
-------------

.. versionadded:: 8.3

    The ``setMasked()`` method was introduced in Symfony 8.3.

A masked input displays a bullet for each character of its value, which
is useful for passwords and other secrets::

    $password = new InputWidget()
        ->setPrompt('Password: ')
        ->setMasked();

``getValue()`` still returns the real value.

Suggestions
-----------

.. versionadded:: 8.3

    The ``setSuggestions()`` and ``getCompletion()`` methods were
    introduced in Symfony 8.3.

Pass a list of suggestions to complete what the user types. The first
suggestion that starts with the value, ignoring case, is displayed after
the cursor and styled by the ``suggestion`` sub-element (gray by
default)::

    $input->setSuggestions(['symfony/console', 'symfony/tui']);

    // the user types "sy": the input displays "symfony/console",
    // with "mfony/console" in gray
    $input->getCompletion(); // 'mfony/console'

**Tab**, or **Right** when the cursor is at the end of the value,
replaces the value with the suggestion as written (typing ``sy`` and
accepting ``Symfony`` gives ``Symfony``) and dispatches a
``ChangeEvent``. Masked inputs never display suggestions.

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

  Changing the value with ``setValue()`` doesn't dispatch this event,
  unless you pass ``true`` as its second argument::

      $input->setValue('new value', dispatch: true);

  .. versionadded:: 8.3

      The ``dispatch`` argument of ``setValue()`` was introduced in
      Symfony 8.3.

You can check after the fact whether the input was submitted or
cancelled with ``wasSubmitted()``.

Default Keybindings
-------------------

============================  ==================================
Key                           Action
============================  ==================================
Enter                         Submit
Escape, Ctrl+C                Cancel
Left, Ctrl+B                  Move cursor left
Right, Ctrl+F                 Move cursor right
Alt+Left, Ctrl+Left, Alt+B    Move word left
Alt+Right, Ctrl+Right, Alt+F  Move word right
Home, Ctrl+A                  Move to line start
End, Ctrl+E                   Move to line end
Backspace, Shift+Backspace    Delete character backward
Delete, Ctrl+D, Shift+Delete  Delete character forward
Ctrl+W, Alt+Backspace         Delete word backward
Ctrl+U                        Delete to line start
Ctrl+K                        Delete to line end
Tab, Right (at the end)       Accept the suggestion
============================  ==================================

Custom Keybindings
------------------

Pass a ``Keybindings`` instance to the constructor to override any
of the default bindings::

    use Symfony\Component\Tui\Input\Keybindings;

    $input = new InputWidget(keybindings: new Keybindings([
        'submit' => ['ctrl+s'],
    ]));
