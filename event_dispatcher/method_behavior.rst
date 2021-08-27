.. index::
   single: EventDispatcher

How to Customize a Method Behavior without Using Inheritance
============================================================

Doing something before or after a Method Call
---------------------------------------------

If you want to do something right before, or directly after a method is
called, you can dispatch an event respectively at the beginning or at the
end of the method::

    class CustomMailer
    {
        // ...

        public function send($subject, $message)
        {
            // dispatch an event before the method
            $event = new BeforeSendMailEvent($subject, $message);
            $this->dispatcher->dispatch($event, 'mailer.pre_send');

            // get $subject and $message from the event, they may have been modified
            $subject = $event->getSubject();
            $message = $event->getMessage();

            // the real method implementation is here
            $returnValue = ...;

            // do something after the method
            $event = new AfterSendMailEvent($returnValue);
            $this->dispatcher->dispatch($event, 'mailer.post_send');

            return $event->getReturnValue();
        }
    }

In this example, two events are dispatched:

#. ``mailer.pre_send``, before the method is called,
#. and ``mailer.post_send`` after the method is called.

Each uses a custom Event class to communicate information to the listeners
of the two events. For example, ``BeforeSendMailEvent`` might look like
this::

    // src/Event/BeforeSendMailEvent.php
    namespace App\Event;

    use Symfony\Contracts\EventDispatcher\Event;

    class BeforeSendMailEvent extends Event
    {
        private $subject;
        private $message;

        public function __construct($subject, $message)
        {
            $this->subject = $subject;
            $this->message = $message;
        }

        public function getSubject()
        {
            return $this->subject;
        }

        public function setSubject($subject)
        {
            $this->subject = $subject;
        }

        public function getMessage()
        {
            return $this->message;
        }

        public function setMessage($message)
        {
            $this->message = $message;
        }
    }

And the ``AfterSendMailEvent`` even like this::

    // src/Event/AfterSendMailEvent.php
    namespace App\Event;

    use Symfony\Contracts\EventDispatcher\Event;

    class AfterSendMailEvent extends Event
    {
        private $returnValue;

        public function __construct($returnValue)
        {
            $this->returnValue = $returnValue;
        }

        public function getReturnValue()
        {
            return $this->returnValue;
        }

        public function setReturnValue($returnValue)
        {
            $this->returnValue = $returnValue;
        }
    }

Both events allow you to get some information (e.g. ``getMessage()``) and even change
that information (e.g. ``setMessage()``).

Now, you can create an event subscriber to hook into this event. For example, you
could listen to the ``mailer.post_send`` event and change the method's return value::

    // src/EventSubscriber/MailPostSendSubscriber.php
    namespace App\EventSubscriber;

    use App\Event\AfterSendMailEvent;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    class MailPostSendSubscriber implements EventSubscriberInterface
    {
        public function onMailerPostSend(AfterSendMailEvent $event)
        {
            $returnValue = $event->getReturnValue();
            // modify the original ``$returnValue`` value

            $event->setReturnValue($returnValue);
        }

        public static function getSubscribedEvents()
        {
            return [
                'mailer.post_send' => 'onMailerPostSend',
            ];
        }
    }

That's it! Your subscriber should be called automatically (or read more about
:ref:`event subscriber configuration <ref-event-subscriber-configuration>`).
