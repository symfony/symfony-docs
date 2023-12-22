End-to-End Testing
==================

    The Panther component allows to drive a real web browser with PHP to create
    end-to-end tests.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/panther

.. include:: /components/require_autoload.rst.inc

Introduction
------------

End to end tests are a special type of application tests that
simulate a real user interacting with your application. They are
typically used to test the user interface (UI) of your application
and the effects of these interactions (e.g. when I click on this button, a mail
must be sent). The difference with functional tests detailed above is
that End-to-End tests use a real browser instead of a simulated one. This
browser can run in headless mode (without a graphical interface) or not.
The first option is convenient for running tests in a Continuous Integration
(CI), while the second one is useful for debugging purpose.

This is the purpose of Panther, a component that provides a real browser
to run your tests. Here are a few things that make Panther special, compared
to other testing tools provided by Symfony:

* Possibility to take screenshots of the browser at any time during the test
* The JavaScript code contained in webpages is executed
* Panther supports everything that Chrome (or Firefox) implements
* Convenient way to test real-time applications (e.g. WebSockets, Server-Sent Events
  with Mercure, etc.)

Installing Web Drivers
~~~~~~~~~~~~~~~~~~~~~~

Panther uses the WebDriver protocol to control the browser used to crawl
websites. On all systems, you can use `dbrekelmans/browser-driver-installer`_
to install ChromeDriver and geckodriver locally:

.. code-block:: terminal

    $ composer require --dev dbrekelmans/bdi

    $ vendor/bin/bdi detect drivers

Panther will detect and use automatically drivers stored in the ``drivers/`` directory
of your project when installing them manually. You can download `ChromeDriver`_
for Chromium or Chromeand `GeckoDriver`_ for Firefox and put them anywhere in
your ``PATH`` or in the ``drivers/`` directory of your project.

Alternatively, you can use the package manager of your operating system
to install them:

.. code-block:: terminal

    # Ubuntu
    $ apt-get install chromium-chromedriver firefox-geckodriver

    # MacOS, using Homebrew
    $ brew install chromedriver geckodriver

    # Windows, using Chocolatey
    $ choco install chromedriver selenium-gecko-driver

.. _panther_phpunit-extension:

Registering The PHPUnit Extension
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you intend to use Panther to test your application, it is strongly recommended
to register the Panther PHPUnit extension. While not strictly mandatory, this
extension dramatically improves the testing experience by boosting the performance
and allowing to use the :ref:`interactive debugging mode <panther_interactive-mode>`.

When using the extension in conjunction with the ``PANTHER_ERROR_SCREENSHOT_DIR``
environment variable, tests using the Panther client that fail or error (after the
client is created) will automatically get a screenshot taken to help debugging.

To register the Panther extension, add the following lines to ``phpunit.xml.dist``:

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <extensions>
        <extension class="Symfony\Component\Panther\ServerExtension"/>
    </extensions>

Without the extension, the web server used by Panther to serve the application
under test is started on demand and stopped when ``tearDownAfterClass()`` is called.
On the other hand, when the extension is registered, the web server will be stopped
only after the very last test.

Usage
-----

Here is an example of a snippet that uses Panther to test an application::

    use Symfony\Component\Panther\Client;

    $client = Client::createChromeClient();
    // alternatively, create a Firefox client
    $client = Client::createFirefoxClient();

    $client->request('GET', 'https://api-platform.com');
    $client->clickLink('Getting started');

    // wait for an element to be present in the DOM, even if hidden
    $crawler = $client->waitFor('#installing-the-framework');
    // you can also wait for an element to be visible
    $crawler = $client->waitForVisibility('#installing-the-framework');

    // get the text of an element thanks to the query selector syntax
    echo $crawler->filter('#installing-the-framework')->text();
    // take a screenshot of the current page
    $client->takeScreenshot('screen.png');

.. note::

    According to the specification, WebDriver implementations return only the
    **displayed** text by default. When you filter on a ``head`` tag (like
    ``title``), the method ``text()`` returns an empty string. Use the
    ``html()`` method to get the complete contents of the tag, including the
    tag itself.

Creating a TestCase
~~~~~~~~~~~~~~~~~~~

The ``PantherTestCase`` class allows you to write end-to-end tests. It
automatically starts your app using the built-in PHP web server and let
you crawl it using Panther. To provide all the testing tools you're used
to, it extends `PHPUnit`_'s ``TestCase``.

