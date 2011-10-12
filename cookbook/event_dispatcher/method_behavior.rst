.. index::
   single: Event Dispatcher

How to customize a Method Behavior without using Inheritance
============================================================

Doing something before or after a Method Call
---------------------------------------------

If you want to do something just before, or just after a method is called, you
can dispatch an event respectively at the beginning or at the end of the
method::

    class Foo
    {
        // ...

        public function send($foo, $bar)
        {
            // do something before the method
            $event = new FilterBeforeSendEvent($foo, $bar);
            $this->dispatcher->dispatch('foo.pre_send', $event);

            // get $foo and $bar from the event, they may have been modified
            $foo = $event->getFoo();
            $bar = $event->getBar();
            // the real method implementation is here
            // $ret = ...;

            // do something after the method
            $event = new FilterSendReturnValue($ret);
            $this->dispatcher->dispatch('foo.post_send', $event);

            return $event->getReturnValue();
        }
    }

In this example, two events are thrown: ``foo.pre_send``, before the method is
executed, and ``foo.post_send`` after the method is executed. Each uses a
custom Event class to communicate information to the listeners of the two
events. These event classes would need to be created by you and should allow,
in this example, the variables ``$foo``, ``$bar`` and ``$ret`` to be retrieved
and set by the listeners.

For example, assuming the ``FilterSendReturnValue`` has a ``setReturnValue``
method, one listener might look like this:

.. code-block:: php

    public function onFooPostSend(FilterSendReturnValue $event)
    {
        $ret = $event->getReturnValue();
        // modify the original ``$ret`` value

        $event->setReturnValue($ret);
    }
