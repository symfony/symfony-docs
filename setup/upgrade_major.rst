.. index::
    single: Upgrading; Major Version

Upgrading a Major Version (e.g. 4.4.0 to 5.0.0)
===============================================

Every two years, Symfony releases a new major version release (the first number
changes). These releases are the trickiest to upgrade, as they are allowed to
break backward compatibility. However, Symfony makes this upgrade process as
smooth as possible.

This means that you can update most of your code before the major release is
actually released. This is called making your code *future compatible*.

There are a couple of steps to upgrading a major version:

#. :ref:`Make your code deprecation free <upgrade-major-symfony-deprecations>`;
#. :ref:`Update to the new major version via Composer <upgrade-major-symfony-composer>`;
#. :ref:`Update your code to work with the new version <upgrade-major-symfony-after>`.

.. _upgrade-major-symfony-deprecations:

1) Make your Code Deprecation Free
----------------------------------

During the lifecycle of a major release, new features are added and method
signatures and public API usages are changed. However,
:doc:`minor versions </setup/upgrade_minor>` should not contain any
backwards incompatible changes. To accomplish this, the "old" (e.g. functions,
classes, etc) code still works, but is marked as *deprecated*, indicating that
it will be removed/changed in the future and that you should stop using it.

When the major version is released (e.g. 5.0.0), all deprecated features and
functionality are removed. So, as long as you've updated your code to stop
using these deprecated features in the last version before the major (e.g.
``4.4.*``), you should be able to upgrade without a problem. That means that
you should first :doc:`upgrade to the last minor version </setup/upgrade_minor>`
(e.g. 4.4) so that you can see *all* the deprecations.

To help you find deprecations, notices are triggered whenever you end up
using a deprecated feature. When visiting your application in the
:ref:`dev environment <configuration-environments>`
in your browser, these notices are shown in the web dev toolbar:

.. image:: /_images/install/deprecations-in-profiler.png
   :align: center
   :class: with-browser

Ultimately, you should aim to stop using the deprecated functionality.
Sometimes, this is easy: the warning might tell you exactly what to change.

But other times, the warning might be unclear: a setting somewhere might
cause a class deeper to trigger the warning. In this case, Symfony does its
best to give a clear message, but you may need to research that warning further.

And sometimes, the warning may come from a third-party library or bundle
that you're using. If that's true, there's a good chance that those deprecations
have already been updated. In that case, upgrade the library to fix them.

Once all the deprecation warnings are gone, you can upgrade with a lot
more confidence.

Deprecations in PHPUnit
~~~~~~~~~~~~~~~~~~~~~~~

When you run your tests using PHPUnit, no deprecation notices are shown.
To help you here, Symfony provides a PHPUnit bridge. This bridge will show
you a nice summary of all deprecation notices at the end of the test report.

All you need to do is install the PHPUnit bridge:

.. code-block:: terminal

    $ composer require --dev symfony/phpunit-bridge

Now, you can start fixing the notices:

.. code-block:: terminal

    # this command is available after running "composer require --dev symfony/phpunit-bridge"
    $ ./bin/phpunit
    ...

    OK (10 tests, 20 assertions)

    Remaining deprecation notices (6)
    
    The "request" service is deprecated and will be removed in 3.0. Add a type-hint for
    Symfony\Component\HttpFoundation\Request to your controller parameters to retrieve the
    request instead: 6x
        3x in PageAdminTest::testPageShow from Symfony\Cmf\SimpleCmsBundle\Tests\WebTest\Admin
        2x in PageAdminTest::testPageList from Symfony\Cmf\SimpleCmsBundle\Tests\WebTest\Admin
        1x in PageAdminTest::testPageEdit from Symfony\Cmf\SimpleCmsBundle\Tests\WebTest\Admin

Once you fixed them all, the command ends with ``0`` (success) and you're
done!

.. sidebar:: Using the Weak Deprecations Mode

    Sometimes, you can't fix all deprecations (e.g. something was deprecated
    in 4.4 and you still need to support 4.3). In these cases, you can still
    use the bridge to fix as many deprecations as possible and then allow
    more of them to make your tests pass again. You can do this by using the
    ``SYMFONY_DEPRECATIONS_HELPER`` env variable:

    .. code-block:: xml

        <!-- phpunit.xml.dist -->
        <phpunit>
            <!-- ... -->

            <php>
                <env name="SYMFONY_DEPRECATIONS_HELPER" value="max[total]=999999"/>
            </php>
        </phpunit>

    You can also execute the command like:

    .. code-block:: terminal

        $ SYMFONY_DEPRECATIONS_HELPER=max[total]=999999 php ./bin/phpunit

.. _upgrade-major-symfony-composer:

2) Update to the New Major Version via Composer
-----------------------------------------------

Once your code is deprecation free, you can update the Symfony library via
Composer by modifying your ``composer.json`` file and changing all the libraries
starting with ``symfony/`` to the new major version:

.. code-block:: diff

    {
        "...": "...",

        "require": {
    -         "symfony/cache": "4.4.*",
    +         "symfony/cache": "5.0.*",
    -         "symfony/config": "4.4.*",
    +         "symfony/config": "5.0.*",
    -         "symfony/console": "4.4.*",
    +         "symfony/console": "5.0.*",
            "...": "...",

            "...": "A few libraries starting with
                    symfony/ follow their own versioning scheme. You
                    do not need to update these versions: you can
                    upgrade them independently whenever you want",
            "symfony/monolog-bundle": "^3.5",
        },
        "...": "...",
    }

At the bottom of your ``composer.json`` file, in the ``extra`` block you can
find a data setting for the Symfony version. Make sure to also upgrade
this one. For instance, update it to ``5.0.*`` to upgrade to Symfony 5.0:

.. code-block:: diff

    "extra": {
        "symfony": {
            "allow-contrib": false,
    -       "require": "4.4.*"
    +       "require": "5.0.*"
        }
    }

Next, use Composer to download new versions of the libraries:

.. code-block:: terminal

    $ composer update symfony/*

.. include:: /setup/_update_dep_errors.rst.inc

.. include:: /setup/_update_all_packages.rst.inc

.. _upgrade-major-symfony-after:

3) Update your Code to Work with the New Version
------------------------------------------------

In some rare situations, the next major version *may* contain backwards-compatibility
breaks. Make sure you read the ``UPGRADE-X.0.md`` (where X is the new major version)
included in the Symfony repository for any BC break that you need to be aware of.
