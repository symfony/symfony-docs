.. index::
   single: Messenger

How to Use the Messenger
========================

Symfony's Messenger provide a message bus and some routing capabilities to send
messages within your application and through adapaters such as message queues.
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

Adapters
--------

The communication with queuing system or third parties is delegated to
libraries for now. The built-in AMQP adapter allows you to communicate with
most of the AMQP brokers such as RabbitMQ.

.. note::

    If you need more message brokers, you should have a look to `Enqueue's adapter`_
    which supports things like Kafka, Amazon SQS or Google Pub/Sub.

An adapter is registered using a "DSN", which is a string that represents the
connection credentials and configuration. By default, when you've installed
the messenger component, the following configuration should have been created:

.. code-block:: yaml

    # config/packages/messenger.yaml
    framework:
        messenger:
            adapters:
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

Routing
-------

Instead of calling a handler, you have the option to route your message(s) to a
sender. Part of an adapter, it is responsible for sending your message somewhere.
You can configure which message is routed to which sender with the following
configuration:

.. code-block:: yaml

    framework:
        messenger:
            routing:
                'My\Message\Message':  amqp # The name of the defined adapter

Such configuration would only route the ``My\Message\Message`` message to be
asynchronous, the rest of the messages would still be directly handled.

You can route all classes of message to a sender using an asterisk instead of a class name:

.. code-block:: yaml

    framework:
        messenger:
            routing:
                'My\Message\MessageAboutDoingOperationalWork': another_adapter
                '*': amqp

A class of message can also be routed to a multiple senders by specifying a list:

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
of the cases. Do to so, you can use the ``messenger:consume-messages`` command
like this:

.. code-block:: terminal

    $ bin/console messenger:consume-messages amqp

The first argument is the receiver's service name. It might have been created by
your ``adapters`` configuration or it can be your own receiver.

Your own Adapters
-----------------

Once you have written your adapter's sender and receiver, you can register your
adapter factory to be able to use it via a DSN in the Symfony application.

Create your adapter Factory
~~~~~~~~~~~~~~~~~~~~~~~~~~~

You need to give FrameworkBundle the opportunity to create your adapter from a
DSN. You will need an adapter factory::

    use Symfony\Component\Messenger\Adapter\Factory\AdapterFactoryInterface;
    use Symfony\Component\Messenger\Transport\ReceiverInterface;
    use Symfony\Component\Messenger\Transport\SenderInterface;

    class YourAdapterFactory implements AdapterFactoryInterface
    {
        public function createReceiver(string $dsn, array $options): ReceiverInterface
        {
            return new YourReceiver(/* ... */);
        }

        public function createSender(string $dsn, array $options): SenderInterface
        {
            return new YourSender(/* ... */);
        }

        public function supports(string $dsn, array $options): bool
        {
            return 0 === strpos($dsn, 'my-adapter://');
        }
    }

Register your factory
~~~~~~~~~~~~~~~~~~~~~

.. code-block:: xml

    <service id="Your\Adapter\YourAdapterFactory">
       <tag name="messenger.adapter_factory" />
    </service>

Use your adapter
~~~~~~~~~~~~~~~~

Within the ``framework.messenger.adapters.*`` configuration, create your
named adapter using your own DSN:

.. code-block:: yaml

    framework:
        messenger:
            adapters:
                yours: 'my-adapter://...'

In addition of being able to route your messages to the ``yours`` sender, this
will give you access to the following services:

1. ``messenger.sender.hours``: the sender.
2. ``messenger.receiver.hours``: the receiver.

.. _`enqueue's adapter`: https://github.com/sroze/enqueue-bridge
