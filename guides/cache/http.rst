.. index::
   single: Cache

HTTP Cache
==========

The best way to improve the performance of an application is probably to cache
its output and bypass it altogether. Of course, this is not possible for
highly dynamic websites, or is it? This document will show you how Symfony2
cache system works and why we think this is the best possible approach.

Symfony2 cache system relies on the simplicity and power of the HTTP cache as
defined in the HTTP specification. Basically, if you already know HTTP
validation and expiration caching models, you are ready to use most of the
Symfony2 cache system.

.. index::
   single: Cache; Types of
   single: Cache; Proxy
   single: Cache; Reverse Proxy
   single: Cache; Gateway

Kinds of Caches
---------------

HTTP cache headers are consumed and interpreted by three different kind of
caches:

* *Browser caches*: Every browser comes with its own local cache that is
  mainly useful for when you hit "back" or when images are reused throughout a
  website;

* *Proxy caches*: A proxy is a *shared* cache as many people can be behind a
  single one. It's usually installed by large corporations and ISPs to reduce
  latency and network traffic.

* *Gateway caches*: Like a proxy, it's also a shared cache but on the server
  side. Installed by network administrators, it makes websites more scalable,
  reliable and performing better (CDNs like Akamaï are gateway caches).

.. note::

    Gateway caches are sometimes referred to as reverse proxy caches,
    surrogate caches, or even HTTP accelerators.

HTTP 1.1 allows caching anything by default unless there is an explicit
``Cache-Control`` header. In practice, most caches do nothing when requests
have a cookie, an authorization header, or come with a non-safe method, and
when responses have a redirect status code.

Symfony2 automatically sets a sensible and conservative ``Cache-Control``
header when none is set by the developer by following these rules:

* If no cache header is defined (``Cache-Control``, ``ETag``,
  ``Last-Modified``, and ``Expires``), ``Cache-Control`` is set to
  ``no-cache``;

* If ``Cache-Control`` is empty, its value is set to ``private, max-age=0,
  must-revalidate``;

* But if at least one ``Cache-Control`` directive is set, and no 'public' or
  ``private`` directives have been explicitly added, Symfony2 adds the
  ``private`` directive automatically (except when ``s-maxage`` is set).

.. tip::

    Most gateway caches have the ability to remove cookies before forwarding a
    request to the backend application, and add them back when they send the
    response to the browser (that's useful for cookies that do not change the
    resource representation like tracking cookies).

Manipulating Response Headers
-----------------------------

Before we start our tour of the different HTTP headers you can use to enable
caching for your application, you first need to learn how to change them in a
Symfony2 application.

The :class:`Symfony\\Component\\HttpFoundation\\Response` class exposes a nice
and simple API to ease HTTP headers manipulation::

    // pass an array of headers as the third argument to the Response constructor
    $response = new Response($content, $status, $headers);

    // set a header value
    $response->headers->set('Content-Type', 'text/plain');

    // add a header value to the existing values
    $response->headers->set('Vary', 'Accept', false);

    // set a multi-valued header
    $response->headers->set('Vary', array('Accept', 'Accept-Encoding'));

    // delete a header
    $response->headers->delete('Content-Type');

Besides this generic way of setting headers, the Response class also provides
many specialized methods that ease the manipulation of the HTTP cache headers.
You will learn more about them along the way.

.. tip::

    HTTP header names are case insensitive. As Symfony2 converts them to a
    normalized form internally, the case you use does not matter
    (``Content-Type`` is considered the same as ``content-type``). You can
    also use underscores (``_``) instead of dashes (``-``) if you want.

If you use the Controller shortcut method ``render`` to render a template and
create a Response object for you, you can still manipulate the Response
headers easily::

    // Create a Response and set headers first...
    $response = new Response();
    $response->headers->set('Content-Type', 'text/plain');

    // ...and then pass it as the third argument to the render method
    return $this->render($name, $vars, $response);

    // Or, call render first...
    $response = $this->render($name, $vars);

    // ...and manipulate the Response headers afterwards
    $response->headers->set('Content-Type', 'text/plain');

    return $response;

.. index::
   single: Cache; HTTP

Understanding HTTP Cache
------------------------

