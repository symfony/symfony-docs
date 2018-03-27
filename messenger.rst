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

Once enabled, the :code:`message_bus` service can be injected in any service where
you need it, like in a controller::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\Messenger\MessageBusInterface;

    class DefaultController extends Controller
    {
        public function indexAction(MessageBusInterface $bus)
        {
            $bus->dispatch(new MyMessage());
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
                default: "%env(MESSENGER_DSN)%"

.. code-block:: bash

    # .env
    ###> symfony/messenger ###
    AMQP_DSN=amqp://guest:guest@localhost:5672/%2f/messages
    ###< symfony/messenger ###

This is enough to allow you to route your message to the :code:`messenger.default_adapter`
adapter. This will also configure the following for you:

1. A :code:`messenger.default_sender` sender to be used when routing messages
2. A :code:`messenger.default_receiver` receiver to be used when consuming messages.

Routing
-------

Instead of calling a handler, you have the option to route your message(s) to a
sender. Part of an adapter, it is responsible of sending your message somewhere.
You can configuration which message is routed to which sender with the following
configuration:

.. code-block:: yaml

    framework:
        messenger:
            routing:
                'My\Message\Message':  messenger.default_sender # Or another sender service name

Such configuration would only route the ``MessageAboutDoingOperationalWork``
message to be asynchronous, the rest of the messages would still be directly
handled.

If you want to do route all the messages to a queue by default, you can use such
configuration:

.. code-block:: yaml

    framework:
        messenger:
            routing:
                'My\Message\MessageAboutDoingOperationalWork': messenger.operations_sender
                '*': messenger.default_sender

Note that you can also route a message to multiple senders at the same time:

.. code-block:: yaml

    framework:
        messenger:
            routing:
                'My\Message\ToBeSentToTwoSenders': [messenger.default_sender, messenger.audit_sender]

Last but not least you can also route a message while still calling the handler
on your application by having a :code:`null` sender:

.. code-block:: yaml

    framework:
        messenger:
            routing:
                'My\Message\ThatIsGoingToBeSentAndHandledLocally': [messenger.default_sender, ~]

Consuming messages
------------------

Once your messages have been routed, you will like to consume your messages in most
of the cases. Do to so, you can use the :code:`messenger:consume-messages` command
like this:

.. code-block:: terminal

    $ bin/console messenger:consume-messages messenger.default_receiver

The first argument is the receiver's service name. It might have been created by
your :code:`adapters` configuration or it can be your own receiver.

Registering your middlewares
----------------------------

The message bus is based on middlewares. If you are un-familiar with the concept,
look at the :doc:`Messenger component docs </components/messenger>`.

To register your middleware, use the :code:`messenger.middleware` tag as in the
following example:

.. code-block:: xml

    <service id="Your\Own\Middleware">
       <tag name="messenger.middleware" />
    </service>

Your own Adapters
-----------------

Learn how to build your own adapters within the Component's documentation. Once
you have built your classes, you can register your adapter factory to be able to
use it via a DSN in the Symfony application.

Create your adapter Factory
~~~~~~~~~~~~~~~~~~~~~~~~~~~

You need to give FrameworkBundle the opportunity to create your adapter from a
DSN. You will need an adapter factory::

    use Symfony\Component\Messenger\Adapter\Factory\AdapterInterface;
    use Symfony\Component\Messenger\Adapter\Factory\AdapterFactoryInterface;

    class YourAdapterFactory implements AdapterFactoryInterface
    {
        public function create(string $dsn): AdapterInterface
        {
            return new YourAdapter(/* ... */);
        }

        public function supports(string $dsn): bool
        {
            return 0 === strpos($dsn, 'my-adapter://');
        }
    }

The :code:`YourAdaper` class need to implements the :code:`AdapterInterface`. It
will like the following example::

    use Symfony\Component\Messenger\Adapter\Factory\AdapterInterface;
    use Symfony\Component\Messenger\Transport\ReceiverInterface;
    use Symfony\Component\Messenger\Transport\SenderInterface;

    class YourAdapter implements AdapterInterface
    {
        public function receiver(): ReceiverInterface
        {
            return new YourReceiver(/* ... */);
        }

        public function sender(): SenderInterface
        {
            return new YourSender(/* ... */);
        }
    }

Register your factory
~~~~~~~~~~~~~~~~~~~~~

.. code-block:: xml

    <service id="Your\Adapter\Factory">
       <tag name="messenger.adapter_factory" />
    </service>

Use your adapter
~~~~~~~~~~~~~~~~

Within the :code:`framework.messenger.adapters.*` configuration, create your
named adapter using your own DSN:

.. code-block:: yaml

    framework:
        messenger:
            adapters:
                yours: 'my-adapter://...'

This will give you access to the following services:

1. :code:`messenger.yours_adapter`: the instance of your adapter.
2. :code:`messenger.yours_receiver` and :code:`messenger.yours_sender`, the
   receiver and sender created by the adapter.

.. _`enqueue's adapter`: https://github.com/sroze/enqueue-bridge
