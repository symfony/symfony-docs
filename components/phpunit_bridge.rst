.. index::
   single: PHPUnitBridge
   single: Components; PHPUnitBridge

The PHPUnit Bridge
==================

    The PHPUnit Bridge provides utilities to report legacy tests and usage of
    deprecated code and a helper for time-sensitive tests.

It comes with the following features:

* Forces the tests to use a consistent locale (``C``);

* Auto-register ``class_exists`` to load Doctrine annotations (when used);

* It displays the whole list of deprecated features used in the application;

* Displays the stack trace of a deprecation on-demand;

* Provides a ``ClockMock`` and ``DnsMock`` helper classes for time or network-sensitive tests.

* Provides a modified version of PHPUnit that does not embed ``symfony/yaml`` nor
  ``prophecy`` to prevent any conflicts with these dependencies.

Installation
------------

.. code-block:: terminal

    $ composer require --dev "symfony/phpunit-bridge:*"

Alternatively, you can clone the `<https://github.com/symfony/phpunit-bridge>`_ repository.

.. include:: /components/require_autoload.rst.inc

.. note::

    The PHPUnit bridge is designed to work with all maintained versions of
    Symfony components, even across different major versions of them. You should
    always use its very latest stable major version to get the most accurate
    deprecation report.

If you plan to :ref:`write-assertions-about-deprecations` and use the regular
PHPUnit script (not the modified PHPUnit script provided by Symfony), you have
to register a new `test listener`_ called ``SymfonyTestsListener``:

.. code-block:: xml

    <!-- http://phpunit.de/manual/6.0/en/appendixes.configuration.html -->
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

Trigger Deprecation Notices
---------------------------

Deprecation notices can be triggered by using::

    @trigger_error('Your deprecation message', E_USER_DEPRECATED);

Without the `@-silencing operator`_, users would need to opt-out from deprecation
notices. Silencing by default swaps this behavior and allows users to opt-in
when they are ready to cope with them (by adding a custom error handler like the
one provided by this bridge). When not silenced, deprecation notices will appear
in the **Unsilenced** section of the deprecation report.

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

    <!-- http://phpunit.de/manual/6.0/en/appendixes.configuration.html -->
    <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/6.0/phpunit.xsd"
    >

        <!-- ... -->

        <php>
            <server name="KERNEL_CLASS" value="App\Kernel"/>
            <env name="SYMFONY_DEPRECATIONS_HELPER" value="/foobar/"/>
        </php>
    </phpunit>

PHPUnit_ will stop your test suite once a deprecation notice is triggered whose
message contains the ``"foobar"`` string.

Making Tests Fail
~~~~~~~~~~~~~~~~~

By default, any non-legacy-tagged or any non-`@-silenced`_ deprecation notices
will make tests fail. Alternatively, setting ``SYMFONY_DEPRECATIONS_HELPER`` to
an arbitrary value (ex: ``320``) will make the tests fails only if a higher
number of deprecation notices is reached (``0`` is the default value). You can
also set the value ``"weak"`` which will make the bridge ignore any deprecation
notices. This is useful to projects that must use deprecated interfaces for
backward compatibility reasons.

When you maintain a library, having the test suite fail as soon as a dependency
introduces a new deprecation is not desirable, because it shifts the burden of
fixing that deprecation to any contributor that happens to submit a pull
request shortly after a new vendor release is made with that deprecation. To
mitigate this, you can either use tighter requirements, in the hope that
dependencies will not introduce deprecations in a patch version, or even commit
the Composer lock file, which would create another class of issues. Libraries
will often use ``SYMFONY_DEPRECATIONS_HELPER=weak`` because of this. This has
the drawback of allowing contributions that introduce deprecations but:

* forget to fix the deprecated calls if there are any;
* forget to mark appropriate tests with the ``@group legacy`` annotations.

By using the ``"weak_vendors"`` value, deprecations that are triggered outside
the ``vendors`` directory will make the test suite fail, while deprecations
triggered from a library inside it will not, giving you the best of both
worlds.

Disabling the Deprecation Helper
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Set the ``SYMFONY_DEPRECATIONS_HELPER`` environment variable to ``disabled`` to
completely disable the deprecation helper. This is useful to make use of the
rest of features provided by this component without getting errors or messages
related to deprecations.

.. _write-assertions-about-deprecations:

Deprecation Notices at Autoloading Time
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, the PHPUnit Bridge uses ``DebugClassLoader`` from the
:doc:`Debug component </components/debug>` to throw deprecation notices at
class autoloading time. This can be disabled with the ``debug-class-loader`` option.

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

