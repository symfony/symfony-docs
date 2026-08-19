Asset Preloading and Resource Hints with WebLink
================================================

Symfony provides native support (via the `WebLink`_ component)
for managing ``Link`` HTTP headers, which are the key to improve the application
performance when using preloading capabilities of modern web browsers.

The component implements `Web Linking`_ (RFC 8288), the two link set document
formats defined by `RFC 9264`_ and the ``Link-Template`` header field defined by
`RFC 9652`_.

``Link`` headers are used to hint resources (e.g. CSS and JavaScript files) to
clients before they even know that they need them. WebLink enables several
optimizations:

* Telling the browser to preload resources that will be needed for the current page;
* Sending `103 Early Hints`_ responses so the browser starts downloading assets
  before the full response is ready (see :ref:`early-hints`);
* Making early DNS lookups, TCP handshakes or TLS negotiations.

.. note::

    Some of these features (like Early Hints or resource hints) work best over
    a secure HTTPS connection. The main web servers (Apache, nginx, Caddy, etc.)
    support this, and you can also use the `Docker installer and runtime for Symfony`_
    created by Kévin Dunglas, from the Symfony community.

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run the following command
to install the WebLink feature before using it:

.. code-block:: terminal

    $ composer require symfony/web-link

Preloading Assets
-----------------

Imagine that your application includes a web page like this:

.. code-block:: html

    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>My Application</title>
        <link rel="stylesheet" href="/app.css">
    </head>
    <body>
        <main role="main" class="container">
            <!-- ... -->
        </main>
    </body>
    </html>

In a traditional HTTP workflow, when this page is loaded, browsers make one
request for the HTML document and another for the linked CSS file. With the
``Link`` HTTP header, your application can hint the browser to preload the
CSS file while processing the HTML.

This is useful for resources that are not directly linked in the HTML but are
needed early (e.g. a font file referenced inside a CSS stylesheet).

To preload a resource, use the ``preload()`` Twig function provided by WebLink.
The `"as" attribute`_ is required, as browsers use it to prioritize resources
correctly and comply with the content security policy:

.. code-block:: html+twig

    <head>
        <!-- ... -->
        <link rel="preload" href="{{ preload('/fonts/myfont.woff2', {as: 'font'}) }}">
        <!-- you can optionally add more attributes to the preload link -->
        <!-- <link rel="preload" href="{{ preload('/fonts/myfont.woff2', {as: 'font', type: 'font/woff2', crossorigin: 'anonymous'}) }}"> -->

        <link rel="stylesheet" href="/app.css">
    </head>

The ``preload()`` function adds a ``Link`` HTTP header to the response (e.g.
``Link: </fonts/myfont.woff2>; rel="preload"; as="font"``). This tells the
browser (or an HTTP/2 compatible server or CDN) to start fetching the resource
as early as possible. You can also combine it with the ``asset()`` function:

.. code-block:: html+twig

    <link rel="preload" href="{{ preload(asset('build/app.css'), {as: 'style'}) }}" as="style">
    <link rel="stylesheet" href="{{ asset('build/app.css') }}">

If you reload the page, the perceived performance will improve because the
browser starts downloading the CSS file as soon as it receives the ``Link``
header, without waiting for the full HTML to be parsed.

.. tip::

    When using the :doc:`AssetMapper component </frontend/asset_mapper>` (e.g.
    ``importmap('app')``), there's no need to add the ``<link rel="preload">``
    tag. The ``importmap()`` Twig function automatically adds the ``Link`` HTTP
    header for you when the WebLink component is available.

Additionally, according to `the Priority Hints specification`_, you can signal
the priority of the resource to download using the ``importance`` attribute:

.. code-block:: html+twig

    <head>
        <!-- ... -->
        <link rel="preload" href="{{ preload('/app.css', {as: 'style', importance: 'low'}) }}" as="style">
        <!-- ... -->
    </head>

How does it work?
~~~~~~~~~~~~~~~~~

The WebLink component manages the ``Link`` HTTP headers added to the response.
When using the ``preload()`` function, a header like this is added to the
response: ``Link </fonts/myfont.woff2>; rel="preload"; as="font"``

When the browser receives this header, it starts downloading the resource right
away, before it encounters the corresponding tag in the HTML.

