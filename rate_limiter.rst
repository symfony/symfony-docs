.. index::
   single: Rate limiter

Rate Limiter Component
========

The Rate Limiter component provides a Token Bucket implementation to rate limit input and output in your application.

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the ``profiler`` :ref:`Symfony pack <symfony-packs>` before using it:

.. code-block:: terminal

    $ composer require symfony/rate-limiter


Using Rate Limiter Component
----------------------------

.. code-block:: php

    use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
    use Symfony\Component\RateLimiter\Limiter;

    $limiter = new Limiter([
        'id' => 'login',
        'strategy' => 'token_bucket', // or 'fixed_window'
        'limit' => 10,
        'rate' => ['interval' => '15 minutes'],
    ], new InMemoryStorage());

    // blocks until 1 token is free to use for this process
    $limiter->reserve(1)->wait();
    // ... execute the code

    // only claims 1 token if it's free at this moment (useful if you plan to skip this process)
    if ($limiter->consume(1)) {
        // ... execute the code
    }


