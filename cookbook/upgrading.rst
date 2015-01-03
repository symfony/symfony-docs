How to Upgrade Your Symfony Project
===================================

So a new Symfony release has come out and you want to upgrade, great! Fortunately,
because Symfony protects backwards-compatibility very closely, this *should*
be quite easy.

There are two types of upgrades, and both are a little different:

* :ref:`upgrading-patch-version`
* :ref:`upgrading-minor-version`

.. _upgrading-patch-version:

Upgrading a Patch Version (e.g. 2.6.0 to 2.6.1)
-----------------------------------------------

If you're upgrading and only the patch version (the last number) is changing,
then it's *really* easy:

.. code-block:: bash

    $ composer update symfony/symfony

That's it! You should not encounter any backwards-compatibility breaks or
need to change anything else in your code. That's because when you started
your Symfony project, your ``composer.json`` included Symfony using a constraint
such as ``2.6.*``, where only the *last* version number changes when you update.

You may also want to upgrade the rest of your libraries. If you've done a
good job with your version constraints in ``composer.json``, you can do this
safely by running:

.. code-block:: bash

    $ composer update

But beware. If you have some bad version constraints in your ``composer.json``,
(e.g. ``dev-master``), then this could upgrade some non-Symfony libraries
to new versions that contain backwards-compatibility breaking changes.

.. _upgrading-minor-version:

Upgrading a Minor Version (e.g. 2.5.3 to 2.6.0)
-----------------------------------------------

If you're upgrading a minor version (where the middle number changes), then
you should also *not* encounter significant backwards compatibility changes.
For details, see our :doc:`/contributing/code/bc`.

However, some backwards-compatibility breaks *are* possible, and you'll learn
in a second how to prepare for them.

There are two steps to upgrading:

#. :ref:`upgrade-minor-symfony-composer`;
#. :ref:`upgrade-minor-symfony-code`, which includes instructions for each version.

.. _`upgrade-minor-symfony-composer`:

Update the Symfony Library via Composer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

First, you need to update Symfony by modifying your ``composer.json`` to
use the new version:

.. code-block:: json

    {
        "...": "...",

        "require": {
            "php": ">=5.3.3",
            "symfony/symfony": "~2.6.*",
            "...": "... no changes to anything else..."
        },
        "...": "...",
    }

Next, use Composer to download new versions of the libraries:

.. code-block:: bash

    $ composer update symfony/symfony

Updating a minor version like this should *not* cause any dependency issues,
though it's always possible that an outside library or bundle you're using
didn't support this new version of Symfony at the version you have of that
library. In that case, consult the library: you may need to modify its version
in ``composer.json`` and run a full ``composer update``. 

.. _`upgrade-minor-symfony-code`:

Updating Your Code to Work with the new Version
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In theory, you should be done! However, you *may* need to make a few changes
to your code to get everything working. Additionally, some features you're
using might still work, but might now be deprecated. That's actually ok,
but if you know about these deprecations, you can start to fix them over
time.

Every version of Symfony comes with an UPGRADE file that describes these
changes. Below are links to the file for each version, which you'll need
to read to see if you need any code changes.

.. tip::

    Don't see the version here that you're upgrading too? Just find the
    UPGRADE-X.X.md file for the appropriate version on the `Symfony Repository`_.

Upgrading to Symfony 2.6
........................

First, of course, update your ``composer.json`` file with the ``2.6`` version
of Symfony as described above in :ref:`upgrade-minor-symfony-composer`.

Next, check the `UPGRADE-2.6`_ document for details about any code changes
that you might need to make in your project.

* If you're using PdoSessionStorage, there was a change in the session schema
  that **requires** your session table to be updated. See :doc:`/cookbook/configuration/pdo_session_storage`.

* Symfony 2.6 comes with a great new `dump`_ function. To use it, you'll
  need to add the new ``DebugBundle`` to your ``AppKernel``. See
  `UPGRADE-2.6-DebugBundle`_ for details.

Upgrading to Symfony 2.5
........................

First, of course, update your ``composer.json`` file with the ``2.5`` version
of Symfony as described above in :ref:`upgrade-minor-symfony-composer`.

Next, check the `UPGRADE-2.5`_ document for details about any code changes
that you might need to make in your project.

.. _`UPGRADE-2.5`: https://github.com/symfony/symfony/blob/2.5/UPGRADE-2.5.md
.. _`UPGRADE-2.6`: https://github.com/symfony/symfony/blob/2.6/UPGRADE-2.6.md
.. _`Symfony Repository`: https://github.com/symfony/symfony