The HTTP specification (aka `RFC 2616`_) defines two caching models:

* *Expiration*: You specify how long a response should be considered "fresh"
  by including a ``Cache-Control`` and/or an ``Expires`` header. Caches that
  understand expiration will not make the same request until the cached
  version reaches its expiration time and becomes "stale".

* *Validation*: When some pages are really dynamic (meaning that their
  representation changes often), the validation model uses a unique identifier
  (the ``Etag`` header) and/or a timestamp (the ``Last-Modified`` headers) to
  check if the page changed since last time.

The goal of both models is to never generate the same Response twice.

.. tip::

    There is an on-going effort (`HTTP Bis`_) to rewrite the RFC 2616. It does
    not describe a new version of HTTP, but mostly clarifies the original HTTP
    specification. The organization is also much better as the specification
    is split into several parts; everything related to HTTP caching can be
    found in two dedicated parts (`P4 - Conditional Requests`_ and `P6 -
    Caching: Browser and intermediary caches`_).

.. tip::

    The HTTP cache headers only work with "safe" HTTP methods (like GET and
    HEAD). Being safe means that you must never change the application's state
    of the server when serving such requests (but you can of course log
    information, cache data, ...)

.. index::
   single: Cache; HTTP Expiration

Expiration
~~~~~~~~~~

Whenever possible, you should use the expiration caching model as your
application will only be called for the very first request and it will never
be called again until it expires (it saves server CPU and allows for better
scaling).

.. index::
   single: Cache; Expires header
   single: HTTP headers; Expires

Expiration with the ``Expires`` Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

According to RFC 2616, "the ``Expires`` header field gives the date/time after
which the response is considered stale." The ``Expires`` header can be set
with the ``setExpires()`` Response method. It takes a ``DateTime`` instance as
an argument::

    $date = new DateTime();
    $date->modify('+600 seconds');

    $response->setExpires($date);

.. note::

    The ``setExpires()`` method automatically converts the date to the GMT
    timezone as required by the specification (the date must be in the RFC1123
    format).

The ``Expires`` header suffers from two limitations. First, the clocks on the
Web server and the cache (aka the browser) must be synchronized. Then, the
specification states that "HTTP/1.1 servers should not send ``Expires`` dates
more than one year in the future."

.. index::
   single: Cache; Cache-Control header
   single: HTTP headers; Cache-Control

Expiration with the ``Cache-Control`` Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Because of the ``Expires`` header limitations, most of the time, you should
use the ``Cache-Control`` header instead. As ``Cache-Control`` is a
general-field header used to specify many different directives, Symfony2
provides methods that abstract their manipulation. For expiration, there are
two directives, ``max-age`` and ``s-maxage``. The first one is used by all
caches, whereas the second one is only taken into account by shared caches::

    // Sets the number of seconds after which the response
    // should no longer be considered fresh
    $response->setMaxAge(600);

    // Same as above but only for shared caches
    $response->setSharedMaxAge(600);

.. index::
   single: Cache; Validation

Validation
~~~~~~~~~~

When a resource must be updated as soon as a change is made to the underlying
data, the expiration model falls short. The validation model addresses this
issue. Under this model, you mainly save bandwidth as the representation is
not sent twice to the same client (a 304 response is sent instead). But if you
design your application carefully, you might be able to get the bare minimum
data needed to send a 304 response and save CPU also; and if needed, perform
the more heavy tasks (see below for an implementation example).

.. index::
   single: Cache; Etag header
   single: HTTP headers; Etag

Validation with the ``ETag`` Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

According to the RFC, "The ``ETag`` response-header field provides the current
value of the entity-tag for one representation of the target resource. An
entity-tag is intended for use as a resource-local identifier for
differentiating between representations of the same resource that vary over
time or via content negotiation.". "An entity-tag MUST be unique across all
versions of all representations associated with a particular resource."

A possible value for the "entity-tag" can be the hash of the response content
for instance::

    $response->setETag(md5($response->getContent()));

This algorithm is simple enough and very generic, but you need to create the
whole Response before being able to compute the ETag, which is sub-optimal.
This strategy is often used as a default algorithm in many frameworks, but you
should use any algorithm that fits the way you create resources better (see
the section below about optimizing validation).

