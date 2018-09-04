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

Each test - whether it's a unit test or a functional test - is a PHP class
that should live in the ``tests/`` directory of your application. If you follow
this rule, then you can run all of your application's tests with the following
command:

.. code-block:: terminal

    $ ./vendor/bin/simple-phpunit

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
`Functional Tests`_.

Writing Symfony unit tests is no different from writing standard PHPUnit
unit tests. Suppose, for example, that you have an *incredibly* simple class
called ``Calculator`` in the ``Util/`` directory of the app bundle::

    // src/AppBundle/Util/Calculator.php
    namespace AppBundle\Util;

    class Calculator
    {
        public function add($a, $b)
        {
            return $a + $b;
        }
    }

To test this, create a ``CalculatorTest`` file in the ``tests/AppBundle/Util`` directory
of your application::

    // tests/AppBundle/Util/CalculatorTest.php
    namespace Tests\AppBundle\Util;

    use AppBundle\Util\Calculator;
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

    By convention, the ``tests/AppBundle`` directory should replicate the directory
    of your bundle for unit tests. So, if you're testing a class in the
    ``src/AppBundle/Util/`` directory, put the test in the ``tests/AppBundle/Util/``
    directory.

Just like in your real application - autoloading is automatically enabled
via the ``vendor/autoload.php`` file (as configured by default in the
``phpunit.xml.dist`` file).

Running tests for a given file or directory is also very easy:

.. code-block:: terminal

    # run all tests of the application
    $ ./vendor/bin/simple-phpunit

    # run all tests in the Util directory
    $ ./vendor/bin/simple-phpunit tests/AppBundle/Util

    # run tests for the Calculator class
    $ ./vendor/bin/simple-phpunit tests/AppBundle/Util/CalculatorTest.php

    # run all tests for the entire Bundle
    $ ./vendor/bin/simple-phpunit tests/AppBundle/

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

Functional tests are simple PHP files that typically live in the ``tests/AppBundle/Controller``
directory for your bundle. If you want to test the pages handled by your
``PostController`` class, start by creating a new ``PostControllerTest.php``
file that extends a special ``WebTestCase`` class.

As an example, a test could look like this::

    // tests/AppBundle/Controller/PostControllerTest.php
    namespace Tests\AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class PostControllerTest extends WebTestCase
    {
        public function testShowPost()
        {
            $client = static::createClient();

            $crawler = $client->request('GET', '/post/hello-world');

            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Hello World")')->count()
            );
        }
    }

.. tip::

    To run your functional tests, the ``WebTestCase`` class bootstraps the
    kernel of your application. In most cases, this happens automatically.
    However, if your kernel is in a non-standard directory, you'll need
    to modify your ``phpunit.xml.dist`` file to set the ``KERNEL_DIR``
    environment variable to the directory of your kernel:

    .. code-block:: xml

        <?xml version="1.0" charset="utf-8" ?>
        <phpunit>
            <php>
                <server name="KERNEL_DIR" value="/path/to/your/app/" />
            </php>
            <!-- ... -->
        </phpunit>

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

Click on a link by first selecting it with the crawler using either an XPath
expression or a CSS selector, then use the client to click on it. For example::

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

Now that you can easily navigate through an application, use assertions to test
that it actually does what you expect it to. Use the Crawler to make assertions
on the DOM::

    // asserts that the response matches a given CSS selector.
    $this->assertGreaterThan(0, $crawler->filter('h1')->count());

