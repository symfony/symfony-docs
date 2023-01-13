.. index::
   single: Tests

Testing
=======

Whenever you write a new line of code, you also potentially add new bugs.
To build better and more reliable applications, you should test your code
using both functional and unit tests.

.. _testing-installation:

The PHPUnit Testing Framework
-----------------------------

Symfony integrates with an independent library called `PHPUnit`_ to give
you a rich testing framework. This article won't cover PHPUnit itself,
which has its own excellent `documentation`_.

Before creating your first test, install ``symfony/test-pack``, which installs
some other packages needed for testing (such as ``phpunit/phpunit``):

.. code-block:: terminal

    $ composer require --dev symfony/test-pack

After the library is installed, try running PHPUnit:

.. code-block:: terminal

    $ php bin/phpunit

This command automatically runs your application tests. Each test is a
PHP class ending with "Test" (e.g. ``BlogControllerTest``) that lives in
the ``tests/`` directory of your application.

PHPUnit is configured by the ``phpunit.xml.dist`` file in the root of your
application. The default configuration provided by Symfony Flex will be
enough in most cases. Read the `PHPUnit documentation`_ to discover all
possible configuration options (e.g. to enable code coverage or to split
your test into multiple "test suites").

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

You can run tests using the ``bin/phpunit`` command:

.. code-block:: terminal

    # run all tests of the application
    $ php bin/phpunit

    # run all tests in the Form/ directory
    $ php bin/phpunit tests/Form

    # run tests for the UserType class
    $ php bin/phpunit tests/Form/UserTypeTest.php

.. tip::

    In large test suites, it can make sense to create subdirectories for
    each type of tests (e.g. ``tests/Unit/`` and ``tests/Functional/``).

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
test. This assures that each test is run independently from each other.

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
    test, which takes precedence over the ``KERNEL_CLASS`` env var.

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

.. code-block:: env

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
the container is returned by ``static::getContainer()``::

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

            // (2) use static::getContainer() to access the service container
            $container = static::getContainer();

            // (3) run some service & test the result
            $newsletterGenerator = $container->get(NewsletterGenerator::class);
            $newsletter = $newsletterGenerator->generateMonthlyNews(/* ... */);

            $this->assertEquals('...', $newsletter->getContent());
        }
    }

The container from ``static::getContainer()`` is actually a special test container.
It gives you access to both the public services and the non-removed
:ref:`private services <container-public>`.

.. note::

    If you need to test private services that have been removed (those who
    are not used by any other services), you need to declare those private
    services as public in the ``config/services_test.yaml`` file.

Mocking Dependencies
--------------------

Sometimes it can be useful to mock a dependency of a tested service.
From the example in the previous section, let's assume the
``NewsletterGenerator`` has a dependency to a private alias
``NewsRepositoryInterface`` pointing to a private ``NewsRepository`` service
and you'd like to use a mocked ``NewsRepositoryInterface`` instead of the
concrete one::

    // ...
    use App\Contracts\Repository\NewsRepositoryInterface;

    class NewsletterGeneratorTest extends KernelTestCase
    {
        public function testSomething()
        {
            // ... same bootstrap as the section above

            $newsRepository = $this->createMock(NewsRepositoryInterface::class);
            $newsRepository->expects(self::once())
                ->method('findNewsFromLastMonth')
                ->willReturn([
                    new News('some news'),
                    new News('some other news'),
                ])
            ;

            // the following line won't work unless the alias is made public
            $container->set(NewsRepositoryInterface::class, $newsRepository);

            // will be injected the mocked repository
            $newsletterGenerator = $container->get(NewsletterGenerator::class);

            // ...
        }
    }

In order to make the alias public, you will need to update configuration for
the ``test`` environment as follows:

.. configuration-block::

    .. code-block:: yaml

        # config/services_test.yaml
        services:
            # redefine the alias as it should be while making it public
            App\Contracts\Repository\NewsRepositoryInterface:
                alias: App\Repository\NewsRepository
                public: true

    .. code-block:: xml

        <!-- config/services_test.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
        ">
            <services>
                <!-- redefine the alias as it should be while making it public -->
                <service id="App\Contracts\Repository\NewsRepositoryInterface"
                    alias="App\Repository\NewsRepository"
                />
            </services>
        </container>

    .. code-block:: php

        // config/services_test.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Contracts\Repository\NewsRepositoryInterface;
        use App\Repository\NewsRepository;

        return static function (ContainerConfigurator $containerConfigurator) {
            $containerConfigurator->services()
                // redefine the alias as it should be while making it public
                ->alias(NewsRepositoryInterface::class, NewsRepository::class)
                    ->public()
            ;
        };

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

Then you modify and use this class to load new entities in the database. For
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

    $ php bin/console --env=test doctrine:fixtures:load

For more information, read the `DoctrineFixturesBundle documentation`_.

.. _functional-tests:

Application Tests
-----------------

Application tests check the integration of all the different layers of the
application (from the routing to the views). They are no different from
unit tests or integration tests as far as PHPUnit is concerned, but they
have a very specific workflow:

#. :ref:`Make a request <testing-applications-arrange>`;
#. :ref:`Interact with the page <testing-applications-act>` (e.g. click on a link or submit a form);
#. :ref:`Test the response <testing-application-assertions>`;
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
and the request body contains a ``<h1>`` tag with ``"Hello world"``.

The ``request()`` method also returns a crawler, which you can use to
create more complex assertions in your tests::

    $crawler = $client->request('GET', '/post/hello-world');

    // for instance, count the number of ``.comment`` elements on the page
    $this->assertCount(4, $crawler->filter('.comment'));

You can learn more about the crawler in :doc:`/testing/dom_crawler`.

.. _testing-applications-arrange:

Making Requests
~~~~~~~~~~~~~~~

The test client simulates an HTTP client like a browser and makes requests
into your Symfony application::

    $crawler = $client->request('GET', '/post/hello-world');

The :method:`request() <Symfony\\Component\\BrowserKit\\AbstractBrowser::request>` method takes the HTTP method and a URL as arguments and
returns a ``Crawler`` instance.

.. tip::

    Hardcoding the request URLs is a best practice for application tests.
    If the test generates URLs using the Symfony router, it won't detect
    any change made to the application URLs which may impact the end users.

The full signature of the ``request()`` method is::

    request(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        string $content = null,
        bool $changeHistory = true
    ): Crawler

This allows you to create all types of requests you can think of:

.. tip::

    The test client is available as the ``test.client`` service in the
    container in the ``test`` environment (or wherever the
    :ref:`framework.test <reference-framework-test>` option is enabled).
    This means you can override the service entirely if you need to.

.. caution::

    Before each request, the client reboots the kernel, recreating
    the container from scratch.
    This ensures that every requests are "isolated" using "new" service objects.
    Also, it means that entities loaded by Doctrine repositories will
    be "detached", so they will need to be refreshed by the manager or
    queried again from a repository.

Browsing the Site
.................

The Client supports many operations that can be done in a real browser::

    $client->back();
    $client->forward();
    $client->reload();

    // clears all cookies and the history
    $client->restart();

.. note::

    The ``back()`` and ``forward()`` methods skip the redirects that may have
    occurred when requesting a URL, as normal browsers do.

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

.. _testing_logging_in_users:

Logging in Users (Authentication)
.................................

When you want to add application tests for protected pages, you have to
first "login" as a user. Reproducing the actual steps - such as
submitting a login form - makes a test very slow. For this reason, Symfony
provides a ``loginUser()`` method to simulate logging in in your functional
tests.

Instead of logging in with real users, it's recommended to create a user
only for tests. You can do that with `Doctrine data fixtures`_ to load the
testing users only in the test database.

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
            $userRepository = static::getContainer()->get(UserRepository::class);

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

.. note::

    By design, the ``loginUser()`` method doesn't work when using stateless firewalls.
    Instead, add the appropriate token/header in each ``request()`` call.

Making AJAX Requests
....................

The client provides an
:method:`Symfony\\Component\\BrowserKit\\AbstractBrowser::xmlHttpRequest`
method, which has the same arguments as the ``request()`` method and is
a shortcut to make AJAX requests::

    // the required HTTP_X_REQUESTED_WITH header is added automatically
    $client->xmlHttpRequest('POST', '/submit', ['name' => 'Fabien']);

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