.. tip::

    Symfony2 also supports weak ETags by passing ``true`` as the second
    argument to the
    :method:`Symfony\\Component\\HttpFoundation\\Response::setETag` method.

.. index::
   single: Cache; Last-Modified header
   single: HTTP headers; Last-Modified

Validation with the ``Last-Modified`` Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

According to the RFC, "The ``Last-Modified`` header field indicates the date
and time at which the origin server believes the representation was last
modified."

For instance, you can use the latest update date for all the objects needed to
compute the resource representation as the value for the ``Last-Modified``
header value::

    $articleDate = new \DateTime($article->getUpdatedAt());
    $authorDate = new \DateTime($author->getUpdatedAt());

    $date = $authorDate > $articleDate ? $authorDate : $articleDate;

    $response->setLastModified($date);

.. index::
   single: Cache; Conditional Get
   single: HTTP; 304

Optimizing your Code with Validation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The main goal of any caching strategy is to lighten the load on the
application; put another way, the less you do in your application to return a
304 response, the better. The Symfony2 ``Response::isNotModified()`` method
does exactly that by exposing a simple and efficient pattern::

    // Get the minimum information to compute
    // the ETag or the Last-Modified value
    // (based on the Request, data are retrieved from
    // a database or a key-value store for instance)
    $article = Article::get(...);

    // create a Response with a ETag and/or a Last-Modified header
    $response = new Response();
    $response->setETag($post->computeETag());
    $response->setLastModified($post->getPublishedAt());

    // Check that the Response is not modified for the given Request
    if ($response->isNotModified($request)) {
        // send the 304 Response immediately
        $response->send();
    } else {
        // do some more heavy stuff here
        // like getting more stuff from the DB
        // and rendering a template
    }

When the Response is not modified, the ``isNotModified()`` automatically sets
the response status code to ``304``, remove the content, and remove some
headers that must not be present for ``304`` responses (see
:method:`Symfony\\Component\\HttpFoundation\\Response::setNotModified`).

.. index::
   single: Cache; Vary
   single: HTTP headers; Vary

Varying the Response
~~~~~~~~~~~~~~~~~~~~

Sometimes, the representation of a resource depends not only on its URI, but
also on some other header values. For instance, if you compress pages when the
client supports it, any given URI has two representations: one when the client
supports compression, and one when it does not. For such cases, you must use
the ``Vary`` header to help the cache determine whether a stored response can
be used to satisfy a given request::

    $response->setVary('Accept-Encoding');

    $response->setVary(array('Accept-Encoding', 'Accept'));

The ``setVary()`` method takes a header name or an array of header names for
which the response varies.

Expiration and Validation
~~~~~~~~~~~~~~~~~~~~~~~~~

You can of course use both validation and expiration within the same Response.
As expiration wins over validation, you can easily benefit from the best of
both worlds. It gives you many ways to configure and tweak your caching
strategy.

.. index::
    pair: Cache; Configuration

More Response Methods
~~~~~~~~~~~~~~~~~~~~~

The Response class provides many more methods related to the cache. Here are
the most useful ones::

    // Mark the Response as private
    $response->setPrivate(true);

    // Mark the Response as public
    $response->setPublic(true);

    // Marks the Response stale
    $response->expire();

Last but not the least, most cache-related HTTP headers can be set via the
single ``setCache()`` method::

    // Set cache settings in one call
    $response->setCache(array(
        'etag'          => $etag,
        'last_modified' => $date,
        'max_age'       => 10,
        'public'        => true,
    ));

Configuring the Cache
---------------------

As you might have guessed, the best configuration to speed your application is
by adding a gateway cache in front of your application. And as Symfony2 only
uses standard HTTP headers to manage its cache, there is no need for a
proprietary cache layer. Instead, you can use any reverse proxy you want like
Apache mod_cache, Squid, or Varnish. If you don't want to install yet another
software, you can also use the Symfony2 reverse proxy, which is written in PHP
and does the same job as any other reverse proxy.

Public vs Private Responses
~~~~~~~~~~~~~~~~~~~~~~~~~~~

As explained at the beginning of this document, Symfony2 is very conservative
and makes all Responses private by default (the exact rules are described
there).

