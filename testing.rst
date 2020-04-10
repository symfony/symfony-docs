.. index::
   single: Tests

Testing
=======

Whenever you write a new line of code, you also potentially add new bugs.
To build better and more reliable applications, you should test your code
using both functional and unit tests.

The PHPUnit Testing Framework
-----------------------------

Symfony integrates with an independent library called `PHPUnit`_ to give you a
rich testing framework. This article won't cover PHPUnit itself, which has its
own excellent `documentation`_.

Before creating your first test, install the `PHPUnit Bridge component`_, which
wraps the original PHPUnit binary to provide additional features:

.. code-block:: terminal

    $ composer require --dev symfony/phpunit-bridge

After the library downloads, try executing PHPUnit by running (the first time
you run this, it will download PHPUnit itself and make its classes available in
your app):

.. code-block:: terminal

    $ ./bin/phpunit

.. note::

    The ``./bin/phpunit`` command is created by :ref:`Symfony Flex <symfony-flex>`
    when installing the ``phpunit-bridge`` package. If the command is missing, you
    can remove the package (``composer remove symfony/phpunit-bridge``) and install
    it again. Another solution is to remove the project's ``symfony.lock`` file and
    run ``composer install`` to force the execution of all Symfony Flex recipes.

Each test - whether it's a unit test or a functional test - is a PHP class
that should live in the ``tests/`` directory of your application. If you follow
this rule, then you can run all of your application's tests with the same
command as before.

PHPUnit is configured by the ``phpunit.xml.dist`` file in the root of your
Symfony application.

.. tip::

    Code coverage can be generated with the ``--coverage-*`` options, see the
    help information that is shown when using ``--help`` for more information.

.. index::
   single: Tests; Unit tests

Unit Tests
----------

A unit test is a test against a single PHP class, also called a *unit*. If you
want to test the overall behavior of your application, see the section about
:ref:`Functional Tests <functional-tests>`.

Writing Symfony unit tests is no different from writing standard PHPUnit
unit tests. Suppose, for example, that you have an *incredibly* simple class
called ``Calculator`` in the ``src/Util/`` directory of the app::

    // src/Util/Calculator.php
    namespace App\Util;

    class Calculator
    {
        public function add($a, $b)
        {
            return $a + $b;
        }
    }

To test this, create a ``CalculatorTest`` file in the ``tests/Util`` directory
of your application::

    // tests/Util/CalculatorTest.php
    namespace App\Tests\Util;

    use App\Util\Calculator;
    use PHPUnit\Framework\TestCase;

    class CalculatorTest extends TestCase
    {
        public function testAdd()
        {
            $calculator = new Calculator();
            $result = $calculator->add(30, 12);

            // assert that your calculator added the numbers correctly!
            $this->assertEquals(42, $result);
        }
    }

.. note::

    By convention, the ``tests/`` directory should replicate the directory
    of your bundle for unit tests. So, if you're testing a class in the
    ``src/Util/`` directory, put the test in the ``tests/Util/``
    directory.

Just like in your real application - autoloading is automatically enabled
via the ``vendor/autoload.php`` file (as configured by default in the
``phpunit.xml.dist`` file).

You can also limit a test run to a directory or a specific test file:

.. code-block:: terminal

    # run all tests of the application
    $ php bin/phpunit

    # run all tests in the Util/ directory
    $ php bin/phpunit tests/Util

    # run tests for the Calculator class
    $ php bin/phpunit tests/Util/CalculatorTest.php

.. index::
   single: Tests; Functional tests

.. _functional-tests:

Functional Tests
----------------

Functional tests check the integration of the different layers of an
application (from the routing to the views). They are no different from unit
tests as far as PHPUnit is concerned, but they have a very specific workflow:

* Make a request;
* Click on a link or submit a form;
* Test the response;
* Rinse and repeat.

Before creating your first test, install these packages that provide some of the
utilities used in the functional tests:

