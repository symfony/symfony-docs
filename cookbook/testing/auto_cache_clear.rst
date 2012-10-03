How to automatically clear the cache before tests are run
=========================================================

Some features need the cache to be cleared in order to work.
You could have tests failing, because some part of the cache would need to
be cleared. (For example, new translation resources)

It's a good idea to clear the cache before running your tests, and automate it
is even better.

Add the following file::

    // app/tests.bootstrap.php
    if (isset($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'])) {
        passthru(sprintf(
            'php "%s/console" cache:clear --env=%s --no-warmup',
            __DIR__,
            $_ENV['BOOTSTRAP_CLEAR_CACHE_ENV']
        ));
    }

    require __DIR__.'/bootstrap.php.cache';

Replace the test bootstrap file ``bootstrap.php.cache`` in `app/phpunit.xml` by
``tests.bootstrap.php``:

.. code-block:: xml

    <!-- app/phpunit.xml -->
    bootstrap = "tests.bootstrap.php"

And then, you can define in your `phpunit.xml` file which environment you want the
cache to be cleared:

.. code-block:: xml

    <!-- app/phpunit.xml -->
    <php>
        <env name="BOOTSTRAP_CLEAR_CACHE_ENV" value="test"/>
    </php>

