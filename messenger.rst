.. index::
   single: Messenger

How to Use the Messenger
========================

Symfony's Messenger provides a message bus and some routing capabilities to send
messages within your application and through transports such as message queues.
Before using it, read the :doc:`Messenger component docs </components/messenger>`
to get familiar with its concepts.

Installation
------------

In applications using :doc:`Symfony Flex </setup/flex>`, run this command to
install messenger before using it:

.. code-block:: terminal

    $ composer require messenger

Message
-------

Before you can send a message, you must create it first. There is no specific
requirement for a message, except it should be serializable and unserializable
by a Symfony Serializer instance::

    // src/Message/SmsNotification.php
    namespace App\Message;

    class SmsNotification
    {
        private $content;

        public function __construct(string $content)
        {
            $this->content = $content;
        }

        // ...getters
    }

Using the Messenger Service
---------------------------

Once enabled, the ``message_bus`` service can be injected in any service where
you need it, like in a controller::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use App\Message\SmsNotification;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Messenger\MessageBusInterface;

    class DefaultController extends AbstractController
    {
        public function index(MessageBusInterface $bus)
        {
            $bus->dispatch(new SmsNotification('A string to be sent...'));
        }
    }

Registering Handlers
--------------------

In order to do something when your message is dispatched, you need to create a
message handler. It's a class with an ``__invoke`` method::

    // src/MessageHandler/SmsNotificationHandler.php
    namespace App\MessageHandler;

    use App\Message\SmsNotification;
    use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

    class SmsNotificationHandler implements MessageHandlerInterface
    {
        public function __invoke(SmsNotification $message)
        {
            // do something with it.
        }
    }

Message handlers must be registered as services and :doc:`tagged </service_container/tags>`
with the ``messenger.message_handler`` tag. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>` and implement
:class:`Symfony\\Component\\Messenger\\Handler\\MessageHandlerInterface`
or :class:`Symfony\\Component\\Messenger\\Handler\\MessageSubscriberInterface`,
this is already done for you, thanks to :ref:`autoconfiguration <services-autoconfigure>`.

If you're not using service autoconfiguration, then you need to add this config:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\MessageHandler\SmsNotificationHandler:
                tags: [messenger.message_handler]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\MessageHandler\SmsNotificationHandler">
                   <tag name="messenger.message_handler"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\MessageHandler\SmsNotificationHandler;

        $container->register(SmsNotificationHandler::class)
            ->addTag('messenger.message_handler');

.. note::

    If the message cannot be guessed from the handler's type-hint, use the
    ``handles`` attribute on the tag.

Transports
----------

By default, messages are processed as soon as they are dispatched. If you prefer
to process messages asynchronously, you must configure a transport. These
transports communicate with your application via queuing systems or third parties.
The built-in AMQP transport allows you to communicate with most of the AMQP
brokers such as RabbitMQ.

.. note::

    If you need more message brokers, you should have a look at `Enqueue's transport`_
    which supports things like Kafka, Amazon SQS or Google Pub/Sub.

A transport is registered using a "DSN", which is a string that represents the
connection credentials and configuration. By default, when you've installed
the messenger component, the following configuration should have been created:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    amqp: "%env(MESSENGER_TRANSPORT_DSN)%"

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
                    <framework:transport name="amqp" dsn="%env(MESSENGER_TRANSPORT_DSN)%"/>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'transports' => [
                    'amqp' => '%env(MESSENGER_TRANSPORT_DSN)%',
                ],
            ],
        ]);

.. code-block:: bash

    # .env
    ###> symfony/messenger ###
    MESSENGER_TRANSPORT_DSN=amqp://guest:guest@localhost:5672/%2f/messages
    ###< symfony/messenger ###

This is enough to allow you to route your message to the ``amqp`` transport.
This will also configure the following services for you:

#. A ``messenger.sender.amqp`` sender to be used when routing messages;
#. A ``messenger.receiver.amqp`` receiver to be used when consuming messages.

.. note::

    In order to use Symfony's built-in AMQP transport, you will need the AMQP
    PHP extension and the Serializer Component. Ensure that they are installed with:

    .. code-block:: terminal

        $ composer require symfony/amqp-pack

