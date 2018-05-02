.. index::
   single: Messenger
   single: Components; Messenger

The Messenger Component
=======================

    The Messenger component helps applications send and receive messages to/from
    other applications or via message queues.

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

The bus is used to dispatch messages. The behaviour of the bus is in its ordered
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

.. note:

    Every middleware needs to implement the ``MiddlewareInterface`` interface.

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

Adapters
--------

In order to send and receive messages, you will have to configure an adapter. An
adapter will be responsible of communicating with your message broker or 3rd parties.

Your own sender
~~~~~~~~~~~~~~~

Using the ``SenderInterface``, you can easily create your own message sender.
Let's say you already have an ``ImportantAction`` message going through the
message bus and handled by a handler. Now, you also want to send this message as
an email.

First, create your sender::

    namespace App\MessageSender;

    use App\Message\ImportantAction;
    use Symfony\Component\Messenger\Transport\SenderInterface;

    class ImportantActionToEmailSender implements SenderInterface
    {
       private $toEmail;
       private $mailer;

       public function __construct(\Swift_Mailer $mailer, string $toEmail)
       {
           $this->mailer = $mailer;
           $this->toEmail = $toEmail;
       }

       public function send($message)
       {
           if (!$message instanceof ImportantAction) {
               throw new \InvalidArgumentException(sprintf('Producer only supports "%s" messages.', ImportantAction::class));
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

Your own receiver
~~~~~~~~~~~~~~~~~

A receiver is responsible for receiving messages from a source and dispatching
them to the application.

Let's say you already processed some "orders" in your application using a
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

    class NewOrdersFromCsvFile implements ReceiverInterface
    {
       private $serializer;
       private $filePath;

       public function __construct(SerializerInteface $serializer, string $filePath)
       {
           $this->serializer = $serializer;
           $this->filePath = $filePath;
       }

       public function receive(callable $handler) : void
       {
           $ordersFromCsv = $this->serializer->deserialize(file_get_contents($this->filePath), 'csv');

           foreach ($ordersFromCsv as $orderFromCsv) {
               $handler(new NewOrder($orderFromCsv['id'], $orderFromCsv['account_id'], $orderFromCsv['amount']));
           }
       }

       public function stop(): void
       {
           // noop
       }
    }

Receiver and Sender on the same bus
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To allow us to receive and send messages on the same bus and prevent an infinite
loop, the message bus is equipped with the ``WrapIntoReceivedMessage`` middleware.
It will wrap the received messages into ``ReceivedMessage`` objects and the
``SendMessageMiddleware`` middleware will know it should not route these
messages again to an adapter.