.. code-block:: terminal

    $ composer require --dev symfony/browser-kit symfony/css-selector

Your First Functional Test
~~~~~~~~~~~~~~~~~~~~~~~~~~
Functional tests are PHP files that typically live in the ``tests/Controller``
directory for your bundle. If you want to test the pages handled by your
``PostController`` class, start by creating a new ``PostControllerTest.php``
file that extends a special ``WebTestCase`` class.

As an example, a test could look like this::

    // tests/Controller/PostControllerTest.php
    namespace App\Tests\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class PostControllerTest extends WebTestCase
    {
        public function testShowPost()
        {
            $client = static::createClient();

            $client->request('GET', '/post/hello-world');

            $this->assertEquals(200, $client->getResponse()->getStatusCode());
        }
    }

.. tip::

    To run your functional tests, the ``WebTestCase`` class needs to know which
    is the application kernel to bootstrap it. The kernel class is usually
    defined in the ``KERNEL_CLASS`` environment variable (included in the
    default ``.env.test`` file provided by Symfony):

    If your use case is more complex, you can also override the
    ``createKernel()`` or ``getKernelClass()`` methods of your functional test,
    which take precedence over the ``KERNEL_CLASS`` env var.

In the above example, you validated that the HTTP response was successful. The
next step is to validate that the page actually contains the expected content.
The ``createClient()`` method returns a client, which is like a browser that
you'll use to crawl your site::

    $crawler = $client->request('GET', '/post/hello-world');

The ``request()`` method (read
:ref:`more about the request method <testing-request-method-sidebar>`)
returns a :class:`Symfony\\Component\\DomCrawler\\Crawler` object which can
be used to select elements in the response, click on links and submit forms.

.. tip::

    The ``Crawler`` only works when the response is an XML or an HTML document.
    To get the raw content response, call ``$client->getResponse()->getContent()``.

The crawler integrates with the ``symfony/css-selector`` component to give you the
power of CSS selectors to find content in a page. To install the CSS selector
component, run:

.. code-block:: terminal

    $ composer require --dev symfony/css-selector

Now you can use CSS selectors with the crawler. To assert that the phrase
"Hello World" is present in the page's main title, you can use this assertion::

    $this->assertSelectorTextContains('html h1.title', 'Hello World');

This assertion checks if the first element matching the CSS selector contains
the given text. This asserts calls ``$crawler->filter('html h1.title')``
internally, which allows you to use CSS selectors to filter any HTML element in
the page and check for its existence, attributes, text, etc.

The ``assertSelectorTextContains`` method is not a native PHPUnit assertion and is
available thanks to the ``WebTestCase`` class.

The crawler can also be used to interact with the page. Click on a link by first
selecting it with the crawler using either an XPath expression or a CSS selector,
then use the client to click on it::

    $link = $crawler
        ->filter('a:contains("Greet")') // find all links with the text "Greet"
        ->eq(1) // select the second link in the list
        ->link()
    ;

    // and click it
    $crawler = $client->click($link);

Submitting a form is very similar: select a form button, optionally override
some form values and submit the corresponding form::

    $form = $crawler->selectButton('submit')->form();

    // set some values
    $form['name'] = 'Lucas';
    $form['form_name[subject]'] = 'Hey there!';

    // submit the form
    $crawler = $client->submit($form);

.. tip::

    The form can also handle uploads and contains methods to fill in different types
    of form fields (e.g. ``select()`` and ``tick()``). For details, see the
    `Forms`_ section below.

Now that you can navigate through an application, use assertions to test
that it actually does what you expect it to. Use the Crawler to make assertions
on the DOM::

    // asserts that the response matches a given CSS selector.
    $this->assertGreaterThan(0, $crawler->filter('h1')->count());

Or test against the response content directly if you just want to assert that
the content contains some text or in case that the response is not an XML/HTML
document::

    $this->assertStringContainsString(
        'Hello World',
        $client->getResponse()->getContent()
    );