Write Assertions about Deprecations
-----------------------------------

When adding deprecations to your code, you might like writing tests that verify
that they are triggered as required. To do so, the bridge provides the
``@expectedDeprecation`` annotation that you can use on your test methods.
It requires you to pass the expected message, given in the same format as for
the `PHPUnit's assertStringMatchesFormat()`_ method. If you expect more than one
deprecation message for a given test method, you can use the annotation several
times (order matters)::

    /**
     * @group legacy
     * @expectedDeprecation This "%s" method is deprecated.
     * @expectedDeprecation The second argument of the "%s" method is deprecated.
     */
    public function testDeprecatedCode()
    {
        @trigger_error('This "Foo" method is deprecated.', E_USER_DEPRECATED);
        @trigger_error('The second argument of the "Bar" method is deprecated.', E_USER_DEPRECATED);
    }

Display the Full Stack Trace
----------------------------

By default, the PHPUnit Bridge displays only deprecation messages.
To show the full stack trace related to a deprecation, set the value of ``SYMFONY_DEPRECATIONS_HELPER``
to a regular expression matching the deprecation message.

For example, if the following deprecation notice is thrown::

    1x: Doctrine\Common\ClassLoader is deprecated.
      1x in EntityTypeTest::setUp from Symfony\Bridge\Doctrine\Tests\Form\Type

Running the following command will display the full stack trace:

.. code-block:: terminal

    $ SYMFONY_DEPRECATIONS_HELPER='/Doctrine\\Common\\ClassLoader is deprecated\./' ./vendor/bin/simple-phpunit

Time-sensitive Tests
--------------------

Use Case
~~~~~~~~

If you have this kind of time-related tests::

    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Stopwatch\Stopwatch;

    class MyTest extends TestCase
    {
        public function testSomething()
        {
            $stopwatch = new Stopwatch();

            $stopwatch->start('event_name');
            sleep(10);
            $duration = $stopwatch->stop('event_name')->getDuration();

            $this->assertEquals(10000, $duration);
        }
    }

You used the :doc:`Symfony Stopwatch Component </components/stopwatch>` to
calculate the duration time of your process, here 10 seconds. However, depending
on the load of the server or the processes running on your local machine, the
``$duration`` could for example be ``10.000023s`` instead of ``10s``.

This kind of tests are called transient tests: they are failing randomly
depending on spurious and external circumstances. They are often cause trouble
when using public continuous integration services like `Travis CI`_.

Clock Mocking
~~~~~~~~~~~~~

