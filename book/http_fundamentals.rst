.. index::
   single: Symfony2 Fundamentals

The HTTP Spec and Symfony2 Fundamentals
=======================================

To go far with Symfony2, we'll start first by discussing the Hypertext Transfer
Protocol (HTTP) - the refreshingly simple message format used by all clients
(e.g. web browsers) and servers when communicating with each other.
This is important because, as we'll discover, Symfony2 has been tirelessly
architected at its core to use HTTP - not reinvent it. The end product
is a framework that, unlike many others, is an abstraction around the proven
fundamental rules of the World Wide Web. Whether you realize it or not,
HTTP is something you use everyday. With Symfony2, you'll learn how to
leverage and master it.

.. index::
   single: HTTP; Request-response paradigm

The Client sends the Request
----------------------------

The Web is built around the idea that every communication begins when a client
makes a *request* to a server. The request is a simple text message created by
the client in a special format known as HTTP. Though there are many different
types of clients - web browser, web services, RSS reader, etc - each sends a
request with the same basic format. For example:

.. code-block:: text

    GET /index.html HTTP/1.1
    Host: www.example.com
    Accept: text/html
    User-Agent: Mozilla/5.0 (Linux; X11)

The first line of an HTTP request is the most important one (and as a matter
of fact, the only mandatory one). It contains two things: the URI and the
HTTP method. The URI (URL if combined with the host header) uniquely identifies
the location of the resource while the HTTP method defines what you want to
*do* with the resource. In this example, that unique location of the resource
is ``/index.html`` and the HTTP method is GET. In other words, the client's
request is to retrieve the resource identified by ``/index.html``.

The HTTP methods are the *verbs* of the HTTP request and define the few common
ways that we can act upon the resource:

* *GET*  Retrieve the resource from the server;
* *POST* Create a resource on the server;
* *PUT*  Update the resource on the server;
* *DELETE* Delete the resource from the server.

With this in mind, we can imagine what an HTTP request might look like to
delete a specific, say, a specific blog entry:

.. code-block:: text

    DELETE /blog/15 HTTP/1.1

.. note::

    There are actually nine HTTP methods defined by the HTTP specification,
    but many of them are not widely used or supported. In reality, many modern
    browsers don't support the ``PUT`` and ``DELETE`` methods. One additional
    header that *is* commonly supported is the ``HEAD`` method, which asks
    for the response of an identical GET request, but without the response
    body.

In addition to the first line, an HTTP request commonly contains other lines
of information known as HTTP request headers. The headers can supply a wide
array of additional information such as the requested ``Host``, the response
formats the client accepts (``Accept``) and the application the client is
using to make the request (``User-Agent``). Many other headers exist and
can be found on Wikipedia's `List of HTTP header fields`_ article.

The Server returns the Response
-------------------------------

Now that the server has read the HTTP-formatted request from the client, it
knows exactly which resource the client has identified (the URI) and what
the client would like to do with that resource (HTTP method). In the case
of a GET request, the server prepares the resource and returns it as an HTTP
response:

.. code-block:: text

    HTTP/1.1 200 OK
    Date: Fri, 12 Nov 2010 12:43:38 GMT
    Server: Apache/2.2.14 (Ubuntu)
    Connection: Keep-Alive
    Content-Length: 563
    Content-Type: text/html

    <html><body>Hello Symfony2 World!</body></html>

The HTTP response returned by the server to the client contains not only
the requested resource (the HTML content in this case), but also other information
about the response. Like the HTTP request, the first line of the response
is especially important and contains the HTTP response status code (200 in
this case). The status code is extremely important and communicates the overall
outcome of the request back to the client. Different status codes exist for
successful requests, failed requests, and requests that require action from
the client (e.g. a redirect). A full list can be found on Wikipedia's
`List of HTTP status codes`_ article.

Also like the request, an HTTP response message may contain additional pieces
of information. These are known as HTTP headers and sit between the first line
(the status code) and the response content.

One important HTTP response header is the ``Content-Type``. The body of the
same resource may be returned in multiple different formats including HTML,
XML, or JSON to name a few. The ``Content-Type`` header tells the client
which format is being returned.

As we'll find out, many other headers exist. Many are very powerful and can
be used, for example, to manage a powerful caching system.

HTTP and Client-Server Communication
------------------------------------

This request-response exchange is the fundamental process that drives all
communication on the World Wide Web. And as important and powerful as this
process is, it's inescapably simple. In fact, the rapid client-server communication
mirrors the way in which we send and receive email messages everyday. HTTP
is simply a commonly-understood language for these messages so that a disparate
set of applications and machines can communicate.

But why is a book about Symfony going to such lengths to explain requests,
responses, and the HTTP messaging format? Regardless of the framework you
choose, the type of application you build (web, mobile, JSON API), or the
development philosophy you follow, the end goal of the server is *always*
to understand each request and create and return the appropriate response.
Symfony is architected to match this reality.

