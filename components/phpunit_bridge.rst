The PHPUnit Bridge
==================

    The PHPUnit Bridge provides utilities to report legacy tests and usage of
    deprecated code and helpers for mocking native functions related to time,
    DNS and class existence.

It comes with the following features:

* Sets by default a consistent locale (``C``) for your tests (if you
  create locale-sensitive tests, use PHPUnit's ``setLocale()`` method);

* Auto-register ``class_exists`` to load Doctrine annotations (when used);

* It displays the whole list of deprecated features used in the application;

* Displays the stack trace of a deprecation on-demand;

* Provides a ``ClockMock``, ``DnsMock`` and ``ClassExistsMock`` classes for tests
  sensitive to time, network or class existence;

* Provides a modified version of PHPUnit that allows:

  #. separating the dependencies of your app from those of phpunit to prevent any unwanted constraints to apply;
  #. running tests in parallel when a test suite is split in several phpunit.xml files;
  #. recording and replaying skipped tests;

* It allows to create tests that are compatible with multiple PHPUnit versions
  (because it provides polyfills for missing methods, namespaced aliases for
  non-namespaced classes, etc.).

Installation
------------

.. code-block:: terminal

    $ composer require --dev symfony/phpunit-bridge

.. include:: /components/require_autoload.rst.inc

.. note::

    The PHPUnit bridge is designed to work with all maintained versions of
    Symfony components, even across different major versions of them. You should
    always use its very latest stable major version to get the most accurate
    deprecation report.

If you plan to :ref:`write assertions about deprecations <write-assertions-about-deprecations>` and use the regular
PHPUnit script (not the modified PHPUnit script provided by Symfony), you have
to register a new `test listener`_ called ``SymfonyTestsListener``:

.. code-block:: xml

    <!-- https://phpunit.de/manual/6.0/en/appendixes.configuration.html -->
    <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/6.0/phpunit.xsd"
    >

        <!-- ... -->

        <listeners>
            <listener class="Symfony\Bridge\PhpUnit\SymfonyTestsListener"/>
        </listeners>
    </phpunit>

Usage
-----

.. seealso::

    This article explains how to use the PhpUnitBridge features as an independent
    component in any PHP application. Read the :doc:`/testing` article to learn
    about how to use it in Symfony applications.

Once the component is installed, a ``simple-phpunit`` script is created in the
``vendor/`` directory to run tests. This script wraps the original PHPUnit binary
to provide more features:

.. code-block:: terminal

    $ cd my-project/
    $ ./vendor/bin/simple-phpunit

After running your PHPUnit tests, you will get a report similar to this one:

.. code-block:: terminal

    $ ./vendor/bin/simple-phpunit
      PHPUnit by Sebastian Bergmann.

      Configuration read from <your-project>/phpunit.xml.dist
      .................

      Time: 1.77 seconds, Memory: 5.75Mb

      OK (17 tests, 21 assertions)

      Remaining deprecation notices (2)

      getEntityManager is deprecated since Symfony 2.1. Use getManager instead: 2x
        1x in DefaultControllerTest::testPublicUrls from App\Tests\Controller
        1x in BlogControllerTest::testIndex from App\Tests\Controller

The summary includes:

**Unsilenced**
    Reports deprecation notices that were triggered without the recommended
    `@-silencing operator`_.

**Legacy**
    Deprecation notices denote tests that explicitly test some legacy features.

**Remaining/Other**
    Deprecation notices are all other (non-legacy) notices, grouped by message,
    test class and method.

.. note::

    If you don't want to use the ``simple-phpunit`` script, register the following
    `PHPUnit event listener`_ in your PHPUnit configuration file to get the same
    report about deprecations (which is created by a `PHP error handler`_
    called :class:`Symfony\\Bridge\\PhpUnit\\DeprecationErrorHandler`):

    .. code-block:: xml

        <!-- phpunit.xml.dist -->
        <!-- ... -->
        <listeners>
            <listener class="Symfony\Bridge\PhpUnit\SymfonyTestsListener"/>
        </listeners>

Running Tests in Parallel
-------------------------

The modified PHPUnit script allows running tests in parallel by providing
a directory containing multiple test suites with their own ``phpunit.xml.dist``.

.. code-block:: terminal

    ├── tests/
    │   ├── Functional/
    │   │   ├── ...
    │   │   └── phpunit.xml.dist
    │   ├── Unit/
    │   │   ├── ...
    │   │   └── phpunit.xml.dist

.. code-block:: terminal

    $ ./vendor/bin/simple-phpunit tests/

The modified PHPUnit script will recursively go through the provided directory,
up to a depth of 3 subdirectories or the value specified by the environment variable
``SYMFONY_PHPUNIT_MAX_DEPTH``, looking for ``phpunit.xml.dist`` files and then
running each suite it finds in parallel, collecting their output and displaying
each test suite's results in their own section.

Trigger Deprecation Notices
---------------------------

Deprecation notices can be triggered by using ``trigger_deprecation`` from
the ``symfony/deprecation-contracts`` package::

    // indicates something is deprecated since version 1.3 of vendor-name/packagename
    trigger_deprecation('vendor-name/package-name', '1.3', 'Your deprecation message');

    // you can also use printf format (all arguments after the message will be used)
    trigger_deprecation('...', '1.3', 'Value "%s" is deprecated, use ...  instead.', $value);

Mark Tests as Legacy
--------------------

There are three ways to mark a test as legacy:

* (**Recommended**) Add the ``@group legacy`` annotation to its class or method;

* Make its class name start with the ``Legacy`` prefix;

* Make its method name start with ``testLegacy*()`` instead of ``test*()``.

.. note::

    If your data provider calls code that would usually trigger a deprecation,
    you can prefix its name with ``provideLegacy`` or ``getLegacy`` to silence
    these deprecations. If your data provider does not execute deprecated
    code, it is not required to choose a special naming just because the
    test being fed by the data provider is marked as legacy.

    Also be aware that choosing one of the two legacy prefixes will not mark
    tests as legacy that make use of this data provider. You still have to
    mark them as legacy tests explicitly.

Configuration
-------------

In case you need to inspect the stack trace of a particular deprecation
triggered by your unit tests, you can set the ``SYMFONY_DEPRECATIONS_HELPER``
`environment variable`_ to a regular expression that matches this deprecation's
message, enclosed with ``/``. For example, with:

.. code-block:: xml

    <!-- https://phpunit.de/manual/6.0/en/appendixes.configuration.html -->
    <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/6.0/phpunit.xsd"
    >

        <!-- ... -->

        <php>
            <server name="KERNEL_CLASS" value="App\Kernel"/>
            <env name="SYMFONY_DEPRECATIONS_HELPER" value="/foobar/"/>
        </php>
    </phpunit>

`PHPUnit`_ will stop your test suite once a deprecation notice is triggered whose
message contains the ``"foobar"`` string.

.. _making-tests-fail:

Making Tests Fail
~~~~~~~~~~~~~~~~~

By default, any non-legacy-tagged or any non-silenced (`@-silencing operator`_)
deprecation notices will make tests fail. Alternatively, you can configure
an arbitrary threshold by setting ``SYMFONY_DEPRECATIONS_HELPER`` to
``max[total]=320`` for instance. It will make the tests fail only if a
higher number of deprecation notices is reached (``0`` is the default
value).

You can have even finer-grained control by using other keys of the ``max``
array, which are ``self``, ``direct``, and ``indirect``. The
``SYMFONY_DEPRECATIONS_HELPER`` environment variable accepts a URL-encoded
string, meaning you can combine thresholds and any other configuration setting,
like this: ``SYMFONY_DEPRECATIONS_HELPER='max[total]=42&max[self]=0&verbose=0'``

Internal deprecations
.....................

When you maintain a library, having the test suite fail as soon as a dependency
introduces a new deprecation is not desirable, because it shifts the burden of
fixing that deprecation to any contributor that happens to submit a pull request
shortly after a new vendor release is made with that deprecation.

To mitigate this, you can either use tighter requirements, in the hope that
dependencies will not introduce deprecations in a patch version, or even commit
the ``composer.lock`` file, which would create another class of issues.
Libraries will often use ``SYMFONY_DEPRECATIONS_HELPER=max[total]=999999``
because of this. This has the drawback of allowing contributions that introduce
deprecations but:

* forget to fix the deprecated calls if there are any;
* forget to mark appropriate tests with the ``@group legacy`` annotations.

By using ``SYMFONY_DEPRECATIONS_HELPER=max[self]=0``, deprecations that are
triggered outside the ``vendors`` directory will be accounted for separately,
while deprecations triggered from a library inside it will not (unless you reach
999999 of these), giving you the best of both worlds.

Direct and Indirect Deprecations
................................

When working on a project, you might be more interested in ``max[direct]``.
Let's say you want to fix deprecations as soon as they appear. A problem many
developers experience is that some dependencies they have tend to lag behind
their own dependencies, meaning they do not fix deprecations as soon as
possible, which means you should create a pull request on the outdated vendor,
and ignore these deprecations until your pull request is merged.

The ``max[direct]`` config allows you to put a threshold on direct deprecations
only, allowing you to notice when *your code* is using deprecated APIs, and to
keep up with the changes. You can still use ``max[indirect]`` if you want to
keep indirect deprecations under a given threshold.

Here is a summary that should help you pick the right configuration:

+------------------------+-----------------------------------------------------+
| Value                  | Recommended situation                               |
+========================+=====================================================+
| max[total]=0           | Recommended for actively maintained projects        |
|                        | with robust/no dependencies                         |
+------------------------+-----------------------------------------------------+
| max[direct]=0          | Recommended for projects with dependencies          |
|                        | that fail to keep up with new deprecations.         |
+------------------------+-----------------------------------------------------+
| max[self]=0            | Recommended for libraries that use                  |
|                        | the deprecation system themselves and               |
|                        | cannot afford to use one of the modes above.        |
+------------------------+-----------------------------------------------------+

Ignoring Deprecations
.....................

If your application has some deprecations that you can't fix for some reasons,
you can tell Symfony to ignore them.

You need first to create a text file where each line is a deprecation to ignore
defined as a regular expression. Lines beginning with a hash (``#``) are
considered comments:

.. code-block:: terminal

    # This file contains patterns to be ignored while testing for use of
    # deprecated code.

    %The "Symfony\\Component\\Validator\\Context\\ExecutionContextInterface::.*\(\)" method is considered internal Used by the validator engine\. (Should not be called by user\W+code\. )?It may change without further notice\. You should not extend it from "[^"]+"\.%
    %The "PHPUnit\\Framework\\TestCase::addWarning\(\)" method is considered internal%

Then, you can run the following command to use that file and ignore those deprecations:

.. code-block:: terminal

    $ SYMFONY_DEPRECATIONS_HELPER='ignoreFile=./tests/baseline-ignore' ./vendor/bin/simple-phpunit

Baseline Deprecations
.....................

You can also take a snapshot of deprecations currently triggered by your application
code, and ignore those during your test runs, still reporting newly added ones.
The trick is to create a file with the allowed deprecations and define it as the
"deprecation baseline". Deprecations inside that file are ignored but the rest of
deprecations are still reported.

First, generate the file with the allowed deprecations (run the same command
whenever you want to update the existing file):

.. code-block:: terminal

    $ SYMFONY_DEPRECATIONS_HELPER='generateBaseline=true&baselineFile=./tests/allowed.json' ./vendor/bin/simple-phpunit

This command stores all the deprecations reported while running tests in the
given file path and encoded in JSON.

Then, you can run the following command to use that file and ignore those deprecations:

.. code-block:: terminal

    $ SYMFONY_DEPRECATIONS_HELPER='baselineFile=./tests/allowed.json' ./vendor/bin/simple-phpunit

Disabling the Verbose Output
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, the bridge will display a detailed output with the number of
deprecations and where they arise. If this is too much for you, you can use
``SYMFONY_DEPRECATIONS_HELPER=verbose=0`` to turn the verbose output off.

It's also possible to change verbosity per deprecation type. For example, using
``quiet[]=indirect&quiet[]=other`` will hide details for deprecations of types
"indirect" and "other".

The ``quiet`` option hides details for the specified deprecation types, but will
not change the outcome in terms of exit code. That's what :ref:`max <making-tests-fail>`
is for, and both settings are orthogonal.

Disabling the Deprecation Helper
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Set the ``SYMFONY_DEPRECATIONS_HELPER`` environment variable to ``disabled=1``
to completely disable the deprecation helper. This is useful to make use of the
rest of features provided by this component without getting errors or messages
related to deprecations.

Deprecation Notices at Autoloading Time
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, the PHPUnit Bridge uses ``DebugClassLoader`` from the
`ErrorHandler component`_ to throw deprecation notices at class autoloading
time. This can be disabled with the ``debug-class-loader`` option.

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <!-- ... -->
    <listeners>
        <listener class="Symfony\Bridge\PhpUnit\SymfonyTestsListener">
            <arguments>
                <array>
                    <!-- set this option to 0 to disable the DebugClassLoader integration -->
                    <element key="debug-class-loader"><integer>0</integer></element>
                </array>
            </arguments>
        </listener>
    </listeners>

Compile-time Deprecations
~~~~~~~~~~~~~~~~~~~~~~~~~

Use the ``debug:container`` command to list the deprecations generated during
the compiling and warming up of the container:

.. code-block:: terminal

    $ php bin/console debug:container --deprecations

Log Deprecations
~~~~~~~~~~~~~~~~

For turning the verbose output off and write it to a log file instead you can use
``SYMFONY_DEPRECATIONS_HELPER='logFile=/path/deprecations.log'``.

Setting The Locale For Tests
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, the PHPUnit Bridge forces the locale to ``C`` to avoid locale
issues in tests. This behavior can be changed by setting the
``SYMFONY_PHPUNIT_LOCALE`` environment variable to the desired locale:

.. code-block:: bash

    # .env.test
    SYMFONY_PHPUNIT_LOCALE="fr_FR"

Alternatively, you can set this environment variable in the PHPUnit
configuration file:

.. code-block:: xml

    <!-- https://phpunit.de/manual/6.0/en/appendixes.configuration.html -->
    <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/6.0/phpunit.xsd"
    >

        <!-- ... -->

        <php>
            <!-- ... -->
            <env name="SYMFONY_PHPUNIT_LOCALE" value="fr_FR"/>
        </php>
    </phpunit>

.. versionadded:: 6.4

    The support for the ``SYMFONY_PHPUNIT_LOCALE`` environment variable was
    introduced in Symfony 6.4.

.. _write-assertions-about-deprecations:

Write Assertions about Deprecations
-----------------------------------

When adding deprecations to your code, you might like writing tests that verify
that they are triggered as required. To do so, the bridge provides the
``expectDeprecation()`` method that you can use on your test methods.
It requires you to pass the expected message, given in the same format as for
the `PHPUnit's assertStringMatchesFormat()`_ method. If you expect more than one
deprecation message for a given test method, you can use the method several
times (order matters)::

    use PHPUnit\Framework\TestCase;
    use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

    class MyTest extends TestCase
    {
        use ExpectDeprecationTrait;

        /**
         * @group legacy
         */
        public function testDeprecatedCode(): void
        {
            // test some code that triggers the following deprecation:
            // trigger_deprecation('vendor-name/package-name', '5.1', 'This "Foo" method is deprecated.');
            $this->expectDeprecation('Since vendor-name/package-name 5.1: This "%s" method is deprecated');

            // ...

            // test some code that triggers the following deprecation:
            // trigger_deprecation('vendor-name/package-name', '4.4', 'The second argument of the "Bar" method is deprecated.');
            $this->expectDeprecation('Since vendor-name/package-name 4.4: The second argument of the "%s" method is deprecated.');
        }
    }

Display the Full Stack Trace
----------------------------

By default, the PHPUnit Bridge displays only deprecation messages.
To show the full stack trace related to a deprecation, set the value of ``SYMFONY_DEPRECATIONS_HELPER``
to a regular expression matching the deprecation message.

For example, if the following deprecation notice is thrown:

.. code-block:: bash

    1x: Doctrine\Common\ClassLoader is deprecated.
      1x in EntityTypeTest::setUp from Symfony\Bridge\Doctrine\Tests\Form\Type

Running the following command will display the full stack trace:

.. code-block:: terminal

    $ SYMFONY_DEPRECATIONS_HELPER='/Doctrine\\Common\\ClassLoader is deprecated\./' ./vendor/bin/simple-phpunit

Testing with Multiple PHPUnit Versions
--------------------------------------

When testing a library that has to be compatible with several versions of PHP,
the test suite cannot use the latest versions of PHPUnit because:

* PHPUnit 8 deprecated several methods in favor of other methods which are not
  available in older versions (e.g. PHPUnit 4);
* PHPUnit 8 added the ``void`` return type to the ``setUp()`` method, which is
  not compatible with PHP 5.5;
* PHPUnit switched to namespaced classes starting from PHPUnit 6, so tests must
  work with and without namespaces.

Polyfills for the Unavailable Methods
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using the ``simple-phpunit`` script, PHPUnit Bridge injects polyfills for
most methods of the ``TestCase`` and ``Assert`` classes (e.g. ``expectException()``,
``expectExceptionMessage()``, ``assertContainsEquals()``, etc.). This allows writing
test cases using the latest best practices while still remaining compatible with
older PHPUnit versions.

Removing the Void Return Type
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When running the ``simple-phpunit`` script with the ``SYMFONY_PHPUNIT_REMOVE_RETURN_TYPEHINT``
environment variable set to ``1``, the PHPUnit bridge will alter the code of
PHPUnit to remove the return type (introduced in PHPUnit 8) from ``setUp()``,
``tearDown()``, ``setUpBeforeClass()`` and ``tearDownAfterClass()`` methods.
This allows you to write a test compatible with both PHP 5 and PHPUnit 8.

Using Namespaced PHPUnit Classes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The PHPUnit bridge adds namespaced class aliases for most of the PHPUnit classes
declared without namespaces (e.g. ``PHPUnit_Framework_Assert``), allowing you to
always use the namespaced class declaration even when the test is executed with
PHPUnit 4.

Time-sensitive Tests
--------------------

Use Case
~~~~~~~~

If you have this kind of time-related tests::

    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Stopwatch\Stopwatch;

    class MyTest extends TestCase
    {
        public function testSomething(): void
        {
            $stopwatch = new Stopwatch();

            $stopwatch->start('event_name');
            sleep(10);
            $duration = $stopwatch->stop('event_name')->getDuration();

            $this->assertEquals(10000, $duration);
        }
    }

You calculated the duration time of your process using the Stopwatch utilities to
:ref:`profile Symfony applications <profiling-applications>`. However, depending
on the load of the server or the processes running on your local machine, the
``$duration`` could for example be ``10.000023s`` instead of ``10s``.

This kind of tests are called transient tests: they are failing randomly
depending on spurious and external circumstances. They are often cause trouble
when using public continuous integration services like `Travis CI`_.

Clock Mocking
~~~~~~~~~~~~~

The :class:`Symfony\\Bridge\\PhpUnit\\ClockMock` class provided by this bridge
allows you to mock the PHP's built-in time functions ``time()``, ``microtime()``,
``sleep()``, ``usleep()``, ``gmdate()``, and ``hrtime()``. Additionally the
function ``date()`` is mocked so it uses the mocked time if no timestamp is
specified.

Other functions with an optional timestamp parameter that defaults to ``time()``
will still use the system time instead of the mocked time. This means that you
may need to change some code in your tests. For example, instead of ``new DateTime()``,
you should use ``DateTime::createFromFormat('U', (string) time())`` to use the mocked
``time()`` function.

To use the ``ClockMock`` class in your test, add the ``@group time-sensitive``
annotation to its class or methods. This annotation only works when executing
PHPUnit using the ``vendor/bin/simple-phpunit`` script or when registering the
following listener in your PHPUnit configuration:

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <!-- ... -->
    <listeners>
        <listener class="\Symfony\Bridge\PhpUnit\SymfonyTestsListener"/>
    </listeners>

.. note::

    If you don't want to use the ``@group time-sensitive`` annotation, you can
    register the ``ClockMock`` class manually by calling
    ``ClockMock::register(__CLASS__)`` and ``ClockMock::withClockMock(true)``
    before the test and ``ClockMock::withClockMock(false)`` after the test.

As a result, the following is guaranteed to work and is no longer a transient
test::

    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Stopwatch\Stopwatch;

    /**
     * @group time-sensitive
     */
    class MyTest extends TestCase
    {
        public function testSomething(): void
        {
            $stopwatch = new Stopwatch();

            $stopwatch->start('event_name');
            sleep(10);
            $duration = $stopwatch->stop('event_name')->getDuration();

            $this->assertEquals(10000, $duration);
        }
    }

And that's all!

.. caution::

    Time-based function mocking follows the `PHP namespace resolutions rules`_
    so "fully qualified function calls" (e.g ``\time()``) cannot be mocked.

The ``@group time-sensitive`` annotation is equivalent to calling
``ClockMock::register(MyTest::class)``. If you want to mock a function used in a
different class, do it explicitly using ``ClockMock::register(MyClass::class)``::

    // the class that uses the time() function to be mocked
    namespace App;

    class MyClass
    {
        public function getTimeInHours(): void
        {
            return time() / 3600;
        }
    }

    // the test that mocks the external time() function explicitly
    namespace App\Tests;

    use App\MyClass;
    use PHPUnit\Framework\TestCase;
    use Symfony\Bridge\PhpUnit\ClockMock;

    /**
     * @group time-sensitive
     */
    class MyTest extends TestCase
    {
        public function testGetTimeInHours(): void
        {
            ClockMock::register(MyClass::class);

            $my = new MyClass();
            $result = $my->getTimeInHours();

            $this->assertEquals(time() / 3600, $result);
        }
    }

.. tip::

    An added bonus of using the ``ClockMock`` class is that time passes
    instantly. Using PHP's ``sleep(10)`` will make your test wait for 10
    actual seconds (more or less). In contrast, the ``ClockMock`` class
    advances the internal clock the given number of seconds without actually
    waiting that time, so your test will execute 10 seconds faster.

DNS-sensitive Tests
-------------------

Tests that make network connections, for example to check the validity of a DNS
record, can be slow to execute and unreliable due to the conditions of the
network. For that reason, this component also provides mocks for these PHP
functions:

* :phpfunction:`checkdnsrr`
* :phpfunction:`dns_check_record`
* :phpfunction:`getmxrr`
* :phpfunction:`dns_get_mx`
* :phpfunction:`gethostbyaddr`
* :phpfunction:`gethostbyname`
* :phpfunction:`gethostbynamel`
* :phpfunction:`dns_get_record`

Use Case
~~~~~~~~

Consider the following example that tests a custom class called ``DomainValidator``
which defines a ``checkDnsRecord`` option to also validate that a domain is
associated to a valid host::

    use App\Validator\DomainValidator;
    use PHPUnit\Framework\TestCase;

    class MyTest extends TestCase
    {
        public function testEmail(): void
        {
            $validator = new DomainValidator(['checkDnsRecord' => true]);
            $isValid = $validator->validate('example.com');

            // ...
        }
    }

In order to avoid making a real network connection, add the ``@group dns-sensitive``
annotation to the class and use the ``DnsMock::withMockedHosts()`` to configure
the data you expect to get for the given hosts::

    use App\Validator\DomainValidator;
    use PHPUnit\Framework\TestCase;
    use Symfony\Bridge\PhpUnit\DnsMock;

    /**
     * @group dns-sensitive
     */
    class DomainValidatorTest extends TestCase
    {
        public function testEmails(): void
        {
            DnsMock::withMockedHosts([
                'example.com' => [['type' => 'A', 'ip' => '1.2.3.4']],
            ]);

            $validator = new DomainValidator(['checkDnsRecord' => true]);
            $isValid = $validator->validate('example.com');

            // ...
        }
    }

The ``withMockedHosts()`` method configuration is defined as an array. The keys
are the mocked hosts and the values are arrays of DNS records in the same format
returned by :phpfunction:`dns_get_record`, so you can simulate diverse network
conditions::

    DnsMock::withMockedHosts([
        'example.com' => [
            [
                'type' => 'A',
                'ip' => '1.2.3.4',
            ],
            [
                'type' => 'AAAA',
                'ipv6' => '::12',
            ],
        ],
    ]);

Class Existence Based Tests
---------------------------

Tests that behave differently depending on existing classes, for example Composer's
development dependencies, are often hard to test for the alternate case. For that
reason, this component also provides mocks for these PHP functions:

* :phpfunction:`class_exists`
* :phpfunction:`interface_exists`
* :phpfunction:`trait_exists`
* :phpfunction:`enum_exists`

Use Case
~~~~~~~~

Consider the following example that relies on the ``Vendor\DependencyClass`` to
toggle a behavior::

    use Vendor\DependencyClass;

    class MyClass
    {
        public function hello(): string
        {
            if (class_exists(DependencyClass::class)) {
                return 'The dependency behavior.';
            }

            return 'The default behavior.';
        }
    }

A regular test case for ``MyClass`` (assuming the development dependencies
are installed during tests) would look like::

    use MyClass;
    use PHPUnit\Framework\TestCase;

    class MyClassTest extends TestCase
    {
        public function testHello(): void
        {
            $class = new MyClass();
            $result = $class->hello(); // "The dependency behavior."

            // ...
        }
    }

In order to test the default behavior instead use the
``ClassExistsMock::withMockedClasses()`` to configure the expected
classes, interfaces and/or traits for the code to run::

    use MyClass;
    use PHPUnit\Framework\TestCase;
    use Vendor\DependencyClass;

    class MyClassTest extends TestCase
    {
        // ...

        public function testHelloDefault(): void
        {
            ClassExistsMock::register(MyClass::class);
            ClassExistsMock::withMockedClasses([DependencyClass::class => false]);

            $class = new MyClass();
            $result = $class->hello(); // "The default behavior."

            // ...
        }
    }

Note that mocking a class with ``ClassExistsMock::withMockedClasses()``
will make :phpfunction:`class_exists`, :phpfunction:`interface_exists`
and :phpfunction:`trait_exists` return true.

To register an enumeration and mock :phpfunction:`enum_exists`,
``ClassExistsMock::withMockedEnums()`` must be used. Note that, like in
PHP 8.1 and later, calling ``class_exists`` on a enum will return ``true``.
That's why calling ``ClassExistsMock::withMockedEnums()`` will also register the enum
as a mocked class.

Troubleshooting
---------------

The ``@group time-sensitive`` and ``@group dns-sensitive`` annotations work
"by convention" and assume that the namespace of the tested class can be
obtained just by removing the ``Tests\`` part from the test namespace. I.e.
if your test cases fully-qualified class name (FQCN) is
``App\Tests\Watch\DummyWatchTest``, it assumes the tested class namespace
is ``App\Watch``.

If this convention doesn't work for your application, configure the mocked
namespaces in the ``phpunit.xml`` file, as done for example in the
:doc:`HttpKernel Component </components/http_kernel>`:

.. code-block:: xml

    <!-- https://phpunit.de/manual/4.1/en/appendixes.configuration.html -->
    <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/4.1/phpunit.xsd"
    >

        <!-- ... -->

        <listeners>
            <listener class="Symfony\Bridge\PhpUnit\SymfonyTestsListener">
                <arguments>
                    <array>
                        <element key="time-sensitive"><string>Symfony\Component\HttpFoundation</string></element>
                    </array>
                </arguments>
            </listener>
        </listeners>
    </phpunit>

Under the hood, a PHPUnit listener injects the mocked functions in the tested
classes' namespace. In order to work as expected, the listener has to run before
the tested class ever runs.

By default, the mocked functions are created when the annotation are found and
the corresponding tests are run. Depending on how your tests are constructed,
this might be too late.

You can either:

* Declare the namespaces of the tested classes in your ``phpunit.xml.dist``;
* Register the namespaces at the end of the ``config/bootstrap.php`` file.

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <!-- ... -->
    <listeners>
        <listener class="Symfony\Bridge\PhpUnit\SymfonyTestsListener">
                <arguments>
                    <array>
                        <element key="time-sensitive"><string>Acme\MyClassTest</string></element>
                    </array>
                </arguments>
            </listener>
    </listeners>

::

    // config/bootstrap.php
    use Symfony\Bridge\PhpUnit\ClockMock;

    // ...
    if ('test' === $_SERVER['APP_ENV']) {
        ClockMock::register('Acme\\MyClassTest\\');
    }

Modified PHPUnit script
-----------------------

This bridge provides a modified version of PHPUnit that you can call by using
its ``bin/simple-phpunit`` command. It has the following features:

* Works with a standalone vendor directory that doesn't conflict with yours;
* Does not embed ``prophecy`` to prevent any conflicts with its dependencies;
* Collects and replays skipped tests when the ``SYMFONY_PHPUNIT_SKIPPED_TESTS``
  env var is defined: the env var should specify a file name that will be used for
  storing skipped tests on a first run, and replay them on the second run;
* Parallelizes test suites execution when given a directory as argument, scanning
  this directory for ``phpunit.xml.dist`` files up to ``SYMFONY_PHPUNIT_MAX_DEPTH``
  levels (specified as an env var, defaults to ``3``);

The script writes the modified PHPUnit it builds in a directory that can be
configured by the ``SYMFONY_PHPUNIT_DIR`` env var, or in the same directory as
the ``simple-phpunit`` if it is not provided. It's also possible to set this
env var in the ``phpunit.xml.dist`` file.

If you have installed the bridge through Composer, you can run it by calling e.g.:

.. code-block:: terminal

    $ vendor/bin/simple-phpunit

.. tip::

    It's possible to change the PHPUnit version by setting the
    ``SYMFONY_PHPUNIT_VERSION`` env var in the ``phpunit.xml.dist`` file (e.g.
    ``<server name="SYMFONY_PHPUNIT_VERSION" value="5.5"/>``). This is the
    preferred method as it can be committed to your version control repository.

    It's also possible to set ``SYMFONY_PHPUNIT_VERSION`` as a real env var
    (not defined in a :ref:`dotenv file <config-dot-env>`).

    In the same way, ``SYMFONY_MAX_PHPUNIT_VERSION`` will set the maximum version
    of PHPUnit to be considered. This is useful when testing a framework that does
    not support the latest version(s) of PHPUnit.

.. tip::

    If you still need to use ``prophecy`` (but not ``symfony/yaml``),
    then set the ``SYMFONY_PHPUNIT_REMOVE`` env var to ``symfony/yaml``.

    It's also possible to set this env var in the ``phpunit.xml.dist`` file.

.. tip::

    It is also possible to require additional packages that will be installed along
    with the rest of the needed PHPUnit packages using the ``SYMFONY_PHPUNIT_REQUIRE``
    env variable. This is specially useful for installing PHPUnit plugins without
    having to add them to your main ``composer.json`` file. The required packages
    need to be separated with a space.

    .. code-block:: xml

        <!-- phpunit.xml.dist -->
        <!-- ... -->
        <php>
            <env name="SYMFONY_PHPUNIT_REQUIRE" value="vendor/name:^1.2 vendor/name2:^3"/>
        </php>

Code Coverage Listener
----------------------

By default, the code coverage is computed with the following rule: if a line of
code is executed, then it is marked as covered. The test which executes a
line of code is therefore marked as "covering the line of code". This can be
misleading.

Consider the following example::

    class Bar
    {
        public function barMethod(): string
        {
            return 'bar';
        }
    }

    class Foo
    {
        public function __construct(
            private Bar $bar,
        ) {
        }

        public function fooMethod(): string
        {
            $this->bar->barMethod();

            return 'bar';
        }
    }

    class FooTest extends PHPUnit\Framework\TestCase
    {
        public function test(): void
        {
            $bar = new Bar();
            $foo = new Foo($bar);

            $this->assertSame('bar', $foo->fooMethod());
        }
    }

The ``FooTest::test`` method executes every single line of code of both ``Foo``
and ``Bar`` classes, but ``Bar`` is not truly tested. The ``CoverageListener``
aims to fix this behavior by adding the appropriate `@covers`_ annotation on
each test class.

If a test class already defines the ``@covers`` annotation, this listener does
nothing. Otherwise, it tries to find the code related to the test by removing
the ``Test`` part of the classname: ``My\Namespace\Tests\FooTest`` ->
``My\Namespace\Foo``.

Installation
~~~~~~~~~~~~

Add the following configuration to the ``phpunit.xml.dist`` file:

.. code-block:: xml

    <!-- https://phpunit.de/manual/6.0/en/appendixes.configuration.html -->
    <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/6.0/phpunit.xsd"
    >

        <!-- ... -->

        <listeners>
            <listener class="Symfony\Bridge\PhpUnit\CoverageListener"/>
        </listeners>
    </phpunit>

If the logic used to find the related code is too simple or doesn't work for
your application, you can use your own SUT (System Under Test) solver:

.. code-block:: xml

    <listeners>
        <listener class="Symfony\Bridge\PhpUnit\CoverageListener">
            <arguments>
                <string>My\Namespace\SutSolver::solve</string>
            </arguments>
        </listener>
    </listeners>

The ``My\Namespace\SutSolver::solve`` can be any PHP callable and receives the
current test as its first argument.

Finally, the listener can also display warning messages when the SUT solver does
not find the SUT:

.. code-block:: xml

    <listeners>
        <listener class="Symfony\Bridge\PhpUnit\CoverageListener">
            <arguments>
                <null/>
                <boolean>true</boolean>
            </arguments>
        </listener>
    </listeners>

.. _`PHPUnit`: https://phpunit.de
.. _`PHPUnit event listener`: https://docs.phpunit.de/en/10.0/extending-phpunit.html#phpunit-s-event-system
.. _`ErrorHandler component`: https://github.com/symfony/error-handler
.. _`PHPUnit's assertStringMatchesFormat()`: https://docs.phpunit.de/en/9.6/assertions.html#assertstringmatchesformat
.. _`PHP error handler`: https://www.php.net/manual/en/book.errorfunc.php
.. _`environment variable`: https://docs.phpunit.de/en/9.6/configuration.html#the-env-element
.. _`@-silencing operator`: https://www.php.net/manual/en/language.operators.errorcontrol.php
.. _`Travis CI`: https://travis-ci.org/
.. _`test listener`: https://docs.phpunit.de/en/9.6/configuration.html#the-extensions-element
.. _`@covers`: https://docs.phpunit.de/en/9.6/annotations.html#covers
.. _`PHP namespace resolutions rules`: https://www.php.net/manual/en/language.namespaces.rules.php
