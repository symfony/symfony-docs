Upgrading a Major Version (e.g. 5.4.0 to 6.0.0)
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

When the major version is released (e.g. 6.0.0), all deprecated features and
functionality are removed. So, as long as you've updated your code to stop
using these deprecated features in the last version before the major (e.g.
``5.4.*``), you should be able to upgrade without a problem. That means that
you should first :doc:`upgrade to the last minor version </setup/upgrade_minor>`
(e.g. 5.4) so that you can see *all* the deprecations.

To help you find deprecations, notices are triggered whenever you end up
using a deprecated feature. When visiting your application in the
:ref:`dev environment <configuration-environments>`
in your browser, these notices are shown in the web dev toolbar:

.. image:: /_images/install/deprecations-in-profiler.png
   :align: center
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

.. caution::

    You will probably see many deprecations about incompatible native
    return types. See :ref:`Add Native Return Types <upgrading-native-return-types>`
    for guidance in fixing these deprecations.

.. sidebar:: Using the Weak Deprecations Mode

    Sometimes, you can't fix all deprecations (e.g. something was deprecated
    in 5.4 and you still need to support 5.3). In these cases, you can still
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
    -         "symfony/cache": "5.4.*",
    +         "symfony/cache": "6.0.*",
    -         "symfony/config": "5.4.*",
    +         "symfony/config": "6.0.*",
    -         "symfony/console": "5.4.*",
    +         "symfony/console": "6.0.*",
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
this one. For instance, update it to ``6.0.*`` to upgrade to Symfony 6.0:

.. code-block:: diff

      "extra": {
          "symfony": {
              "allow-contrib": false,
    -       "require": "5.4.*"
    +       "require": "6.0.*"
          }
      }

Next, use Composer to download new versions of the libraries:

.. code-block:: terminal

    $ composer update "symfony/*"

.. include:: /setup/_update_dep_errors.rst.inc

.. include:: /setup/_update_all_packages.rst.inc

.. _upgrade-major-symfony-after:

.. include:: /setup/_update_recipes.rst.inc

4) Update your Code to Work with the New Version
------------------------------------------------

In some rare situations, the next major version *may* contain backwards-compatibility
breaks. Make sure you read the ``UPGRADE-X.0.md`` (where X is the new major version)
included in the Symfony repository for any BC break that you need to be aware of.

.. _upgrading-native-return-types:

Upgrading to Symfony 6: Add Native Return Types
-----------------------------------------------

Symfony 6 will come with native PHP return types to (almost all) methods.

In PHP, if the parent has a return type declaration, any class implementing
or overriding the method must have the return type as well. However, you
can add a return type before the parent adds one. This means that it is
important to add the native PHP return types to your classes before
upgrading to Symfony 6.0. Otherwise, you will get incompatible declaration
errors.

When debug mode is enabled (typically in the dev and test environment),
Symfony will trigger deprecations for every incompatible method
declarations. For instance, the ``UserInterface::getRoles()`` method will
have an ``array`` return type in Symfony 6. In Symfony 5.4, you will get a
deprecation notice about this and you must add the return type declaration
to your ``getRoles()`` method.

To help with this, Symfony provides a script that can add these return
types automatically for you. Make sure you installed the ``symfony/error-handler``
component. When installed, generate a complete class map using Composer and
run the script to iterate over the class map and fix any incompatible
method:

.. code-block:: terminal

    # Make sure "exclude-from-classmap" is not filled in your "composer.json". Then dump the autoloader:

    # "-o" is important! This forces Composer to find all classes
    $ composer dump-autoload -o

    # patch all incompatible method declarations
    $ ./vendor/bin/patch-type-declarations

.. tip::

    This feature is not limited to Symfony packages. It will also help you
    add types and prepare for other dependencies in your project.

The behavior of this script can be modified using the ``SYMFONY_PATCH_TYPE_DECLARATIONS``
env var. The value of this env var is url-encoded (e.g.
``param1=value1&param2=value2``), the following parameters are available:

``force``
    Enables fixing return types, the value must be one of:

    * ``2`` to add all possible return types (default, recommended for applications);
    * ``1`` to add return types only to tests, final, internal or private methods;
    * ``phpdoc`` to only add ``@return`` docblock annotations to the incompatible
      methods, or ``#[\ReturnTypeWillChange]`` if it's triggered by the PHP engine.

``php``
    The target version of PHP - e.g. ``7.1`` doesn't generate "object"
    types (which were introduced in 7.2). This defaults to the PHP version
    used when running the script.

``deprecations``
    Set to ``0`` to disable deprecations. Otherwise, a deprecation notice
    when a child class misses a return type while the parent declares an
    ``@return`` annotation (defaults to ``1``).

If there are specific files that should be ignored, you can set the
``SYMFONY_PATCH_TYPE_EXCLUDE`` env var to a regex. This regex will be
matched to the full path to the class and each matching path will be
ignored (e.g. ``SYMFONY_PATCH_TYPE_EXCLUDE="/tests\/Fixtures\//"``).
Classes in the ``vendor/`` directory are always ignored.

.. tip::

    The script does not care about code style. Run your code style fixer,
    or `PHP CS Fixer`_ with the ``phpdoc_trim_consecutive_blank_line_separation``,
    ``no_superfluous_phpdoc_tags`` and ``ordered_imports`` rules, after
    patching the types.

.. _patching-types-for-open-source-maintainers:

.. sidebar:: Patching Types for Open Source Maintainers

    Open source bundles and packages need to be more cautious with adding
    return types, as adding a return type forces all users extending the
    class to add the return type as well. The recommended approach is to
    use a 2 step process:

    1. First, create a minor release (i.e. without backwards compatibility
       breaks) where you add types that can be safely introduced and add
       ``@return`` PHPDoc to all other methods:

       .. code-block:: terminal

           # Add type declarations to all internal, final, tests and private methods.
           # Update the "php" parameter to match your minimum required PHP version
           $ SYMFONY_PATCH_TYPE_DECLARATIONS="force=1&php=7.4" ./vendor/bin/patch-type-declarations

           # Add PHPDoc to the leftover public and protected methods
           $ SYMFONY_PATCH_TYPE_DECLARATIONS="force=phpdoc&php=7.4" ./vendor/bin/patch-type-declarations

       After running the scripts, check your classes and add more ``@return``
       PHPDoc where they are missing. The deprecations and patch script
       work purely based on the PHPDoc information. Users of this release
       will get deprecation notices telling them to add the missing return
       types from your package to their code.

       If you didn't need any PHPDoc and all your method declarations are
       already compatible with Symfony, you can safely allow ``^6.0`` for
       the Symfony dependencies. Otherwise, you have to continue with (2).

    2. Create a new major release (i.e. *with* backwards compatibility
       breaks) where you add types to all methods:

       .. code-block:: terminal

           # Update the "php" parameter to match your minimum required PHP version
           $ SYMFONY_PATCH_TYPE_DECLARATIONS="force=2&php=7.4" ./vendor/bin/patch-type-declarations

       Now, you can safely allow ``^6.0`` for the Symfony dependencies.

.. _`PHP CS Fixer`: https://github.com/friendsofphp/php-cs-fixer
.. _`Rector`: https://github.com/rectorphp/rector