If you are testing a Symfony application, ``PantherTestCase`` automatically
extends the :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\WebTestCase` class.
It means you can create functional tests, which can directly execute the
kernel of your application and access all your existing services.
In this case, you can use
:ref:`all crawler test assertions <testing-application-assertions>`
provided by Symfony with Panther.

Here is an example of a ``PantherTestCase``::

    namespace App\Tests;

    use Symfony\Component\Panther\PantherTestCase;

    class HomepageTest extends PantherTestCase
    {
        public function testMyApp(): void
        {
            // your app is automatically started using the built-in web server
            $client = static::createPantherClient();
            $client->request('GET', '/home');

            // use any PHPUnit assertion, including the ones provided by Symfony...
            $this->assertPageTitleContains('My Title');
            $this->assertSelectorTextContains('#main', 'My body');

            // ... or the one provided by Panther
            $this->assertSelectorIsEnabled('.search');
            $this->assertSelectorIsDisabled('[type="submit"]');
            $this->assertSelectorIsVisible('.errors');
            $this->assertSelectorIsNotVisible('.loading');
            $this->assertSelectorAttributeContains('.price', 'data-old-price', '42');
            $this->assertSelectorAttributeNotContains('.price', 'data-old-price', '36');

            // ...
        }
    }

Panther client comes with methods that wait until some asynchronous process
finishes::

    namespace App\Tests;

    use Symfony\Component\Panther\PantherTestCase;

    class HomepageTest extends PantherTestCase
    {
        public function testMyApp(): void
        {
            // ...

            // wait for element to be attached to the DOM
            $client->waitFor('.popin');

            // wait for element to be removed from the DOM
            $client->waitForStaleness('.popin');

            // wait for element of the DOM to become visible
            $client->waitForVisibility('.loader');

            // wait for element of the DOM to become hidden
            $client->waitForInvisibility('.loader');

            // wait for text to be inserted in the element content
            $client->waitForElementToContain('.total', '25 €');

            // wait for text to be removed from the element content
            $client->waitForElementToNotContain('.promotion', '5%');

            // wait for the button to become enabled
            $client->waitForEnabled('[type="submit"]');

            // wait for  the button to become disabled
            $client->waitForDisabled('[type="submit"]');

            // wait for the attribute to contain content
            $client->waitForAttributeToContain('.price', 'data-old-price', '25 €');

            // wait for the attribute to not contain content
            $client->waitForAttributeToNotContain('.price', 'data-old-price', '25 €');
        }
    }

Finally, you can also make assertions on things that will happen in the
future::

    namespace App\Tests;

    use Symfony\Component\Panther\PantherTestCase;

    class HomepageTest extends PantherTestCase
    {
        public function testMyApp(): void
        {
            // ...

            // element will be attached to the DOM
            $this->assertSelectorWillExist('.popin');

            // element will be removed from the DOM
            $this->assertSelectorWillNotExist('.popin');

            // element will be visible
            $this->assertSelectorWillBeVisible('.loader');

            // element will not be visible
            $this->assertSelectorWillNotBeVisible('.loader');

            // text will be inserted in the element content
            $this->assertSelectorWillContain('.total', '€25');

            // text will be removed from the element content
            $this->assertSelectorWillNotContain('.promotion', '5%');

            // button will be enabled
            $this->assertSelectorWillBeEnabled('[type="submit"]');

            // button will be disabled
            $this->assertSelectorWillBeDisabled('[type="submit"]');

            // attribute will contain content
            $this->assertSelectorAttributeWillContain('.price', 'data-old-price', '€25');

            // attribute will not contain content
            $this->assertSelectorAttributeWillNotContain('.price', 'data-old-price', '€25');
        }
    }

You can then run this test by using PHPUnit, like you would do for any other
test:

.. code-block:: terminal

    $ ./vendor/bin/phpunit tests/HomepageTest.php

When writing end-to-end tests, you should keep in mind that they are
slower than other tests. If you need to check that the WebDriver connection
is still active during long-running tests, you can use the
``Client::ping()`` method which returns a boolean depending on the
connection status.

Advanced Usage
--------------

Changing The Hostname and the Port Of The Web Server
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to change the host and/or the port used by the built-in web server,
pass the ``hostname`` and ``port`` to the ``$options`` parameter of the
``createPantherClient()`` method::

    $client = self::createPantherClient([
        'hostname' => 'example.com', // defaults to 127.0.0.1
        'port' => 8080, // defaults to 9080
    ]);

Using Browser-Kit Clients
~~~~~~~~~~~~~~~~~~~~~~~~~

Panther also gives access to other BrowserKit-based implementations of
``Client`` and ``Crawler``. Unlike Panther's native client, these alternative
clients don't support JavaScript, CSS and screenshot capturing, but are way
faster. Two alternative clients are available:

* The first directly manipulates the Symfony kernel provided by
  ``WebTestCase``. It is the fastest client available, but it
  is only available for Symfony applications.
* The second leverages :class:`Symfony\\Component\\BrowserKit\\HttpBrowser`.
  It is an intermediate between Symfony's kernel and Panther's test clients.
  ``HttpBrowser`` sends real HTTP requests using the
  :doc:`HttpClient component </http_client>`. It is fast and is able to browse
  any webpage, not only the ones of the application under test.
  However, HttpBrowser doesn't support JavaScript and other advanced features
  because it is entirely written in PHP. This one can be used in any PHP
  application.

Because all clients implement the exact same API, you can switch from one to
another just by calling the appropriate factory method, resulting in a good
trade-off for every single test case: if JavaScript is needed or not, if an
authentication against an external SSO has to be done, etc.

Here is how to retrieve instances of these clients::

    namespace App\Tests;

    use Symfony\Component\Panther\Client;
    use Symfony\Component\Panther\PantherTestCase;

    class AppTest extends PantherTestCase
    {
        public function testMyApp(): void
        {
            // retrieve an existing client
            $symfonyClient = static::createClient();
            $httpBrowserClient = static::createHttpBrowserClient();
            $pantherClient = static::createPantherClient();
            $firefoxClient = static::createPantherClient(['browser' => static::FIREFOX]);

            // create a custom client
            $customChromeClient = Client::createChromeClient(null, null, [], 'https://example.com');
            $customFirefoxClient = Client::createFirefoxClient(null, null, [], 'https://example.com');
            $customSeleniumClient = Client::createSeleniumClient('http://127.0.0.1:4444/wd/hub', null, 'https://example.com');

            // if you are testing a Symfony app, you also have access to the kernel
            $kernel = static::createKernel();

            // ...
        }
    }

.. note::

    When initializing a custom client, the integrated web server **is not** started
    automatically. Use ``PantherTestCase::startWebServer()`` or the ``WebServerManager``
    class if you want to start it manually.

Testing Real-Time Applications
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Panther provides a convenient way to test applications with real-time
capabilities which use `Mercure`_, `WebSocket`_ and similar technologies.

The ``PantherTestCase::createAdditionalPantherClient()`` method can create
additional, isolated browsers which can interact with other ones. For instance,
this can be useful to test a chat application having several users
connected simultaneously::

    use Symfony\Component\Panther\PantherTestCase;

    class ChatTest extends PantherTestCase
    {
        public function testChat(): void
        {
            $client1 = self::createPantherClient();
            $client1->request('GET', '/chat');

            // connect a 2nd user using an isolated browser
            $client2 = self::createAdditionalPantherClient();
            $client2->request('GET', '/chat');
            $client2->submitForm('Post message', ['message' => 'Hi folks !']);

            // wait for the message to be received by the first client
            $client1->waitFor('.message');

            // Symfony Assertions are *always* executed in the primary browser
            $this->assertSelectorTextContains('.message', 'Hi folks !');
        }
    }

Accessing Browser Console Logs
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If needed, you can use Panther to access the content of the console::

    use Symfony\Component\Panther\PantherTestCase;

    class ConsoleTest extends PantherTestCase
    {
        public function testConsole(): void
        {
            $client = self::createPantherClient(
                [],
                [],
                [
                    'capabilities' => [
                        'goog:loggingPrefs' => [
                            'browser' => 'ALL', // calls to console.* methods
                            'performance' => 'ALL', // performance data
                        ],
                    ],
                ]
            );

            $client->request('GET', '/');

            $consoleLogs = $client->getWebDriver()->manage()->getLog('browser');
            $performanceLogs = $client->getWebDriver()->manage()->getLog('performance'); // performance logs
        }
    }

Passing Arguments to ChromeDriver
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If needed, you can configure `the arguments`_ to pass to the ``chromedriver`` binary::

    use Symfony\Component\Panther\PantherTestCase;

    class MyTest extends PantherTestCase
    {
        public function testLogging(): void
        {
            $client = self::createPantherClient(
                [],
                [],
                [
                    'chromedriver_arguments' => [
                        '--log-path=myfile.log',
                        '--log-level=DEBUG'
                    ],
                ]
            );

            $client->request('GET', '/');
        }
    }

Using a Proxy
~~~~~~~~~~~~~

To use a proxy server, you have to set the ``PANTHER_CHROME_ARGUMENTS``:

.. code-block:: bash

    # .env.test
    PANTHER_CHROME_ARGUMENTS='--proxy-server=socks://127.0.0.1:9050'

Accepting Self-Signed SSL Certificates
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To force Chrome to accept invalid and self-signed certificates, you can set the
following environment variable: ``PANTHER_CHROME_ARGUMENTS='--ignore-certificate-errors'``.

.. caution::

    This option is insecure, use it only for testing in development environments,
    never in production (e.g. for web crawlers).

For Firefox, instantiate the client like this, you can do this at client
creation::

    $client = Client::createFirefoxClient(null, null, ['capabilities' => ['acceptInsecureCerts' => true]]);

Using An External Web Server
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes, it's convenient to reuse an existing web server configuration
instead of starting the built-in PHP one. To do so, set the
``external_base_uri`` option when creating your client::

    namespace App\Tests;

    use Symfony\Component\Panther\PantherTestCase;

    class E2eTest extends PantherTestCase
    {
        public function testMyApp(): void
        {
            $pantherClient = static::createPantherClient(['external_base_uri' => 'https://localhost']);

            // ...
        }
    }

.. note::

    When using an external web server, Panther will not start the built-in
    PHP web server.

Having a Multi-domain Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It happens that your PHP/Symfony application might serve several different
domain names. As Panther saves the Client in memory between tests to improve
performance, you will have to run your tests in separate
processes if you write several tests using Panther for different domain names.

To do so, you can use the native ``@runInSeparateProcess`` PHPUnit annotation.
Here is an example using the ``external_base_uri`` option to determine the
domain name used by the Client when using separate processes::

    // tests/FirstDomainTest.php
    namespace App\Tests;

    use Symfony\Component\Panther\PantherTestCase;

    class FirstDomainTest extends PantherTestCase
    {
        /**
         * @runInSeparateProcess
         */
        public function testMyApp(): void
        {
            $pantherClient = static::createPantherClient([
                'external_base_uri' => 'http://mydomain.localhost:8000',
            ]);

            // ...
        }
    }

    // tests/SecondDomainTest.php
    namespace App\Tests;

    use Symfony\Component\Panther\PantherTestCase;

    class SecondDomainTest extends PantherTestCase
    {
        /**
         * @runInSeparateProcess
         */
        public function testMyApp(): void
        {
            $pantherClient = static::createPantherClient([
                'external_base_uri' => 'http://anotherdomain.localhost:8000',
            ]);

            // ...
        }
    }

Usage With Other Testing Tools
------------------------------

If you want to use Panther with other testing tools like `LiipFunctionalTestBundle`_
or if you just need to use a different base class, you can use the
``Symfony\Component\Panther\PantherTestCaseTrait`` to enhance your existing
test-infrastructure with some Panther mechanisms::

    namespace App\Tests\Controller;

    use Liip\FunctionalTestBundle\Test\WebTestCase;
    use Symfony\Component\Panther\PantherTestCaseTrait;

    class DefaultControllerTest extends WebTestCase
    {
        use PantherTestCaseTrait;

        public function testWithFixtures(): void
        {
            $this->loadFixtures([]); // load your fixtures
            $client = self::createPantherClient(); // create your panther client

            $client->request('GET', '/');

            // ...
        }
    }

Configuring Panther Through Environment Variables
-------------------------------------------------

The following environment variables can be set to change some Panther's
behavior:

``PANTHER_NO_HEADLESS``
    Disable the browser's headless mode (will display the testing window, useful to debug)
``PANTHER_WEB_SERVER_DIR``
    Change the project's document root (default to ``./public/``, relative paths **must start** by ``./``)
``PANTHER_WEB_SERVER_PORT``
    Change the web server's port (default to ``9080``)
``PANTHER_WEB_SERVER_ROUTER``
    Use a web server router script which is run at the start of each HTTP request
``PANTHER_EXTERNAL_BASE_URI``
    Use an external web server (the PHP built-in web server will not be started)
``PANTHER_APP_ENV``
    Override the ``APP_ENV`` variable passed to the web server running the PHP app
``PANTHER_ERROR_SCREENSHOT_DIR``
    Set a base directory for your failure/error screenshots (e.g. ``./var/error-screenshots``)
``PANTHER_DEVTOOLS``
    Toggle the browser's dev tools (default ``enabled``, useful to debug)
``PANTHER_ERROR_SCREENSHOT_ATTACH``
    Add screenshots mentioned above to test output in junit attachment format

Chrome Specific Environment Variables
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

``PANTHER_NO_SANDBOX``
    Disable `Chrome's sandboxing`_ (unsafe, but allows to use Panther in containers)
