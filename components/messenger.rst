.. index::
   single: Messenger
   single: Components; Messenger

The Messenger Component
=======================

    The Messenger component helps applications send and receive messages to/from
    other applications or via message queues.

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
   Responsible for serializing and sending messages to *something*. This
   something can be a message broker or a third party API for example.

**Receiver**:
   Responsible for deserializing and forwarding messages to handler(s). This
   can be a message queue puller or an API endpoint for example.

**Handler**:
   Responsible for handling messages using the business logic applicable to the messages.

Bus
---

The bus is used to dispatch messages. The behaviour of the bus is in its ordered
middleware stack. The component comes with a set of middleware that you can use.

When using the message bus with Symfony's FrameworkBundle, the following middleware
are configured for you:

#. ``LoggingMiddleware`` (logs the processing of your messages)
#. ``SendMessageMiddleware`` (enables asynchronous processing)
#. ``HandleMessageMiddleware`` (calls the registered handle)

Example::

    use App\Message\MyMessage;
    use Symfony\Component\Messenger\MessageBus;
    use Symfony\Component\Messenger\HandlerLocator;
    use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

    $bus = new MessageBus([
        new HandleMessageMiddleware(new HandlerLocator([
            MyMessage::class => $handler,
        ])),
    ]);

    $result = $bus->dispatch(new MyMessage(/* ... */));

.. note:

    Every middleware needs to implement the ``MiddlewareInterface`` interface.

Handlers
--------

Once dispatched to the bus, messages will be handled by a "message handler". A
message handler is a PHP callable (i.e. a function or an instance of a class)
that will do the required processing for your message::

    namespace App\MessageHandler;

    use App\Message\MyMessage;

    class MyMessageHandler
    {
       public function __invoke(MyMessage $message)
       {
           // Message processing...
       }
    }

Transports
----------

In order to send and receive messages, you will have to configure a transport. An
transport will be responsible of communicating with your message broker or 3rd parties.

.. note:

    Check out the :doc:`Messenger transports documentation </components/messenger/transports>`.

Receiver and Sender on the same bus
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In order to receive and send messages on the same bus without having an infinite
loop, the message bus is equipped with the ``WrapIntoReceivedMessage`` middleware.

This middleware wraps the received messages into ``ReceivedMessage`` objects and the
``SendMessageMiddleware`` middleware will know it should not route these
messages again to a transport.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /messenger
    /messenger/*