Reporting Exceptions
....................

Debugging exceptions in application tests may be difficult because by default
they are caught and you need to look at the logs to see which exception was
thrown. Disabling catching of exceptions in the test client allows the exception
to be reported by PHPUnit::

    $client->catchExceptions(false);

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

To get the profiler for the last request, do the following::

    // enables the profiler for the very next request
    $client->enableProfiler();

    $crawler = $client->request('GET', '/profiler');

    // gets the profile
    $profile = $client->getProfile();

For specific details on using the profiler inside a test, see the
:doc:`/testing/profiling` article.

.. _testing-applications-act:

Interacting with the Response
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Like a real browser, the Client and Crawler objects can be used to interact
with the page you're served:

.. _testing-links:

Clicking on Links
.................

Use the ``clickLink()`` method to click on the first link that contains the
given text (or the first clickable image with that ``alt`` attribute)::

    $client = static::createClient();
    $client->request('GET', '/post/hello-world');

    $client->clickLink('Click here');

If you need access to the :class:`Symfony\\Component\\DomCrawler\\Link` object
that provides helpful methods specific to links (such as ``getMethod()`` and
``getUri()``), use the ``Crawler::selectLink()`` method instead::

    $client = static::createClient();
    $crawler = $client->request('GET', '/post/hello-world');

    $link = $crawler->selectLink('Click here')->link();
    // ...

    // use click() if you want to click the selected link
    $client->click($link);

.. _testing-forms:

Submitting Forms
................

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

    Notice that you select form buttons and not forms, as a form can have several
    buttons. If you use the traversing API, keep in mind that you must look for a
    button.

If you need access to the :class:`Symfony\\Component\\DomCrawler\\Form` object
that provides helpful methods specific to forms (such as ``getUri()``,
``getValues()`` and ``getFields()``) use the ``Crawler::selectButton()`` method instead::

    $client = static::createClient();
    $crawler = $client->request('GET', '/post/hello-world');

    // select the button
    $buttonCrawlerNode = $crawler->selectButton('submit');

    // retrieve the Form object for the form belonging to this button
    $form = $buttonCrawlerNode->form();

    // set values on a form object
    $form['my_form[name]'] = 'Fabien';
    $form['my_form[subject]'] = 'Symfony rocks!';

    // submit the Form object
    $client->submit($form);

    // optionally, you can combine the last 2 steps by passing an array of
    // field values while submitting the form:
    $client->submit($form, [
        'my_form[name]'    => 'Fabien',
        'my_form[subject]' => 'Symfony rocks!',
    ]);

Based on the form type, you can use different methods to fill in the
input::

    // selects an option or a radio
    $form['my_form[country]']->select('France');

    // ticks a checkbox
    $form['my_form[like_symfony]']->tick();

    // uploads a file
    $form['my_form[photo]']->upload('/path/to/lucas.jpg');

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

.. _testing-application-assertions:

Testing the Response (Assertions)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that the tests have visited a page and interacted with it (e.g. filled
in a form), it is time to verify that the expected output is shown.

As all tests are based on PHPUnit, you can use any `PHPUnit Assertion`_ in
your tests. Combined with test Client and the Crawler, this allows you to
check anything you want.

However, Symfony provides useful shortcut methods for the most common cases:

Response Assertions
...................

``assertResponseIsSuccessful(string $message = '')``
    Asserts that the response was successful (HTTP status is 2xx).
``assertResponseStatusCodeSame(int $expectedCode, string $message = '')``
    Asserts a specific HTTP status code.
``assertResponseRedirects(string $expectedLocation = null, int $expectedCode = null, string $message = '')``
    Asserts the response is a redirect response (optionally, you can check
    the target location and status code).
``assertResponseHasHeader(string $headerName, string $message = '')``/``assertResponseNotHasHeader(string $headerName, string $message = '')``
    Asserts the given header is (not) available on the response.
``assertResponseHeaderSame(string $headerName, string $expectedValue, string $message = '')``/``assertResponseHeaderNotSame(string $headerName, string $expectedValue, string $message = '')``
    Asserts the given header does (not) contain the expected value on the
    response.