The :class:`Symfony\\Bridge\\PhpUnit\\ClockMock` class provided by this bridge
allows you to mock the PHP's built-in time functions ``time()``,
``microtime()``, ``sleep()`` and ``usleep()``. Additionally the function
``date()`` is mocked so it uses the mocked time if no timestamp is specified.
Other functions with an optional timestamp parameter that defaults to ``time()``
will still use the system time instead of the mocked time.

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
        public function testSomething()
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
        public function getTimeInHours()
        {
            return time() / 3600;
        }
    }

    // the test that mocks the external time() function explicitly
    namespace App\Tests;

    use App\MyClass;
    use PHPUnit\Framework\TestCase;

    /**
     * @group time-sensitive
     */
    class MyTest extends TestCase
    {
        public function testGetTimeInHours()
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

Consider the following example that uses the ``checkMX`` option of the ``Email``
constraint to test the validity of the email domain::

    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Validator\Constraints\Email;

    class MyTest extends TestCase
    {
        public function testEmail()
        {
            $validator = ...
            $constraint = new Email(['checkMX' => true]);

            $result = $validator->validate('foo@example.com', $constraint);

            // ...
    }

In order to avoid making a real network connection, add the ``@dns-sensitive``
annotation to the class and use the ``DnsMock::withMockedHosts()`` to configure
the data you expect to get for the given hosts::

    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Validator\Constraints\Email;

    /**
     * @group dns-sensitive
     */
    class MyTest extends TestCase
    {
        public function testEmails()
        {
            DnsMock::withMockedHosts(['example.com' => [['type' => 'MX']]]);

            $validator = ...
            $constraint = new Email(['checkMX' => true]);

            $result = $validator->validate('foo@example.com', $constraint);

            // ...
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

Troubleshooting
---------------

The ``@group time-sensitive`` and ``@group dns-sensitive`` annotations work
"by convention" and assume that the namespace of the tested class can be
obtained just by removing the ``Tests\`` part from the test namespace. I.e.
that if the your test case fully-qualified class name (FQCN) is
``App\Tests\Watch\DummyWatchTest``, it assumes the tested class namespace
is ``App\Watch``.

If this convention doesn't work for your application, configure the mocked
namespaces in the ``phpunit.xml`` file, as done for example in the
:doc:`HttpKernel Component </components/http_kernel>`:

.. code-block:: xml

    <!-- http://phpunit.de/manual/4.1/en/appendixes.configuration.html -->
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
the tested class ever runs. By default, the mocked functions are created when the
annotation are found and the corresponding tests are run. Depending on how your
tests are constructed, this might be too late. In this case, you will need to declare
the namespaces of the tested classes in your phpunit.xml.dist

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

Modified PHPUnit script
-----------------------

This bridge provides a modified version of PHPUnit that you can call by using
its ``bin/simple-phpunit`` command. It has the following features:

* Does not embed ``symfony/yaml`` nor ``prophecy`` to prevent any conflicts with
  these dependencies;
* Uses PHPUnit 4.8 when run with PHP <=5.5, PHPUnit 5.7 when run with PHP >=5.6
  and PHPUnit 6.5 when run with PHP >=7.2;
* Collects and replays skipped tests when the ``SYMFONY_PHPUNIT_SKIPPED_TESTS``
  env var is defined: the env var should specify a file name that will be used for
  storing skipped tests on a first run, and replay them on the second run;
* Parallelizes test suites execution when given a directory as argument, scanning
  this directory for ``phpunit.xml.dist`` files up to ``SYMFONY_PHPUNIT_MAX_DEPTH``
  levels (specified as an env var, defaults to ``3``);

The script writes the modified PHPUnit it builds in a directory that can be
configured by the ``SYMFONY_PHPUNIT_DIR`` env var, or in the same directory as
the ``simple-phpunit`` if it is not provided.

It's also possible to set this env var in the ``phpunit.xml.dist`` file.

If you have installed the bridge through Composer, you can run it by calling e.g.:

.. code-block:: terminal

    $ vendor/bin/simple-phpunit

.. tip::

    Set the ``SYMFONY_PHPUNIT_VERSION`` env var to e.g. ``5.5`` to change the
    base version of PHPUnit to ``5.5`` instead of the default ``5.3``.

    It's also possible to set this env var in the ``phpunit.xml.dist`` file.

.. tip::

    If you still need to use ``prophecy`` (but not ``symfony/yaml``),
    then set the ``SYMFONY_PHPUNIT_REMOVE`` env var to ``symfony/yaml``.

    It's also possible to set this env var in the ``phpunit.xml.dist`` file.

Code Coverage Listener
----------------------

By default, the code coverage is computed with the following rule: if a line of
code is executed, then it is marked as covered. The test which executes a
line of code is therefore marked as "covering the line of code". This can be
misleading.

Consider the following example::

    class Bar
    {
        public function barMethod()
        {
            return 'bar';
        }
    }

    class Foo
    {
        private $bar;

        public function __construct(Bar $bar)
        {
            $this->bar = $bar;
        }

        public function fooMethod()
        {
            $this->bar->barMethod();

            return 'bar';
        }
    }

    class FooTest extends PHPUnit\Framework\TestCase
    {
        public function test()
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

    <!-- http://phpunit.de/manual/6.0/en/appendixes.configuration.html -->
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
current test classname as its first argument.

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

.. _PHPUnit: https://phpunit.de
.. _`PHPUnit event listener`: https://phpunit.de/manual/current/en/extending-phpunit.html#extending-phpunit.PHPUnit_Framework_TestListener
.. _`PHPUnit's assertStringMatchesFormat()`: https://phpunit.de/manual/current/en/appendixes.assertions.html#appendixes.assertions.assertStringMatchesFormat
.. _`PHP error handler`: https://php.net/manual/en/book.errorfunc.php
.. _`environment variable`: https://phpunit.de/manual/current/en/appendixes.configuration.html#appendixes.configuration.php-ini-constants-variables
.. _Packagist: https://packagist.org/packages/symfony/phpunit-bridge
.. _`@-silencing operator`: https://php.net/manual/en/language.operators.errorcontrol.php
.. _`@-silenced`: https://php.net/manual/en/language.operators.errorcontrol.php
.. _`Travis CI`: https://travis-ci.org/
.. _`test listener`: https://phpunit.de/manual/current/en/appendixes.configuration.html#appendixes.configuration.test-listeners
.. _`@covers`: https://phpunit.de/manual/current/en/appendixes.annotations.html#appendixes.annotations.covers
.. _`PHP namespace resolutions rules`: https://php.net/manual/en/language.namespaces.rules.php
