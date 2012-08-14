.. index::
   single: Tests

Testing
=======

Whenever you write a new line of code, you also potentially add new bugs.
To build better and more reliable applications, you should test your code
using both functional and unit tests.

The PHPUnit Testing Framework
-----------------------------

Symfony2 integrates with an independent library - called PHPUnit - to give
you a rich testing framework. This chapter won't cover PHPUnit itself, but
it has its own excellent `documentation`_.

.. note::

    Symfony2 works with PHPUnit 3.5.11 or later.

Each test - whether it's a unit test or a functional test - is a PHP class
that should live in the `Tests/` subdirectory of your bundles. If you follow
this rule, then you can run all of your application's tests with the following
command:

.. code-block:: bash

    # specify the configuration directory on the command line
    $ phpunit -c app/

The ``-c`` option tells PHPUnit to look in the ``app/`` directory for a configuration
file. If you're curious about the PHPUnit options, check out the ``app/phpunit.xml.dist``
file.

.. tip::

    Code coverage can be generated with the ``--coverage-html`` option.

.. index::
   single: Tests; Unit tests

Unit Tests
----------

A unit test is usually a test against a specific PHP class. If you want to
test the overall behavior of your application, see the section about `Functional Tests`_.

Writing Symfony2 unit tests is no different than writing standard PHPUnit
unit tests. Suppose, for example, that you have an *incredibly* simple class
called ``Calculator`` in the ``Utility/`` directory of your bundle::

    // src/Acme/DemoBundle/Utility/Calculator.php
    namespace Acme\DemoBundle\Utility;

    class Calculator
    {
        public function add($a, $b)
        {
            return $a + $b;
        }
    }

To test this, create a ``CalculatorTest`` file in the ``Tests/Utility`` directory
of your bundle::

    // src/Acme/DemoBundle/Tests/Utility/CalculatorTest.php
    namespace Acme\DemoBundle\Tests\Utility;

    use Acme\DemoBundle\Utility\Calculator;

    class CalculatorTest extends \PHPUnit_Framework_TestCase
    {
        public function testAdd()
        {
            $calc = new Calculator();
            $result = $calc->add(30, 12);

            // assert that our calculator added the numbers correctly!
            $this->assertEquals(42, $result);
        }
    }

.. note::

    By convention, the ``Tests/`` sub-directory should replicate the directory
    of your bundle. So, if you're testing a class in your bundle's ``Utility/``
    directory, put the test in the ``Tests/Utility/`` directory.

Just like in your real application - autoloading is automatically enabled
via the ``bootstrap.php.cache`` file (as configured by default in the ``phpunit.xml.dist``
file).

Running tests for a given file or directory is also very easy:

.. code-block:: bash

    # run all tests in the Utility directory
    $ phpunit -c app src/Acme/DemoBundle/Tests/Utility/

    # run tests for the Calculator class
    $ phpunit -c app src/Acme/DemoBundle/Tests/Utility/CalculatorTest.php

    # run all tests for the entire Bundle
    $ phpunit -c app src/Acme/DemoBundle/

.. index::
   single: Tests; Functional tests

Functional Tests
----------------

Functional tests check the integration of the different layers of an
application (from the routing to the views). They are no different from unit
tests as far as PHPUnit is concerned, but they have a very specific workflow:

* Make a request;
* Test the response;
* Click on a link or submit a form;
* Test the response;
* Rinse and repeat.

Your First Functional Test
~~~~~~~~~~~~~~~~~~~~~~~~~~

Functional tests are simple PHP files that typically live in the ``Tests/Controller``
directory of your bundle. If you want to test the pages handled by your
``DemoController`` class, start by creating a new ``DemoControllerTest.php``
file that extends a special ``WebTestCase`` class.

For example, the Symfony2 Standard Edition provides a simple functional test
for its ``DemoController`` (`DemoControllerTest`_) that reads as follows::

    // src/Acme/DemoBundle/Tests/Controller/DemoControllerTest.php
    namespace Acme\DemoBundle\Tests\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class DemoControllerTest extends WebTestCase
    {
        public function testIndex()
        {
            $client = static::createClient();

            $crawler = $client->request('GET', '/demo/hello/Fabien');

            $this->assertGreaterThan(0, $crawler->filter('html:contains("Hello Fabien")')->count());
        }
    }

.. tip::

    To run your functional tests, the ``WebTestCase`` class bootstraps the
    kernel of your application. In most cases, this happens automatically.
    However, if your kernel is in a non-standard directory, you'll need
    to modify your ``phpunit.xml.dist`` file to set the ``KERNEL_DIR`` environment
    variable to the directory of your kernel::

        <phpunit>
            <!-- ... -->
            <php>
                <server name="KERNEL_DIR" value="/path/to/your/app/" />
            </php>
            <!-- ... -->
        </phpunit>

The ``createClient()`` method returns a client, which is like a browser that
you'll use to crawl your site::

    $crawler = $client->request('GET', '/demo/hello/Fabien');

The ``request()`` method (see :ref:`more about the request method<book-testing-request-method-sidebar>`)
returns a :class:`Symfony\\Component\\DomCrawler\\Crawler` object which can
be used to select elements in the Response, click on links, and submit forms.

.. tip::

    The Crawler only works when the response is an XML or an HTML document.
    To get the raw content response, call ``$client->getResponse()->getContent()``.

Click on a link by first selecting it with the Crawler using either an XPath
expression or a CSS selector, then use the Client to click on it. For example,
the following code finds all links with the text ``Greet``, then selects
the second one, and ultimately clicks on it::

    $link = $crawler->filter('a:contains("Greet")')->eq(1)->link();

    $crawler = $client->click($link);

Submitting a form is very similar; select a form button, optionally override
some form values, and submit the corresponding form::

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

    // Assert that the response matches a given CSS selector.
    $this->assertGreaterThan(0, $crawler->filter('h1')->count());

Or, test against the Response content directly if you just want to assert that
the content contains some text, or if the Response is not an XML/HTML
document::

    $this->assertRegExp('/Hello Fabien/', $client->getResponse()->getContent());

.. _book-testing-request-method-sidebar:

.. sidebar:: More about the ``request()`` method:

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
    find in the PHP `$_SERVER`_ superglobal. For example, to set the `Content-Type`
    and `Referer` HTTP headers, you'd pass the following::

        $client->request(
            'GET',
            '/demo/hello/Fabien',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_REFERER' => '/foo/bar',
            )
        );

.. index::
   single: Tests; Assertions

.. sidebar:: Useful Assertions

    To get you started faster, here is a list of the most common and
    useful test assertions::

        // Assert that there is more than one h2 tag with the class "subtitle"
        $this->assertGreaterThan(0, $crawler->filter('h2.subtitle')->count());

        // Assert that there are exactly 4 h2 tags on the page
        $this->assertCount(4, $crawler->filter('h2'));

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));

        // Assert that the response content matches a regexp.
        $this->assertRegExp('/foo/', $client->getResponse()->getContent());

        // Assert that the response status code is 2xx
        $this->assertTrue($client->getResponse()->isSuccessful());
        // Assert that the response status code is 404
        $this->assertTrue($client->getResponse()->isNotFound());
        // Assert a specific 200 status code
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Assert that the response is a redirect to /demo/contact
        $this->assertTrue($client->getResponse()->isRedirect('/demo/contact'));
        // or simply check that the response is a redirect to any URL
        $this->assertTrue($client->getResponse()->isRedirect());

.. index::
   single: Tests; Client

Working with the Test Client
-----------------------------

The Test Client simulates an HTTP client like a browser and makes requests
into your Symfony2 application::

    $crawler = $client->request('GET', '/hello/Fabien');

The ``request()`` method takes the HTTP method and a URL as arguments and
returns a ``Crawler`` instance.

Use the Crawler to find DOM elements in the Response. These elements can then
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
    :ref:`Crawler<book-testing-crawler>` section below.

The ``request`` method can also be used to simulate form submissions directly
or perform more complex requests::

    // Directly submit a form (but using the Crawler is easier!)
    $client->request('POST', '/submit', array('name' => 'Fabien'));

    // Form submission with a file upload
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    $photo = new UploadedFile(
        '/path/to/photo.jpg',
        'photo.jpg',
        'image/jpeg',
        123
    );
    // or
    $photo = array(
        'tmp_name' => '/path/to/photo.jpg',
        'name' => 'photo.jpg',
        'type' => 'image/jpeg',
        'size' => 123,
        'error' => UPLOAD_ERR_OK
    );
    $client->request(
        'POST',
        '/submit',
        array('name' => 'Fabien'),
        array('photo' => $photo)
    );

    // Perform a DELETE requests, and pass HTTP headers
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

    // Clears all cookies and the history
    $client->restart();

Accessing Internal Objects
~~~~~~~~~~~~~~~~~~~~~~~~~~

