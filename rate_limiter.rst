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

Rate Limiting Strategies
------------------------

Symfony's rate limiter implements two of the most common strategies to enforce
rate limits: **fixed window** and **token bucket**.

Fixed Window Rate Limiter
~~~~~~~~~~~~~~~~~~~~~~~~~

This is the simplest technique and it's based on setting a limit for a given
interval of time. For example: 5,000 requests per hour or 3 login attempts
every 15 minutes.

Its main drawback is that resource usage is not evenly distributed in time and
it can overload the server at the window edges. In the previous example, a user
could make the 4,999 requests in the last minute of some hour and another 5,000
requests during the first minute of the next hour, making 9,999 requests in
total in two minutes and possibly overloading the server.

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
                strategy: fixed_window
                limit: 100
                interval: '60 minutes'
            authenticated_api:
                strategy: token_bucket
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
    use Symfony\Component\RateLimiter\Limiter;

    class ApiController extends AbstractController
    {
        // the variable name must be: "rate limiter name" + "limiter" suffix
        public function index(Limiter $anonymousApiLimiter)
        {
            // create a limiter based on a unique identifier of the client
            // (e.g. the client's IP address, a username/email, an API key, etc.)
            $limiter = $anonymousApiLimiter->create($request->getClientIp());

            // the argument of consume() is the number of tokens to consume
            // and returns an object of type Limit
            if (false === $anonymous_api_limiter->consume(1)->isAccepted()) {
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

In other scenarios you may want instead to wait as long as needed until a new
token is available. In those cases, use the ``wait()`` method::

    // src/Controller/ApiController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\RateLimiter\Limiter;

    class ApiController extends AbstractController
    {
        public function registerUser(Request $request, Limiter $authenticatedApiLimiter)
        {
            $apiKey = $request->headers->get('apikey');
            $limiter = $authenticatedApiLimiter->create($apiKey);

            // this blocks the application until the given number of tokens can be consumed
            do {
                $limit = $limiter->consume(1);
                $limit->wait();
            } while (!$limit->isAccepted());

            // ...
        }

        // ...
    }

Rate Limiter Storage and Locking
--------------------------------

Rate limiters use the default cache and locking mechanisms defined in your
Symfony application. If you prefer to change that, use the ``lock`` and
``storage`` options:

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
                storage: 'App\RateLimiter\CustomRedisStorage'
                # the value is the name of any lock defined in your application
                lock: 'app.rate_limiter_lock'

.. _`token bucket algorithm`: https://en.wikipedia.org/wiki/Token_bucket
.. _`PHP date relative formats`: https://www.php.net/datetime.formats.relative
