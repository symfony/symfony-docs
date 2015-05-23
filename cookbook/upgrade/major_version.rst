.. index::
    single: Upgrading; Major Version

Upgrading a Major Version (e.g. 2.7.0 to 3.0.0)
===============================================

Every few years, Symfony releases a new major version release (the first number
changes). These releases are the trickiest to upgrade, as they are allowed to
contain BC breaks. However, Symfony tries to make this upgrade process as
smooth as possible.

This means that you can update most of your code before the major release is
actually released. This is called making your code *future compatible*.

There are a couple of steps to upgrading a major version:

#. :ref:`Make your code deprecation free <upgrade-major-symfony-deprecations>`;
#. :ref:`Update to the new major version via Composer <upgrade-major-symfony-composer>`.
#. :ref:`Update your code to work with the new version <upgrade-major-symfony-after>`

.. _upgrade-major-symfony-deprecations:

1) Make your Code Deprecation Free
----------------------------------

During the lifecycle of a major release, new features are added and method
signatures and public API usages are changed. However, minor versions should
not contain any backwards compatibility changes. It is made sure that there is
a so-called *backwards compatibility layer* (or BC layer). This means that the
old API will still work, while the new feature is used internally. This BC
layer is then marked as *deprecated*, indicating that it will be
removed/changed in the future.

The major version is the only time all existing BC layers are removed. However,
if you make sure you've fixed all deprecated usages in the last version of the
previous major version, you should be able to upgrade to the new major version
without problems. To help you with this, the last minor releases will trigger
deprecated notices. For example, 2.7 and 2.8 trigger deprecated notices and if
you do not have any notice while using 2.8, you can savely upgrade to 3.0.

When visiting your application in the
:doc:`dev environment </cookbook/configuration/environments>` in your browser,
these notices are shown in the web dev toolbar:

.. image:: /images/cookbook/deprecations-in-profiler.png

Deprecations in PHPUnit
~~~~~~~~~~~~~~~~~~~~~~~

By default, PHPUnit will handle deprecation notices as real errors. This means
that all tests are aborted because it uses a BC layer.

To make sure this doesn't happen, you can install the PHPUnit bridge:

.. code-block:: bash

    $ composer require symfony/phpunit-bridge

Now, your tests execute normally and a nice summary of the deprecation notices
is displayed at the end of the test report:

.. code-block:: text

    $ phpunit
    ...

    OK (10 tests, 20 assertions)

    Remaining deprecation notices (6)

    The "request" service is deprecated and will be removed in 3.0. Add a typehint for
    Symfony\Component\HttpFoundation\Request to your controller parameters to retrieve the
    request instead: 6x
        3x in PageAdminTest::testPageShow from Symfony\Cmf\SimpleCmsBundle\Tests\WebTest\Admin
        2x in PageAdminTest::testPageList from Symfony\Cmf\SimpleCmsBundle\Tests\WebTest\Admin
        1x in PageAdminTest::testPageEdit from Symfony\Cmf\SimpleCmsBundle\Tests\WebTest\Admin

.. _upgrade-major-symfony-composer:

2) Update to the New Major Version via Composer
-----------------------------------------------

If your code is deprecation free, you can update the Symfony library via
Composer by modifying your ``composer.json`` file:

.. code-block:: json

    {
        "...": "...",

        "require": {
            "symfony/symfony": "3.0.*",
        },
        "...": "...",
    }

Next, use Composer to download new versions of the libraries:

.. code-block:: bash

    $ composer update symfony/symfony

.. include:: /cookbook/upgrade/_update_all_packages.rst.inc

.. _upgrade-major-symfony-after:

3) Update your Code to Work with the New Version
------------------------------------------------

There is a high chance that you're done now! However, the next major version
*may* also contain new BC breaks as a BC layer is not always a possibility.
Make sure you read the ``UPGRADE-X.0.md`` (where X is the new major version)
included in the Symfony repository for any BC break that you need to be aware
of.
