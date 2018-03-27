.. index::
   single: Messenger
   single: Components; Messenger

The Messenger Component
=======================

    The Messenger component helps application send and receive messages to/from other applications or via message queues.

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
   Responsible for serializing and sending the message to _something_. This
   something can be a message broker or a third party API for example.

**Receiver**:
   Responsible for deserializing and forwarding the messages to handler(s). This
   can be a message queue puller or an API endpoint for example.

**Handler**:
   Given a received message, contains the user business logic related to the
   message. In practice, that is just a PHP callable.

Bus
---

The bus is used to dispatch messages. MessageBus' behaviour is in its ordered
middleware stack. When using the message bus with Symfony's FrameworkBundle, the
following middlewares are configured for you:

#. :code:`LoggingMiddleware` (logs the processing of your messages)
#. :code:`SendMessageMiddleware` (enables asynchronous processing)
#. :code:`HandleMessageMiddleware` (calls the registered handle)

Example::

    use App\Message\MyMessage;
    use Symfony\Component\Messenger\MessageBus;
    use Symfony\Component\Messenger\HandlerLocator;
    use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

    $bus = new MessageBus([
        new HandleMessageMiddleware(new HandlerLocator([
            MyMessage::class => $handler,
        ]))
    ]);

    $result = $bus->handle(new MyMessage(/* ... */));

Handlers
--------

Once dispatched to the bus, messages will be handled by a "message handler". A
message handler is a PHP callable (i.e. a function or an instance of a class)
that will do the required processing for your message. It _might_ return a
result::

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

The communication with queuing system or third parties is delegated to
libraries for now.

Create your adapter
~~~~~~~~~~~~~~~~~~~

Your own sender
---------------

Using the ``SenderInterface``, you can easily create your own message sender.
Let's say you already have an ``ImportantAction`` message going through the
message bus and handled by a handler. Now, you also want to send this message as
an email.

First, create your sender::

    namespace App\MessageSender;

    use App\Message\ImportantAction;
    use Symfony\Component\Message\SenderInterface;

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
-----------------

A consumer is responsible for receiving messages from a source and dispatching
them to the application.

Let's say you already proceed some "orders" on your application using a
``NewOrder`` message. Now you want to integrate with a 3rd party or a legacy
application but you can't use an API and need to use a shared CSV file with new
orders.

You will read this CSV file and dispatch a ``NewOrder`` message. All you need to
do is your custom CSV consumer and Symfony will do the rest.

First, create your receiver::

    namespace App\MessageReceiver;

    use App\Message\NewOrder;
    use Symfony\Component\Message\ReceiverInterface;
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

       public function receive() : \Generator
       {
           $ordersFromCsv = $this->serializer->deserialize(file_get_contents($this->filePath), 'csv');

           foreach ($ordersFromCsv as $orderFromCsv) {
               yield new NewOrder($orderFromCsv['id'], $orderFromCsv['account_id'], $orderFromCsv['amount']);
           }
       }
    }

Same bus received and sender
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To allow us to receive and send messages on the same bus and prevent a loop, the
message bus is equipped with the ``WrapIntoReceivedMessage`` received. It will
wrap the received messages into ``ReceivedMessage`` objects and the
``SendMessageMiddleware`` middleware will know it should not send these messages.

.. _`PHP Enqueue bridge`: https://github.com/sroze/enqueue-bridge
