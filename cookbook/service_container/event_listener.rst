.. index::
   single: Events; Create listener

How to Create an Event Listener
===============================

Symfony has various events and hooks that can be used to trigger custom
behavior in your application. Those events are thrown by the HttpKernel
component and can be viewed in the :class:`Symfony\\Component\\HttpKernel\\KernelEvents` class.

To hook into an event and add your own custom logic, you have to create
a service that will act as an event listener on that event. In this entry,
you will create a service that will act as an Exception Listener, allowing
you to modify how exceptions are shown by your application. The ``KernelEvents::EXCEPTION``
event is just one of the core kernel events::

    // src/AppBundle/EventListener/AcmeExceptionListener.php
    namespace AppBundle\EventListener;

    use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

    class AcmeExceptionListener
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
    To see what type of object each event listener receives, see :class:`Symfony\\Component\\HttpKernel\\KernelEvents`.

.. note::

    When setting a response for the ``kernel.request``, ``kernel.view`` or
    ``kernel.exception`` events, the propagation is stopped, so the lower
    priority listeners on that event don't get called.

Now that the class is created, you just need to register it as a service and
notify Symfony that it is a "listener" on the ``kernel.exception`` event by
using a special "tag":

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            kernel.listener.your_listener_name:
                class: AppBundle\EventListener\AcmeExceptionListener
                tags:
                    - { name: kernel.event_listener, event: kernel.exception, method: onKernelException }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <service id="kernel.listener.your_listener_name" class="AppBundle\EventListener\AcmeExceptionListener">
            <tag name="kernel.event_listener" event="kernel.exception" method="onKernelException" />
        </service>

    .. code-block:: php

        // app/config/services.php
        $container
            ->register('kernel.listener.your_listener_name', 'AppBundle\EventListener\AcmeExceptionListener')
            ->addTag('kernel.event_listener', array('event' => 'kernel.exception', 'method' => 'onKernelException'))
        ;

.. note::

    There is an additional tag option ``priority`` that is optional and defaults
    to 0. This value can be from -255 to 255, and the listeners will be executed
    in the order of their priority (highest to lowest). This is useful when
    you need to guarantee that one listener is executed before another.

Request Events, Checking Types
------------------------------

A single page can make several requests (one master request, and then multiple
sub-requests), which is why when working with the ``KernelEvents::REQUEST``
event, you might need to check the type of the request. This can be easily
done as follow::

    // src/AppBundle/EventListener/AcmeRequestListener.php
    namespace AppBundle\EventListener;

    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    use Symfony\Component\HttpKernel\HttpKernel;

    class AcmeRequestListener
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

.. tip::

    Two types of request are available in the :class:`Symfony\\Component\\HttpKernel\\HttpKernelInterface`
    interface: ``HttpKernelInterface::MASTER_REQUEST`` and
    ``HttpKernelInterface::SUB_REQUEST``.

Debugging Event Listeners
-------------------------

.. versionadded:: 2.6
    The ``debug:event-dispatcher`` command was introduced in Symfony 2.6.

You can find out what listeners are registered in the event dispatcher
using the console. To show all events and their listeners, run:

.. code-block:: bash

    $ php app/console debug:event-dispatcher

You can get registered listeners for a particular event by specifying
its name:

.. code-block:: bash

    $ php app/console debug:event-dispatcher kernel.exception
