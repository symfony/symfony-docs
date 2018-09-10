WebLink
=======

Symfony natively supports `Web Linking`_. It is especially useful to improve
the performance of your application by leveraging the HTTP/2 protocol and
preloading capabilities of modern web browsers.

By implementing cutting edge web standards, namely `HTTP/2 Server Push`_ and
W3C's `Resource Hints`_, the WebLink component
brings great opportunities to boost webapp's performance.

Thanks to WebLink, HTTP/2 (**h2**) servers are able to push resources to clients
before they even know that they need them (think to CSS or JavaScript
files, or relations of an API resource). WebLink also enables other very
efficient optimisations that work with HTTP 1:

-  telling the browser to fetch or to render another webpage in the
   background ;
-  init early DNS lookups, TCP handshakes or TLS negotiations

Let's discover how easy it is to use it and the real life benefits you
can expect.

To benefit from HTTP/2 Server Pushes, a HTTP/2 server and a HTTPS connection
are mandatory (even in local).
Both Apache, Nginx and Caddy support these protocols.
Be sure they are properly configured before reading.

Alternatively, you can use the `Docker installer and runtime for
Symfony`_ provided by Kévin Dunglas (community supported).

It includes everything you need to run Symfony
(PHP :doc:`configured properly for Symfony </performance>`, and Composer)
as well as a development reverse proxy (Apache) supporting HTTP/2 Server Push
and HTTPS (most clients only support HTTP/2 over TLS).

Unzip the downloaded archive, open a shell in the resulting directory and run
the following command:

.. code-block:: terminal

   # Install Symfony and start the project
   $ docker-compose up

Open ``https://localhost``, if this nice page appears, you
successfully created your first Symfony 4 project and are browsing it in
HTTP/2!

.. image:: /_images/components/weblink/symfony4-http2.png

Let's create a very simple homepage using
the Twig_ templating engine.

The first step is to install the library itself:

.. code-block:: terminal

    composer req twig

Symfony is smart enough to download Twig, to automatically register it,
and to enable Symfony features requiring the library.
It also generates a base HTML5 layout in the ``templates/`` directory.

Now, download Bootstrap_, extract the archive and copy the file
``dist/css/bootstrap.min.css`` in the ``public/`` directory of our
project.

Symfony comes with a `nice integration with of the most popular CSS framework`_.

.. note::

    In a real project, you should use Yarn or NPM with
    :doc:`Symfony Encore </frontend/encore/bootstrap>`
    to install Bootstrap.

Now, it's time to create the template of our homepage:

.. code-block:: html

   <!DOCTYPE html>
   <html>
   <head>
       <meta charset="UTF-8">
       <title>Welcome!</title>
       <link rel="stylesheet" href="/bootstrap.min.css">
   </head>
   <body>
       <main role="main" class="container">
           <h1>Hello World</h1>
           <p class="lead">That's a lot of highly dynamic content, right?</p>
       </main>
   </body>
   </html>

And finally, register our new template as the homepage using the builtin
:doc:`TemplateController </templating/render_without_controller>`:

.. code-block:: yaml

   # config/routes.yaml
   index:
       path: /
       defaults:
         _controller: 'Symfony\Bundle\FrameworkBundle\Controller\TemplateController::templateAction'
         template: 'homepage.html.twig'

Refresh your browser, this nice homepage should appear:

.. image:: /_images/components/weblink/homepage-requests.png

HTTP requests are issued by the browser, one for the homepage, and
another one for Bootstrap. But we know from the very beginning that the
browser **will** need Bootstrap. Instead of waiting that the browser
downloads the homepage, parses the HTML (notice "Initiator: Parser" in
Chrome DevTools), encounters the reference to ``bootstrap.min.css`` and
finally sends a new HTTP request, we could take benefit of the HTTP/2
Push feature to directly send both resources to the browser.

Let's do it! Install the WebLink component:

.. code-block:: terminal

    composer req weblink

As for Twig, Symfony will automatically download and register this component into our app.
Now, update the template to use the ``preload`` Twig helper that
leverages the WebLink component:

