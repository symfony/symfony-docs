How to Customize the Bootstrap Process before Running Tests
===========================================================

Sometimes when running tests, you need to do additional bootstrap work before
running those tests. For example, if you're running a functional test and
have introduced a new translation resource, then you will need to clear your
cache before running those tests.

To do this, first add a file that executes your bootstrap work::

    // tests/bootstrap.php
    if (isset($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'])) {
        // executes the "php bin/console cache:clear" command
        passthru(sprintf(
            'APP_ENV=%s php "%s/../bin/console" cache:clear --no-warmup',
            $_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'],
            __DIR__
        ));
    }

    require __DIR__.'/../config/bootstrap.php';

Then, configure ``phpunit.xml.dist`` to execute this ``bootstrap.php`` file
before running the tests:

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <?xml version="1.0" encoding="UTF-8"?>
    <phpunit
        bootstrap="tests/bootstrap.php"
    >
        <!-- ... -->
    </phpunit>

Now, you can define in your ``phpunit.xml.dist`` file which environment you want the
cache to be cleared:

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <?xml version="1.0" encoding="UTF-8"?>
    <phpunit>
        <!-- ... -->

        <php>
            <env name="BOOTSTRAP_CLEAR_CACHE_ENV" value="test"/>
        </php>
    </phpunit>

This now becomes an environment variable (i.e. ``$_ENV``) that's available
in the custom bootstrap file (``tests/bootstrap.php``).
