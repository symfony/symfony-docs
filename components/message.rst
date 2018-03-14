.. index::
   single: Message
   single: Components; Message

The Message Component
=====================

    The Message component helps application to send and receive messages
    to/from other applications or via

Concepts
--------

.. image:: /_images/components/message/overview.png

1. **Sender**
   Responsible for serializing and sending the message to _something_. This something can be a message broker or a 3rd
   party API for example.

2. **Receiver**
   Responsible for deserializing and forwarding the messages to handler(s). This can be a message queue puller or an API
   endpoint for example.

3. **Handler**
   Given a received message, contains the user business logic related to the message. In practice, that is just a PHP
   callable.

Bus
---

The bus is used to dispatch messages. MessageBus' behaviour is in its ordered middleware stack. When using
the message bus with Symfony's FrameworkBundle, the following middlewares are configured for you:

1. `LoggingMiddleware` (log the processing of your messages)
2. `SendMessageMiddleware` (enable asynchronous processing)
3. `HandleMessageMiddleware` (call the registered handle)

    use App\Message\MyMessage;

    $result = $this->get('message_bus')->handle(new MyMessage(/* ... */));

Handlers
--------

Once dispatched to the bus, messages will be handled by a "message handler". A message handler is a PHP callable
(i.e. a function or an instance of a class) that will do the required processing for your message. It _might_ return a
result.

    namespace App\MessageHandler;

    use App\Message\MyMessage;

    class MyMessageHandler
    {
       public function __invoke(MyMessage $message)
       {
           // Message processing...
       }
    }


    <service id="App\Handler\MyMessageHandler">
       <tag name="message_handler" />
    </service>

**Note:** If the message cannot be guessed from the handler's type-hint, use the `handles` attribute on the tag.

### Asynchronous messages

Using the Message Component is useful to decouple your application but it also very useful when you want to do some
asychronous processing. This means that your application will produce a message to a queuing system and consume this
message later in the background, using a _worker_.

#### Adapters

The communication with queuing system or 3rd parties is for delegated to libraries for now. You can use one of the
following adapters:

- [PHP Enqueue bridge](https://github.com/sroze/enqueue-bridge) to use one of their 10+ compatible queues such as
 RabbitMq, Amazon SQS or Google Pub/Sub.

Routing
-------

When doing asynchronous processing, the key is to route the message to the right sender. As the routing is
application-specific and not message-specific, the configuration can be made within the `framework.yaml`
configuration file as well:

    framework:
       message:
           routing:
               'My\Message\MessageAboutDoingOperationalWork': my_operations_queue_sender

Such configuration would only route the `MessageAboutDoingOperationalWork` message to be asynchronous, the rest of the
messages would still be directly handled.

If you want to do route all the messages to a queue by default, you can use such configuration:

    framework:
       message:
           routing:
               'My\Message\MessageAboutDoingOperationalWork': my_operations_queue_sender
               '*': my_default_sender

Note that you can also route a message to multiple senders at the same time:

    framework:
       message:
           routing:
               'My\Message\AnImportantMessage': [my_default_sender, my_audit_sender]

Same bus received and sender
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To allow us to receive and send messages on the same bus and prevent a loop, the message bus is equipped with the
`WrapIntoReceivedMessage` received. It will wrap the received messages into `ReceivedMessage` objects and the
`SendMessageMiddleware` middleware will know it should not send these messages.

Your own sender
---------------

Using the `SenderInterface`, you can easily create your own message sender. Let's say you already have an
`ImportantAction` message going through the message bus and handled by a handler. Now, you also want to send this
message as an email.

1. Create your sender

    namespace App\MessageSender;

    use Symfony\Component\Message\SenderInterface;
    use App\Message\ImportantAction;

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

2. Register your sender service

    services:
         App\MessageSender\ImportantActionToEmailSender:
             arguments:
                 - "@mailer"
                 - "%to_email%"

             tags:
                 - message.sender

3. Route your important message to the sender

    framework:
       message:
           routing:
               'App\Message\ImportantAction': [App\MessageSender\ImportantActionToEmailSender, ~]

**Note:** this example shows you how you can at the same time send your message and directly handle it using a `null`
(`~`) sender.

Your own receiver
-----------------

A consumer is responsible of receiving messages from a source and dispatching them to the application.

Let's say you already proceed some "orders" on your application using a `NewOrder` message. Now you want to integrate with
a 3rd party or a legacy application but you can't use an API and need to use a shared CSV file with new orders.

You will read this CSV file and dispatch a `NewOrder` message. All you need to do is your custom CSV consumer and Symfony will do the rest.

1. Create your receiver

    namespace App\MessageReceiver;

    use Symfony\Component\Message\ReceiverInterface;
    use Symfony\Component\Serializer\SerializerInterface;

    use App\Message\NewOrder;

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

2. Register your receiver service

    services:
       App\MessageReceiver\NewOrdersFromCsvFile:
           arguments:
               - "@serializer"
               - "%new_orders_csv_file_path%"

           tags:
               - message.receiver

3. Use your consumer

    $ bin/console message:consume App\MessageReceived\NewOrdersFromCsvFile
