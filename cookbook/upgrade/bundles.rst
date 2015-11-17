.. index::
    single: Upgrading; Bundle; Major Version

Upgrading a Third-Party Bundle for a Major Symfony Version
==========================================================

Symfony 3 was released on November 2015. Although this version doesn't contain
any new feature, it removes all the backwards compatibility layers included in
the previous 2.8 version. If your bundle uses any deprecated feature and it's
published as a third-party bundle, applications upgrading to Symfony 3 will no
longer be able to use it.

Allow to Install Symfony 3 Components
-------------------------------------

Most third-party bundles define their Symfony dependencies using the ``~2.N`` or
``^2.N`` constraints in the ``composer.json`` file. For example:

.. code-block:: json

    {
        "require": {
            "symfony/framework-bundle": "~2.3",
            "symfony/finder": "~2.3",
            "symfony/validator": "~2.3"
        }
    }

These constraints prevent the bundle from using Symfony 3 components, so it makes
it impossible to install it in a Symfony 3 based application. This issue is very
easy to solve thanks to the flexibility of Composer dependencies constraints.
Just replace ``~2.N`` by ``~2.N|~3.0`` (or ``^2.N`` by ``^2.N|~3.0``).

The above example can be updated to work with Symfony 3 as follows:

.. code-block:: json

    {
        "require": {
            "symfony/framework-bundle": "~2.3|~3.0",
            "symfony/finder": "~2.3|~3.0",
            "symfony/validator": "~2.3|~3.0"
        }
    }

Look for Deprecations and Fix Them
----------------------------------

Besides allowing to install Symfony 3 component, your bundle must stop using
any feature deprecated in 2.8 version, because they'll throw exceptions in 3.0
version. The easiest way to detect deprecations is to install the `PHPUnit Bridge`_
component and then run the test suite.

First, install the component as a ``dev`` dependency of your bundle:

.. code-block:: bash

    $ composer require --dev "symfony/phpunit-bridge"

Then, run your test suite and look for the deprecation list displayed after the
PHPUnit test report:

.. code-block:: bash

    $ phpunit

    // ... PHPUnit output ...

    Remaining deprecation notices (3)

    The "pattern" option in file ... is deprecated since version 2.2 and will be
    removed in 3.0. Use the "path" option in the route definition instead ...

    Twig Function "form_enctype" is deprecated. Use "form_start" instead in ...

    The Symfony\Component\Security\Core\SecurityContext class is deprecated since
    version 2.6 and will be removed in 3.0. Use ...

Fix the reported deprecations, run the test suite again and repeat the process
until no deprecation usage is reported.

Useful Resources
~~~~~~~~~~~~~~~~

There are several resources that can help you detect, understand and fix the use
of deprecated features:

* `Official Symfony Guide to Upgrade from 2.x to 3.0`_, the full list of changes
  required to upgrade to Symfony 3.0 and grouped by component.
* `SensioLabs DeprecationDetector`_, it runs a static code analysis against your
  project's source code to find usages of deprecated methods, classes and
  interfaces. It works for any PHP application, but it includes special detectors
  for Symfony application, where it can also detect usages of deprecated services.
* `Symfony Upgrade Fixer`_, it analyzes Symfony projects to find deprecations. In
  addition it solves automatically some of them thanks to the growing list of
  supported "fixers".

Test your Bundle in Symfony 3
-----------------------------

.. TODO

* Upgrade a test app to Sf3 or create an empty app (symfony new my_app 3.0)
* Use the "ln -s my_bundle vendor/.../my_bundle" trick to use the new code in the 3.0 app
* Configure Travis CI to test your bundle in both 2 and 3 versions.

.. _`PHPUnit Bridge`: https://github.com/symfony/phpunit-bridge
.. _`Official Symfony Guide to Upgrade from 2.x to 3.0`: https://github.com/symfony/symfony/blob/2.8/UPGRADE-3.0.md
.. _`SensioLabs DeprecationDetector`: https://github.com/sensiolabs-de/deprecation-detector
.. _`Symfony Upgrade Fixer`: https://github.com/umpirsky/Symfony-Upgrade-Fixer