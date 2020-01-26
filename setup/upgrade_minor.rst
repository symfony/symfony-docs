.. index::
    single: Upgrading; Minor Version

Upgrading a Minor Version (e.g. 4.0.0 to 4.1.0)
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
``symfony/``. Suppose you are upgrading from Symfony 4.3 to 4.4:

.. code-block:: diff

    {
        "...": "...",

        "require": {
    -         "symfony/cache": "4.3.*",
    +         "symfony/cache": "4.4.*",
    -         "symfony/config": "4.3.*",
    +         "symfony/config": "4.4.*",
    -         "symfony/console": "4.3.*",
    +         "symfony/console": "4.4.*",
            "...": "...",

            "...": "A few libraries starting with
                    symfony/ follow their versioning scheme. You
                    do not need to update these versions: you can
                    upgrade them independently whenever you want",
            "symfony/monolog-bundle": "^3.5",
        },
        "...": "...",
    }

Your ``composer.json`` file should also have an ``extra`` block that you will
*also* need to update:

.. code-block:: diff

    "extra": {
        "symfony": {
            "...": "...",
    -         "require": "4.3.*"
    +         "require": "4.4.*"
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
using might still work, but might now be deprecated. While that's just fine,
if you know about these deprecations, you can start to fix them over time.

Every version of Symfony comes with an UPGRADE file (e.g. `UPGRADE-4.4.md`_)
included in the Symfony directory that describes these changes. If you follow
the instructions in the document and update your code accordingly, it should be
safe to update in the future.

These documents can also be found in the `Symfony Repository`_.

.. _updating-flex-recipes:

3) Updating Recipes
-------------------

Over time - and especially when you upgrade to a new version of a library - an
updated version of the :ref:`recipe <recipes-description>` may be available.
These updates are usually minor - e.g. new comments in a configuration file - but
it's a good idea to update the core Symfony recipes.

Symfony Flex provides several commands to help upgrade your recipes. Be sure to
commit any unrelated changes you're working on before starting:

.. versionadded:: 1.6

    The recipes commands were introduced in Symfony Flex 1.6.

.. code-block:: terminal

    # see a list of all installed recipes and which have updates available
    $ composer recipes

    # see detailed information about a specific recipes
    $ composer recipes symfony/framework-bundle

    # update a specific recipes
    $ composer recipes:install symfony/framework-bundle --force -v

The tricky part of this process is that the recipe "update" does not perform
any intelligent "upgrading" of your code. Instead, **the updates process re-installs
the latest version of the recipe** which means that **your custom code will be
overridden completely**. After updating a recipe, you need to carefully choose
which changes you want, and undo the rest.

.. admonition:: Screencast
    :class: screencast

    For a detailed example, see the `SymfonyCasts Symfony 5 Upgrade Tutorial`_.

.. _`Symfony Repository`: https://github.com/symfony/symfony
.. _`UPGRADE-4.4.md`: https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.4.md
.. _`SymfonyCasts Symfony 5 Upgrade Tutorial`: https://symfonycasts.com/screencast/symfony5-upgrade