Or test against the response content directly if you just want to assert that
the content contains some text or in case that the response is not an XML/HTML
document::

    $this->assertContains(
        'Hello World',
        $client->getResponse()->getContent()
    );

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
        $this->assertContains('foo', $client->getResponse()->getContent());
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
        return array(
            array('/'),
            array('/blog'),
            array('/contact'),
            // ...
        );
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
            array $parameters = array(),
            array $files = array(),
            array $server = array(),
            $content = null,
            $changeHistory = true
        )

    The ``server`` array is the raw values that you'd expect to normally
    find in the PHP `$_SERVER`_ superglobal. For example, to set the ``Content-Type``,
    ``Referer`` and ``X-Requested-With`` HTTP headers, you'd pass the following (mind
    the ``HTTP_`` prefix for non standard headers)::

        $client->request(
            'GET',
            '/post/hello-world',
            array(),
            array(),
            array(
                'CONTENT_TYPE'          => 'application/json',
                'HTTP_REFERER'          => '/foo/bar',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );

Use the crawler to find DOM elements in the response. These elements can then
be used to click on links and submit forms::

    $link = $crawler->selectLink('Go elsewhere...')->link();
    $crawler = $client->click($link);

    $form = $crawler->selectButton('validate')->form();
    $crawler = $client->submit($form, array('name' => 'Fabien'));

The ``click()`` and ``submit()`` methods both return a ``Crawler`` object.
These methods are the best way to browse your application as it takes care
of a lot of things for you, like detecting the HTTP method from a form and
giving you a nice API for uploading files.

.. tip::

    You will learn more about the ``Link`` and ``Form`` objects in the
    :ref:`Crawler <testing-crawler>` section below.

The ``request()`` method can also be used to simulate form submissions directly
or perform more complex requests. Some useful examples::

    // submits a form directly (but using the Crawler is easier!)
    $client->request('POST', '/submit', array('name' => 'Fabien'));

    // submits a raw JSON string in the request body
    $client->request(
        'POST',
        '/submit',
        array(),
        array(),
        array('CONTENT_TYPE' => 'application/json'),
        '{"name":"Fabien"}'
    );

    // Form submission with a file upload
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    $photo = new UploadedFile(
        '/path/to/photo.jpg',
        'photo.jpg',
        'image/jpeg',
        123
    );
    $client->request(
        'POST',
        '/submit',
        array('name' => 'Fabien'),
        array('photo' => $photo)
    );

    // Perform a DELETE request and pass HTTP headers
    $client->request(
        'DELETE',
        '/post/12',
        array(),
        array(),
        array('PHP_AUTH_USER' => 'username', 'PHP_AUTH_PW' => 'pa$$word')
    );

Last but not least, you can force each request to be executed in its own PHP
process to avoid any side-effects when working with several clients in the same
script::

    $client->insulate();

Browsing
~~~~~~~~

The Client supports many operations that can be done in a real browser::

    $client->back();
    $client->forward();
    $client->reload();

    // clears all cookies and the history
    $client->restart();

.. versionadded:: 3.4
    Starting from Symfony 3.4, the ``back()`` and ``forward()`` methods skip the
    redirects that may have occurred when requesting a URL, as normal browsers
    do. In previous Symfony versions they weren't skipped.

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

    $crawler = $client->getCrawler();

Accessing the Container
~~~~~~~~~~~~~~~~~~~~~~~

It's highly recommended that a functional test only tests the response. But
under certain very rare circumstances, you might want to access some internal
objects to write assertions. In such cases, you can access the Dependency
Injection Container::

    // will be the same container used in your test, unless you're using
    // $client->insulate() or using real HTTP requests to test your application
    $container = $client->getContainer();

For a list of services available in your application, use the ``debug:container``
command.

.. tip::

    If the information you need to check is available from the profiler, use
    it instead.

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

.. versionadded:: 3.4
    The ``catchExceptions()`` method was introduced in Symfony 3.4.

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
            if (!$node->getAttribute('class')) {
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

    // extracts an array of attributes for all nodes
    // (_text returns the node value)
    // returns an array for each element in crawler,
    // each with the value and href
    $info = $crawler->extract(array('_text', 'href'));

    // executes a lambda for each node and return an array of results
    $data = $crawler->each(function ($node, $i) {
        return $node->attr('href');
    });

Links
~~~~~

To select links, you can use the traversing methods above or the convenient
``selectLink()`` shortcut::

    $crawler->selectLink('Click here');

This selects all links that contain the given text, or clickable images for
which the ``alt`` attribute contains the given text. Like the other filtering
methods, this returns another ``Crawler`` object.

Once you've selected a link, you have access to a special ``Link`` object,
which has helpful methods specific to links (such as ``getMethod()`` and
``getUri()``). To click on the link, use the Client's ``click()`` method
and pass it a ``Link`` object::

    $link = $crawler->selectLink('Click here')->link();

    $client->click($link);

Forms
~~~~~

Forms can be selected using their buttons, which can be selected with the
``selectButton()`` method, just like links::

    $buttonCrawlerNode = $crawler->selectButton('submit');

.. note::

    Notice that you select form buttons and not forms as a form can have several
    buttons; if you use the traversing API, keep in mind that you must look for a
    button.

The ``selectButton()`` method can select ``button`` tags and submit ``input``
tags. It uses several parts of the buttons to find them:

* The ``value`` attribute value;
* The ``id`` or ``alt`` attribute value for images;
* The ``id`` or ``name`` attribute value for ``button`` tags.

Once you have a Crawler representing a button, call the ``form()`` method
to get a ``Form`` instance for the form wrapping the button node::

    $form = $buttonCrawlerNode->form();

When calling the ``form()`` method, you can also pass an array of field values
that overrides the default ones::

    $form = $buttonCrawlerNode->form(array(
        'my_form[name]'    => 'Fabien',
        'my_form[subject]' => 'Symfony rocks!',
    ));

And if you want to simulate a specific HTTP method for the form, pass it as a
second argument::

    $form = $buttonCrawlerNode->form(array(), 'DELETE');

The Client can submit ``Form`` instances::

    $client->submit($form);

The field values can also be passed as a second argument of the ``submit()``
method::

    $client->submit($form, array(
        'my_form[name]'    => 'Fabien',
        'my_form[subject]' => 'Symfony rocks!',
    ));

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
``test`` environment. Since Symfony loads the ``app/config/config_test.yml``
in the ``test`` environment, you can tweak any of your application's settings
specifically for testing.

For example, by default, the Swift Mailer is configured to *not* actually
deliver emails in the ``test`` environment. You can see this under the ``swiftmailer``
configuration option:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_test.yml

        # ...
        swiftmailer:
            disable_delivery: true

    .. code-block:: xml

        <!-- app/config/config_test.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer
                http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd">

            <!-- ... -->
            <swiftmailer:config disable-delivery="true" />
        </container>

    .. code-block:: php

        // app/config/config_test.php

        // ...
        $container->loadFromExtension('swiftmailer', array(
            'disable_delivery' => true,
        ));

You can also use a different environment entirely, or override the default
debug mode (``true``) by passing each as options to the ``createClient()``
method::

    $client = static::createClient(array(
        'environment' => 'my_test_env',
        'debug'       => false,
    ));

If your application behaves according to some HTTP headers, pass them as the
second argument of ``createClient()``::

    $client = static::createClient(array(), array(
        'HTTP_HOST'       => 'en.example.com',
        'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
    ));

You can also override HTTP headers on a per request basis::

    $client->request('GET', '/', array(), array(), array(
        'HTTP_HOST'       => 'en.example.com',
        'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
    ));

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

By default, only the tests stored in ``/tests`` are run via the ``phpunit`` command,
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

But you can easily add more directories. For instance, the following
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

* :ref:`Testing a console command <console-testing-commands>`
* :doc:`The chapter about tests in the Symfony Framework Best Practices </best_practices/tests>`
* :doc:`/components/dom_crawler`
* :doc:`/components/css_selector`

.. _`PHPUnit`: https://phpunit.de/
.. _`documentation`: https://phpunit.de/documentation.html
.. _`PHPUnit Bridge component`: https://symfony.com/components/PHPUnit%20Bridge
.. _`$_SERVER`: https://php.net/manual/en/reserved.variables.server.php
.. _`data providers`: https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers
