Testing
=======

Whenever you write a new line of code, you also potentially add new bugs.
Automated tests should have you covered and this tutorial is all about writing
unit and functional tests for your Symfony2 application.

Testing Framework
-----------------

Symfony2 tests rely heavily on PHPUnit, its best practices, and some
conventions. This part does not document PHPUnit itself, but if you don't know
it yet, you can read its excellent [documentation][1].

>**NOTE**
>The PHPUnit integration relies on PHPUnit 3.5 or later.

The default PHPUnit configuration looks for tests under `Tests/`
sub-directories:

    [xml]
    <!-- hello/phpunit.xml.dist -->
    <?xml version="1.0" encoding="UTF-8"?>

    <phpunit ... bootstrap="../src/autoload.php">
        <testsuites>
            <testsuite name="Project Test Suite">
                <directory>../src/Application/*/Tests</directory>
            </testsuite>
        </testsuites>

        ...
    </phpunit>

Running the test suite for a given application is straightforward:

    # specify the configuration directory on the command line
    $ phpunit -c hello/

    # or run phpunit from within the application directory
    $ cd hello/
    $ phpunit


>**TIP**
>Code coverage can be generated with the `--coverage-html` option.

Unit Tests
----------

Writing Symony2 unit tests is no different than writing standard PHPUnit unit
tests. By convention, its better to replicate the bundle directory structure
under the `Tests/` sub-directory of a bundle. So, write tests for the
`Application\HelloBundle\Model\Article` class in the
`Application/HelloBundle/Tests/Model/ArticleTest.php` file.

In a unit test, autoloading is automatically done based on `src/autoload.php`
file (as configured by default in the `phpunit.xml.dist` file).

If you want to run tests for a given file or directory, this is also very
easy:

    # run all tests for the Model
    $ phpunit -c hello Application/HelloBundle/Tests/Model/

    # run tests for the Article class
    $ phpunit -c hello Application/HelloBundle/Tests/Model/ArticleTest.php

Functional Tests
----------------

Functional tests are no different from unit tests as far as PHPUnit is
concerned. But as functional tests exercise controllers and views, they are
run in their own environment and have their own configuration stored in
`config_test.yml`:

    [yml]
    # config_test.yml
    imports:
        - { resource: config_dev.yml }

    web.config:
        toolbar: false

    zend.logger:
        priority: debug

    kernel.test: ~

A Symfony2 functional tests also extend a special test case class that
provides tools that simulate a client. This client is enabled with the
`kernel.test` configuration block above.

The sandbox comes with a simple test class for `HelloController`:

    [php]
    // src/Application/HelloBundle/Tests/Controller/HelloControllerTest.php
    namespace Application\HelloBundle\Tests\Controller;

    use Symfony\Framework\WebBundle\Test\WebTestCase;

    class HelloControllerTest extends WebTestCase
    {
        public function testIndex()
        {
            $client = $this->createClient();
            $crawler = $client->request('GET', '/hello/Fabien');

            $this->assertTrue($crawler->filter('html:contains("Hello Fabien")')->count());
        }
    }

A test is made of two parts: the client, returned by the `createClient()`
method and used to browse the application, and assertions to write tests.

### The Client

The `createClient()` method returns a client tied to the current application.
It can be used to browse the application by making simple requests:

    [php]
    $crawler = $client->request('GET', 'hello/Fabien');

The `request` method returns a `Crawler` object. It can be used to click on
links or to submit forms.

>**TIP**
>You can get the `Response` object for a given request with the `getResponse()`
>method of the client object.

Click on a link by first selecting it with the crawler using either a XPath or
a CSS selector, then use the client to click on it:

    [php]
    $link = $crawler->filter('a:contains("Greet")')->eq(1)->link();

    $crawler = $client->click($link);

Submitting a form is very similar; select a form button, optionally override
some form values, and submit the corresponding form:

    [php]
    $form = $crawler->selectButton('submit');

    // set some values
    $form['name'] = 'Lucas';

    // submit the form
    $crawler = $client->submit($form);

Each `Form` field has specialized methods depending on its type:

    [php]
    // fill an input field
    $form['name'] = 'Lucas';

    // select an option or a radio
    $form['country']->select('France');

    // tick a checkbox
    $form['like_symfony']->tick();

    // upload a file
    $form['photo']->upload('/path/to/lucas.jpg');

Instead of changing one field at a time, you can also pass an array of values
to the `submit()` method:

    [php]
    $crawler = $client->submit($form, array(
        'name'         => 'Lucas',
        'country'      => 'France',
        'like_symfony' => true,
        'photo'        => '/path/to/lucas.jpg',
    ));

### Assertions

Now that you can easily navigate through an application, let's see how you can
test that it actually does what you expect it to. The following code shows the
most common and useful tests you might want to do on the response:

    [php]
    // Assert that the response matches a given CSS selector.
    $this->assertFalse($crawler->filter($selector)->isEmpty());

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
    $this->assertTrue($client->getResponse()->isRedirected('google.com'));

[1]: http://www.phpunit.de/manual/3.5/en/
