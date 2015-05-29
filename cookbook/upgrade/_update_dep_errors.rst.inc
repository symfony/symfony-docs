Dependency Errors
~~~~~~~~~~~~~~~~~

If you get a dependency error, it may just mean that you *also* need to upgrade
that package too. For example, Symfony 2.7 requires a higher version of Twig,
so just upgrading ``symfony/symfony`` alone may give you an error. In this
case, try adding the package in question to the ``update`` statement:

    $ composer update symfony/symfony twig/twig

If this still doesn't work, your ``composer.json`` file may specify a version
for a library that is not compatible with the newer Symfony version. In that
case, updating that library to a newer version in ``composer.json`` may solve
the issue.

Or, you may have deeper issues where different libraries depend on conflicting
versions of other libraries. Check your error message to debug.