``assertResponseHasCookie(string $name, string $path = '/', string $domain = null, string $message = '')``/``assertResponseNotHasCookie(string $name, string $path = '/', string $domain = null, string $message = '')``
    Asserts the given cookie is present in the response (optionally
    checking for a specific cookie path or domain).
``assertResponseCookieValueSame(string $name, string $expectedValue, string $path = '/', string $domain = null, string $message = '')``
    Asserts the given cookie is present and set to the expected value.
``assertResponseFormatSame(?string $expectedFormat, string $message = '')``
    Asserts the response format returned by the
    :method:`Symfony\\Component\\HttpFoundation\\Response::getFormat` method
    is the same as the expected value.
``assertResponseIsUnprocessable(string $message = '')``
    Asserts the response is unprocessable (HTTP status is 422)

Request Assertions
..................

``assertRequestAttributeValueSame(string $name, string $expectedValue, string $message = '')``
    Asserts the given :ref:`request attribute <component-foundation-attributes>`
    is set to the expected value.
``assertRouteSame($expectedRoute, array $parameters = [], string $message = '')``
    Asserts the request matches the given route and optionally route parameters.

Browser Assertions
..................

``assertBrowserHasCookie(string $name, string $path = '/', string $domain = null, string $message = '')``/``assertBrowserNotHasCookie(string $name, string $path = '/', string $domain = null, string $message = '')``
    Asserts that the test Client does (not) have the given cookie set
    (meaning, the cookie was set by any response in the test).
``assertBrowserCookieValueSame(string $name, string $expectedValue, string $path = '/', string $domain = null, string $message = '')``
    Asserts the given cookie in the test Client is set to the expected
    value.
``assertThatForClient(Constraint $constraint, string $message = '')``
    Asserts the given Constraint in the Client. Useful for using your custom asserts
    in the same way as built-in asserts (i.e. without passing the Client as argument)::

        // add this method in some custom class imported in your tests
        protected static function assertMyOwnCustomAssert(): void
        {
            self::assertThatForClient(new SomeCustomConstraint());
        }

Crawler Assertions
..................

``assertSelectorExists(string $selector, string $message = '')``/``assertSelectorNotExists(string $selector, string $message = '')``
    Asserts that the given selector does (not) match at least one element
    in the response.
``assertSelectorCount(int $expectedCount, string $selector, string $message = '')``
    Asserts that the expected number of selector elements are in the response
``assertSelectorTextContains(string $selector, string $text, string $message = '')``/``assertSelectorTextNotContains(string $selector, string $text, string $message = '')``
    Asserts that the first element matching the given selector does (not)
    contain the expected text.
``assertSelectorTextSame(string $selector, string $text, string $message = '')``
    Asserts that the contents of the first element matching the given
    selector does (not) equal the expected text.
``assertPageTitleSame(string $expectedTitle, string $message = '')``
    Asserts that the ``<title>`` element is equal to the given title.
``assertPageTitleContains(string $expectedTitle, string $message = '')``
    Asserts that the ``<title>`` element contains the given title.
``assertInputValueSame(string $fieldName, string $expectedValue, string $message = '')``/``assertInputValueNotSame(string $fieldName, string $expectedValue, string $message = '')``
    Asserts that value of the form input with the given name does (not)
    equal the expected value.
``assertCheckboxChecked(string $fieldName, string $message = '')``/``assertCheckboxNotChecked(string $fieldName, string $message = '')``
    Asserts that the checkbox with the given name is (not) checked.
``assertFormValue(string $formSelector, string $fieldName, string $value, string $message = '')``/``assertNoFormValue(string $formSelector, string $fieldName, string $message = '')``
    Asserts that value of the field of the first form matching the given
    selector does (not) equal the expected value.

.. versionadded:: 6.3

    The ``assertSelectorCount()`` method was introduced in Symfony 6.3.

.. _mailer-assertions:

Mailer Assertions
.................

``assertEmailCount(int $count, string $transport = null, string $message = '')``
    Asserts that the expected number of emails was sent.
``assertQueuedEmailCount(int $count, string $transport = null, string $message = '')``
    Asserts that the expected number of emails was queued (e.g. using the
    Messenger component).
