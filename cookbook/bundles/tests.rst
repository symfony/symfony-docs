.. index::
   single: Bundle; Testing third party bundles

Writing Tests for Third Party Bundles
=====================================

The Standard Edition's front controller looks like this::

    // web/app.php
    use Symfony\Component\HttpFoundation\Request;

    require_once __DIR__.'/../vendor/autoload.php';
    require_once __DIR__.'/../app/AppKernel.php';

    // Create the request from PHP variables super globals such as $_GET, $_POST, $_SERVER, etc
    $request = Request::createFromGlobals();

    // The application handles the Request
    $kernel = new AppKernel('prod', false);
    $response = $kernel->handle($request);

    // Dump the Response's headers and print the Response's content
    $response->send();

In this example, Symfony is used as an application that mimicks the HTTP protocol,
which means that it is possible to write transversal tests (System Tests) by building
"manually" a ``Request``, giving it to our application and then check the returned ``Response``.

This article provides examples on how to write such tests with third party bundles.

Creating a Minimal AppKernel
----------------------------

Third party bundles cannot be ran standalone, they need an application.
It is possible to create a minimalistic ``AppKernel`` inside the bundle for testing
and showcase purposes, instead of:

# Creating a separate application;
# Install the bundle in it;
# Then check how things turn out.

If the third party bundle is named ``AcmeMyBundle``, the ``AppKernel`` could look like this::

    // Tests/app/AppKernel.php
    namespace Acme\MyBundle\Tests;

    use Symfony\Component\HttpKernel\Kernel;
    use Symfony\Component\Config\Loader\LoaderInterface;

    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            return array(
                new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                new Acme\MyBundle\AcmeMyBundle(),
            );
        }

        public function registerContainerConfiguration(LoaderInterface $loader)
        {
            $loader->load(__DIR__.'/config.yml');
        }
    }

.. note::

    ``AppKernel`` adds a direct dependency on Symfony's FrameworkBundle, which can be
    installed by running the following command: ``composer require --dev symfony/framework-bundle``.

In its minimum state ``AppKernel`` requires only one configuration parameter, ``secret``:

.. configuration-block::

    .. code-block:: yaml

        # Tests/app/config.yml
        framework:
            secret: Th1s1sS3cr3t!

Symfony applications generate cache and logs directories, which can be ignored by
adding those lines to ``.gitignore``:

.. code-block::

    # .gitignore
    /Tests/app/cache
    /Tests/app/logs
    /vendor
    /composer.lock

Browsing Pages
--------------

If the third party bundle provides a controller, it can be helpful to create a
front controller similar to the one that can be found in Symfony's Standard Edition::

    // Tests/app/web.php
    use Symfony\Component\HttpFoundation\Request;

    require_once __DIR__.'/autoload.php';

    $kernel = new AppKernel('prod', false);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();

With this it becomes possible to browse the page. A way to do it without having to configure
a web server is to run the following command:

.. code-block:: bash

    $ php Tests/app/console.php server:run -d Tests/app

.. note::

    If the third party bundle uses the Twig templating engine to render HTML pages
    or if it uses the Form Component or anything else, then more dependencies and
    configuration parameters should be added.

Running Commands
----------------

If the third party bundle provides a command, it can be helpful to create a ``console``
similar to the one that can be found in Symfony's Standard Edition::

    // Tests/app/console.php
    set_time_limit(0);

    require_once __DIR__.'/autoload.php';

    use Symfony\Bundle\FrameworkBundle\Console\Application;

    $kernel = new AppKernel('dev', true);
    $application = new Application($kernel);
    $application->run();

With this it becomes possible to run manually the console:

.. code-block:: bash

    $ php Tests/app/console.php

Automated Tests
---------------

Manual tests are great to get a quick idea of what the bundle does. But writing
automated tests is even better!

The first step is to install a test framework like PHPUnit:

.. code-block:: bash

    $ composer require --dev phpunit/phpunit

.. note::

    The steps should be similar with other tests frameworks.