.. tip::

    To learn more about the HTTP specification, we highly recommend reading
    the original `HTTP 1.1 RFC`_ or the `HTTP Bis`_, which is an active
    effort to clarify the original specification. A great tool to check
    both the request and response headers while browsing is the `Live HTTP Headers`_
    extension for Firefox.

.. index::
   single: Symfony2 Fundamentals; Requests and responses

Requests and Responses in Symfony
---------------------------------

PHP comes packaged with an array variables and methods that allow the developer
to understand each request and send a response. For request information,
PHP prepares superglobal variables such as ``$_SERVER`` and ``$_GET``.
Recall that each raw request is simply an HTTP-formatted block of text.
The transformation of the request message into the superglobal variables
is done behind the scenes by PHP and your web server. The end result is that
the request message information is now available in PHP, but as a scattered
collection of different superglobals.

As object-oriented developers, we need a better (object-oriented) way to
access our request information. Symfony provides a ``Request`` class for
just that purpose. The ``Request`` class is simply an object-oriented
representation of an HTTP request message. With it, you have all the
request information at your fingertips::

    use Symfony\Component\HttpFoundation\Request;

    $request = Request::createFromGlobals();

    // the URI being requested ((e.g. /about) minus any query parameters
    $request->getPathInfo();

    // retrieve GET and POST variables respectively
    $request->query->get('foo');
    $request->request->get('bar');

    // retrieves an instance of UploadedFile identified by foo
    $request->files->get('foo');

    $request->getMethod();          // GET, POST, PUT, DELETE, HEAD
    $request->getLanguages();       // an array of accepted languages

The ``getPathInfo()`` method is especially important as it returns the URI
being requested relative to your application. For example, suppose an
application is being executed from the ``foo`` subdirectory of a server. In
that case::

    // http://example.com/foo/index.php/bar
    $request->getPathInfo();  // returns "bar"

Symfony also provides a ``Response`` class, which is simply a PHP abstraction
of the raw HTTP response message. This allows your application to use an
object-oriented interface to construct response that needs to be returned
to the client::

    use Symfony\Component\HttpFoundation\Response;
    $response = new Response();

    $response->setContent('<html><body><h1>Hello world!</h1></body></html');
    $response->setStatusCode(200);
    $response->headers->set('Content-Type', 'text/html');

    // echos the headers followed by the content
    $response->send();

At this point, if Symfony did nothing else, you would already have a
framework for accessing request information and an object-oriented
interface for creating the response. Symfony provides you with a rich toolset,
without obscuring the reality that *the end goal of any web application is
to process an HTTP request and return the appropriate HTTP response based on
the application-specific business logic*. Even as we discuss the many features
in Symfony, this goal will remain fundamental and transparent.

.. tip::

    The ``Request`` and ``Response`` classes are part of a standalone component
    included with Symfony called ``HttpFoundation``. This component can be
    used entirely independent of Symfony and also provides classes for handling
    sessions and file uploads.

The Journey from the Request to the Response
--------------------------------------------

We know now that the end goal of any application is to use the HTTP
request to create and return the appropriate HTTP response. Symfony provides
``Request`` and ``Response`` classes that allow this to be done through
an object-oriented interface. Though, so far, we're only leveraging a small
piece of Symfony, we already have the tools to write a simple application!
Let's dive in:

.. code-block:: php

    $request = Request::createFromGlobals();
    $path = $request->getPathInfo(); // the URL being requested
    $method = $request->getMethod();

    if (in_array($path, array('', '/') && $method == 'GET') {
        $response = new Response('Welcome to the homepage.');
    } elseif ($path == '/about' && $method == 'GET') {
        $response = new Response('About us');
    } else {
        $response = new Response('Page not found.', 404);
    }
    $response->send();

In this simple example, the application correctly processes the request and
returns an appropriate response. From a very technical standpoint, then, our
application does exactly what it should.

An Application without a Framework
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

But what if the application needs to grow? Imagine this same application if it
were forced now to handle hundreds or even thousands of different pages! In
order to keep things maintainable (i.e. not all in one file), we'd need to do
some reorganization. For starters, we might move the work of creating the
``Response`` into a set of different functions. These functions are commonly
known as *controllers* and allow us to further organize our code::

    if (in_array($path, array('', '/') && $method == 'GET') {
        $response = main_controller($request);
    } elseif ($path == '/about' && $method == 'GET') {
        $response = about_controller($request);
    } else {
        $response = error404_controller($request);
    }

    function main_controller(Request $request)
    {
        return new Response('Welcome to the homepage.');
    }

    function about_controller(Request $request)
    {
        return new Response('About us');
    }

    function error404_controller(Request $request)
    {
        return new Response('Page not found.', 404);
    }

Next, our growing application still contains a long ``if`` ``elseif`` block
that routes the creation of the ``Response`` object to a different controller
(i.e. PHP method). We might consider building a configuration-based routing
system that maps each request (by URI and HTTP method) to a specific controller.

Obvious or not, the application is beginning to spin out of control. Recall
that the goal of any application is to apply the custom application logic and
information from the request to create an appropriate response. In our
application, these proposed changes are **not** to the business logic. Instead,
the necessary refactoring means inventing a system of controllers and a custom
routing system. As we continue development, we'll inevitably spend some time
developing our application and some time developing and enhancing the framework
around it.

We need a better solution - one where the developer spends his/her time developing
the application logic for creating ``Response`` objects instead of on so many
low-level details.

The Symfony framework does just this by allowing you to focus on your most
valuable deliverables without sacrificing the power and organization of a
framework. Of course, a popular framework like Symfony comes with a long
list of "bonuses" such as free maintenance, documentation, standardization,
and a community-driven group of open source bundles (i.e. plugins) available
for use.

.. index::
   single: Symfony2 Fundamentals; The Kernel
   single: Kernel; Introduction

Introducing the Symfony Kernel
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony is based around a ``Kernel`` object whose only job is to facilitate
the journey from the ``Request`` object to the final ``Response`` object.
The ``Kernel`` is what handles each request and actually executes your application
code.

The "application code" that's executed by the ``Kernel`` is called a "controller",
a special term for what's actually a basic PHP callable (most commonly,
an object method). The controller is where your application code lives -
it's where you create the final ``Response`` object. The Kernel works by
determining and then calling a "Controller" for each request:

.. code-block:: text

    Request -> Kernel::handle() -> Controller (your code) -> Response (returned by controller)

Our original sample application could be refactored into two "controllers",
which, in this example, are PHP methods in some ``myController`` class.
The code needed to determine and execute these controllers is isolated
elsewhere and handled by the ``Kernel``::

    class myController
    {
        public function homepageAction()
        {
            return new Response('Welcome to the homepage.');
        }

        public function aboutAction()
        {
            return Response('About us');
        }
    }

.. tip::

    Notice that each controller returns a ``Response`` object. This is the
    basic job of your controllers: to apply complex business logic and
    ultimately construct and return the final ``Response``.

But how does the ``Kernel`` know which controller to call for each request?
Though this process is entirely configurable, Symfony2 integrates a ``Router``
that uses a "map" to connect path info from the ``Request`` to a specific
controller.

.. code-block:: text

    Request -> Kernel::handle() -> Controller -> Response
                        |    ^
                        | controller
                        |    |
                        v    |
                        Routing

We'll talk a lot more about :doc:`Controllers </book/controller>` and the
:doc:`Router </book/routing>` in later chapters.

.. tip::

    The ``Kernel`` class is part of a standalone component used by Symfony2
    called ``HttpKernel``. This component provides functionality related to
    Bundles, Security, Caching and more. The ``Router`` is also part of a
    standalone component called ``Routing``.

.. index::
   single: Symfony2 Components

Symfony2 *Components* versus the Symfony2 *Framework*?
------------------------------------------------------

By now, we've seen the most basic components that make up the Symfony2 framework.
In reality, everything we've talked about so far (the ``Request``, ``Response``,
``Kernel`` and ``Router``) lives in three different standalone components
used by Symfony. In fact, each feature in Symfony2 belongs to one of over
twenty independent libraries (called the "Symfony Components")! Even if you
decided to build your own PHP framework (an unwise idea), you could use the
Symfony2 Components as the building blocks for many layers of functionality.
And if you do use Symfony2, but need to replace a component entirely, you have
the ability to do that. Symfony2 is decoupled and relies on interface-driven
dependency injection. In other words, the developer has complete control.

So then, what *is* the Symfony2 **Framework**? The *Symfony2 Framework* is
a PHP framework that accomplishes two distinct tasks:

#. Provides a selection of components (i.e. the Symfony2 Components) and
   third-party libraries.

#. Provides sensible configuration that ties everything together nicely.

The goal of the framework is to integrate many independent tools in order
to provide a consistent experience for the developer. Even the framework
itself is a Symfony2 bundle that can be configured or replaced entirely.

Basically, Symfony2 provides a powerful set of tools for rapidly developing
web applications without imposing on your application. Normal users can
quickly start development by using a Symfony2 distribution, which provides
a project skeleton with sensible defaults. For more advanced users, the sky
is the limit.

.. _`HTTP 1.1 RFC`: http://www.w3.org/Protocols/rfc2616/rfc2616.html
.. _`HTTP Bis`: http://datatracker.ietf.org/wg/httpbis/
.. _`Live HTTP Headers`: https://addons.mozilla.org/en-US/firefox/addon/3829/
.. _`List of HTTP status codes`: http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
.. _`List of HTTP header fields`: http://en.wikipedia.org/wiki/List_of_HTTP_header_fields
