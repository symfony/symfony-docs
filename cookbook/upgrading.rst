How to Upgrade your Symfony Project
===================================

So a new Symfony release has come out and you want to upgrade, great! Fortunately,
because Symfony protects backwards-compatibility very closely, this *should*
be quite easy.

Upgrading a Patch Version (e.g. 2.6.0 to 2.6.1)
-----------------------------------------------

If you're upgrading and only the patch version (the last number) is changing,
then it's *really* easy:

.. code-block:: bash

    $ composer update symfony/symfony

That's it! You should not encounter any backwards-compatability breaks or
need to change anything else in your code.

You may also want to upgrade the rest of your libraries. If you've done a
good job with your version constraints in ``composer.json``, you can do this
safely by running:

.. code-block:: bash

    $ composer update symfony/symfony

But beware. If you have some bad version constraints in your ``composer.json``,
(e.g. ``dev-master``), then this could upgrade some non-Symfony libraries
to new versions that contain backwards-compability changes.

Upgrading a Minor Version (e.g. 2.5.3 to 2.6.0)
-----------------------------------------------

If you're upgrading a minor version (where the middle number changes), then
you should also *not* encounter significant backwards compability changes.
For details, see our :doc:`/contributing/code/bc`.

However, some backwards-compability breaks *are* possible, and you'll learn
in a second how to prepare for them.

There are two steps to upgrading:

1. :ref:`upgrade-minor-symfony-composer`;
2. :ref:`upgrade-minor-symfony-code`, which includes instructions for each version.

.. _`upgrade-minor-symfony-composer`:

Update the Symfony Library
~~~~~~~~~~~~~~~~~~~~~~~~~~

First, you need to update Symfony by modifying your ``composer.json`` to
use the new version:

.. code-block:: json

    {
        "...": "...",

        "require": {
            "php": ">=5.3.3",
            "symfony/symfony": "~2.6.0",
            "...": "... no changes to anything else..."
        },
        "...": "...",
    }

Next, update the same as before:

.. code-block:: bash

    $ composer update symfony/symfony

Updating a minor version like this should *not* cause any dependency issues,
though it's always possible that an outside library or bundle you're using
didn't support this new version of Symfony at the version you have of that
library.  In that case, consult the library: you may need to modify its version
in ``composer.json`` and run a full ``composer update``. 

.. _`upgrade-minor-symfony-code`:

Updating your Code to work with the new Version
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In theory, you should be done! However, you *may* need to make a few changes
to your code to get everything working. Additionally, some features you're
using might still work, but might now be deprecated. That's actually ok,
but if you know about these deprecations, you can start to fix them over
time.

Every version of Symfony comes with an UPGRADE file that describes these
changes. Below are links to the file for each version, along with some other
details.

Upgrading to Symfony 2.6
........................

First, of course, update your ``composer.json`` file with the ``2.6`` version
of Symfony as described above in :ref:`upgrade-minor-symfony-composer`.

Check the `UPGRADE-2.6`_ document for details. Highlights:

* If you're using PdoSessionStorage, there was a change in the session schema
  that **requires** your session table to be updated. See :doc:`/cookbook/configuration/pdo_session_storage`.

* Symfony 2.6 comes with a great new `dump`_ function. To use it, you'll
  need to add the new ``DebugBundle`` to your ``AppKernel``. See
  `UPGRADE-2.6-DebugBundle`_ for details.

Upgrading to Symfony 2.5
........................

First, of course, update your ``composer.json`` file with the ``2.5`` version
of Symfony as described above in :ref:`upgrade-minor-symfony-composer`.

Check the `UPGRADE-2.5`_ document for details. Highlights:

* This version introduced a new Validator API. But, as long as you're using
  PHP 5.3.9 or higher, you can configure Symfony in a way that allows you
  to use the new API, but still let the old API work (called ``2.5-bc``).
  See the `UPGRADE-2.5-Validator`_ for details.

.. _`UPGRADE-2.5`: https://github.com/symfony/symfony/blob/2.5/UPGRADE-2.5.md
.. _`UPGRADE-2.5-Validator`: https://github.com/symfony/symfony/blob/2.7/UPGRADE-2.5.md#validator
.. _`UPGRADE-2.6`: https://github.com/symfony/symfony/blob/2.6/UPGRADE-2.6.md
.. _`UPGRADE-2.6-DebugBundle`: https://github.com/symfony/symfony/blob/2.6/UPGRADE-2.6.md#vardumper-and-debugbundle
