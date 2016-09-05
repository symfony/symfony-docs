Dependency Errors
~~~~~~~~~~~~~~~~~

If you get a dependency error, it may simply mean that you need to upgrade
other Symfony dependencies too. In that case, try the following command:

.. code-block:: bash

    $ composer update symfony/symfony --with-dependencies

This updates ``symfony/symfony`` and *all* packages that it depends on, which will
include several other packages. By using tight version constraints in
``composer.json``, you can control what versions each library upgrades to.

If this still doesn't work, your ``composer.json`` file may specify a version
for a library that is not compatible with the newer Symfony version. In that
case, updating that library to a newer version in ``composer.json`` may solve
the issue.

Or, you may have deeper issues where different libraries depend on conflicting
versions of other libraries. Check your error message to debug.
