.. index::
   single: EventDispatcher

How to Customize a Method Behavior without Using Inheritance
============================================================

Doing something before or after a Method Call
---------------------------------------------

If you want to do something just before, or just after a method is called, you
can dispatch an event respectively at the beginning or at the end of the
method::

    class CustomMailer
    {
        // ...

        public function send($subject, $message)
        {
            // dispatch an event before the method
            $event = new BeforeSendMailEvent($subject, $message);
            $this->dispatcher->dispatch('mailer.pre_send', $event);

            // get $foo and $bar from the event, they may have been modified
            $subject = $event->getSubject();
            $message = $event->getMessage();

            // the real method implementation is here
            $ret = ...;

            // do something after the method
            $event = new AfterSendMailEvent($ret);
            $this->dispatcher->dispatch('mailer.post_send', $event);

            return $event->getReturnValue();
        }
    }

In this example, two events are thrown: ``mailer.pre_send``, before the method is
executed, and ``mailer.post_send`` after the method is executed. Each uses a
custom Event class to communicate information to the listeners of the two
events. For example, ``BeforeSendMailEvent`` might look like this::

    // src/AppBundle/Event/BeforeSendMailEvent.php
    namespace AppBundle\Event;

    use Symfony\Component\EventDispatcher\Event;

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

    // src/AppBundle/Event/AfterSendMailEvent.php
    namespace AppBundle\Event;

    use Symfony\Component\EventDispatcher\Event;

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

    // src/AppBundle/EventSubscriber/MailPostSendSubscriber.php
    namespace AppBundle\EventSubscriber;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use AppBundle\Event\AfterSendMailEvent;

    class MailPostSendSubscriber implements EventSubscriberInterface
    {
        public function onMailerPostSend(AfterSendMailEvent $event)
        {
            $ret = $event->getReturnValue();
            // modify the original ``$ret`` value

            $event->setReturnValue($ret);
        }

        public static function getSubscribedEvents()
        {
            return array(
                'mailer.post_send' => 'onMailerPostSend'
            );
        }
    }

That's it! Your subscriber should be called automatically (or read more about
:ref:`event subscriber configuration <ref-event-subscriber-configuration>`).
