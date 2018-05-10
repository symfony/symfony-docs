.. index::
    single: Messenger; Transports

Transports
==========

To send and receive messages via message brokers, the Messenger component has
some transports, to which you can route your message.

Every transport is configurable using a DSN. This DSN allows you to chose the
transport layer as well as configuring it.

AMQP
----

Very likely the most famous message broker protocol, AMQP, especially with RabbitMQ
has a built-in support within the Messenger component.

How it works?
~~~~~~~~~~~~~

The DSN protocol to use is ``amqp``. The following DSN example shows you how to
use the adapter to connect to a local RabbitMQ with the ``user`` username and
``password`` password. The messages will be sent to the ``messages`` exchange
bound to the ``messages`` queue on the ``/`` vhost::

    amqp://user:password@localhost/%2f/messages

.. note:

    By default, RabbitMQ uses ``guest`` as a username and ``guest`` as a password
    and has a ``/`` vhost.

Error handling
~~~~~~~~~~~~~~

If something wrong happens (i.e. an exception is thrown) while handling your message,
the default behaviour is that your message will be "NACK" and requeued.

However, if your exception implements the ``RejectMessageExceptionInterface`` interface,
the message will be rejected from the queue.

Retry
~~~~~

When receiving messages from a broker, it might happen that some exceptions will
arise. Typically, a 3rd party provider is down or your system is under heavy load
and can't really process some messages. To handle this scenario, there is a built-in
retry mechanism that can be enabled via your DSN::

    amqp://guest:guest@localhost/%2f/messages
      ?retry[attempts]=3
      &retry[ttl][0]=10000
      &retry[ttl][1]=30000
      &retry[ttl][2]=60000

In the example above, if handling the message fails, it will retry it 3 times. After
the first failure, it will wait 10 seconds before trying it. After the 2nd failure,
it will wait 30 seconds. After the 3rd failure, it will wait a minute. If it still
fails, the message will be moved to a "dead queue" and you will have to manually
handle this message.

DSN configuration reference
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The options available to in the DSN are documented on the ``Connection`` class
in the code repository.

Enqueue
-------

Probably one of the most famous PHP queue-broker libraries, Enqueue has 10+ adapters
with brokers like Kafka, Google Pub/Sub, AWS SQS and more. Check out the transport
documentation in `Enqueue's official repository`_.

Your own transport
------------------

If there is no available transport for your message broker, you can easily
create your own.

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

Create your Transport Factory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You need to give FrameworkBundle the opportunity to create your transport from a
DSN. You will need an transport factory::

    use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
    use Symfony\Component\Messenger\Transport\TransportInterface;
    use Symfony\Component\Messenger\Transport\ReceiverInterface;
    use Symfony\Component\Messenger\Transport\SenderInterface;

    class YourTransportFactory implements TransportFactoryInterface
    {
        public function createTransport(string $dsn, array $options): TransportInterface
        {
            return new YourTransport(/* ... */);
        }

        public function supports(string $dsn, array $options): bool
        {
            return 0 === strpos($dsn, 'my-transport://');
        }
    }

The transport object is needs to implements the ``TransportInterface`` (which simply combine
the ``SenderInterface`` and ``ReceiverInterface``). It will look
like this::

    class YourTransport implements TransportInterface
    {
        public function send($message) : void
        {
            // ...
        }

        public function receive(callable $handler) : void
        {
            // ...
        }

        public function stop() : void
        {
            // ...
        }
    }

If you plan to use it within a Symfony application, you should look at
:doc:`registering your transport factory </components/messenger>` with the FrameworkBundle.

.. _`Enqueue's official repository`: https://github.com/enqueue/messenger-adapter