.. tip::

    Instead of installing each testing dependency individually, you can use the
    ``test`` :ref:`Symfony pack <symfony-packs>` to install all those dependencies at once:

    .. code-block:: terminal

        $ composer require --dev symfony/test-pack

.. index::
   single: Tests; Assertions

.. sidebar:: Useful Assertions

    To get you started faster, here is a list of the most common and
    useful test assertions::

        use Symfony\Component\HttpFoundation\Response;

        // ...

        // asserts that there is at least one h2 tag
        // with the class "subtitle"
        $this->assertGreaterThan(
            0,
            $crawler->filter('h2.subtitle')->count()
        );

        // asserts that there are exactly 4 h2 tags on the page
        $this->assertCount(4, $crawler->filter('h2'));

        // asserts that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            ),
            'the "Content-Type" header is "application/json"' // optional message shown on failure
        );

        // asserts that the response content contains a string
        $this->assertStringContainsString('foo', $client->getResponse()->getContent());
        // ...or matches a regex
        $this->assertRegExp('/foo(bar)?/', $client->getResponse()->getContent());

        // asserts that the response status code is 2xx
        $this->assertTrue($client->getResponse()->isSuccessful(), 'response status is 2xx');
        // asserts that the response status code is 404
        $this->assertTrue($client->getResponse()->isNotFound());
        // asserts a specific 200 status code
        $this->assertEquals(
            200, // or Symfony\Component\HttpFoundation\Response::HTTP_OK
            $client->getResponse()->getStatusCode()
        );

        // asserts that the response is a redirect to /demo/contact
        $this->assertTrue(
            $client->getResponse()->isRedirect('/demo/contact')
            // if the redirection URL was generated as an absolute URL
            // $client->getResponse()->isRedirect('http://localhost/demo/contact')
        );
        // ...or simply check that the response is a redirect to any URL
        $this->assertTrue($client->getResponse()->isRedirect());

.. _testing-data-providers:

Testing against Different Sets of Data
--------------------------------------

It's common to have to execute the same test against different sets of data to
check the multiple conditions code must handle. This is solved with PHPUnit's
`data providers`_, which work both for unit and functional tests.

