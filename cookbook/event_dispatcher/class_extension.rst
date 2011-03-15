.. index::
   single: Event Dispatcher

How to extend a Class without using Inheritance
===============================================

To allow multiple classes to add methods to another one, you can define the
magic ``__call()`` method in the class you want to be extended like this::

    class Foo
    {
        // ...

        public function __call($method, $arguments)
        {
            // create an event named 'foo.method_is_not_found'
            // and pass the method name and the arguments passed to this method
            $event = new Event($this, 'foo.method_is_not_found', array('method' => $method, 'arguments' => $arguments));

            // calls all listeners until one is able to implement the $method
            $this->dispatcher->notifyUntil($event);

            // no listener was able to process the event? The method does not exist
            if (!$event->isProcessed()) {
                throw new \Exception(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
            }

            // return the listener returned value
            return $event->getReturnValue();
        }
    }

Then, create a class that will host the listener::

    class Bar
    {
        public function addBarMethodToFoo(Event $event)
        {
            // we only want to respond to the calls to the 'bar' method
            if ('bar' != $event->get('method']) {
                // allow another listener to take care of this unknown method
                return false;
            }

            // the subject object (the foo instance)
            $foo = $event->getSubject();

            // the bar method arguments
            $arguments = $event->get('parameters');

            // do something
            // ...

            // set the return value
            $event->setReturnValue($someValue);

            // tell the world that you have processed the event
            return true;
        }
    }

Eventually, add the new ``bar`` method to the ``Foo`` class::

    $dispatcher->connect('foo.method_is_not_found', array($bar, 'addBarMethodToFoo'));
