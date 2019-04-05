.. index::
   single: HTTP
   single: HttpKernel
   single: Components; HttpKernel

The HttpKernel Component
========================

    The HttpKernel component provides a structured process for converting
    a ``Request`` into a ``Response`` by making use of the EventDispatcher
    component. It's flexible enough to create a full-stack framework (Symfony),
    a micro-framework (Silex) or an advanced CMS system (Drupal).

Installation
------------

.. code-block:: terminal

    $ composer require symfony/http-kernel

Alternatively, you can clone the `<https://github.com/symfony/http-kernel>`_ repository.

.. include:: /components/require_autoload.rst.inc

The Workflow of a Request
-------------------------

.. seealso::

    This article explains how to use the HttpKernel features as an independent
    component in any PHP application. In Symfony applications everything is
    already configured and ready to use. Read the :doc:`/controller` and
    :doc:`/event_dispatcher` articles to learn about how to use it to create
    controllers and define events in Symfony applications.


Every HTTP web interaction begins with a request and ends with a response.
Your job as a developer is to create PHP code that reads the request information
(e.g. the URL) and creates and returns a response (e.g. an HTML page or JSON string).
This is a simplified overview of the request workflow in Symfony applications:

#. The **user** asks for a **resource** in a **browser**;
#. The **browser** sends a **request** to the **server**;
#. **Symfony** gives the **application** a **Request** object;
#. The **application** generates a **Response** object using the data of the **Request** object;
#. The **server** sends back the **response** to the **browser**;
#. The **browser** displays the **resource** to the **user**.

Typically, some sort of framework or system is built to handle all the repetitive
tasks (e.g. routing, security, etc) so that a developer can build each *page* of
the application. Exactly *how* these systems are built varies greatly. The HttpKernel
component provides an interface that formalizes the process of starting with a
request and creating the appropriate response. The component is meant to be the
heart of any application or framework, no matter how varied the architecture of
that system::

    namespace Symfony\Component\HttpKernel;

    use Symfony\Component\HttpFoundation\Request;

    interface HttpKernelInterface
    {
        // ...

        /**
         * @return Response A Response instance
         */
        public function handle(
            Request $request,
            $type = self::MASTER_REQUEST,
            $catch = true
        );
    }

Internally, :method:`HttpKernel::handle() <Symfony\\Component\\HttpKernel\\HttpKernel::handle>` -
the concrete implementation of :method:`HttpKernelInterface::handle() <Symfony\\Component\\HttpKernel\\HttpKernelInterface::handle>` -
defines a workflow that starts with a :class:`Symfony\\Component\\HttpFoundation\\Request`
and ends with a :class:`Symfony\\Component\\HttpFoundation\\Response`.

.. raw:: html

    <object data="../_images/components/http_kernel/http-workflow.svg" type="image/svg+xml"></object>

The exact details of this workflow are the key to understanding how the kernel
(and the Symfony Framework or any other library that uses the kernel) works.

HttpKernel: Driven by Events
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``HttpKernel::handle()`` method works internally by dispatching events.
This makes the method both flexible, but also a bit abstract, since all the
"work" of a framework/application built with HttpKernel is actually done
in event listeners.

To help explain this process, this document looks at each step of the process
and talks about how one specific implementation of the HttpKernel - the Symfony
Framework - works.

Initially, using the :class:`Symfony\\Component\\HttpKernel\\HttpKernel` does
not take many steps. You create an
:doc:`event dispatcher </components/event_dispatcher>` and a
:ref:`controller and argument resolver <component-http-kernel-resolve-controller>`
(explained below). To complete your working kernel, you'll add more event
listeners to the events discussed below::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\HttpKernel;
    use Symfony\Component\EventDispatcher\EventDispatcher;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
    use Symfony\Component\HttpKernel\Controller\ControllerResolver;

    // create the Request object
    $request = Request::createFromGlobals();

    $dispatcher = new EventDispatcher();
    // ... add some event listeners

    // create your controller and argument resolvers
    $controllerResolver = new ControllerResolver();
    $argumentResolver = new ArgumentResolver();

    // instantiate the kernel
    $kernel = new HttpKernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);

    // actually execute the kernel, which turns the request into a response
    // by dispatching events, calling a controller, and returning the response
    $response = $kernel->handle($request);

    // send the headers and echo the content
    $response->send();

    // trigger the kernel.terminate event
    $kernel->terminate($request, $response);

