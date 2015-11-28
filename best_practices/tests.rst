Tests
=====

Roughly speaking, there are two types of test. Unit testing allows you to
test the input and output of specific functions. Functional testing allows
you to command a "browser" where you browse to pages on your site, click
links, fill out forms and assert that you see certain things on the page.

Unit Tests
----------

Unit tests are used to test your "business logic", which should live in classes
that are independent of Symfony. For that reason, Symfony doesn't really
have an opinion on what tools you use for unit testing. However, the most
popular tools are `PhpUnit`_ and `PhpSpec`_.

Functional Tests
----------------

Creating really good functional tests can be tough so some developers skip
these completely. Don't skip the functional tests! By defining some *simple*
functional tests, you can quickly spot any big errors before you deploy them:

.. best-practice::

    Define a functional test that at least checks if your application pages
    are successfully loading.

A functional test can be as easy as this:

.. code-block:: php

    // tests/AppBundle/ApplicationAvailabilityFunctionalTest.php
    namespace Tests\AppBundle;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class ApplicationAvailabilityFunctionalTest extends WebTestCase
    {
        /**
         * @dataProvider urlProvider
         */
        public function testPageIsSuccessful($url)
        {
            $client = self::createClient();
            $client->request('GET', $url);

            $this->assertTrue($client->getResponse()->isSuccessful());
        }

        public function urlProvider()
        {
            return array(
                array('/'),
                array('/posts'),
                array('/post/fixture-post-1'),
                array('/blog/category/fixture-category'),
                array('/archives'),
                // ...
            );
        }
    }

This code checks that all the given URLs load successfully, which means that
their HTTP response status code is between ``200`` and ``299``. This may
not look that useful, but given how little effort this took, it's worth
having it in your application.

In computer software, this kind of test is called `smoke testing`_ and consists
of *"preliminary testing to reveal simple failures severe enough to reject a
prospective software release"*.

Hardcode URLs in a Functional Test
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Some of you may be asking why the previous functional test doesn't use the URL
generator service:

.. best-practice::

    Hardcode the URLs used in the functional tests instead of using the URL
    generator.

Consider the following functional test that uses the ``router`` service to
generate the URL of the tested page:

.. code-block:: php

    public function testBlogArchives()
    {
        $client = self::createClient();
        $url = $client->getContainer()->get('router')->generate('blog_archives');
        $client->request('GET', $url);

        // ...
    }

This will work, but it has one *huge* drawback. If a developer mistakenly
changes the path of the ``blog_archives`` route, the test will still pass,
but the original (old) URL won't work! This means that any bookmarks for
that URL will be broken and you'll lose any search engine page ranking.

Testing JavaScript Functionality
--------------------------------

The built-in functional testing client is great, but it can't be used to
test any JavaScript behavior on your pages. If you need to test this, consider
using the `Mink`_ library from within PHPUnit.

Of course, if you have a heavy JavaScript frontend, you should consider using
pure JavaScript-based testing tools.

Learn More about Functional Tests
---------------------------------

Consider using `Faker`_ and `Alice`_ libraries to generate real-looking data
for your test fixtures.

.. _`Faker`: https://github.com/fzaninotto/Faker
.. _`Alice`: https://github.com/nelmio/alice
.. _`PhpUnit`: https://phpunit.de/
.. _`PhpSpec`: http://www.phpspec.net/
.. _`Mink`: http://mink.behat.org
.. _`smoke testing`: https://en.wikipedia.org/wiki/Smoke_testing_(software)
