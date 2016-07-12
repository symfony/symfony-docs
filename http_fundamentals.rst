.. index::
   single: Symfony Fundamentals

.. _symfony2-and-http-fundamentals:

Symfony and HTTP Fundamentals
=============================

Symfony is modeled after the HTTP Response-Request flow. This means that
knowing HTTP fundamentals is an important part of understanding Symfony.
Fortunately, understanding a basic Request-Response flow in HTTP is not
difficult. This chapter will walk you through the HTTP fundamental basics and
what this means for Symfony.

Requests and Responses in HTTP
------------------------------

HTTP (Hypertext Transfer Protocol) is a text language that allows two machines
to communicate with each other. For example, when checking for the latest
`xkcd`_ comic, the following (approximate) conversation takes place:

.. image:: /images/http-xkcd.png
   :align: center

And while the actual language used is a bit more formal, it's still dead-simple.
HTTP is the term used to describe this simple text-based language. The goal of
your server is *always* to understand text requests and return text responses.

Symfony is built from the ground up around that reality. Whether you realize
it or not, HTTP is something you use every day. With Symfony, you'll learn
how to master it.

.. index::
   single: HTTP; Request-response paradigm

Step 1: The Client Sends a Request
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Every conversation on the web starts with a *request*. The request is a text
message created by a client (e.g. a browser, a smartphone app, etc) in a
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
most important, because it contains two important things: the HTTP method (GET)
and the URI (``/``).

The URI (e.g. ``/``, ``/contact``, etc) is the unique address or location
that identifies the resource the client wants. The HTTP method (e.g. ``GET``)
defines what the client wants to *do* with the resource. The HTTP methods (also
known as verbs) define the few common ways that the client can act upon the
resource - the most common HTTP methods are:

**GET**
    Retrieve the resource from the server (e.g. when visiting a page);
**POST**
    Create a resource on the server (e.g. when submitting a form);
