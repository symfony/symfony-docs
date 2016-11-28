.. index::
   single: Internals

Internals
=========

Looks like you want to understand how Symfony works and how to extend it.
That makes me very happy! This section is an in-depth explanation of the
Symfony internals.

.. note::

    You only need to read this section if you want to understand how Symfony
    works behind the scenes, or if you want to extend Symfony.

Overview
--------

The Symfony code is made of several independent layers. Each layer is built
on top of the previous one.

.. tip::

    Autoloading is not managed by the framework directly; it's done by using
    Composer's autoloader (``vendor/autoload.php``), which is included in
    the ``app/autoload.php`` file.

HttpFoundation Component
~~~~~~~~~~~~~~~~~~~~~~~~

The deepest level is the :namespace:`Symfony\\Component\\HttpFoundation`
component. HttpFoundation provides the main objects needed to deal with HTTP.
It is an object-oriented abstraction of some native PHP functions and
variables:

* The :class:`Symfony\\Component\\HttpFoundation\\Request` class abstracts
  the main PHP global variables like ``$_GET``, ``$_POST``, ``$_COOKIE``,
  ``$_FILES``, and ``$_SERVER``;

* The :class:`Symfony\\Component\\HttpFoundation\\Response` class abstracts
  some PHP functions like ``header()``, ``setcookie()``, and ``echo``;

* The :class:`Symfony\\Component\\HttpFoundation\\Session` class and
  :class:`Symfony\\Component\\HttpFoundation\\SessionStorage\\SessionStorageInterface`
  interface abstract session management ``session_*()`` functions.

.. note::

    Read more about the :doc:`HttpFoundation component </components/http_foundation/introduction>`.

HttpKernel Component
~~~~~~~~~~~~~~~~~~~~

On top of HttpFoundation is the :namespace:`Symfony\\Component\\HttpKernel`
component. HttpKernel handles the dynamic part of HTTP; it is a thin wrapper
on top of the Request and Response classes to standardize the way requests are
handled. It also provides extension points and tools that makes it the ideal
starting point to create a Web framework without too much overhead.

It also optionally adds configurability and extensibility, thanks to the
DependencyInjection component and a powerful plugin system (bundles).

.. seealso::

    Read more about the :doc:`HttpKernel component </components/http_kernel/introduction>`,
    :doc:`Dependency Injection </book/service_container>` and
    :doc:`Bundles </cookbook/bundles/best_practices>`.

FrameworkBundle
~~~~~~~~~~~~~~~

The :namespace:`Symfony\\Bundle\\FrameworkBundle` bundle is the bundle that
ties the main components and libraries together to make a lightweight and fast
MVC framework. It comes with a sensible default configuration and conventions
to ease the learning curve.

.. index::
   single: Internals; Kernel

Kernel
------

The :class:`Symfony\\Component\\HttpKernel\\HttpKernel` class is the central
class of Symfony and is responsible for handling client requests. Its main
goal is to "convert" a :class:`Symfony\\Component\\HttpFoundation\\Request`
object to a :class:`Symfony\\Component\\HttpFoundation\\Response` object.

Every Symfony Kernel implements
:class:`Symfony\\Component\\HttpKernel\\HttpKernelInterface`::

    function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)

.. index::
   single: Internals; Controller resolver

Controllers
~~~~~~~~~~~

To convert a Request to a Response, the Kernel relies on a "Controller". A
Controller can be any valid PHP callable.

The Kernel delegates the selection of what Controller should be executed
to an implementation of
:class:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface`::

    public function getController(Request $request);

    public function getArguments(Request $request, $controller);

The
:method:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface::getController`
method returns the Controller (a PHP callable) associated with the given
Request. The default implementation
(:class:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolver`)
looks for a ``_controller`` request attribute that represents the controller
name (a "class::method" string, like ``Bundle\BlogBundle\PostController:indexAction``).

.. tip::

    The default implementation uses the
    :class:`Symfony\\Bundle\\FrameworkBundle\\EventListener\\RouterListener`
    to define the ``_controller`` Request attribute (see :ref:`kernel-core-request`).

The
:method:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface::getArguments`
method returns an array of arguments to pass to the Controller callable. The
default implementation automatically resolves the method arguments, based on
the Request attributes.

.. sidebar:: Matching Controller Method Arguments from Request Attributes

    For each method argument, Symfony tries to get the value of a Request
    attribute with the same name. If it is not defined, the argument default
    value is used if defined::

        // Symfony will look for an 'id' attribute (mandatory)
        // and an 'admin' one (optional)
        public function showAction($id, $admin = true)
        {
            // ...
        }

.. index::
  single: Internals; Request handling

Handling Requests
~~~~~~~~~~~~~~~~~

The :method:`Symfony\\Component\\HttpKernel\\HttpKernel::handle` method
takes a ``Request`` and *always* returns a ``Response``. To convert the
``Request``, ``handle()`` relies on the Resolver and an ordered chain of
Event notifications (see the next section for more information about each
Event):

#. Before doing anything else, the ``kernel.request`` event is notified -- if
   one of the listeners returns a ``Response``, it jumps to step 8 directly;

#. The Resolver is called to determine the Controller to execute;

#. Listeners of the ``kernel.controller`` event can now manipulate the
   Controller callable the way they want (change it, wrap it, ...);

#. The Kernel checks that the Controller is actually a valid PHP callable;

#. The Resolver is called to determine the arguments to pass to the Controller;

#. The Kernel calls the Controller;

#. If the Controller does not return a ``Response``, listeners of the
   ``kernel.view`` event can convert the Controller return value to a ``Response``;

#. Listeners of the ``kernel.response`` event can manipulate the ``Response``
   (content and headers);

#. The Response is returned;

#. Listeners of the ``kernel.terminate`` event can perform tasks after the
   Response has been served.

If an Exception is thrown during processing, the ``kernel.exception`` is
notified and listeners are given a chance to convert the Exception to a
Response. If that works, the ``kernel.response`` event is notified; if not, the
Exception is re-thrown.

If you don't want Exceptions to be caught (for embedded requests for
instance), disable the ``kernel.exception`` event by passing ``false`` as the
third argument to the ``handle()`` method.

