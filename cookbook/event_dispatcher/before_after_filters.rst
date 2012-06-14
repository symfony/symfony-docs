.. index::
   single: Event Dispatcher

How to setup before and after filters
=====================================

It is quite common in web applications development to need some logic to be executed just before
or just after our controller actions acting as filters or hooks.

In Symfony1, this was achieved with the preExecute and postExecute methods, most major frameworks have similar
methods but there is no such thing in Symfony2. Good news is that there is a much better way to interfere our
Request -> Response process with the EventListener component.

Token validation example
========================

Imagine that we need to develop an API where some controllers are public but some others are restricted
to one or some clients. For this private features, we provide a token to our clients to identify themselves.

So, before executing our controller action, we need to check if the action is restricted or not.
And if it is restricted, we need to validate the provided token.

.. note::

Please note that for simplicity in the recipe, tokens will be defined in config
and neither database setup nor authentication provider via Security component will be used

Creating a before filter with a controller.request event
========================================================

Basic Setup
-----------

We can add basic tokens configuration using config.yml and parameters key

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        parameters:
            tokens:
                client1: pass1
                client2: pass2


Tag controllers to be checked
-----------------------------

A kernel.controller listener gets executed at every request, so we need some way to identify
if the controller that matches the request needs a token validation.

A clean and easy way is to create an empty interface and make the controllers implement it

.. code-block:: php

    namespace Acme\DemoBundle\Controller;

    interface TokenAuthenticatedController
    {
    // Nothing here
    }

    class FooController implements TokenAuthenticatedController
    {
    // Your actions that need authentication
    }

Creating an Event Listener
--------------------------

.. code-block:: php

    namespace Acme\DemoBundle\EventListener;

    use Acme\DemoBundle\Controller\TokenAuthenticatedController;
    use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

    class BeforeListener
    {
        private $tokens;
        public function __contruct($tokens)
        {
            $this->tokens = $tokens;
        }

        public function onKernelController(FilterControllerEvent $event)
        {
            $controller = $event->getController();

            /**
             * $controller passed can be either a class or a Closure. This is not usual in Symfony2 but it may happen.
             * If it is a class, it comes in array format
             */
            if (!is_array($controller)) return;

            if($controller[0] instanceof TokenAuthenticatedController) {
                $token = $event->getRequest()->get('token');
                if (!in_array($token, $this->tokens)) {
                    throw new AccessDeniedHttpException('This action needs a valid token!');
                }
            }
        }
    }

Tagging the EventListener
-------------------------

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml (or inside or your services.yml)
        services:
            demo.tokens.action_listener:
              class: Acme\DemoBundle\EventListener\BeforeListener
              arguments: [ %tokens% ]
              tags:
                    - { name: kernel.event_listener, event: kernel.controller, method: onKernelController }

    .. code-block:: xml

        <service id="demo.tokens.action_listener" class="Acme\DemoBundle\EventListener\BeforeListener">
            <argument>%tokens%</argument>
            <tag name="kernel.event_listener" event="kernel.controller" method="onKernelController" />
        </service>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $listener = new Definition('Acme\DemoBundle\EventListener\BeforeListener', array('%tokens%'));
        $listener->addTag('kernel.event_listener', array('event' => 'kernel.controller', 'method' => 'onKernelController'));
        $container->setDefinition('demo.tokens.action_listener', $listener);

