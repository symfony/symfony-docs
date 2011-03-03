.. index::
   single: Event Dispatcher

How to customize a Method Behavior without using Inheritance
============================================================

Doing something before or after a Method Call
---------------------------------------------

If you want to do something just before, or just after a method is called, you
can notify respectively an event at the beginning or at the end of the
method::

    class Foo
    {
        // ...

        public function send($foo, $bar)
        {
            // do something before the method
            $event = new Event($this, 'foo.do_before_send', array('foo' => $foo, 'bar' => $bar));
            $this->dispatcher->notify($event);

            // the real method implementation is here
            // $ret = ...;

            // do something after the method
            $event = new Event($this, 'foo.do_after_send', array('ret' => $ret));
            $this->dispatcher->notify($event);

            return $ret;
        }
    }

Modifying Method Arguments
--------------------------

If you want to allow third party classes to modify arguments passed to a method
just before that method is executed, add a ``filter`` event at the beginning of
the method::

    class Foo
    {
        // ...

        public function render($template, $arguments = array())
        {
            // filter the arguments
            $event = new Event($this, 'foo.filter_arguments');
            $this->dispatcher->filter($event, $arguments);

            // get the filtered arguments
            $arguments = $event->getReturnValue();
            // the method starts here
        }
    }

And here is a filter example::

    class Bar
    {
        public function filterFooArguments(Event $event, $arguments)
        {
            $arguments['processed'] = true;

            return $arguments;
        }
    }