``assertEmailIsQueued(MessageEvent $event, string $message = '')``/``assertEmailIsNotQueued(MessageEvent $event, string $message = '')``
    Asserts that the given mailer event is (not) queued. Use
    ``getMailerEvent(int $index = 0, string $transport = null)`` to
    retrieve a mailer event by index.
``assertEmailAttachmentCount(RawMessage $email, int $count, string $message = '')``
    Asserts that the given email has the expected number of attachments. Use
    ``getMailerMessage(int $index = 0, string $transport = null)`` to
    retrieve a specific email by index.
``assertEmailTextBodyContains(RawMessage $email, string $text, string $message = '')``/``assertEmailTextBodyNotContains(RawMessage $email, string $text, string $message = '')``
    Asserts that the text body of the given email does (not) contain the
    expected text.
``assertEmailHtmlBodyContains(RawMessage $email, string $text, string $message = '')``/``assertEmailHtmlBodyNotContains(RawMessage $email, string $text, string $message = '')``
    Asserts that the HTML body of the given email does (not) contain the
    expected text.
``assertEmailHasHeader(RawMessage $email, string $headerName, string $message = '')``/``assertEmailNotHasHeader(RawMessage $email, string $headerName, string $message = '')``
    Asserts that the given email does (not) have the expected header set.
``assertEmailHeaderSame(RawMessage $email, string $headerName, string $expectedValue, string $message = '')``/``assertEmailHeaderNotSame(RawMessage $email, string $headerName, string $expectedValue, string $message = '')``
    Asserts that the given email does (not) have the expected header set to
    the expected value.
``assertEmailAddressContains(RawMessage $email, string $headerName, string $expectedValue, string $message = '')``
    Asserts that the given address header equals the expected e-mail
    address. This assertion normalizes addresses like ``Jane Smith
    <jane@example.com>`` into ``jane@example.com``.

Notifier Assertions
...................

``assertNotificationCount(int $count, string $transportName = null, string $message = '')``
    Asserts that the given number of notifications has been created
    (in total or for the given transport).
``assertQueuedNotificationCount(int $count, string $transportName = null, string $message = '')``
    Asserts that the given number of notifications are queued
    (in total or for the given transport).
``assertNotificationIsQueued(MessageEvent $event, string $message = '')``
    Asserts that the given notification is queued.
``assertNotificationIsNotQueued(MessageEvent $event, string $message = '')``
    Asserts that the given notification is not queued.
``assertNotificationSubjectContains(MessageInterface $notification, string $text, string $message = '')``
    Asserts that the given text is included in the subject of
    the given notification.
``assertNotificationSubjectNotContains(MessageInterface $notification, string $text, string $message = '')``
    Asserts that the given text is not included in the subject of
    the given notification.
``assertNotificationTransportIsEqual(MessageInterface $notification, string $transportName, string $message = '')``
    Asserts that the name of the transport for the given notification
    is the same as the given text.
``assertNotificationTransportIsNotEqual(MessageInterface $notification, string $transportName, string $message = '')``
    Asserts that the name of the transport for the given notification
    is not the same as the given text.

.. versionadded:: 6.2

    The Notifier assertions were introduced in Symfony 6.2.

.. TODO
..  End to End Tests (E2E)
..  ----------------------
..  * panther
..  * testing javascript
..  * UX or form collections as example?

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
.. _`Writing Tests for PHPUnit`: https://phpunit.readthedocs.io/en/9.5/writing-tests-for-phpunit.html
.. _`PHPUnit documentation`: https://phpunit.readthedocs.io/en/9.5/configuration.html
.. _`unit test`: https://en.wikipedia.org/wiki/Unit_testing
.. _`DAMADoctrineTestBundle`: https://github.com/dmaicher/doctrine-test-bundle
.. _`Doctrine data fixtures`: https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
.. _`DoctrineFixturesBundle documentation`: https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
.. _`SymfonyMakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
.. _`PHPUnit Assertion`: https://phpunit.readthedocs.io/en/9.5/assertions.html
.. _`section 4.1.18 of RFC 3875`: https://tools.ietf.org/html/rfc3875#section-4.1.18
