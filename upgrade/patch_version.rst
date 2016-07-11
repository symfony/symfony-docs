.. index::
    single: Upgrading; Patch Version

Upgrading a Patch Version (e.g. 2.6.0 to 2.6.1)
===============================================

When a new patch version is released (only the last number changed), it is a
release that only contains bug fixes. This means that upgrading to a new patch
version is *really* easy:

.. code-block:: bash

    $ composer update symfony/symfony

That's it! You should not encounter any backwards-compatibility breaks or
need to change anything else in your code. That's because when you started
your project, your ``composer.json`` included Symfony using a constraint
like ``2.6.*``, where only the *last* version number will change when you
update.

.. tip::

    It is recommended to update to a new patch version as soon as possible, as
    important bugs and security leaks may be fixed in these new releases.

.. include:: /cookbook/upgrade/_update_all_packages.rst.inc
