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
   Responsible for deserializing and forwarding messages to handler(s). This
   can be a message queue puller or an API endpoint for example.

**Handler**:
   Responsible for handling messages using the business logic applicable to the messages.

Bus
---

The bus is used to dispatch messages. The behavior of the bus is in its ordered
middleware stack. The component comes with a set of middleware that you can use.

When using the message bus with Symfony's FrameworkBundle, the following middleware
are configured for you:

#. ``LoggingMiddleware`` (logs the processing of your messages)
#. ``SendMessageMiddleware`` (enables asynchronous processing)
#. ``HandleMessageMiddleware`` (calls the registered handle)

Example::

    use App\Message\MyMessage;
    use Symfony\Component\Messenger\MessageBus;
    use Symfony\Component\Messenger\HandlerLocator;
    use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

    $bus = new MessageBus([
        new HandleMessageMiddleware(new HandlerLocator([
            MyMessage::class => $handler,
        ])),
    ]);

    $result = $bus->dispatch(new MyMessage(/* ... */));

.. note::

    Every middleware needs to implement the ``MiddlewareInterface``.

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
:class:`Symfony\\Component\\Messenger\\Envelope` class. For example, to set the
serialization groups used when the message goes through the transport layer, use
the ``SerializerConfiguration`` envelope::

    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\Transport\Serialization\SerializerConfiguration;

    $bus->dispatch(
        (new Envelope($message))->with(new SerializerConfiguration([
            'groups' => ['my_serialization_groups'],
        ]))
    );

At the moment, the Symfony Messenger has the following built-in envelopes:

1. :class:`Symfony\\Component\\Messenger\\Transport\\Serialization\\SerializerConfiguration`,
   to configure the serialization groups used by the transport.
2. :class:`Symfony\\Component\\Messenger\\Middleware\\Configuration\\ValidationConfiguration`,
   to configure the validation groups used when the validation middleware is enabled.
3. :class:`Symfony\\Component\\Messenger\\Asynchronous\\Transport\\ReceivedMessage`,
   an internal item that marks the message as received from a transport.

Instead of dealing directly with the messages in the middleware you can receive the
envelope by implementing the :class:`Symfony\\Component\\Messenger\\EnvelopeAwareInterface`
marker, like this::

    use Symfony\Component\Messenger\Asynchronous\Transport\ReceivedMessage;
    use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
    use Symfony\Component\Messenger\EnvelopeAwareInterface;

    class MyOwnMiddleware implements MiddlewareInterface, EnvelopeAwareInterface
    {
        public function handle($message, callable $next)
        {
            // $message here is an `Envelope` object, because this middleware
            // implements the EnvelopeAwareInterface interface.

            if (null !== $message->get(ReceivedMessage::class)) {
                // Message just has been received...

                // You could for example add another item.
                $message = $message->with(new AnotherEnvelopeItem(/* ... */));
            }

            return $next($message);
        }
    }

The above example will forward the message to the next middleware with an additional
envelope item if the message has just been received (i.e. has the `ReceivedMessage` item).
You can create your own items by implementing the :class:`Symfony\\Component\\Messenger\\EnvelopeAwareInterface`
interface.

Transports
----------

In order to send and receive messages, you will have to configure a transport. A
transport will be responsible for communicating with your message broker or 3rd parties.

Your own Sender
~~~~~~~~~~~~~~~

Using the ``SenderInterface``, you can easily create your own message sender.
Imagine that you already have an ``ImportantAction`` message going through the
message bus and being handled by a handler. Now, you also want to send this
message as an email.

First, create your sender::

    namespace App\MessageSender;

    use App\Message\ImportantAction;
    use Symfony\Component\Messenger\Transport\SenderInterface;
    use Symfony\Component\Messenger\Envelope;

    class ImportantActionToEmailSender implements SenderInterface
    {
       private $toEmail;
       private $mailer;

       public function __construct(\Swift_Mailer $mailer, string $toEmail)
       {
           $this->mailer = $mailer;
           $this->toEmail = $toEmail;
       }

       public function send(Envelope $envelope)
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
       }
    }

Your own Receiver
~~~~~~~~~~~~~~~~~

A receiver is responsible for receiving messages from a source and dispatching
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
    use Symfony\Component\Messenger\Transport\ReceiverInterface;
    use Symfony\Component\Serializer\SerializerInterface;
    use Symfony\Component\Messenger\Envelope;

    class NewOrdersFromCsvFile implements ReceiverInterface
    {
       private $serializer;
       private $filePath;

       public function __construct(SerializerInterface $serializer, string $filePath)
       {
           $this->serializer = $serializer;
           $this->filePath = $filePath;
       }

       public function receive(callable $handler) : void
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
loop, the message bus will add a :class:`Symfony\\Component\\Messenger\\Asynchronous\\Transport\\ReceivedMessage`
envelope item to the message envelopes and the :class:`Symfony\\Component\\Messenger\\Asynchronous\\Middleware\\SendMessageMiddleware`
middleware will know it should not route these messages again to a transport.

.. _blog posts about command buses: https://matthiasnoback.nl/tags/command%20bus/
.. _SimpleBus project: http://simplebus.io