See ":ref:`http-kernel-working-example`" for a more concrete implementation.

For general information on adding listeners to the events below, see
:ref:`http-kernel-creating-listener`.

.. caution::

    As of 3.1 the :class:`Symfony\\Component\\HttpKernel\\HttpKernel` accepts a
    fourth argument, which must be an instance of
    :class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolverInterface`.
    In 4.0 this argument will become mandatory.

.. seealso::

    There is a wonderful tutorial series on using the HttpKernel component and
    other Symfony components to create your own framework. See
    :doc:`/create_framework/introduction`.

.. _component-http-kernel-kernel-request:

1) The ``kernel.request`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: To add more information to the ``Request``, initialize
parts of the system, or return a ``Response`` if possible (e.g. a security
layer that denies access).

:ref:`Kernel Events Information Table <component-http-kernel-event-table>`

The first event that is dispatched inside :method:`HttpKernel::handle <Symfony\\Component\\HttpKernel\\HttpKernel::handle>`
is ``kernel.request``, which may have a variety of different listeners.

Listeners of this event can be quite varied. Some listeners - such as a security
listener - might have enough information to create a ``Response`` object immediately.
For example, if a security listener determined that a user doesn't have access,
that listener may return a :class:`Symfony\\Component\\HttpFoundation\\RedirectResponse`
to the login page or a 403 Access Denied response.

If a ``Response`` is returned at this stage, the process skips directly to
the :ref:`kernel.response <component-http-kernel-kernel-response>` event.

Other listeners initialize things or add more information to the request.
For example, a listener might determine and set the locale on the ``Request``
object.

Another common listener is routing. A router listener may process the ``Request``
and determine the controller that should be rendered (see the next section).
In fact, the ``Request`` object has an ":ref:`attributes <component-foundation-attributes>`"
bag which is a perfect spot to store this extra, application-specific data
about the request. This means that if your router listener somehow determines
the controller, it can store it on the ``Request`` attributes (which can be used
by your controller resolver).

Overall, the purpose of the ``kernel.request`` event is either to create and
return a ``Response`` directly, or to add information to the ``Request``
(e.g. setting the locale or setting some other information on the ``Request``
attributes).

.. note::

    When setting a response for the ``kernel.request`` event, the propagation
    is stopped. This means listeners with lower priority won't be executed.

.. sidebar:: ``kernel.request`` in the Symfony Framework

    The most important listener to ``kernel.request`` in the Symfony Framework
    is the :class:`Symfony\\Component\\HttpKernel\\EventListener\\RouterListener`.
    This class executes the routing layer, which returns an *array* of information
    about the matched request, including the ``_controller`` and any placeholders
    that are in the route's pattern (e.g. ``{slug}``). See
    :doc:`Routing component </components/routing>`.

    This array of information is stored in the :class:`Symfony\\Component\\HttpFoundation\\Request`
    object's ``attributes`` array. Adding the routing information here doesn't
    do anything yet, but is used next when resolving the controller.

.. _component-http-kernel-resolve-controller:

2) Resolve the Controller
~~~~~~~~~~~~~~~~~~~~~~~~~

Assuming that no ``kernel.request`` listener was able to create a ``Response``,
the next step in HttpKernel is to determine and prepare (i.e. resolve) the
controller. The controller is the part of the end-application's code that
is responsible for creating and returning the ``Response`` for a specific page.
The only requirement is that it is a PHP callable - i.e. a function, method
on an object or a ``Closure``.

