.. index::
   single: Events; Create Listener

How to create an Event Listener
===============================

Symfony has various events and hooks that can be used to trigger custom
behavior in your application. Those events are thrown by the HttpKernel 
component and can be viewed in the :class:`Symfony\\Component\\HttpKernel\\KernelEvents` class. 

To hook into an event and add your own custom logic, you have to  create
a service that will act as an event listener on that event. In this entry,
we will create a service that will act as an Exception Listener, allowing
us to modify how exceptions are shown by  our application. The ``KernelEvents::EXCEPTION``
event is just one of the core kernel events::

    // src/Acme/DemoBundle/Listener/AcmeExceptionListener.php
    namespace Acme\DemoBundle\Listener;

    use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

    class AcmeExceptionListener
    {
        public function onKernelException(GetResponseForExceptionEvent $event)
        {
            // We get the exception object from the received event
            $exception = $event->getException();
            $message = 'My Error says: ' . $exception->getMessage();
            
            // Customize our response object to display our exception details
            $response->setContent($message);
            $response->setStatusCode($exception->getStatusCode());
            
            // Send our modified response object to the event
            $event->setResponse($response);
        }
    }

.. tip::

    Each event receives a slightly different type of ``$event`` object. For
    the ``kernel.exception`` event, it is :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent`.
    To see what type of object each event listener receives, see :class:`Symfony\\Component\\HttpKernel\\KernelEvents`.

Now that the class is created, we just need to register it as a service and
notify Symfony that it is a "listener" on the ``kernel.exception`` event by
using a special "tag":

.. configuration-block::

    .. code-block:: yaml

        services:
            kernel.listener.your_listener_name:
                class: Acme\DemoBundle\Listener\AcmeExceptionListener
                tags:
                    - { name: kernel.event_listener, event: kernel.exception, method: onKernelException }

    .. code-block:: xml

        <service id="kernel.listener.your_listener_name" class="Acme\DemoBundle\Listener\AcmeExceptionListener">
            <tag name="kernel.event_listener" event="kernel.exception" method="onKernelException" />
        </service>

    .. code-block:: php

        $container
            ->register('kernel.listener.your_listener_name', 'Acme\DemoBundle\Listener\AcmeExceptionListener')
            ->addTag('kernel.event_listener', array('event' => 'kernel.exception', 'method' => 'onKernelException'))
        ;
        
.. note::

    There is an additional tag option ``priority`` that is optional and defaults
    to 0. This value can be from -255 to 255, and the listeners will be executed
    in the order of their priority. This is useful when you need to guarantee
    that one listener is executed before another.
