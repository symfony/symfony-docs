.. index::
   single: Event Dispatcher

How to setup before and after Filters
=====================================

It is quite common in web application development to need some logic to be
executed just before or just after your controller actions acting as filters
or hooks.

In symfony1, this was achieved with the preExecute and postExecute methods.
Most major frameworks have similar methods but there is no such thing in Symfony2.
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
    in config and neither database setup nor authentication via the Security
    component will be used.

Before filters with the ``kernel.controller`` Event
---------------------------------------------------

First, store some basic token configuration using ``config.yml`` and the
parameters key:

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
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

A ``kernel.controller`` listener gets notified on *every* request, right before
the controller is executed. So, first, you need some way to identify if the
controller that matches the request needs token validation.

A clean and easy way is to create an empty interface and make the controllers
implement it::

    namespace Acme\DemoBundle\Controller;

    interface TokenAuthenticatedController
    {
        // ...
    }

A controller that implements this interface simply looks like this::

    namespace Acme\DemoBundle\Controller;

    use Acme\DemoBundle\Controller\TokenAuthenticatedController;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class FooController extends Controller implements TokenAuthenticatedController
    {
        // An action that needs authentication
        public function barAction()
        {
            // ...
        }
    }

Creating an Event Listener
~~~~~~~~~~~~~~~~~~~~~~~~~~

Next, you'll need to create an event listener, which will hold the logic
that you want executed before your controllers. If you're not familiar with
event listeners, you can learn more about them at :doc:`/cookbook/service_container/event_listener`::

    // src/Acme/DemoBundle/EventListener/TokenListener.php
    namespace Acme\DemoBundle\EventListener;

    use Acme\DemoBundle\Controller\TokenAuthenticatedController;
    use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
    use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

    class TokenListener
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
                $token = $event->getRequest()->query->get('token');
                if (!in_array($token, $this->tokens)) {
                    throw new AccessDeniedHttpException('This action needs a valid token!');
                }
            }
        }
    }

Registering the Listener
~~~~~~~~~~~~~~~~~~~~~~~~

Finally, register your listener as a service and tag it as an event listener.
By listening on ``kernel.controller``, you're telling Symfony that you want
your listener to be called just before any controller is executed.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml (or inside your services.yml)
        services:
            demo.tokens.action_listener:
                class: Acme\DemoBundle\EventListener\TokenListener
                arguments: ["%tokens%"]
                tags:
                    - { name: kernel.event_listener, event: kernel.controller, method: onKernelController }

    .. code-block:: xml

        <!-- app/config/config.xml (or inside your services.xml) -->
        <service id="demo.tokens.action_listener" class="Acme\DemoBundle\EventListener\TokenListener">
            <argument>%tokens%</argument>
            <tag name="kernel.event_listener" event="kernel.controller" method="onKernelController" />
        </service>

    .. code-block:: php

        // app/config/config.php (or inside your services.php)
        use Symfony\Component\DependencyInjection\Definition;

        $listener = new Definition('Acme\DemoBundle\EventListener\TokenListener', array('%tokens%'));
        $listener->addTag('kernel.event_listener', array(
            'event'  => 'kernel.controller',
            'method' => 'onKernelController'
        ));
        $container->setDefinition('demo.tokens.action_listener', $listener);

With this configuration, your ``TokenListener`` ``onKernelController`` method
will be executed on each request. If the controller that is about to be executed
implements ``TokenAuthenticatedController``, token authentication is
applied. This lets you have a "before" filter on any controller that you
want.

After filters with the ``kernel.response`` Event
------------------------------------------------

In addition to having a "hook" that's executed before your controller, you
can also add a hook that's executed *after* your controller. For this example,
imagine that you want to add a sha1 hash (with a salt using that token) to
all responses that have passed this token authentication.

Another core Symfony event - called ``kernel.response`` - is notified on
every request, but after the controller returns a Response object. Creating
an "after" listener is as easy as creating a listener class and registering
it as a service on this event.

For example, take the ``TokenListener`` from the previous example and first
record the authentication token inside the request attributes. This will
serve as a basic flag that this request underwent token authentication::

    public function onKernelController(FilterControllerEvent $event)
    {
        // ...

        if ($controller[0] instanceof TokenAuthenticatedController) {
            $token = $event->getRequest()->query->get('token');
            if (!in_array($token, $this->tokens)) {
                throw new AccessDeniedHttpException('This action needs a valid token!');
            }

            // mark the request as having passed token authentication
            $event->getRequest()->attributes->set('auth_token', $token);
        }
    }

Now, add another method to this class - ``onKernelResponse`` - that looks
for this flag on the request object and sets a custom header on the response
if it's found::

    // add the new use statement at the top of your file
    use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

    public function onKernelResponse(FilterResponseEvent $event)
    {
        // check to see if onKernelController marked this as a token "auth'ed" request
        if (!$token = $event->getRequest()->attributes->get('auth_token')) {
            return;
        }

        $response = $event->getResponse();

        // create a hash and set it as a response header
        $hash = sha1($response->getContent().$token);
        $response->headers->set('X-CONTENT-HASH', $hash);
    }

Finally, a second "tag" is needed on the service definition to notify Symfony
that the ``onKernelResponse`` event should be notified for the ``kernel.response``
event:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml (or inside your services.yml)
        services:
            demo.tokens.action_listener:
                class: Acme\DemoBundle\EventListener\TokenListener
                arguments: ["%tokens%"]
                tags:
                    - { name: kernel.event_listener, event: kernel.controller, method: onKernelController }
                    - { name: kernel.event_listener, event: kernel.response, method: onKernelResponse }

    .. code-block:: xml

        <!-- app/config/config.xml (or inside your services.xml) -->
        <service id="demo.tokens.action_listener" class="Acme\DemoBundle\EventListener\TokenListener">
            <argument>%tokens%</argument>
            <tag name="kernel.event_listener" event="kernel.controller" method="onKernelController" />
            <tag name="kernel.event_listener" event="kernel.response" method="onKernelResponse" />
        </service>

    .. code-block:: php

        // app/config/config.php (or inside your services.php)
        use Symfony\Component\DependencyInjection\Definition;

        $listener = new Definition('Acme\DemoBundle\EventListener\TokenListener', array('%tokens%'));
        $listener->addTag('kernel.event_listener', array(
            'event'  => 'kernel.controller',
            'method' => 'onKernelController'
        ));
        $listener->addTag('kernel.event_listener', array(
            'event'  => 'kernel.response',
            'method' => 'onKernelResponse'
        ));
        $container->setDefinition('demo.tokens.action_listener', $listener);

That's it! The ``TokenListener`` is now notified before every controller is
executed (``onKernelController``) and after every controller returns a response
(``onKernelResponse``). By making specific controllers implement the ``TokenAuthenticatedController``
interface, your listener knows which controllers it should take action on.
And by storing a value in the request's "attributes" bag, the ``onKernelResponse``
method knows to add the extra header. Have fun!