But *how* you determine the exact controller for a request is entirely up
to your application. This is the job of the "controller resolver" - a class
that implements :class:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface`
and is one of the constructor arguments to ``HttpKernel``.

Your job is to create a class that implements the interface and fill in its
method: ``getController()``. In fact, one default implementation already
exists, which you can use directly or learn from:
:class:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolver`.
This implementation is explained more in the sidebar below::

    namespace Symfony\Component\HttpKernel\Controller;

    use Symfony\Component\HttpFoundation\Request;

    interface ControllerResolverInterface
    {
        public function getController(Request $request);
    }

Internally, the ``HttpKernel::handle()`` method first calls
:method:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface::getController`
on the controller resolver. This method is passed the ``Request`` and is responsible
for somehow determining and returning a PHP callable (the controller) based
on the request's information.

.. sidebar:: Resolving the Controller in the Symfony Framework

    The Symfony Framework uses the built-in
    :class:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolver`
    class (actually, it uses a sub-class with some extra functionality
    mentioned below). This class leverages the information that was placed
    on the ``Request`` object's ``attributes`` property during the ``RouterListener``.

    **getController**

    The ``ControllerResolver`` looks for a ``_controller``
    key on the ``Request`` object's attributes property (recall that this
    information is typically placed on the ``Request`` via the ``RouterListener``).
    This string is then transformed into a PHP callable by doing the following:

    a) If the ``_controller`` key doesn't follow the recommended PHP namespace
       format (e.g. ``App\Controller\DefaultController::index``) its format is
       transformed into it. For example, the legacy ``FooBundle:Default:index``
       format would be changed to ``Acme\FooBundle\Controller\DefaultController::indexAction``.
       This transformation is specific to the :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerResolver`
       sub-class used by the Symfony Framework.

    b) A new instance of your controller class is instantiated with no
       constructor arguments.

    c) If the controller implements :class:`Symfony\\Component\\DependencyInjection\\ContainerAwareInterface`,
       ``setContainer()`` is called on the controller object and the container
       is passed to it. This step is also specific to the  :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerResolver`
       sub-class used by the Symfony Framework.

.. _component-http-kernel-kernel-controller:

3) The ``kernel.controller`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: Initialize things or change the controller just before
the controller is executed.

:ref:`Kernel Events Information Table <component-http-kernel-event-table>`

After the controller callable has been determined, ``HttpKernel::handle()``
dispatches the ``kernel.controller`` event. Listeners to this event might initialize
some part of the system that needs to be initialized after certain things
have been determined (e.g. the controller, routing information) but before
the controller is executed. For some examples, see the Symfony section below.

Listeners to this event can also change the controller callable completely
by calling :method:`FilterControllerEvent::setController <Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent::setController>`
on the event object that's passed to listeners on this event.

.. sidebar:: ``kernel.controller`` in the Symfony Framework

    There are a few minor listeners to the ``kernel.controller`` event in
    the Symfony Framework, and many deal with collecting profiler data when
    the profiler is enabled.

    One interesting listener comes from the `SensioFrameworkExtraBundle`_. This
    listener's `@ParamConverter`_ functionality allows you to pass a full object
    (e.g. a ``Post`` object) to your controller instead of a scalar value (e.g.
    an ``id`` parameter that was on your route). The listener -
    ``ParamConverterListener`` - uses reflection to look at each of the
    arguments of the controller and tries to use different methods to convert
    those to objects, which are then stored in the ``attributes`` property of
    the ``Request`` object. Read the next section to see why this is important.

4) Getting the Controller Arguments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Next, ``HttpKernel::handle()`` calls
:method:`ArgumentResolverInterface::getArguments() <Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolverInterface::getArguments>`.
Remember that the controller returned in ``getController()`` is a callable.
The purpose of ``getArguments()`` is to return the array of arguments that
should be passed to that controller. Exactly how this is done is completely
up to your design, though the built-in :class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver`
is a good example.

At this point the kernel has a PHP callable (the controller) and an array
of arguments that should be passed when executing that callable.

