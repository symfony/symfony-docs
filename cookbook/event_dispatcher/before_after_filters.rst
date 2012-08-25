.. index::
   single: Event Dispatcher

How to setup before and after Filters
=====================================

It is quite common in web application development to need some logic to be
executed just before or just after your controller actions acting as filters 
or hooks.

In Symfony1, this was achieved with the preExecute and postExecute methods,
most major frameworks have similar methods but there is no such thing in Symfony2.
The good news is that there is a much better way to interfere with the
Request -> Response process using the :doc:`EventDispatcher component</components/event_dispatcher/introduction>`.

Token validation Example
------------------------

Imagine that you need to develop an API where some controllers are public
but some others are restricted to one or some clients. For these private features,
you might provide a token to your clients to identify themselves.

So, before executing your controller action, you need to check if the action
is restricted or not. If it is restricted, you need to validate the provided
token.

.. note::

    Please note that for simplicity in this recipe, tokens will be defined
    in config and neither database setup nor authentication via
    the Security component will be used.

Creating a before filter with a controller.request event
--------------------------------------------------------

Basic Setup
~~~~~~~~~~~

You can add basic token configuration using ``config.yml`` and the parameters key:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        parameters:
            tokens:
                client1: pass1
                client2: pass2

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <parameters>
            <parameter key="tokens" type="collection">
                <parameter key="client1">pass1</parameter>
                <parameter key="client2">pass2</parameter>
            </parameter>
        </parameters>

    .. code-block:: php

        // app/config/config.php
        $container->setParameter('tokens', array(
            'client1' => 'pass1',
            'client2' => 'pass2',
        ));

Tag Controllers to be checked
-----------------------------

A ``kernel.controller`` listener gets notified on every request, right before
the controller is executed. First, you need some way to identify if the controller
that matches the request needs token validation.

A clean and easy way is to create an empty interface and make the controllers
implement it::

    namespace Acme\DemoBundle\Controller;

    interface TokenAuthenticatedController
    {
        // Nothing here
    }

A controller that implements this interface simply looks like this::

    class FooController implements TokenAuthenticatedController
    {
        // ... Your actions that need authentication
    }

Creating an Event Listener
--------------------------

Next, you'll need to create an event listener, which will hold the logic
that you want executed before your controllers. If you're not familiar with
event listeners, you can learn more about them at :doc:`/cookbook/service_container/event_listener`::

    // src/Acme/DemoBundle/EventListener/BeforeListener.php
    namespace Acme\DemoBundle\EventListener;

    use Acme\DemoBundle\Controller\TokenAuthenticatedController;
    use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
    use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

    class BeforeListener
    {
        private $tokens;

        public function __construct($tokens)
        {
            $this->tokens = $tokens;
        }

        public function onKernelController(FilterControllerEvent $event)
        {
            $controller = $event->getController();

            /*
             * $controller passed can be either a class or a Closure. This is not usual in Symfony2 but it may happen.
             * If it is a class, it comes in array format
             */
            if (!is_array($controller)) {
                return;
            }

            if ($controller[0] instanceof TokenAuthenticatedController) {
                $token = $event->getRequest()->get('token');
                if (!in_array($token, $this->tokens)) {
                    throw new AccessDeniedHttpException('This action needs a valid token!');
                }
            }
        }
    }

Registering the Listener
------------------------

Finally, register your listener as a service and tag it as an event listener.
By listening on ``kernel.controller``, you're telling Symfony that you want
your listener to be called just before any controller is executed:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml (or inside your services.yml)
        services:
            demo.tokens.action_listener:
                class: Acme\DemoBundle\EventListener\BeforeListener
                arguments: [ %tokens% ]
                tags:
                    - { name: kernel.event_listener, event: kernel.controller, method: onKernelController }

    .. code-block:: xml

        <!-- app/config/config.xml (or inside your services.xml) -->
        <service id="demo.tokens.action_listener" class="Acme\DemoBundle\EventListener\BeforeListener">
            <argument>%tokens%</argument>
            <tag name="kernel.event_listener" event="kernel.controller" method="onKernelController" />
        </service>

    .. code-block:: php

        // app/config/config.php (or inside your services.php)
        use Symfony\Component\DependencyInjection\Definition;

        $listener = new Definition('Acme\DemoBundle\EventListener\BeforeListener', array('%tokens%'));
        $listener->addTag('kernel.event_listener', array('event' => 'kernel.controller', 'method' => 'onKernelController'));
        $container->setDefinition('demo.tokens.action_listener', $listener);

