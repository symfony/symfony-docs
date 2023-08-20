Getting Results from your Handler
=================================

When a message is handled, the :class:`Symfony\\Component\\Messenger\\Middleware\\HandleMessageMiddleware`
adds a :class:`Symfony\\Component\\Messenger\\Stamp\\HandledStamp` for each object that handled the message.
You can use this to get the value returned by the handler(s)::

    use Symfony\Component\Messenger\MessageBusInterface;
    use Symfony\Component\Messenger\Stamp\HandledStamp;

    $envelope = $messageBus->dispatch(new SomeMessage());

    // get the value that was returned by the last message handler
    $handledStamp = $envelope->last(HandledStamp::class);
    $handledStamp->getResult();

    // or get info about all of handlers
    $handledStamps = $envelope->all(HandledStamp::class);

Working with Command & Query Buses
----------------------------------

The Messenger component can be used in CQRS architectures where command & query
buses are central pieces of the application. Read Martin Fowler's
`article about CQRS`_ to learn more and
:doc:`how to configure multiple buses </messenger/multiple_buses>`.

As queries are usually synchronous and expected to be handled once,
getting the result from the handler is a common need.

A :class:`Symfony\\Component\\Messenger\\HandleTrait` exists to get the result
of the handler when processing synchronously. It also ensures that exactly one
handler is registered. The ``HandleTrait`` can be used in any class that has a
``$messageBus`` property::

    // src/Action/ListItems.php
    namespace App\Action;

    use App\Message\ListItemsQuery;
    use App\MessageHandler\ListItemsQueryResult;
    use Symfony\Component\Messenger\HandleTrait;
    use Symfony\Component\Messenger\MessageBusInterface;

    class ListItems
    {
        use HandleTrait;

        public function __construct(
            private MessageBusInterface $messageBus,
        ) {
        }

        public function __invoke(): void
        {
            $result = $this->query(new ListItemsQuery(/* ... */));

            // Do something with the result
            // ...
        }

        // Creating such a method is optional, but allows type-hinting the result
        private function query(ListItemsQuery $query): ListItemsQueryResult
        {
            return $this->handle($query);
        }
    }

Hence, you can use the trait to create command & query bus classes.
For example, you could create a special ``QueryBus`` class and inject it
wherever you need a query bus behavior instead of the ``MessageBusInterface``::

    // src/MessageBus/QueryBus.php
    namespace App\MessageBus;

    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\HandleTrait;
    use Symfony\Component\Messenger\MessageBusInterface;

    class QueryBus
    {
        use HandleTrait;

        public function __construct(
            private MessageBusInterface $messageBus,
        ) {
        }

        /**
         * @param object|Envelope $query
         *
         * @return mixed The handler returned value
         */
        public function query($query): mixed
        {
            return $this->handle($query);
        }
    }

.. _`article about CQRS`: https://martinfowler.com/bliki/CQRS.html