``PANTHER_CHROME_ARGUMENTS``
    Customize Chrome arguments. You need to set ``PANTHER_NO_HEADLESS`` to fully customize
``PANTHER_CHROME_BINARY``
    To use another ``google-chrome`` binary

Firefox Specific Environment Variables
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

``PANTHER_FIREFOX_ARGUMENTS``
    Customize Firefox arguments. You need to set ``PANTHER_NO_HEADLESS`` to fully customize
``PANTHER_FIREFOX_BINARY``
    To use another ``firefox`` binary

.. _panther_interactive-mode:

Interactive Mode
----------------

Panther can make a pause in your tests suites after a failure.
Thanks to this break time, you can investigate the encountered problem through
the web browser. To enable this mode, you need the ``--debug`` PHPUnit option
without the headless mode:

.. code-block:: terminal

    $ PANTHER_NO_HEADLESS=1 bin/phpunit --debug

    Test 'App\AdminTest::testLogin' started
    Error: something is wrong.

    Press enter to continue...

To use the interactive mode, the
:ref:`PHPUnit extension <panther_phpunit-extension>` has to be registered.

Docker Integration
------------------

Here is a minimal Docker image that can run Panther with both Chrome and
Firefox:

.. code-block:: dockerfile

    FROM php:alpine

    # Chromium and ChromeDriver
    ENV PANTHER_NO_SANDBOX 1
    # Not mandatory, but recommended
    ENV PANTHER_CHROME_ARGUMENTS='--disable-dev-shm-usage'
    RUN apk add --no-cache chromium chromium-chromedriver

    # Firefox and GeckoDriver (optional)
    ARG GECKODRIVER_VERSION=0.28.0
    RUN apk add --no-cache firefox libzip-dev; \
        docker-php-ext-install zip
    RUN wget -q https://github.com/mozilla/geckodriver/releases/download/v$GECKODRIVER_VERSION/geckodriver-v$GECKODRIVER_VERSION-linux64.tar.gz; \
        tar -zxf geckodriver-v$GECKODRIVER_VERSION-linux64.tar.gz -C /usr/bin; \
        rm geckodriver-v$GECKODRIVER_VERSION-linux64.tar.gz

