Upgrading a Minor Version (e.g. 6.3.0 to 6.4.0)
===============================================

If you're upgrading a minor version (where the middle number changes), then
you should *not* encounter significant backward compatibility changes. For
details, see the :doc:`Symfony backward compatibility promise </contributing/code/bc>`.

However, some backwards-compatibility breaks *are* possible and you'll learn in
a second how to prepare for them.

There are two steps to upgrading a minor version:

#. :ref:`Update the Symfony library via Composer <upgrade-minor-symfony-composer>`;
#. :ref:`Update your code to work with the new version <upgrade-minor-symfony-code>`.

.. _`upgrade-minor-symfony-composer`:

1) Update the Symfony Library via Composer
------------------------------------------

The ``composer.json`` file is configured to allow Symfony packages to be
upgraded to patch versions. But to upgrade to a new minor version, you will
probably need to update the version constraint next to each library starting
``symfony/``. Suppose you are upgrading from Symfony 6.3 to 6.4:

.. code-block:: diff

      {
          "...": "...",

          "require": {
    -         "symfony/config": "6.3.*",
    +         "symfony/config": "6.4.*",
    -         "symfony/console": "6.3.*",
    +         "symfony/console": "6.4.*",
              "...": "...",

              "...": "A few libraries starting with
                      symfony/ follow their own versioning scheme. You
                      do not need to update these versions: you can
                      upgrade them independently whenever you want",
              "symfony/monolog-bundle": "^3.10",
          },
          "...": "...",
      }

Your ``composer.json`` file should also have an ``extra`` block that you will
*also* need to update:

.. code-block:: diff

      "extra": {
          "symfony": {
              "...": "...",
    -         "require": "6.3.*"
    +         "require": "6.4.*"
          }
      }

Next, use Composer to download new versions of the libraries:

.. code-block:: terminal

    $ composer update "symfony/*"

.. include:: /setup/_update_dep_errors.rst.inc

.. include:: /setup/_update_all_packages.rst.inc

.. _`upgrade-minor-symfony-code`:

2) Updating your Code to Work with the new Version
--------------------------------------------------

In theory, you should be done! However, you *may* need to make a few changes
to your code to get everything working. Additionally, some features you're
using might still work, but might now be deprecated. While that's fine,
if you know about these deprecations, you can start to fix them over time.

Every version of Symfony comes with an UPGRADE file (e.g. `UPGRADE-6.4.md`_)
included in the Symfony directory that describes these changes. If you follow
the instructions in the document and update your code accordingly, it should be
safe to update in the future.

.. tip::

    `Rector`_ is a third-party project that automates the upgrading and
    refactoring of PHP projects. Rector includes some rules to fix certain
    Symfony deprecations automatically.

These documents can also be found in the `Symfony Repository`_.

.. _updating-flex-recipes:

.. include:: /setup/_update_recipes.rst.inc

.. _`Symfony Repository`: https://github.com/symfony/symfony
.. _`UPGRADE-6.4.md`: https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.4.md
.. _`Rector`: https://github.com/rectorphp/rector
