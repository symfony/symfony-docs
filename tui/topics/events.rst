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
They belong to the widget itself and live as long as it does: taking it
out of the tree and adding it back keeps them. A widget out of the tree
still calls its own listeners, while global listeners only see its
events while it is attached.

``onSubmit()`` and its siblings are wrappers around ``on()``, which
takes the event class and the callback::

    $input->on(SubmitEvent::class, $listener);

Releasing Listeners
-------------------

``off()`` releases what ``on()`` registered. Pass the listener to drop
it, or nothing to drop every listener the widget holds for the event
class::

    $listener = function (SubmitEvent $event) {
        // ...
    };

    $input->on(SubmitEvent::class, $listener);

    $input->off(SubmitEvent::class, $listener);
    $input->off(SubmitEvent::class);

``off()`` matches the listener the way
:method:`Symfony\\Component\\EventDispatcher\\EventDispatcher::removeListener`
does. Two first-class callables of the same method on the same object
match, so ``off(SubmitEvent::class, $service->handle(...))`` releases
what ``on(SubmitEvent::class, $service->handle(...))`` registered. An
inline closure only matches the very instance that was registered, which
is why the example above keeps a reference to it.

When the same callable was registered more than once, ``off()`` releases
every registration of it. Calling it for an event class the widget never
listened to does nothing, so it is safe to call unconditionally.

``off()`` only touches the listeners of the widget itself, those
registered through ``on()`` and its wrappers. It leaves alone the ones
registered with ``Tui::addListener()``, which live on the event
dispatcher.

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
* ``FocusEvent``: focus moved between widgets. Carries the new
  and previous widget.