If you want to use a shared cache, you must remember to explicitly add the
``public`` directive to ``Cache-Control``::

    // The Response is private by default
    $response->setEtag($etag);
    $response->setLastModified($date);
    $response->setMaxAge(10);

    // Change the Response to be public
    $response->setPublic();

    // Set cache settings in one call
    $response->setCache(array(
        'etag'          => $etag,
        'last_modified' => $date,
        'max_age'       => 10,
        'public'        => true,
    ));

Symfony2 Reverse Proxy
~~~~~~~~~~~~~~~~~~~~~~

Symfony2 comes with a reverse proxy written in PHP. Enable it and it will
start to cache your application resources right away. Installing it is as easy
as it can get. Each new Symfony2 application comes with a pre-configured
caching Kernel (``AppCache``) that wraps the default one (``AppKernel``).
Modify the code of a front controller so that it reads as follows to enable
caching::

    // web/app.php

    require_once __DIR__.'/../app/AppCache.php';

    // wrap the default AppKernel with the AppCache one
    $kernel = new AppCache(new AppKernel('prod', false));
    $kernel->handle()->send();

.. tip::

    The cache kernel has a special ``getLog()`` method that returns a string
    representation of what happened in the cache layer. In the development
    environment, use it to debug and validate your cache strategy::

        error_log($kernel->getLog());

The ``AppCache`` object has a sensible default configuration, but it can be
finely tuned via a set of options you can set by overriding the
``getOptions()`` method::

    // app/AppCache.php
    class BlogCache extends Cache
    {
        protected function getOptions()
        {
            return array(
                'debug'                  => false,
                'default_ttl'            => 0,
                'private_headers'        => array('Authorization', 'Cookie'),
                'allow_reload'           => false,
                'allow_revalidate'       => false,
                'stale_while_revalidate' => 2,
                'stale_if_error'         => 60,
            );
        }
    }

Here is a list of the main options:

* ``default_ttl``: The number of seconds that a cache entry should be
  considered fresh when no explicit freshness information is provided in a
  response. Explicit ``Cache-Control`` or ``Expires`` headers override this
  value (default: ``0``);

* ``private_headers``: Set of request headers that trigger "private"
  ``Cache-Control`` behavior on responses that don't explicitly state whether
  the response is ``public`` or ``private`` via a ``Cache-Control`` directive.
  (default: ``Authorization`` and ``Cookie``);

* ``allow_reload``: Specifies whether the client can force a cache reload by
  including a ``Cache-Control`` "no-cache" directive in the request. Set it to
  ``true`` for compliance with RFC 2616 (default: ``false``);

* ``allow_revalidate``: Specifies whether the client can force a cache
  revalidate by including a ``Cache-Control`` "max-age=0" directive in the
  request. Set it to ``true`` for compliance with RFC 2616 (default: false);

* ``stale_while_revalidate``: Specifies the default number of seconds (the
  granularity is the second as the Response TTL precision is a second) during
  which the cache can immediately return a stale response while it revalidates
  it in the background (default: ``2``); this setting is overridden by the
  ``stale-while-revalidate`` HTTP ``Cache-Control`` extension (see RFC 5861);

* ``stale_if_error``: Specifies the default number of seconds (the granularity
  is the second) during which the cache can serve a stale response when an
  error is encountered (default: ``60``). This setting is overridden by the
  ``stale-if-error`` HTTP ``Cache-Control`` extension (see RFC 5861).

If ``debug`` is ``true``, Symfony2 automatically adds a ``X-Symfony-Cache``
header to the Response containing useful information about cache hits and
misses.

The Symfony2 reverse proxy is a great tool to use when developing your website
on your local network or when you deploy your website on a shared host where
you cannot install anything beyond PHP code. But being written in PHP, it
cannot be as fast as a proxy written in C. That's why we highly recommend you
to use Squid or Varnish on your production servers if possible. The good news
is that the switch from one proxy server to another is easy and transparent as
no code modification is needed in your application; start easy with the
Symfony2 reverse proxy and upgrade later to Varnish when your traffic raises.