.. index::
  single: Internals; Internal requests

Internal Requests
~~~~~~~~~~~~~~~~~

At any time during the handling of a request (the 'master' one), a sub-request
can be handled. You can pass the request type to the ``handle()`` method (its
second argument):

* ``HttpKernelInterface::MASTER_REQUEST``;
* ``HttpKernelInterface::SUB_REQUEST``.

The type is passed to all events and listeners can act accordingly (some
processing must only occur on the master request).

.. index::
   pair: Kernel; Event

Events
~~~~~~

Each event thrown by the Kernel is a subclass of
:class:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent`. This means that
each event has access to the same basic information:

:method:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent::getRequestType`
    Returns the *type* of the request (``HttpKernelInterface::MASTER_REQUEST`` or
    ``HttpKernelInterface::SUB_REQUEST``).

:method:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent::isMasterRequest`
    Checks if it is a master request.

:method:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent::getKernel`
    Returns the Kernel handling the request.

:method:`Symfony\\Component\\HttpKernel\\Event\\KernelEvent::getRequest`
    Returns the current ``Request`` being handled.

``isMasterRequest()``
.....................

The ``isMasterRequest()`` method allows listeners to check the type of the
request. For instance, if a listener must only be active for master requests,
add the following code at the beginning of your listener method::

    use Symfony\Component\HttpKernel\HttpKernelInterface;

    if (!$event->isMasterRequest()) {
        // return immediately
        return;
    }

.. tip::

    If you are not yet familiar with the Symfony EventDispatcher, read the
    :doc:`EventDispatcher component documentation </components/event_dispatcher/introduction>`
    section first.

.. index::
   single: Event; kernel.request

.. _kernel-core-request:

``kernel.request`` Event
........................

*Event Class*: :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent`

The goal of this event is to either return a ``Response`` object immediately
or setup variables so that a Controller can be called after the event. Any
listener can return a ``Response`` object via the ``setResponse()`` method on
the event. In this case, all other listeners won't be called.

This event is used by the FrameworkBundle to populate the ``_controller``
``Request`` attribute, via the
:class:`Symfony\\Bundle\\FrameworkBundle\\EventListener\\RouterListener`. RequestListener
uses a :class:`Symfony\\Component\\Routing\\RouterInterface` object to match
the ``Request`` and determine the Controller name (stored in the
``_controller`` ``Request`` attribute).

.. seealso::

    Read more on the :ref:`kernel.request event <component-http-kernel-kernel-request>`.

.. index::
   single: Event; kernel.controller

``kernel.controller`` Event
...........................

*Event Class*: :class:`Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent`

This event is not used by the FrameworkBundle, but can be an entry point used
to modify the controller that should be executed::

    use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        // ...

        // the controller can be changed to any PHP callable
        $event->setController($controller);
    }

.. seealso::

    Read more on the :ref:`kernel.controller event <component-http-kernel-kernel-controller>`.

.. index::
   single: Event; kernel.view

``kernel.view`` Event
.....................

*Event Class*: :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForControllerResultEvent`

This event is not used by the FrameworkBundle, but it can be used to implement
a view sub-system. This event is called *only* if the Controller does *not*
return a ``Response`` object. The purpose of the event is to allow some other
return value to be converted into a ``Response``.

The value returned by the Controller is accessible via the
``getControllerResult`` method::

    use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
    use Symfony\Component\HttpFoundation\Response;

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $val = $event->getControllerResult();
        $response = new Response();

        // ... some how customize the Response from the return value

        $event->setResponse($response);
    }

