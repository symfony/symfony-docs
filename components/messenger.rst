.. index::
   single: Messenger
   single: Components; Messenger

The Messenger Component
=======================

    The Messenger component helps applications send and receive messages to/from
    other applications or via message queues.

    The component is greatly inspired by Matthias Noback's series of `blog posts
    about command buses`_ and the `SimpleBus project`_.

.. seealso::

    This article explains how to use the Messenger features as an independent
    component in any PHP application. Read the :doc:`/messenger` article to
    learn about how to use it in Symfony applications.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/messenger

Alternatively, you can clone the `<https://github.com/symfony/messenger>`_ repository.

.. include:: /components/require_autoload.rst.inc

Concepts
--------

.. image:: /_images/components/messenger/overview.png

**Sender**:
   Responsible for serializing and sending messages to *something*. This
   something can be a message broker or a third party API for example.

**Receiver**:
   Responsible for retrieving, deserializing and forwarding messages to handler(s).
   This can be a message queue puller or an API endpoint for example.

**Handler**:
   Responsible for handling messages using the business logic applicable to the messages.
   Handlers are called by the ``HandleMessageMiddleware`` middleware.

**Middleware**:
   Middleware can access the message and its wrapper (the envelope) while it is
   dispatched through the bus.
   Literally *"the software in the middle"*, those are not about core concerns
   (business logic) of an application. Instead, they are cross cutting concerns
   applicable throughout the application and affecting the entire message bus.
   For instance: logging, validating a message, starting a transaction, ...
   They are also responsible for calling the next middleware in the chain,
   which means they can tweak the envelope, by adding stamps to it or even
   replacing it, as well as interrupt the middleware chain.

**Envelope**
   Messenger specific concept, it gives full flexibility inside the message bus,
   by wrapping the messages into it, allowing to add useful information inside
   through *envelope stamps*.

**Envelope Stamps**
   Piece of information you need to attach to your message: serializer context
   to use for transport, markers identifying a received message or any sort of
   metadata your middleware or transport layer may use.

Bus
---

The bus is used to dispatch messages. The behavior of the bus is in its ordered
middleware stack. The component comes with a set of middleware that you can use.

When using the message bus with Symfony's FrameworkBundle, the following middleware
are configured for you:

#. :class:`Symfony\\Component\\Messenger\\Middleware\\LoggingMiddleware` (logs the processing of your messages)
#. :class:`Symfony\\Component\\Messenger\\Middleware\\SendMessageMiddleware` (enables asynchronous processing)
#. :class:`Symfony\\Component\\Messenger\\Middleware\\HandleMessageMiddleware` (calls the registered handler(s))

Example::

    use App\Message\MyMessage;
    use Symfony\Component\Messenger\MessageBus;
    use Symfony\Component\Messenger\Handler\HandlersLocator;
    use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

    $bus = new MessageBus([
        new HandleMessageMiddleware(new HandlersLocator([
            MyMessage::class => ['dummy' => $handler],
        ])),
    ]);

    $bus->dispatch(new MyMessage(/* ... */));

.. note::

    Every middleware needs to implement the :class:`Symfony\\Component\\Messenger\\Middleware\\MiddlewareInterface`.

Handlers
--------

Once dispatched to the bus, messages will be handled by a "message handler". A
message handler is a PHP callable (i.e. a function or an instance of a class)
that will do the required processing for your message::

    namespace App\MessageHandler;

    use App\Message\MyMessage;

    class MyMessageHandler
    {
       public function __invoke(MyMessage $message)
       {
           // Message processing...
       }
    }

Adding Metadata to Messages (Envelopes)
---------------------------------------

If you need to add metadata or some configuration to a message, wrap it with the
:class:`Symfony\\Component\\Messenger\\Envelope` class and add stamps.
For example, to set the serialization groups used when the message goes
through the transport layer, use the ``SerializerStamp`` stamp::

    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\Stamp\SerializerStamp;

    $bus->dispatch(
        (new Envelope($message))->with(new SerializerStamp([
            'groups' => ['my_serialization_groups'],
        ]))
    );

At the moment, the Symfony Messenger has the following built-in envelope stamps:

#. :class:`Symfony\\Component\\Messenger\\Stamp\\SerializerStamp`,
   to configure the serialization groups used by the transport.
#. :class:`Symfony\\Component\\Messenger\\Stamp\\ValidationStamp`,
   to configure the validation groups used when the validation middleware is enabled.
#. :class:`Symfony\\Component\\Messenger\\Stamp\\ReceivedStamp`,
   an internal stamp that marks the message as received from a transport.
#. :class:`Symfony\\Component\\Messenger\\Stamp\\SentStamp`,
   a stamp that marks the message as sent by a specific sender.
   Allows accessing the sender FQCN and the alias if available from the
   :class:`Symfony\\Component\\Messenger\\Transport\\Sender\\SendersLocator`.
