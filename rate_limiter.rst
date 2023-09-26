Rate Limiter
============

.. versionadded:: 5.2

    The RateLimiter component was introduced in Symfony 5.2.

A "rate limiter" controls how frequently some event (e.g. an HTTP request or a
login attempt) is allowed to happen. Rate limiting is commonly used as a
defensive measure to protect services from excessive use (intended or not) and
maintain their availability. It's also useful to control your internal or
outbound processes (e.g. limit the number of simultaneously processed messages).

Symfony uses these rate limiters in built-in features like :ref:`login throttling <security-login-throttling>`,
which limits how many failed login attempts a user can make in a given period of
time, but you can use them for your own features too.

.. caution::

    By definition, the Symfony rate limiters require Symfony to be booted
    in a PHP process. This makes them not useful to protect against `DoS attacks`_.
    Such protections must consume the least resources possible. Consider
    using `Apache mod_ratelimit`_, `NGINX rate limiting`_ or proxies (like
    AWS or Cloudflare) to prevent your server from being overwhelmed.

.. _rate-limiter-policies:

Rate Limiting Policies
----------------------

Symfony's rate limiter implements some of the most common policies to enforce
rate limits: **fixed window**, **sliding window**, **token bucket**.

Fixed Window Rate Limiter
~~~~~~~~~~~~~~~~~~~~~~~~~

This is the simplest technique and it's based on setting a limit for a given
interval of time (e.g. 5,000 requests per hour or 3 login attempts every 15
minutes).

In the diagram below, the limit is set to "5 tokens per hour". Each window
starts at the first hit (i.e. 10:15, 11:30 and 12:30). As soon as there are
5 hits (the blue squares) in a window, all others will be rejected (red
squares).

.. raw:: html

    <object data="_images/rate_limiter/fixed_window.svg" type="image/svg+xml"
        alt="A timeline showing fixed windows that accept a maximum of 5 hits."
    ></object>

Its main drawback is that resource usage is not evenly distributed in time and
it can overload the server at the window edges. In this example,
there were 6 accepted requests between 11:00 and 12:00.

This is more significant with bigger limits. For instance, with 5,000 requests
per hour, a user could make 4,999 requests in the last minute of some
hour and another 5,000 requests during the first minute of the next hour,
making 9,999 requests in total in two minutes and possibly overloading the
server. These periods of excessive usage are called "bursts".

Sliding Window Rate Limiter
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The sliding window algorithm is an alternative to the fixed window algorithm
designed to reduce bursts. This is the same example as above, but then
using a 1 hour window that slides over the timeline:

.. raw:: html

    <object data="_images/rate_limiter/sliding_window.svg" type="image/svg+xml"
        alt="The same timeline with a sliding window that accepts only 5 hits in the previous hour."
    ></object>

As you can see, this removes the edges of the window and would prevent the
6th request at 11:45.

To achieve this, the rate limit is approximated based on the current window and
the previous window.

For example: the limit is 5,000 requests per hour; a user made 4,000 requests
the previous hour and 500 requests this hour. 15 minutes in to the current hour
(25% of the window) the hit count would be calculated as: 75% * 4,000 + 500 = 3,500.
At this point in time the user can only do 1,500 more requests.

The math shows that the closer the last window is, the more the hit count
of the last window will affect the current limit. This will make sure that a user can
do 5,000 requests per hour but only if they are evenly spread out.

Token Bucket Rate Limiter
~~~~~~~~~~~~~~~~~~~~~~~~~

This technique implements the `token bucket algorithm`_, which defines
continuously updating the budget of resource usage. It roughly works like this:

#. A bucket is created with an initial set of tokens;
#. A new token is added to the bucket with a predefined frequency (e.g. every second);
#. Allowing an event consumes one or more tokens;
#. If the bucket still contains tokens, the event is allowed; otherwise, it's denied;
#. If the bucket is at full capacity, new tokens are discarded.

The below diagram shows a token bucket of size 4 that is filled with a rate
of 1 token per 15 minutes:

.. raw:: html

    <object data="_images/rate_limiter/token_bucket.svg" type="image/svg+xml"
        alt="A timeline showing the token bucket over time, as described in this section."
    ></object>

This algorithm handles more complex back-off burst management.
For instance, it can allow a user to try a password 5 times and then only
allow 1 every 15 minutes (unless the user waits 75 minutes and they will be
allowed 5 tries again).

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

