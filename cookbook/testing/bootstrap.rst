How to customize the Bootstrap Process before running Tests
===========================================================

Sometimes when running tests, you need to do additional bootstrap work before
running those tests. For example, if you're running a functional test and
have introduced a new translation resource, then you will need to clear your
cache before running those tests. This cookbook covers how to do that.

First, add the following file::

    // app/tests.bootstrap.php
    if (isset($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'])) {
        passthru(sprintf(
            'php "%s/console" cache:clear --env=%s --no-warmup',
            __DIR__,
            $_ENV['BOOTSTRAP_CLEAR_CACHE_ENV']
        ));
    }

    require __DIR__.'/bootstrap.php.cache';

Replace the test bootstrap file ``bootstrap.php.cache`` in ``app/phpunit.xml.dist``
with ``tests.bootstrap.php``:

.. code-block:: xml

    <!-- app/phpunit.xml.dist -->

    <!-- ... -->
    <phpunit
        ...
        bootstrap = "tests.bootstrap.php"
    >

Now, you can define in your ``phpunit.xml.dist`` file which environment you want the
cache to be cleared:

.. code-block:: xml

    <!-- app/phpunit.xml.dist -->
    <php>
        <env name="BOOTSTRAP_CLEAR_CACHE_ENV" value="test"/>
    </php>

This now becomes an environment variable (i.e. ``$_ENV``) that's available
in the custom bootstrap file (``tests.bootstrap.php``).
