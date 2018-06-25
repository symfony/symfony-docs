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

First, you need to update all Symfony packages by modifying your ``composer.json`` file
to use the new version (the list of packages may vary depending on what you've installed):

.. code-block:: json

    {
        "...": "...",

        "require": {
            "symfony/asset": "^4.1",
            "symfony/console": "^4.1",
            "symfony/expression-language": "^4.1",
            "symfony/form": "^4.1",
            "symfony/framework-bundle": "^4.1",
            "symfony/process": "^4.1",
            "symfony/security-bundle": "^4.1",
            "symfony/twig-bundle": "^4.1",
            "symfony/validator": "^4.1",
            "symfony/web-link": "^4.1",
            "symfony/yaml": "^4.1"
        },
        "...": "...",
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

Every version of Symfony comes with an UPGRADE file (e.g. `UPGRADE-4.1.md`_)
included in the Symfony directory that describes these changes. If you follow
the instructions in the document and update your code accordingly, it should be
safe to update in the future.

These documents can also be found in the `Symfony Repository`_.

.. _`Symfony Repository`: https://github.com/symfony/symfony
.. _`UPGRADE-4.1.md`: https://github.com/symfony/symfony/blob/4.1/UPGRADE-4.1.md
