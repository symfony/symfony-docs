.. index::
   single: Mercure

Pushing Data to Clients Using the Mercure Protocol
==================================================

Being able to broadcast data in real-time from servers to clients is a
requirement for many modern web and mobile applications.

Creating an UI reacting in live to changes made by other users
(e.g. a user changes the data currently browsed by several other users,
all UIs are instantly updated),
notifying the user when :doc:`an asynchronous job </messenger>` has been
completed or creating chat applications are among the typical use cases
requiring "push" capabilities.

Symfony provides a straightforward component, built on top of
`the Mercure protocol`_, specifically designed for this class of use cases.

Mercure is an open protocol designed from the ground to publish updates from
server to clients. It is a modern and efficient alternative to timer-based
polling and to WebSocket.

Because it is built on top `Server-Sent Events (SSE)`_, Mercure is supported
out of the box in most modern browsers (Edge and IE require `a polyfill`_) and
has `high-level implementations`_ in many programming languages.

Mercure comes with an authorization mechanism,
automatic re-connection in case of network issues
with retrieving of lost updates, "connection-less" push for smartphones and
auto-discoverability (a supported client can automatically discover and
subscribe to updates of a given resource thanks to a specific HTTP header).

All these features are supported in the Symfony integration.

Unlike WebSocket, which is only compatible with HTTP 1.x,
Mercure leverages the multiplexing capabilities provided by HTTP/2
and HTTP/3 (but also supports older versions of HTTP).

`In this recording`_ you can see how a Symfony web API leverages Mercure
and API Platform to update in live a React app and a mobile app (React Native)
generated using the API Platform client generator.

Installation
------------

Installing the Symfony Component
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In applications using :doc:`Symfony Flex </setup/flex>`, run this command to
install the Mercure support before using it:

.. code-block:: terminal

    $ composer require mercure

Running a Mercure Hub
~~~~~~~~~~~~~~~~~~~~~

To manage persistent connections, Mercure relies on a Hub: a dedicated server
that handles persistent SSE connections with the clients.
The Symfony app publishes the updates to the hub, that will broadcast them to
clients.

.. image:: /_images/mercure/schema.png

An official and open source (AGPL) implementation of a Hub can be downloaded
as a static binary from `Mercure.rocks`_.

Run the following command to start it:

.. code-block:: terminal

    $ JWT_KEY='aVerySecretKey' ADDR='localhost:3000' ALLOW_ANONYMOUS=1 CORS_ALLOWED_ORIGINS=* ./mercure

.. note::

    Alternatively to the binary, a Docker image, a Helm chart for Kubernetes
    and a managed, High Availability Hub are also provided by Mercure.rocks.

.. tip::

    The `API Platform distribution`_ comes with a Docker Compose configuration
    as well as a Helm chart for Kubernetes that are 100% compatible with Symfony,
    and contain a Mercure hub.
    You can copy them in your project, even if you don't use API Platform.

Configuration
-------------

The preferred way to configure the MercureBundle is using
:doc:`environment variables </configuration>`.

