.. index::
   single: Event Dispatcher

The Event Dispatcher
====================

Objected Oriented code has gone a long way to ensuring code extensibility. By
creating classes that have well defined responsibilities, your code becomes
more flexible as a developer can extend them with sub-classes to modify their
behaviors. But if he wants to share his changes with other developers who have
also made their own subclasses, code inheritance is moot.

A real-world example is when you want to provide a plugin system for your
project. A plugin should be able to add methods, or do something before or
after a method is executed, without interfering with other plugins. This is
not an easy problem to solve with single inheritance, and multiple inheritance
(were it possible with PHP) has its own drawbacks.

The Symfony2 Event Dispatcher implements the `Observer`_ pattern in a simple
and effective way to make all these things possible and make your projects
truly extensible (see the :doc:`recipes` section for some implementation
examples).

The Event Dispatcher provides *dispatcher* that allows objects to communicate
together without knowing each others. Objects (*listeners*) can *connect* to
the dispatcher to listen to specific events, and some others can *notify* an
*event* to the dispatcher. Whenever an event is notified, the dispatcher will
call all the connected listeners.

.. index::
   pair: Event Dispatcher; Naming Conventions

Events
------

Unlike many other implementations of the Observer pattern, you don't need to
create a class to define a new event. All events are instead instances of the
``Event`` class and are uniquely identified by their names, a string that
optionally follows simple naming conventions:

* use only lowercase letters, numbers, and underscores (``_``);

* prefix names with a namespace followed by a dot (``.``);

* use a verb to indicate what action will be done.

Here are some examples of good event names:

* user.change_culture
* response.filter_content

.. note::

    You can of course extend the ``Event`` class to specialize an event
    further, or enforce some constraints, but most of the time it adds an
    unnecessary level of complexity.

Besides its name, an ``Event`` instance can store additional data about the
notified event:

* The *subject* of the event (most of the time, this is the object notifying
  the event, but it can be any other object or ``null``);

* The event name;

* An array of parameters to pass to the listeners (an empty array by default).

These data are passed as arguments to the ``Event`` constructor::

    use Symfony\Component\EventDispatcher\Event;

    $event = new Event($this, 'user.change_culture', array('culture' => $culture));

The event object has several methods to get the event data:

* ``getName()``: Returns the event name;

* ``getSubject()``: Gets the subject object attached to the event;

* ``getParameters()``: Returns the event parameters.

The event object can also be accessed as an array to get its parameters::

    echo $event['culture'];

The Dispatcher
--------------

The dispatcher maintains a register of listeners and calls them whenever an
event is notified::

    use Symfony\Component\EventDispatcher\EventDispatcher;

    $dispatcher = new EventDispatcher();

.. index::
   single: Event Dispatcher; Listeners

Connecting Listeners
--------------------

Obviously, you need to connect some listeners to the dispatcher before it can
be useful. A call to the dispatcher ``connect()`` method associates a PHP
callable to an event::

    $dispatcher->connect('user.change_culture', $callable);

The ``connect()`` method takes two arguments:

* The event name;

* A PHP callable to call when the event is notified.

.. note::

    A `PHP callable`_ is a PHP variable that can be used by the
    ``call_user_func()`` function and returns ``true`` when passed to the
    ``is_callable()`` function. It can be a ``\Closure`` instance, a string
    representing a function, or an array representing an object method or a
    class method.

Once a listener is registered with the dispatcher, it waits until the event is
notified. For the above example, the dispatcher calls ``$callable`` whenever
the ``user.change_culture`` event is notified; the listener receives an
``Event`` instance as an argument.

.. note::

    The listeners are called by the event dispatcher in the same order you
    connected them.

.. tip::

    If you use the Symfony2 MVC framework, listeners are automatically
    registered based on your :ref:`configuration <kernel_listener_tag>`.

.. index::
   single: Event Dispatcher; Notification

Notifying Events
----------------

Events can be notified by using three methods:

* ``notify()``

* ``notifyUntil()``

* ``filter()``

``notify``
~~~~~~~~~~

The ``notify()`` method notifies all listeners in turn::

    $dispatcher->notify($event);

By using the ``notify()`` method, you make sure that all registered listeners
for the event are executed but their return values is ignored.

``notifyUntil``
~~~~~~~~~~~~~~~

In some cases, you need to allow a listener to stop the event and prevent
further listeners from being notified about it. In this case, you should use
``notifyUntil()`` instead of ``notify()``. The dispatcher will then execute all
listeners until one returns ``true``, and then stop the event notification::

    $dispatcher->notifyUntil($event);

The listener that stops the chain may also call the ``setReturnValue()`` method
to return back some value to the subject::

    $event->setReturnValue('foo');

    return true;

The notifier can check if a listener has processed the event by calling the
``isProcessed()`` method::

    if ($event->isProcessed()) {
        $ret = $event->getReturnValue();

        // ...
    }

``filter``
~~~~~~~~~~

The ``filter()`` method asks all listeners to filter a given value, passed by
the notifier as its second argument, and retrieved by the listener callable as
the second argument::

    $dispatcher->filter($event, $response->getContent());

    $listener = function (Event $event, $content)
    {
        // do something with $content

        // don't forget to return the content
        return $content;
    };

All listeners are passed the value and they must return the filtered value,
whether they altered it or not. All listeners are guaranteed to be executed.

The notifier can get the filtered value by calling the ``getReturnValue()``
method::

    $ret = $event->getReturnValue();

.. _Observer:     http://en.wikipedia.org/wiki/Observer_pattern
.. _PHP callable: http://www.php.net/manual/en/function.is-callable.php