**PUT**/**PATCH**
    Update the resource on the server (used by APIs);
**DELETE**
    Delete the resource from the server (used by APIs).

With this in mind, you can imagine what an HTTP request might look like to
delete a specific blog entry, for example:

.. code-block:: text

    DELETE /blog/15 HTTP/1.1

.. note::

    There are actually nine HTTP methods defined by the HTTP specification,
    but many of them are not widely used or supported. In reality, many
    modern browsers only support ``POST`` and ``GET`` in HTML forms. Various
    others are however supported in `XMLHttpRequest`_.

In addition to the first line, an HTTP request invariably contains other
lines of information called request **headers**. The headers can supply a wide
range of information such as the host of the resource being requested (``Host``),
the response formats the client accepts (``Accept``) and the application the
client is using to make the request (``User-Agent``). Many other headers exist
and can be found on Wikipedia's `List of HTTP header fields`_ article.

Step 2: The Server Returns a Response
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
      <!-- ... HTML for the xkcd comic -->
    </html>

The HTTP response contains the requested resource (the HTML content in this
case), as well as other information about the response. The first line is
especially important and contains the HTTP response status code (200 in this
case).

The status code communicates the overall outcome of the request back to the
client. Was the request successful? Was there an error? Different status codes
exist that indicate success, an error or that the client needs to do something
(e.g. redirect to another page). Check out the `list of HTTP status codes`_.

Like the request, an HTTP response contains additional pieces of information
known as HTTP headers. The body of the same resource could be returned in multiple
different formats like HTML, XML or JSON and the ``Content-Type`` header uses
Internet Media Types like ``text/html`` to tell the client which format is
being returned. You can see a `List of common media types`_ from IANA.

Many other headers exist, some of which are very powerful. For example, certain
headers can be used to create a powerful caching system.

Requests, Responses and Web Development
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This request-response conversation is the fundamental process that drives all
communication on the web. And as important and powerful as this process is,
it's inescapably simple.

The most important fact is this: regardless of the language you use, the
type of application you build (web, mobile, JSON API) or the development
philosophy you follow, the end goal of an application is **always** to understand
each request and create and return the appropriate response.

.. seealso::

    To learn more about the HTTP specification, read the original `HTTP 1.1 RFC`_
    or the `HTTP Bis`_, which is an active effort to clarify the original
    specification.

.. index::
   single: Symfony Fundamentals; Requests and responses

Requests and Responses in PHP
-----------------------------

So how do you interact with the "request" and create a "response" when using
PHP? In reality, PHP abstracts you a bit from the whole process::

    $uri = $_SERVER['REQUEST_URI'];
    $foo = $_GET['foo'];

    header('Content-Type: text/html');
    echo 'The URI requested is: '.$uri;
    echo 'The value of the "foo" parameter is: '.$foo;

As strange as it sounds, this small application is in fact taking information
from the HTTP request and using it to create an HTTP response. Instead of
parsing the raw HTTP request message, PHP prepares superglobal variables
(such as ``$_SERVER`` and ``$_GET``) that contain all the information from the
request. Similarly, instead of returning the HTTP-formatted text response, you
can use the PHP :phpfunction:`header` function to create response headers and
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

Symfony Request Object
~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\HttpFoundation\\Request` class is an
object-oriented representation of the HTTP request message. With it, you
have all the request information at your fingertips::

    use Symfony\Component\HttpFoundation\Request;

    $request = Request::createFromGlobals();

    // the URI being requested (e.g. /about) minus any query parameters
    $request->getPathInfo();

    // retrieve $_GET and $_POST variables respectively
    $request->query->get('id');
    $request->request->get('category', 'default category');

    // retrieve $_SERVER variables
    $request->server->get('HTTP_HOST');

    // retrieves an instance of UploadedFile identified by "attachment"
    $request->files->get('attachment');

    // retrieve a $_COOKIE value
    $request->cookies->get('PHPSESSID');

    // retrieve an HTTP request header, with normalized, lowercase keys
    $request->headers->get('host');
    $request->headers->get('content_type');

    $request->getMethod();    // e.g. GET, POST, PUT, DELETE or HEAD
    $request->getLanguages(); // an array of languages the client accepts

As a bonus, the ``Request`` class does a lot of work in the background that
you'll never need to worry about. For example, the ``isSecure()`` method
checks the *three* different values in PHP that can indicate whether or not
the user is connecting via a secured connection (i.e. HTTPS).

Symfony Response Object
~~~~~~~~~~~~~~~~~~~~~~~

Symfony also provides a :class:`Symfony\\Component\\HttpFoundation\\Response`
class: a simple PHP representation of an HTTP response message. This allows your
application to use an object-oriented interface to construct the response that
needs to be returned to the client::

    use Symfony\Component\HttpFoundation\Response;

    $response = new Response();

    $response->setContent('<html><body><h1>Hello world!</h1></body></html>');
    $response->setStatusCode(Response::HTTP_OK);

    // set a HTTP response header
    $response->headers->set('Content-Type', 'text/html');

    // print the HTTP headers followed by the content
    $response->send();

.. tip::

    The ``Request`` and ``Response`` classes are part of a standalone component
    called :doc:`symfony/http-foundation </components/http_foundation/introduction>`
    that you can use in *any* PHP project. This also contains classes for handling
    sessions, file uploads and more.

If Symfony offered nothing else, you would already have a toolkit for easily
accessing request information and an object-oriented interface for creating
the response. Even as you learn the many powerful features in Symfony, keep
in mind that the goal of your application is always *to interpret a request
and create the appropriate response based on your application logic*.

The Journey from the Request to the Response
--------------------------------------------

Like HTTP itself, using the ``Request`` and ``Response`` objects is pretty
simple. The hard part of building an application is writing what comes in
between. In other words, the real work comes in writing the code that
interprets the request information and creates the response.

Your application probably does many things, like sending emails, handling
form submissions, saving things to a database, rendering HTML pages and protecting
content with security. How can you manage all of this and still keep your
code organized and maintainable? Symfony was created to help you with these
problems.

.. index::
    single: Front controller; Origins

The Front Controller
~~~~~~~~~~~~~~~~~~~~

Traditionally, applications were built so that each "page" of a site was
its own physical file (e.g. ``index.php``, ``contact.php``, etc.).

There are several problems with this approach, including the inflexibility
of the URLs (what if you wanted to change ``blog.php`` to ``news.php`` without
breaking all of your links?) and the fact that each file *must* manually
include some set of core files so that security, database connections and
the "look" of the site can remain consistent.

A much better solution is to use a front controller: a single PHP file that
handles every request coming into your application. For example:

+------------------------+------------------------+
| ``/index.php``         | executes ``index.php`` |
+------------------------+------------------------+
| ``/index.php/contact`` | executes ``index.php`` |
+------------------------+------------------------+
| ``/index.php/blog``    | executes ``index.php`` |
+------------------------+------------------------+

.. tip::

    By using rewrite rules in your
    :doc:`web server configuration </cookbook/configuration/web_server_configuration>`,
    the ``index.php`` won't be needed and you will have beautiful, clean URLs
    (e.g. ``/show``).

Now, every request is handled exactly the same way. Instead of individual URLs
executing different PHP files, the front controller is *always* executed,
and the routing of different URLs to different parts of your application
is done internally. This solves both problems with the original approach.
Almost all modern web apps do this.

.. index::
    single: HTTP; Symfony request flow

The Symfony Application Flow
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony will operate in this front-controller file to handle each incoming
request. Symfony follows the same simple pattern for every request:

.. _request-flow-figure:

.. figure:: /images/request-flow.png
   :align: center
   :alt: Symfony request flow

   Incoming requests are interpreted by the :doc:`Routing component </book/routing>` and
   passed to PHP functions that return ``Response`` objects.

Each "page" of your site is defined in a routing configuration file that
maps different URLs to different PHP functions. The job of each PHP function,
called a controller, is to use information from the request - along with many
other tools Symfony makes available - to create and return a ``Response``
object. In other words, the controller is where *your* code goes: it's where
you interpret the request and create a response.

Conclusion
----------

To review what you've learned so far:

#. A client sends an HTTP request;
#. Each request executes the same, single file (called a "front controller");
#. The front controller boots Symfony and passes the request information;
#. The router matches the request URI to a specific route and returns
   information about the route, including the controller (usually a PHP method)
   that should be executed;
#. The controller (PHP method) is executed: this is where *your* code creates
   and returns the appropriate ``Response`` object;
#. Symfony turns your ``Response`` object into the text headers and content
   (i.e. the HTTP response), which are sent back to the client.

Symfony provides a powerful set of tools for rapidly developing web applications
without imposing on your application. Normal users can quickly start development
by using a Symfony distribution, which provides a project skeleton with
sensible defaults. For more advanced users, the sky is the limit.

.. _`xkcd`: http://xkcd.com/
.. _`XMLHttpRequest`: https://en.wikipedia.org/wiki/XMLHttpRequest
.. _`HTTP 1.1 RFC`: http://www.w3.org/Protocols/rfc2616/rfc2616.html
.. _`HTTP Bis`: http://datatracker.ietf.org/wg/httpbis/
.. _`Live HTTP Headers`: https://addons.mozilla.org/en-US/firefox/addon/live-http-headers/
.. _`List of HTTP header fields`: https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
.. _`list of HTTP status codes`: https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
.. _`List of common media types`: https://www.iana.org/assignments/media-types/media-types.xhtml
.. _`Validator`: https://github.com/symfony/validator
.. _`Swift Mailer`: http://swiftmailer.org/