.. note::

    The performance of the Symfony2 reverse proxy is independent of the
    complexity of the application; that's because the application kernel is
    only booted when the request needs to be forwarded to it.

Apache mod_cache
~~~~~~~~~~~~~~~~

If you use Apache, it can act as a simple gateway cache when the mod_cache
extension is enabled.

Squid
~~~~~

Squid is a "regular" proxy server that can also be used as a reverse proxy
server. If you already use Squid in your architecture, you can probably
leverage its power for your Symfony2 applications. If not, we highly recommend
you to use Varnish as it has many advantages over Squid and because it
supports features needed for advanced Symfony2 caching strategies (like ESI
support).

Varnish
~~~~~~~

Varnish is our preferred choice for three main reasons:

* It has been designed as a reverse proxy from day one so its configuration is
  really straightforward;

* Its modern architecture means that it is insanely fast;

* It supports ESI, a technology used by Symfony2 to allow different elements
  of a page to have their own caching strategy (read the next section for more
  information).

.. index::
  single: Cache; ESI
  single: ESI

Using Edge Side Includes
------------------------

Gateway caches are a great way to make your website performs better. But they
have one limitation: they can only cache whole pages. So, if you cannot cache
whole pages or if a page has "more" dynamic parts, you are out of luck.
Fortunately, Symfony2 provides a solution for these cases, based on a
technology called `ESI`_, or Edge Side Includes. Akamaï wrote this
specification almost 10 years ago, and it allows specific parts of a page to
have a different caching strategy that the main page.

The ESI specification describes tags you can embed in your pages to
communicate with the gateway cache. Only one tag is implemented in Symfony2,
``include``, as this is the only useful one outside of Akamaï context:

.. code-block:: html

    <html>
        <body>
            Some content

            <!-- Embed the content of another page here -->
            <esi:include src="http://..." />

            More content
        </body>
    </html>

When a request comes in, the gateway cache gets the page from its cache or
calls the backend application. If the response contains one or more ESI tags,
the proxy behaves like for the main request. It gets the included page content
from its cache or calls the backend application again. Then it merges all the
included content in the main page and sends it back to the client.

.. index::
    single: Helper; actions

As the embedded content comes from another page (or controller for that
matter), Symfony2 uses the standard ``render`` helper to configure ESI tags:

.. configuration-block::

    .. code-block:: php

        <?php echo $view['actions']->render('...:list', array(), array('standalone' => true)) ?>

    .. code-block:: jinja

        {% render '...:list' with [], ['standalone': true] %}

By setting ``standalone`` to ``true``, you tell Symfony2 that the action
should be rendered as an ESI tag. You might be wondering why you would want to
use a helper instead of just writing the ESI tag yourself. That's because
using a helper makes your application works even if there is no gateway cache
installed. Let's see how it works.

When standalone is ``false`` (the default), Symfony2 merges the included page
content within the main one before sending the response to the client. But
when standalone is ``true`` and if Symfony 2 detects that it talks to a
gateway cache that supports ESI, it generates an ESI include tag. But if there
is no gateway cache or if it does not support ESI, Symfony2 will just merge
the included page content within the main one as it would have done when
standalone is ``false``.

.. note::

    Symfony2 detects if a gateway cache supports ESI via another Akamaï
    specification that is supported out of the box by the Symfony2 reverse
    proxy (a working configuration for Varnish is also provided below).

For the ESI include tag to work properly, you must define the ``_internal``
route:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        _internal:
            resource: FrameworkBundle/Resources/config/routing/internal.xml
            prefix:   /_internal

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://www.symfony-project.org/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.symfony-project.org/schema/routing http://www.symfony-project.org/schema/routing/routing-1.0.xsd">

            <import resource="FrameworkBundle/Resources/config/routing/internal.xml" prefix="/_internal" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection->addCollection($loader->import('FrameworkBundle/Resources/config/routing/internal.xml', '/_internal'));

        return $collection;

.. tip::

    You might want to protect this route by either choosing a non easily
    guessable prefix, or by protecting them using the Symfony2 firewall
    feature (by allowing access to your reverse proxies IP range).

One great advantage of this caching strategy is that you can make your
application as dynamic as needed and at the same time, hit the application as
less as possible.