#. :class:`Symfony\\Component\\Messenger\\Stamp\\HandledStamp`,
   a stamp that marks the message as handled by a specific handler.
   Allows accessing the handler returned value, the handler callable name
   and its alias if available from the :class:`Symfony\\Component\\Messenger\\Handler\\HandlersLocator`.

Instead of dealing directly with the messages in the middleware you receive the envelope.
Hence you can inspect the envelope content and its stamps, or add any::

    use App\Message\Stamp\AnotherStamp;
    use Symfony\Component\Messenger\Stamp\ReceivedStamp;
    use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
    use Symfony\Component\Messenger\Middleware\StackInterface;

    class MyOwnMiddleware implements MiddlewareInterface
    {
        public function handle(Envelope $envelope, StackInterface $stack): Envelope
        {
            if (null !== $envelope->last(ReceivedStamp::class)) {
                // Message just has been received...

                // You could for example add another stamp.
                $envelope = $envelope->with(new AnotherStamp(/* ... */));
            }

            return $stack->next()->handle($envelope, $stack);
        }
    }

The above example will forward the message to the next middleware with an additional
stamp *if* the message has just been received (i.e. has the `ReceivedStamp` stamp).
You can create your own stamps by implementing :class:`Symfony\\Component\\Messenger\\Stamp\\StampInterface`.

.. note::

    Any stamp must be serializable using the Symfony Serializer component
    if going through transport using the :class:`Symfony\\Component\\Messenger\\Transport\\Serialization\\Serializer`
    base serializer.

Transports
----------

In order to send and receive messages, you will have to configure a transport. A
transport will be responsible for communicating with your message broker or 3rd parties.

Your own Sender
~~~~~~~~~~~~~~~

Using the :class:`Symfony\\Component\\Messenger\\Transport\\Sender\\SenderInterface`,
you can create your own message sender.
Imagine that you already have an ``ImportantAction`` message going through the
message bus and being handled by a handler. Now, you also want to send this
message as an email.

First, create your sender::

    namespace App\MessageSender;

    use App\Message\ImportantAction;
    use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
    use Symfony\Component\Messenger\Envelope;

    class ImportantActionToEmailSender implements SenderInterface
    {
       private $mailer;
       private $toEmail;

       public function __construct(\Swift_Mailer $mailer, string $toEmail)
       {
           $this->mailer = $mailer;
           $this->toEmail = $toEmail;
       }

       public function send(Envelope $envelope): Envelope
       {
           $message = $envelope->getMessage();

           if (!$message instanceof ImportantAction) {
               throw new \InvalidArgumentException(sprintf('This transport only supports "%s" messages.', ImportantAction::class));
           }

           $this->mailer->send(
               (new \Swift_Message('Important action made'))
                   ->setTo($this->toEmail)
                   ->setBody(
                       '<h1>Important action</h1><p>Made by '.$message->getUsername().'</p>',
                       'text/html'
                   )
           );

           return $envelope;
       }
    }

Your own Receiver
~~~~~~~~~~~~~~~~~

A receiver is responsible for getting messages from a source and dispatching
them to the application.

Imagine you already processed some "orders" in your application using a
``NewOrder`` message. Now you want to integrate with a 3rd party or a legacy
application but you can't use an API and need to use a shared CSV file with new
orders.

You will read this CSV file and dispatch a ``NewOrder`` message. All you need to
do is to write your custom CSV receiver and Symfony will do the rest.

First, create your receiver::

    namespace App\MessageReceiver;

    use App\Message\NewOrder;
    use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
    use Symfony\Component\Serializer\SerializerInterface;
    use Symfony\Component\Messenger\Envelope;

    class NewOrdersFromCsvFileReceiver implements ReceiverInterface
    {
       private $serializer;
       private $filePath;

       public function __construct(SerializerInterface $serializer, string $filePath)
       {
           $this->serializer = $serializer;
           $this->filePath = $filePath;
       }

       public function receive(callable $handler): void
       {
           $ordersFromCsv = $this->serializer->deserialize(file_get_contents($this->filePath), 'csv');

           foreach ($ordersFromCsv as $orderFromCsv) {
               $order = new NewOrder($orderFromCsv['id'], $orderFromCsv['account_id'], $orderFromCsv['amount']);

               $handler(new Envelope($order));
           }
       }

       public function stop(): void
       {
           // noop
       }
    }

Receiver and Sender on the same Bus
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To allow sending and receiving messages on the same bus and prevent an infinite
loop, the message bus will add a :class:`Symfony\\Component\\Messenger\\Stamp\\ReceivedStamp`
stamp to the message envelopes and the :class:`Symfony\\Component\\Messenger\\Middleware\\SendMessageMiddleware`
middleware will know it should not route these messages again to a transport.

.. _blog posts about command buses: https://matthiasnoback.nl/tags/command%20bus/
.. _SimpleBus project: http://simplebus.io
