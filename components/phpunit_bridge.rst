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

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>`
  (``symfony/phpunit-bridge`` on `Packagist`_); as a ``dev`` dependency;

* Use the official Git repository (https://github.com/symfony/phpunit-bridge).

.. include:: /components/require_autoload.rst.inc


If you are using the `Modified PHPUnit script`_ (A.K.A. Simple PHPUnit), then no further step is required. If however
you are using the regular `PHPUnit script`_, then you need to register ``SymfonyTestsListener``
(a `PHPUnit test listener`_):

.. code-block:: xml

    <!-- http://phpunit.de/manual/6.0/en/appendixes.configuration.html -->
    <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/6.0/phpunit.xsd"
    >

        <!-- ... -->

        <listeners>
            <listener class="Symfony\Bridge\PhpUnit\SymfonyTestsListener" />
        </listeners>
    </phpunit>

.. tip:: 3.1
    Note that this step is mandatory only if you plan to `Write Assertions about Deprecations`_.

Usage
-----

Once the component is installed, it automatically registers a
`PHPUnit event listener`_ which in turn registers a `PHP error handler`_
called :class:`Symfony\\Bridge\\PhpUnit\\DeprecationErrorHandler`. After
running your PHPUnit tests, you will get a report similar to this one:

.. image:: /_images/components/phpunit_bridge/report.png

The summary includes:

**Unsilenced**
    Reports deprecation notices that were triggered without the recommended
    `@-silencing operator`_.

**Legacy**
    Deprecation notices denote tests that explicitly test some legacy features.

**Remaining/Other**
    Deprecation notices are all other (non-legacy) notices, grouped by message,
    test class and method.

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

There are four ways to mark a test as legacy:

* (**Recommended**) Add the ``@group legacy`` annotation to its class or method;

* Make its class name start with the ``Legacy`` prefix;

* Make its method name start with ``testLegacy*()`` instead of ``test*()``;

* Make its data provider start with ``provideLegacy*()`` or ``getLegacy*()``.

Configuration
-------------

In case you need to inspect the stack trace of a particular deprecation
triggered by your unit tests, you can set the ``SYMFONY_DEPRECATIONS_HELPER``
`environment variable`_ to a regular expression that matches this deprecation's
message, enclosed with ``/``. For example, with:

.. code-block:: xml

    <!-- http://phpunit.de/manual/6.0/en/appendixes.configuration.html -->
    <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/6.0/phpunit.xsd"
    >

        <!-- ... -->

        <php>
            <server name="KERNEL_DIR" value="app/" />
            <env name="SYMFONY_DEPRECATIONS_HELPER" value="/foobar/" />
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

Disabling the Deprecation Helper
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 3.1
    The ability to disable the deprecation helper was introduced in the 3.1 
    version of this component.

Set the ``SYMFONY_DEPRECATIONS_HELPER`` environment variable to ``disabled`` to
completely disable the deprecation helper. This is useful to make use of the
rest of features provided by this component without getting errors or messages
related to deprecations.

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


Time-sensitive Tests
--------------------

Use Case
~~~~~~~~

If you have this kind of time-related tests::

    use Symfony\Component\Stopwatch\Stopwatch;

    class MyTest extends \PHPUnit_Framework_TestCase
    {
        public function testSomething()
        {
            $stopwatch = new Stopwatch();

            $stopwatch->start();
            sleep(10);
            $duration = $stopwatch->stop();

            $this->assertEquals(10, $duration);
        }
    }

You used the :doc:`Symfony Stopwatch Component </components/stopwatch>` to
calculate the duration time of your process, here 10 seconds. However, depending
on the load of the server your the processes running on your local machine, the
``$duration`` could for example be `10.000023s` instead of `10s`.

This kind of tests are called transient tests: they are failing randomly
depending on spurious and external circumstances. They are often cause trouble
when using public continuous integration services like `Travis CI`_.

Clock Mocking
~~~~~~~~~~~~~

The :class:`Symfony\\Bridge\\PhpUnit\\ClockMock` class provided by this bridge
allows you to mock the PHP's built-in time functions ``time()``,
``microtime()``, ``sleep()`` and ``usleep()``.

To use the ``ClockMock`` class in your test, you can:

* (**Recommended**) Add the ``@group time-sensitive`` annotation to its class or
  method;

* Register it manually by calling ``ClockMock::register(__CLASS__)`` and
  ``ClockMock::withClockMock(true)`` before the test and
  ``ClockMock::withClockMock(false)`` after the test.

As a result, the following is guaranteed to work and is no longer a transient
test::

    use Symfony\Component\Stopwatch\Stopwatch;

    /**
     * @group time-sensitive
     */
    class MyTest extends \PHPUnit_Framework_TestCase
    {
        public function testSomething()
        {
            $stopwatch = new Stopwatch();

            $stopwatch->start();
            sleep(10);
            $duration = $stopwatch->stop();

            $this->assertEquals(10, $duration);
        }
    }

And that's all!

.. tip::

    An added bonus of using the ``ClockMock`` class is that time passes
    instantly. Using PHP's ``sleep(10)`` will make your test wait for 10
    actual seconds (more or less). In contrast, the ``ClockMock`` class
    advances the internal clock the given number of seconds without actually
    waiting that time, so your test will execute 10 seconds faster.

DNS-sensitive Tests
-------------------

.. versionadded:: 3.1
    The mocks for DNS related functions were introduced in the 3.1 version
    of this component.

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

    use Symfony\Component\Validator\Constraints\Email;

    class MyTest extends \PHPUnit_Framework_TestCase
    {
        public function testEmail()
        {
            $validator = ...
            $constraint = new Email(array('checkMX' => true));

            $result = $validator->validate('foo@example.com', $constraint);

            // ...
    }

In order to avoid making a real network connection, add the ``@dns-sensitive``
annotation to the class and use the ``DnsMock::withMockedHosts()`` to configure
the data you expect to get for the given hosts::

    use Symfony\Component\Validator\Constraints\Email;

    /**
     * @group dns-sensitive
     */
    class MyTest extends \PHPUnit_Framework_TestCase
    {
        public function testEmails()
        {
            DnsMock::withMockedHosts(array('example.com' => array(array('type' => 'MX'))));

            $validator = ...
            $constraint = new Email(array('checkMX' => true));

            $result = $validator->validate('foo@example.com', $constraint);

            // ...
    }

The ``withMockedHosts()`` method configuration is defined as an array. The keys
are the mocked hosts and the values are arrays of DNS records in the same format
returned by :phpfunction:`dns_get_record`, so you can simulate diverse network
conditions::

    DnsMock::withMockedHosts(array(
        'example.com' => array(
            array(
                'type' => 'A',
                'ip' => '1.2.3.4',
            ),
            array(
                'type' => 'AAAA',
                'ipv6' => '::12',
            ),
        ),
    ));

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
             xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/4.1/phpunit.xsd"
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

Modified PHPUnit script
-----------------------

.. versionadded:: 3.2
    The modified PHPUnit script script was introduced in the 3.2 version of 
    this component.

This bridge provides a modified version of PHPUnit that you can call by using
its ``bin/simple-phpunit`` command. It has the following features:

* Does not embed ``symfony/yaml`` nor ``prophecy`` to prevent any conflicts with
  these dependencies;
* Uses PHPUnit 4.8 when run with PHP <=5.5 and PHPUnit 5.3 when run with PHP >=5.6;
* Collects and replays skipped tests when the ``SYMFONY_PHPUNIT_SKIPPED_TESTS``
  env var is defined: the env var should specify a file name that will be used for
  storing skipped tests on a first run, and replay them on the second run;
* Parallelizes test suites execution when given a directory as argument, scanning
  this directory for ``phpunit.xml.dist`` files up to ``SYMFONY_PHPUNIT_MAX_DEPTH``
  levels (specified as an env var, defaults to ``3``);

The script writes the modified PHPUnit it builds in a directory that can be
configured by the ``SYMFONY_PHPUNIT_DIR`` env var, or in the same directory as
the ``simple-phpunit`` if it is not provided.

If you have installed the bridge through Composer, you can run it by calling e.g.:

.. code-block:: bash

    $ vendor/bin/simple-phpunit

.. tip::

    Set the ``SYMFONY_PHPUNIT_VERSION`` env var to e.g. ``5.5`` to change the
    base version of PHPUnit to ``5.5`` instead of the default ``5.3``.

.. tip::

    Set the ``SYMFONY_PHPUNIT_REMOVE`` env var to ``symfony/yaml`` if you need
    ``prophecy`` but not ``symfony/yaml``.

.. _PHPUnit: https://phpunit.de
.. _`PHPUnit event listener`: https://phpunit.de/manual/current/en/extending-phpunit.html#extending-phpunit.PHPUnit_Framework_TestListener
.. _`PHPUnit's assertStringMatchesFormat()`: https://phpunit.de/manual/current/en/appendixes.assertions.html#appendixes.assertions.assertStringMatchesFormat
.. _`PHP error handler`: http://php.net/manual/en/book.errorfunc.php
.. _`environment variable`: https://phpunit.de/manual/current/en/appendixes.configuration.html#appendixes.configuration.php-ini-constants-variables
.. _Packagist: https://packagist.org/packages/symfony/phpunit-bridge
.. _`@-silencing operator`: http://php.net/manual/en/language.operators.errorcontrol.php
.. _`@-silenced`: http://php.net/manual/en/language.operators.errorcontrol.php
.. _`Travis CI`: https://travis-ci.org/
.. _`PHPUnit script`: PHPUnit_
.. _`PHPUnit test listener`: https://phpunit.de/manual/current/en/appendixes.configuration.html#appendixes.configuration.test-listeners
