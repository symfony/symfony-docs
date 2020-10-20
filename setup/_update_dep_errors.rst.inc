Dependency Errors
~~~~~~~~~~~~~~~~~

If you get a dependency error, it may mean that you also need to upgrade
other libraries that are dependencies of the Symfony libraries. To allow
that, pass the ``--with-all-dependencies`` flag:

.. code-block:: terminal

    $ composer update "symfony/*" --with-all-dependencies

This updates ``symfony/*`` and *all* packages that those packages depend on.
By using tight version constraints in ``composer.json``, you can control what
versions each library upgrades to.

If this still doesn't work, your ``composer.json`` file may specify a version
for a library that is not compatible with the newer Symfony version. In that
case, updating that library to a newer version in ``composer.json`` may solve
the issue.

Or, you may have deeper issues where different libraries depend on conflicting
versions of other libraries. Check your error message to debug.

Another issue that may happen is that the project dependencies can be installed
on your local computer but not on the remote server. This usually happens when
the PHP versions are different on each machine. The solution is to add the
`platform`_ config option to your `composer.json` file to define the highest
PHP version allowed for the dependencies (set it to the server's PHP version).

.. _`platform`: https://getcomposer.org/doc/06-config.md#platform
