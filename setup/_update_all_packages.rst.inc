Upgrading other Packages
~~~~~~~~~~~~~~~~~~~~~~~~

You may also want to upgrade the rest of your libraries. If you've done a
good job with your `version constraints`_ in ``composer.json``, you can do
this safely by running:

.. code-block:: terminal

    $ composer update

.. caution::

    Beware, if you have some unspecific `version constraints`_ in your
    ``composer.json`` (e.g. ``dev-master``), this could upgrade some
    non-Symfony libraries to new versions that contain backwards-compatibility
    breaking changes.

.. _`version constraints`: https://getcomposer.org/doc/articles/versions.md

If you are in a default Symfony application that uses :ref:`Symfony Flex <symfony-flex>`
and Symfony Recipes, you may also want to reinstall them to their latest version:

.. code-block:: terminal

    $ composer sync-recipes --force

.. caution::

    By doing this, you reinstall all the recipes again for all packages.
    It is your job to carefully verify the code changes as it may override
    your custom code.
