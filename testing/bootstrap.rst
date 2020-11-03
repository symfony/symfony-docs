How to Customize the Bootstrap Process before Running Tests
===========================================================

Sometimes when running tests, you need to do additional bootstrap work before
running those tests. For example, if you're running a functional test and
have introduced a new translation resource, then you will need to clear your
cache before running those tests.

Symfony already created the following ``tests/bootstrap.php`` file when installing
the package to work with tests. If you don't have this file, create it::

    // tests/bootstrap.php
    use Symfony\Component\Dotenv\Dotenv;

    require dirname(__DIR__).'/vendor/autoload.php';

    if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
        require dirname(__DIR__).'/config/bootstrap.php';
    } elseif (method_exists(Dotenv::class, 'bootEnv')) {
        (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
    }

Then, check that your ``phpunit.xml.dist`` file runs this ``bootstrap.php`` file
before running the tests:

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <?xml version="1.0" encoding="UTF-8" ?>
    <phpunit
        bootstrap="tests/bootstrap.php"
    >
        <!-- ... -->
    </phpunit>

Now, you can define in your ``phpunit.xml.dist`` file which environment you want the
cache to be cleared:

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <?xml version="1.0" encoding="UTF-8" ?>
    <phpunit>
        <!-- ... -->

        <php>
            <env name="BOOTSTRAP_CLEAR_CACHE_ENV" value="test"/>
        </php>
    </phpunit>

This now becomes an environment variable (i.e. ``$_ENV``) that's available
in the custom bootstrap file (``tests/bootstrap.php``).
