How to Create Your own Messenger Transport
==========================================

If none of the :ref:`built-in Messenger transports <messenger-transports-config>`
fits your needs, you can create your own. A transport is responsible for
communicating with your message broker or third parties: it combines a *sender*
(to send messages) and a *receiver* (to retrieve them). This article explains
how to implement each of them and how to register your own transport.

Your own Sender
---------------

Imagine that you already have an ``ImportantAction`` message going through the
message bus and being handled by a handler. Now, you also want to send this
message as an email (using the :doc:`Mailer </mailer>` component).

Using the :class:`Symfony\\Component\\Messenger\\Transport\\Sender\\SenderInterface`,
you can create your own message sender::

    namespace App\MessageSender;

    use App\Message\ImportantAction;
    use Symfony\Component\Mailer\MailerInterface;
    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
    use Symfony\Component\Mime\Email;

    class ImportantActionToEmailSender implements SenderInterface
    {
        public function __construct(
            private MailerInterface $mailer,
            private string $toEmail,
        ) {
        }

        public function send(Envelope $envelope): Envelope
        {
            $message = $envelope->getMessage();

            if (!$message instanceof ImportantAction) {
                throw new \InvalidArgumentException(sprintf('This transport only supports "%s" messages.', ImportantAction::class));
            }

            $this->mailer->send(
                (new Email())
                    ->to($this->toEmail)
                    ->subject('Important action made')
                    ->html('<h1>Important action</h1><p>Made by '.$message->getUsername().'</p>')
            );

            return $envelope;
        }
    }

Your own Receiver
-----------------

A receiver is responsible for getting messages from a source and dispatching
them to the application.

Imagine you already processed some "orders" in your application using a
``NewOrder`` message. Now you want to integrate with a 3rd party or a legacy
application but you can't use an API and need to use a shared CSV file with new
orders.

You will read this CSV file and dispatch a ``NewOrder`` message. All you need to
do is to write your own CSV receiver::

    namespace App\MessageReceiver;

    use App\Message\NewOrder;
    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
    use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
    use Symfony\Component\Serializer\SerializerInterface;

    class NewOrdersFromCsvFileReceiver implements ReceiverInterface
    {
        private $connection;

        public function __construct(
            private SerializerInterface $serializer,
            private string $filePath,
        ) {
            // Available connection bundled with the Messenger component
            // can be found in "Symfony\Component\Messenger\Bridge\*\Transport\Connection".
            $this->connection = /* create your connection */;
        }

        public function get(): iterable
        {
            // Receive the envelope according to your transport ($yourEnvelope here),
            // in most cases, using a connection is the easiest solution.
            $yourEnvelope = $this->connection->get();
            if (null === $yourEnvelope) {
                return [];
            }

            try {
                $envelope = $this->serializer->decode([
                    'body' => $yourEnvelope['body'],
                    'headers' => $yourEnvelope['headers'],
                ]);
            } catch (MessageDecodingFailedException $exception) {
                $this->connection->reject($yourEnvelope['id']);
                throw $exception;
            }

            return [$envelope->with(new CustomStamp($yourEnvelope['id']))];
        }

        public function ack(Envelope $envelope): void
        {
            // Add information about the handled message
        }

        public function reject(Envelope $envelope): void
        {
            // In the case of a custom connection
            $id = /* get the message id thanks to information or stamps present in the envelope */;

            $this->connection->reject($id);
        }
    }

Receiver and Sender on the same Bus
-----------------------------------

To allow sending and receiving messages on the same bus and prevent an infinite
loop, the message bus will add a :class:`Symfony\\Component\\Messenger\\Stamp\\ReceivedStamp`
stamp to the message envelopes and the :class:`Symfony\\Component\\Messenger\\Middleware\\SendMessageMiddleware`
middleware will know it should not route these messages again to a transport.

Create your Transport Factory
-----------------------------

Once you have written your transport's sender and receiver, you can register
your transport factory to be able to use it via a DSN in the Symfony
application.

You need to give FrameworkBundle the opportunity to create your transport from a
DSN. You will need a transport factory::

    use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
    use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
    use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
    use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
    use Symfony\Component\Messenger\Transport\TransportInterface;

    class YourTransportFactory implements TransportFactoryInterface
    {
        public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
        {
            return new YourTransport(/* ... */);
        }

        public function supports(string $dsn, array $options): bool
        {
            return 0 === strpos($dsn, 'my-transport://');
        }
    }

