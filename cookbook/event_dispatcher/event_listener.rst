.. index::
   single: Events; Create listener
   single: Create subscriber

How to Create Event Listeners and Subscribers
=============================================

During the execution of a Symfony application, lots of event notifications are
triggered. Your application can listen to these notifications and respond to
them by executing any piece of code.

Internal events provided by Symfony itself are defined in the
:class:`Symfony\\Component\\HttpKernel\\KernelEvents` class. Third-party bundles
and libraries also trigger lots of events and your own application can trigger
:doc:`custom events </components/event_dispatcher/index>`.

All the examples shown in this article use the same ``KernelEvents::EXCEPTION``
event for consistency purposes. In your own application, you can use any event
and even mix several of them in the same subscriber.

Creating an Event Listener
--------------------------

The most common way to listen to an event is to register an **event listener**::

    // src/AppBundle/EventListener/ExceptionListener.php
    namespace AppBundle\EventListener;

    use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

    class ExceptionListener
    {
        public function onKernelException(GetResponseForExceptionEvent $event)
        {
            // You get the exception object from the received event
            $exception = $event->getException();
            $message = sprintf(
                'My Error says: %s with code: %s',
                $exception->getMessage(),
                $exception->getCode()
            );

            // Customize your response object to display the exception details
            $response = new Response();
            $response->setContent($message);

            // HttpExceptionInterface is a special type of exception that
            // holds status code and header details
            if ($exception instanceof HttpExceptionInterface) {
                $response->setStatusCode($exception->getStatusCode());
                $response->headers->replace($exception->getHeaders());
            } else {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Send the modified response object to the event
            $event->setResponse($response);
        }
    }

.. tip::

    Each event receives a slightly different type of ``$event`` object. For
    the ``kernel.exception`` event, it is :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent`.
    To see what type of object each event listener receives, see :class:`Symfony\\Component\\HttpKernel\\KernelEvents`
    or the documentation about the specific even you're listening to.

Now that the class is created, you just need to register it as a service and
notify Symfony that it is a "listener" on the ``kernel.exception`` event by
using a special "tag":

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.exception_listener:
                class: AppBundle\EventListener\ExceptionListener
                tags:
                    - { name: kernel.event_listener, event: kernel.exception }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.exception_listener"
                    class="AppBundle\EventListener\ExceptionListener">

                    <tag name="kernel.event_listener" event="kernel.exception" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        $container
            ->register('app.exception_listener', 'AppBundle\EventListener\ExceptionListener')
            ->addTag('kernel.event_listener', array('event' => 'kernel.exception'))
        ;

.. note::

    There is an optional tag attribute called ``method`` which defines which method
    to execute when the event is triggered. By default the name of the method is
    ``on`` + "camel-cased event name". If the event is ``kernel.exception`` the
    method executed by default is ``onKernelException()``.

    The other optional tag attribute is called  ``priority``, which defaults to
    ``0`` and it controls the order in which listeners are executed (the highest
    the priority, the earlier a listener is executed). This is useful when you
    need to guarantee that one listener is executed before another. The priorities
    of the internal Symfony listeners usually range from ``-255`` to ``255`` but
    your own listeners can use any positive or negative integer.

Creating an Event Subscriber
----------------------------

Another way to listen to events is via an **event subscriber**, which is a class
that defines one or more methods that listen to one or various events. The main
difference with the event listeners is that subscribers always know which events
they are listening to.

In a given subscriber, different methods can listen to the same event. The order
in which methods are executed is defined by the ``priority`` parameter of each
method (the higher the priority the earlier the method is called). To learn more
about event subscribers, read :doc:`/components/event_dispatcher/introduction`.

The following example shows an event subscriber that defines several methods which
listen to the same ``kernel.exception`` event::

    // src/AppBundle/EventSubscriber/ExceptionSubscriber.php
    namespace AppBundle\EventSubscriber;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
    use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

    class ExceptionSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            // return the subscribed events, their methods and priorities
            return array(
               'kernel.exception' => array(
                   array('processException', 10),
                   array('logException', 0),
                   array('notifyException', -10),
               )
            );
        }

        public function processException(GetResponseForExceptionEvent $event)
        {
            // ...
        }

        public function logException(GetResponseForExceptionEvent $event)
        {
            // ...
        }

        public function notifyException(GetResponseForExceptionEvent $event)
        {
            // ...
        }
    }

Now, you just need to register the class as a service and add the
``kernel.event_subscriber`` tag to tell Symfony that this is an event subscriber:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.exception_subscriber:
                class: AppBundle\EventSubscriber\ExceptionSubscriber
                tags:
                    - { name: kernel.event_subscriber }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.exception_subscriber"
                    class="AppBundle\EventSubscriber\ExceptionSubscriber">

                    <tag name="kernel.event_subscriber"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        $container
            ->register(
                'app.exception_subscriber',
                'AppBundle\EventSubscriber\ExceptionSubscriber'
            )
            ->addTag('kernel.event_subscriber')
        ;

Request Events, Checking Types
------------------------------

A single page can make several requests (one master request, and then multiple
sub-requests - typically by :ref:`templating-embedding-controller`). For the core
Symfony events, you might need to check to see if the event is for a "master" request
or a "sub request"::

    // src/AppBundle/EventListener/RequestListener.php
    namespace AppBundle\EventListener;

    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    use Symfony\Component\HttpKernel\HttpKernel;
    use Symfony\Component\HttpKernel\HttpKernelInterface;

    class RequestListener
    {
        public function onKernelRequest(GetResponseEvent $event)
        {
            if (!$event->isMasterRequest()) {
                // don't do anything if it's not the master request
                return;
            }

            // ...
        }
    }

Certain things, like checking information on the *real* request, may not need to
be done on the sub-request listeners.

.. _events-or-subscribers:

Listeners or Subscribers
------------------------

Listeners and subscribers can be used in the same application indistinctly. The
decision to use either of them is usually a matter of personal taste. However,
there are some minor advantages for each of them:

* **Subscribers are easier to reuse** because the knowledge of the events is kept
  in the class rather than in the service definition. This is the reason why
  Symfony uses subscribers internally;
* **Listeners are more flexible** because bundles can enable or disable each of
  them conditionally depending on some configuration value.

Debugging Event Listeners
-------------------------

You can find out what listeners are registered in the event dispatcher
using the console. To show all events and their listeners, run:

.. code-block:: bash

    $ php bin/console debug:event-dispatcher

You can get registered listeners for a particular event by specifying
its name:

.. code-block:: bash

    $ php bin/console debug:event-dispatcher kernel.exception
