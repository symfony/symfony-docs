The PHPUnit Bridge Component
=============================

    The PHPUnit Bridge component provides utilities to report trigerred errors,
    legacy tests and usage of deprecated code.

It comes with the following features:

* Enforce a consistent `C` locale (It forces applications to use the default language for output)
* Auto-register `class_exists` to load Doctrine annotations (when used);
* Print a user deprecation notices summary at the end of the test suite;
* Display the stack trace of a deprecation on-demand.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>`
  (``symfony/phpunit-bridge`` on `Packagist`_); as a dev dependency
* Use the official Git repository (https://github.com/symfony/phpunit-bridge).

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once the component installed, it automatically registers a
`PHPUnit event listener`_. This listener simply registers a `PHP error handler`_
called `DeprecationErrorHandler`. After running your PHPUnit tests again, you
will have a similar report:

.. image:: /images/components/phpunit_bridge/report.png

The summary includes:

* **Unsilenced** reports deprecation notices that were triggered without the recommended @-silencing operator;
* **Legacy** deprecation notices denote tests that explicitly test some legacy interfaces.
* **Remaining/Other** deprecation notices are all other (non-legacy) notices, grouped by message, test class and method.

Trigger deprecation notices
---------------------------

Deprecation notices can be triggered by using:

    @trigger_error('Your deprecation message', E_USER_DEPRECATED);

Without the @-silencing operator, users would need to opt-out from deprecation
notices. Silencing by default swaps this behavior and allows users to opt-in
when they are ready to cope with them (by adding a custom error handler like the
one provided by this bridge.) When not silenced, deprecation notices will appear
in the **Unsilenced** section of the deprecation report.

Mark tests as legacy
--------------------

There are four ways to mark a test as legacy:

* Make its class start with the `Legacy` prefix
* Make its method start with `testLegacy`
* Make its data provider start with `provideLegacy` or `getLegacy`
* Add the `@group legacy` annotation to its class or method

Configuration
-------------

In case you need to inspect the stack trace of a particular deprecation
triggered by your unit tests, you can set the `SYMFONY_DEPRECATIONS_HELPER`
`environment variable`_ to a regular expression that matches this deprecation's
message, encapsed between `/`. For example, with:

.. configuration-block::

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8"?>

        <!-- http://phpunit.de/manual/4.1/en/appendixes.configuration.html -->
        <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                 xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/4.1/phpunit.xsd"
                 backupGlobals="false"
                 colors="true"
                 bootstrap="app/autoload.php"
        >
            <php>
                <ini name="error_reporting" value="-1" />
            </php>

            <testsuites>
                <testsuite name="Project Test Suite">
                    <directory>tests</directory>
                </testsuite>
            </testsuites>

            <php>
                <server name="KERNEL_DIR" value="app/" />
                <env name="SYMFONY_DEPRECATIONS_HELPER" value="/foobar/" />
            </php>

            <filter>
                <whitelist>
                    <directory>src</directory>
                    <exclude>
                        <directory>src/*Bundle/Resources</directory>
                        <directory>src/*/*Bundle/Resources</directory>
                        <directory>src/*/Bundle/*Bundle/Resources</directory>
                    </exclude>
                </whitelist>
            </filter>
        </phpunit>

PHPUnit_ will stop your test suite once a deprecation notice is triggered whose
message contains the `"foobar"` string.

By default, any non-legacy-tagged or any non-@-silenced deprecation notices will
make tests fail. Alternatively, setting `SYMFONY_DEPRECATIONS_HELPER` to the
value `"weak"` will make the bridge ignore any deprecation notices. This is
useful to projects that must use deprecated interfaces for backward compatibility
reasons.

.. _PHPUnit: https://phpunit.de
.. _`PHPUnit event listener`: https://phpunit.de/manual/current/en/extending-phpunit.html#extending-phpunit.PHPUnit_Framework_TestListener
.. _`PHP error handler`: http://php.net/manual/en/book.errorfunc.php
.. _`environment variable`: https://phpunit.de/manual/current/en/appendixes.configuration.html#appendixes.configuration.php-ini-constants-variables
