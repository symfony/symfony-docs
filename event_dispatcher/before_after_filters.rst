.. index::
   single: EventDispatcher

How to Set Up Before and After Filters
======================================

It is quite common in web application development to need some logic to be
performed right before or directly after your controller actions acting as
filters or hooks.

Some web frameworks define methods like ``preExecute()`` and ``postExecute()``,
but there is no such thing in Symfony. The good news is that there is a much
better way to interfere with the Request -> Response process using the
:doc:`EventDispatcher component </components/event_dispatcher>`.

Token Validation Example
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

Before Filters with the ``kernel.controller`` Event
---------------------------------------------------

First, define some token configuration as parameters:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            tokens:
                client1: pass1
                client2: pass2

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="tokens" type="collection">
                    <parameter key="client1">pass1</parameter>
                    <parameter key="client2">pass2</parameter>
                </parameter>
            </parameters>
        </container>

    .. code-block:: php

        // config/services.php
        $container->setParameter('tokens', [
            'client1' => 'pass1',
            'client2' => 'pass2',
        ]);

Tag Controllers to Be Checked
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

A ``kernel.controller`` (aka ``KernelEvents::CONTROLLER``) listener gets notified
on *every* request, right before the controller is executed. So, first, you need
some way to identify if the controller that matches the request needs token validation.

A clean and easy way is to create an empty interface and make the controllers
implement it::

    namespace App\Controller;

    interface TokenAuthenticatedController
    {
        // ...
    }

A controller that implements this interface looks like this::

    namespace App\Controller;

    use App\Controller\TokenAuthenticatedController;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class FooController extends AbstractController implements TokenAuthenticatedController
    {
        // An action that needs authentication
        public function bar()
        {
            // ...
        }
    }

Creating an Event Subscriber
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Next, you will need to create an event subscriber, which will hold the logic
that you want to be executed before your controllers. If you are not familiar with
event subscribers, you can learn more about them at :doc:`/event_dispatcher`::

    // src/EventSubscriber/TokenSubscriber.php
    namespace App\EventSubscriber;

    use App\Controller\TokenAuthenticatedController;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\ControllerEvent;
    use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
    use Symfony\Component\HttpKernel\KernelEvents;

    class TokenSubscriber implements EventSubscriberInterface
    {
        private $tokens;

        public function __construct($tokens)
        {
            $this->tokens = $tokens;
        }

        public function onKernelController(ControllerEvent $event)
        {
            $controller = $event->getController();

            // when a controller class defines multiple action methods, the controller
            // is returned as [$controllerInstance, 'methodName']
            if (is_array($controller)) {
                $controller = $controller[0];
            }

            if ($controller instanceof TokenAuthenticatedController) {
                $token = $event->getRequest()->query->get('token');
                if (!in_array($token, $this->tokens)) {
                    throw new AccessDeniedHttpException('This action needs a valid token!');
                }
            }
        }

        public static function getSubscribedEvents()
        {
            return [
                KernelEvents::CONTROLLER => 'onKernelController',
            ];
        }
    }

That's it! Your ``services.yaml`` file should already be setup to load services from
the ``EventSubscriber`` directory. Symfony takes care of the rest. Your
``TokenSubscriber`` ``onKernelController()`` method will be executed on each request.
If the controller that is about to be executed implements ``TokenAuthenticatedController``,
token authentication is applied. This lets you have a "before" filter on any controller
you want.

.. tip::

    If your subscriber is *not* called on each request, double-check that
    you are :ref:`loading services <service-container-services-load-example>` from
    the ``EventSubscriber`` directory and have :ref:`autoconfigure <services-autoconfigure>`
    enabled. You can also manually add the ``kernel.event_subscriber`` tag.

After Filters with the ``kernel.response`` Event
------------------------------------------------

In addition to having a "hook" that's executed *before* your controller, you
can also add a hook that's executed *after* your controller. For this example,
imagine that you want to add a ``sha1`` hash (with a salt using that token) to
all responses that have passed this token authentication.

Another core Symfony event - called ``kernel.response`` (aka ``KernelEvents::RESPONSE``) -
is notified on every request, but after the controller returns a Response object.
To create an "after" listener, create a listener class and register
it as a service on this event.

For example, take the ``TokenSubscriber`` from the previous example and first
record the authentication token inside the request attributes. This will
serve as a basic flag that this request underwent token authentication::

    public function onKernelController(ControllerEvent $event)
    {
        // ...

        if ($controller instanceof TokenAuthenticatedController) {
            $token = $event->getRequest()->query->get('token');
            if (!in_array($token, $this->tokens)) {
                throw new AccessDeniedHttpException('This action needs a valid token!');
            }

            // mark the request as having passed token authentication
            $event->getRequest()->attributes->set('auth_token', $token);
        }
    }

Now, configure the subscriber to listen to another event and add ``onKernelResponse()``.
This will look for the ``auth_token`` flag on the request object and set a custom
header on the response if it is found::

    // add the new use statement at the top of your file
    use Symfony\Component\HttpKernel\Event\ResponseEvent;

    public function onKernelResponse(ResponseEvent $event)
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

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

That's it! The ``TokenSubscriber`` is now notified before every controller is
executed (``onKernelController()``) and after every controller returns a response
(``onKernelResponse()``). By making specific controllers implement the ``TokenAuthenticatedController``
interface, your listener knows which controllers it should take action on.
And by storing a value in the request's "attributes" bag, the ``onKernelResponse()``
method knows to add the extra header. Have fun!