.. note::

    Once you start using ESI, remember to always use the ``s-maxage``
    directive instead of ``max-age``. As the browser only ever receives the
    aggregated resource, it is not aware of the sub-components, and so it will
    obey the ``max-age`` directive and cache the entire page. And you don't
    want that.

.. tip::

    The ``render`` helper supports two other useful options, ``alt`` and
    ``ignore_errors``. They are automatically converted to ``alt`` and
    ``onerror`` attributes when an ESI include tag is generated.

.. index::
    single: Cache; Varnish

Varnish Configuration
~~~~~~~~~~~~~~~~~~~~~

As seen previously, Symfony2 is smart enough to detect whether it talks to a
reverse proxy that understands ESI or not. It works out of the box when you
use the Symfony2 reverse proxy, but you need a special configuration to make
it work with Varnish. Thankfully, Symfony2 relies on yet another standard
written by Akamaï (`Edge Architecture`_), so the configuration tips in this
chapter can be useful even if you don't use Symfony2.

.. note::

    Varnish only supports the ``src`` attribute for ESI tags (``onerror`` and
    ``alt`` attributes are ignored).

First, configure Varnish so that it advertises its ESI support by adding a
``Surrogate-Capability`` header to requests forwarded to the backend
application:

.. code-block:: text

    sub vcl_recv {
        set req.http.Surrogate-Capability = "abc=ESI/1.0";
    }

Then, optimize Varnish so that it only parses the Response contents when there
is at least one ESI tag by checking the ``Surrogate-Control`` header that
Symfony2 adds automatically:

.. code-block:: text

    sub vcl_fetch {
        if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
            unset beresp.http.Surrogate-Control;
            esi;
        }
    }

.. caution::

    Don't use compression with ESI as Varnish won't be able to parse the
    response content. If you want to use compression, put a web server in
    front of Varnish to do the job.

.. index::
    single: Cache; Invalidation

Invalidation
------------

"There are only two hard things in Computer Science: cache invalidation and
naming things." --Phil Karlton

You never need to invalidate cached data because invalidation is already taken
into account natively in the HTTP cache models. If you use validation, you
never need to invalidate anything by definition; and if you use expiration and
need to invalidate a resource, it means that you set the expires date too far
away in the future.

.. note::

    It's also because there is no invalidation mechanism that you can use any
    reverse proxy without changing anything in your application code.

Actually, all reverse proxies provide ways to purge cached data, but you
should avoid them as much as possible. The most standard way is to purge the
cache for a given URL by requesting it with the special ``PURGE`` HTTP method.

.. index::
    single: Cache; Invalidation with Varnish

Here is how you can configure the Symfony2 reverse proxy to support the
``PURGE`` HTTP method::

    // app/AppCache.php
    class AppCache extends Cache
    {
        protected function lookup(Request $request)
        {
            if ('PURGE' !== $request->getMethod()) {
                return parent::lookup($request);
            }

            $response = new Response();
            if (!$this->store->purge($request->getUri())) {
                $response->setStatusCode(404, 'Not purged');
            } else {
                $response->setStatusCode(200, 'Purged');
            }

            return $response;
        }
    }

And the same can be done with Varnish too:

.. code-block:: text

    sub vcl_hit {
        if (req.request == "PURGE") {
            set obj.ttl = 0s;
            error 200 "Purged";
        }
    }

    sub vcl_miss {
        if (req.request == "PURGE") {
            error 404 "Not purged";
        }
    }

.. caution::

    You must protect the ``PURGE`` HTTP method somehow to avoid random people
    purging your cached data.

.. _`RFC 2616`: http://www.ietf.org/rfc/rfc2616.txt
.. _`HTTP Bis`: http://tools.ietf.org/wg/httpbis/
.. _`P4 - Conditional Requests`: http://tools.ietf.org/id/draft-ietf-httpbis-p4-conditional-12.txt
.. _`P6 - Caching: Browser and intermediary caches`: http://tools.ietf.org/id/draft-ietf-httpbis-p6-cache-12.txt
.. _`ESI`: http://www.w3.org/TR/esi-lang
.. _`Edge Architecture`: http://www.w3.org/TR/edge-arch
