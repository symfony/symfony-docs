.. index::
   single: PHPUnitBridge
   single: Components; PHPUnitBridge

The PHPUnit Bridge Component
=============================

    The PHPUnit Bridge component provides utilities to report legacy tests and
    usage of deprecated code.

It comes with the following features:

* Forces the tests to use a consistent locale (``C``)
* Auto-register ``class_exists`` to load Doctrine annotations (when used)
* It displays the whole list of deprecated features used in the application
* Displays the stack trace of a deprecation on-demand.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>`
  (``symfony/phpunit-bridge`` on `Packagist`_); as a dev dependency
* Use the official Git repository (https://github.com/symfony/phpunit-bridge)

.. include:: /components/require_autoload.rst.inc

Usage
-----

Once the component installed, it automatically registers a
`PHPUnit event listener`_ which in turn registers a `PHP error handler`_
called ``DeprecationErrorHandler``. After running your PHPUnit tests, you will
get a report similar to this one:

.. image:: /images/components/phpunit_bridge/report.png

The summary includes:

**Unsilenced**
    Reports deprecation notices that were triggered without the recommended
    @-silencing operator.
**Legacy**
    Deprecation notices denote tests that explicitly test some legacy features.
**Remaining/Other**
    Deprecation notices are all other (non-legacy) notices, grouped by message,
    test class and method.

Trigger Deprecation Notices
---------------------------

Deprecation notices can be triggered by using::

    @trigger_error('Your deprecation message', E_USER_DEPRECATED);

Without the @-silencing operator, users would need to opt-out from deprecation
notices. Silencing by default swaps this behavior and allows users to opt-in
when they are ready to cope with them (by adding a custom error handler like the
one provided by this bridge). When not silenced, deprecation notices will appear
in the **Unsilenced** section of the deprecation report.

Mark Tests as Legacy
--------------------

There are four ways to mark a test as legacy:

* (**Recommended**) Add the ``@group legacy`` annotation to its class or method
* Make its class start with the ``Legacy`` prefix
* Make its method start with ``testLegacy``
* Make its data provider start with ``provideLegacy`` or ``getLegacy``

Configuration
-------------

In case you need to inspect the stack trace of a particular deprecation
triggered by your unit tests, you can set the ``SYMFONY_DEPRECATIONS_HELPER``
`environment variable`_ to a regular expression that matches this deprecation's
message, enclosed with ``/``. For example, with:

.. code-block:: xml

    <!-- http://phpunit.de/manual/4.1/en/appendixes.configuration.html -->
    <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/4.1/phpunit.xsd"
    >
    <?xml version="1.0" encoding="UTF-8"?>

        <!-- ... -->

        <php>
            <server name="KERNEL_DIR" value="app/" />
            <env name="SYMFONY_DEPRECATIONS_HELPER" value="/foobar/" />
        </php>
    </phpunit>

PHPUnit_ will stop your test suite once a deprecation notice is triggered whose
message contains the ``"foobar"`` string.

Making Tests Fail
-----------------

By default, any non-legacy-tagged or any non-@-silenced deprecation notices will
make tests fail. Alternatively, setting ``SYMFONY_DEPRECATIONS_HELPER`` to an
arbitrary value (ex: ``320``) will make the tests fails only if a higher number
of deprecation notices is reached (``0`` is the default value). You can also set
the value ``"weak"`` will make the bridge ignore any deprecation notices. This is
useful to projects that must use deprecated interfaces for backward compatibility
reasons.

.. _PHPUnit: https://phpunit.de
.. _`PHPUnit event listener`: https://phpunit.de/manual/current/en/extending-phpunit.html#extending-phpunit.PHPUnit_Framework_TestListener
.. _`PHP error handler`: http://php.net/manual/en/book.errorfunc.php
.. _`environment variable`: https://phpunit.de/manual/current/en/appendixes.configuration.html#appendixes.configuration.php-ini-constants-variables
.. _Packagist: https://packagist.org/packages/symfony/phpunit-bridge