If you use the client to test your application, you might want to access the
client's internal objects::

    $history   = $client->getHistory();
    $cookieJar = $client->getCookieJar();

You can also get the objects related to the latest request::

    $request  = $client->getRequest();
    $response = $client->getResponse();
    $crawler  = $client->getCrawler();

If your requests are not insulated, you can also access the ``Container`` and
the ``Kernel``::

    $container = $client->getContainer();
    $kernel    = $client->getKernel();

Accessing the Container
~~~~~~~~~~~~~~~~~~~~~~~

It's highly recommended that a functional test only tests the Response. But
under certain very rare circumstances, you might want to access some internal
objects to write assertions. In such cases, you can access the dependency
injection container::

    $container = $client->getContainer();

Be warned that this does not work if you insulate the client or if you use an
HTTP layer. For a list of services available in your application, use the
``container:debug`` console task.

.. tip::

    If the information you need to check is available from the profiler, use
    it instead.

Accessing the Profiler Data
~~~~~~~~~~~~~~~~~~~~~~~~~~~

On each request, the Symfony profiler collects and stores a lot of data about
the internal handling of that request. For example, the profiler could be
used to verify that a given page executes less than a certain number of database
queries when loading.

To get the Profiler for the last request, do the following::

    $profile = $client->getProfile();

For specific details on using the profiler inside a test, see the
:doc:`/cookbook/testing/profiling` cookbook entry.

Redirecting
~~~~~~~~~~~

When a request returns a redirect response, the client does not follow
it automatically. You can examine the response and force a redirection
afterwards  with the ``followRedirect()`` method::

    $crawler = $client->followRedirect();
    
If you want the client to automatically follow all redirects, you can 
force him with the ``followRedirects()`` method::

    $client->followRedirects();

.. index::
   single: Tests; Crawler

.. _book-testing-crawler:

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

+------------------------+----------------------------------------------------+
| Method                 | Description                                        |
+========================+====================================================+
| ``filter('h1.title')`` | Nodes that match the CSS selector                  |
+------------------------+----------------------------------------------------+
| ``filterXpath('h1')``  | Nodes that match the XPath expression              |
+------------------------+----------------------------------------------------+
| ``eq(1)``              | Node for the specified index                       |
+------------------------+----------------------------------------------------+
| ``first()``            | First node                                         |
+------------------------+----------------------------------------------------+
| ``last()``             | Last node                                          |
+------------------------+----------------------------------------------------+
| ``siblings()``         | Siblings                                           |
+------------------------+----------------------------------------------------+
| ``nextAll()``          | All following siblings                             |
+------------------------+----------------------------------------------------+
| ``previousAll()``      | All preceding siblings                             |
+------------------------+----------------------------------------------------+
| ``parents()``          | Returns the parent nodes                           |
+------------------------+----------------------------------------------------+
| ``children()``         | Returns children nodes                             |
+------------------------+----------------------------------------------------+
| ``reduce($lambda)``    | Nodes for which the callable does not return false |
+------------------------+----------------------------------------------------+

Since each of these methods returns a new ``Crawler`` instance, you can
narrow down your node selection by chaining the method calls::

    $crawler
        ->filter('h1')
        ->reduce(function ($node, $i)
        {
            if (!$node->getAttribute('class')) {
                return false;
            }
        })
        ->first();

.. tip::

    Use the ``count()`` function to get the number of nodes stored in a Crawler:
    ``count($crawler)``

Extracting Information
~~~~~~~~~~~~~~~~~~~~~~

