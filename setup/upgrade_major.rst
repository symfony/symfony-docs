Upgrading a Major Version (e.g. 6.4.0 to 7.0.0)
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

When the major version is released (e.g. 7.0.0), all deprecated features and
functionality are removed. So, as long as you've updated your code to stop
using these deprecated features in the last version before the major (e.g.
``6.4.*``), you should be able to upgrade without a problem. That means that
you should first :doc:`upgrade to the last minor version </setup/upgrade_minor>`
(e.g. 5.4) so that you can see *all* the deprecations.

To help you find deprecations, notices are triggered whenever you end up
using a deprecated feature. When visiting your application in the
:ref:`dev environment <configuration-environments>`
in your browser, these notices are shown in the web dev toolbar:

.. image:: /_images/install/deprecations-in-profiler.png
    :alt: The Logs page of the Symfony Profiler showing the deprecation notices.
    :class: with-browser

Ultimately, you should aim to stop using the deprecated functionality.
Sometimes the warning might tell you exactly what to change.

But other times, the warning might be unclear: a setting somewhere might
cause a class deeper to trigger the warning. In this case, Symfony does its
best to give a clear message, but you may need to research that warning further.

And sometimes, the warning may come from a third-party library or bundle
that you're using. If that's true, there's a good chance that those deprecations
have already been updated. In that case, upgrade the library to fix them.

.. tip::

    `Rector`_ is a third-party project that automates the upgrading and
    refactoring of PHP projects. Rector includes some rules to fix certain
    Symfony deprecations automatically.

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
    in 6.4 and you still need to support 6.3). In these cases, you can still
    use the bridge to fix as many deprecations as possible and then allow
    more of them to make your tests pass again. You can do this by using the
    ``SYMFONY_DEPRECATIONS_HELPER`` env variable:

    .. code-block:: xml

        <!-- phpunit.dist.xml -->
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
    -         "symfony/config": "6.4.*",
    +         "symfony/config": "7.0.*",
    -         "symfony/console": "6.4.*",
    +         "symfony/console": "7.0.*",
              "...": "...",

              "...": "A few libraries starting with symfony/ follow their own
                      versioning scheme (e.g. symfony/polyfill-[...],
                      symfony/ux-[...], symfony/[...]-bundle).
                      You do not need to update these versions: you can
                      upgrade them independently whenever you want",
              "symfony/monolog-bundle": "^3.10",
          },
          "...": "...",
      }

A more efficient way to handle Symfony dependency updates is by setting the
``extra.symfony.require`` configuration option in your ``composer.json`` file.
In Symfony applications using :doc:`Symfony Flex </setup/flex>`, this setting
restricts Symfony packages to a single specific version, improving both
dependency management and Composer update performance:

.. code-block:: diff

      {
          "...": "...",

          "require": {
    -         "symfony/cache": "7.0.*",
    +         "symfony/cache": "*",
    -         "symfony/config": "7.0.*",
    +         "symfony/config": "*",
    -         "symfony/console": "7.0.*",
    +         "symfony/console": "*",
              "...": "...",
          },
          "...": "...",

    +     "extra": {
    +         "symfony": {
    +             "require": "7.0.*"
    +         }
    +     }
      }

.. warning::

    Tools like `dependabot`_ may ignore this setting and upgrade Symfony
    dependencies. For more details, see this `GitHub issue about dependabot`_.

.. tip::

    If a more recent minor version is available (e.g. ``6.4``) you can use that
    version directly and skip the older releases (``6.0``, ``6.1``, etc.).
    Check the `maintained Symfony versions`_.

Next, use Composer to download new versions of the libraries:

.. code-block:: terminal

    $ composer update "symfony/*"

A best practice after updating to a new major version is to clear the cache.
Instead of running the ``cache:clear`` command (which won't work if the application
is not bootable in the console after the upgrade) it's better to remove the entire
cache directory contents:

.. code-block:: terminal

    # run this command on Linux and macOS
    $ rm -rf var/cache/*

    # run this command on Windows
    C:\> rmdir /s /q var\cache\*

.. include:: /setup/_update_dep_errors.rst.inc

.. include:: /setup/_update_all_packages.rst.inc

.. _upgrade-major-symfony-after:

.. include:: /setup/_update_recipes.rst.inc

4) Update your Code to Work with the New Version
------------------------------------------------

In some rare situations, the next major version *may* contain backwards-compatibility
breaks. Make sure you read the ``UPGRADE-X.0.md`` (where X is the new major version)
included in the Symfony repository for any BC break that you need to be aware of.

.. _`Rector`: https://github.com/rectorphp/rector
.. _`maintained Symfony versions`: https://symfony.com/releases
.. _`dependabot`: https://docs.github.com/en/code-security/dependabot
.. _`GitHub issue about dependabot`: https://github.com/dependabot/dependabot-core/issues/4631