You can then build and run your image:

.. code-block:: bash

    $ docker build . -t myproject
    $ docker run -it -v "$PWD":/srv/myproject -w /srv/myproject myproject bin/phpunit

Integrating Panther In Your CI
------------------------------

Github Actions
~~~~~~~~~~~~~~

Panther works out of the box with `GitHub Actions`_.
Here is a minimal ``.github/workflows/panther.yaml`` file to run Panther tests:

.. code-block:: yaml

    name: Run Panther tests

    on: [ push, pull_request ]

    jobs:
      tests:

        runs-on: ubuntu-latest

        steps:
          - uses: actions/checkout@v4
          - uses: "ramsey/composer-install@v2"

          - name: Install dependencies
            run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist

          - name: Run test suite
            run: bin/phpunit

Travis CI
~~~~~~~~~

Panther will work out of the box with `Travis CI`_ if you add the Chrome addon.
Here is a minimal ``.travis.yaml`` file to run Panther tests:

.. code-block:: yaml

    language: php
    addons:
      # If you don't use Chrome, or Firefox, remove the corresponding line
      chrome: stable
      firefox: latest

    php:
      - 8.0

    script:
      - bin/phpunit

Gitlab CI
~~~~~~~~~

Here is a minimal ``.gitlab-ci.yaml`` file to run Panther tests
with `Gitlab CI`_:

