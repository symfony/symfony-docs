Rate Limiter
============

.. versionadded:: 5.2

    The RateLimiter component was introduced in Symfony 5.2 as an
    :doc:`experimental feature </contributing/code/experimental>`.

A "rate limiter" controls how frequently some event (e.g. an HTTP request or a
login attempt) is allowed to happen. Rate limiting is commonly used as a
defensive measure to protect services from excessive use (intended or not) and
maintain their availability. It's also useful to control your internal or
outbound processes (e.g. limit the number of simultaneously processed messages).

Symfony uses these rate limiters in built-in features like "login throttling",
which limits how many failed login attempts a user can make in a given period of
time, but you can use them for your own features too.

Rate Limiting Policies
----------------------

Symfony's rate limiter implements some of the most common policies to enforce
rate limits: **fixed window**, **sliding window**, **token bucket**.

Fixed Window Rate Limiter
~~~~~~~~~~~~~~~~~~~~~~~~~

This is the simplest technique and it's based on setting a limit for a given
interval of time. For example: 5,000 requests per hour or 3 login attempts
every 15 minutes.

Its main drawback is that resource usage is not evenly distributed in time and
it can overload the server at the window edges. In the previous example, a user
could make the 4,999 requests in the last minute of some hour and another 5,000
requests during the first minute of the next hour, making 9,999 requests in
total in two minutes and possibly overloading the server. These periods of
excessive usage are called "bursts".

Sliding Window Rate Limiter
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The sliding window algorithm is an alternative to the fixed window algorithm
designed to reduce bursts. To do that, the rate limit is calculated based on
the current window and the previous window.

For example: the limit is 5,000 requests per hour; a user made 4,000 requests
the previous hour and 500 requests this hour. 15 minutes in to the current hour
(25% of the window) the hit count would be calculated as: 75% * 4,000 + 500 = 3,500.
At this point in time the user can only do 1,500 more requests.

The math shows that the closer the last window is, the more will the hit count
of the last window effect the current limit. This will make sure that a user can
do 5,000 requests per hour but only if they are spread out evenly.

Token Bucket Rate Limiter
~~~~~~~~~~~~~~~~~~~~~~~~~

This technique implements the `token bucket algorithm`_, which defines a
continuously updating budget of resource usage. It roughly works like this:

* A bucket is created with an initial set of tokens;
* A new token is added to the bucket with a predefined frequency (e.g. every second);
* Allowing an event consumes one or more tokens;
* If the bucket still contains tokens, the event is allowed; otherwise, it's denied;
* If the bucket is at full capacity, new tokens are discarded.

Installation
------------

Before using a rate limiter for the first time, run the following command to
install the associated Symfony Component in your application:

.. code-block:: terminal

    $ composer require symfony/rate-limiter

Configuration
-------------

The following example creates two different rate limiters for an API service, to
enforce different levels of service (free or paid):

.. code-block:: yaml

    # config/packages/rate_limiter.yaml
    framework:
        rate_limiter:
            anonymous_api:
                # use 'sliding_window' if you prefer that policy
                policy: 'fixed_window'
                limit: 100
                interval: '60 minutes'
            authenticated_api:
                policy: 'token_bucket'
                limit: 5000
                rate: { interval: '15 minutes', amount: 500 }

.. note::

    The value of the ``interval`` option must be a number followed by any of the
    units accepted by the `PHP date relative formats`_ (e.g. ``3 seconds``,
    ``10 hours``, ``1 day``, etc.)

In the ``anonymous_api`` limiter, after making the first HTTP request, you can
make up to 100 requests in the next 60 minutes. After that time, the counter
resets and you have another 100 requests for the following 60 minutes.

In the ``authenticated_api`` limiter, after making the first HTTP request you
are allowed to make up to 5,000 HTTP requests in total, and this number grows
at a rate of another 500 requests every 15 minutes. If you don't make that
number of requests, the unused ones don't accumulate (the ``limit`` option
prevents that number from being higher than 5,000).

Rate Limiting in Action
-----------------------

