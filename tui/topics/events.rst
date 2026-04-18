Events
======

Widgets communicate with your application through events. Tui uses
the Symfony EventDispatcher, so all events flow through a single
dispatcher shared by the entire widget tree.

Per-Widget Listeners
--------------------

The most common way to handle events is to register a callback on
the widget that emits them::

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

Register a listener on the Tui to catch events from any widget.
The event class is inferred from the listener's type hint::

    use Symfony\Component\Tui\Event\CancelEvent;

    $tui->addListener(function (CancelEvent $event) {
        // Fires when ANY widget dispatches CancelEvent
        $tui->stop();
    });

This is useful when multiple widgets should trigger the same
behavior (e.g. stopping the Tui on cancel).

When you need to distinguish which widget fired the event, use
``getTarget()``::

    use Symfony\Component\Tui\Event\SubmitEvent;

    $tui->addListener(function (SubmitEvent $event) use ($editor) {
        if ($event->getTarget() === $editor) {
            // handle editor submit
        }
    });

Global Input Interceptor
------------------------

``InputEvent`` is dispatched before focus navigation and before the
focused widget receives input. Register a listener with
``Tui::addListener()`` to intercept raw input. Call
``stopPropagation()`` to consume the input and prevent further
processing::

    use Symfony\Component\Tui\Event\InputEvent;
    use Symfony\Component\Tui\Input\Keybindings;

    $keys = new Keybindings(['quit' => ['ctrl+c', 'ctrl+q']]);
    $tui->addListener(function (InputEvent $event) use ($tui, $keys): void {
        if ($keys->matches($event->getData(), 'quit')) {
            $tui->stop();
        }
    });

    $tui->addListener(function (InputEvent $event): void {
        if ('?' === $event->getData()) {
            // show a help overlay
            $event->stopPropagation();
        }
    });

Multiple listeners can be registered; they run in priority order.
The input flow is: ``InputEvent`` listeners, then focus manager
(F6 cycling), then the focused widget.

Event Types
-----------

* ``InputEvent``: raw terminal input received. Dispatched before
  focus handling; call ``stopPropagation()`` to consume the input.
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