.. code-block:: yaml

    image: ubuntu

    before_script:
      - apt-get update
      - apt-get install software-properties-common -y
      - ln -sf /usr/share/zoneinfo/Europe/Paris /etc/localtime
      - apt-get install curl wget php php-cli php8.1 php8.1-common php8.1-curl php8.1-intl php8.1-xml php8.1-opcache php8.1-mbstring php8.1-zip libfontconfig1 fontconfig libxrender-dev libfreetype6 libxrender1 zlib1g-dev xvfb chromium-chromedriver firefox-geckodriver -y -qq
      - export PANTHER_NO_SANDBOX=1
      - export PANTHER_WEB_SERVER_PORT=9080
      - php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
      - php composer-setup.php --install-dir=/usr/local/bin --filename=composer
      - php -r "unlink('composer-setup.php');"
      - composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist

    test:
      script:
        - bin/phpunit

AppVeyor
~~~~~~~~

Panther will work out of the box with `AppVeyor`_ as long as Google Chrome
is installed. Here is a minimal ``appveyor.yaml`` file to run Panther tests:

.. code-block:: yaml

    build: false
    platform: x86
    clone_folder: c:\projects\myproject

    cache:
      - '%LOCALAPPDATA%\Composer\files'

    install:
      - ps: Set-Service wuauserv -StartupType Manual
      - cinst -y php composer googlechrome chromedriver firfox selenium-gecko-driver
      - refreshenv
      - cd c:\tools\php80
      - copy php.ini-production php.ini /Y
      - echo date.timezone="UTC" >> php.ini
      - echo extension_dir=ext >> php.ini
      - echo extension=php_openssl.dll >> php.ini
      - echo extension=php_mbstring.dll >> php.ini
      - echo extension=php_curl.dll >> php.ini
      - echo memory_limit=3G >> php.ini
      - cd %APPVEYOR_BUILD_FOLDER%
      - composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist

    test_script:
      - cd %APPVEYOR_BUILD_FOLDER%
      - php bin\phpunit