.. seealso::

    Read more on the :ref:`kernel.view event <component-http-kernel-kernel-view>`.

.. index::
   single: Event; kernel.response

``kernel.response`` Event
.........................

*Event Class*: :class:`Symfony\\Component\\HttpKernel\\Event\\FilterResponseEvent`

The purpose of this event is to allow other systems to modify or replace the
``Response`` object after its creation::

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        // ... modify the response object
    }

The FrameworkBundle registers several listeners:

:class:`Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener`
    Collects data for the current request.

:class:`Symfony\\Bundle\\WebProfilerBundle\\EventListener\\WebDebugToolbarListener`
    Injects the Web Debug Toolbar.

:class:`Symfony\\Component\\HttpKernel\\EventListener\\ResponseListener`
    Fixes the Response ``Content-Type`` based on the request format.

:class:`Symfony\\Component\\HttpKernel\\EventListener\\EsiListener`
    Adds a ``Surrogate-Control`` HTTP header when the Response needs to be parsed
    for ESI tags.

.. seealso::

    Read more on the :ref:`kernel.response event <component-http-kernel-kernel-response>`.

.. index::
    single: Event; kernel.finish_request

``kernel.finish_request`` Event
...............................

*Event Class*: :class:`Symfony\\Component\\HttpKernel\\Event\\FinishRequestEvent`

The purpose of this event is to handle tasks that should be performed after
the request has been handled but that do not need to modify the response.
Event listeners for the ``kernel.finish_request`` event are called in both
successful and exception cases.

.. index::
   single: Event; kernel.terminate

``kernel.terminate`` Event
..........................

*Event Class*: :class:`Symfony\\Component\\HttpKernel\\Event\\PostResponseEvent`

The purpose of this event is to perform "heavier" tasks after the response
was already served to the client.

.. seealso::

    Read more on the :ref:`kernel.terminate event <component-http-kernel-kernel-terminate>`.

.. index::
   single: Event; kernel.exception

.. _kernel-kernel.exception:

``kernel.exception`` Event
..........................