Popular proxy services and CDNs including `Cloudflare`_, `Fastly`_ and `Akamai`_
also leverage ``Link`` headers to optimize resource delivery and improve
performance of your applications in production.

.. _early-hints:

Sending Early Hints
-------------------

By default, ``Link`` headers are sent along with the final response. However,
you can further improve performance by sending these headers *before* the full
response is ready, using `103 Early Hints`_ responses. This tells the browser
to start downloading assets while the server is still preparing the page.

.. note::

    In order to work, the `SAPI`_ you're using must support this feature, like
    `FrankenPHP`_.

The simplest way to send early hints is by using the ``preload()`` Twig function.
When early hints are supported by your web server, the ``Link`` headers added via
``preload()`` are automatically sent as ``103`` responses:

.. code-block:: html+twig

    <head>
        <!-- ... -->
        <link rel="preload" href="{{ preload('/app.css', {as: 'style'}) }}" as="style">
        <!-- ... -->
    </head>

For more control, you can send early hints explicitly from your controller action thanks to the
:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController::sendEarlyHints`
method::

    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\WebLink\Link;

    class HomepageController extends AbstractController
    {
        #[Route("/", name: "homepage")]
        public function index(): Response
        {
            $response = $this->sendEarlyHints([
                new Link(rel: 'preconnect', href: 'https://fonts.google.com'),
                new Link(href: '/style.css')->withAttribute('as', 'style'),
                new Link(href: '/script.js')->withAttribute('as', 'script'),
            ]);

            // prepare the contents of the response...

            return $this->render('homepage/index.html.twig', response: $response);
        }
    }

Technically, Early Hints are an informational HTTP response with the status code
``103``. The ``sendEarlyHints()`` method creates a ``Response`` object with that
status code and sends its headers immediately.

This way, browsers can start downloading the assets immediately; like the
``style.css`` and ``script.js`` files in the above example. The
``sendEarlyHints()`` method also returns the ``Response`` object, which you
must use to create the full response sent from the controller action.

.. tip::

    When using the :doc:`AssetMapper component </frontend/asset_mapper>`,
    asset file names contain a version hash (e.g.
    ``styles-3c16d9220694c0e56d8648f25e6035e9.css``). To reference the correct
    versioned URL in early hints, use the
    :class:`Symfony\\Component\\AssetMapper\\AssetMapperInterface` service::

        use Symfony\Component\AssetMapper\AssetMapperInterface;

        class HomepageController extends AbstractController
        {
            public function index(AssetMapperInterface $assetMapper): Response
            {
                $response = $this->sendEarlyHints([
                    new Link(href: $assetMapper->getAsset('styles/app.css')->publicPath)
                        ->withAttribute('as', 'style'),
                ]);

                return $this->render('homepage/index.html.twig', response: $response);
            }
        }

Resource Hints
--------------

`Resource Hints`_ are used by applications to help browsers when deciding which
resources should be downloaded, preprocessed or connected to first.

The WebLink component provides the following Twig functions to send those hints:

* ``dns_prefetch()``: "indicates an origin (e.g. ``https://foo.cloudfront.net``)
  that will be used to fetch required resources, and that the user agent should
  resolve as early as possible".
* ``preconnect()``: "indicates an origin (e.g. ``https://www.google-analytics.com``)
  that will be used to fetch required resources. Initiating an early connection,
  which includes the DNS lookup, TCP handshake, and optional TLS negotiation, allows
  the user agent to mask the high latency costs of establishing a connection".
* ``prefetch()``: "identifies a resource that might be required by the next
  navigation, and that the user agent *should* fetch, such that the user agent
  can deliver a faster response once the resource is requested in the future".
* ``prerender()``: " **deprecated** and superseded by the `Speculation Rules API`_,
  identifies a resource that might be required by the next
  navigation, and that the user agent *should* fetch and execute, such that the
  user agent can deliver a faster response once the resource is requested later".

The component also supports sending HTTP links not related to performance and
any link implementing the `PSR-13`_ standard. For instance, any
`link defined in the HTML specification`_:

.. code-block:: html+twig

    <head>
        <!-- ... -->
        <link rel="alternate" href="{{ link('/index.jsonld', 'alternate') }}">
        <link rel="preload" href="{{ preload('/app.css', {as: 'style', nopush: true}) }}" as="style">
        <!-- ... -->
    </head>

The previous snippet will result in this HTTP header being sent to the client:
``Link: </index.jsonld>; rel="alternate",</app.css>; rel="preload"; nopush``

You can also add links to the HTTP response directly from controllers and services::

    // src/Controller/BlogController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\WebLink\GenericLinkProvider;
    use Symfony\Component\WebLink\Link;

    class BlogController extends AbstractController
    {
        public function index(Request $request): Response
        {
            // using the addLink() shortcut provided by AbstractController
            $this->addLink($request, new Link('preload', '/app.css')->withAttribute('as', 'style'));

            // alternative if you don't want to use the addLink() shortcut
            $linkProvider = $request->attributes->get('_links', new GenericLinkProvider());
            $request->attributes->set('_links', $linkProvider->withLink(
                new Link('preload', '/app.css')->withAttribute('as', 'style')
            ));

            return $this->render('...');
        }
    }

.. tip::

    The possible values of link relations (``'preload'``, ``'preconnect'``, etc.)
    are also defined as constants in the :class:`Symfony\\Component\\WebLink\\Link`
    class (e.g. ``Link::REL_PRELOAD``, ``Link::REL_PRECONNECT``, etc.).

Parsing Link Headers
--------------------

Some third-party APIs provide resources such as pagination URLs using the
``Link`` HTTP header. The WebLink component provides the
:class:`Symfony\\Component\\WebLink\\HttpHeaderParser` utility class to parse
those headers and transform them into :class:`Symfony\\Component\\WebLink\\Link`
instances::

    use Symfony\Component\WebLink\HttpHeaderParser;

    $parser = new HttpHeaderParser();
    // get the value of the Link header from the Request
    $linkHeader = '</foo.css>; rel="prerender",</bar.otf>; rel="dns-prefetch"; pr="0.7",</baz.js>; rel="preload"; as="script"';

    $links = $parser->parse($linkHeader)->getLinks();
    $links[0]->getRels();       // ['prerender']
    $links[1]->getAttributes(); // ['pr' => '0.7']
    $links[2]->getHref();       // '/baz.js'

Sending Templated Links
-----------------------

.. versionadded:: 8.2

    Support for the ``Link-Template`` HTTP header was introduced in Symfony 8.2.

The ``Link`` header can only carry concrete URLs. When the target of a link is
not known in advance (e.g. the URL of any item of a collection), describe it
with a `URI template`_ and send it in the ``Link-Template`` header defined by
`RFC 9652`_::

    use Symfony\Component\WebLink\Link;
    use Symfony\Component\WebLink\LinkTemplateHeaderSerializer;

    $links = [
        new Link('item', '/users/{id}'),
        new Link('author', '/books/{book_id}/author')->withAttribute('anchor', '#{book_id}'),
    ];

    $serializer = new LinkTemplateHeaderSerializer();
    $serializer->serialize($links);
    // "/users/{id}"; rel="item", "/books/{book_id}/author"; rel="author"; anchor="#{book_id}"

A link whose target is not a URI template is skipped, as it belongs to the
``Link`` header. Links added to the response are dispatched between both
headers automatically: those pointing at a concrete URL go to ``Link`` and
those using a URI template go to ``Link-Template``::

    // src/Controller/UserController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\WebLink\Link;

    class UserController extends AbstractController
    {
        public function index(Request $request): Response
        {
            $this->addLink($request, new Link('preload', '/app.css')->withAttribute('as', 'style'));
            $this->addLink($request, new Link('item', '/users/{id}'));

            // Link: </app.css>; rel="preload"; as="style"
            // Link-Template: "/users/{id}"; rel="item"

            return $this->render('...');
        }
    }

Reading those headers from a third-party response works the same way as for ``Link``
headers, with the :class:`Symfony\\Component\\WebLink\\LinkTemplateHeaderParser` class::

    use Symfony\Component\WebLink\LinkTemplateHeaderParser;

    $parser = new LinkTemplateHeaderParser();
    $links = $parser->parse('"/users/{id}"; rel="item"')->getLinks();

    $links[0]->getHref();      // '/users/{id}'
    $links[0]->isTemplated();  // true
    $links[0]->getRels();      // ['item']

.. note::

    Unlike ``Link``, the ``Link-Template`` header is an `HTTP structured field`_.
    As required by that specification, a header value which doesn't follow the
    syntax is ignored as a whole, so the parser returns an empty link provider
    instead of throwing an exception.

Publishing a Set of Links
-------------------------

.. versionadded:: 8.2

    Support for link sets was introduced in Symfony 8.2.

A **link set** is a collection of links published as a standalone document instead
of being attached to a given HTTP interaction. `RFC 9264`_ defines two formats
for those documents and WebLink supports both.

The ``application/linkset`` format uses the very same syntax as the ``Link``
header, so the ``HttpHeaderSerializer`` and ``HttpHeaderParser`` classes
described above already produce and read it. The only difference is that
newline characters are allowed as separators, to make documents easier to read.

The ``application/linkset+json`` format is handled by the
:class:`Symfony\\Component\\WebLink\\JsonLinksetSerializer` class::

    use Symfony\Component\WebLink\JsonLinksetSerializer;
    use Symfony\Component\WebLink\Link;

    $links = [
        new Link('next', 'https://example.com/foo')
            ->withAttribute('anchor', 'https://example.net/bar')
            ->withAttribute('type', 'text/html')
            ->withAttribute('hreflang', ['en', 'de']),
    ];

    $serializer = new JsonLinksetSerializer();
    // the second argument is an optional bitmask of json_encode() options
    $document = $serializer->serialize($links, \JSON_UNESCAPED_SLASHES);

The above example returns the following document, where links are grouped by
link context (their ``anchor`` attribute) and then by relation type:

.. code-block:: json

    {
        "linkset": [
            {
                "anchor": "https://example.net/bar",
                "next": [
                    {
                        "href": "https://example.com/foo",
                        "type": "text/html",
                        "hreflang": ["en", "de"]
                    }
                ]
            }
        ]
    }

Use the :class:`Symfony\\Component\\WebLink\\JsonLinksetParser` class to read
such a document::

    use Symfony\Component\WebLink\JsonLinksetParser;

    $parser = new JsonLinksetParser();
    $links = $parser->parse($document)->getLinks();

    $links[0]->getHref(); // 'https://example.com/foo'
    $links[0]->getRels(); // ['next']
    // ['anchor' => 'https://example.net/bar', 'type' => 'text/html', 'hreflang' => ['en', 'de']]
    $links[0]->getAttributes();

Serve the document with the matching ``Content-Type`` and advertise it from the
resources it describes with a link using the ``linkset`` relation type::

    $this->addLink($request, new Link(Link::REL_LINKSET, '/my-linkset')
        ->withAttribute('type', 'application/linkset+json'));

.. _`WebLink`: https://github.com/symfony/web-link
.. _`Docker installer and runtime for Symfony`: https://github.com/dunglas/symfony-docker
.. _`Resource Hints`: https://www.w3.org/TR/resource-hints/
.. _`Cloudflare`: https://blog.cloudflare.com/announcing-support-for-http-2-server-push-2/
.. _`Fastly`: https://docs.fastly.com/en/guides/http2-server-push
.. _`Akamai`: https://http2.akamai.com/
.. _`"as" attribute`: https://w3c.github.io/preload/#as-attribute
.. _`the Priority Hints specification`: https://wicg.github.io/priority-hints/
.. _`103 Early Hints`: https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/103
.. _`SAPI`: https://www.php.net/manual/en/function.php-sapi-name.php
.. _`FrankenPHP`: https://frankenphp.dev
.. _`link defined in the HTML specification`: https://html.spec.whatwg.org/dev/links.html#linkTypes
.. _`PSR-13`: https://www.php-fig.org/psr/psr-13/
.. _`Speculation Rules API`: https://developer.mozilla.org/docs/Web/API/Speculation_Rules_API
.. _`Web Linking`: https://www.rfc-editor.org/rfc/rfc8288.html
.. _`RFC 9264`: https://www.rfc-editor.org/rfc/rfc9264.html
.. _`RFC 9652`: https://www.rfc-editor.org/rfc/rfc9652.html
.. _`URI template`: https://www.rfc-editor.org/rfc/rfc6570.html
.. _`HTTP structured field`: https://www.rfc-editor.org/rfc/rfc9651.html