Then the second one is to configure it to use Composer's autoloading:

.. configuration-block::

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8"?>
        <!-- phpunit.xml.dist -->

        <!-- http://phpunit.de/manual/4.3/en/appendixes.configuration.html -->
        <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/4.3/phpunit.xsd"
            backupGlobals="false"
            colors="true"
            bootstrap="./vendor/autoload.php"
        >
            <testsuites>
                <testsuite name="Test Suite">
                    <directory>./Tests/</directory>
                </testsuite>
            </testsuites>

            <filter>
                <whitelist>
                    <directory>.</directory>
                    <exclude>
                        <directory>./Resources</directory>
                        <directory>./Tests</directory>
                    </exclude>
                </whitelist>
            </filter>
        </phpunit>

With these two simple steps it becomes possible to run the test suite with the following command:

.. code-block:: bash

    $ vendor/bin/phpunit

Functional Web Tests
~~~~~~~~~~~~~~~~~~~~

As advised in the official best practices (smoke testing), writing tests for
controllers can be done by simply checking the status code::

    // Tests/Controller/DemoControllerTest.php
    namespace Acme\MyBundle\Tests\Controller;

    use Acme\MyBundle\Tests\AppKernel;
    use Symfony\Component\HttpFoundation\Request;

    class DemoControllerTest extends \PHPUnit_Framework_TestCase
    {
        private $app;

        protected function setUp()
        {
            $this->app = new AppKernel('test', false);
            $this->app->boot();
        }

        public function testItRunsSuccessfully()
        {
            $headers = array('CONTENT_TYPE' => 'application/json');
            $content = array('parameter' => 'value');
            $request = Request::create('/demo', 'POST', array(), array(), array(), $headers, $content);

            $response = $this->app->handle($request);

            $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        }
    }

Functional CLI Tests
~~~~~~~~~~~~~~~~~~~~

As advised in the official best practices (smoke testing), writing tests for
commands can be done by simply checking the exit code::

    // Tests/Command/DemoCommandTest.php
    namespace Acme\MyBundle\Tests\Command;

    use Acme\MyBundle\Tests\AppKernel;
    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Component\Console\Tester\ApplicationTester;

    class DemoCommandTest extends \PHPUnit_Framework_TestCase
    {
        private $application;

        protected function setUp()
        {
            $kernel = new AppKernel('test', false);
            $application = new Application($kernel);
            $application->setAutoExit(false);
            $this->application = new ApplicationTester($application);
        }

        public function testItRunsSuccessfully()
        {
            $exitCode = $this->application->run(array(
                'command_name' => 'acme:demo',
                'argument' => 'value',
                '--option' => 'value',
            ));

            $this->assertSame(0, $exitCode, $this->application->getDisplay());
        }
    }

Service Definition Tests
~~~~~~~~~~~~~~~~~~~~~~~~

It is possible to check that a service is correctly defined using a test::

    // Tests/ServiceDefinition/DemoServiceDefinitionTest.php
    namespace Acme\MyBundle\Tests\ServiceDefinition;

    use Acme\MyBundle\Tests\AppKernel;

    class DemoServiceDefinitionTest extends \PHPUnit_Framework_TestCase
    {
        private $container;

        protected function setUp()
        {
            $app = new AppKernel('test', false);
            $app->boot();
            $this->container = $app->getContainer();
        }

        public function testItIsDefinedCorrectly()
        {
            $demo = $this->container->get('app.demo');

            $this->assertInstanceOf('Acme\Service\Demo', $demo);
        }
    }

.. tip::

    Matthias Noback's library, `SymfonyServiceDefinitioValidator`_, is also
    a good way to check service definitions.

Conclusion
----------

By creating a minimal ``AppKernel`` in a third party bundle it becomes possible
to run it on its own which can be useful for showcases, but most importantly: It
makes it possible to write automated tests.

.. _SymfonyServiceDefinitioValidator: https://github.com/matthiasnoback/symfony-service-definition-validator