.. sidebar:: Getting the Controller Arguments in the Symfony Framework

    Now that you know exactly what the controller callable (usually a method
    inside a controller object) is, the ``ArgumentResolver`` uses `reflection`_
    on the callable to return an array of the *names* of each of the arguments.
    It then iterates over each of these arguments and uses the following tricks
    to determine which value should be passed for each argument:

    a) If the ``Request`` attributes bag contains a key that matches the name
       of the argument, that value is used. For example, if the first argument
       to a controller is ``$slug`` and there is a ``slug`` key in the ``Request``
       ``attributes`` bag, that value is used (and typically this value came
       from the ``RouterListener``).

    b) If the argument in the controller is type-hinted with Symfony's
       :class:`Symfony\\Component\\HttpFoundation\\Request` object, the
       ``Request`` is passed in as the value.

    c) If the function or method argument is `variadic`_ and the ``Request``
       ``attributes`` bag contains an array for that argument, they will all be
       available through the `variadic`_ argument.

    This functionality is provided by resolvers implementing the
    :class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolverInterface`.
    There are four implementations which provide the default behavior of
    Symfony but customization is the key here. By implementing the
    ``ArgumentValueResolverInterface`` yourself and passing this to the
    ``ArgumentResolver``, you can extend this functionality.

.. _component-http-kernel-calling-controller:

5) Calling the Controller
~~~~~~~~~~~~~~~~~~~~~~~~~

The next step ``HttpKernel::handle()`` does is executing the controller.

The job of the controller is to build the response for the given resource.
This could be an HTML page, a JSON string or anything else. Unlike every
other part of the process so far, this step is implemented by the "end-developer",
for each page that is built.

Usually, the controller will return a ``Response`` object. If this is true,
then the work of the kernel is just about done! In this case, the next step
is the :ref:`kernel.response <component-http-kernel-kernel-response>` event.

But if the controller returns anything besides a ``Response``, then the kernel
has a little bit more work to do - :ref:`kernel.view <component-http-kernel-kernel-view>`
(since the end goal is *always* to generate a ``Response`` object).

.. note::

    A controller must return *something*. If a controller returns ``null``,
    an exception will be thrown immediately.

.. _component-http-kernel-kernel-view:

6) The ``kernel.view`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: Transform a non-``Response`` return value from a controller
into a ``Response``

:ref:`Kernel Events Information Table <component-http-kernel-event-table>`

If the controller doesn't return a ``Response`` object, then the kernel dispatches
another event - ``kernel.view``. The job of a listener to this event is to
use the return value of the controller (e.g. an array of data or an object)
to create a ``Response``.

This can be useful if you want to use a "view" layer: instead of returning
a ``Response`` from the controller, you return data that represents the page.
A listener to this event could then use this data to create a ``Response`` that
is in the correct format (e.g HTML, JSON, etc).

At this stage, if no listener sets a response on the event, then an exception
is thrown: either the controller *or* one of the view listeners must always
return a ``Response``.

.. note::

    When setting a response for the ``kernel.view`` event, the propagation
    is stopped. This means listeners with lower priority won't be executed.

.. sidebar:: ``kernel.view`` in the Symfony Framework

    There is no default listener inside the Symfony Framework for the ``kernel.view``
    event. However, `SensioFrameworkExtraBundle`_ *does* add a listener to this
    event. If your controller returns an array, and you place the `@Template`_
    annotation above the controller, then this listener renders a template,
    passes the array you returned from your controller to that template, and
    creates a ``Response`` containing the returned content from that template.

    Additionally, a popular community bundle `FOSRestBundle`_ implements
    a listener on this event which aims to give you a robust view layer
    capable of using a single controller to return many different content-type
    responses (e.g. HTML, JSON, XML, etc).

.. _component-http-kernel-kernel-response:

