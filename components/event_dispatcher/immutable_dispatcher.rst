The Immutable Event Dispatcher
==============================

The :class:`Symfony\\Component\\EventDispatcher\\ImmutableEventDispatcher`
is a locked or frozen event dispatcher. The dispatcher cannot register new
listeners or subscribers.

The ``ImmutableEventDispatcher`` takes another event dispatcher with all
the listeners and subscribers. The immutable dispatcher is just a proxy
of this original dispatcher.

To use it, first create a normal ``EventDispatcher`` dispatcher and register
some listeners or subscribers::

    use Symfony\Component\EventDispatcher\EventDispatcher;
    use Symfony\Contracts\EventDispatcher\Event;

    $dispatcher = new EventDispatcher();
    $dispatcher->addListener('foo.action', function (Event $event): void {
        // ...
    });

    // ...

Now, inject that into an ``ImmutableEventDispatcher``::

    use Symfony\Component\EventDispatcher\ImmutableEventDispatcher;
    // ...

    $immutableDispatcher = new ImmutableEventDispatcher($dispatcher);

You'll need to use this new dispatcher in your project.

If you are trying to execute one of the methods which modifies the dispatcher
(e.g. ``addListener()``), a ``BadMethodCallException`` is thrown.
