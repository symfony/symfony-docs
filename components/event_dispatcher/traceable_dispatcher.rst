.. index::
    single: EventDispatcher; Debug
    single: EventDispatcher; Traceable

The Traceable Event Dispatcher
==============================

.. versionadded:: 2.5
    The ``TraceableEventDispatcher`` class was moved to the EventDispatcher
    component in Symfony 2.5. Before, it was located in the HttpKernel component.

The :class:`Symfony\\Component\\EventDispatcher\\Debug\\TraceableEventDispatcher`
is an event dispatcher that wraps any other event dispatcher and can then
be used to determine which event listeners have been called by the dispatcher.
Pass the event dispatcher to be wrapped and an instance of the
:class:`Symfony\\Component\\Stopwatch\\Stopwatch` to its constructor::

    use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
    use Symfony\Component\Stopwatch\Stopwatch;

    // the event dispatcher to debug
    $eventDispatcher = ...;

    $traceableEventDispatcher = new TraceableEventDispatcher($eventDispatcher, new Stopwatch());

Now, the ``TraceableEventDispatcher`` can be used like any other event dispatcher
to register event listeners and dispatch events::

    // ...

    // register an event listener
    $eventListener = ...;
    $priority = ...;
    $traceableEventDispatcher->addListener('the-event-name', $eventListener, $priority);

    // dispatch an event
    $event = ...;
    $traceableEventDispatcher->dispatch('the-event-name', $event);

After your application has been processed, you can use the
:method:`Symfony\\Component\\EventDispatcher\\Debug\\TraceableEventDispatcherInterface::getCalledListeners`
method to retrieve an array of event listeners that have been called in your
application. Similarly, the
:method:`Symfony\\Component\\EventDispatcher\\Debug\\TraceableEventDispatcherInterface::getNotCalledListeners`
method returns an array of event listeners that have not been called::

    // ...

    $calledListeners = $traceableEventDispatcher->getCalledListeners();
    $notCalledListeners = $traceableEventDispatcher->getNotCalledListeners();
