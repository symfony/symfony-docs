.. index::
   single: Tests

Testing
=======

Whenever you write a new line of code, you also potentially add new bugs.
To build better and more reliable applications, you should test your code
using both functional and unit tests.

The PHPUnit Testing Framework
-----------------------------

Symfony integrates with an independent library called `PHPUnit`_ to give
you a rich testing framework. This article won't cover PHPUnit itself,
which has its own excellent `documentation`_.

Before creating your first test, install ``phpunit/phpunit`` and the
``symfony/test-pack``, which installs some other packages providing useful
Symfony test utilities:

.. code-block:: terminal

    $ composer require --dev phpunit/phpunit symfony/test-pack

After the library is installed, try running PHPUnit:

.. code-block:: terminal

    $ php ./vendor/bin/phpunit

This commands automatically runs your application's tests. Each test is a
PHP class ending with "Test" (e.g. ``BlogControllerTest``) that lives in
the ``tests/`` directory of your application.

PHPUnit is configured by the ``phpunit.xml.dist`` file in the root of your
application.

.. note::

    :ref:`Symfony Flex <symfony-flex>` automatically creates
    ``phpunit.xml.dist`` and ``tests/bootstrap.php``. If these files are
    missing, you can try running the recipe again using
    ``composer recipes:install phpunit/phpunit --force -v``.

Types of Tests
--------------

There are many types of automated tests and precise definitions often
differ from project to project. In Symfony, the following definitions are
used. If you have learned something different, that is not necessarily
wrong, just different from what the Symfony documentation is using.

`Unit Tests`_
    These tests ensure that *individual* units of source code (e.g. a single
    class) behave as intended.

`Integration Tests`_
    These tests test a combination of classes and commonly interact with
    Symfony's service container. These tests do not yet cover the fully
    working application, those are called *Application tests*.

`Application Tests`_
    Application tests test the behavior of a complete application. They
    make HTTP requests (both real and simulated ones) and test that the
    response is as expected.

Unit Tests
----------

A `unit test`_ ensures that individual units of source code (e.g. a single
class or some specific method in some class) meet their design and behave
as intended. Writing unit tests in a Symfony application is no different
from writing standard PHPUnit unit tests. You can learn about it in the
PHPUnit documentation: `Writing Tests for PHPUnit`_.

By convention, the ``tests/`` directory should replicate the directory
of your application for unit tests. So, if you're testing a class in the
``src/Form/`` directory, put the test in the ``tests/Form/`` directory.
Autoloading is automatically enabled via the ``vendor/autoload.php`` file
(as configured by default in the ``phpunit.xml.dist`` file).

You can run tests using the ``./vendor/bin/phpunit`` command:

.. code-block:: terminal

    # run all tests of the application
    $ php ./vendor/bin/phpunit

    # run all tests in the Form/ directory
    $ php ./vendor/bin/phpunit tests/Form

    # run tests for the UserType class
    $ php ./vendor/bin/phpunit tests/Form/UserTypeTest.php

.. tip::

    In large test suites, it can make sense to create subdirectories for
    each type of tests (e.g. ``tests/Unit/`` and ``test/Functional/``).

.. _integration-tests:

Integration Tests
-----------------

An integration test will test a larger part of your application compared to
a unit test (e.g. a combination of services). Integration tests might want
to use the Symfony Kernel to fetch a service from the dependency injection
container.