7) The ``kernel.response`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: Modify the ``Response`` object just before it is sent

:ref:`Kernel Events Information Table <component-http-kernel-event-table>`

The end goal of the kernel is to transform a ``Request`` into a ``Response``. The
``Response`` might be created during the :ref:`kernel.request <component-http-kernel-kernel-request>`
event, returned from the :ref:`controller <component-http-kernel-calling-controller>`,
or returned by one of the listeners to the :ref:`kernel.view <component-http-kernel-kernel-view>`
event.

Regardless of who creates the ``Response``, another event - ``kernel.response``
is dispatched directly afterwards. A typical listener to this event will modify
the ``Response`` object in some way, such as modifying headers, adding cookies,
or even changing the content of the ``Response`` itself (e.g. injecting some
JavaScript before the end ``</body>`` tag of an HTML response).

After this event is dispatched, the final ``Response`` object is returned
from :method:`Symfony\\Component\\HttpKernel\\HttpKernel::handle`. In the
most typical use-case, you can then call the :method:`Symfony\\Component\\HttpFoundation\\Response::send`
method, which sends the headers and prints the ``Response`` content.

.. sidebar:: ``kernel.response`` in the Symfony Framework

    There are several minor listeners on this event inside the Symfony Framework,
    and most modify the response in some way. For example, the
    :class:`Symfony\\Bundle\\WebProfilerBundle\\EventListener\\WebDebugToolbarListener`
    injects some JavaScript at the bottom of your page in the ``dev`` environment
    which causes the web debug toolbar to be displayed. Another listener,
    :class:`Symfony\\Component\\Security\\Http\\Firewall\\ContextListener`
    serializes the current user's information into the
    session so that it can be reloaded on the next request.

.. _component-http-kernel-kernel-terminate:

8) The ``kernel.terminate`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: To perform some "heavy" action after the response has
been streamed to the user

:ref:`Kernel Events Information Table <component-http-kernel-event-table>`

The final event of the HttpKernel process is ``kernel.terminate`` and is unique
because it occurs *after* the ``HttpKernel::handle()`` method, and after the
response is sent to the user. Recall from above, then the code that uses
the kernel, ends like this::

    // sends the headers and echoes the content
    $response->send();

    // triggers the kernel.terminate event
    $kernel->terminate($request, $response);

As you can see, by calling ``$kernel->terminate`` after sending the response,
you will trigger the ``kernel.terminate`` event where you can perform certain
actions that you may have delayed in order to return the response as quickly
as possible to the client (e.g. sending emails).

.. caution::

    Internally, the HttpKernel makes use of the :phpfunction:`fastcgi_finish_request`
    PHP function. This means that at the moment, only the `PHP FPM`_ server
    API is able to send a response to the client while the server's PHP process
    still performs some tasks. With all other server APIs, listeners to ``kernel.terminate``
    are still executed, but the response is not sent to the client until they
    are all completed.

.. note::

    Using the ``kernel.terminate`` event is optional, and should only be
    called if your kernel implements :class:`Symfony\\Component\\HttpKernel\\TerminableInterface`.

.. sidebar:: ``kernel.terminate`` in the Symfony Framework

    If you use the :ref:`memory spooling <email-spool-memory>` option of the
    default Symfony mailer, then the `EmailSenderListener`_ is activated, which
    actually delivers any emails that you scheduled to send during the request.

.. _component-http-kernel-kernel-exception:

Handling Exceptions: the ``kernel.exception`` Event
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Typical Purposes**: Handle some type of exception and create an appropriate
``Response`` to return for the exception

:ref:`Kernel Events Information Table <component-http-kernel-event-table>`

If an exception is thrown at any point inside ``HttpKernel::handle()``, another
event - ``kernel.exception`` is thrown. Internally, the body of the ``handle()``
function is wrapped in a try-catch block. When any exception is thrown, the
``kernel.exception`` event is dispatched so that your system can somehow respond
to the exception.

.. raw:: html

    <object data="../_images/components/http_kernel/http-workflow-exception.svg" type="image/svg+xml"></object>

Each listener to this event is passed a :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent`
object, which you can use to access the original exception via the
:method:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent::getException`
method. A typical listener on this event will check for a certain type of
exception and create an appropriate error ``Response``.

For example, to generate a 404 page, you might throw a special type of exception
and then add a listener on this event that looks for this exception and
creates and returns a 404 ``Response``. In fact, the HttpKernel component
comes with an :class:`Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener`,
which if you choose to use, will do this and more by default (see the sidebar
below for more details).

.. note::

    When setting a response for the ``kernel.exception`` event, the propagation
    is stopped. This means listeners with lower priority won't be executed.

