How to create an event listener
===============================

Symfony has various events and hooks that can be used to trigger custom
behavior in your application. Those events are thrown by the HttpKernel 
component and can be viewed in the `KernelEvents`_ class. 

In order to trigger a custom behavior based on an event, you have to 
create a service that will act as an event listener to those specific
events. In this entry, we will create a service that will act as an 
Exception Listener, allowing us to modify how exceptions are shown by 
our application.

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

Now, all we have to do is the service declaration and the registering
of our event listener to the correct tag.

.. configuration-block::

    .. code-block:: yaml

        services:
            kernel.listener.your_listener_name:
                class: Acme\DemoBundle\AcmeExceptionListener
                tags:
                    - { name: kernel.event_listener, event: kernel.exception, method: onKernelException }

    .. code-block:: xml

        <service id="kernel.listener.your_listener_name" class="Acme\DemoBundle\AcmeExceptionListener">
            <tag name="kernel.event_listener" event="kernel.exception" method="onKernelException" />
        </service>

    .. code-block:: php

        $container
            ->register('kernel.listener.your_listener_name', 'Acme\DemoBundle\AcmeExceptionListener')
            ->addTag('kernel.event_listener', array('event' => 'kernel.exception', 'method' => 'onKernelException'))
        ;
        
.. note::

    There is an additional tag option ``priority`` that is optional and defaults
    to 0. This value can be from -255 to 255, and the listeners will be executed
    in the order of their priority. This is useful when you need to guarantee
    that one listener is executed before another.


.. _`KernelEvents`: https://github.com/symfony/symfony/blob/2.0/src/Symfony/Component/HttpKernel/KernelEvents.php