The transport object needs to implement the
:class:`Symfony\\Component\\Messenger\\Transport\\TransportInterface`
(which combines the :class:`Symfony\\Component\\Messenger\\Transport\\Sender\\SenderInterface`
and :class:`Symfony\\Component\\Messenger\\Transport\\Receiver\\ReceiverInterface`).
Here is a simplified example of a database transport::

    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
    use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
    use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
    use Symfony\Component\Messenger\Transport\TransportInterface;
    use Symfony\Component\Uid\Uuid;

    class YourTransport implements TransportInterface
    {
        private SerializerInterface $serializer;

        /**
         * @param FakeDatabase $db is used for demo purposes. It is not a real class.
         */
        public function __construct(
            private FakeDatabase $db,
            ?SerializerInterface $serializer = null,
        ) {
            $this->serializer = $serializer ?? new PhpSerializer();
        }

        public function get(): iterable
        {
            // Get a message from "my_queue"
            $row = $this->db->createQuery(
                    'SELECT *
                    FROM my_queue
                    WHERE (delivered_at IS NULL OR delivered_at < :redeliver_timeout)
                    AND handled = FALSE'
                )
                ->setParameter('redeliver_timeout', new DateTimeImmutable('-5 minutes'))
                ->getOneOrNullResult();

            if (null === $row) {
                return [];
            }

            $envelope = $this->serializer->decode([
                'body' => $row['envelope'],
            ]);

            return [$envelope->with(new TransportMessageIdStamp($row['id']))];
        }

        public function ack(Envelope $envelope): void
        {
            $stamp = $envelope->last(TransportMessageIdStamp::class);
            if (!$stamp instanceof TransportMessageIdStamp) {
                throw new \LogicException('No TransportMessageIdStamp found on the Envelope.');
            }

            // Mark the message as "handled"
            $this->db->createQuery('UPDATE my_queue SET handled = TRUE WHERE id = :id')
                ->setParameter('id', $stamp->getId())
                ->execute();
        }

        public function reject(Envelope $envelope): void
        {
            $stamp = $envelope->last(TransportMessageIdStamp::class);
            if (!$stamp instanceof TransportMessageIdStamp) {
                throw new \LogicException('No TransportMessageIdStamp found on the Envelope.');
            }

            // Delete the message from the "my_queue" table
            $this->db->createQuery('DELETE FROM my_queue WHERE id = :id')
                ->setParameter('id', $stamp->getId())
                ->execute();
        }

        public function send(Envelope $envelope): Envelope
        {
            $encodedMessage = $this->serializer->encode($envelope);
            $uuid = (string) Uuid::v4();
            // Add a message to the "my_queue" table
            $this->db->createQuery(
                    'INSERT INTO my_queue (id, envelope, delivered_at, handled)
                    VALUES (:id, :envelope, NULL, FALSE)'
                )
                ->setParameters([
                    'id' => $uuid,
                    'envelope' => $encodedMessage['body'],
                ])
                ->execute();

            return $envelope->with(new TransportMessageIdStamp($uuid));
        }
    }

The implementation above is not runnable code but illustrates how a
:class:`Symfony\\Component\\Messenger\\Transport\\TransportInterface` could
be implemented. For real implementations see :class:`Symfony\\Component\\Messenger\\Transport\\InMemory\\InMemoryTransport`
and :class:`Symfony\\Component\\Messenger\\Bridge\\Doctrine\\Transport\\DoctrineReceiver`.

Register your Factory
---------------------

Before using your factory, you must register it. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
this is already done for you, thanks to :ref:`autoconfiguration <services-autoconfigure>`.
Otherwise, add the following:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            Your\Transport\YourTransportFactory:
                tags: [messenger.transport_factory]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Your\Transport\YourTransportFactory">
                   <tag name="messenger.transport_factory"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Your\Transport\YourTransportFactory;

        $container->register(YourTransportFactory::class)
            ->setTags(['messenger.transport_factory']);

Use your Transport
------------------

Within the ``framework.messenger.transports.*`` configuration, create your
named transport using your own DSN:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    yours: 'my-transport://...'

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <framework:transport name="yours" dsn="my-transport://..."/>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->messenger()
                ->transport('yours')
                    ->dsn('my-transport://...')
            ;
        };

In addition to being able to route your messages to the ``yours`` sender, this
will give you access to the following services:

#. ``messenger.sender.yours``: the sender;
#. ``messenger.receiver.yours``: the receiver.