.. code:: html+twig

   {# ... #}
       <link rel="stylesheet" href="{{ preload('/bootstrap.min.css') }}">
   {# ... #}

Reload the page:

.. image:: /_images/components/weblink/http2-server-push.png

As you can see (Initiator: Push), both
responses have been sent directly by the server.
``bootstrap.min.css`` has started to be received before the browser even requested it!

.. note::

    Google Chrome provides a nice interface to debug HTTP/2 connections.
    Open ``chrome://net-internals/#http2`` to start the tool.

How does it works?
~~~~~~~~~~~~~~~~~~

The WebLink component tracks Link HTTP headers to add to the response.
When using the ``preload()`` helper, a ``Link`` header
with a `preload`_
``rel`` attribute is added to the response:

.. image:: /_images/components/weblink/response-headers.png

According to `the Preload specification`_,
when a HTTP/2 server detects that the original (HTTP 1) response
contains this HTTP header, it will automatically trigger a push for the
related file in the same HTTP/2 connection.
The Apache server provided in the Docker setup supports this feature.
It's why Bootstrap is pushed
to the client!

Popular proxy services and CDN including
`Cloudflare`_, `Fastly`_ and `Akamai`_ also leverage this feature.
It means that you can push resources to
clients and improve performance of your apps in production right now!
All you need is Symfony 3.3+ and a compatible web server or CDN service.

If you want to prevent the push but let the browser preload the resource by
issuing an early separate HTTP request, use the ``nopush`` attribute:

.. code-block:: html+twig

   {# ... #}
       <link rel="stylesheet" href="{{ preload('/bootstrap.min.css', {nopush: true}) }}">
   {# ... #}

Before using HTTP/2 Push, be sure to read `this great article`_ about
known issues, cache implications and the state of the support in popular
browsers.

In addition to HTTP/2 Push and preloading, the WebLink component also
provide some helpers to send `Resource
Hints <https://www.w3.org/TR/resource-hints/#resource-hints>`__ to
clients, the following helpers are available:

-  ``dns_prefetch``: "indicate an origin that will be used to fetch
   required resources, and that the user agent should resolve as early
   as possible"
-  ``preconnect``: "indicate an origin that will be used to fetch
   required resources. Initiating an early connection, which includes
   the DNS lookup, TCP handshake, and optional TLS negotiation, allows
   the user agent to mask the high latency costs of establishing a
   connection"
-  ``prefetch``: "identify a resource that might be required by the next
   navigation, and that the user agent *should* fetch, such that the
   user agent can deliver a faster response once the resource is
   requested in the future"
-  ``prerender``: "identify a resource that might be required by the
   next navigation, and that the user agent *should* fetch and
   execute, such that the user agent can deliver a faster response once
   the resource is requested in the future"

The component can also be used to send HTTP link not related to
performance. For instance, any `link defined in the HTML specification`_:

.. code:: html+twig

   {# ... #}
       <link rel="alternate" href="{{ link('/index.jsonld', 'alternate') }}">
       <link rel="stylesheet" href="{{ preload('/bootstrap.min.css', {nopush: true}) }}">
   {# ... #}

The previous snippet will result in this HTTP header being sent to the
client:
``Link: </index.jsonld>; rel="alternate",</bootstrap.min.css>; rel="preload"; nopush``

You can also add links to the HTTP response directly from a controller
or any service:

.. code:: php

   // src/Controller/BlogPostAction.php
   namespace App\Controller;

   use Fig\Link\GenericLinkProvider;
   use Fig\Link\Link;
   use Symfony\Component\HttpFoundation\Request;
   use Symfony\Component\HttpFoundation\Response;

   final class BlogPostAction
   {
       public function __invoke(Request $request): Response
       {
           $linkProvider = $request->attributes->get('_links', new GenericLinkProvider());
           $request->attributes->set('_links', $linkProvider->withLink(new Link('preload', '/bootstrap.min.css')));

           return new Response('Hello');
       }
   }

.. code-block:: yaml

   # app/config/routes.yaml
   blog_post:
       path: /post
       defaults:
         _controller: 'App\Controller\BlogPostAction'

.. seealso::

    As all Symfony components, WebLink can be used :doc:`as a
    standalone PHP library </components/weblink>`.

To see how WebLink is used in the wild, take a look to the `Bolt`_
and `Sulu`_ CMS, they both use WebLink to trigger HTTP/2 pushes.

While we're speaking about interoperability, WebLink can deal with any link implementing
`PSR-13`_.

Thanks to Symfony WebLink, there is no excuses to not to switch to HTTP/2!

.. _`Web Linking`_: https://tools.ietf.org/html/rfc5988
.. _`HTTP/2 Server Push`: https://tools.ietf.org/html/rfc7540#section-8.2
.. _`Resource Hints`: https://www.w3.org/TR/resource-hints/
.. _`Twig`: https://twig.symfony.com/
.. _`Docker installer and runtime for Symfony`: https://github.com/dunglas/symfony-docker
.. _`Bootstrap`: https://getbootstrap.com/
.. _`nice integration with of the most popular CSS framework`: https://symfony.com/blog/new-in-symfony-3-4-bootstrap-4-form-theme
.. _`preload`: https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content
.. _`the Preload specification`: https://www.w3.org/TR/preload/#server-push-(http/2)
.. _`Cloudflare`: https://blog.cloudflare.com/announcing-support-for-http-2-server-push-2/
.. _`Fastly`: https://docs.fastly.com/guides/performance-tuning/http2-server-push
.. _`Akamai`: https://blogs.akamai.com/2017/03/http2-server-push-the-what-how-and-why.html
.. _`this great article`: https://www.shimmercat.com/en/blog/articles/whats-push/
.. _`link defined in the HTML specification`: https://html.spec.whatwg.org/dev/links.html#linkTypes
.. _`Bolt`: https://bolt.cm/
.. _`Sulu`: https://sulu.io/
.. _`PSR-13`: http://www.php-fig.org/psr/psr-13/