*Event Class*: :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent`

The FrameworkBundle registers an
:class:`Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener` that
forwards the ``Request`` to a given Controller (the value of the
``exception_listener.controller`` parameter -- must be in the
``class::method`` notation).

A listener on this event can create and set a ``Response`` object, create
and set a new ``Exception`` object, or do nothing::

    use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
    use Symfony\Component\HttpFoundation\Response;

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $response = new Response();
        // setup the Response object based on the caught exception
        $event->setResponse($response);

        // you can alternatively set a new Exception
        // $exception = new \Exception('Some special exception');
        // $event->setException($exception);
    }

.. note::

    As Symfony ensures that the Response status code is set to the most
    appropriate one depending on the exception, setting the status on the
    response won't work. If you want to overwrite the status code (which you
    should not without a good reason), set the ``X-Status-Code`` header::

        return new Response(
            'Error',
            Response::HTTP_NOT_FOUND, // ignored
            array('X-Status-Code' => Response::HTTP_OK)
        );

    .. versionadded:: 2.4
        Support for HTTP status code constants was introduced in Symfony 2.4.

.. seealso::

    Read more on the :ref:`kernel.exception event <component-http-kernel-kernel-exception>`.

.. index::
   single: EventDispatcher

The EventDispatcher
-------------------

The EventDispatcher is a standalone component that is responsible for much
of the underlying logic and flow behind a Symfony request. For more information,
see the :doc:`EventDispatcher component documentation </components/event_dispatcher/introduction>`.

.. index::
   single: Profiler

.. _internals-profiler:

Profiler
--------

When enabled, the Symfony profiler collects useful information about each
request made to your application and store them for later analysis. Use the
profiler in the development environment to help you to debug your code and
enhance performance; use it in the production environment to explore problems
after the fact.

You rarely have to deal with the profiler directly as Symfony provides
visualizer tools like the Web Debug Toolbar and the Web Profiler. If you use
the Symfony Standard Edition, the profiler, the web debug toolbar, and the
web profiler are all already configured with sensible settings.

.. note::

    The profiler collects information for all requests (simple requests,
    redirects, exceptions, Ajax requests, ESI requests; and for all HTTP
    methods and all formats). It means that for a single URL, you can have
    several associated profiling data (one per external request/response
    pair).

.. index::
   single: Profiler; Visualizing

Visualizing Profiling Data
~~~~~~~~~~~~~~~~~~~~~~~~~~

Using the Web Debug Toolbar
...........................

In the development environment, the web debug toolbar is available at the
bottom of all pages. It displays a good summary of the profiling data that
gives you instant access to a lot of useful information when something does
not work as expected.

If the summary provided by the Web Debug Toolbar is not enough, click on the
token link (a string made of 13 random characters) to access the Web Profiler.

.. note::

    If the token is not clickable, it means that the profiler routes are not
    registered (see below for configuration information).

Analyzing Profiling Data with the Web Profiler
..............................................

The Web Profiler is a visualization tool for profiling data that you can use
in development to debug your code and enhance performance; but it can also be
used to explore problems that occur in production. It exposes all information
collected by the profiler in a web interface.

.. index::
   single: Profiler; Using the profiler service

Accessing the Profiling information
...................................

You don't need to use the default visualizer to access the profiling
information. But how can you retrieve profiling information for a specific
request after the fact? When the profiler stores data about a Request, it also
associates a token with it; this token is available in the ``X-Debug-Token``
HTTP header of the Response::

    $profile = $container->get('profiler')->loadProfileFromResponse($response);

    $profile = $container->get('profiler')->loadProfile($token);

.. tip::

    When the profiler is enabled but not the web debug toolbar, or when you
    want to get the token for an Ajax request, use a tool like Firebug to get
    the value of the ``X-Debug-Token`` HTTP header.

Use the :method:`Symfony\\Component\\HttpKernel\\Profiler\\Profiler::find`
method to access tokens based on some criteria::

    // get the latest 10 tokens
    $tokens = $container->get('profiler')->find('', '', 10, '', '');

    // get the latest 10 tokens for all URL containing /admin/
    $tokens = $container->get('profiler')->find('', '/admin/', 10, '', '');

    // get the latest 10 tokens for local requests
    $tokens = $container->get('profiler')->find('127.0.0.1', '', 10, '', '');

    // get the latest 10 tokens for requests that happened between 2 and 4 days ago
    $tokens = $container->get('profiler')
        ->find('', '', 10, '4 days ago', '2 days ago');

If you want to manipulate profiling data on a different machine than the one
where the information were generated, use the
:method:`Symfony\\Component\\HttpKernel\\Profiler\\Profiler::export` and
:method:`Symfony\\Component\\HttpKernel\\Profiler\\Profiler::import` methods::

    // on the production machine
    $profile = $container->get('profiler')->loadProfile($token);
    $data = $profiler->export($profile);

    // on the development machine
    $profiler->import($data);

.. index::
   single: Profiler; Visualizing

Configuration
.............

The default Symfony configuration comes with sensible settings for the
profiler, the web debug toolbar, and the web profiler. Here is for instance
the configuration for the development environment:

.. configuration-block::

    .. code-block:: yaml

        # load the profiler
        framework:
            profiler: { only_exceptions: false }

        # enable the web profiler
        web_profiler:
            toolbar: true
            intercept_redirects: true

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:webprofiler="http://symfony.com/schema/dic/webprofiler"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/webprofiler
                http://symfony.com/schema/dic/webprofiler/webprofiler-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- load the profiler -->
            <framework:config>
                <framework:profiler only-exceptions="false" />
            </framework:config>

            <!-- enable the web profiler -->
            <webprofiler:config
                toolbar="true"
                intercept-redirects="true" />
        </container>

    .. code-block:: php

        // load the profiler
        $container->loadFromExtension('framework', array(
            'profiler' => array('only_exceptions' => false),
        ));

        // enable the web profiler
        $container->loadFromExtension('web_profiler', array(
            'toolbar'             => true,
            'intercept_redirects' => true,
        ));

When ``only_exceptions`` is set to ``true``, the profiler only collects data
when an exception is thrown by the application.

When ``intercept_redirects`` is set to ``true``, the web profiler intercepts
the redirects and gives you the opportunity to look at the collected data
before following the redirect.

If you enable the web profiler, you also need to mount the profiler routes:

.. configuration-block::

    .. code-block:: yaml

        _profiler:
            resource: "@WebProfilerBundle/Resources/config/routing/profiler.xml"
            prefix:   /_profiler

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <import
                resource="@WebProfilerBundle/Resources/config/routing/profiler.xml"
                prefix="/_profiler" />
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;

        $profiler = $loader->import(
            '@WebProfilerBundle/Resources/config/routing/profiler.xml'
        );
        $profiler->addPrefix('/_profiler');

        $collection = new RouteCollection();
        $collection->addCollection($profiler);

As the profiler adds some overhead, you might want to enable it only under
certain circumstances in the production environment. The ``only_exceptions``
settings limits profiling to exceptions, but what if you want to get
information when the client IP comes from a specific address, or for a limited
portion of the website? You can use a Profiler Matcher, learn more about that
in ":doc:`/cookbook/profiler/matchers`".

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/testing/profiling`
* :doc:`/cookbook/profiler/data_collector`
* :doc:`/cookbook/event_dispatcher/class_extension`
* :doc:`/cookbook/event_dispatcher/method_behavior`