.. sidebar:: ``kernel.exception`` in the Symfony Framework

    There are two main listeners to ``kernel.exception`` when using the
    Symfony Framework.

    **ExceptionListener in the HttpKernel Component**

    The first comes core to the HttpKernel component
    and is called :class:`Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener`.
    The listener has several goals:

    1) The thrown exception is converted into a
       :class:`Symfony\\Component\\Debug\\Exception\\FlattenException`
       object, which contains all the information about the request, but which
       can be printed and serialized.

    2) If the original exception implements
       :class:`Symfony\\Component\\HttpKernel\\Exception\\HttpExceptionInterface`,
       then ``getStatusCode()`` and ``getHeaders()`` are called on the exception
       and used to populate the headers and status code of the ``FlattenException``
       object. The idea is that these are used in the next step when creating
       the final response. If you want to set custom HTTP headers, you can always
       use the ``setHeaders()`` method on exceptions derived from the
       :class:`Symfony\\Component\\HttpKernel\\Exception\\HttpException` class.

    3) If the original exception implements
       :class:`Symfony\\Component\\HttpFoundation\\Exception\\RequestExceptionInterface`,
       then the status code of the ``FlattenException`` object is populated with
       ``400`` and no other headers are modified.

    4) A controller is executed and passed the flattened exception. The exact
       controller to render is passed as a constructor argument to this listener.
       This controller will return the final ``Response`` for this error page.

    **ExceptionListener in the Security Component**

    The other important listener is the
    :class:`Symfony\\Component\\Security\\Http\\Firewall\\ExceptionListener`.
    The goal of this listener is to handle security exceptions and, when
    appropriate, *help* the user to authenticate (e.g. redirect to the login
    page).

.. _http-kernel-creating-listener:

Creating an Event Listener
--------------------------

As you've seen, you can create and attach event listeners to any of the events
dispatched during the ``HttpKernel::handle()`` cycle. Typically a listener is a PHP
class with a method that's executed, but it can be anything. For more information
on creating and attaching event listeners, see :doc:`/components/event_dispatcher`.

The name of each of the "kernel" events is defined as a constant on the
:class:`Symfony\\Component\\HttpKernel\\KernelEvents` class. Additionally, each
event listener is passed a single argument, which is some sub-class of :class:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent`.
This object contains information about the current state of the system and
each event has their own event object:

.. _component-http-kernel-event-table:

===========================  ======================================  ===================================================================================
Name                         ``KernelEvents`` Constant               Argument passed to the listener
===========================  ======================================  ===================================================================================
kernel.request               ``KernelEvents::REQUEST``               :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent`
kernel.controller            ``KernelEvents::CONTROLLER``            :class:`Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent`
kernel.controller_arguments  ``KernelEvents::CONTROLLER_ARGUMENTS``  :class:`Symfony\\Component\\HttpKernel\\Event\\FilterControllerArgumentsEvent`
kernel.view                  ``KernelEvents::VIEW``                  :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForControllerResultEvent`
kernel.response              ``KernelEvents::RESPONSE``              :class:`Symfony\\Component\\HttpKernel\\Event\\FilterResponseEvent`
kernel.finish_request        ``KernelEvents::FINISH_REQUEST``        :class:`Symfony\\Component\\HttpKernel\\Event\\FinishRequestEvent`
kernel.terminate             ``KernelEvents::TERMINATE``             :class:`Symfony\\Component\\HttpKernel\\Event\\PostResponseEvent`
kernel.exception             ``KernelEvents::EXCEPTION``             :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent`
===========================  ======================================  ===================================================================================

.. _http-kernel-working-example:

A full Working Example
----------------------

