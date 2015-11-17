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
            "symfony/validator": "~2.3",
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
            "symfony/validator": "~2.3|~3.0",
        }
    }

Look for Deprecations and Fix Them
----------------------------------

.. TODO

* Install: composer require --dev "symfony/phpunit-bridge" and run your test suite
* Use for basic fixes: https://github.com/umpirsky/Symfony-Upgrade-Fixer
* Read the "UPGRADE from 2.x to Sf3" guide (https://github.com/symfony/symfony/blob/2.8/UPGRADE-3.0.md)

Test your Bundle in Symfony 3
-----------------------------

.. TODO

* Upgrade a test app to Sf3 or create an empty app (symfony new my_app 3.0)
* Use the "ln -s my_bundle vendor/.../my_bundle" trick to use the new code in the 3.0 app
* Configure Travis CI to test your bundle in both 2 and 3 versions.
