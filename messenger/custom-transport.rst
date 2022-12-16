How to Create Your own Messenger Transport
==========================================

Once you have written your transport's sender and receiver, you can register your
transport factory to be able to use it via a DSN in the Symfony application.

Create your Transport Factory
-----------------------------

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
        private $db;
        private $serializer;

        /**
         * @param FakeDatabase $db is used for demo purposes. It is not a real class.
         */
        public function __construct(FakeDatabase $db, SerializerInterface $serializer = null)
        {
            $this->db = $db;
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

        return static function (FrameworkConfig $framework) {
            $framework->messenger()
                ->transport('yours')
                    ->dsn('my-transport://...')
            ;
        };

In addition of being able to route your messages to the ``yours`` sender, this
will give you access to the following services:

#. ``messenger.sender.yours``: the sender;
#. ``messenger.receiver.yours``: the receiver.
