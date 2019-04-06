Unit Testing
============

You might have noticed some subtle but nonetheless important bugs in the
framework we built in the previous chapter. When creating a framework, you
must be sure that it behaves as advertised. If not, all the applications based
on it will exhibit the same bugs. The good news is that whenever you fix a
bug, you are fixing a bunch of applications too.

Today's mission is to write unit tests for the framework we have created by
using `PHPUnit`_. Create a PHPUnit configuration file in
``example.com/phpunit.xml.dist``:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <phpunit
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/5.1/phpunit.xsd"
        backupGlobals="false"
        colors="true"
        bootstrap="vendor/autoload.php"
    >
        <testsuites>
            <testsuite name="Test Suite">
                <directory>./tests</directory>
            </testsuite>
        </testsuites>

        <filter>
            <whitelist processUncoveredFilesFromWhitelist="true">
                <directory suffix=".php">./src</directory>
            </whitelist>
        </filter>
    </phpunit>

This configuration defines sensible defaults for most PHPUnit settings; more
interesting, the autoloader is used to bootstrap the tests, and tests will be
stored under the ``example.com/tests/`` directory.

Now, let's write a test for "not found" resources. To avoid the creation of
all dependencies when writing tests and to really just unit-test what we want,
we are going to use `test doubles`_. Test doubles are easier to create when we
rely on interfaces instead of concrete classes. Fortunately, Symfony provides
such interfaces for core objects like the URL matcher and the controller
resolver. Modify the framework to make use of them::

    // example.com/src/Simplex/Framework.php
    namespace Simplex;

    // ...

    use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
    use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
    use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

    class Framework
    {
        protected $matcher;
        protected $controllerResolver;
        protected $argumentResolver;

        public function __construct(UrlMatcherInterface $matcher, ControllerResolverInterface $resolver, ArgumentResolverInterface $argumentResolver)
        {
            $this->matcher = $matcher;
            $this->controllerResolver = $resolver;
            $this->argumentResolver = $argumentResolver;
        }

        // ...
    }

We are now ready to write our first test::

    // example.com/tests/Simplex/Tests/FrameworkTest.php
    namespace Simplex\Tests;

    use PHPUnit\Framework\TestCase;
    use Simplex\Framework;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
    use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
    use Symfony\Component\Routing;
    use Symfony\Component\Routing\Exception\ResourceNotFoundException;

    class FrameworkTest extends TestCase
    {
        public function testNotFoundHandling()
        {
            $framework = $this->getFrameworkForException(new ResourceNotFoundException());

            $response = $framework->handle(new Request());

            $this->assertEquals(404, $response->getStatusCode());
        }

        private function getFrameworkForException($exception)
        {
            $matcher = $this->createMock(Routing\Matcher\UrlMatcherInterface::class);
            // use getMock() on PHPUnit 5.3 or below
            // $matcher = $this->getMock(Routing\Matcher\UrlMatcherInterface::class);

            $matcher
                ->expects($this->once())
                ->method('match')
                ->will($this->throwException($exception))
            ;
            $matcher
                ->expects($this->once())
                ->method('getContext')
                ->will($this->returnValue($this->createMock(Routing\RequestContext::class)))
            ;
            $controllerResolver = $this->createMock(ControllerResolverInterface::class);
            $argumentResolver = $this->createMock(ArgumentResolverInterface::class);

            return new Framework($matcher, $controllerResolver, $argumentResolver);
        }
    }

This test simulates a request that does not match any route. As such, the
``match()`` method returns a ``ResourceNotFoundException`` exception and we
are testing that our framework converts this exception to a 404 response.

Execute this test by running ``phpunit`` in the ``example.com`` directory:

.. code-block:: terminal

    $ phpunit

.. note::

    If you don't understand what the hell is going on in the code, read the
    PHPUnit documentation on `test doubles`_.

After the test ran, you should see a green bar. If not, you have a bug
either in the test or in the framework code!

Adding a unit test for any exception thrown in a controller::

    public function testErrorHandling()
    {
        $framework = $this->getFrameworkForException(new \RuntimeException());

        $response = $framework->handle(new Request());

        $this->assertEquals(500, $response->getStatusCode());
    }

Last, but not the least, let's write a test for when we actually have a proper
Response::

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Controller\ControllerResolver;
    use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
    // ...

    public function testControllerResponse()
    {
        $matcher = $this->createMock(Routing\Matcher\UrlMatcherInterface::class);
        // use getMock() on PHPUnit 5.3 or below
        // $matcher = $this->getMock(Routing\Matcher\UrlMatcherInterface::class);

        $matcher
            ->expects($this->once())
            ->method('match')
            ->will($this->returnValue([
                '_route' => 'foo',
                'name' => 'Fabien',
                '_controller' => function ($name) {
                    return new Response('Hello '.$name);
                }
            ]))
        ;
        $matcher
            ->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->createMock(Routing\RequestContext::class)))
        ;
        $controllerResolver = new ControllerResolver();
        $argumentResolver = new ArgumentResolver();

        $framework = new Framework($matcher, $controllerResolver, $argumentResolver);

        $response = $framework->handle(new Request());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Hello Fabien', $response->getContent());
    }

In this test, we simulate a route that matches and returns a simple
controller. We check that the response status is 200 and that its content is
the one we have set in the controller.

To check that we have covered all possible use cases, run the PHPUnit test
coverage feature (you need to enable `XDebug`_ first):

.. code-block:: terminal

    $ phpunit --coverage-html=cov/

Open ``example.com/cov/src/Simplex/Framework.php.html`` in a browser and check
that all the lines for the Framework class are green (it means that they have
been visited when the tests were executed).

Alternatively you can output the result directly to the console:

.. code-block:: terminal

    $ phpunit --coverage-text

Thanks to the clean object-oriented code that we have written so far, we have
been able to write unit-tests to cover all possible use cases of our
framework; test doubles ensured that we were actually testing our code and not
Symfony code.

Now that we are confident (again) about the code we have written, we can
safely think about the next batch of features we want to add to our framework.

.. _`PHPUnit`: https://phpunit.de/manual/current/en/index.html
.. _`test doubles`: https://phpunit.de/manual/current/en/test-doubles.html
.. _`XDebug`: https://xdebug.org/