Routing
-------

Instead of calling a handler, you have the option to route your message(s) to a
sender. Part of a transport, it is responsible for sending your message somewhere.
You can configure which message is routed to which sender with the following
configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                routing:
                    'My\Message\Message':  amqp # The name of the defined transport

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
                    <framework:routing message-class="My\Message\Message">
                        <framework:sender service="amqp"/>
                    </framework:routing>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'routing' => [
                    'My\Message\Message' => 'amqp',
                ],
            ],
        ]);

Such configuration would only route the ``My\Message\Message`` message to be
asynchronous, the rest of the messages would still be directly handled.

You can route all classes of messages to the same sender using an asterisk
instead of a class name:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                routing:
                    'My\Message\MessageAboutDoingOperationalWork': another_transport
                    '*': amqp

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
                    <framework:routing message-class="My\Message\Message">
                        <framework:sender service="another_transport"/>
                    </framework:routing>
                    <framework:routing message-class="*">
                        <framework:sender service="amqp"/>
                    </framework:routing>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'routing' => [
                    'My\Message\Message' => 'another_transport',
                    '*' => 'amqp',
                ],
            ],
        ]);

A class of messages can also be routed to multiple senders by specifying a list:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                routing:
                    'My\Message\ToBeSentToTwoSenders': [amqp, audit]

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
                    <framework:routing message-class="My\Message\ToBeSentToTwoSenders">
                        <framework:sender service="amqp"/>
                        <framework:sender service="audit"/>
                    </framework:routing>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'routing' => [
                    'My\Message\ToBeSentToTwoSenders' => ['amqp', 'audit'],
                ],
            ],
        ]);

By specifying the ``send_and_handle`` option, you can also route a class of messages to a sender
while still having them passed to their respective handler:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                routing:
                    'My\Message\ThatIsGoingToBeSentAndHandledLocally':
                         senders: [amqp]
                         send_and_handle: true

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
                    <framework:routing message-class="My\Message\ThatIsGoingToBeSentAndHandledLocally" send-and-handle="true">
                        <framework:sender service="amqp"/>
                    </framework:routing>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'routing' => [
                    'My\Message\ThatIsGoingToBeSentAndHandledLocally' => [
                        'senders' => ['amqp'],
                        'send_and_handle' => true,
                    ],
                ],
            ],
        ]);

Consuming Messages
------------------

Once your messages have been routed, you will like to consume your messages in most
of the cases. To do so, you can use the ``messenger:consume-messages`` command
like this:

.. code-block:: terminal

    $ php bin/console messenger:consume-messages amqp

The first argument is the receiver's service name. It might have been created by
your ``transports`` configuration or it can be your own receiver.
It also requires a ``--bus`` option in case you have multiple buses configured,
which is the name of the bus to which received messages should be dispatched.

Middleware
----------

What happens when you dispatch a message to a message bus(es) depends on its
collection of middleware (and their order). By default, the middleware configured
for each bus looks like this:

#. ``logging`` middleware. Responsible for logging the beginning and the end of the
   message within the bus;

#. _Your own collection of middleware_;

#. ``send_message`` middleware. Will route the messages you configured to their
   corresponding sender and stop the middleware chain;

#. ``handle_message`` middleware. Will call the message handler(s) for the
   given message.

.. note::

    These middleware names are actually shortcuts working by convention.
    The real service ids are prefixed with the ``messenger.middleware.`` namespace.

Disabling default Middleware
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you don't want the default collection of middleware to be present on your bus,
you can disable them like this:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                buses:
                    messenger.bus.default:
                        default_middleware: false

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
                    <framework:bus name="messenger.bus.default" default-middleware="false"/>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'buses' => [
                    'messenger.bus.default' => [
                        'default_middleware' => false,
                    ],
                ],
            ],
        ]);

Adding your own Middleware
~~~~~~~~~~~~~~~~~~~~~~~~~~

