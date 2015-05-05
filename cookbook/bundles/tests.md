.. index::
   single: Bundle; Testing Bundles

Writing tests for third party bundles
=====================================

Let's have a look at a minimized version og the Standard Edition's front controller:

    <?php
    // File: web/app.php

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

As we can see Symfony applications mimick the HTTP protocol, which means that we can
test them by building "manually" a ``Request``, giving it to our application and then
check the returned ``Response``.

In this article, we'll see how to write such tests in third party bundles.

Creating a minimal AppKernel
----------------------------

To be able to test our third party bundle, we'd need an application. This issue
can be solved by creating a minimal ``AppKernel`` inside our bundle, for testing
or showcase purpose only.

Let's write one:

    <?php
    // File: Tests/app/AppKernel.php

    use Symfony\Component\HttpKernel\Kernel;
    use Symfony\Component\Config\Loader\LoaderInterface;

    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            return array(
                new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                // register our bundle here
            );
        }

        public function registerContainerConfiguration(LoaderInterface $loader)
        {
            $loader->load(__DIR__.'/config.yml');
        }
    }

.. note::

    With this file we add a direct dependency on Symfony's FrameworkBundle, so we need
    to run the folowing command: `composer require --dev symfony/framework-bundle`

In its minimum state `AppKernel` requires one configuration parameter, `secret`:

    # File: Tests/app/config.yml
    framework:
        secret: Th1s1sS3cr3t!

If we want to use this kernel in our functional tests, we'll need to load it:

    <?php
    // File: Tests/app/autoload.php

    $loader = require __DIR__.'/../../vendor/autoload.php';
    require __DIR__.'/AppKernel.php';

Finally, running our application will create logs and cache directories, so we want to ignore them:

    # File: .gitignore
    /Tests/app/cache
    /Tests/app/logs

Running commands
----------------

Let's pretend we created a command in our bundle. we'd like to run it just to
make sure everything works as expected. For this we'll need to create an console:

    <?php
    // File: Tests/app/console.php

    set_time_limit(0);

    require_once __DIR__.'/autoload.php';

    use Symfony\Bundle\FrameworkBundle\Console\Application;

    $kernel = new AppKernel('dev', true);
    $application = new Application($kernel);
    $application->run();

That's it! we can now run:

    php Tests/app/console.php

Browsing pages
--------------

Let's pretend we created a controller which returns some JSON data. we'd like to
browse it just to make sure everyting works as expected. For this, we'll need to
create a front controller:

    <?php
    // File: Tests/app/web.php

    use Symfony\Component\HttpFoundation\Request;

    require_once __DIR__.'/autoload.php';

    $kernel = new AppKernel('prod', false);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();

That's it! we can now run:

    php Tests/app/console.php server:run -d Tests/app

And browse our application.

.. note::

    If we use a templating engine like Twig to render HTML pages, or if we use
    the Form Component in our bundle, we need to add the dependencies to our
    `composer.json` file and to register the appropriate bundles to our AppKernel.

Automated tests
---------------

Manual tests are great to get a quick idea of what our bundle does. But writing
automated tests is even better!

.. note::

    In this example we use PHPUnit, but the steps should be similar with other tests frameworks.

First of all let's install PHPUnit:

    composer require --dev phpunit/phpunit

Then we need to configure it to use our autoload:

    <?xml version="1.0" encoding="UTF-8"?>

    <!-- http://phpunit.de/manual/4.3/en/appendixes.configuration.html -->
    <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/4.3/phpunit.xsd"
        backupGlobals="false"
        colors="true"
        bootstrap="./Tests/app/autoload.php"
    >
        <testsuites>
            <testsuite name="Test Suite">
                <directory>./Tests/</directory>
            </testsuite>
        </testsuites>
    </phpunit>

Tests can now be ran with the following command:

    vendor/bin/phpunit

Functional CLI tests
~~~~~~~~~~~~~~~~~~~~

Let's pretend we created a command. we'd like to run it automatically and check
its exit code to make sure it works. For this, we'll need to create a simple test:

    <?php
    // File: Tests/ServiceTest.php

    namespace Acme\StandaloneBundle\Tests\Command;

    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Component\Console\Tester\ApplicationTester;

    class DemoCommandTest extends \PHPUnit_Framework_TestCase
    {
        private $application;

        protected function setUp()
        {
            $kernel = new \AppKernel('test', false);
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

And that's it!

Functional web tests
~~~~~~~~~~~~~~~~~~~~

Let's pretend we created a controller which returns some JSON data. we'd like to
browse it automatically and check its status code to make sure it works. For this,
we'll need to created a simple test:

    <?php
    // File: Tests/ServiceTest.php

    namespace Acme\StandaloneBundle\Tests\Controller;

    class DemoControllerTest extends \PHPUnit_Framework_TestCase
    {
        private $client;

        protected function setUp()
        {
            $kernel = new \AppKernel('test', false);
            $kernel->boot();

            $this->client = $kernel->getContainer()->get('test.client');
        }

        public function testItRunsSuccessfully()
        {
            $headers = array('CONTENT_TYPE' => 'application/json');
            $content = array('parameter' => 'value');
            $this->client->request('POST', '/demo', array(), array(), $headers, $content);
            $response = $this->client->getResponse();

            $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        }
    }

The ``test.client`` service is only available when the test configuration parameter is set.

    # File: Tests/app/config.yml
    framework:
        secret: "Three can keep a secret, if two of them are dead."
        test: ~

And that's it!

Conclusion
----------

By creating a minimal ``AppKernel`` in our third party bundle, we've enabled anyone
to run it on its own, which can be useful for showcases. But most importantly, we've
made it possible to write automated tests.