Symfony provides a :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\KernelTestCase`
class to help you creating and booting the kernel in your tests using
``bootKernel()``::

    // tests/Service/NewsletterGeneratorTest.php
    namespace App\Tests\Service;

    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

    class NewsletterGeneratorTest extends KernelTestCase
    {
        public function testSomething()
        {
            self::bootKernel();

            // ...
        }
    }

The ``KernelTestCase`` also makes sure your kernel is rebooted for each
test. This assures that each test is run independently from eachother.

To run your application tests, the ``KernelTestCase`` class needs to
find the application kernel to initialize. The kernel class is
usually defined in the ``KERNEL_CLASS`` environment variable
(included in the default ``.env.test`` file provided by Symfony Flex):

.. code-block:: env

    # .env.test
    KERNEL_CLASS=App\Kernel

.. note::

    If your use case is more complex, you can also override the
    ``getKernelClass()`` or ``createKernel()`` methods of your functional
    test, which take precedence over the ``KERNEL_CLASS`` env var.

Set-up your Test Environment
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The tests create a kernel that runs in the ``test``
:ref:`environment <configuration-environments>`. This allows to have
special settings for your tests inside ``config/packages/test/``.

If you have Symfony Flex installed, some packages already installed some
useful test configuration. For example, by default, the Twig bundle is
configured to be especially strict to catch errors before deploying your
code to production:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/test/twig.yaml
        twig:
            strict_variables: true

    .. code-block:: xml

        <!-- config/packages/test/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <framework:config strict-variables="true"/>
        </container>

    .. code-block:: php

        // config/packages/test/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig) {
            $twig->strictVariables(true);
        };

You can also use a different environment entirely, or override the default
debug mode (``true``) by passing each as options to the ``bootKernel()``
method::

    self::bootKernel([
        'environment' => 'my_test_env',
        'debug'       => false,
    ]);

.. tip::

    It is recommended to run your test with ``debug`` set to ``false`` on
    your CI server, as it significantly improves test performance. This
    disables clearing the cache. If your tests don't run in a clean
    environment each time, you have to manually clear it using for instance
    this code in ``tests/bootstrap.php``::

        // ...

        // ensure a fresh cache when debug mode is disabled
        (new \Symfony\Component\Filesystem\Filesystem())->remove(__DIR__.'/../var/cache/test');

Customizing Environment Variables
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to customize some environment variables for your tests (e.g. the
``DATABASE_URL`` used by Doctrine), you can do that by overriding anything you
need in your ``.env.test`` file:

.. code-block:: text

    # .env.test

    # ...
    DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name_test?serverVersion=5.7"

In the test environment, these env files are read (if vars are duplicated
in them, files lower in the list override previous items):

#. ``.env``: containing env vars with application defaults;
#. ``.env.test``: overriding/setting specific test values or vars;
#. ``.env.test.local``: overriding settings specific for this machine.

.. caution::

    The ``.env.local`` file is **not** used in the test environment, to
    make each test set-up as consistent as possible.

Retrieving Services in the Test
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In your integration tests, you often need to fetch the service from the
service container to call a specific method. After booting the kernel,
the container is stored in ``self::$container``::

    // tests/Service/NewsletterGeneratorTest.php
    namespace App\Tests\Service;

    use App\Service\NewsletterGenerator;
    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

    class NewsletterGeneratorTest extends KernelTestCase
    {
        public function testSomething()
        {
            // (1) boot the Symfony kernel
            self::bootKernel();

            // (2) use self::$container to access the service container
            $container = self::$container;

            // (3) run some service & test the result
            $newsletterGenerator = $container->get(NewsletterGenerator::class);
            $newsletter = $newsletterGenerator->generateMonthlyNews(...);

            $this->assertEquals(..., $newsletter->getContent());
        }
    }

The container in ``self::$container`` is actually a special test container.
It gives you access to both the public services and the non-removed
:ref:`private services <container-public>` services.

.. note::

    If you need to test private services that have been removed (those who
    are not used by any other services), you need to declare those private
    services as public in the ``config/services_test.yaml`` file.

.. _testing-databases:

Configuring a Database for Tests
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Tests that interact with the database should use their own separate
database to not mess with the databases used in the other
:ref:`configuration environments <configuration-environments>`.

To do that, edit or create the ``.env.test.local`` file at the root
directory of your project and define the new value for the ``DATABASE_URL``
env var:

.. code-block:: env

    # .env.test.local
    DATABASE_URL="mysql://USERNAME:PASSWORD@127.0.0.1:3306/DB_NAME?serverVersion=5.7"

This assumes that each developer/machine uses a different database for the
tests. If the test set-up is the same on each machine, use the ``.env.test``
file instead and commit it to the shared repository. Learn more about
:ref:`using multiple .env files in Symfony applications <configuration-multiple-env-files>`.

After that, you can create the test database and all tables using:

.. code-block:: terminal

    # create the test database
    $ php bin/console --env=test doctrine:database:create

    # create the tables/columns in the test database
    $ php bin/console --env=test doctrine:schema:create

.. tip::

    A common practice is to append the ``_test`` suffix to the original
    database names in tests. If the database name in production is called
    ``project_acme`` the name of the testing database could be
    ``project_acme_test``.

Resetting the Database Automatically Before each Test
.....................................................

Tests should be independent from each other to avoid side effects. For
example, if some test modifies the database (by adding or removing an
entity) it could change the results of other tests.

The `DAMADoctrineTestBundle`_ uses Doctrine transactions to let each test
interact with an unmodified database. Install it using:

.. code-block:: terminal

    $ composer require --dev dama/doctrine-test-bundle

Now, enable it as a PHPUnit extension:

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <phpunit>
        <!-- ... -->

        <extensions>
            <extension class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension"/>
        </extensions>
    </phpunit>

That's it! This bundle uses a clever trick: it begins a database
transaction before every test and rolls it back automatically after the
test finishes to undo all changes. Read more in the documentation of the
`DAMADoctrineTestBundle`_.

.. _doctrine-fixtures:

Load Dummy Data Fixtures
........................

Instead of using the real data from the production database, it's common to
use fake or dummy data in the test database. This is usually called
*"fixtures data"* and Doctrine provides a library to create and load them.
Install it with:

.. code-block:: terminal

    $ composer require --dev doctrine/doctrine-fixtures-bundle

Then, use the ``make:fixtures`` command of the `SymfonyMakerBundle`_ to
generate an empty fixture class:

.. code-block:: terminal

    $ php bin/console make:fixtures

    The class name of the fixtures to create (e.g. AppFixtures):
    > ProductFixture

Then you modify use this class to load new entities in the database. For
instance, to load ``Product`` objects into Doctrine, use::

    // src/DataFixtures/ProductFixture.php
    namespace App\DataFixtures;

    use App\Entity\Product;
    use Doctrine\Bundle\FixturesBundle\Fixture;
    use Doctrine\Persistence\ObjectManager;

    class ProductFixture extends Fixture
    {
        public function load(ObjectManager $manager)
        {
            $product = new Product();
            $product->setName('Priceless widget');
            $product->setPrice(14.50);
            $product->setDescription('Ok, I guess it *does* have a price');
            $manager->persist($product);

            // add more products

            $manager->flush();
        }
    }

Empty the database and reload *all* the fixture classes with:

.. code-block:: terminal

    $ php bin/console doctrine:fixtures:load

For more information, read the `DoctrineFixturesBundle documentation`_.

.. _functional-tests:

Application Tests
-----------------

Application tests check the integration of all the different layers of the
application (from the routing to the views). They are no different from
unit tests or integration tests as far as PHPUnit is concerned, but they
have a very specific workflow:

#. Make a request;
#. Click on a link or submit a form;
#. Test the response;
#. Rinse and repeat.

.. note::

    The tools used in this section can be installed via the ``symfony/test-pack``,
    use ``composer require symfony/test-pack`` if you haven't done so already.

Write Your First Application Test
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Application tests are PHP files that typically live in the ``tests/Controller/``
directory of your application. They often extend
:class:`Symfony\\Bundle\\FrameworkBundle\\Test\\WebTestCase`. This class
adds special logic on top of the ``KernelTestCase``. You can read more
about that in the above :ref:`section on integration tests <integration-tests>`.

If you want to test the pages handled by your
``PostController`` class, start by creating a new ``PostControllerTest``
using the ``make:test`` command of the `SymfonyMakerBundle`_:

.. code-block:: terminal

    $ php bin/console make:test

     Which test type would you like?:
     > WebTestCase

     The name of the test class (e.g. BlogPostTest):
     > Controller\PostControllerTest

This creates the following test class::

    // tests/Controller/PostControllerTest.php
    namespace App\Tests\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class PostControllerTest extends WebTestCase
    {
        public function testSomething(): void
        {
            // This calls KernelTestCase::bootKernel(), and creates a
            // "client" that is acting as the browser
            $client = static::createClient();

            // Request a specific page
            $crawler = $client->request('GET', '/');

            // Validate a successful response and some content
            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('h1', 'Hello World');
        }
    }

In the above example, the test validates that the HTTP response was successful
and the request body contains a ``<h1>`` tag with ``"Hello world"``. The
``createClient()`` method also returns a client, which is like a browser
that you can use to crawl your site::

    $crawler = $client->request('GET', '/post/hello-world');

    // for instance, count the number of ``.comment`` elements on the page
    $this->assertCount(4, $crawler->filter('.comment'));

The ``request()`` method (read
:ref:`more about the request method <testing-request-method-sidebar>`)
returns a :class:`Symfony\\Component\\DomCrawler\\Crawler` object which can
be used to select elements in the response, click on links and submit forms.

Working with the Test Client
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The test client simulates an HTTP client like a browser and makes requests
into your Symfony application::

    $crawler = $client->request('GET', '/post/hello-world');

The ``request()`` method takes the HTTP method and a URL as arguments and
returns a ``Crawler`` instance.

.. tip::

    Hardcoding the request URLs is a best practice for application tests.
    If the test generates URLs using the Symfony router, it won't detect
    any change made to the application URLs which may impact the end users.

Use the crawler to find DOM elements in the response. These elements can then
be used to click on links and submit forms::

    $crawler = $client->clickLink('Go elsewhere...');

    $crawler = $client->submitForm('validate', ['name' => 'Fabien']);

The ``clickLink()`` and ``submitForm()`` methods both return a ``Crawler`` object.
These methods are the best way to browse your application as it takes care
of a lot of things for you, like detecting the HTTP method from a form and
giving you a nice API for uploading files.

The ``request()`` method can also be used to simulate form submissions directly
or perform more complex requests. Some useful examples::

    // submits a form directly (but using the Crawler is easier!)
    $client->request('POST', '/submit', ['name' => 'Fabien']);

    // submits a raw JSON string in the request body
    $client->request(
        'POST',
        '/submit',
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        '{"name":"Fabien"}'
    );

    // Form submission with a file upload
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    $photo = new UploadedFile(
        '/path/to/photo.jpg',
        'photo.jpg',
        'image/jpeg',
        null
    );
    $client->request(
        'POST',
        '/submit',
        ['name' => 'Fabien'],
        ['photo' => $photo]
    );

    // Perform a DELETE request and pass HTTP headers
    $client->request(
        'DELETE',
        '/post/12',
        [],
        [],
        ['PHP_AUTH_USER' => 'username', 'PHP_AUTH_PW' => 'pa$$word']
    );

Last but not least, you can force each request to be executed in its own PHP
process to avoid any side effects when working with several clients in the same
script::

    $client->insulate();

.. _testing-request-method-sidebar:

.. sidebar:: More about the ``request()`` Method:

    The full signature of the ``request()`` method is::

        request(
            string $method,
            string $uri,
            array $parameters = [],
            array $files = [],
            array $server = [],
            string $content = null,
            bool $changeHistory = true
        )

    The ``server`` array is the raw values that you'd expect to normally
    find in the PHP `$_SERVER`_ superglobal. For example, to set the
    ``Content-Type`` and ``Referer`` HTTP headers, you'd pass the following (mind
    the ``HTTP_`` prefix for non standard headers)::

        $client->request(
            'GET',
            '/post/hello-world',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_REFERER' => '/foo/bar',
            ]
        );

.. _testing_logging_in_users:

Logging in Users (Authentication)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 5.1

    The ``loginUser()`` method was introduced in Symfony 5.1.

When you want to add application tests for protected pages, you have to
first "login" as a user. Reproducing the actual steps - such as
submitting a login form - make a test very slow. For this reason, Symfony
provides a ``loginUser()`` method to simulate logging in in your functional
tests.

Instead of logging in with real users, it's recommended to create a user only for
tests. You can do that with Doctrine :ref:`data fixtures <user-data-fixture>`,
to load the testing users only in the test database.

After loading users in your database, use your user repository to fetch
this user and use
:method:`$client->loginUser() <Symfony\\Bundle\\FrameworkBundle\\KernelBrowser::loginUser>`
to simulate a login request::

    // tests/Controller/ProfileControllerTest.php
    namespace App\Tests\Controller;

    use App\Repository\UserRepository;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class ProfileControllerTest extends WebTestCase
    {
        // ...

        public function testVisitingWhileLoggedIn()
        {
            $client = static::createClient();
            $userRepository = static::$container->get(UserRepository::class);

            // retrieve the test user
            $testUser = $userRepository->findOneByEmail('john.doe@example.com');

            // simulate $testUser being logged in
            $client->loginUser($testUser);

            // test e.g. the profile page
            $client->request('GET', '/profile');
            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('h1', 'Hello John!');
        }
    }

You can pass any
:class:`Symfony\\Component\\Security\\Core\\User\\UserInterface` instance to
``loginUser()``. This method creates a special
:class:`Symfony\\Bundle\\FrameworkBundle\\Test\\TestBrowserToken` object and
stores in the session of the test client.

AJAX Requests
.............

The Client provides a :method:`Symfony\\Component\\BrowserKit\\AbstractBrowser::xmlHttpRequest`
method, which has the same arguments as the ``request()`` method, and it's a
shortcut to make AJAX requests::

    // the required HTTP_X_REQUESTED_WITH header is added automatically
    $client->xmlHttpRequest('POST', '/submit', ['name' => 'Fabien']);

Browsing
........

The Client supports many operations that can be done in a real browser::

    $client->back();
    $client->forward();
    $client->reload();

    // clears all cookies and the history
    $client->restart();

.. note::

    The ``back()`` and ``forward()`` methods skip the redirects that may have
    occurred when requesting a URL, as normal browsers do.

Accessing Internal Objects
..........................

If you use the client to test your application, you might want to access the
client's internal objects::

    $history = $client->getHistory();
    $cookieJar = $client->getCookieJar();

You can also get the objects related to the latest request::

    // the HttpKernel request instance
    $request = $client->getRequest();

    // the BrowserKit request instance
    $request = $client->getInternalRequest();

    // the HttpKernel response instance
    $response = $client->getResponse();

    // the BrowserKit response instance
    $response = $client->getInternalResponse();

    // the Crawler instance
    $crawler = $client->getCrawler();

Accessing the Profiler Data
...........................

On each request, you can enable the Symfony profiler to collect data about the
internal handling of that request. For example, the profiler could be used to
verify that a given page runs less than a certain number of database
queries when loading.

To get the Profiler for the last request, do the following::

    // enables the profiler for the very next request
    $client->enableProfiler();

    $crawler = $client->request('GET', '/profiler');

    // gets the profile
    $profile = $client->getProfile();

For specific details on using the profiler inside a test, see the
:doc:`/testing/profiling` article.

Redirecting
...........

When a request returns a redirect response, the client does not follow
it automatically. You can examine the response and force a redirection
afterwards with the ``followRedirect()`` method::

    $crawler = $client->followRedirect();

If you want the client to automatically follow all redirects, you can
force them by calling the ``followRedirects()`` method before performing the request::

    $client->followRedirects();

If you pass ``false`` to the ``followRedirects()`` method, the redirects
will no longer be followed::

    $client->followRedirects(false);

Sending Custom Headers
......................

If your application behaves according to some HTTP headers, pass them as the
second argument of ``createClient()``::

    $client = static::createClient([], [
        'HTTP_HOST'       => 'en.example.com',
        'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
    ]);

You can also override HTTP headers on a per request basis::

    $client->request('GET', '/', [], [], [
        'HTTP_HOST'       => 'en.example.com',
        'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
    ]);

.. caution::

    The name of your custom headers must follow the syntax defined in the
    `section 4.1.18 of RFC 3875`_: replace ``-`` by ``_``, transform it into
    uppercase and prefix the result with ``HTTP_``. For example, if your
    header name is ``X-Session-Token``, pass ``HTTP_X_SESSION_TOKEN``.

.. tip::

    The test client is available as a service in the container in the ``test``
    environment (or wherever the :ref:`framework.test <reference-framework-test>`
    option is enabled). This means you can override the service entirely
    if you need to.

Reporting Exceptions
....................

Debugging exceptions in application tests may be difficult because by default
they are caught and you need to look at the logs to see which exception was
thrown. Disabling catching of exceptions in the test client allows the exception
to be reported by PHPUnit::

    $client->catchExceptions(false);

Useful Assertions
~~~~~~~~~~~~~~~~~

To get you started faster, here is a list of the most common and
useful test assertions::

    use Symfony\Component\HttpFoundation\Response;

    // ...

    // asserts that there is at least one h2 tag with the class "subtitle"
    // the third argument is an optional message shown on failed tests
    $this->assertGreaterThan(0, $crawler->filter('h2.subtitle')->count(),
        'There is at least one subtitle'
    );

    // asserts that there are exactly 4 h2 tags on the page
    $this->assertCount(4, $crawler->filter('h2'));

    // asserts that the "Content-Type" header is "application/json"
    $this->assertResponseHeaderSame('Content-Type', 'application/json');
    // equivalent to:
    $this->assertTrue($client->getResponse()->headers->contains(
        'Content-Type', 'application/json'
    ));

    // asserts that the response content contains a string
    $this->assertStringContainsString('foo', $client->getResponse()->getContent());
    // ...or matches a regex
    $this->assertRegExp('/foo(bar)?/', $client->getResponse()->getContent());

    // asserts that the response status code is 2xx
    $this->assertResponseIsSuccessful();
    // equivalent to:
    $this->assertTrue($client->getResponse()->isSuccessful());

    // asserts that the response status code is 404 Not Found
    $this->assertTrue($client->getResponse()->isNotFound());

    // asserts a specific status code
    $this->assertResponseStatusCodeSame(201);
    // HTTP status numbers are available as constants too:
    // e.g. 201 === Symfony\Component\HttpFoundation\Response::HTTP_CREATED
    // equivalent to:
    $this->assertEquals(201, $client->getResponse()->getStatusCode());

    // asserts that the response is a redirect to /demo/contact
    $this->assertResponseRedirects('/demo/contact');
    // equivalent to:
    $this->assertTrue($client->getResponse()->isRedirect('/demo/contact'));
    // ...or check that the response is a redirect to any URL
    $this->assertResponseRedirects();

.. index::
   single: Tests; Crawler

.. _testing-crawler:

The Crawler
~~~~~~~~~~~

A Crawler instance is returned each time you make a request with the Client.
It allows you to traverse HTML documents, select nodes, find links and forms.

Traversing
..........

Like jQuery, the Crawler has methods to traverse the DOM of an HTML/XML
document. For example, the following finds all ``input[type=submit]`` elements,
selects the last one on the page, and then selects its immediate parent element::

    $newCrawler = $crawler->filter('input[type=submit]')
        ->last()
        ->ancestors()
        ->first()
    ;

Many other methods are also available:

``filter('h1.title')``
    Nodes that match the CSS selector.
``filterXpath('h1')``
    Nodes that match the XPath expression.
``eq(1)``
    Node for the specified index.
``first()``
    First node.
``last()``
    Last node.
``siblings()``
    Siblings.
``nextAll()``
    All following siblings.
``previousAll()``
    All preceding siblings.
``ancestors()``
    Returns the ancestor nodes.
``children()``
    Returns children nodes.
``reduce($lambda)``
    Nodes for which the callable does not return false.

Since each of these methods returns a new ``Crawler`` instance, you can
narrow down your node selection by chaining the method calls::

    $crawler
        ->filter('h1')
        ->reduce(function ($node, $i) {
            if (!$node->attr('class')) {
                return false;
            }
        })
        ->first()
    ;

.. tip::

    Use the ``count()`` function to get the number of nodes stored in a Crawler:
    ``count($crawler)``

Extracting Information
......................

The Crawler can extract information from the nodes::

    use Symfony\Component\DomCrawler\Crawler;

    // returns the attribute value for the first node
    $crawler->attr('class');

    // returns the node value for the first node
    $crawler->text();

    // returns the default text if the node does not exist
    $crawler->text('Default text content');

    // pass TRUE as the second argument of text() to remove all extra white spaces, including
    // the internal ones (e.g. "  foo\n  bar    baz \n " is returned as "foo bar baz")
    $crawler->text(null, true);

    // extracts an array of attributes for all nodes
    // (_text returns the node value)
    // returns an array for each element in crawler,
    // each with the value and href
    $info = $crawler->extract(['_text', 'href']);

    // executes a lambda for each node and return an array of results
    $data = $crawler->each(function (Crawler $node, $i) {
        return $node->attr('href');
    });

Links
.....

Use the ``clickLink()`` method to click on the first link that contains the
given text (or the first clickable image with that ``alt`` attribute)::

    $client = static::createClient();
    $client->request('GET', '/post/hello-world');

    $client->clickLink('Click here');

If you need access to the :class:`Symfony\\Component\\DomCrawler\\Link` object
that provides helpful methods specific to links (such as ``getMethod()`` and
``getUri()``), use the ``selectLink()`` method instead::

    $client = static::createClient();
    $crawler = $client->request('GET', '/post/hello-world');

    $link = $crawler->selectLink('Click here')->link();
    $client->click($link);

Forms
.....

Use the ``submitForm()`` method to submit the form that contains the given button::

    $client = static::createClient();
    $client->request('GET', '/post/hello-world');

    $crawler = $client->submitForm('Add comment', [
        'comment_form[content]' => '...',
    ]);

The first argument of ``submitForm()`` is the text content, ``id``, ``value`` or
``name`` of any ``<button>`` or ``<input type="submit">`` included in the form.
The second optional argument is used to override the default form field values.

.. note::

    Notice that you select form buttons and not forms as a form can have several
    buttons; if you use the traversing API, keep in mind that you must look for a
    button.

If you need access to the :class:`Symfony\\Component\\DomCrawler\\Form` object
that provides helpful methods specific to forms (such as ``getUri()``,
``getValues()`` and ``getFields()``) use the ``selectButton()`` method instead::

    $client = static::createClient();
    $crawler = $client->request('GET', '/post/hello-world');

    $buttonCrawlerNode = $crawler->selectButton('submit');

    // select the form that contains this button
    $form = $buttonCrawlerNode->form();

    // you can also pass an array of field values that overrides the default ones
    $form = $buttonCrawlerNode->form([
        'my_form[name]'    => 'Fabien',
        'my_form[subject]' => 'Symfony rocks!',
    ]);

    // you can pass a second argument to override the form HTTP method
    $form = $buttonCrawlerNode->form([], 'DELETE');

    // submit the Form object
    $client->submit($form);

The field values can also be passed as a second argument of the ``submit()``
method::

    $client->submit($form, [
        'my_form[name]'    => 'Fabien',
        'my_form[subject]' => 'Symfony rocks!',
    ]);

For more complex situations, use the ``Form`` instance as an array to set the
value of each field individually::

    // changes the value of a field
    $form['my_form[name]'] = 'Fabien';
    $form['my_form[subject]'] = 'Symfony rocks!';

There is also a nice API to manipulate the values of the fields according to
their type::

    // selects an option or a radio
    $form['country']->select('France');

    // ticks a checkbox
    $form['like_symfony']->tick();

    // uploads a file
    $form['photo']->upload('/path/to/lucas.jpg');

    // In the case of a multiple file upload
    $form['my_form[field][0]']->upload('/path/to/lucas.jpg');
    $form['my_form[field][1]']->upload('/path/to/lisa.jpg');

.. tip::

    Instead of hardcoding the form name as part of the field names (e.g.
    ``my_form[...]`` in previous examples), you can use the
    :method:`Symfony\\Component\\DomCrawler\\Form::getName` method to get the
    form name.

.. tip::

    If you purposefully want to select "invalid" select/radio values, see
    :ref:`components-dom-crawler-invalid`.

.. tip::

    You can get the values that will be submitted by calling the ``getValues()``
    method on the ``Form`` object. The uploaded files are available in a
    separate array returned by ``getFiles()``. The ``getPhpValues()`` and
    ``getPhpFiles()`` methods also return the submitted values, but in the
    PHP format (it converts the keys with square brackets notation - e.g.
    ``my_form[subject]`` - to PHP arrays).

.. tip::

    The ``submit()`` and ``submitForm()`` methods define optional arguments to
    add custom server parameters and HTTP headers when submitting the form::

        $client->submit($form, [], ['HTTP_ACCEPT_LANGUAGE' => 'es']);
        $client->submitForm($button, [], 'POST', ['HTTP_ACCEPT_LANGUAGE' => 'es']);

.. TODO
    End to End Tests (E2E)
    ----------------------

    * panther
    * testing javascript
    * UX or form collections as example?

PHPUnit Configuration
---------------------

Each application has its own PHPUnit configuration, stored in the
``phpunit.xml.dist`` file. You can edit this file to change the defaults or
create a ``phpunit.xml`` file to set up a configuration for your local machine
only.

.. tip::

    Store the ``phpunit.xml.dist`` file in your code repository and ignore
    the ``phpunit.xml`` file.

By default, only the tests stored in ``tests/`` are run via the ``phpunit`` command,
as configured in the ``phpunit.xml.dist`` file:

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <phpunit>
        <!-- ... -->
        <testsuites>
            <testsuite name="Project Test Suite">
                <directory>tests</directory>
            </testsuite>
        </testsuites>
        <!-- ... -->
    </phpunit>

But you can add more directories. For instance, the following
configuration adds tests from a custom ``lib/tests`` directory:

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <phpunit>
        <!-- ... -->
        <testsuites>
            <testsuite name="Project Test Suite">
                <!-- ... -->
                <directory>lib/tests</directory>
            </testsuite>
        </testsuites>
        <!-- ... -->
    </phpunit>

To include other directories in the `code coverage analysis`_, also edit the
``<filter>`` section:

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <phpunit>
        <!-- ... -->
        <filter>
            <whitelist>
                <!-- ... -->
                <directory>lib</directory>
                <exclude>
                    <!-- ... -->
                    <directory>lib/tests</directory>
                </exclude>
            </whitelist>
        </filter>
        <!-- ... -->
    </phpunit>

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    testing/*
    /components/dom_crawler
    /components/css_selector

.. _`PHPUnit`: https://phpunit.de/
.. _`documentation`: https://phpunit.readthedocs.io/
.. _`Writing Tests for PHPUnit`: https://phpunit.readthedocs.io/en/stable/writing-tests-for-phpunit.html
.. _`unit test`: https://en.wikipedia.org/wiki/Unit_testing
.. _`DAMADoctrineTestBundle`: https://github.com/dmaicher/doctrine-test-bundle
.. _`DoctrineFixturesBundle documentation`: https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
.. _`$_SERVER`: https://www.php.net/manual/en/reserved.variables.server.php
.. _`SymfonyMakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
.. _`code coverage analysis`: https://phpunit.readthedocs.io/en/9.1/code-coverage-analysis.html
.. _`section 4.1.18 of RFC 3875`: https://tools.ietf.org/html/rfc3875#section-4.1.18
