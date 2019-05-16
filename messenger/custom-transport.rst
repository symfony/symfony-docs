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
    use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
    use Symfony\Component\Messenger\Transport\TransportInterface;

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

The transport object needs to implement the ``TransportInterface`` (which simply combines
the ``SenderInterface`` and ``ReceiverInterface``). It will look like this::

    class YourTransport implements TransportInterface
    {
        public function send(Envelope $envelope): Envelope
        {
            // ...
        }

        public function receive(callable $handler): void
        {
            // ...
        }

        public function stop(): void
        {
            // ...
        }
    }

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
        $container->loadFromExtension('framework', [
            'messenger' => [
                'transports' => [
                    'yours' => 'my-transport://...',
                ],
            ],
        ]);

In addition of being able to route your messages to the ``yours`` sender, this
will give you access to the following services:

#. ``messenger.sender.yours``: the sender;
#. ``messenger.receiver.yours``: the receiver.
