.. index::
   single: Event Dispatcher

The Generic Event Object
========================

.. versionadded:: 2.1
    The ``GenericEvent`` event class was added in Symfony 2.1

The base :class:`Symfony\\Component\\EventDispatcher\\Event` class provided by the
``Event Dispatcher`` component is deliberately sparse to allow the creation of
API specific event objects by inheritance using OOP. This allow for elegant and
readable code in complex applications.

The :class:`Symfony\\Component\\EventDispatcher\\GenericEvent` is available
for convenience for those who wish to use just one event object throughout their
application. It is suitable for most purposes straight out of the box, because
it follows the standard observer pattern where the event object
encapsulates an event 'subject', but has the addition of optional extra
arguments.

:class:`Symfony\\Component\\EventDispatcher\\GenericEvent` has a simple API in
addition to the base class :class:`Symfony\\Component\\EventDispatcher\\Event`

* :method:`Symfony\\Component\\EventDispatcher\\GenericEvent::__construct`:
  Constructor takes the event subject and any arguments;

* :method:`Symfony\\Component\\EventDispatcher\\GenericEvent::getSubject`:
  Get the subject;

* :method:`Symfony\\Component\\EventDispatcher\\GenericEvent::setArg`:
  Sets an argument by key;

* :method:`Symfony\\Component\\EventDispatcher\\GenericEvent::setArgs`:
  Sets arguments array;

* :method:`Symfony\\Component\\EventDispatcher\\GenericEvent::getArg`:
  Gets an argument by key;

* :method:`Symfony\\Component\\EventDispatcher\\GenericEvent::getArgs`:
  Gets an array of argument;

* :method:`Symfony\\Component\\EventDispatcher\\GenericEvent::hasArg`:
  Returns true if the argument key exists;

The ``GenericEvent`` also implements :phpclass:`ArrayAccess` on the event
arguments which makes it very convenient to pass extra arguments regarding the
event subject.

The following examples show use-cases to give a general idea of the flexibility.
The examples assume event listeners have been added to the dispatcher.

Simply passing a subject::

    use Symfony\Component\EventDispatcher\GenericEvent;

    $event = GenericEvent($subject);
    $dispatcher->dispatch('foo', $event);

    class FooListener
    {
        public function handler(GenericEvent $event)
        {
            if ($event->getSubject() instanceof Foo) {
                // ...
            }
        }
    }

Passing and processing arguments using the :phpclass:`ArrayAccess` API to access
the event arguments::

    use Symfony\Component\EventDispatcher\GenericEvent;

    $event = new GenericEvent($subject, array('type' => 'foo', 'counter' => 0)));
    $dispatcher->dispatch('foo', $event);

    echo $event['counter'];

    class FooListener
    {
        public function handler(GenericEvent $event)
        {
            if (isset($event['type']) && $event['type'] === 'foo') {
                // ... do something
            }

            $event['counter']++;
        }
    }

Filtering data::

    use Symfony\Component\EventDispatcher\GenericEvent;

    $event = new GenericEvent($subject, array('data' => 'foo'));
    $dispatcher->dispatch('foo', $event);

    echo $event['data'];

    class FooListener
    {
        public function filter(GenericEvent $event)
        {
            strtolower($event['data']);
        }
    }
