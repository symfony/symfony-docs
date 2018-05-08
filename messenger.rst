.. index::
   single: Messenger

How to Use the Messenger
========================

Symfony's Messenger provide a message bus and some routing capabilities to send
messages within your application and through transports such as message queues.
Before using it, read the :doc:`Messenger component docs </components/messenger>`
to get familiar with its concepts.

Installation
------------

In applications using :doc:`Symfony Flex </setup/flex>`, run this command to
install messenger before using it:

.. code-block:: terminal

    $ composer require messenger

Using the Messenger Service
---------------------------

Once enabled, the ``message_bus`` service can be injected in any service where
you need it, like in a controller::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use App\Message\SendNotification;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\Messenger\MessageBusInterface;

    class DefaultController extends Controller
    {
        public function index(MessageBusInterface $bus)
        {
            $bus->dispatch(new SendNotification('A string to be sent...'));
        }
    }

Registering Handlers
--------------------

In order to do something when your message is dispatched, you need to create a
message handler. It's a class with an `__invoke` method::

    // src/MessageHandler/MyMessageHandler.php
    namespace App\MessageHandler;

    class MyMessageHandler
    {
        public function __invoke(MyMessage $message)
        {
            // do something with it.
        }
    }

Once you've created your handler, you need to register it:

.. code-block:: xml

    <service id="App\MessageHandler\MyMessageHandler">
       <tag name="messenger.message_handler" />
    </service>

.. note::

    If the message cannot be guessed from the handler's type-hint, use the
    ``handles`` attribute on the tag.

Transports
----------

The communication with queuing system or third parties is delegated to
libraries for now. The built-in AMQP transport allows you to communicate with
most of the AMQP brokers such as RabbitMQ.

.. note::

    If you need more message brokers, you should have a look to `Enqueue's transport`_
    which supports things like Kafka, Amazon SQS or Google Pub/Sub.

A transport is registered using a "DSN", which is a string that represents the
connection credentials and configuration. By default, when you've installed
the messenger component, the following configuration should have been created:

.. code-block:: yaml

    # config/packages/messenger.yaml
    framework:
        messenger:
            transports:
                amqp: "%env(MESSENGER_DSN)%"

.. code-block:: bash

    # .env
    ###> symfony/messenger ###
    MESSENGER_DSN=amqp://guest:guest@localhost:5672/%2f/messages
    ###< symfony/messenger ###

This is enough to allow you to route your message to the ``amqp``. This will also
configure the following services for you:

1. A ``messenger.sender.amqp`` sender to be used when routing messages.
2. A ``messenger.receiver.amqp`` receiver to be used when consuming messages.

.. note::

    In order to use Symfony's built-in AMQP adapter, you will need the Serializer
    Component. Ensure that it is installed with:

    .. code-block:: terminal

        $ composer require symfony/serializer-pack

Routing
-------

Instead of calling a handler, you have the option to route your message(s) to a
sender. Part of a transport, it is responsible for sending your message somewhere.
You can configure which message is routed to which sender with the following
configuration:

.. code-block:: yaml

    framework:
        messenger:
            routing:
                'My\Message\Message':  amqp # The name of the defined transport

Such configuration would only route the ``My\Message\Message`` message to be
asynchronous, the rest of the messages would still be directly handled.

You can route all classes of message to a sender using an asterisk instead of a class name:

.. code-block:: yaml

    framework:
        messenger:
            routing:
                'My\Message\MessageAboutDoingOperationalWork': another_transport
                '*': amqp

A class of message can also be routed to multiple senders by specifying a list:

.. code-block:: yaml

    framework:
        messenger:
            routing:
                'My\Message\ToBeSentToTwoSenders': [amqp, audit]

By specifying a ``null`` sender, you can also route a class of messages to a sender
while still having them passed to their respective handler:

.. code-block:: yaml

    framework:
        messenger:
            routing:
                'My\Message\ThatIsGoingToBeSentAndHandledLocally': [amqp, ~]

Consuming messages
------------------

Once your messages have been routed, you will like to consume your messages in most
of the cases. To do so, you can use the ``messenger:consume-messages`` command
like this:

.. code-block:: terminal

    $ bin/console messenger:consume-messages amqp

The first argument is the receiver's service name. It might have been created by
your ``transports`` configuration or it can be your own receiver.

Multiple buses
--------------

If you are interested into architectures like CQRS, you might want to have multiple
buses within your application.

You can create multiple buses (in this example, a command and an event bus) like
this:

.. code-block:: yaml

    framework:
        messenger:
            # The bus that is going to be injected when injecting MessageBusInterface:
            default_bus: commands

            # Create buses
            buses:
                messenger.bus.commands: ~
                messenger.bus.events: ~

This will generate the ``messenger.bus.commands`` and ``messenger.bus.events`` services
that you can inject in your services.

Type-hints and auto-wiring
~~~~~~~~~~~~~~~~~~~~~~~~~~

Auto-wiring is a great feature that allows you to reduce the amount of configuration
required for your service container to be created. When using multiple buses, by default,
the auto-wiring will not work as it won't know why bus to inject in your own services.

In order to clarify this, you will have to create your own decorators for your message
buses. Let's create one for your ``CommandBus``::

    namespace App;

    use Symfony\Component\Messenger\AbstractMessageBusDecorator;

    final class CommandBus extends AbstractMessageBusDecorator
    {
    }

Last step is to register your service (and explicit its argument) to be able to typehint
your ``CommandBus`` in your services:

.. code-block:: yaml

    # config/services.yaml
    services:
        App\CommandBus: ['@messenger.bus.commands']

Middleware
----------

What happens when you dispatch a message to a message bus(es) depends on its
collection of middleware (and their order). By default, the middleware configured
for each bus looks like this:

1. ``logging`` middleware. Responsible of logging the beginning and the end of the
   message within the bus.

2. _Your own collection of middleware__

3. ``route_messages`` middleware. Will route the messages your configured to their
   corresponding sender and stop the middleware chain.

4. ``call_message_handler`` middleware. Will call the message handler(s) for the
   given message.

Adding your own middleware
~~~~~~~~~~~~~~~~~~~~~~~~~~

As described in the component documentation, you can add your own middleware
within the buses to add some extra capabilities like this:

.. code-block:: yaml

    framework:
        messenger:
            buses:
                messenger.bus.default:
                    middleware:
                        # Works with the FQCN if the class discovery is enabled
                        - App\\Middleware\\MyMiddleware

                        # Or with some service name
                        - app.middleware.yours

Note that if the service is abstract, then a child service will be created per bus.

Disabling default middleware
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you don't want the default collection of middleware to be present on your bus,
you can disable them like this:

.. code-block:: yaml

    framework:
        messenger:
            buses:
                messenger.bus.default:
                    default_middleware: false

Your own Transport
------------------

Once you have written your transport's sender and receiver, you can register your
transport factory to be able to use it via a DSN in the Symfony application.

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

Register your factory
~~~~~~~~~~~~~~~~~~~~~

.. code-block:: xml

    <service id="Your\Transport\YourTransportFactory">
       <tag name="messenger.transport_factory" />
    </service>

Use your transport
~~~~~~~~~~~~~~~~~~

Within the ``framework.messenger.transports.*`` configuration, create your
named transport using your own DSN:

.. code-block:: yaml

    framework:
        messenger:
            transports:
                yours: 'my-transport://...'

In addition of being able to route your messages to the ``yours`` sender, this
will give you access to the following services:

#. ``messenger.sender.yours``: the sender.
#. ``messenger.receiver.yours``: the receiver.

.. _`enqueue's transport`: https://github.com/sroze/enqueue-bridge
