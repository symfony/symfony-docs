Events
======

Widgets communicate with your application through events. Tui uses
the Symfony EventDispatcher, so all events flow through a single
dispatcher shared by the entire widget tree.

Per-Widget Listeners
--------------------

The most common way to handle events is to register a callback on
the widget that emits them:

.. code-block:: php

    use Symfony\Component\Tui\Event\SubmitEvent;

    $input->onSubmit(function (SubmitEvent $event) {
        $value = $event->getValue();
    });

    $input->onCancel(function () use ($tui) {
        $tui->stop();
    });

Per-widget listeners are automatically scoped to the target widget.
They are also cleaned up when the widget is detached from the tree.

Global Listeners
----------------

Register a listener on the Tui to catch events from any widget:

.. code-block:: php

    use Symfony\Component\Tui\Event\CancelEvent;

    $tui->on(CancelEvent::class, function (CancelEvent $event) {
        // Fires when ANY widget dispatches CancelEvent
        $tui->stop();
    });

This is useful when multiple widgets should trigger the same
behavior (e.g. stopping the Tui on cancel).

When you need to distinguish which widget fired the event, use
``getTarget()``:

.. code-block:: php

    use Symfony\Component\Tui\Event\SubmitEvent;

    $tui->on(SubmitEvent::class, function (SubmitEvent $event) use ($editor) {
        if ($event->getTarget() === $editor) {
            // handle editor submit
        }
    });

Quitting the Application
------------------------

``Tui::quitOn()`` registers key patterns that stop the Tui
automatically:

.. code-block:: php

    $tui->quitOn('ctrl+c');
    $tui->quitOn('ctrl+c', 'ctrl+q'); // multiple keys

Quit keys are checked first, before any other input handling.

Global Input Interceptor
------------------------

``Tui::onInput()`` registers a callback that runs before any widget
receives keyboard input. Return ``true`` to consume the input and
prevent further processing:

.. code-block:: php

    $tui->onInput(function (string $data) use ($tui): bool {
        if ('?' === $data) {
            // show a help overlay
            return true;
        }

        return false;
    });

The input flow is: quit keys, then global interceptor, then focus
manager (F6 cycling), then the focused widget.

Event Types
-----------

* ``SubmitEvent``: the user confirmed input (Enter). Carries
  a text value.
* ``CancelEvent``: the user cancelled (Escape, Ctrl+C).
* ``ChangeEvent``: the text content changed. Carries a text value.
* ``SelectEvent``: the user picked an item from a list. Carries
  the value, label and full item array.
* ``SelectionChangeEvent``: the highlighted item changed (arrow
  keys, scroll).
* ``SettingChangeEvent``: a setting value changed. Carries the
  setting id and new value.
* ``TabChangeEvent``: the active tab changed. Carries previous
  and new indices/ids.
* ``FocusEvent``: focus moved between widgets. Carries the new
  and previous widget.