As described in the component documentation, you can add your own middleware
within the buses to add some extra capabilities like this:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                buses:
                    messenger.bus.default:
                        middleware:
                            - 'App\Middleware\MyMiddleware'
                            - 'App\Middleware\AnotherMiddleware'

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
                    <framework:bus name="messenger.bus.default">
                        <framework:middleware id="App\Middleware\MyMiddleware"/>
                        <framework:middleware id="App\Middleware\AnotherMiddleware"/>
                    </framework:bus>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'buses' => [
                    'messenger.bus.default' => [
                        'middleware' => [
                            'App\Middleware\MyMiddleware',
                            'App\Middleware\AnotherMiddleware',
                        ],
                    ],
                ],
            ],
        ]);

Note that if the service is abstract, a different instance of the service will
be created per bus.

Using Middleware Factories
~~~~~~~~~~~~~~~~~~~~~~~~~~

Some third-party bundles and libraries provide configurable middleware via
factories.

For instance, the ``messenger.middleware.doctrine_transaction`` is a
built-in middleware wired automatically when the DoctrineBundle and the Messenger
component are installed and enabled.
This middleware can be configured to define the entity manager to use:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                buses:
                    command_bus:
                        middleware:
                            # Using the default configured entity manager name
                            - doctrine_transaction
                            # Using another entity manager
                            - doctrine_transaction: ['custom']

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
                    <framework:bus name="command_bus">
                        <!-- Using the default configured entity manager name -->
                        <framework:middleware id="doctrine_transaction"/>
                        <!-- Using another entity manager -->
                        <framework:middleware id="doctrine_transaction">
                            <framework:argument>custom</framework:argument>
                        </framework:middleware>
                    </framework:bus>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'buses' => [
                    'command_bus' => [
                        'middleware' => [
                            // Using the default configured entity manager name
                            'doctrine_transaction',
                            // Using another entity manager
                            ['id' => 'doctrine_transaction', 'arguments' => ['custom']],
                        ],
                    ],
                ],
            ],
        ]);

Defining such configurable middleware is based on Symfony's
:doc:`dependency injection </service_container>` features:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            messenger.middleware.doctrine_transaction:
                class: Symfony\Bridge\Doctrine\Messenger\DoctrineTransactionMiddleware
                # Definition is abstract, so a child definition will be created, per bus
                abstract: true
                # Main dependencies are defined by the parent definitions.
                # Arguments provided in the middleware config will be appended on the child definition.
                arguments: ['@doctrine']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="messenger.middleware.doctrine_transaction"
                    class="Symfony\Bridge\Doctrine\Messenger\DoctrineTransactionMiddleware"
                    <!-- Definition is abstract, so a child definition will be created, per bus -->
                    abstract="true">
                    <!-- Main dependencies are defined by the parent definitions. -->
                    <!-- Arguments provided in the middleware config will be appended on the child definition. -->
                    <argument type="service" id="doctrine"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Symfony\Bridge\Doctrine\Messenger\DoctrineTransactionMiddleware;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register('messenger.middleware.doctrine_transaction', DoctrineTransactionMiddleware::class)
            // Definition is abstract, so a child definition will be created, per bus
            ->setAbstract(true)
            // Main dependencies are defined by the parent definitions.
            // Arguments provided in the middleware config will be appended on the child definition.
            ->setArguments([new Reference('doctrine')]);

.. note::

    Middleware factories only allow appending scalar and array arguments in config
    (no references to other services). For most advanced use-cases, register a
    concrete definition of the middleware manually and use its id.

Your own Transport
------------------

Once you have written your transport's sender and receiver, you can register your
transport factory to be able to use it via a DSN in the Symfony application.

Create your Transport Factory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You need to give FrameworkBundle the opportunity to create your transport from a
DSN. You will need a transport factory::

    use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
    use Symfony\Component\Messenger\Transport\TransportInterface;
    use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
    use Symfony\Component\Messenger\Transport\Sender\SenderInterface;

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
~~~~~~~~~~~~~~~~~~~~~

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
~~~~~~~~~~~~~~~~~~

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

Learn more
----------
.. toctree::
    :maxdepth: 1
    :glob:

    /messenger/*

.. _`enqueue's transport`: https://github.com/php-enqueue/messenger-adapter