Known Limitations and Troubleshooting
-------------------------------------

The following features are not currently supported:

* Crawling XML documents (only HTML is supported)
* Updating existing documents (browsers are mostly used to consume data, not to create webpages)
* Setting form values using the multidimensional PHP array syntax
* Methods returning an instance of ``\DOMElement`` (because this library uses ``WebDriverElement`` internally)
* Selecting invalid choices in select

Also, there is a known issue if you are using Bootstrap 5. It implements a
scrolling effect, which tends to mislead Panther. To fix this, we advise you to
deactivate this effect by setting the Bootstrap 5 ``$enable-smooth-scroll``
variable to ``false`` in your style file:

.. code-block:: scss

    $enable-smooth-scroll: false;

Additional Documentation
------------------------

Since Panther implements the API of popular libraries, you can find even more
documentation:

* For the ``Client`` class, by reading the
  :doc:`BrowserKit component </components/browser_kit>` page
* For the ``Crawler`` class, by reading the
  :doc:`DomCrawler component </components/dom_crawler>` page
* For WebDriver, by reading the `PHP WebDriver documentation`_

.. _`dbrekelmans/browser-driver-installer`: https://github.com/dbrekelmans/browser-driver-installer
.. _`ChromeDriver`: https://sites.google.com/chromium.org/driver/
.. _`GeckoDriver`: https://github.com/mozilla/geckodriver
.. _`PHPUnit`: https://phpunit.de/
.. _`Mercure`: https://mercure.rocks/
.. _`WebSocket`: https://developer.mozilla.org/en-US/docs/Web/API/WebSockets_API
.. _`the arguments`: https://chromedriver.chromium.org/logging#TOC-All-languages
.. _`PHP WebDriver documentation`: https://github.com/php-webdriver/php-webdriver
.. _`Chrome's sandboxing`: https://chromium.googlesource.com/chromium/src/+/b4730a0c2773d8f6728946013eb812c6d3975bec/docs/design/sandbox.md
.. _`GitHub Actions`: https://help.github.com/en/actions
.. _`Travis CI`: https://travis-ci.com/
.. _`Gitlab CI`: https://docs.gitlab.com/ee/ci/
.. _`AppVeyor`: https://www.appveyor.com/
.. _`LiipFunctionalTestBundle`: https://github.com/liip/LiipFunctionalTestBundle