Set the URL of your hub as the value of the ``MERCURE_PUBLISH_URL`` env var.
The ``.env`` file of your project has been updated by the Flex recipe to
provide example values.
Set it to the URL of the Mercure Hub (``http://localhost:3000/hub`` by default).

In addition, the Symfony application must bear a `JSON Web Token`_ (JWT)
to the Mercure Hub to be authorized to publish updates.

This JWT should be stored in the ``MERCURE_JWT_SECRET`` environment variable.

The JWT must be signed with the same secret key than the one used by
the Hub to verify the JWT (``aVerySecretKey`` in our example).
Its payload must contain at least the following structure to be allowed to
publish:

.. code-block:: json

    {
        "mercure": {
            "publish": []
        }
    }

Because the array is empty, the Symfony app will only be authorized to publish
public updates (see the authorization_ section for further informations).

.. tip::

    The jwt.io website is a convenient way to create and sign JWTs.
    Checkout this `example JWT`_, that grants publishing rights for all *targets*
    (notice the star in the array).
    Don't forget to set your secret key properly in the bottom of the right panel of the form!

.. caution::

    Don't put the secret key in ``MERCURE_JWT_SECRET``, it will not work!
    This environment variable must contain a JWT, signed with the secret key.

    Also, be sure to keep both the secret key and the JWTs... secrets!

Basic Usage
-----------

Publishing
~~~~~~~~~~

The Mercure Component provides an ``Update`` value object representing
the update to publish. It also provides a ``Publisher`` service to dispatch
updates to the Hub.

The ``Publisher`` service can be injected using the
:doc:`autowiring </service_container/autowiring>` in any other
service, including controllers::

    // src/Controller/PublishController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Mercure\Publisher;
    use Symfony\Component\Mercure\Update;

    class PublishController
    {
        public function __invoke(Publisher $publisher): Response
        {
            $update = new Update(
                'http://example.com/books/1',
                json_encode(['status' => 'OutOfStock'])
            );

            // The Publisher service is an invokable object
            $publisher($update);

            return new Response('published!');
        }
    }

The first parameter to pass to the ``Update`` constructor is
the **topic** being updated. This topic should be an IRI_
(Internationalized Resource Identifier, RFC 3987): a unique identifier
of the resource being dispatched.

Usually, this parameter contains the original URL of the resource
transmitted to the client, but it can be any valid IRI_, it doesn't
have to be an URL that exists (similarly to XML namespaces).

The second parameter of the constructor is the content of the update.
It can be anything, stored in any format.
However, serializing the resource in a hypermedia format such as JSON-LD,
Atom, HTML or XML is recommended.

Subscribing
~~~~~~~~~~~

Subscribing to updates in JavaScript is straightforward:

.. code-block:: javascript

    const es = new EventSource('http://localhost:3000/hub?topic=' + encodeURIComponent('http://example.com/books/1'));
    es.onmessage = e => {
        // Will be called every time an update is published by the server
        console.log(JSON.parse(e.data));
    }

Mercure also allows to subscribe to several topics,
and to use URI Templates as patterns:

.. code-block:: javascript

    // URL is a built-in JavaScript class to manipulate URLs
    const u = new URL('http://localhost:3000/hub');
    u.searchParams.append('topic', 'http://example.com/books/1');
    // Subscribe to updates of several Book resources
    u.searchParams.append('topic', 'http://example.com/books/2');
    // All Review resources will match this pattern
    u.searchParams.append('topic', 'http://example.com/reviews/{id}');

    const es = new EventSource(u);
    es.onmessage = e => {
        console.log(JSON.parse(e.data));
    }

.. tip::

    Google Chrome DevTools natively integrate a `practical UI`_ displaying in live
    the received events:

    .. image:: /_images/mercure/chrome.png

    To use it:

    * open the DevTools
    * select the "Network" tab
    * click on the request to the Mercure hub
    * click on the "EventStream" sub-tab.

.. tip::

    Test if a URI Template match an URL using `the online debugger`_

Async dispatching
-----------------

Instead of calling the ``Publisher`` service directly, you can also let Symfony
dispatching the updates asynchronously thanks to the provided integration with
the Messenger component.

First, be sure :doc:`to install the Messenger component </messenger>`
and to configure properly a transport (if you don't, the handler will
be called synchronously).

Then, dispatch the Mercure ``Update`` to the Messenger's Message Bus,
it will be handled automatically::

    // src/Controller/PublishController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Messenger\MessageBusInterface;
    use Symfony\Component\Mercure\Update;

    class PublishController
    {
        public function __invoke(MessageBusInterface $bus): Response
        {
            $update = new Update(
                'http://example.com/books/1',
                json_encode(['status' => 'OutOfStock'])
            );

            // Sync, or async (RabbitMQ, Kafka...)
            $bus->dispatch($update);

            return new Response('published!');
        }
    }

Discovery
---------

The Mercure protocol comes with a discovery mechanism.
To leverage it, the Symfony application must expose the URL of the Mercure Hub
in a ``Link`` HTTP header.

.. image:: /_images/mercure/discovery.png

You can create ``Link`` headers with the :doc:`WebLink Component </web_link>`,
by using the ``AbstractController::addLink`` helper method::

    // src/Controller/DiscoverController.php
    namespace App\Controller;

    use Fig\Link\Link;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;

    class DiscoverController extends AbstractController
    {
        public function __invoke(Request $request): JsonResponse
        {
            // This parameter is automatically created by the MercureBundle
            $hubUrl = $this->getParameter('mercure.default_hub');

            // Link: <http://localhost:3000/hub>; rel="mercure"
            $this->addLink($request, new Link('mercure', $hubUrl));

            return $this->json([
                '@id' => '/books/1',
                'availability' => 'https://schema.org/InStock',
            ]);
        }
    }

Then, this header can be parsed client-side to find the URL of the Hub,
and to subscribe to it:

.. code-block:: javascript

    // Fetch the original resource served by the Symfony web API
    fetch('/books/1') // Has Link: <http://localhost:3000/hub>; rel="mercure"
        .then(response => {
            // Extract the hub URL from the Link header
            const hubUrl = response.headers.get('Link').match(/<([^>]+)>;\s+rel=(?:mercure|"[^"]*mercure[^"]*")/)[1];

            // Append the topic(s) to subscribe as query parameter
            const h = new URL(hubUrl);
            h.searchParams.append('topic', 'http://example.com/books/{id}');

            // Subscribe to updates
            const es = new EventSource(h);
            es.onmessage = e => console.log(e.data);
        });

Authorization
-------------

Mercure also allows to dispatch updates only to authorized clients.
To do so, set the list of **targets** allowed to receive the update
as the third parameter of the ``Update`` constructor::

    // src/Controller/Publish.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Mercure\Publisher;
    use Symfony\Component\Mercure\Update;

    class PublishController
    {
        public function __invoke(Publisher $publisher): Response
        {
            $update = new Update(
                'http://example.com/books/1',
                json_encode(['status' => 'OutOfStock']),
                ['http://example.com/user/kevin', 'http://example.com/groups/admin'] // Here are the targets
            );

            // Publisher's JWT must contain all of these targets or * in mercure.publish or you'll get a 401
            // Subscriber's JWT must contain at least one of these targets or * in mercure.subscribe to receive the update
            $publisher($update);

            return new Response('published to the selected targets!');
        }
    }

To subscribe to private updates, subscribers must provide
a JWT containing at least one target marking the update to the Hub.

To provide this JWT, the subscriber can use a cookie,
or a ``Authorization`` HTTP header.
Cookies are automatically sent by the browsers when opening an ``EventSource`` connection.
They are the most secure and preferred way when the client is a web browser.
If the client is not a web browser, then using an authorization header is the way to go.

In the following example controller,
the generated cookie contains a JWT, itself containing the appropriate targets.
This cookie will be automatically sent by the web browser when connecting to the Hub.
Then, the Hub will verify the validity of the provided JWT, and extract the targets
from it.

To generate the JWT, we'll use the ``lcobucci/jwt`` library. Install it:

.. code-block:: terminal

    $ composer require lcobucci/jwt

And here is the controller::

    // src/Controller/DiscoverController.php
    namespace App\Controller;

    use Fig\Link\Link;
    use Lcobucci\JWT\Builder;
    use Lcobucci\JWT\Signer\Hmac\Sha256;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    class DiscoverController extends AbstractController
    {
        public function __invoke(Request $request): Response
        {
            $hubUrl = $this->getParameter('mercure.default_hub');
            $this->addLink($request, new Link('mercure', $hubUrl));

            $username = $this->getUser()->getUsername(); // Retrieve the username of the current user
            $token = (new Builder())
                // set other appropriate JWT claims, such as an expiration date
                ->set('mercure', ['subscribe' => "http://example.com/user/$username"]) // could also include the security roles, or anything else
                ->sign(new Sha256(), $this->getParameter('mercure_secret_key')) // don't forget to set this parameter! Test value: aVerySecretKey
                ->getToken();

            $response = $this->json(['@id' => '/demo/books/1', 'availability' => 'https://schema.org/InStock']);
            $response->headers->set(
                'set-cookie',
                sprintf('mercureAuthorization=%s; path=/hub; secure; httponly; SameSite=strict', $token)
            );

            return $response;
        }
    }

.. caution::

    To use the cookie authentication method, the Symfony app and the Hub
    must be served from the same domain (can be different sub-domains).

Generating Programmatically The JWT Used to Publish
---------------------------------------------------

Instead of directly storing a JWT in the configuration,
you can create a service that will return the token used by
the ``Publisher`` object::

    // src/Mercure/MyJwtProvider.php
    namespace App\Mercure;

    final class MyJwtProvider
    {
        public function __invoke(): string
        {
            return 'the-JWT';
        }
    }

Then, reference this service in the bundle configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/mercure.yaml
        mercure:
            hubs:
                default:
                    url: https://mercure-hub.example.com/hub
                    jwt_provider: App\Mercure\MyJwtProvider

    .. code-block:: xml

        <!-- config/packages/mercure.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <config>
            <hub
                name="default"
                url="https://mercure-hub.example.com/hub"
                jwt-provider="App\Mercure\MyJwtProvider"
            />
        </config>

    .. code-block:: php

        // config/packages/mercure.php
        use App\Mercure\MyJwtProvider;

        $container->loadFromExtension('mercure', [
            'hubs' => [
                'default' => [
                    'url' => 'https://mercure-hub.example.com/hub',
                    'jwt_provider' => MyJwtProvider::class,
                ],
            ],
        ]);

This method is especially convenient when using tokens having an expiration
date, that can be refreshed programmatically.

Web APIs
--------

When creating a web API, it's convenient to be able to instantly push
new versions of the resources to all connected devices, and to update
their views.

API Platform can use the Mercure Component to dispatch updates automatically,
every time an API resource is created, modified or deleted.

Start by installing the library using its official recipe:

.. code-block:: terminal

    $ composer require api

Then, creating the following entity is enough to get a fully-featured
hypermedia API, and automatic update broadcasting through the Mercure hub::

    // src/Entity/Book.php
    namespace App\Entity;

    use ApiPlatform\Core\Annotation\ApiResource;
    use Doctrine\ORM\Mapping as ORM;

    /**
    * @ApiResource(mercure=true)
    * @ORM\Entity
    */
    class Book
    {
        /**
         * @ORM\Id
         * @ORM\Column
         */
        public $name;

        /**
         * @ORM\Column
         */
        public $status;
    }

As showcased `in this recording`_, the API Platform Client Generator also
allows to scaffold complete React and React Native applications from this API.
These applications will render the content of Mercure updates in real-time.

Checkout `the dedicated API Platform documentation`_ to learn more about
its Mercure support.

.. _`the Mercure protocol`: https://github.com/dunglas/mercure#protocol-specification
.. _`Server-Sent Events (SSE)`: https://developer.mozilla.org/docs/Server-sent_events
.. _`a polyfill`: https://github.com/Yaffle/EventSource
.. _`high-level implementations`: https://github.com/dunglas/mercure#tools
.. _`In this recording`: https://www.youtube.com/watch?v=UI1l0JOjLeI
.. _`API Platform`: https://api-platform.com
.. _`Mercure.rocks`: https://mercure.rocks
.. _`API Platform distribution`: https://api-platform.com/docs/distribution/
.. _`JSON Web Token`: https://tools.ietf.org/html/rfc7519
.. _`example JWT`: https://jwt.io/#debugger-io?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOlsiKiJdfX0.iHLdpAEjX4BqCsHJEegxRmO-Y6sMxXwNATrQyRNt3GY
.. _`IRI`: https://tools.ietf.org/html/rfc3987
.. _`practical UI`: https://twitter.com/chromedevtools/status/562324683194785792
.. _`the dedicated API Platform documentation`: https://api-platform.com/docs/core/mercure/
.. _`the online debugger`: https://uri-template-tester.mercure.rocks
