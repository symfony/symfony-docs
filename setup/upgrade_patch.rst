Upgrading a Patch Version (e.g. 6.0.0 to 6.0.1)
===============================================

When a new patch version is released (only the last number changed), it is a
release that only contains bug fixes. This means that upgrading to a new patch
version should not cause any problems.

To upgrade to a new "patch" release, read the
:doc:`Upgrading a Minor Version </setup/upgrade_minor>` article. Thanks to
Symfony's :doc:`backwards compatibility promise </contributing/code/bc>`, it's
always safe to upgrade to the latest "minor" version.

.. tip::

    It is recommended to update to a new patch version as soon as possible, as
    important bugs and security vulnerabilities may be fixed in these new
    releases.

.. include:: /setup/_update_all_packages.rst.inc
