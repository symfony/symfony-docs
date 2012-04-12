.. index::
   single: Event Dispatcher

The Generic Event Object
========================

.. versionadded:: 2.1
    Added general purpose ``GenericEvent`` event class since Symfony 2.1

The base ``Event`` class provided by the ``Event Dispatcher`` component is
deliberately sparse to allow the creation of API specific event objects by
inheritance using OOP. This allow for elegant and readable code in complex
applications.

However, for convenience, the ``Event Dispatcher`` component comes with a general
purpose event class :class:`Symfony\Component\EventDispatcher\GenericEvent`
for those who wish to use one event object throughout their application. Since
it is suitable for most purposes out of the box, it avoids the need to
create situation specific event objects suitable for less complex applications.

``GenericEvent`` follows the standard observer pattern where the event object
encapsulates an event 'subject', but has the addition of optional extra
arguments and a convenient way of filtering data via an additional `data`
property.

The ``GenericEvent`` implements :phpclass:`ArrayAccess` on the event arguments
which makes it very convenient to pass extra arguments regarding the event
subject.

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

Passing and processing arguments::

    use Symfony\Component\EventDispatcher\GenericEvent;

    $event = GenericEvent($subject, array('type' => 'foo', 'counter' => 0)));
    $dispatcher->dispatch('foo', $event);

    echo $event['counter'];

    class FooListener
    {
        public function handler(GenericEvent $event)
        {
            if (isset($event['type']) && $event['type'] == 'foo') {
                // ... do something
            }

            $event['counter']++;
        }
    }

Filtering data::

    $event = GenericEvent($subject, array(), $data);
    $dispatcher->dispatch('foo', $event);

    echo $event->getData();

    class FooListener
    {
        public function filter(GenericEvent $event)
        {
            $event->setData(strtolower($event->getData()));
        }
    }