When using the HttpKernel component, you're free to attach any listeners
to the core events, use any controller resolver that implements the
:class:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface` and
use any argument resolver that implements the
:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolverInterface`.
However, the HttpKernel component comes with some built-in listeners and everything
else that can be used to create a working example::

    use Symfony\Component\EventDispatcher\EventDispatcher;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
    use Symfony\Component\HttpKernel\Controller\ControllerResolver;
    use Symfony\Component\HttpKernel\EventListener\RouterListener;
    use Symfony\Component\HttpKernel\HttpKernel;
    use Symfony\Component\Routing\Matcher\UrlMatcher;
    use Symfony\Component\Routing\RequestContext;
    use Symfony\Component\Routing\Route;
    use Symfony\Component\Routing\RouteCollection;

    $routes = new RouteCollection();
    $routes->add('hello', new Route('/hello/{name}', [
        '_controller' => function (Request $request) {
            return new Response(
                sprintf("Hello %s", $request->get('name'))
            );
        }]
    ));

    $request = Request::createFromGlobals();

    $matcher = new UrlMatcher($routes, new RequestContext());

    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));

    $controllerResolver = new ControllerResolver();
    $argumentResolver = new ArgumentResolver();

    $kernel = new HttpKernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);

    $response = $kernel->handle($request);
    $response->send();

    $kernel->terminate($request, $response);

.. _http-kernel-sub-requests:

Sub Requests
------------

In addition to the "main" request that's sent into ``HttpKernel::handle()``,
you can also send so-called "sub request". A sub request looks and acts like
any other request, but typically serves to render just one small portion of
a page instead of a full page. You'll most commonly make sub-requests from
your controller (or perhaps from inside a template, that's being rendered by
your controller).

.. raw:: html

    <object data="../_images/components/http_kernel/http-workflow-subrequest.svg" type="image/svg+xml"></object>

To execute a sub request, use ``HttpKernel::handle()``, but change the second
argument as follows::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\HttpKernelInterface;

    // ...

    // create some other request manually as needed
    $request = new Request();
    // for example, possibly set its _controller manually
    $request->attributes->set('_controller', '...');

    $response = $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    // do something with this response

This creates another full request-response cycle where this new ``Request`` is
transformed into a ``Response``. The only difference internally is that some
listeners (e.g. security) may only act upon the master request. Each listener
is passed some sub-class of :class:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent`,
whose :method:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent::isMasterRequest`
can be used to check if the current request is a "master" or "sub" request.

For example, a listener that only needs to act on the master request may
look like this::

    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    // ...

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        // ...
    }

.. _http-kernel-resource-locator:

Locating Resources
------------------

The HttpKernel component is responsible of the bundle mechanism used in Symfony
applications. The key feature of the bundles is that they allow to override any
resource used by the application (config files, templates, controllers,
translation files, etc.)

This overriding mechanism works because resources are referenced not by their
physical path but by their logical path. For example, the ``services.xml`` file
stored in the ``Resources/config/`` directory of a bundle called FooBundle is
referenced as ``@FooBundle/Resources/config/services.xml``. This logical path
will work when the application overrides that file and even if you change the
directory of FooBundle.

The HttpKernel component provides a method called :method:`Symfony\\Component\\HttpKernel\\Kernel::locateResource`
which can be used to transform logical paths into physical paths::

    use Symfony\Component\HttpKernel\HttpKernel;

    // ...
    $kernel = new HttpKernel($dispatcher, $resolver);
    $path = $kernel->locateResource('@FooBundle/Resources/config/services.xml');

Learn more
----------

.. toctree::
   :maxdepth: 1
   :glob:

   /reference/events

.. _Packagist: https://packagist.org/packages/symfony/http-kernel
.. _reflection: https://php.net/manual/en/book.reflection.php
.. _FOSRestBundle: https://github.com/friendsofsymfony/FOSRestBundle
.. _`Create your own framework... on top of the Symfony2 Components`: http://fabien.potencier.org/article/50/create-your-own-framework-on-top-of-the-symfony2-components-part-1
.. _`PHP FPM`: https://php.net/manual/en/install.fpm.php
.. _`SensioFrameworkExtraBundle`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html
.. _`@ParamConverter`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
.. _`@Template`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/view.html
.. _`EmailSenderListener`: https://github.com/symfony/swiftmailer-bundle/blob/master/EventListener/EmailSenderListener.php
.. _variadic: http://php.net/manual/en/functions.arguments.php
