.. index::
    single: Notifier; Events

Using Events
============

The class:``...\\..\\Transport`` of the Notifier component allows you to optionally hook
into the lifecycle via events.

The ``MessageEvent::class`` Event
---------------------------------

**Typical Purposes**: Doing something before the message is send (like logging
which message is going to be send, or displaying something about the event
to be executed.

Just before send the message, the event class ``MessageEvent`` is
dispatched. Listeners receive a
:class:`Symfony\\Component\\Notifier\\Event\\MessageEvent` event::

    use Symfony\Component\Notifier\Event\MessageEvent;

    $dispatcher->addListener(MessageEvent::class, function (MessageEvent $event) {
        // gets the message instance
        $message = $event->getMessage();

        // log something
        $this->logger(sprintf('Message with subject: %s will be send to %s, $message->getSubject(), $message->getRecipientId()'));
    });

The ``FailedMessageEvent`` Event
--------------------------------

**Typical Purposes**: Doing something before the exception is thrown (Retry to send the message or log additional information).

Whenever an exception is thrown while sending the message, the event class ``FailedMessageEvent`` is
dispatched. A listener can do anything useful before the exception is thrown.

Listeners receive a
:class:`Symfony\\Component\\Notifier\\Event\\FailedMessageEvent` event::

    use Symfony\Component\Notifier\Event\FailedMessageEvent;

    $dispatcher->addListener(FailedMessageEvent::class, function (FailedMessageEvent $event) {
        // gets the message instance
        $message = $event->getMessage();

        // gets the error instance
        $error = $event->getError();

        // log something
        $this->logger(sprintf('The message with subject: %s has not been sent successfully. The error is: %s, $message->getSubject(), $error->getMessage()'));
    });


The ``SentMessageEvent`` Event
------------------------------

**Typical Purposes**: To perform some action when the message is successfully sent (like retrieve the id returned
when the message is sent).

After the message has been successfully sent, the event class ``SentMessageEvent`` is
dispatched. Listeners receive a
:class:`Symfony\\Component\\Notifier\\Event\\SentMessageEvent` event::

    use Symfony\Component\Notifier\Event\SentMessageEvent;

    $dispatcher->addListener(SentMessageEvent::class, function (SentMessageEvent $event) {
        // gets the message instance
        $message = $event->getOriginalMessage();

        // log something
        $this->logger(sprintf('The message has been successfully sent and have id: %s, $message->getMessageId()'));
    });
