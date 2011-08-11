.. index::
   single: Tests

Testing
=======

Whenever you write a new line of code, you also potentially add new bugs.
Automated tests should have you covered and this tutorial shows you how to
write unit and functional tests for your Symfony2 application.

Testing Framework
-----------------

Symfony2 tests rely heavily on PHPUnit, its best practices, and some
conventions. This part does not document PHPUnit itself, but if you don't know
it yet, you can read its excellent `documentation`_.

.. note::

    Symfony2 works with PHPUnit 3.5.11 or later.

The default PHPUnit configuration looks for tests under the ``Tests/``
sub-directory of your bundles:

.. code-block:: xml

    <!-- app/phpunit.xml.dist -->

    <phpunit bootstrap="../src/autoload.php">
        <testsuites>
            <testsuite name="Project Test Suite">
                <directory>../src/*/*Bundle/Tests</directory>
            </testsuite>
        </testsuites>

        ...
    </phpunit>

Running the test suite for a given application is straightforward:

.. code-block:: bash

    # specify the configuration directory on the command line
    $ phpunit -c app/

    # or run phpunit from within the application directory
    $ cd app/
    $ phpunit

.. tip::

    Code coverage can be generated with the ``--coverage-html`` option.

.. index::
   single: Tests; Unit Tests

Unit Tests
----------

Writing Symfony2 unit tests is no different than writing standard PHPUnit unit
tests. By convention, it's recommended to replicate the bundle directory
structure under its ``Tests/`` sub-directory. So, write tests for the
``Acme\HelloBundle\Model\Article`` class in the
``Acme/HelloBundle/Tests/Model/ArticleTest.php`` file.

In a unit test, autoloading is automatically enabled via the
``src/autoload.php`` file (as configured by default in the ``phpunit.xml.dist``
file).

Running tests for a given file or directory is also very easy:

.. code-block:: bash

    # run all tests for the Controller
    $ phpunit -c app src/Acme/HelloBundle/Tests/Controller/

    # run all tests for the Model
    $ phpunit -c app src/Acme/HelloBundle/Tests/Model/

    # run tests for the Article class
    $ phpunit -c app src/Acme/HelloBundle/Tests/Model/ArticleTest.php

    # run all tests for the entire Bundle
    $ phpunit -c app src/Acme/HelloBundle/

.. index::
   single: Tests; Functional Tests

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

Requests, clicks, and submissions are done by a client that knows how to talk
to the application. To access such a client, your tests need to extend the
Symfony2 ``WebTestCase`` class. The Symfony2 Standard Edition provides a
simple functional test for ``DemoController`` that reads as follows::

    // src/Acme/DemoBundle/Tests/Controller/DemoControllerTest.php
    namespace Acme\DemoBundle\Tests\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class DemoControllerTest extends WebTestCase
    {
        public function testIndex()
        {
            $client = static::createClient();

            $crawler = $client->request('GET', '/demo/hello/Fabien');

            $this->assertTrue($crawler->filter('html:contains("Hello Fabien")')->count() > 0);
        }
    }

The ``createClient()`` method returns a client tied to the current application::

    $crawler = $client->request('GET', 'hello/Fabien');

The ``request()`` method returns a ``Crawler`` object which can be used to
select elements in the Response, to click on links, and to submit forms.

.. tip::

    The Crawler can only be used if the Response content is an XML or an HTML
    document. For other content types, get the content of the Response with
    ``$client->getResponse()->getContent()``.

    You can set the content-type of the request to JSON by adding 'HTTP_CONTENT_TYPE' => 'application/json'.

.. tip::

    The full signature of the ``request()`` method is::

        request($method,
            $uri, 
            array $parameters = array(), 
            array $files = array(), 
            array $server = array(), 
            $content = null, 
            $changeHistory = true
        )   

Click on a link by first selecting it with the Crawler using either a XPath
expression or a CSS selector, then use the Client to click on it::

    $link = $crawler->filter('a:contains("Greet")')->eq(1)->link();

    $crawler = $client->click($link);

Submitting a form is very similar; select a form button, optionally override
some form values, and submit the corresponding form::

    $form = $crawler->selectButton('submit')->form();

    // set some values
    $form['name'] = 'Lucas';

    // submit the form
    $crawler = $client->submit($form);

Each ``Form`` field has specialized methods depending on its type::

    // fill an input field
    $form['name'] = 'Lucas';

    // select an option or a radio
    $form['country']->select('France');

    // tick a checkbox
    $form['like_symfony']->tick();

    // upload a file
    $form['photo']->upload('/path/to/lucas.jpg');

Instead of changing one field at a time, you can also pass an array of values
to the ``submit()`` method::

    $crawler = $client->submit($form, array(
        'name'         => 'Lucas',
        'country'      => 'France',
        'like_symfony' => true,
        'photo'        => '/path/to/lucas.jpg',
    ));

Now that you can easily navigate through an application, use assertions to test
that it actually does what you expect it to. Use the Crawler to make assertions
on the DOM::

    // Assert that the response matches a given CSS selector.
    $this->assertTrue($crawler->filter('h1')->count() > 0);

Or, test against the Response content directly if you just want to assert that
the content contains some text, or if the Response is not an XML/HTML
document::

    $this->assertRegExp('/Hello Fabien/', $client->getResponse()->getContent());

.. index::
   single: Tests; Assertions

Useful Assertions
~~~~~~~~~~~~~~~~~

After some time, you will notice that you always write the same kind of
assertions. To get you started faster, here is a list of the most common and
useful assertions::

    // Assert that the response matches a given CSS selector.
    $this->assertTrue($crawler->filter($selector)->count() > 0);

    // Assert that the response matches a given CSS selector n times.
    $this->assertEquals($count, $crawler->filter($selector)->count());

    // Assert the a response header has the given value.
    $this->assertTrue($client->getResponse()->headers->contains($key, $value));

    // Assert that the response content matches a regexp.
    $this->assertRegExp($regexp, $client->getResponse()->getContent());

    // Assert the response status code.
    $this->assertTrue($client->getResponse()->isSuccessful());
    $this->assertTrue($client->getResponse()->isNotFound());
    $this->assertEquals(200, $client->getResponse()->getStatusCode());

    // Assert that the response status code is a redirect.
    $this->assertTrue($client->getResponse()->isRedirect('google.com'));

.. _documentation: http://www.phpunit.de/manual/3.5/en/

.. index::
   single: Tests; Client

The Test Client
---------------

The test Client simulates an HTTP client like a browser.

.. note::

    The test Client is based on the ``BrowserKit`` and the ``Crawler``
    components.

Making Requests
~~~~~~~~~~~~~~~

The client knows how to make requests to a Symfony2 application::

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
These methods are the best way to browse an application as it hides a lot of
details. For instance, when you submit a form, it automatically detects the
HTTP method and the form URL, it gives you a nice API to upload files, and it
merges the submitted values with the form default ones, and more.

.. tip::

    You will learn more about the ``Link`` and ``Form`` objects in the Crawler
    section below.

But you can also simulate form submissions and complex requests with the
additional arguments of the ``request()`` method::

    // Form submission
    $client->request('POST', '/submit', array('name' => 'Fabien'));

    // Form submission with a file upload
    $client->request('POST', '/submit', array('name' => 'Fabien'), array('photo' => '/path/to/photo'));

    // Specify HTTP headers
    $client->request('DELETE', '/post/12', array(), array(), array('PHP_AUTH_USER' => 'username', 'PHP_AUTH_PW' => 'pa$$word'));

When a request returns a redirect response, the client automatically follows
it. This behavior can be changed with the ``followRedirects()`` method::

    $client->followRedirects(false);

When the client does not follow redirects, you can force the redirection with
the ``followRedirect()`` method::

    $crawler = $client->followRedirect();

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
HTTP layer.

.. tip::

    If the information you need to check are available from the profiler, use
    them instead.

Accessing the Profiler Data
~~~~~~~~~~~~~~~~~~~~~~~~~~~

To assert data collected by the profiler, you can get the profile for the
current request like this::

    $profile = $client->getProfile();

Redirecting
~~~~~~~~~~~

By default, the Client doesn't follow HTTP redirects, so that you can get
and examine the Response before redirecting. Once you do want the client
to redirect, call the ``followRedirect()`` method::

    // do something that would cause a redirect to be issued (e.g. fill out a form)

    // follow the redirect
    $crawler = $client->followRedirect();

If you want the Client to always automatically redirect, you can call the
``followRedirects()`` method::

    $client->followRedirects();

    $crawler = $client->request('GET', '/');

    // all redirects are followed

    // set Client back to manual redirection
    $client->followRedirects(false);

.. index::
   single: Tests; Crawler

The Crawler
-----------

A Crawler instance is returned each time you make a request with the Client.
It allows you to traverse HTML documents, select nodes, find links and forms.

Creating a Crawler Instance
~~~~~~~~~~~~~~~~~~~~~~~~~~~

A Crawler instance is automatically created for you when you make a request
with a Client. But you can create your own easily::

    use Symfony\Component\DomCrawler\Crawler;

    $crawler = new Crawler($html, $url);

The constructor takes two arguments: the second one is the URL that is used to
generate absolute URLs for links and forms; the first one can be any of the
following:

* An HTML document;
* An XML document;
* A ``DOMDocument`` instance;
* A ``DOMNodeList`` instance;
* A ``DOMNode`` instance;
* An array of the above elements.

After creation, you can add more nodes:

+-----------------------+----------------------------------+
| Method                | Description                      |
+=======================+==================================+
| ``addHTMLDocument()`` | An HTML document                 |
+-----------------------+----------------------------------+
| ``addXMLDocument()``  | An XML document                  |
+-----------------------+----------------------------------+
| ``addDOMDocument()``  | A ``DOMDocument`` instance       |
+-----------------------+----------------------------------+
| ``addDOMNodeList()``  | A ``DOMNodeList`` instance       |
+-----------------------+----------------------------------+
| ``addDOMNode()``      | A ``DOMNode`` instance           |
+-----------------------+----------------------------------+
| ``addNodes()``        | An array of the above elements   |
+-----------------------+----------------------------------+
| ``add()``             | Accept any of the above elements |
+-----------------------+----------------------------------+

Traversing
~~~~~~~~~~

Like jQuery, the Crawler has methods to traverse the DOM of an HTML/XML
document:

+-----------------------+----------------------------------------------------+
| Method                | Description                                        |
+=======================+====================================================+
| ``filter('h1')``      | Nodes that match the CSS selector                  |
+-----------------------+----------------------------------------------------+
| ``filterXpath('h1')`` | Nodes that match the XPath expression              |
+-----------------------+----------------------------------------------------+
| ``eq(1)``             | Node for the specified index                       |
+-----------------------+----------------------------------------------------+
| ``first()``           | First node                                         |
+-----------------------+----------------------------------------------------+
| ``last()``            | Last node                                          |
+-----------------------+----------------------------------------------------+
| ``siblings()``        | Siblings                                           |
+-----------------------+----------------------------------------------------+
| ``nextAll()``         | All following siblings                             |
+-----------------------+----------------------------------------------------+
| ``previousAll()``     | All preceding siblings                             |
+-----------------------+----------------------------------------------------+
| ``parents()``         | Parent nodes                                       |
+-----------------------+----------------------------------------------------+
| ``children()``        | Children                                           |
+-----------------------+----------------------------------------------------+
| ``reduce($lambda)``   | Nodes for which the callable does not return false |
+-----------------------+----------------------------------------------------+

You can iteratively narrow your node selection by chaining method calls as
each method returns a new Crawler instance for the matching nodes::

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
    $crawler->extract(array('_text', 'href'));

    // Executes a lambda for each node and return an array of results
    $data = $crawler->each(function ($node, $i)
    {
        return $node->getAttribute('href');
    });

Links
~~~~~

You can select links with the traversing methods, but the ``selectLink()``
shortcut is often more convenient::

    $crawler->selectLink('Click here');

It selects links that contain the given text, or clickable images for which
the ``alt`` attribute contains the given text.

The Client ``click()`` method takes a ``Link`` instance as returned by the
``link()`` method::

    $link = $crawler->link();

    $client->click($link);

.. tip::

    The ``links()`` method returns an array of ``Link`` objects for all nodes.

Forms
~~~~~

As for links, you select forms with the ``selectButton()`` method::

    $crawler->selectButton('submit');

Notice that we select form buttons and not forms as a form can have several
buttons; if you use the traversing API, keep in mind that you must look for a
button.

The ``selectButton()`` method can select ``button`` tags and submit ``input``
tags; it has several heuristics to find them:

* The ``value`` attribute value;

* The ``id`` or ``alt`` attribute value for images;

* The ``id`` or ``name`` attribute value for ``button`` tags.

When you have a node representing a button, call the ``form()`` method to get a
``Form`` instance for the form wrapping the button node::

    $form = $crawler->form();

When calling the ``form()`` method, you can also pass an array of field values
that overrides the default ones::

    $form = $crawler->form(array(
        'name'         => 'Fabien',
        'like_symfony' => true,
    ));

And if you want to simulate a specific HTTP method for the form, pass it as a
second argument::

    $form = $crawler->form(array(), 'DELETE');

The Client can submit ``Form`` instances::

    $client->submit($form);

The field values can also be passed as a second argument of the ``submit()``
method::

    $client->submit($form, array(
        'name'         => 'Fabien',
        'like_symfony' => true,
    ));

For more complex situations, use the ``Form`` instance as an array to set the
value of each field individually::

    // Change the value of a field
    $form['name'] = 'Fabien';

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
    method. The uploaded files are available in a separate array returned by
    ``getFiles()``. The ``getPhpValues()`` and ``getPhpFiles()`` also return
    the submitted values, but in the PHP format (it converts the keys with
    square brackets notation to PHP arrays).

.. index::
   pair: Tests; Configuration

Testing Configuration
---------------------

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
``phpunit`` command (standard being tests under Vendor\\*Bundle\\Tests
namespaces). But you can easily add more namespaces. For instance, the
following configuration adds the tests from the installed third-party bundles:

.. code-block:: xml

    <!-- hello/phpunit.xml.dist -->
    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>../src/*/*Bundle/Tests</directory>
            <directory>../src/Acme/Bundle/*Bundle/Tests</directory>
        </testsuite>
    </testsuites>

To include other namespaces in the code coverage, also edit the ``<filter>``
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

Client Configuration
~~~~~~~~~~~~~~~~~~~~

The Client used by functional tests creates a Kernel that runs in a special
``test`` environment, so you can tweak it as much as you want:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_test.yml
        imports:
            - { resource: config_dev.yml }

        framework:
            error_handler: false
            test: ~

        web_profiler:
            toolbar: false
            intercept_redirects: false

        monolog:
            handlers:
                main:
                    type:  stream
                    path:  %kernel.logs_dir%/%kernel.environment%.log
                    level: debug

    .. code-block:: xml

        <!-- app/config/config_test.xml -->
        <container>
            <imports>
                <import resource="config_dev.xml" />
            </imports>

            <webprofiler:config
                toolbar="false"
                intercept-redirects="false"
            />

            <framework:config error_handler="false">
                <framework:test />
            </framework:config>

            <monolog:config>
                <monolog:main
                    type="stream"
                    path="%kernel.logs_dir%/%kernel.environment%.log"
                    level="debug"
                 />               
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config_test.php
        $loader->import('config_dev.php');

        $container->loadFromExtension('framework', array(
            'error_handler' => false,
            'test'          => true,
        ));

        $container->loadFromExtension('web_profiler', array(
            'toolbar' => false,
            'intercept-redirects' => false,
        ));

        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'main' => array('type' => 'stream',
                                'path' => '%kernel.logs_dir%/%kernel.environment%.log'
                                'level' => 'debug')
           
        )));

You can also change the default environment (``test``) and override the
default debug mode (``true``) by passing them as options to the
``createClient()`` method::

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

    $client->request('GET', '/', array(), array(
        'HTTP_HOST'       => 'en.example.com',
        'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
    ));

.. tip::

    To provide your own Client, override the ``test.client.class`` parameter,
    or define a ``test.client`` service.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/testing/http_authentication`
* :doc:`/cookbook/testing/insulating_clients`
* :doc:`/cookbook/testing/profiling`