The Crawler can extract information from the nodes::

    // Returns the attribute value for the first node
    $crawler->attr('class');

    // Returns the node value for the first node
    $crawler->text();

    // Extracts an array of attributes for all nodes (_text returns the node value)
    // returns an array for each element in crawler, each with the value and href
    $info = $crawler->extract(array('_text', 'href'));

    // Executes a lambda for each node and return an array of results
    $data = $crawler->each(function ($node, $i)
    {
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

Just like links, you select forms with the ``selectButton()`` method::

    $buttonCrawlerNode = $crawler->selectButton('submit');

.. note::

    Notice that we select form buttons and not forms as a form can have several
    buttons; if you use the traversing API, keep in mind that you must look for a
    button.

The ``selectButton()`` method can select ``button`` tags and submit ``input``
tags. It uses several different parts of the buttons to find them:

* The ``value`` attribute value;

* The ``id`` or ``alt`` attribute value for images;

* The ``id`` or ``name`` attribute value for ``button`` tags.

Once you have a Crawler representing a button, call the ``form()`` method
to get a ``Form`` instance for the form wrapping the button node::

    $form = $buttonCrawlerNode->form();

When calling the ``form()`` method, you can also pass an array of field values
that overrides the default ones::

    $form = $buttonCrawlerNode->form(array(
        'name'              => 'Fabien',
        'my_form[subject]'  => 'Symfony rocks!',
    ));

And if you want to simulate a specific HTTP method for the form, pass it as a
second argument::

    $form = $buttonCrawlerNode->form(array(), 'DELETE');

The Client can submit ``Form`` instances::

    $client->submit($form);

The field values can also be passed as a second argument of the ``submit()``
method::

    $client->submit($form, array(
        'name'              => 'Fabien',
        'my_form[subject]'  => 'Symfony rocks!',
    ));

For more complex situations, use the ``Form`` instance as an array to set the
value of each field individually::

    // Change the value of a field
    $form['name'] = 'Fabien';
    $form['my_form[subject]'] = 'Symfony rocks!';

There is also a nice API to manipulate the values of the fields according to
their type::

    // Select an option or a radio
    $form['country']->select('France');

    // Tick a checkbox
    $form['like_symfony']->tick();

    // Upload a file
    $form['photo']->upload('/path/to/lucas.jpg');

.. tip::

    You can get the values that will be submitted by calling the ``getValues()``
    method on the ``Form`` object. The uploaded files are available in a
    separate array returned by ``getFiles()``. The ``getPhpValues()`` and
    ``getPhpFiles()`` methods also return the submitted values, but in the
    PHP format (it converts the keys with square brackets notation - e.g.
    ``my_form[subject]`` - to PHP arrays).

.. index::
   pair: Tests; Configuration

Testing Configuration
---------------------

The Client used by functional tests creates a Kernel that runs in a special
``test`` environment. Since Symfony loads the ``app/config/config_test.yml``
in the ``test`` environment, you can tweak any of your application's settings
specifically for testing.

For example, by default, the swiftmailer is configured to *not* actually
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
        <container>
            <!-- ... -->

            <swiftmailer:config disable-delivery="true" />
        </container>

    .. code-block:: php

        // app/config/config_test.php

        // ...

        $container->loadFromExtension('swiftmailer', array(
            'disable_delivery' => true
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
    environment (or wherever the :ref:`framework.test<reference-framework-test>`
    option is enabled). This means you can override the service entirely
    if you need to.

.. index::
   pair: PHPUnit; Configuration

PHPUnit Configuration
~~~~~~~~~~~~~~~~~~~~~

Each application has its own PHPUnit configuration, stored in the
``phpunit.xml.dist`` file. You can edit this file to change the defaults or
create a ``phpunit.xml`` file to tweak the configuration for your local machine.

.. tip::

    Store the ``phpunit.xml.dist`` file in your code repository, and ignore the
    ``phpunit.xml`` file.

By default, only the tests stored in "standard" bundles are run by the
``phpunit`` command (standard being tests in the ``src/*/Bundle/Tests`` or
``src/*/Bundle/*Bundle/Tests`` directories) But you can easily add more
directories. For instance, the following configuration adds the tests from
the installed third-party bundles:

.. code-block:: xml

    <!-- hello/phpunit.xml.dist -->
    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>../src/*/*Bundle/Tests</directory>
            <directory>../src/Acme/Bundle/*Bundle/Tests</directory>
        </testsuite>
    </testsuites>

To include other directories in the code coverage, also edit the ``<filter>``
section:

.. code-block:: xml

    <filter>
        <whitelist>
            <directory>../src</directory>
            <exclude>
                <directory>../src/*/*Bundle/Resources</directory>
                <directory>../src/*/*Bundle/Tests</directory>
                <directory>../src/Acme/Bundle/*Bundle/Resources</directory>
                <directory>../src/Acme/Bundle/*Bundle/Tests</directory>
            </exclude>
        </whitelist>
    </filter>

Learn more
----------

* :doc:`/components/dom_crawler`
* :doc:`/components/css_selector`
* :doc:`/cookbook/testing/http_authentication`
* :doc:`/cookbook/testing/insulating_clients`
* :doc:`/cookbook/testing/profiling`


.. _`DemoControllerTest`: https://github.com/symfony/symfony-standard/blob/master/src/Acme/DemoBundle/Tests/Controller/DemoControllerTest.php
.. _`$_SERVER`: http://php.net/manual/en/reserved.variables.server.php
.. _`documentation`: http://www.phpunit.de/manual/3.5/en/
