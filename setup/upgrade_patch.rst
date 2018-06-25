.. index::
    single: Upgrading; Patch Version

Upgrading a Patch Version (e.g. 4.1.0 to 4.1.1)
===============================================

When a new patch version is released (only the last number changed), it is a
release that only contains bug fixes. This means that upgrading to a new patch
version is *really* easy:

.. code-block:: terminal

    $ composer update "symfony/*"

That's it! You should not encounter any backwards-compatibility breaks or
need to change anything else in your code. That's because when you started
your project, your ``composer.json`` included Symfony using a constraint
like ``^4.1``, where only the *last* version number will change when you
update.

.. tip::

    It is recommended to update to a new patch version as soon as possible, as
    important bugs and security vulnerabilities may be fixed in these new
    releases.

.. include:: /setup/_update_all_packages.rst.inc
