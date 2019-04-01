.. index::
    single: Cache; Validation

HTTP Cache Validation
=====================

When a resource needs to be updated as soon as a change is made to the underlying
data, the expiration model falls short. With the `expiration model`_, the
application won't be asked to return the updated response until the cache
finally becomes stale.

The validation model addresses this issue. Under this model, the cache continues
to store responses. The difference is that, for each request, the cache asks the
application if the cached response is still valid or if it needs to be regenerated.
If the cache *is* still valid, your application should return a 304 status code
and no content. This tells the cache that it's ok to return the cached response.

Under this model, you only save CPU if you're able to determine that the
cached response is still valid by doing *less* work than generating the whole
page again (see below for an implementation example).

.. tip::

    The 304 status code means "Not Modified". It's important because with
    this status code the response does *not* contain the actual content being
    requested. Instead, the response only consists of the response headers that
    tells the cache that it can use its stored version of the content.

Like with expiration, there are two different HTTP headers that can be used
to implement the validation model: ``ETag`` and ``Last-Modified``.

.. include:: /http_cache/_expiration-and-validation.rst.inc

.. index::
    single: Cache; Etag header
    single: HTTP headers; Etag

Validation with the ``ETag`` Header
-----------------------------------

The ``ETag`` header is a string header (called the "entity-tag") that uniquely
identifies one representation of the target resource. It's entirely generated
and set by your application so that you can tell, for example, if the ``/about``
resource that's stored by the cache is up-to-date with what your application
would return. An ``ETag`` is like a fingerprint and is used to quickly compare
if two different versions of a resource are equivalent. Like fingerprints,
each ``ETag`` must be unique across all representations of the same resource.

To see a simple implementation, generate the ETag as the md5 of the content::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;

    class DefaultController extends AbstractController
    {
        public function homepage(Request $request)
        {
            $response = $this->render('static/homepage.html.twig');
            $response->setEtag(md5($response->getContent()));
            $response->setPublic(); // make sure the response is public/cacheable
            $response->isNotModified($request);

            return $response;
        }
    }

The :method:`Symfony\\Component\\HttpFoundation\\Response::isNotModified`
method compares the ``If-None-Match`` header with the ``ETag`` response header.
If the two match, the method automatically sets the ``Response`` status code
to 304.

.. note::

    The cache sets the ``If-None-Match`` header on the request to the ``ETag``
    of the original cached response before sending the request back to the
    app. This is how the cache and server communicate with each other and
    decide whether or not the resource has been updated since it was cached.

This algorithm is simple enough and very generic, but you need to create the
whole ``Response`` before being able to compute the ETag, which is sub-optimal.
In other words, it saves on bandwidth, but not CPU cycles.

In the :ref:`optimizing-cache-validation` section, you'll see how validation
can be used more intelligently to determine the validity of a cache without
doing so much work.

.. tip::

    Symfony also supports weak ETags by passing ``true`` as the second
    argument to the
    :method:`Symfony\\Component\\HttpFoundation\\Response::setEtag` method.

.. index::
    single: Cache; Last-Modified header
    single: HTTP headers; Last-Modified

Validation with the ``Last-Modified`` Header
--------------------------------------------

The ``Last-Modified`` header is the second form of validation. According
to the HTTP specification, "The ``Last-Modified`` header field indicates
the date and time at which the origin server believes the representation
was last modified." In other words, the application decides whether or not
the cached content has been updated based on whether or not it's been updated
since the response was cached.

For instance, you can use the latest update date for all the objects needed to
compute the resource representation as the value for the ``Last-Modified``
header value::

    // src/Controller/ArticleController.php
    namespace App\Controller;

    // ...
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\Request;
    use App\Entity\Article;

    class ArticleController extends AbstractController
    {
        public function show(Article $article, Request $request)
        {
            $author = $article->getAuthor();

            $articleDate = new \DateTime($article->getUpdatedAt());
            $authorDate = new \DateTime($author->getUpdatedAt());

            $date = $authorDate > $articleDate ? $authorDate : $articleDate;

            $response = new Response();
            $response->setLastModified($date);
            // Set response as public. Otherwise it will be private by default.
            $response->setPublic();

            if ($response->isNotModified($request)) {
                return $response;
            }

            // ... do more work to populate the response with the full content

            return $response;
        }
    }

The :method:`Symfony\\Component\\HttpFoundation\\Response::isNotModified`
method compares the ``If-Modified-Since`` header with the ``Last-Modified``
response header. If they are equivalent, the ``Response`` will be set to a
304 status code.

.. note::

    The cache sets the ``If-Modified-Since`` header on the request to the ``Last-Modified``
    of the original cached response before sending the request back to the
    app. This is how the cache and server communicate with each other and
    decide whether or not the resource has been updated since it was cached.

.. index::
    single: Cache; Conditional get
    single: HTTP; 304

.. _optimizing-cache-validation:

Optimizing your Code with Validation
------------------------------------

The main goal of any caching strategy is to lighten the load on the application.
Put another way, the less you do in your application to return a 304 response,
the better. The ``Response::isNotModified()`` method does exactly that by
exposing a simple and efficient pattern::

    // src/Controller/ArticleController.php
    namespace App\Controller;

    // ...
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\Request;

    class ArticleController extends AbstractController
    {
        public function show($articleSlug, Request $request)
        {
            // Get the minimum information to compute
            // the ETag or the Last-Modified value
            // (based on the Request, data is retrieved from
            // a database or a key-value store for instance)
            $article = ...;

            // create a Response with an ETag and/or a Last-Modified header
            $response = new Response();
            $response->setEtag($article->computeETag());
            $response->setLastModified($article->getPublishedAt());

            // Set response as public. Otherwise it will be private by default.
            $response->setPublic();

            // Check that the Response is not modified for the given Request
            if ($response->isNotModified($request)) {
                // return the 304 Response immediately
                return $response;
            }

            // do more work here - like retrieving more data
            $comments = ...;

            // or render a template with the $response you've already started
            return $this->render('article/show.html.twig', [
                'article' => $article,
                'comments' => $comments,
            ], $response);
        }
    }

When the ``Response`` is not modified, the ``isNotModified()`` automatically sets
the response status code to ``304``, removes the content, and removes some
headers that must not be present for ``304`` responses (see
:method:`Symfony\\Component\\HttpFoundation\\Response::setNotModified`).

.. _`expiration model`: https://tools.ietf.org/html/rfc2616#section-13.2
.. _`validation model`: http://tools.ietf.org/html/rfc2616#section-13.3