First, add one or more arguments to your test method and use them inside the
test code. Then, define another method which returns a nested array with the
arguments to use on each test run. Lastly, add the ``@dataProvider`` annotation
to associate both methods::

    /**
     * @dataProvider provideUrls
     */
    public function testPageIsSuccessful($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function provideUrls()
    {
        return [
            ['/'],
            ['/blog'],
            ['/contact'],
            // ...
        ];
    }

.. index::
   single: Tests; Client

Working with the Test Client
----------------------------

The test client simulates an HTTP client like a browser and makes requests
into your Symfony application::

    $crawler = $client->request('GET', '/post/hello-world');

The ``request()`` method takes the HTTP method and a URL as arguments and
returns a ``Crawler`` instance.

.. tip::

    Hardcoding the request URLs is a best practice for functional tests. If the
    test generates URLs using the Symfony router, it won't detect any change
    made to the application URLs which may impact the end users.

.. _testing-request-method-sidebar:

.. sidebar:: More about the ``request()`` Method:

    The full signature of the ``request()`` method is::

        request(
            $method,
            $uri,
            array $parameters = [],
            array $files = [],
            array $server = [],
            $content = null,
            $changeHistory = true
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

AJAX Requests
~~~~~~~~~~~~~

The Client provides a :method:`Symfony\\Component\\BrowserKit\\AbstractBrowser::xmlHttpRequest`
method, which has the same arguments as the ``request()`` method, and it's a
shortcut to make AJAX requests::

    // the required HTTP_X_REQUESTED_WITH header is added automatically
    $client->xmlHttpRequest('POST', '/submit', ['name' => 'Fabien']);

Browsing
~~~~~~~~

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
~~~~~~~~~~~~~~~~~~~~~~~~~~

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

Accessing the Container
~~~~~~~~~~~~~~~~~~~~~~~

It's highly recommended that a functional test only tests the response. But
under certain very rare circumstances, you might want to access some services
to write assertions. Given that services are private by default, test classes
define a property that stores a special container created by Symfony which
allows fetching both public and all non-removed private services::

    // gives access to the same services used in your test, unless you're using
    // $client->insulate() or using real HTTP requests to test your application
    // don't forget to call self::bootKernel() before, otherwise, the container
    // will be empty
    $container = self::$container;

For a list of services available in your application, use the ``debug:container``
command.

If a private service is *never* used in your application (outside of your test),
it is *removed* from the container and cannot be accessed as described above. In
that case, you can create a public alias in the ``test`` environment and access
it via that alias:

.. configuration-block::

    .. code-block:: yaml

        # config/services_test.yaml
        services:
            # access the service in your test via
            # self::$container->get('test.App\Test\SomeTestHelper')
            test.App\Test\SomeTestHelper:
                # the id of the private service
                alias: 'App\Test\SomeTestHelper'
                public: true

    .. code-block:: xml

        <!-- config/services_test.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="test.App\Test\SomeTestHelper" alias="App\Test\SomeTestHelper" public="true"/>
            </services>
        </container>

    .. code-block:: php

        // config/services_test.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MessageGenerator;
        use App\Updates\SiteUpdateManager;

        return function(ContainerConfigurator $configurator) {
            // ...

            $services->alias('test.App\Test\SomeTestHelper', 'App\Test\SomeTestHelper')->public();
        };

.. tip::

    The special container that gives access to private services exists only in
    the ``test`` environment and is itself a service that you can get from the
    real container using the ``test.service_container`` id.

.. tip::

    If the information you need to check is available from the profiler, use
    it instead.

.. _testing_logging_in_users:

Logging in Users (Authentication)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 5.1

    The ``loginUser()`` method was introduced in Symfony 5.1.

When you want to add functional tests for protected pages, you have to
first "login" as a user. Reproducing the actual steps - such as
submitting a login form - make a test very slow. For this reason, Symfony
provides a ``loginUser()`` method to simulate logging in in your functional
tests.

Instead of login in with real users, it's recommended to create a user only for
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

Accessing the Profiler Data
~~~~~~~~~~~~~~~~~~~~~~~~~~~

On each request, you can enable the Symfony profiler to collect data about the
internal handling of that request. For example, the profiler could be used to
verify that a given page executes less than a certain number of database
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
~~~~~~~~~~~

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

Reporting Exceptions
~~~~~~~~~~~~~~~~~~~~

Debugging exceptions in functional tests may be difficult because by default
they are caught and you need to look at the logs to see which exception was
thrown. Disabling catching of exceptions in the test client allows the exception
to be reported by PHPUnit::

    $client->catchExceptions(false);

.. index::
   single: Tests; Crawler

.. _testing-crawler:

The Crawler
-----------

A Crawler instance is returned each time you make a request with the Client.
It allows you to traverse HTML documents, select nodes, find links and forms.

Traversing
~~~~~~~~~~

Like jQuery, the Crawler has methods to traverse the DOM of an HTML/XML
document. For example, the following finds all ``input[type=submit]`` elements,
selects the last one on the page, and then selects its immediate parent element::

    $newCrawler = $crawler->filter('input[type=submit]')
        ->last()
        ->parents()
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
``parents()``
    Returns the parent nodes.
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
~~~~~~~~~~~~~~~~~~~~~~

The Crawler can extract information from the nodes::

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
    $data = $crawler->each(function ($node, $i) {
        return $node->attr('href');
    });

Links
~~~~~

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
~~~~~

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
    $form['my_form[field][O]']->upload('/path/to/lucas.jpg');
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

Adding and Removing Forms to a Collection
.........................................

If you use a :doc:`Collection of Forms </form/form_collections>`,
you can't add fields to an existing form with
``$form['task[tags][0][name]'] = 'foo';``. This results in an error
``Unreachable field "…"`` because ``$form`` can only be used in order to
set values of existing fields. In order to add new fields, you have to
add the values to the raw data array::

    // gets the form
    $form = $crawler->filter('button')->form();

    // gets the raw values
    $values = $form->getPhpValues();

    // adds fields to the raw values
    $values['task']['tags'][0]['name'] = 'foo';
    $values['task']['tags'][1]['name'] = 'bar';

    // submits the form with the existing and new values
    $crawler = $client->request($form->getMethod(), $form->getUri(), $values,
        $form->getPhpFiles());

    // the 2 tags have been added to the collection
    $this->assertEquals(2, $crawler->filter('ul.tags > li')->count());

Where ``task[tags][0][name]`` is the name of a field created
with JavaScript.

You can remove an existing field, e.g. a tag::

    // gets the values of the form
    $values = $form->getPhpValues();

    // removes the first tag
    unset($values['task']['tags'][0]);

    // submits the data
    $crawler = $client->request($form->getMethod(), $form->getUri(),
        $values, $form->getPhpFiles());

    // the tag has been removed
    $this->assertEquals(0, $crawler->filter('ul.tags > li')->count());

.. index::
   pair: Tests; Configuration

Testing Configuration
---------------------

The Client used by functional tests creates a Kernel that runs in a special
``test`` environment. Since Symfony loads the ``config/packages/test/*.yaml``
in the ``test`` environment, you can tweak any of your application's settings
specifically for testing.

For example, by default, the Swift Mailer is configured to *not* actually
deliver emails in the ``test`` environment. You can see this under the ``swiftmailer``
configuration option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/test/swiftmailer.yaml

        # ...
        swiftmailer:
            disable_delivery: true

    .. code-block:: xml

        <!-- config/packages/test/swiftmailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer
                https://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd">

            <!-- ... -->
            <swiftmailer:config disable-delivery="true"/>
        </container>

    .. code-block:: php

        // config/packages/test/swiftmailer.php

        // ...
        $container->loadFromExtension('swiftmailer', [
            'disable_delivery' => true,
        ]);

You can also use a different environment entirely, or override the default
debug mode (``true``) by passing each as options to the ``createClient()``
method::

    $client = static::createClient([
        'environment' => 'my_test_env',
        'debug'       => false,
    ]);

Customizing Database URL / Environment Variables
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to customize some environment variables for your tests (e.g. the
``DATABASE_URL`` used by Doctrine), you can do that by overriding anything you
need in your ``.env.test`` file:

.. code-block:: text

    # .env.test
    DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name_test"

    # use SQLITE
    # DATABASE_URL="sqlite:///%kernel.project_dir%/var/app.db"

This file is automatically read in the ``test`` environment: any keys here override
the defaults in ``.env``.

.. caution::

    Applications created before November 2018 had a slightly different system,
    involving a ``.env.dist`` file. For information about upgrading, see:
    :doc:`configuration/dot-env-changes`.

Sending Custom Headers
~~~~~~~~~~~~~~~~~~~~~~

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

.. tip::

    The test client is available as a service in the container in the ``test``
    environment (or wherever the :ref:`framework.test <reference-framework-test>`
    option is enabled). This means you can override the service entirely
    if you need to.

.. index::
   pair: PHPUnit; Configuration

PHPUnit Configuration
~~~~~~~~~~~~~~~~~~~~~

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
                <!-- ... --->
                <directory>lib/tests</directory>
            </testsuite>
        </testsuites>
        <!-- ... -->
    </phpunit>

To include other directories in the code coverage, also edit the ``<filter>``
section:

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
.. _`PHPUnit Bridge component`: https://symfony.com/components/PHPUnit%20Bridge
.. _`$_SERVER`: https://www.php.net/manual/en/reserved.variables.server.php
.. _`data providers`: https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers
