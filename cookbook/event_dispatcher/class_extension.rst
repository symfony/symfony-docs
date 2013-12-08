.. index::
   single: EventDispatcher

How to extend a Class without using Inheritance
===============================================

To allow multiple classes to add methods to another one, you can define the
magic ``__call()`` method in the class you want to be extended like this:

.. code-block:: php

    class Foo
    {
        // ...

        public function __call($method, $arguments)
        {
            // create an event named 'foo.method_is_not_found'
            $event = new HandleUndefinedMethodEvent($this, $method, $arguments);
            $this->dispatcher->dispatch('foo.method_is_not_found', $event);

            // no listener was able to process the event? The method does not exist
            if (!$event->isProcessed()) {
                throw new \Exception(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
            }

            // return the listener returned value
            return $event->getReturnValue();
        }
    }

This uses a special ``HandleUndefinedMethodEvent`` that should also be
created. This is a generic class that could be reused each time you need to
use this pattern of class extension:

.. code-block:: php

    use Symfony\Component\EventDispatcher\Event;

    class HandleUndefinedMethodEvent extends Event
    {
        protected $subject;
        protected $method;
        protected $arguments;
        protected $returnValue;
        protected $isProcessed = false;

        public function __construct($subject, $method, $arguments)
        {
            $this->subject = $subject;
            $this->method = $method;
            $this->arguments = $arguments;
        }

        public function getSubject()
        {
            return $this->subject;
        }

        public function getMethod()
        {
            return $this->method;
        }

        public function getArguments()
        {
            return $this->arguments;
        }

        /**
         * Sets the value to return and stops other listeners from being notified
         */
        public function setReturnValue($val)
        {
            $this->returnValue = $val;
            $this->isProcessed = true;
            $this->stopPropagation();
        }

        public function getReturnValue()
        {
            return $this->returnValue;
        }

        public function isProcessed()
        {
            return $this->isProcessed;
        }
    }

Next, create a class that will listen to the ``foo.method_is_not_found`` event
and *add* the method ``bar()``:

.. code-block:: php

    class Bar
    {
        public function onFooMethodIsNotFound(HandleUndefinedMethodEvent $event)
        {
            // only respond to the calls to the 'bar' method
            if ('bar' != $event->getMethod()) {
                // allow another listener to take care of this unknown method
                return;
            }

            // the subject object (the foo instance)
            $foo = $event->getSubject();

            // the bar method arguments
            $arguments = $event->getArguments();

            // ... do something

            // set the return value
            $event->setReturnValue($someValue);
        }
    }

Finally, add the new ``bar`` method to the ``Foo`` class by registering an
instance of ``Bar`` with the ``foo.method_is_not_found`` event:

.. code-block:: php

    $bar = new Bar();
    $dispatcher->addListener('foo.method_is_not_found', array($bar, 'onFooMethodIsNotFound'));