.. configuration-block::

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

    .. code-block:: xml

        <!-- config/packages/rate_limiter.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:rate-limiter>
                    <!-- policy: use 'sliding_window' if you prefer that policy -->
                    <framework:limiter name="anonymous_api"
                        policy="fixed_window"
                        limit="100"
                        interval="60 minutes"
                    />

                    <framework:limiter name="authenticated_api"
                        policy="token_bucket"
                        limit="5000"
                    >
                        <framework:rate interval="15 minutes"
                            amount="500"
                        />
                    </framework:limiter>
                </framework:rate-limiter>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/rate_limiter.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->rateLimiter()
                ->limiter('anonymous_api')
                    // use 'sliding_window' if you prefer that policy
                    ->policy('fixed_window')
                    ->limit(100)
                    ->interval('60 minutes')
                ;

            $framework->rateLimiter()
                ->limiter('authenticated_api')
                    ->policy('token_bucket')
                    ->limit(5000)
                    ->rate()
                        ->interval('15 minutes')
                        ->amount(500)
                ;
        };

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
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
    use Symfony\Component\RateLimiter\RateLimiterFactory;

    class ApiController extends AbstractController
    {
        // if you're using service autowiring, the variable name must be:
        // "rate limiter name" (in camelCase) + "Limiter" suffix
        public function index(Request $request, RateLimiterFactory $anonymousApiLimiter)
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

            // to reset the counter
            // $limiter->reset();

            // ...
        }
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
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\RateLimiter\RateLimiterFactory;

    class ApiController extends AbstractController
    {
        public function index(Request $request, RateLimiterFactory $anonymousApiLimiter)
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

            $response = new Response('...');
            $response->headers->add($headers);

            return $response;
        }
    }

.. _rate-limiter-storage:

Storing Rate Limiter State
--------------------------

All rate limiter policies require to store their state (e.g. how many hits were
already made in the current time window). By default, all limiters use the
``cache.rate_limiter`` cache pool created with the :doc:`Cache component </cache>`.
This means that every time you clear the cache, the rate limiter will be reset.

You can use the ``cache_pool`` option to override the cache used by a specific limiter
(or even :ref:`create a new cache pool <cache-create-pools>` for it):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/rate_limiter.yaml
        framework:
            rate_limiter:
                anonymous_api:
                    # ...

                    # use the "cache.anonymous_rate_limiter" cache pool
                    cache_pool: 'cache.anonymous_rate_limiter'

    .. code-block:: xml

        <!-- config/packages/rate_limiter.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:rate-limiter>
                    <!-- cache-pool: use the "cache.anonymous_rate_limiter" cache pool -->
                    <framework:limiter name="anonymous_api"
                        policy="fixed_window"
                        limit="100"
                        interval="60 minutes"
                        cache-pool="cache.anonymous_rate_limiter"
                    />

                    <!-- ... -->
                </framework:rate-limiter>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/rate_limiter.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->rateLimiter()
                ->limiter('anonymous_api')
                    // ...

                    // use the "cache.anonymous_rate_limiter" cache pool
                    ->cachePool('cache.anonymous_rate_limiter')
                ;
        };

.. note::

    Instead of using the Cache component, you can also implement a custom
    storage. Create a PHP class that implements the
    :class:`Symfony\\Component\\RateLimiter\\Storage\\StorageInterface` and
    use the ``storage_service`` setting of each limiter to the service ID
    of this class.

Using Locks to Prevent Race Conditions
--------------------------------------

`Race conditions`_ can happen when the same rate limiter is used by multiple
simultaneous requests (e.g. three servers of a company hitting your API at the
same time). Rate limiters use :doc:`locks </lock>` to protect their operations
against these race conditions.

By default, Symfony uses the global lock configured by ``framework.lock``, but
you can use a specific :ref:`named lock <lock-named-locks>` via the
``lock_factory`` option (or none at all):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/rate_limiter.yaml
        framework:
            rate_limiter:
                anonymous_api:
                    # ...

                    # use the "lock.rate_limiter.factory" for this limiter
                    lock_factory: 'lock.rate_limiter.factory'

                    # or don't use any lock mechanism
                    lock_factory: null

    .. code-block:: xml

        <!-- config/packages/rate_limiter.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:rate-limiter>
                    <!-- limiter-factory: use the "lock.rate_limiter.factory" for this limiter -->
                    <framework:limiter name="anonymous_api"
                        policy="fixed_window"
                        limit="100"
                        interval="60 minutes"
                        lock-factory="lock.rate_limiter.factory"
                    />

                    <!-- limiter-factory: or don't use any lock mechanism -->
                    <framework:limiter name="anonymous_api"
                        policy="fixed_window"
                        limit="100"
                        interval="60 minutes"
                        lock-factory="null"
                    />

                    <!-- ... -->
                </framework:rate-limiter>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/rate_limiter.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->rateLimiter()
                ->limiter('anonymous_api')
                    // ...

                    // use the "lock.rate_limiter.factory" for this limiter
                    ->lockFactory('lock.rate_limiter.factory')

                    // or don't use any lock mechanism
                    ->lockFactory(null)
                ;
        };

.. versionadded:: 5.3

    The login throttling doesn't use any lock since Symfony 5.3 to avoid extra load.

.. _`DoS attacks`: https://cheatsheetseries.owasp.org/cheatsheets/Denial_of_Service_Cheat_Sheet.html
.. _`Apache mod_ratelimit`: https://httpd.apache.org/docs/current/mod/mod_ratelimit.html
.. _`NGINX rate limiting`: https://www.nginx.com/blog/rate-limiting-nginx/
.. _`token bucket algorithm`: https://en.wikipedia.org/wiki/Token_bucket
.. _`PHP date relative formats`: https://www.php.net/manual/en/datetime.formats.php#datetime.formats.relative
.. _`Race conditions`: https://en.wikipedia.org/wiki/Race_condition
