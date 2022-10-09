How to Customize the Bootstrap Process before Running Tests
===========================================================

Sometimes when running tests, you need to do additional bootstrap work before
running those tests. For example, if you're running a functional test and
have introduced a new translation resource, then you will need to clear your
cache before running those tests.

When :ref:`installing testing <testing-installation>` using Symfony Flex,
it already created a ``tests/bootstrap.php`` file that is run by PHPUnit
before your tests.

You can modify this file to add custom logic:

.. code-block:: diff

      // tests/bootstrap.php
      use Symfony\Component\Dotenv\Dotenv;

      require dirname(__DIR__).'/vendor/autoload.php';

      if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
          require dirname(__DIR__).'/config/bootstrap.php';
      } elseif (method_exists(Dotenv::class, 'bootEnv')) {
          (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
      }

    + if (isset($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'])) {
    +     // executes the "php bin/console cache:clear" command
    +     passthru(sprintf(
    +         'APP_ENV=%s php "%s/../bin/console" cache:clear --no-warmup',
    +         $_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'],
    +         __DIR__
    +     ));
    + }

.. note::

    If you don't use Symfony Flex, make sure this file is configured as
    bootstrap file in your ``phpunit.xml.dist`` file:

    .. code-block:: xml

        <!-- phpunit.xml.dist -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <phpunit
            bootstrap="tests/bootstrap.php"
        >
            <!-- ... -->
        </phpunit>

Now, you can update the ``phpunit.xml.dist`` file to declare the custom
environment variable introduced to ``tests/bootstrap.php``:

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <?xml version="1.0" encoding="UTF-8" ?>
    <phpunit>
        <php>
            <env name="BOOTSTRAP_CLEAR_CACHE_ENV" value="test"/>
            <!-- ... -->
        </php>

        <!-- ... -->
    </phpunit>

Now, when running ``vendor/bin/phpunit``, the cache will be cleared
automatically by the bootstrap file before running all tests.