After having installed and configured the rate limiter, inject it in any service
or controller and call the ``consume()`` method to try to consume a given number
of tokens. For example, this controller uses the previous rate limiter to control
the number of requests to the API::

    // src/Controller/ApiController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
    use Symfony\Component\RateLimiter\RateLimiterFactory;

    class ApiController extends AbstractController
    {
        // if you're using service autowiring, the variable name must be:
        // "rate limiter name" (in camelCase) + "Limiter" suffix
        public function index(RateLimiterFactory $anonymousApiLimiter)
        {
            // create a limiter based on a unique identifier of the client
            // (e.g. the client's IP address, a username/email, an API key, etc.)
            $limiter = $anonymousApiLimiter->create($request->getClientIp());

            // the argument of consume() is the number of tokens to consume
            // and returns an object of type Limit
            if (false === $limiter->consume(1)->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }

            // you can also use the ensureAccepted() method - which throws a
            // RateLimitExceededException if the limit has been reached
            // $limiter->consume(1)->ensureAccepted();

            // ...
        }

        // ...
    }

.. note::

    In a real application, instead of checking the rate limiter in all the API
    controller methods, create an :doc:`event listener or subscriber </event_dispatcher>`
    for the :ref:`kernel.request event <component-http-kernel-kernel-request>`
    and check the rate limiter once for all requests.

Wait until a Token is Available
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of dropping a request or process when the limit has been reached,
you might want to wait until a new token is available. This can be achieved
using the ``reserve()`` method::

    // src/Controller/ApiController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\RateLimiter\RateLimiterFactory;

    class ApiController extends AbstractController
    {
        public function registerUser(Request $request, RateLimiterFactory $authenticatedApiLimiter)
        {
            $apiKey = $request->headers->get('apikey');
            $limiter = $authenticatedApiLimiter->create($apiKey);

            // this blocks the application until the given number of tokens can be consumed
            $limiter->reserve(1)->wait();

            // optional, pass a maximum wait time (in seconds), a MaxWaitDurationExceededException
            // is thrown if the process has to wait longer. E.g. to wait at most 20 seconds:
            //$limiter->reserve(1, 20)->wait();

            // ...
        }

        // ...
    }

The ``reserve()`` method is able to reserve a token in the future. Only use
this method if you're planning to wait, otherwise you will block other
processes by reserving unused tokens.

.. note::

    Not all strategies allow reserving tokens in the future. These
    strategies may throw a ``ReserveNotSupportedException`` when calling
    ``reserve()``.

    In these cases, you can use ``consume()`` together with ``wait()``, but
    there is no guarantee that a token is available after the wait::

        // ...
        do {
            $limit = $limiter->consume(1);
            $limit->wait();
        } while (!$limit->isAccepted());

Exposing the Rate Limiter Status
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using a rate limiter in APIs, it's common to include some standard HTTP
headers in the response to expose the limit status (e.g. remaining tokens, when
new tokens will be available, etc.)

Use the :class:`Symfony\\Component\\RateLimiter\\RateLimit` object returned by
the ``consume()`` method (also available via the ``getRateLimit()`` method of
the :class:`Symfony\\Component\\RateLimiter\\Reservation` object returned by the
``reserve()`` method) to get the value of those HTTP headers::

    // src/Controller/ApiController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\RateLimiter\RateLimiterFactory;

    class ApiController extends AbstractController
    {
        public function index(RateLimiterFactory $anonymousApiLimiter)
        {
            $limiter = $anonymousApiLimiter->create($request->getClientIp());
            $limit = $limiter->consume();
            $headers = [
                'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
                'X-RateLimit-Retry-After' => $limit->getRetryAfter()->getTimestamp(),
                'X-RateLimit-Limit' => $limit->getLimit(),
            ];

            if (false === $limit->isAccepted()) {
                return new Response(null, Response::HTTP_TOO_MANY_REQUESTS, $headers);
            }

            // ...

            $reponse = new Response('...');
            $response->headers->add($headers);

            return $response;
        }
    }

Rate Limiter Storage and Locking
--------------------------------

Rate limiters use the default cache and locking mechanisms defined in your
Symfony application. If you prefer to change that, use the ``lock_factory`` and
``storage_service`` options:

.. code-block:: yaml

    # config/packages/rate_limiter.yaml
    framework:
        rate_limiter:
            anonymous_api_limiter:
                # ...
                # the value is the name of any cache pool defined in your application
                cache_pool: 'app.redis_cache'
                # or define a service implementing StorageInterface to use a different
                # mechanism to store the limiter information
                storage_service: 'App\RateLimiter\CustomRedisStorage'
                # the value is the name of any lock defined in your application
                lock_factory: 'app.rate_limiter_lock'

.. _`token bucket algorithm`: https://en.wikipedia.org/wiki/Token_bucket
.. _`PHP date relative formats`: https://www.php.net/datetime.formats.relative
