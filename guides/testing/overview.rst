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

    Symfony2 works with PHPUnit 3.5 or later.

The default PHPUnit configuration looks for tests under ``Tests/``
sub-directory of your bundles:

.. code-block:: xml

    <!-- app/phpunit.xml.dist -->

    <phpunit ... bootstrap="../src/autoload.php">
        <testsuites>
            <testsuite name="Project Test Suite">
                <directory>../src/Application/*/Tests</directory>
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
``Application\HelloBundle\Model\Article`` class in the
``Application/HelloBundle/Tests/Model/ArticleTest.php`` file.

In a unit test, autoloading is automatically enabled via the
``src/autoload.php`` file (as configured by default in the ``phpunit.xml.dist``
file).

Running tests for a given file or directory is also very easy:

.. code-block:: bash

    # run all tests for the Model
    $ phpunit -c app Application/HelloBundle/Tests/Model/

    # run tests for the Article class
    $ phpunit -c app Application/HelloBundle/Tests/Model/ArticleTest.php

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
Symfony2 ``WebTestCase`` class. The sandbox provides a simple functional test
for ``HelloController`` that reads as follows::

    // src/Application/HelloBundle/Tests/Controller/HelloControllerTest.php
    namespace Application\HelloBundle\Tests\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class HelloControllerTest extends WebTestCase
    {
        public function testIndex()
        {
            $client = $this->createClient();
            $crawler = $client->request('GET', '/hello/Fabien');

            $this->assertEquals(1, count($crawler->filter('html:contains("Hello Fabien")')));
        }
    }

The ``createClient()`` method returns a client tied to the current application::

    $crawler = $client->request('GET', 'hello/Fabien');

The ``request()`` method returns a ``Crawler`` object which can be used to
select elements in the Response, to click on links, and to submit forms.

.. tip::

    The Crawler can only be used if the Response content is an XML or an HTML
    document.

Click on a link by first selecting it with the Crawler using either a XPath
expression or a CSS selector, then use the Client to click on it::

    $link = $crawler->filter('a:contains("Greet")')->eq(1)->link();

    $crawler = $client->click($link);

Submitting a form is very similar; select a form button, optionally override
some form values, and submit the corresponding form::

    $form = $crawler->selectButton('submit');

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
    $this->assertTrue(count($crawler->filter('h1')) > 0);

Or, test against the Response content directly if you just want to assert that
the content contains some text, or if the Response is not an XML/HTML
document::

    $this->assertRegExp('/Hello Fabien/', $client->getResponse()->getContent());

.. _documentation: http://www.phpunit.de/manual/3.5/en/
