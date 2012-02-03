.. index::
   single: Symfony2 Fundamentals

Symfony2 and HTTP Fundamentals
==============================

Congratulations! By learning about Symfony2, you're well on your way towards
being a more *productive*, *well-rounded* and *popular* web developer (actually,
you're on your own for the last part). Symfony2 is built to get back to
basics: to develop tools that let you develop faster and build more robust
applications, while staying out of your way. Symfony is built on the best
ideas from many technologies: the tools and concepts you're about to learn
represent the efforts of thousands of people, over many years. In other words,
you're not just learning "Symfony", you're learning the fundamentals of the
web, development best practices, and how to use many amazing new PHP libraries,
inside or independent of Symfony2. So, get ready.

True to the Symfony2 philosophy, this chapter begins by explaining the fundamental
concept common to web development: HTTP. Regardless of your background or
preferred programming language, this chapter is a **must-read** for everyone.

HTTP is Simple
--------------

HTTP (Hypertext Transfer Protocol to the geeks) is a text language that allows
two machines to communicate with each other. That's it! For example, when
checking for the latest `xkcd`_ comic, the following (approximate) conversation
takes place:

.. image:: /images/http-xkcd.png
   :align: center

And while the actual language used is a bit more formal, it's still dead-simple.
HTTP is the term used to describe this simple text-based language. And no
matter how you develop on the web, the goal of your server is *always* to
understand simple text requests, and return simple text responses.

Symfony2 is built from the ground-up around that reality. Whether you realize
it or not, HTTP is something you use everyday. With Symfony2, you'll learn
how to master it.

.. index::
   single: HTTP; Request-response paradigm

Step1: The Client sends a Request
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Every conversation on the web starts with a *request*. The request is a text
message created by a client (e.g. a browser, an iPhone app, etc) in a
special format known as HTTP. The client sends that request to a server,
and then waits for the response.

Take a look at the first part of the interaction (the request) between a
browser and the xkcd web server:

.. image:: /images/http-xkcd-request.png
   :align: center

In HTTP-speak, this HTTP request would actually look something like this:

.. code-block:: text

    GET / HTTP/1.1
    Host: xkcd.com
    Accept: text/html
    User-Agent: Mozilla/5.0 (Macintosh)

This simple message communicates *everything* necessary about exactly which
resource the client is requesting. The first line of an HTTP request is the
most important and contains two things: the URI and the HTTP method.

The URI (e.g. ``/``, ``/contact``, etc) is the unique address or location
that identifies the resource the client wants. The HTTP method (e.g. ``GET``)
defines what you want to *do* with the resource. The HTTP methods are the
*verbs* of the request and define the few common ways that you can act upon
the resource:

+----------+---------------------------------------+
| *GET*    | Retrieve the resource from the server |
+----------+---------------------------------------+
| *POST*   | Create a resource on the server       |
+----------+---------------------------------------+
| *PUT*    | Update the resource on the server     |
+----------+---------------------------------------+
| *DELETE* | Delete the resource from the server   |
+----------+---------------------------------------+

With this in mind, you can imagine what an HTTP request might look like to
delete a specific blog entry, for example:

.. code-block:: text

    DELETE /blog/15 HTTP/1.1

.. note::

    There are actually nine HTTP methods defined by the HTTP specification,
    but many of them are not widely used or supported. In reality, many modern
    browsers don't support the ``PUT`` and ``DELETE`` methods.

In addition to the first line, an HTTP request invariably contains other
lines of information called request headers. The headers can supply a wide
range of information such as the requested ``Host``, the response formats
the client accepts (``Accept``) and the application the client is using to
make the request (``User-Agent``). Many other headers exist and can be found
on Wikipedia's `List of HTTP header fields`_ article.

Step 2: The Server returns a Response
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Once a server has received the request, it knows exactly which resource the
client needs (via the URI) and what the client wants to do with that resource
(via the method). For example, in the case of a GET request, the server
prepares the resource and returns it in an HTTP response. Consider the response
from the xkcd web server:

.. image:: /images/http-xkcd.png
   :align: center

Translated into HTTP, the response sent back to the browser will look something
like this: 

.. code-block:: text

    HTTP/1.1 200 OK
    Date: Sat, 02 Apr 2011 21:05:05 GMT
    Server: lighttpd/1.4.19
    Content-Type: text/html

    <html>
      <!-- HTML for the xkcd comic -->
    </html>

The HTTP response contains the requested resource (the HTML content in this
case), as well as other information about the response. The first line is
especially important and contains the HTTP response status code (200 in this
case). The status code communicates the overall outcome of the request back
to the client. Was the request successful? Was there an error? Different
status codes exist that indicate success, an error, or that the client needs
to do something (e.g. redirect to another page). A full list can be found
on Wikipedia's `List of HTTP status codes`_ article.

Like the request, an HTTP response contains additional pieces of information
known as HTTP headers. For example, one important HTTP response header is
``Content-Type``. The body of the same resource could be returned in multiple
different formats like HTML, XML, or JSON and the ``Content-Type`` header uses
Internet Media Types like ``text/html`` to tell the client which format is
being returned. A list of common media types can be found on Wikipedia's 
`List of common media types`_ article.

Many other headers exist, some of which are very powerful. For example, certain
headers can be used to create a powerful caching system.

Requests, Responses and Web Development
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This request-response conversation is the fundamental process that drives all
communication on the web. And as important and powerful as this process is,
it's inescapably simple.

The most important fact is this: regardless of the language you use, the
type of application you build (web, mobile, JSON API), or the development
philosophy you follow, the end goal of an application is **always** to understand
each request and create and return the appropriate response.

Symfony is architected to match this reality.

.. tip::

    To learn more about the HTTP specification, read the original `HTTP 1.1 RFC`_
    or the `HTTP Bis`_, which is an active effort to clarify the original
    specification. A great tool to check both the request and response headers
    while browsing is the `Live HTTP Headers`_ extension for Firefox.

.. index::
   single: Symfony2 Fundamentals; Requests and responses

Requests and Responses in PHP
-----------------------------

So how do you interact with the "request" and create a "response" when using
PHP? In reality, PHP abstracts you a bit from the whole process:

.. code-block:: php

    <?php
    $uri = $_SERVER['REQUEST_URI'];
    $foo = $_GET['foo'];

    header('Content-type: text/html');
    echo 'The URI requested is: '.$uri;
    echo 'The value of the "foo" parameter is: '.$foo;

As strange as it sounds, this small application is in fact taking information
from the HTTP request and using it to create an HTTP response. Instead of
parsing the raw HTTP request message, PHP prepares superglobal variables
such as ``$_SERVER`` and ``$_GET`` that contain all the information from
the request. Similarly, instead of returning the HTTP-formatted text response,
you can use the ``header()`` function to create response headers and simply
print out the actual content that will be the content portion of the response
message. PHP will create a true HTTP response and return it to the client:

.. code-block:: text

    HTTP/1.1 200 OK
    Date: Sat, 03 Apr 2011 02:14:33 GMT
    Server: Apache/2.2.17 (Unix)
    Content-Type: text/html

    The URI requested is: /testing?foo=symfony
    The value of the "foo" parameter is: symfony

Requests and Responses in Symfony
---------------------------------

Symfony provides an alternative to the raw PHP approach via two classes that
allow you to interact with the HTTP request and response in an easier way.
The :class:`Symfony\\Component\\HttpFoundation\\Request` class is a simple
object-oriented representation of the HTTP request message. With it, you
have all the request information at your fingertips::

    use Symfony\Component\HttpFoundation\Request;

    $request = Request::createFromGlobals();

    // the URI being requested (e.g. /about) minus any query parameters
    $request->getPathInfo();

    // retrieve GET and POST variables respectively
    $request->query->get('foo');
    $request->request->get('bar', 'default value if bar does not exist');

    // retrieve SERVER variables
    $request->server->get('HTTP_HOST');

    // retrieves an instance of UploadedFile identified by foo
    $request->files->get('foo');

    // retrieve a COOKIE value
    $request->cookies->get('PHPSESSID');

    // retrieve an HTTP request header, with normalized, lowercase keys
    $request->headers->get('host');
    $request->headers->get('content_type');

    $request->getMethod();          // GET, POST, PUT, DELETE, HEAD
    $request->getLanguages();       // an array of languages the client accepts

As a bonus, the ``Request`` class does a lot of work in the background that
you'll never need to worry about. For example, the ``isSecure()`` method
checks the *three* different values in PHP that can indicate whether or not
the user is connecting via a secured connection (i.e. ``https``).

.. sidebar:: ParameterBags and Request attributes

    As seen above, the ``$_GET`` and ``$_POST`` variables are accessible via
    the public ``query`` and ``request`` properties respectively. Each of
    these objects is a :class:`Symfony\\Component\\HttpFoundation\\ParameterBag`
    object, which has methods like
    :method:`Symfony\\Component\\HttpFoundation\\ParameterBag::get`,
    :method:`Symfony\\Component\\HttpFoundation\\ParameterBag::has`,
    :method:`Symfony\\Component\\HttpFoundation\\ParameterBag::all` and more.
    In fact, every public property used in the previous example is some instance
    of the ParameterBag.
    
    The Request class also has a public ``attributes`` property, which holds
    special data related to how the application works internally. For the
    Symfony2 framework, the ``attributes`` holds the values returned by the
    matched route, like ``_controller``, ``id`` (if you have an ``{id}``
    wildcard), and even the name of the matched route (``_route``). The
    ``attributes`` property exists entirely to be a place where you can
    prepare and store context-specific information about the request.
    

Symfony also provides a ``Response`` class: a simple PHP representation of
an HTTP response message. This allows your application to use an object-oriented
interface to construct the response that needs to be returned to the client::

    use Symfony\Component\HttpFoundation\Response;
    $response = new Response();

    $response->setContent('<html><body><h1>Hello world!</h1></body></html>');
    $response->setStatusCode(200);
    $response->headers->set('Content-Type', 'text/html');

    // prints the HTTP headers followed by the content
    $response->send();

If Symfony offered nothing else, you would already have a toolkit for easily
accessing request information and an object-oriented interface for creating
the response. Even as you learn the many powerful features in Symfony, keep
in mind that the goal of your application is always *to interpret a request
and create the appropriate response based on your application logic*.

.. tip::

    The ``Request`` and ``Response`` classes are part of a standalone component
    included with Symfony called ``HttpFoundation``. This component can be
    used entirely independent of Symfony and also provides classes for handling
    sessions and file uploads.

The Journey from the Request to the Response
--------------------------------------------

Like HTTP itself, the ``Request`` and ``Response`` objects are pretty simple.
The hard part of building an application is writing what comes in between.
In other words, the real work comes in writing the code that interprets the
request information and creates the response.

Your application probably does many things, like sending emails, handling
form submissions, saving things to a database, rendering HTML pages and protecting
content with security. How can you manage all of this and still keep your
code organized and maintainable?

Symfony was created to solve these problems so that you don't have to.

The Front Controller
~~~~~~~~~~~~~~~~~~~~

Traditionally, applications were built so that each "page" of a site was
its own physical file:

.. code-block:: text

    index.php
    contact.php
    blog.php

There are several problems with this approach, including the inflexibility
of the URLs (what if you wanted to change ``blog.php`` to ``news.php`` without
breaking all of your links?) and the fact that each file *must* manually
include some set of core files so that security, database connections and
the "look" of the site can remain consistent.

A much better solution is to use a :term:`front controller`: a single PHP
file that handles every request coming into your application. For example:

+------------------------+------------------------+
| ``/index.php``         | executes ``index.php`` |
+------------------------+------------------------+
| ``/index.php/contact`` | executes ``index.php`` |
+------------------------+------------------------+
| ``/index.php/blog``    | executes ``index.php`` |
+------------------------+------------------------+

.. tip::

    Using Apache's ``mod_rewrite`` (or equivalent with other web servers),
    the URLs can easily be cleaned up to be just ``/``, ``/contact`` and
    ``/blog``.

Now, every request is handled exactly the same. Instead of individual URLs
executing different PHP files, the front controller is *always* executed,
and the routing of different URLs to different parts of your application
is done internally. This solves both problems with the original approach.
Almost all modern web apps do this - including apps like WordPress.

Stay Organized
~~~~~~~~~~~~~~

But inside your front controller, how do you know which page should
be rendered and how can you render each in a sane way? One way or another, you'll need to
check the incoming URI and execute different parts of your code depending
on that value. This can get ugly quickly:

.. code-block:: php

    // index.php

    $request = Request::createFromGlobals();
    $path = $request->getPathInfo(); // the URI path being requested

    if (in_array($path, array('', '/')) {
        $response = new Response('Welcome to the homepage.');
    } elseif ($path == '/contact') {
        $response = new Response('Contact us');
    } else {
        $response = new Response('Page not found.', 404);
    }
    $response->send();

Solving this problem can be difficult. Fortunately it's *exactly* what Symfony
is designed to do.

The Symfony Application Flow
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When you let Symfony handle each request, life is much easier. Symfony follows
the same simple pattern for every request:

.. _request-flow-figure:

.. figure:: /images/request-flow.png
   :align: center
   :alt: Symfony2 request flow

   Incoming requests are interpreted by the routing and passed to controller
   functions that return ``Response`` objects.

Each "page" of your site is defined in a routing configuration file that
maps different URLs to different PHP functions. The job of each PHP function,
called a :term:`controller`, is to use information from the request - along
with many other tools Symfony makes available - to create and return a ``Response``
object. In other words, the controller is where *your* code goes: it's where
you interpret the request and create a response.

It's that easy! Let's review:

* Each request executes a front controller file;

* The routing system determines which PHP function should be executed based
  on information from the request and routing configuration you've created;

* The correct PHP function is executed, where your code creates and returns
  the appropriate ``Response`` object.

A Symfony Request in Action
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Without diving into too much detail, let's see this process in action. Suppose
you want to add a ``/contact`` page to your Symfony application. First, start
by adding an entry for ``/contact`` to your routing configuration file:

.. code-block:: yaml

    contact:
        pattern:  /contact
        defaults: { _controller: AcmeDemoBundle:Main:contact }

.. note::

   This example uses :doc:`YAML</components/yaml>` to define the routing
   configuration. Routing configuration can also be written in other formats
   such as XML or PHP.

When someone visits the ``/contact`` page, this route is matched, and the
specified controller is executed. As you'll learn in the :doc:`routing chapter</book/routing>`,
the ``AcmeDemoBundle:Main:contact`` string is a short syntax that points to a
specific PHP method ``contactAction`` inside a class called ``MainController``:

.. code-block:: php

    class MainController
    {
        public function contactAction()
        {
            return new Response('<h1>Contact us!</h1>');
        }
    }

In this very simple example, the controller simply creates a ``Response``
object with the HTML "<h1>Contact us!</h1>". In the :doc:`controller chapter</book/controller>`,
you'll learn how a controller can render templates, allowing your "presentation"
code (i.e. anything that actually writes out HTML) to live in a separate
template file. This frees up the controller to worry only about the hard
stuff: interacting with the database, handling submitted data, or sending
email messages. 

Symfony2: Build your App, not your Tools.
-----------------------------------------

You now know that the goal of any app is to interpret each incoming request
and create an appropriate response. As an application grows, it becomes more
difficult to keep your code organized and maintainable. Invariably, the same
complex tasks keep coming up over and over again: persisting things to the
database, rendering and reusing templates, handling form submissions, sending
emails, validating user input and handling security.

The good news is that none of these problems is unique. Symfony provides
a framework full of tools that allow you to build your application, not your
tools. With Symfony2, nothing is imposed on you: you're free to use the full
Symfony framework, or just one piece of Symfony all by itself.

.. index::
   single: Symfony2 Components

Standalone Tools: The Symfony2 *Components*
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

So what *is* Symfony2? First, Symfony2 is a collection of over twenty independent
libraries that can be used inside *any* PHP project. These libraries, called
the *Symfony2 Components*, contain something useful for almost any situation,
regardless of how your project is developed. To name a few:

* `HttpFoundation`_ - Contains the ``Request`` and ``Response`` classes, as
  well as other classes for handling sessions and file uploads;

* `Routing`_ - Powerful and fast routing system that allows you to map a
  specific URI (e.g. ``/contact``) to some information about how that request
  should be handled (e.g. execute the ``contactAction()`` method);

* `Form`_ - A full-featured and flexible framework for creating forms and
  handing form submissions;

* `Validator`_ A system for creating rules about data and then validating
  whether or not user-submitted data follows those rules;

* `ClassLoader`_ An autoloading library that allows PHP classes to be used
  without needing to manually ``require`` the files containing those classes;

* `Templating`_ A toolkit for rendering templates, handling template inheritance
  (i.e. a template is decorated with a layout) and performing other common
  template tasks;

* `Security`_ - A powerful library for handling all types of security inside
  an application;

* `Translation`_ A framework for translating strings in your application.

Each and every one of these components is decoupled and can be used in *any*
PHP project, regardless of whether or not you use the Symfony2 framework.
Every part is made to be used if needed and replaced when necessary.

The Full Solution: The Symfony2 *Framework*
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

So then, what *is* the Symfony2 *Framework*? The *Symfony2 Framework* is
a PHP library that accomplishes two distinct tasks:

#. Provides a selection of components (i.e. the Symfony2 Components) and
   third-party libraries (e.g. ``Swiftmailer`` for sending emails);

#. Provides sensible configuration and a "glue" library that ties all of these
   pieces together.

The goal of the framework is to integrate many independent tools in order
to provide a consistent experience for the developer. Even the framework
itself is a Symfony2 bundle (i.e. a plugin) that can be configured or replaced
entirely.

Symfony2 provides a powerful set of tools for rapidly developing web applications
without imposing on your application. Normal users can quickly start development
by using a Symfony2 distribution, which provides a project skeleton with
sensible defaults. For more advanced users, the sky is the limit.

.. _`xkcd`: http://xkcd.com/
.. _`HTTP 1.1 RFC`: http://www.w3.org/Protocols/rfc2616/rfc2616.html
.. _`HTTP Bis`: http://datatracker.ietf.org/wg/httpbis/
.. _`Live HTTP Headers`: https://addons.mozilla.org/en-US/firefox/addon/3829/
.. _`List of HTTP status codes`: http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
.. _`List of HTTP header fields`: http://en.wikipedia.org/wiki/List_of_HTTP_header_fields
.. _`List of common media types`: http://en.wikipedia.org/wiki/Internet_media_type#List_of_common_media_types
.. _`HttpFoundation`: https://github.com/symfony/HttpFoundation
.. _`Routing`: https://github.com/symfony/Routing
.. _`Form`: https://github.com/symfony/Form
.. _`Validator`: https://github.com/symfony/Validator
.. _`ClassLoader`: https://github.com/symfony/ClassLoader
.. _`Templating`: https://github.com/symfony/Templating
.. _`Security`: https://github.com/symfony/Security
.. _`Translation`: https://github.com/symfony/Translation
