.. index::
    single: Upgrading; Minor Version

Upgrading a Minor Version (e.g. 2.5.3 to 2.6.1)
===============================================

If you're upgrading a minor version (where the middle number changes), then
you should *not* encounter significant backwards compatibility changes. For
details, see the :doc:`Symfony backwards compatibility promise </contributing/code/bc>`.

However, some backwards-compatibility breaks *are* possible and you'll learn in
a second how to prepare for them.

There are two steps to upgrading a minor version:

#. :ref:`Update the Symfony library via Composer <upgrade-minor-symfony-composer>`;
#. :ref:`Update your code to work with the new version <upgrade-minor-symfony-code>`.

.. _`upgrade-minor-symfony-composer`:

1) Update the Symfony Library via Composer
------------------------------------------

First, you need to update Symfony by modifying your ``composer.json`` file
to use the new version:

.. code-block:: json

    {
        "...": "...",

        "require": {
            "symfony/symfony": "2.6.*",
        },
        "...": "...",
    }

Next, use Composer to download new versions of the libraries:

.. code-block:: bash

    $ composer update symfony/symfony

.. include:: /cookbook/upgrade/_update_dep_errors.rst.inc

.. include:: /cookbook/upgrade/_update_all_packages.rst.inc

.. _`upgrade-minor-symfony-code`:

2) Updating your Code to Work with the new Version
--------------------------------------------------

In theory, you should be done! However, you *may* need to make a few changes
to your code to get everything working. Additionally, some features you're
using might still work, but might now be deprecated. While that's just fine,
if you know about these deprecations, you can start to fix them over time.

Every version of Symfony comes with an UPGRADE file (e.g. `UPGRADE-2.7.md`_)
included in the Symfony directory that describes these changes. If you follow
the instructions in the document and update your code accordingly, it should be
safe to update in the future.

These documents can also be found in the `Symfony Repository`_.

.. _`Symfony Repository`: https://github.com/symfony/symfony
.. _`UPGRADE-2.7.md`: https://github.com/symfony/symfony/blob/2.7/UPGRADE-2.7.md
